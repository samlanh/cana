<?php 
class Billing_Form_FrmDeliveryconcrete extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	
	///customer  form ///////////////////////
	public function frm_customer($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db_cus=new Billing_Model_DbTable_DbDeliveryconcrete();
		$db = new Category_Model_DbTable_DbCategory();
		$tru_type=new Billing_Model_DbTable_DbTruck();
		
		$cus_name = new Zend_Form_Element_Select("cus_name");
		$cus_name->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required'
		));
		$r_cus=$db_cus->getCustomerCode();
		$opt_cus = array('0'=>$tr->translate("customer name "));
		if(!empty($r_cus))foreach ($r_cus As $rs_cus)$opt_cus[$rs_cus['id']]=$rs_cus['cu_name'];
		$cus_name->setMultiOptions($opt_cus);
		
		$cus_names = new Zend_Form_Element_Text("cus_names");
		$cus_names->setAttribs(array(
				'class'=>'form-control',
		));
		
		$cus_no = new Zend_Form_Element_Select("cus_no");
		$cus_no->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getCustomer();',
				'required'=>'required'
		));
		$r_cus=$db_cus->getCustomerCode();
		$opt_cus = array('0'=>$tr->translate("Selected customer code "));
		if(!empty($r_cus))foreach ($r_cus As $rs_cus)$opt_cus[$rs_cus['id']]=$rs_cus['cu_code'];
		$cus_no->setMultiOptions($opt_cus);
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$auto_num_no= new Zend_Form_Element_Text('auto_num_no');
		$auto_num_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required',
				'readOnly'=>true
		));
		$auto_no= $tru_type->autoNumber();
		$auto_num_no->setValue($auto_no);
		
		$serial_no= new Zend_Form_Element_Text('serial_no');
		$serial_no->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required',
				'readOnly'=>true
		));
		$row_s= $tru_type->serailNo();
		$serial_no->setValue($row_s);
		
		$deli_to = new Zend_Form_Element_Text('deli_to');
		$deli_to->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$delivery_to = new Zend_Form_Element_Text('delivery_to');
		$delivery_to->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$auto_date = $request->getParam('auto_date');
		if($auto_date==""){
			$dob=date("d/m/Y");
			//$startDateValue=date("m/d/Y");
		}
		$auto_date = new Zend_Form_Element_Text('auto_date');
		$auto_date->setValue($dob);
		$auto_date->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker ',
				'placeholder'=>'Start Date'
		));
		if($data !=''){
			$cus_no->setValue($data['cus_id']);
			$delivery_to->setValue($data['delivery_to']);
			$serial_no->setValue($data['serail_id']);
			$auto_num_no->setValue($data['aouto_num']);
			$auto_date->setValue($data['divery_date']);
		}
		$this->addElements(array($cus_name,$cus_names,$auto_num_no,$cus_no,$serial_no,$deli_to,$delivery_to,$auto_date));
		return $this;
		
	}
	
	/////////////	Form Product		/////////////////
	public function frm_product($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Category_Model_DbTable_DbCategory();
		$tru_type=new Billing_Model_DbTable_DbTruck();
		
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$rows= $tru_type->getProducCode();
		//print_r($rows);exit();
		$opt_cus = array('0'=>$tr->translate("product no"));
		if(!empty($rows))foreach ($rows As $rs_cus)$opt_cus[$rs_cus['id']]=$rs_cus['code'];
		$pro_no = new Zend_Form_Element_Select("pro_no");
		$pro_no->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
				'onChange'=>'getProName();'
		));
		$pro_no->setMultiOptions($opt_cus);
		 
		
		$pro_name = new Zend_Form_Element_Text("pro_name");
		$pro_name->setAttribs(array(
				'class'=>'form-control',
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
		
		$del_qty = new Zend_Form_Element_Text('del_qty');
		$del_qty->setAttribs(array(
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
				'class'=>'form-control select2me',
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
		if($data != null){
			//print_r($data);exit();
			$pro_no->setValue($data['product_id']);
			$type_concrete->setValue($data['concrete_id']);
			$strength->setValue($data['stength']);
			$slump->setValue($data['slump']);
			$delivery_qty->setValue($data['dilivery_qty']);
			$total_del_qty->setValue($data['total_dil_qty']);
			$trip_no->setValue($data['trip_no']);
			 
		}
			
		$this->addElements(array($del_qty,$pro_no,$pro_name,$type_concrete,$strength,$slump,$delivery_qty,$total_del_qty,$price,
				$trip_no));
		return $this;
	}
	
	///truck from ////////////////////////////////////////////////////////////////
	public function frm_car($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db_glob=new Application_Model_GlobalClass();
		$row_time=$db_glob->getHours();
		
		$db = new Category_Model_DbTable_DbCategory();
		$tru_type=new Billing_Model_DbTable_DbTruck();
		$db_cus=new Billing_Model_DbTable_DbDeliveryconcrete();
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();

		$truck_no = new Zend_Form_Element_Select("tru_code");
		$truck_no->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getTruckName()'
		));
		$opt = array(''=>$tr->translate("Selected truck no"));
		$car_no=$db_cus->getCareCode();
		if(!empty($car_no)) foreach ($car_no As $rs_car)$opt[$rs_car['id']]=$rs_car['tru_no'];
		$truck_no->setMultiOptions($opt);
		
		
		$truck_name= new Zend_Form_Element_Text('truck_name');
		$truck_name->setAttribs(array(
				'class'=>'form-control',
				 
		));
		
		$opt_d=array(''=>$tr->translate("Select Driver Name"));
		$rs_dn=$db_cus->getDriverName();
		if(!empty($rs_dn)) foreach($rs_dn As $rs_d) $opt_d[$rs_d['id']]=$rs_d['name'];
		$driver_name = new Zend_Form_Element_Select("driver_name");
		$driver_name->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getDriverPhone()'
		));
		$driver_name->setMultiOptions($opt_d);
		
		$driver_phone = new Zend_Form_Element_Text("driver_phone");
		$driver_phone->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
	
		 
		$otp_t=array();
		if(!empty($row_time)) foreach($row_time As $key=>$rs_time)$otp_t[$rs_time]=$rs_time; 
		$depart_time = new Zend_Form_Element_Select("depart_time");
		$depart_time->setAttribs(array(
				'class'=>'form-control select2me',
				'placholder'=>'00:00'
		));
		$depart_time->setMultiOptions($otp_t);
		
		$arrive_time = new Zend_Form_Element_Select("arrive_time");
		$arrive_time->setAttribs(array(
				'class'=>'form-control',
				 
		));
		$arrive_time->setMultiOptions($otp_t);
		
		$emp_weidth = new Zend_Form_Element_Text('emp_weidth');
		$emp_weidth->setAttribs(array(
				'class'=>'form-control',
				 
		));
	   
		
		
		$net_weight = new Zend_Form_Element_Text('net_weight');
		$net_weight->setAttribs(array(
				'class'=>'form-control',
					
		));
	
		$total_weight = new Zend_Form_Element_Text('total_weight');
		$total_weight->setAttribs(array(
				'class'=>'form-control',
					
		));
		
		$pump_truck = new Zend_Form_Element_Checkbox('pump_truck');
		$pump_truck->setAttribs(array(
				'class'=>'form-control',
				'onclick'=>'checkPumtru()'
					
		));
		
		$pump_machine = new Zend_Form_Element_Checkbox('pump_machine');
		$pump_machine->setAttribs(array(
				'class'=>'form-control',
				'onclick'=>'checkPummac()'
					
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
	
	
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				 
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
	
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
	
		if($data != null){
			$truck_no    ->setValue($data['truck_id']); 
			$driver_name ->setValue($data['driver_id']);
			$depart_time    ->setValue($data['depart_time']);
			$arrive_time->setValue($data['arrive_time']);
			$driver_phone->setValue($data['driver_phone']);
			$emp_weidth->setValue($data['emp_weight']);
			$total_weight->setValue($data['total_weight']);
			
			$net_weight->setValue($data['net_weight']);
			$pump_truck->setValue($data['pum_truck']);
			$pump_machine->setValue($data['pum_machine']);
			
			$status->setValue($data['status']);
			$remark->setValue($data['remark']);
		}
			
		$this->addElements(array($pump_truck,$truck_no,$truck_name,$driver_name,$depart_time,$arrive_time,
				$driver_phone,$emp_weidth,$total_weight,$net_weight,$pump_machine,$truck_type,$status,$remark));
		return $this;
	}
}