<?php

class Purchase_Form_FrmPayment extends Zend_Form
{
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function Payment($data=null)
    {
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
		$db_r = new Purchase_Model_DbTable_Dbpayment();
		$opt_pay_method = array(1=>$tr->translate("PAYMENT_BY_SUPLLIER"),2=>$tr->translate("PAYMENT_BY_INVOICE"));
		$payment_method = new Zend_Form_Element_Select("payment_method");
		$payment_method->setAttribs(array("class"=>"form-control"));
		$payment_method->setMultiOptions($opt_pay_method);
		$this->addElement($payment_method);
		
		$payment_number = new Zend_Form_Element_text('payment_number');
		$payment_number->setAttribs(array('class'=>'custom[number] form-control',"readOnly"=>"true"));
		$this->addElement($payment_number);
		$pay_code=  $db_r->getPayCode($result["branch_id"]);
		$payment_number->setvalue($pay_code);

    	$customerid=new Zend_Form_Element_Select('customer_id');
    	$customerid ->setAttribs(array(
    			'class' => 'form-control select2me',
    			'Onchange'=>'getInvoice(1)'
    			));
    	
    	//$options = $db->getAllCustomer(1);
		$options = $db->getAllVendor(1);
    	$customerid->setMultiOptions($options);
    	$this->addElement($customerid);
    	
    	/*$roder_element= new Zend_Form_Element_Text("receipt");
    	$roder_element->setAttribs(array('placeholder' => 'Optional','class'=>'form-control',"readonly"=>true,
    			"onblur"=>"CheckPOInvoice();"));
    	$this->addElement($roder_element);
    	$qo = $db->getReceiptNumber(1);
    	$roder_element->setValue($qo);
    	$this->addElement($roder_element);*/
    	
    	$invoice_id = new Zend_Form_Element_Select('invoice_id');
    	$invoice_id ->setAttribs(array('class'=>'form-control select2me','readonly'=>true));
    	$options = $db->getAllInvoicePO(1,1);
    	$invoice_id->setMultiOptions($options);
    	$invoice_id->setattribs(array(
    			'Onchange'=>'getInvoice(2)'));
    	$this->addElement($invoice_id);
    	    	
    	$rowspayment= $db->getGlobalDb('SELECT * FROM tb_paymentmethod WHERE status=1 ');
    	if($rowspayment) {
    		foreach($rowspayment as $readCategory) $options_cg[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($options_cg);
    	$this->addElement($paymentmethodElement);
    	$paymentmethodElement->setAttribs(array("class"=>"form-control select2me","onchange"=>"checkpayment();"));
    	
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$descriptionElement->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','require'=>true,'style'=>'text-align:right'));
    	$this->addElement($allTotalElement);
    	
//     	$netTotalElement = new Zend_Form_Element_Text('paid');
//     	$netTotalElement->setAttribs(array("class"=>"validate[required] form-control",'onchange'=>'doRemain();'));
//     	$this->addElement($netTotalElement);
    	
    	$remainlElement = new Zend_Form_Element_Hidden('balance');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',"class"=>"red form-control"));
    	$this->addElement($remainlElement);
    	
    	$date_inElement = new Zend_Form_Element_Text('expense_date');
    	$date =new Zend_Date();
    	$date_inElement ->setAttribs(array('class'=>'validate[required] form-control form-control-inline date-picker'));
    	$date_inElement ->setValue($date->get('MM/d/Y'));
    	$this->addElement($date_inElement);
    	
    	$holder_name = new Zend_Form_Element_Text('holder_name');
    	$date =new Zend_Date();
    	$holder_name ->setAttribs(array('class'=>'validate[required] form-control form-control-inline','readOnly'=>true));
    	$this->addElement($holder_name);
    	
    	$cheque_issue = new Zend_Form_Element_Text('cheque_issuedate');
    	$date =new Zend_Date();
    	$cheque_issue ->setAttribs(array('class'=>'validate[required] form-control form-control-inline date-picker'));
    	$cheque_issue ->setValue($date->get('MM/d/Y'));
    	$this->addElement($cheque_issue);
    	
    	$cheque_withdraw = new Zend_Form_Element_Text('cheque_withdrawdate');
    	$date =new Zend_Date();
    	$cheque_withdraw ->setAttribs(array('class'=>'validate[required] form-control form-control-inline date-picker'));
    	$cheque_withdraw ->setValue($date->get('MM/d/Y'));
    	$this->addElement($cheque_withdraw);
    	
    	$paidElement = new Zend_Form_Element_Hidden('paid');
    	$paidElement->setAttribs(array('class'=>'validate[required,custom[number]] form-control','onkeyup'=>'doRemain();'));
    	$this->addElement($paidElement);
    	
    	$cheque = new Zend_Form_Element_Text('cheque');
    	$cheque->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','require'=>true,"placeHolder"=>"Cheque Number"));
    	$this->addElement($cheque);
    	
    	$bank = new Zend_Form_Element_Text('bank_name');
    	$bank->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','require'=>true,"placeHolder"=>"Bank Name"));
    	$this->addElement($bank);
    	
    	$paid_dollar = new Zend_Form_Element_Text('paid_dollar');
    	$paid_dollar->setAttribs(array("class"=>"validate[required] form-control","onkeyup"=>"paidtotal(1);",'require'=>true,"placeHolder"=>"Paid in Dollar"));
    	$this->addElement($paid_dollar);
    	
    	$paid_riel = new Zend_Form_Element_Text('paid_riel');
    	$paid_riel->setAttribs(array("class"=>"form-control","onkeyup"=>"paidtotal(2);","placeHolder"=>"Paid in Riel"));
    	$this->addElement($paid_riel);
    	
    	$exchange_rate = new Zend_Form_Element_Text('exchange_rate');
    	$exchange_rate->setAttribs(array("class"=>"form-control",'readonly'=>'readonly'));
    	$exchange_value = 4100;
    	$exchange_rate->setValue($exchange_value);
    	$this->addElement($exchange_rate);
		
		$account_name = new Zend_Form_Element_Text("acc_name");
		$account_name->setAttribs(array("class"=>"form-control",'readOnly'=>true));
		$this->addElement($account_name);
		
		
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','date_in'));
    		if($data != null) {
    			//$idElement = new Zend_Form_Element_Hidden('id');
    			//$this->addElement($idElement);
    			$invoice_id->setValue($data['invoice_id']);
    			$payment_method->setValue($data["payment_type"]);
				$customerid->setValue($data["vendor_id"]);
				$payment_number->setValue($data["pol_no"]);
    			$paymentmethodElement->setValue($data["payment_id"]);
				$holder_name->setValue($data["withdraw_name"]);
				$account_name->setValue($data["bank_name"]);
				$date_inElement->setValue(date("m/d/Y",strtotime($data["expense_date"])));
				//$locationID->setValue($data["invoice_id"]);
    		} else {
    	}
     	return $this;
    }

}

