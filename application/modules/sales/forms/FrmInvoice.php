<?php

class Sales_Form_FrmInvoice extends Zend_Form
{
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function invoice($data=null,$type=null)
    {
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
		$date = date("m/d/Y");
		
		$locationID = new Zend_Form_Element_Select('branch_id');
    	$locationID ->setAttribs(array('class'=>'validate[required] form-control',"readOnly"=>true));
		$options = $db->getAllLocation(1);
    	$locationID->setMultiOptions($options);
    	$locationID->setattribs(array(
    			'Onchange'=>'getsaleOrderNumber()',));
    	$this->addElement($locationID);
		
		$invoice = $db->getInvoiceNumber($result['branch_id']);
		$dn = $db->getallDN();
		
		$inovice_method=new Zend_Form_Element_Select('inovice_method');
    	$inovice_method ->setAttribs(array(
    			'class' => 'validate[required] form-control select2me',
    			'Onchange'=>'checkControll()',
    			));
    	$options = array(''=>$tr->translate("SELECT_METHOD"),'1'=>$tr->translate("INVOICE_BY_CUSTOMER"),'2'=>$tr->translate("INVOICE_BY_INVOICE"));
    	$inovice_method->setMultiOptions($options);
    	$this->addElement($inovice_method);
    	
    	$customerid=new Zend_Form_Element_Select('customer_id');
    	$customerid ->setAttribs(array(
    			'class' => 'validate[required] form-control select2me',
    			'Onchange'=>'getInvoice(1)',
				'id'=>'customer_id',"readOnly"=>true
    			));
    	$options = $db->getAllCustomer(1);
    	$customerid->setMultiOptions($options);
    	$this->addElement($customerid);
		
		$opt = array(''=>$tr->translate('SELECT_DN'));
		$dn_no= new Zend_Form_Element_Select("dn_no");
    	$dn_no->setAttribs(array('class'=>'form-control select2me','style'=>'','Onchange'=>'getInvoice(2)',
    			"readOnly"=>true));
		if(!empty($dn)){
			foreach($dn as $rs){
				$opt[$rs["id"]] = $rs["deliver_no"];
			}
		}
		$dn_no->setMultiOptions($opt);
    	$this->addElement($dn_no);
		
		$invoice_no= new Zend_Form_Element_Text("invoice_no");
    	$invoice_no->setAttribs(array('class'=>'form-control','style'=>'',
    			"readOnly"=>true));
		$invoice_no->setValue($invoice);
    	$this->addElement($invoice_no);
		
		
		$invoice_date= new Zend_Form_Element_Text("invoice_date");
    	$invoice_date->setAttribs(array('class'=>'form-control date-picker','style'=>'',
    			"readOnly"=>true));
    	$invoice_date->setValue($date);
    	$this->addElement($invoice_date);
		
		$date_sold= new Zend_Form_Element_Text("date_sold");
    	$date_sold->setAttribs(array('class'=>'form-control date-picker','style'=>'',
    			"readOnly"=>true));
    	$this->addElement($date_sold);
    	
    	
    	$user= $this->GetuserInfo();
    	$options="";
		
		$locationID = new Zend_Form_Element_Select('branch_id');
    	$locationID ->setAttribs(array('class'=>'validate[required] form-control',"readOnly"=>true));
		$options = $db->getAllLocation(1);
    	$locationID->setMultiOptions($options);
    	$locationID->setattribs(array(
    			'Onchange'=>'getsaleOrderNumber()',));
    	$this->addElement($locationID);
		
		$remark= new Zend_Form_Element_Text("remark");
    	$remark->setAttribs(array('class'=>'form-control ','style'=>'',));
    	//$remark->setValue($remark);
    	$this->addElement($remark);
    	    	
    	
    	
    	$opt=array();
    	$rows = $db->getGlobalDb('SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1');
    	if(!empty($rows)) {
    		foreach($rows as $rs) $opt[$rs['id']]=$rs['name'];
    	}
    	$saleagent_id = new Zend_Form_Element_Select('saleagent_id');
    	$saleagent_id->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$saleagent_id->setMultiOptions($opt);
    	$this->addElement($saleagent_id);
		
		$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','require'=>true,'style'=>'text-align:right'));
    	$this->addElement($allTotalElement);
    	
//     	$netTotalElement = new Zend_Form_Element_Text('paid');
//     	$netTotalElement->setAttribs(array("class"=>"validate[required] form-control",'onchange'=>'doRemain();'));
//     	$this->addElement($netTotalElement);
    	
    	$remainlElement = new Zend_Form_Element_Hidden('balance');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',"class"=>"red form-control",'style'=>'text-align:right'));
    	$this->addElement($remainlElement);
		
		$paidElement = new Zend_Form_Element_Hidden('paid');
    	$paidElement->setAttribs(array('class'=>'validate[required,custom[number]] form-control','onkeyup'=>'doRemain();','style'=>'text-align:right'));
    	$this->addElement($paidElement);
    	
    	
    	
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','date_in'));
    		if($data != null) {
			
				$dn_no->setValue($db->getDeliverNumber($data[0]["branch_id"]));
				$so =$data[0]["sale_no"];
				$sale_no->setValue($so);
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			$idElement->setValue($data[0]["id"]);
				
    			$customerid->setValue($data[0]["customer_id"]);
    			$locationID->setValue($data[0]['branch_id']);
				$date_sold->setValue(date("m/d/Y",strtotime($data[0]["date_sold"])));
    			
    			/*$currencyElement->setValue($data['currency_id']);
    			$saleagent_id->setValue($data['saleagent_id']);
    			$descriptionElement->setValue($data['remark']);
    			$qouate_date->setValue($date_quote);
    			$roder_element->setValue($data['quoat_number']);
    			$totalAmountElement->setValue($data['all_total']);
    			$dis_valueElement->setValue($data['discount_value']);
    			$allTotalElement->setValue($data['net_total']);*/
    		} else {
			}
     	return $this;
    }

}

