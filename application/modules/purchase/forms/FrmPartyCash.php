<?php 
class Purchase_Form_FrmPartyCash extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
	public function add($data=null) {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		
		$db_r = new Purchase_Model_DbTable_DbPartyCash();
		
		$db_re = new Sales_Model_DbTable_DbRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		$rp_code = $db_r->getRequestCode($result["branch_id"]);
		$date =new Zend_Date();
		
		$row = $db_re->getPlan();
		$option = array(''=>$tr->translate("SELECT_PLAN"));
		if(!empty($row)){
			foreach($row as $rs){
				$option[$rs["id"]] = $rs["name"];
			}
		}
		$plan=new Zend_Form_Element_Select('plan');
    	$plan ->setAttribs(array(
    			'class' => 'validate[required] form-control select2me',
				'onChange'=>'addPlanAddr()'
//'Onchange'=>'getCustomerInfo()',
    			));
    	$plan->setMultiOptions($option);
    	$this->addElement($plan);
		
		$date_request_work_space = new Zend_Form_Element_Text('date_request_work_space');
		$date_request_work_space->setAttribs(array('class'=>'form-control date-picker'));
		$date_request_work_space->setValue(date('m/d/Y'));
		$this->addElement($date_request_work_space);
		
		$number_work_space = new Zend_Form_Element_Text('number_work_space');
		$number_work_space->setAttribs(array('class'=>'form-control'));
		$this->addElement($number_work_space);
		
		$plan_addr=new Zend_Form_Element_Text('plan_addr');
    	$plan_addr ->setAttribs(array(
    			'class' => 'validate[required] form-control',
    			'Onchange'=>'getCustomerInfo()',
    			));
    	$this->addElement($plan_addr);
		
		$request_no = new Zend_Form_Element_Text('request_no');
		$request_no->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Select Purchase No','readOnly'=>'readOnly'));
		$request_no->setValue($rp_code);
    	$this->addElement($request_no);
		
		$date_request = new Zend_Form_Element_Text('date_request');
		$date_request->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Select Purchase No','readOnly'=>true));
		$date_request->setValue(date('m/d/Y'));
    	$this->addElement($date_request);
		
		$sql_v = "SELECT v.`vendor_id`,v.`v_name`  FROM `tb_vendor` AS v WHERE 1";
		$row_v = $db->getGlobalDb($sql_v);
		$opt_v = array(''=>$tr->translate('SELECT_VENDOR'));
		if(!empty($row_v)){
			foreach($row_v as $rs){
				$opt_v[$rs["vendor_id"]] = $rs["v_name"];
			}
		}
		$vendor = new Zend_Form_Element_Select('vendor');
		$vendor->setAttribs(array('class'=>'validate[required] form-control select2me',"readOnly"=>"readOnly",'placeholder'=>'Select Purchase No'));
    	$vendor->setMultiOptions($opt_v);
		$this->addElement($vendor);
		
		$sql = "SELECT pl.id,pl.`name` FROM `tb_sublocation` AS pl WHERE 1";
		$row_b = $db->getGlobalDb($sql);
		$opt_b = array(''=>$tr->translate('SELECT_BRANCH'));
		if(!empty($row_b)){
			foreach($row_b as $rs){
				$opt_b[$rs["id"]] = $rs["name"];
			}
		}
		$branch = new Zend_Form_Element_Select('branch');
		$branch->setAttribs(array('class'=>'validate[required] form-control select2me','readOnly'=>'readOnly','onChange'=>'getRequestCode()'));
		$branch->setMultiOptions($opt_b);
		$branch->setValue($result['branch_id']);
    	$this->addElement($branch);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array('class'=>'form-control','placeholder'=>'Remark Here'));
    	$this->addElement($remark);
		
		$opt_s = array(1=>$tr->translate("ACTIVE"),2=>$tr->translate("DEACTIVE"));
		$status = new Zend_Form_Element_Select('status');
		$status->setAttribs(array('class'=>'validate[required] form-control'));
		$status->setMultiOptions($opt_s);
    	$this->addElement($status);
		
		$reject_check = new Zend_Form_Element_Textarea("reject_check");
		$reject_check->setAttribs(array('class'=>'form-control','style'=>'height:50px;'));
		$this->addElement($reject_check);
		
		$reject_approve = new Zend_Form_Element_Textarea("reject_approve");
		$reject_approve->setAttribs(array('class'=>'form-control','style'=>'height:50px;'));
		$this->addElement($reject_approve);
		
    	
    	if($data != null) {
	        $branch->setValue($data["branch_id"]);
			$request_no->setValue($data["re_code"]);
			$status->setValue($data["status"]);
			$remark->setValue($data["remark"]);
			$plan->setValue($data["plan_id"]);
			$number_work_space->setValue($data["number_request"]);
			$date_request_work_space->setValue(date("m/d/Y",strtotime($data["date_from_work_space"])));
			$reject_approve->setValue($data["reject_approve"]);
			$reject_check->setValue($data["reject_check"]);
		}
    	return $this;
	}
	
	public function addpo($data=null) {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		
		$db_r = new Purchase_Model_DbTable_DbPartyCash();
		
		$db_re = new Sales_Model_DbTable_DbRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		$rp_code = $db_r->getRequestCode($result["branch_id"]);
		$pocode = $db_r->getPOCode($result["branch_id"]);
		$date =new Zend_Date();
		
		$po_no = new Zend_Form_Element_Text("po_no");
		$po_no->setAttribs(array('class'=>'form-control'));
		$po_no->setValue($pocode);
		$this->addElement($po_no);
		
		$po_date = new Zend_Form_Element_Text('po_date');
		$po_date->setAttribs(array('class'=>'form-control date-picker'));
		$po_date->setValue(date('m/d/Y'));
		$this->addElement($po_date);
		
		$row = $db_re->getPlan();
		$option = array(''=>$tr->translate("SELECT_PLAN"));
		if(!empty($row)){
			foreach($row as $rs){
				$option[$rs["id"]] = $rs["name"];
			}
		}
		$plan=new Zend_Form_Element_Select('plan');
    	$plan ->setAttribs(array(
    			'class' => 'validate[required] form-control',
				'onChange'=>'addPlanAddr()','readOnly'=>true,
    			));
    	$plan->setMultiOptions($option);
    	$this->addElement($plan);
		
		$date_request_work_space = new Zend_Form_Element_Text('date_request_work_space');
		$date_request_work_space->setAttribs(array('class'=>'form-control date-picker','readOnly'=>true));
		$date_request_work_space->setValue(date('m/d/Y'));
		$this->addElement($date_request_work_space);
		
		$number_work_space = new Zend_Form_Element_Text('number_work_space');
		$number_work_space->setAttribs(array('class'=>'form-control','readOnly'=>true));
		$this->addElement($number_work_space);
		
		$plan_addr=new Zend_Form_Element_Text('plan_addr');
    	$plan_addr ->setAttribs(array(
    			'class' => 'validate[required] form-control',
    			'Onchange'=>'getCustomerInfo()',
    			));
    	$this->addElement($plan_addr);
		
		$request_no = new Zend_Form_Element_Text('request_no');
		$request_no->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Select Purchase No','readOnly'=>'readOnly'));
		$request_no->setValue($rp_code);
    	$this->addElement($request_no);
		
		$date_request = new Zend_Form_Element_Text('date_request');
		$date_request->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Select Purchase No','readOnly'=>true));
		$date_request->setValue(date('m/d/Y'));
    	$this->addElement($date_request);
		
		$sql_v = "SELECT v.`vendor_id`,v.`v_name`  FROM `tb_vendor` AS v WHERE 1";
		$row_v = $db->getGlobalDb($sql_v);
		$opt_v = array(''=>$tr->translate('SELECT_VENDOR'));
		if(!empty($row_v)){
			foreach($row_v as $rs){
				$opt_v[$rs["vendor_id"]] = $rs["v_name"];
			}
		}
		$vendor = new Zend_Form_Element_Select('vendor');
		$vendor->setAttribs(array('class'=>'validate[required] form-control select2me',"readOnly"=>"readOnly",'placeholder'=>'Select Purchase No'));
    	$vendor->setMultiOptions($opt_v);
		$this->addElement($vendor);
		
		$sql = "SELECT pl.id,pl.`name` FROM `tb_sublocation` AS pl WHERE 1";
		$row_b = $db->getGlobalDb($sql);
		$opt_b = array(''=>$tr->translate('SELECT_BRANCH'));
		if(!empty($row_b)){
			foreach($row_b as $rs){
				$opt_b[$rs["id"]] = $rs["name"];
			}
		}
		$branch = new Zend_Form_Element_Select('branch');
		$branch->setAttribs(array('class'=>'validate[required] form-control','readOnly'=>true,'readOnly'=>'readOnly','onChange'=>'getRequestCode()'));
		$branch->setMultiOptions($opt_b);
		$branch->setValue($result['branch_id']);
    	$this->addElement($branch);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array('class'=>'form-control','placeholder'=>'Remark Here'));
    	$this->addElement($remark);
		
		$opt_s = array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
		$status = new Zend_Form_Element_Select('status');
		$status->setAttribs(array('class'=>'validate[required] form-control'));
		$status->setMultiOptions($opt_s);
    	$this->addElement($status);
		
		$reject_check = new Zend_Form_Element_Textarea("reject_check");
		$reject_check->setAttribs(array('class'=>'form-control','style'=>'height:50px;'));
		$this->addElement($reject_check);
		
		$reject_approve = new Zend_Form_Element_Textarea("reject_approve");
		$reject_approve->setAttribs(array('class'=>'form-control','style'=>'height:50px;'));
		$this->addElement($reject_approve);
		
    	
    	if($data != null) {
    		$request=Zend_Controller_Front::getInstance()->getRequest();
    		if($request->getActionName()=='editpurchase'){
    			$date_request->setValue(date("m/d/Y",strtotime($data["po_date"])));
    			$plan->setValue($data["paln_id"]);
    			$number_work_space->setValue($data["order_number"]);
    			$date_request_work_space->setValue(date("m/d/Y",strtotime($data["re_work_date"])));
    		}else{
				$date_request->setValue(date("m/d/Y",strtotime($data["date_request"])));
				$plan->setValue($data["plan_id"]);
				$number_work_space->setValue($data["number_request"]);
				$date_request_work_space->setValue(date("m/d/Y",strtotime($data["date_from_work_space"])));
    		}
    		$reject_approve->setValue($data["reject_approve"]);
    		$reject_check->setValue($data["reject_check"]);
    		$remark->setValue($data["remark"]);
    		$status->setValue($data["status"]);
    		$request_no->setValue($data["re_code"]);
    		$branch->setValue($data["branch_id"]);
		}
    	return $this;
	}
}