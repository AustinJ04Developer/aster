<?php
require_once '../settings/db.php';
require_once 'model/UpiTypeModel.php';
class UpiTypeController {
    private $model;
    private $errors = [];
    private $success = '';

    public function __construct($conn) {
        $this->model = new UpiTypeModel($conn);
    }

    public function handleRequest() {
        $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $upi = $editId > 0 ? $this->model->getUpiById($editId) : null;
        $upiname = $upi ? $upi['UPITypeName'] : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upi-submit'])) {
            error_log("POST received: " . print_r($_POST, true));
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0; 
			$upiname = trim($_POST['upiname'] ?? '');

            // Validation
            if (empty($upiname)) {
                $this->errors[] = "upiname is required.";
            } elseif (strlen($upiname) > 50) {
                $this->errors[] = "upiname cannot exceed 50 characters.";
            }

            error_log("Validation errors: " . print_r($this->errors, true));

            if (empty($this->errors)) {
                if ($editId > 0) {
                    if ($this->model->updateUpi($editId,$upiname)) {
                        $this->success = "Upi updated successfully.";
                        $editId = 0;
                        $upiname = '';

                    } else {
                        $this->errors[] = "Failed to update upi amount.";
                    }
                } else {
                    if ($this->model->addUpi($upiname)) {
                        $this->success = "Upi  added successfully.";
                        $upiname = '';
                    } else {
                        $this->errors[] = "Failed to add Upi.";
                    }
                }
            }
        }

        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->deleteUpi($deleteId)) {
                $this->success = "Upi  deleted successfully.";
            } else {
                $this->errors[] = "Failed to delete Upi .";
            }
        }

        $upis = $this->model->getAllUpi();

        return [
            'upis' => $upis,
            'editId' => $editId,
			'upiname' => $upiname,
            'success' => $this->success,
            'errors' => $this->errors
        ];
    }
}
?>