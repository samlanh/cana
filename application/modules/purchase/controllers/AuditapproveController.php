<?php
class Purchase_AuditapproveController extends Zend_Controller_Action
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
					'suppliyer_id'=>0,
					'purchase_status'=>0,
					);
		}
		$db = new Purchase_Model_DbTable_DbAuditapprov();
		$rows = $db->getAllPurchaseOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		/*$link=array(
				'module'=>'Purchase','controller'=>'Purchaseapprove','action'=>'add',
		);*/
		$link=array(
				'module'=>'Purchase','controller'=>'invoiceapprove','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Application_Form_Frmsearch();
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
		$db = new Purchase_Model_DbTable_DbAuditapprov();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE","SALE_APP_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'Purchase','controller'=>'invoiceapprove','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Purchase_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);*/
		
	}
	function approvedAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/Purchase/Purchaseapprove");
    	}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			try {
				$dbq = new Purchase_Model_DbTable_DbAuditapprov();				
				$returnid = $dbq->addAuditApproved($data);
				//Application_Form_FrmMessage::message("APPROVED_SUCESS");
				if(!empty($data["saveprint"])){
					//Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/report/index/deliverynote/id/".$returnid);
					Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/report/index/rpt-delivery");
					
				}else{
					//Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/Purchase/invoiceapprove");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/Purchase/invoiceapprove");
			}
		}
		
	}	
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/Purchase/Purchaseapprove");
    	}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
		try {
				$dbq = new Purchase_Model_DbTable_DbAuditapprov();				
				$returnid = $dbq->addAuditApproved($data);
				//Application_Form_FrmMessage::message("APPROVED_SUCESS");
				if(!empty($data["saveprint"])){
					//Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/report/index/deliverynote/id/".$returnid);
					Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/report/index/rpt-delivery");
					
				}else{
					//Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/Purchase/invoiceapprove");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/Purchase/invoiceapprove");
			}
		}
    	$query = new Purchase_Model_DbTable_DbAuditapprov();
    	$this->view->product =  $query->getProductPurchaseById($id);
		$rs = $query->getProductPurchaseById($id);
    	if(empty($rs)){
    		$this->_redirect("/Purchase/Purchaseapprove");
    	}
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionByIdIinvocie(3, null);
	}	
}