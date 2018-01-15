<?php
class Sales_QuoteapproveController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_Dbquoteapprov();
		$rows = $db->getAllSaleOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","REQUEST_NO", "REQUEST_DATE"
				,"TOTAL","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array('module'=>'sales','controller'=>'quoteapprove','action'=>'add');
		$link1=array('module'=>'sales','controller'=>'quoteapprove','action'=>'edit');
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,
		'sale_no'=>$link,'Make SO'=>$link1));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function approvedAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_Dbquoteapprov();				
				$dbq->addQuoateOrderApproved($data);
				//Application_Form_FrmMessage::message("APPROVED_SUCESS");
				//Application_Form_FrmMessage::redirectUrl("/sales/quoteapprove/index");
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/sales/quoteapprove/index");
			}
		}
		Application_Form_FrmMessage::message("APPROVED_SUCESS");
		Application_Form_FrmMessage::redirectUrl("/sales/quoteapprove/index");
	}	
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/quoteapprove");
    	}
    	
    	$query = new Sales_Model_DbTable_Dbquoteapprov();
    	$this->view->product =  $query->getProductSaleById($id);
		$rs = $query->getProductSaleById($id);
    	if(empty($rs)){
    		$this->_redirect("/sales/quoteapprove");
    	}
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$data["id"] = $id;
				$dbq = new Sales_Model_DbTable_Dbquoteapprov();				
				$dbq->addQuoateOrderApproved($data);
				Application_Form_FrmMessage::message("Request has been Approved!");
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/sales/quoteapprove");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/quoteapprove/purproductdetail?id=".$ids);
				}
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/sales/quoteapprove/index");
			}
		}
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}	
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbquoatation();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db = new Sales_Model_DbTable_Dbquoteapprov();
				if(!empty($data['identity'])){
					$db->convertQouteToSO($data);
				}else{
					Application_Form_FrmMessage::message('No Data to Submit');
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/quoteapprove");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getQuotationItemById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/quoteapprove");
		}		
		$this->view->rs = $dbq->getQuotationItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		$this->view->rsq = $row;
		$frm_purchase = new Sales_Form_FrmQuoatation();
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$db = new Application_Model_GlobalClass();
		$this->view->items = $db->getProductOption();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition(1);
		//print_r($row);
	}	
	function purproductdetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/quoatation");
		}
		$query = new report_Model_DbQuery();
		$this->view->product =  $query->getQuotationById($id);
		if(empty($query->getQuotationById($id))){
			$this->_redirect("/sales/quoatation");
		}
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
		$this->view->term = $db->getTermConditionByType(3);
    	 
    }
}