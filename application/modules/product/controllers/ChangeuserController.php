<?php
class Product_changeuserController extends Zend_Controller_Action
{
public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
    }
    public function indexAction()
    {
    	$id=$this->getRequest()->getParam("id");
    	$session_user=new Zend_Session_Namespace('auth');
    	$session_user->location_id=$id;
		//print_r($session_user->location_id);
		//echo "<script>alert(".$session_user->location_id.")</script>";
    	$this->_redirect("/product/index");
	}
	
}

