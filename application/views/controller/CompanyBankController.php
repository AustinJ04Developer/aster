<?php
require_once '../settings/db.php';
require_once 'model/CompanyBankModel.php';
class CompanyBankController {
    private $model;
    private $errors = [];
    private $success = '';

    public function __construct($conn) {
        $this->model = new CompanyBankModel($conn);
    }

    public function handleRequest() {
        $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $companybank = $editId > 0 ? $this->model->getCompanyBankById($editId) : null;
        $bankname = $companybank ? $companybank['BankName'] : '';
		$balance = $companybank ? $companybank['Balance'] : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bank-submit'])) {
            error_log("POST received: " . print_r($_POST, true));
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0; 
			$bankname = trim($_POST['bankname'] ?? '');
			$balance = trim($_POST['balance'] ?? '');

            // Validation
            if (empty($bankname)) {
                $this->errors[] = "Bankname is required.";
            } elseif (strlen($bankname) > 50) {
                $this->errors[] = "Bankname cannot exceed 50 characters.";
            }

            error_log("Validation errors: " . print_r($this->errors, true));

            if (empty($this->errors)) {
                if ($editId > 0) {
                    if ($this->model->updateCompanybank($editId,$bankname,$balance)) {
                        $this->success = "Bank name updated successfully.";
                        $editId = 0;
                        $bankname = '';
						$balance = '';

                    } else {
                        $this->errors[] = "Failed to update bankname .";
                    }
                } else {
                    if ($this->model->addCompanybank($bankname,$balance)) {
                        $this->success = "Bank name  added successfully.";
                        $bankname = '';
						$balance = '';
                    } else {
                        $this->errors[] = "Failed to add bankname.";
                    }
                }
            }
        }

        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->deleteCompanybank($deleteId)) {
                $this->success = "Bank name  deleted successfully.";
            } else {
                $this->errors[] = "Failed to delete bankname .";
            }
        }

        $companybanks = $this->model->getAllCompanybank();

        return [
            'companybanks' => $companybanks,
            'editId' => $editId,
			'bankname' => $bankname,
			'balance' => $balance,
            'success' => $this->success,
            'errors' => $this->errors
        ];
    }
}
?>