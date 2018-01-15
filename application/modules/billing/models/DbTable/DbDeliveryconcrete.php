<?php

class Billing_Model_DbTable_DbDeliveryconcrete extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_billing";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($_data){
		 //print_r($_data);exit();
	 	 $db = $this->getAdapter();
	     $db->beginTransaction();
		     try{
		  		 $_arr = array(
		  		 		
		  		 		//customer 
				         'cus_id'		=>$_data['cus_no'],
				         'aouto_num'	=>$_data['auto_num_no'],
				         'serail_id'	=>$_data['serial_no'],
				         'delivery_to'	=>$_data['delivery_to'],
				         'divery_date'	=>$_data['auto_date'],
		  		 		
				        //product 
		  		 		'concrete_id'	=>$_data['type_concrete'],
		  		 		'product_id'	=>$_data['pro_no'],
		  		 		'stength'		=>$_data['strength'],
		  		 		'slump'			=>$_data['slump'],
		  		 		'dilivery_qty'	=>$_data['del_qty'],
		  		 		'total_dil_qty'	=>$_data['delivery_qty'],
		  		 		'trip_no'		=>$_data['trip_no'],
		  		 		
		  		 		//truck info
		  		 		'truck_id'		=>$_data['tru_code'],
		  		 		'driver_id'		=>$_data['driver_name'],
		  		 		'driver_phone'	=>$_data['driver_phone'],
		  		 		'depart_time'	=>$_data['depart_time'],
		  		 		'arrive_time'	=>$_data['arrive_time'],
		  		 		'net_weight'	=>$_data['net_weight'],
		  		 		'emp_weight'	=>$_data['emp_weidth'],
		  		 		'total_weight'	=>$_data['total_weight'],
		  		 		'pum_truck'		=>$_data['pump_truck'],
		  		 		'pum_machine'	=>$_data['pump_machine'],
		  		 		'remark'		=>$_data['remark'],
		  		 		'status'		=>$_data['status'],
				        'date'			=>date("Y-m-d"),
				        'user_id'		=>$this->getUserId()
				     );
				   $this->insert($_arr);
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
			$_arr = array(
						
					//customer
					'cus_id'		=>$_data['cus_no'],
					'aouto_num'	=>$_data['auto_num_no'],
					'serail_id'	=>$_data['serial_no'],
					'delivery_to'	=>$_data['delivery_to'],
					'divery_date'	=>$_data['auto_date'],
						
					//product
					'concrete_id'	=>$_data['type_concrete'],
					'product_id'	=>$_data['pro_no'],
					'stength'		=>$_data['strength'],
					'slump'			=>$_data['slump'],
					'dilivery_qty'	=>$_data['del_qty'],
					'total_dil_qty'	=>$_data['delivery_qty'],
					'trip_no'		=>$_data['trip_no'],
						
					//truck info
					'truck_id'		=>$_data['tru_code'],
					'driver_id'		=>$_data['driver_name'],
					'driver_phone'	=>$_data['driver_phone'],
					'depart_time'	=>$_data['depart_time'],
					'arrive_time'	=>$_data['arrive_time'],
					'net_weight'	=>$_data['net_weight'],
					'emp_weight'	=>$_data['emp_weidth'],
					'total_weight'	=>$_data['total_weight'],
					'pum_truck'		=>$_data['pump_truck'],
					'pum_machine'	=>$_data['pump_machine'],
					'remark'		=>$_data['remark'],
					'status'		=>$_data['status'],
					'date'			=>date("Y-m-d"),
					'user_id'		=>$this->getUserId()
			);
			$where=" id=".$_data['id'];
			$this->update($_arr, $where);
			$db->commit();
			return true;
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
		
	}
	
	public function getAllBilling($search){
		$db = $this->getAdapter();
		$sql = " SELECT id,aouto_num,serail_id,(SELECT cust_name FROM tb_customer WHERE tb_customer.id=tb_billing.cus_id LIMIT 1) AS cus_name,
					(SELECT item_name FROM tb_bill_product AS p WHERE p.id=tb_billing.product_id LIMIT 1) AS pro_name,delivery_to,
					 (SELECT `name` FROM tb_bill_driver WHERE tb_bill_driver.id=tb_billing.driver_id LIMIT 1) AS driver_name,
					 driver_phone,depart_time,arrive_time,total_weight,divery_date,`status`
					 FROM tb_billing WHERE product_id !=0 ";
		
		$where ='';
		$order = " ORDER BY id DESC";
		if(!empty($search['search_name'])){
			$s_where = array();
			$s_search = addslashes(trim($search['search_name']));
			$s_where[] = "aouto_num LIKE '%$s_search%'";
			$s_where[] = "serail_id LIKE '%$s_search%'";
			$s_where[] = "delivery_to LIKE '%$s_search%'";
			$s_where[] = "driver_phone LIKE '%$s_search%'";
			
			$s_where[] = "depart_time LIKE '%$s_search%'";
			$s_where[] = "arrive_time LIKE '%$s_search%'";
			$s_where[] = "total_weight LIKE '%$s_search%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_serach']>-1){
			$where.= " AND status = '".$search['status_serach']."'";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getBillingById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_billing WHERE id=$id";
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
	
	//select customer code and name 
	function getCustomerCode(){
		$db=$this->getAdapter();
		$sql="SELECT id,cu_code,contact_name AS cu_name FROM tb_customer WHERE `status`=1";
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$order);
	}
	//select car code and name 
	function getCareCode(){
		$db=$this->getAdapter();
		$sql="SELECT id,tru_no,`name`  FROM  tb_bill_truck   WHERE `status`=1";
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$order);
	}
	
	//select driver name
	function getDriverName(){
		$db=$this->getAdapter();
		$sql="SELECT id,`name` FROM tb_bill_driver WHERE status=1";
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$order);
	}
}