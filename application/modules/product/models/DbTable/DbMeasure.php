<?php
class Product_Model_DbTable_DbMeasure extends Zend_Db_Table_Abstract{
	protected $_name = "tb_measure";
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
		}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],);
		$this->_name = "tb_measure";
		$this->insert($arr);
		}
	function getmeasurename($data){
		$db= $this->getAdapter();
		$sql ="SELECT m.name from tb_measure as m where REPLACE(m.name,' ','')=REPLACE('$data',' ','')";
		return $db->fetchOne($sql);
		}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],);
		$this->_name = "tb_measure";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
		}
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],);
		$this->_name = "tb_measure";
		
		return $this->insert($arr);
		}
	public function getAllMeasure($data=null){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`remark`,(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=m.`status` LIMIT 1) AS `status` FROM `tb_measure` AS m WHERE 1 ";
		$where='';
		if($data["name"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['name']));
			$s_where[]= " m.`name` LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["parent"]!=""){
			$where.=' AND m.id='.$data["parent"];
		}
		if($data["status"]!=""){
			$where.=' AND m.status='.$data["status"];
		}
		return $db->fetchAll($sql.$where);
		}
	public function getMeasure($id){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_measure` AS m  WHERE m.`id`= $id";
		return $db->fetchRow($sql);
	}
}