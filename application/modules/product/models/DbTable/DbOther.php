<?php

class Product_Model_DbTable_DbOther extends Zend_Db_Table_Abstract{
	function getOtherByname_type($data){
		$db= $this->getAdapter();
		$sql ="SELECT v.`name_en` FROM `tb_view` AS v WHERE v.`type`=".$data["type"]. " AND v.name_en=REPLACE('".$data["title_en"]."',' ','') ";
		//echo $sql;
		return $db->fetchOne($sql);
	}
    function getAllView($data){
    	$db = $this->getAdapter();
    	$sql = "SELECT v.`id`,v.`name_en`,(SELECT name_en FROM `tb_view` WHERE type=10 AND key_code=v.type LIMIT 1) AS `type`,(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=v.status LIMIT 1) AS `status` FROM `tb_view` AS v WHERE v.`type` IN(2,3,4)";
    	$where = '';
    	if($data["adv_search"]!=""){
    		$s_where=array();
    		$s_search = addslashes(trim($data['adv_search']));
    		$s_where[]= " v.`name_en` LIKE '%{$s_search}%'";
    		$s_where[]=" v.`key_code` LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($data["status_search"]!=""){
    		$where.=' AND v.status='.$data["status_search"];
    	}
    	if($data["type"]!=""){
    		$where.=' AND v.type='.$data["type"];
    	}
		$where.=" ORDER BY v.type DESC";
    	return $db->fetchAll($sql.$where);
    }
    function getViewById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT v.`id`,v.`name_en`,v.`status`,v.`key_code`,`type` FROM `tb_view` AS v WHERE v.`id`=$id";
    	return $db->fetchRow($sql);
    }
    function add($data){
    	$db = $this->getAdapter();
    	$key_code = $this->getLastKeycodeByType($data['type']);
    	$arr = array(
    		'name_en'	=>	$data["title_en"],
    		'key_code'	=>	$key_code,
    		'type'		=>	$data["type"],
    		'status'	=>	$data["status"],);
    	$this->_name = "tb_view";
    	$this->insert($arr);
    }
    function edit($data){
    	$db = $this->getAdapter();
    	$key_code = $this->getLastKeycodeByType($data['type']);
    	$arr = array(
    			'name_en'	=>	$data["title_en"],
    			'type'		=>	$data["type"],
    			'status'	=>	$data["status"],);
    	$this->_name = "tb_view";
    	$where = $db->quoteInto("id=?", $data["id"]);
    	$this->update($arr, $where);
    }
    //Insert Popup=====================================================================
	
	function addNew($data){
    	$db = $this->getAdapter();
    	$key_code = $this->getLastKeycodeByType($data['type']);
    	$arr = array(
    		'name_en'	=>	$data["title_en"],
    		'key_code'	=>	$key_code,
    		'type'		=>	$data["type"],
    		'status'	=>	$data["status"],
    	);
    	$this->_name = "tb_view";
    	$id = $this->insert($arr);
		return $key_code;
    }
    function getLastKeycodeByType($type){
    	$sql = "SELECT key_code FROM `tb_view` WHERE type=$type ORDER BY key_code DESC LIMIT 1 ";
    	$db =$this->getAdapter();
    	$number = $db->fetchOne($sql);
    	return $number+1;
    }
    function updateOldPettycash(){
    	$sql="SELECT n.id,n.item_name,n.item_code,o.item_name,o.item_code AS item_code_old ,c.name
			  FROM `tb_product` AS n ,
			  `tb_product_old` AS o,
			  `tb_category` AS c
			 WHERE  n.id=o.id AND c.id=n.cate_id AND c.is_none_stock=1 AND n.item_code!=o.item_code";
    	$db = $this->getAdapter();
    	$row = $db->fetchAll($sql);
    	if(!empty($row)){
    		
    		$this->_name="tb_product";
    		foreach($row as $rs){
    			$arr = array(
    					'item_code'=>$rs['item_code_old']
    					);
    			$where="id=".$rs['id'];
    			$this->update($arr, $where);
    		}
    	}
    }
}