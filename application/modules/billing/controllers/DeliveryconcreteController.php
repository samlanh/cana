<?php
class Billing_DeliveryconcreteController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    		 
    	}
    	else{
    		$search =array(
    				'search_name'=>'',
    				'status_serach'=>1,
//     				'start_date'=>date("Y-m-d"),
//     				'end_date'=>date("Y-m-d"),
    		);
    	}
    	$db = new Billing_Model_DbTable_DbDeliveryconcrete();
    	$glob_class=new Application_Model_GlobalClass();
    	$rows = $db->getAllBilling($search);
    	$rows=$glob_class->getImgStatus($rows, BASE_URL);
    	$this->view->rs = $rows;
    	$list = new Application_Form_Frmlist();
    	$columns=array("NUMBER_NO","SERAIL_NO","CUSTOMER_NAME","PRODUCT_NAME","DELIVERY_TO",
    			"DRIVER_NAME","DRIVER_PHONE","DEPART_TIME","ARRIVE_TIME","TOTAL_WEIGHT","DIVERY_DATE","STATUS");
    	$link=array(
    			'module'=>'billing','controller'=>'deliveryconcrete','action'=>'edit',
    	);
    	
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('aouto_num'=>$link,'serail_id'=>$link,));
    	$formFilter = new Billing_Form_FrmSearch();
    	$form=$formFilter->formSearch();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->formFilter = $form;
    	
    }
	public function addAction()
	{
		$db = new Billing_Model_DbTable_DbDeliveryconcrete();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data);exit();
			$db->add($data);
			if(isset($data['btnsavenew'])){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/deliveryconcrete/add');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/deliveryconcrete');
			}
		}
		$formFilter = new Billing_Form_FrmDeliveryconcrete();
		$this->view->cus_form=$formFilter->frm_customer();
		$this->view->pro_form=$formFilter->frm_product();
		$this->view->car_form=$formFilter->frm_car();
		$formAdd = $formFilter->frm_product();
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$u_driver = $db->getLocationAssign();
		$this->view->location = $u_driver;
		$this->view->truck_no=$db->getTruckCode();
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Billing_Model_DbTable_DbDeliveryconcrete();
		$row=$db->getBillingById($id);
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data);exit();
			$data['id']=$id;
			$db->edit($data);
			if(isset($data['saveclose'])){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/deliveryconcrete');
			}else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/deliveryconcrete');
			}
		}
		$formFilter = new Billing_Form_FrmDeliveryconcrete();
		$formAdd=$this->view->cus_form=$formFilter->frm_customer($row);
		$formAdd=$this->view->pro_form=$formFilter->frm_product($row);
		$formAdd=$this->view->car_form=$formFilter->frm_car($row);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$u_driver = $db->getLocationAssign();
		$this->view->location = $u_driver;
		$this->view->truck_no=$db->getTruckCode();
	}
	//view category 27-8-2013
	
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Product_Model_DbTable_DbAddProduct();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	function getcustomerAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Billing_Model_DbTable_DbProduct();
			$qo = $db->getCustomerName($post['cus_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function productinfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Billing_Model_DbTable_DbProduct();
			$qo = $db->getProductName($post['pro_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function truckinfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Billing_Model_DbTable_DbProduct();
			$qo = $db->getTruckInfo($post['tru_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function driverinfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Billing_Model_DbTable_DbProduct();
			$qo = $db->getDriverInfo($post['driver_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
}

