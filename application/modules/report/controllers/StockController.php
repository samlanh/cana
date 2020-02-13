<?php
class report_StockController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	
    
    }
    public function rptstocksummaryAction()
    {
    	$db = new report_Model_DbStock();
		$data = array();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']= $data['start_date'];
    		$data['end_date']=  $data['end_date'];
    	}else{
    		$data = array(
    			'ad_search'		=>	'',
    			'start_date'	=>	date('2016-m-d'),
    			'end_date'		=>	date('Y-m-d'),
    			'category'		=>	35,
    		    'product_id'    => -1,
    			'suppliyer_id'	=>	-1,
    		    'branch'		=>	0
    		);
    	}
		$this->view->start_date = $data["start_date"];
		$this->view->end_date = $data["end_date"];
		$this->view->search = $data;
    	$this->view->stockin = $db->getAllStockSummaryreport($data);
	
    	$formFilter = new report_Form_FrmSearch();
    	$this->view->formFilter = $formFilter->formSearch($data);
    	 
    	//Application_Model_Decorator::removeAllDecorator($formFilter);
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
		
		$db=new report_Model_DbStock();
		$row_cat = $db->getCategoryOption();
		$this->view->cat=$row_cat;
    }
    
    public function rptstockinAction()
    {
    	$db = new report_Model_DbStock();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'start_date'	=>	date('Y-m-1'),
    				'end_date'		=>	date('Y-m-d'),
    				'category'		=>	-1,
    				'suppliyer_id'	=>	-1,
    				'add_item'		=>	-1,
    				'branch'		=>-1,
    		);
    	}
		$this->view->start_date = $data["start_date"];
		$this->view->end_date = $data["end_date"];
    	$this->view->stockin = $db->getAllStockinReport($data);
    	$formFilter = new report_Form_FrmSearch();
    	$this->view->formFilter = $formFilter->formSearch();
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
    
    public function rptstockoutAction()
    {
    	$db = new report_Model_DbStock();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'start_date'	=>	date('Y-m-1'),
    				'end_date'		=>	date('Y-m-d'),
    				'category'		=>	-1,
    				'add_item'		=>	-1,
    				'branch'		=>-1,
    		);
    	}
		$this->view->start_date = $data["start_date"];
		$this->view->end_date = $data["end_date"];
    	$this->view->stockout = $db->getAllStockoutport($data);
    	$formFilter = new report_Form_FrmSearch();
    	$frm_search = $formFilter->formSearch();
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->formFilter = $frm_search;
    	
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
	
	public function rptworkingstoneAction()
    {
    	$db = new report_Model_DbStock();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=$data['start_date'];
    		$data['end_date']=$data['end_date'];
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'start_date'	=>	date('Y-m-1'),
    				'end_date'		=>	date('Y-m-d'),
    				'category'		=>	-1,
					'add_item'		=>	-1,
					'suppliyer_id'	=>	-1,
    				//'suppliyer_id'	=>	-1,
    		);
    	}
		$this->view->start_date = $data["start_date"];
		$this->view->end_date = $data["end_date"];
		
    	$this->view->stockin = $db->getAllWorkingStone($data);
		
		
    	$formFilter = new report_Form_FrmSearch();
    	$this->view->formFilter = $formFilter->formSearch();
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	
}