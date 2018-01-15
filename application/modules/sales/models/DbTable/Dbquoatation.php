<?php

class Sales_Model_DbTable_Dbquoatation extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_quoatation"; 
	
	function getItemQty($item_id,$branch){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`qty_perunit`,
				  (SELECT qty FROM `tb_prolocation` WHERE location_id = $branch AND pro_id = $item_id LIMIT 1) AS qty ,
				   (SELECT price FROM `tb_prolocation` WHERE location_id = $branch AND pro_id = $item_id LIMIT 1) AS price ,
				  (SELECT m.name FROM tb_measure as m where m.id=p.measure_id) as measure
				FROM
				  tb_product as p
				WHERE p.id = $item_id 
				LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getAllQuoatation($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
						(SELECT NAME FROM `tb_sublocation` WHERE id=tb_quoatation.branch_id LIMIT 1) AS branch,
						(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_quoatation.customer_id LIMIT 1 ) AS customer_name,
						(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_quoatation.saleagent_id  LIMIT 1 ) AS staff_name,
						quoat_number,date_order,valid_date,
						(SELECT symbal FROM `tb_currency` WHERE id= currency_id LIMIT 1) AS curr_name,
						all_total,discount_value,net_total,appr_status AS appr_stat,pending_status AS pedding_stat,
						(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=is_approved LIMIT 1),
						(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=appr_status LIMIT 1) AS appr_status,
						(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=pending_status LIMIT 1) AS pedding,
						(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
					FROM `tb_quoatation`";
			$order=" ORDER BY id DESC";
			
			$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " quoat_number LIKE '%{$s_search}%'";
				$s_where[] = " all_total LIKE '%{$s_search}%'";
				$s_where[] = " discount_value LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND branch_id = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addQuoatationOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$qoatationid = $db_global->getQuoationNumber($data["branch_id"]);
			
			$info=array(
					"customer_id"    => $data['customer_id'],
					'saleagent_id'   =>	$data['saleagent_id'],
					"branch_id"      => $data["branch_id"],
					"quoat_number"   => $qoatationid,
					"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"   => $data['saleagent_id'],
					"currency_id"    => @$data['currency'],
					"remark"         => $data['remark'],
					"all_total"      => $data['totalAmoun'],
					"discount_value" => $data['dis_value'],
					"net_total"      => $data['all_total'],
					"user_mod"       => $GetUserId,
					"date"      	 => date("Y-m-d"),
					'is_approved'	 =>0,
					'pending_status' =>1,
					'appr_status'	 =>	0,
					"valid_date"     => date("Y-m-d",strtotime($data['valid_date'])),
					'date_time'		=> date("M d Y",strtotime($data['order_date']))." ".date('H:i', time()),
			);
			 $this->_name="tb_quoatation";
			$qoid = $this->insert($info); 
			unset($info);

			
				
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'quoat_id'	  		=> 	$qoid,
						'pro_id'	  		=> 	$data['item_id_'.$i],
						//'qty_unit'  =>$data['qty_unit_'.$i],
						'qty_order'	  		=> 	$data['qty'.$i],
						//'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'price'		  		=> 	$data['price'.$i],
						'old_price' 		=> 	$data['oldprice_'.$i],
						'disc_value'	  	=> $data['real-value'.$i],
						'benefit_plus'		=>	$data["dis_value".$i],
						'sub_total'	  		=> $data['total'.$i],
				);
				$this->_name='tb_quoatation_item';
				$this->insert($data_item);
			 }
			 /*$ids=explode(',',$data['identity_term']);
			 if(!empty($data['identity_term'])){
				 foreach ($ids as $i)
				 {
				 	$data_item= array(
				 			'quoation_id'=> $qoid,
				 			'condition_id'=> $data['termid_'.$i],
				 			"user_id"   => 	$GetUserId,
				 			"date"      => 	date("Y-m-d"),
							'term_type'=>1
				 			
				 	);
				 	$this->_name='tb_quoatation_termcondition';
				 	$this->insert($data_item);
				 }
			 }*/
			$db->commit();
			return $qoid;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	} 
	public function updateQoutation($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
// 			$qoatationid = $db_global->getQuoationNumber($data["branch_id"]);
						$qdata=array(
								"customer_id"    => $data['customer_id'],
								'saleagent_id'   =>	$data['saleagent_id'],
								"branch_id"      => $data["branch_id"],
								//"quoat_number"   => $qoatationid,
								"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
								"saleagent_id"   => $data['saleagent_id'],
								"currency_id"    => @$data['currency'],
								"remark"         => $data['remark'],
								"all_total"      => $data['totalAmoun'],
								"discount_value" => $data['dis_value'],
								"net_total"      => $data['all_total'],
								"user_mod"       => $GetUserId,
								"date"      	 => date("Y-m-d"),
								"valid_date"     => date("Y-m-d",strtotime($data['valid_date'])),
								'is_approved'	 =>0,
								'pending_status' =>1,
								'appr_status'	 =>	0,
								'date_time'		=> date("M d Y",strtotime($data['order_date']))." ".date('H:i', time()),
						);
					
					$this->_name="tb_quoatation";
					$where="id = ".$id;
					$this->update($qdata, $where);
// 					unset($info_purchase_order);
			        
					//delete detail
					$this->_name='tb_quoatation_item';
					$where = " quoat_id =".$id;
					$this->delete($where);
					
					$ids=explode(',',$data['identity']);
					$locationid=$data['branch_id'];
					foreach ($ids as $i)
					{
						$data_item= array(
								'quoat_id'	  		=> 	$id,
								'pro_id'	  		=> 	$data['item_id_'.$i],
								//'qty_unit'  =>$data['qty_unit_'.$i],
								'qty_order'	  		=> 	$data['qty'.$i],
								//'qty_detail'  => 	$data['qty_per_unit_'.$i],
								'price'		  		=> 	$data['price'.$i],
								'old_price' 		=> 	$data['oldprice_'.$i],
								'disc_value'	  	=> $data['real-value'.$i],
								'benefit_plus'		=>	$data["dis_value".$i],
								'sub_total'	  		=> $data['total'.$i],
						);
						$this->_name='tb_quoatation_item';
						$this->insert($data_item);
					}	
		
					/*$this->_name='tb_quoatation_termcondition';
					$where = " term_type=1 AND quoation_id = ".$id;		
					$this->delete($where);	
					
					/$ids=explode(',',$data['identity_term']);
					if(!empty($data['identity_term'])){
						foreach ($ids as $i)
						{
							$data_item= array(
									'quoation_id'	=> $id,
									'condition_id'	=> $data['termid_'.$i],
									"user_id"   	=> 	$GetUserId,
									"date"      	=> 	date("Y-m-d"),
									'term_type'		=>1
							);
							$this->insert($data_item);
						}
					}*/
		    
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function reNewQuoatation($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$qoatationid = $db_global->getQuoationNumber($data["branch_id"]);
			
			$info=array(
					"customer_id"    => $data['customer_id'],
					'saleagent_id'   =>	$data['saleagent_id'],
					"branch_id"      => $data["branch_id"],
					"quoat_number"   => $qoatationid,
					"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"   => $data['saleagent_id'],
					"currency_id"    => @$data['currency'],
					"remark"         => $data['remark'],
					"all_total"      => $data['totalAmoun'],
					"discount_value" => $data['dis_value'],
					"net_total"      => $data['all_total'],
					"user_mod"       => $GetUserId,
					"date"      	 => date("Y-m-d"),
					'is_approved'	 =>0,
					'pending_status' =>1,
					'appr_status'	 =>	0,
					"valid_date"     => date("Y-m-d",strtotime($data['valid_date'])),
					'date_time'		=> date("M d Y",strtotime($data['order_date']))." ".date('H:i', time()),
			);
			 $this->_name="tb_quoatation";
			$qoid = $this->insert($info); 
			unset($info);

			
				
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'quoat_id'	  		=> 	$qoid,
						'pro_id'	  		=> 	$data['item_id_'.$i],
						//'qty_unit'  =>$data['qty_unit_'.$i],
						'qty_order'	  		=> 	$data['qty'.$i],
						//'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'price'		  		=> 	$data['price'.$i],
						'old_price' 		=> 	$data['oldprice_'.$i],
						'disc_value'	  	=> $data['real-value'.$i],
						'benefit_plus'		=>	$data["dis_value".$i],
						'sub_total'	  		=> $data['total'.$i],
				);
				$this->_name='tb_quoatation_item';
				$this->insert($data_item);
			 }
			 /*$ids=explode(',',$data['identity_term']);
			 if(!empty($data['identity_term'])){
				 foreach ($ids as $i)
				 {
				 	$data_item= array(
				 			'quoation_id'=> $qoid,
				 			'condition_id'=> $data['termid_'.$i],
				 			"user_id"   => 	$GetUserId,
				 			"date"      => 	date("Y-m-d"),
							'term_type'=>1
				 			
				 	);
				 	$this->_name='tb_quoatation_termcondition';
				 	$this->insert($data_item);
				 }
			 }*/
			$db->commit();
			return $qoid;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	} 
	function getQuotationItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
				  q.*,
				  (SELECT s.remark FROM `tb_sale_request_remark` AS s WHERE s.re_id=q.id AND s.type=1) AS reject_check,
				  (SELECT s.remark FROM `tb_sale_request_remark` AS s WHERE s.re_id=q.id AND s.type=2) AS reject_approve 
				FROM
				  tb_quoatation AS q 
				WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getQuotationItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
				  q.* ,
				  (SELECT p.qty FROM `tb_prolocation` AS p WHERE p.pro_id=q.`pro_id` AND p.`location_id`=(SELECT `branch_id` FROM `tb_quoatation` WHERE `id`=q.`quoat_id`)) AS cu_qty,
				  (SELECT m.name FROM tb_measure AS m WHERE m.id=(SELECT p.measure_id FROM tb_product AS p WHERE p.id=q.pro_id ) LIMIT 1) AS measure
				FROM
				  `tb_quoatation_item` AS q
				WHERE q.quoat_id =$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=1 ";
		return $db->fetchAll($sql);
	}
	
}