<?php

class Purchase_Model_DbTable_DbMakePurchase extends Zend_Db_Table_Abstract
{
	protected $_name = 'tb_su_price_idcompare';
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getSu($id){
		$db = $this->getAdapter();
		$sql="SELECT s.*  FROM `tb_su_price_idcompare` AS s WHERE s.`re_id`=1";
		return $db->fetchAll($sql);
	}
	function getPrice($id,$su_id,$pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.* FROM `tb_pro_compare` AS p WHERE p.`re_id`=$id AND p.`su_id`=$su_id AND p.pro_id=$pro_id";
		//echo $sql;
		return $db->fetchRow($sql);
	}
	function getVendor(){
		$db = $this->getAdapter();
		$sql = "SELECT v.`vendor_id`,v.`v_name`  FROM `tb_vendor` AS v WHERE v.`v_name`!=''";
		return $db->fetchAll($sql);
	}
	function getProductRequestDetail($id){
		$db= $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id = p.`pro_id` LIMIT 1) AS item_name,
				  p.`qty`,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_purchase_request_detail` AS p 
				WHERE p.`pur_id` =$id";
		return $db->fetchAll($sql);
	}
	
	function getSuCompare($id){
		$db = $this->getAdapter();
		$sql = "SELECT s.*,(SELECT v.v_name FROM `tb_vendor`AS v WHERE v.`vendor_id`=s.`su_id`) AS v_name,(SELECT v.`phone_person` FROM `tb_vendor`AS v WHERE v.`vendor_id`=s.`su_id`) AS v_phone FROM `tb_su_price_idcompare`AS s WHERE s.`re_id`=$id";
		return $db->fetchAll($sql);
	}
	function getProduct($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.id,
				  p.`item_name`,
				  pc.`qty`,
				  pc.`pro_brand`,
				  pc.`pro_from`,
				  (SELECT s.code FROM `tb_su_price_idcompare` AS s WHERE s.re_id=pc.`re_id` LIMIT 1) AS c_code,
				  (SELECT pr.re_code FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) AS re_code ,
				  (SELECT pr.`date_request` FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id`LIMIT 1) AS date_request ,
				  (SELECT pr.`number_request` FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) AS number_request ,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id = (SELECT pr.plan_id FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) LIMIT 1) AS plan ,
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id = (SELECT pr.branch_id FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id` LIMIT 1) LIMIT 1) AS branch ,
				   (SELECT pr.`date_from_work_space` FROM `tb_purchase_request` AS pr WHERE pr.id=pc.`re_id`) AS date_from_work_space ,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id = p.`measure_id`) AS measure 
				FROM
				  `tb_product` AS p,
				  `tb_pro_compare` AS pc 
				WHERE p.`id` = pc.`pro_id` AND pc.`re_id`= $id
				GROUP BY p.id";
		return $db->fetchAll($sql);
	}
	function getProCompare($id,$pro_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  p.`su_id`,
				  p.`price`,
				  p.`is_check`,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id=p.`pro_id`) AS item_name ,
				  p.qty,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_pro_compare` AS p 
				WHERE p.`re_id` =$id AND p.`pro_id`=$pro_id";
		return $db->fetchAll($sql);
	}
	
	function getProToPo($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  p.`su_id`,
				  SUM(p.`price`) AS price,
				  p.`is_check`,
				  pr.`branch_id`,
				  p.vat,
				  (SELECT i.item_name FROM `tb_product` AS i WHERE i.id=p.`pro_id`) AS item_name ,
				  p.qty,
				  (SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT i.measure_id FROM `tb_product` AS i WHERE i.id=p.`pro_id` LIMIT 1) LIMIT 1) AS measure
				FROM
				  `tb_pro_compare` AS p ,
				  `tb_purchase_request` AS pr
				WHERE p.`re_id` =$id AND p.`is_check`=1 AND pr.`id`=p.`re_id` GROUP BY p.`su_id`";
		return $db->fetchAll($sql);
	}
	function getProDetailToPo($id,$su_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`pro_id`,
				  p.`su_id`,
				  p.`price`,
				  p.qty,
				  p.`pro_brand`,
				  p.`pro_from`
				FROM
				  `tb_pro_compare` AS p 
				WHERE p.`re_id` =$id AND p.`is_check`=1 AND p.`su_id`=$su_id";
		return $db->fetchAll($sql);
	}
	function getAllCompare($search){
		$db = $this->getAdapter();
		$sql ="SELECT 
				  p.id,
				  p.`re_code`,
				  p.`date_request`,
				   p.`date_from_work_space`,
				  p.`number_request`,
				  s.`pur_date`,
				   s.`code`,
				  (SELECT pl.`name` FROM `tb_plan` AS pl WHERE pl.id=p.`plan_id`) AS plan,
					s.`is_approve`,
					s.`is_purchase`,
				   (SELECT pl.`name` FROM `tb_sublocation` AS pl WHERE pl.`id`=p.`branch_id` LIMIT 1) AS branch,
				  (SELECT fullname FROM `tb_acl_user` AS u WHERE u.`user_id` = p.`user_id` LIMIT 1) AS `user`,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code=s.`appr_status` AND v.type=12 LIMIT 1) AS app_status, 
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code=s.`pedding` AND v.type=11 LIMIT 1) AS pedding,
				  (SELECT name_en FROM `tb_view` AS v WHERE v.key_code=s.`status` AND v.type=5 LIMIT 1) AS  `status`,
				  (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = P.user_id LIMIT 1 ) AS user_name
				FROM
				  `tb_su_price_idcompare` AS s,
				  `tb_purchase_request` AS p 
				WHERE s.`re_id` = p.`id` AND s.is_approve=1";
		$from_date =(empty($search['start_date']))? '1': "  p.`date_request` >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': "  p.`date_request` <= '".$search['end_date']."'";
		$where = " AND ".$from_date." AND ".$to_date;
		$groupby = " GROUP BY s.`re_id`";
				
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.re_code LIKE '%{$s_search}%'";
			$s_where[] = " p.number_request LIKE '%{$s_search}%'";
			$s_where[] = " p.status LIKE '%{$s_search}%'";
			$s_where[] = " s.code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		// if($search['po_pedding']>0){
			// $where .= " AND p.pedding =".$search['po_pedding'];
		// }
		if($search['suppliyer_id']>0){
			$where .= " AND s.su_id =".$search['suppliyer_id'];
		}
		if($search['branch']>0){
			$where .= " AND p.branch_id =".$search['branch'];
		}
		if($search['plan']>0){
			$where .= " AND p.`plan_id` =".$search['plan'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY s.`is_approve` ASC , id DESC ";
		
		return $db->fetchAll($sql.$where.$groupby.$order);
	}
	
	 public function getPuNumber($branch_id = 1){
		 
    	$this->_name='tb_purchase_order';
    	$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $db_globle->getPrefixCode($branch_id)."P0-";
		$pre = "P0-";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	
	function add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$GetUserId = $user['user_id'];
		try{
				$pro = $this->getProToPo($data["id"]);
				if(!empty($pro)){
					$arr = array(
						'is_purchase'	=>	1,
						'appr_status'	=>	5,
						'pedding'		=>	7,
						'pur_user'		=>	$GetUserId,
						'pur_date'		=>  date('Y-m-d'),
					);
					$this->_name = "tb_su_price_idcompare";
					$where = "re_id=".$data["id"];
					$this->update($arr,$where);
					foreach($pro as $rs){
						$date= new Zend_Date();
						$order_add = $this->getPuNumber($rs["branch_id"]);
						$arr_po = array(
							'vendor_id'		=>	$rs["su_id"],
							'branch_id'		=>	$rs["branch_id"],
							're_id'			=>	$data["id"],
							'order_number'	=>	$order_add,
							'date_order'	=>	date("Y-m-d"),
							'user_mod'		=>	$GetUserId,
							'is_recieved'	=>	0,
							'is_approved'	=>	1,
							'is_purchase_order'	=>	1,
							'user_id'		=>	$GetUserId,
							'date'			=>	date("Y-m-d"),
							'status'		=>	1,
							'purchase_status'	=>	5,
							'pending_status'	=>	7,
							'vat'			=>	$rs["vat"]
						);
						$this->_name = "tb_purchase_order";
						$po_id = $this->insert($arr_po);
						$po_d = $this->getProDetailToPo($data["id"],$rs["su_id"]);
						if(!empty($po_d)){
							$total = 0;
							$t_vat =0;
							foreach($po_d as $rss){
								$total = $total + ($rss["qty"]*$rss["price"]);
								$arr_pod = array(
									'purchase_id'	=>	$po_id,
									'pro_id'		=>	$rss["pro_id"],
									'qty_order'		=>	$rss["qty"],
									'qty_after'		=>	$rss["qty"],
									'price'			=>	$rss["price"],
									//'price'			=>	$rss["price"]/$rss["qty"],
									'sub_total'		=>	$rss["qty"]*$rss["price"],
									'total_before'	=>	$rss["qty"]*$rss["price"],
									'pro_brand'		=>	$rss["pro_brand"],
									'pro_from'		=>	$rss["pro_from"],
								);
								$this->_name = "tb_purchase_order_item";
								$po_ids = $this->insert($arr_pod);
								
								$t_vat = ($total * $rs["vat"])/100;
						
								$arr_up = array(
									'all_total'		=>	$total+$t_vat,
									'net_total'		=>	$total,
									'total_after'	=>	$total+$t_vat,
									'paid'			=>	0,
									'balance'		=>	$total+$t_vat,
								);
								$this->_name = "tb_purchase_order";
								$where = "id=".$po_id;
								$this->update($arr_up,$where);
							}
						}
						
					}
					
				}
				
				$arr_re = array(
					'pedding'		=>	7,
					'appr_status'	=>	5,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["id"];
			$this->update($arr_re,$where);
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);			
		}
	}
	function edit($data){
		try{
			//print_r($data);exit();
			$db=$this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user = new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$sql ="DELETE FROM `tb_su_price_idcompare` WHERE re_id	="."'".$data["id"]."'";
			$db->query($sql);
			
			$sqls ="DELETE FROM tb_pro_compare WHERE re_id="."'".$data["id"]."'";
			$db->query($sqls);
			
			for($i=1;$i<=4;$i++){
				$arr=array(
						're_id'			=> $data["id"],
						'su_id'			=> $data["su_id_".$i],
				);
				$this->insert($arr);
			}
			//$identity = $data["identity"];
			$ids=explode(',',$data['identity']);
			foreach($ids as $i){
				for($j=1;$j<=4;$j++){
					$arr_pro=array(
							're_id'		=> 	$data["id"],
							'pro_id'		=> 	$data["pro_id_".$i],
							'su_id'			=> 	$data["su_id_".$i],
							'price'			=>	$data["su_price_".$i."_".$j],
							'qty'			=>	$data["pro_qty_".$i],
							'is_check'		=>	@$data["checkbox_".$i."_".$j]
					);
					$this->_name="tb_pro_compare";
					$this->insert($arr_pro);
				}
			}
			$db->commit();
			//return $GetProductId;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function editPO($data){
		$db = $this->getAdapter();
		$arr_up = array(
				'net_total'		=>	$data["total_amount"],
				'all_total'		=>	$data["identity"],//with vat
				'total_after'	=>	$data["grand_total"],//with vat
				'paid'			=>	0,
				'balance'		=>	$data["grand_total"],
		);
		$this->_name = "tb_purchase_order";
		$where = "id=".$data['poid'];
		$this->update($arr_up,$where);
		
		$ids=explode(',',$data['identity']);
		foreach($ids as $i){
			$arr_pod = array(
					'qty_order'		=>	$data["qty_order".$i],
					'qty_after'		=>	$data["qty_order".$i],
					'price'			=>	$data["price".$i],
					'sub_total'		=>	$data["total".$i],
					'total_before'	=>	$data["total".$i],
			);
			$this->_name = "tb_purchase_order_item";
			$where="id = ".$data["id".$i];
			$this->update($arr_pod, $where);
			
			$arr_pro=array(
					'price'			=>	$data["price".$i],
					'qty'			=>	$data["qty_order".$i],
			);
			$this->_name="tb_pro_compare";
			$where=" re_id = ".$data['re_id']." AND is_check=1 AND pro_id=".$data["pro_id".$i];	
			$this->update($arr_pro, $where);	
		}
	}

}