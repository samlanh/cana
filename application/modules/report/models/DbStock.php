<?php 
Class report_Model_DbStock extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getPORequestHistory($search){
		$db= $this->getAdapter();
		$sql="SELECT * FROM `v_purchase_history` AS p WHERE p.date_request BETWEEN '".date("Y-m-d",strtotime($search["start_date"]))."' AND '".date("Y-m-d",strtotime($search["end_date"]))."'";
		$where='';
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.`re_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`number_request` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_name` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`prepare_by` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['plan']>0){
			$where .= " AND p.plan_id = ".$search['plan'];
		}
		$order = " ORDER BY p.re_id";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getReceivedHistory($search){
		$db= $this->getAdapter();
		$sql="SELECT * FROM `v_receive_purchase_history` AS p WHERE p.date_in BETWEEN '".date("Y-m-d",strtotime($search["start_date"]))."' AND '".date("Y-m-d",strtotime($search["end_date"]))."'";
		$where='';
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.`recieve_number` LIKE '%{$s_search}%'";
			$s_where[] = " p.`order_number` LIKE '%{$s_search}%'";
			$s_where[] = " p.`po_no` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_name` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`receiver` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['plan']>0){
			$where .= " AND p.plan_id = ".$search['plan'];
		}
		$order = " ORDER BY p.receive_id";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllProduct($search){
		$db= $this->getAdapter();
		$user_info = $this->GetuserInfo();
		$loc = $user_info["branch_id"];
		
		$sql="SELECT 
			  p.`item_code`,
			  p.`id`,
			  p.`item_name` ,
			  pl.`qty`,
			  pl.price,
			  pl.`location_id`,
			  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id`) AS measure,
               v.`date_order`,v.vendor_name
			  
			FROM
			  `tb_product` AS p,
			  `tb_prolocation` AS pl,
               v_receive_purchase AS v
			WHERE 
			p.id=pl.`pro_id` 
			AND p.is_meterail=0
			AND pl.`location_id`=$loc 
			AND p.`status`=1
            AND v.`pro_id`=p.`id`";
		$where='';
		
		$from_date =(empty($search['start_date']))? '1': " v.`date_order` >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': "  v.`date_order` <= '".$search['end_date']."'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
		    $s_where = array();
		    $s_search=addslashes(trim($search['ad_search']));
		    $s_search = str_replace(' ', '', $s_search);
		    $s_where[]="REPLACE(p.`item_code`,' ','')   LIKE '%{$s_search}%'";
		    $s_where[]="REPLACE(p.`item_name`,' ','')   LIKE '%{$s_search}%'";
		    
		    $where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['category']>0){
		$where .= " AND p.`cate_id` = ".$search['category'];
		}
		
		if($search['branch']>0){
		    $where .= " AND pl.`location_id` = ".$search['branch'];
		}
		
		if($search['product_id']>0){
		    $where .= " AND p.`id` = ".$search['product_id'];
		}
		
		if($search['suppliyer_id']>0){
		    $where .= " AND v.`vendor_id` = ".$search['suppliyer_id'];
		}
		if($search['category']>0){
		    $where .= " AND p.`cate_id` = ".$search['category'];
		}
// 		if($search['category']>0){
// 			$category_id = $search["category"];
// 			$parent = $this->checkCateparent($category_id);
// 			if ($parent['parent_id']==0){
// 				$where.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
// 			}else{
// 				$where.=' AND p.cate_id='.$category_id;
// 			}
// 		}
		$order=" GROUP BY p.`id` ORDER BY p.`item_code` ASC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function checkCateparent($id){
		$db = $this->getAdapter();
		$sql=" SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` = $id  limit 1";
		return $db->fetchRow($sql);
	}
	function getPoByPro($id){
		$db= $this->getAdapter();
		$user_info = $this->GetuserInfo();
		$loc = $user_info["branch_id"];
		
		$sql="SELECT 
				  poi.`qty_order`,
				  po.`date` 
				FROM
				  `tb_purchase_order_item` AS poi,
				  `tb_purchase_order` AS po 
				WHERE po.`id` = poi.`purchase_id` 
				  AND poi.`pro_id` =$id";
		return $db->fetchRow($sql);
	}
	
	function getReceiveByPro($pro_id,$data){
	    //print_r($data);exit();
		$db= $this->getAdapter();
		$user_info = $this->GetuserInfo();
		$loc = $user_info["branch_id"];
		$from_date =(empty($data['start_date']))? '1': "  r.`date_in` >= '".$data['start_date']."'";
		$to_date = (empty($data['end_date']))? '1': "   r.`date_in` <= '".$data['end_date']."'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql="SELECT 
			  r.`date_in`,
			  SUM(rt.`qty_receive`) as qty_receive
			FROM
			  `tb_recieve_order` AS r,
			  `tb_recieve_order_item` AS rt 
			WHERE r.`order_id` = rt.`recieve_id` 
			  AND rt.`pro_id` =$pro_id ";
			  $groupby = "  GROUP BY rt.`pro_id`";
		return $db->fetchRow($sql.$where.$groupby);
	}
	
	function getDeliByPro($id,$data){
		$db= $this->getAdapter();
		$user_info = $this->GetuserInfo();
		$loc = $user_info["branch_id"];
		$from_date =(empty($data['start_date']))? '1': "  d.`deli_date` >= '".$data['start_date']."'";
		$to_date = (empty($data['end_date']))? '1': "    d.`deli_date` <= '".$data['end_date']."'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql="SELECT 
			  d.`deli_date`,
			  SUM(dd.`qty`) AS deli_qty
			FROM
			  `tb_deliverynote` AS d,
			  `tb_deliver_detail` AS dd 
			WHERE d.`id` = dd.`deliver_id` AND dd.`pro_id` = $id ";
			$groupby = " GROUP BY dd.`pro_id`";
		return $db->fetchRow($sql.$where.$groupby);
	}
	function getAllStockSummary($search){
		$db= $this->getAdapter();
		$sql="SELECT 
				  p.`item_code`,
				  p.`item_name` ,
				  pl.`qty`,
				  pl.price,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id`) AS measure,
				  (SELECT SUM(po.`qty_order`) FROM `tb_purchase_order_item` AS po WHERE po.`pro_id`=p.`id` GROUP BY po.`pro_id`) AS pur_qty,
				  (SELECT SUM(so.qty_order) FROM `tb_salesorder_item` AS so WHERE so.pro_id=p.id GROUP BY so.pro_id) AS so_qty,
				  (SELECT SUM(dd.`qty`) FROM `tb_deliver_detail` AS dd WHERE dd.pro_id=p.id GROUP BY dd.pro_id) AS dd_qty,
				  (SELECT SUM(rt.`qty_receive`) FROM `tb_recieve_order_item` AS rt WHERE rt.pro_id=p.id GROUP BY rt.pro_id) AS rt_qty
				  
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.id=pl.`pro_id` AND pl.`location_id`=1 
				  ";
		$from_date =$search['start_date'];
		$to_date = $search['end_date'];
		$where = " AND r.`date_in` BETWEEN "."'".date("m/d/Y",strtotime($from_date))."'"." AND "."'".date("m/d/Y",strtotime($to_date))."'";
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.`item_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_name` LIKE '%{$s_search}%'";
			$s_where[] = " p.`measure` LIKE '%{$s_search}%'";
			$s_where[] = " r.`order_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`recieve_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`supplier` LIKE '%{$s_search}%'";
			$s_where[] = " r.`purchaser` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		
		if($search['category']>0){
			$where .= " AND p.`cate_id` = ".$search['category'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY r.`order_number` ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllStockinReport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT 
				  r.`date_in`,
				  p.`item_code`,
				  p.`item_name`,
				  p.`measure`,
				  p.`cattegory`,
					r.* 
				FROM
				  `v_product` AS p,
				  `v_receive_purchase` AS r 
				WHERE 
				p.is_meterail=0
				AND p.`id` = r.`pro_id` ";
		$from_date =date("Y-m-d",strtotime($search['start_date']));
		$to_date = date("Y-m-d",strtotime($search['end_date']));
		
		$from_date =(empty($search['start_date']))? '1': " r.`date_in` >= '".$from_date." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.`date_in` <= '".$to_date." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " p.`item_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_name` LIKE '%{$s_search}%'";
			$s_where[] = " p.`measure` LIKE '%{$s_search}%'";
			$s_where[] = " r.`order_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`recieve_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`supplier` LIKE '%{$s_search}%'";
			$s_where[] = " r.`purchaser` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['suppliyer_id']>0){
			$where.= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['branch']>0){
			$where.= " AND branch = ".$search['branch'];
		}
		
		if(!empty($search['purchaser'])){
		    $where.= " AND (SELECT pur.user_mod FROM `tb_purchase_order` AS pur WHERE pur.id=r.`purchase_id`)=".$search['purchaser'];
		}
		
		if($search["category"]!="" AND $search["category"]>0){
			$category_id = $search["category"];
			$parent = $this->checkCateparent($category_id);
			if ($parent['parent_id']==0){
				$sql.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
			}else{
				$where.=' AND p.cate_id='.$category_id;
			}
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission('branch');
		$order=" ORDER BY r.`order_number` ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
		
	public function getAllStockoutport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT 
				  s.`date_sold`,
				  p.`item_code`,
				  p.`item_name`,
				  p.`cattegory`,
				  p.`measure`,
				  s.`qty_order`,
				  s.`price`,
				  s.`sub_total`,
				  s.`sale_no`,
				  s.`user`,
				  s.currency,
				  s.`customer`,
				  s.type,
				  s.note_sale 
				FROM
				  `v_product` AS p,
				  `v_sale_order` AS s 
				WHERE 
				p.is_meterail=0
				AND p.id = s.`pro_id` ";
// 		$from_date =$search['start_date'];
// 		$to_date = $search['end_date'];
// 		$where = " AND s.`date_sold` BETWEEN "."'".date("Y-m-d",strtotime($from_date))."'"." AND "."'".date("Y-m-d",strtotime($to_date))."'";
		
		$from_date =date("Y-m-d",strtotime($search['start_date']));
		$to_date = date("Y-m-d",strtotime($search['end_date']));
		
		$from_date =(empty($search['start_date']))? '1': " s.`date_sold` >= '".$from_date." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.`date_sold` <= '".$to_date." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.`item_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_name` LIKE '%{$s_search}%'";
			$s_where[] = " p.`measure` LIKE '%{$s_search}%'";
			$s_where[] = " s.`sale_no` LIKE '%{$s_search}%'";
			$s_where[] = " s.`customer` LIKE '%{$s_search}%'";
			$s_where[] = " s.`user` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['category']>0 AND $search["category"]>0){
			if($search["category"]!=""){
				$category_id = $search["category"];
				$parent = $this->checkCateparent($category_id);
				if ($parent['parent_id']==0){
					$where.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
				}else{
					$where.=' AND p.cate_id='.$category_id;
				}
			}
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY s.`sale_no`";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getVendor(){
		$db = $this->getAdapter();
		$sql ="SELECT v.`vendor_id`,v.`v_name` FROM `tb_vendor` AS v WHERE v.`v_name`!=''";
		return $db->fetchAll($sql);
	}
	
	public function getCategoryOption(){
		$db = $this->getAdapter();
		$sql = 'SELECT c.id,c.`name` FROM `tb_category` AS c  WHERE c.`name`!=""';
		return $db->fetchAll($sql);
	}
	
	function getPORequestDetail($search){
		$db = $this->getAdapter();
		$sql ="SELECT 
				  v.*,
				  p.`number_request`,
				  p.`re_code`,
				  p.`date_request`,
				  p.`date_from_work_space` ,
				  pr.`qty`,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`branch_id`) AS branch,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`appr_status` AND v.type=12) AS `app_status`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`pedding` AND v.type=11) AS `pedding`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name,
  (SELECT prr.`date` FROM `tb_purchase_request_remark` AS prr WHERE prr.re_id=p.id AND prr.type=2) AS mr_receive_date
				FROM
				  `v_product` AS v,
				  `tb_purchase_request_detail` AS pr,
				  `tb_purchase_request` AS p 
				WHERE p.`id` = pr.`pur_id` 
				  AND pr.`pro_id` = v.`id` ";
		
// 		if($search['search_date']==1){
// 		    $search['start_date']=date("Y-m-d");
// 		    $search['end_date']=date("Y-m-d");
		     
// 		}elseif ($search['search_date']==2){
// 		    $str_next = '+1 week';
// 		    $search['start_date']=date("Y-m-d");
// 		    $search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		
// 		}elseif ($search['search_date']==3){
// 		    $str_next = '+1 month';
// 		    $search['start_date']=date("Y-m-d");
// 		    $search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
// 		}else{}
		
		$from_date =(empty($search['start_date']))? '1': " p.`date_request` >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': "  p.`date_request` <= '".$search['end_date']."'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = "  p.`number_request` LIKE '%{$s_search}%'";
			$s_where[] = "  p.`re_code` LIKE '%{$s_search}%'";
			$s_where[] = "  pr.`qty`  LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch'])){
			$where .= " AND p.`branch_id` = ".$search['branch'];
		}
		if(!empty($search['plan'])){
			$where .= " AND p.`plan_id` = ".$search['plan'];
		}
		if(!empty($search['appr_status'])){
		    $where .= " AND p.`appr_status` = ".$search['appr_status'];
		}
		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	
	function getAllPurchaseReport($search){//new
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
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
			$s_where[] = " (SELECT p.`re_code` FROM `tb_purchase_request` AS p WHERE p.id=re_id) LIKE '%{$s_search}%'";
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['po_pedding']>0){
			$where .= " AND purchase_status =".$search['po_pedding'];
		}
		
		if(!empty($search['plan'])){
		    $where .= " AND (SELECT `plan_id` FROM `tb_purchase_request` WHERE tb_purchase_request.id = re_id AND status=1)  =".$search['plan'];
		}
		
		if(!empty($search['branch_id'])){
		    $where .= " AND branch_id =".$search['branch_id'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);

	}
	
	function getAllPurchaseReportDetail($search){//new
		$db= $this->getAdapter();
		$sql="SELECT 
				  v.*,
				  p.`order_number`,
				  p.`date`,
				  pr.`qty_order`,
				  pr.`price`,
				  p.`vat`,
				  pr.`sub_total`,
				   pr.`remark`,
				  (SELECT po.`re_code` FROM `tb_purchase_request` AS po WHERE po.`id`=p.`re_id` LIMIT 1) AS re_no ,
				  (SELECT po.`date_request` FROM `tb_purchase_request` AS po WHERE po.`id`=p.`re_id` LIMIT 1) AS date_request ,
				  (SELECT po.`number_request` FROM `tb_purchase_request` AS po WHERE po.`id`=p.`re_id` LIMIT 1) AS number_request ,
				  (SELECT po.`date_from_work_space` FROM `tb_purchase_request` AS po WHERE po.`id`=p.`re_id` LIMIT 1) AS date_from_work_space,
				  (SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = p.branch_id LIMIT 1) AS branch_name,
				  (SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 LIMIT 1) AS user_name
				FROM
				  `v_product` AS v,
				  `tb_purchase_order` AS p,
				  `tb_purchase_order_item` AS pr 
				WHERE p.id = pr.`purchase_id` 
				  AND pr.`pro_id` = v.`id`  ";
		
		$from_date =(empty($search['start_date']))? '1': " p.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search=addslashes(trim($search['text_search']));
			$s_search = str_replace(' ', '', $s_search);
			$s_where[] = " REPLACE(item_name,' ','')     LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(item_code,' ','')     LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(order_number,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(net_total,' ','')     LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(paid,' ','')          LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(balance,' ','')       LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['add_item']>0){
			$where .= " AND v.id = ".$search['add_item'];
		}
		
		if(!empty($search['plan'])){
		    $where .= " AND (SELECT `plan_id` FROM `tb_purchase_request` WHERE tb_purchase_request.id = p.re_id AND status=1)  =".$search['plan'];
		}
		
		if(!empty($search['branch_id'])){
		    $where .= " AND p.branch_id =".$search['branch_id'];
		}
// 		if($search['po_pedding']>0){
// 			$where .= " AND purchase_status =".$search['po_pedding'];
// 		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.order_number DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getReceiveDetailReport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT 
				  r.`date_in`,
				  p.`item_code`,
				  p.`item_name`,
				  p.`measure`,
				  r.`qty_receive`,
				  r.`price`,
				  r.sub_total_after,
				  r.`order_number`,
				  r.`recieve_number`,
				  r.`supplier`,
				  r.`purchaser` ,
				   p.`cattegory`,
					r.`currency` ,
					 (SELECT name FROM `tb_sublocation` WHERE id=r.branch LIMIT 1) AS branch,
					 (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = r.user_id LIMIT 1) AS user_name
				FROM
				  `v_product` AS p,
				  `v_receive_purchase` AS r 
				WHERE p.`id` = r.`pro_id`
				  ";
		$from_date =$search['start_date'];
		$to_date = $search['end_date'];
		//$where = " AND r.`date_in` BETWEEN "."'".date("m/d/Y",strtotime($from_date))."'"." AND "."'".date("m/d/Y",strtotime($to_date))."'";
		$where='';
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.`item_code` LIKE '%{$s_search}%'";
			$s_where[] = " p.`item_name` LIKE '%{$s_search}%'";
			$s_where[] = " p.`measure` LIKE '%{$s_search}%'";
			$s_where[] = " r.`order_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`recieve_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`supplier` LIKE '%{$s_search}%'";
			$s_where[] = " r.`purchaser` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		
		if($search['category']>0){
			$where .= " AND p.`cate_id` = ".$search['category'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY r.`order_number` ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	function getAllPOInvoice($search){
		$db = $this->getAdapter();
		$where='';
		$sql= "SELECT 
				  po.*,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.user_id=po.`user_id` LIMIT 1) AS user_name
				FROM
				  `tb_purchase_invoice` AS po";
		$from_date =(empty($search['start_date']))? '1': " po.`invoice_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " po.`invoice_date` <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " po.`invoice_no` LIKE '%{$s_search}%'";
			$s_where[] = " po.`sub_total_after` LIKE '%{$s_search}%'";
			$s_where[] = " po.`paid_after` LIKE '%{$s_search}%'";
			$s_where[] = " po.`balance_after` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where);
	}
	
	function getAllPOInvoiceDetail(){
		$db = $this->getAdapter();
		$sql= "SELECT 
				  po.*,
				  pd.`total`,
  pd.`paid`,
  pd.`balance`,
				  pd.`receive_date`,
				  (SELECT r.`recieve_number` FROM `tb_recieve_order` AS r WHERE r.`order_id`=pd.`receive_id`) AS recieve_number,
				  (SELECT r.`dn_number` FROM `tb_recieve_order` AS r WHERE r.`order_id`=pd.`receive_id`) AS dn_number,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.user_id=po.`user_id` LIMIT 1) AS user_name
				FROM
				  `tb_purchase_invoice` AS po,
				  `tb_purchase_invoice_detail` AS pd 
				WHERE po.id = pd.`invoice_id`";
		return $db->fetchAll($sql);
	}
	
	function getInvoiceControlling($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  i.`invoice_no`,
				  i.`invoice_date`,
				  i.`receive_invoice_date`,
				  i.`invoice_controlling_date` ,
				  i.`grand_total`,
				  i.`paid`,
				  i.`balance`,
				  (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=i.`user_id`) AS user_nam
				  
				FROM
				  `tb_invoice_controlling` AS i  ";
		
		$from_date =(empty($search['start_date']))? '1': " i.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " i.`invoice_date`  <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " i.`invoice_no` LIKE '%{$s_search}%'";
			$s_where[] = " i.`grand_total` LIKE '%{$s_search}%'";
			$s_where[] = " i.`paid`    LIKE '%{$s_search}%'";
			$s_where[] = " i.`balance` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['is_paid_balance'])){
			if($search['is_paid_balance']==1){
				$where .= " AND  i.`balance` > 0 ";
			}else{
				$where .= " AND  i.`balance` = 0 ";
			}
		}
		//echo $sql.$where;exit();
		return $db->fetchAll($sql.$where);
	}
	
	
	function getAllReciept($search){
			$db= $this->getAdapter();
			$sql=" SELECT r.id,
			(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
			(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS customer_name,
			r.`date_input`,
			r.`total`,r.`paid`,r.`balance`,r.`pol_no`,r.`expense_date`,r.`bank_acc`,
			(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id`) AS payment_name,
			cheque_number,bank_name,withdraw_name,che_issuedate,che_withdrawaldate,
			(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
			(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name 
			FROM `tb_vendor_payment` AS r ";
			
			$from_date =(empty($search['start_date']))? '1': " r.`expense_date` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " r.`expense_date` <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " r.`pol_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`total` LIKE '%{$s_search}%'";
				$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND r.`branch_id` = ".$search['branch_id'];
			}
			if($search['suppliyer_id']>0){
				$where .= " AND r.vendor_id =".$search['suppliyer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllRecieptDetail($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
					r.id,
					(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
					(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS customer_name,
					r.`date_input`,
					r.`total`,r.`paid`,r.`balance`,r.`pol_no`,r.`expense_date`,r.`bank_acc`,
					(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id`) AS payment_name,
					bank_name,withdraw_name,
					(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name ,
					vd.`date_input` AS invoice_date,
					vd.`grand_total` AS in_total,
					vd.`paid` AS in_paid,
					vd.`balance` AS in_balance,
					(SELECT i.invoice_no FROM `tb_invoice_controlling` AS i WHERE i.id=vd.`invoice_id`) AS invoice_no
				FROM
				  `tb_vendor_payment` AS r,
				  `tb_vendorpayment_detail` AS vd 
				WHERE r.`id` = vd.`receipt_id` ";
			
			$from_date =(empty($search['start_date']))? '1': " r.`expense_date` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " r.`expense_date` <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`total` LIKE '%{$s_search}%'";
				$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND r.`branch_id` = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND r.vendor_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllWorkingStone($search){
		$db = $this->getAdapter();
		$start_date = date("Y-m-d",strtotime($search["start_date"]));
		$end_date =  date("Y-m-d",strtotime($search["end_date"]));
		$sql = "SELECT 
				  p.*,
				  r.* 
				FROM
				  `v_product` AS p,
				  `v_receive_purchase` AS r 
				  WHERE 
				  	p.id=r.`pro_id` 
				  	AND p.`is_meterail`=1  ";
			$where ='';
			$from_date =(empty($search['start_date']))? '1': " r.`date_in` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "  r.`date_in` <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			
			if(!empty($search['ad_search'])){
				$s_where = array();
				$s_search=addslashes(trim($search['ad_search']));
				$s_search = str_replace(' ', '', $s_search);
				$s_where[]="REPLACE(r.plat_no,' ','')   LIKE '%{$s_search}%'";
				$s_where[]="REPLACE(p.item_code,' ','')   LIKE '%{$s_search}%'";
				$s_where[]="REPLACE(p.item_name,' ','')   LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['category']>0){
				if($search["category"]!=""){
					$category_id = $search["category"];
					$parent = $this->checkCateparent($category_id);
					if ($parent['parent_id']==0){
						$where.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
					}else{
						$where.=' AND p.cate_id='.$category_id;
					}
				}
			}
			if($search['add_item']>0){
				$where .= " AND p.id = ".$search['add_item'];
			}
			if($search['suppliyer_id']>0){
				$where .= " AND r.`vendor_id` = ".$search['suppliyer_id'];
			}
			// $dbg = new Application_Model_DbTable_DbGlobal();
			// $where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where);
	}
	
	function RecivePO($search,$groupbys=null){
		$db = $this->getAdapter();
		$start_date = date("Y-m-d",strtotime($search["start_date"]));
		$end_date =  date("Y-m-d",strtotime($search["end_date"]));
		$sql = "SELECT 
				  p.*,
				  r.* 
				FROM
				  `v_product` AS p,
				  `v_receive_purchase` AS r 
				  WHERE p.id=r.`pro_id` AND p.is_meterail!=1 AND r.`date_in` BETWEEN '$start_date' AND '$end_date'";
			$where ='';
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " sale_no LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$s_where[] = " paid LIKE '%{$s_search}%'";
				$s_where[] = " balance LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['category']>0){
				$category_id = $search["category"];
				$parent = $this->checkCateparent($category_id);
				if ($parent['parent_id']==0){
					$where.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
				}else{
					$where.=' AND p.cate_id='.$category_id;
				}
			}
			if($search['add_item']>0){
				$where .= " AND p.id = ".$search['add_item'];
			}
			if($search['suppliyer_id']>0){
				$where .= " AND r.`vendor_id` = ".$search['suppliyer_id'];
			}
			$order=" ORDER BY r.order_id DESC ";
			if($groupbys ==1){
				$groupby=' GROUP BY r.order_id';
			}else{
				$groupby='';
			}
		return $db->fetchAll($sql.$where.$groupby.$order);
	}
	
	function getAllSaleDetail($search){
		$db= $this->getAdapter();
			$sql=" SELECT s.id,
			(SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND `status`=1 AND `name`!='' LIMIT 1) AS branch_name,
			(SELECT q.`quoat_number` FROM `tb_quoatation` AS q WHERE q.id=s.`quote_id`) AS quote_no,
			(SELECT q.`date_order` FROM `tb_quoatation` AS q WHERE q.id=s.`quote_id`) AS quote_date,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
			(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
			(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=s.appr_status LIMIT 1) AS appr_status,
			(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=s.pending_status LIMIT 1) AS pedding,
			(SELECT p.item_name FROM `tb_product` AS p WHERE p.id=si.`pro_id` LIMIT 1) AS item_name,
			(SELECT p.item_code FROM `tb_product` AS p WHERE p.id=si.`pro_id` LIMIT 1) AS item_code,
			(SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=si.`pro_id`) LIMIT 1) AS measure,
			si.`qty_order`,
			si.`price`,
			si.`benefit_plus`,
			s.sale_no,
			s.date_sold,
			s.pending_status AS pedding_stat,
			s.net_total,
			s.discount_real,
			s.all_total,
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
			FROM `tb_sales_order` AS s,`tb_salesorder_item` AS si WHERE s.type=1 AND s.id=si.`saleorder_id` ";
			
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
	
	function getAllQuoatationDetail($search){
			$db= $this->getAdapter();
			$sql=" SELECT q.id,
						(SELECT NAME FROM `tb_sublocation` WHERE id=q.branch_id LIMIT 1) AS branch,
						(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=q.customer_id LIMIT 1 ) AS customer_name,
						(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =q.saleagent_id  LIMIT 1 ) AS staff_name,
						q.quoat_number,
						q.date_order,
						q.valid_date,
						(SELECT symbal FROM `tb_currency` WHERE id= q.currency_id LIMIT 1) AS curr_name,
						q.all_total,
						q.discount_value,
						q.net_total,
						q.appr_status AS appr_stat,
						q.pending_status AS pedding_stat,
						(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=q.is_approved LIMIT 1),
						(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=q.appr_status LIMIT 1) AS appr_status,
						(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=q.pending_status LIMIT 1) AS pedding,
						(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = q.user_mod) AS user_name,
						(SELECT p.item_code FROM `tb_product` AS p WHERE p.id=qi.`pro_id` LIMIT 1) AS item_code,
						(SELECT p.item_name FROM `tb_product` AS p WHERE p.id=qi.`pro_id` LIMIT 1) AS item_name,
						(SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=qi.`pro_id` LIMIT 1) LIMIT 1) AS measure,
						qi.`qty_order`,
						qi.`price`,
						qi.`benefit_plus`,
						qi.`sub_total`
					FROM `tb_quoatation` AS q,`tb_quoatation_item` AS qi WHERE q.id=qi.`quoat_id`";
			$order=" ORDER BY id DESC";
			
			$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
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
	
	
	function getAllInvoiceDetail($search){
			$db= $this->getAdapter();
			$sql="SELECT 
					  i.id,
					  i.`invoice_no`,
					  i.`invoice_date`,
					  i.`sub_total`,
					  i.`paid`,
					  i.`balance` ,
					  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=i.`branch_id`) AS branch,
					  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=i.`customer_id`) AS customer,
					  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.user_id=i.`user_id`) AS user_name,
					  (SELECT d.`deliver_no` FROM `tb_deliverynote` AS d WHERE d.id=id.`deliver_id` LIMIT 1) AS dn_no,
					  id.`total`,
					  id.`paid`,
					  id.`balance`,
					  dd.`qty`,
					  dd.`price`,
					  dd.`total`,
					  dd.`benefit_plus`,
					  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=dd.pro_id LIMIT 1) AS item_name,
					  (SELECT p.item_code FROM `tb_product` AS p WHERE p.id=dd.pro_id LIMIT 1) AS item_code,
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=dd.pro_id LIMIT 1)) AS measure
					FROM
					  `tb_invoice` AS i,`tb_invoice_detail` AS id,`tb_deliver_detail` AS dd 
					WHERE i.`id`=id.`invoice_id` AND dd.`deliver_id`=id.`deliver_id` AND i.`invoice_date` BETWEEN '".$search['start_date']."' AND '".$search['end_date']."'";
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
	
	function getPettyCashDetail($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				  p.`number_request` ,
				  p.`re_code`,
				  p.`date_from_work_space`,
				  p.`date_request`,
				  p.`remark` AS p_remark,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=p.`branch_id` LIMIT 1) AS branch,
				  (SELECT pd.item_name FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_name,
				  (SELECT pd.item_code FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pd.measure_id FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  pci.`price`,
				  pci.`qty`,
				  pci.`total`,
				  pci.`remark`,
				  pci.`date_in`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name
				FROM
				  `tb_purchase_request_party_cash` AS p,
				  `tb_purchase_request_party_cash_item` AS pci 
				  WHERE p.id=pci.`pur_id`";
		//$where = "";
		$from_date =(empty($search['start_date']))? '1': " p.date_request >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': " p.date_request <= '".$search['end_date']."'";
		$where = " AND".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
			$s_search = str_replace(' ', '', $s_search);
			$s_where[] = "REPLACE(p.number_request,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(p.re_code,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(pci.price,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(pci.total,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(pci.remark,' ','')  LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch']>0){
			$where .= " AND p.`branch_id` = ".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.id DESC ";
		//echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllPurchasePettyCash($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				   p.`re_work_number` AS number_request  ,
				  p.`re_code`,
				  p.`re_work_date` AS `date_from_work_space`,
				  p.`re_date` AS `date_request`,
				  p.`remark` AS p_remark,
				  p.`po_date`,
				  p.`order_number`,
				  p.net_total,
				  p.total_est AS total_estamount,
				  pci.`sample_price`,
				  pci.sub_total,
				  pci.total_est,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id=p.`paln_id`) AS plan,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=p.`branch_id` LIMIT 1) AS branch,
				  (SELECT pd.item_name FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_name,
				  (SELECT pd.item_code FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pd.measure_id FROM `tb_product` AS pd WHERE pd.id=pci.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  pci.`price`,
				  pci.`qty`,
				  pci.`remark`,
				  pci.`date_in`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name
				FROM
				  `tb_purchase_party_cash` AS p,
				  `tb_purchase_party_cash_item` AS pci 
				  WHERE p.id=pci.`pur_id`";
		//$where = "";
		$from_date =(empty($search['start_date']))? '1': " p.po_date >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': " p.po_date <= '".$search['end_date']."'";
		$where = " AND".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
			$s_search = str_replace(' ', '', $s_search);
			$s_where[] = "REPLACE( p.re_work_number,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(p.re_code,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(pci.price,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(pci.`qty`,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(p.`order_number`,' ','')  LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch']>0){
			$where .= " AND p.`branch_id` = ".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
}

?>