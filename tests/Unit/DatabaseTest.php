<?php
require_once __DIR__ . '/../TestCase.php';

class DatabaseTest extends TestCase {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function run() {
        $this->setTestName("Database Connection & Health");
        $this->assertTrue($this->conn instanceof mysqli, "Database connection is instance of mysqli");
        $this->assertTrue($this->conn->ping(), "Database responds to ping query");
        $this->assertTrue($this->conn->errno === 0, "No initial database connection error");

        $this->setTestName("Required Schema Tables Existence");
        $requiredTables = [
            'ast_jewel_loan_customer',
            'ast_jewel_loan_details',
            'ast_jewel_loan_items',
            'ast_jewel_loan_receipt',
            'ast_members',
            'ast_district',
            'ast_company_bank_account'
        ];

        foreach ($requiredTables as $tbl) {
            $res = $this->conn->query("SHOW TABLES LIKE '{$tbl}'");
            $this->assertTrue($res && $res->num_rows > 0, "Table `{$tbl}` exists in database");
        }

        $this->setTestName("Critical Table Columns");
        // Verify key columns in ast_jewel_loan_receipt
        $resR = $this->conn->query("SHOW COLUMNS FROM ast_jewel_loan_receipt");
        $colsR = [];
        while ($row = $resR->fetch_assoc()) {
            $colsR[] = $row['Field'];
        }
        $this->assertContains('ReceiptNo', $colsR, "ReceiptNo column exists in ast_jewel_loan_receipt");
        $this->assertContains('PrincipalPaid', $colsR, "PrincipalPaid column exists in ast_jewel_loan_receipt");
        $this->assertContains('InterestPaid', $colsR, "InterestPaid column exists in ast_jewel_loan_receipt");
        $this->assertContains('BalanceLoanAmount', $colsR, "BalanceLoanAmount column exists in ast_jewel_loan_receipt");
        $this->assertContains('InterestPeriodTo', $colsR, "InterestPeriodTo column exists in ast_jewel_loan_receipt");

        // Verify key columns in ast_jewel_loan_details
        $resD = $this->conn->query("SHOW COLUMNS FROM ast_jewel_loan_details");
        $colsD = [];
        while ($row = $resD->fetch_assoc()) {
            $colsD[] = $row['Field'];
        }
        $this->assertContains('DNumber', $colsD, "DNumber column exists in ast_jewel_loan_details");
        $this->assertContains('LoanAmount', $colsD, "LoanAmount column exists in ast_jewel_loan_details");
        $this->assertContains('BalanceLoanAmount', $colsD, "BalanceLoanAmount column exists in ast_jewel_loan_details");
        $this->assertContains('InterestRate', $colsD, "InterestRate column exists in ast_jewel_loan_details");
    }
}
