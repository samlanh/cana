<?php
class Sales_WorktypeController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
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
	function updatecodeAction(){
		$db = new Sales_Model_DbTable_DbSales();
		$db->getSalesCoded();
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
		$db = new Sales_Model_DbTable_DbWorkType();
		$rows = $db->getPlanType($search);
		$list = new Application_Form_Frmlist();
		$columns=array("TITLE","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'worktype','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('name'=>$link));
		$formFilter = new Sales_Form_FrmPlan();
    	$this->view->formFilter = $formFilter->SalesFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);  
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbWorkType();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/worktype/index');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/sales/worktype/add");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
			$formSales = new Sales_Form_FrmWorkType();
			$formStockAdd = $formSales->frmworkType(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;	
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Sales_Model_DbTable_DbWorkType();
		$row=$db->getplantypeById($id);
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post['id']=$id;
					$db->edit($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/worktype/index');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/worktype/add');
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formSales = new Sales_Form_FrmWorkType();
			$formStockAdd = $formSales->frmworkType($row);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
	}
	
}

