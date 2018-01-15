<?php

class Purchase_Model_DbTable_DbGloble extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_vendor";
	public function setName($name)
	{
		$this->_name=$name;
	}
	function getRequestPrint($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  p.`id`,
				  p.`re_code`,
				  p.`date_request` ,
				  p.`remark` AS p_remark,
				  p.`date_from_work_space`,
				  p.`number_request`,
				  p.appr_reject_count,
				  p.check_reject_count,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id = p.`plan_id` LIMIT 1) AS plan,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=p.`branch_id` LIMIT 1) AS branch,
				  (SELECT `fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=p.`user_id` LIMIT 1) AS `user`,
				  (SELECT name_en FROM `tb_view` AS v  WHERE v.key_code=p.`appr_status` AND v.type=12) AS `status`,
				  (SELECT name_en FROM `tb_view` AS v  WHERE v.key_code=p.`pedding` AND v.type=11) AS `pedding`,
				  (SELECT pr.item_name FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id` LIMIT 1) AS item_name,
				  (SELECT pr.item_code FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id` LIMIT 1) AS item_code,
				  (SELECT c.`name` FROM `tb_category` AS c WHERE c.id=(SELECT pr.cate_id FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id`) LIMIT 1) AS type,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id` AND v.type=3) LIMIT 1) AS size,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id` AND v.type=2) LIMIT 1) AS model,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id` AND v.type=4) LIMIT 1) AS color,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pr.measure_id FROM `tb_product` AS pr WHERE pr.id=pd.`pro_id` LIMIT 1 )) AS measure,
				  (SELECT pr.`re_code` FROM `tb_purchase_request` AS pr WHERE pr.id=p.`id`LIMIT 1) AS mr_no,
				  (SELECT pr.`date_request` FROM `tb_purchase_request` AS pr WHERE pr.id=p.`id`LIMIT 1) AS mr_date,
				  pd.`qty`,
				  pd.`date_in`,
				  pd.`remark`
				FROM
				  `tb_purchase_request` AS p,
				  `tb_purchase_request_detail` AS pd 
				WHERE p.id = pd.`pur_id` AND p.id=$id";
		
		return $db->fetchAll($sql);
	}
	function getvendorById($id){
		$sql = "SELECT * FROM tb_vendor WHERE vendor_id=".$id;
		$db = $this->getAdapter();
		return $db->fetchRow($sql);
	}
	final public function addVendor($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$db->beginTransaction();
		try{
			if($post["is_over_sea"]==1){
				$data=array(
						'v_name'		=> $post['txt_name'],
// 						'v_phone'		=> $post['txt_phone'],
// 						'contact_name'	=> $post['txt_contact_name'],
// 						'phone_person'	=> $post['contact_phone'],
// 						'add_name'		=> $post['txt_address'],
// 						'email'			=> $post['txt_mail'],
// 						'website'		=> $post['txt_website'],
// 						'fax'			=> $post['txt_fax'],
// 						'note'	=> $post['remark'],
						'is_over_sea'	=>	$post["is_over_sea"],
						'last_usermod'	=> $GetUserId,
						'last_mod_date' => new Zend_Date(),
						'date'			=>	date("Y-m-d"),
				);
			}else {
				$data=array(
						'v_name'		=> $post['txt_name'],
						'v_phone'		=> $post['txt_phone'],
						'contact_name'	=> $post['txt_contact_name'],
						'phone_person'	=> $post['contact_phone'],
						'add_name'		=> $post['txt_address'],
						'email'			=> $post['txt_mail'],
						'website'		=> $post['txt_website'],
						'fax'			=> $post['txt_fax'],
						'note'			=> $post['remark'],
						'is_over_sea'	=>	0,
						'last_usermod'	=> $GetUserId,
						'last_mod_date' => new Zend_Date(),
						'date'			=>	date("Y-m-d"),
				);
			}
			if(!empty($post['id'])){
				$where = "vendor_id = ".$post["id"];
				$this->update($data, $where);
			}else{
				$db->insert("tb_vendor", $data);
			}
			return $db->commit();
		}
		catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	final public function addnewvendor($post){//ajax
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		try{
		$data=array(
				"v_name"	   => $post["v_name"],
				"v_phone"=> $post["com_phone"],
				"contact_name" => $post["contact"],
				"phone_person"=> $post["phone"],
				"add_name"	   => $post["address"],
				"email"		   => $post["txt_mail"],
				"last_usermod" => $GetUserId,
				"last_mod_date"=>new Zend_Date()
		);
		return $this->insert($data);
		}catch(Exception $e){
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
		
	}

	
	
}