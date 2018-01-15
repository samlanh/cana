<?php

class Product_Model_DbTable_DbImportss extends Zend_Db_Table_Abstract
{

   // protected $_name = 'tb_category';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
	function letimport(){
		$db = $this->getAdapter();
		$sql = "SELECT s.id FROM `tb_sublocation` AS s ";
		$rs_loc = $db->fetchAll($sql);
		if($rs_loc){
			foreach($rs_loc as $row){
				$sql = "SELECT p.id FROM `tb_product` AS p ";
				$rs_pro = $db->fetchAll($sql);
				if($rs_pro){
					foreach($rs_pro as $row_pro){
						$sql = "SELECT p.id FROM `tb_prolocation` AS p WHERE p.`pro_id`='".$row_pro["id"]."' AND p.`location_id`='".$row["id"]."'";
						$rs_pl = $db->fetchOne($sql);
						if(empty($rs_pl)){
							$arr= array(
								'pro_id'			=> $row_pro["id"],
								'location_id'		=>	$row["id"],
								'last_mod_userid'	=>	$this->getUserId(),
								'last_mod_date'		=>	date("Y-m-d")
							);
							$this->_name = "tb_prolocation";
							$this->insert($arr);
						}
					}
				}
			}
		}
	}
	function getParentCat($title){
		$db = $this->getAdapter();
		$sql = "SELECT c.id FROM `tb_category` AS c WHERE c.`name` = '".$title."'";
		return $db->fetchOne($sql);
	}
	function getSubCategory($parent_id,$title){
		$db = $this->getAdapter();
		$sql = "SELECT c.id FROM `tb_category` AS c WHERE c.`name` = '".$title."'";
		return $db->fetchOne($sql);
	}
	function getMeasure($title){
		$db = $this->getAdapter();
		$sql = "SELECT m.`id` FROM `tb_measure` AS m WHERE m.`name`= '".$title."'";
		return $db->fetchOne($sql);
	}
	function getProduct($cate_title,$title){
		$db = $this->getAdapter();
		$sql = "SELECT p.`id` FROM `tb_product` AS p WHERE p.`cate_id`='".$cate_title."' AND p.`item_name`= '".$title."'";
		return $db->fetchOne($sql);
	}
	
