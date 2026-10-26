<?php
require_once '../settings/db.php';
require_once 'model/CurrentSDModel.php';
require_once 'model/GeneralClassModel.php';
require_once '../php/global-functions.php';
class CurrentSDPaymentController 
{
    private $model;
	private $conn;
	private $generalClassModel; 
    private $errors = [];
    private $success = '';

   public function __construct($conn) {
   		$this->conn = $conn;
        $this->model = new CurrentSDModel($conn);
		$this->generalClassModel = new GeneralClassModel($conn);
    }

    public function handleRequest($delete_transaction_type)
    {
        $data = [];
		///////////////code for search sd number adn fill in combobox//////////////////////////////////////
		// Fill dropdown
        $data['sdno'] = $this->model->getSDNumbers();
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
		//code updated on 10-7-2026
//code updated by jino
////////////////////////////////////////////////////code for Current payment Start////////////////////////////////////////////////////////////////////////////
        //$scid = $_REQUEST['scid'] ?? 0;
		$scid = $_POST['scid'] ?? $_GET['id'] ?? 0;
		$memberid = $_POST['memberid'] ?? 0;

        // Fetch balance
        $data['balanceAmount'] = $this->model->getBalanceAmount($scid);
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sd-current-payment-submit'])) 
		{
            error_log("POST received: " . print_r($_POST, true));
			$cashamount = (float) ($_POST['cash-amount'] ?? 0);
			$memberid   = trim($_POST['member_id'] ?? 0);
			$scid = $_REQUEST['scid'] ?? 0;
			$transaction_method  = trim($_POST['cash-cheque-rtgs-upi'] ?? ''); // cash / cheque / rtgs / upi
			$account_create_date = trim($_POST['current-payment-date'] ?? ''); // transaction date
			$current_date = checkIsEmpty($account_create_date);
			
			if(isset($_POST['current-sd-account-close']))
			{
				$account_close = trim($_POST['current-sd-account-close'] ?? 0);
			}
			else
			{
				$account_close = 0;
			}
			
			// Common Amount Detection
			if ($transaction_method == 1) // CASH
			{
				$amount = (float) str_replace(',', '', $_POST['cash-amount'] ?? 0);
			}
			else if ($transaction_method == 2) // CHEQUE
			{
				$amount = (float) str_replace(',', '', $_POST['cheque-amount'] ?? 0);
			}
			else if ($transaction_method == 3) // RTGS
			{
				$amount = (float) str_replace(',', '', $_POST['rtgs-amount'] ?? 0);
			}
			else if ($transaction_method == 4) // UPI
			{
				$amount = (float) str_replace(',', '', $_POST['upi-amount'] ?? 0);
			}
			else
			{
				$amount = 0;
			}
			
			// Get current balance
    		$currentBalance = $this->model->getBalanceAmount($scid);
			
			// Validation
			if ($amount <= 0)
			{
				$this->errors[] = "Invalid Amount";
			}
			
			if ($amount > $currentBalance)
			{
				$this->errors[] = "Insufficient Balance";
			}
			
			if (empty($this->errors)) 
			{ 
				$trans_type = 2; // 1->receipt , 2-> payment
				//$savings_id = $this->model->updateSDCurrentPayment($scid, $amount);
				$account_no = $scid; // Primary key 
				list($ayid,$account_start_date) = FindCurrentFinancialYearAndStartDateForADate($current_date);//
				$atmid = 1;// Savings current ast_account_type table
				if($trans_type == 1)
					$chellan_no = $this->generalClassModel->generateReceiptChallan();
				else
					$chellan_no = $this->generalClassModel->generatePaymentChallan();
					
				$trans_method = $transaction_method;
				$acode = 3;
				$asubcode = 1;
				$interest_type = 0;
				$pr_int_penalty = 1; // 1-> principal amount, 2-> interest, 3->penalty
				$remarks = '';
				$narration = 'Savings Payment'; // Savings Receipt, Savings Payment
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
					$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}
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
					$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}
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
					$bankBalance = $this->model->getBankBalance($from_bank);
				
					if($amount > $bankBalance)
					{
						$this->errors[] = "Insufficient Bank Balance";
					}
				}
				
