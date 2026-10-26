<?php
require_once '../settings/db.php';
require_once 'model/SavingsDailyModel.php';

class SavingsDailyController {

    private $model;

    public function __construct($conn) {
        $this->model = new SavingsDailyModel($conn);
    }

    public function handleRequest() {

        $data = [
            'savingsList' => $this->model->getSavingsList(),
            'editId' => 0,
            'installmentAmount' => '',
            'D100' => '',
            'D200' => '',
            'D300' => '',
            'success' => '',
            'errors' => []
        ];

        // EDIT
        if (isset($_GET['edit_id'])) {
            $row = $this->model->getById($_GET['edit_id']);
            if ($row) {
                $data['editId'] = $row['DSID'];
                $data['installmentAmount'] = $row['InstallmentAmount'];
                $data['D100'] = $row['D100'];
                $data['D200'] = $row['D200'];
                $data['D300'] = $row['D300'];
            }
        }

        // DELETE
        if (isset($_GET['delete_id'])) {
            $this->model->delete($_GET['delete_id']);
            header("Location: aster-master-savings-daily.php");
            exit;
        }

        // SUBMIT
        if (isset($_POST['savings-submit'])) {

            $form = [
                'installment_amount' => trim($_POST['installment_amount']),
                'D100' => trim($_POST['D100']),
                'D200' => trim($_POST['D200']),
                'D300' => trim($_POST['D300']),
                'user_id' => $_SESSION['UserId']
            ];

            if ($_POST['edit_id'] > 0) {
                $this->model->update($_POST['edit_id'], $form);
                $msg = "Savings updated successfully.";
            } else {
                $this->model->insert($form);
                $msg = "Savings added successfully.";
            }

            // Prevent duplicate insert on refresh
            header("Location: aster-master-savings-daily.php?success=" . urlencode($msg));
            exit;
        }

        if (isset($_GET['success'])) {
            $data['success'] = $_GET['success'];
        }

        return $data;
    }
}

?>
