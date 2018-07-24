<?php

class Purchase_Model_DbTable_Dbpayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getVendore($id){
		$db = $this->getAdapter();
		$sql = "SELECT v.`bank_name`,v.`bank_no` FROM `tb_vendor` AS v WHERE v.`vendor_id`=$id";
		return $db->fetchRow($sql);
	}
	function getPayCode($location){
	    $db = $this->getAdapter();
	    $user = $this->GetuserInfo();
	    //$location = $user['branch_id'];
	
	    $sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
	    $prefix = $db->fetchOne($sql_pre);
	
	    $sql="SELECT r.`id` FROM `tb_vendor_payment` AS r WHERE r.`branch_id`=$location ORDER BY r.`id` DESC LIMIT 1";
	    $num = $db->fetchOne($sql);
	
	    $num_lentgh = strlen((int)$num+1);
	    $num = (int)$num+1;
	    $pre = "POL-";
	    for($i=$num_lentgh;$i<4;$i++){
	        $pre.="0";
	    }
	    return $pre.$num;
	}
	function getHeaderPayment($id){
		$db = $this->getAdapter();
		$sql = "SELECT v.*,
       (SELECT vd.invoice_id FROM `tb_vendorpayment_detail` AS vd WHERE vd.receipt_id= v.id LIMIT 1)  AS invoice_id
		FROM `tb_vendor_payment` AS v WHERE v.id=$id";
		return $db->fetchRow($sql);
	}
	
	function getPaymentDetail($id){
		$db = $this->getAdapter();
// 		$sql = "SELECT 
// 			  * ,
// 			  p.`invoice_no`,
// 			  P.`invoice_date`,
// 			  P.`invoice_date_from_stock`,
// 			  i.`invoice_controlling_date`,
// 			  p.`discount`
// 			FROM
// 			  `tb_vendorpayment_detail` AS v,
// 			  `tb_purchase_invoice` AS p ,
// 			  `tb_invoice_controlling` AS i
// 			WHERE v.`invoice_id` = p.`id` AND p.id=i.`invoice_id` AND v.invoice_id=$id";
		$sql = "SELECT
		* ,
		v.invoice_id,
		p.`invoice_no`,
		P.`invoice_date`,
		P.`invoice_date_from_stock`,
		i.`invoice_controlling_date`,
		p.`discount`
		FROM
		`tb_vendorpayment_detail` AS v,
		`tb_purchase_invoice` AS p ,
		`tb_invoice_controlling` AS i
		WHERE v.`invoice_id` = p.`id` AND p.id=i.`invoice_id` AND v.receipt_id=$id";
		return $db->fetchAll($sql);
	}
	function getAllReciept($search){
			$db= $this->getAdapter();
			$sql=" SELECT r.id,
			(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
			(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS customer_name,
			r.`date_input`,
			r.`total`,r.`paid`,r.`balance`,r.`pol_no`,r.`expense_date`,r.`bank_acc`,total_pay,vat,is_get,is_paid,
			(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id`) AS payment_name,
			cheque_number,bank_name,withdraw_name,che_issuedate,che_withdrawaldate,
			(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
			(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name ,
			(SELECT p.`date_get` FROM `tb_pol_pay` AS p WHERE p.`pol_id`=r.id LIMIT 1) AS get_date,
			(SELECT p.`date_pay` FROM `tb_pol_pay` AS p WHERE p.`pol_id`=r.id LIMIT 1) AS paid_date
			FROM `tb_vendor_payment` AS r ";
			
			$from_date =(empty($search['start_date']))? '1': " r.`expense_date` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " r.`expense_date` <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`pol_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch']>0){
				$where .= " AND r.`branch_id` = ".$search['branch'];
			}
			if($search['suppliyer_id']>0){
				$where .= " AND r.vendor_id =".$search['suppliyer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	function getPaymentById($id){
		$db = $this->getAdapter();
		$sql ="SELECT r.id,
			(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
			(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS customer_name,
			(SELECT v.`v_phone` FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS v_phone,
			(SELECT v.`contact_name` FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS contact_name,
			(SELECT v.`email` FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS email,
			(SELECT v.`add_name` FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS add_name,
			
			(SELECT receive_invoice_date FROM `tb_invoice_controlling` WHERE invoice_id =v.invoice_id LIMIT 1 ) as invoice_received_date,			
			r.`date_input`,
			r.`total`,
			r.`paid`,
			r.`balance`,
			r.`pol_no`,
			r.`expense_date`,
			r.`bank_acc`,
			r.payment_id,
			r.remark,
			r.total_pay,
			r.vat AS total_vat,
			v.invoice_id,
			v.`sub_total`,
			v.`vat`,
			v.`total_vat` AS d_total_vat,
			v.`grand_total`,
			r.`branch_id`,
			r.vendor_id,
			(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id` LIMIT 1) AS payment_name,
			cheque_number,bank_name,withdraw_name,che_issuedate,che_withdrawaldate,
			(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
			(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name ,
			(SELECT p.`invoice_no` FROM `tb_purchase_invoice` AS p WHERE p.id=v.`invoice_id` LIMIT 1) AS invoice_no,
			(SELECT p.`invoice_date` FROM `tb_purchase_invoice` AS p WHERE p.id=v.`invoice_id` LIMIT 1) AS invoice_date,
			(SELECT p.`sub_total_after` FROM `tb_purchase_invoice` AS p WHERE p.id=v.`invoice_id` LIMIT 1) AS sub_total_after,
			(SELECT p.`paid_after` FROM `tb_purchase_invoice` AS p WHERE p.id=v.`invoice_id` LIMIT 1) AS paid_after,
			(SELECT p.`balance_after` FROM `tb_purchase_invoice` AS p WHERE p.id=v.`invoice_id` LIMIT 1) AS balance_after
			FROM 
				`tb_vendor_payment` AS r ,
				`tb_vendorpayment_detail` AS v 
			WHERE r.`id`=v.`receipt_id` AND r.id=$id";
			
			
			return $db->fetchAll($sql);
	}
	function getAllItemReceived($receipt_id){
		$db = $this->getAdapter();
		$sql="SELECT ri.*, 
					pv.invoice_no, pv.purchase_id,
					pv.invoice_date,
					pv.invoice_date_from_stock,
				   (ro.all_total-ro.net_total) AS total_tax, 
                   (SELECT vp.vat FROM `tb_vendor_payment` AS vp WHERE vp.id=iv.receipt_id LIMIT 1 )AS vat,
                   (SELECT vp.total FROM `tb_vendor_payment` AS vp WHERE vp.id=iv.receipt_id LIMIT 1 )AS total,

                   ro.all_total ,
				   ro.recieve_number,
				 (SELECT p.item_name FROM `tb_product` AS p WHERE p.id = ri.pro_id LIMIT 1) AS product_name, 
				 (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT pr.measure_id FROM `tb_product` AS pr WHERE 
				 pr.id = ri.pro_id LIMIT 1 ) LIMIT 1) AS measure 
				 FROM 
				     `tb_purchase_invoice` AS pv,
				 	  tb_purchase_invoice_detail AS pid,
				 	  tb_vendorpayment_detail AS iv, 
				  	 `tb_recieve_order` As ro, 
				     `tb_recieve_order_item` As ri 
				  WHERE 
				  pv.id=pid.invoice_id
				  AND pid.receive_id=ro.order_id
				  AND pv.id=iv.invoice_id 
				  AND iv.receipt_id= $receipt_id
				  AND ro.order_id= ri.recieve_id AND ro.purchase_id=pv.purchase_id
				  GROUP BY  pv.purchase_id,ro.order_id,ri.pro_id";
			  //groupby before:pv.id,ri.pro_id
	   return $db->fetchAll($sql);
	
	}
	function getRequestInfoBypurchaseId($purchase_id){
		$sql="SELECT r.re_code AS request_no,r.date_request,
		r.date_from_work_space,
		po.order_number
		FROM 
			`tb_purchase_request` as r,
			tb_purchase_order as po
		 WHERE po.id=$purchase_id and po.re_id=r.id";
		return $this->getAdapter()->fetchRow($sql);
	}
	
	function polpay($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$rs = $this->getPolById($data["id"]);
		$sql = "SELECT * FROM `tb_pol_pay` AS p WHERE p.`pol_id`=".$data["id"];
		$pol = $db->fetchRow($sql);
		try{
			$arr = array(
				'pol_id'		=>	$rs["id"],
				'pol_no'		=>	$rs["pol_no"],
				'sub_total'		=>	$rs["total"],
				'total_vat'		=>	$rs["vat"],
				'grand_total'	=>	$rs["subtotal_after"],
				'date_get'		=>	$data["date_get"],
				'date_pay'		=>	$data["date_pay"],
				'date'			=>	new Zend_date(),
				'status'		=>	1,
				'user_id'		=>	$GetUserId,
			);
			$this->_name ="tb_pol_pay";
			if(!empty($pol)){
				$where = "pol_id=".$data["id"];
				$this->update($arr,$where);
			}else{
				$this->insert($arr);
			}
			
			$is_get = 0;
			$is_paid =0;
			if($data["date_get"]!=""){
				$is_get = 1;
			}
			if($data["date_pay"]!=""){
				$is_paid = 1;
			}
			$arr_up = array(
				'is_paid'	=>	$is_paid,
				'is_get'	=>	$is_get,
			);
			$this->_name = "tb_vendor_payment";
			$where = "id=".$data["id"];
			$this->update($arr_up,$where);
			
			$db->commit();
			
			return 1;
		}catch(Exception $e){
			$db->rollBack();
			//Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function getPolById($id){
		$db = $this->getAdapter();
		$sql = "SELECT v.*,
			(SELECT p.`date_get` FROM `tb_pol_pay` AS p WHERE p.`pol_id`=v.id LIMIT 1) AS get_date,
			(SELECT p.`date_pay` FROM `tb_pol_pay` AS p WHERE p.`pol_id`=v.id LIMIT 1) AS paid_date
		FROM `tb_vendor_payment` AS v WHERE v.`id`=$id";
		return $db->fetchRow($sql);
	}
	public function addReceiptPayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$ids=explode(',',$data['identity']);
			$branch_id = '';
			
// 			foreach ($ids as $row){
// 				$branch_id = $this->getInvoiceByInvoiceId($data['invoice_no'.$row]);
// 				break;
// 			}
			//$data['receipt'] = $db_global->getReceiptNumber($branch_id['branch_id']);
			$branch_id=1;
			$info_purchase_order=array(
					"branch_id"   			=> 	$branch_id,
					'pol_no'				=>	$data["payment_number"],
					"vendor_id"   			=> 	$data["customer_id"],
					"payment_type"  		=> 	$data["payment_method"],//payment by customer/invoice
					"payment_id"    		=> 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"    		=> 	'',//$data['receipt'],
					"date_input"  			=>  date("Y-m-d"),
					//"che_issuedate"  		=>  date("Y-m-d",strtotime($data['cheque_issuedate'])),
					//"che_withdrawaldate"  	=>  date("Y-m-d",strtotime($data['cheque_withdrawdate'])),
					"expense_date"  		=>  date("Y-m-d",strtotime($data['expense_date'])),
					"total_pay"				=>	 $data['total'],
					"total"         		=> 	$data['all_total'],
					"vat"         			=> 	$data['total_vat'],
					"paid"          		=> 	$data['paid'],
					"balance"       		=> 	$data['balance'],
					"remark"        		=> 	$data['remark'],
					"user_id"       		=> 	$GetUserId,
					'status'        		=>	1,
					//"bank_name" 			=> 	$data['bank_name'],
					//"cheque_number" 		=> 	$data['cheque'],
					"withdraw_name" 		=> 	$data['holder_name'],
					'bank_acc'				=>	$data["acc_name"],
				 // "paid_dollar"   => 	$data['paid_dollar'],
// 				    "paid_riel"     => 	$data['paid_riel'],
				
			);
			$this->_name="tb_vendor_payment";
			
			$reciept_id = $this->insert($info_purchase_order); 
			
			
			unset($info_purchase_order);
// 			$ids=explode(',',$data['identity']);
			$count = count($ids);
			$paid = $data['paid'];
			$balance=$data['balance'];
			$compelted = 0;
			foreach ($ids as $key => $i)
			{
				
				$paid = $paid -($data['balance_after'.$i]);
				$recipt_paid = 0;
				if ($paid>=0){
					$paided = $data['balance_after'.$i];
					$balance=0;
					$compelted=1;
				}else{
					$paided = abs($paid);
					$balance= $data['balance_after'.$i]-abs($paid);
					$compelted=0;
				}
				
				
				$data_item= array(
						'receipt_id'		=>  $reciept_id,
						'invoice_id'		=> 	$data['invoice_no'.$i],
						'sub_total'			=>	$data['total'.$i],
						'vat'				=>	$data['vat'.$i],
						"total_vat"			=>	$data['total_vat'.$i],
						'grand_total'		=>	$data['balance_after'.$i],
// 						'discount'  		=> 	$data['discount'.$i],
						'paid'	  			=> 	$paided,
						'balance'		  	=> 	$balance,
						'is_completed'    	=>  $compelted,
						'status'  			=>  1,
						'date_input'	  	=>  date("Y-m-d"),
				);
				$this->_name='tb_vendorpayment_detail';
				
				$this->insert($data_item);
				$rs_invoice = $this->getInvoiceByInvoiceId($data['invoice_no'.$i]);
				
				if(!empty($rs_invoice)){
					
					$data_invoice = array(
								'paid'				=>$rs_invoice['paid']+$paided,
								//'discount_after'  => 	0,
								'paid_after'	  	=> 	$paided,
								'balance_after'	  	=> 	$balance,
								'grand_total_after' =>    $balance,
								'is_completed'	  	=> 	$compelted,
								);
					$this->_name='tb_invoice_controlling';
					$where = 'invoice_id = '.$data['invoice_no'.$i];
					
					$this->update($data_invoice, $where);
					
					/*$rsinvoice = $this->getPurchaseByInvoiceId($rs_invoice['purchase_id']);
					
					if(!empty($rsinvoice)){
						$pu_completed = 0;
						if(($rsinvoice['balance_after']-$paided)==0){
							$pu_completed = 1;
						}
						$data_invoice = array(
									'paid'			  =>	$rsinvoice['paid']+$paided,
									//'discount_after'  => 	0,
									'paid_after'	  => 	$paided,
									'balance_after'	  => 	$rsinvoice['balance_after']-$paided,
									'net_total_after' =>    $rsinvoice['net_total_after']-$paided,
									'is_completed'	  => 	$pu_completed,
									);
						$this->_name='tb_purchase_order';
						$where = 'id = '.$rs_invoice['purchase_id'];
						
						$this->update($data_invoice, $where);
						
					}
					
					$receive_invoice = $this->getReceiveByInvoiceId($rs_invoice['receive_id']);
					//print_r($receive_invoice);exit();
					if(!empty($receive_invoice)){
						$data_invoice = array(
									'paid'			  =>	$receive_invoice['paid']+$paided,
									//'discount_after'  => 	0,
									'paid_after'	  => 	$paided,
									'balance_after'	  => 	$balance,
									'net_total_after' =>    $balance,
									'is_completed'	  => 	$compelted,
									);
						$this->_name='tb_recieve_order';
						$where = 'order_id = '.$rs_invoice['receive_id'];
						
						$this->update($data_invoice, $where);
						*/
					}else{
						
					}
				}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updatePayment($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$ids=explode(',',$data['identity']);
			$branch_id = '';
			foreach ($ids as $row){
				$branch_id = $this->getInvoiceByInvoiceId($data['invoice_no'.$row]);
				break;
			}
			//$data['receipt'] = $db_global->getReceiptNumber($branch_id['branch_id']);
			
			$info_purchase_order=array(
// 					"branch_id"   			=> 	$branch_id['branch_id'],
					'pol_no'				=>	$data["payment_number"],
					"vendor_id"   			=> 	$data["customer_id"],
					"payment_type"  		=> 	$data["payment_method"],//payment by customer/invoice
					"payment_id"    		=> 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"    		=> 	'',//$data['receipt'],
					"date_input"  			=>  date("Y-m-d"),
					//"che_issuedate"  		=>  date("Y-m-d",strtotime($data['cheque_issuedate'])),
					//"che_withdrawaldate"  	=>  date("Y-m-d",strtotime($data['cheque_withdrawdate'])),
					"expense_date"  		=>  date("Y-m-d",strtotime($data['expense_date'])),
					"total_pay"				=>	 $data['total'],
					"total"         		=> 	$data['all_total'],
					"vat"         			=> 	$data['total_vat'],
					"paid"          		=> 	$data['paid'],
					"balance"       		=> 	$data['balance'],
					"remark"        		=> 	$data['remark'],
					"user_id"       		=> 	$GetUserId,
					'status'        		=>	1,
					//"bank_name" 			=> 	$data['bank_name'],
					//"cheque_number" 		=> 	$data['cheque'],
					"withdraw_name" 		=> 	$data['holder_name'],
					'bank_acc'				=>	$data["acc_name"],
				 // "paid_dollar"   => 	$data['paid_dollar'],
// 				    "paid_riel"     => 	$data['paid_riel'],
				
			);
// 			print_r($info_purchase_order);exit();
			$this->_name="tb_vendor_payment";
			$where = "id=".$data["id"];
			$reciept_id = $this->update($info_purchase_order,$where); 
			
			unset($info_purchase_order);
// 			$ids=explode(',',$data['identity']);
			$sql ="DELETE FROM tb_vendorpayment_detail WHERE receipt_id='".$data["id"]."'";
			$db->query($sql);
			$count = count($ids);
			$paid = $data['paid'];
			$compelted = 0;
			foreach ($ids as $key => $i)
			{
				$paid = $paid -($data['balance_after'.$i]);
				$recipt_paid = 0;
				if ($paid>=0){
					$paided = $data['balance_after'.$i];
					$balance=0;
					$compelted=1;
				}else{
					$paided = $data['paid'];
					$balance= $data['balance_after'.$i]-$data['paid'];
					$compelted=0;
				}
				$data_item= array(
						'receipt_id'		=>  $data["id"],
						'invoice_id'		=> 	$data['invoice_no'.$i],
						'sub_total'			=>	$data['total'.$i],
						'vat'				=>	$data['vat'.$i],
						"total_vat"			=>	$data['total_vat'.$i],
						'grand_total'		=>	$data['balance_after'.$i],
// 						'discount'  		=> 	$data['discount'.$i],
						'paid'	  			=> 	$paided,
						'balance'		  	=> 	$balance,
						'is_completed'    	=>  $compelted,
						'status'  			=>  1,
						'date_input'	  	=>  date("Y-m-d"),
				);
				$this->_name='tb_vendorpayment_detail';
				$this->insert($data_item);
				$rs_invoice = $this->getInvoiceByInvoiceId($data['invoice_no'.$row]);
				if(!empty($rs_invoice)){
					$data_invoice = array(
								'paid'				=>$rs_invoice['paid']+$paided,
								//'discount_after'  => 	0,
								'paid_after'	  	=> 	$paided,
								'balance_after'	  	=> 	$balance,
								'grand_total_after' =>    $balance,
								'is_completed'	  	=> 	1,
								);
					$this->_name='tb_invoice_controlling';
					$where = 'id = '.$data['invoice_no'.$row];
					$this->update($data_invoice, $where);
					/*$rsinvoice = $this->getPurchaseByInvoiceId($rs_invoice['purchase_id']);
					if(!empty($rsinvoice)){
						$pu_completed = 0;
						if(($rsinvoice['balance_after']-$paided)==0){
							$pu_completed = 1;
						}
						$data_invoice = array(
									'paid'			  =>	$rsinvoice['paid']+$paided,
									//'discount_after'  => 	0,
									'paid_after'	  => 	$paided,
									'balance_after'	  => 	$rsinvoice['balance_after']-$paided,
									'net_total_after' =>    $rsinvoice['net_total_after']-$paided,
									'is_completed'	  => 	$pu_completed,
									);
						$this->_name='tb_purchase_order';
						$where = 'id = '.$rs_invoice['purchase_id'];
						$this->update($data_invoice, $where);
					}
					$receive_invoice = $this->getReceiveByInvoiceId($rs_invoice['receive_id']);
					//print_r($receive_invoice);exit();
					if(!empty($receive_invoice)){
						$data_invoice = array(
									'paid'			  =>	$receive_invoice['paid']+$paided,
									//'discount_after'  => 	0,
									'paid_after'	  => 	$paided,
									'balance_after'	  => 	$balance,
									'net_total_after' =>    $balance,
									'is_completed'	  => 	$compelted,
									);
						$this->_name='tb_recieve_order';
						$where = 'order_id = '.$rs_invoice['receive_id'];
						$this->update($data_invoice, $where);
						*/
					}
				}
			// exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getPurchaseByInvoiceId($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `tb_purchase_order` AS p WHERE p.`id` = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getInvoiceByInvoiceId($id){
		$db = $this->getAdapter();
		//$sql="SELECT * FROM `tb_invoice_controlling` AS p WHERE p.`id` = $id LIMIT 1";//before
		$sql="SELECT * FROM `tb_invoice_controlling` AS p WHERE p.`invoice_id` = $id LIMIT 1";//just update 25/9/17 of other use too please create new above
		return $db->fetchRow($sql);
	}
	
	function getReceiveByInvoiceId($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `tb_recieve_order` AS p WHERE p.`order_id` = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getBranchByInvoice($invoice_id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `tb_purchase_order` AS p WHERE p.`id` = $invoice_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT d.`id`,d.`receipt_id`,d.`invoice_id`,
		( SELECT i.invoice_no FROM `tb_invoice` AS i  WHERE i.id = d.`invoice_id` ) AS invoice_no,
		d.`total`,d.`paid`,d.`balance`,d.`discount`,d.`date_input`
		FROM `tb_receipt_detail` AS d WHERE d.`receipt_id` =".$reciept_id;
		return $db->fetchAll($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
}