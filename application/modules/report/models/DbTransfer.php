<?php 
Class report_Model_DbTransfer extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	function getRequestTransfer($data){
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date = date("Y-m-d",strtotime($data["end_date"]));
		$db = $this->getAdapter();
		$sql = "SELECT 
				  rt.id,
				  rt.`tran_no`,
				  rt.`date`,
				  rt.`date_tran`,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`cur_location` LIMIT 1) AS re_tran ,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`tran_location` LIMIT 1) AS to_tran ,
				  rt.`remark`,
				  rt.`is_approved`,
				  rt.`approved_by`,
				  rt.status,
				  rt.is_transfer,
				  rt.appr_pedding as ap_pedding,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = rt.`appr_status` AND v.type=7 LIMIT 1) AS appr_status,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = rt.`appr_pedding` AND v.type=13 LIMIT 1) AS appr_pedding,
				  (SELECT u.`fullname` FROM  `tb_acl_user` AS u WHERE u.`user_id`=rt.`user_id`) AS `user`
				FROM
				  `tb_request_transfer` AS rt WHERE rt.`date_tran` BETWEEN '".$start_date."' AND '".$end_date."'";
		$where = '';
	  	if($data["avd_search"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['avd_search']));
	  		$s_where[]= " rt.tran_no LIKE '%{$s_search}%'";
	  		$s_where[]= " rt.date LIKE '%{$s_search}%'";
	  		$s_where[]= " rt.remark LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	if($data["branch"]!=-1){
	  		$where.=' AND rt.`cur_location`='.$data["branch"];
	  	}
		return $db->fetchAll($sql.$where);
	}
	function getRequestTransferCheck($data){
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date = date("Y-m-d",strtotime($data["end_date"]));
		$db = $this->getAdapter();
		$sql = "SELECT 
				  rt.id,
				  rt.`tran_no`,
				  rt.`date`,
				  rt.`date_tran`,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`cur_location` LIMIT 1) AS re_tran ,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`tran_location` LIMIT 1) AS to_tran ,
				  rt.`remark`,
				  rt.`is_approved`,
				  rt.`approved_by`,
				  rt.status,
				  rt.is_transfer,
				  rt.appr_pedding as ap_pedding,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = rt.`appr_status` AND v.type=7 LIMIT 1) AS appr_status,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = rt.`appr_pedding` AND v.type=13 LIMIT 1) AS appr_pedding,
				  (SELECT u.`fullname` FROM  `tb_acl_user` AS u WHERE u.`user_id`=rt.`user_id`) AS `user`
				FROM
				  `tb_request_transfer` AS rt WHERE rt.`date_tran` BETWEEN '".$start_date."' AND '".$end_date."'";
		$where = '';
	  	if($data["avd_search"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['avd_search']));
	  		$s_where[]= " rt.tran_no LIKE '%{$s_search}%'";
	  		$s_where[]= " rt.date LIKE '%{$s_search}%'";
	  		$s_where[]= " rt.remark LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	if($data["branch"]!=-1){
	  		$where.=' AND rt.`cur_location`='.$data["branch"];
	  	}
		if($data["check_stat"]!=-1){
			if($data["check_stat"]==1){
				$where.=' AND rt.`appr_status`= 0 AND rt.appr_pedding=1';
			}elseif($data["check_stat"]==3){
				$where.=' AND rt.appr_pedding>1';
			}elseif($data["check_stat"]==2){
				$where.=' AND rt.`appr_status`= 2';
			}
	  	}
		return $db->fetchAll($sql.$where);
	}
	
	function getTransfer($data){
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date = date("Y-m-d",strtotime($data["end_date"]));
		
		$db = $this->getAdapter();
		$sql = "SELECT 
				  pt.id,
				  pt.`tran_no`,
				  pt.`re_id`,
				  (SELECT rt.`tran_no` FROM `tb_request_transfer` AS rt WHERE rt.id=pt.`re_id`) AS re_no,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=pt.`cur_location`) AS tran_from,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=pt.`tran_location`) AS tran_to,
				  pt.`date`,
				  pt.`re_date`,
				  pt.`remark`,
				  (SELECT a.`fullname` FROM `tb_acl_user` AS a WHERE a.`user_id`=pt.`user_mod`) AS user_name,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = (SELECT rt.`appr_pedding` FROM `tb_request_transfer` AS rt WHERE rt.id=pt.`re_id`) AND v.type=13 LIMIT 1) AS pedding_status
				FROM
				  `tb_product_transfer` AS pt 
				WHERE pt.`date` BETWEEN '".$start_date."' AND '".$end_date."'";
		$where = '';
	  	if($data["avd_search"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['avd_search']));
	  		$s_where[]= " pt.tran_no LIKE '%{$s_search}%'";
	  		$s_where[]= " pt.date LIKE '%{$s_search}%'";
	  		$s_where[]= " pt.remark LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	if($data["branch"]!=-1){
	  		$where.=' AND pt.`cur_location`='.$data["branch"];
	  	}
		return $db->fetchAll($sql.$where);
	}
	function getReceiveTransfer($data){
		$tran_date = $data["tran_date"];
		$db = $this->getAdapter();
		$sql = "SELECT 
				  pt.id,
				  pt.`receive_no`,
				  pt.`tran_id`,
				  (SELECT p.`tran_no` FROM `tb_product_transfer` AS p WHERE p.id=pt.`tran_id`) AS tran_no,
				  (SELECT rt.`tran_no` FROM `tb_request_transfer` AS rt WHERE rt.id=(SELECT p.re_id FROM `tb_product_transfer` AS p WHERE p.id=pt.`tran_id`)) AS re_no,
  				  (SELECT rt.`date_tran` FROM `tb_request_transfer` AS rt WHERE rt.id=(SELECT p.re_id FROM `tb_product_transfer` AS p WHERE p.id=pt.`tran_id`)) AS re_date,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=pt.`cu_loc`) AS tran_to,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=pt.`tran_loc`) AS tran_from,
				  pt.`date`,
				  pt.`date_tran`,
				  pt.`remark`,
				  (SELECT a.`fullname` FROM `tb_acl_user` AS a WHERE a.`user_id`=pt.`user_id`) AS user_name,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code = (SELECT rt.`appr_pedding` FROM `tb_request_transfer` AS rt WHERE rt.id=(SELECT p.re_id FROM `tb_product_transfer` AS p WHERE p.id=pt.`tran_id`)) AND v.type=13 LIMIT 1) AS pedding_status
				FROM
				  `tb_recieve_transfer` AS pt 
				WHERE 1";
		$where = '';
		return $db->fetchAll($sql.$where);
	}
}
?>