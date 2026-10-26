<?php
require_once '../settings/db.php';
require_once 'model/GlAppraiserModel.php';

class GlAppraiserController {

    private $model;

    public function __construct($conn) {
        $this->model = new GlAppraiserModel($conn);
    }

    public function handleRequest() {
        $data = [
            'appraiserList' => $this->model->getAllAppraisers(),
            'editId' => 0,
            'date' => date('Y-m-d'),
            'amountFrom' => '',
            'amountTo' => '',
            'appraiserCharge' => '',
            'success' => '',
            'errors' => []
        ];

        // EDIT
        if (isset($_GET['edit_id'])) {
            $row = $this->model->getById((int)$_GET['edit_id']);
            if ($row) {
                $data['editId'] = (int)$row['AID'];
                $data['date'] = $row['Date'];
                $data['amountFrom'] = $row['AmountFrom'];
                $data['amountTo'] = $row['AmountTo'];
                $data['appraiserCharge'] = $row['AppraiserCharge'];
            } else {
                $data['errors'][] = "Appraiser charge record not found.";
            }
        }

        // DELETE
        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->delete($deleteId)) {
                $msg = "Appraiser charge deleted successfully.";
            } else {
                $msg = "Failed to delete appraiser charge.";
            }
            header("Location: aster-master-gl-appraiser.php?success=" . urlencode($msg));
            exit;
        }

        // SUBMIT (ADD / UPDATE)
        if (isset($_POST['appraiser-submit'])) {
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
            $date = trim($_POST['date'] ?? date('Y-m-d'));
            $amountFrom = trim($_POST['amount_from'] ?? '');
            $amountTo = trim($_POST['amount_to'] ?? '');
            $appraiserCharge = trim($_POST['appraiser_charge'] ?? '');
            $userId = isset($_SESSION['UserId']) && !empty($_SESSION['UserId']) ? (int)$_SESSION['UserId'] : 12;

            // Retain submitted values
            $data['editId'] = $editId;
            $data['date'] = $date;
            $data['amountFrom'] = $amountFrom;
            $data['amountTo'] = $amountTo;
            $data['appraiserCharge'] = $appraiserCharge;

            // Validations
            if (empty($date)) {
                $data['errors'][] = "Date is required.";
            }
            if ($amountFrom === '' || !is_numeric($amountFrom) || (float)$amountFrom < 0) {
                $data['errors'][] = "Valid Amount From is required.";
            }
            if ($amountTo === '' || !is_numeric($amountTo) || (float)$amountTo < 0) {
                $data['errors'][] = "Valid Amount To is required.";
            }
            if (is_numeric($amountFrom) && is_numeric($amountTo) && (float)$amountFrom > (float)$amountTo) {
                $data['errors'][] = "Amount From cannot exceed Amount To.";
            }
            if ($appraiserCharge === '' || !is_numeric($appraiserCharge) || (int)$appraiserCharge < 0) {
                $data['errors'][] = "Valid Appraiser Charge is required.";
            }

            if (empty($data['errors'])) {
                $form = [
                    'date' => $date,
                    'amount_from' => (int)$amountFrom,
                    'amount_to' => (int)$amountTo,
                    'appraiser_charge' => (int)$appraiserCharge,
                    'user_id' => $userId
                ];

                if ($editId > 0) {
                    if ($this->model->update($editId, $form)) {
                        $msg = "Appraiser charge updated successfully.";
                        header("Location: aster-master-gl-appraiser.php?success=" . urlencode($msg));
                        exit;
                    } else {
                        $data['errors'][] = "Failed to update appraiser charge.";
                    }
                } else {
                    if ($this->model->insert($form)) {
                        $msg = "Appraiser charge added successfully.";
                        header("Location: aster-master-gl-appraiser.php?success=" . urlencode($msg));
                        exit;
                    } else {
                        $data['errors'][] = "Failed to add appraiser charge.";
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
