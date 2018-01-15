<?php
class Sales_IndexController extends Zend_Controller_Action
{	
	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
	}	
	function addold1Action(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_DbSaleOrder();
				if(!empty($data['identity'])){
					$id = $dbq->addSaleOrder($data);
					Application_Form_FrmMessage::message('INSERT_SUCESS');
				}if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/sales/index");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/viewsale?id=".$id);
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmSale(null);
		$form_sale = $frm_purchase->SaleOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	}
	
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbquoatation();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db = new Sales_Model_DbTable_Dbmakeso();
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
		$row = $dbq->getQuotationItemById($id);
		$this->view->qqq = $row;
		if(empty($row)){
			//Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/makeso");
		}		
		$this->view->rs = $dbq->getQuotationItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		$this->view->rsq = $row;
		$frm_purchase = new Sales_Form_FrmSales();
		$form_sale = $frm_purchase->SaleOrder($row,1);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$db = new Application_Model_GlobalClass();
		$this->view->items = $db->getProductOption();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}	
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_DbSaleOrder();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$dbq->updateSaleOrder($data);
					Application_Form_FrmMessage::message('UPDATE_SUCESS');
				}if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/sales/index");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/viewsale?id=".$id);
				}
				//Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getSaleorderItemById($id);
		if(empty($row)){
			//Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/index");
		}if($row['is_approved']==1){
			Application_Form_FrmMessage::Sucessfull("SALE_ORDER_WARNING","/sales/index");
		}
		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmSale(null);
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}

	function addrequestAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_DbRequest();
				if(!empty($data['identity'])){
					$id = $dbq->addRequestOrder($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/requestlist");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/viewrequest/id/".$id);
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmRequest(null);
		$form_sale = $frm_purchase->SaleOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->worktype = $items->getWorkType();
		$this->view->term_opt = $db->getAllTermCondition(1);
		
		$this->view->work_type = $db->getWorkType();
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	}
	
	function editrequestAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_DbRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updateRequestOrder($data);
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/requestlist");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/viewrequest/id/".$id);
				}else{
					Application_Form_FrmMessage::redirectUrl("/sales/index/addrequest");
				}
				//Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index/requestlist");
			}catch (Exception $e){
				//Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				echo $err;exit();
			}
		}
		$row = $dbq->getSaleorderItemById($id);
		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmRequest(null);
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->worktype = $items->getWorkType();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}
	
	function requestlistAction(){
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
	}
	
	function checkrequestAction(){
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
	}
	function addcheckAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/sales/index/requestlist");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$query->checkRequest($data);
			if(isset($data["save_print"])){
				Application_Form_FrmMessage::redirectUrl("/sales/index/viewrequest/id/".$id);
			}else{
				Application_Form_FrmMessage::redirectUrl("/sales/index/checkrequest");
			}
		}
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function requestdeliveryAction(){
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
		
	}
	function addrequestdeliveryAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/sales/index/requestlist");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$query->addDelivery($data);
			if(isset($data["save_print"])){
				Application_Form_FrmMessage::redirectUrl("/sales/index/viewrequest/id/".$id);
			}else{
				Application_Form_FrmMessage::redirectUrl("/sales/index/checkrequest");
			}
		}
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function viewrequestAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/sales/index/requestlist");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function viewappAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/salesapprove");
		}
		$query = new Sales_Model_DbTable_Dbsalesapprov();
		$this->view->product =  $query->getProductSaleById($id);
		$rs = $query->getProductSaleById($id);
		if(empty($rs)){
			$this->_redirect("/sales/salesapprove");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
	}
	
	function viewsaleAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/salesapprove");
		}
		$query = new Sales_Model_DbTable_Dbsalesapprov();
		$this->view->product =  $query->getProductSaleById($id);
		$rs = $query->getProductSaleById($id);
		if(empty($rs)){
			$this->_redirect("/sales/salesapprove");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
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
	function getsonumberAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getSalesNumber($post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function getRequestNoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getRequestNumber($post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
		
	function getPlanAddrAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbRequest();
			$qo = $db->getPlanAddr($post['id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function getProductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbSaleOrder();
			$qo = $db->getProductPrice($post['item_id'],$post['branch_id']);
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
}