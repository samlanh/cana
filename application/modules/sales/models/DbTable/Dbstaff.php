<?php

class Sales_Model_DbTable_Dbstaff extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_staff"; 
	
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
	function getAllstaff($search){
			$db  = $this->getAdapter();
			$sql =" SELECT u.id ,
						  u.name ,
						  u.phone ,
						  u.email ,
						  u.position , 
						  u.date ,
						  u.status ,
						 (SELECT s.username FROM tb_acl_user AS s WHERE s.user_id = u. user_id) AS user_name
					FROM
						`tb_staff` AS u";
			
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$order);
	}
	public function getstaffById($id){
		$db  = $this->getAdapter();
			$sql =" SELECT u.id ,
						  u.name ,
						  u.phone ,
						  u.email ,
						  u.position , 
						  u.date ,
						  u.status ,
						 (SELECT s.username FROM tb_acl_user AS s WHERE s.user_id = u. user_id) AS user_name
					FROM
						`tb_staff` AS u
					where 
                        u.id ='".$id."'				";
			return $db->fetchrow($sql);
	}
	public function insertStaff($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$info=array(
					"name"    	  => $data['name'],
					"position"    => $data['position'],
					"phone"       => $data['phone'],
					"email"       => $data['email'],
					"status"       => $data['status'],
					"user_id"       => $GetUserId,
					"date"        => date("Y-m-d"),
			);
			$this->_name="tb_staff";
			$this->insert($info); 
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	} 
	
	public function updateStaff($data){
		$id = $data['id'];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$info=array(
					"name"    	  => $data['name'],
					"position"    => $data['position'],
					"phone"       => $data['phone'],
					"email"       => $data['email'],
					"status"       => $data['status'],
					"user_id"       => $GetUserId,
					"date"        => date("Y-m-d"),
			);
			$this->_name="tb_staff";
			$where="id = ".$id;
			$this->update($info, $where);
			$db->commit();
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
								'is_approved'	 =>0,
								'pending_status' =>1,
								'appr_status'	 =>	0,
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