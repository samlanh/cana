<?php
class report_ProchesController extends Zend_Controller_Action
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
    
    public function rptprucheslistAction()
    {
    	if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'	=>	'',
					'start_date'	=>	date("Y-m-01"),
					'branch'		=>	'',
					'plan'			=>	'',
					'end_date'		=>	date("Y-m-d"),
					'po_pedding'	=>	'',
			        'appr_status'	=>	'',
					);
		}
		$db = new Purchase_Model_DbTable_DbRequest();
		$this->view->rssearch=$search;
		$rows = $db->getAllRequest($search);
		$this->view->rs = $rows;
		$list = new Application_Form_Frmlist();
		$columns=array("BRANCH_NAME","VENDOR_NAME","PURCHASE_ORDER","ORDER_DATE","DATE_IN",
				 "CURRNECY_TYPE","TOTAL_AMOUNT","PAID","BALANCE","ORDER_STATUS","STATUS","BY_USER");
		$link=array(
				'module'=>'purchase','controller'=>'index','action'=>'edit',
		);
		
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'vendor_name'=>$link,'order_number'=>$link,'date_order'=>$link));
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
    
	public function rptpurchaserequestdetailAction()
    {
    	if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
				
				if($search['search_date']==1){
				    $search['start_date']=date("Y-m-d");
				    $search['end_date']=date("Y-m-d");
				    
				}elseif ($search['search_date']==2){
				    $str_next = '+1 week';
				    $search['start_date']=date("Y-m-d");
				    $search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
				    
				}elseif ($search['search_date']==3){
				    $str_next = '+1 month';
				    $search['start_date']=date("Y-m-d");
				    $search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
				}else{}
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-01"),
					'end_date'=>date("Y-m-d"),
					//'purchase_status'=>0,
					'appr_status'=>''
					);
		}
		$this->view->rssearch=$search;
		$db = new report_Model_DbStock();
		$rows = $db->getPORequestDetail($search);
		$this->view->rs = $rows;
		
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	
	public function rptrequestpurchasehistoryAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-01"),
    				'end_date'=>date("Y-m-d"),
    				'plan'=>0,
    		);
    	}
    	$this->view->rssearch = $data;
    	$query = new report_Model_DbStock();
    	$this->view->repurchase =  $query->getPORequestHistory($data);
    	$frm = new Application_Form_FrmReport();
    
    	$form_search=$frm->FrmReportPurchase($data);
    	Application_Model_Decorator::removeAllDecorator($form_search);
    	$this->view->form_purchase = $form_search;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
	
	public function rptreceivehistoryAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-01"),
    				'end_date'=>date("Y-m-d"),
    				'plan'=>0,
    		);
    	}
    	$this->view->rssearch = $data;
    	$query = new report_Model_DbStock();
    	$this->view->repurchase =  $query->getReceivedHistory($data);
    	$frm = new Application_Form_FrmReport();
    
    	$form_search=$frm->FrmReportPurchase($data);
    	Application_Model_Decorator::removeAllDecorator($form_search);
    	$this->view->form_purchase = $form_search;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
	
	public function rptPurchaseAction()//purchase 
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    		
    		if($data['search_date']==1){
    		    $data['start_date']=date("Y-m-d");
    		    $data['end_date']=date("Y-m-d");
    		    
    		}elseif ($data['search_date']==2){
    		    $str_next = '+1 week';
    		    $data['start_date']=date("Y-m-d");
    		    $data['end_date']=date("Y-m-d", strtotime($data['end_date'].$str_next));
    		    
    		}elseif ($data['search_date']==3){
    		    $str_next = '+1 month';
    		    $data['start_date']=date("Y-m-d");
    		    $data['end_date']=date("Y-m-d", strtotime($data['end_date'].$str_next));
    		}else{}
    		
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-01"),
    				'end_date'=>date("Y-m-d"),
    				'suppliyer_id'=>0,
    				'branch_id'=>0,
    		        'plan'     =>0,
					'po_pedding'=>''
    				
    		);
    	}
    	$this->view->rssearch = $data;
    	$query = new report_Model_DbStock();
    	$this->view->repurchase =  $query->getAllPurchaseReport($data);
    	$frm = new Application_Form_FrmReport();
    
    	$form_search=$frm->FrmReportPurchase($data);
    	Application_Model_Decorator::removeAllDecorator($form_search);
    	$this->view->form_purchase = $form_search;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    }
    
	public function rptpurchasedetailAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    		
    		if($data['search_date']==1){
    		    $data['start_date']=date("Y-m-d");
    		    $data['end_date']=date("Y-m-d");
    		    
    		}elseif ($data['search_date']==2){
    		    $str_next = '+1 week';
    		    $data['start_date']=date("Y-m-d");
    		    $data['end_date']=date("Y-m-d", strtotime($data['end_date'].$str_next));
    		    
    		}elseif ($data['search_date']==3){
    		    $str_next = '+1 month';
    		    $data['start_date']=date("Y-m-d");
    		    $data['end_date']=date("Y-m-d", strtotime($data['end_date'].$str_next));
    		}else{}
    		
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-01"),
    				'end_date'=>date("Y-m-d"),
    				'suppliyer_id'=>0,
    				'branch_id'=>0,
    		        'plan'=>0,
					//'po_pedding'=>-1,
    				'add_item'=>-1
    		);
    	}
    	$this->view->rssearch = $data;
    	$query = new report_Model_DbStock();
    	$this->view->repurchase =  $query->getAllPurchaseReportDetail($data);
    	$frm = new Application_Form_FrmReport();
    
    	$form_search=$frm->FrmReportPurchase($data);
    	Application_Model_Decorator::removeAllDecorator($form_search);
    	$this->view->form_purchase = $form_search;
		
		$form_search=$frm->FrmReportPurchase($data);
    	Application_Model_Decorator::removeAllDecorator($form_search);
    	$this->view->form_purchase = $form_search;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
		
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
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
//     				'brand'		=>	'',
//     				'category'	=>	'',
//     				'model'		=>	'',
//     				'color'		=>	'',
//     				'size'		=>	'',
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
	function rptpurchaserequistAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new Purchase_Model_DbTable_DbGloble();
    	$this->view->product =  $query->getRequestPrint($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    	 
    }
    
	public function rptpricecompareAction(){
		if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-01"),
					'end_date'=>date("Y-m-d"),
					'suppliyer_id'=>0,
					'purchase_status'=>0,
					'po_pedding'=>''
					);
		}
		$db = new Purchase_Model_DbTable_DbPriceCompare();
		
		$rows = $db->getAllCompare($search);
		$this->view->rs = $rows;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function purproductdetailAction(){
		$db = new Purchase_Model_DbTable_DbPriceCompareCheck();
		$dbs = new Purchase_Model_DbTable_DbMrApprove();
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->product =  $query->getProductPruchaseById($id);
		$this->view->id = $id;
		$this->view->su_id = $db->getSuCompare($id);
		$this->view->product = $db->getProduct($id);
		$this->view->request = $dbs->getRequestById($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    	 
    }
	public function rptreceiveAction(){
		if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'suppliyer_id'		=>	0,
					'category'			=>	-1,
					'add_item'			=>	-1,
					'end_date'			=>	date("Y-m-d"),
					//'po_invoice_status'	=>	'',
					);
		}
		
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
			$db = new report_Model_DbStock();
			$rows = $db->RecivePO($search,1);
			$glClass = new Application_Model_GlobalClass();
			$columns=array("PURCHASE_ORDER_CAP","ORDER_DATE_CAP", "VENDOR_NAME_CAP","TOTAL_CAP_DOLLAR","BY_USER_CAP");
			$link=array(
					'module'=>'purchase','controller'=>'receive','action'=>'detail-purchase-order',
			);
			$urlEdit = BASE_URL . "/purchase/index/update-purchase-order-test";
			$list = new Application_Form_Frmlist();
			$this->view->list=$list->getCheckList(1, $columns, $rows, array('order'=>$link),$urlEdit);
			
			$this->view->rs = $rows;
			$formFilter = new report_Form_FrmSearch();
			$this->view->formFilter = $formFilter->formSearch();
			Application_Model_Decorator::removeAllDecorator($formFilter);
			
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function rptreceivedetailAction(){
		if($this->getRequest()->isPost()){
					$search = $this->getRequest()->getPost();
			}else{
				$search= array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'suppliyer_id'		=>	0,
					'category'			=>	-1,
					'add_item'			=>	-1,
					'end_date'			=>	date("Y-m-d"),
				);
			}
			$items = new Application_Model_GlobalClass();
			$this->view->items = $items->getProductOption();
		
			$db = new report_Model_DbStock();
			$rows = $db->RecivePO($search);
			
			$this->view->rs = $rows;
			$formFilter = new report_Form_FrmSearch();
			$this->view->formFilter = $formFilter->formSearch();
			Application_Model_Decorator::removeAllDecorator($formFilter);
			
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function receivenoteAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new Purchase_Model_DbTable_DbRecieve();
    	$this->view->product =  $query->getProductReceiveById($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	
	public function rptinvoiceAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-01"),
					'end_date'=>date("Y-m-d"),
			);
		}
			$db = new report_Model_DbStock();
			$rows = $db->getAllPOInvoice($search);
			
			$this->view->rs = $rows;
			$formFilter = new Application_Form_Frmsearch();
			$this->view->formFilter = $formFilter;
			Application_Model_Decorator::removeAllDecorator($formFilter);
			
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function rptinvoicedetailAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			}else{
				$search= array();
			}
			$db = new report_Model_DbStock();
			$rows = $db->getAllPOInvoiceDetail($search);
			
			$this->view->rs = $rows;
			$formFilter = new Application_Form_Frmsearch();
			$this->view->formFilter = $formFilter;
			Application_Model_Decorator::removeAllDecorator($formFilter);
			
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function rptinvoicecontrollingAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-01"),
					'end_date'=>date("Y-m-d"),
					'is_paid_balance'=>0
			);
		}
			$db = new report_Model_DbStock();
			$rows = $db->getInvoiceControlling($search);
			
			$this->view->rs = $rows;
			$formFilter = new Application_Form_Frmsearch();
			$this->view->formFilter = $formFilter;
			Application_Model_Decorator::removeAllDecorator($formFilter);
			
			$session_user=new Zend_Session_Namespace('auth');
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function polAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-01"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'suppliyer_id'=>-1,
					);
		}
		$db = new report_Model_DbStock();
		$rows = $db->getAllReciept($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","EXSPENSE_DATE",
				"TOTAL","PAID","BALANCE","PAYMENT_TYPE","CHEQUE_NUMBER","BANK_NAME","WITHDRAWER","CHEQ_ISSUE","CHEQ_WIDRAW","PAYMENT_METHOD","BY_USER");
		$link=array(
				'module'=>'purchase','controller'=>'payment','action'=>'edit',
		);
