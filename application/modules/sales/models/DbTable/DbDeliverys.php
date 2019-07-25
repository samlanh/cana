<?php

class Sales_Model_DbTable_DbDeliverys extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_invoice";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS customer_name,
			(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_sales_order.saleagent_id  LIMIT 1 ) AS staff_name,
			sale_no,date_sold,approved_date,
			(SELECT symbal FROM `tb_currency` WHERE id= currency_id limit 1) As curr_name,
			all_total,discount_value,net_total,
			(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1),
			(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1),
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
			FROM `tb_sales_order` WHERE is_toinvocie=1 ";
			
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
	function getInvoieExisting($saleid){
		$db = $this->getAdapter();
		$sql="SELECT id,invoice_no FROM `tb_invoice` WHERE sale_id=$saleid limit 1 ";
		return $db->fetchRow($sql);
	}
	public function addDelivery($data)	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		$db_global = new Application_Model_DbTable_DbGlobal();
		//echo $data["is_invoice"];
		
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$is_toinvocie=0;
			$is_invoice=0;
			
			$row = $this->getItemOrder($data["id"]);
			$dn = $db_global->getDeliverNumber($data["branch_id"]);
			$invoice_no = $db_global->getInvoiceNumber($data["branch_id"]);
			//echo $row[0]["branch_id"];
			if(@$data["is_invoice"]==1){
				$is_invoice = 1;
			}
			$ids= explode(',',$data['identity']);
			if(!empty($ids)){
				
					$arr = array(
						'branch_id'				=>	$data["branch_id"],
						'deliver_no'			=>	$dn,
						'so_id'					=>	$data["id"],
						'customer_id'			=>	$data['customer_id'],
						'deli_date'				=>	date("Y-m-d"),
						'user_id'				=>	$GetUserId,
						'type'					=>	1,
						'all_total'				=>	$data["totalAmoun"],
						'all_total_after'		=>	$data["totalAmoun"],
						'net_total_after'		=>	$data["all_total"],
						'net_total'				=>	$data["all_total"],
						'paid'					=>	0,
						'paid_after'			=>	0,
						'balance'				=>	$data["all_total"],
						'balance_after'			=>	$data["all_total"],
						'discount'				=>	$data["dis_value"],
						'is_invoice'			=>	$is_invoice,
						);
					$this->_name="tb_deliverynote";//if delevery existing update
					$db->getProfiler()->setEnabled(true);
					$deliver_id = $this->insert($arr);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
					
					if(@$data["is_invoice"]==1){
						$is_toinvocie = 1;
						$arr_invoice = array(
							'branch_id'				=>	$data["branch_id"],
							'invoice_no'			=>	$invoice_no,
							'sale_id'				=>	$data["id"],
							'dn_id'					=>	$deliver_id,
							'customer_id'			=>	$data['customer_id'],
							'invoice_date'			=>	date("Y-m-d"),
							'user_id'				=>	$GetUserId,
							'sub_total'				=>	$data["all_total"],
							'discount'				=>	$data["dis_value"],
							'paid_amount'			=>	0,
							'balance'				=>	$data["all_total"],
							'balance_after'			=>	$data["all_total"],
							'is_fullpaid'			=>	0,
							//'type'					=>	1,
							);
						$this->_name="tb_invoice";//if delevery existing update
						$db->getProfiler()->setEnabled(true);
						$invoice_id = $this->insert($arr_invoice);
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

						$arr_de = array('is_invoice');
					}
				foreach($ids as $i){
					$recieved_item = array(
							'deliver_id'	  	=> 	$deliver_id,
							'pro_id'	  		=> 	$data['item_id_'.$i],
							'qty_order'			=> 	$data['qty_order_'.$i],
							'qty_order_after'	=> 	$data['qty_order_after_'.$i],
							'qty'	  			=> 	$data['qty_deliver'.$i],
							//'qty_detail'  		=> 	$data['qty_per_unit_'.$i],
							'price'		  		=> 	$data['price_'.$i],
							'benefit_plus'	  	=> $data['benefit_plus_'.$i],
							'total'	  		=> $data['sub_total_'.$i],
							//'sub_total_after'	=> $data['total_after_'.$i],
							'remark'			=>	$data["note".$i]
					);
					$this->_name="tb_deliver_detail";
					$db->getProfiler()->setEnabled(true);
					$this->insert($recieved_item);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

					$qty_after = $data['qty_order_after_'.$i]-$data['qty_deliver'.$i];
					if($qty_after==0){
						$is_completed =1; 
					}else{
						$is_completed =0; 
					}
					
					// Update Purchase Order Item Qty
					
					$po_update = array(
							'qty_order_after'		=> 	$qty_after,
							'is_completed'	=>	$is_completed,
						);
					$this->_name="tb_salesorder_item";
					$where = "saleorder_id=".$data["id"]." AND pro_id=".$data['item_id_'.$i];
					$db->getProfiler()->setEnabled(true);
					$this->update($po_update,$where);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

					if(@$data["is_invoice"]==1){
						
						$recieved_item = array(
							'deliver_id'	  	=> 	$deliver_id,
							'invoice_id'	  	=> 	$invoice_id,
							'customer_id'		=>	$data["customer_id"],
							'total'				=>	$data["all_total"],
							'paid'				=>	0,
							'balance'			=>	$data["all_total"],
							'is_complete'		=>	0,
							'status'			=>	1,
							'date'				=>	new Zend_date(),
							'deliver_date'		=>	date("Y-m-d"),
						);
						$this->_name="tb_invoice_detail";
						$db->getProfiler()->setEnabled(true);
						$this->insert($recieved_item);
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
					}
						
						
							$rs_pro = $this->getProductExist($data["item_id_".$i],$data["branch_id"]);
							if(!empty($rs_pro)){
								$arr_pro = array(
									'qty'		=>	$rs_pro["qty"]-$data["qty_deliver".$i],
								);
								$this->_name = "tb_prolocation";
								$where=" id = ".$rs_pro['id'];
								$db->getProfiler()->setEnabled(true);
								$this->update($arr_pro,$where);
								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
							}
						
					
					$row_product = $this->getProductForCost($data["item_id_".$i],$data["branch_id"]);
						if(!empty($row_product)){
							//print_r($row_product);
							$amount_pro = $row_product["price"] * $row_product["qty"];
							$new_amount_pro = $data['qty_deliver'.$i] * $data['price_'.$i];
							$total_qty = $row_product["qty"]+$data['qty_deliver'.$i];
							$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_prolocation";
							$where=" pro_id = ".$row_product['id']." AND location_id=".$data["branch_id"];
							$db->getProfiler()->setEnabled(true);
							$this->update($arr_pro, $where);
							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
							
							//update Product Table
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_product";
							$where=" id = ".$row_product['id'];
							$db->getProfiler()->setEnabled(true);
							$this->update($arr_pro, $where);
							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
						}
						
					$sql = "SELECT p.`saleorder_id` FROM `tb_salesorder_item` AS p WHERE p.`saleorder_id`=".$data["id"]." AND p.`is_completed`=0";
					$rs_po = $db->fetchAll($sql);
					//print_r($rs_po);
					if(empty($rs_po)){
						$is_po_completed=1;
						$appr_status = 1;
						$pedding = 5;
					}else{
						$is_po_completed=0;
						$appr_status = 6;
						$pedding = 4;
					}
					//echo $purchase_sta;exit();
					
					$this->_name="tb_sales_order";
					$data_to = array(
								'pending_status'	=>	$pedding,
								'appr_status'		=>	$appr_status,
								'is_deliver'		=>	1,
								'is_toinvocie'		=>	$is_toinvocie,
								);
					$where=" id = ".$data['id'];
					$db->getProfiler()->setEnabled(true);
					$this->update($data_to, $where);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				}
			}
			//exit();
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err;exit();
		}
	}
	
	
	function getSaleOrder($id){
		$db = $this->getAdapter();
		$sql="SELECT 
					s.*,
					s.`id` AS sale_id,
					so.*
				FROM
				  `tb_sales_order` AS s,
				  `tb_salesorder_item` AS so 
				WHERE s.`id` = so.`saleorder_id` AND s.id=$id";
		return $db->fetchAll($sql);
	}
	public function addRequestDeliver($id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
		
		try{
			$rs = $this->getSaleOrder($id);
			$is_set = 0;
			
			if(!empty($rs)){
				$dn = $db_global->getDeliverNumber($rs[0]["branch_id"]);
				$invoice_no = $db_global->getInvoiceNumber($rs[0]["branch_id"]);
				foreach($rs as $row){
					if($is_set!=1){
						$arr = array(
								'branch_id'				=>	$row["branch_id"],
								'deliver_no'			=>	$dn,
								'so_id'					=>	$row["sale_id"],
								'customer_id'			=>	$row['customer_id'],
								'deli_date'				=>	date("Y-m-d"),
								'user_id'				=>	$GetUserId,
								'type'					=>	2,
								'all_total'				=>	$row["all_total_after"],
								'all_total_after'		=>	$row["all_total_after"],
								'net_total_after'		=>	$row["net_total_after"],
								'net_total'				=>	$row["net_total_after"],
								'paid'					=>	0,
								'paid_after'			=>	0,
								'balance'				=>	$row["net_total_after"],
								'balance_after'			=>	$row["net_total_after"],
								'discount'				=>	$row["discount_real"],
								'is_invoice'			=>	1,
								);
							$this->_name="tb_deliverynote";//if delevery existing update
							$db->getProfiler()->setEnabled(true);
							$deliver_id = $this->insert($arr);
							
								$arr_invoice = array(
									'branch_id'				=>	$row["branch_id"],
									'invoice_no'			=>	$invoice_no,
									'sale_id'				=>	$row["sale_id"],
									'dn_id'					=>	$deliver_id,
									'customer_id'			=>	$row['customer_id'],
									'invoice_date'			=>	date("Y-m-d"),
									'user_id'				=>	$GetUserId,
									'sub_total'				=>	$row["net_total_after"],
									'discount'				=>	$row["discount_real"],
									'paid_amount'			=>	0,
									'balance'				=>	$row["net_total_after"],
									'balance_after'			=>	$row["net_total_after"],
									'is_fullpaid'			=>	0,
									'type'					=>	2,
									);
								$this->_name="tb_invoice";//if delevery existing update
								$invoice_id = $this->insert($arr_invoice);
						$is_set	= 1;
					}
					
					$recieved_item = array(
							'deliver_id'	  	=> 	$deliver_id,
							'pro_id'	  		=> 	$row['pro_id'],
							'qty_order'			=> 	$row['qty_order_after'],
							'qty_order_after'	=> 	$row['qty_order_after'],
							'qty'	  			=> 	$row['qty_order_after'],
							
							'price'		  		=> 	$row['price'],
							'benefit_plus'	  	=> $row['benefit_plus'],
							'total'	  			=> $row['sub_total'],
							
							'remark'			=>	$row["remark"]
					);
					$this->_name="tb_deliver_detail";
					
					$this->insert($recieved_item);
					

					$recieved_item = array(
							'deliver_id'	  	=> 	$deliver_id,
							'invoice_id'	  	=> 	$invoice_id,
							'customer_id'		=>	$row["customer_id"],
							'total'				=>	$row["net_total_after"],
							'paid'				=>	0,
							'balance'			=>	$row["net_total_after"],
							'is_complete'		=>	0,
							'status'			=>	1,
							'date'				=>	new Zend_date(),
							'deliver_date'		=>	date("Y-m-d"),
						);
						$this->_name="tb_invoice_detail";
						
						$this->insert($recieved_item);

					$rs_pro = $this->getProductExist($row["pro_id"],$row["branch_id"]);
							if(!empty($rs_pro)){
								$arr_pro = array(
									'qty'		=>	$rs_pro["qty"]-$row["pro_id"],
								);
								$this->_name = "tb_prolocation";
								$where=" id = ".$rs_pro['id'];
								$this->update($arr_pro,$where);
				$row_product = $this->getProductForCost($row["pro_id"],$row["branch_id"]);
						if(!empty($row_product)){
							
							$amount_pro = $row_product["price"] * $row_product["qty"];
							$new_amount_pro = $row['qty_order_after'] * $row['price'];
							$total_qty = $row_product["qty"]+$row['qty_order_after'];
							$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_prolocation";
							$where=" pro_id = ".$row_product['id']." AND location_id=".$row["branch_id"];
							$db->getProfiler()->setEnabled(true);
							$this->update($arr_pro, $where);
							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
							
							//update Product Table
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_product";
							$where=" id = ".$row_product['id'];
							$db->getProfiler()->setEnabled(true);
							$this->update($arr_pro, $where);
							
						}
						
						$this->_name="tb_sales_order";
					$data_to = array(
								'pending_status'	=>	5,
								'appr_status'		=>	1,
								'is_deliver'		=>	1,
								'is_toinvocie'		=>	1,
								);
					$where=" id = ".$id;
					
					$this->update($data_to, $where);
					
							}
			}
		}
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function addRequestDelivery($data)	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		$db_global = new Application_Model_DbTable_DbGlobal();
		//echo $data["is_invoice"];
		
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$is_toinvocie=0;
			$is_invoice=0;
			
			$row = $this->getItemOrder($data["id"]);
			$dn = $db_global->getDeliverNumber($data["branch_id"]);
			$invoice_no = $db_global->getInvoiceNumber($data["branch_id"]);
			//echo $row[0]["branch_id"];
			if(@$data["is_invoice"]==1){
				$is_invoice = 1;
			}
			$ids= explode(',',$data['identity']);
			if(!empty($ids)){
				
					$arr = array(
						'branch_id'				=>	$data["branch_id"],
						'deliver_no'			=>	$dn,
						'so_id'					=>	$data["id"],
						'customer_id'			=>	$data['customer_id'],
						'deli_date'				=>	date("Y-m-d"),
						'user_id'				=>	$GetUserId,
						'type'					=>	2,
						'all_total'				=>	$data["totalAmoun"],
						'all_total_after'		=>	$data["totalAmoun"],
						'net_total_after'		=>	$data["all_total"],
						'net_total'				=>	$data["all_total"],
						'paid'					=>	0,
						'paid_after'			=>	0,
						'balance'				=>	$data["all_total"],
						'balance_after'			=>	$data["all_total"],
						'discount'				=>	$data["dis_value"],
						'is_invoice'			=>	$is_invoice,
						);
					$this->_name="tb_deliverynote";//if delevery existing update
					$db->getProfiler()->setEnabled(true);
					$deliver_id = $this->insert($arr);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
					
					if(@$data["is_invoice"]==1){
						$is_toinvocie = 1;
						$arr_invoice = array(
							'branch_id'				=>	$data["branch_id"],
							'invoice_no'			=>	$invoice_no,
							'sale_id'				=>	$data["id"],
							'dn_id'					=>	$deliver_id,
							'customer_id'			=>	$data['customer_id'],
							'invoice_date'			=>	date("Y-m-d"),
							'user_id'				=>	$GetUserId,
							'sub_total'				=>	$data["all_total"],
							'discount'				=>	$data["dis_value"],
							'paid_amount'			=>	0,
							'balance'				=>	$data["all_total"],
							'balance_after'			=>	$data["all_total"],
							'is_fullpaid'			=>	0,
							//'type'					=>	1,
							);
						$this->_name="tb_invoice";//if delevery existing update
						$db->getProfiler()->setEnabled(true);
						$invoice_id = $this->insert($arr_invoice);
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

						$arr_de = array('is_invoice');
					}
				foreach($ids as $i){
					$recieved_item = array(
							'deliver_id'	  	=> 	$deliver_id,
							'pro_id'	  		=> 	$data['item_id_'.$i],
							'qty_order'			=> 	$data['qty_order_'.$i],
							'qty_order_after'	=> 	$data['qty_order_after_'.$i],
							'qty'	  			=> 	$data['qty_deliver'.$i],
							//'qty_detail'  		=> 	$data['qty_per_unit_'.$i],
							'price'		  		=> 	$data['price_'.$i],
							'benefit_plus'	  	=> $data['benefit_plus_'.$i],
							'total'	  		=> $data['sub_total_'.$i],
							//'sub_total_after'	=> $data['total_after_'.$i],
							'remark'			=>	$data["note".$i]
					);
					$this->_name="tb_deliver_detail";
					$db->getProfiler()->setEnabled(true);
					$this->insert($recieved_item);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

					$qty_after = $data['qty_order_after_'.$i]-$data['qty_deliver'.$i];
					if($qty_after==0){
						$is_completed =1; 
					}else{
						$is_completed =0; 
					}
					
					// Update Purchase Order Item Qty
					
					$po_update = array(
							'qty_order_after'		=> 	$qty_after,
							'is_completed'	=>	$is_completed,
						);
					$this->_name="tb_salesorder_item";
					$where = "saleorder_id=".$data["id"]." AND pro_id=".$data['item_id_'.$i];
					$db->getProfiler()->setEnabled(true);
					$this->update($po_update,$where);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

					if(@$data["is_invoice"]==1){
						
						$recieved_item = array(
							'deliver_id'	  	=> 	$deliver_id,
							'invoice_id'	  	=> 	$invoice_id,
							'customer_id'		=>	$data["customer_id"],
							'total'				=>	$data["all_total"],
							'paid'				=>	0,
							'balance'			=>	$data["all_total"],
							'is_complete'		=>	0,
							'status'			=>	1,
							'date'				=>	new Zend_date(),
							'deliver_date'		=>	date("Y-m-d"),
						);
						$this->_name="tb_invoice_detail";
						$db->getProfiler()->setEnabled(true);
						$this->insert($recieved_item);
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
					}
						
						
							$rs_pro = $this->getProductExist($data["item_id_".$i],$data["branch_id"]);
							if(!empty($rs_pro)){
								$arr_pro = array(
									'qty'		=>	$rs_pro["qty"]-$data["qty_deliver".$i],
								);
								$this->_name = "tb_prolocation";
								$where=" id = ".$rs_pro['id'];
								$db->getProfiler()->setEnabled(true);
								$this->update($arr_pro,$where);
								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
							}
						
					
					$row_product = $this->getProductForCost($data["item_id_".$i],$data["branch_id"]);
						if(!empty($row_product)){
							//print_r($row_product);
							$amount_pro = $row_product["price"] * $row_product["qty"];
							$new_amount_pro = $data['qty_deliver'.$i] * $data['price_'.$i];
							$total_qty = $row_product["qty"]+$data['qty_deliver'.$i];
							$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_prolocation";
							$where=" pro_id = ".$row_product['id']." AND location_id=".$data["branch_id"];
							$db->getProfiler()->setEnabled(true);
							$this->update($arr_pro, $where);
							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
							
							//update Product Table
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_product";
							$where=" id = ".$row_product['id'];
							$db->getProfiler()->setEnabled(true);
							$this->update($arr_pro, $where);
							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
						}
						
					$sql = "SELECT p.`saleorder_id` FROM `tb_salesorder_item` AS p WHERE p.`saleorder_id`=".$data["id"]." AND p.`is_completed`=0";
					$rs_po = $db->fetchAll($sql);
					//print_r($rs_po);
					if(empty($rs_po)){
						$is_po_completed=1;
						$appr_status = 1;
						$pedding = 5;
					}else{
						$is_po_completed=0;
						$appr_status = 6;
						$pedding = 4;
					}
					//echo $purchase_sta;exit();
					
					$this->_name="tb_sales_order";
					$data_to = array(
								'pending_status'	=>	$pedding,
								'appr_status'		=>	$appr_status,
								'is_deliver'		=>	1,
								'is_toinvocie'		=>	$is_toinvocie,
								);
					$where=" id = ".$data['id'];
					$db->getProfiler()->setEnabled(true);
					$this->update($data_to, $where);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				}
			}
			//exit();
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
	
	function getProductForCost($pro_id,$location){
		$db = $this->getAdapter();
		$sql="SELECT p.id,pl.`price`,pl.`qty` FROM `tb_product` AS p ,`tb_prolocation` AS pl WHERE p.id=pl.`pro_id` AND pl.`location_id`=$location AND p.id=$pro_id";
		return $db->fetchRow($sql);
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.branch_id,customer_id,
		s.sale_no,s.date_sold,s.remark,s.approved_note,s.approved_date,s.all_total,s.quote_id,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) AS model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approved_by,
		(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=s.is_approved LIMIT 1) approval_status,
		(SELECT pl.qty FROM `tb_prolocation` AS pl WHERE pl.pro_id=so.`pro_id` AND pl.`location_id`=s.`branch_id` LIMIT 1) AS qty,
		(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=s.pending_status LIMIT 1) processing,
		(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=s.appr_status LIMIT 1) processing,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,so.disc_value,
		s.paid,s.discount_real,s.tax,s.discount_value,so.qty_order_after,
		s.balance,so.benefit_plus,so.pro_id,
		(SELECT q.quoat_number FROM tb_quoatation AS q WHERE q.id=s.quote_id) AS quote_no,
		(SELECT q.date_order FROM tb_quoatation AS q WHERE q.id=s.quote_id) AS quote_date ,
		(SELECT m.name FROM tb_measure AS m WHERE m.id=(SELECT p.measure_id FROM tb_product AS p WHERE p.id=so.pro_id ) LIMIT 1) AS measure
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so 
		WHERE s.id=so.saleorder_id 
		AND s.id = $id ";
		return $db->fetchAll($sql);
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
}