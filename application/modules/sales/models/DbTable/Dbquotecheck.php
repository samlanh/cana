<?php

class Sales_Model_DbTable_Dbquotecheck extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_quoatation";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
						(SELECT NAME FROM `tb_sublocation` WHERE id=tb_quoatation.branch_id LIMIT 1) AS branch,
						(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_quoatation.customer_id LIMIT 1 ) AS customer_name,
						(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_quoatation.saleagent_id  LIMIT 1 ) AS staff_name,
						quoat_number,date_order,
						(SELECT symbal FROM `tb_currency` WHERE id= currency_id LIMIT 1) AS curr_name,
						all_total,discount_value,net_total,appr_status AS appr_stat,pending_status AS pedding_stat,
						(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=is_approved LIMIT 1),
						(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=appr_status LIMIT 1) AS appr_status,
						(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=pending_status LIMIT 1) AS pedding,
						(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
					FROM `tb_quoatation`";
			
			$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " quoat_number LIKE '%{$s_search}%'";
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
	public function addQuoateOrderCheck($data)
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
			$app_status = 0;
			if($data['approved_name']==2){$pending=1;$app_status =2;}
			$arr=array(		
// 					'is_toinvocie'=>1,		
					'is_approved'	=> $data['approved_name'],
					'appr_status'	=>	$app_status,
					'approved_userid'=> $GetUserId,
					'approval_note'	=> $data['remark'],
					'approved_date'	=> date("Y-m-d"),
					'pending_status'=>$pending,					
			);
			$this->_name="tb_quoatation";
			$where = " id = ".$data["id"];
			$sale_id = $this->update($arr, $where);			
			
			$rs_reject = $db_global->getSaleRejectExist($data["id"],1);
				if(!empty($rs_reject)){
					$arr = array(
						'user_id'	=>	$GetUserId,
						'date'		=>  date('Y-m-d'),
						'remark'	=>  $data["remark"],
					);
					$this->_name = "tb_sale_request_remark";
					$where = "re_id=".$data["id"]." AND type=1";
					$this->update($arr,$where);
				}else{
					$arr = array(
						're_id'		=>	$data["id"],
						'user_id'	=>	$GetUserId,
						'date'		=>  date('Y-m-d'),
						'remark'	=>  $data["remark"],
						'type'		=>  1,
					);
					$this->_name = "tb_sale_request_remark";
					$this->insert($arr);
				}
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err ;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_name,
		(SELECT sm.remark FROM `tb_sale_request_remark` AS sm WHERE sm.re_id=s.`id` AND sm.type=1) AS reject_check,
		(SELECT sm.remark FROM `tb_sale_request_remark` AS sm WHERE sm.re_id=s.`id` AND sm.type=2) AS reject_approve,
		s.is_approved,s.quoat_number,s.discount_real,s.all_total,s.date_order,s.remark,s.approval_note,s.approved_date,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT m.name FROM tb_measure AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id= so.pro_id LIMIT 1) limit 1) as measure,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) As model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approved_by,
		(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=is_approved LIMIT 1) approval_status,
		(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=pending_status LIMIT 1) processing,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,s.discount_value,so.disc_value AS disc_detailvalue,so.benefit_plus
		
		FROM `tb_quoatation` AS s,
		`tb_quoatation_item` AS so WHERE s.id=so.quoat_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	} 
	
	public function convertQouteToSO($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
					$this->_name="tb_quoatation";
					$arr = array(
					'is_tosale'=>1);
					$where ="id = ".$id;
					$this->update($arr,$where);
				
					$so = $db_global->getSalesNumber($data["branch_id"]);
					$qdata=array(
							'quote_id'=>$id,
							"customer_id"    => $data['customer_id'],
							"branch_id"      => $data["branch_id"],
							"sale_no"       => 	$so,//$data['txt_order'],
							"date_sold"     => date("Y-m-d",strtotime($data['order_date'])),
							"saleagent_id"  => 	$data['saleagent_id'],
							"currency_id"    => $data['currency'],
							"remark"         => $data['remark'],
							"all_total"      => $data['totalAmoun'],
							"discount_value" => $data['dis_value'],
							"net_total"      => $data['all_total'],
							"user_mod"       => $GetUserId,
							"date"      	 => date("Y-m-d"),
// 							'is_approved'=>0,
							'pending_status' =>2,
							"date"      => 	date("Y-m-d"),
					);
					$this->_name="tb_sales_order";
					$sale_id = $this->insert($qdata);
					
					$this->_name='tb_salesorder_item';
					$ids=explode(',',$data['identity']);
					foreach ($ids as $i)
					{
						$data_item= array(
								'saleorder_id'=> $sale_id,
								'pro_id'	  => 	$data['item_id_'.$i],
								'qty_order'	  => 	$data['qty'.$i],
								'qty_detail'  => 	$data['qty_per_unit_'.$i],
								'price'		  => 	$data['price'.$i],
								'old_price'   =>    $data['oldprice_'.$i],
								'disc_value'  => $data['dis_value'.$i],
								'sub_total'	  => $data['total'.$i],
						);
						
						$this->insert($data_item);
					}
					///add term condtion of so
					$ids=explode(',',$data['identity_term']);
					if(!empty($data['identity_term'])){
						foreach ($ids as $i)
						{
							$data_item= array(
									'quoation_id'=> $sale_id,
									'condition_id'=> $data['termid_'.$i],
									"user_id"   => 	$GetUserId,
									"date"      => 	date("Y-m-d"),
									'term_type'=>2
							);
							$this->_name='tb_quoatation_termcondition';
							$this->insert($data_item);
						}
					}
				
		    
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	function getProductExist($id,$br_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.id,pl.`pro_id`,pl.`qty` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=$id AND pl.`location_id`=$br_id";
		return $db->fetchRow($sql);
	}
}