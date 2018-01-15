<?php
class Sales_quoatationController extends Zend_Controller_Action
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
		
	}
	function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_Dbquoatation();
				if(!empty($data['identity'])){
					$ids = $dbq->addQuoatationOrder($data);
					if(!empty($data['save_close'])){
						Application_Form_FrmMessage::redirectUrl("/sales/quoatation");
					}elseif(isset($data["save_print"])){
						Application_Form_FrmMessage::Sucessfull("Request has been Saved!", "/sales/quoatation/purproductdetail?id=".$ids);
					}
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmQuoatation(null);
		$form_sale = $frm_purchase->SaleOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		 
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		
		$this->view->term_opt = $db->getAllTermCondition(1);
		$this->view->rsterm = $db->getAllTermCondition(null,1);//call default quotion

		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		$a = $items->getAllProduct();
		$this->view->product = $a;
		//print_r($a);
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbquoatation();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updateQoutation($data);
					Application_Form_FrmMessage::message("Request has been Saved!");
					if(!empty($data['save_close'])){
						Application_Form_FrmMessage::redirectUrl("/sales/quoatation");
					}elseif(isset($data["save_print"])){
						Application_Form_FrmMessage::redirectUrl("/sales/quoatation/purproductdetail?id=".$ids);
					}
				}else{
					Application_Form_FrmMessage::message('No Data to Submit');
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getQuotationItemById($id);
		
		if($row['is_approved']==1){
			Application_Form_FrmMessage::Sucessfull("QUOTATIO_WARNING","/sales/quoatation");
		}
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/quoatation");
		}		
		$this->view->rs = $dbq->getQuotationItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		$this->view->rsq = $row;
		$frm_purchase = new Sales_Form_FrmQuoatation();
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition(1);
		//print_r($row);
		$a = $items->getAllProduct();
		$this->view->product = $a;
	}	
	function renewquoteAction(){
		
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbquoatation();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->reNewQuoatation($data);
					Application_Form_FrmMessage::message("Request has been Saved!");
					if(!empty($data['save_close'])){
						Application_Form_FrmMessage::redirectUrl("/sales/quoatation");
					}elseif(isset($data["save_print"])){
						Application_Form_FrmMessage::redirectUrl("/sales/quoatation/purproductdetail?id=".$ids);
					}
				}else{
					Application_Form_FrmMessage::message('No Data to Submit');
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getQuotationItemById($id);
		
		
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/quoatation");
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
		$a = $db->getAllProduct();
		$this->view->product = $a;
	}
	function getquotenoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getQuoationNumber($post['branch_id']);
			echo Zend_Json::encode($qo);
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
		
		$this->view->term = $db->getTermConditionByType(3);//1=purchase,2=sale,3=quote
    }
	
	public function getproductpriceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db ->getProductPriceBytype($post['customer_id'], $post['product_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}