	function productImport($data){
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$count = count($data);
		$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
		try{
			for($i=1; $i<=$count; $i++){
				$rs_paren = $this->getParentCat($data[$i]['C']);
				if(empty($rs_paren)){
					$arr_parent = array(
						'name'	=>	$data[$i]['C'],
						'status'	=>	1,
						'parent_id'	=>	0,
						'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
						'start_code'=>	$data[$i]['E'],
						'prefix'	=>	$data[$i]['N'],
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_category";
					$parent_id = $this->insert($arr_parent);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}else{
					$parent_id = $rs_paren;
				}
				
				$rs_sub_cat = $this->getSubCategory($parent_id,$data[$i]['D']);
				if(empty($rs_sub_cat)){
					$arr_sub = array(
						'name'	=>	$data[$i]['D'],
						'status'	=>	1,
						'parent_id'	=>	$parent_id,
						'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
						'start_code'=>	$data[$i]['E'],
						'prefix'	=>	$data[$i]['O'],
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_category";
					$sub_id = $this->insert($arr_sub);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				//echo count($rs_sub_cat);
				}else{
					$sub_id = $rs_sub_cat;
				}
				
				
				$rs_measure = $this->getMeasure($data[$i]['G']);
				if(empty($rs_measure)){
					$arr_measure = array(
						'name'	=>	$data[$i]['G'],
						'status'	=>	1,
						'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_measure";
					$measur_id = $this->insert($arr_measure);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				//echo count($rs_sub_cat);
				}else{
					$measur_id = $rs_measure;
				}
				
				/*if($data[$i]['L']=="Use"){
					$status = 1;
				}else{
					$status = 0;
				}*/
				$rs_product = $this->getProduct($data[$i]['D'],$data[$i]['B']);
				if(empty($rs_product)){
					$arr_product = array(
						'item_name'	=>	$data[$i]['B'],
						'item_code'	=>	$data[$i]['A'],
						//'note'		=>	$data[$i]['G'],
						'measure_id'=>	$measur_id,
						'cate_id'	=>	$sub_id,
						'status'	=>	1,
						'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
						//'is_convertor'	=>	$data[$i]['P'],
						//'convertor_measure'	=>	$data[$i]['Q'],
						//'sign'		=> $data[$i]['R'],
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_product";
					$pro_id = $this->insert($arr_product);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				//echo count($rs_sub_cat);
				}else{
					$pro_id = $rs_product;
				}
				
			}
		//exit();	
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		echo $e->getMessage();exit();
		}
	}
	
	
	function getProductByCode($code){
		$db = $this->getAdapter();
		$sql = "SELECT p.id FROM `tb_product` AS p WHERE p.`item_code`= '".$code."'";
		return $db->fetchOne($sql);
	}
	function getLocation($code){
		$db = $this->getAdapter();
		$sql = "SELECT s.id FROM `tb_sublocation` AS s WHERE s.`name`= '".$code."'";
		return $db->fetchOne($sql);
	}
	function ProLocation($data){
		
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$count = count($data);
		$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
		try{
			for($i=2; $i<=$count; $i++){
				$rs_pro = $this->getProductByCode($data[$i]['A']);
				if(empty($rs_pro)){
					$arr_product = array(
						'item_name'	=>	$data[$i]['B'],
						'item_code'	=>	$data[$i]['A'],
						'note'		=>	$data[$i]['G'],
						//'measure_id'=>	$measur_id,
						//'cate_id'	=>	$sub_id,
						'status'	=>	1,
						'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_product";
					$pro_id = $this->insert($arr_product);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}else{
					$pro_id = $rs_pro;
				}
				
				/*$rs_loc = $this->getLocation($data[$i]['G']);
				if(empty($rs_loc)){
					$arr_loc = array(
						'name'				=>	$data[$i]['G'],
						'branch_code'		=>	$data[$i]['I'],
						'contact'			=>	$data[$i]['H'],
						'phone'				=>	$data[$i]['K'],
						'address'			=>	$data[$i]['J'],
						'email'				=>	$data[$i]['L'],
						//'office_tel'		=>  $data[$i]['H'],
						'fax'				=>	$data[$i]['M'],
						'status'			=>	1,
						'user_id'			=>	$GetUserId,	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_sublocation";
					$loc_id = $this->insert($arr_loc);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}else{
					$loc_id = $rs_loc;
				}*/
				
				$arr_pro_loc = array(
					'pro_id'			=>	$pro_id,
					'location_id'		=>	1,
					'qty'				=>	number_format($data[$i]['D'],2),
					'qty_warning'		=>	0,
					'price'				=>	$data[$i]['F'],
					'damaged_qty'		=>	0,
					'last_mod_userid'	=>	$GetUserId,	
				);
				$db->getProfiler()->setEnabled(true);
				$this->_name = "tb_prolocation";
				$this->insert($arr_pro_loc);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				
				
			}
		//exit();	
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		echo $e->getMessage();exit();
		}
	}
	
	function acl($data){
		
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$count = count($data);
		$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
		try{
			for($i=2; $i<=$count; $i++){
				
					$arr_product = array(
						'module'		=>	preg_replace('/\s+/','',$data[$i]['B']),
						'controller'	=>	preg_replace('/\s+/','',$data[$i]['C']),
						'action'		=>	preg_replace('/\s+/','',$data[$i]['D']),
						'status'		=>	preg_replace('/\s+/','',$data[$i]['E']),
						'rank'			=>	preg_replace('/\s+/','',$data[$i]['F']),
						'lable'			=>	preg_replace('/\s+/','',$data[$i]['G']),
						'parent'		=>	preg_replace('/\s+/','',$data[$i]['H']),
						'sub_parent'	=>	preg_replace('/\s+/','',$data[$i]['I']),	
						'is_parent'		=>	preg_replace('/\s+/','',$data[$i]['J']),	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_acl_acl";
					$pro_id = $this->insert($arr_product);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				
			}
		//exit();	
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		echo $e->getMessage();exit();
		}
	}
	
	function vendor($data){
		
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$count = count($data);
		$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
		try{
			for($i=2; $i<=$count; $i++){
				//$rs_pro = $this->getProductByCode($data[$i]['A']);
				if(empty($rs_pro)){
					$arr_product = array(
						'v_no'		=>	$data[$i]['A'],
						'invoice_name'	=>	$data[$i]['D'],
						'v_name'		=>	$data[$i]['C'],
						'bank_name'=>	$data[$i]['E'],
						'v_phone'	=>	$data[$i]['H'],
						'bank_no'	=>	$data[$i]['F'],
						'bts'		=>	$data[$i]['G'],
						'status'	=>	1,
						'date'		=>	date("Y-m-d",strtotime($data[$i]['B'])),
						'date'		=>	$data[$i]['B'],
						'last_usermod'	=>	$GetUserId,	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_vendor";
					$pro_id = $this->insert($arr_product);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}else{
					$pro_id = $rs_pro;
				}
				
			}
		//exit();	
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		echo $e->getMessage();exit();
		}
}
//     public function AddCateByImport($data){ // first Optioin
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$count = count($data);
//     	try{
//     		$k=76;
//     		$propertycode=76;
//     	for($i=1; $i<=$count; $i++){
//     		$k=$k+1;
//     		$propertycode = $propertycode+1;
//     		$rs = $this->getCustomer($data[$i]['B']);
//     		if(empty($rs)){
//     			if($data[$i]['D']=="ប្រុស"){$sex=1;}else{ $sex=2;}
//     			$client_arr_ =array(
//     					'branch_id'=>1,
//     					'name_en'=>$data[$i]['B'],
//     					'name_kh'=>$data[$i]['B'],
//     					'sex'=>$sex,
//     					'pro_id'=>12,
//     					'phone'=>$data[$i]['C'],
//     			);
//     			$this->_name='ln_client';
//     			$client_id = $this->insert($client_arr_);
//     		}else{
//     			$client_id=$rs;
//     		}
    		
//     		$address=explode(" ", $data[$i]['J']);
//     		$proper_row = $this->getProperty($address[0], str_replace(")","",str_replace("(","",$address[1])));
//     		if (empty($proper_row)){
// 	    		$property_arr =array(
// 	    				'branch_id'=>1,
// 	    				'land_code'=>"PRO-".$propertycode,
// 	    				'street'=>str_replace(")","",str_replace("(","",$address[1])),
// 	    				'land_address'=>$address[0],
// 	    				'land_price'=>0,
// 	    				'house_price'=>$data[$i]['K'],
// 	    				'price'=>$data[$i]['K'],
// 	    				'property_type'=>1,
// 	    		);
// 	    		$this->_name='ln_properties';
// 	    		$property_id = $this->insert($property_arr);
//     		}else{
//     			$property_id = $proper_row;
//     		}
    		
    		
//     		$staff_id = $this->getStaffId($data[$i]['AG']);
//     		if(empty($staff_id)){
// 	    		$staff_arr =array(
// 	    				'branch_id'=>1,
// 	    				'co_code'=>"STAFF-".$i,
// 	    				'co_khname'=>$data[$i]['AG'],
// 	    				'sex'=>1,
// 	    		);
// 	    		$this->_name='ln_staff';
// 	    		$staff_id = $this->insert($staff_arr);
//     		}
    		
//     		$create_date=$data[$i]['E'];
//     		$payment_dura = $data[$i]['H'];
//     		$create_date=$data[$i]['E'];
//     		$sale_arr =array(
//     				'branch_id'=>1,
//     				'sale_number'=>$data[$i]['A'],
//     				'receipt_no'=>$this->getReceiptByBranch(),
//     				'client_id'=>$client_id,
//     				'house_id'=>$property_id,
//     				'price_before'=>$data[$i]['K'],
//     				'price_sold'=>$data[$i]['K'],
//     				'other_fee'=>0,
//     				'paid_amount'=>0,
//     				'discount_percent'=>0,
//     				'discount_amount'=>0,
//     				'balance'=>$data[$i]['K'],
//     				'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     				'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     				'interest_rate'=>0,
//     				'total_duration'=>$data[$i]['H'],
//     			   	'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     				'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     			   	'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     				'payment_method'=>1,//$data['loan_type'],
//     				'payment_id'=>4,//រំលស់
//     			   	'land_price'=>$data[$i]['K'],
//     			   	'total_installamount'=>$data[$i]['H'],
    				
//     				'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
//     				'staff_id'=>$staff_id,
//     				'comission'=>$data[$i]['H'],
//     				'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     		);
//     		$this->_name='ln_sale';
//     		$sale_id = $this->insert($sale_arr);
//     		$sale_id =$i;
//     		$sale_id = $k;
    		
//     		$property_update = array(
//     				'is_lock'=>1,
//     		);
//     		$this->_name="ln_properties";
//     		$where="id = ".$property_id;
//     		$this->update($property_update, $where);
    		
//     		$array_new = array(
//     				1=>$data[$i]['L'],//payment
//     				2=>$data[$i]['M'],
//     				3=>$data[$i]['N'],
//     				4=>$data[$i]['O'],
//     				5=>$data[$i]['P'],
//     				6=>$data[$i]['Q'],
//     				7=>$data[$i]['R'],
//     				8=>$data[$i]['S'],
//     				9=>$data[$i]['T'],
//     				10=>$data[$i]['U'],
//     				11=>$data[$i]['V'],
//     				12=>$data[$i]['W'],
//     				13=>$data[$i]['X'],
//     				14=>$data[$i]['Y'],
//     				15=>$data[$i]['Z'],
//     				16=>$data[$i]['AA'],
//     		);
//     		$beginning_balance= $data[$i]['K'];
//     		$beginning_balance_after=0;
//     		$principal_permonth=0;
//     		$principal_permonthafter=0;
//     		$total_interest=0;
//     		$total_interest_after=0;
//     		$total_payment_after=0;
//     		$ending_balance=0;
//     		$cum_interest=0;
//     		$amount_day=0;
    		 
//     		$old_paid=0;
    		 
//     		$isset=0;
//     		$paid_install = $data[$i]['AB'];
//    			 $rs = $array_new;
//    			$old_paid_aftercondic =0;
//    			$ccc='';
//     		for($index=1; $index<=count($rs); $index++){
// //     			echo $rs[$index];exit();
//     			if ($index>=$paid_install){
//     				if($paid_install<=6){
//     					$rs[$index]=$old_paid;
//     					$paid_install++;
    		
//     				}else{
// //     					if($paid_install>6){
// //     						$rs[$index]=$old_paid;
// //     					}else{
// 	    					if($isset==0){
// 	    						//
// 	    						if($data[$i]['AB']>6){
// 	    							$old_paid_aftercondic=$array_new[$data[$i]['AB']];//check if default <6
// 	    						}else{
// 	    							$old_paid_aftercondic = round(($data[$i]['K']-($data[$i]['L']*6))/36);
// 	    						}
// 	    						$rs[$index]= $old_paid_aftercondic;
// 	    						$isset=1;
// 	    					}else{
// 	    						$rs[$index]= $old_paid_aftercondic;
// 	    					}
// //     					}
//     				}
//     			}
//     			if($index>1){
//     				$beginning_balance = $ending_balance;
//     			}
//     			$ending_balance = $beginning_balance-$rs[$index];
//     			$sale_schedule_id = array(
//     					'branch_id'=>1,
//     					'sale_id'=>$sale_id,
//     					'begining_balance'=>$beginning_balance,//good
//     					'begining_balance_after'=> $beginning_balance,//good
//     					'principal_permonth'=> $rs[$index],//good
//     					'principal_permonthafter'=>$rs[$index],//good
//     					'total_interest'=>$total_interest,//good
//     					'total_interest_after'=>$total_interest_after,//good
//     					'total_payment'=>$rs[$index],//good
//     					'total_payment_after'=>$rs[$index],//good
//     					'ending_balance'=>$ending_balance,
//     					'cum_interest'=>$cum_interest,
//     					'amount_day'=>$amount_day,
//     					'is_completed'=>0,
//     					'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     					'no_installment'=>$index,
//     			);
//     			$this->_name='ln_saleschedule';
//     			$old_paid = $rs[$index];
//     			$sale_sche_id=$this->insert($sale_schedule_id);
    			
//     			if ($index<=$data[$i]['AB']){
// 	    			$receipt_money = array(
// 	    					'branch_id'			=>1,
// 	    					'client_id'			=>$client_id,
// 	    					'receipt_no'		=>$this->getReceiptByBranch(),
// 	    					'date_pay'			=>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'land_id'			=>$property_id,
// 	    					'sale_id'			=>$sale_id,
// 	    					'date_input'		=>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'outstanding'		=> $beginning_balance,
// 	    					'principal_amount'	=> $beginning_balance-$rs[$index],
// 	    					'total_principal_permonth'	=>$rs[$index],
// 	    					'total_principal_permonthpaid'=>$rs[$index],
// 	    					'total_interest_permonth'	=>0,
// 	    					'total_interest_permonthpaid'=>0,
// 	    					'penalize_amount'			=>0,
// 	    					'penalize_amountpaid'		=>0,
// 	    					'service_charge'	=>0,
// 	    					'service_chargepaid'=>0,
// 	    					'total_payment'		=>$rs[$index],
// 	    					'amount_payment'	=>$rs[$index],
// 	    					'recieve_amount'	=>$rs[$index],
// 	    					'balance'			=>0,
// 	    					'payment_option'	=>1,
// 	    					'is_completed'		=>1,
// 	    					'status'			=>1,
// 	    					'user_id'			=>1,
// 	    					'field3'			=>1
// 	    			);
// 	    			$this->_name='ln_client_receipt_money';
// 	    			$receipt_id = $this->insert($receipt_money);
	    			
// 	    			$this->_name='ln_client_receipt_money_detail';
// 	    			$reilcei_money_deta = array(
// 	    					'crm_id'				=>$receipt_id,
// 	    					'lfd_id'				=>$sale_sche_id,
// 	    					'client_id'				=>$client_id,
// 	    					'land_id'				=>$property_id,
// 	    					'date_payment'			=>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'paid_date'             =>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'capital'				=>$beginning_balance,
// 	    					'remain_capital'		=>$beginning_balance-$rs[$index],
// 	    					'principal_permonth'	=>$rs[$index],
// 	    					'total_interest'		=>0,
// 	    					'total_payment'			=>$rs[$index],
// 	    					'total_recieve'			=>$rs[$index],
// 	    					'service_charge'		=>0,
// 	    					'penelize_amount'		=>0,
// 	    					'is_completed'			=>1,
// 	    					'status'				=>1,
// 	    					'old_interest'			 =>0,
// 	    					'old_principal_permonth'=>0,
// 	    					'old_total_payment'	 =>0,
// 	    					//     					'old_total_priciple'	=>		$data["old_total_priciple_".$i],
// 	    			);
// 	    			$this->insert($reilcei_money_deta);
// 				    $sale_sche_update = array(
// 				    //     							"principal_permonthafter"=>$remain_principal,
// 				    //     							'total_interest_after'=>$total_interestafter,
// 				    //     							'begining_balance_after'=>$row['begining_balance_after']-($data['deposit']-$total_interest),
// 				    //     							'ending_balance'=>$row['begining_balance_after']-($data['deposit']-$total_interest)-$remain_principal,//check again
// 				    		'is_completed'=>1,
// 				    		'paid_date'			=> 	date("Y-m-d",strtotime("$create_date + $index month")),
// 				    		//     							'total_payment_after'	=>	$pyament_after,
// 				    );
// 				    $this->_name="ln_saleschedule";
// 				    $where="id = ".$sale_sche_id;
// 				    $this->update($sale_sche_update, $where);
//     			}
//     			if ($index==16){
//     				$index=$index+1;
//     				while ($index<=42){
//     					$beginning_balance = $ending_balance;
//     					$ending_balance = $beginning_balance-$old_paid;
    						
//     					if ($index==42){
//     						$old_paid = $old_paid+$ending_balance;
//     						$ending_balance=0;
//     					}
//     					$sale_schedule_id = array(
//     							'branch_id'=>1,
//     							'sale_id'=>$sale_id,
//     							'begining_balance'=>$beginning_balance,//good
//     							'begining_balance_after'=> $beginning_balance,//good
//     							'principal_permonth'=> $old_paid,//good
//     							'principal_permonthafter'=>$old_paid,//good
//     							'total_interest'=>$total_interest,//good
//     							'total_interest_after'=>$total_interest_after,//good
//     							'total_payment'=>$old_paid,//good
//     							'total_payment_after'=>$old_paid,//good
//     							'ending_balance'=>$ending_balance,
//     							'cum_interest'=>$cum_interest,
//     							'amount_day'=>$amount_day,
//     							'is_completed'=>0,
//     							'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'no_installment'=>$index,
//     					);
//     					$this->_name='ln_saleschedule';
//     					$this->insert($sale_schedule_id);
//     					$index++;
//     				}
//     			}
//     		}
    		
//     	}//end main loop
//     	$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     	}
//     }
// 	public function AddCateByImport($data){//Second Option
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$count = count($data);
//     	try{
//     		$k=151;
//     		$propertycode = 151;
//     	for($i=1; $i<=$count; $i++){
//     		$k = $k+1;
//     		$propertycode = $propertycode+1;
//     		$rs = $this->getCustomer($data[$i]['B']);
//     		if(empty($rs)){
//     			if($data[$i]['D']=="ប្រុស"){$sex=1;}else{ $sex=2;}
//     			$client_arr_ =array(
//     					'branch_id'=>1,
//     					'name_en'=>$data[$i]['B'],
//     					'name_kh'=>$data[$i]['B'],
//     					'sex'=>$sex,
//     					'pro_id'=>12,
//     					'phone'=>$data[$i]['C'],
//     			);
//     			$this->_name='ln_client';
//     			$client_id = $this->insert($client_arr_);
//     		}else{
//     			$client_id=$rs;
//     		}
//     		$address=explode(" ", $data[$i]['J']);
//     		$proper_row = $this->getProperty($address[0], str_replace(")","",str_replace("(","",$address[1])));
//     		if (empty($proper_row)){
// 	    		$property_arr =array(
// 	    				'branch_id'=>1,
// 	    				'land_code'=>"PRO-".$propertycode,
// 	    				'street'=>str_replace(")","",str_replace("(","",$address[1])),
// 	    				'land_address'=>$address[0],
// 	    				'land_price'=>0,
// 	    				'house_price'=>$data[$i]['K'],
// 	    				'price'=>$data[$i]['K'],
// 	    				'property_type'=>1,
// 	    		);
// 	    		$this->_name='ln_properties';
// 	    		$property_id = $this->insert($property_arr);
//     		}else{
//     			$property_id = $proper_row;
//     		}
    		
//     		$staff_id = $this->getStaffId($data[$i]['AG']);
//     		if(empty($staff_id)){
// 	    		$staff_arr =array(
// 	    				'branch_id'=>1,
// 	    				'co_code'=>"STAFF-".$i,
// 	    				'co_khname'=>$data[$i]['AG'],
// 	    				'sex'=>1,
// 	    		);
// 	    		$this->_name='ln_staff';
// 	    		$staff_id = $this->insert($staff_arr);
//     		}
    		
//     		$create_date=$data[$i]['E'];
//     		$payment_dura = $data[$i]['H'];
//     		$create_date=$data[$i]['E'];
//     		$sale_arr =array(
//     				'branch_id'=>1,
//     				'sale_number'=>$data[$i]['A'],
//     				'receipt_no'=>$this->getReceiptByBranch(),
//     				'client_id'=>$client_id,
//     				'house_id'=>$property_id,
//     				'price_before'=>$data[$i]['K'],
//     				'price_sold'=>$data[$i]['K'],
//     				'other_fee'=>0,
//     				'paid_amount'=>0,
//     				'discount_percent'=>0,
//     				'discount_amount'=>0,
//     				'balance'=>$data[$i]['K'],
//     				'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     				'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     				'interest_rate'=>0,
//     				'total_duration'=>$data[$i]['H'],
//     			   	'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     				'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     			   	'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     				'payment_method'=>1,//$data['loan_type'],
//     				'payment_id'=>4,//រំលស់
//     			   	'land_price'=>$data[$i]['K'],
//     			   	'total_installamount'=>$data[$i]['H'],
    				
//     				'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
//     				'staff_id'=>$staff_id,
//     				'comission'=>$data[$i]['H'],
//     				'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     		);
//     		$this->_name='ln_sale';
//     		$sale_id = $this->insert($sale_arr);
// //     		$sale_id =$i;
//     		$sale_id =$k;
    		
//     		$property_update = array(
//     		    				'is_lock'=>1,
//     		);
//     		$this->_name="ln_properties";
//     		$where="id = ".$property_id;
//     		$this->update($property_update, $where);
    		
//     		$array_new = array(
//     				1=>$data[$i]['L'],//payment
//     				2=>$data[$i]['M'],
//     				3=>$data[$i]['N'],
//     				4=>$data[$i]['O'],
//     				5=>$data[$i]['P'],
//     				6=>$data[$i]['Q'],
//     				7=>$data[$i]['R'],
//     				8=>$data[$i]['S'],
//     				9=>$data[$i]['T'],
//     				10=>$data[$i]['U'],
//     				11=>$data[$i]['V'],
//     				12=>$data[$i]['W'],
//     				13=>$data[$i]['X'],
//     				14=>$data[$i]['Y'],
//     				15=>$data[$i]['Z'],
//     				16=>$data[$i]['AA'],
//     		);
//     		$beginning_balance= $data[$i]['K'];
//     		$beginning_balance_after=0;
//     		$principal_permonth=0;
//     		$principal_permonthafter=0;
//     		$total_interest=0;
//     		$total_interest_after=0;
//     		$total_payment_after=0;
//     		$ending_balance=0;
//     		$cum_interest=0;
//     		$amount_day=0;
    		 
//     		$old_paid=0;
    		 
//     		$isset=0;
//     		$paid_install = $data[$i]['AB'];
//    			 $rs = $array_new;
//    			$old_paid_aftercondic =0;
//    			$ccc='';
//     		for($index=1; $index<=count($rs); $index++){
// //     			echo $rs[$index];exit();
//     			if ($index>=$paid_install){
//     				if($paid_install<=3){
//     					$rs[$index]=$old_paid;
//     					$paid_install++;
    		
//     				}else{
// 	    					if($isset==0){
// 	    						//
// 	    						if($data[$i]['AB']>3){
// 	    							$old_paid_aftercondic=$array_new[$data[$i]['AB']];//check if default <6
// 	    						}else{
// 	    							$old_paid_aftercondic = round(($data[$i]['K']-($data[$i]['L']*3))/36);
// 	    						}
// 	    						$rs[$index]= $old_paid_aftercondic;
// 	    						$isset=1;
// 	    					}else{
// 	    						$rs[$index]= $old_paid_aftercondic;
// 	    					}
//     				}
//     			}
//     			if($index>1){
//     				$beginning_balance = $ending_balance;
//     			}
//     			$ending_balance = $beginning_balance-$rs[$index];
//     			$sale_schedule_id = array(
//     					'branch_id'=>1,
//     					'sale_id'=>$sale_id,
//     					'begining_balance'=>$beginning_balance,//good
//     					'begining_balance_after'=> $beginning_balance,//good
//     					'principal_permonth'=> $rs[$index],//good
//     					'principal_permonthafter'=>$rs[$index],//good
//     					'total_interest'=>$total_interest,//good
//     					'total_interest_after'=>$total_interest_after,//good
//     					'total_payment'=>$rs[$index],//good
//     					'total_payment_after'=>$rs[$index],//good
//     					'ending_balance'=>$ending_balance,
//     					'cum_interest'=>$cum_interest,
//     					'amount_day'=>$amount_day,
//     					'is_completed'=>0,
//     					'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     					'no_installment'=>$index,
//     			);
//     			$this->_name='ln_saleschedule';
//     			$old_paid = $rs[$index];
//     			$sale_sche_id=$this->insert($sale_schedule_id);
    			
//     			if ($index<=$data[$i]['AB']){
// 	    			$receipt_money = array(
// 	    					'branch_id'			=>1,
// 	    					'client_id'			=>$client_id,
// 	    					'receipt_no'		=>$this->getReceiptByBranch(),
// 	    					'date_pay'			=>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'land_id'			=>$property_id,
// 	    					'sale_id'			=>$sale_id,
// 	    					'date_input'		=>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'outstanding'		=> $beginning_balance,
// 	    					'principal_amount'	=> $beginning_balance-$rs[$index],
// 	    					'total_principal_permonth'	=>$rs[$index],
// 	    					'total_principal_permonthpaid'=>$rs[$index],
// 	    					'total_interest_permonth'	=>0,
// 	    					'total_interest_permonthpaid'=>0,
// 	    					'penalize_amount'			=>0,
// 	    					'penalize_amountpaid'		=>0,
// 	    					'service_charge'	=>0,
// 	    					'service_chargepaid'=>0,
// 	    					'total_payment'		=>$rs[$index],
// 	    					'amount_payment'	=>$rs[$index],
// 	    					'recieve_amount'	=>$rs[$index],
// 	    					'balance'			=>0,
// 	    					'payment_option'	=>1,
// 	    					'is_completed'		=>1,
// 	    					'status'			=>1,
// 	    					'user_id'			=>1,
// 	    					'field3'			=>1
// 	    			);
// 	    			$this->_name='ln_client_receipt_money';
// 	    			$receipt_id = $this->insert($receipt_money);
	    			
// 	    			$this->_name='ln_client_receipt_money_detail';
// 	    			$reilcei_money_deta = array(
// 	    					'crm_id'				=>$receipt_id,
// 	    					'lfd_id'				=>$sale_sche_id,
// 	    					'client_id'				=>$client_id,
// 	    					'land_id'				=>$property_id,
// 	    					'date_payment'			=>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'paid_date'             =>date("Y-m-d",strtotime("$create_date + $index month")),
// 	    					'capital'				=>$beginning_balance,
// 	    					'remain_capital'		=>$beginning_balance-$rs[$index],
// 	    					'principal_permonth'	=>$rs[$index],
// 	    					'total_interest'		=>0,
// 	    					'total_payment'			=>$rs[$index],
// 	    					'total_recieve'			=>$rs[$index],
// 	    					'service_charge'		=>0,
// 	    					'penelize_amount'		=>0,
// 	    					'is_completed'			=>1,
// 	    					'status'				=>1,
// 	    					'old_interest'			 =>0,
// 	    					'old_principal_permonth'=>0,
// 	    					'old_total_payment'	 =>0,
// 	    					//     					'old_total_priciple'	=>		$data["old_total_priciple_".$i],
// 	    			);
// 	    			$this->insert($reilcei_money_deta);
// 				    $sale_sche_update = array(
// 				    //     							"principal_permonthafter"=>$remain_principal,
// 				    //     							'total_interest_after'=>$total_interestafter,
// 				    //     							'begining_balance_after'=>$row['begining_balance_after']-($data['deposit']-$total_interest),
// 				    //     							'ending_balance'=>$row['begining_balance_after']-($data['deposit']-$total_interest)-$remain_principal,//check again
// 				    		'is_completed'=>1,
// 				    		'paid_date'			=> 	date("Y-m-d",strtotime("$create_date + $index month")),
// 				    		//     							'total_payment_after'	=>	$pyament_after,
// 				    );
// 				    $this->_name="ln_saleschedule";
// 				    $where="id = ".$sale_sche_id;
// 				    $this->update($sale_sche_update, $where);
//     			}
//     			if ($index==16){
//     				$index=$index+1;
//     				while ($index<=39){
//     					$beginning_balance = $ending_balance;
//     					$ending_balance = $beginning_balance-$old_paid;
    						
//     					if ($index==39){
//     						$old_paid = $old_paid+$ending_balance;
//     						$ending_balance=0;
//     					}
//     					$sale_schedule_id = array(
//     							'branch_id'=>1,
//     							'sale_id'=>$sale_id,
//     							'begining_balance'=>$beginning_balance,//good
//     							'begining_balance_after'=> $beginning_balance,//good
//     							'principal_permonth'=> $old_paid,//good
//     							'principal_permonthafter'=>$old_paid,//good
//     							'total_interest'=>$total_interest,//good
//     							'total_interest_after'=>$total_interest_after,//good
//     							'total_payment'=>$old_paid,//good
//     							'total_payment_after'=>$old_paid,//good
//     							'ending_balance'=>$ending_balance,
//     							'cum_interest'=>$cum_interest,
//     							'amount_day'=>$amount_day,
//     							'is_completed'=>0,
//     							'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'no_installment'=>$index,
//     					);
//     					$this->_name='ln_saleschedule';
//     					$this->insert($sale_schedule_id);
//     					$index++;
//     				}
//     			}
//     		}
    		
//     	}//end main loop
//     	$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     	}
//     }
//     public function AddCateByImport($data){//បង់ថេរ
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$count = count($data);
//     	try{
//     		//print_r($data);exit();
//     		$k=172;
//     		$propertycode = 172;
//     		for($i=1; $i<=$count; $i++){
//     			$k=$k+1;
//     			$propertycode = $propertycode+1;
//     			$rs = $this->getCustomer($data[$i]['B']);
//     			if(empty($rs)){
//     				if($data[$i]['D']=="ប្រុស"){
//     					$sex=1;
//     				}else{ $sex=2;
//     				}
//     				$client_arr_ =array(
//     						'branch_id'=>1,
//     						'name_en'=>$data[$i]['B'],
//     						'name_kh'=>$data[$i]['B'],
//     						'sex'=>$sex,
//     						'pro_id'=>12,
//     						'phone'=>$data[$i]['C'],
//     				);
//     				$this->_name='ln_client';
//     				$client_id = $this->insert($client_arr_);
//     			}else{
//     				$client_id=$rs;
//     			}
//     			$address=explode(" ", $data[$i]['J']);
//     			$proper_row = $this->getProperty($address[0], str_replace(")","",str_replace("(","",$address[1])));
//     			if (empty($proper_row)){
// 	    			$property_arr =array(
// 	    					'branch_id'=>1,
// 	    					'land_code'=>"PRO-".$propertycode,
// 	    					'street'=>str_replace(")","",str_replace("(","",$address[1])),
// 	    					'land_address'=>$address[0],
// 	    					'land_price'=>0,
// 	    					'house_price'=>$data[$i]['K'],
// 	    					'price'=>$data[$i]['K'],
// 	    					'property_type'=>1,
// 	    			);
// 	    			$this->_name='ln_properties';
// 	    			$property_id = $this->insert($property_arr);
//     			}else{ $property_id = $proper_row;}
    
//     			$staff_id = $this->getStaffId($data[$i]['AG']);
//     			if(empty($staff_id)){
//     				$staff_arr =array(
//     						'branch_id'=>1,
//     						'co_code'=>"STAFF-".$i,
//     						'co_khname'=>$data[$i]['AG'],
//     						'sex'=>1,
//     				);
//     				$this->_name='ln_staff';
//     				$staff_id = $this->insert($staff_arr);
//     			}
    
//     			$create_date=$data[$i]['E'];
//     			$payment_dura = $data[$i]['H'];
//     			$create_date=$data[$i]['E'];
//     			$sale_arr =array(
//     					'branch_id'=>1,
//     					'sale_number'=>$data[$i]['A'],
//     					'receipt_no'=>$this->getReceiptByBranch(),
//     					'client_id'=>$client_id,
//     					'house_id'=>$property_id,
//     					'price_before'=>$data[$i]['K'],
//     					'price_sold'=>$data[$i]['K'],
//     					'other_fee'=>0,
//     					'paid_amount'=>0,
//     					'discount_percent'=>0,
//     					'discount_amount'=>0,
//     					'balance'=>$data[$i]['K'],
//     					'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     					'interest_rate'=>0,
//     					'total_duration'=>$data[$i]['H'],
//     					'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     					'payment_method'=>1,//$data['loan_type'],
//     					'payment_id'=>4,//រំលស់
//     					'land_price'=>$data[$i]['K'],
//     					'total_installamount'=>$data[$i]['H'],
    
//     					'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
//     					'staff_id'=>$staff_id,
//     					'comission'=>$data[$i]['H'],
//     					'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     			);
//     			$this->_name='ln_sale';
//     			$sale_id = $this->insert($sale_arr);
// //     			$sale_id =$i;
// 				$sale_id = $k;
// 				$property_update = array(
// 				    		'is_lock'=>1,
// 				);
// 				$this->_name="ln_properties";
// 				$where="id = ".$property_id;
// 				$this->update($property_update, $where);
				
//     			$array_new = array(
//     					1=>$data[$i]['L'],//payment
//     					2=>$data[$i]['M'],
//     					3=>$data[$i]['N'],
//     					4=>$data[$i]['O'],
//     					5=>$data[$i]['P'],
//     					6=>$data[$i]['Q'],
//     					7=>$data[$i]['R'],
//     					8=>$data[$i]['S'],
//     					9=>$data[$i]['T'],
//     					10=>$data[$i]['U'],
//     					11=>$data[$i]['V'],
//     					12=>$data[$i]['W'],
//     					13=>$data[$i]['X'],
//     					14=>$data[$i]['Y'],
//     					15=>$data[$i]['Z'],
//     					16=>$data[$i]['AA'],
//     			);
//     			$beginning_balance= $data[$i]['K'];
//     			$beginning_balance_after=0;
//     			$principal_permonth=0;
//     			$principal_permonthafter=0;
//     			$total_interest=0;
//     			$total_interest_after=0;
//     			$total_payment_after=0;
//     			$ending_balance=0;
//     			$cum_interest=0;
//     			$amount_day=0;
    			 
//     			$old_paid=0;
    			 
//     			$isset=0;
//     			$paid_install = $data[$i]['AB'];
//     			$rs = $array_new;
//     			$old_paid_aftercondic =0;
//     			$ccc='';
//     			for($index=1; $index<=count($rs); $index++){

// 			if ($index <=$data[$i]['AB']){
// 				$rs[$index] = round($rs[$index]);
// 				if(empty($rs[$index]) OR $rs[$index]==0 OR $rs[$index]==''){
// 					$rs[$index] = $old_paid;
// 				}
// 				$isset=1;
// 			}else{
// 				$rs[$index] = round($data[$i]['K']/$data[$i]['H']);
// 				if ($isset==1){
// 						$rs[$index] =$old_paid;
// 				}
// 			}
// 				if($index>1){
// 					$beginning_balance = $ending_balance;
// 				}
// 			$ending_balance = $beginning_balance-$rs[$index];
// 			$sale_schedule_id = array(
// 					'branch_id'=>1,
// 					'sale_id'=>$sale_id,
// 					'begining_balance'=>$beginning_balance,//good
// 					'begining_balance_after'=> $beginning_balance,//good
// 					'principal_permonth'=> $rs[$index],//good
// 					'principal_permonthafter'=>$rs[$index],//good
// 					'total_interest'=>$total_interest,//good
// 					'total_interest_after'=>$total_interest_after,//good
// 					'total_payment'=>$rs[$index],//good
// 					'total_payment_after'=>$rs[$index],//good
// 					'ending_balance'=>$ending_balance,
// 					'cum_interest'=>$cum_interest,
// 					'amount_day'=>$amount_day,
// 					'is_completed'=>0,
// 					'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
// 					'no_installment'=>$index,
// 			);
// 			$this->_name='ln_saleschedule';
// 			$old_paid = $rs[$index];
// 			$sale_sche_id=$this->insert($sale_schedule_id);    				 
//     				if ($index<=$data[$i]['AB']){
//     						$receipt_money = array(
//     							'branch_id'			=>1,
//     							'client_id'			=>$client_id,
//     							'receipt_no'		=>$this->getReceiptByBranch(),
//     							'date_pay'			=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'land_id'			=>$property_id,
//     							'sale_id'			=>$sale_id,
//     							'date_input'		=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'outstanding'		=> $beginning_balance,
//     							'principal_amount'	=> $beginning_balance-$rs[$index],
//     							'total_principal_permonth'	=>$rs[$index],
//     							'total_principal_permonthpaid'=>$rs[$index],
//     							'total_interest_permonth'	=>0,
//     							'total_interest_permonthpaid'=>0,
//     							'penalize_amount'			=>0,
//     							'penalize_amountpaid'		=>0,
//     							'service_charge'	=>0,
//     							'service_chargepaid'=>0,
//     							'total_payment'		=>$rs[$index],
//     							'amount_payment'	=>$rs[$index],
//     							'recieve_amount'	=>$rs[$index],
//     							'balance'			=>0,
//     							'payment_option'	=>1,
//     							'is_completed'		=>1,
//     							'status'			=>1,
//     							'user_id'			=>1,
//     							'field3'			=>1
//     					);
//     					$this->_name='ln_client_receipt_money';
//     					$receipt_id = $this->insert($receipt_money);
    
//     					$this->_name='ln_client_receipt_money_detail';
//     					$reilcei_money_deta = array(
//     							'crm_id'				=>$receipt_id,
//     							'lfd_id'				=>$sale_sche_id,
//     							'client_id'				=>$client_id,
//     							'land_id'				=>$property_id,
//     							'date_payment'			=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'paid_date'             =>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'capital'				=>$beginning_balance,
//     							'remain_capital'		=>$beginning_balance-$rs[$index],
//     							'principal_permonth'	=>$rs[$index],
//     							'total_interest'		=>0,
//     							'total_payment'			=>$rs[$index],
//     							'total_recieve'			=>$rs[$index],
//     							'service_charge'		=>0,
//     							'penelize_amount'		=>0,
//     							'is_completed'			=>1,
//     							'status'				=>1,
//     							'old_interest'			 =>0,
//     							'old_principal_permonth'=>0,
//     							'old_total_payment'	 =>0,
//     							//     					'old_total_priciple'	=>		$data["old_total_priciple_".$i],
//     					);
//     					$this->insert($reilcei_money_deta);
    					
//     					$sale_sche_update = array(
// //     							"principal_permonthafter"=>$remain_principal,
// //     							'total_interest_after'=>$total_interestafter,
// //     							'begining_balance_after'=>$row['begining_balance_after']-($data['deposit']-$total_interest),
// //     							'ending_balance'=>$row['begining_balance_after']-($data['deposit']-$total_interest)-$remain_principal,//check again
//     							'is_completed'=>1,
//     							'paid_date'			=> 	date("Y-m-d",strtotime("$create_date + $index month")),
// //     							'total_payment_after'	=>	$pyament_after,
//     							);
//     					$this->_name="ln_saleschedule";
//     					$where="id = ".$sale_sche_id;
//     					$this->update($sale_sche_update, $where);
//     				}
//     				if ($index==16){
//     					$index=$index+1;
//     					while ($index<=$data[$i]['H']){
//     						$beginning_balance = $ending_balance;
//     						$ending_balance = $beginning_balance-$old_paid;
    
//     						if ($index==$data[$i]['H']){
//     							$old_paid = $old_paid+$ending_balance;
//     							$ending_balance=0;
//     						}
//     						$sale_schedule_id = array(
//     								'branch_id'=>1,
//     								'sale_id'=>$sale_id,
//     								'begining_balance'=>$beginning_balance,//good
//     								'begining_balance_after'=> $beginning_balance,//good
//     								'principal_permonth'=> $old_paid,//good
//     								'principal_permonthafter'=>$old_paid,//good
//     								'total_interest'=>$total_interest,//good
//     								'total_interest_after'=>$total_interest_after,//good
//     								'total_payment'=>$old_paid,//good
//     								'total_payment_after'=>$old_paid,//good
//     								'ending_balance'=>$ending_balance,
//     								'cum_interest'=>$cum_interest,
//     								'amount_day'=>$amount_day,
//     								'is_completed'=>0,
//     								'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     								'no_installment'=>$index,
//     						);
//     						$this->_name='ln_saleschedule';
//     						$this->insert($sale_schedule_id);
//     						$index++;
//     					}
//     				}
//     			}
    
//     		}//end main loop
//     		$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     	}
//     }

//     public function AddCateByImport($data){//Third Option
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$count = count($data);
//     	try{
//     		//print_r($data);exit();
//     		$k=637;
//     		$propertycode =610;
//     		for($i=1; $i<=$count; $i++){
//     			$k=$k+1;
//     			$propertycode = $propertycode+1;
//     			$rs = $this->getCustomer($data[$i]['B']);
//     			if(empty($rs)){
//     				if($data[$i]['D']=="ប្រុស"){
//     					$sex=1;
//     				}else{ $sex=2;
//     				}
//     				$client_arr_ =array(
//     						'branch_id'=>1,
//     						'name_en'=>$data[$i]['B'],
//     						'name_kh'=>$data[$i]['B'],
//     						'sex'=>$sex,
//     						'pro_id'=>12,
//     						'phone'=>$data[$i]['C'],
//     				);
//     				$this->_name='ln_client';
//     				$client_id = $this->insert($client_arr_);
//     			}else{
//     				$client_id=$rs;
//     			}
//     			$address=explode(" ", $data[$i]['J']);
//     			$proper_row = $this->getProperty($address[0], str_replace(")","",str_replace("(","",$address[1])));
//     			if (empty($proper_row)){
// 	    			$property_arr =array(
// 	    					'branch_id'=>1,
// 	    					'land_code'=>"PRO-".$propertycode,
// 	    					'street'=>str_replace(")","",str_replace("(","",$address[1])),
// 	    					'land_address'=>$address[0],
// 	    					'land_price'=>0,
// 	    					'house_price'=>$data[$i]['K'],
// 	    					'price'=>$data[$i]['K'],
// 	    					'property_type'=>1,
// 	    			);
// 	    			$this->_name='ln_properties';
// 	    			$property_id = $this->insert($property_arr);
//     			}else{
//     				$property_id = $proper_row;
//     			}
    
//     			$staff_id = $this->getStaffId($data[$i]['AG']);
//     			if(empty($staff_id)){
//     				$staff_arr =array(
//     						'branch_id'=>1,
//     						'co_code'=>"STAFF-".$i,
//     						'co_khname'=>$data[$i]['AG'],
//     						'sex'=>1,
//     				);
//     				$this->_name='ln_staff';
//     				$staff_id = $this->insert($staff_arr);
//     			}
    
//     			$create_date=$data[$i]['E'];
//     			$payment_dura = $data[$i]['H'];
//     			$create_date=$data[$i]['E'];
//     			$sale_arr =array(
//     					'branch_id'=>1,
//     					'sale_number'=>$data[$i]['A'],
//     					'receipt_no'=>$this->getReceiptByBranch(),
//     					'client_id'=>$client_id,
//     					'house_id'=>$property_id,
//     					'price_before'=>$data[$i]['K'],
//     					'price_sold'=>$data[$i]['K'],
//     					'other_fee'=>0,
//     					'paid_amount'=>0,
//     					'discount_percent'=>0,
//     					'discount_amount'=>0,
//     					'balance'=>$data[$i]['K'],
//     					'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     					'interest_rate'=>0,
//     					'total_duration'=>$data[$i]['H'],
//     					'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     					'payment_method'=>1,//$data['loan_type'],
//     					'payment_id'=>4,//រំលស់
//     					'land_price'=>$data[$i]['K'],
//     					'total_installamount'=>$data[$i]['H'],
    
//     					'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
//     					'staff_id'=>$staff_id,
//     					'comission'=>$data[$i]['H'],
//     					'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     			);
//     			$this->_name='ln_sale';
//     			$sale_id = $this->insert($sale_arr);
//     			//     			$sale_id =$i;
//     			$sale_id = $k;
//     			$property_update = array(
//     							    		'is_lock'=>1,
//     							);
//     							$this->_name="ln_properties";
//     							$where="id = ".$property_id;
//     							$this->update($property_update, $where);
//     			$array_new = array(
//     					1=>$data[$i]['L'],//payment
//     					2=>$data[$i]['M'],
//     					3=>$data[$i]['N'],
//     					4=>$data[$i]['O'],
//     					5=>$data[$i]['P'],
//     					6=>$data[$i]['Q'],
//     					7=>$data[$i]['R'],
//     					8=>$data[$i]['S'],
//     					9=>$data[$i]['T'],
//     					10=>$data[$i]['U'],
//     					11=>$data[$i]['V'],
//     					12=>$data[$i]['W'],
//     					13=>$data[$i]['X'],
//     					14=>$data[$i]['Y'],
//     					15=>$data[$i]['Z'],
//     					16=>$data[$i]['AA'],
//     			);
//     			$beginning_balance= $data[$i]['K'];
//     			$beginning_balance_after=0;
//     			$principal_permonth=0;
//     			$principal_permonthafter=0;
//     			$total_interest=0;
//     			$total_interest_after=0;
//     			$total_payment_after=0;
//     			$ending_balance=0;
//     			$cum_interest=0;
//     			$amount_day=0;
    
//     			$old_paid=0;
    
//     			$isset=0;
//     			$paid_install = $data[$i]['AB'];
//     			$rs = $array_new;
//     			$old_paid_aftercondic =0;
//     			$ccc='';
//     			for($index=1; $index<=count($rs); $index++){
    
//     				if ($index <=$data[$i]['AB']){
//     					$rs[$index] = $rs[$index];
// //     					if(empty($rs[$index]) OR $rs[$index]==0 OR $rs[$index]==''){
// //     						$rs[$index] = $old_paid;
// //     					}
// //     					$isset=1;
//     				}else{
// //     					$rs[$index] = round($data[$i]['K']/$data[$i]['H']);
// //     					if ($isset==1){
//     						$rs[$index] =$old_paid;
// //     					}
//     				}
//     				if($index>1){
//     					$beginning_balance = $ending_balance;
//     				}
//     				$ending_balance = $beginning_balance-$rs[$index];
//     				$sale_schedule_id = array(
//     						'branch_id'=>1,
//     						'sale_id'=>$sale_id,
//     						'begining_balance'=>$beginning_balance,//good
//     						'begining_balance_after'=> $beginning_balance,//good
//     						'principal_permonth'=> $rs[$index],//good
//     						'principal_permonthafter'=>$rs[$index],//good
//     						'total_interest'=>$total_interest,//good
//     						'total_interest_after'=>$total_interest_after,//good
//     						'total_payment'=>$rs[$index],//good
//     						'total_payment_after'=>$rs[$index],//good
//     						'ending_balance'=>$ending_balance,
//     						'cum_interest'=>$cum_interest,
//     						'amount_day'=>$amount_day,
//     						'is_completed'=>0,
//     						'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     						'no_installment'=>$index,
//     				);
//     				$this->_name='ln_saleschedule';
//     				$old_paid = $rs[$index];
//     				$sale_sche_id=$this->insert($sale_schedule_id);
//     				if ($index<=$data[$i]['AB']){
//     					$receipt_money = array(
//     							'branch_id'			=>1,
//     							'client_id'			=>$client_id,
//     							'receipt_no'		=>$this->getReceiptByBranch(),
//     							'date_pay'			=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'land_id'			=>$property_id,
//     							'sale_id'			=>$sale_id,
//     							'date_input'		=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'outstanding'		=> $beginning_balance,
//     							'principal_amount'	=> $beginning_balance-$rs[$index],
//     							'total_principal_permonth'	=>$rs[$index],
//     							'total_principal_permonthpaid'=>$rs[$index],
//     							'total_interest_permonth'	=>0,
//     							'total_interest_permonthpaid'=>0,
//     							'penalize_amount'			=>0,
//     							'penalize_amountpaid'		=>0,
//     							'service_charge'	=>0,
//     							'service_chargepaid'=>0,
//     							'total_payment'		=>$rs[$index],
//     							'amount_payment'	=>$rs[$index],
//     							'recieve_amount'	=>$rs[$index],
//     							'balance'			=>0,
//     							'payment_option'	=>1,
//     							'is_completed'		=>1,
//     							'status'			=>1,
//     							'user_id'			=>1,
//     							'field3'			=>1
//     					);
//     					$this->_name='ln_client_receipt_money';
//     					$receipt_id = $this->insert($receipt_money);
    
//     					$this->_name='ln_client_receipt_money_detail';
//     					$reilcei_money_deta = array(
//     							'crm_id'				=>$receipt_id,
//     							'lfd_id'				=>$sale_sche_id,
//     							'client_id'				=>$client_id,
//     							'land_id'				=>$property_id,
//     							'date_payment'			=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'paid_date'             =>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'capital'				=>$beginning_balance,
//     							'remain_capital'		=>$beginning_balance-$rs[$index],
//     							'principal_permonth'	=>$rs[$index],
//     							'total_interest'		=>0,
//     							'total_payment'			=>$rs[$index],
//     							'total_recieve'			=>$rs[$index],
//     							'service_charge'		=>0,
//     							'penelize_amount'		=>0,
//     							'is_completed'			=>1,
//     							'status'				=>1,
//     							'old_interest'			 =>0,
//     							'old_principal_permonth'=>0,
//     							'old_total_payment'	 =>0,
//     							//     					'old_total_priciple'	=>		$data["old_total_priciple_".$i],
//     					);
//     					$this->insert($reilcei_money_deta);
    						
//     					$sale_sche_update = array(
//     					//     							"principal_permonthafter"=>$remain_principal,
//     					//     							'total_interest_after'=>$total_interestafter,
//     					//     							'begining_balance_after'=>$row['begining_balance_after']-($data['deposit']-$total_interest),
//     					//     							'ending_balance'=>$row['begining_balance_after']-($data['deposit']-$total_interest)-$remain_principal,//check again
//     							'is_completed'=>1,
//     							'paid_date'			=> 	date("Y-m-d",strtotime("$create_date + $index month")),
//     							//     							'total_payment_after'	=>	$pyament_after,
//     					);
//     					$this->_name="ln_saleschedule";
//     					$where="id = ".$sale_sche_id;
//     					$this->update($sale_sche_update, $where);
//     				}
//     				if ($index==16){
//     					$index=$index+1;
//     					while ($index<=$data[$i]['H']){
//     						$beginning_balance = $ending_balance;
//     						$ending_balance = $beginning_balance-$old_paid;
    
//     						if ($index==$data[$i]['H']){
//     							$old_paid = $old_paid+$ending_balance;
//     							$ending_balance=0;
//     						}
//     						$sale_schedule_id = array(
//     								'branch_id'=>1,
//     								'sale_id'=>$sale_id,
//     								'begining_balance'=>$beginning_balance,//good
//     								'begining_balance_after'=> $beginning_balance,//good
//     								'principal_permonth'=> $old_paid,//good
//     								'principal_permonthafter'=>$old_paid,//good
//     								'total_interest'=>$total_interest,//good
//     								'total_interest_after'=>$total_interest_after,//good
//     								'total_payment'=>$old_paid,//good
//     								'total_payment_after'=>$old_paid,//good
//     								'ending_balance'=>$ending_balance,
//     								'cum_interest'=>$cum_interest,
//     								'amount_day'=>$amount_day,
//     								'is_completed'=>0,
//     								'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     								'no_installment'=>$index,
//     						);
//     						$this->_name='ln_saleschedule';
//     						$this->insert($sale_schedule_id);
//     						$index++;
//     					}
//     				}
//     			}
    
//     		}//end main loop
//     		$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     	}
//     }

//     public function AddCateByImport($data){//បង់30%
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$count = count($data);
//     	try{
//     		$k=658;
//     		$propertycode = 631;
//     		for($i=1; $i<=$count; $i++){
//     			$k=$k+1;
//     			$propertycode=$propertycode+1;
//     			$rs = $this->getCustomer($data[$i]['B']);
//     			if(empty($rs)){
//     				if($data[$i]['D']=="ប្រុស"){
//     					$sex=1;
//     				}else{ $sex=2;
//     				}
//     				$client_arr_ =array(
//     						'branch_id'=>1,
//     						'name_en'=>$data[$i]['B'],
//     						'name_kh'=>$data[$i]['B'],
//     						'sex'=>$sex,
//     						'pro_id'=>12,
//     						'phone'=>$data[$i]['C'],
//     				);
//     				$this->_name='ln_client';
//     				$client_id = $this->insert($client_arr_);
//     			}else{
//     				$client_id=$rs;
//     			}
//     			$address=explode(" ", $data[$i]['J']);
//     			$proper_row = $this->getProperty($address[0], str_replace(")","",str_replace("(","",$address[1])));
//     			if (empty($proper_row)){
//     			$property_arr =array(
//     					'branch_id'=>1,
//     					'land_code'=>"PRO-".$propertycode,
//     					'street'=>str_replace(")","",str_replace("(","",$address[1])),
//     					'land_address'=>$address[0],
//     					'land_price'=>0,
//     					'house_price'=>$data[$i]['K'],
//     					'price'=>$data[$i]['K'],
//     					'property_type'=>1,
//     			);
//     			$this->_name='ln_properties';
//     			$property_id = $this->insert($property_arr);
//     			}else{
//     				$property_id = $proper_row;
//     			}
    
//     			$staff_id = $this->getStaffId($data[$i]['AG']);
//     			if(empty($staff_id)){
//     				$staff_arr =array(
//     						'branch_id'=>1,
//     						'co_code'=>"STAFF-".$i,
//     						'co_khname'=>$data[$i]['AG'],
//     						'sex'=>1,
//     				);
//     				$this->_name='ln_staff';
//     				$staff_id = $this->insert($staff_arr);
//     			}
    
//     			$create_date=$data[$i]['E'];
//     			$payment_dura = $data[$i]['H'];
//     			$create_date=$data[$i]['E'];
//     			$sale_arr =array(
//     					'branch_id'=>1,
//     					'sale_number'=>$data[$i]['A'],
//     					'receipt_no'=>$this->getReceiptByBranch(),
//     					'client_id'=>$client_id,
//     					'house_id'=>$property_id,
//     					'price_before'=>$data[$i]['K'],
//     					'price_sold'=>$data[$i]['K'],
//     					'other_fee'=>0,
//     					'paid_amount'=>0,
//     					'discount_percent'=>0,
//     					'discount_amount'=>0,
//     					'balance'=>$data[$i]['K'],
//     					'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     					'interest_rate'=>0,
//     					'total_duration'=>$data[$i]['H'],
//     					'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     					'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
//     					'payment_method'=>1,//$data['loan_type'],
//     					'payment_id'=>4,
//     					'land_price'=>$data[$i]['K'],
//     					'total_installamount'=>$data[$i]['H'],
    
//     					'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
//     					'staff_id'=>$staff_id,
//     					'comission'=>$data[$i]['H'],
//     					'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
//     			);
//     			$this->_name='ln_sale';
//     			$sale_id = $this->insert($sale_arr);
//     			//     			$sale_id =$i;
//     			$sale_id = $k;
//     			$property_update = array(
//     			   'is_lock'=>1,
//     			  	);
//     			  $this->_name="ln_properties";
//     			  $where="id = ".$property_id;
//     			  $this->update($property_update, $where);
//     			$array_new = array(
//     					1=>$data[$i]['L'],//payment
//     					2=>$data[$i]['M'],
//     					3=>$data[$i]['N'],
//     					4=>$data[$i]['O'],
//     					5=>$data[$i]['P'],
//     					6=>$data[$i]['Q'],
//     					7=>$data[$i]['R'],
//     					8=>$data[$i]['S'],
//     					9=>$data[$i]['T'],
//     					10=>$data[$i]['U'],
//     					11=>$data[$i]['V'],
//     					12=>$data[$i]['W'],
//     					13=>$data[$i]['X'],
//     					14=>$data[$i]['Y'],
//     					15=>$data[$i]['Z'],
//     					16=>$data[$i]['AA'],
//     			);
//     			$beginning_balance= $data[$i]['K'];
//     			$beginning_balance_after=0;
//     			$principal_permonth=0;
//     			$principal_permonthafter=0;
//     			$total_interest=0;
//     			$total_interest_after=0;
//     			$total_payment_after=0;
//     			$ending_balance=0;
//     			$cum_interest=0;
//     			$amount_day=0;
    
//     			$old_paid=0;
    
//     			$isset=0;
//     			$paid_install = $data[$i]['AB'];
//     			$rs = $array_new;
//     			$old_paid_aftercondic =0;
//     			$ccc='';
//     			for($index=1; $index<=count($rs); $index++){
// 	    				if ($index <=$data[$i]['AB']){
// 	    					$rs[$index] = $rs[$index];
// 	    				}else{
// 	    					$rs[$index] =$old_paid;
// 	    				}
//     				if($index>1){
//     					$beginning_balance = $ending_balance;
//     				}
//     				$ending_balance = $beginning_balance-$rs[$index];
//     				$sale_schedule_id = array(
//     						'branch_id'=>1,
//     						'sale_id'=>$sale_id,
//     						'begining_balance'=>$beginning_balance,//good
//     						'begining_balance_after'=> $beginning_balance,//good
//     						'principal_permonth'=> $rs[$index],//good
//     						'principal_permonthafter'=>$rs[$index],//good
//     						'total_interest'=>$total_interest,//good
//     						'total_interest_after'=>$total_interest_after,//good
//     						'total_payment'=>$rs[$index],//good
//     						'total_payment_after'=>$rs[$index],//good
//     						'ending_balance'=>$ending_balance,
//     						'cum_interest'=>$cum_interest,
//     						'amount_day'=>$amount_day,
//     						'is_completed'=>0,
//     						'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     						'no_installment'=>$index,
//     				);
//     				$this->_name='ln_saleschedule';
//     				$old_paid = $rs[$index];
//     				$sale_sche_id=$this->insert($sale_schedule_id);
//     				if ($index<=$data[$i]['AB']){
//     					$receipt_money = array(
//     							'branch_id'			=>1,
//     							'client_id'			=>$client_id,
//     							'receipt_no'		=>$this->getReceiptByBranch(),
//     							'date_pay'			=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'land_id'			=>$property_id,
//     							'sale_id'			=>$sale_id,
//     							'date_input'		=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'outstanding'		=> $beginning_balance,
//     							'principal_amount'	=> $beginning_balance-$rs[$index],
//     							'total_principal_permonth'	=>$rs[$index],
//     							'total_principal_permonthpaid'=>$rs[$index],
//     							'total_interest_permonth'	=>0,
//     							'total_interest_permonthpaid'=>0,
//     							'penalize_amount'			=>0,
//     							'penalize_amountpaid'		=>0,
//     							'service_charge'	=>0,
//     							'service_chargepaid'=>0,
//     							'total_payment'		=>$rs[$index],
//     							'amount_payment'	=>$rs[$index],
//     							'recieve_amount'	=>$rs[$index],
//     							'balance'			=>0,
//     							'payment_option'	=>1,
//     							'is_completed'		=>1,
//     							'status'			=>1,
//     							'user_id'			=>1,
//     							'field3'			=>1
//     					);
//     					$this->_name='ln_client_receipt_money';
//     					$receipt_id = $this->insert($receipt_money);
    
//     					$this->_name='ln_client_receipt_money_detail';
//     					$reilcei_money_deta = array(
//     							'crm_id'				=>$receipt_id,
//     							'lfd_id'				=>$sale_sche_id,
//     							'client_id'				=>$client_id,
//     							'land_id'				=>$property_id,
//     							'date_payment'			=>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'paid_date'             =>date("Y-m-d",strtotime("$create_date + $index month")),
//     							'capital'				=>$beginning_balance,
//     							'remain_capital'		=>$beginning_balance-$rs[$index],
//     							'principal_permonth'	=>$rs[$index],
//     							'total_interest'		=>0,
//     							'total_payment'			=>$rs[$index],
//     							'total_recieve'			=>$rs[$index],
//     							'service_charge'		=>0,
//     							'penelize_amount'		=>0,
//     							'is_completed'			=>1,
//     							'status'				=>1,
//     							'old_interest'			 =>0,
//     							'old_principal_permonth'=>0,
//     							'old_total_payment'	 =>0,
//     							//     					'old_total_priciple'	=>		$data["old_total_priciple_".$i],
//     					);
//     					$this->insert($reilcei_money_deta);
    
//     					$sale_sche_update = array(
//     							//     							"principal_permonthafter"=>$remain_principal,
//     							//     							'total_interest_after'=>$total_interestafter,
//     							//     							'begining_balance_after'=>$row['begining_balance_after']-($data['deposit']-$total_interest),
//     							//     							'ending_balance'=>$row['begining_balance_after']-($data['deposit']-$total_interest)-$remain_principal,//check again
//     							'is_completed'=>1,
//     							'paid_date'			=> 	date("Y-m-d",strtotime("$create_date + $index month")),
//     							//     							'total_payment_after'	=>	$pyament_after,
//     					);
//     					$this->_name="ln_saleschedule";
//     					$where="id = ".$sale_sche_id;
//     					$this->update($sale_sche_update, $where);
//     				}
//     				if ($index==16){
//     					$index=$index+1;
//     					while ($index<=$data[$i]['H']){
//     						$beginning_balance = $ending_balance;
//     						$ending_balance = $beginning_balance-$old_paid;
    
//     						if ($index==$data[$i]['H']){
//     							$old_paid = $old_paid+$ending_balance;
//     							$ending_balance=0;
//     						}
//     						$sale_schedule_id = array(
//     								'branch_id'=>1,
//     								'sale_id'=>$sale_id,
//     								'begining_balance'=>$beginning_balance,//good
//     								'begining_balance_after'=> $beginning_balance,//good
//     								'principal_permonth'=> $old_paid,//good
//     								'principal_permonthafter'=>$old_paid,//good
//     								'total_interest'=>$total_interest,//good
//     								'total_interest_after'=>$total_interest_after,//good
//     								'total_payment'=>$old_paid,//good
//     								'total_payment_after'=>$old_paid,//good
//     								'ending_balance'=>$ending_balance,
//     								'cum_interest'=>$cum_interest,
//     								'amount_day'=>$amount_day,
//     								'is_completed'=>0,
//     								'date_payment'=>date("Y-m-d",strtotime("$create_date + $index month")),
//     								'no_installment'=>$index,
//     						);
//     						$this->_name='ln_saleschedule';
//     						$this->insert($sale_schedule_id);
//     						$index++;
//     					}
//     				}
//     			}
    
//     		}//end main loop
//     		$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     	}
//     }
//     public function AddCateByImport($data){//Customer & Propery
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$count = count($data);
//     	try{
//     		$k=662;//pro_id
//     		$propertycode = 635;
//     		for($i=1; $i<=$count; $i++){
//     			$k=$k+1;
//     			$propertycode = $propertycode+1;
//     			$rs = $this->getCustomer($data[$i]['B']);
//     			if(empty($rs)){
//     				if($data[$i]['D']=="ប្រុស"){
//     					$sex=1;
//     				}else{ $sex=2;
//     				}
//     				$client_arr_ =array(
//     						'branch_id'=>1,
//     						'name_en'=>$data[$i]['B'],
//     						'name_kh'=>$data[$i]['B'],
//     						'sex'=>$sex,
//     						'pro_id'=>12,
//     						'phone'=>$data[$i]['C'],
//     				);
//     				$this->_name='ln_client';
//     				$client_id = $this->insert($client_arr_);
//     			}else{
//     				$client_id=$rs;
//     			}
//     			$address=explode(" ", $data[$i]['J']);
//     			$proper_row = $this->getProperty($address[0], str_replace(")","",str_replace("(","",$address[1])));
//     			if (empty($proper_row)){
// 	    			$property_arr =array(
// 	    					'branch_id'=>1,
// 	    					'land_code'=>"PRO-".$propertycode,
// 	    					'street'=>str_replace(")","",str_replace("(","",$address[1])),
// 	    					'land_address'=>$address[0],
// 	    					'land_price'=>0,
// 	    					'house_price'=>$data[$i]['K'],
// 	    					'price'=>$data[$i]['K'],
// 	    					'property_type'=>1,
// 	    			);
// 	    			$this->_name='ln_properties';
// 	    			$property_id = $this->insert($property_arr);
//     			}else{
//     				$property_id =$proper_row;
//     			}
    
// //     			$staff_id = $this->getStaffId($data[$i]['AG']);
// //     			if(empty($staff_id)){
// //     				$staff_arr =array(
// //     						'branch_id'=>1,
// //     						'co_code'=>"STAFF-".$i,
// //     						'co_khname'=>$data[$i]['AG'],
// //     						'sex'=>1,
// //     				);
// //     				$this->_name='ln_staff';
// //     				$staff_id = $this->insert($staff_arr);
// //     			}
    
// //     			$create_date=$data[$i]['E'];
// //     			$payment_dura = $data[$i]['H'];
// //     			$create_date=$data[$i]['E'];
// //     			$sale_arr =array(
// //     					'branch_id'=>1,
// //     					'sale_number'=>$data[$i]['A'],
// //     					'receipt_no'=>$this->getReceiptByBranch(),
// //     					'client_id'=>$client_id,
// //     					'house_id'=>$property_id,
// //     					'price_before'=>$data[$i]['K'],
// //     					'price_sold'=>$data[$i]['K'],
// //     					'other_fee'=>0,
// //     					'paid_amount'=>0,
// //     					'discount_percent'=>0,
// //     					'discount_amount'=>0,
// //     					'balance'=>$data[$i]['K'],
// //     					'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
// //     					'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
// //     					'interest_rate'=>0,
// //     					'total_duration'=>$data[$i]['H'],
// //     					'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
// //     					'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
// //     					'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
// //     					'payment_method'=>1,//$data['loan_type'],
// //     					'payment_id'=>4,
// //     					'land_price'=>$data[$i]['K'],
// //     					'total_installamount'=>$data[$i]['H'],
    
// //     					'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
// //     					'staff_id'=>$staff_id,
// //     					'comission'=>$data[$i]['H'],
// //     					'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
// //     			);
// //     			$this->_name='ln_sale';
// //     			$sale_id = $this->insert($sale_arr);
// //     			$sale_id =$i;
// //     			$sale_id = $k;
    
//     		}//end main loop
//     		$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     	}
//     }
    public function AddCateByImport($data){//Customer & Propery
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$count = count($data);
    	try{
    		$k=662;//pro_id
    		$propertycode = 635;
    		for($i=1; $i<=$count; $i++){
    			$k=$k+1;
    			
    
    			    			$create_date=$data[$i]['E'];
    			    			$payment_dura = $data[$i]['H'];
    			    			$create_date=$data[$i]['E'];
    			    			$sale_arr =array(
    			    					'branch_id'=>1,
    			    					'sale_number'=>$data[$i]['A'],
    			    					'receipt_no'=>$this->getReceiptByBranch(),
//     			    					'client_id'=>$client_id,
//     			    					'house_id'=>$property_id,
    			    					'price_before'=>$data[$i]['K'],
    			    					'price_sold'=>$data[$i]['K'],
    			    					'other_fee'=>0,
    			    					'paid_amount'=>0,
    			    					'discount_percent'=>0,
    			    					'discount_amount'=>0,
    					    					'balance'=>$data[$i]['K'],
    					    					'buy_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
    					    					'end_line'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
    					    					'interest_rate'=>0,
    					    					'total_duration'=>$data[$i]['H'],
    					    					'startcal_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
    					    					'first_payment'=>date("Y-m-d",strtotime($data[$i]['E'])),
    					    					'validate_date'=>date("Y-m-d",strtotime("$create_date + $payment_dura month")),
    					    					'payment_method'=>1,//$data['loan_type'],
    					    					'payment_id'=>4,
    					    					'land_price'=>$data[$i]['K'],
    					    					'total_installamount'=>$data[$i]['H'],
    
    					    					'agreement_date'=>date("Y-m-d",strtotime($data[$i]['F'])),
    					    					'comission'=>$data[$i]['AL'],
    					    					'create_date'=>date("Y-m-d",strtotime($data[$i]['E'])),
    					    			);
    			    			$this->_name='ln_sale_copy';
    			    			$sale_id = $this->insert($sale_arr);
    			    			$sale_id =$i;
    			    			$sale_id = $k;
    
    		}//end main loop
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		echo $e->getMessage();exit();
    	}
    }
    public function getReceiptByBranch(){
    	$this->_name='ln_client_receipt_money';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id) FROM $this->_name WHERE 1 LIMIT 1 ";
    	$pre="N1:";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	//    	return $pre.$new_acc_no;
    	return $pre.$new_acc_no;
    }
    public function getLoanNumber(){
    	$this->_name='ln_sale';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id) FROM $this->_name  LIMIT 1 ";
    	//$pre = $this->getPrefixCode($data['branch_id'])."-S";
    	$pre="BR-S";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    
   function getAllsaleCopy(){
   	$db = $this->getAdapter();
   	$sql="SELECT sc.`id`,sc.`sale_number`,sc.`comission` FROM `ln_sale_copy` AS sc";
   	return $db->fetchAll($sql);
   }
   
   function updateSaleCommission($commission,$sale_id){
   	$db = $this->getAdapter();
   	$arr = array(
   			'comission'=>$commission
   			);
   	$where=' id='.$sale_id;
   	$this->_name='ln_sale';
   	$this->update($arr, $where);
   }
}  
	  

