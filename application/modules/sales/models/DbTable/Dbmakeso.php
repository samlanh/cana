<?php

class Sales_Model_DbTable_Dbmakeso extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_quoatation";
	
	function getProductForSale($data){
		$branch_id = $data["branch_id"];
		$cu = $data["customer_id"];
		$id= $data["id"];
		$db= $this->getAdapter();
			$sql="SELECT 
					  p.id,
					  p.`item_code`,
					  p.`item_name`,
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id`) AS measure,
					  (SELECT pl.qty FROM `tb_prolocation` AS pl WHERE pl.pro_id=p.id AND pl.location_id=$branch_id) AS qty,
					  q.quoat_number,
					  q.`date_order`,
					  qi.`price`,
					  qi.`benefit_plus`
					  
					FROM
					  `tb_product` AS p ,
					  `tb_quoatation_item` AS qi,
					  `tb_quoatation` AS q
					WHERE p.id=qi.`pro_id` AND q.id=qi.`quoat_id` AND q.`branch_id`=$branch_id AND q.`customer_id`=$cu AND p.id IN($id) GROUP BY p.id ORDER BY q.`date_order` DESC ";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function getAllProductSale($data){
		$branch_id = $data["branch_id"];
		$cu = $data["customer_id"];
		$db= $this->getAdapter();
			$sql="SELECT 
					  p.id,
					  p.`item_code`,
					  p.`item_name`,
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id`) AS measure,
					  (SELECT pl.qty FROM `tb_prolocation` AS pl WHERE pl.pro_id=p.id AND pl.location_id=$branch_id) AS qty,
					  q.quoat_number,
					  q.`date_order`,
					  qi.`price`,
					  qi.`benefit_plus`
					  
					FROM
					  `tb_product` AS p ,
					  `tb_quoatation_item` AS qi,
					  `tb_quoatation` AS q
					WHERE p.id=qi.`pro_id` AND q.id=qi.`quoat_id` AND q.`branch_id`=$branch_id AND q.`customer_id`=$cu GROUP BY p.id ORDER BY q.`date_order` DESC ";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql="SELECT id,
						(SELECT NAME FROM `tb_sublocation` WHERE id=tb_quoatation.branch_id LIMIT 1) AS branch,
						(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_quoatation.customer_id LIMIT 1 ) AS customer_name,
						(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_quoatation.saleagent_id  LIMIT 1 ) AS staff_name,
						quoat_number,date_order,is_tosale,slae_id,
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
				$s_where[] = " net_total LIKE '%{$s_search}%'";
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
	
	function getSaleById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.id,
				  s.`customer_id`,
				  s.`currency_id`,
				  s.`branch_id`,
				  s.`date_sold`,
				  q.`quoat_number`,
				  s.`saleagent_id`,
				  s.`all_total`,
				  s.`net_total`,
				  s.`discount_value`,
				  s.`sale_no` ,
				  
				  s.`remark`
				FROM
				  `tb_quoatation` AS q,
				  `tb_sales_order` AS s 
				WHERE s.`quote_id` = q.`id` AND s.`quote_id`=$id";
		return $db->fetchRow($sql);
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
	public function addQuoateOrderApproved($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
				$dbq = new Sales_Model_DbTable_Dbquoatation();
				$row = $dbq->getQuotationItemById($data["id"]);
				$so = $db_global->getSalesNumber($row["branch_id"]);
					$qdata=array(
					       'is_toinvocie'	 =>	1,
							'quote_id'		 =>	$data["id"],
							"customer_id"    => $row['customer_id'],
							"branch_id"      => $row["branch_id"],
							"sale_no"        => $so,//$data['txt_order'],
							"date_sold"      => date("Y-m-d"),
							"saleagent_id"   => $row['saleagent_id'],
							"currency_id"    => $row['currency_id'],
							"remark"         => $row['remark'],
							"all_total"      => $row['all_total'],
							"discount_value" => $row['discount_value'],
							"discount_value" => $row['discount_value'],
							"net_total"      => $row['net_total'],
							"user_mod"       => $GetUserId,
							"date"      	 => date("Y-m-d"),
							'pending_status' =>	4,
							"date"      	 => date("Y-m-d"),
							'type'			 =>	1,
					);
					$this->_name="tb_sales_order";
					$sale_id = $this->insert($qdata);
					 $rs = $dbq->getQuotationItemDetailid($data["id"]);
					 
					
					foreach ($rs as $r)
					{
						$data_item= array(
								'saleorder_id'	=> 	$sale_id,
								'pro_id'	  	=> 	$r['pro_id'],//$data['item_id_'.$i],
								'qty_order'	  	=> 	$r['qty_order'],
								'qty_detail'  	=> 	$r['qty_detail'],
								'qty_unit'  	=> 	$r['qty_unit'],
								'price'		  	=> 	$r['price'],
								'old_price'   	=>  $r['old_price'],
								'disc_value'  	=> 	$r['disc_value'],
								'sub_total'	  	=> 	$r['sub_total'],
								'benefit_plus'	=>	$r["benefit_plus"],
								'remark'		=> 	$r['remark'],
						);
						$this->_name='tb_salesorder_item';
						$this->insert($data_item);
					
						/*$rs_pro = $this->getProductExist($r["pro_id"],$row["branch_id"]);
						if(!empty($rs_pro)){
							$arr_pro = array(
								'qty'		=>	$rs_pro["qty"]-$r["qty_order"],
							);
							$this->_name = "tb_prolocation";
							$where=" id = ".$rs_pro['id'];
							$this->update($arr_pro,$where);
						}*/
					}
				$dbc=new Application_Model_DbTable_DbGlobal();
				$pending=4;
				$tosale = 1;
				$arr=array(		
						'is_tosale'			=>1,		
						'is_approved'		=> 1,
						'approved_userid'	=> $GetUserId,
						'approved_date'		=> date("Y-m-d"),
						'pending_status'	=>$pending,
						'appr_status'		=>	0,
				);
				$this->_name="tb_quoatation";
				$where = " id = ".$data["id"];
				$sale_id = $this->update($arr, $where);
			
			
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			
		}
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_name,
		s.is_approved,s.quoat_number,s.discount_real,s.all_total,s.date_order,s.remark,s.approval_note,s.approved_date,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
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
	function getInvoieExisting($saleid){
		$db = $this->getAdapter();
		$sql="SELECT id,invoice_no FROM `tb_invoice` WHERE sale_id=$saleid limit 1 ";
		return $db->fetchRow($sql);
	}
	public function convertQouteToSO($data)
	{
		//$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
					$so = $db_global->getSalesNumber($data["branch_id"]);
					$qdata=array(
							//'quote_id'			=>	@$id,
							"customer_id"    	=> 	$data['customer_id'],
							"branch_id"      	=> 	$data["branch_id"],
							"sale_no"       	=> 	$data['so_number'],
							"date_sold"     	=> 	date("Y-m-d",strtotime($data['order_date'])),
							"saleagent_id"  	=> 	$data['saleagent_id'],
							"currency_id"    	=> 	@$data['currency'],
							"remark"         	=> 	$data['remark'],
							"all_total"      	=> 	$data['totalAmoun'],
							"discount_value" 	=> 	$data['dis_value'],
							"net_total"      	=> 	$data['all_total'],
							"net_total"      	=> 	$data['all_total'],
							'all_total_after'	=>	$data['totalAmoun'],
							'net_total_after'	=>	$data['all_total'],
							'paid_after'		=>	0,
							'paid'				=>	0,
							'balance_after'		=>	$data['all_total'],
							'balance'			=>	$data['all_total'],
							"user_mod"       	=> 	$GetUserId,
							"date"      	 	=> 	date("Y-m-d"),
// 							'is_approved'=>0,
							'pending_status' 	=>	4,
							'appr_status'		=>	0,
							'type'			 	=>	1,
					);
					$this->_name="tb_sales_order";
					$sale_id = $this->insert($qdata);
					
					/*$dbg = new Application_Model_DbTable_DbGlobal();
					$rowexisting = $this->getInvoieExisting($data['id']);
					if(!empty($rowexisting)){// for update
						$invoice_number = $rowexisting['invoice_no'];
					}else{//for add
						$invoice_number = $dbg->getInvoiceNumber($data['branch_id']);
					}
					
					$this->_name="tb_invoice"; /// if invoice existing update
						$arr = array(
								//'approved_note'		=>	$data['app_remark'],
								'sale_id'			=>	$sale_id,
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
					*/
					
					$ids=explode(',',$data['identity']);
					foreach ($ids as $i)
					{
						$data_item= array(
								'saleorder_id'		=>  $sale_id,
								'pro_id'	  		=> 	$data['item_id_'.$i],
								'qty_order'	  		=> 	$data['qty'.$i],
								'qty_order_after'  	=> 	$data['qty'.$i],
								'price'		  		=> 	$data['price'.$i],
								'old_price'   		=>  $data['oldprice_'.$i],
								//'disc_value'  => $data['dis_value'.$i],
								'benefit_plus'		=>	$data['dis_value'.$i],
								'sub_total'	  		=>  $data['total'.$i],
						);
						$this->_name='tb_salesorder_item';
						$this->insert($data_item);
					}
					
					/*if($id!=""){
					$arr=array(		
						'is_tosale'			=>	1,		
						'is_approved'		=> 	1,
						'approved_userid'	=> 	$GetUserId,
						'approved_date'		=> 	date("Y-m-d"),
						'pending_status'	=>	4,
						'appr_status'		=>	0,
						'slae_id'			=>	$sale_id,
					);
					$this->_name="tb_quoatation";
					$where = " id = ".$data["id"];
					$sale_id = $this->update($arr, $where);
					}*/
					///add term condtion of so
					
		    
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
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
	
	function getProductExist($id,$br_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.id,pl.`pro_id`,pl.`qty` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=$id AND pl.`location_id`=$br_id";
		return $db->fetchRow($sql);
	}
}