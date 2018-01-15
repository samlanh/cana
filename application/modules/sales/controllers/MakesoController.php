<?php
class Sales_MakesoController extends Zend_Controller_Action
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
   	public function indexoldAction()
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
		$db = new Sales_Model_DbTable_Dbmakeso();
		$rows = $db->getAllSaleOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","REQUEST_NO", "REQUEST_DATE"
				,"TOTAL","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","MAKE_SO","BY_USER");
		$link1=array('module'=>'sales','controller'=>'makeso','action'=>'add');
		//$link1=array('module'=>'sales','controller'=>'makeso','action'=>'edit');
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link1,'customer_name'=>$link1,'staff_name'=>$link1,
		'sale_no'=>$link1,'Make SO'=>$link1));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
		public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			//$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			//$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
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
		$this->view->search = $search;
		$db = new Sales_Model_DbTable_Dbmakeso();
		$rows = $db->getAllProductSale($search);
		$this->view->rs = $rows;
		$columns=array("ITEM_CODE","ITEM_NAME","MEASURE","CUR_QTY", "QUOTE_NO","DATE_ORDER"
				,"PRICE","BENEFIT_PLUS");
		$link1=array('module'=>'sales','controller'=>'makeso','action'=>'add');
		//$link1=array('module'=>'sales','controller'=>'makeso','action'=>'edit');
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(1, $columns, $rows, array());
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function approvedAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_Dbmakeso();				
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
	function addoldAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/quoteapprove");
    	}
    	
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			try {
				$dbq = new Sales_Model_DbTable_Dbmakeso();				
				$dbq->addQuoateOrderApproved($data);
				//Application_Form_FrmMessage::message("APPROVED_SUCESS");
				//Application_Form_FrmMessage::redirectUrl("/sales/quoteapprove/index");
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/sales/quoteapprove/index");
			}
		}
		
    	$query = new Sales_Model_DbTable_Dbmakeso();
    	$this->view->product =  $query->getProductSaleById($id);
		$rs = $query->getProductSaleById($id);
    	if(empty($rs)){
    		$this->_redirect("/sales/quoteapprove");
    	}
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionById(1, $id);
	}
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$cu_id = ($this->getRequest()->getParam('cu_id'))? $this->getRequest()->getParam('cu_id'): '0';
		$branch_id =($this->getRequest()->getParam('branch_id'))? $this->getRequest()->getParam('branch_id'): '0';
		echo $cu_id."-".$branch_id;
		$dbq = new Sales_Model_DbTable_Dbquoatation();
		$db = new Sales_Model_DbTable_Dbmakeso();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				
				if(!empty($data['identity'])){
					$db->convertQouteToSO($data);
				}else{
					Application_Form_FrmMessage::message('No Data to Submit');
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/makeso");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$data=array();
		$data["customer_id"]=$cu_id;
		$data["branch_id"]=$branch_id;
		$data["id"]=$id;
		$this->view->rs = $db->getProductForSale($data);
		$frm_purchase = new Sales_Form_FrmSales();
		$form_sale = $frm_purchase->SaleOrder($data,1);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$db = new Application_Model_GlobalClass();
		$this->view->items = $db->getProductOption();
		
	}	
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbmakeso();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db = new Sales_Model_DbTable_Dbmakeso();
				if(!empty($data['identity'])){
					$db->editSO($data);
				}else{
					//Application_Form_FrmMessage::message('No Data to Submit');
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getSaleById($id);
		if(empty($row)){
			//Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/makeso");
		}		
		$this->view->rs = $dbq->getSaleItem($id);
		$this->view->rsq = $row;
		$frm_purchase = new Sales_Form_FrmSales();
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$db = new Application_Model_GlobalClass();
		$this->view->items = $db->getProductOption();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition(1);
		//print_r($row);
	}	
	public function purproductdetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-sales");
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->product =  $query->getProductSaleById($id);
    	if(empty($query->getProductSaleById($id))){
    		$this->_redirect("/report/index/rpt-sales");
    	}
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    }
}