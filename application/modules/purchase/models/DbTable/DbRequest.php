<?php

class Purchase_Model_DbTable_DbRequest extends Zend_Db_Table_Abstract
{
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getProductName($id){
		$db = $this->getAdapter();
// 		$sql = "SELECT p.id,p.`item_code`,p.`item_name`,
// 		(SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure
// 		 FROM `tb_product` AS p WHERE p.id=$id";
		
		$sql = "SELECT
		p.`id`,
		p.`item_name` ,
		p.`item_code`,
		(SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
		(SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id` LIMIT 1) AS brand,
		(SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id` LIMIT 1 ) AS category,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id` LIMIT 1 ) AS model,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id` LIMIT 1 ) AS color,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id` LIMIT 1) AS size,
		pl.`qty`
		FROM
		`tb_product` AS p,
		`tb_prolocation` AS pl
		WHERE p.`id` = pl.`pro_id` AND p.`id`=$id";
		
		return $db->fetchRow($sql);
	}
	function getRequestCode($location){
		$db = $this->getAdapter();
		$user = $this->GetuserInfo();
		//$location = $user['branch_id'];
		
		$sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
		$prefix = $db->fetchOne($sql_pre);
		
		$sql="SELECT r.id FROM `tb_purchase_request` AS r WHERE r.`branch_id`=$location ORDER BY r.`id` DESC LIMIT 1";
		$num = $db->fetchOne($sql);
		
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = "MR-";
		for($i=$num_lentgh;$i<5;$i++){
			$pre.="0";
		}
		return $pre.$num;
	}
	function getRequestDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.*,
				  (SELECT pr.item_code FROM `tb_product` AS pr WHERE pr.id=p.`pro_id` LIMIT 1) AS item_code,
				  (SELECT pr.item_name FROM `tb_product` AS pr WHERE pr.id=p.`pro_id` LIMIT 1) AS item_name,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pr.measure_id FROM `tb_product` AS pr WHERE pr.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure 
				FROM
				  `tb_purchase_request_detail` AS p 
				WHERE p.`pur_id` = $id";
		return $db->fetchAll($sql);
	}
	function getAllRequest($search){
		$start_date = date("Y-m-d",strtotime($search['start_date']));
		$end_date = date("Y-m-d",strtotime($search['end_date']));
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`branch_id`) AS branch,
				  p.`re_code`,
				  p.`date_request`,
				  p.`status` ,
				  p.re_edit,
				  p.`date_from_work_space`,
				  p.`number_request`,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				  p.`pedding` AS pedding_stat,
				  p.`appr_status` AS appr_stat,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`status` AND v.type=5) AS `pstatus`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`appr_status` AND v.type=12) AS `app_status`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`pedding` AND v.type=11) AS `pedding`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name,
				  (SELECT s.is_edit FROM `tb_su_price_idcompare` AS s WHERE s.re_id=p.id LIMIT 1) AS is_edit
				FROM
				  `tb_purchase_request` AS p WHERE p.date_request BETWEEN '$start_date' AND '$end_date'";
		
		$where ='';
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.re_code LIKE '%{$s_search}%'";
			$s_where[] = " p.number_request LIKE '%{$s_search}%'";
			$s_where[] = " p.status LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['po_pedding']!=""){
			$where .= " AND p.pedding =".$search['po_pedding'];
		}
		if($search['branch']>0){
			$where .= " AND p.branch_id =".$search['branch'];
		}
		if($search['plan']>0){
			$where .= " AND p.`plan_id` =".$search['plan'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
 		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getRequestById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`date_request`,
				  p.`re_code`,
				  p.`status`,
				  p.`branch_id`,
				  p.`remark`,
				  p.`number_request`,
				  p.`date_from_work_space`,
				  p.`plan_id`,
				  (SELECT pr.remark FROM `tb_purchase_request_remark` AS pr WHERE pr.re_id=p.`id` AND pr.type=1) AS reject_check,
				  (SELECT pr.remark FROM `tb_purchase_request_remark` AS pr WHERE pr.re_id=p.`id` AND pr.type=2) AS reject_approve
				FROM
				  `tb_purchase_request` AS p 
				WHERE p.`id` =$id";
		return $db->fetchRow($sql);
	}
	
	function  add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);exit();
		$identity = $data["identity"];
		$ids=explode(',',$data['identity']);
		try{
			$sql = "SELECT p.id  FROM `tb_purchase_request` AS p WHERE p.`re_code`='".$data["request_no"]."'";
			$exist_code = $db->fetchOne($sql);
			$new_code = $this->getRequestCode($data["branch"]);
			if(!empty($exist_code)){
				$code = $new_code;
			}else{
				$code = $data["request_no"];
			}
			if($identity!=""){
				$arr = array(
					're_code'				=>	$code,
					'plan_id'				=>	$data["plan"],
					'number_request'		=>	$data["number_work_space"],
					'date_from_work_space'	=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					'date_request'			=>	date("Y-m-d",strtotime($data["date_request"])),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pedding'				=>	1,
					'appr_status'			=>	0,
					're_edit'				=>	0,
					'remark'				=>	$data["remark"],
				);
				$this->_name = "tb_purchase_request";
				$id = $this->insert($arr);
				
				$arr_his = array(
					're_id'					=>	$id,
					're_code'				=>	$data["request_no"],
					'plan_id'				=>	$data["plan"],
					'number_request'		=>	$data["number_work_space"],
					'date_from_work_space'	=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					'date_request'			=>	date("Y-m-d",strtotime($data["date_request"])),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pedding'				=>	1,
					'appr_status'			=>	0,
					're_edit'				=>	0,
					'remark'				=>	$data["remark"],
					'type'					=>	1,
					'date_time'				=>	new Zend_Date(),
				);
				$this->_name = "tb_purchase_request_history";
				$id_his = $this->insert($arr_his);
				
				foreach($ids as $i){
					$orderdata = array(
						'pur_id'		=>	$id,
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
						"remark"       	=> 	$data["remark_".$i],
					);
					
					$this->_name='tb_purchase_request_detail';
					$recieved_order = $this->insert($orderdata);
					
					$orderdata_his = array(
						're_id'			=>	$id_his,
						'pur_id'		=>	$id,
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						"old_qty"     		=> 	$data["qty_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
						"remark"       	=> 	$data["remark_".$i],
					);
					
					$this->_name='tb_purchase_request_history_detail';
					$this->insert($orderdata_his);
				}
			}
			$db->commit();
			return $id;
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			}
	}
	
	function  edit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		$identity = $data["identity"];
		$ids=explode(',',$data['identity']);
		try{
			if($identity!=""){
				
				$arr = array(
					//'re_code'				=>	$data["request_no"],
					'plan_id'				=>	$data["plan"],
					'number_request'		=>	$data["number_work_space"],
					'date_from_work_space'	=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					'date_request'			=>	date("Y-m-d",strtotime($data["date_request"])),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pedding'				=>	1,
					'appr_status'			=>	0,
					're_edit'				=>	0,
					'remark'				=>	$data["remark"],
				);
				$this->_name = "tb_purchase_request";
				$where = "id=".$data["id"];
				$this->update($arr,$where);
				
				$arr_his = array(
					're_id'					=>	$data["id"],
					're_code'				=>	$data["request_no"],
					'plan_id'				=>	$data["plan"],
					'number_request'		=>	$data["number_work_space"],
					'date_from_work_space'	=>	date("Y-m-d",strtotime($data["date_request_work_space"])),
					'branch_id'				=>	$data["branch"],
					'date_request'			=>	date("Y-m-d",strtotime($data["date_request"])),
					'user_id'				=>	$GetUserId,
					'status'				=>	$data["status"],
					'pedding'				=>	1,
					'appr_status'			=>	0,
					're_edit'				=>	0,
					'remark'				=>	$data["remark"],
					'type'					=>	2,
					'date_time'				=>	new Zend_Date(),
				);
				$this->_name = "tb_purchase_request_history";
				$id_his = $this->insert($arr_his);
				
				$sql = "DELETE FROM tb_purchase_request_detail WHERE pur_id="."'".$data["id"]."'";
				$db->query($sql);
				
				foreach($ids as $i){
					$orderdata = array(
						'pur_id'		=>	$data["id"],
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						"remark"       	=> 	$data["remark_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
					);
					
					$this->_name='tb_purchase_request_detail';
					$recieved_order = $this->insert($orderdata);
					if(empty($data["old_qty_".$i])){
						$data["old_qty_".$i]=0;
					}
					$orderdata_his = array(
						're_id'			=>	$id_his,
						'pur_id'		=>	$data["id"],
						"pro_id"      	=> 	$data['item_id_'.$i],
						"old_qty"     	=> 	$data["old_qty_".$i],
						"qty"     		=> 	$data["qty_".$i],
						"date_in"     	=> 	date("Y-m-d",strtotime($data["date_in_".$i])),
						"remark"       	=> 	$data["remark_".$i],
						
					);
					
					$this->_name='tb_purchase_request_history_detail';
					$this->insert($orderdata_his);
				}
			}
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			}
	}
	
	public function getProductOption(){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
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
			if($result["level"]==1 OR $result["level"]==2){
				$sql = "SELECT id,item_name,
				(SELECT tb_brand.name FROM `tb_brand` WHERE tb_brand.id=brand_id limit 1) As brand_name,
				item_code FROM tb_product WHERE cate_id = ".$cate['id']." 
						AND item_name!='' ORDER BY id DESC ";
			}else{
				$sql = " SELECT p.id,p.item_name,p.item_code,
				(SELECT tb_brand.name FROM `tb_brand` WHERE tb_brand.id=p.brand_id limit 1) As brand_name
				 FROM tb_product AS p
				INNER JOIN tb_prolocation As pl ON p.id = pl.pro_id
				WHERE p.cate_id = ".$cate['id']."
				AND p.item_name!='' AND pl.location_id =".$result['branch_id']." ORDER BY user_id DESC ";
			}
				$rows = $db->fetchAll($sql);
				if($rows){
					foreach($rows as $value){
						$option .= '<option value="'.$value['id'].'" label="'.htmlspecialchars($value['item_name']." ".$value['brand_name'], ENT_QUOTES).'">'.
							htmlspecialchars($value['item_name']." ".$value['brand_name'], ENT_QUOTES)." ".htmlspecialchars($value['item_code'], ENT_QUOTES)
						.'</option>';
					}
				}
			$option.="</optgroup>";
		}
		
		return $option;
	}
}