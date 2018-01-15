<?php

class Product_Model_DbTable_DbLocation extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_sublocation";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$part= PUBLIC_PATH.'/images/logo/';
		$photo = $_FILES['logo'];
			if($photo["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = $data["branch_name"]. '.' . end($temp);
				move_uploaded_file($_FILES['logo']["tmp_name"], $part . $newfilename);
				$photo_name = $newfilename;
			}
			
			
		$arr = array(
			'name'				=>	$data["branch_name"],
			'prefix'			=>	$data["prefix"],
			'title_report_en'	=>	$data["title_en"],
			'title_report_kh'	=>	$data["title_kh"],
			'logo'				=>	$photo_name,
			'user_id'			=>	$this->getUserId(),
			'last_mod_date'		=>	new Zend_Date(),
			'status'			=>	$data["status"],
			'remark'			=>	$data["remark"],
		);
		$this->_name = "tb_location";
		return 11;//$this->insert($arr);
		
	}
	public function addBranchAjax($data){
		$db = $this->getAdapter();
// 		$part= PUBLIC_PATH.'/images/logo/';
// 		$photo = $_FILES['logo'];
// 		if($photo["name"]!=""){
// 			$temp = explode(".", $photo["name"]);
// 			$newfilename = $data["branch_name"]. '.' . end($temp);
// 			move_uploaded_file($_FILES['logo']["tmp_name"], $part . $newfilename);
// 			$photo_name = $newfilename;
// 		}
		$photo_name='';
			
		$arr = array(
				'name'				=>	$data["branch_name"],
				'prefix'			=>	$data["prefix"],
				'title_report_en'	=>	$data["title_en"],
				'title_report_kh'	=>	$data["title_kh"],
				'logo'				=>	$photo_name,
				'user_id'			=>	$this->getUserId(),
				'last_mod_date'		=>	new Zend_Date(),
				'status'			=>	$data["status"],
				'remark'			=>	$data["remark"],
		);
		$this->_name = "tb_location";
		return $this->insert($arr);
	
	}
	
	public function edit($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		try{
			$part= PUBLIC_PATH.'/images/logo/';
			$photo = $_FILES['logo'];
				if($photo["name"]!=""){
					$temp = explode(".", $photo["name"]);
					$newfilename = $data["branch_name"]. '.' . end($temp);
					move_uploaded_file($_FILES['logo']["tmp_name"], $part . $newfilename);
					$photo_name = $newfilename;
				}else{
					$photo_name = $data["old_photo"];
				}
				
			$arr = array(
				'name'				=>	$data["branch_name"],
				'prefix'			=>	$data["prefix"],
				'title_report_en'	=>	$data["title_en"],
				'title_report_kh'	=>	$data["title_kh"],
				'logo'				=>	$photo_name,
				'user_id'			=>	$this->getUserId(),
				'last_mod_date'		=>	new Zend_Date(),
				'status'			=>	$data["status"],
				'remark'			=>	$data["remark"],
			);
			$this->_name = "tb_location";
			$where = $db->quoteInto("id=?", $data["id"]);
			$this->update($arr, $where);
		}catch(Exception $e){
			//$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			
		}
	
	}
	
	public function getAllBranch($data){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.`id`,
				  s.`name`,
				  s.prefix,
				  (SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=s.`status` LIMIT 1) status
				FROM
				  `tb_location` AS s where 1 ";
		$where='';
		if($data["branch_name"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['branch_name']));
			$s_where[]= " s.`name` LIKE '%{$s_search}%'";
			$s_where[]=" s.`prefix` LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["status"]!=""){
			$where.=' AND s.status='.$data["status"];
		}
		//echo $sql;
		return $db->fetchAll($sql.$where);
	}
	
	public function getBranchById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.* 
				FROM
				  `tb_location` AS s 
				WHERE s.id = $id";
		return $db->fetchRow($sql);
	}
}