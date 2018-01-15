<?php
class Billing_BillingproductController extends Zend_Controller_Action
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
    	$db = new Billing_Model_DbTable_DbProduct();
    	$glob_class=new Application_Model_GlobalClass();
    	$rows = $db->getAllProduct($search);
    	$rows=$glob_class->getImgStatus($rows, BASE_URL);
    	$this->view->rs = $rows;
    	$list = new Application_Form_Frmlist();
    	$columns=array("LOCATION_NAME","PRODUCT_NO","PRODUCT_NAME","TYPE_CONCRETE","STRENGTH","SLUMP","DELVERY_QTY",
    			"TOTAL_DEL_QTY","TRIP_NO","QTY","DATE","STATUS");
    	$link=array(
    			'module'=>'billing','controller'=>'billingproduct','action'=>'edit',
    	);
    	
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('location_id'=>$link,'barcode'=>$link,'item_name'=>$link,'concrete_id'=>$link));
    	$formFilter = new Billing_Form_FrmSearch();
    	$form=$formFilter->formSearch();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->formFilter = $form;
    	
    }
	public function addAction()
	{
		$db = new Billing_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data);exit();
			$db->add($data);
			if(isset($data['saveclose'])){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/billingproduct');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/billingproduct/add');
			}
		}
		$formFilter = new Billing_Form_FrmBillingproduct();
		$formAdd = $formFilter->frm_product();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$u_driver = $db->getLocationAssign();
		$this->view->location = $u_driver;
		$this->view->truck_no=$db->getTruckCode();
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Billing_Model_DbTable_DbProduct();
		$row=$db->getProductById($id);
		$this->view->prlocation=$db->getPolocation($id);
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			$db->edit($data);
			if(isset($data['saveclose'])){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/billingproduct');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/billingproduct/add');
			}
		}
		$formFilter = new Billing_Form_FrmBillingproduct();
		$formAdd = $formFilter->frm_product($row);
		$this->view->frmAdd = $formAdd;
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
	
}

