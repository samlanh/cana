<?php
class Purchase_vendorController extends Zend_Controller_Action
{	
	const REDIRECT_URL ='/purchase';
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
		}else{
			$search =array(
					'text_search'=>'',
					'start_date'=>1,
					'end_date'=>date("Y-m-d"),
					'suppliyer_id'=>0,
					);
		}
		$db = new Purchase_Model_DbTable_DbVendor();
		$rows = $db->getAllVender($search);
		$columns=array("COMPANY_NAME","COMPANY_NUMBER","CON_NAME","CON_NUMBER","EMAIL_CAP","WEBSITE_CAP","ADDRESS_CAP","STATUS");
		$link=array(
				'module'=>'purchase','controller'=>'vendor','action'=>'edit',
		);
		$urlEdit = BASE_URL . "/purchase/vendor/edit";
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('v_name'=>$link,'phone_person'=>$link,'v_phone'=>$link,'contact_name'=>$link));
		
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			try{
				$vendor = new Purchase_Model_DbTable_DbVendor();
				$vendor->addVendor($post);
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$formStock = new Purchase_Form_FrmVendor(null);
		$formStockAdd = $formStock->AddVendorForm(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
	}
	public function editAction() {
		$db = new Purchase_Model_DbTable_DbVendor();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			$post["id"]=$id;
			$db->addVendor($post);
			Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL . '/vendor/index');
		}
		
		$row= $db->getvendorById($id);
		$this->view->is_over_sea = $row["is_over_sea"];
		$formStock = new Purchase_Form_FrmVendor();
		$formStockAdd = $formStock->AddVendorForm($row);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
	}
	//for add vendor from purchase
	final function addvendorAction(){
		$post=$this->getRequest()->getPost();
		$add_vendor = new Purchase_Model_DbTable_DbVendor();
		$vid = $add_vendor->addnewvendor($post);
		$result = array('vid'=>$vid);
		echo Zend_Json::encode($result);
		exit();
	}
}