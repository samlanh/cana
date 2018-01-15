<?php
class Sales_WorkplanController extends Zend_Controller_Action
{
public function init(){
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		if(empty($rs)){
			//Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction(){
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    	}else{
    		$search = array(
    				'ad_search'	=>	'',
    				'status'	=>	1,
    				'name'	=>	'',
    		);
    	}
    	$db = new Sales_Model_DbTable_DbPlan();
    	$rows = $db->getWorkPlan($search);
    	$list = new Application_Form_Frmlist();
    	$columns=array("TITLE","PLAN","STATUS");
    	$link=array(
    			'module'=>'sales','controller'=>'workplan','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('name'=>$link));
    	$formFilter = new Sales_Form_FrmPlan();
    	$this->view->formFilter = $formFilter->SalesFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbPlan();
		if($this->getRequest()->isPost()){
			try{
				$post = $this->getRequest()->getPost();
				$db->addworkplan($post);
				if(isset($post["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/workplan');
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
			
		$formSales = new Sales_Form_FrmPlan();
		$formStockAdd = $formSales->frmWorkPlan(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Sales_Model_DbTable_DbPlan();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->editworkplan($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/workplan');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$rs = $db->getWorkplanById($id);
			$formSales = new Sales_Form_FrmPlan();
			$formStockAdd = $formSales->frmWorkPlan($rs);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;	
		
	}
}

