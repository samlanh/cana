<?php

class Sales_Model_DbTable_DbSaleOrder extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_sales_order";
	
	
	function getProductPrice($id,$branch_id,$customer_id){
		$db= $this->getAdapter();
			$sql="SELECT 
					  p.`item_name`,
					  p.`item_code`,
					  p.id,
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id`) AS measure,
					  (SELECT pl.qty FROM `tb_prolocation` AS pl WHERE pl.pro_id=p.id AND pl.location_id=$branch_id) AS qty,
					  qi.`benefit_plus`,
					  qi.`price`
					FROM
					  `tb_product` AS p ,
					  `tb_quoatation_item` AS qi,
					  `tb_quoatation` AS q
					WHERE p.id=qi.`pro_id` 
						AND q.id=qi.`quoat_id` 
						AND q.`pending_status`=3 
						AND p.id=$id 
						AND q.`branch_id`=$branch_id 
						 AND q.`customer_id`=$customer_id 
					ORDER BY STR_TO_DATE(q.`date_time`, '%M %e %Y %l:%i') DESC LIMIT 1";
			
		return $db->fetchRow($sql);
	}
	
	function getProductOption($opt=null,$customer,$branch_id){
		
		$db = $this->getAdapter();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql_cate = 'SELECT `id`,name FROM tb_category WHERE status = 1 AND name!="" ORDER BY name ';
		
		$row_cate = $db->fetchAll($sql_cate);
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$option="";		
		if($result["level"]==1 OR $result["level"]==2){
			$option .= '<option value="-1">'.$tr->translate("SELECT_PRODUCT").'</option>';
		}
		foreach($row_cate as $cate){
			$option .= '<optgroup  label="'.htmlspecialchars($cate['name'], ENT_QUOTES).'">';
				$sql = "SELECT 
					  p.id,
					  p.`item_name` ,
					  P.item_code,
					  qi.`benefit_plus`,
					  q.`date_order`
					FROM
					  `tb_product` AS p,
					  `tb_quoatation` AS q,
					  `tb_quoatation_item` AS qi 
					WHERE p.`id` = qi.`pro_id` 
					  AND q.id = qi.`quoat_id` 
					  AND q.`pending_status`=3 
					  AND q.`customer_id`=$customer 
					  AND q.`branch_id`=$branch_id 
					  AND q.`valid_date`>=DATE(NOW())
					  AND p.`cate_id`=".$cate['id']."
					GROUP BY p.id ORDER BY MAX(q.`date_order`) DESC ";
				$rows = $db->fetchAll($sql);
				if($rows){
					foreach($rows as $value){
						$option .= '<option value="'.$value['id'].'" label="'.htmlspecialchars($value['item_name'], ENT_QUOTES).'">'.
							htmlspecialchars($value['item_name'], ENT_QUOTES)." ".htmlspecialchars($value['item_code'], ENT_QUOTES)
						.'</option>';
					}
				}
			$option.="</optgroup>";
		}
		
		return $option;
    	
	}
	function getSaleItem($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  si.* ,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=si.`pro_id` LIMIT 1) ) AS measure 
				FROM
				  `tb_salesorder_item` AS si,
				  `tb_sales_order` AS s 
				WHERE s.`id` = si.`saleorder_id` 
				  AND s.`id` =$id";
		return $db->fetchAll($sql);
	}
	function getProductForSale($customer,$branch_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					  p.id,
					  p.`item_name` ,
					  P.item_code,
					  qi.`benefit_plus`,
					  q.`date_order`
					FROM
					  `tb_product` AS p,
					  `tb_quoatation` AS q,
					  `tb_quoatation_item` AS qi 
					WHERE p.`id` = qi.`pro_id` 
					  AND q.id = qi.`quoat_id` 
					  AND q.`pending_status`=3 
					  AND q.`customer_id`=$customer 
					  AND q.`branch_id`=$branch_id 
					  AND q.`valid_date`>=DATE(NOW())
					GROUP BY p.id ORDER BY MAX(q.`date_order`) DESC ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
			(SELECT q.`quoat_number` FROM `tb_quoatation` AS q WHERE q.id=`quote_id`) AS quote_no,
			(SELECT q.`date_order` FROM `tb_quoatation` AS q WHERE q.id=`quote_id`) AS quote_date,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS customer_name,
			(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_sales_order.saleagent_id  LIMIT 1 ) AS staff_name,
			(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=appr_status LIMIT 1) AS appr_status,
						(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) AS pedding,
			sale_no,date_sold,pending_status AS pedding_stat,
			net_total,discount_real,all_total,
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
			FROM `tb_sales_order` WHERE type=1 ";
			
			$from_date =(empty($search['start_date']))? '1': " date_sold >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_sold <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " sale_no LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$s_where[] = " paid LIKE '%{$s_search}%'";
				$s_where[] = " balance LIKE '%{$s_search}%'";
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
	public function addSaleOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$so = $dbc->getSalesNumber($data["branch_id"]);

			$info_purchase_order=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					//"payment_method" => $data['payment_name'],
					"currency_id"    => $data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_real"  => 	$data['global_disc'],
					"net_total"      => 	$data['all_total'],
					//"paid"         => 	$data['paid'],
					//"balance"      => 	$data['remain'],
					//"tax"			 =>     $data["total_tax"],
					"user_mod"       => 	$GetUserId,
					'pending_status' =>2,
					"date"      => 	date("Y-m-d"),
					'type'		=> 	1
			);
			$this->_name="tb_sales_order";
			$sale_id = $this->insert($info_purchase_order); 
			unset($info_purchase_order);

			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'	=> 	$sale_id,
						'pro_id'	  	=> 	$data['item_id_'.$i],
						//'qty_unit'		=>	$data['qty_unit_'.$i],
						//'qty_detail'  	=> 	$data['qty_per_unit_'.$i],
						'qty_order'	  	=> 	$data['qty'.$i],
						'price'		  	=> 	$data['price'.$i],
						'old_price'   	=>  $data['current_qty'.$i],
						'disc_value'  	=> 	$data['real-value'.$i],//check it
						'sub_total'	  	=> 	$data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
				
				/*$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						//if($data["status"]==4 OR $data["status"]==5){
							$datatostock   = array(
									'qty'   			=> 		$rows["qty"]-$data['qty'.$i],
									'last_mod_date'		=>		date("Y-m-d"),
									'last_mod_userid'	=>		$GetUserId
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						//}
					}*/
				
			 }
			 
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
			//exit();
			$db->commit();
			return $sale_id;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function editSO($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
					$so = $db_global->getSalesNumber($data["branch_id"]);
					$qdata=array(
							//'quote_id'=>$id,
							"customer_id"    	=> $data['customer_id'],
							"branch_id"      	=> $data["branch_id"],
							"sale_no"       	=> 	$so,//$data['txt_order'],
							"date_sold"     	=> date("Y-m-d",strtotime($data['order_date'])),
							"saleagent_id"  	=> 	$data['saleagent_id'],
							"currency_id"    	=> @$data['currency'],
							"remark"         	=> $data['remark'],
							"all_total"      	=> $data['totalAmoun'],
							"discount_value" 	=> $data['dis_value'],
							"net_total"      	=> $data['all_total'],
							"user_mod"       	=> $GetUserId,
							"date"      	 	=> date("Y-m-d"),
// 							'is_approved'=>0,
							'pending_status' 	=>	4,
							'appr_status'		=>	0,
							'type'			 	=>	1,
					);
					$this->_name="tb_sales_order";
					$where = "id=".$data["id"];
					$this->update($qdata,$where);
					
					$dbg = new Application_Model_DbTable_DbGlobal();
					$rowexisting = $this->getInvoieExisting($data['id']);
					if(!empty($rowexisting)){// for update
						$invoice_number = $rowexisting['invoice_no'];
					}else{//for add
						$invoice_number = $dbg->getInvoiceNumber($data['branch_id']);
					}
					
					$this->_name="tb_invoice"; /// if invoice existing update
						$arr = array(
								//'approved_note'		=>	$data['app_remark'],
								'sale_id'			=>	$data['id'],
								'branch_id'			=>	$data['branch_id'],
								'invoice_no'		=>	$invoice_number,
								'invoice_date'		=>	date("Y-m-d"),
								'approved_date'		=>	date("Y-m-d"),
								'user_id'			=>	$GetUserId,
								'sub_total'			=>	$data['totalAmoun'],//$data['net_total'],
								//'discount'			=>	$data['discount'],
								//'paid_amount'		=>	$data['deposit'],
								//'balance'			=>	$data['balance'],
								
								//'sub_total_after'	=>	$data['all_total'],//$data['net_total'],
								//'discount_after'	=>	$data['discount'],
								//'paid_after'		=>	$data['deposit'],
								//'balance_after'		=>	$data['balance'],
								
								'is_approved'		=>	1,
								);	
					if(!empty($rowexisting)){// for update
						$where = "sale_id = ".$data['id'];
						$this->update($arr,$where);
						$invoice_id=$rowexisting["id"];
					}else{//for add
						$invoice_id = $this->insert($arr);	
					}
					
					$sql = "DELETE FROM tb_salesorder_item WHERE saleorder_id="."'".$data["id"]."'";
					$db->query($sql);
					
					$this->_name='tb_salesorder_item';
					$ids=explode(',',$data['identity']);
					foreach ($ids as $i)
					{
						$data_item= array(
								'saleorder_id'	=> $data["id"],
								'pro_id'	  	=> 	$data['item_id_'.$i],
								'qty_order'	  	=> 	$data['qty'.$i],
								//'qty_detail'  => 	$data['qty_per_unit_'.$i],
								'price'		  	=> 	$data['price'.$i],
								'old_price'   	=>    $data['oldprice_'.$i],
								//'disc_value'  => $data['dis_value'.$i],
								'benefit_plus'	=>	$data['dis_value'.$i],
								'sub_total'	  	=> $data['total'.$i],
						);
						
						$this->insert($data_item);
					}
					
					$arr=array(		
						'is_tosale'			=>	1,		
						'is_approved'		=> 	1,
						'approved_userid'	=> 	$GetUserId,
						'approved_date'		=> 	date("Y-m-d"),
						'pending_status'	=>	4,
						'appr_status'		=>	0,
					);
					$this->_name="tb_quoatation";
					$where = " id = ".$data["id"];
					$sale_id = $this->update($arr, $where);
					///add term condtion of so
					
		    
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	function getInvoieExisting($saleid){
		$db = $this->getAdapter();
		$sql="SELECT id,invoice_no FROM `tb_invoice` WHERE sale_id=$saleid limit 1 ";
		return $db->fetchRow($sql);
	}
	public function updateSaleOrder($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
// 			$so = $dbc->getSalesNumber($data["branch_id"]);
			$arr=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
// 					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					"currency_id"    => $data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
// 					"discount_real"  => 	$data['global_disc'],
					"net_total"      => 	$data['all_total'],
					"user_mod"       => 	$GetUserId,
 					'pending_status' =>1,
					'is_approved'=>0,
					'is_toinvocie'=>0,
					"date"      => 	date("Y-m-d"),
			);

			$this->_name="tb_sales_order";
			$where="id = ".$id;
			$this->update($arr, $where);
			unset($arr);
			
			$this->_name='tb_salesorder_item';
			$where = " saleorder_id =".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $id,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'=>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i],
						'old_price'   =>    $data['current_qty'.$i],
						'disc_value'  => $data['real-value'.$i],//check it
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
// 				print_r($data_item);exit();
			}
			$this->_name='tb_quoatation_termcondition';
			$where = " term_type=2 AND quoation_id = ".$id;
			$this->delete($where);
			
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
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"			=>	$pro_id,
    				"location_id"		=>	$location_id,
    				"qty"				=>	0,
    				"qty_warning"		=>	0,
    				"last_mod_userid"	=>	$GetUserId,
    				"user_id"			=>	$GetUserId,
    				"last_mod_date"		=>	date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		
    	return $row; 
    	}  	
    }
	
	function getSaleorderItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
}