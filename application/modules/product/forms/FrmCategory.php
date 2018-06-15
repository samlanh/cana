<?php 
class Product_Form_FrmCategory extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function cat($data=null){
		$db = new Product_Model_DbTable_DbCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('cat_name');
		$name->setAttribs(array(
				'class'=>'form-control','onChange'=>'getCategoryExist()',
				'required'=>1
		));
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$prifix = new Zend_Form_Element_Text('prifix');
		$prifix->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required',
				'onChange'=>'getPrefixyExist()'
		));
		 
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$opt = array(''=>$tr->translate("SEELECT_CATEGORY"));
		$row_cate = $db->getAllCategorys();
		if(!empty($row_cate)){
			foreach ($row_cate as $rs){
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
		$is_none_stock = new Zend_Form_Element_CheckBox("is_none_stock");
		$is_none_stock->setValue(0);
		
		$start_code = new Zend_Form_Element_Text('start_code');
		$start_code->setAttribs(array(
				'class'=>'form-control',
		));
		if($data != null){
			
			$name->setValue($data["name"]);
			$parent->setValue($data["parent_id"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
			$prifix->setValue($data["prefix"]);
			$is_none_stock->setValue($data["is_none_stock"]);
			$start_code->setValue($data["start_code"]);
		}
			
		$this->addElements(array($start_code,$is_none_stock,$parent,$name,$status,$remark,$prifix));
		return $this;
	}
	
	public function categoryFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbCategory();
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array(
				'class'=>'form-control'
		));
		$name->setValue($request->getParam("name"));
		
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_CATEGORY"));
		$row_cate = $db->getAllCategorys();
		if(!empty($row_cate)){
			foreach ($row_cate as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$parent->setMultiOptions($opt);
		$parent->setValue($request->getParam("parent"));
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$stock_type = new Zend_Form_Element_Select("stock_type");
		$stock_type->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array(-1=>$tr->translate("TYPE"),0=>$tr->translate("រាប់បញ្ចូលស្តុក"),1=>$tr->translate("មិនរាប់បញ្ចូលស្តុក"));
		$stock_type->setMultiOptions($opt);
		$stock_type->setValue($request->getParam("stock_type"));
		
		$this->addElements(array($stock_type,$parent,$name,$status));
		return $this;
	}
}