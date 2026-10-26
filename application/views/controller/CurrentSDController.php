<?php
require_once '../settings/db.php';
require_once 'model/CurrentSDModel.php';
require_once 'model/GeneralClassModel.php'; // Include this class in all models
require_once '../php/global-functions.php'; // Include this class in all models
class CurrentSDController {
    private $model;
	private $conn;
	private $generalClassModel;
    private $errors = [];
    private $success = '';

   public function __construct($conn) {
        $this->model = new CurrentSDModel($conn);
		$this->generalClassModel = new GeneralClassModel($conn);
    }

    public function handleRequest() {
        $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $current_account = $editId > 0 ? $this->model->getMemberDetailsById($editId) : null;
        $memberid = $current_account ? $current_account['MemberId'] : '';
		$membername = $current_account ? $current_account['MemberName'] : '';
		$createddate = $current_account ? $current_account['CreatedDate'] : '';
		$amount = $current_account ? $current_account['Amount'] : '';
		$remarks = $current_account ? $current_account['Remarks'] : '';
		$upi_id = '';
// Fetch banks
     //$banks = $this->model->getAllBanks();
	 //$upis = $this->model->getAllUPIs();
//Delete Account
        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->deleteMemberDetails($deleteId)) {
                $this->success = "Account deleted Successfully ";
            } else {
                $this->errors[] = "Failed to delete Account .";
            }
        }		
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account-submit'])) {
            error_log("POST received: " . print_r($_POST, true));
			$memberid   = trim($_POST['member_id'] ?? 0);
            $membername = trim($_POST['member_name'] ?? '');
            $createddate = trim($_POST['current-date'] ?? '');
			$createddate = checkIsEmpty($createddate);
			$amount     = trim($_POST['sd-amount'] ?? '');
            $remarks    = trim($_POST['remark'] ?? '');
			$userRemarks = $remarks;
			$current_date = checkIsEmpty($_POST['current-date'] ?? '');
			//$current_date = date('Y-m-d'); // transaction date
			$transaction_method  = trim($_POST['cash-cheque-rtgs-upi'] ?? ''); // cash / cheque / rtgs / upi
            if (empty($this->errors)) 
			{  
					
					
					list($ayid,$account_start_date) = FindCurrentFinancialYearAndStartDateForADate($current_date);// 
					$atmid = 1;// Savings current ast_account_type table
					$trans_type = 1; // 1->receipt , 2-> payment
					$trans_method = $transaction_method;
					$receipt_chellan_no = $this->generalClassModel->generateReceiptChallan();
					$acode = 3;
					$asubcode = 1;
					$interest_type = 0;
					$pr_int_penalty = 1; // 1-> principal amount, 2-> interest, 3->penalty
					$remarks = '';
					$narration = 'Savings Deposit'; // Savings Receipt, Savings Payment
					$user_id = $_SESSION['UserId'];
					if($transaction_method == 1) // CASH
					{
						$amount = trim($_POST['sd-amount'] ?? '');
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
						$cheque_rtgs_upi_number    = trim($_POST['cheque-number'] ?? ''); // DATA BASE Field <ChequeNumber>
						$upi_type = 0;
						$from_bank    = trim($_POST['cheque-from-bank'] ?? '');// DATA BASE Field <FromBank>
						$to_bank    = trim($_POST['cheque-to-bank'] ?? '');// DATA BASE Field <ToBank>
						$amount    = trim($_POST['cheque-amount'] ?? '');//  DATA BASE Field <Amount>
						$upi_id = 0;//  DATA BASE Field <UPITransactionId>
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
					}
					else if($transaction_method == 4) // UPI
					{
						// GET UPI DETAILS
						//$upi_entered_date   = trim($_POST['upi-entered-date'] ?? '');// DATA BASE Field <ChequeDate>
						$cheque_date = NULL;
						$cheque_rtgs_upi_number    = trim($_POST['upi-id'] ?? ''); // DATA BASE Field <ChequeNumber>
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
						//$from_bank = 0;
						//$to_bank    = trim($_POST['upi-to-bank'] ?? '');// DATA BASE Field <ToBank>
						$amount    = trim($_POST['upi-amount'] ?? '');//  DATA BASE Field <Amount>
						$upi_id = trim($_POST['upi-id'] ?? '');//  DATA BASE Field <UPITransactionId>
					}
					$savings_id = $this->model->addMemberDetails($memberid,$membername,$createddate,$amount,$userRemarks);
					// INSERT TRANSACTION VALUES
					$account_no = $savings_id; // Primary key 
					// Insert data in transaction tables
					$cash_transaction =	$this->generalClassModel->addTransactionCashChequeRTGSUPI($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
							$trans_method, $receipt_chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$memberid);
							
					// END INSERT TRANSACTION VALUES
					if ($savings_id) {
                        $this->success = " Savings Current Account Created Successfully";
						$memberid = '';
						$membername = '';
						$createddate = '';
						$amount = '';
						$remarks = '';
						// INSERT A RECORD IN THE TRANSACTION TABLE
						
                    } else {
                        $this->errors[] = "Transaction Failed";
                    }
                }
        }
       
		/*-------------------------------------------------------SD current Delete receipt code Start-----------------------------------------------------------------*/
        $sdno = $this->model->getSDNumbers();
    	/*print 'aaa'.$sdno;
		exit;*/
		$challanno = $this->model->getTodayReceiptChallanNumbers();
		
		$searchmemberdelete = [];
		if (isset($_POST['search-submit-sd-delete-receipt']))
		{
			$tid = $_POST['search-field'];
			if (!empty($tid))
			{
				$searchmemberdelete = $this->model->searchSDReceipt($tid);
			}
			else
			{
				$this->errors[] = "Please Select Challan Number";
			}
		}
		if (isset($_GET['delete']))
		{
			$tid  = $_GET['tid'] ?? 0;
			$scid = $_GET['scid'] ?? 0;
			$result = $this->model->deleteSDReceipt($tid, $scid);
			if ($result['status'])
			{
				$updatedBalance = $this->model->getBalanceAmount($scid);
				$scno = '';
				foreach($sdno as $row)
				{
					if($row['SCID'] == $scid)
					{
						$scno = $row['SCNO'];
						break;
					}
				}
				$_SESSION['success'] = "SD No : " . $scno . " Receipt Deleted Successfully. Updated Balance : " . number_format($updatedBalance,2);
			}
			else
			{
				$_SESSION['error'] = $result['message'];
			}
			header("Location: sd-current-delete-receipt.php");
			exit;
		}
		/*-------------------------------------------------------SD current Delete receipt code End-----------------------------------------------------------------*/
			
		//-------------------------------------------------------SD CURRENT ACCOUNT DELETE code Start---------------------------------------------------------------*/
    	// LOAD TODAY ACCOUNT NUMBERS
    	$current_account_no  = $this->model->getTodayAccountNumbers();
		
		$searchmemberaccountdelete = [];
		// SEARCH
		if (isset($_POST['search-submit-sd-delete-account']))
		{
			$scid = $_POST['account-search-field'];
			
			if (!empty($scid))
			{
   			 $searchmemberaccountdelete = $this->model->searchSDaccount($scid);
			}
			else
			{
				$this->errors[] = "Please Select account Number";
			}
		}
		/*print_r($data);
		exit;*/
		
		// DELETE
		if (isset($_GET['delete_current']))
		{
			$tid  = $_GET['tid'] ?? 0;
			$scid = $_GET['scid'] ?? 0;
			$result = $this->model->deleteSDAccount($scid);
			if ($result['status'])
			{
				$_SESSION['success'] =
   					 "Savings Account Deleted Successfully";
			}
			else
			{
				$_SESSION['error'] = $result['message'];
			}
			header("Location: sd-current-delete-account.php");
			exit;
		}
		/******************* END SD ACCOUNT DELETE END **************************/
		//Fetch All Accounts
        $savings_current = $this->model->getAllMemberDetails();
		//print_r($savings_current);
		return [
			'savings_current' => $savings_current,
            'memberid' => $memberid,
            'membername' => $membername,
            'createddate' => $createddate,
            'amount' => $amount,
            'remarks' => $remarks,
			'sdno' => $sdno,
			//'sdaccount' => $sdaccount,
			'challanno' => $challanno,
			'searchmemberdelete' => $searchmemberdelete,
			'searchmemberaccountdelete' => $searchmemberaccountdelete,
			'upiType' => $upi_id,
			'current_account_no' => $current_account_no,
            'editId' => $editId,
            'success' => $this->success,
            'errors' => $this->errors
        	];
	}
}
?>