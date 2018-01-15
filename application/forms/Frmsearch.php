<?php
class Application_Form_Frmsearch extends Zend_Form
{
	public function init()
	{
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $db->getBranch();
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
				//'required'=>'required',
				'Onchange'	=>	'addNewProLocation()'
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam('branch'));
		$this->addElement($branch);
		
		$po_pedding = new Zend_Form_Element_Select("po_pedding");
		$opt = array(''=>$tr->translate("SELECT"));
		$rspo_pedding = $db->getPurchasePedding();
		if(!empty($rspo_pedding)){
			foreach ($rspo_pedding as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$po_pedding->setAttribs(array(
				'class'=>'form-control select2me',
				//'required'=>'required',
				'Onchange'	=>	'addNewProLocation()'
		));
		$po_pedding->setMultiOptions($opt);
		$po_pedding->setValue($request->getParam('po_pedding'));
		$this->addElement($po_pedding);
		
		$nameValue = $request->getParam('text_search');
		$nameElement = new Zend_Form_Element_Text('text_search');
		$nameElement->setAttribs(array(
				'class'=>'form-control'
				));
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		
		$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" AND status=1 ');
		$options=array($tr->translate('Choose Suppliyer'));
		$vendorValue = $request->getParam('suppliyer_id');
		if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
		$vendor_element=new Zend_Form_Element_Select('suppliyer_id');
		$vendor_element->setMultiOptions($options);
		$vendor_element->setAttribs(array(
				'id'=>'suppliyer_id',
				'class'=>'form-control select2me'
		));
		$vendor_element->setValue($vendorValue);
		$this->addElement($vendor_element);

		
		/////////////Date of lost item		/////////////////
		$startDateValue = $request->getParam('start_date');
		$endDateValue = $request->getParam('end_date');
		
		if($endDateValue==""){
			$endDateValue=date("m/d/Y");
			//$startDateValue=date("m/d/Y");
		}
		if($startDateValue==""){
			//$startDateValue=date("m/01/Y");
			//$startDateValue=date("m/d/Y");
		}
		
		$startDateElement = new Zend_Form_Element_Text('start_date');
		$startDateElement->setValue($startDateValue);
		
		$startDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
		$startDateElement->setValue($startDateValue);
		$this->addElement($startDateElement);
		
		$endDateElement = new Zend_Form_Element_Text('end_date');
		$endDateElement->setValue($endDateValue);
		$this->addElement($endDateElement);
		$endDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker'
		));
		
// 		$rs=$db->getGlobalDb('SELECT DISTINCT name,id FROM tb_sublocation WHERE Name!="" AND status=1 ');
// 		$options=array($tr->translate('Please_Select'));
// 		$locationValue = $request->getParam('LocationId');
// 		foreach($rs as $read) $options[$read['id']]=$read['name'];
// 		$location_id=new Zend_Form_Element_Select('id');
// 		$location_id->setMultiOptions($options);
// 		$location_id->setAttribs(array(
// 				'id'=>'LocationId',
// 				'onchange'=>'this.form.submit()',
// 				'class'=>'form-control'
				
// 		));
// 		$location_id->setValue($locationValue);
// 		$this->addElement($location_id);
	  
		$statusCOValue=4;
		$statusCOValue = $request->getParam('purchase_status');
		$optionsCOStatus=array(0=>$tr->translate('CHOOSE_STATUS'),2=>$tr->translate('OPEN'),3=>$tr->translate('IN_PROGRESS'),4=>$tr->translate('PAID'),5=>$tr->translate('RECEIVED'),6=>$tr->translate('MENU_CANCEL'));
		$statusCO=new Zend_Form_Element_Select('purchase_status');
		$statusCO->setMultiOptions($optionsCOStatus);
		$statusCO->setattribs(array(
				'id'=>'status',
				'class'=>'form-control'
		));
		
		$statusCO->setValue($statusCOValue);
		$this->addElement($statusCO);
		
		$po_invoice_status = new Zend_Form_Element_Select('po_invoice_status');
		$po_invoice_status->setAttribs(array(
			'class'=>'form-control',
			
		));
		$opt_in_stat = array(''=>$tr->translate("SELECT"),1=>$tr->translate("RECEIVED_INVOICE"),2=>$tr->translate('RECEIVE_INVOICE'));
		$po_invoice_status->setMultiOptions($opt_in_stat);
		$po_invoice_status->setValue($request->getParam('po_invoice_status'));
		$this->addElement($po_invoice_status);
	}
	
}

