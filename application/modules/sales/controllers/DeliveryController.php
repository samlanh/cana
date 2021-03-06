<?php
class Sales_DeliveryController extends Zend_Controller_Action
{	
	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$rows = $db->getAllSaleOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		/*$link=array(
				'module'=>'sales','controller'=>'salesapprove','action'=>'add',
		);*/
		$link=array(
				'module'=>'sales','controller'=>'delivery','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		/*if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_Dbinvoiceapprove();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE","SALE_APP_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'invoiceapprove','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);*/
		
	}
	function adddeliveryAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_Dbdeliverys();				
				$returnid = $dbq->addDelivery($data);
				if(!empty($data["saveprint"])){
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/deliverynote/id/".$returnid);
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/rpt-delivery");
				}else{
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/sales/delivery");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				//Application_Form_FrmMessage::Sucessfull("DELIVERY_FAIL", "/sales/delivery");
			}
		}
		
	}	
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/delivery");
    	}
    	$query = new Sales_Model_DbTable_Dbdeliverys();
    	$this->view->product =  $query->getProductSaleById($id);
		$row = $query->getProductSaleById($id);
		//print_r($row);
    	if(empty($row)){
    		$this->_redirect("/sales/delivery/");
    	}
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionByIdIinvocie(4, null);
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			try {
				$dbq = new Sales_Model_DbTable_Dbdeliverys();				
				$returnid = $dbq->addDelivery($data);
				if(!empty($data["saveprint"])){
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/deliverynote/id/".$returnid);
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/rpt-delivery");
				}else{
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/sales/delivery");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				//Application_Form_FrmMessage::Sucessfull("DELIVERY_FAIL", "/sales/delivery");
			}
		}
		$query = new Sales_Model_DbTable_DbRequest();
		//$this->view->product =  $query->getRequestById($id);
		$frm = new Sales_Form_FrmDeliver();
		$this->view->form = $frm->DN($row);
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	function addrequestAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/delivery");
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			try {
// 				print_r($data);exit();
				$dbq = new Sales_Model_DbTable_Dbdeliverys();				
				$returnid = $dbq->addRequestDeliver($id);
				Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/sales/index/requestdelivery");
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				
			}
		}
		
		
		$query = new Sales_Model_DbTable_Dbdeliverys();
		$this->view->product =  $query->getProductSaleById($id);
		$row = $query->getProductSaleById($id);
		
		if(empty($row)){
			$this->_redirect("/sales/delivery/");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionByIdIinvocie(4, null);
		
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		
		$frm = new Sales_Form_FrmDeliver();
		$this->view->form = $frm->DN($row);
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	public function deliverynoteAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/delivery");
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->product =  $query->getProductDelivyerId($id);
    	if(empty($query->getProductDelivyerId($id))){
    		//$this->_redirect("/sales/delivery");
    	}
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    }
	public function invoiceAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/delivery");
    	}
    	$query = new report_Model_DbQuery();
		$rs = $query->getInvoiceById($id);
    	$this->view->product = $rs ;
    	if(empty($query->getInvoiceById($id))){
    		$this->_redirect("/sales/delivery");
    	}
		$this->view-> rsinvoice = $query->getCustomerPayment($rs[0]['customer_id'],$id);
		
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    }
}