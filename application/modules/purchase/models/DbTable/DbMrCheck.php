<?php

class Purchase_Model_DbTable_DbMrCheck extends Zend_Db_Table_Abstract
{
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	function getRequestDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id=p.`pro_id`) AS item_name,
				  (SELECT i.item_code FROM `tb_product` AS i WHERE i.id=p.`pro_id`) AS item_code,
				  p.`qty`
				   
				FROM
				  `tb_purchase_request_detail` AS p 
				WHERE p.`pur_id` =$id";
		return $db->fetchAll($sql);
	}
	function getAllRequest($search){
		$db = $this->getAdapter();
		$start_date = date("Y-m-d",strtotime($search['start_date']));
		$end_date = date("Y-m-d",strtotime($search['end_date']));
		$sql = "SELECT 
				  p.`id`,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`branch_id`) AS branch,
				  p.`re_code`,
				  p.`date_request`,
				  p.`status` ,
				  p.`pedding` AS pedding_stat,
				  p.`appr_status` AS appr_stat,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`status` AND v.type=5) AS `pstatus`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`appr_status` AND v.type=12) AS `app_status`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code = p.`pedding` AND v.type=11) AS `pedding`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name
				FROM
				  `tb_purchase_request` AS p WHERE p.date_request BETWEEN '$start_date' AND '$end_date'";
		
		$where ='';
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " re_code LIKE '%{$s_search}%'";
			$s_where[] = " id LIKE '%{$s_search}%'";
			$s_where[] = " status LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		/*if($search['purchase_status']>0){
			$where .= " AND purchase_status =".$search['purchase_status'];
		}*/
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
				  p.`date_from_work_space`,
				  p.`number_request`,
				  p.check_reject_count,
				   p.`appr_remark`,
				  (SELECT `name` FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				  p.`status`,
				  p.`branch_id`,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`branch_id`) AS branch,
				  p.`remark`,
				  (SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id=p.`appr_user`) AS appr_name,
				  (SELECT pr.remark FROM `tb_purchase_request_remark` AS pr WHERE pr.re_id=p.`id` AND pr.type=1 limit 1) AS reject_check,
				  (SELECT pr.remark FROM `tb_purchase_request_remark` AS pr WHERE pr.re_id=p.`id` AND pr.type=2 limit 1) AS reject_approve
				FROM
				  `tb_purchase_request` AS p 
				WHERE p.`id` =$id";
		return $db->fetchRow($sql);
	}
	
	function  add($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		
		try{
			if($data["approve"]==1){
				$pedding = 3;
				$appr_status=0;
				$re_edit = 1;
				$reject_count = $data["check_reject_count"];
			}else{
				$pedding = 1;
				$appr_status=2;
				$re_edit = 0;
				$reject_count = $data["check_reject_count"]+1;
			}
				$arr = array(
					'pedding'				=>	$pedding,
					'appr_status'			=>	$appr_status,
					'check_user'			=>	$GetUserId,
					'check_date'			=>  date('Y-m-d'),
					'appr_remark'			=>  $data["remark"],
					're_edit'				=>	$re_edit,
					'check_reject_count'	=>	$reject_count,
				);
				$this->_name = "tb_purchase_request";
				$where = "id=".$data["id"];
				$this->update($arr,$where);
				
				$rs_reject = $db_globle->getRejectExist($data["id"],1);
				if(!empty($rs_reject)){
					$arr = array(
						'user_id'	=>	$GetUserId,
						'date'		=>  date('Y-m-d'),
						'remark'	=>  $data["remark"],
					);
					$this->_name = "tb_purchase_request_remark";
					$where = "re_id=".$data["id"]." AND type=1";
					$this->update($arr,$where);
				}else{
					$arr = array(
						're_id'		=>	$data["id"],
						'user_id'	=>	$GetUserId,
						'date'		=>  date('Y-m-d'),
						'remark'	=>  $data["remark"],
						'type'		=>  1,
					);
					$this->_name = "tb_purchase_request_remark";
					$this->insert($arr);
				}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			
		}
	}
	
	function  edit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);exit();
		$identity = $data["identity"];
		$ids=explode(',',$data['identity']);
		try{
			if($identity!=""){
				
				$arr = array(
					//'re_code'		=>	$data["request_no"],
					'branch_id'		=>	$data["branch"],
					'date_request'	=>	date("Y-m-d"),
					'user_id'		=>	$GetUserId,
					'status'		=>	$data["status"],
					'pedding'		=>	1,
					'remark'		=>	$data["remark"],
				);
				$this->_name = "tb_purchase_request";
				$where = "id=".$data["id"];
				$id = $this->update($arr,$where);
				
				$sql = "DELETE FROM tb_purchase_request_detail WHERE pur_id="."'".$data["id"]."'";
				$db->query($sql);
				
				foreach($ids as $i){
					$orderdata = array(
						'pur_id'		=>	$id,
						"pro_id"      	=> 	$data['item_id_'.$i],
						"qty"     		=> 	$data["qty_".$i],
						"remark"       	=> 	$data["remark_".$i],
					);
					
					$this->_name='tb_purchase_request_detail';
					$recieved_order = $this->insert($orderdata);
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			}
	}
	
	public function getProductOption(){
		$db = $this->getAdapter();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$sql_cate = 'SELECT `id`,name FROM tb_category WHERE status = 1 AND name!="" ORDER BY name ';
		
		$row_cate = $db->fetchAll($sql_cate);
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$option="";		
		if($result["level"]==1 OR $result["level"]==2){
			$option .= '<option value="-1">Please Select Product</option>';
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