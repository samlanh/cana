<?php
class report_ProductController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		//if(empty($rs)){
		//	Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		//}
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	
    
    }
    public function rptcurrentstockAction()
    {
    	$db = new Product_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
    
    
    public function rptproductlistAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
    
    public function rptproductlistprintAction()
    {
        $db = new report_Model_DbProduct();
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
        }else{
            $data = array(
                'ad_search'	=>	'',
                'branch'	=>	'',
                'brand'		=>	'',
                'category'	=>	'',
                'model'		=>	'',
                'color'		=>	'',
                'size'		=>	'',
                'status'	=>	1
            );
        }
        $this->view->product = $db->getAllProduct($data);
        $formFilter = new Product_Form_FrmProduct();
        $this->view->formFilter = $formFilter->productFilter();
        Application_Model_Decorator::removeAllDecorator($formFilter);
        
        $session_user=new Zend_Session_Namespace('auth');
        $db_globle = new Application_Model_DbTable_DbGlobal();
        $this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
	
	public function rptproductwarningAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProduct($data,1);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
    
    public function rptadjuststockAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'pro_id'	=>	'',
					'branch'	=>	-1,
     				'brand'		=>	'',
     				'category'	=>	'',
     				'model'		=>	'',
     				'color'		=>	'',
     				'size'		=>	'',
//     				'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllAdjustStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
    public function rpttransferAction()
    {
    	$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'tran_num'	=>	'',
    			'tran_date'	=>	date("m/d/Y"),
    			'type'		=>	'',
    			'status'	=>	1,
    			'to_loc'	=>	'',
    		);
    	}
    	$this->view->product = $db->getTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	
	public function rptrequesttransferAction()
    {
    	$db = new report_Model_DbTransfer();
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
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	
	public function rptcheckrequesttransferAction()
    {
    	$db = new report_Model_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'	=>	'',
    			'start_date'	=>	date("m/d/Y"),
    			'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
				'check_stat'	=>	-1,
    		);
    	}
    	$this->view->product = $db->getRequestTransferCheck($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	public function rpttransferlistAction()
    {
    	$db = new report_Model_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'avd_search'	=>	'',
    			'start_date'	=>	date("m/d/Y"),
    			'end_date'		=>	date("m/d/Y"),
    			'status'		=>	1,
    			'branch'		=>	-1,
				'check_stat'	=>	-1,
    		);
    	}
    	$this->view->product = $db->getTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	public function rptreceivetransferAction()
    {
    	$db = new report_Model_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'tran_num'	=>	'',
    			'tran_date'	=>	date("m/d/Y"),
    			'type'		=>	'',
    			'status'	=>	1,
    			'to_loc'	=>	'',
    		);
    	}
    	$this->view->product = $db->getReceiveTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
    
    public function stockclosingAction()
    {
    	$db = new Product_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',
    			'category'	=>	'',
    			'model'		=>	'',
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getSearchClosing($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    
    	$session_user=new Zend_Session_Namespace('auth');
    	$db_globle = new Application_Model_DbTable_DbGlobal();
    	$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
    public function submitclosingAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$post=$this->getRequest()->getPost();
    			$db = new Product_Model_DbTable_DbProduct();
//     			print_r($post);exit();
    			$result =$db->submitClosingStock($post);
//     			exit();
    			echo Zend_Json::encode($result);
//     			exit();
    		}catch (Exception $e){
    			$result = array('err'=>$e->getMessage());
    			echo Zend_Json::encode($result);
    			exit();
    		}
    	}
    }
	
}