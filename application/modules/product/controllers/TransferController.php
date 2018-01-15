<?php
class Product_transferController extends Zend_Controller_Action
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
    	$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'		=>	'',
    			'start_date'	=>	date("Y-m-d"),
				'end_date'		=>	date("Y-m-d"),
    			'status'		=>	1,
    			'branch'		=>	'',
    		);
    	}
    	$this->view->product = $db->getTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->add(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
			$rs = $db->getTransferById($id);
			$rs_detail = $db->getTransferDettail($id);
			$this->view->rs_detail = $rs_detail;
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->add($rs);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
	}
	//view category 27-8-2013
	
	function requestlistAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'		=>	'',
    			'start_date'	=>	date("m/d/Y"),
				'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function addrequestAction(){
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addRequest($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/requestlist');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->addRequest(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
			$items = new Application_Model_GlobalClass();
			//$this->view->items = $items->getProductOption();
		//$items = new Application_Model_GlobalClass();
			$this->view->product = $items->getAllProduct();
	}
	
	function editrequestAction(){
		
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->editRequest($post);
					
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/requestlist');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$row = $db->getReqTransferById($id);
			//print_r($row);
			$this->view->rs_detail = $db->getReqTransferDetail($id);
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->addRequest($row);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
			$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
	}
	
	function requestcheckAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'		=>	'',
    			'start_date'	=>	date("m/d/Y"),
				'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	function addrequestcheckAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->checkRequest($post);
					
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/requestcheck/');
					}else{
						Application_Form_FrmMessage::redirectUrl('/product/transfer/requestcheck/');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$this->view->rs= $db->getReqTransferById($id);
			$this->view->rs_detail = $db->getReqTransferDetail($id);
			
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
			
	}
	
	function requestapprAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'	=>	'',
    			'start_date'	=>	date("m/d/Y"),
    			'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	function addrequestapprAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->ApproveRequest($post);
					
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/requestappr/');
					}else{
						Application_Form_FrmMessage::redirectUrl('/product/transfer/requestappr/');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$this->view->rs= $db->getReqTransferById($id);
			$this->view->rs_detail = $db->getReqTransferDetail($id);
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
			
	}
	
	function transferlistAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'		=>	'',
    			'start_date'	=>	date("m/d/Y"),
				'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function maketransferAction(){
		
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->makeTransfer($post);
					
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/transferlist');
					}else{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/transferlist');
					}
				}catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				}
			}
			$row = $db->getReqTransferById($id);
			//print_r($row);
			$this->view->rs_detail = $db->getReqTransferDetail($id);
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->makeTransfers($row);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
	}
	function edittransferAction(){
		
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->editTransfer($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/transferlist');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
		$rs = $db->getTransferById($id);
		$rs_detail = $db->getTransferDettail($id);
		$this->view->rs_detail = $rs_detail;
		$formProduct = new Product_Form_FrmTransfer();
		$formStockAdd = $formProduct->editTransfers($rs);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->formFilter = $formStockAdd;
	}
	function receiverequestAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'		=>	'',
    			'start_date'	=>	date("m/d/Y"),
				'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function addreceiveAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->ReceiveTransfer($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer/receiverequest');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
		$rs = $db->getTransferById($id);
		$rs_detail = $db->getTransferDettail($id);
		$this->view->rs_detail = $rs_detail;
		$formProduct = new Product_Form_FrmReceive();
		$formStockAdd = $formProduct->receiveTransfers($rs);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->formFilter = $formStockAdd;
	}
	function editreceiveAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
		$rs = $db->getTransferById($id);
		$rs_detail = $db->getTransferDettail($id);
		$this->view->rs_detail = $rs_detail;
		$formProduct = new Product_Form_FrmReceive();
		$formStockAdd = $formProduct->makeTransfers($rs);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->formFilter = $formStockAdd;
	}
	
	function receivelistAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'		=>	'',
    			'start_date'	=>	date("m/d/Y"),
				'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function requestnoteAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		Application_Form_FrmMessage::message("Request Transfer Not yet Make Transfer");
    		$this->_redirect("/product/transfer/transferlist");
    	}
    	$query = new Product_Model_DbTable_DbTransfer();
    	$this->view->product =  $query->getRequestTransferPrint($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}

	public function viewtransferAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		Application_Form_FrmMessage::message("Request Transfer Not yet Make Transfer");
    		$this->_redirect("/product/transfer/transferlist");
    	}
    	$query = new Product_Model_DbTable_DbTransfer();
    	$this->view->product =  $query->getRequestTransferById($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}	
	
	public function viewreceiveAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
			Application_Form_FrmMessage::message("Transfer Not yet Receive");
    		$this->_redirect("/product/transfer/receivelist");
    	}
    	$query = new Product_Model_DbTable_DbTransfer();
    	$this->view->product =  $query->getReceiveById($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}	
	
	public function getProductAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbTransfer();
			$data = $this->getRequest()->getPost();
			$rs = $db->getProductByIds($data["id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
}

