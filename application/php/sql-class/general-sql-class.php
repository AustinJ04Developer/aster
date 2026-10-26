<?php
session_start();
//include ("../../settings/config.php");
class PDNGeneralSQL
{
	//Code added by jino
//Debit amount from company bank account when delete others receipt
	
	function PDNFDInterestTransferProvisionTemp($effective_date,$renewal_interest,$renewal_tax,$fd_id,$user_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_fd_provision_temp
										(Date,
										FDID,
										OldProvisionAmount,
										Tax,
										UserId) VALUES (";
					$transaction_query .= "'".$effective_date."',".$fd_id.",".$renewal_interest.",".$renewal_tax.",".$user_id.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DebitCompanyBankOthersDeleteReceipt($cbid,$transaction_amount)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_company_bank SET
										Amount = Amount - ".$transaction_amount." WHERE CBID =".$cbid;
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			// echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	
//Code added by jino
//Credit amount from company bank account when delete others payment
	function CreditCompanyBankOthersDeletePayment($cbid,$transaction_amount)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_company_bank SET
										Amount = Amount + ".$transaction_amount." WHERE CBID =".$cbid;
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			// echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNRDFDLoanTransaction($trans_id,$amount,$pay_mode,$remarks) //  During Edit Mode SDID,AMOUNT,PAYMODE EDITABLE in edit mode for updating the values use this function.
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET ManagerApproved = 0,EditDeleteMode = 1, Amount=".$amount.",TransMethod=".$pay_mode.",Remarks='".$remarks."',ModifiedBy=".$_SESSION['UserId']." WHERE TID=".$trans_id;
				
			/*	print $transaction_query;
				exit;*/
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeleteRecord($table,$deletefield,$conditionfield,$conditionvalue)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE ".$table." SET ".
										$deletefield." = 1 WHERE ".$conditionfield." = ".$conditionvalue;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function


	function UpdateTransactionIsInterest($fieldname,$tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET ".$fieldname." = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function ClosePrematureRD($tablename,$amountfield,$amount,$accnofield,$accno,$userid,$accountclosetype,$interestpercent,$interestamount,$interest_provision,$default_interest_provision)
	{
		global $dbh;
		// Query Generation
		try
		{
				$date = date('Y-m-d');
				$param= "$amountfield=$amount,IsClosed=1,ClosedBy=$userid,ClosedDate='".$date."',AccoutClosingType=".$accountclosetype.",PaidInterestPercentage=".$interestpercent.",PaidInterestAmount=".$interestamount.",Interest_Provision=".$interest_provision.",DefaultInterest_Provision=".$default_interest_provision; // .",PenaltyReceived=".$penaltyreceived
				$condition="$accnofield=$accno";
				$sql_credit_amount .= "UPDATE ".$tablename." SET ".$param." WHERE IsDeleted = 0 AND ".$condition;
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DebitAmountExcessPenalty($tablename,$amountfield,$amount,$accnofield,$accno) // Debit the Amount from Any table pass the parameter Tablename,amountfield,amount,accnofieldname,accountno
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$condition="$accnofield=$accno";
				$sql ="SELECT ".$amountfield." FROM ".$tablename." WHERE IsDeleted = 0 AND ".$condition;
				
				$query_sql = $dbh->query($sql);
				$query_sql->setFetchMode(PDO::FETCH_ASSOC);
				$result = $query_sql->fetch();	
				if($query_sql->rowCount() > 0)
					$balance = $result[$amountfield];
				if($balance>0)
				{
					$currentbalance=0;
				
					$sql_credit_amount = "UPDATE ".$tablename." SET ".$amountfield."=".$currentbalance." WHERE IsDeleted = 0 AND ".$condition;
				
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql_credit_amount)){
						$dbh->rollback();
						return 0;
					}
					// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
					// $sdid = $dbh->lastInsertId();
					$dbh->commit();	// End Transaction Add Data to the share details table
			    }
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeleteOthersReceiptPayment($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				//EditDeleteMode = 2=> delete record
				$transaction_query = "UPDATE pdn_transaction SET ManagerApproved = 0,EditDeleteMode = 2, IsDeleted = 1 WHERE TID = ".$tid."";
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNTransaction($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".date('Y-m-d')."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNTransactionFDOnly($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$fd_start_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				 //	$fd_start_date = date('Y-m-d');
					$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".$fd_start_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.")"; 
					/*print $transaction_query;
					exit;*/
									  // In Transaction query For share using certificate number as AccountNo
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				/*print $transaction_query;
				exit;*/
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNTransactionFDOnlye($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$fd_start_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				 //	$fd_start_date = date('Y-m-d');
					$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".$fd_start_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.")"; 
					/*print $transaction_query;
					exit;*/
									  // In Transaction query For share using certificate number as AccountNo
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				/*print $transaction_query;
				exit;*/
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNTransactionOldData($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$fd_start_date,$is_old_data)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,IsDataFromOldSoftware) VALUES (";
					$transaction_query .= "'".$fd_start_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",".$is_old_data.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
function InsertPDNTransactionDepositLoanOldEntry($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,IsDataFromOldSoftware) VALUES (";
					$transaction_query .= "'".date('Y-m-d')."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",1)"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
function InsertPDNTransactionDepositLoanOldEntryFDLRDL($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$outstanding_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,IsDataFromOldSoftware) VALUES (";
					$transaction_query .= "'".$outstanding_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",1)"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
function InsertPDNTransactionJLRec($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$receipt_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".$receipt_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function

function InsertPDNTransactionOthers($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$date)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".checkIsEmpty($date)."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function

function InsertPDNTransactionOthersDepreciation($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$date,$show_in_daily_report)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,IsShownDailyReport) VALUES (";
					$transaction_query .= "'".checkIsEmpty($date)."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",
									  ".$show_in_daily_report.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function

	function UpdatePDNTransactionOthers($pay_mode,$transaction_amount,$account_code,$account_subcode,$tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET EditDeleteMode = 1, ManagerApproved = 0, TransMethod = ".$pay_mode.",
									  Amount = ".$transaction_amount.",ACode = ".$account_code.", 
									  ASubCode = ".$account_subcode." WHERE TID = ".$tid; // In Transaction query For share using certificate number as AccountNo
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNTransactionOthersOldDate($account_code,$account_subcode,$tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET ACode = ".$account_code.", 
									  ASubCode = ".$account_subcode." WHERE TID = ".$tid; // In Transaction query For share using certificate number as AccountNo
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
		
	function InsertPDNTransactionTransferToSD($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$transfer_field,$is_transfer)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,".$transfer_field.") VALUES (";
					$transaction_query .= "'".date('Y-m-d')."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",1)"; 
									 /* print $transaction_query;
									  exit;*/
									  // In Transaction query For share using certificate number as AccountNo
									  /*if($atmid == 4)
									  {
									  print $transaction_query;
									  exit;
									  }*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNTransactionTransferToSDFromFD($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$transfer_field,$is_transfer,$fd_start_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,".$transfer_field.") VALUES (";
					$transaction_query .= "'".$fd_start_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",1)"; 
									 /* print $transaction_query;
									  exit;*/
									  // In Transaction query For share using certificate number as AccountNo
									  /*if($atmid == 4)
									  {
									  print $transaction_query;
									  exit;
									  }*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function InsertPDNTransactionInterestOnly($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$interest_or_definterest,$receipt_date) // interest_or_definterest => 1-> interest and 2-> Default interest
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode";
					if($atmid == 8)
					{
						$transaction_query .= ",IsInterestFromJL) VALUES (";
					}
					if($atmid == 8)
					{
						$transaction_query .= "'".$receipt_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",
									  ".$acc_sub_code.",".$interest_or_definterest.")";
					}
					if($atmid == 13)
					{
						$transaction_query .= ",IsInterestFromML) VALUES (";
					}
					if($atmid == 14)
					{
						$transaction_query .= ",IsInterestFromSL) VALUES (";
					}
					if($atmid == 13 || $atmid == 14)
					{
						$transaction_query .= "'".$receipt_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",
									  ".$acc_sub_code.",".$interest_or_definterest.")";
					}
									   // In Transaction query For share using certificate number as AccountNo
									 /* print $transaction_query;
									  exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function

function InsertPDNTransactionInterestOnlySDTransfer($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$interest_or_definterest,$transfer_field,$is_transfer,$receipt_date) // interest_or_definterest => 1-> interest and 2-> Default interest
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode";
					if($atmid == 8 || $atmid == 4)
					{
						$transaction_query .= ",IsInterestFromJL,".$transfer_field.") VALUES (";
					}
					if($atmid == 8 || $atmid == 4)
					{
						$transaction_query .= "'".$receipt_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",
									  ".$acc_sub_code.",".$interest_or_definterest.",1)";
					}
									   // In Transaction query For share using certificate number as AccountNo
									 /* print $transaction_query;
									  exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
function InsertPDNTransactionInterestOnlySDTransferFromSL($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$interest_or_definterest,$transfer_field,$is_transfer,$receipt_date) // interest_or_definterest => 1-> interest and 2-> Default interest
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode";
					if($atmid == 4 || $atmid == 14)
					{
						$transaction_query .= ",IsInterestFromSL,".$transfer_field.") VALUES (";
					}
					if($atmid == 14 || $atmid == 4)
					{
						$transaction_query .= "'".$receipt_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",
									  ".$acc_sub_code.",".$interest_or_definterest.",1)";
					}
									   // In Transaction query For share using certificate number as AccountNo
									/*if($atmid == 14)
									{
									  print $transaction_query;
									  exit;
									 }*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function

function InsertPDNTransactionInterestOnlySDTransferFromML($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$interest_or_definterest,$transfer_field,$is_transfer,$receipt_date) // interest_or_definterest => 1-> interest and 2-> Default interest
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode";
					if($atmid == 4 || $atmid == 13)
					{
						$transaction_query .= ",IsInterestFromML,".$transfer_field.") VALUES (";
					}
					if($atmid == 13 || $atmid == 4)
					{
						$transaction_query .= "'".$receipt_date."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",
									  ".$acc_sub_code.",".$interest_or_definterest.",1)";
					}
									   // In Transaction query For share using certificate number as AccountNo
									 /* print $transaction_query;
									  exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
function InsertPDNTransactionWithRemarks($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code,$remarks)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode,Remarks) VALUES (";
					$transaction_query .= "'".date('Y-m-d')."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.",'".$remarks."')"; // In Transaction query For share using certificate number as AccountNo
									  /*if($atmid == 4)
									  {
									  print $transaction_query;
									  exit;
									  }*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function	
	/*function InsertPDNTransaction($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$txn_challan_no)
	{
		global $dbh;
		// Query Generation
		try
		{
				if($transaction_method == 2)// Check then update status
				{
					if($transaction_type ==1) //receipt cheque status=0
					{
						$status=0;
					}
					else
					{
						$status=1;  // payment cheque status=1
					}
					$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,Status,ChallanNo) VALUES (";
					$transaction_query .= "'".date('Y-m-d')."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",".$status.",'".$txn_challan_no."')"; // In Transaction query For share using certificate number as AccountNo
				 }
				 else
				 {
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo) VALUES (";
					$transaction_query .= "'".date('Y-m-d')."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$txn_challan_no."')"; // In Transaction query For share using certificate number as AccountNo
				 }
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function*/
	function InsertPDNTransactionFDInterest($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$date,$txn_challan_no,$main_ac_no,$sub_ac_no)
	{
		global $dbh;
		// Query Generation
		try
		{
					$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,Status,ChallanNo,IsInterestFromFD,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".checkIsEmpty($date)."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",1,'".$txn_challan_no."',1,".$main_ac_no.",".$sub_ac_no.")"; // In Transaction query For share using certificate number as AccountNo
									 /* print $transaction_query;
									  exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				// Update FD Interest Table with all transfer as 1
				return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNTransactionCDInterest($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$date,$txn_challan_no,$main_ac_no,$sub_ac_no)
	{
		global $dbh;
		// Query Generation
		try
		{
					$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,Status,ChallanNo,IsInterestFromCD,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".checkIsEmpty($date)."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",1,'".$txn_challan_no."',1,".$main_ac_no.",".$sub_ac_no.")"; // In Transaction query For share using certificate number as AccountNo
									 /* print $transaction_query;
									  exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				// Update FD Interest Table with all transfer as 1
				return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function InsertPDNTransactionSDInterest($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$date,$txn_challan_no,$is_sd_interest,$sd_int_main_ac,$sd_int_sub_ac) // Interest from SD
	{
		global $dbh;
		// Query Generation
		try
		{
					$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,Status,ChallanNo,IsInterestFromSD,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".checkIsEmpty($date)."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",1,'".$txn_challan_no."',1,".$sd_int_main_ac.",".$sd_int_sub_ac.")"; // In Transaction query For share using certificate number as AccountNo
									 /* print $transaction_query;
									  exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				// Update FD Interest Table with all transfer as 1
				return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdateFDCalculatonTable($transfer_from_fd_acc_no,$current_month_start_date,$transfer_sd_account)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				/*$transaction_query = "UPDATE pdn_fd_interest_calculation SET TransferedToSD = 1,ToSDAccount = ".$transfer_sd_account." 
									  WHERE FDID = ".$transfer_from_fd_acc_no." AND 
										  DATE(Date) < '".$current_month_start_date."'";*/
				
				$transaction_query = "UPDATE pdn_fd_interest_calculation SET ToSDAccount = ".$transfer_sd_account." 
									  WHERE FDID = ".$transfer_from_fd_acc_no." AND 
										  DATE(Date) < '".$current_month_start_date."'";
				/*print 'aaaaa'.$transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function UpdatePDNBank($bank_id,$cheque_amount)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$transaction_query = "UPDATE pdn_company_bank SET Amount = Amount - ".$cheque_amount." WHERE CBID = ".$bank_id." AND 
										  IsDeleted = 0";
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	// Update all closed records in the calcuation table status as zero
	function UpdatePDNFDCalculation($fd_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$transaction_query = "UPDATE pdn_fd_interest_calculation SET Status = 0 WHERE FDID = ".$fd_id." AND 
										  IsDeleted = 0";
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNCDCalculation($fd_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$transaction_query = "UPDATE pdn_cd_interest_calculation SET Status = 0 WHERE FDID = ".$fd_id." AND 
										  IsDeleted = 0";
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	//function to insert data in details table
	function InsertPDNShareDetails($eqno,$director_purchased_value,$no_of_shares,$share_amount,$distinctive_number_from,$distinctive_number_to,$certificate_no,$user_id,
								   $represented_by,$represented_by_relation,$nominee,$nominee_age,$relation,$secondary_name,$secondary_address1,
								   $secondary_address2,$secondary_address3,$third_name,$third_address1,$third_address2,$third_address3,$transaction_method,$date,$share_type)
	{
		global $dbh;
		// Query Generation
		try
		{
				
			$sql_sub_table .= "INSERT INTO pdn_sharedetails(
												EQNO,
												IsDirectorPurchased,
												NoOfShares,
												ShareAmount,
												DistinctiveNumberFrom,
												DistinctiveNumberTo,
												CertificateNo,
												LastModifiedDate,
												CertificatePrinted,
												UserId,
												RepresentedBy,
												RepresentedByRelation,
												Nominee,
												NomineeAge,
												Relation,
												NameS,
												AddressS1,
												AddressS2,
												AddressS3,
												NameT,
												AddressT1,
												AddressT2,
												AddressT3,
												CashChequeTransfer,
												Date,
												ShareType) VALUES (";
				$sql_sub_table .= $eqno.",".$director_purchased_value.",".$no_of_shares.",".$share_amount.",".$distinctive_number_from.",".$distinctive_number_to.",".$certificate_no.",'".date('Y-m-d')."',0,".$_SESSION['UserId'].",'".$represented_by."','".$represented_by_relation."','".$nominee."',".$nominee_age.",'".$relation."','".$secondary_name."','".$secondary_address1."','".$secondary_address2."','".$secondary_address3."','".$third_name."','".$third_address1."','".$third_address2."','".$third_address3."',".$transaction_method.",'".$date."',".$share_type.")";
				/*print $sql_sub_table;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_sub_table)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	//function update share receipt number
	function UpdateShareReceiptNo()
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_receipt_no .= "UPDATE pdn_branchmaster SET ShareReceiptNo = ShareReceiptNo+1 WHERE IsDeleted = 0 AND DefaultBranch =1";
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_receipt_no)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	
	function InsertPDNCheque($no_of_fields_in_check_data_array, $cheque_amount,$transaction_tid,$cheque_date,$bank_name,$cheque_number,$to_bank_id,$cheque_paytype)
	{
		global $dbh;
		// Query Generation
		try
		{
				if($no_of_fields_in_check_data_array >0) // Insert Cheque
				{
					// $cheque_amount = $check_data_array[5];
					$cheque_insert_query = "INSERT INTO pdn_cheque(
											TID,
											Date,
											ChequeDate,
											Bank,
											ChequeNo,
											Amount,
											UserId,
											CBID,
											ChequePaymentType) VALUES (";
					$cheque_insert_query .= $transaction_tid.",'".date('Y-m-d')."','".checkIsEmpty($cheque_date)."',
											'".$bank_name."','".$cheque_number."',
											".$cheque_amount.",".$_SESSION['UserId'].",".$to_bank_id.",".$cheque_paytype.")"; 
					/*print $cheque_insert_query;
					exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($cheque_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
					// Update Amount in the bank
					if($cheque_paytype==1)// 1->receipt Add Values to Bank Account
					{
						$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount + ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					}
					else // 2->payment Reduce the Amount from Bank Account
					{
						$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount - ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					}
					
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($update_amount_in_bank)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
					return 1;
				}
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNChequeWithReceiptDate($no_of_fields_in_check_data_array, $cheque_amount,$transaction_tid,$cheque_date,$bank_name,$cheque_number,$to_bank_id,$cheque_paytype,$receipt_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				if($no_of_fields_in_check_data_array >0) // Insert Cheque
				{
					// $cheque_amount = $check_data_array[5];
					$cheque_insert_query = "INSERT INTO pdn_cheque(
											TID,
											Date,
											ChequeDate,
											Bank,
											ChequeNo,
											Amount,
											UserId,
											CBID,
											ChequePaymentType) VALUES (";
					$cheque_insert_query .= $transaction_tid.",'".$receipt_date."','".checkIsEmpty($cheque_date)."',
											'".$bank_name."','".$cheque_number."',
											".$cheque_amount.",".$_SESSION['UserId'].",".$to_bank_id.",".$cheque_paytype.")"; 
					/*print $cheque_insert_query;
					exit;*/
					$dbh->exec($cheque_insert_query);
					/*$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($cheque_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table*/
					// Update Amount in the bank
					if($cheque_paytype==1)// 1->receipt Add Values to Bank Account
					{
						$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount + ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					}
					else // 2->payment Reduce the Amount from Bank Account
					{
						$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount - ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					}
					$dbh->exec($update_amount_in_bank);
					/*$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($update_amount_in_bank)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table*/
					return 1;
				}
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNChequeWithDate($no_of_fields_in_check_data_array, $cheque_amount,$transaction_tid,$cheque_date,$bank_name,$cheque_number,$to_bank_id,$cheque_paytype,$date)
	{
		global $dbh;
		// Query Generation
		try
		{
				if($no_of_fields_in_check_data_array >0) // Insert Cheque
				{
					// $cheque_amount = $check_data_array[5];
					$cheque_insert_query = "INSERT INTO pdn_cheque(
											TID,
											Date,
											ChequeDate,
											Bank,
											ChequeNo,
											Amount,
											UserId,
											CBID,
											ChequePaymentType) VALUES (";
					$cheque_insert_query .= $transaction_tid.",'".$date."','".checkIsEmpty($cheque_date)."',
											'".$bank_name."','".$cheque_number."',
											".$cheque_amount.",".$_SESSION['UserId'].",".$to_bank_id.",".$cheque_paytype.")"; 
					/*print $cheque_insert_query;
					exit;
*/					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($cheque_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
					// Update Amount in the bank
					if($cheque_paytype==1)// 1->receipt Add Values to Bank Account
					{
						$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount + ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					}
					else // 2->payment Reduce the Amount from Bank Account
					{
						$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount - ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					}
					
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($update_amount_in_bank)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
					return 1;
				}
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	/*function InsertPDNCheque($no_of_fields_in_check_data_array, $cheque_amount,$transaction_tid,$cheque_date,$bank_name,$cheque_number,$to_bank_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				if($no_of_fields_in_check_data_array >0) // Insert Cheque
				{
					// $cheque_amount = $check_data_array[5];
					$cheque_insert_query = "INSERT INTO pdn_cheque(
											TID,
											Date,
											ChequeDate,
											Bank,
											ChequeNo,
											Amount,
											UserId,
											CBID
											) VALUES (";
					$cheque_insert_query .= $transaction_tid.",'".date('Y-m-d')."','".checkIsEmpty($cheque_date)."',
											'".$bank_name."','".$cheque_number."',
											".$cheque_amount.",".$_SESSION['UserId'].",".$to_bank_id.")"; 
					
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($cheque_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
					// Update Amount in the bank
					$update_amount_in_bank = "UPDATE pdn_company_bank SET Amount = Amount + ".$cheque_amount." WHERE IsDeleted = 0 AND CBID = ".$to_bank_id;
					
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($update_amount_in_bank)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
					return 1;
				}
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function*/
	function InsertPDNCash($transaction_tid,$cash_amount)
	{
		global $dbh;
		// Query Generation
		try
		{
				
					$cash_insert_query = "INSERT INTO pdn_cash(
											TID,
											Date,
											Amount,
											UserId) VALUES (";
					$cash_insert_query .= $transaction_tid.",'".date('Y-m-d')."',".$cash_amount.",".$_SESSION['UserId'].")"; 
					/*print $cash_insert_query;
					exit;*/

					$dbh->beginTransaction(); // Begin Transaction
					$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					if(!$dbh->exec($cash_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNCashWithReceiptDate($transaction_tid,$cash_amount,$receipt_date)
	{
		global $dbh;
		// Query Generation
		try
		{
				
					$cash_insert_query = "INSERT INTO pdn_cash(
											TID,
											Date,
											Amount,
											UserId) VALUES (";
					$cash_insert_query .= $transaction_tid.",'".$receipt_date."',".$cash_amount.",".$_SESSION['UserId'].")"; 
					/*print $cash_insert_query;
					exit;*/
					$dbh->exec($cash_insert_query);
					/*$dbh->beginTransaction(); // Begin Transaction
					$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					if(!$dbh->exec($cash_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table*/
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertPDNCashWithDate($transaction_tid,$cash_amount,$date,$is_out_standing_data)
	{
		global $dbh;
		// Query Generation
		try
		{
				
					$cash_insert_query = "INSERT INTO pdn_cash(
											TID,
											Date,
											Amount,
											UserId,IsDataFromOldSoftware) VALUES (";
					$cash_insert_query .= $transaction_tid.",'".$date."',".$cash_amount.",".$_SESSION['UserId'].",,$is_out_standing_data)"; 
					/*print $cash_insert_query;
					exit;*/

					$dbh->beginTransaction(); // Begin Transaction
					$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					if(!$dbh->exec($cash_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	//function update Receipt and Payment numbers
	function UpdateReceiptPaymentNo($fieldname)
	{
		global $dbh;
		// Query Generation
		try
		{
				$param= "$fieldname=$fieldname+1";
				$sql_update_receipt_no .= "UPDATE pdn_branchmaster SET ".$param." WHERE IsDeleted = 0 AND DefaultBranch =1";
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_receipt_no)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	//function Credit Amount in All type of Accounts table name-> debit table name,amount filed name, amount, account number field name, account number value
	function CreditDebitAmount($tablename,$amountfield,$amount,$accnofield,$accno)
	{
		global $dbh;
		// Query Generation
		try
		{
				$param= "$amountfield=$amount";
				$condition="$accnofield=$accno";
				$sql_credit_amount .= "UPDATE ".$tablename." SET ".$param." WHERE IsDeleted = 0 AND ".$condition;
				/*print $sql_credit_amount;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	//function Credit Amount in All type of Accounts table name-> debit table name,amount filed name, amount, account number field name, account number value
	function CreditAmount($tablename,$amountfield,$amount,$accnofield,$accno)
	{
		global $dbh;
		// Query Generation
		try
		{
			
				$condition="$accnofield=$accno";
				$sql ="SELECT ".$amountfield." FROM ".$tablename." WHERE IsDeleted = 0 AND ".$condition;
				 
				$query_sql = $dbh->query($sql);
				$query_sql->setFetchMode(PDO::FETCH_ASSOC);
				$result = $query_sql->fetch();	
				if($query_sql->rowCount() > 0)
					$balance = $result[$amountfield];
				else
					$balance = 0;
				$currentbalance=$balance+$amount;
			   
				$sql_credit_amount = "UPDATE ".$tablename." SET ".$amountfield."=".$currentbalance." WHERE IsDeleted = 0 AND ".$condition;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function TransferFDAmountToSD($transfer_month,$transfer_year,$transfer_from_fd_acc_no) // Update calculation table with value as 1 if transfer comleted
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql = "UPDATE pdn_fd_interest_calculation SET TransferedToSD = 1 
						WHERE Month = ".$transfer_month." AND Year = ".$transfer_year." AND FDID =".$transfer_from_fd_acc_no;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	//function Debit Amount in All type of Accounts table name-> debit table name,amount filed name, amount, account number field name, account number value
	function DebitAmount($tablename,$amountfield,$amount,$accnofield,$accno) // Debit the Amount from Any table pass the parameter Tablename,amountfield,amount,accnofieldname,accountno
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$condition="$accnofield=$accno";
				$sql ="SELECT ".$amountfield." FROM ".$tablename." WHERE IsDeleted = 0 AND ".$condition;
				
				$query_sql = $dbh->query($sql);
				$query_sql->setFetchMode(PDO::FETCH_ASSOC);
				$result = $query_sql->fetch();	
				if($query_sql->rowCount() > 0)
					$balance = $result[$amountfield];
				else
					$balance = 0;
					
				$currentbalance=$balance-$amount;
			
				$sql_credit_amount = "UPDATE ".$tablename." SET ".$amountfield."=".$currentbalance." WHERE IsDeleted = 0 AND ".$condition;
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNTransaction($eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$transaction_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET
										ManagerApproved = 0,
										EditDeleteMode = 1,
										Date = '".date('Y-m-d')."',
										EQNO = ".$eqno.",
										AccountNo = ".$accountno.",
										ATMID = ".$atmid.",
										TransType = ".$transaction_type.",
										TransMethod = ".$transaction_method.",
										Amount = ".$transaction_amount.",
										RecPayNo = ".$receipt_payment_no.",
										UserId = ".$_SESSION['UserId'].",
										TxnBillNo = '".$transaction_billno."',
										AYID = ".$ayid.",
										TransAccountType = ".$transaction_account_type." WHERE TID = ".$transaction_id;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	// Delete Cash
	function DeletePDNCash($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_cash SET
										IsDeleted = 1 WHERE TID = ".$tid;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
function DeletePDNTransfer($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transfer SET
										IsDeleted = 1 WHERE ToTID = ".$tid;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeletePDNTransferFromTid($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
				$transaction_query = "UPDATE pdn_transfer SET
										IsDeleted = 1 WHERE FromTID = ".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				$dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
				//return $transaction_tid;
				/*$isdel = 1;
				$sql = "UPDATE pdn_transfer   
						SET IsDeleted = :IsDeleted
						WHERE FromTID = :FromTID";
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$statement = $dbh->prepare($sql);
			    $statement->bindValue(":IsDeleted", $isdel);
				$statement->bindValue(":FromTID", $tid);
				$count = $statement->execute(); 
				print $count;
				exit;*/
 				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 //$dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
			// exit;
		} // End Catch
	}// End Function
	function DeletePDNTransferPayment($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transfer SET
										IsDeleted = 1 WHERE FromTID = ".$tid;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeletePDNTransaction($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction 
										SET IsDeleted = 1,ManagerApproved = 0,EditDeleteMode = 2 
										WHERE TID = ".$tid;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	// Delete Cheque data
	function DeletePDNCheque($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_cheque SET
										IsDeleted = 1 WHERE TID = ".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNSDTransaction($trans_id,$eq_no,$sdid,$sd_amount,$pay_mode,$remarks) //  During Edit Mode SDID,AMOUNT,PAYMODE EDITABLE in edit mode for updating the values use this function.
	{
		global $dbh;
		// Query Generation
		try
		{
				/*$transaction_query = "UPDATE pdn_transaction SET ManagerApproved = 0, EditDeleteMode = 1, EQNO=".$eq_no.",AccountNo=".$sdid.",Amount=".$sd_amount.",TransMethod=".$pay_mode.",Remarks='".$remarks."',ModifiedBy=".$_SESSION['UserId']." WHERE TID=".$trans_id;*/
				$transaction_query = "UPDATE pdn_transaction SET  EQNO=".$eq_no.",AccountNo=".$sdid.",Amount=".$sd_amount.",TransMethod=".$pay_mode.",Remarks='".$remarks."',ModifiedBy=".$_SESSION['UserId']." WHERE TID=".$trans_id;
				
			/*	print $transaction_query;
				exit;*/
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNSDTransactionStatus($trans_id,$sdid,$sd_amount,$pay_mode,$remarks,$status,$rmapproved)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET ManagerApproved = 0,EditDeleteMode = 1, AccountNo=".$sdid.",Amount=".$sd_amount.",TransMethod=".$pay_mode.",Remarks='".$remarks."',ModifiedBy=".$_SESSION['UserId'].",Status=".$status.",RMApproved=".$rmapproved." WHERE TID=".$trans_id;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeletePDNCashCheque($trans_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				$cash_query = "UPDATE pdn_cash SET IsDeleted=1 WHERE TID=".$trans_id;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($cash_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
				$cheque_query = "UPDATE pdn_cheque SET IsDeleted=1 WHERE TID=".$trans_id;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($cheque_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
				
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function FindSDBalanceAmount($acc_id,$account_type)
	{
	 global $dbh;
		try
		{
			if(!empty($acc_id)){
			$balanceamount=0.0;
			$creditamount=0.0;
			$debitamount=0.0;
			$receipt_query = "SELECT SUM(Amount) AS credit FROM pdn_transaction WHERE AccountNo=".$acc_id." AND ATMID=".$account_type." AND TransType=1  AND IsDeleted=0";
				$query_sql = $dbh->query($receipt_query);
				$query_sql->setFetchMode(PDO::FETCH_ASSOC);
				$result = $query_sql->fetch();	
				$creditamount = (float)$result["credit"];	
				
				$payment_query = "SELECT SUM(Amount) AS debit FROM pdn_transaction WHERE AccountNo=".$acc_id." AND ATMID=".$account_type." AND TransType=2  AND IsDeleted=0";
				$query_sql = $dbh->query($payment_query);
				$query_sql->setFetchMode(PDO::FETCH_ASSOC);
				$result = $query_sql->fetch();	
				if($result["debit"] === NULL) {
					$debitamount = 0.0;
				}
				else
				{
					$debitamount = (float)$result["debit"];
				}
				//$debitamount = (float)$result["debit"];	
				
				$balanceamount=(float)$creditamount - (float)$debitamount;
				
				$sd_query = "UPDATE pdn_savings_deposit SET BalanceAmount=".$balanceamount." WHERE SDID=".$acc_id;
				/*print 'ccc'.$creditamount;
				print 'ddddd'.$debitamount;
				print $sd_query;
				print 'bbbbbb'.$balanceamount;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sd_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				/*print 'ccc'.$creditamount;
				print 'ddddd'.$debitamount;
				print $sd_query;
				print 'bbbbbb'.$balanceamount;
				exit;*/
				return 1;
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('sd-functions','FindSDBalanceAmount',$e->getMessage());
		}
	}// End function
	function UpdateSDAccount($account_no_sd,$transfer_amount)
	{
	 global $dbh;
		try
		{
			if(!empty($account_no_sd))
			{
				$sql = "UPDATE pdn_savings_deposit SET BalanceAmount = BalanceAmount - ".$transfer_amount." WHERE IsDeleted = 0 AND Status = 1 AND SDID=".$account_no_sd;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('sd-functions','FindSDBalanceAmount',$e->getMessage());
		}
	}// End function
	function UpdateSDAccountCredit($account_no_sd,$transfer_amount)
	{
	 global $dbh;
		try
		{
			if(!empty($account_no_sd))
			{
				$sql_sd = "UPDATE pdn_savings_deposit SET BalanceAmount = BalanceAmount + ".$transfer_amount." WHERE IsDeleted = 0 AND Status = 1 AND SDID=".$account_no_sd;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_sd)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				/*print $sql_sds;
				exit;*/
				return 1;
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('sd-functions','FindSDBalanceAmount',$e->getMessage());
		}
	}// End function
	function UpdateCreditSDAccount($account_no_sd,$transfer_amount)
	{
	 global $dbh;
		try
		{
			if(!empty($account_no_sd))
			{
				$sql = "UPDATE pdn_savings_deposit SET BalanceAmount = BalanceAmount + ".$transfer_amount." WHERE IsDeleted = 0 AND Status = 1 AND SDID=".$account_no_sd;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('sd-functions','FindSDBalanceAmount',$e->getMessage());
		}
	}// End function
	
	
	function InsertPDNTransfer($date,$trans_from_acc_type,$trans_from_acc,$trans_to_acc_type,$trans_to_acc,$transfer_amount,$from_tid,$to_tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transfer_query = "INSERT INTO pdn_transfer
										(Date,
										TrnsFromAccType,
										TransFromAccount,
										TransToAccType,
										TransToAccount,
										Amount,UserId,FromTID,ToTID
										) VALUES (";
				$transfer_query .= "'".checkIsEmpty($date)."',".$trans_from_acc_type.",".$trans_from_acc.",".$trans_to_acc_type.",".$trans_to_acc.",".$transfer_amount.",".$_SESSION['UserId'].",".$from_tid.",".$to_tid.")"; 
				/*print $transfer_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transfer_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertSDTempTrans($transaction_tid,$old_sdid,$sdid,$remarks,$amount,$transtype)
	{
		global $dbh;
		// Query Generation
		try
		{
				
					$trans_insert_query = "INSERT INTO pdn_sd_temp_transaction(
											TID,
											TransDate,
											OLDSDID,
											NEWSDID,
											Remarks,
											Amount,
											Transtype) VALUES (";
					$trans_insert_query .= $transaction_tid.",'".date('Y-m-d')."',".$old_sdid.",".$sdid.",'".$remarks."',".$amount.",".$transtype.")"; 
					/*print $trans_insert_query;
					exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($trans_insert_query)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdatePDNSDOldTransaction($transaction_id,$userid,$remarks)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET
										ModifiedBy = ".$userid.",
										RMApproved = 0,Remarks='".$remarks."' WHERE TID = ".$transaction_id;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function CloseSDAccount($account_no)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_savings_deposit SET
										Status = 0 WHERE SDID = ".$account_no;
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function CloseSD($tablename,$amountfield,$amount,$accnofield,$accno)
	{
		global $dbh;
		// Query Generation
		try
		{
				$param= "$amountfield=$amount";
				$condition="$accnofield=$accno";
				$sql_credit_amount .= "UPDATE ".$tablename." SET ".$param." WHERE IsDeleted = 0 AND ".$condition;
				/*print $sql_credit_amount;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function UpdatePDNTransactionChequeApproval($transaction_date,$sd_amount,$status,$rmapprove,$transaction_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET
										Date = '".$transaction_date."',
										Amount = ".$sd_amount.",
										Status = ".$status.",
										RMApproved = ".$rmapprove."
										WHERE TID = ".$transaction_id;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function UpdatePDNChequeCollectionDate($transaction_date,$sd_amount,$transaction_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_cheque SET
										ChequeCollectionDate = '".$transaction_date."',Amount = ".$sd_amount." WHERE TID = ".$transaction_id." AND IsDeleted=0";
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function UpdatePDNSDAccountCreationDate($transaction_date,$sdid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_savings_deposit SET
										LastUpdatedDate = '".$transaction_date."' WHERE SDID =".$sdid." AND IsDeleted=0";
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function InsertInSDInterestTransferTable($month,$year,$end_of_date_in_seleted_month_year)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "INSERT INTO pdn_sd_interest_transfer(Month,Year,Date,UserId) VALUES (".$month.",".$year.",'".$end_of_date_in_seleted_month_year."',".$_SESSION['UserId'].")";
				/*print $transaction_query;
				exit;*/
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdateSDInterestTransferTable($sdid,$end_of_date_in_seleted_month_year)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_sd_interest_calculation SET TransferedToSD = 1 WHERE SDID = ".$sdid." AND DATE(Date) <= 
									 '".$end_of_date_in_seleted_month_year."'";
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function PDNFDInterestTransferProvision($fd_id,$eqno,$start_date_month,$start_date_year,$fd_start_date,$interest_percentage,$interest,$tax_value,$ayid,$is_provision_value)
	{
		global $dbh;
		try
		{
				$interest = round($interest,2);
				$start_date_month = intval($start_date_month);
				$sql_fd_provisiion = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,Status,IsInterestFromProvision) VALUES (".$fd_id.",
													 ".$eqno.",
													 ".$start_date_month.",
													 ".$start_date_year.",
													 '".$fd_start_date."',
													 ".$interest_percentage.",
													 ".$interest.",
													 ".$tax_value.",
													 ".$_SESSION['UserId'].",1,
													 ".$is_provision_value."
													 )";
					$dbh->exec($sql_fd_provisiion);
					/*print $sql_fd_provisiion;
					exit;*/
					/*$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table*/
					return 1;
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}
	function PDNFDInterestTransferFDClosing($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,Status) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".round($arr_fd_monthly_interest[$ifields],0).",
													 ".round($arr_fd_monthly_tax[$ifields],0).",
													 ".$_SESSION['UserId'].",1
													 )";
				/*print $sql;
				exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function PDNCDInterestTransferFDClosing($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_cd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,Status) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".round($arr_fd_monthly_interest[$ifields],0).",
													 ".round($arr_fd_monthly_tax[$ifields],0).",
													 ".$_SESSION['UserId'].",1
													 )";
				/*print $sql;
				exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function PDNFDInterestTransferFDPREClosing($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$pre_closing)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,Status,IsPrematureClosing) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".round($arr_fd_monthly_interest[$ifields],0).",
													 ".round($arr_fd_monthly_tax[$ifields],0).",
													 ".$_SESSION['UserId'].",1,1
													 )";
				/*print $sql;
				exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function PDNCDInterestTransferFDPREClosing($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$pre_closing)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_cd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,Status,IsPrematureClosing) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".round($arr_fd_monthly_interest[$ifields],0).",
													 ".round($arr_fd_monthly_tax[$ifields],0).",
													 ".$_SESSION['UserId'].",1,1
													 )";
				/*print $sql;
				exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function InsertInterestProvision($jlid,$eqno,$month,$year,$date,$interest_percentage,$interest,$start_date,$userid,$no_of_days)
	{
		global $dbh;
		// Query Generation
		try
		{
			$sql = "INSERT INTO pdn_jl_interest(JLID,EQNO,Month,Year,Date,InterestRatePercentage,Interest,StartDate,UserId,InterestForNoOfDays) 
					VALUES (".$jlid.",".$eqno.",".$month.",".$year.",'".$date."',".$interest_percentage.",".$interest.",'".$start_date."',".$userid.",".$no_of_days.")";
			/*print $sql;
			exit;*/
			$dbh->beginTransaction(); // Begin Transaction
			if(!$dbh->exec($sql)){
				$dbh->rollback();
				}
			$dbh->commit();
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}
	function JLActualInsertInterestProvision($jlid,$eqno,$month,$year,$date,$interest_percentage,$interest,$start_date,$userid,$no_of_days,$actual_interest_for_posting,$adate)
	{
		global $dbh;
		// Query Generation
		try
		{
			$sql = "INSERT INTO pdn_jl_interest(JLID, EQNO, Month, Year, Date, InterestRatePercentage, Interest, StartDate, UserId, InterestForNoOfDays, ActualInterest, 
					InterestPostedDate,CurrentDate) 
					VALUES (".$jlid.",".$eqno.",".$month.",".$year.",'".$date."',".$interest_percentage.",".$interest.",'".$start_date."',".$userid.",".$no_of_days.",".$actual_interest_for_posting.",'".$adate."','".date('Y-m-d')."')";
			/*print $sql;
			exit;*/
			$dbh->beginTransaction(); // Begin Transaction
			if(!$dbh->exec($sql)){
				$dbh->rollback();
				}
			$dbh->commit();
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}
	function CloseRD($tablename,$amountfield,$amount,$accnofield,$accno,$userid,$accountclosetype,$interestpercent,$interestamount,$penaltyreceived)
	{
		global $dbh;
		// Query Generation
		try
		{
				$date = date('Y-m-d');
				$param= "$amountfield=$amount,IsClosed=1,ClosedBy=$userid,ClosedDate='".$date."',AccoutClosingType=".$accountclosetype.",PaidInterestPercentage=".$interestpercent.",PaidInterestAmount=".$interestamount.",PenaltyReceived=".$penaltyreceived;
				$condition="$accnofield=$accno";
				$sql_credit_amount .= "UPDATE ".$tablename." SET ".$param." WHERE IsDeleted = 0 AND ".$condition;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function OpenRD($tablename,$amountfield,$amount,$accnofield,$accno,$userid,$accountclosetype,$interestpercent,$interestamount,$penaltyreceived,$closedby,$isclosed)
	{
		global $dbh;
		// Query Generation
		try
		{
				$date = date('Y-m-d');
				$param= "$amountfield=$amount,ClosedBy=$userid,ClosedDate='".$date."',AccoutClosingType=".$accountclosetype.",PaidInterestPercentage=".$interestpercent.",PaidInterestAmount=".$interestamount.",PenaltyReceived=".$penaltyreceived.",ClosedBy=".$closedby.",IsClosed=".$isclosed;
				$condition="$accnofield=$accno";
				$sql_credit_amount .= "UPDATE ".$tablename." SET ".$param." WHERE IsDeleted = 0 AND ".$condition;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_credit_amount)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdateRDPenalty($rdid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_rd_master SET
										Excess_Penalty_Amount = 0 WHERE RDID = ".$rdid;
				/*print $transaction_query;
				exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeleteRDPaymentDetail($trans_id)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_rd_payment_detail SET
										IsDeleted=1 WHERE TransactionID =".$trans_id;
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function CreditCompanyBank($cbid,$transaction_amount)
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_company_bank SET
										Amount = Amount + ".$transaction_amount." WHERE CBID =".$cbid;
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				//$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
				//return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			// echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdateTransactionNewAccount($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET NewAccount = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function UpdateTransactionRDPenalty($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET IsPenaltyForRD = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function UpdateTransactionIsRDInterset($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET IsInterestToRD = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdateTransactionIsInterestFromRD($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET IsInterestFromRD = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function UpdateTransactionIsPrincipalFromRD($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET IsPrincipalFromRD = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function FindDueDate($loan_date,$no_of_months)
	{
		try
		{
			$due_date = date('Y-m-d', strtotime("+".$no_of_months." months", strtotime($loan_date)));
			$new_due_date = date('Y-m-d', strtotime( $due_date . " -1 days")); // new requirment. Set due date as one day before due date
			return $new_due_date;
		}
		catch (PDOException $e)
		{
			 //ErrorTracking('share-functions','FindLastEQNO',$e->getMessage());
		}
	}
	function FindNoOfMonths($scheme_id)
	{
		global $dbh;
		try
		{
			$sql ="SELECT Scheme FROM pdn_jl_scheme_master WHERE IsDeleted = 0 AND JLSMID = ".$scheme_id;
			//print $sql;
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();
			$no_of_months = $result["Scheme"];
			return $no_of_months;
		}
		catch (PDOException $e)
		{
			 //ErrorTracking('share-functions','FindLastEQNO',$e->getMessage());
		}
	}
	function FindDefaultInterest($current_date,$grace_period_starting_date,$grace_period_percentage,$current_interest_percentage)
	{
		if($current_date >= $grace_period_starting_date)
		{
			$now = strtotime($current_date); // or your date as well
			$grace_period_date = strtotime($grace_period_starting_date);
			$datediff = $now - $grace_period_date;
			$grace_period_days = round($datediff / ( 60 * 60 * 24))+1;
			$grace_period_total_percentages = $grace_period_percentage + $current_interest_percentage;
			$default_interest = $balance_loan_amount * $grace_period_total_percentages / 100 / 365 * $grace_period_days;
		}
	}
	// Function to transfer interest to provision table
	function PDNFDInterestTransferToProvision($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$ayid,$is_provision)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,AYID,IsInterestFromProvision) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".$arr_fd_monthly_interest[$ifields].",
													 ".$arr_fd_monthly_tax[$ifields].",
													 ".$_SESSION['UserId'].",
													 ".$ayid.",
													 ".$is_provision."
													 )";
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	//insert cd interest provision
	function PDNCDInterestTransferToProvision($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$ayid,$is_provision)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_cd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,AYID,IsInterestFromProvision) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".$arr_fd_monthly_interest[$ifields].",
													 ".$arr_fd_monthly_tax[$ifields].",
													 ".$_SESSION['UserId'].",
													 ".$ayid.",
													 ".$is_provision."
													 )";
													/*print $sql;
													exit;*/
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
					$sql = "UPDATE pdn_cd_deposit SET CurrentAmountBalance = CurrentAmountBalance + ".$arr_fd_monthly_interest[$ifields]." WHERE 
							FDID = ".$arr_fd_accounts[$ifields];
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	// Function to update data for new fd account
	function PDNFDInterestTransfer($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$ayid)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,AYID) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".$arr_fd_monthly_interest[$ifields].",
													 ".$arr_fd_monthly_tax[$ifields].",
													 ".$_SESSION['UserId'].",
													 ".$ayid."
													 )";
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function PDNFDInterestTransferWithNegativeAmount($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$transfer_sd_account)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,ToSDAccount,TransferedToSD) VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 ".$arr_fd_monthly_interest[$ifields].",
													 ".$arr_fd_monthly_tax[$ifields].",
													 ".$_SESSION['UserId'].",".$transfer_sd_account.",1
													 )";
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	// partially interest transfer
	function PDNFDInterestTransferPartialAmount($arr_fd_accounts,$arr_fd_eqnos,$fd_month,$fd_year,$fd_interest_date,$fd_interest_percentage,$arr_fd_monthly_interest,$arr_fd_monthly_tax,$transfer_sd_account)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		/*print 'date'.$fd_interest_date;
		exit;*/
		// Query Generation
		try
		{
			if($no_of_fields_in_fd_accounts > 0)
			{
				
				for($ifields = 0; $ifields < $no_of_fields_in_fd_accounts; $ifields++)
				{
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,ToSDAccount,TransferedToSD,IsPartiallyInterestTransfered,PartiallyTransferedInterest,PartiallyInterestTransferedDate)
													    VALUES (".$arr_fd_accounts[$ifields].",
													 ".$arr_fd_eqnos[$ifields].",
													 ".$fd_month.",
													 ".$fd_year.",
													 '".$fd_interest_date."',
													 ".$fd_interest_percentage.",
													 0,
													 ".$arr_fd_monthly_tax[$ifields].",
													 ".$_SESSION['UserId'].",".$transfer_sd_account.",0,1,".$arr_fd_monthly_interest[$ifields].",'".$fd_interest_date."'
													 )";
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
				}
			return 1;
			}
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	
	function PDNFDInterestTransferProvisionLastMonthBalance($matureddate,$transfer_from_eqno,$fd_month,$fd_interest_percentage,$transfer_from_fd_acc_no,$balance_interest,$balance_tax,$ayid)
	{
		global $dbh;
		$no_of_fields_in_fd_accounts = sizeof($arr_fd_accounts);
		try
		{
					$month = date("m",strtotime($matureddate));
					$year = date("Y",strtotime($matureddate));
					$sql = "INSERT INTO  pdn_fd_interest_calculation(FDID,EQNO,Month,Year,Date,InterestRate,Interest,Tax,
				                                       UserId,TransferedToSD,AYID)
													    VALUES (".$transfer_from_fd_acc_no.",
													 ".$transfer_from_eqno.",
													 ".$month.",
													 ".$year.",
													 '".$matureddate."',
													 ".$fd_interest_percentage.",
													 ".$balance_interest.",
													 ".$balance_tax.",
													 ".$_SESSION['UserId'].",
													 1,
													 ".$ayid."
													 )";
					$dbh->beginTransaction(); // Begin Transaction
					if(!$dbh->exec($sql)){
						$dbh->rollback();
						return 0;
					}
					$dbh->commit();	// End Transaction Add Data to the main table
					//print $sql;
					//exit;
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','PDNFDInterestTransferProvisionLastMonthBalance',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	// Function to find last new jl account
	function GetLastJLNewAcNo($jlsid)
	{
		global $dbh;
		try
		{
			$sql = "SELECT JLAccNo,JLAcNoSixMOneY  FROM  pdn_jl_settings WHERE IsDeleted = 0 AND JLSID = ".$jlsid;
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();
			return array($result["JLAccNo"],$result["JLAcNoSixMOneY"]);
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	// Function to find last new jl account
	function UpdateJLNewAcNo($jlsid)
	{
		global $dbh;
		try
		{
			$sql = "UPDATE  pdn_jl_settings SET JLAcNoSixMOneY = JLAcNoSixMOneY + 1 WHERE IsDeleted = 0 AND JLSID = ".$jlsid;
			$dbh->beginTransaction(); // Begin Transaction
			if(!$dbh->exec($sql)){
				$dbh->rollback();
				return 0;
			}
			$dbh->commit();	// End Transaction Add Data to the main table
			return 1;
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function UpdateTransactionRDPenaltyMature($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_new_account = "UPDATE pdn_transaction SET IsPenaltyForRDMature = 1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_new_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	
	function DeletePDNCashGeneral($tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				$cash_query = "UPDATE pdn_cash SET IsDeleted=1 WHERE TID=".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($cash_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeletePDNChequeGeneral($tid,$trans_type)// trans type 1-> receipt 2-> payment
	{
		global $dbh;
		// Query Generation
		try
		{
				$sql = "SELECT Amount, CBID FROM pdn_cheque WHERE IsDeleted = 0 AND TID = ".$tid;
				$query_sql = $dbh->query($sql);
				$query_sql->setFetchMode(PDO::FETCH_ASSOC);
				$result = $query_sql->fetch();	
				if($trans_type == 1)
					$update_bank_account = "UPDATE pdn_company_bank SET Amount = Amount - ".$result['Amount']." WHERE CBID =".$result['CBID'];
				else
					$update_bank_account = "UPDATE pdn_company_bank SET Amount = Amount + ".$result['Amount']." WHERE CBID =".$result['CBID'];
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($update_bank_account)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
				$cheque_query = "UPDATE pdn_cheque SET IsDeleted=1 WHERE TID=".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($cheque_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function DeletePDNTransferGeneral($tid,$trans_type) // trans type 1->receipt 2->payment
	{
		global $dbh;
		// Query Generation
		try
		{
				if($trans_type == 1)
					$transfer_query = "UPDATE pdn_transfer SET IsDeleted=1 WHERE ToTID=".$tid;
				else if($trans_type == 2)
					$transfer_query = "UPDATE pdn_transfer SET IsDeleted=1 WHERE FromTID=".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transfer_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
		function UpdateTransactionOldEntryDate($date,$tid)
	{
		global $dbh;
		// Query Generation
		try
		{
				
				$sql_update_old_account = "UPDATE pdn_transaction SET Date ='".$date."',IsOldOpening=1 WHERE IsDeleted = 0 AND TID =".$tid;
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql_update_old_account)){
					$dbh->rollback();
					return 0;
				}
				// $sdid -> This is the primary key value of details table and insert this value n the transaction table and it is used for further edit transaction purpose
				// $sdid = $dbh->lastInsertId();
				$dbh->commit();	// End Transaction Add Data to the share details table
					return 1;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	function PDNOLDSDInterestTransfer($sdid,$eqno,$month,$year,$sd_interest_date,$interest_rate,$interest_for_sd_account)
	{
		global $dbh;
		// Query Generation
		try
		{
			$sql_sd_interest = "INSERT INTO pdn_sd_interest_calculation(SDID,EQNO,Month,Year,Date,InterestRate,Interest) VALUES (
					".$sdid.",".$eqno.",".$month.",".$year.",'".$sd_interest_date."',".$interest_rate.",".round($interest_for_sd_account,0).")";
			//$dbh->exec($sql_sd_interest);
			$dbh->beginTransaction(); // Begin Transaction
			if(!$dbh->exec($sql_sd_interest)){
				$dbh->rollback();
				return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the main table
				return 1;
		} // End Try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function UpdatePDNSDTransactionOld($trans_id,$sd_interest,$sdid,$sd_amount,$entry_date,$remarks) //  During Edit Mode SDID,AMOUNT,PAYMODE EDITABLE in edit mode for updating the values use this function.
	{
		global $dbh;
		// Query Generation
		try
		{
				$transaction_query = "UPDATE pdn_transaction SET Date='".$entry_date."',Amount=".$sd_amount.",Remarks='".$remarks."',ModifiedBy=".$_SESSION['UserId']." WHERE TID=".$trans_id;
				
			/*	print $transaction_query;
				exit;*/
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
				$savings_query = "UPDATE pdn_savings_deposit SET BalanceAmount=".$sd_amount." WHERE SDID=".$sdid;
				
				
				
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($savings_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
				$now = new DateTime($entry_date);
				$month = $now->format('m');
				$year = $now->format('Y');
				
				$sdinterest_query = "UPDATE pdn_sd_interest_calculation SET Date='".$entry_date."',Interest=".$sd_interest.",Month=".$month.",Year=".$year." WHERE SDID=".$sdid;
				
			
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sdinterest_query)){
					$dbh->rollback();
					return 0;
				}
				$dbh->commit();	// End Transaction Add Data to the transaction table
				
				
				
				
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
	//code added by jino
//code for insert transaction table for SL and ML
function InsertPDNTransactionSLOnly($sl_date_entry,$eqno,$accountno,$atmid,$transaction_type,$transaction_method,$transaction_amount,$receipt_payment_no,$transaction_billno,$ayid,$transaction_account_type,$challan_no,$acc_code,$acc_sub_code)
	{
		global $dbh;
		// Query Generation
		try
		{
				 	$transaction_query = "INSERT INTO pdn_transaction
										(Date,
										EQNO,
										AccountNo,
										ATMID,
										TransType,
										TransMethod,
										Amount,
										RecPayNo,
										UserId,
										TxnBillNo,
										AYID,
										TransAccountType,ChallanNo,ACode,ASubCode) VALUES (";
					$transaction_query .= "'".$sl_date_entry."',".$eqno.",".$accountno.",".$atmid.",".$transaction_type.",".$transaction_method.",
									  ".$transaction_amount.",".$receipt_payment_no.",".$_SESSION['UserId'].",
									  '".$transaction_billno."',".$ayid.",".$transaction_account_type.",'".$challan_no."',".$acc_code.",".$acc_sub_code.")"; // In Transaction query For share using certificate number as AccountNo
/*print $transaction_query;
exit;*/
				$dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($transaction_query)){
					$dbh->rollback();
					return 0;
				}
				$transaction_tid = $dbh->lastInsertId(); // Transaction table primary key. This is used in cash chque transfer tables
				$dbh->commit();	// End Transaction Add Data to the transaction table
					return $transaction_tid;
		} // end try
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		} // End Catch
	}// End Function
} // End Class

