<?php

class Sales_Model_DbTable_DbRequest extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_sales_order";
	
	function getPlan(){
		$db= $this->getAdapter();
			$sql="SELECT p.`id`,p.`name` FROM `tb_plan` AS p WHERE p.`status`=1";
			return $db->fetchAll($sql);
	}
	
	function getWorkPlan(){
		$db= $this->getAdapter();
			$sql="SELECT p.`id`,p.`name` FROM `tb_work_plan` AS p WHERE p.`status`=1";
			return $db->fetchAll($sql);
	}
	function getPlanAddr($id){
		$db= $this->getAdapter();
		$sql="SELECT p.`address` FROM `tb_plan` AS p WHERE p.id=$id";
		return $db->fetchOne($sql);
	}
	
	function getRequestCode($id){
		$db= $this->getAdapter();
		$sql = "SELECT s.`prefix` FROM `tb_sublocation` AS s WHERE s.id=$id";
		$prefix = $db->fetchOne($sql);
		
		$sql="SELECT s.id FROM `tb_sales_order` AS s WHERE s.`type`=2 ORDER BY s.`id` DESC LIMIT 1";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = $prefix."DIBP";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function getAllRequestOrder($search){
			$db= $this->getAdapter();
			$sql="SELECT 
					  s.id,
					  (SELECT sl.`name` FROM `tb_sublocation` AS sl WHERE sl.`id`=s.`branch_id` LIMIT 1) AS location,
					  s.`sale_no`,
					  (SELECT sf.name FROM `tb_staff` AS sf WHERE sf.id=s.`user_request_id` LIMIT 1) AS request_name,
					  s.`position`,
					  (SELECT `name` FROM `tb_plan` AS p WHERE p.id=s.`plan_id` LIMIT 1) AS plan,
					  s.`date_sold`,
					  s.`all_total` ,
					  s.pending_status,
					  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=s.`user_mod` LIMIT 1) AS `user`,
					  (SELECT v.name_en FROM `tb_view` AS v WHERE v.type=5 AND v.key_code=s.`status` LIMIT 1) AS `status`,
					  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=s.`pending_status` AND v.type=8 LIMIT 1) AS pedding,
					  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=s.`appr_status` AND v.type=7 LIMIT 1) AS appr_status
					FROM
					  `tb_sales_order` AS s 
					WHERE s.`type` = 2 ";
			
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
	function getRequestById($id){
		$db = $this->getAdapter();
		$sql = "SELECT
		s.id,s.branch_id,
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		
		(SELECT wp.name FROM `tb_work_plan` AS wp WHERE wp.id=s.`work_plan`) AS work_plan,
		s.sale_no,s.date_sold,s.remark as remarks,s.approved_note,s.approved_date,s.`position`,
		(SELECT r.name FROM tb_staff AS r WHERE r.id=s.user_request_id LIMIT 1 ) AS `request_name`,
		(SELECT p.name FROM `tb_plan` AS p WHERE p.id=s.`plan_id`) AS plant,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approved_by,
		(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=is_approved LIMIT 1) approval_status,
		(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=pending_status LIMIT 1) processing,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,
		s.paid,s.discount_real,s.tax,s.re_type,
		(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=(SELECT p.`measure_id` FROM `tb_product` AS p WHERE p.id=so.`pro_id`)) AS measure,
		s.balance,
		so.`remark`,
		s.approved_note,
		s.`number_work_request`,
		s.`date_work_request`,
		(SELECT w.name FROM tb_work_type as w where w.id=so.work_type) AS work_type,
		so.note
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so WHERE s.id=so.saleorder_id
		AND s.status=1 AND s.id =$id";
		
		return $db->fetchAll($sql);
	}
	public function addRequestOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$db_po = new Purchase_Model_DbTable_DbRequest();
			$so = $dbc->getSalesNumber($data["branch_id"]);
			$is_set = 0;
			$info_purchase_order=array(
					"plan_id"  		 	=> 		$data['plan'],
					"user_request_id"	=>		$data["request_by"],
					"position"			=>		$data["requstman_pos"],
					"branch_id"     	=> 		$data["branch_id"],
					"sale_no"       	=> 		$data["apno"],//$data['txt_order'],
					"date_sold"     	=> 		date("Y-m-d",strtotime($data['order_date'])),
					//"saleagent_id"  	=> 		$data['saleagent_id'],
					'number_work_request'	=>	$data["request_no"],
					'date_work_request'		=>	date("Y-m-d",strtotime($data["request_date"])),
					//"payment_method" => 	$data['payment_name'],
					//"currency_id"    => 	$data['currency'],
					"remark"         	=> 		$data['remark'],
					"all_total"      	=> 		$data['totalAmoun'],
					'all_total_after'	=>		$data["totalAmoun"],
					"discount_value" 	=> 		$data['dis_value'],
					"discount_real" 	 => 	$data['global_disc'],
					"net_total"     	 => 	$data['all_total'],
					'net_total_after'	=>		$data["all_total"],
					"paid"        	 	=> 		0,
					"paid_after"        => 		0,
					"balance"      	 	=> 	$data['all_total'],
					"balance_after"     => 	$data['all_total'],
					//"tax"			 =>     $data["total_tax"],
					"user_mod"       	=> 		$GetUserId,
					'pending_status' 	=>		1,
					'appr_status' 		=>		0,
					"date"      	 	=> 		date("Y-m-d"),
					"type"				=>		2,
					//"re_type"			=>		$data["re_type"],
					"work_plan"			=>		$data["work_plan"],
					
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
						'qty_order_after'	  	=> 	$data['qty'.$i],
						'price'		  	=> 	$data['price'.$i],
						'old_price'   	=>  $data['current_qty'.$i],
						//'disc_value'  	=> 	$data['real-value'.$i],//check it
						'sub_total'	  	=> 	$data['total'.$i],
						'work_type'	  	=> 	$data['work_type_'.$i],
						'remark'	  	=> 	$data['remark_'.$i],
						'note'	  		=> 	$data['note_'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
				
				if($data['qty_order_'.$i]!="" OR $data['qty_order_'.$i]!=0){
					if($is_set!=1){
						$array = array(
							're_code'				=>	$db_po->getRequestCode($data["branch_id"]),
							'branch_id'				=>	$data["branch_id"],
							'plan_id'				=>	$data['plan'],
							'number_request'		=>	$data['request_no'],
							'date_request'			=>	date("Y-m-d",strtotime($data['order_date'])),
							'date_from_work_space'	=>	date("Y-m-d",strtotime($data['request_date'])),
							'appr_status'			=>	0,
							'pedding'				=>	1,
							'status'				=>	1,
							're_edit'				=>	0,
							'user_id'				=>	$GetUserId,
						);
						$this->_name = "tb_purchase_request";
						$po_id = $this->insert($array);
						$is_set=1;
					}
					
					$arr_poi=array(
						'pur_id'	=>	$po_id,
						'pro_id'	=>	$data['item_id_'.$i],
						'cur_qty'	=>	$data['current_qty'.$i],
						'qty'		=>	$data['qty_order_'.$i],
						'date_in'	=>	$data['re_date_in_'.$i],
						'remark'	=>	$data['re_note_'.$i],
					);
					$this->_name = "tb_purchase_request_detail";
					$this->insert($arr_poi);
				}
			 }
			$db->commit();
			return $sale_id;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updateRequestOrder($data)
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
					"plan_id"  		 	=> 	$data['plan'],
					"user_request_id"		=>		$data["request_by"],
					"position"			=>		$data["requstman_pos"],
					"branch_id"     	=> 		$data["branch_id"],
					"sale_no"       	=> 		$data["apno"],//$data['txt_order'],
					"date_sold"     	=> 		date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  	=> 		$data['saleagent_id'],
					'number_work_request'	=>	$data["request_no"],
					'date_work_request'		=>	date("Y-m-d",strtotime($data["request_date"])),
					//"payment_method" => 	$data['payment_name'],
					//"currency_id"    => 	$data['currency'],
					"remark"         	=> 	$data['remark'],
					"all_total"      	=> 		$data['totalAmoun'],
					'all_total_after'	=>		$data["totalAmoun"],
					"discount_value" 	=> 		$data['dis_value'],
					"discount_real" 	 => 	$data['global_disc'],
					"net_total"     	 => 	$data['all_total'],
					'net_total_after'	=>		$data["all_total"],
					"paid"        	 	=> 		0,
					"paid_after"        => 		0,
					"balance"      	 	=> 	$data['all_total'],
					"balance_after"     => 	$data['all_total'],
					//"tax"			 	=>     $data["total_tax"],
					"user_mod"       	=> 	$GetUserId,
					'pending_status' 	=>		1,
					'appr_status' 		=>		0,
					"date"      	 	=> 	date("Y-m-d"),
					"type"				=>		2,
					//"re_type"			=>		$data["re_type"],
					"work_plan"			=>		$data["work_plan"],
			);

			$this->_name="tb_sales_order";
			$where="id = ".$id;
			$this->update($arr, $where);
			unset($arr);
			
			/*$row_old_item = $this->getSaleorderItemDetailid($data["id"]);
			if(!empty($row_old_item)){
				foreach($row_old_item AS $rs){
					$sql = "SELECT pl.id,pl.`qty` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=".$rs["pro_id"]. " AND pl.`location_id` =".$data["old_location"];
					$row = $db->fetchRow($sql);
					
						$arr_old = array(
							'qty'	=> $row["qty"]+$rs["qty_order"],
						);
						$where = $db->quoteInto("id=?",$row["id"]);
						$this->_name = "tb_prolocation";
						$this->update($arr_old,$where);
				}
				
				$this->_name='tb_salesorder_item';
				$where = " saleorder_id =".$id;
				$this->delete($where);
			}*/
			
			$sql="DELETE FROM tb_salesorder_item WHERE saleorder_id="."'".$id."'";
			$db->query($sql);
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'	=> 	$id,
						'pro_id'	  	=> 	$data['item_id_'.$i],
						//'qty_unit'		=>	$data['qty_unit_'.$i],
						//'qty_detail'  	=> 	$data['qty_per_unit_'.$i],
						'qty_order'	  	=> 	$data['qty'.$i],
						'price'		  	=> 	$data['price'.$i],
						'old_price'   	=>  $data['current_qty'.$i],
						//'disc_value'  	=> 	$data['real-value'.$i],//check it
						'sub_total'	  	=> 	$data['total'.$i],
						'work_type'	  	=> 	$data['work_type_'.$i],
						'remark'	  	=> 	$data['remark_'.$i],
						'note'	  		=> 	$data['note_'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
				
				/*$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						//if($data["status"]==4 OR $data["status"]==5){
							//echo$rows["qty"];
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
// 				print_r($data_item);exit();
			}
			/*$this->_name='tb_quoatation_termcondition';
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
			}*/
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			//echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	function checkRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			if($data["apprrove"]==1){
			$appr_status = 0;
			$pending_status=4;
		}else{
			$appr_status = 2;
			$pending_status=1;
		}
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
			$arr=array(
					"approved_userid"   => 	$GetUserId,
					'approved_note'		=>	$data["remark"],
					'pending_status' 	=>	$pending_status,
					'appr_status' 		=>	$appr_status,
					"approved_date"     => 	date("Y-m-d"),
			);
			$this->_name="tb_sales_order";
			$where="id = ".$data["id"];
			$this->update($arr, $where);
			unset($arr);
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getItemOrder($id){
		$db = $this->getAdapter();
		$sql = "SELECT so.`qty_order`,so.price,so.`pro_id`,s.`branch_id` FROM `tb_sales_order` AS s,`tb_salesorder_item` AS so WHERE s.id=so.`saleorder_id` AND so.`saleorder_id`=$id";
		return $db->fetchAll($sql);
	}
	
	function getProductExist($id,$br_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.id,pl.`pro_id`,pl.`qty` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=$id AND pl.`location_id`=$br_id";
		return $db->fetchRow($sql);
	}
	
	function getProductForCost($pro_id,$location){
		$db = $this->getAdapter();
		$sql="SELECT p.id,pl.`price`,pl.`qty` FROM `tb_product` AS p ,`tb_prolocation` AS pl WHERE p.id=pl.`pro_id` AND pl.`location_id`=$location AND p.id=$pro_id";
		return $db->fetchRow($sql);
	}
	public function addDelivery($data)	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			
			$row = $this->getItemOrder($data["id"]);
			$dn = $db_global->getDeliverNumber($row[0]["branch_id"]);
			//echo $row[0]["branch_id"];
			if(!empty($row)){
					foreach ($row as $rs){
						$rs_pro = $this->getProductExist($rs["pro_id"],$rs["branch_id"]);
						if(!empty($rs_pro)){
							$arr_pro = array(
								'qty'		=>	$rs_pro["qty"]-$rs["qty_order"],
							);
							$this->_name = "tb_prolocation";
							$where=" id = ".$rs_pro['id'];
							$this->update($arr_pro,$where);
						}
					}
				
				$row_product = $this->getProductForCost($rs["pro_id"],$rs["branch_id"]);
					if(!empty($row_product)){
						//print_r($row_product);
						$amount_pro = $row_product["price"] * $row_product["qty"];
						$new_amount_pro = $rs_pro["qty"] * $rs_pro["price"];
						$total_qty = $row_product["qty"]+$data['qty_recieve_'.$i];
						$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_prolocation";
						$where=" pro_id = ".$row_product['id']." AND location_id=".$rs["branch_id"];
						$this->update($arr_pro, $where);
						
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_product";
						$where=" id = ".$row_product['id'];
						$this->update($arr_pro, $where);
					}
				
					
				$this->_name="tb_sales_order";
				$data_to = array(
							'pending_status'	=>	5,
							'appr_status'		=>	1,
							'is_deliver'		=>	1
							);
				$where=" id = ".$data['id'];
				$this->update($data_to, $where);
				
				
				$arr = array(
					'branch_id'				=>	$row[0]["branch_id"],
					'deliver_no'			=>	$dn,
					'so_id'					=>$data["id"],
					//'delivery_userid'		=>$data['app_remark'],
					'deli_date'				=>date("Y-m-d"),
					'user_id'				=>$GetUserId,
					'type'					=>2,
					);
				$this->_name="tb_deliverynote";//if delevery existing update
				$this->insert($arr);
			}
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;
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
		$sql="SELECT 
				  s.* ,
				  (SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=(SELECT p.`measure_id` FROM `tb_product` AS p WHERE p.id=s.`pro_id`)) AS measure,
				  (SELECT p.qty FROM `tb_prolocation` AS p WHERE p.pro_id=s.`pro_id` AND p.`location_id`=(SELECT `branch_id` FROM `tb_sales_order` WHERE `id`=s.`saleorder_id`)) AS cu_qty
				FROM
				  `tb_salesorder_item` AS s 
				WHERE saleorder_id =$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
	
	function getProductPriceBytype($product_id){//BY Customer Level and Product id
   	$db = $this->getAdapter();
   	$sql=" SELECT price,pro_id FROM `tb_product_price` WHERE type_id = 
   		(SELECT customer_level FROM `tb_customer` WHERE id=$customer_id limit 1) AND pro_id=$product_id LIMIT 1 ";
   	return $db->fetchRow($sql);
   }
}
