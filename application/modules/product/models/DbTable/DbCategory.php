<?php

class Product_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract{
	protected $_name = "tb_category";
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function getAllCategorys(){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`parent_id`,c.`remark`,c.`status` FROM `tb_category` AS c WHERE c.`status` =1";
		return $db->fetchAll($sql);
	}
	public function add($data){//
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'prefix'		=>	$data["prifix"],
				'start_code'	=>	$data["start_code"],
				'is_none_stock'	=>	@$data["is_none_stock"],
		);
		$this->_name = "tb_category";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		try {
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'prefix'		=>	$data["prifix"],
				'start_code'	=>	$data["start_code"],
				'is_none_stock'	=>	@$data["is_none_stock"],
		);
		$this->_name = "tb_category";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
		
		$sql="SELECT id FROM tb_product where status=1 AND cate_id = ".$data["id"]." ORDER BY int_code DESC ";
		$item_id = $db->fetchOne($sql);
		$arr = array(
			'int_code'=>$data["start_nuumber"],
		);
		$this->_name = "tb_product";
		$where = $db->quoteInto("id=?", $item_id);
		$this->update($arr, $where);
		
		}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e);
    		echo $e->getMessage();exit();
    	}
	}
	//Insert Popup=============================
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'prefix'		=>	$data["prifix"],
				'start_code'	=>	$data["start_code"],
				'is_none_stock'	=>	@$data["is_none_stock"],
		);
		$this->_name = "tb_category";
		return $this->insert($arr);
		
	}
	public function getAllCategory($data){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,
				(SELECT par_id.name FROM tb_category AS par_id WHERE par_id.id = c.parent_id  ) AS parent_name,
				(SELECT name_kh FROM `tb_view` WHERE type=16 AND key_code=c.`is_none_stock` LIMIT 1) AS stock_type,
				c.prefix,
				c.start_code,
				c.`remark`,
				(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=c.`status` LIMIT 1) AS status
				FROM `tb_category` AS c WHERE 1";
				
		$where='';
		if($data["name"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['name']));
			$s_where[]= " c.`name` LIKE '%{$s_search}%'";
			$s_where[]= " c.prefix LIKE '%{$s_search}%'";
			$s_where[]= " c.start_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["parent"]!=""){
			$where.=' AND c.id='.$data["parent"];
		}
		if($data["status"]!=""){
			$where.=' AND c.status='.$data["status"];
		}
		if($data["stock_type"]>-1){
			$where.=' AND c.is_none_stock='.$data["stock_type"];
		}
		
		return $db->fetchAll($sql.$where);
	}
	 
    function getParent($par_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT  c.`name` FROM `tb_category` AS c WHERE c.`parent_id`=$par_id";
    	return $db->fetchAll($sql);
    }
	public function getCategory($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`parent_id`,c.`remark`,c.`status`,c.`prefix`,c.is_none_stock,c.start_code FROM `tb_category` AS c WHERE c.`id`= $id";
		return $db->fetchRow($sql);
	}
	function getCategoryExist($data){
		$db = $this->getAdapter();
		$sql = "SELECT c.name FROM tb_category AS c WHERE REPLACE(c.name,' ','')=REPLACE('$data',' ','')";
		return $db->fetchOne($sql);
	}
	function getPrefixyExist($data){
		$db = $this->getAdapter();
		$sql = "SELECT c.prefix FROM tb_category AS c WHERE REPLACE(c.prefix,' ','')=REPLACE('$data',' ','')";
		return $db->fetchOne($sql);
	}
}