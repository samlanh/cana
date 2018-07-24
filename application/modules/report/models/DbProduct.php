<?php 
Class report_Model_DbProduct extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getAllProduct($data,$warning=null){
		$db = $this->getAdapter();
		//print_r($data);exit();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		/*$sql ="SELECT
				  p.`id`,
				  p.`barcode`,
				  p.`item_code`,
				  p.`item_name` ,
	  			  p.`serial_number`,
	  			  p.`status`,
				  (SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=pl.`location_id` LIMIT 1) AS branch,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
				  (SELECT m.name FROM `tb_model` AS m WHERE m.id=p.`model_id` LIMIT 1) AS model,
				  (SELECT s.name FROM `tb_size` AS s WHERE s.id=p.`size_id` LIMIT 1) AS size,
				  (SELECT c.name FROM `tb_color` AS c WHERE c.id=p.`color_id` LIMIT 1) AS color,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  SUM(pl.`qty`) AS qty,pl.`location_id`
				FROM
				  `tb_product` AS p ,
				  `tb_prolocation` AS pl
				WHERE p.`id`=pl.`pro_id`";*/
		$sql ="SELECT * FROM `v_product` AS p ,
				  `tb_prolocation` AS pl
				WHERE p.`id`=pl.`pro_id`";
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]>0){
	  		$category_id = $data["category"];
	  		$parent = $this->checkCateparent($category_id);
	  		if ($parent['parent_id']==0){
	  			$sql.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
	  		}else{
	  			$where.=' AND p.cate_id='.$category_id;
	  		}
  		}
		if($data["size"]!=""){
			$where.=' AND p.size_id='.$data["size"];
		}
		if($data["color"]!=""){
			$where.=' AND p.color_id='.$data["color"];
		}
		if($data["status"]!=""){
			$where.=' AND p.p_status='.$data["status"];
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		
		if($warning!=null){
			$where .= " AND pl.`qty`<=pl.`qty_warning`";
		}

		$group = " GROUP BY p.`id`";
		return $db->fetchAll($sql.$where.$location.$group);
		 
	}
	function checkCateparent($id){
		$db = $this->getAdapter();
		$sql=" SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` = $id  limit 1";
		return $db->fetchRow($sql);
	}
	
	function getAllAdjustStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
				  m.* ,
				  p.`item_name`,
				  p.`barcode`,
				  p.`item_code`,
				  (SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=m.`location_id` LIMIT 1) AS branch,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id` = p.`brand_id`) AS brand ,
				  (SELECT b.`name` FROM `tb_category` AS b WHERE b.`id` = p.`cate_id`) AS cat ,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=4) AS color,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=2) AS model,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=3) AS size,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.id=m.`location_id` LIMIT 1) AS location,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=m.`user_mod` LIMIT 1) AS `username`,
				   m.`date`
				FROM
				  `tb_move_history` AS m ,
				  `tb_product` AS p
				WHERE m.`pro_id`=p.`id`";
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND m.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]>0){
			$category_id = $data["category"];
			$parent = $this->checkCateparent($category_id);
			if ($parent['parent_id']==0){
				$where.=" AND (p.cate_id=".$category_id." OR (SELECT c.`parent_id` FROM `tb_category` AS c WHERE c.`id` =p.cate_id AND p.cate_id LIMIT 1) = $category_id)";
			}else{
				$where.=' AND p.cate_id='.$category_id;
			}
		}
		if($data["model"]!=""){
			$where.=' AND p.model_id='.$data["model"];
		}
		if($data["size"]!=""){
			$where.=' AND p.size_id='.$data["size"];
		}
		if($data["color"]!=""){
			$where.=' AND p.color_id='.$data["color"];
		}
		$location = $db_globle->getAccessPermission('m.`location_id`');
		return $db->fetchAll($sql.$where.$location);
			
	}
	
}

?>