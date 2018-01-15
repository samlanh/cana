<?php
class Billing_TypeconcreteController extends Zend_Controller_Action
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
    	$db = new Billing_Model_DbTable_DbTypeconcrete();
    	$glob_rs=new Application_Model_GlobalClass();
    	$rows = $db->getAllTypeConcrete($search);
    	$rows=$glob_rs->getImgStatus($rows, BASE_URL);
    	$this->view->rs = $rows;
    	$list = new Application_Form_Frmlist();
    	$columns=array("TRUCT_CAT_NAME_EN","TRUCT_CAT_NAME_EN","DATE","STATUS");
    	$link=array(
    			'module'=>'billing','controller'=>'typeconcrete','action'=>'edit',
    	);
    	
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('name_kh'=>$link,'name_en'=>$link,'date'=>$link));
    	$formFilter = new Billing_Form_FrmSearch();
    	$form=$formFilter->formSearch();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->formFilter = $form;
    	
    }
	public function addAction()
	{
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Billing_Model_DbTable_DbTypeconcrete();
			$db->add($data);
			if(isset($data['saveclose'])){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/typeconcrete');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/typeconcrete/add');
			}
		}
		$formFilter = new Billing_Form_FrmTrucktype();
		$formAdd = $formFilter->frm_truck_type_concrete();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Billing_Model_DbTable_DbTypeconcrete();
		$row=$db->getTypeConcreteById($id);
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			$db->edit($data);
			if(isset($data['saveclose'])){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/billing/typeconcrete');
			}
		}
		$formFilter = new Billing_Form_FrmTrucktype();
		$formAdd = $formFilter->frm_truck_type_concrete($row);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
		
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

