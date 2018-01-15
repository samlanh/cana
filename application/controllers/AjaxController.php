<?php
class AjaxController extends Zend_Controller_Action
{ 
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		
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
	public function addBrandAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbBrand();
				$brand_id =$db->addNew($post);
				$result = array('brand_id'=>$brand_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	public function addCategoryAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbCategory();
				$cat_id =$db->addNew($post);
				$result = array('cat_id'=>$cat_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}

	public function addMeasureAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbMeasure();
				$measure_id =$db->addNew($post);
				$result = array('measure_id'=>$measure_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	public function addOtherAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbOther();
				$other_id =$db->addNew($post);
				$result = array('other_id'=>$other_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	public function getpoductcodeexistAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbProduct();
		$result = $get_code->getProductPrefix($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
	
	function getProductNameAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRequest();
		$rs = $db->getProductName($post["item_id"]);
		echo Zend_Json::encode($rs);
		exit();
	}
	function getRequestCodeAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRequest();
		$rs = $db->getRequestCode($post["branch_id"]);
		echo Zend_Json::encode($rs);
		exit();
	}
	
	function getreceiveAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRecieve();
		$rs = $db->getRecieve($post["id"],$post["type"]);
		echo Zend_Json::encode($rs);
		exit();
	}
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllInvoicePaymentPurchase($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
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
	
	public function getProductAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbTransfer();
			$data = $this->getRequest()->getPost();
			$rs = $db->getProductByIds($data["id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	function getProductsAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbSaleOrder();
			$qo = $db->getProductPrice($post['item_id'],$post['branch_id'],$post["customer_id"]);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	function getProductOptionAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbSaleOrder();
			$qo = $db->getProductForSale($post['customer_id'],$post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	function getstaffAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getAllStaffByID($post['id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	function addstaffAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->insertStaff($post);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	function getworkplanAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getAllWorkPlanByID($post['id'],1);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	function addepartmentAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->addDepartment($post);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	
	public  function getPurchaseItemAction(){
		if($this->getRequest()->isPost()){
		    $db= new Purchase_Model_DbTable_DbRecieve();
			$post=$this->getRequest()->getPost();
			$result= $db->getItemByPuId($post["id"]);
			echo Zend_Json::encode($result);
			exit();
		}
	}
	
	 public function closereceiveAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Purchase_Model_DbTable_DbRecieve();
		$result = $get_code->closeReceive($post["id"],$post["re_id"]);
		echo Zend_Json::encode($result);
		exit();
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
	public function getvendorAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Purchase_Model_DbTable_Dbpayment();
			$rs = $db->getVendore($post['post_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}	

	public function getdnAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllDnNo($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}

	public function getCategoryExistAction(){//dynamic by customer
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbCategory();
		$result = $get_code->getCategoryExist($post["name"]);
		echo Zend_Json::encode($result);
		exit();
	}
	public function getPrefixyExistAction(){//dynamic by customer
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbCategory();
		$result = $get_code->getPrefixyExist($post["prefix"]);
		echo Zend_Json::encode($result);
		exit();
	}	
	public function getProductPrefixAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbProduct();
		$result = $get_code->getProductPrefix($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
}