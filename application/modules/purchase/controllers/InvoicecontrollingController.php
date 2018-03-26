<?php
class Purchase_InvoicecontrollingController extends Zend_Controller_Action
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
				$this->view->search=$search;
		}
		else{
			$search =array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'suppliyer_id'		=>	0,
					'end_date'			=>	date("Y-m-d"),
					'po_invoice_status'	=>	'',
					);
		}
			$db = new Purchase_Model_DbTable_DbInvoiceControlling();
			$rows = $db->getAllReceiveInvoice($search);
			
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
		$this->view->item = $db->getItemByPuId($id);
		$frm_purchase = new Purchase_Form_FrmRecieve();
		$form_add_purchase = $frm_purchase->add($row);
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
	}
	
	public function addAjaxAction(){		
		if($this->getRequest()->isPost()){
		    $db= new Purchase_Model_DbTable_DbInvoiceControlling();
			$post=$this->getRequest()->getPost();
			$result= $db->add($post);
			echo Zend_Json::encode($result);
			exit();
		}
		
	}
}