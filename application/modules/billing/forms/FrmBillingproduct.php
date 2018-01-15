<?php 
class Billing_Form_FrmBillingproduct extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	
	///customer  form ///////////////////////
	public function frm_customer($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Category_Model_DbTable_DbCategory();
		$tru_type=new Billing_Model_DbTable_DbTruck();
		
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$cus_no= new Zend_Form_Element_Text('cus_no');
		$cus_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$cus_noss= $tru_type->getProductNo();
		$cus_no->setValue($cus_noss);
		
		$serial_no= new Zend_Form_Element_Text('serial_no');
		$serial_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$row_s= $tru_type->getProductNo();
		$serial_no->setValue($row_s);
		
		$deli_to = new Zend_Form_Element_Text('deli_to');
		$deli_to->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$deli_to_no = new Zend_Form_Element_Text('deli_to_no');
		$deli_to_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$start_date = $request->getParam('start_date');
		if($start_date==""){
			$start_date=date("m/d/y");
			//$startDateValue=date("m/d/Y");
		}
		$start_date = new Zend_Form_Element_Text('start_date');
		$start_date->setValue($start_date);
		$start_date->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
		
		$this->addElements(array($cus_no,$serial_no,$deli_to,$deli_to_no,$start_date));
		return $this;
		
	}
	
	/////////////	Form Product		/////////////////
	public function frm_product($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Category_Model_DbTable_DbCategory();
		$tru_type=new Billing_Model_DbTable_DbTruck();
		
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$pro_no= new Zend_Form_Element_Text('pro_no');
		$pro_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$row_s= $tru_type->getProductNo();
		$pro_no->setValue($row_s);
		
		$pro_name = new Zend_Form_Element_Text('pro_name');
		$pro_name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
	 
		$strength = new Zend_Form_Element_Text('strength');
		$strength->setAttribs(array(
				'class'=>'form-control',
			    'placeholder'=>'CUM350/CYM300'
		));
		
		$slump = new Zend_Form_Element_Text('slump');
		$slump->setAttribs(array(
				'class'=>'form-control',
				'placeholder'=>'14+2'
		));
		
		$delivery_qty = new Zend_Form_Element_Text('delivery_qty');
		$delivery_qty->setAttribs(array(
				'class'=>'form-control',
					
		));
		
		$price = new Zend_Form_Element_Text('price');
		$price->setAttribs(array(
				'class'=>'form-control',
					
		));
		
		$total_del_qty = new Zend_Form_Element_Text('total_del_qty');
		$total_del_qty->setAttribs(array(
				'class'=>'form-control',
					
		));
		
		$trip_no = new Zend_Form_Element_Text('trip_no');
		$trip_no->setAttribs(array(
				'class'=>'form-control',
					
		));
		
		$type_concrete = new Zend_Form_Element_Select("type_concrete");
		$type_concrete->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('0'=>$tr->translate("Pease selected"));
		$row_brand= $tru_type->getTypeConcrete();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$type_concrete->setMultiOptions($opt);
		
		
		$start_date = $request->getParam('start_date');
		if($start_date==""){
			$start_date=date("m/d/y");
			//$startDateValue=date("m/d/Y");
		}
		$start_date = new Zend_Form_Element_Text('start_date');
		$start_date->setValue($start_date);
		$start_date->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
		
		if($data != null){
			//print_r($data);exit();
			$pro_no->setValue($data["barcode"]);
			$pro_name->setValue($data["item_name"]);
			$type_concrete->setValue($data["concrete_id"]);
			$strength->setValue($data["strength"]);
			$slump->setValue($data["slump"]);
			
			$delivery_qty->setValue($data["delvery_qty"]);
			$price->setValue($data["price"]);
			$trip_no->setValue($data["trip_no"]);
			$total_del_qty->setValue($data["total_deli_qty"]);
			$start_date->setValue($data["date"]);
			$remark->setValue($data["note"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($pro_no,$pro_name,$type_concrete,$strength,$slump,$delivery_qty,$total_del_qty,$price,
				$trip_no,$start_date,$status,$remark));
		return $this;
	}
	
	///truck from ////////////////////////////////////////////////////////////////
	public function frm_truck($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Category_Model_DbTable_DbCategory();
		$tru_type=new Billing_Model_DbTable_DbTruck();
	
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$tru_no = new Zend_Form_Element_Text('tru_no');
		$tru_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$row_s= $tru_type->getTruckCode();
		$tru_no->setValue($row_s);
			
		$tru_name= new Zend_Form_Element_Text('tru_name');
		$tru_name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
	
		$emp_weidth = new Zend_Form_Element_Text('emp_weidth');
		$emp_weidth->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
	
		$net_weight = new Zend_Form_Element_Text('net_weight');
		$net_weight->setAttribs(array(
				'class'=>'form-control',
					
		));
	
		$total_weight = new Zend_Form_Element_Text('total_weight');
		$total_weight->setAttribs(array(
				'class'=>'form-control',
					
		));
	
		$truck_type = new Zend_Form_Element_Select("tru_type");
		$truck_type->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('0'=>$tr->translate("Pease selected"));
		$row_brand= $tru_type->getTrucktypeopt();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$truck_type->setMultiOptions($opt);
	
	
		$dri_dob = $request->getParam('dri_dob');
		if($dri_dob==""){
			$dob=date("m/d/1990");
			//$startDateValue=date("m/d/Y");
		}
		$dri_dob = new Zend_Form_Element_Text('dri_dob');
		$dri_dob->setValue($dob);
		$dri_dob->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
			
		$startDateValue = $request->getParam('start_date');
		if($startDateValue==""){
			$endDateValue=date("m/d/Y");
			//$startDateValue=date("m/d/Y");
		}
		$startDateElement = new Zend_Form_Element_Text('start_date');
		$startDateElement->setValue($startDateValue);
		$startDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
	
		$this->addElement($startDateElement);
	
	
		$sex = new Zend_Form_Element_Select("sex");
		$sex->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array('1'=>$tr->translate("MALE"),'0'=>$tr->translate("FEMALE"));
		$sex->setMultiOptions($opt);
	
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
	
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
	
		if($data != null){
			//print_r($data);exit();
			$tru_name->setValue($data["name"]);
			$tru_no->setValue($data["tru_no"]);
			$emp_weidth->setValue($data["emp_weidth"]);
			$net_weight->setValue($data["net_weight"]);
			$total_weight->setValue($data["total_weight"]);
				
			$truck_type->setValue($data["tru_type"]);
			$dri_dob->setValue($data["date"]);
			$status->setValue($data["status"]);
	
		}
			
		$this->addElements(array($tru_no,$tru_name,$emp_weidth,$total_weight,$net_weight,
				$truck_type,$sex,$status,$remark,$dri_dob));
		return $this;
	}
}