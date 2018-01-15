<?php

class Billing_Model_DbTable_DbTypeconcrete extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_bill_typeconcrete";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$arr = array(
				'name_kh'			=>	$data["truck_ca_kh"],
				'name_en'		=>	$data["truck_ca_en"],
				'date'			=>	date("Y-m-d"),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name_kh'			=>	$data["truck_ca_kh"],
				'name_en'		=>	$data["truck_ca_en"],
				'date'			=>	date("Y-m-d"),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_bill_typeconcrete";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	public function getAllTypeConcrete($search){
		$db = $this->getAdapter();
		$sql = "SELECT id,name_kh,name_en,`date`,`status` FROM tb_bill_typeconcrete WHERE 1";
		
		$where ='';
		$order = " ORDER BY id DESC";
		if(!empty($search['search_name'])){
			$s_where = array();
			$s_search = addslashes(trim($search['search_name']));
			$s_where[] = "name_en LIKE '%$s_search%'";
			$s_where[] = "name_kh LIKE '%$s_search%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_serach']>-1){
			$where.= " AND status = '".$search['status_serach']."'";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getTypeConcreteById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name where id=$id";
		return $db->fetchRow($sql);
	}
}