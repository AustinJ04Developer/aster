<?php
require_once '../settings/db.php';
require_once 'model/DailySDModel.php';
require_once 'model/GeneralClassModel.php'; // Include this class in all models
require_once '../php/global-functions.php'; // Include this class in all models
class CurrentSDController {
    private $model;
	private $conn;
	private $generalClassModel;
    private $errors = [];
    private $success = '';

   public function __construct($conn) {
        $this->model = new DailySDModel($conn);
		$this->generalClassModel = new GeneralClassModel($conn);
    }

    public function handleRequest() {
		// Fill dropdown
        $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $daily_account = $editId > 0 ? $this->model->getMemberDetailsById($editId) : null;
        $memberid = $daily_account ? $daily_account['MemberId'] : '';
		$membername = $daily_account ? $daily_account['MemberName'] : '';
		$createddate = $daily_account ? $daily_account['CreatedDate'] : '';
		$savingstype = $daily_account ? $daily_account['SavingsType'] : '';
		$interestpercentage = $daily_account ? $daily_account['InterestPercentage'] : '';
		$installmentamount = $daily_account ? $daily_account['InstallMentAmount'] : '';
		$totalinterest = $daily_account ? $daily_account['TotalInterest'] : '';
		$maturedamount = $daily_account ? $daily_account['MaturedAmount'] : '';
//Delete Account
        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->deleteMemberDetails($deleteId)) {
                $this->success = "Account deleted Successfully ";
            } else {
                $this->errors[] = "Failed to delete Account .";
            }
        }		
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account-daily-submit'])) {
            error_log("POST received: " . print_r($_POST, true));
			$memberid   = trim($_POST['member_id'] ?? 0);
            $membername = trim($_POST['member_name'] ?? '');
            $createddate = trim($_POST['current-date'] ?? '');
			$createddate = checkIsEmpty($createddate);
			$savingstype     = trim($_POST['savings-type'] ?? '');
            $interestpercentage    = trim($_POST['interest-percentage'] ?? '');
			$installmentamount    = trim($_POST['install-amount'] ?? '');
			$totalinterest    = trim($_POST['total-interest'] ?? '');
			$maturedamount    = trim($_POST['matured-amount'] ?? '');
			$current_date = date('Y-m-d'); // transaction date         
				}
		
//Fetch All Accounts
        $savings_daily = $this->model->getAllMemberDetails();
        return [
			'savings_daily' => $savings_daily,
            'memberid' => $memberid,
            'membername' => $membername,
            'createddate' => $createddate,
            'interestpercentage' => $interestpercentage,
			'totalinterest' => $totalinterest,
			'maturedamount' => $maturedamount,
			'accountNo' =>  $data['accountNo'],
			'searchmemberdelete' => $searchmemberdelete,
			'installamount' => $installamount,
            'editId' => $editId,
            'success' => $this->success,
            'errors' => $this->errors

        ];
}
}
?>