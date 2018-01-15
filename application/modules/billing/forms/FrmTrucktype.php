<?php 
class Billing_Form_FrmTrucktype extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function frm_truck_type($data=null){
		$db = new Category_Model_DbTable_DbCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('cat_name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
		$truck_en = new Zend_Form_Element_Text('truck_ca_en');
		$truck_en->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$truck_kh = new Zend_Form_Element_Text('truck_ca_kh');
		$truck_kh->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_CATEGORY"));
		if(!empty($db->getAllCategory())){
			foreach ($db->getAllCategory() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$parent->setMultiOptions($opt);
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
		
		if($data != null){
			//print_r($data);exit();
			$truck_kh->setValue($data["name_kh"]);
			$truck_en->setValue($data["name_en"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($truck_kh,$truck_en,$parent,$name,$status,$remark));
		return $this;
	}
	
	/////form type concrete 
	public function frm_truck_type_concrete($data=null){
		$db = new Category_Model_DbTable_DbCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('cat_name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
			
		$truck_en = new Zend_Form_Element_Text('truck_ca_en');
		$truck_en->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
	
		$truck_kh = new Zend_Form_Element_Text('truck_ca_kh');
		$truck_kh->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
	
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_CATEGORY"));
		if(!empty($db->getAllCategory())){
			foreach ($db->getAllCategory() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$parent->setMultiOptions($opt);
	
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
	
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
	
		if($data != null){
			//print_r($data);exit();
			$truck_kh->setValue($data["name_kh"]);
			$truck_en->setValue($data["name_en"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($truck_kh,$truck_en,$parent,$name,$status,$remark));
		return $this;
	}
}