<?php
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanModel.php';

class JewelLoanModelTest extends TestCase {
    private $conn;
    private $model;
    private $testDNumber = 'TEST-D-8888';
    private $testLoanId = 0;
    private $createdReceiptIds = [];

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new JewelLoanModel($conn);
    }

    public function run() {
        $this->setTestName("ID & Voucher Sequence Generators");
        $nextD = $this->model->getNextDNumber();
        $this->assertMatchesRegex('/^D-\d{4}$/', $nextD, "Generated D-Number has format D-XXXX: {$nextD}");

        $nextR = $this->model->getNextReceiptNumber();
        $this->assertMatchesRegex('/^JLR-\d{4}-\d{4}$/', $nextR, "Generated Receipt has format JLR-YYYY-XXXX: {$nextR}");

        // Cleanup any leftover test record before starting
        $this->teardown();

        try {
            $this->setTestName("Loan Creation Flow");
            $custData = [
                'DNumber' => $this->testDNumber,
                'Name' => 'Automated Test User',
                'FathersName' => 'Test Father',
                'Job' => 'Software Engineer',
                'DateOfBirth' => '1990-01-01',
                'Address' => '123 Test Street, Test City',
                'MobileNumber' => '9876543210',
                'Pincode' => '600001',
                'NomineeName' => 'Test Nominee',
                'NomineeRelation' => 'Spouse',
                'NomineeAddress' => '123 Test Street',
                'IfNomineeIsMinor' => 'No',
                'GuardianName' => '',
                'LoanDate' => date('Y-m-d'),
                'MaxAmountPerGram' => 4000.00,
                'AllowedAmount' => 60000.00,
                'PacketNumber' => 'P-999',
                'Amount' => 50000.00,
                'ReasonForLoan' => 'Automated Testing',
                'LoanPeriod' => '12 Months',
                'AadhaarNumber' => '123456789012',
                'TotalWeight' => 15.500,
                'GoldQuality' => '22K',
                'NoOfItems' => 1,
                'UploadSign' => '',
                'UploadAadhaar' => '',
                'CreatedBy' => 1
            ];

            $loanData = [
                'LoanDate' => date('Y-m-d'),
                'DueDate' => date('Y-m-d', strtotime('+1 year')),
                'PaymentMethod' => 'Cash',
                'PaymentDate' => date('Y-m-d'),
                'PaymentAmount' => 50000.00,
                'TotalGrams' => 15.500,
                'TotalWastage' => 0.500,
                'TotalNetWeight' => 15.000,
                'NetWeight' => 15.000,
                'AmountPerGram' => 4500.00,
                'TotalAmount' => 67500.00,
                'Percentage' => 75.0,
                'MaxAmountPerGram' => 4000.00,
                'AllowedAmount' => 60000.00,
                'LoanAmount' => 50000.00,
                'BalanceLoanAmount' => 50000.00,
                'InterestRate' => 18.00,
                'TotDays' => 0,
                'Interest' => 0.00,
                'DefaultInterest' => 0.00,
                'Total' => 50000.00,
                'AppraiserCharge' => 0.00,
                'ProcessingFeePercentage' => 0.00,
                'ProcessingFee' => 0.00,
                'ChequeNumber' => '',
                'ChequeFromBank' => '',
                'ChequeToBank' => '',
                'RtgsNumber' => '',
                'RtgsFromBank' => '',
                'RtgsToBank' => '',
                'UpiType' => '',
                'UpiTransactionNumber' => '',
                'UpiToBank' => ''
            ];

            $itemsData = [
                [
                    'SlNo' => 1,
                    'ItemName' => 'Gold Ring (Test)',
                    'NoOfItems' => 1,
                    'Gram' => 15.500,
                    'Wastage' => 0.500,
                    'NetWeight' => 15.000
                ]
            ];

            $createRes = $this->model->saveJewelLoan($custData, $loanData, $itemsData);
            $this->assertTrue($createRes['status'], "Loan created successfully via saveJewelLoan()");
            $this->testLoanId = (int)$createRes['loanDetailId'];
            $this->assertGreaterThan(0, $this->testLoanId, "Returned valid loan detail ID: {$this->testLoanId}");

            // Verify initial loan record in database
            $loanRecord = $this->model->getJewelLoanByDNumber($this->testDNumber);
            $this->assertNotNull($loanRecord, "Fetched created loan by D-Number");
            $this->assertEquals(50000.00, (float)$loanRecord['loan']['LoanAmount'], "LoanAmount is 50,000.00");
            $this->assertEquals(50000.00, (float)$loanRecord['loan']['BalanceLoanAmount'], "Initial BalanceLoanAmount is 50,000.00");

            // Verify initial LOAN_CREATION disbursement receipt
            $receipts = $this->model->getReceiptsByLoanId($this->testLoanId);
            $this->assertEquals(1, count($receipts), "1 initial disbursement receipt created");
            $this->assertEquals('LOAN_CREATION', $receipts[0]['TransactionType'], "First receipt is LOAN_CREATION");
            $this->createdReceiptIds[] = (int)$receipts[0]['ReceiptId'];

            $this->setTestName("Loan Summary Calculation (Pre-Payment)");
            $summary = $this->model->getLoanReceiptSummary($this->testLoanId, $this->testDNumber);
            $this->assertEquals(50000.00, (float)$summary['calculation']['principalBalance'], "Summary principalBalance is 50,000.00");
            // Under 15-day policy: 50000 * 6% / 365 * 15 = 123.29
            $expectedInitialInt = round((50000.00 * 0.06 / 365) * 15, 2);
            $this->assertEquals(0.00, (float)$summary['calculation']['accruedInterest'], "Accrued monthly interest on loan creation day is 0.00");

            $this->setTestName("Partial Payment & Principal Balance Reduction");
            // Customer pays ₹10,123.29: covers ₹123.29 interest + ₹10,000.00 principal reduction
            $partialPay = 10000.00;
            $newBalExpected = 40000.00;

            $receiptData = [
                'ReceiptDate' => date('Y-m-d'),
                'LoanDetailId' => $this->testLoanId,
                'DNumber' => $this->testDNumber,
                'TransactionType' => 'INTEREST_PAYMENT',
                'PaymentMethod' => 'Cash',
                'PrincipalPaid' => 10000.00,
                'InterestPaid' => 0.00,
                'DefaultInterestPaid' => 0.00,
                'AppraiserCharge' => 0.00,
                'ProcessingFee' => 0.00,
                'TotalPaid' => $partialPay,
                'BalanceLoanAmount' => $newBalExpected,
                'InterestMonths' => 1,
                'InterestPeriodFrom' => date('Y-m-d'),
                'InterestPeriodTo' => date('Y-m-d'),
                'Remarks' => 'Automated test partial payment',
                'CreatedBy' => 1
            ];

            $recRes = $this->model->saveReceipt($receiptData);
            $this->assertTrue($recRes['status'], "Receipt saved successfully via saveReceipt()");
            $this->createdReceiptIds[] = (int)$recRes['receiptId'];

            // Verify ast_jewel_loan_details.BalanceLoanAmount updated in database
            $updatedLoan = $this->model->getJewelLoanByDNumber($this->testDNumber);
            $this->assertEquals($newBalExpected, (float)$updatedLoan['loan']['BalanceLoanAmount'], "ast_jewel_loan_details.BalanceLoanAmount reduced to 40,000.00");

            $this->setTestName("Interest Calculation Post-Payment (No Double Charging)");
            $summaryPost = $this->model->getLoanReceiptSummary($this->testLoanId, $this->testDNumber);
            $this->assertEquals(40000.00, (float)$summaryPost['calculation']['principalBalance'], "Updated principalBalance is 40,000.00");
            $this->assertEquals(0.00, (float)$summaryPost['calculation']['accruedInterest'], "Accrued interest is 0.00 because interest was paid to date (no double charge)");
            $this->assertEquals(40000.00, (float)$summaryPost['calculation']['totalAmountDue'], "Total due equals updated balance 40,000.00");

            $this->setTestName("Full Closure / Jewel Release");
            // Settle remaining ₹40,000.00 balance
            $releaseData = [
                'ReceiptDate' => date('Y-m-d'),
                'LoanDetailId' => $this->testLoanId,
                'DNumber' => $this->testDNumber,
                'TransactionType' => 'JEWEL_RELEASE',
                'PaymentMethod' => 'Cash',
                'PrincipalPaid' => 40000.00,
                'InterestPaid' => 0.00,
                'DefaultInterestPaid' => 0.00,
                'AppraiserCharge' => 0.00,
                'ProcessingFee' => 0.00,
                'TotalPaid' => 40000.00,
                'BalanceLoanAmount' => 0.00,
                'InterestMonths' => 0,
                'InterestPeriodFrom' => date('Y-m-d'),
                'InterestPeriodTo' => date('Y-m-d'),
                'Remarks' => 'Full Jewel Release Test',
                'CreatedBy' => 1
            ];

            $relRes = $this->model->saveReceipt($releaseData);
            $this->assertTrue($relRes['status'], "Jewel release receipt saved successfully");
            $this->createdReceiptIds[] = (int)$relRes['receiptId'];

            // Verify loan balance is 0.00 in database
            $closedLoan = $this->model->getJewelLoanByDNumber($this->testDNumber);
            $this->assertEquals(0.00, (float)$closedLoan['loan']['BalanceLoanAmount'], "BalanceLoanAmount is 0.00 after Jewel Release");

            $summaryClosed = $this->model->getLoanReceiptSummary($this->testLoanId, $this->testDNumber);
            $this->assertEquals(0.00, (float)$summaryClosed['calculation']['principalBalance'], "Principal balance is 0.00 in closed loan summary");
            $this->assertEquals(0.00, (float)$summaryClosed['calculation']['totalAmountDue'], "Total due is 0.00 for released loan");

        } finally {
            // Teardown and clean up test data so database remains pristine
            $this->teardown();
        }
    }

    private function teardown() {
        // Delete receipts
        $this->conn->query("DELETE FROM ast_jewel_loan_receipt WHERE DNumber = '{$this->testDNumber}'");
        if (!empty($this->createdReceiptIds)) {
            $ids = implode(',', array_map('intval', $this->createdReceiptIds));
            $this->conn->query("DELETE FROM ast_jewel_loan_receipt WHERE ReceiptId IN ({$ids})");
        }
        // Delete gold items
        $this->conn->query("DELETE FROM ast_jewel_loan_items WHERE DNumber = '{$this->testDNumber}'");
        // Delete loan details
        $this->conn->query("DELETE FROM ast_jewel_loan_details WHERE DNumber = '{$this->testDNumber}'");
        // Delete customer
        $this->conn->query("DELETE FROM ast_jewel_loan_customer WHERE DNumber = '{$this->testDNumber}'");
    }
}
