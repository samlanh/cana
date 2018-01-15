<?php

class Sales_Form_FrmDeliver extends Zend_Form
{
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function DN($data=null,$type=null)
    {
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
		$date = date("m/d/Y");
    	
    	$customerid=new Zend_Form_Element_Select('customer_id');
    	$customerid ->setAttribs(array(
    			'class' => 'validate[required] form-control select2me',
    			'Onchange'=>'productOption()',
				'id'=>'customer_id',"readOnly"=>true
    			));
    	$options = $db->getAllCustomer(1);
    	$customerid->setMultiOptions($options);
    	$this->addElement($customerid);
    	
    	
    	$sale_no= new Zend_Form_Element_Text("so_number");
    	$sale_no->setAttribs(array('class'=>'form-control','style'=>'',
    			"readOnly"=>true));
    	$this->addElement($sale_no);
		
		$dn_no= new Zend_Form_Element_Text("dn_no");
    	$dn_no->setAttribs(array('class'=>'form-control','style'=>'',
    			"readOnly"=>true));
    	$qo = $db->getSalesNumber($result["branch_id"]);
    	$dn_no->setValue($qo);
    	$this->addElement($dn_no);
		
		$date_dn= new Zend_Form_Element_Text("date_dn");
    	$date_dn->setAttribs(array('class'=>'form-control date-picker','style'=>'',
    			"readOnly"=>true));
    	$date_dn->setValue($date);
    	$this->addElement($date_dn);
		
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
    	    	
    	
    	
    	$opt=array();
    	$rows = $db->getGlobalDb('SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1');
    	if(!empty($rows)) {
    		foreach($rows as $rs) $opt[$rs['id']]=$rs['name'];
    	}
    	$saleagent_id = new Zend_Form_Element_Select('saleagent_id');
    	$saleagent_id->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$saleagent_id->setMultiOptions($opt);
    	$this->addElement($saleagent_id);
    	
    	
    	
    	
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

