<?php

class Application_Form_FrmReport extends Zend_Form
{
    public function init()
    {
   
    }
    public function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function salseReport($data=null)
    {
    	$db=new Application_Model_DbTable_DbGlobal();
    	$request = Zend_Controller_Front::getInstance()->getRequest();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		
    	
    	$rs=$db->getGlobalDb('SELECT id, item_name,item_code FROM tb_product WHERE item_name!="" ORDER BY item_name ');
    	$options=array(''=>$tr->translate('SELECT'));
    	$proValue = $request->getParam('item');
    	foreach($rs as $read) $options[$read['id']]=$read['item_name']." ".$read['item_code'];
    	$pro_id=new Zend_Form_Element_Select('item');
    	$pro_id->setMultiOptions($options);
    	$pro_id->setAttribs(array(
    			'id'=>'item',
    	));
    	$pro_id->setValue($proValue);
    	$this->addElement($pro_id);
    	
    	$sql='SELECT DISTINCT name,id FROM tb_sublocation WHERE name!="" AND status=1 ';
    	$user = $this->GetuserInfo();
    	if($user["level"]!=1 AND $user["level"]!=2){
    		$sql .= " AND location_id = ".$user["location_id"];
    			
    	}
    	$rs=$db->getGlobalDb($sql);
    	$options=array(''=>$tr->translate('Please_Select_Location'));
    	$locationValue = $request->getParam('branch_id');
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$location_id=new Zend_Form_Element_Select('branch_id');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'branch_id',
    			'class'=>'form-control select2me'
    	));
    	$location_id->setValue($locationValue);
    	
		$nameValue = $request->getParam('text_search');
    	$nameElement = new Zend_Form_Element_Text('text_search');
    	$nameElement->setAttribs(array(
    			'class'=>'form-control'
    	));
    	$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
		
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_category WHERE name!="" ');
    	$options=array(''=>$tr->translate('Please_Select'));
    	$cateValue = $request->getParam('category_id');
    	foreach($rs as $read) $options[$read['id']]=$read['Name'];
    	$cate_element=new Zend_Form_Element_Select('category_id');
    	$cate_element->setMultiOptions($options);
    	$cate_element->setAttribs(array(
    			'id'=>'category_id',
    			'onchange'=>'getProductFilter()',
    	));
    	$cate_element->setValue($cateValue);
    	$this->addElement($cate_element);
    	 
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_sublocation WHERE name!="" ORDER BY name');
    	$options=array(''=>$tr->translate('Please_Select'));
    	$branchValue = $request->getParam('branch_id');
    	foreach($rs as $read) $options[$read['branch_id']]=$read['Name'];
    	$branch_element=new Zend_Form_Element_Select('branch_id');
    	$branch_element->setMultiOptions($options);
    	$branch_element->setAttribs(array(
    			'id'=>'branch_id',
    			'onchange'=>'getProductFilter();',
    	));
    	$branch_element->setValue($branchValue);
    	$this->addElement($branch_element);
    	
    	$startDate = new Zend_Form_Element_Text("start_date");//echo date("Y-m-d");
    	$startDatevalue = $request->getParam("start_date");
    	$startDate->setAttribs(array(
    			'class'=>'form-control date-picker',
    			'placeHolder'=>'Start Date'
    			));
    	$startDate->setValue($startDatevalue);
    	 
    	$endDate = new Zend_Form_Element_Text("end_date");
    	$endDate->setAttribs(array(
    			'class'=>'form-control date-picker'
    	));
    	
    	$endDatevalue = $request->getParam("end_date");
    	if(empty($endDatevalue)){$endDatevalue = date("m/d/Y");}
    	$endDate->setValue($endDatevalue);

    	$this->addElements(array($startDate,$endDate,$location_id));
    	Application_Form_DateTimePicker::addDateField(array('start_date','end_date'));
     	return $this;
    
    }
    
    
    public function productDetailReport($data=null)
    {
    	$db=new Application_Model_DbTable_DbGlobal();
    	$request = Zend_Controller_Front::getInstance()->getRequest();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
     	$item = new report_Model_DbQuery();
    	$sql="SELECT p.id, p.item_name,p.item_code FROM tb_product AS p WHERE p.item_name!='' ";
			$sql.=" ORDER BY p.item_name " ;
		$rs=$db->getGlobalDb($sql);
		if($rs){
    	$options=array(''=>$tr->translate('CHOOSE_PRODUCT'));
    	foreach($rs as $read) $options[$read['id']]=$read['item_code']." ".$read['item_name'];
		}else{
			$options=array(''=>'No Items Results');
		}
    	$pro_id=new Zend_Form_Element_Select('item');
    	$pro_id->setMultiOptions($options);
    	$proValue = $request->getParam('item');
    	$pro_id->setAttribs(array(
    			'id'=>'item',
    			'class'=>'form-control select2me'
    	));
    	$pro_id->setValue($proValue);
    	$this->addElement($pro_id);
    	
    	$sql='SELECT DISTINCT name,id FROM tb_sublocation WHERE name!="" AND status=1 ';
    	$user = $this->GetuserInfo();
    	if($user["level"]!=1 AND $user["level"]!=2){
    		$sql .= " AND id= ".$user["location_id"];
    		 
    	}
    	$rs=$db->getGlobalDb($sql);
    	$options=array(''=>$tr->translate('CHOOSE_BRANCH'));
    	$locationValue = $request->getParam('branch_id');
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$location_id=new Zend_Form_Element_Select('branch_id');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'LocationId',
    			'onchange'=>'getProductFilter();',
    			'class'=>'form-control select2me'
    	));
    	$location_id->setValue($locationValue);
    	
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_category WHERE name!="" ORDER BY id DESC ');
    	$options=array(''=>$tr->translate('CHOOSE_CATEGORY'));
    	$cateValue = $request->getParam('category_id');
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$cate_element=new Zend_Form_Element_Select('category_id');
    	$cate_element->setMultiOptions($options);
    	$cate_element->setAttribs(array(
    			'id'=>'category_id',
    			'onchange'=>'getProductFilter()',
    			'class'=>'form-control select2me'
    	));
    	$cate_element->setValue($cateValue);
    	$this->addElement($cate_element);
    
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_brand WHERE name!="" ORDER BY id ');
    	$options=array(''=>$tr->translate('CHOOSE_BRAND'));
    	$branchValue = $request->getParam('branch_id');
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	
    	$branch_element=new Zend_Form_Element_Select('brand_id');
    	$branch_element->setMultiOptions($options);
    	$branch_element->setAttribs(array(
    			'id'=>'branch_id',
    			'onchange'=>'getProductFilter()',
    			'class'=>'form-control select2me'
    	));
    	$brandValue = $request->getParam('brand_id');
    	$branch_element->setValue($brandValue);
    	$this->addElement($branch_element);
    	 
    	$startDate = new Zend_Form_Element_Text("start_date");//echo date("Y-m-d");
    	$startDatevalue = $request->getParam("start_date");
    	$startDate->setAttribs(array(
    			'class'=>'form-control date-picker',
    			'placeHolder'=>'Start Date'
    			));
    	$startDate->setValue($startDatevalue);
    	 
    	$endDate = new Zend_Form_Element_Text("end_date");
    	$endDate->setAttribs(array(
    			'class'=>'form-control date-picker'
    	));
    	
    	$endDatevalue = $request->getParam("end_date");
    	if(empty($endDatevalue)){$endDatevalue = date("m/d/Y");}
    	$endDate->setValue($endDatevalue);
    	
    	$txt_search = new Zend_Form_Element_Text("txt_search");
    	$txt_search->setAttribs(array(
    			'class'=>'form-control'));
    	$txt_searchvalue = $request->getParam("txt_search");
    	$txt_search->setValue($txt_searchvalue);
    	$this->addElements(array($txt_search,$startDate,$endDate,$location_id));
    	return $this;
    }
    
    function FrmReportPurchase(){
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		
		
		$rs_plan = $db->getGlobalDb('SELECT id,name FROM tb_plan WHERE status=1');
		$options_plan=array(''=>$tr->translate('SELECT'));
    	$plan_val = $request->getParam('plan');
    	foreach($rs_plan as $read) $options_plan[$read['id']]=$read['name'];
    	$plan=new Zend_Form_Element_Select('plan');
    	$plan->setMultiOptions($options_plan);
		$plan->setAttribs(array(
    			'class'=>'form-control select2me'
    	));
    	$plan->setValue($plan_val);
    	$this->addElement($plan);
    	
    	
    	$nameValue = $request->getParam('text_search');
    	$nameElement = new Zend_Form_Element_Text('text_search');
    	$nameElement->setAttribs(array(
    			'class'=>'form-control'
    	));
    	$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    	
    	$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" AND status=1 ');
    	$options=array($tr->translate('Choose Suppliyer'));
    	$vendorValue = $request->getParam('suppliyer_id');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
    	$vendor_element=new Zend_Form_Element_Select('suppliyer_id');
    	$vendor_element->setMultiOptions($options);
    	$vendor_element->setAttribs(array(
    			'id'=>'suppliyer_id',
    			'class'=>'form-control select2me'
    	));
    	$vendor_element->setValue($vendorValue);
    	$this->addElement($vendor_element);
    	
    	$startDateValue = $request->getParam('start_date');
		
    	$endDateValue = $request->getParam('end_date');
    	
    	if($endDateValue==""){
    		$endDateValue=date("m/d/Y");
    	}
    	
    	$startDateElement = new Zend_Form_Element_Text('start_date');
    	$startDateElement->setValue($startDateValue);
    	$startDateElement->setAttribs(array(
    			'class'=>'form-control form-control-inline date-picker',
    			'placeholder'=>'Start Date'
    	));
    	if($startDateValue==""){
			//$startDateElement->setValue(date("m/01/Y"));
		}else{
			$startDateElement->setValue($startDateValue);
		}
    	$this->addElement($startDateElement);
    	$endDateElement = new Zend_Form_Element_Text('end_date');
    	
    	$endDateElement->setValue($endDateValue);
    	$this->addElement($endDateElement);
    	$endDateElement->setAttribs(array(
    			'class'=>'form-control form-control-inline date-picker'
    	));
    	 
    	$statusCOValue=4;
    	$statusCOValue = $request->getParam('purchase_status');
    	$optionsCOStatus=array(0=>$tr->translate('CHOOSE_STATUS'),2=>$tr->translate('OPEN'),3=>$tr->translate('IN_PROGRESS'),4=>$tr->translate('PAID'),5=>$tr->translate('RECEIVED'),6=>$tr->translate('MENU_CANCEL'));
    	$statusCO=new Zend_Form_Element_Select('purchase_status');
    	$statusCO->setMultiOptions($optionsCOStatus);
    	$statusCO->setattribs(array(
    			'id'=>'status',
    			'class'=>'form-control'
    	));
    	
    	$statusCO->setValue($statusCOValue);
    	$this->addElement($statusCO);
    	
    	$sql='SELECT DISTINCT name,id FROM tb_sublocation WHERE name!="" AND status=1 ';
    	$user = $this->GetuserInfo();
    	if($user["level"]!=1 AND $user["level"]!=2){
    		$sql .= " AND id= ".$user["branch_id"];
    		 
    	}
    	$rs=$db->getGlobalDb($sql);
    	$options=array(''=>$tr->translate('CHOOSE_BRANCH'));
    	$locationValue = $request->getParam('branch_id');
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$location_id=new Zend_Form_Element_Select('branch_id');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'LocationId',
    			'onchange'=>'getProductFilter();',
    			'class'=>'form-control select2me'
    	));
    	$location_id->setValue($locationValue);
    	$this->addElement($location_id);
    	
    	$po_pedding = new Zend_Form_Element_Select("po_pedding");
    	$opt = array(''=>$tr->translate("SELECT"));
    	$rspo_pedding = $db->getPurchasePedding();
    	if(!empty($rspo_pedding)){
    		foreach ($rspo_pedding as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$po_pedding->setAttribs(array(
    			'class'=>'form-control select2me',
    			//'required'=>'required',
    			'Onchange'	=>	'addNewProLocation()'
    	));
    	$po_pedding->setMultiOptions($opt);
    	$po_pedding->setValue($request->getParam('po_pedding'));
    	$this->addElement($po_pedding);
    	
    	$search_date = new Zend_Form_Element_Select('search_date');
    	$search_date->setAttribs(array(
    	    'class'=>'form-control',
    	    
    	));
    	$opt_d = array(''=>$tr->translate("SELECT"),1=>$tr->translate("Daily"),2=>$tr->translate('Weekly'),3=>$tr->translate('Monthly'));
    	$search_date->setMultiOptions($opt_d);
    	$search_date->setValue($request->getParam('search_date'));
    	$this->addElement($search_date);
    	
    	return $this;
    }
	
}

