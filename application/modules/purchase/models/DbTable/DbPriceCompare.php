<?php

class Purchase_Model_DbTable_DbPriceCompare extends Zend_Db_Table_Abstract
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
	function integerToRoman($integer)
	{
		 // Convert the integer into an integer (just to make sure)
		 $integer = intval($integer);
		 $result = '';
		 
		 // Create a lookup array that contains all of the Roman numerals.
		 $lookup = array('M' => 1000,
		 'CM' => 900,
		 'D' => 500,
		 'CD' => 400,
		 'C' => 100,
		 'XC' => 90,
		 'L' => 50,
		 'XL' => 40,
		 'X' => 10,
		 'IX' => 9,
		 'V' => 5,
		 'IV' => 4,
		 'I' => 1);
		 
		 foreach($lookup as $roman => $value){
		  // Determine the number of matches
		  $matches = intval($integer/$value);
		 
		  // Add the same number of characters to the string
		  $result .= str_repeat($roman,$matches);
		 
		  // Set the integer to be the remainder of the integer and the value
		  $integer = $integer % $value;
		 }
		 
		 // The Roman numeral should be built, return it
		 return $result;
	}
	function getPriceCode($location){
		$db = $this->getAdapter();
		$user = $this->GetuserInfo();
		//$location = $user['branch_id'];
		
		$sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
		$prefix = $db->fetchOne($sql_pre);
		
		$sql = "SELECT s.id FROM `tb_su_price_idcompare` AS s GROUP BY s.`re_id`";
		$num = count($db->fetchAll($sql));
		
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = "PCL-";
		for($i=$num_lentgh;$i<5;$i++){
			$pre.="0";
		}
		return $pre.$num;
		
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
		$sql = "SELECT v.`vendor_id`,v.`v_name`  FROM `tb_vendor` AS v WHERE v.`v_name`!='' AND status= 1 ";
		return $db->fetchAll($sql);
	}
	function getProductRequestDetail($id){
		$db= $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  pr.`re_code`,
				  pr.`date_from_work_space`,
				  pr.`date_request`,
				  pr.`number_request`,
				  pr.branch_id,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id=pr.`plan_id`) AS plan,
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=pr.`branch_id`) AS branch,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id = p.`pro_id` LIMIT 1) AS item_name,
				  p.`qty`,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_purchase_request_detail` AS p ,
				  `tb_purchase_request` AS pr
				  
				WHERE pr.id=p.`pur_id` AND p.`pur_id` =$id";
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
				   p.`pro_brand`,
				  p.`pro_from`,
				  p.`is_check`,
				  p.vat,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id=p.`pro_id`) AS item_name ,
				  p.qty,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_pro_compare` AS p 
				WHERE p.`re_id` =$id AND p.`pro_id`=$pro_id";
		return $db->fetchAll($sql);
	}
	function getAllCompare($search){
		$db = $this->getAdapter();
		$start_date = date("Y-m-d",strtotime($search['start_date']));
		$end_date = date("Y-m-d",strtotime($search['end_date']));
		$sql ="SELECT 
				  p.id,
				  p.`re_code`,
				  s.`code`,
				  p.`date_request`,
				  p.`date_from_work_space`,
				  p.`number_request`,
				  s.`pur_date`,
				  s.pedding as pedding_stat,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
				s.`is_approve`,
				   (SELECT pl.`name` FROM `tb_sublocation` AS pl WHERE pl.`id`=p.`branch_id`) AS branch,
				  (SELECT fullname FROM `tb_acl_user` AS u WHERE u.`user_id` = p.`user_id`) AS `user`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code=s.`appr_status` AND v.type=12) AS app_status, 
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code=s.`pedding` AND v.type=11) AS pedding,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code=s.`status` AND v.type=5) AS  `status`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name
				FROM
				  `tb_su_price_idcompare` AS s,
				  `tb_purchase_request` AS p 
				WHERE s.`re_id` = p.`id` AND p.date_request BETWEEN '$start_date' AND '$end_date'";
		$where ='';
		$groupby = " GROUP BY s.`re_id`";
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.re_code LIKE '%{$s_search}%'";
			$s_where[] = " p.number_request LIKE '%{$s_search}%'";
			$s_where[] = " p.status LIKE '%{$s_search}%'";
			$s_where[] = " s.code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['po_pedding']>0){
			$where .= " AND p.pedding =".$search['po_pedding'];
		}
		if($search['suppliyer_id']>0){
			$where .= " AND s.su_id =".$search['suppliyer_id'];
		}
		if(!empty($search['branch'])){
			$where .= " AND p.branch_id =".$search['branch'];
		}
		if(!empty($search['plan'])){
			$where .= " AND p.`plan_id` =".$search['plan'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY  s.`is_approve` ASC, id DESC ";
		//echo $sql.$where.$groupby;
		return $db->fetchAll($sql.$where.$groupby.$order);
	}
	
	function add($data){
		try{
			$sql = "SELECT s.`id` FROM `tb_su_price_idcompare` AS s WHERE s.`code`='".$data["c_code"]."'";
			$db=$this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user = new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			for($i=1;$i<=4;$i++){
				$arr=array(
						're_id'			=> $data["id"],
						'code'			=>	$data["c_code"],
						'su_id'			=> $data["su_id_".$i],
						'is_vat'		=>	@$data["vat_".$i],
						'is_approve'	=>	0,
						'appr_status'	=> 	0,
						'pedding'		=>	4,
						'pur_date'		=>	date("Y-m-d"),
						'is_edit'		=>	1,
						'remark'		=>	$data["note"]
				);
				$this->insert($arr);
			}
			//$identity = $data["identity"];
			$ids=explode(',',$data['identity']);
			foreach($ids as $i){
				for($j=1;$j<=4;$j++){
					if(@$data["vat_".$j]!=""){
						$is_vat = 10;
					}else{
						$is_vat = 0;
					}
					$arr_pro=array(
							're_id'			=> 	$data["id"],
							'pro_id'		=> 	$data["pro_id_".$i],
							'su_id'			=> 	$data["su_id_".$j],
							'price'			=>	$data["su_price_".$i."_".$j],
							'qty'			=>	$data["pro_qty_".$i],
							'pro_from'		=>	$data["pro_from_".$i."_".$j],
							'pro_brand'		=>	$data["brand_".$i."_".$j],
							'is_check'		=>	@$data["checkbox_".$i."_".$j],
							'vat'			=>	$is_vat,
					);
					$this->_name="tb_pro_compare";
					$this->insert($arr_pro);
				}
			}
			$arr_remark = array(
					're_id'			=> 	$data["id"],
					'remark'	=>	'',
				);
			$this->_name = "tb_price_compare_remark";
			$this->insert($arr_remark);
			
			$arr_re = array(
					'pedding'		=>	4,
					'appr_status'	=>	0,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["id"];
			$this->update($arr_re,$where);
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
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
						'code'			=>	$data["c_code"],
						'is_vat'		=>	@$data["vat_".$i],
						'is_approve'	=>	0,
						'appr_status'	=> 	0,
						'pedding'		=>	4,
						'pur_date'		=>	date("Y-m-d"),
						'is_edit'		=>	1,
						'remark'		=>	$data["note"]
				);
				$this->insert($arr);
			}
			//$identity = $data["identity"];
			$ids=explode(',',$data['identity']);
			foreach($ids as $i){
				for($j=1;$j<=4;$j++){
					if(@$data["vat_".$j]!=""){
						$is_vat = 10;
					}else{
						$is_vat = 0;
					}
					$arr_pro=array(
							're_id'			=> 	$data["id"],
							'pro_id'		=> 	$data["pro_id_".$i],
							'su_id'			=> 	$data["su_id_".$j],
							'price'			=>	$data["su_price_".$i."_".$j],
							'qty'			=>	$data["pro_qty_".$i],
							'pro_from'		=>	$data["pro_from_".$i."_".$j],
							'pro_brand'		=>	$data["brand_".$i."_".$j],
							'is_check'		=>	@$data["checkbox_".$i."_".$j],
							'vat'			=>	$is_vat,
					);
					$this->_name="tb_pro_compare";
					$this->insert($arr_pro);
				}
			}
			$sql = "SELECT re_id FROM tb_price_compare_remark WHERE re_id="."'".$data["id"]."'";
			$rs = $db->fetchOne($sql);
			if(empty($rs)){
				$arr_remark = array(
						're_id'			=> 	$data["id"],
						'remark'	=>	'',
					);
				$this->_name = "tb_price_compare_remark";
				$this->insert($arr_remark);
			}
			
			$arr_re = array(
					'pedding'		=>	4,
					'appr_status'	=>	0,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["id"];
			$this->update($arr_re,$where);
			
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}

}