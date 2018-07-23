<?php 
class Product_Form_FrmProduct extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function add($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbProduct();
		$p_code = $db->getProductCode();
		$name = new Zend_Form_Element_Text("name");
		$name->setAttribs(array(
				'class'=>'form-control validate[required]',
				'id'=>'item_name',
				'required'=>'required'
		));
		
		$pro_code = new Zend_Form_Element_Text("pro_code");
		$pro_code->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$pro_code->setValue($p_code);
		
		$int_code = new Zend_Form_Element_Text("int_code");
		$int_code->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$int_code->setValue($p_code);
		$this->addElement($int_code);
		 
		$serial = new Zend_Form_Element_Text("serial");
		$serial->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		 
		$barcode = new Zend_Form_Element_Text("barcode");
		$barcode->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		 
		$opt = array(''=>$tr->translate("SELECT_BRAND"),-1=>$tr->translate("ADD_NEW_BRAND"));
		$brand = new Zend_Form_Element_Select("brand");
		$brand->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupBrand();',
				//'required'=>'required'
		));
		$row_brand= $db->getBrand();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$brand->setMultiOptions($opt);
		 
		$opt = array(''=>$tr->translate("SELECT_MODEL"),-1=>$tr->translate("ADD_NEW_MODEL"));
		$model = new Zend_Form_Element_Select("model");
		$model->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupModel()',
				//'required'=>'required'
		));
		$row_model = $db->getModel();
		if(!empty($row_model)){
			foreach ($row_model as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$model->setMultiOptions($opt);
		 
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"),'-1'=>$tr->translate("ADD_NEW_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupCategory();getProductPrefix();',
				//'required'=>'required'
		));
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		
		$opt = array(''=>$tr->translate("SELECT_COLOR"),-1=>$tr->translate("ADD_NEW_COLOR"));
		$color = new Zend_Form_Element_Select("color");
		$color->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupColor()',
				//'required'=>'required'
		));
		$row_color = $db->getColor();
		if(!empty($row_color)){
			foreach ($row_color as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$color->setMultiOptions($opt);
		 
		$opt = array(''=>$tr->translate("SELECT_SIZE"),-1=>$tr->translate("ADD_NEW_SIZE"));
		$size = new Zend_Form_Element_Select("size");
		$size->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupSize()',
				//'required'=>'required'
		));
		$row_size = $db->getSize();
		if(!empty($row_size)){
			foreach ($row_size as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$size->setMultiOptions($opt);
		 
		$unit = new Zend_Form_Element_Text("unit");
		$unit->setAttribs(array(
				'class'=>'form-control',
				'readonly'=>true
		));
		$unit->setValue(1);
		 
		$qty_per_unit = new Zend_Form_Element_Text("qty_unit");
		$qty_per_unit->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		 
		$opt = array(''=>$tr->translate("SELECT_MEASURE"),-1=>$tr->translate("ADD_NEW_MEASURE"));
		$measure = new Zend_Form_Element_Select("measure");
		$measure->setAttribs(array(
				'class'=>'form-control select2me',
				//'required'=>'required',
				'Onchange'	=>	'getMeasureLabel();getPopupMeasure();'
		));
		$row_measure= $db->getMeasure();
		if(!empty($row_measure)){
			foreach ($row_measure as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$measure->setMultiOptions($opt);
		 
		$label = new Zend_Form_Element_Text("label");
		$label->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		 
		$description = new Zend_Form_Element_Text("description");
		$description->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
				//'Onchange'	=>	'getMeasureLabel()'
		));
		$status->setMultiOptions($opt);
		
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
				//'required'=>'required',
				'Onchange'	=>	'addNewProLocation()'
		));
		$branch->setMultiOptions($opt);
		
		$price_type = new Zend_Form_Element_Select("price_type");
		$opt = array();
		$row_price = $db->getPriceType();
		if(!empty($row_price)){
			foreach ($row_price as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$price_type->setAttribs(array(
				'class'=>'form-control select2me',
				//'required'=>'required',
				'Onchange'	=>	'addNewPriceType()'
		));
		$price_type->setMultiOptions($opt);
		
		$price = new Zend_Form_Element_Text("price");
		$price->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$is_convertor = new Zend_Form_Element_CheckBox("is_convertor");
		$is_convertor->setAttribs(array('onChange'=>'isConvertor()'));
		$this->addElement($is_convertor);
		
		$is_meterail = new Zend_Form_Element_CheckBox("is_meterail");
		$is_meterail->setAttribs(array(''));
		$this->addElement($is_meterail);
		
		$convertor_measure = new Zend_Form_Element_Text("convertor_measure");
		$convertor_measure->setAttribs(array(
				'class'=>'form-control',
				'placeHolder'=>$tr->translate('CONVERTOR_MEASURE'),'readonly'=>true
		));
		$this->addElement($convertor_measure);
		
		$sign = new Zend_Form_Element_Text("sign");
		$sign->setAttribs(array(
				'class'=>'form-control',
				'placeHolder'=>$tr->translate('SIGN'),'readonly'=>true
		));
		$this->addElement($sign);
		
		if($data!=null){
			if($data["is_convertor"]==1){
				$is_convertor->setAttribs(array('checked'=>true));
			}
			$convertor_measure->setValue($data["convertor_measure"]);
			$sign->setValue($data["sign"]);
			$name->setValue($data["item_name"]);
			$pro_code->setValue($data["item_code"]);
			$int_code->setValue($data["int_code"]);
			$barcode->setValue($data["barcode"]);
			$serial->setValue($data["serial_number"]);
			$brand->setValue($data["brand_id"]);
			$category->setValue($data["cate_id"]);
			$model->setValue($data["model_id"]);
			$color->setValue($data["color_id"]);
			$size->setValue($data["size_id"]);
			$measure->setValue($data["measure_id"]);
			$label->setValue($data["unit_label"]);
			$description->setValue($data["note"]);
			$qty_per_unit->setValue($data["qty_perunit"]);
			$status->setValue($data["status"]);
			$price->setValue($data["price"]);
			$is_meterail->setValue($data["is_meterail"]);
		}
		
		$this->addElements(array($price,$price_type,$branch,$status,$pro_code,$name,$serial,$brand,$model,$barcode,$category,$size,$color,$measure,$qty_per_unit,$unit,$label,$description));
		return $this;
	}
	function productFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbProduct();
		$ad_search = new Zend_Form_Element_Text("ad_search");
		$ad_search->setAttribs(array(
				'class'=>'form-control',
		));
		$ad_search->setValue($request->getParam("ad_search"));
		
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
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam("branch"));
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$stock_type = new Zend_Form_Element_Select("stock_type");
		$opt = array(-1=>$tr->translate("SELECT_STOCKTYPE"),'1'=>$tr->translate("PRODUCT_PITYCASH"),'0'=>$tr->translate("NORMAL_PRODUCT"));
		$stock_type->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$stock_type->setMultiOptions($opt);
		$stock_type->setValue($request->getParam("stock_type"));
		
		$opt = array(''=>$tr->translate("SELECT_BRAND"));
		$brand = new Zend_Form_Element_Select("brand");
		$brand->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_brand = $db->getBrand();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$brand->setMultiOptions($opt);
		$brand->setValue($request->getParam("brand"));
			
		$opt = array(''=>$tr->translate("SELECT_MODEL"));
		$model = new Zend_Form_Element_Select("model");
		$model->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_model = $db->getModel();
		if(!empty($row_model)){
			foreach ($row_model as $rss){
				$opt[$rss["key_code"]] = $rss["name"];
			}
		}
		$model->setMultiOptions($opt);
		$model->setValue($request->getParam("model"));
			
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
		
		$opt = array(''=>$tr->translate("SELECT_COLOR"));
		$color = new Zend_Form_Element_Select("color");
		$color->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_color = $db->getColor();
		if(!empty($row_color)){
			foreach ($row_color as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$color->setMultiOptions($opt);
		$color->setValue($request->getParam("color"));
			
		$opt = array(''=>$tr->translate("SELECT_SIZE"));
		$size = new Zend_Form_Element_Select("size");
		$size->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_size = $db->getSize();
		if(!empty($row_size)){
			foreach ($row_size as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$size->setMultiOptions($opt);
		$size->setValue($request->getParam("size"));
		
		$this->addElements(array($stock_type,$ad_search,$branch,$brand,$model,$category,$color,$size,$status));
		return $this;
	}
}