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
		
		$status_paid = $request->getParam('is_paid_balance');
		$optionsCOStatus=array('0'=>$tr->translate('CHOOSE_STATUS'),1=>$tr->translate('Paid Balance'),2=>$tr->translate('Paid Full'));
		$is_paid_balance=new Zend_Form_Element_Select('is_paid_balance');
		$is_paid_balance->setMultiOptions($optionsCOStatus);
		$is_paid_balance->setattribs(array(
				'id'=>'is_paid_balance',
				'class'=>'form-control'
		));
		$is_paid_balance->setValue($status_paid);
		$this->addElement($is_paid_balance);
		
		$po_invoice_status = new Zend_Form_Element_Select('po_invoice_status');
		$po_invoice_status->setAttribs(array(
			'class'=>'form-control',
			
		));
		$opt_in_stat = array(''=>$tr->translate("SELECT"),1=>$tr->translate("RECEIVED_INVOICE"),2=>$tr->translate('RECEIVE_INVOICE'));
		$po_invoice_status->setMultiOptions($opt_in_stat);
		$po_invoice_status->setValue($request->getParam('po_invoice_status'));
		$this->addElement($po_invoice_status);
		
		$db_re = new Sales_Model_DbTable_DbRequest();
		$row = $db_re->getPlan();
		$option = array(''=>$tr->translate("SELECT_PLAN"));
		if(!empty($row)){
			foreach($row as $rs){
				$option[$rs["id"]] = $rs["name"];
			}
		}
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$plan=new Zend_Form_Element_Select('plan');
		$nameValue = $request->getParam('plan');
		$plan ->setAttribs(array(
				'class' => 'validate[required] form-control select2me',
		));
		$plan->setMultiOptions($option);
		$plan->setValue($nameValue);
		$this->addElement($plan);
		
		$search_bydate = new Zend_Form_Element_Select('search_bydate');
		$search_bydate->setAttribs(array(
				'class'=>'form-control',
					
		));
		
		$appr_status = new Zend_Form_Element_Select("appr_status");
		$opt = array(''=>$tr->translate("SELECT"));
		$rspo_pedding = $db->getRequestStatusAprove();
		if(!empty($rspo_pedding)){
		    foreach ($rspo_pedding as $rs){
		        $opt[$rs["id"]] = $rs["name"];
		    }
		}
		$appr_status->setAttribs(array(
		    'class'=>'form-control select2me',
		));
		$appr_status->setMultiOptions($opt);
		$appr_status->setValue($request->getParam('appr_status'));
		$this->addElement($appr_status);
		
		$search_date = new Zend_Form_Element_Select('search_date');
		$search_date->setAttribs(array(
		    'class'=>'form-control',
		    
		));
		$opt_d = array(''=>$tr->translate("SELECT"),1=>$tr->translate("Daily"),2=>$tr->translate('Weekly'),3=>$tr->translate('Monthly'));
		$search_date->setMultiOptions($opt_d);
		$search_date->setValue($request->getParam('search_date'));
		$this->addElement($search_date);
		
		$opt_date = array(1=>$tr->translate("MR_DATE"),2=>$tr->translate('CHECK_DATE'),3=>$tr->translate('DATE_REQUEST_WORK_SPACE'));
		$search_bydate->setMultiOptions($opt_date);
		$search_bydate->setValue($request->getParam('search_bydate'));
		$this->addElement($search_bydate);
	}
	
}

