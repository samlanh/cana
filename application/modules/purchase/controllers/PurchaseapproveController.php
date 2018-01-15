<?php
class Purchase_PurchaseapproveController extends Zend_Controller_Action
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
		$db = new Purchase_Model_DbTable_Dbpurchasepprove();
		$rows = $db->getAllPurchaseOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'Purchase','controller'=>'Purchaseapprove','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function approvedAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Purchase_Model_DbTable_DbPurchaseapprov();				
				$dbq->addSaleOrderApproved($data);
				Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/Purchase/Purchaseapprove");
				$this->_redirect("/Purchase/Purchaseapprove");
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/Purchase/Purchaseapprove");
			}
		}
		
	}	
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/purchase/purchaseapprove");
    	}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Purchase_Model_DbTable_Dbpurchasepprove();				
				$dbq->addPurchaseApproved($data);
				Application_Form_FrmMessage::Sucessfull("APPROVED_SUCESS", "/purchase/purchaseapprove");
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("APPROVED_FAIL", "/purchase/purchaseapprove");
			}
		}
    	$query = new Purchase_Model_DbTable_Dbpurchasepprove();
    	$this->view->product =  $query->getProductPurchaseById($id);
    	if(empty($query->getProductPurchaseById($id))){
    		$this->_redirect("/purchase/purchaseapprove");
    	}
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionById(2, $id);
	}	
	
}