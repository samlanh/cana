<?php

class Rsvacl_UserAccessController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/rmsacl/useraccess';
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());  
    }
    public function indexAction()
    {
    // action body
    	try {
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$getUser = new Rsvacl_Model_DbTable_DbUserType();
			$userQuery = "SELECT u.user_type_id,u.user_type,(SELECT u1.user_type FROM `tb_acl_user_type` u1 
						 WHERE u1.user_type_id = u.parent_id LIMIT 1) parent_id FROM `tb_acl_user_type` u ORDER BY user_type_id DESC";
			$order=" ORDER BY user_type_id DESC";
			 
			$rows = $getUser->getUserTypeInfo($userQuery);
			if($rows){
				$link = array("rsvacl","useraccess","add");
				$links = array('user_type'=>$link);
				$list=new Application_Form_Frmlist();
				$columns=array($tr->translate('USER_TYPE_NAME'), $tr->translate('TYPE_OF_CAP'));
				$this->view->list=$list->getCheckList(0, $columns, $rows, $links);
			}else $this->view->list = $tr->translate('NO_RECORD_FOUND');
			
    	} catch (Exception $e) {
    		$result = Rsvacl_Model_DbTable_DbUserAccess::getResultWarning();
    	}
    }
    function addAction(){
    	$id = $this->getRequest()->getParam('id');
    	if(empty($id)){
    		$this->_redirect('/rsvacl/useraccess');
    	}
    	if($id){
    		$db = new Rsvacl_Model_DbTable_DbUserAccess();
    		$gc = new Application_Model_GlobalClass();
    		 
    		$where = " ";
    		$status = null;
    		if($this->getRequest()->isPost()){
    			$post = $this->getRequest()->getPost();
    		}else{
    			$post =array(
    					'fmod'=>'',
    					'fcon'=>'',
    					'fact'=>'',
    					'fstatus'=>'',
    			);
    		}
    		$this->view->data = $post;
    		 
    		$db_acl=new Application_Model_DbTable_DbGlobal();
    		$sqlNotParentId ="SELECT user_type_id,parent_id,user_type FROM `tb_acl_user_type` WHERE user_type_id =".$id;
    		$rsuser_type = $db_acl->getGlobalDbRow($sqlNotParentId);
    		$this->view-> rs_usertype = $rsuser_type['user_type'];
    		$usernotparentid = $rsuser_type['parent_id'];
    		 
    		if($id == 1){ 
    			$sql = "select acl.acl_id,acl.lable as label,CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access , acl.status, acl.module, acl.is_menu
    			from tb_acl_acl as acl
    			WHERE 1 " . $where;
    		}
    		else {
    			$sql="SELECT acl.acl_id,acl.lable as label, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status, acl.module, acl.is_menu
    			FROM tb_acl_user_access AS ua
    			INNER JOIN tb_acl_user_type AS ut ON (ua.user_type_id = ut.parent_id)
    			INNER JOIN tb_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE ut.user_type_id =".$id . $where;
    		}
    		$order = " order by acl.menuordering ASC, acl.rank ASC,acl.controller ASC ,acl.is_menu DESC ";//
    		$acl=$db_acl->getGlobalDb($sql.$order);
    		$acl = (is_null($acl))? array(): $acl;
    		 
    		if($usernotparentid>0){
    			$sql_acl = "SELECT acl.acl_id,acl.lable as label, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status, acl.status , acl.is_menu
    			FROM tb_acl_user_access AS ua
    			INNER JOIN tb_acl_user_type AS ut ON (ua.user_type_id = ut.user_type_id)
    			INNER JOIN tb_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE ua.user_type_id =".$id . $where;
    		}else{
    			$sql_acl = "SELECT acl.acl_id,acl.lable as label, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status, acl.status , acl.is_menu
    			FROM tb_acl_user_access AS ua
    			INNER JOIN tb_acl_user_type AS ut ON (ua.user_type_id = ut.parent_id)
    			INNER JOIN tb_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE ua.user_type_id =".$id . $where;
    		}
    		 
    		$acl_name = $db_acl->getGlobalDb($sql_acl.$order);
    		$acl_name = (is_null($acl_name))? array(): $acl_name;
    		 
    		$imgnone='<img src="'.BASE_URL.'/images/icon/none.png"/>';
    		$imgtick='<img src="'.BASE_URL.'/images/icon/tick.png"/>';
    		 
    		$rows= array();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		foreach($acl as $com){
    			$img='<img src="'.BASE_URL.'/images/icon/none.png" id="img_'.$com['acl_id'].'" onclick="changeStatus('.$com['acl_id'].','.$id.');" class="pointer"/>';
    			$tmp_status = 0;
    			foreach($acl_name as $read){
    				if($read['acl_id']==$com['acl_id']){
    					$img='<img src="'.BASE_URL.'/images/icon/tick.png" id="img_'.$com['acl_id'].'" onclick="changeStatus('.$com['acl_id'].', '.$id.');" class="pointer"/>';
    					$tmp_status = 1;
    					break;
    				}
    			}
    			if(!empty($status) || $status === 0){
    				if($tmp_status !== $status) continue;
    			}
    			$rows[] = array("acl_id"=>$com['acl_id'],"label"=>$tr->translate($com['label']), "url"=>$com['user_access'], "img"=>$img,"module"=>$com['module'] , "is_menu"=>$com['is_menu']) ;//
    		}
    		$this->view->rows = $rows;
    		$list = new Application_Form_Frmlist();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$columns=array("label",$tr->translate('URL'), $tr->translate('STATUS'));
    		$this->view->list = $list->getCheckList('radio', $columns, $rows);
   		}
	}
	public function editAction()
    {	
    	$this->_redirect('rsvacl/useraccess/index');
    }
    public function updateStatusAction(){
    	if($this->getRequest()->isPost()){
    		$post=$this->getRequest()->getPost();
    		$db = new Rsvacl_Model_DbTable_DbUserAccess();
    		$user_type_id =  $post['user_type_id'];
    		$acl_id = $post['acl_id'];
    		$status = $post['status'];
    		$data=array('acl_id'=>$acl_id, 'user_type_id'=>$user_type_id);
    		if($status== "yes"){
    			$where="user_type_id='".$user_type_id."' AND acl_id='". $acl_id . "'";
    			$db->delete($where);    		
    			echo "no";	
    		}
    		elseif($status== "no"){
    			$db->insert($data);    		
    			echo "yes";
    		}
    		//$userLog= new Application_Model_Log();
    		//$userLog->writeUserLog($acl_id);
    	}
    	exit();
    }
}