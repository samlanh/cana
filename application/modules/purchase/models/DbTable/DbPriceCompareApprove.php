<?php

class Purchase_Model_DbTable_DbPriceCompareApprove extends Zend_Db_Table_Abstract
{
	protected $_name = 'tb_su_price_idcompare';
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getSu($id){
		$db = $this->getAdapter();
		$sql="SELECT s.*  FROM `tb_su_price_idcompare` AS s WHERE s.`re_id`=1";
		return $db->fetchAll($sql);
	}
	function getPrice($id,$su_id,$pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.* FROM `tb_pro_compare` AS p WHERE p.`re_id`=$id AND p.`su_id`=$su_id AND p.pro_id=$pro_id";
		//echo $sql;
		return $db->fetchRow($sql);
	}
	function getVendor(){
		$db = $this->getAdapter();
		$sql = "SELECT v.`vendor_id`,v.`v_name`  FROM `tb_vendor` AS v WHERE v.`v_name`!=''";
		return $db->fetchAll($sql);
	}
	function getProductRequestDetail($id){
		$db= $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id = p.`pro_id` LIMIT 1) AS item_name,
				  p.`qty`,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_purchase_request_detail` AS p 
				WHERE p.`pur_id` =$id";
		return $db->fetchAll($sql);
	}
	
	function getSuCompare($id){
		$db = $this->getAdapter();
		$sql = "SELECT s.*,(SELECT v.v_name FROM `tb_vendor`AS v WHERE v.`vendor_id`=s.`su_id`) AS v_name,(SELECT v.`phone_person` FROM `tb_vendor`AS v WHERE v.`vendor_id`=s.`su_id`) AS v_phone FROM `tb_su_price_idcompare`AS s WHERE s.`re_id`=$id";
		return $db->fetchAll($sql);
	}
	function getProduct($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.id,
				  p.`item_name`,
				  pc.`qty`,
				  pc.`pro_brand`,
				  pc.`pro_from`,
				  (SELECT s.code FROM `tb_su_price_idcompare` AS s WHERE s.re_id=pc.`re_id` LIMIT 1) AS c_code,
				  (SELECT s.pur_date FROM `tb_su_price_idcompare` AS s WHERE s.re_id=pc.`re_id` LIMIT 1) AS com_date,
				  (SELECT s.remark FROM `tb_price_compare_remark` AS s WHERE s.re_id=pc.`re_id` LIMIT 1) AS appr_remark,
				  (SELECT pr.re_code FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) AS re_code ,
				  (SELECT pr.`date_request` FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id`LIMIT 1) AS date_request ,
				  (SELECT pr.`number_request` FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) AS number_request ,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id = (SELECT pr.plan_id FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) LIMIT 1) AS plan ,
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id = (SELECT pr.branch_id FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) LIMIT 1) AS branch ,
				   (SELECT pr.`date_from_work_space` FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id`) AS date_from_work_space ,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id = p.`measure_id`) AS measure ,
				  (SELECT pr.remark FROM `tb_price_compare_remark` AS pr WHERE pr.re_id=pc.`re_id` AND pr.type=1) AS reject_check,
				  (SELECT pr.remark FROM `tb_price_compare_remark` AS pr WHERE pr.re_id=pc.`re_id` AND pr.type=2) AS reject_approve
				FROM
				  `tb_product` AS p,
				  `tb_pro_compare` AS pc 
				WHERE p.`id` = pc.`pro_id` AND pc.`re_id`= $id
				GROUP BY p.id";
		return $db->fetchAll($sql);
	}
	function getProCompare($id,$pro_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  p.`su_id`,
				  p.`price`,
				  p.`is_check`,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id=p.`pro_id`) AS item_name ,
				  p.qty,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_pro_compare` AS p 
				WHERE p.`re_id` =$id AND p.`pro_id`=$pro_id";
		return $db->fetchAll($sql);
	}
	function getAllCompare(){
		$db = $this->getAdapter();
		$sql ="SELECT 
				  p.id,
				  p.`re_code`,
				  p.`date_request`,
					s.`is_approve`,
				   (SELECT pl.`name` FROM `tb_sublocation` AS pl WHERE pl.`id`=p.`branch_id`) AS branch,
				  (SELECT fullname FROM `tb_acl_user` AS u WHERE u.`user_id` = p.`user_id`) AS `user` 
				FROM
				  `tb_su_price_idcompare` AS s,
				  `tb_purchase_request` AS p 
				WHERE s.`re_id` = p.`id` GROUP BY s.`re_id`";
		return $db->fetchAll($sql);
	}
	
	function add($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//echo $data["approve"];exit();
		try{
			if($data["approve"]==1){
				$pedding = 6;
				$appr_status=0;
				$is_approve =1;
				$is_edit = 0;
				$reject_count = $data["appr_reject_count"];
			}else{
				$pedding = 5;
				$appr_status=2;
				$is_approve =0;
				$is_edit = 1;
				$reject_count = $data["appr_reject_count"]+1;
			}
				$arr = array(
					'is_approve'			=>	$is_approve,
					'appr_status'			=>	$appr_status,
					'pedding'				=>	$pedding,
					'appr_user'				=>	$GetUserId,
					'appr_date'				=>  date('Y-m-d'),
					'remark'				=>	$data["remark"],
					'is_edit'				=>	$is_edit,
					'appr_reject_count'		=>	$reject_count,
				);
				$this->_name = "tb_su_price_idcompare";
				$where = "re_id=".$data["id"];
				$this->update($arr,$where);
				
				
				
			$arr_re = array(
					'pedding'		=>	$pedding,
					'appr_status'	=>	$appr_status,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["id"];
			$this->update($arr_re,$where);
			
			$rs_reject = $db_globle->getPriceCompareRejectExist($data["id"],2);
			//print_r($rs_reject);
				if(!empty($rs_reject)){
					$arr = array(
						'user_id'	=>	$GetUserId,
						'date'		=>  date('Y-m-d'),
						'remark'	=>  $data["remark"],
					);
					$this->_name = "tb_price_compare_remark";
					$where = "re_id=".$data["id"]." AND type=2";
					$this->update($arr,$where);
				}else{
					$arr = array(
						're_id'		=>	$data["id"],
						'user_id'	=>	$GetUserId,
						'date'		=>  date('Y-m-d'),
						'remark'	=>  $data["remark"],
						'type'		=>  2,
					);
					$this->_name = "tb_price_compare_remark";
					$this->insert($arr);
				}
			
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			
		}
		
	}
	
	function edit($data){
		try{
			//print_r($data);exit();
			$db=$this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user = new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$sql ="DELETE FROM `tb_su_price_idcompare` WHERE re_id	="."'".$data["id"]."'";
			$db->query($sql);
			
			$sqls ="DELETE FROM tb_pro_compare WHERE re_id="."'".$data["id"]."'";
			$db->query($sqls);
			
			for($i=1;$i<=4;$i++){
				$arr=array(
						're_id'			=> $data["id"],
						'su_id'			=> $data["su_id_".$i],
				);
				$this->insert($arr);
			}
			//$identity = $data["identity"];
			$ids=explode(',',$data['identity']);
			foreach($ids as $i){
				for($j=1;$j<=4;$j++){
					$arr_pro=array(
							're_id'		=> 	$data["id"],
							'pro_id'		=> 	$data["pro_id_".$i],
							'su_id'			=> 	$data["su_id_".$i],
							'price'			=>	$data["su_price_".$i."_".$j],
							'qty'			=>	$data["pro_qty_".$i],
							'is_check'		=>	@$data["checkbox_".$i."_".$j]
					);
					$this->_name="tb_pro_compare";
					$this->insert($arr_pro);
				}
			}
			
			$arr_re = array(
					'pedding'		=>	5,
					'appr_status'	=>	0,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["id"];
			$this->update($arr_re,$where);
			$db->commit();
			//return $GetProductId;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}

}