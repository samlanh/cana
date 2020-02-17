<?php
class Product_ReceiveController extends Zend_Controller_Action
{	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
// 		$rs = $db->getValidUserUrl();
// 		if(empty($rs)){
// 			Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
// 		}
    }
	public function polistAction(){
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
					'plan'				=>	'',
					'suppliyer_id'		=>	0,
					'end_date'			=>	date("Y-m-d"),
					'po_pedding'	=>	7,
					);
		}
		$db = new Purchase_Model_DbTable_DbPurchaseOrder();
		
		$rows = $db->getAllPurchaseOrder($search);
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
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'suppliyer_id'		=>	0,
					'end_date'			=>	date("Y-m-d"),
					'po_pedding'	=>	7,
					);
		}
		$db = new Purchase_Model_DbTable_DbPurchaseOrder();
		$rows = $db->getAllPurchaseOrder($search);
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
// 		$db = new Purchase_Model_DbTable_DbPartyCash();
// 		$db->updateTotal();
	}
	public function receivedlistAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'suppliyer_id'		=>	0,
					'end_date'			=>	date("Y-m-d"),
			);
		}
		$db = new Purchase_Model_DbTable_DbRecieve();
		$rows = $db->getAllReceivedOrder($search);
		// 			$glClass = new Application_Model_GlobalClass();
		// 			$columns=array("PURCHASE_ORDER_CAP","ORDER_DATE_CAP", "VENDOR_NAME_CAP","TOTAL_CAP_DOLLAR","BY_USER_CAP");
		// 			$link=array(
		// 					'module'=>'purchase','controller'=>'receive','action'=>'detail-purchase-order',
		// 			);
		// 			$urlEdit = BASE_URL . "/purchase/index/update-purchase-order-test";
		// 			$list = new Application_Form_Frmlist();
		// 			$this->view->list=$list->getCheckList(1, $columns, $rows, array('order'=>$link),$urlEdit);
			
		$this->view->rs = $rows;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
public function addAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbRecieve();
		$db_p = new Purchase_Model_DbTable_DbPurchaseVendor();
		$row = $db_p->getPurchaseById($id);
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data["id"] = $id;
				$ids = $db->add($data);
				Application_Form_FrmMessage::message("Purchase has been Receive!"); 		
				if(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/product/receive/purproductdetail/id/".$ids);
				}else{
					Application_Form_FrmMessage::redirectUrl("/product/receive");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->item = $db->getItemByPuId($id);
		$frm_purchase = new Purchase_Form_FrmRecieve();
		$form_add_purchase = $frm_purchase->add($row);
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
	}
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbRecieve();
		$db_p = new Purchase_Model_DbTable_DbPurchaseVendor();
	
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data["id"] = $id;
				$ids = $db->edit($data);
				Application_Form_FrmMessage::message("Purchase has been Receive!");
				if(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/receive/purproductdetail/id/".$ids);
				}else{
					Application_Form_FrmMessage::redirectUrl("/purchase/receive");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $db->getReceiveById($id);
		$this->view->data = $row;
		$frm_purchase = new Purchase_Form_FrmRecieve();
		$form_add_purchase = $frm_purchase->edit($row);
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
	}
	public function receivenoteAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/report/index/rpt-purchase");
		}
		$query = new Purchase_Model_DbTable_DbRecieve();
		$this->view->product =  $query->getProductReceiveById($id);
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
}