				// Insert data in transaction tables
					$cash_transaction =	$this->generalClassModel->addTransactionCashChequeRTGSUPI($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
							$trans_method, $chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$memberid);
							
				// Decide which update to run
				if(isset($_POST['current-sd-account-close']))
				{
					$result = $this->model->updateSDAccountClosed($scid,$amount);
				}
				else
				{
					$result = $this->model->updateSDCurrentPayment($scid,$amount);
				}
				if ($result)
				{
					$updatedBalance = $this->model->getBalanceAmount($scid);
				
					$scno = '';
				
					foreach($data['sdno'] as $row)
					{
						if($row['SCID'] == $scid)
						{
							$scno = $row['SCNO'];
							break;
						}
					}
				
					// Closing success message
					if(isset($_POST['current-sd-account-close']) 
					   && $_POST['current-sd-account-close'] == 1)
					{
						$this->success =
							"SD No : ".$scno.
							" Account Closed Successfully";
					}
					else
					{
						// Payment success message
						$this->success =
							"SD No : ".$scno.
							" Payment Updated Successfully. Remaining Balance : ".
							number_format($updatedBalance,2);
					}
				}
				else
				{
					$this->errors[] = "Transaction Failed";
				}
			}
				
		}
		

////////////////////////////////////////////////////code for Current payment End////////////////////////////////////////////////////////////////////////////
		/*-------------------------------------------------------SD current Delete payment code Start-----------------------------------------------------------------*/
    	// LOAD TODAY CHALLAN NUMBERS
		
    	$data['challanno'] = $this->model->getTodayChallanNumbers($delete_transaction_type);
		// DEFAULT EMPTY
		$data['searchmemberdelete'] = [];
		// SEARCH
		if (isset($_POST['search-submit-sd-delete-payment']))
		{
			$tid = $_POST['search-field'];
			$current_trans_type = 2;
			if (!empty($tid))
			{
				$data['searchmemberdelete'] = $this->model->searchSDPayment($tid,$current_trans_type);
			}
			else
			{
				$this->errors[] = "Please Select Challan Number";
			}
		}
		// DELETE
		if (isset($_GET['delete']))
		{
			$tid  = $_GET['tid'] ?? 0;
			$scid = $_GET['scid'] ?? 0;
			$result = $this->model->deleteSDPayment($tid, $scid);
			if ($result['status'])
			{
				// Get updated balance after amount added back
				$updatedBalance = $this->model->getBalanceAmount($scid);
				// Get SD Number
				$scno = '';
				foreach($data['sdno'] as $row)
				{
					if($row['SCID'] == $scid)
					{
						$scno = $row['SCNO'];
						break;
					}
				}
				$_SESSION['success'] =
					"SD No : " . $scno .
					" Payment Deleted Successfully. Updated Balance : " .
					number_format($updatedBalance,2);
			}
			else
			{
				$_SESSION['error'] = $result['message'];
			}
			header("Location: sd-current-delete-payment.php");
			exit;
		}
/******************* END DELETE PAYAMENT **************************/
		/*-------------------------------------------------------SD current Ledger Report code Start-----------------------------------------------------------------*/

		// Ledger Report
		$data['ledgerData'] = [];
		$data['accountInfo'] = [];
		$data['scno'] = $this->model->getAllSDCurrentAccounts();
		if(isset($_GET['sd-current-ledger-report']))
		{
    		$scid = $_GET['id'] ?? 0;
			$atmid = 1; // Current SD
			if($scid > 0)
			{
				$data['accountInfo'] = $this->model->getSDCurrentAccountDetails($scid);
				$data['ledgerData']  = $this->model->getSDCurrentLedgerTransactions($scid,$atmid);
				$data['openingTransaction'] = $this->model->getSDCurrentOpeningTransaction($scid,$atmid);
			}
		}	
		//******END SD current Ledger Report code Sta*******//
		$data['success'] = $this->success ?? '';
		$data['errors'] = $this->errors ?? [];
		$data['balanceAmount'] = $this->model->getBalanceAmount($scid);
        return $data;
    }
}
?>