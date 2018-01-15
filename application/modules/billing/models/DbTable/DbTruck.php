<?php

class Billing_Model_DbTable_DbTruck extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_bill_truck";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($_data){
		//print_r($_data);exit();
	 	 $db = $this->getAdapter();
	     $db->beginTransaction();
		     try{
			  		 $_arr = array(
					         'name'			=>$_data['tru_name'],
					         'tru_no'		=>$_data['tru_no'],
					         'emp_weidth'	=>$_data['emp_weidth'],
					         'net_weight'	=>$_data['net_weight'],
					         'total_weight'	=>$_data['total_weight'],
					         //'photo'		=>$_data['amountweek'],
			   				 'tru_type'		=>$_data['tru_type'],
					         'status'		=>$_data['status'],
					         'date'			=>date("Y-m-d"),
					         'user_id'		=>$this->getUserId()
					     );
					   $id = $this->insert($_arr);
				       $ids = explode(',', $_data['identity']);
					       foreach ($ids as $i){
					         $_arr = array(
					           'truck_id'		=>$id,
					           'driver_id'		=>$_data['driver_id_'.$i],
					           'phone'			=>$_data['phone_'.$i],
					           'note'			=>$_data['orther_'.$i],
					           'user_id'		=>$this->getUserId(),
					         );
					         $this->_name='tb_bill_truckdetail';
					         $this->insert($_arr);
					       }
		          $db->commit();
		          return true;
		     }catch (Exception $e){
			      $db->rollBack();
			      Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			      return false;
	         }
	 }
	public function edit($_data){
		//print_r($_data);exit();
	 	 $db = $this->getAdapter();
	     $db->beginTransaction();
		     try{
				 $arr = array(
					         'name'			=>$_data['tru_name'],
					         'tru_no'		=>$_data['tru_no'],
					         'emp_weidth'	=>$_data['emp_weidth'],
					         'net_weight'	=>$_data['net_weight'],
					         'total_weight'	=>$_data['total_weight'],
					         //'photo'		=>$_data['amountweek'],
			   				 'tru_type'		=>$_data['tru_type'],
					         'status'		=>$_data['status'],
					         'date'			=>date("Y-m-d"),
					         'user_id'		=>$this->getUserId()
					     );
				$where = $db->quoteInto("id=?", $_data["id"]);
				$this->update($arr, $where);
				
				$where_del="truck_id=".$_data["id"];
				$this->_name="tb_bill_truckdetail";
				$this->delete($where_del);
				
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					$_arr = array(
							'truck_id'		=>$_data["id"],
							'driver_id'		=>$_data['driver_id_'.$i],
							'phone'			=>$_data['phone_'.$i],
							'note'			=>$_data['orther_'.$i],
							'user_id'		=>$this->getUserId(),
					);
					$this->_name='tb_bill_truckdetail';
					$this->insert($_arr);
				}
			$db->commit();
			return true;
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	public function getAllTruck($search){
		$db = $this->getAdapter();
		$sql = "SELECT t.id,t.tru_no,t.name,(SELECT tp.name_kh FROM tb_bill_trucktype AS tp WHERE tp.id=t.tru_type ) AS tru_type,
			       (SELECT NAME FROM tb_bill_driver WHERE tb_bill_driver.id=td.driver_id) AS driver_id,td.phone,
			       CONCAT(t.emp_weidth,'Kg'),CONCAT(t.net_weight,'Kg'),CONCAT(t.total_weight,'Kg'),t.date,t.status 
			       FROM tb_bill_truck AS t,tb_bill_truckdetail AS td 
			       WHERE t.id=td.truck_id ";
		
		$where ='';
		$order = " ORDER BY id DESC";
		if(!empty($search['search_name'])){
			$s_where = array();
			$s_search = addslashes(trim($search['search_name']));
			$s_where[] = "t.tru_no LIKE '%$s_search%'";
			$s_where[] = "t.name LIKE '%$s_search%'";
			$s_where[] = "td.phone LIKE '%$s_search%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_serach']>-1){
			$where.= " AND t.status = '".$search['status_serach']."'";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getTruckById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_bill_truck where id=$id";
		return $db->fetchRow($sql);
	}
	public function getTruckdetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_bill_truckdetail where truck_id=$id";
		return $db->fetchAll($sql);
	}
	
	public function getLocationAssign(){
		$db = $this->getAdapter();
		$sql = "SELECT id,name FROM tb_bill_driver WHERE name!='' AND status=1 ";
		$sql.=" ORDER BY id DESC";
		$rows = $db->fetchAll($sql);
		$option ="";
		foreach($rows as $value){
			$option .= '<option label="'.htmlspecialchars($value['name'], ENT_QUOTES).'" value="'.$value['id'].'">'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $option;
	}
	
	//select car no 
	public function getTruckCode(){
		$db =$this->getAdapter();
		$sql="SELECT id FROM  tb_bill_truck ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "TRU-";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	//select produc no
	public function getProductNo(){
		$db =$this->getAdapter();
		$sql="SELECT id FROM  tb_bill_product ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "P-";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	//select produc no
	public function getProducCode(){
		$db =$this->getAdapter();
		$sql="SELECT id,barcode AS `code` FROM tb_bill_product WHERE `status`=1 ORDER BY id DESC";
		return $db->fetchAll($sql);
	}
	
	//select produc no
	public function autoNumber(){
		$db =$this->getAdapter();
		$sql="SELECT id FROM  tb_billing ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	//select produc no
	public function serailNo(){
		$db =$this->getAdapter();
		$sql="SELECT id FROM  tb_billing ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "Ser-";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	//select type truck 
	function getTrucktypeopt(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(name_kh,'-',name_en) AS `name` FROM tb_bill_trucktype  WHERE `status`=1";
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$order);
	}
	function getTypeConcrete(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(name_kh,'-',name_en) AS `name` FROM tb_bill_typeconcrete  WHERE `status`=1";
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$order);
	}
}