<?php

class Purchase_Model_DbTable_DbAuditapprov extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_Purchase_order";
	function getAllPurchaseOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
		(SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,date_order,date_in,
		(SELECT symbal FROM `tb_currency` WHERE id= currency_id LIMIT 1) AS curr_name,
		net_total,paid,balance,purchase_status AS p_status,is_purchase_order,is_recieved,
		(SELECT name_en FROM `tb_view` WHERE key_code = pending_status AND `type`=8) AS pedding_status,
		(SELECT name_en FROM `tb_view` WHERE key_code = is_approved AND `type`=7) AS is_approved,
		(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1) AS purchase_status,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND TYPE=5 LIMIT 1) AS `status`,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name,
		is_recieved
		FROM `tb_purchase_order` ";
			
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['purchase_status']>0){
			$where .= " AND purchase_status =".$search['purchase_status'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
 		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public function addAuditApproved($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$pending=2;
			$is_purchase_order=0;
			if($data['approved_name']==0){$pending=1;$is_purchase_order=0;}
			$arr=array(		
					'is_purchase_order'	=>	$is_purchase_order,		
					'is_approved'		=> 	$data['approved_name'],
					'approved_userid'	=> 	$GetUserId,
					'approved_note'		=> 	$data['app_remark'],
					'approved_date'		=> 	date("Y-m-d",strtotime($data['app_date'])),
					'pending_status'	=>	$pending,					
			);
			$this->_name="tb_purchase_order";
			$where = " id = ".$data["id"];
			$db->getProfiler()->setEnabled(true);
			$Purchase_id = $this->update($arr, $where);
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			$db->getProfiler()->setEnabled(false);
			
			unset($info_purchase_order);
// 			 $ids=explode(',',$data['identity_term']);
// 			 if(!empty($data['identity_term'])){
// 				 foreach ($ids as $i)
// 				 {
// 				 	$data_item= array(
// 				 			'quoation_id'=> $Purchase_id,
// 				 			'condition_id'=> $data['termid_'.$i],
// 				 			"user_id"   => 	$GetUserId,
// 				 			"date"      => 	date("Y-m-d"),
// 							'term_type'=>2
				 			
// 				 	);
// 				 	$this->_name='tb_quoatation_termcondition';
// 				 	$this->insert($data_item);
// 				 }
// 			 }
			 
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getProductPurchaseById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT 
			  p.`id`,
			  p.`order_number`,
			  (SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=p.`branch_id`) AS branch ,
			  p.`date_order`,
			  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=p.`user_mod`) AS prepare_by,
			  (SELECT v.`v_name` FROM `tb_vendor` AS v WHERE v.`vendor_id`=p.`vendor_id`) AS supplier,
			  (SELECT v.`v_phone` FROM `tb_vendor` AS v WHERE v.`vendor_id`=p.`vendor_id`) AS v_phone,
			  (SELECT v.`contact_name` FROM `tb_vendor` AS v WHERE v.`vendor_id`=p.`vendor_id`) AS contact_name,
			  (SELECT v.`email` FROM `tb_vendor` AS v WHERE v.`vendor_id`=p.`vendor_id`) AS v_email,
			  (SELECT v.`add_name` FROM `tb_vendor` AS v WHERE v.`vendor_id`=p.`vendor_id`) AS v_address,
			  (SELECT p.`item_name` FROM `tb_product` AS p WHERE p.`id`=po.`pro_id`) AS item_name,
			  (SELECT p.`item_code` FROM `tb_product` AS p WHERE p.`id`=po.`pro_id`) AS item_code,
			  (SELECT symbal FROM `tb_currency` WHERE id=p.currency_id LIMIT 1) AS curr_name,
			  po.`qty_order`,
			  po.`price`,
			  po.`disc_value`,
			  po.`sub_total`,
			  p.`all_total`,
			  p.`net_total`,
			  p.`balance`,
			  p.`branch_id`,
			  p.`discount_value`,
			  p.`paid`
			FROM
			  `tb_purchase_order` AS p,
			  `tb_purchase_order_item` AS po 
			WHERE p.`id` = po.`purchase_id` 
			  AND p.`id` = $id ";
		return $db->fetchAll($sql);
	} 
}