<?php
class Product_locationController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
		$db = new Product_Model_DbTable_DbLocation();
		$formFilter = new Product_Form_FrmBranchFilter();
		$frmsearch = $formFilter->branchFilter();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'branch_name'	=>	'',
					'status'	=>	1
			);
		}
		
		$columns=array("LOCAT_NAME","PREFIX","STATUS");
		$link=array(
				'module'=>'product','controller'=>'location','action'=>'edit',
		);
		
		$rows = $db->getAllBranch($data);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('name'=>$link,'branch_code'=>$link,'prefix'=>$link,
				'contact'=>$link));
				
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$result = $db->getAllBranch($data);
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbLocation();
			$db->add($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("INSER_SUCCESS", '/product/location/index');
			}
			else{
				Application_Form_FrmMessage::Sucessfull("INSER_SUCCESS", '/product/location/index/add');
			}
		}
		$formFilter = new Product_Form_FrmBranch();
		$formAdd = $formFilter->branch();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Product_Model_DbTable_DbLocation();
		
		if($id==0){
			$this->_redirect('/product/location/index/add');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db->edit($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/product/location/index');
			}
		}
		$rs = $db->getBranchById($id);
		$formFilter = new Product_Form_FrmBranch();
		$formAdd = $formFilter->branch($rs);
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
	public function addbranchAction(){
		$post=$this->getRequest()->getPost();
		$db = new Product_Model_DbTable_DbLocation();
		$post['branch_name']=$post['txtbranch_name'];
		$result = $db->addBranchAjax($post);
		echo Zend_Json::encode($result);
		exit();
	}

}

