<?php

class Rsvacl_UsertypeController extends Zend_Controller_Action
{

	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		if(empty($rs)){
			//Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
    }

    public function indexAction()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $getUser = new Rsvacl_Model_DbTable_DbUserType();
        $userQuery = "SELECT u.user_type_id,u.user_type,(SELECT u1.user_type FROM `tb_acl_user_type` u1 
                     WHERE u1.user_type_id = u.parent_id LIMIT 1) parent_id FROM `tb_acl_user_type` u ORDER BY user_type_id DESC";
        $order=" ORDER BY user_type_id DESC";
         
        $rows = $getUser->getUserTypeInfo($userQuery);
        if($rows){
        	$link = array("rsvacl","usertype","edit");
        	$links = array('user_type'=>$link);
        	$list=new Application_Form_Frmlist();
        	$columns=array($tr->translate('USER_TYPE_NAME'), $tr->translate('TYPE_OF_CAP'));
        	$this->view->form=$list->getCheckList(1, $columns, $rows, $links);
        }else $this->view->form = $tr->translate('NO_RECORD_FOUND');
    }
    
    public function viewUserTypeAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new Rsvacl_Model_DbTable_DbUserType();
    		$user_type_id = $this->getRequest()->getParam('id');
    		$rs=$db->getUserType($user_type_id);
    		$this->view->rs=$rs;
    	}
    }
	public function addAction()
		{
			$form=new Rsvacl_Form_FrmUserType();
			$this->view->form=$form;
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$db=new Rsvacl_Model_DbTable_DbUserType();
			if($this->getRequest()->isPost())
			{
				$db=new Rsvacl_Model_DbTable_DbUserType();	
				$post=$this->getRequest()->getPost();
				if(!$db->isUserTypeExist($post['user_type'])){
					$id=$db->insertUserType($post);
// 					$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 					Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
// 					Application_Form_FrmMessage::redirector('/rsvAcl/user-type/index');
					$this->_redirect('/rsvacl/usertype/index');
				}else {
					Application_Form_FrmMessage::message('User type had existed already');
				}
			}
			$this->view->acl_parent = $db->getAclParent();
		}
    public function editAction()
    {	
    	$user_type_id=$this->getRequest()->getParam('id');
    	if(!$user_type_id)$user_type_id=0;

   		$form = new Rsvacl_Form_FrmUserType();

    	$db = new Rsvacl_Model_DbTable_DbUserType();
        $rs = $db->getUserTypeInfo('SELECT * FROM tb_acl_user_type where user_type_id='.$user_type_id);
		Application_Model_Decorator::setForm($form, $rs);
		
		$this->view->acl_user_type = $db->getAllAclUserByUserType($user_type_id);

    	$this->view->form = $form;
    	$this->view->user_id = $user_type_id;
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			if($rs[0]['user_type']==$post['user_type']){
				Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
				$db->updateUserType($post,$rs[0]['user_type_id']);
				Application_Form_FrmMessage::redirector('/rsvacl/usertype/index');
			}else{
				if(!$db->isUserTypeExist($post['user_type'])){
					$db->updateUserType($post,$rs[0]['user_type_id']);
					Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
					Application_Form_FrmMessage::redirector('/rsvacl/usertype/index');
				}else {
					Application_Form_FrmMessage::message('User had existed already');
				}
			}
		}
		$this->view->acl_parent = $db->getAclParent();
    }
	
	public function deleteAction()
    {	
    	$user_type_id=$this->getRequest()->getParam('id');
    	$db = new Rsvacl_Model_DbTable_DbUserType();
        $db->deleteUserType($user_type_id);
		Application_Form_FrmMessage::redirector('/rsvacl/usertype');
    }
	
	
	public function copyAction()
    {	
		
		$id=$this->getRequest()->getParam('id');
		$user_type_id=explode(',',$id);
    	if(!$user_type_id)$user_type_id=0;

   		$form = new Rsvacl_Form_FrmUserType();

    	$db = new Rsvacl_Model_DbTable_DbUserType();
        $rs = $db->getUserTypeInfo('SELECT * FROM tb_acl_user_type where user_type_id='.$user_type_id[0]);
		Application_Model_Decorator::setForm($form, $rs);
		
		$this->view->acl_user_type = $db->getAllAclUserByUserType($user_type_id[0]);

    	$this->view->form = $form;
    	$this->view->user_id = $user_type_id[0];
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	if($this->getRequest()->isPost())
		{
			$db=new Rsvacl_Model_DbTable_DbUserType();	
				$post=$this->getRequest()->getPost();
				if(!$db->isUserTypeExist($post['user_type'])){
					$id=$db->insertUserType($post);
// 					$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 					Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
// 					Application_Form_FrmMessage::redirector('/rsvAcl/user-type/index');
					$this->_redirect('/rsvacl/usertype/index');
				}else {
					Application_Form_FrmMessage::message('User type had existed already');
				}
		}
		$this->view->acl_parent = $db->getAclParent();
    }
	
	
}