<?php

class Sales_Form_FrmStaff extends Zend_Form
{
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function SaleOrder($data=null,$type=null)
    {
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$status=new Zend_Form_Element_Select('status');
    	$status ->setAttribs(array(
    			'class' => 'form-control',
    			));
    	$options = $db->getAllCustomer(1);
    	$status->setMultiOptions(array('1'=>'Active' ,'2'=> 'Deactive'  ));
    	$this->addElement($status);
    	
    	$name= new Zend_Form_Element_Text("name");
    	$name->setAttribs(array('class'=>'form-control'));
    	$this->addElement($name);
		
		$position= new Zend_Form_Element_Text("position");
    	$position->setAttribs(array('class'=>'form-control'));
    	$this->addElement($position);
		
		$phone= new Zend_Form_Element_Text("phone");
    	$phone->setAttribs(array('class'=>'form-control'));
    	$this->addElement($phone);
		
		$email= new Zend_Form_Element_Text("email");
    	$email->setAttribs(array('class'=>'form-control'));
    	$this->addElement($email);
    		if($data != null) {
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			$idElement->setValue($data["id"]);
    			$name->setValue($data["name"]);
				$position->setValue($data["position"]);
				$phone->setValue($data["phone"]);
				$email->setValue($data["email"]);
    			$status->setValue($data['status']);
    		} else {
			}
     	return $this;
    }

}

