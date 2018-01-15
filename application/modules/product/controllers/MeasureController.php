<?php
class Product_measureController extends Zend_Controller_Action
{
public function init(){
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
    public function indexAction(){
		$db = new Product_Model_DbTable_DbMeasure();
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'name'		=>	'',
					'parent'	=>	'',
					'status'	=>	1
			);
		}
		$columns=array("MEASURE_NAME","REMRK","STATUS");
		$link=array(
				'module'=>'product','controller'=>'measure','action'=>'edit',);
		$rows = $db->getAllMeasure($data);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('name'=>$link,'remark'=>$link));		
		$formFilter = new Product_Form_FrmMeasure();
		$frmsearch = $formFilter->MeasureFilter();
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$result = $db->getAllMeasure($data);
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		}
	public function addAction(){
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbMeasure();
			$rows = $db->getmeasurename($data['measure_name']);
			if(!empty($rows)){
				Application_Form_FrmMessage::Sucessfull("measure ready Exit!", '/product/measure/index');
			}else{
				$db->add($data);
				if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/measure/index');
					}
				}	
			}
		$formFilter = new Product_Form_FrmMeasure();
		$formAdd = $formFilter->measure();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
		}
	public function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Product_Model_DbTable_DbMeasure();
		if($id==0){
			$this->_redirect('/product/measure/index/add');
		}else{
				if($this->getRequest()->isPost()) {
					$data = $this->getRequest()->getPost();
					$rows = $db->getmeasurename($data['measure_name']);
					//echo $rows; exit();
					if(!empty($rows)){
						Application_Form_FrmMessage::Sucessfull("measure ready Exit!", '/product/measure/index');
					}else{
						$data["id"] = $id;
						$db->edit($data);
					if(isset($data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/product/measure/index');
						}
					}	
				}	
			}
		$rs = $db->getMeasure($id);
		$formFilter = new Product_Form_FrmMeasure();
		$formAdd = $formFilter->measure($rs);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Product_Model_DbTable_DbAddProduct();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
}

