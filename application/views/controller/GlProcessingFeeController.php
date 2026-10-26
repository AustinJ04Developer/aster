<?php
require_once '../settings/db.php';
require_once 'model/GlProcessingFeeModel.php';

class GlProcessingFeeController {

    private $model;

    public function __construct($conn) {
        $this->model = new GlProcessingFeeModel($conn);
    }

    public function handleRequest() {
        $data = [
            'feeList' => $this->model->getAllFees(),
            'editId' => 0,
            'date' => date('Y-m-d'),
            'processingFeePercentage' => '',
            'success' => '',
            'errors' => []
        ];

        // EDIT
        if (isset($_GET['edit_id'])) {
            $row = $this->model->getById((int)$_GET['edit_id']);
            if ($row) {
                $data['editId'] = (int)$row['PFID'];
                $data['date'] = $row['Date'];
                $data['processingFeePercentage'] = $row['ProcessingFeePercentage'];
            } else {
                $data['errors'][] = "Processing fee record not found.";
            }
        }

        // DELETE
        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->delete($deleteId)) {
                $msg = "Processing fee deleted successfully.";
            } else {
                $msg = "Failed to delete processing fee.";
            }
            header("Location: aster-master-gl-processing-fee.php?success=" . urlencode($msg));
            exit;
        }

        // SUBMIT (ADD / UPDATE)
        if (isset($_POST['fee-submit'])) {
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
            $date = trim($_POST['date'] ?? date('Y-m-d'));
            $processingFeePercentage = trim($_POST['processing_fee_percentage'] ?? '');
            $userId = isset($_SESSION['UserId']) && !empty($_SESSION['UserId']) ? (int)$_SESSION['UserId'] : 12;

            // Retain submitted values
            $data['editId'] = $editId;
            $data['date'] = $date;
            $data['processingFeePercentage'] = $processingFeePercentage;

            // Validations
            if (empty($date)) {
                $data['errors'][] = "Date is required.";
            }
            if ($processingFeePercentage === '' || !is_numeric($processingFeePercentage)) {
                $data['errors'][] = "Valid Processing Fee Percentage (%) is required.";
            } elseif ((float)$processingFeePercentage < 0 || (float)$processingFeePercentage > 100) {
                $data['errors'][] = "Processing Fee Percentage must be between 0% and 100%.";
            }

            if (empty($data['errors'])) {
                $form = [
                    'date' => $date,
                    'processing_fee_percentage' => (float)$processingFeePercentage,
                    'user_id' => $userId
                ];

                if ($editId > 0) {
                    if ($this->model->update($editId, $form)) {
                        $msg = "Processing fee updated successfully.";
                        header("Location: aster-master-gl-processing-fee.php?success=" . urlencode($msg));
                        exit;
                    } else {
                        $data['errors'][] = "Failed to update processing fee.";
                    }
                } else {
                    if ($this->model->insert($form)) {
                        $msg = "Processing fee added successfully.";
                        header("Location: aster-master-gl-processing-fee.php?success=" . urlencode($msg));
                        exit;
                    } else {
                        $data['errors'][] = "Failed to add processing fee.";
                    }
                }
            }
        }

        if (isset($_GET['success'])) {
            $data['success'] = $_GET['success'];
        }

        return $data;
    }
}
?>
