<?php
class Purchase_PurchaserequestController extends Zend_Controller_Action
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
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
				$this->view->search=$search;
		}
		else{
			$search =array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'end_date'			=>	date("Y-m-d"),
					'plan'				=>'',
					'po_pedding'	=>	'',
					);
		}
		$db = new Purchase_Model_DbTable_DbRequest();
		
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
	public function addAction(){
		$db = new Purchase_Model_DbTable_DbRequest();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$ids = $db->add($data);
				Application_Form_FrmMessage::message("Request has been Saved!");
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/purchaserequest");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/purchaserequest/purproductdetail?id=".$ids);
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->items = $db->getProductOption();
		$items = new Application_Model_GlobalClass();
		$this->view->product = $items->getAllProduct();
		
		$form = new Purchase_Form_FrmRequest();
		$this->view->form = $form->add();
	}
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbRequest();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]  = $id;
			try {
				$db->edit($data);
				Application_Form_FrmMessage::message("Request has been Saved!");
				Application_Form_FrmMessage::redirectUrl("/purchase/purchaserequest");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->items = $db->getProductOption();
		$this->view->item = $db->getRequestDetail($id);
		$rs = $db->getRequestById($id);
		$form = new Purchase_Form_FrmRequest();
		$this->view->form = $form->add($rs);
		
		$items = new Application_Model_GlobalClass();
		$this->view->product = $items->getAllProduct();
			
	}
	
	function purproductdetailAction(){
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
	
	function getreceiveAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRecieve();
		$rs = $db->getRecieve($post["id"],$post["type"]);
		echo Zend_Json::encode($rs);
		exit();
	}
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllInvoicePaymentPurchase($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getqtybyidAction(){
	  $db = new Sales_Model_DbTable_Dbquoatation();
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$item_id = $post['item_id'];
			$branch_id = $post['branch_id'];
			
			$row = $db->getItemQty($item_id,$branch_id);
			
			echo Zend_Json::encode($row);
			exit();
		}
	}
	
	function getProductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbSaleOrder();
			$qo = $db->getProductPrice($post['item_id'],$post['branch_id'],$post["customer_id"]);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function getProductOptionAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbSaleOrder();
			$qo = $db->getProductOption(1,$post['customer_id'],$post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function getstaffAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getAllStaffByID($post['id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function getworkplanAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getAllWorkPlanByID($post['id'],1);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
}