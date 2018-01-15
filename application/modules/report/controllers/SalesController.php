<?php
class report_SalesController extends Zend_Controller_Action
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
    public function rptquoteAction()
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
		$db = new Sales_Model_DbTable_Dbquoatation();
		$rows = $db->getAllQuoatation($search);
		$this->view->rs = $rows;
		$list = new Application_Form_Frmlist();
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","QUOTATION_NO", "ORDER_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'quoatation','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'quoat_number'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	
	public function rptquotedetailAction()
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
		$rows = $db->getAllQuoatationDetail($search);
		$this->view->rs = $rows;
		$list = new Application_Form_Frmlist();
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","QUOTATION_NO", "ORDER_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'quoatation','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'quoat_number'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
    
    }
	
	public function rptsalesAction()
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
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$rows = $db->getAllSaleOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE"
				,"TOTAL","DISCOUNT","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'edit',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,
				'sale_no'=>$link,'approval'=>$link1));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
	public function rptsalesdetailAction()
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
		$rows = $db->getAllSaleDetail($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE"
				,"TOTAL","DISCOUNT","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'edit',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,
				'sale_no'=>$link,'approval'=>$link1));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
	public function rptinvoiceAction()
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
		$db = new Sales_Model_DbTable_Dbinvoice();
		$rows = $db->getAllInvoice($search);
		$this->view->rs = $rows;
		$columns=array("INOVICE_NO","INVOICE_DATE","BRANCH","CUSTOMER_NAME", "TOTAL","DISCOUNT","TOTAL_AMOUNT","BY_USER");
		/*$link=array(
				'module'=>'sales','controller'=>'salesapprove','action'=>'add',
		);*/
		$link=array(
				'module'=>'sales','controller'=>'invoice','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
	public function rptinvoicedetailAction()
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
		$rows = $db->getAllInvoiceDetail($search);
		$this->view->rs = $rows;
		$columns=array("INOVICE_NO","INVOICE_DATE","BRANCH","CUSTOMER_NAME", "TOTAL","DISCOUNT","TOTAL_AMOUNT","BY_USER");
		/*$link=array(
				'module'=>'sales','controller'=>'salesapprove','action'=>'add',
		);*/
		$link=array(
				'module'=>'sales','controller'=>'invoice','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
	public function rptrequestAction()
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
		$db = new Sales_Model_DbTable_DbRequest();
		$rows = $db->getAllRequestOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","SALE_NO","REQUEST_NAME","POSITION","PLAN","ORDER_DATE","TOTAL_AMOUNT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'editrequest',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('location'=>$link,'request_name'=>$link,'position'=>$link,
				'sale_no'=>$link,'plan'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
	public function rptrequestdetailAction()
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
		$db = new Sales_Model_DbTable_DbRequest();
		$rows = $db->getAllRequestOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","SALE_NO","REQUEST_NAME","POSITION","PLAN","ORDER_DATE","TOTAL_AMOUNT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'editrequest',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('location'=>$link,'request_name'=>$link,'position'=>$link,
				'sale_no'=>$link,'plan'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db_globle->getTitleReport($session_user->location_id);
	}
	
   
}