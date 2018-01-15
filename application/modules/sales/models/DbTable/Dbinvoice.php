<?php

class Sales_Model_DbTable_Dbinvoice extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_invoice";
	function getAllInvoice($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
					  i.id,
					  i.`invoice_no`,
					  i.`invoice_date`,
					  i.`sub_total`,
					  i.`paid`,
					  i.`balance` ,
					  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=i.`branch_id`) AS branch,
					  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS customer,
					  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.user_id=i.`user_id`) AS user_name
					FROM
					  `tb_invoice` AS i WHERE i.`invoice_date` BETWEEN '".$search['start_date']."' AND '".$search['end_date']."'";
			
			
			$where = "";
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " i.invoice_no LIKE '%{$s_search}%'";
				$s_where[] = " i.invoice_date LIKE '%{$s_search}%'";
				$s_where[] = " i.sub_total LIKE '%{$s_search}%'";
				$s_where[] = " i.paid LIKE '%{$s_search}%'";
				$s_where[] = " i.balance LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND i.branch_id = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND i.customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
	
		return $db->fetchAll($sql.$where.$order);
	}
	public function getInvoiceById($id){
		$db = $this->getAdapter();
		$sql ="SELECT 
  i.id,
  i.`invoice_no`,
  i.`invoice_date`,
  i.`sub_total`,
  i.`paid`,
  i.`balance`,
  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=i.`branch_id`) AS branch,
  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS customer_name,
  (SELECT c.`phone` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS phone,
  (SELECT c.`email` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS email,
  (SELECT c.`address` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS add_name,
  (SELECT c.`contact_name` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS contact_name,
  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.user_id=i.`user_id`) AS user_name,
  (SELECT d.`deliver_no` FROM `tb_deliverynote` AS d WHERE d.id=iv.`deliver_id`) AS  dn_no,
  (SELECT d.`deli_date` FROM `tb_deliverynote` AS d WHERE d.id=iv.`deliver_id`) AS  deli_date,
  (SELECT d.`all_total` FROM `tb_deliverynote` AS d WHERE d.id=iv.`deliver_id`) AS  dn_all_total,
  (SELECT d.`paid` FROM `tb_deliverynote` AS d WHERE d.id=iv.`deliver_id`) AS  dn_paid,
  (SELECT d.`balance_after` FROM `tb_deliverynote` AS d WHERE d.id=iv.`deliver_id`) AS  dn_balance,
  iv.* 
FROM
  `tb_invoice` AS i,
  `tb_invoice_detail` AS iv 
WHERE i.`id`=iv.`invoice_id` AND i.id=$id";
		return $db->fetchAll($sql);
	}
	public function addInvoice($data)	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbg = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$is_full_paid=0;
			$sale_is_fullpaid =0;

			$invoice_number = $dbg->getInvoiceNumber($data['branch_id']);
			$ids= explode(',',$data['identity']);
			if(!empty($ids)){
				if($data['paid']>=$data['balance']){
					$is_full_paid = 1;
				}
				
				$arr = array(
						'branch_id'			=>	$data['branch_id'],
						'invoice_no'		=>	$invoice_number,
						'invoice_date'		=>	date("Y-m-d",strtotime($data['invoice_date'])),
						'customer_id'		=>	$data["customer_id"],
						//'approved_date'		=>	date("Y-m-d",strtotime($data['app_date'])),
						'user_id'			=>	$GetUserId,
						'sub_total'			=>	$data['all_total'],//$data['net_total'],
						//'discount'			=>	$data['discount'],
						'paid'				=>	$data['paid'],
						'balance'			=>	$data['balance'],
						'sub_total_after'	=>	$data['all_total'],//$data['net_total'],
						//'discount_after'	=>	$data['discount'],
						'paid_after'		=>	$data['paid'],
						'balance_after'		=>	$data['balance'],
						'is_fullpaid'		=>	$is_full_paid,
				);
				$this->_name = "tb_invoice";
				
				$invoice_id = $this->insert($arr);
				
				
			$paid = $data['paid'];
			//echo $paid;
			$compelted = 0;
			foreach($ids as $i){
				if($paid>=$data['balance_after'.$i]){
					//echo  $paid ."-".($data['balance_after'.$i]);
					$paid = $paid-($data['balance_after'.$i]);
					$paided = $data['balance_after'.$i];
					$balance=0;
					$compelted=1;
					$dn_fullpaid = 1;
				}else{
					if($paid>=0){
						//echo  $data['balance_after'.$i] ."-".($paid);
						$paided = $paid;
						$paid =$paid - $data['balance_after'.$i];
						$balance=abs($paid);
						$compelted=1;
						$dn_fullpaid = 0;
					
					
					}else{	
						$paided=0;
						$balance=$data['balance_after'.$i];
						$compelted=0;
						$dn_fullpaid = 0;
					}
				}
				$recipt_paid = 0;
				//echo $paid."<br />".$data['balance_after'.$i];
				/*if ($paids>0){
					$paided = $data['balance_after'.$i];
					$balance=0;
					$compelted=1;
				}else{
					$paided = 0;
					$balance= $data['balance_after'.$i];
					$compelted=0;
				}*/
				
				$rs_delever_exist = $this->getDeliveryExist($data['invoice_no'.$i]);
				$arr_de = array(
					'paid'				=>	$rs_delever_exist["paid"]+$paided,
					'balance_after'		=>	$balance,
					'paid_after'		=>	$balance,
					'is_fullpaid'		=>	$dn_fullpaid,
				);
				$this->_name = "tb_deliverynote";
				$where = "id=".$data['invoice_no'.$i];
				
				$this->update($arr_de,$where);
				
				
				$rs_sale_order_exit = $this->getSaleOrderExist($data['sale_id'.$i]);
				
				//print_r($rs_sale_order_exit);
				if(($rs_sale_order_exit["balance_after"]-$paided)<=0){
					$sale_is_fullpaid =1;
				}
				$arr_sale = array(
					'paid'				=>	$rs_sale_order_exit["paid"]+$paided,
					'paid_after'		=>	$rs_sale_order_exit["balance_after"]-$paided,
					'balance_after'		=>	$rs_sale_order_exit["balance_after"]-$paided,
					'is_fullpaid'		=>	$sale_is_fullpaid,
				);
				$this->_name = "tb_sales_order";
				$where  = "id=".$data["sale_id".$i];
				
				$this->update($arr_sale,$where);
				
				
				$arr = array(
						'invoice_id'		=>	$invoice_id,
						'deliver_id'		=>	$data['invoice_no'.$i],
						'customer_id'		=>	$data["customer_id"],
						'total'				=>	$data['subtotal'.$i],
						'paid'				=>	$data['paid_amount'.$i],
						'balance'			=>	$data['balance_after'.$i],//$data['net_total'],
						//'discount'			=>	$data['discount'],
						//'sub_total_after'	=>	$data['all_total'],//$data['net_total'],
						//'paid_after'		=>	$data['paid'],
						//'balance_after'		=>	$data['balance_after'.$i],
				);
				$this->_name = "tb_invoice_detail";
				
				$this->insert($arr);
            }				
			}
			//exit();			
			$db->commit();
			return $invoice_id;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			//echo 333;
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getDeliveryExist($id){
		$db = $this->getAdapter();
		$sql = "SELECT d.id,d.`paid`,d.`all_total`,d.`all_total_after`,d.`net_total`,d.`net_total_after` FROM `tb_deliverynote` AS d WHERE d.`id`=$id";
		return $db->fetchRow($sql);
	}
	
	function getSaleOrderExist($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.id,
				  s.`all_total`,
				  s.`all_total_after`,
				  s.`balance`,
				  s.`balance_after`,
				  s.`net_total`,
				  s.`net_total_after`,
				  s.`paid`,
				  s.`paid_after`
				FROM
				  `tb_sales_order` AS s 
				WHERE s.`id` =$id";
		return $db->fetchRow($sql);
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.branch_id,
		s.sale_no,s.date_sold,s.remark,s.approved_note,s.approved_date,s.all_total,s.quote_id,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
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
		(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1) approval_status,
		(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) processing,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,so.disc_value,
		s.paid,s.discount_real,s.tax,s.discount_value,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so WHERE s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	} 
	
	function printByDn($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  d.id,
				  d.`deliver_no`,
				  d.`deli_date` ,
				  v.`invoice_no`,
  v.`invoice_date`,
				  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS customer_name,
				  (SELECT c.`contact_name` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS contact_name,
				  (SELECT c.`phone` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS phone,
				  (SELECT c.`email` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS email,
				  (SELECT c.`address` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS address,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=dt.`pro_id`) AS item_name,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.id=dt.`pro_id`) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=dt.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  dt.`qty`,
				  dt.`qty_order`,
				  dt.`qty_order_after`,
				  dt.`price`,
				  dt.`benefit_plus`
				FROM
				  `tb_invoice` AS v,
				  `tb_invoice_detail` AS vd,
				  `tb_deliver_detail` AS dt,
				  `tb_deliverynote` AS d 
				WHERE v.`id` = vd.`invoice_id`
				  AND vd.`deliver_id`=d.id 
				  AND d.id = dt.`deliver_id` 
				  AND v.id=$id";
		return $db->fetchAll($sql);
	}
	
	function printByItem($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  d.id,
				  v.`invoice_no`,
				  v.`invoice_date`,
				  d.`deliver_no`,
				  d.`deli_date` ,
				  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS customer_name,
				  (SELECT c.`contact_name` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS contact_name,
				  (SELECT c.`phone` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS phone,
				  (SELECT c.`email` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS email,
				  (SELECT c.`address` FROM `tb_customer` AS c WHERE c.id=v.`customer_id`) AS address,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=dt.`pro_id`) AS item_name,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.id=dt.`pro_id`) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=dt.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  SUM(dt.`qty`) AS qty,
				  dt.`qty_order`,
				  dt.`qty_order_after`,
				  dt.`price`,
				  dt.`benefit_plus`
				FROM
				  `tb_invoice` AS v,
				  `tb_invoice_detail` AS vd,
				  `tb_deliver_detail` AS dt,
				  `tb_deliverynote` AS d 
				WHERE v.`id` = vd.`invoice_id`
				  AND vd.`deliver_id`=d.id 
				  AND d.id = dt.`deliver_id` 
				  AND v.id=$id GROUP BY dt.`pro_id`";
		return $db->fetchAll($sql);
	}
}