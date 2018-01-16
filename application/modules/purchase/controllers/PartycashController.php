<?php
class Purchase_PartycashController extends Zend_Controller_Action
{	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
// 		echo $rs;
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
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
					'start_date'=>date("Y-m-01"),
					'end_date'=> date("Y-m-t"),//date("Y-m-d")
					'purchase_status'=>0,
					);
		}
		$db = new Purchase_Model_DbTable_DbPartyCash();
		
		$rows = $db->getAllRequest($search);
		$this->view->rs = $rows;
// 		$list = new Application_Form_Frmlist();
// 		$columns=array("BRANCH_NAME","VENDOR_NAME","PURCHASE_ORDER","ORDER_DATE","DATE_IN",
// 				 "CURRNECY_TYPE","TOTAL_AMOUNT","PAID","BALANCE","ORDER_STATUS","STATUS","BY_USER");
// 		$link=array(
// 				'module'=>'purchase','controller'=>'index','action'=>'edit',
// 		);
		
// 		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'vendor_name'=>$link,'order_number'=>$link,'date_order'=>$link));
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction(){
		$db = new Purchase_Model_DbTable_DbPartyCash();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$ids = $db->add($data);
				Application_Form_FrmMessage::message("Request has been Saved!");
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash/purproductdetail?id=".$ids);
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->items = $db->getProductOption();
		$items = new Application_Model_GlobalClass();
		$this->view->product = $items->getAllProductPettyCash();
		
		$form = new Purchase_Form_FrmPartyCash();
		$this->view->form = $form->add();
	}
	public function editAction(){//edit Request
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbPartyCash();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]  = $id;
			try {
				$db->edit($data);
				Application_Form_FrmMessage::message("Request has been Saved!");
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash/purproductdetail?id=".$id);
				}else{
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->items = $db->getProductOption();
		$this->view->item = $db->getRequestDetail($id);
		$rs = $db->getRequestById($id);
		$form = new Purchase_Form_FrmPartyCash();
		$this->view->form = $form->add($rs);
		$this->view->rs_total = $rs['total'];
		
		$items = new Application_Model_GlobalClass();
// 		$this->view->product = $items->getAllProduct();
		
		$this->view->product = $items->getAllProductPettyCash();
	}
	public function makepurchaseAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbPartyCash();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]  = $id;
			try {
				$db->addpo($data);
				Application_Form_FrmMessage::message("Request has been Saved!");
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash/purproductdetail?id=".$id);
				}else{
					Application_Form_FrmMessage::redirectUrl("/purchase/partycash/add");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->items = $db->getProductOption();
		$this->view->item = $db->getRequestDetail($id);
		$rs = $db->getRequestById($id);
		$form = new Purchase_Form_FrmPartyCash();
		$this->view->form = $form->addpo($rs);
		$this->view->rs_total = $rs['total'];
	
		$items = new Application_Model_GlobalClass();
		$this->view->product = $items->getAllProduct();
			
	}
	public function editpurchaseAction(){//Edit ទូរទាត់
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbPartyCash();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]  = $id;
			try {
				$db->editpurchasepettycash($data);
				Application_Form_FrmMessage::message("Update has been Saved!");
				Application_Form_FrmMessage::redirectUrl("/purchase/partycash/partypurchase");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->items = $db->getProductOption();
		$this->view->item = $db->getPettycashDetail($id);
		$rs = $db->getPurchaseById($id);
		$form = new Purchase_Form_FrmPartyCash();
		$this->view->form = $form->addpo($rs);
		$this->view->rs = $rs;
		$items = new Application_Model_GlobalClass();
		$this->view->product = $items->getAllProduct();
	}
	public function partypurchaseAction()
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
					'end_date'=>date("Y-m-t"),
					'purchase_status'=>0,
					'branch'=>0,
					);
		}
		$db = new Purchase_Model_DbTable_DbPartyCash();
		
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
	}
	
	
	
	function purproductdetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new Purchase_Model_DbTable_DbPartyCash();
    	$this->view->product =  $query->getRequestPrint($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    	 
    }
    
	
	function printpurchaseAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new Purchase_Model_DbTable_DbPartyCash();
    	$this->view->product =  $query->getPoPrint($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    	 
    }
	function getProductNameAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRequest();
		$rs = $db->getProductName($post["item_id"]);
		echo Zend_Json::encode($rs);
		exit();
	}
	function getRequestCodeAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRequest();
		$rs = $db->getRequestCode($post["branch_id"]);
		echo Zend_Json::encode($rs);
		exit();
	}
}