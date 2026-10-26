<?php
require_once '../settings/db.php';
require_once 'model/AccountYearModel.php';
class AccountYearController {
    private $model;
    private $errors = [];
    private $success = '';

    public function __construct($conn) {
        $this->model = new AccountYearModel($conn);
    }

    public function handleRequest() {
        $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $yearaccount = $editId > 0 ? $this->model->getAccountYearById($editId) : null;
        $accountyear = $yearaccount ? $yearaccount['AccountYear'] : '';
		$is_default = $yearaccount ? $yearaccount['IsDefault'] : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['year-submit'])) {
            error_log("POST received: " . print_r($_POST, true));
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0; 
			$is_default = trim($_POST['is_default'] ?? '');

            // Validation
            /*if (empty($accountyear)) {
                $this->errors[] = "account year is required.";
            } elseif (strlen($accountyear) > 50) {
                $this->errors[] = "accountyear cannot exceed 50 characters.";
            }*/

            error_log("Validation errors: " . print_r($this->errors, true));

            if (empty($this->errors)) {
                if ($editId > 0) {
                    if ($this->model->updateAccountYear($editId,$is_default)) {
                        $this->success = "Account year updated successfully.";
                        $editId = 0;
						$is_default = '';

                    } else {
                        $this->errors[] = "Failed to update account year .";
                    }
                } else {
                    if ($this->model->addAccountYear($accountyear,$is_default)) {
                        $this->success = "Account year  added successfully.";
                        $accountyear = '';
						$is_default = '';
                    } else {
                        $this->errors[] = "Failed to add Account year.";
                    }
                }
            }
        }

        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->deleteAccountYear($deleteId)) {
                $this->success = "Account year  deleted successfully.";
            } else {
                $this->errors[] = "Failed to delete Account year .";
            }
        }

        $accountyears = $this->model->getAllAccountYear();

        return [
            'accountyears' => $accountyears,
            'editId' => $editId,
			'accountyear' => $accountyear,
			'isdefault' => $is_default,
            'success' => $this->success,
            'errors' => $this->errors
        ];
    }
}
?>