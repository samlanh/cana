<?php

class Billing_Model_DbTable_DbYouDriver extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_bill_driver";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["dri_name"],
				'national_id'	=>	$data["dri_national"],
				'sex'			=>	$data["sex"],
				'phone'			=>	$data["dri_phone"],
				'date_of_birdth'=>	$data["dri_dob"],
				'email'			=>	$data["dri_email"],
				'address'		=>	$data["dri_address"],
				'position'		=>	$data["dri_position"],
				'date'			=>	date("Y-m-d"),
				'status'		=>	$data["status"],
		);
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["dri_name"],
				'national_id'	=>	$data["dri_national"],
				'sex'			=>	$data["sex"],
				'phone'			=>	$data["dri_phone"],
				'date_of_birdth'=>	$data["dri_dob"],
				'email'			=>	$data["dri_email"],
				'address'		=>	$data["dri_address"],
				'position'		=>	$data["dri_position"],
				'date'			=>	date("Y-m-d"),
				'status'		=>	$data["status"],
		);
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	public function getAllYouDriver($search){
		$db = $this->getAdapter();
		$sql = "SELECT d.id,d.national_id,d.name, (SELECT name_en FROM tb_view WHERE tb_view.key_code=d.sex AND tb_view.type=10) AS sexs 
		        ,d.phone,d.email,d.date_of_birdth,d.address,d.position,d.status 
		               FROM tb_bill_driver AS d WHERE 1";
		
		$where ='';
		$order = " ORDER BY id DESC";
		if(!empty($search['search_name'])){
			$s_where = array();
			$s_search = addslashes(trim($search['search_name']));
			$s_where[] = "d.national_id LIKE '%$s_search%'";
			$s_where[] = "d.name LIKE '%$s_search%'";
			$s_where[] = "d.phone LIKE '%$s_search%'";
			$s_where[] = "d.email LIKE '%$s_search%'";
			$s_where[] = "d.date_of_birdth LIKE '%$s_search%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_serach']>-1){
			$where.= " AND status = '".$search['status_serach']."'";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getYoudiverById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_bill_driver where id=$id";
		return $db->fetchRow($sql);
	}
}