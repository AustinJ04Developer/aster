<?php
require_once '../settings/db.php';
require_once 'model/GlSchemeModel.php';

class GlSchemeController {

    private $model;

    public function __construct($conn) {
        $this->model = new GlSchemeModel($conn);
    }

    public static function formatDnumber($num) {
        $n = (int)$num;
        return 'D' . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }

    public function handleRequest() {
        $data = [
            'schemeList' => $this->model->getAllSchemes(),
            'editId' => 0,
            'date' => date('Y-m-d'),
            'interestPercentage' => '',
            'amountFrom' => '',
            'amountTo' => '',
            'success' => '',
            'errors' => []
        ];

        // EDIT
        if (isset($_GET['edit_id'])) {
            $row = $this->model->getById((int)$_GET['edit_id']);
            if ($row) {
                $data['editId'] = (int)$row['LSID'];
                $data['date'] = $row['Date'];
                $data['interestPercentage'] = (int)$row['InterestPercentage'];
                $data['amountFrom'] = $row['AmountFrom'];
                $data['amountTo'] = $row['AmountTo'];
            } else {
                $data['errors'][] = "Scheme record not found.";
            }
        }

        // DELETE
        if (isset($_GET['delete_id'])) {
            $deleteId = (int)$_GET['delete_id'];
            if ($this->model->delete($deleteId)) {
                $msg = "Scheme deleted successfully.";
            } else {
                $msg = "Failed to delete scheme.";
            }
            header("Location: aster-master-gl-schemes.php?success=" . urlencode($msg));
            exit;
        }

        // SUBMIT (ADD / UPDATE)
        if (isset($_POST['scheme-submit'])) {
            $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
            $date = trim($_POST['date'] ?? date('Y-m-d'));
            // Auto-generate D Number (keep the column populated)
            $dnumber = $editId > 0 ? 0 : $this->model->getNextDnumber();
            if ($editId > 0) {
                $existingRow = $this->model->getById($editId);
                if ($existingRow) {
                    $dnumber = (int)$existingRow['Dnumber'];
                }
            }

            $interestPercentage = trim($_POST['interest_percentage'] ?? '');
            $amountFrom = trim($_POST['amount_from'] ?? '');
            $amountTo = trim($_POST['amount_to'] ?? '');
            $userId = isset($_SESSION['UserId']) && !empty($_SESSION['UserId']) ? (int)$_SESSION['UserId'] : 12;

            // Retain submitted values in case of error
            $data['editId'] = $editId;
            $data['date'] = $date;
            $data['interestPercentage'] = $interestPercentage;
            $data['amountFrom'] = $amountFrom;
            $data['amountTo'] = $amountTo;

            // Validations
            if (empty($date)) {
                $data['errors'][] = "Date is required.";
            }

            $allowedRates = ['18', '24', '30'];
            if ($interestPercentage === '' || !in_array($interestPercentage, $allowedRates, true)) {
                $data['errors'][] = "Interest Percentage must be 18%, 24%, or 30%.";
            }

            if ($amountFrom === '' || !is_numeric($amountFrom) || (int)$amountFrom < 0) {
                $data['errors'][] = "Valid Amount From is required.";
            }
            if ($amountTo === '' || !is_numeric($amountTo) || (int)$amountTo < 0) {
                $data['errors'][] = "Valid Amount To is required.";
            }
            if (is_numeric($amountFrom) && is_numeric($amountTo) && (int)$amountFrom > (int)$amountTo) {
                $data['errors'][] = "Amount From cannot exceed Amount To.";
            }

            if (empty($data['errors'])) {
                $form = [
                    'date' => $date,
                    'dnumber' => $dnumber,
                    'interest_percentage' => (float)$interestPercentage,
                    'amount_from' => (int)$amountFrom,
                    'amount_to' => (int)$amountTo,
                    'user_id' => $userId
                ];

                if ($editId > 0) {
                    if ($this->model->update($editId, $form)) {
                        $msg = "Scheme updated successfully.";
                        header("Location: aster-master-gl-schemes.php?success=" . urlencode($msg));
                        exit;
                    } else {
                        $data['errors'][] = "Failed to update scheme. Please check database connection.";
                    }
                } else {
                    if ($this->model->insert($form)) {
                        $msg = "Scheme added successfully.";
                        header("Location: aster-master-gl-schemes.php?success=" . urlencode($msg));
                        exit;
                    } else {
                        $data['errors'][] = "Failed to add scheme. Please check database connection.";
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