// 		$link1=array(
// 				'module'=>'sales','controller'=>'index','action'=>'viewapp',
// 		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}

	public function poldetailAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-01"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new report_Model_DbStock();
		$rows = $db->getAllRecieptDetail($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","EXSPENSE_DATE",
				"TOTAL","PAID","BALANCE","PAYMENT_TYPE","CHEQUE_NUMBER","BANK_NAME","WITHDRAWER","CHEQ_ISSUE","CHEQ_WIDRAW","PAYMENT_METHOD","BY_USER");
		$link=array(
				'module'=>'purchase','controller'=>'payment','action'=>'edit',
		);
// 		$link1=array(
// 				'module'=>'sales','controller'=>'index','action'=>'viewapp',
// 		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	public function rptPettycashDetailAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'	=>'',
					'branch'		=>0,
					'start_date'	=>date("Y-m-01"),
					'end_date'		=>date("Y-m-d"),
			);
		}
		$db = new report_Model_DbStock();
	
		$rows = $db->getPettyCashDetail($search);
		$this->view->rs = $rows;
	
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
	public function rptPettycashPurchaseDetailAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'	=>'',
					'branch'		=>0,
					'start_date'	=>date("Y-m-01"),
					'end_date'		=>date("Y-m-d"),
			);
		}
		$db = new report_Model_DbStock();
	
		$rows = $db->getAllPurchasePettyCash($search);
		$this->view->rs = $rows;
	
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
}