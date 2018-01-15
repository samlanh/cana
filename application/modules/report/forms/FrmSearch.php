<?php 
class report_Form_FrmSearch extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
	public function formSearch($data=null) {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db=new report_Model_DbStock();
		$row_vendor = $db->getVendor();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$row_cat = $db->getCategoryOption();
		$date =new Zend_Date();
    	$txtsearch = new Zend_Form_Element_Text('ad_search');
		$txtsearch->setAttribs(array(
				'class'=>'form-control',
		));
    	$this->addElement($txtsearch);
		$txtsearch->setValue($request->getParam('ad_search'));
    	
    	$start_date = new Zend_Form_Element_Text('start_date');
		$start_date->setAttribs(array(
				'class'=>'form-control date-picker',
		));
		
    	$this->addElement($start_date);
		if($request->getParam('start_date') ==""){
			$start_date->setValue($date->get('MM/01/YYYY'));
		}else{
			$start_date->setValue($request->getParam('start_date'));
		}
		
		$end_date = new Zend_Form_Element_Text('end_date');
		$end_date->setAttribs(array(
				'class'=>'form-control date-picker',
		));
		$end_date->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($end_date);
		if($request->getParam('end_date') ==""){
			$end_date->setValue($date->get('MM/dd/YYYY'));
		}else{
			$end_date->setValue($request->getParam('end_date'));
		}
		
		
		$opt_c = array('-1'=>$tr->translate("SELECT_CATEGORY"));
		if(!empty($row_cat)){
			foreach($row_cat as $rs){
				$opt_c[$rs["id"]] = $rs["name"];
			}
		}
		$category = new Zend_Form_Element_Select('category');
		$category->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$category->setMultiOptions($opt_c);
    	$this->addElement($category);
		$category->setValue($request->getParam('category'));
		
		$opt_v = array('-1'=>$tr->translate("SELECT_VENDOR"));
		if(!empty($row_vendor)){
			foreach($row_vendor as $rs){
				$opt_v[$rs["vendor_id"]] = $rs["v_name"];
			}
		}
		$suppliyer_id = new Zend_Form_Element_Select('suppliyer_id');
		$suppliyer_id->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$suppliyer_id->setMultiOptions($opt_v);
    	$this->addElement($suppliyer_id);
		$suppliyer_id->setValue($request->getParam('suppliyer_id'));
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $db->getBranch();
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
				'Onchange'	=>	'addNewProLocation()'
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam('branch'));
		$this->addElement($branch);
		
		return $this;
	}
	
	

}