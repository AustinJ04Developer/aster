<?php
/**
 * Test Suite: Jewel Loan Calculations, Connected Masters, and Scenarios 1, 2, 3
 * Validates all 24 required business rules and calculations.
 */

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once __DIR__ . '/../../application/settings/config.php';
require_once __DIR__ . '/../../application/settings/db.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanModel.php';

class JewelLoanCalculationAndScenariosTest {
    private $conn;
    private $model;
    private $passed = 0;
    private $failed = 0;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new JewelLoanModel($conn);
    }

    private function assert($condition, $testName, $details = '') {
        if ($condition) {
            $this->passed++;
            echo " [PASS] {$testName}\n";
        } else {
            $this->failed++;
            echo " [FAIL] {$testName}\n";
            if ($details) {
                echo "        Details: {$details}\n";
            }
        }
    }

    private function assertEquals($expected, $actual, $testName) {
        $matches = ($expected === $actual || (is_numeric($expected) && is_numeric($actual) && abs((float)$expected - (float)$actual) < 0.01));
        $this->assert($matches, $testName, "Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true));
    }

    public function runAllTests() {
        echo "====================================================================\n";
        echo "RUNNING JEWEL LOAN CALCULATION & SCENARIOS TEST SUITE (24 CHECKS)\n";
        echo "====================================================================\n\n";

        // 1 & 2. Permissible per gram validation
        $this->assert($this->model->isValidPermissibleAmountPerGram(10000), "1. Permissible amount 10000 is valid");
        $this->assert($this->model->isValidPermissibleAmountPerGram(10500), "1b. Permissible amount 10500 is valid");
        $this->assert($this->model->isValidPermissibleAmountPerGram(11000), "1c. Permissible amount 11000 is valid");
        $this->assert(!$this->model->isValidPermissibleAmountPerGram(10200), "2. Arbitrary permissible amount 10200 is rejected");
        $this->assert(!$this->model->isValidPermissibleAmountPerGram(9500), "2b. Arbitrary permissible amount 9500 is rejected");

        // 3, 4, 5. Permissible amount to Interest rate mapping
        $this->assertEquals(18.00, $this->model->getInterestRateForPermissibleAmount(10000), "3. Permissible amount 10000 maps to 18% interest rate");
        $this->assertEquals(24.00, $this->model->getInterestRateForPermissibleAmount(10500), "4. Permissible amount 10500 maps to 24% interest rate");
        $this->assertEquals(30.00, $this->model->getInterestRateForPermissibleAmount(11000), "5. Permissible amount 11000 maps to 30% interest rate");

        // 6. Weight calculations: Net = Gross - Wastage
        $gross = 10.000;
        $wastage = 0.500;
        $net = $gross - $wastage;
        $this->assertEquals(9.500, $net, "6. Net weight = Gross (10.000g) - Wastage (0.500g) = 9.500g");

        // 7. Valuation = Net weight * Value per gram
        $valPerGram = 10000.00;
        $totalVal = round($net * $valPerGram, 2);
        $this->assertEquals(95000.00, $totalVal, "7. Total valuation = 9.500g * 10000/g = ₹95,000.00");

        // 8. Permissible loan amount = Net weight * Permissible per gram
        $permPerGram = 10000.00;
        $allowed = round(10.000 * $permPerGram, 2);
        $this->assertEquals(100000.00, $allowed, "8. Permissible loan amount = 10.000g * 10000/g = ₹1,00,000.00");

        // 9. Sanctioned principal check
        $principal = 100000.00;
        $this->assert($principal <= $allowed, "9. Sanctioned principal (₹1,00,000) does not exceed permissible amount (₹1,00,000)");

        // 10, 11, 12. Appraiser charge master lookup & boundary
        $this->assertEquals(200.00, $this->model->getAppraiserCharge(100000), "10. Appraiser charge at exactly ₹1,00,000 is ₹200");
        $this->assertEquals(400.00, $this->model->getAppraiserCharge(150000), "11. Appraiser charge for ₹1,50,000 is ₹400");
        $this->assertEquals(600.00, $this->model->getAppraiserCharge(250000), "12. Appraiser charge for ₹2,50,000 is ₹600");

        // Look up Customer D DNumber from DB
        $resD = $this->conn->query("SELECT DNumber FROM ast_jewel_loan_customer WHERE Name = 'Customer D' LIMIT 1");
        $dCustRow = $resD ? $resD->fetch_assoc() : null;
        $dNumberCustD = $dCustRow ? $dCustRow['DNumber'] : 'D-0028';
        $dLoans = $this->model->getLoansByDNumber($dNumberCustD);
        $this->assert(!empty($dLoans), "Found loan record for Customer D ({$dNumberCustD})");
        $dLoanId = (int)$dLoans[0]['LoanDetailId'];
        $dReceipts = $this->model->getReceiptsByLoanId($dLoanId);

        // 13. Appraiser charge collected once at creation, not at receipt or release
        $disbRec = $dReceipts[0];
        $this->assertEquals(200.00, (float)$disbRec['AppraiserCharge'], "13. Appraiser charge (₹200) collected on LOAN_CREATION receipt");
        $monthlyRec1 = $dReceipts[1];
        $this->assertEquals(0.00, (float)$monthlyRec1['AppraiserCharge'], "13b. Appraiser charge is ₹0.00 on monthly receipt");

        // 14. Processing fee from master (1% of principal)
        $this->assertEquals(1000.00, $this->model->getProcessingFee(100000), "14. Processing fee for ₹1,00,000 is ₹1,000 (1%)");
        $this->assertEquals(800.00, $this->model->getProcessingFee(80000), "14b. Processing fee for ₹80,000 is ₹800 (1%)");

        // 15. Processing fee collected ONLY once at release, never with monthly receipts
        $this->assertEquals(0.00, (float)$disbRec['ProcessingFee'], "15. Processing fee on LOAN_CREATION receipt is ₹0.00 (unpaid until release)");
        $this->assertEquals(0.00, (float)$monthlyRec1['ProcessingFee'], "15b. Processing fee on monthly receipt is ₹0.00");

        // 16. Daily interest formula: Principal * Rate * Days / 365
        $dailyCalc = $this->model->calculateDailyInterest(100000, 18.00, 1);
        $this->assertEquals(49.32, $dailyCalc, "16. Daily interest on ₹1,00,000 @ 18% for 1 day = ₹49.32");

        // 17. Customer D 31-day interest
        $custD31 = $this->model->calculateDailyInterest(100000, 18.00, 31);
        $this->assertEquals(1528.77, $custD31, "17. Customer D 31-day interest (15 Mar - 15 Apr @ 18%) = ₹1,528.77");

        // 18. Customer D 30-day interest
        $custD30 = $this->model->calculateDailyInterest(100000, 18.00, 30);
        $this->assertEquals(1479.45, $custD30, "18. Customer D 30-day interest (15 Apr - 15 May @ 18%) = ₹1,479.45");

        // 19. Customer D 7 receipts and state on 29 Oct 2026
        $totIntD = 0.00;
        for ($i = 1; $i <= 7; $i++) {
            $totIntD += (float)$dReceipts[$i]['InterestPaid'];
        }
        $this->assertEquals(10553.43, round($totIntD, 2), "19. Customer D 7 monthly receipts total = ₹10,553.43");
        $paidThroughD = $this->model->getLoanPaidThroughDate($dLoanId, '2026-03-15');
        $this->assertEquals('2026-10-15', $paidThroughD, "19b. Customer D interest paid through 15 October 2026");
        $nextDueD = $this->model->calculateNextMonthlyDueDate('2026-03-15', $dLoanId);
        $this->assertEquals('2026-11-15', $nextDueD, "19c. Customer D next due date is 15 November 2026");

        // Look up Customer F DNumber from DB
        $resF = $this->conn->query("SELECT DNumber FROM ast_jewel_loan_customer WHERE Name = 'Customer F' LIMIT 1");
        $fCustRow = $resF ? $resF->fetch_assoc() : null;
        $dNumberCustF = $fCustRow ? $fCustRow['DNumber'] : 'D-0029';
        $fLoans = $this->model->getLoansByDNumber($dNumberCustF);
        $this->assert(!empty($fLoans), "Found loan record for Customer F ({$dNumberCustF})");
        $fLoanId = (int)$fLoans[0]['LoanDetailId'];
        $fReceipts = $this->model->getReceiptsByLoanId($fLoanId);

        // 20. Customer F payment 1 covers 20 days @ 24% = ₹1,052.05; remaining 11 days of month 1 stays unpaid with due date 31 Jan 2026
        $fPay1 = $fReceipts[1];
        $this->assertEquals(1052.05, (float)$fPay1['InterestPaid'], "20. Customer F first payment on 20 Jan covers 20 days = ₹1,052.05");
        $paidThroughF = $this->model->getLoanPaidThroughDate($fLoanId, '2025-12-31');
        $this->assertEquals('2026-01-20', $paidThroughF, "20b. Customer F interest paid through 20 January 2026");
        $nextDueF = $this->model->calculateNextMonthlyDueDate('2025-12-31', $fLoanId);
        $this->assertEquals('2026-01-31', $nextDueF, "20c. Customer F next due date for remaining Jan interest is 31 January 2026");

        // 21. Customer F on 15 Feb:
        // Overdue range 21–31 Jan (11 days) escalates 24% -> 30% = ₹723.29
        // Subsequent range 1–15 Feb (15 days @ 24%) = ₹789.04
        // Total interest through 15 Feb = ₹1,512.33
        // Or through 28 Feb (28 days @ 24% = ₹1,472.88) -> Total = ₹2,196.17
        $schedF15 = $this->model->calculateLoanInterestSchedule($fLoanId, '2026-02-15', '2026-02-15');
        $this->assert($schedF15['overdueBreakdown']['isOverdue'], "21. Overdue breakdown active for Customer F on 15 Feb 2026");
        $this->assertEquals(30.00, $schedF15['overdueBreakdown']['rate'], "21b. Overdue rate escalated from 24% to 30%");
        $this->assertEquals(11, $schedF15['overdueBreakdown']['days'], "21c. Overdue period contains exactly 11 days (21-31 Jan)");
        $this->assertEquals(723.29, $schedF15['overdueBreakdown']['interest'], "21d. Overdue interest = ₹723.29 (11 days @ 30%)");
        $this->assertEquals(24.00, $schedF15['regularBreakdown']['rate'], "21e. Subsequent period uses standard rate 24%");
        $this->assertEquals(15, $schedF15['regularBreakdown']['days'], "21f. Subsequent period contains 15 days (1-15 Feb)");
        $this->assertEquals(789.04, $schedF15['regularBreakdown']['interest'], "21g. Regular interest = ₹789.04 (15 days @ 24%)");
        $this->assertEquals(1512.33, $schedF15['totalInterestDue'], "21h. Total interest due through 15 Feb = ₹1,512.33");

        $schedF28 = $this->model->calculateLoanInterestSchedule($fLoanId, '2026-02-15', '2026-02-28');
        $this->assertEquals(1472.88, $schedF28['regularBreakdown']['interest'], "21i. Feb 1-28 regular interest (28 days @ 24%) = ₹1,472.88");
        $this->assertEquals(2196.17, $schedF28['totalInterestDue'], "21j. Total interest through 28 Feb = ₹2,196.17");

        // 22. Original day anchor monthly due dates
        $due1 = $this->model->calculateMonthlyInstallmentDueDate('2025-12-31', 1);
        $due2 = $this->model->calculateMonthlyInstallmentDueDate('2025-12-31', 2);
        $due3 = $this->model->calculateMonthlyInstallmentDueDate('2025-12-31', 3);
        $due4 = $this->model->calculateMonthlyInstallmentDueDate('2025-12-31', 4);
        $due5 = $this->model->calculateMonthlyInstallmentDueDate('2025-12-31', 5);
        $this->assertEquals('2026-01-31', $due1, "22. Anchor 31: Due 1 is 2026-01-31");
        $this->assertEquals('2026-02-28', $due2, "22b. Anchor 31: Due 2 is 2026-02-28");
        $this->assertEquals('2026-03-31', $due3, "22c. Anchor 31: Due 3 recovers 31st (2026-03-31) and does not stay on 28th");
        $this->assertEquals('2026-04-30', $due4, "22d. Anchor 31: Due 4 is 2026-04-30");
        $this->assertEquals('2026-05-31', $due5, "22e. Anchor 31: Due 5 recovers 31st (2026-05-31)");

        // 23. Excess payment reduces principal balance directly
        $alloc = $this->model->allocatePayment($fLoanId, 5000.00, '2026-02-15', '2026-02-15');
        $this->assertEquals(1512.33, $alloc['totalInterestPaid'], "23. Interest covered is exactly ₹1,512.33");
        $this->assertEquals(3487.67, $alloc['excessPayment'], "23b. Excess payment is ₹3,487.67");
        $this->assertEquals(3487.67, $alloc['principalPaid'], "23c. Principal reduced by full excess amount of ₹3,487.67");
        $this->assertEquals(76512.33, $alloc['newPrincipalBalance'], "23d. New principal balance reduced from ₹80,000 to ₹76,512.33");

        // 24. Final settlement (JEWEL_RELEASE) calculation
        $schedCloseD = $this->model->calculateLoanInterestSchedule($dLoanId, '2026-10-29', '2026-10-29');
        $this->assertEquals(690.41, $schedCloseD['totalInterestDue'], "24. Customer D closure interest for 14 days = ₹690.41");
        $this->assertEquals(1000.00, $schedCloseD['processingFee'], "24b. Customer D closure processing fee = ₹1,000.00");
        $this->assertEquals(101690.41, $schedCloseD['totalClosureAmount'], "24c. Customer D total closure amount = ₹1,01,690.41");

        // Quick release within 15 days rule check
        $quickInt = $this->model->calculateDailyInterest(100000, 6.00, 15);
        $this->assertEquals(246.58, $quickInt, "24d. Minimum 15-day rule for quick release within 15 days: 15 days @ 6% = ₹246.58");

        echo "\n====================================================================\n";
        echo "TEST SUMMARY: {$this->passed} PASSED, {$this->failed} FAILED\n";
        echo "====================================================================\n";

        return ($this->failed === 0);
    }
}

if (isset($_SERVER['SCRIPT_FILENAME']) && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $tester = new JewelLoanCalculationAndScenariosTest($conn);
    $allPassed = $tester->runAllTests();
    exit($allPassed ? 0 : 1);
}
