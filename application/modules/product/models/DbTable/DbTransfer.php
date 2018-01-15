<?php

class Product_Model_DbTable_DbTransfer extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product_transfer";
	public function setName($name)
	{
		$this->_name=$name;
	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getPrefix(){
		$id = $this->GetuserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT s.prefix FROM `tb_sublocation` AS s WHERE s.`id` =".$id["branch_id"];
		//echo $sql;
		return $db->fetchOne($sql);
	}
	public function getTransferNo(){
		$db =$this->getAdapter();
		$prefix = $this->getPrefix();
		$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "TR-";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	public function getReceiveNo(){
		$db =$this->getAdapter();
		$this->_name ="tb_recieve_transfer";
		$prefix = $this->getPrefix();
		$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "GN-";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $prefix.$pre.$new_acc_no;
	}
	
	public function getRequestTransferNo(){
		$location = $this->GetuserInfo();
		$db =$this->getAdapter();
		$prefix = $this->getPrefix();
		//$sql="SELECT id FROM `tb_request_transfer` AS t WHERE t.`cur_location`=".$location["branch_id"]." ORDER BY id DESC LIMIT 1 ";
		$sql="SELECT id FROM `tb_request_transfer` AS t ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "RQTR";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $prefix.$pre.$new_acc_no;
	}
	function getLocation($type=null){
		$id = $this->GetuserInfo();
		$db = $this->getAdapter();
		if($type==1){
			$sql = "SELECT s.id,s.`name` FROM `tb_sublocation` AS s WHERE s.`status`=1 AND s.`id` !=".$id["branch_id"];
		}else{
			$sql = "SELECT s.id,s.`name` FROM `tb_sublocation` AS s WHERE s.`status`=1 AND s.`name` !=''";
		}
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function getTransfer($data){
		$start_date = $data["start_date"];
		$end_date = $data["end_date"];
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.*,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`cur_location`) AS cur_location,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`tran_location`) AS tran_location,
  				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=p.`user_mod`) AS user_tran
				FROM
				  `tb_product_transfer` AS p 
				WHERE 1 AND p.`date` BETWEEN '$start_date' AND 'end_date'";
		$where = '';
	  	if($data["avd_search"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['avd_search']));
	  		$s_where[]= " p.tran_no LIKE '%{$s_search}%'";
	  		//$s_where[]=" p.user_mod LIKE '%{$s_search}%'";
	  		$s_where[]= " p.date LIKE '%{$s_search}%'";
	  		$s_where[]= " p.remark LIKE '%{$s_search}%'";
	  		//$s_where[]= " cate LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	if($data["status"]!=""){
	  		$where.=' AND p.status='.$data["status"];
	  	}
	  	if($data["branch"]!=""){
	  		$where.=' AND p.tran_location='.$data["branch"];
	  	}
  		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	function getProductByIds($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT m.name FROM tb_measure as m WHERE m.id=p.measure_id) as measure,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size
				FROM
				  `tb_product` AS p
				WHERE p.`id`=$id ";
		///$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchRow($sql);
	}
	
	
	function getTransferById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  p.* ,
				  (SELECT r.`tran_no` FROM `tb_request_transfer` AS r WHERE r.id=p.`re_id`) AS re_no
				FROM
				  `tb_product_transfer` AS p 
				WHERE p.id =$id";
		return $db->fetchRow($sql);
	}
	function getTransferDettail($id){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$loc_id = $result["branch_id"];
		$sql="SELECT 
				  t.* ,
				  (SELECT p.`item_name` FROM `tb_product` AS p WHERE p.id=t.`pro_id` LIMIT 1) AS item_name,
				  (SELECT  m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=t.pro_id LIMIT 1) LIMIT 1) AS measure,
				  (SELECT p.`qty` FROM `tb_prolocation` AS p WHERE p.pro_id=t.`pro_id` AND p.`location_id`=$loc_id LIMIT 1) AS qty_loc
				FROM
				  `tb_transfer_item` AS t 
				WHERE t.`tran_id` = $id";
		return $db->fetchAll($sql);
	}
	
	function getCurrentTransfer($id){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$loc_id = $result["branch_id"];
		$sql="SELECT 
				  t.`pro_id`,
				  t.`qty`,
				  p.`cur_location`,
				  p.`tran_location`,
				  p.`type`
				FROM
				  `tb_transfer_item` AS t ,
				  `tb_product_transfer` AS p
				WHERE t.`tran_id` = p.`id`AND p.id=$id";
		return $db->fetchAll($sql);
	}
	public function add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			
			$arr = array(
					'tran_no'		=>	$data["tran_num"],
					'cur_location'	=>	$result["branch_id"],
					'tran_location'	=>	$data["to_loc"],
					'type'			=>	$data["type"],
					'date'			=>	$data["tran_date"],
					'date_mod'		=>	new Zend_Date(),
					'remark'		=>	$data["remark"],
					'user_mod'		=>	$result["user_id"],
			);
			$id = $this->insert($arr);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$result["branch_id"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					if(!empty($rs_to)){
						$arr_to = array(
							'qty'	=>	$rs_to["qty"]+$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
					
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
							'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$result["branch_id"]);
						$this->update($arr_fo, $where);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	public function edit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
				
			$arr = array(
					//'tran_no'		=>	$data["tran_num"],
					//'cur_location'	=>	$result["location_id"],
					'tran_location'	=>	$data["to_loc"],
					'type'			=>	$data["type"],
					'date'			=>	$data["tran_date"],
					'date_mod'		=>	new Zend_Date(),
					'remark'		=>	$data["remark"],
					'user_mod'		=>	$result["user_id"],
			);
			$where = $db->quoteInto("id=?", $data["id"]);
			$this->update($arr, $where);
			
			
			// Update Tb_prolocation has Transfered to old qty  to old Qty
			$rs_detail = $this->getCurrentTransfer($data["id"]);
			if(!empty($rs_detail)){
				foreach ($rs_detail as $rs){
					//Update Prolocation has transfer to
					$arr_up_to = array(
						//'' 		=>		$
					);
					
					//Update Prolocation has transfer to
					$arr_up_fr = array(
					
					);
				}
			}
				
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$result["branch_id"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					if(!empty($rs_to)){
						$arr_to = array(
								'qty'	=>	$rs_to["qty"]+$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
						
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
								'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$result["branch_id"]);
						$this->update($arr_fo, $where);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	function getProductName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl 
				WHERE p.`id` = pl.`pro_id` ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchAll($sql.$location);
	}
	function getProductById($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		//echo $sql.$location;
		return $db->fetchRow($sql.$location);
	}
	
	public function addRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$date =new Zend_Date();
		//print_r($data);exit();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			
			$arr = array(
					'tran_no'		=>	$data["tran_num"],
					'cur_location'	=>	$data["from_loc"],
					'tran_location'	=>	$data["to_loc"],
					//'type'			=>	$data["type"],
					'date'			=>	date('Y-m-d'),
					'date_tran'		=>	date("Y-m-d",strtotime($data["tran_date"])),
					'remark'		=>	$data["remark"],
					'user_id'		=>	$result["user_id"],
					'appr_status'	=>	0,
					'appr_pedding'	=>	1
			);
			$this->_name="tb_request_transfer";
			$id = $this->insert($arr);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_request_transfer_item";
					$this->insert($arr_ti);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function editRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			$arr = array(
					//'tran_no'		=>	$data["tran_num"],
					'cur_location'	=>	$data["from_loc"],
					'tran_location'	=>	$data["to_loc"],
					//'type'			=>	$data["type"],
					'date'			=>	date('Y-m-d'),
					'date_tran'		=>	$data["tran_date"],
					'remark'		=>	$data["remark"],
					'user_id'		=>	$result["user_id"],
					'appr_status'	=>	0,
					'appr_pedding'	=>	1
			);
			$this->_name="tb_request_transfer";
			$where = "id=".$data["id"];
			$this->update($arr,$where);
			
			$sql ="DELETE FROM tb_request_transfer_item WHERE tran_id= "."'".$data["id"]."'";
			$db->query($sql);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$data["id"],
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_request_transfer_item";
					$this->insert($arr_ti);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	
	public function checkRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			if($data["approved_name"]==1){
				$appr_status = 0;
				$appr_pedding = 2;
			}else{
				$appr_status = 2;
				$appr_pedding = 2;
			}
			$arr = array(
				'appr_status'	=>	$appr_status,
				'appr_pedding'	=>	$appr_pedding,
				'approved_by'	=>	$result["user_id"],
			);
			
			$this->_name="tb_request_transfer";
			$where = "id=".$data["id"];
			$this->update($arr,$where);
			
			$sql = "SELECT id FROM tb_transfer_request_remark WHERE type=1 AND re_id=".$data["id"];
			$rs = $db->fetchOne($sql);
			if($rs){
				$arr_remark = array(
					'remark'	=>	$data["remark"],
					'user_id'	=>	$result["user_id"],
					'date'		=>	date("Y-m-d"),
					'type'		=>	1
				);
				$this->_name = "tb_transfer_request_remark";
				$where = "re_id=".$data["id"]." AND type=1";
				$this->update($arr_remark,$where);
			}else{
				$arr_remark = array(
					're_id'		=>	$data["id"],
					'remark'	=>	$data["remark"],
					'user_id'	=>	$result["user_id"],
					'date'		=>	date("Y-m-d"),
					'type'		=>	1
				);
				$this->_name = "tb_transfer_request_remark";
				$this->insert($arr_remark);
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function ApproveRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			if($data["approved_name"]==1){
				$appr_status = 0;
				$appr_pedding = 3;
			}else{
				$appr_status = 2;
				$appr_pedding = 3;
			}
			$arr = array(
				'appr_status'	=>	$appr_status,
				'appr_pedding'	=>	$appr_pedding,
				'approved_by'	=>	$result["user_id"],
			);
			$this->_name="tb_request_transfer";
			$where = "id=".$data["id"];
			$this->update($arr,$where);
			
			$sql = "SELECT id FROM tb_transfer_request_remark WHERE type=2 AND re_id=".$data["id"];
			$rs = $db->fetchOne($sql);
			if($rs){
				$arr_remark = array(
					'remark'	=>	$data["remark"],
					'user_id'	=>	$result["user_id"],
					'date'		=>	date("Y-m-d"),
					'type'		=>	2
				);
				$this->_name = "tb_transfer_request_remark";
				$where = "re_id=".$data["id"]." AND type=2";
				$this->update($arr_remark,$where);
			}else{
				$arr_remark = array(
					're_id'		=>	$data["id"],
					'remark'	=>	$data["remark"],
					'user_id'	=>	$result["user_id"],
					'date'		=>	date("Y-m-d"),
					'type'		=>	2
				);
				$this->_name = "tb_transfer_request_remark";
				$this->insert($arr_remark);
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function makeTransfer($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			if(!empty($data['identity'])){
				$arr = array(
						'tran_no'		=>	$data["tran_num"],
						'cur_location'	=>	$data["from_loc"],
						'tran_location'	=>	$data["to_loc"],
						're_id'			=>	$data["id"],
						//'type'			=>	$data["type"],
						'date'			=>	$data["tran_date"],
						're_date'		=>	$data["re_date"],
						'date_mod'		=>	date('Y-m-d'),
						'remark'		=>	$data["remark"],
						'user_mod'		=>	$result["user_id"],
				);
				$id = $this->insert($arr);
			
			
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
						'tran_id'		=>	$id,
						'pro_id'		=>	$data["pro_id_".$i],
						'qty'			=>	$data["qty_tran_".$i],
						'qty_request'	=>	$data["qty_request_".$i],
						'price'			=>	$data["cost_price_".$i],
						'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$data["from_loc"]);
					//print_r($rs_from);
	
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
							'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
						$this->update($arr_fo, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["from_loc"],
								'qty'				=>	"-".$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
					
					$row_product = $this->getProductForCost($data["pro_id_".$i],$data["from_loc"]);
					if(!empty($row_product)){
						//print_r($row_product);
						$amount_pro = $row_product["price"] * $row_product["qty"];
						$new_amount_pro = $data['qty_tran_'.$i] * $row_product["price"];
						$total_qty = $row_product["qty"]+$data['qty_tran_'.$i];
						$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_prolocation";
						$where=" pro_id = ".$row_product['id']." AND location_id=".$data["from_loc"];
						$this->update($arr_pro, $where);
						
						//update Product Table
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_product";
						$where=" id = ".$row_product['id'];
						$this->update($arr_pro, $where);
					}
				
				
			}
			
			$arr = array(
					'appr_status'	=>	0,
					'appr_pedding'	=>	4,
					'is_transfer'	=>	$id,
				);
				$this->_name="tb_request_transfer";
				$where = "id=".$data["id"];
				$this->update($arr,$where);
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	function getProductForCost($pro_id,$location){
		$db = $this->getAdapter();
		$sql="SELECT p.id,pl.`price`,pl.`qty` FROM `tb_product` AS p ,`tb_prolocation` AS pl WHERE p.id=pl.`pro_id` AND pl.`location_id`=$location AND p.id=$pro_id";
		return $db->fetchRow($sql);
	}
	public function editTransfer($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			if(!empty($data['identity'])){
				$arr = array(
						//'tran_no'		=>	$data["tran_num"],
						'cur_location'	=>	$data["from_loc"],
						'tran_location'	=>	$data["to_loc"],
						're_id'			=>	$data["re_id"],
						//'type'			=>	$data["type"],
						'date'			=>	$data["tran_date"],
						're_date'		=>	$data["re_date"],
						'date_mod'		=>	date('Y-m-d'),
						'remark'		=>	$data["remark"],
						'user_mod'		=>	$result["user_id"],
				);
				$where  = "id=".$data["id"];
				$this->update($arr,$where);
				
				$sql = "SELECT *,(SELECT pt.`cur_location` FROM `tb_product_transfer` AS pt WHERE pt.`id`=tran_id) AS branch_id FROM tb_transfer_item WHERE tran_id ="."'".$data["id"]."'";
				$old_qty = $db->fetchAll($sql);
				if(!empty($old_qty)){
					foreach($old_qty AS $rs){
						$rs_old = $this->getProductExist($rs["pro_id"],$rs["branch_id"]);
						
						$arr_old = array(
							'qty'	=>	$rs_old["qty"]+$rs["qty"],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$rs["pro_id"],"location_id=?"=>$rs["branch_id"]);
						$this->update($arr_old, $where);
					}
				}
			
				$sql = "DELETE FROM tb_transfer_item WHERE tran_id = "."'".$data["id"]."'";
				$db->query($sql);
			
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
						'tran_id'		=>	$data["id"],
						'pro_id'		=>	$data["pro_id_".$i],
						'qty'			=>	$data["qty_tran_".$i],
						'qty_request'	=>	$data["qty_request_".$i],
						'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$data["from_loc"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					/*if(!empty($rs_to)){
						$arr_to = array(
								'qty'	=>	$rs_to["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}*/
					
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
							'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
						$this->update($arr_fo, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["from_loc"],
								'qty'				=>	"-".$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
				
				$arr = array(
					'appr_status'	=>	0,
					'appr_pedding'	=>	4,
					'is_transfer'	=>	$data["id"],
				);
				$this->_name="tb_request_transfer";
				$where = "id=".$data["re_id"];
				$this->update($arr,$where);
			}
			}
			//exit();
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	
	
	public function ReceiveTransfer($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			if(!empty($data['identity'])){
				$arr = array(
						'receive_no'	=>	$data["receive_num"],
						'tran_id'		=>	$data["id"],
						'cu_loc'		=>	$data["to_loc"],
						'tran_loc'		=>	$data["from_loc"],
						'req_id'		=>	$data["id"],
						//'type'			=>	$data["type"],
						'date_re'		=>	$data["re_date"],
						'date'			=>	date('Y-m-d'),
						'date_tran'		=>	date('Y-m-d',strtotime($data["tran_date"])),
						'remark'		=>	$data["remark"],
						'user_id'		=>	$result["user_id"],
				);
				$this->_name = "tb_recieve_transfer";
				$id = $this->insert($arr);
			
			
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				foreach($identitys as $i)
				{
					
					$row_product = $this->getProductForCost($data["pro_id_".$i],$data["to_loc"]);
					if(!empty($row_product)){
						//print_r($row_product);
						$amount_pro = $row_product["price"] * $row_product["qty"];
						$new_amount_pro = $data['qty_receive_'.$i] * $data['price_'.$i];
						$total_qty = $row_product["qty"]+$data['qty_receive_'.$i];
						$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_prolocation";
						$where=" pro_id = ".$row_product['id']." AND location_id=".$data["to_loc"];
						$this->update($arr_pro, $where);
						
						//update Product Table
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_product";
						$where=" id = ".$row_product['id'];
						$this->update($arr_pro, $where);
					}
					
					
					$arr_ti = array(
						're_id'		=>	$id,
						'pro_id'		=>	$data["pro_id_".$i],
						're_qty'		=>	$data["qty_request_".$i],
						'tran_qty'		=>	$data["qty_tran_".$i],
						'receive_qty'	=>	$data["qty_receive_".$i],
						'price'			=>	$data["price_".$i],
						'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_recieve_transfer_item";
					$this->insert($arr_ti);
	
					//$rs_from = $this->getProductExist($data["pro_id_".$i],$result["branch_id"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					if(!empty($rs_to)){
						$arr_to = array(
								'qty'	=>	$rs_to["qty"]+$data["qty_receive_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_receive_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
					
					
			}
					$arr = array(
					'appr_status'	=>	0,
					'appr_pedding'	=>	5,
					//'is_transfer'	=>	$data["id"],
				);
				$this->_name="tb_request_transfer";
				$where = "is_transfer=".$data["id"];
				$this->update($arr,$where);
			}
			//exit();
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function editReceiveTransfer($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			if(!empty($data['identity'])){
				$arr = array(
						//'receive_no'	=>	$data["receive_num"],
						//'tran_id'		=>	$data["id"],
						'cu_loc'		=>	$result["branch_id"],
						//'tran_loc'		=>	$data["to_loc"],
						//'req_id'		=>	$data["id"],
						//'type'			=>	$data["type"],
						//'date_re'		=>	$data["re_date"],
						'date'			=>	date('Y-m-d'),
						'date_tran'		=>	date('Y-m-d',strtotime($data["tran_date"])),
						'remark'		=>	$data["remark"],
						'user_id'		=>	$result["user_id"],
				);
				$this->_name = "tb_recieve_transfer";
				$where = "id=".$data["id"];
				$this->update($arr,$where);
				
				$sql = "SELECT *,(SELECT pt.`cu_loc` FROM `tb_recieve_transfer` AS pt WHERE pt.`id`=re_id) AS branch_id FROM tb_recieve_transfer_item WHERE re_id ="."'".$data["id"]."'";
				$old_qty = $db->fetchAll($sql);
				if(!empty($old_qty)){
					foreach($old_qty AS $rs){
						$rs_old = $this->getProductExist($rs["pro_id"],$rs["branch_id"]);
						
						$arr_old = array(
							'qty'	=>	$rs_old["qty"]+$rs["qty"],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$rs["pro_id"],"location_id=?"=>$rs["branch_id"]);
						$this->update($arr_old, $where);
					}
				}
			
			
				$sql = "DELETE FROM tb_recieve_transfer_item WHERE re_id="."'".$data["id"]."'";
				$db->query($sql);
				
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
						're_id'		=>	$id,
						'pro_id'		=>	$data["pro_id_".$i],
						're_qty'		=>	$data["qty_request_".$i],
						'tran_qty'		=>	$data["qty_tran_".$i],
						'receive_qty'	=>	$data["qty_receive_".$i],
						'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_recieve_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$result["branch_id"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					/*if(!empty($rs_to)){
						$arr_to = array(
								'qty'	=>	$rs_to["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}*/
					
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
							'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$result["branch_id"]);
						$this->update($arr_fo, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	"-".$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
				
				$arr = array(
					'appr_status'	=>	1,
					'appr_pedding'	=>	5,
					'is_transfer'	=>	$id,
				);
				$this->_name="tb_request_transfer";
				$where = "id=".$data["id"];
				$this->update($arr,$where);
			}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	function getRequestTransfer($data){
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date =  date("Y-m-d",strtotime($data["end_date"]));
		$db = $this->getAdapter();
		$sql = "SELECT 
				  rt.id,
				  rt.`tran_no`,
				  rt.`date`,
				  rt.`date_tran`,
				  (SELECT p.tran_no FROM `tb_product_transfer` AS  p WHERE p.re_id=rt.id limit 1) AS transfer_no,
				  (SELECT p.date FROM `tb_product_transfer` AS  p WHERE p.re_id=rt.id limit 1) AS transfer_date,
				  (SELECT r.`receive_no` FROM `tb_recieve_transfer` AS r WHERE r.`tran_id`=rt.`is_transfer` limit 1) AS receive_no,
				  (SELECT r.`date` FROM `tb_recieve_transfer` AS r WHERE r.`tran_id`=rt.`is_transfer` limit 1) AS receive_date,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`cur_location` LIMIT 1) AS re_tran ,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`tran_location` LIMIT 1) AS to_tran ,
				  rt.`remark`,
				  rt.`is_approved`,
				  rt.`approved_by`,
				  rt.status,
				  rt.is_transfer,rt.appr_status as ap_status,
				  rt.appr_pedding AS ap_pedding,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = rt.`appr_status` AND v.type=7 LIMIT 1) AS appr_status,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = rt.`appr_pedding` AND v.type=13 LIMIT 1) AS appr_pedding,
				  (SELECT u.`fullname` FROM  `tb_acl_user` AS u WHERE u.`user_id`=rt.`user_id`) AS `user`
				FROM
				  `tb_request_transfer` AS rt WHERE 1 AND rt.`date` BETWEEN '$start_date' AND '$end_date'";
		$where = '';
	  	if($data["avd_search"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['avd_search']));
	  		$s_where[]= " rt.tran_no LIKE '%{$s_search}%'";
	  		//$s_where[]=" p.user_mod LIKE '%{$s_search}%'";
	  		$s_where[]= " rt.date LIKE '%{$s_search}%'";
	  		$s_where[]= " rt.remark LIKE '%{$s_search}%'";
	  		//$s_where[]= " cate LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	/*if($data["status"]!=""){
	  		$where.=' AND rt.status='.$data["status"];
	  	}*/
	  	if($data["branch"]!=-1){
	  		$where.=' AND rt.cur_location='.$data["branch"];
	  	}
		
  		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	
	function getReqTransferById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  t.id,
				  t.`cur_location`,
				  t.`tran_location`,
				  (SELECT `name` FROM `tb_sublocation` AS s WHERE s.`id`=t.`cur_location` LIMIT 1) AS re_from,
				  (SELECT `name` FROM `tb_sublocation` AS s WHERE s.`id`=t.`tran_location` LIMIT 1) AS re_to,
				  t.`tran_no`,
				  t.`date_tran` AS re_date,
				  t.`remark`,
				  t.`status` ,
				  (SELECT tr.remark FROM `tb_transfer_request_remark` AS tr WHERE tr.re_id=t.id AND tr.type=1 LIMIT 1) AS reason,
				  (SELECT tr.remark FROM `tb_transfer_request_remark` AS tr WHERE tr.re_id=t.id AND tr.type=2 LIMIT 1) AS approve_reason
				FROM
				  `tb_request_transfer` AS t 
				WHERE t.`id` =$id";
		return $db->fetchRow($sql);
	}
	function getReqTransferDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  t.`pro_id`,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id = t.`pro_id` LIMIT 1) AS item_name,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.id = t.`pro_id` LIMIT 1) AS item_code,
				 (SELECT m.name FROM tb_measure AS m WHERE m.id=(SELECT p.measure_id FROM tb_product AS p WHERE p.id=t.`pro_id`)) AS measure,
				  t.`qty`,
				  t.`remark` ,
					(SELECT pl.price FROM tb_prolocation AS pl WHERE pl.pro_id=t.pro_id AND  pl.location_id=(SELECT rt.tran_location FROM tb_request_transfer AS rt WHERE rt.id=t.tran_id LIMIT 1 )) AS price,
				  (SELECT tr.remark FROM `tb_transfer_request_remark` AS tr WHERE tr.re_id=t.tran_id AND tr.type=1 LIMIT 1) AS reason,
				  (SELECT tr.remark FROM `tb_transfer_request_remark` AS tr WHERE tr.re_id=t.tran_id AND tr.type=2 LIMIT 1) AS approve_reason
				FROM
				  `tb_request_transfer_item` AS t 
				WHERE t.`tran_id` = $id";
		return $db->fetchAll($sql);
	}
	
	function getProductExist($pro_id,$loc_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.`pro_id`,pl.`price`,pl.`qty`,pl.`location_id` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=$pro_id AND pl.`location_id`=$loc_id";
		return $db->fetchRow($sql);
	}
	function getRequestTransferPrint($id){
		$db = $this->getAdapter();
		$sql ="SELECT 
  t.`tran_no` ,
  t.`date_tran`,
  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=t.`cur_location`) AS cu_branch,
  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=t.`tran_location`) AS to_branch,
  (SELECT  u.username FROM tb_acl_user AS u WHERE u.user_id = t.`user_id`) AS user_name,
  rt.*,
  (SELECT p.item_name FROM `tb_product` AS p WHERE p.`id`=rt.`pro_id`) AS item_name,
(SELECT p.item_code FROM `tb_product` AS p WHERE p.`id`=rt.`pro_id`) AS item_code,
(SELECT m.name FROM `tb_measure` AS m WHERE m.id =(SELECT p.measure_id FROM `tb_product` AS p WHERE p.`id`=rt.`pro_id`)) AS measur
FROM
  `tb_request_transfer` AS t,
  `tb_request_transfer_item` AS rt 
WHERE t.id = rt.`tran_id`  AND t.id=$id";
return $db->fetchAll($sql);
	}
	function getRequestTransferById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  rt.id,
				  rt.`tran_no`,
				  (SELECT r.`tran_no` FROM `tb_request_transfer` AS r WHERE r.id=rt.`re_id`) AS req_no,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.`id`=ri.`pro_id`) AS item_name,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.`id`=ri.`pro_id`) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id =(SELECT p.measure_id FROM `tb_product` AS p WHERE p.`id`=ri.`pro_id`)) AS measur,
				  (SELECT c.`name` FROM `tb_category` AS c WHERE c.id=(SELECT pr.cate_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_request_transfer_item` AS r WHERE r.`tran_id`=rt.`id` LIMIT 1)) LIMIT 1) AS TYPE,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_request_transfer_item` AS r WHERE r.`tran_id`=rt.`id` LIMIT 1) AND v.type=3) LIMIT 1) AS size,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_request_transfer_item` AS r WHERE r.`tran_id`=rt.`id` LIMIT 1) AND v.type=2) LIMIT 1) AS model,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_request_transfer_item` AS r WHERE r.`tran_id`=rt.`id` LIMIT 1) AND v.type=4) LIMIT 1) AS color,
				  rt.`date`,
				  rt.`date` as date_tran ,
				  rt.`re_date`,
				  rt.`remark`,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=rt.`cur_location`) AS cu_branch,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=rt.`tran_location`) AS to_branch,
				  (SELECT  u.username FROM tb_acl_user AS u WHERE u.user_id = rt.`user_mod`) AS user_name,
				  ri.`qty`,
				  ri.`qty_request`,
				  ri.`remark`
				FROM
				  `tb_product_transfer` AS rt,`tb_transfer_item` AS ri
				WHERE rt.id=ri.`tran_id` AND rt.`id`=$id";
		return $db->fetchAll($sql);
	}
	
	function getReceiveById($id){
		$db = $this->getAdapter();
		$sql ="SELECT 
				  rt.id,
				  rt.`receive_no`,
				  (SELECT p.`tran_no` FROM `tb_product_transfer` AS p WHERE p.id=rt.`tran_id`) AS tran_no,
				  (SELECT `tran_no` FROM `tb_request_transfer` AS r WHERE r.id=(SELECT p.re_id FROM  `tb_product_transfer` AS p WHERE p.id=rt.`tran_id`)) AS req_no,
				  (SELECT `date` FROM `tb_request_transfer` AS r WHERE r.id=(SELECT p.re_id FROM  `tb_product_transfer` AS p WHERE p.id=rt.`tran_id`)) AS req_date,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.`id`=ri.`pro_id`) AS item_name,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.`id`=ri.`pro_id`) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id =(SELECT p.measure_id FROM `tb_product` AS p WHERE p.`id`=ri.`pro_id`)) AS measur,
				  (SELECT c.`name` FROM `tb_category` AS c WHERE c.id=(SELECT pr.cate_id FROM `tb_product` AS pr WHERE pr.id=ri.`pro_id`) LIMIT 1) AS TYPE,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=ri.`pro_id` AND v.type=3) LIMIT 1) AS size,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=ri.`pro_id` AND v.type=2) LIMIT 1) AS model,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=ri.`pro_id` AND v.type=4) LIMIT 1) AS color,
				  rt.`date`,
				  rt.`remark`,
				  rt.`date_tran`,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=rt.`cu_loc`) AS cu_branch,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=rt.`tran_loc`) AS from_branch,
				  (SELECT  u.username FROM tb_acl_user AS u WHERE u.user_id = rt.`user_id`) AS user_name,
				  ri.`receive_qty`,
				  ri.`re_qty`,
				  ri.`tran_qty`,
				  ri.`remark`
				FROM
				  `tb_recieve_transfer` AS rt,`tb_recieve_transfer_item` AS ri
				WHERE rt.id=ri.`re_id` AND rt.`tran_id`=$id";
				return $db->fetchAll($sql);
	}
}