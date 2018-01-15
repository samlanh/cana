<?php
class Sales_StaffController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_Dbstaff();
		$rows = $db->getAllstaff($search);
		$this->view->rs = $rows;
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db= new Sales_Model_DbTable_Dbstaff();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->insertStaff($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCESS","/sales/staff");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}		
		$frm_purchase = new Sales_Form_FrmStaff();
		$form_sale = $frm_purchase->SaleOrder();
		$this->view->form_sale = $form_sale;
	}	
	function editAction(){
		$db= new Sales_Model_DbTable_Dbstaff();
		$request = $this->getRequest(); 
		$id = $request->getParam('id');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->updateStaff($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCESS","/sales/staff");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$rows = $db->getstaffById($id);		
		$frm_purchase = new Sales_Form_FrmStaff();
		$form_sale = $frm_purchase->SaleOrder($rows);
		$this->view->form_sale = $form_sale;
	}
}