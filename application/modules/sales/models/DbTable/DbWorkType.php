<?php
class Sales_Model_DbTable_DbWorkType extends Zend_Db_Table_Abstract{
    protected $_name = 'tb_work_type';
    public function setName($name){
    	$this->_name=$name;
    }
    public function add($_data){
		$this->_name='tb_work_type';
		$_arr = array(
			'name'		=>	$_data['name'],
			'date'		=>	date('Y-m-d'),
			'status'	=>	$_data['status'],);
		$this->insert($_arr);
		}
	 public function edit($_data){
		$this->_name='tb_work_type';
		$_arr = array(
			'name'		=>	$_data['name'],
			'date'		=>	date('Y-m-d'),
			'status'	=>	$_data['status'],);
		$where = "id=".$_data["id"];
		$this->update($_arr,$where);
		}
	function getUser(){
		$db = $this->getAdapter();
		$sql ="SELECT u.id,u.`first_name` FROM `rms_users` AS u";
		return $db->fetchAll($sql);
		}
	public function updateplantypeById($_data){
		$_arr = array(
			'name'=>$_data['name'],
			//'desc'=>$_data['desc'],
			'date'=>date('Y-m-d'),
			'status'=>$_data['status'],);
		$where = "id =".$_data['id'];
		$this->update($_arr, $where);
		}
	public function getplantypeById($id){
		$db = $this->getAdapter();
		$sql = " SELECT id,name,status FROM `tb_work_type` WHERE id = $id";
		return $db->fetchRow($sql);
		}
	public function getPlanType($search){
		$db = $this->getAdapter();
		$sql = "SELECT p.id,p.name,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=p.`status` AND v.type=5 LIMIT 1) AS `status` FROM `tb_work_type` AS p WHERE 1";
		$where ='';
		if(!empty($search["adv_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]= " name LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search["status"])){
			$where.=' AND status='.$search["status"];
		}
		return $db->fetchAll($sql.$where);
    }  
}