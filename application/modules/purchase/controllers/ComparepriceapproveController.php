<?php
class Purchase_ComparepriceapproveController extends Zend_Controller_Action
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
		}
		else{
			$search =array(
					'text_search'		=>	'',
					'start_date'		=>	date("Y-m-01"),
					'branch'			=>	'',
					'suppliyer_id'		=>	0,
					'end_date'			=>	date("Y-m-d"),
					'po_pedding'	=>	5,
					);
		}
		$db = new Purchase_Model_DbTable_DbPriceCompare();
		
		$rows = $db->getAllCompare($search);
		$this->view->rs = $rows;
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
	}
	public function approveAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Purchase_Model_DbTable_DbPriceCompareApprove();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]  = $id;
			try {
				$db->add($data);
				Application_Form_FrmMessage::message("Price Compare has been Approved!"); 		
				if(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/comparepriceapprove");
				}else{
					Application_Form_FrmMessage::redirectUrl("/purchase/comparepriceapprove/");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$dbs = new Purchase_Model_DbTable_DbMrApprove();
    	
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}

		$this->view->id = $id;
		$this->view->su_id = $db->getSuCompare($id);
		$this->view->product = $db->getProduct($id);
		$this->view->request = $dbs->getRequestById($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function purproductdetailAction(){
		$db = new Purchase_Model_DbTable_DbPriceCompare();
		$dbs = new Purchase_Model_DbTable_DbMrApprove();
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->product =  $query->getProductPruchaseById($id);
		$this->view->id = $id;
		$this->view->su_id = $db->getSuCompare($id);
		$this->view->product = $db->getProduct($id);
		$this->view->request = $dbs->getRequestById($id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
    	 
    }
}