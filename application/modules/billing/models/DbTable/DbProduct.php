<?php

class Billing_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_bill_product";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($_data){
		//print_r($_data);exit();
	 	 $db = $this->getAdapter();
	     $db->beginTransaction();
		     try{
			  		 $_arr = array(
					         'item_name'	=>$_data['pro_name'],
					         'barcode'		=>$_data['pro_no'],
					         'strength'		=>$_data['strength'],
					         'slump'		=>$_data['slump'],
					         'delvery_qty'	=>$_data['delivery_qty'],
					         'trip_no'		=>$_data['trip_no'],
			   				 'total_deli_qty'=>$_data['total_del_qty'],
			  		 		 'concrete_id'	=>$_data['type_concrete'],
			  		 		 'price'		=>$_data['price'],
			  		 		 'note'			=>$_data['remark'],
			  		 		 'date'			=>date("Y-m-d"),
					         'status'		=>$_data['status'],
					         'user_id'		=>$this->getUserId()
					     );
					   $id = $this->insert($_arr);
				       $ids = explode(',', $_data['identity']);
					       foreach ($ids as $i){
					       	
					         $_arr = array(
					           'pro_id'			=>$id,
					           'location_id'	=>$_data['location_id_'.$i],
					           'qty'			=>$_data['curr_qty_'.$i],
					           'qty_warning'	=>$_data['qty_warning_'.$i],
					         );
					         $this->_name='tb_bill_prolocation';
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
					         'item_name'	=>$_data['pro_name'],
					         'barcode'		=>$_data['pro_no'],
					         'strength'		=>$_data['strength'],
					         'slump'		=>$_data['slump'],
					         'delvery_qty'	=>$_data['delivery_qty'],
					         'trip_no'		=>$_data['trip_no'],
			   				 'total_deli_qty'=>$_data['total_del_qty'],
			  		 		 'concrete_id'	=>$_data['type_concrete'],
				  			 'price'		=>$_data['price'],
			  		 		 'note'			=>$_data['remark'],
			  		 		 'date'			=>date("Y-m-d"),
					         'status'		=>$_data['status'],
					         'user_id'		=>$this->getUserId()
					     );
				$where = $db->quoteInto("id=?", $_data["id"]);
				$this->update($arr, $where);
				
				$where_del="pro_id=".$_data["id"];
				$this->_name="tb_bill_prolocation";
				$this->delete($where_del);
				
				$ids = explode(',', $_data['identity']);
		    			 foreach ($ids as $i){
					         $_arr = array(
					           'pro_id'			=>$_data["id"],
					           'location_id'	=>$_data['location_id_'.$i],
					           'qty'			=>$_data['curr_qty_'.$i],
					           'qty_warning'	=>$_data['qty_warning_'.$i],
					         );
					         $this->_name='tb_bill_prolocation';
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
	
	public function getAllProduct($search){
		$db = $this->getAdapter();
		$sql = "SELECT p.id,(SELECT sl.name FROM tb_sublocation AS sl WHERE sl.id=pl.location_id LIMIT 1) AS location_id,
			       p.barcode,p.item_name,(SELECT tc.name_kh FROM tb_bill_typeconcrete AS tc WHERE tc.id=  p.concrete_id LIMIT 1)  AS concrete_id,
			       p.strength,p.slump,p.delvery_qty,p.total_deli_qty,p.trip_no,pl.qty,p.date,p.status 
			     FROM tb_bill_product AS p,tb_bill_prolocation AS pl
		WHERE p.id=pl.pro_id ";
		
		$where ='';
		$order = "  ORDER BY id DESC ";
		if(!empty($search['search_name'])){
			$s_where = array();
			$s_search = addslashes(trim($search['search_name']));
			$s_where[] = "p.barcode LIKE '%$s_search%'";
			$s_where[] = "p.item_name LIKE '%$s_search%'";
			$s_where[] = "p.strength LIKE '%$s_search%'";
			$s_where[] = "p.slump LIKE '%$s_search%'";
			$s_where[] = "p.delvery_qty LIKE '%$s_search%'";
			$s_where[] = "p.total_deli_qty LIKE '%$s_search%'";
			$s_where[] = "p.trip_no LIKE '%$s_search%'";
			$s_where[] = "pl.qty LIKE '%$s_search%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_serach']>-1){
			$where.= " AND p.status = '".$search['status_serach']."'";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getProductById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_bill_product where id=$id";
		return $db->fetchRow($sql);
	}
	public function getPolocation($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_bill_prolocation where pro_id=$id";
		return $db->fetchAll($sql);
	}
	
	public function getLocationAssign(){
		$db = $this->getAdapter();
		$sql = "SELECT id,name FROM tb_sublocation WHERE name!='' AND status=1 ";
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
	//function get customer name  by cus no 
	function getCustomerName($cus_id){
		$db=$this->getAdapter();
		$sql="SELECT id,cust_name FROM tb_customer WHERE id=$cus_id";
		return $db->fetchRow($sql);
	}
	
	//function get product name  by pro no
	function getProductName($pro_id){
		$db=$this->getAdapter();
		$sql="SELECT item_name FROM tb_bill_product WHERE id=$pro_id";
		return $db->fetchRow($sql);
	}
	
	//function truck info  by pro no
	function getTruckInfo($truck_id){
		$db=$this->getAdapter();
		$sql="SELECT `name` FROM tb_bill_truck WHERE id=$truck_id";
		return $db->fetchRow($sql);
	}
	
	//function truck info  by pro no
	function getDriverInfo($driver_id){
		$db=$this->getAdapter();
		$sql="SELECT phone FROM tb_bill_driver WHERE id=$driver_id";
		return $db->fetchRow($sql);
	}
	
}