<?php 
class Billing_Form_FrmYoudriver extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function frm_you_driver($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Category_Model_DbTable_DbCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dri_name = new Zend_Form_Element_Text('dri_name');
		$dri_name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
		$dri_phone = new Zend_Form_Element_Text('dri_phone');
		$dri_phone->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$dri_national = new Zend_Form_Element_Text('dri_national');
		$dri_national->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$dri_email = new Zend_Form_Element_Text('dri_email');
		$dri_email->setAttribs(array(
				'class'=>'form-control',
				 
		));
		
		$dri_address = new Zend_Form_Element_Text('dri_address');
		$dri_address->setAttribs(array(
				'class'=>'form-control',
				 
		));
		
		$dri_position = new Zend_Form_Element_Text('dri_position');
		$dri_position->setAttribs(array(
				'class'=>'form-control',
				 
		));
		
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
			$dri_name->setValue($data["name"]);
			$dri_national->setValue($data["national_id"]);
			$dri_phone->setValue($data["phone"]);
			$sex->setValue($data["sex"]);
			$dri_dob->setValue($data["date_of_birdth"]);
			$dri_email->setValue($data["email"]);
			$dri_address->setValue($data["address"]);
			$dri_position->setValue($data["position"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($dri_name,$dri_phone,$dri_national,$dri_email,$dri_address,
									$dri_position,$sex,$status,$remark,$dri_dob));
		return $this;
	}
	
}