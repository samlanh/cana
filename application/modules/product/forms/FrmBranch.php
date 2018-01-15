<?php 
class Product_Form_FrmBranch extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function branch($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Product_Model_DbTable_DbBranch();
		$rs_location = $db->getAllLocation();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		$opt = array(''=>$tr->translate('SELECT'));
		if($controller=='branch' and $action=='add'){
			$opt = array(''=>$tr->translate('SELECT'),-1=>$tr->translate('Add Branch'));
		}
		
		if(!empty($rs_location)){
			foreach($rs_location as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
			
		}
		$location = new Zend_Form_Element_Select('location');
		$location->setAttribs(array('class'=>'form-control','required'=>true));
		if($controller=='branch' and $action=='add'){
			$location->setAttribs(array("onchange"=>'showBranch()'));
		}
		$location->setMultiOptions($opt);
		$this->addElement($location);
		
		$branch_name = new Zend_Form_Element_Text('branch_name');
		$branch_name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$code = new Zend_Form_Element_Text("code");
		$code->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$prefix = new Zend_Form_Element_Text("prefix");
		$prefix->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$title_report_en = new Zend_Form_Element_Text("title_en");
		$title_report_en->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$title_report_kh = new Zend_Form_Element_Text("title_kh");
		$title_report_kh->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$logo = new Zend_Form_Element_File("logo");
		$logo->setAttribs(array(
				'class'=>'form-control',
		));
		
		$addres = new Zend_Form_Element_Textarea("address");
		$addres->setAttribs(array(
				'class'=>'form-control',
				'style'=>'height:59px',
				//'required'=>'required'
		));
		 
		$contact_name = new Zend_Form_Element_Text("contact");
		$contact_name->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		 
		$contact_num = new Zend_Form_Element_Text("contact_num");
		$contact_num->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$email = new Zend_Form_Element_Text("email");
		$email->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$fax = new Zend_Form_Element_Text("fax");
		$fax->setAttribs(array(
				'class'=>'form-control',
				
		));
		
		$office_num = new Zend_Form_Element_Text("office_num");
		$office_num->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text("remark");
		$remark->setAttribs(array(
				'class'=>'form-control',
		
		));
		
		if($data != null){
			$branch_name->setValue($data["name"]);
			$contact_name->setValue(@$data["contact"]);
			$contact_num->setValue(@$data["phone"]);
			$email->setValue(@$data["email"]);
			$fax->setValue(@$data["fax"]);
			$office_num->setValue(@$data["office_tel"]);
			$status->setValue($data["status"]);
			$remark->setValue($data["remark"]);
			$addres->setValue(@$data["address"]);
			$code->setValue(@$data["code"]);
			$prefix->setValue(@$data["prefix"]);
			$title_report_kh->setValue($data["title_report_kh"]);
			$title_report_en->setValue($data["title_report_en"]);
			$old_photo = new Zend_Form_Element_Text("old_photo");
			$old_photo->setValue($data["logo"]);
			$this->addElement($old_photo);
			$location->setValue(@$data["loc_id"]);
		}
			
		$this->addElements(array($logo,$title_report_kh,$title_report_en,$code,$prefix,$branch_name,$addres,$contact_name,$contact_num,$email,$fax,$office_num,$status,$remark));
		return $this;
	}
}