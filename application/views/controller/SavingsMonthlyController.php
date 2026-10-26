<?php
require_once '../settings/db.php';
require_once 'model/SavingsMonthlyModel.php';
require_once 'model/GeneralClassModel.php';
require_once '../php/global-functions.php';
require_once '../settings/constants.php';

class SavingsMonthlyController {

    private $model;

    public function __construct($conn) {
		$this->conn = $conn;
        $this->model = new SavingsMonthlyModel($conn);
		$this->generalClassModel = new GeneralClassModel($conn);
    }

    public function handleRequest() {

        $data = [
            'savingsList' => $this->model->getSavingsList(),
            'editId' => 0,
            'installmentAmount' => '',
            'years2' => '',
            'years3' => '',
            'years4' => '',
            'years5' => '',
            'success' => '',
            'errors' => []
        ];

        // EDIT
        if (isset($_GET['edit_id'])) {
            $row = $this->model->getById($_GET['edit_id']);
            if ($row) {
                $data['editId'] = $row['SMID'];
                $data['installmentAmount'] = $row['InstallmentAmount'];
                $data['years2'] = $row['Years2'];
                $data['years3'] = $row['Years3'];
                $data['years4'] = $row['Years4'];
                $data['years5'] = $row['Years5'];
            }
        }

        // DELETE
        if (isset($_GET['delete_id'])) {
            $this->model->delete($_GET['delete_id']);
            header("Location: aster-master-savings-monthly.php");
            exit;
        }

        // SUBMIT
        if (isset($_POST['savings-submit'])) {

            $form = [
                'installment_amount' => trim($_POST['installment_amount']),
                'years2' => trim($_POST['years2']),
                'years3' => trim($_POST['years3']),
                'years4' => trim($_POST['years4']),
                'years5' => trim($_POST['years5']),
                'user_id' => $_SESSION['UserId']
            ];

            if ($_POST['edit_id'] > 0) {
                $this->model->update($_POST['edit_id'], $form);
                $data['success'] = "Savings updated successfully.";
            } else {
                $this->model->insert($form);
                $data['success'] = "Savings added successfully.";
            }

            $data['savingsList'] = $this->model->getSavingsList();
        }
		
//code updated on 18-6-26		
/*-------------------------------------------------------------------- SD Monthly Create Account code Start-------------------------------------------------------*/		
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account-monthly-submit'])) 
		{
			error_log("POST received: " . print_r($_POST, true));
			$cashamount = (float) ($_POST['cash-amount'] ?? 0);
			$memberid = trim($_POST['member-id'] ?? 0);
			//$sdid = $_POST['sdid'] ?? 0;
			$member_name = trim($_POST['member-name'] ?? 0);
			$transaction_method  = trim($_POST['cash-cheque-rtgs-upi'] ?? ''); // cash / cheque / rtgs / upi
			$account_create_date = trim($_POST['account-created-date'] ?? ''); // transaction date
			$current_date = checkIsEmpty($account_create_date);
			$saving_scheme_type = trim($_POST['saving-type'] ?? '');
			$interest_percentage = trim($_POST['interest-percentage'] ?? '');
			$smid = trim($_POST['installment-amount'] ?? '');
			$installment_amount =$this->model->getInstallmentAmountBySMID($smid);
			$total_interest = (float) str_replace(',', '', $_POST['total-interest'] ?? 0);
			$matured_amount = (float) str_replace(',', '', $_POST['matured-amount'] ?? 0);
			
			//var_dump($matured_amount);
			//exit;
			
			// Common Amount Detection
			if ($transaction_method == 1) // CASH
			{
				$amount = (float) ($_POST['cash-amount'] ?? 0);
			}
			else if ($transaction_method == 2) // CHEQUE
			{
				$amount = (float) ($_POST['cheque-amount'] ?? 0);
			}
			else if ($transaction_method == 3) // RTGS
			{
				$amount = (float) ($_POST['rtgs-amount'] ?? 0);
			}
			else if ($transaction_method == 4) // UPI
			{
				$amount = (float) ($_POST['upi-amount'] ?? 0);
			}
			else
			{
				$amount = 0;
			}
			
			// Validate amount should be multiple of 50
			if($amount % 50 != 0)
			{
				$this->errors[] = "Amount should be multiple of 50 (50,100,150,200...)";
			}
			if (empty($this->errors)) 
			{ 
				$trans_type = 1; // 1->receipt , 2-> payment
				//$savings_id = $this->model->updateSDCurrentPayment($scid, $amount);
				//$account_no = $sdid; // Primary key 
				list($ayid,$account_start_date) = FindCurrentFinancialYearAndStartDateForADate($current_date);//
				$atmid = 3;// Savings current ast_account_type table
				if($trans_type == 1)
					$chellan_no = $this->generalClassModel->generateReceiptChallan();
				else
					$chellan_no = $this->generalClassModel->generatePaymentChallan();
					
				$trans_method = $transaction_method;
				$acode = SAVINGSRD;
				$asubcode = SAVINGSRD_SUB;
				$interest_type = 0;
				$pr_int_penalty = 1; // 1-> principal amount, 2-> interest, 3->penalty
				$remarks = '';
				$narration = 'Savings Receipt'; // Savings Receipt, Savings Payment
				$user_id = $_SESSION['UserId'];
				if($transaction_method == 1) // CASH
				{
					$amount = trim($_POST['cash-amount'] ?? '');
					// Data for cheque / rtgs / upi
					$cheque_date = NULL;
					$cheque_rtgs_upi_number    = 0; // DATA BASE Field <ChequeNumber>
					$upi_type    = 0;// DATA BASE Field <UPIType>
					$from_bank = 0;// DATA BASE Field <FromBank>
					$to_bank    = 0;// DATA BASE Field <ToBank>
					$upi_id = 0;//  DATA BASE Field <UPITransactionId>
					/*$cash_transaction =	$this->generalClassModel->addTransactionCash($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
						$trans_method, $receipt_chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id);*/
				}
				else if($transaction_method == 2) // CHEQUE
				{
					// GET CHEQUE DETAILS
					$cheque_date   = trim($_POST['cheque-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
					$cheque_date = checkIsEmpty($cheque_date);
					/*echo $_POST['cheque-entered-date'];
					exit;*/
					$cheque_rtgs_upi_number    = trim($_POST['cheque-number'] ?? ''); // DATA BASE Field <ChequeNumber>
					$upi_type = 0;
					$from_bank    = trim($_POST['cheque-from-bank'] ?? '');// DATA BASE Field <FromBank>
					$to_bank    = trim($_POST['cheque-to-bank'] ?? '');// DATA BASE Field <ToBank>
					$amount    = trim($_POST['cheque-amount'] ?? '');//  DATA BASE Field <Amount>
					$upi_id = 0;//  DATA BASE Field <UPITransactionId>
					
					// CHECK BANK BALANCE
					/*$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}*/
				}
				else if($transaction_method == 3) // RTGS
				{
					// GET RTGS DETAILS
					//$rtgs_entered_date   = trim($_POST['rtgs-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
					$cheque_date = NULL;
					$cheque_rtgs_upi_number    = trim($_POST['reference-number'] ?? ''); // DATA BASE Field <ChequeNumber>
					$upi_type = 0;
					if($trans_type == 1)
					{
						$from_bank = 0;
						$to_bank    = trim($_POST['rtgs-to-bank'] ?? '');// DATA BASE Field <ToBank>
					}
					else
					{
						$from_bank = trim($_POST['rtgs-from-bank'] ?? '');// DATA BASE Field <FromBank>
						$to_bank    = 0;// DATA BASE Field <ToBank>
					}
					$amount    = trim($_POST['rtgs-amount'] ?? '');//  DATA BASE Field <Amount>
					$upi_id = 0;//  DATA BASE Field <UPITransactionId>
					
					// CHECK BANK BALANCE
					/*$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}*/
				}
				else if($transaction_method == 4) // UPI
				{
					// GET UPI DETAILS
					//$upi_entered_date   = trim($_POST['upi-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
					$cheque_date = NULL;
					$cheque_rtgs_upi_number    = 0; // DATA BASE Field <ChequeNumber>
					$upi_type    = trim($_POST['upi-type'] ?? '');// DATA BASE Field <UPIType>
					if($trans_type == 1)
					{
						$from_bank = 0;
						$to_bank    = trim($_POST['upi-to-bank'] ?? '');// DATA BASE Field <ToBank>
					}
					else
					{
						$from_bank = trim($_POST['upi-from-bank'] ?? '');// DATA BASE Field <FromBank>
						$to_bank    = 0;// DATA BASE Field <ToBank>
					}
					$amount    = trim($_POST['upi-amount'] ?? '');//  DATA BASE Field <Amount>
					$upi_id = trim($_POST['upi-id'] ?? '');//  DATA BASE Field <UPITransactionId>
					
					// CHECK BANK BALANCE
					/*$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}*/
				}
				
					$actualInstallmentAmount = $installment_amount;
				
				
				//first insert the data in to ast_savings_daily table			
				$accountData = $this->model->saveSavingsAccount($memberid,$member_name,$current_date,$smid,$saving_scheme_type,$interest_percentage,
    															$actualInstallmentAmount,$total_interest,$matured_amount,$user_id,$amount);
				if($accountData)
				{
					$sdmno = $accountData['sdmno'];
					$sdmid = $accountData['sdmid'];
				
					$account_no = $sdmid;
				}
				else
				{
					$this->errors[] = "Failed to create Savings Account";
				}
				
					$tid = 0;
					$savingsPaidId = 0;
					
					if(empty($this->errors))
					{
						// Insert data in transaction tables
					$tid =	$this->generalClassModel->addTransactionCashChequeRTGSUPI($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
							$trans_method, $chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$memberid);
					
						// ------------------------------------------------
						// STEP 2 : Save first installment in
						// ast_savings_paid_details
						// ------------------------------------------------
					
						if($tid)
						{
							$numberOfInstallments = $amount / $actualInstallmentAmount;
							$savingsPaidId = true;
							$lastInstallmentNo = $this->model->getLastInstallmentNo($sdmid,$atmid);
						
							for($i = 0; $i < $numberOfInstallments; $i++)
							{
								$installmentNo = $lastInstallmentNo + $i + 1;
								$installmentDate = date('Y-m-d',strtotime("+$i month", strtotime($current_date)));						
								$month = date('m', strtotime($installmentDate));
								$year  = date('Y', strtotime($installmentDate));					
								$result = $this->model->saveSavingsPaidDetails($sdmid,$atmid,$memberid,$current_date,$installmentDate,$actualInstallmentAmount,
        																		0,$installmentNo,$month,$year,$user_id,$tid);						
								if(!$result)
								{
									$savingsPaidId = false;
									break;
								}
							}
						}
						if($accountData && $tid && $savingsPaidId)
						{
							/*$_SESSION['success_msg'] =
    											"Savings Account Created Successfully.<span style='color:red;font-weight:bold;font-size:16px;'>
												SD No :".$sdno."</span>";*/
							$updatedData = $this->model->getBalanceAmount($sdmid);
							$updatedBalance = $updatedData['balanceAmount'];
												
							$_SESSION['success_msg'] ="SD No : ".$sdmno." Savings Account Created Successfully. Balance Amount : ".number_format($updatedBalance,2);
					
							header("Location: search-account.php?tp=3");
							exit;
						}
						else
						{
							$this->errors[] = "Failed to Save Savings Account";
						}
					}
			}
		}

/*-------------------------------------------------------------------- SD Monthly Create Account code End-------------------------------------------------------*/

//code updated by jino
//code updated on 18-6-26
/*-------------------------------------------------------------------- SD Monthly Reciept code Start-------------------------------------------------------------*/		
		
		///////////////code for search sd number adn fill in combobox//////////////////////////////////////
		// Fill dropdown
        $data['sdmno'] = $this->model->getSDNumbers();//This fuction for SD Currnt Payment 
        // Default empty grid
        $data['searchmember'] = [];
		// Search button clicked
        if (isset($_GET['search-submit'])) {
            $search_field = $_GET['id'];
            $data['id'] = $search_field;
            if (!empty($search_field)) {
                $data['searchmember'] = $this->model->searchMembers($search_field);
            } else {
                $data['errors'][] = "Please select SD Number";
            }
        }
		
		//Fetch daily receipt details
		$data['receiptCalculation'] = [];
		if(isset($_GET['id']))
		{
			$sdmid = $_GET['id'];		
			$data['receiptCalculation']= $this->model->getReceiptCalculation($sdmid);
		}
		
		//insert data in monthly related tables
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sd-monthly-receipt-submit'])) 
		{
			error_log("POST received: " . print_r($_POST, true));
			$cashamount = (float) ($_POST['cash-amount'] ?? 0);
			$memberid = trim($_POST['memberid'] ?? 0);
			$sdmid = $_POST['sdmid'] ?? 0;
			$transaction_method  = trim($_POST['cash-cheque-rtgs-upi'] ?? ''); // cash / cheque / rtgs / upi
			$account_reciept_date = trim($_POST['current-date'] ?? ''); // transaction date
			$current_date = checkIsEmpty($account_reciept_date);
			//$current_date = date('Y-m-d'); // transaction date
			
			// Common Amount Detection
			if ($transaction_method == 1) // CASH
			{
				$amount = (float) ($_POST['cash-amount'] ?? 0);
			}
			else if ($transaction_method == 2) // CHEQUE
			{
				$amount = (float) ($_POST['cheque-amount'] ?? 0);
			}
			else if ($transaction_method == 3) // RTGS
			{
				$amount = (float) ($_POST['rtgs-amount'] ?? 0);
			}
			else if ($transaction_method == 4) // UPI
			{
				$amount = (float) ($_POST['upi-amount'] ?? 0);
			}
			else
			{
				$amount = 0;
			}
			
			// Validate amount should be multiple of 50
			if($amount % 50 != 0)
			{
				$this->errors[] = "Amount should be multiple of 50 (50,100,150,200...)";
			}
			if (empty($this->errors)) 
			{ 
				$trans_type = 1; // 1->receipt , 2-> payment
				//$savings_id = $this->model->updateSDCurrentPayment($scid, $amount);
				$account_no = $sdmid; // Primary key 
				list($ayid,$account_start_date) = FindCurrentFinancialYearAndStartDateForADate($current_date);//
				$atmid = 3;// Savings current ast_account_type table
				if($trans_type == 1)
					$chellan_no = $this->generalClassModel->generateReceiptChallan();
				else
					$chellan_no = $this->generalClassModel->generatePaymentChallan();
					
				$trans_method = $transaction_method;
				$acode = SAVINGSRD;
				$asubcode = SAVINGSRD_SUB;
				$interest_type = 0;
				$pr_int_penalty = 1; // 1-> principal amount, 2-> interest, 3->penalty
				$remarks = '';
				$narration = 'Savings Receipt'; // Savings Receipt, Savings Payment
				$user_id = $_SESSION['UserId'];
				if($transaction_method == 1) // CASH
				{
					$amount = trim($_POST['cash-amount'] ?? '');
					// Data for cheque / rtgs / upi
					$cheque_date = NULL;
					$cheque_rtgs_upi_number    = 0; // DATA BASE Field <ChequeNumber>
					$upi_type    = 0;// DATA BASE Field <UPIType>
					$from_bank = 0;// DATA BASE Field <FromBank>
					$to_bank    = 0;// DATA BASE Field <ToBank>
					$upi_id = 0;//  DATA BASE Field <UPITransactionId>
					/*$cash_transaction =	$this->generalClassModel->addTransactionCash($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
						$trans_method, $receipt_chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id);*/
				}
				else if($transaction_method == 2) // CHEQUE
				{
					// GET CHEQUE DETAILS
					$cheque_date   = trim($_POST['cheque-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
					$cheque_date = checkIsEmpty($cheque_date);
					/*echo $_POST['cheque-entered-date'];
					exit;*/
					$cheque_rtgs_upi_number    = trim($_POST['cheque-number'] ?? ''); // DATA BASE Field <ChequeNumber>
					$upi_type = 0;
					$from_bank    = trim($_POST['cheque-from-bank'] ?? '');// DATA BASE Field <FromBank>
					$to_bank    = trim($_POST['cheque-to-bank'] ?? '');// DATA BASE Field <ToBank>
					$amount    = trim($_POST['cheque-amount'] ?? '');//  DATA BASE Field <Amount>
					$upi_id = 0;//  DATA BASE Field <UPITransactionId>
					
					// CHECK BANK BALANCE
					/*$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}*/
				}
				else if($transaction_method == 3) // RTGS
				{
					// GET RTGS DETAILS
					//$rtgs_entered_date   = trim($_POST['rtgs-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
					$cheque_date = NULL;
					$cheque_rtgs_upi_number    = trim($_POST['reference-number'] ?? ''); // DATA BASE Field <ChequeNumber>
					$upi_type = 0;
					if($trans_type == 1)
					{
						$from_bank = 0;
						$to_bank    = trim($_POST['rtgs-to-bank'] ?? '');// DATA BASE Field <ToBank>
					}
					else
					{
						$from_bank = trim($_POST['rtgs-from-bank'] ?? '');// DATA BASE Field <FromBank>
						$to_bank    = 0;// DATA BASE Field <ToBank>
					}
					$amount    = trim($_POST['rtgs-amount'] ?? '');//  DATA BASE Field <Amount>
					$upi_id = 0;//  DATA BASE Field <UPITransactionId>
					
					// CHECK BANK BALANCE
					/*$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}*/
				}
				else if($transaction_method == 4) // UPI
				{
					// GET UPI DETAILS
					//$upi_entered_date   = trim($_POST['upi-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
					$cheque_date = NULL;
					$cheque_rtgs_upi_number    = 0; // DATA BASE Field <ChequeNumber>
					$upi_type    = trim($_POST['upi-type'] ?? '');// DATA BASE Field <UPIType>
					if($trans_type == 1)
					{
						$from_bank = 0;
						$to_bank    = trim($_POST['upi-to-bank'] ?? '');// DATA BASE Field <ToBank>
					}
					else
					{
						$from_bank = trim($_POST['upi-from-bank'] ?? '');// DATA BASE Field <FromBank>
						$to_bank    = 0;// DATA BASE Field <ToBank>
					}
					$amount    = trim($_POST['upi-amount'] ?? '');//  DATA BASE Field <Amount>
					$upi_id = trim($_POST['upi-id'] ?? '');//  DATA BASE Field <UPITransactionId>
					
					// CHECK BANK BALANCE
					/*$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}*/
				}
				
				// Insert data in transaction tables
					$tid =	$this->generalClassModel->addTransactionCashChequeRTGSUPI($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
							$trans_method, $chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$memberid);
					
					$savings_id = false;
					if(empty($this->errors))
					{
						$receiptData = $this->model->getReceiptCalculation($sdmid);
						$lastInstallmentNo = $this->model->getLastInstallmentNo($sdmid,$atmid);
						$createdDate = $this->model->getAccountCreatedDate($sdmid);	
						$installmentAmount = (float)$receiptData['dailyAmount'];
						$month = date('m');
						$year  = date('Y');
						$penalty = 0;
						$numberOfInstallments = $amount / $installmentAmount;
						$savings_id = true;						
						for ($i = 0; $i < $numberOfInstallments; $i++)
						{
							$currentInstallmentNo = $lastInstallmentNo + $i + 1;
    						$installmentDate = date('Y-m-d',strtotime('+' . ($currentInstallmentNo - 1) . ' month',strtotime($createdDate)));
							$month = date('m', strtotime($installmentDate));
							$year  = date('Y', strtotime($installmentDate));
							$result = $this->model->saveSavingsPaidDetails(
								$sdmid,
								$atmid,
								$memberid,
								$current_date,
								$installmentDate,
								$installmentAmount,
								$penalty,
								$currentInstallmentNo,
								$month,
								$year,
								$user_id,
								$tid
							);						
							if (!$result)
							{
								$savings_id = false;
								break;
							}
						}
						if ($savings_id) 
						{
							// Update account balance
    						$this->model->updateBalanceAmount($sdmid,$amount);
							
							$updatedData = $this->model->getBalanceAmount($sdmid);
							$updatedBalance = $updatedData['balanceAmount'];
				
							$sdmno = '';				
							foreach($data['sdmno'] as $row)
							{
								if($row['SDMID'] == $sdmid)
								{
									$sdmno = $row['SDMNO'];
									break;
								}
							}
							
							$this->success ="SD No : ".$sdmno." Reciept Save Successfully. Balance Amount : ".number_format($updatedBalance,2);
							
						
						}
						else 
						 {
							$this->errors[] = "Transaction Failed";
						 }
					}
			}
		}
/*-------------------------------------------------------------------- SD Monthly Reciept code End-------------------------------------------------------------*/

		$data['success'] = $this->success ?? '';
		$data['errors'] = $this->errors ?? [];	
        return $data;
    }
}
?>
