<?php
require_once '../settings/db.php';
require_once 'model/ChitModel.php';
class ChitController {
    private $model;
    private $errors = [];
    private $success = '';

    public function __construct($conn) {
        $this->model = new ChitModel($conn);
    }

    public function handleRequest() {
        $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $chit = $editId > 0 ? $this->model->getChitById($editId) : null;
		
        $userid = $chit ? $chit['UserId'] : '';
		$monthno = $chit ? $chit['MonthNo'] : '';
		$monthlyamount = $chit ? $chit['MonthlyAmount'] : '';
		$chitamount = $chit ? $chit['ChitAmount'] : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chit-submit'])) {
		exit;
            error_log("POST received: " . print_r($_POST, true));
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
            $userid = trim($_SESSION['UserId'] ?? '');
			$monthno = trim($_POST['month_num'] ?? '');
			$monthlyamount = trim($_POST['month_amount'] ?? '');
			$chitamount = trim($_POST['chit_amount'] ?? '');
            // Validation
            if (empty($userid)) {
                $this->errors[] = "userid is required.";
            } elseif (strlen($userid) > 50) {
                $this->errors[] = "userid cannot exceed 50 characters.";
            }

            error_log("Validation errors: " . print_r($this->errors, true));

            if (empty($this->errors)) {
                if ($editId > 0) {
                    if ($this->model->updateChit($editId,$userid,$monthno,$monthlyamount,$chitamount)) {
                        $this->success = "Chit amount updated successfully.";
                        $editId = 0;
                        $userid = '';
						$monthno = '';
						$monthlyamount = '';
						$chitamount = '';
                    } else {
                        $this->errors[] = "Failed to update Chit amount.";
                    }
                } else {
                    if ($this->model->addChit($userid,$monthno,$monthlyamount,$chitamount)) {
                        $this->success = "Chit amount added successfully.";
                        $userid = '';
						$monthno = '';
						$monthlyamount = '';
						$chitamount = '';
                    } else {
                        $this->errors[] = "Failed to add Chit amount.";
                    }
                }
            }
        }

        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->deleteChit($deleteId)) {
                $this->success = "Chit  deleted successfully.";
            } else {
                $this->errors[] = "Failed to delete Chit .";
            }
        }

        $chits = $this->model->getAllChit();

        return [
            'chits' => $chits,
            'editId' => $editId,
            'userid' => $userid,
			'monthno' => $monthno,
			'monthlyamount' => $monthlyamount,
			'chitamount' => $chitamount,
            'success' => $this->success,
            'errors' => $this->errors
        ];
    }
}
?>