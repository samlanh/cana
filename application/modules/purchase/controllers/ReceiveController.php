<?php
class Purchase_ReceiveController extends Zend_Controller_Action
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
	public function indexAction(){
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
	
	function makeinvoiceAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Purchase_Model_DbTable_DbRecieve();
				if(!empty($data['identity'])){
					$dbq->addInvoice($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/payment/add");
				}
				Application_Form_FrmMessage::redirectUrl("/purchase/payment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm = new Purchase_Form_FrmInvoice(null);
		$form_pay = $frm->Payment(null);
		Application_Model_Decorator::removeAllDecorator($form_pay);
		$this->view->form_sale = $form_pay;
	}
	public function purproductdetailAction(){
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
// 	public function receivenoteAction(){
// 		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
//     	if(empty($id)){
//     		$this->_redirect("/report/index/rpt-purchase");
//     	}
//     	$query = new Purchase_Model_DbTable_DbRecieve();
//     	$this->view->product =  $query->getProductReceiveById($id);
// 		$session_user=new Zend_Session_Namespace('auth');
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
// 	}
	public function getPurchaseVendorAction(){		
		if($this->getRequest()->isPost()){
		    $db= new Purchase_Model_DbTable_DbRecieve();
			$post=$this->getRequest()->getPost();
			$result= $db->getVendorByPuId($post["id"]);
			echo Zend_Json::encode($result);
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
	
	public  function makefullreceiveAction(){
		if($this->getRequest()->isPost()){
		    $db= new Purchase_Model_DbTable_DbRecieve();
			$post=$this->getRequest()->getPost();
			$result= $db->makeFullReceive($post["id"]);
			echo Zend_Json::encode($result);
			exit();
		}
	}
	public function getPurchaseidAction(){		
		if($this->getRequest()->isPost()){
		    $db= new Application_Model_DbTable_DbGlobal();
			$post=$this->getRequest()->getPost();
			$invoice = $post['invoice_id'];
			$sqlinfo ="SELECT * FROM `tb_purchase_order` WHERE order_id = $invoice LIMIT 1";
			$rowinfo=$db->getGlobalDbRow($sqlinfo);
			$sql = "SELECT pui.qty_order,pui.pro_id,pui.price,pui.sub_total
					,(SELECT pur.order FROM tb_purchase_order as pur WHERE pur.order_id = pui.order_id ) as order_no
					,(SELECT pur.all_total FROM tb_purchase_order as pur WHERE pur.order_id = pui.order_id ) as all_total
					,(SELECT pr.qty_perunit FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS qty_perunit
      				,(SELECT pr.item_name FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS item_name
					,(SELECT pr.pro_id FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS pro_id
      				,(SELECT `label` FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS label
     				 ,(SELECT `measure_name` FROM `tb_measure` AS ms WHERE ms.id=(SELECT measure_id FROM tb_product WHERE pro_id=pui.`pro_id`)) AS measure_name
      			FROM `tb_purchase_order_item` AS pui WHERE pui.order_id = ".$invoice;
			$rows=$db->getGlobalDb($sql);
		    $result = array('poinfo'=>$rowinfo,'item'=>$rows);
			echo Zend_Json::encode($result);
			exit();
		}
		
	}
	
	function getReceiveCodeAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbRecieve();
		$rs = $db->getRecieveCode($post["branch_id"]);
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
}