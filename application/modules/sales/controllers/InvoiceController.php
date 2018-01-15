<?php
class Sales_InvoiceController extends Zend_Controller_Action
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
	
	function addAction(){
		$db = new Sales_Model_DbTable_Dbinvoice();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addInvoice($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/sales/invoice");
				}else{
					Application_Form_FrmMessage::redirectUrl("/sales/invoice/add");
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index/requestlist");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				echo $err;exit();
			}
		}
		
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmInvoice(null);
		$form_sale = $frm_purchase->invoice();
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	function printbydnAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_Dbinvoice();
		if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("No Data To Print", "/sales/invoice");
    	}
		$rows = $db->printByDn($id);
		$this->view->product = $rows;
		///link left not yet get from DbpurchaseOrder
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	function printbyitemAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_Dbinvoice();
		if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("No Data To Print", "/sales/invoice");
    	}
		$rows = $db->printByItem($id);
		$this->view->product = $rows;
		///link left not yet get from DbpurchaseOrder
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	
	function printOLDAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_Dbinvoice();
		if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("No Data To Print", "/sales/invoice");
    	}
		$rows = $db->getInvoiceById($id);
		$this->view->product = $rows;
		///link left not yet get from DbpurchaseOrder
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	public function getdnAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllDnNo($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}	
}