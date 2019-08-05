<?php

class Purchase_Model_DbTable_DbPurchaseOrder extends Zend_Db_Table_Abstract
{	
	//get update order but not well
	function getAllPurchaseOrder($search){//new
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,
		date_order,
		date_in,
		re_id,
		is_approved AS approved,
		(SELECT p.`re_code` FROM `tb_purchase_request` AS p WHERE p.id=re_id) AS re_code,
		(SELECT p.`date_request` FROM `tb_purchase_request` AS p WHERE p.id=re_id) AS date_request,
		(SELECT `name` FROM `tb_plan` AS pl WHERE pl.id=(SELECT p.`plan_id` FROM `tb_purchase_request` AS p WHERE p.id=re_id)) AS plan,
		(SELECT symbal FROM `tb_currency` WHERE id= currency_id LIMIT 1) AS curr_name,
		net_total,paid,balance,purchase_status AS p_status,is_purchase_order,is_recieved,is_completed,
		(SELECT name_en FROM `tb_view` WHERE key_code = pending_status AND `type`=11 LIMIT 1) AS pedding_status,
		(SELECT name_en FROM `tb_view` WHERE key_code = is_approved AND `type`=7 LIMIT 1) AS is_approved,
		(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=12 LIMIT 1) AS purchase_status,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND TYPE=5 LIMIT 1) AS `status`,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 LIMIT 1) AS user_name
		FROM `tb_purchase_order` ";
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT p.`re_code` FROM `tb_purchase_request` AS p WHERE p.id=re_id) LIKE '%{$s_search}%'";
			$s_where[] = " status LIKE '%{$s_search}%'";
			//$s_where[] = " code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['po_pedding']>0){
			$where .= " AND pending_status =".$search['po_pedding'];
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id =".$search['suppliyer_id'];
		}
		if($search['branch']>0){
			$where .= " AND branch_id =".$search['branch'];
		}
		if(!empty($search['plan'])){
			$where .= " AND (SELECT `id` FROM `tb_plan` AS pl WHERE pl.id=(SELECT p.`plan_id` FROM `tb_purchase_request` AS p WHERE p.id=re_id)) =".$search['plan'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY is_recieved ASC,is_completed ASC ,id DESC ";
 		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);

	}
	function getProductPruchaseById($id){//2
		$db = $this->getAdapter();
		$sql=" SELECT 
				 (SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
				 p.order_number,p.date_order,p.date_in,p.remark,
				 (SELECT item_name FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS item_name,
				 (SELECT item_code FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS item_code,
				 (SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
				 (SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
				 (SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
				 (SELECT contact_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
				 (SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
				 (SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
				 (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
				 po.qty_order,po.price,po.sub_total,p.net_total,
				 p.paid,p.discount_real,p.tax,
				 p.balance
				 FROM `tb_purchase_order` AS p,
				`tb_purchase_order_item` AS po WHERE p.id=po.purchase_id
				 AND po.status=1 AND p.id = $id ";
		return $db->fetchAll($sql);
	}
	public function getPurchaseID($id){
		$db = $this->getAdapter();
		$sql = "SELECT CONCAT(p.item_name,'(',p.item_code,' )') AS item_name , p.qty_perunit,od.order_id, od.pro_id, od.qty_order,
				
		od.price, od.total_befor, od.disc_type,	od.disc_value, od.sub_total, od.remark 
				
		FROM tb_purchase_order_item AS od
				
		INNER JOIN tb_product AS p ON p.pro_id=od.pro_id WHERE od.order_id=".$id;
		$row = $db->fetchAll($sql);
		return $row;
	}
	//get purchase info //23/8/13
	public function purchaseInfo($id){
		$db=$this->getAdapter();
		$sql = "SELECT poh.history_id,poh.date,v.vendor_id,v.v_name,v.phone,v.add_name,v.contact_name,v.add_remark,ro.recieve_id,
		p.order_id,p.LocationId, p.order, p.date_order,p.date_in,p.status,p.payment_method,p.currency_id,
		p.remark,p.version,p.net_total,p.discount_type,p.discount_value,p.discount_real,p.paid,p.all_total,p.balance, SUM(poi.sub_total) as sub_total
		FROM 
				tb_purchase_order_item as poi,
				tb_purchase_order AS p 
		INNER JOIN 
				tb_vendor AS v ON v.vendor_id= p.vendor_id
		INNER JOIN tb_purchase_order_history as poh ON poh.order = p.order_id
		INNER JOIN tb_recieve_order as ro ON ro.order_id = p.order_id
		WHERE poi.order_id = p.order_id and p.order_id=".$id." LIMIT 1";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	public function recieved_info($order_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_recieve_order WHERE order_id=".$order_id." LIMIT 1";		
		$row =$db->fetchRow($sql);
		return $row;
	}
	//for get left order address change form showsaleorder to below
	public function showPurchaseOrder(){
		$db= $this->getAdapter();
		$sql = "SELECT p.order_id, p.order, p.date_order, p.status, v.v_name, p.all_total,p.paid,p.balance
		FROM tb_purchase_order AS p  INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id";
		$row=$db->fetchAll($sql);
		return $row;
		
	}
	public function getVendorInfo($post){
		$db=$this->getAdapter();
		$sql="SELECT contact_name,phone, add_name AS address 
		FROM tb_vendor WHERE vendor_id = ".$post['vendor_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
}