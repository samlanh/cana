<?php 
class Billing_Form_FrmTruck extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
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