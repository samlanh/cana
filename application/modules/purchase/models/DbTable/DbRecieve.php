<?php

class Purchase_Model_DbTable_DbRecieve extends Zend_Db_Table_Abstract
{
	function getPURCode(){
		$db = $this->getAdapter();
		$sql="";
		//return $db-
	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getAllPuCode(){
		$db = $this->getAdapter();
		$sql="SELECT p.id,p.`order_number` FROM `tb_purchase_order` AS p";
		return $db->fetchAll($sql);
	}
	function getRecieveById($id){
		$db = $this->getAdapter();
		$sql="SELECT r.* FROM `tb_recieve_order` AS r WHERE r.`purchase_id`=$id";
		return $db->fetchRow($sql);
	}
	function getReceiveById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  * ,
				  r.is_invoice_controlling,
				  (SELECT order_number FROM `tb_purchase_order` WHERE tb_purchase_order.id=r.purchase_id) as purchase_order,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.id=ri.`pro_id` LIMIT 1) AS item_code,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=ri.`pro_id` LIMIT 1) AS item_name,
					  (SELECT p.`sign` FROM `tb_product` AS p WHERE p.id=ri.`pro_id` LIMIT 1) AS sign,
					  (SELECT p.`convertor_measure` FROM `tb_product` AS p WHERE p.id=ri.`pro_id` LIMIT 1) AS convertor_measure,
					  (SELECT m.name  FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=ri.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id = (SELECT pr.`plan_id` FROM `tb_purchase_request` AS pr WHERE pr.id=(SELECT p.`re_id` FROM `tb_purchase_order` AS p WHERE p.id=r.`purchase_id` LIMIT 1) LIMIT 1) LIMIT 1) AS plan,
				  (SELECT `re_code` FROM `tb_purchase_request` AS p WHERE p.id=(SELECT `re_id` FROM `tb_purchase_order` AS po WHERE po.id=r.`purchase_id` LIMIT 1) LIMIT 1) AS re_no,
				  (SELECT `id` FROM `tb_purchase_request` AS p WHERE p.id=(SELECT `re_id` FROM `tb_purchase_order` AS po WHERE po.id=r.`purchase_id` LIMIT 1) LIMIT 1) AS re_id,
				  (SELECT p.`date_request` FROM `tb_purchase_request` AS p WHERE p.id=(SELECT `re_id` FROM `tb_purchase_order` AS po WHERE po.id=r.`purchase_id` LIMIT 1) LIMIT 1) AS re_date
				FROM
				  `tb_recieve_order` AS r,
				  `tb_recieve_order_item` AS ri 
				WHERE r.`order_id` = ri.`recieve_id` 
				  AND r.`order_id` = $id";
		return $db->fetchAll($sql);
	}
	function getRecieve($id,$type){
		$db = $this->getAdapter();
		if($type==1){//by vendor
			$sql = "SELECT 
					  r.`order_id`,
					  r.purchase_id,
					  (SELECT order_number FROM `tb_purchase_order` WHERE tb_purchase_order.id=r.purchase_id) as purchase_order,
					  r.`recieve_number`,
					   r.`dn_number`,
					  r.`date_in`,
					  r.`all_total`,
					  r.`all_total_after`,
					  r.`net_total`,
					  r.`net_total_after`,
					  r.`paid`,
					  r.`paid_after`,
					  r.`balance_after`,
					  r.`balance` ,
					  r.`vendor_id`,
					  r.tax
					FROM
					  `tb_recieve_order` AS r 
					WHERE r.`vendor_id` = $id AND is_invoice_controlling =0  ORDER BY r.purchase_id ASC";
		}else{//by dn
			$sql = "SELECT 
					  r.`order_id`,
					  (SELECT order_number FROM `tb_purchase_order` WHERE tb_purchase_order.id=r.purchase_id) as purchase_order,
					 	r.purchase_id,
					  r.`recieve_number`,
					   r.`dn_number`,
					  r.`date_in`,
					  r.`all_total`,
					  r.`all_total_after`,
					  r.`net_total`,
					  r.`net_total_after`,
					  r.`paid`,
					  r.`paid_after`,
					  r.`balance_after`,
					  r.`balance` ,
					  r.`vendor_id`,
					  r.tax
					FROM
					  `tb_recieve_order` AS r 
					WHERE r.`order_id` = $id AND is_invoice_controlling =0";
		}
		return $db->fetchAll($sql);
	}
	function getVendorByPuId($id){
		$db = $this->getAdapter();
		$sql="SELECT 
			  v.`v_name`,
			  v.`v_phone`,
			  v.`contact_name`,
			  v.`phone_person`,
			  v.`vendor_id`,
			  v.`email`,
			  v.`is_over_sea`,
			  v.`add_name`,
			  v.`vendor_id`,
			  p.`branch_id` ,
			  DATE_FORMAT(p.`date_order`,'%m/%d/%Y') AS date_order,
			  DATE_FORMAT(p.`date_in`,'%m/%d/%Y') AS date_in,
			  p.`payment_method`,
			  p.`currency_id`,
			  p.`discount_value`,
			  p.`discount_real`,
			  p.`all_total`,
			  p.`tax`,
			  p.`net_total`,
			  p.`paid`,
			  p.`balance`,
			  (SELECT pl.`name` FROM `tb_sublocation` AS pl WHERE pl.id=p.`branch_id`) AS location
			FROM
			  `tb_vendor` AS v,
			  `tb_purchase_order` AS p 
			WHERE p.`vendor_id` = v.`vendor_id` 
			  AND p.`id` = $id";
		return $db->fetchRow($sql);
	}
	function getProductReceiveById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  ro.order_id AS id,
				  ro.recieve_number,
				  ro.`invoice_no`,
				  ro.`invoice_date`,
				  ro.`payment_number`,
				  ro.`receive_invoice_date`,
				  (SELECT v.`v_name` FROM `tb_vendor` AS v WHERE v.`vendor_id`=ro.`vendor_id` LIMIT 1) AS v_name,
				  (SELECT v.`v_phone` FROM `tb_vendor` AS v WHERE v.`vendor_id`=ro.`vendor_id` LIMIT 1) AS v_phone,
				  (SELECT v.`add_name` FROM `tb_vendor` AS v WHERE v.`vendor_id`=ro.`vendor_id` LIMIT 1) AS add_name,
				  (SELECT v.`vat` FROM `tb_vendor` AS v WHERE v.`vendor_id`=ro.`vendor_id` LIMIT 1) AS vat,
				  (SELECT p.`order_number` FROM `tb_purchase_order` AS p WHERE p.id=ro.`purchase_id` LIMIT 1) AS po_no,
				  (SELECT pl.name FROM `tb_plan` AS pl WHERE pl.id=(SELECT p.`plan_id` FROM `tb_purchase_request` AS p WHERE p.`id`=(SELECT p.`re_id` FROM `tb_purchase_order` AS p WHERE p.id=ro.`purchase_id` LIMIT 1))) AS plan,
				  (SELECT p.`re_code` FROM `tb_purchase_request` AS p WHERE p.`id`=(SELECT p.`re_id` FROM `tb_purchase_order` AS p WHERE p.id=ro.`purchase_id` LIMIT 1)) AS mr_no,
				  (SELECT p.`date_request` FROM `tb_purchase_request` AS p WHERE p.`id`=(SELECT p.`re_id` FROM `tb_purchase_order` AS p WHERE p.id=ro.`purchase_id` LIMIT 1)) AS mr_date,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.`id`=r.`pro_id` LIMIT 1) AS item_name,
				  (SELECT p.item_code FROM `tb_product` AS p WHERE p.`id`=r.`pro_id` LIMIT 1) AS item_code,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id =(SELECT p.measure_id FROM `tb_product` AS p WHERE p.`id`=(SELECT r.`pro_id` FROM `tb_recieve_order_item` AS r WHERE r.`recieve_id`=ro.`order_id` LIMIT 1))) AS measur,
				  (SELECT c.`name` FROM `tb_category` AS c WHERE c.id=(SELECT pr.cate_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_recieve_order_item` AS r WHERE r.`recieve_id`=ro.`order_id` LIMIT 1)) LIMIT 1) AS TYPE,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_recieve_order_item` AS r WHERE r.`recieve_id`=ro.`order_id` LIMIT 1) AND v.type=3) LIMIT 1) AS size,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_recieve_order_item` AS r WHERE r.`recieve_id`=ro.`order_id` LIMIT 1) AND v.type=2) LIMIT 1) AS model,
				  (SELECT v.name_en  FROM tb_view AS v WHERE v.key_code=(SELECT pr.size_id FROM `tb_product` AS pr WHERE pr.id=(SELECT r.`pro_id` FROM `tb_recieve_order_item` AS r WHERE r.`recieve_id`=ro.`order_id` LIMIT 1) AND v.type=4) LIMIT 1) AS color,
				  r.`qty_order` AS qty,
				  r.`qty_receive`,
				  r.`price`,
				   r.`remark` AS note,
				  r.`sub_total`,
				  ro.date_order,
				  date_in,
					ro.`remark`,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=ro.`LocationId`) AS branch,
				  (SELECT v.v_name FROM tb_vendor AS v WHERE v.vendor_id = ro.vendor_id) AS vendor_name,
				  ro.all_total,
				  (SELECT  u.username FROM tb_acl_user AS u WHERE u.user_id = ro.user_mod) AS user_name ,
				  (SELECT p.name FROM tb_plan AS p WHERE p.id=(SELECT pr.plan_id FROM `tb_purchase_order` AS p ,tb_purchase_request AS pr
				WHERE pr.id=p.re_id AND p.id=ro.`purchase_id` LIMIT 1)) AS plan
				FROM
				  tb_recieve_order AS ro ,`tb_recieve_order_item` AS r
				WHERE ro.order_id=$id AND ro.`order_id`=r.`recieve_id`";
		
		return $db->fetchAll($sql);
	}
	function getItemByPuId($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  p.`pro_id`,
				  p.`qty_order`,
				  p.`qty_after`,
				  pd.`item_code`,
				  pd.`item_name`,
				  p.`price`,
				  p.`sub_total`,
				  p.`disc_value` ,
				  pd.is_convertor,
				  pd.convertor_measure,
				  pd.sign,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=pd.`measure_id` LIMIT 1) AS measure
				FROM
				  `tb_purchase_order_item` AS p,
				  `tb_product` AS pd 
				WHERE p.`pro_id` = pd.`id` 
				  AND p.`purchase_id` = $id ";
		return $db->fetchAll($sql);
	}
	
	function getRecieveCode($location){
		$db = $this->getAdapter();
		$user = $this->GetuserInfo();
		//$location = $user['branch_id'];
		
		$sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
		$prefix = $db->fetchOne($sql_pre);
		
		$sql="SELECT r.`order_id` FROM `tb_recieve_order` AS r WHERE r.`LocationId`=$location ORDER BY r.`order_id` DESC LIMIT 1";
		$num = $db->fetchOne($sql);
		
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = "GN";
		for($i=$num_lentgh;$i<4;$i++){
			$pre.="0";
		}
		return $pre.$num;
	}
	
	function getPayCode($location){
	    $db = $this->getAdapter();
	    $user = $this->GetuserInfo();
	    //$location = $user['branch_id'];
	
	    $sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
	    $prefix = $db->fetchOne($sql_pre);
	
	    $sql="SELECT r.`order_id` FROM `tb_recieve_order` AS r WHERE r.`LocationId`=$location ORDER BY r.`order_id` DESC LIMIT 1";
	    $num = $db->fetchOne($sql);
	
	    $num_lentgh = strlen((int)$num+1);
	    $num = (int)$num+1;
	    $pre = "POL-";
	    for($i=$num_lentgh;$i<4;$i++){
	        $pre.="0";
	    }
	    return $pre.$num;
	}
	
	function closeReceive($id,$re_id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		try{
			$arr_update = array('is_completed' =>	1,);
			$this->_name="tb_purchase_order";
			$where = "id=".$id;
			$this->update($arr_update,$where);
			
			
			$arr = array(
				'is_purchase'	=>	1,
				'appr_status'	=>	3,
				'pedding'		=>	8,
				'pur_user'		=>	$GetUserId,
				'pur_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_su_price_idcompare";
			$where = "re_id=".$re_id;
			$this->update($arr,$where);
					
			$arr = array(
				'status'	=>	5,
				'is_recieved'	=>	1,
				'purchase_status'	=>	3,
				'pending_status'	=>	8
			);
			$where = "id=".$id;
			$this->_name = "tb_purchase_order";
			$this->update($arr, $where);
			
			$arr_re = array(
					'pedding'		=>	8,
					'appr_status'	=>	3,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$re_id;
			$this->update($arr_re,$where);
			
			$db->commit();
			
			return 1;
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err;
		}
	}
	function  add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		try{
			$identity = $data["identity"];
			if($identity!=""){
				$orderdata = array(
					'purchase_id'		=>	$data["pu_code"],
					'dn_number'			=>	$data["dn_no"],
					'invoice_no'		=>	$data["invoice_no"],
					"vendor_id"      	=> 	$data['vendor'],
					"LocationId"     	=> 	$data["branch"],
					"recieve_number" 	=> 	$data["recieve_no"],
					"date_order"     	=> 	date("Y-m-d",strtotime($data['date_order'])),
					"date_in"     		=> 	date("Y-m-d",strtotime($data['date_recieve'])),
					'dn_date'			=>	date("Y-m-d",strtotime($data['dn_date'])),
					"purchase_status"   => 	1,
					"payment_method" 	=> 	$data['payment_name'],
					"currency_id"    	=> 	1,
					"remark"         	=> 	$data['remark'],
					"all_total"      	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"all_total_after"   => 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"tax"				=>	$data["vat"],
					"discount_value" 	=> 	$data['dis_value'],
					"discount_real"  	=> 	$data['global_disc'],
					"net_total"      	=> 	$data['totalAmoun_after'],
					"net_total_after"   => 	$data['totalAmoun_after'],
					"paid"           	=> 	0,
					"balance"        	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"balance_after"		=>	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"user_mod"       	=> 	$GetUserId,
					"date"      		=> 	date("Y-m-d",strtotime($data["date_recieve"])),
				    "invoice_date"      => 	date("Y-m-d",strtotime($data["invoice_date"])),
				    "receive_invoice_date" => date("Y-m-d",strtotime($data["invoice_recieve_date"])),
				);
				
				$this->_name='tb_recieve_order';
				
				$recieved_order = $this->insert($orderdata);
				
				
				$orderdata_his = array(
					'receive_id'		=>	$recieved_order,
					'purchase_id'		=>	$data["pu_code"],
					'dn_number'			=>	$data["dn_no"],
					'invoice_no'		=>	$data["invoice_no"],
					"vendor_id"      	=> 	$data['vendor'],
					"LocationId"     	=> 	$data["branch"],
					"recieve_number" 	=> 	$data["recieve_no"],
					"date_order"     	=> 	date("Y-m-d",strtotime($data['date_order'])),
					"date_in"     		=> 	date("Y-m-d",strtotime($data['date_recieve'])),
					'dn_date'			=>	date("Y-m-d",strtotime($data['dn_date'])),
					"purchase_status"   => 	1,
					"payment_method" 	=> 	$data['payment_name'],
					"currency_id"    	=> 	$data['currency'],
					"remark"         	=> 	$data['remark'],
					"all_total"      	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"all_total_after"   => 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"tax"				=>	$data["vat"],
					"discount_value" 	=> 	$data['dis_value'],
					"discount_real"  	=> 	$data['global_disc'],
					"net_total"      	=> 	$data['totalAmoun_after'],
					"net_total_after"   => 	$data['totalAmoun_after'],
					"paid"           	=> 	0,
					"balance"        	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"balance_after"		=>	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"user_mod"       	=> 	$GetUserId,
					"date"      		=> 		date("Y-m-d",strtotime($data["date_recieve"])),
				    //"payment_number"    =>$data["payment_number"],
				    "invoice_date"      => 		date("Y-m-d",strtotime($data["invoice_date"])),
				    "receive_invoice_date" => 		date("Y-m-d",strtotime($data["invoice_recieve_date"])),
					'type'				=>	1
				);
				
				$this->_name='tb_recieve_order_history';
				$recieved_order_his = $this->insert($orderdata_his);
				
				if(@$data["is_invoice"]==1){
					$datainvoice = array(
						'purchase_id'		=>	$data["pu_code"],
						'invoice_no'		=>	$data["invoice_no"],
						"vendor_id"      	=> 	$data['vendor'],
						"branch_id"     	=> 	$data["branch"],
						"dn_id" 			=> 	$recieved_order,
						"invoice_date"     	=> 	date("Y-m-d",strtotime($data['date_order'])),
						"total"				=>	$data['totalAmoun_after'],
						"total_vat"			=>	(($data['totalAmoun_after']*$data["vat"])/100),
						'vat'				=>	$data["vat"],
						"sub_total"     	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"sub_total_after"   => 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"discount" 			=> 	$data['global_disc'],
						"paid"    			=> 	0,
						"paid_after"        => 	0,
						"balance"      		=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"balance_after"   	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"is_fullpay"		=>	0,
						"user_id"       	=> 	$GetUserId,
						//"date"      		=> 		date("Y-m-d",strtotime($data["date_recieve"])),
						//"payment_number"    =>$data["payment_number"],
						"invoice_date"      => 	date("Y-m-d",strtotime($data["invoice_date"])),
						"invoice_date_from_stock" => 	date("Y-m-d",strtotime($data["invoice_recieve_date"])),
					);
					
					$this->_name='tb_purchase_invoice';
					$invoice_id = $this->insert($datainvoice);
					
					$arr_re_update = array(
						'is_invoice_controlling'	=> 1,
					);
					$this->_name='tb_recieve_order';
					$where = "order_id=".$recieved_order;
					$this->update($arr_re_update,$where);
					
				}
				
				$ids=explode(',',$data['identity']);
				$locationid=$data['branch'];
				foreach ($ids as $i){
					$row_product = $this->getProductForCost($data['item_id_'.$i],$locationid);
					if(!empty($row_product)){
						$amount_pro = $row_product["price"] * $row_product["qty"];
						$new_amount_pro = $data['qty_recieve_'.$i] * $data['price_'.$i];
						$total_qty = $row_product["qty"]+$data['qty_recieve_'.$i];
						$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_product";
						$where=" id = ".$row_product['id'];
						
						$this->update($arr_pro, $where);
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_prolocation";
						$where=" pro_id = ".$row_product['id']." AND location_id=".$locationid;
						$this->update($arr_pro, $where);
					}
					$recieved_item = array(
							'recieve_id'	  	=> 	$recieved_order,
							'pro_id'	  		=> 	$data['item_id_'.$i],
							'origin_qty_order'	=> 	$data['origin_qty_order_'.$i],
							'qty_order'	  		=> 	$data['qty_order_'.$i],
							'qty_receive'	  	=> 	$data['qty_recieve_'.$i],
							//'qty_detail'  		=> 	$data['qty_per_unit_'.$i],
							'price'		  		=> 	$data['price_'.$i],
							'disc_value'	  	=> $data['dis_value'.$i],
							'sub_total'	  		=> $data['total_'.$i],
							'sub_total_after'	=> $data['total_after_'.$i],
							'remark'			=>	$data["remark_".$i],
							'plat_no'			=>	$data["plat_no"],
							'su_scale_refer'	=>	$data["su_scale_refer"],
							'su_scale_qty'		=>	$data["supplier_scale"],
							'com_scale_refer'	=>	$data["com_scale_refer"],
							'com_scale_qty'		=>	$data["company_scale"],
							'su_scale_in_m'		=>	$data["supplier_scale_total"],
							'com_scale_in_m'	=>	$data["company_scale_total"],
							'is_con_choose'		=>	@$data["choose"],
							'is_convertor'		=>	$data["is_convertor_".$i],
					);
					$this->_name="tb_recieve_order_item";
					
					$this->insert($recieved_item);
					
					$recieved_item_his = array(
							'his_id'	  	=> 	$recieved_order_his,
							'recieve_id'	  	=> $recieved_order,
							'pro_id'	  		=> $data['item_id_'.$i],
							'origin_qty_order'	=> $data['origin_qty_order_'.$i],
							'qty_order'	  		=> $data['qty_order_'.$i],
							'qty_receive'	  	=> $data['qty_recieve_'.$i],
							//'qty_detail'  	=> 	$data['qty_per_unit_'.$i],
							'price'		  		=> $data['price_'.$i],
							'disc_value'	  	=> $data['dis_value'.$i],
							'sub_total'	  		=> $data['total_'.$i],
							'sub_total_after'	=> $data['total_after_'.$i],
							'remark'			=> $data["remark_".$i],
							'plat_no'			=> $data["plat_no"],
							'su_scale_refer'	=> $data["su_scale_refer"],
							'su_scale_qty'		=> $data["supplier_scale"],
							'com_scale_refer'	=> $data["com_scale_refer"],
							'com_scale_qty'		=> $data["company_scale"],
							'su_scale_in_m'		=> $data["supplier_scale_total"],
							'com_scale_in_m'	=> $data["company_scale_total"],
							'is_con_choose'		=> @$data["choose"],
							'is_convertor'		=> $data["is_convertor_".$i],
					);
					$this->_name="tb_recieve_order_item_history";
					
					$this->insert($recieved_item_his);
					
					
					// Update Purchase Order To Completed
					$qty_after = $data['qty_order_'.$i]-$data['qty_recieve_'.$i];
					if($qty_after==0){
						$is_completed =1; 
					}else{
						$is_completed =0; 
					}
					
					// Update Purchase Order Item Qty
					
					$po_update = array(
							'qty_after'		=> 	$qty_after,
							'is_completed'	=>	$is_completed,
						);
					$this->_name="tb_purchase_order_item";
					$where = "purchase_id=".$data["pu_code"]." AND pro_id=".$data['item_id_'.$i];
					
					$this->update($po_update,$where);
					
					
					//unset($recieved_item);
					$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						//if($data["status"]==4 OR $data["status"]==5){
							$datatostock   = array(
									'qty'   			=> 		$rows["qty"]+$data['qty_recieve_'.$i],
									'last_mod_date'		=>		date("Y-m-d"),
									'last_mod_userid'	=>		$GetUserId
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							
							$this->update($datatostock, $where);
							
					}
					
					if(@$data["is_invoice"] == 1){
						$recieved_item = array(
							'invoice_id'	  	=> 	$invoice_id,
							'receive_id'	  	=> 	$recieved_order,
							'vendor_id'			=> 	$data['vendor'],
							'total'	  			=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
							'paid'	  			=> 	0,
							'balance'		  	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
							'is_complete'	  	=> 0,
							'status'	  		=> 1,
							'date'				=> date("Y-m-d"),
							'receive_date'		=>	date("Y-m-d"),
						);
						$this->_name="tb_purchase_invoice_detail";
						
						$this->insert($recieved_item);
						
					}
					
				}
			}
			$purchase_sta = 3;
			if($data['all_total']!=$data['all_total_after']){
				$purchase_sta = 4;
			}
			
			// Updte Po To Completed
			
			$sql = "SELECT p.`purchase_id` FROM `tb_purchase_order_item` AS p WHERE p.`purchase_id`=".$data["pu_code"]." AND p.`is_completed`=0";
			$rs_po = $db->fetchAll($sql);
			if(empty($rs_po)){
				$is_po_completed=1;
				$appr_status = 3;
				$pedding = 8;
			}else{
				$is_po_completed=0;
				$appr_status = 4;
				$pedding = 7;
			}
			//echo $purchase_sta;exit();
			$arr_update = array('is_completed' =>	$is_po_completed,);
			$this->_name="tb_purchase_order";
			$where = "id=".$data["pu_code"];
			
			$this->update($arr_update,$where);
			
			
			
			$arr = array(
				'is_purchase'	=>	1,
				'appr_status'	=>	$appr_status,
				'pedding'		=>	$pedding,
				'pur_user'		=>	$GetUserId,
				'pur_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_su_price_idcompare";
			$where = "re_id=".$data["re_id"];
			
			$this->update($arr,$where);
			
					
			$arr = array(
				'status'	=>	5,
				'is_recieved'	=>	1,
				'purchase_status'	=>	$appr_status,
				'pending_status'	=>	$pedding
			);
			$where = "id=".$data["id"];
			$this->_name = "tb_purchase_order";
			
			$this->update($arr, $where);
			
			
			$arr_re = array(
					'pedding'		=>	$pedding,
					'appr_status'	=>	$appr_status,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["re_id"];
			
			$this->update($arr_re,$where);
			
			//exit();
			
			$db->commit();
			//return $recieved_order;
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err;
		}
	}
	
	function  edit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);exit();
		try{
			$identity = $data["identity"];
			if($identity!=""){
				
				$orderdata = array(
					'purchase_id'		=>	$data["pu_code"],
					'dn_number'			=>	$data["dn_no"],
					'invoice_no'		=>	$data["invoice_no"],
					"vendor_id"      	=> 	$data['vendor'],
					"LocationId"     	=> 	$data["branch"],
					"recieve_number" 	=> 	$data["recieve_no"],
					"date_order"     	=> 	date("Y-m-d",strtotime($data['date_order'])),
					"date_in"     		=> 	date("Y-m-d",strtotime($data['date_recieve'])),
					'dn_date'			=>	date("Y-m-d",strtotime($data['dn_date'])),
					"purchase_status"   => 	1,
					"payment_method" 	=> 	$data['payment_name'],
					"currency_id"    	=> 	$data['currency'],
					"remark"         	=> 	$data['remark'],
					"all_total"      	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"all_total_after"   => 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"tax"				=>	$data["vat"],
					"discount_value" 	=> 	$data['dis_value'],
					"discount_real"  	=> 	$data['global_disc'],
					"net_total"      	=> 	$data['totalAmoun_after'],
					"net_total_after"   => 	$data['totalAmoun_after'],
					"paid"           	=> 	0,
					"balance"        	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"balance_after"		=>	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"user_mod"       	=> 	$GetUserId,
					"date"      		=> 		date("Y-m-d",strtotime($data["date_recieve"])),
				    //"payment_number"    =>$data["payment_number"],
				    "invoice_date"      => 		date("Y-m-d",strtotime($data["invoice_date"])),
				    "receive_invoice_date" => 	date("Y-m-d",strtotime($data["invoice_recieve_date"])),
				);
				
				$this->_name='tb_recieve_order';
				$where = "order_id=".$data["id"];
				$recieved_order = $this->update($orderdata,$where);
				
				$orderdata_his = array(
					'receive_id'		=>	$data["id"],
					'purchase_id'		=>	$data["pu_code"],
					'dn_number'			=>	$data["dn_no"],
					'invoice_no'		=>	$data["invoice_no"],
					"vendor_id"      	=> 	$data['vendor'],
					"LocationId"     	=> 	$data["branch"],
					"recieve_number" 	=> 	$data["recieve_no"],
					"date_order"     	=> 	date("Y-m-d",strtotime($data['date_order'])),
					"date_in"     		=> 	date("Y-m-d",strtotime($data['date_recieve'])),
					'dn_date'			=>	date("Y-m-d",strtotime($data['dn_date'])),
					"purchase_status"   => 	1,
					"payment_method" 	=> 	$data['payment_name'],
					"currency_id"    	=> 	$data['currency'],
					"remark"         	=> 	$data['remark'],
					"all_total"      	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"all_total_after"   => 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"tax"				=>	$data["vat"],
					"discount_value" 	=> 	$data['dis_value'],
					"discount_real"  	=> 	$data['global_disc'],
					"net_total"      	=> 	$data['totalAmoun_after'],
					"net_total_after"   => 	$data['totalAmoun_after'],
					"paid"           	=> 	0,
					"balance"        	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"balance_after"		=>	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
					"user_mod"       	=> 	$GetUserId,
					"date"      		=> 		date("Y-m-d",strtotime($data["date_recieve"])),
				    //"payment_number"    =>$data["payment_number"],
				    "invoice_date"      => 		date("Y-m-d",strtotime($data["invoice_date"])),
				    "receive_invoice_date" => 	date("Y-m-d",strtotime($data["invoice_recieve_date"])),
					'type'				=>	2,
				);
				
				$this->_name='tb_recieve_order_history';
				$recieved_order_his = $this->insert($orderdata_his);
				
				
				
				$old_receive = $this->getReceiveById($data["id"]);
				if(!empty($old_receive)){
					foreach($old_receive as $old_rs){
						$receive = $this->getPurchaseItemExist($old_rs["purchase_id"],$old_rs["pro_id"]);
						if(!empty($receive)){
							$arr = array(
								'qty_after'		=>	$receive+$old_rs["qty_receive"],
							);
							$this->_name = "tb_purchase_order_item";
							$where = "purchase_id=".$old_rs["purchase_id"]." AND pro_id=".$old_rs["pro_id"];
							
							$this->update($arr,$where);
							
						}
					
					
					
					
					
					$rs_pro = $this->productLocationInventory($old_rs["pro_id"],$old_rs["LocationId"]);
							if(!empty($rs_pro)){
								$arr_pro = array(
									'qty'		=>	$rs_pro["qty"]+$old_rs["qty_receive"],
								);
								$this->_name = "tb_prolocation";
								$where=" id = ".$rs_pro['id'];
								
								$this->update($arr_pro,$where);
								
							}
						
					
					$row_product = $this->getProductForCost($old_rs["pro_id"],$old_rs["LocationId"]);
						if(!empty($row_product)){
							//print_r($row_product);
							$amount_pro = $row_product["price"] * $row_product["qty"];
							$new_amount_pro = $old_rs['qty_receive'] * $old_rs['price'];
							$total_qty = $row_product["qty"]+$old_rs['qty_receive'];
							$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_prolocation";
							$where=" pro_id = ".$row_product['id']." AND location_id=".$old_rs["LocationId"];
							
							$this->update($arr_pro, $where);
							
							
							//update Product Table
							$arr_pro   = array(
										'price'   	=> 		$cost_price,
										);
							$this->_name="tb_product";
							$where=" id = ".$row_product['id'];
							
							$this->update($arr_pro, $where);
							
						}
					}
				}
				$sql = "DELETE FROM tb_purchase_invoice WHERE dn_id=".$data["id"];
				
				$db->query($sql);
				
				if(@$data["is_invoice"]==1){
					$datainvoice = array(
						'purchase_id'		=>	$data["pu_code"],
						'invoice_no'		=>	$data["invoice_no"],
						"vendor_id"      	=> 	$data['vendor'],
						"branch_id"     	=> 	$data["branch"],
						"dn_id" 			=> 	$data["id"],
						"invoice_date"     	=> 	date("Y-m-d",strtotime($data['date_order'])),
						"total"				=>	$data['totalAmoun_after'],
						"total_vat"			=>	(($data['totalAmoun_after']*$data["vat"])/100),
						'vat'				=>	$data["vat"],
						"sub_total"     	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"sub_total_after"   => 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"discount" 			=> 	$data['global_disc'],
						"paid"    			=> 	0,
						"paid_after"        => 	0,
						"balance"      		=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"balance_after"   	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
						"is_fullpay"		=>	0,
						"user_id"       	=> 	$GetUserId,
						//"date"      		=> 		date("Y-m-d",strtotime($data["date_recieve"])),
						//"payment_number"    =>$data["payment_number"],
						"invoice_date"      => 	date("Y-m-d",strtotime($data["invoice_date"])),
						"invoice_date_from_stock" => 	date("Y-m-d",strtotime($data["invoice_recieve_date"])),
					);
					
					$this->_name='tb_purchase_invoice';
					
					$invoice_id = $this->insert($datainvoice);
					
					
					$arr_re_update = array(
						'is_invoice_controlling'	=> 1,
					);
					$this->_name='tb_recieve_order';
					$where = "order_id=".$recieved_order;
					
					$this->update($arr_re_update,$where);
					
					
				}
				$sql ="DELETE FROM tb_purchase_invoice_detail WHERE receive_id=".$data["id"];
				
				$db->query($sql);
				
				
				$sql ="DELETE FROM tb_recieve_order_item WHERE recieve_id=".$data["id"];
				
				$db->query($sql);
				
				
				$ids=explode(',',$data['identity']);
				$locationid=$data['branch'];
				
				foreach ($ids as $i){
					$row_product = $this->getProductForCost($data['item_id_'.$i],$locationid);
					if(!empty($row_product)){
						//print_r($row_product);
						$amount_pro = $row_product["price"] * $row_product["qty"];
						$new_amount_pro = $data['qty_recieve_'.$i] * $data['price_'.$i];
						$total_qty = $row_product["qty"]+$data['qty_recieve_'.$i];
						$cost_price = ($amount_pro+$new_amount_pro)/$total_qty;
						$arr_pro   = array(
										'price'   	=> 		$cost_price,
									);
						$this->_name="tb_product";
						$where=" id = ".$row_product['id'];
						
						
						$this->update($arr_pro, $where);
						
						
						
						$arr_pro   = array(
									'price'   	=> 		$cost_price,
									);
						$this->_name="tb_prolocation";
						$where=" pro_id = ".$row_product['id']." AND location_id=".$locationid;
						
						
						$this->update($arr_pro, $where);
						
						
					}
					
					$recieved_item = array(
							'recieve_id'	  	=> 	$data["id"],
							'pro_id'	  		=> 	$data['item_id_'.$i],
							'origin_qty_order'	=> 	$data['origin_qty_order_'.$i],
							'qty_order'	  		=> 	($data['qty_order_'.$i]+$data['old_qty_order_'.$i])-$data['qty_recieve_'.$i],
							'qty_receive'	  	=> 	$data['qty_recieve_'.$i],
							//'qty_detail'  		=> 	$data['qty_per_unit_'.$i],
							'price'		  		=> 	$data['price_'.$i],
							'disc_value'	  	=>  $data['dis_value'.$i],
							'sub_total'	  		=>  $data['total_'.$i],
							'sub_total_after'	=>  $data['total_after_'.$i],
							'remark'			=>	$data["remark_".$i],
							'plat_no'			=>	$data["plat_no"],
							'su_scale_refer'	=>	$data["su_scale_refer"],
							'su_scale_qty'		=>	$data["supplier_scale"],
							'com_scale_refer'	=>	$data["com_scale_refer"],
							'com_scale_qty'		=>	$data["company_scale"],
							'su_scale_in_m'		=>	$data["supplier_scale_total"],
							'com_scale_in_m'	=>	$data["company_scale_total"],
							'is_con_choose'		=>	@$data["choose"],
							'is_convertor'		=>	$data["is_convertor_".$i],
					);
					$this->_name="tb_recieve_order_item";
					
					$this->insert($recieved_item);
					

					$recieved_item_his = array(
							'his_id'	  		=> 	$recieved_order_his,
							'recieve_id'	  	=> 	$data["id"],
							'pro_id'	  		=> 	$data['item_id_'.$i],
							'origin_qty_order'	=> 	$data['origin_qty_order_'.$i],
							'qty_order'	  		=> 	($data['qty_order_'.$i]+$data['old_qty_order_'.$i])-$data['qty_recieve_'.$i],
							'qty_receive'	  	=> 	$data['qty_recieve_'.$i],
							//'qty_detail'  		=> 	$data['qty_per_unit_'.$i],
							'price'		  		=> 	$data['price_'.$i],
							'disc_value'	  	=>  $data['dis_value'.$i],
							'sub_total'	  		=>  $data['total_'.$i],
							'sub_total_after'	=>  $data['total_after_'.$i],
							'remark'			=>	$data["remark_".$i],
							'plat_no'			=>	$data["plat_no"],
							'su_scale_refer'	=>	$data["su_scale_refer"],
							'su_scale_qty'		=>	$data["supplier_scale"],
							'com_scale_refer'	=>	$data["com_scale_refer"],
							'com_scale_qty'		=>	$data["company_scale"],
							'su_scale_in_m'		=>	$data["supplier_scale_total"],
							'com_scale_in_m'	=>	$data["company_scale_total"],
							'is_con_choose'		=>	@$data["choose"],
							'is_convertor'		=>	$data["is_convertor_".$i],
					);
					$this->_name="tb_recieve_order_item_history";
					
					$this->insert($recieved_item_his);
					
					
					
					// Update Purchase Order To Completed
					$qty_after = $data['qty_order_'.$i]-$data['qty_recieve_'.$i];
					if($qty_after==0){
						$is_completed =1; 
					}else{
						$is_completed =0; 
					}
					
					// Update Purchase Order Item Qty
					
					$po_update = array(
							'qty_after'		=> 	$qty_after,
							'is_completed'	=>	$is_completed,
						);
					$this->_name="tb_purchase_order_item";
					$where = "purchase_id=".$data["pu_code"]." AND pro_id=".$data['item_id_'.$i];
					
					
					$this->update($po_update,$where);
					
					
					
					//unset($recieved_item);
					$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						//if($data["status"]==4 OR $data["status"]==5){
							$datatostock   = array(
									'qty'   			=> 		$rows["qty"]+$data['qty_recieve_'.$i],
									'last_mod_date'		=>		date("Y-m-d"),
									'last_mod_userid'	=>		$GetUserId
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							
							$this->update($datatostock, $where);
							
							
							
					}
					
					if(@$data["is_invoice"] == 1){
						$recieved_item = array(
							'invoice_id'	  	=> 	$invoice_id,
							'receive_id'	  	=> 	$data["id"],
							'vendor_id'			=> 	$data['vendor'],
							'total'	  			=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
							'paid'	  			=> 	0,
							'balance'		  	=> 	$data['totalAmoun_after']+(($data['totalAmoun_after']*$data["vat"])/100),
							'is_complete'	  	=> 0,
							'status'	  		=> 1,
							'date'				=> date("Y-m-d"),
							'receive_date'		=>	date("Y-m-d"),
						);
						$this->_name="tb_purchase_invoice_detail";
						
						
						$this->insert($recieved_item);
						
						
						
					}
					
				}
			}
			$purchase_sta = 3;
			if($data['all_total']!=$data['all_total_after']){
				$purchase_sta = 4;
			}
			
			// Updte Po To Completed
			
			$sql = "SELECT p.`purchase_id` FROM `tb_purchase_order_item` AS p WHERE p.`purchase_id`=".$data["pu_code"]." AND p.`is_completed`=0";
			
			$rs_po = $db->fetchAll($sql);
			
			if(empty($rs_po)){
				$is_po_completed=1;
				$appr_status = 3;
				$pedding = 8;
			}else{
				$is_po_completed=0;
				$appr_status = 4;
				$pedding = 7;
			}
			//echo $purchase_sta;exit();
			$arr_update = array('is_completed' =>	$is_po_completed,);
			$this->_name="tb_purchase_order";
			$where = "id=".$data["pu_code"];
			
			
			$this->update($arr_update,$where);
			
			
			
			
			$arr = array(
				'is_purchase'	=>	1,
				'appr_status'	=>	$appr_status,
				'pedding'		=>	$pedding,
				'pur_user'		=>	$GetUserId,
				'pur_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_su_price_idcompare";
			$where = "re_id=".$data["re_id"];
			
			
			$this->update($arr,$where);
			
			
			
					
			$arr = array(
				'status'	=>	5,
				'is_recieved'	=>	1,
				'purchase_status'	=>	$appr_status,
				'pending_status'	=>	$pedding
			);
			$where = "id=".$data["id"];
			$this->_name = "tb_purchase_order";
			
			
			$this->update($arr, $where);
			
			
			
			
			$arr_re = array(
					'pedding'		=>	$pedding,
					'appr_status'	=>	$appr_status,
					'appr_user'		=>	$GetUserId,
					'appr_date'		=>  date('Y-m-d'),
			);
			$this->_name = "tb_purchase_request";
			$where = "id=".$data["re_id"];
			
			$this->update($arr_re,$where);
			
			
			
			//exit();
			
			$db->commit();
			//return $recieved_order;
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage().$e->getLine();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err;
		}
	}
	
	function getPurchaseItemExist($po_id,$pro_id){
		$db = $this->getAdapter();
		$sql = "SELECT poi.`qty_after` FROM `tb_purchase_order_item` AS poi WHERE poi.`purchase_id` = $po_id AND poi.`pro_id`=$pro_id";
		return $db->fetchOne($sql);
	}
	
	function addInvoice($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		try{
			if(!empty($data["identity"])){
				$datainvoice = array(
						'purchase_id'		=>	$data["purchase_id"],
						'invoice_no'		=>	$data["payment_number"],
						"vendor_id"      	=> 	$data['customer_id'],
						"branch_id"     	=> 	$data["branch"],
						//"dn_id" 			=> 	$recieved_order,
						"invoice_date"     	=> 	date("Y-m-d",strtotime($data["invoice_date"])),
						'invoice_date_from_stock'=>	date("Y-m-d",strtotime($data["invoice_date"])),
						"total"				=>	$data['total'],
						'vat'				=>	$data["vat"],
						"total_vat"			=>	$data['total_vat'],
						"sub_total"     	=> 	$data['all_total'],
						"sub_total_after"   => 	$data['all_total'],
						//"discount" 			=> 	$data['global_disc'],
						"paid"    			=> 	0,
						"paid_after"        => 	0,
						"balance"      		=> 	$data['balance'],
						"balance_after"   	=> 	$data['balance'],
						"is_fullpay"		=>	0,
						"user_id"       	=> 	$GetUserId,
						//"date"      		=> 	$data["date_recieve"],
						//"payment_number"    =>$data["payment_number"],
						//"invoice_date"      => 	date("Y-m-d"),	//$data["invoice_date"],
						//"receive_invoice_date" => 	date("Y-m-d"),
						"is_invoice_contrilling"	=>	1,
					);
					
					$this->_name='tb_purchase_invoice';
					$invoice_id = $this->insert($datainvoice);
					
				$orderdata = array(
						'invoice_id'					=>	$invoice_id,
						'purchase_id'					=>	$data["purchase_id"],
						//'request_id'					=>	$rs["request_id"],
						'branch_id'						=>	$data["branch"],
						'vendor_id'						=>	$data["customer_id"],
						'invoice_no'					=>	$data["payment_number"],
						//'receive_no'					=>	$rs["recieve_number"],
						//'purchase_no'					=>	$rs["purchase_no"],
						'invoice_date'					=>	date("Y-m-d",strtotime($data["invoice_date"])),
						//"receive_invoice_date"     		=>  date("Y-m-d",strtotime($rs["invoice_date_from_stock"])),
						"invoice_controlling_date"     	=> 	date("Y-m-d",strtotime($data["invoice_date_from_stock"])),
						"sub_total"     				=> 	$data["total"],
						"vat"      						=> 	$data["vat"],
						"total_vat"						=>	$data['total_vat'],
						"grand_total" 					=> 	$data["all_total"],
						"grand_total_after" 			=> 	$data["all_total"],
						"paid" 							=> 	0,
						"balance" 						=> 	$data["all_total"],
						'date'							=>	new Zend_Date(),
						'user_id'						=>	$GetUserId,
					);
					
					$this->_name='tb_invoice_controlling';
					$recieved_order = $this->insert($orderdata);
			$ids=explode(',',$data['identity']);
			foreach ($ids as $key => $i)
			{
			$recieved_item = array(
							'invoice_id'	  	=> 	$invoice_id,
							'receive_id'	  	=> $data["dn_id".$i],
							'vendor_id'			=> 	$data['customer_id'],
							'total'	  			=> 	$data['subtotal'.$i],
							'paid'	  			=> 	0,
							'balance'		  	=> 	$data['subtotal'.$i],
							'is_complete'	  	=> 0,
							'status'	  		=> 1,
							'date'				=> date("Y-m-d"),
							'receive_date'		=>	date("Y-m-d"),
						);
						$this->_name="tb_purchase_invoice_detail";
						
						$this->insert($recieved_item);
						

				$arr_re = array(
					'is_invoice_controlling'	=>	1,
				);
				$this->_name = "tb_recieve_order";
				$where = "order_id=".$data["dn_id".$i];
				
				$this->update($arr_re,$where);
				
			}
			}
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getProductForCost($pro_id,$location){
		$db = $this->getAdapter();
		$sql="SELECT p.id,pl.`price`,pl.`qty` FROM `tb_product` AS p ,`tb_prolocation` AS pl WHERE p.id=pl.`pro_id` AND pl.`location_id`=$location AND p.id=$pro_id";
		//echo $sql;
		return $db->fetchRow($sql);
	}
	public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"			=>	$pro_id,
    				"location_id"		=>	$location_id,
    				"qty"				=>	0,
    				"qty_warning"		=>	0,
    				"last_mod_userid"	=>	$GetUserId,
    				///"user_id"			=>	$GetUserId,
    				"last_mod_date"		=>	date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		
    	return $row; 
    	}  	
    }
	function getAllReceivedOrder($search){
		$db = $this->getAdapter();
		$sql=" SELECT 
				  ro.order_id AS id,
				  ro.recieve_number,
				  ro.`dn_number`,
				  ro.date_order,
				  ro.date_in,
				  ro.dn_date,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=ro.`LocationId` LIMIT 1) AS branch,
				  (SELECT v.v_name FROM tb_vendor AS v WHERE v.vendor_id = ro.vendor_id LIMIT 1) AS vendor_name,
				  ro.all_total,
				  (SELECT  u.username FROM tb_acl_user AS u WHERE u.user_id = ro.user_mod LIMIT 1) AS user_name ,
				   p.`order_number` AS po_no,
				  (SELECT p.name FROM tb_plan AS p WHERE p.id=(SELECT pr.plan_id FROM `tb_purchase_request` AS pr WHERE pr.id=(SELECT po.`re_id` FROM `tb_purchase_order` AS po WHERE po.`id`=ro.`purchase_id` LIMIT 1) LIMIT 1) LIMIT 1) AS plan
				FROM
				  tb_recieve_order AS ro ,
				  `tb_purchase_order` AS p
				WHERE ro.`status` = 1 AND p.id=ro.`purchase_id` ";
		$order=" ORDER BY ro.order_id DESC  ";
		$where='';
		$from_date =(empty($search['start_date']))? '1': "  ro.dn_date >= '".$search['start_date']."'";
		$to_date = (empty($search['end_date']))? '1': "   ro.dn_date <= '".$search['end_date']."'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = "  ro.recieve_number LIKE '%{$s_search}%'";
			$s_where[] = "  ro.`dn_number` LIKE '%{$s_search}%'";
			$s_where[] = "  p.`order_number` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		/*if($search['po_pedding']>0){
			$where .= " AND ro.purchase_status =".$search['po_pedding'];
		}*/
		if($search['suppliyer_id']>0){
			$where .= " AND ro.vendor_id =".$search['suppliyer_id'];
		}
		if($search['branch']>0){
			$where .= " AND ro.LocationId =".$search['branch'];
		}
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function vendorPurchaseOrderPayment($data)
	{
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$db = $this->getAdapter();	
			$db->beginTransaction();
		
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$idrecord=$data['v_name'];
			if($data['txt_order']==""){
				$date= new Zend_Date();
				$order_add="PO".$date->get('hh-mm-ss');
			}
			else{
				$order_add=$data['txt_order'];
					
			}
			$info_purchase_order=array(
					"vendor_id"      => $data['v_name'],
					"LocationId"     => $data["LocationId"],
					"order"          => $order_add,
					"date_order"     => $data['order_date'],
					"date_in"     	 => $data['date_in'],
					"status"         => 4,
					//"payment_method" => $data['payment_name'],
					//"currency_id"    => $data['currency'],
					"remark"         => $data['remark'],
					"user_mod"       => $GetUserId,
					"timestamp"      => new Zend_Date(),
	// 				"net_total"      => $data['net_total'],
	// 				"discount_type"	 => $data['discount_type'],
	// 				"discount_value" => $data['discount_value'],
	// 				"discount_real"  => $data['discount_real'],
					"paid"           => $data['remain'],
					"all_total"      => $data['remain'],
					"balance"        => 0
			);
			
			
			//and info of purchase order
			
			$purchase_id = $db_global->addRecord($info_purchase_order,"tb_purchase_order");
		
			unset($info_purchase_order);
			unset($datainfo); unset($idrecord);
		
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			foreach ($ids as $i)
			{
					
				$itemId=$data['item_id_'.$i];
				$qtyrecord=$data['qty'.$i];//qty on 1 record
				
				
				//add history purchase order
				$data_history[$i] = array(
						'pro_id'	 => $data['item_id_'.$i],
						'type'		 => 1,
						'order'		 => $purchase_id,
						'customer_id'=> $data['v_name'],
						'date'		 => new Zend_Date(),
						'status'	 => 4,
						'order_total'=> $data['remain'],
						'qty'		 => $data['qty'.$i],
						'unit_price' => $data['price'.$i],
						'sub_total'  => $data['total'.$i],
				);
				$order_history = $db_global->addRecord($data_history[$i],"tb_purchase_order_history");
				unset($data_history[$i]);
				
				
				//add purchase order item
				$data_item[$i]= array(
						'order_id'	  => $purchase_id,
						'pro_id'	  => $data['item_id_'.$i],
						'qty_order'	  => $data['qty'.$i],
						'price'		  => $data['price'.$i],
						'total_befor' => $data['total'.$i],
	// 					'disc_type'	  => $data['dis-type-'.$i],
	// 					'disc_value'  => $data['dis-value'.$i],
						'sub_total'	  => $data['total'.$i],
						'remark'	  => $data['remark_'.$i]
				);
				$db->insert("tb_purchase_order_item", $data_item[$i]);
				//print_r($data_item);
				unset($data_item[$i]);
				
				
				
				
				//check stock product location
				
				
				$rows=$db_global -> productLocationInventoryCheck($itemId, $locationid);
				if($rows)
				{
					
					$qtyold       = $rows['qty_onorder'];
					$getrecord_id = $rows["ProLocationID"];
					//$qty_onhand   = $rows["QuantityOnHand"]+$qtyrecord;
					$itemOnHand   = array(
							'qty_onorder'   => $qtyold+$qtyrecord,
							//'qty_available'=> $rows["qty_available"]+$qtyrecord
					);
					
					
					//update total stock
					$itemid=$db_global->updateRecord($itemOnHand,$itemId,"pro_id","tb_product");
					//update stock dork
					//$newqty       = ;
					$updatedata=array(
							'qty_onorder' => $qtyold+$qtyrecord
					);
					//update stock product location
					$itemid=$db_global->updateRecord($updatedata,$getrecord_id,"ProLocationID","tb_prolocation");
					//add move hostory
				}
				else
				{
					//insert stock ;
					$rows_pro_exit= $db_global->productLocation($itemId, $locationid);
					if($rows_pro_exit){
						$updatedata=array(
								'qty_onorder' => $rows_pro_exit['qty_onorder']+$qtyrecord
						);
						//update stock product location
						$itemid=$db_global->updateRecord($updatedata,$rows_pro_exit['ProLocationID'], "ProLocationID", "tb_prolocation");
					}
					else{
						$insertdata=array(
								'pro_id'       => $data['item_id_'.$i],
								'LocationId'   => $locationid,
								'last_usermod' => $GetUserId,
								'qty_onorder'          => $qtyrecord,
								'last_mod_date'=>new Zend_Date()
						);
						//update stock product location
						$db->insert("tb_prolocation", $insertdata);
					}
					//add move hostory
					$rows=$db_global->InventoryExist($itemId);
					if($rows)
					{
						//$qty_onhand   = $rowitem["QuantityOnHand"]+$data['qty'.$i];
						$itemOnHand   = array(
								'qty_onorder'    => $rows["qty_onorder"]+$data['qty'.$i],
								//"qty_available" => $rows["qty_available"]+$data['qty'.$i],
								'pro_id'		    => $itemId
						);
						//update total stock
						 $db_global->updateRecord($itemOnHand,$itemId,"pro_id","tb_product");
					}
					else
					{
						$dataInventory= array(
								'ProdId'            => $itemId,
								'qty_onorder'    => $data['qty'.$i],
								//'qty_available' => $data['qty'.$i],
								'Timestamp'      => new Zend_date()
						);
						$db->insert("tb_product", $dataInventory);
						//add move hostory
					}
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
		}
		
	} 
	//for add purchase order then click save
	public function VendorOrder($data)
	{
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db= $this->getAdapter(); 
	
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
	
		$idrecord=$data['v_name'];
		$datainfo=array(
				"contact_name"=> $data['contact'],
				"phone"       => $data['txt_phone']
		);
		//updage vendor info
		$itemid=$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
		if($data['txt_order']==""){
			$date= new Zend_Date();
			$order_add="PO".$date->get('hh-mm-ss');
		}
		else{
			$order_add=$data['txt_order'];
			
		}
		$info_purchase_order=array(
				"vendor_id"      => $data['v_name'],
				"LocationId"     => $data["LocationId"],
				"order"          => $order_add,
				"date_order"     => $data['order_date'],
				"status"         => 2,
				"payment_method" => $data['payment_name'],
				"currency_id"    => $data['currency'],
				"remark"         => $data['remark'],
				"user_mod"       => $GetUserId,
				"timestamp"      => new Zend_Date(),
				"version"        => 1,
				"net_total"      => $data['net_total'],
				"discount_type"	 => $data['discount_type'],
				"discount_value" => $data['discount_value'],
				"discount_real"  => $data['discount_real'],
				"paid"           => $data['paid'],
				"all_total"      => $data['all_total'],
				"balance"        => $data['all_total']-$data['paid']
		);
		//and info of purchase order
		$purchase_id = $db_global->addRecord($info_purchase_order,"tb_purchase_order");
		unset($info_purchase_order);
			
		$ids=explode(',',$data['identity']);
		//   $qtyonhand=0;
		foreach ($ids as $i)
		{
			//add history purchase order
				$data_history = array(
						'pro_id'	 => $data['item_id_'.$i],
						'type'		 => 1,
						'order'		 => $purchase_id,//$data['txt_order']
						'customer_id'=> $data['v_name'],
						'date'		 => new Zend_Date(),
						'status'	 => 2,
						'order_total'=> $data['all_total'],
						'qty'		 => $data['qty'.$i],
						'unit_price' => $data['price'.$i],
						'sub_total'  => $data['after_discount'.$i],
				);
				
				$db_global->addRecord($data_history,"tb_purchase_order_history");
				unset($data_history);
			//add purchase order item
			$data_item[$i]= array(
					'order_id'	  => $purchase_id,
					'pro_id'	  => $data['item_id_'.$i],
					'qty_order'	  => $data['qty'.$i],
					'price'		  => $data['price'.$i],
					'total_befor' => $data['total'.$i],
					'disc_type'	  => $data['dis-type-'.$i],
					'disc_value'  => $data['dis-value'.$i],
					'sub_total'	  => $data['after_discount'.$i]
			);
			$id_order_item=$db_global->addRecord($data_item[$i],"tb_purchase_order_item");
			unset($data_item[$i]);
				
			//update stock total inventory
			$locationid=$data['LocationId'];
			$itemId=$data['item_id_'.$i];
			$qtyrecord=$data['qty'.$i];//qty on 1 record
			$sql="SELECT tv.ProdId, tv.QuantityOnOrder,tv.QuantityAvailable
			FROM tb_inventorytotal AS tv
			INNER JOIN tb_product AS p ON tv.ProdId = p.pro_id
			WHERE p.pro_id = ".$data['item_id_'.$i];
			$rows=$db_global->getGlobalDbRow($sql);
			if($rows)
			{
				$qty_onhand   = $rows["QuantityOnOrder"]+$qtyrecord;
	
				$qty_on_order = array(
						"QuantityOnOrder"=>$rows["QuantityOnOrder"]+$qtyrecord
				);
				//update total stock
				$db_global->updateRecord($qty_on_order,$itemId,"ProdId","tb_inventorytotal");
				unset($qty_on_order);
			}
			else{
				$row = $db_global->InventoryExist($itemId);
				if($row){
					$qty_onhand   = $rows["QuantityOnOrder"]+$qtyrecord;
					$qty_on_order = array(
							"QuantityOnOrder"=>$rows["QuantityOnOrder"]+$qtyrecord
					);
					//update total stock
					$db_global->updateRecord($qty_on_order,$itemId,"ProdId","tb_inventorytotal");
					unset($qty_on_order);
				}
				else{
					$addInventory= array(
							'ProdId'            => $itemId,
							'QuantityOnOrder'    => $data['qty'.$i],
							'Timestamp'         => new Zend_date()
					);
					$db_global->addRecord($addInventory,"tb_inventorytotal");
					unset($addInventory);
				}
	
			}
	
		}
	
	}
	/*for update page purchase
	 * 
	 * 
	 * */
	///page update purchase order when click update
	public function updateVendorOrder($data)
	{
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
		
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
			//for update order by id\
			$id_order_update=$data['id'];
			$info_purchase_order=array(
					"vendor_id"      => $data['v_name'],
					"LocationId"     => $data["LocationId"],
					"order"          => $data['txt_order'],
					"date_order"     => $data['order_date'],
					"status"         => 4,
					"remark"         => $data['remark'],
					"user_mod"       => $GetUserId,
					"timestamp"      => new Zend_Date(),
					"paid"           => $data['paid'],
					"balance"        => $data['remain']
			);
			//update info of order
			$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");
		
			$sql_itm="SELECT iv.ProdId, iv.QuantityOnHand,iv.QuantityAvailable,sum(po.qty_order) AS qty_order FROM tb_purchase_order_item AS po
			INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = po.pro_id WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
			$rows_order=$db_global->getGlobalDb($sql_itm);
			if($rows_order){
				foreach ($rows_order as $row_order){
					$qty_on_order = array(
							"QuantityOnHand"	=> $row_order["QuantityOnHand"]-$row_order["qty_order"],
							"QuantityAvailable"	=> $row_order["QuantityAvailable"]-$row_order["qty_order"],
							"Timestamp" 		=> new Zend_Date()
					);
					//update total stock
					$db_global->updateRecord($qty_on_order,$row_order["ProdId"],"ProdId","tb_inventorytotal");
					
					$row_get = $db_global->porductLocationExist($row_order["ProdId"],$data["old_location"]);
					if($row_get){
						$qty_on_location = array(
								"qty"			=> $row_get["qty"]-$row_order["qty_order"],
								"last_usermod" 	=> $GetUserId,
								"last_mod_date" => new Zend_Date()
						);
						//update total stock
						$db_global->updateRecord($qty_on_location,$row_get["ProLocationID"],"ProLocationID","tb_prolocation");
					}
				}
				
			}
			unset($rows_order);
			$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($id_order_update)";
			$db_global->deleteRecords($sql);
		
			$ids=explode(',',$data['identity']);
			//add order in tb_inventory must update code again 9/8/13
			foreach ($ids as $i)
			{
				$data_item[$i]= array(
						'order_id'	  => $id_order_update,
						'pro_id'	  => $data['item_id_'.$i],
						'qty_order'	  => $data['qty'.$i],
						'price'		  => $data['price'.$i],
						'sub_total'	  => $data['total'.$i],
						'remark'	  => $data['remark_'.$i],//just add new 
				);
				$db->insert("tb_purchase_order_item", $data_item[$i]);
				unset($data_item[$i]);
				
				$locationid=$data['LocationId'];
				$itemId=$data['item_id_'.$i];
				$qtyrecord=$data['qty'.$i];//qty on 1 record
					
				$rows=$db_global -> productLocationInventory($itemId, $locationid);//to check product location
				if($rows)
				{
					$getrecord_id = $rows["ProLocationID"];
					$itemOnHand = array(
							'QuantityOnHand'   => $rows["QuantityOnHand"]+$qtyrecord,
							'QuantityAvailable'=> $rows["QuantityAvailable"]+$qtyrecord
					);
					//update total stock
					$db_global->updateRecord($itemOnHand,$itemId,"ProdId","tb_inventorytotal");
					unset($itemOnHand);
					//update stock dork
					//$newqty       = $rows['qty']-$qtyrecord;
					$updatedata=array(
							'qty' => $rows['qty']+$qtyrecord
					);
					//update stock product location
					$db_global->updateRecord($updatedata,$getrecord_id,"ProLocationID","tb_prolocation");
					unset($updatedata);
					//update stock record
				}else
				{
						//insert stock ;
						$rows_pro_exit= $db_global->productLocation($itemId, $locationid);
						if($rows_pro_exit){
							$updatedata=array(
									'qty' => $rows_pro_exit['qty']+$qtyrecord
							);
							//update stock product location
							$itemid=$db_global->updateRecord($updatedata,$rows_pro_exit['ProLocationID'], "ProLocationID", "tb_prolocation");
							unset($updatedata);
						}
						else{
							$insertdata=array(
									'pro_id'       => $itemId,
									'LocationId'   => $locationid,
									'last_usermod' => $GetUserId,
									'qty'          => $qtyrecord,
									'last_mod_date'=>new Zend_Date()
							);
							//update stock product location
							$db->insert("tb_prolocation", $insertdata);
							unset($insertdata);
						}
					
						$rowitem=$db_global->InventoryExist($itemId);//to check product location
						if($rowitem)
						{
							$itemOnHand   = array(
									'QuantityOnHand'=>$rowitem["QuantityOnHand"]+$qtyrecord,
									'QuantityAvailable'=>$rowitem["QuantityAvailable"]+$qtyrecord,
					
							);
							//update total stock
							$itemid=$db_global->updateRecord($itemOnHand,$itemId,"ProdId","tb_inventorytotal");
							unset($itemOnHand);
						}
						else
						{
							$dataInventory= array(
									'ProdId'            => $itemId,
									'QuantityOnHand'    => $qtyrecord,
									'QuantityAvailable' => $qtyrecord,
									'Timestamp'         => new Zend_date()
							);
							$db->insert("tb_inventorytotal", $dataInventory);
							unset($dataInventory);
							//update stock product location
						}
					}
			}
			
			$db->commit();
			
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	/*
	 * for update purchase order then click payment
	 * 29-13
	 * 
	 * */
	///when click payment on page update purchase order
	public function updateVendorOrderPayment($data){
	
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
	
		$session_user=new Zend_Session_Namespace('auth');
		$userName = $session_user->user_name;
		$GetUserId = $session_user->user_id;
	
// 		$idrecord=$data['v_name'];
// 		$datainfo=array(
// 				"contact_name"=>$data['contact'],
// 				"phone"       =>$data['txt_phone'],
// 				//	"add_remark"  =>$data['remark_add']
// 		);
// 		//updage customer info
// 		$itemid=$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
		$id_order_update=$data['id'];
		$info_order = array(
				"vendor_id"      => $data['v_name'],
				"LocationId"     => $data["LocationId"],
				"order"          => $data['txt_order'],
				"date_order"     => $data['order_date'],
				"status"         => 4,
// 				"payment_method" => $data['payment_name'],
// 				"currency_id"    => $data['currency'],
				"remark"         => $data['remark'],
				"user_mod"       => $GetUserId,
				"timestamp"      => new Zend_Date(),
				"version"        => 1,
				"net_total"      => $data['net_total'],
				"discount_type"	 => $data['discount_type'],
				"discount_value" => $data['discount_value'],
				"discount_real"  => $data['discount_real'],
				"paid"           => $data['all_total'],
				"all_total"      => $data['all_total'],
				"balance"        => 0
		);
		//update info of order not done
		$db_global->updateRecord($info_order,$id_order_update,"order_id","tb_purchase_order");
			$rows_exist=$db_global->purchaseOrderHistoryExitAll($id_order_update);
			if($rows_exist){
				foreach ($rows_exist as $id_history){
					$data_status=array(
							'status'=> 4
					);
					
					$db_global->updateRecord($data_status, $id_history['history_id'], "history_id", "tb_purchase_order_history");	
					unset($data_status);				
				}
				
			}
		unset($info_order);//if error check this here
		//and info of order
		$sql_item="SELECT iv.ProdId, iv.QuantityOnOrder,sum(po.qty_order) AS qtyorder
		FROM tb_purchase_order_item AS po
		INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = po.pro_id WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
		$rows_order=$db_global->getGlobalDb($sql_item);
			if($rows_order){
				foreach ($rows_order as $row_order){
					$qty_on_order = array(
							"QuantityOnOrder"=>$row_order["QuantityOnOrder"]-$row_order["qtyorder"] ,
					);
							//update total stock
					$db_global->updateRecord($qty_on_order,$row_order["ProdId"],"ProdId","tb_inventorytotal");
				}
			}
				unset($rows_order); unset($rows_order);
				$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($id_order_update)";
				$db_global->deleteRecords($sql);
				//$db->DeleteData("tb_purchase_order_item"," WHERE order_id = ".$id_order_update);
				$ids=explode(',',$data['identity']);
				$qtyonhand=0;
				foreach ($ids as $i)
				{
					$data_item[$i]= array(
					'order_id'	  => $id_order_update,
					'pro_id'	  => $data['item_id_'.$i],
					'qty_order'	  => $data['qty'.$i],
					'price'		  => $data['price'.$i],
					'total_befor' => $data['total'.$i],
					'disc_type'	  => $data['dis-type-'.$i],
					'disc_value'  => $data['dis-value'.$i],
					'sub_total'	  => $data['after_discount'.$i]
					);
					$db->insert("tb_purchase_order_item", $data_item[$i]);
						
					unset($data_item[$i]);
					//UPDATE STOCK
					//check stock product location
					$locationid=$data['LocationId'];
					$itemId=$data['item_id_'.$i];
					$qtyrecord=$data['qty'.$i];//qty on 1 record

					$rows=$db_global->inventoryLocation($locationid, $itemId);
					if($rows)
					{
						$qty_on_order = array(
								"QuantityAvailable" => $rows["QuantityAvailable"] + $data['qty'.$i] ,
								"QuantityOnHand"    => $rows["QuantityOnHand"] + $data['qty'.$i]
						);
								//update total stock
						$db_global->updateRecord($qty_on_order,$itemId,"ProdId","tb_inventorytotal");
								unset($qty_on_order);
								//update stock dork
						$newqty       = $rows["qty"]+$qtyrecord;
						$updatedata=array(
								'qty' => $newqty
						);
						//update stock product location
						$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
								unset($updatedata);
								//update stock record
					}
							else
							{
								$insertdata=array(
										'pro_id'     => $itemId,
										'LocationId' => $locationid,
										'qty'        => $qtyrecord
								);
								//update stock product location
								$db->insert("tb_prolocation", $insertdata);
								
								$rows_stock=$db_global->InventoryExist($itemId);
								if($rows_stock){
									$dataInventory= array(
											'ProdId'            => $itemId,
											'QuantityOnHand'    => $rows_stock["QuantityOnHand"]+ $data['qty'.$i],
											'QuantityAvailable' => $rows_stock["QuantityAvailable"]+$data['qty'.$i],
											'Timestamp'         => new Zend_date()
									);
									$db_global->updateRecord($dataInventory,$rows_stock["ProdId"],"ProdId","tb_inventorytotal");
									unset($dataInventory);
								}//add new to stock inventory if don't have in stock inventory
								else{
									$addInventory= array(
											'ProdId'            => $itemId,
											'QuantityOnHand'    => $data['qty'.$i],
											'QuantityAvailable' => $data['qty'.$i],
											'Timestamp'         => new Zend_date()
									);
									$db->insert("tb_inventorytotal", $addInventory);
									unset($addInventory);
								}
							}
					}
				}
				public function cancelPurchaseOrder($data){
					try{
						$db_global= new Application_Model_DbTable_DbGlobal();
						$db = $this->getAdapter();
						$db->beginTransaction();
				
						$session_user=new Zend_Session_Namespace('auth');
						$GetUserId= $session_user->user_id;
				
						$id_order_update=$data['id'];
						$sql_itm="SELECT iv.ProdId, iv.QuantityOnHand,iv.QuantityAvailable,sum(po.qty_order) AS qty_order FROM tb_purchase_order_item AS po
						INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = po.pro_id WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
						$rows_order=$db_global->getGlobalDb($sql_itm);
						if($rows_order){
							foreach ($rows_order as $row_order){
								$qty_on_order = array(
										"QuantityOnHand"	=> $row_order["QuantityOnHand"]-$row_order["qty_order"],
										"QuantityAvailable"	=> $row_order["QuantityAvailable"]-$row_order["qty_order"],
										"Timestamp" 		=> new Zend_Date()
								);
								$db_global->updateRecord($qty_on_order,$row_order["ProdId"],"ProdId","tb_inventorytotal");
								
								$row_get = $db_global->porductLocationExist($row_order["ProdId"],$data["old_location"]);
								if($row_get){
									$qty_on_location = array(
											"qty"			=> $row_get["qty"]-$row_order["qty_order"],
											"last_usermod" 	=> $GetUserId,
											"last_mod_date" => new Zend_Date()
									);
									$db_global->updateRecord($qty_on_location,$row_get["ProLocationID"],"ProLocationID","tb_prolocation");
								}
								
								$this->getPurchaseHistory($id_order_update, $row_order["ProdId"]);
							}
							
							$update =array("status"=>6);
							$db_global->updateRecord($update, $id_order_update,"order_id","tb_purchase_order");
						}
						$db->commit();
					}catch(Exception $e){
						$db->rollBack();
					}
				
				}
				public function getPurchaseHistory($order_id,$item_name){
					$db = $this->getAdapter();
					$sql = " SELECT history_id FROM tb_purchase_order_history 
					WHERE `order` = $order_id AND pro_id =$item_name ";
					$rows=$db->fetchAll($sql);
					$db_global= new Application_Model_DbTable_DbGlobal();
					$update =array("status"=>6);
					if(!empty($rows)){
						foreach ($rows AS $row){
							$db_global->updateRecord($update, $row["history_id"],"history_id","tb_purchase_order_history");
						}
					}
				}
	
	function makeFullReceive($id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		try{
			/*$arr_re = array(
				'is_completed'	= 1,
			);	
			$where = "order_id=";*/
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err;
		}
	}
}