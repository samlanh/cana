<?php 
class Billing_Form_FrmSearch extends Zend_Form
{
	 
	public function init()
    {	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	 
	}
	/////////////	Form vendor		/////////////////
	public function formSearch($data=null) {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		//$db=new Application_Model_DbTable_DbGlobal();
		
		
    	$search_name = new Zend_Form_Element_Text('search_name');
    	$search_name->setAttribs(array(
    			'class'=>'form-control',
    			//'required'=>'required'
    	));
    	$search_name->setValue($request->getParam("search_name"));
    	
    	$status = new Zend_Form_Element_Select("status_serach");
    	$status->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
    	$status->setMultiOptions($opt);
    	$status->setValue($request->getParam("status_serach"));
    	
    	$remark = new Zend_Form_Element_Text('remark');
    	$remark->setAttribs(array(
    			'class'=>'form-control',
    	));
    	
    	//date
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$dates = date("Y-m-d");
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'required'=>false));
    	$_date = $request->getParam("start_date");
    	if(empty($_date)){
    		$_date = date('Y-m-d');
    	}
    	$start_date->setValue($_date);
    	
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'required'=>false));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	$this->addElements(array($search_name,$start_date,$end_date,$status));
    	return $this;
	}
	public function frmRetrunIn($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		//$db=new Application_Model_DbTable_DbGlobal();
		
		////////////////////////////////////////////////////////Purchase*****/////////////////////////////////////////////
		
		//get sales or purchase id text
		$returnOutValue = $request->getParam('invoice_in');
		$returnOutElement = new Zend_Form_Element_Text('invoice_in');
		$returnOutElement->setValue($returnOutValue);
		$this->addElement($returnOutElement);
		
		$outValue = $request->getParam('invoice_out');
		$returnInElement = new Zend_Form_Element_Text('invoice_out');
		$returnInElement->setValue($outValue);
		$this->addElement($returnInElement);
		
		$startDateValue = $request->getParam('search_start_date');
		$startDateElement = new Zend_Form_Element_Text('search_start_date');
		$startDateElement->setValue($startDateValue);
		$this->addElement($startDateElement);
			
		$endDateValue = $request->getParam('search_end_date');
		$endDateElement = new Zend_Form_Element_Text('search_end_date');
		$endDateElement->setValue($endDateValue);
		$this->addElement($endDateElement);
		
		Application_Form_DateTimePicker::addDateField(array('search_start_date', 'search_end_date'));
		
		return $this;
	}

}