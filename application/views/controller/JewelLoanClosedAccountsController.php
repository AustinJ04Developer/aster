<?php
/**
 * Jewel Loan Closed Accounts Controller
 * Retrieves and serves inactive/closed jewel loan accounts
 */
require_once __DIR__ . '/../../settings/db.php';
require_once 'model/JewelLoanModel.php';

class JewelLoanClosedAccountsController {
    private $conn;
    private $model;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
        $this->model = new JewelLoanModel($this->conn);
    }

    public function handleRequest() {
        $closedLoans = $this->model->getClosedJewelLoans();

        return [
            'closedLoans' => $closedLoans
        ];
    }
}
?>

