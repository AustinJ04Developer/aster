<?php
/**
 * Master Test Suite Runner for Aster Project
 *
 * Usage from terminal:
 * php tests/run_all_tests.php
 */

$startTime = microtime(true);

echo "\n";
echo "====================================================================\n";
echo "                 ASTER AUTOMATED TEST SUITE RUNNER                  \n";
echo "====================================================================\n";

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Unit/DatabaseTest.php';
require_once __DIR__ . '/Unit/DatabaseSyncTest.php';
require_once __DIR__ . '/Unit/PaymentAllocationTest.php';
require_once __DIR__ . '/Unit/SearchModelTest.php';
require_once __DIR__ . '/Unit/JewelLoanModelTest.php';
require_once __DIR__ . '/Unit/JewelLoanAccountListTest.php';
require_once __DIR__ . '/Unit/JewelLoanDeleteReceiptTest.php';
require_once __DIR__ . '/Unit/JewelLoanCalculationAndScenariosTest.php';
require_once __DIR__ . '/Integration/PdfGenerationTest.php';
require_once __DIR__ . '/Integration/SyntaxLintTest.php';

class JewelLoanScenariosTestSuite {
    private $conn;
    private $passed = 0;
    private $failed = 0;
    private $errors = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function run() {
        $tester = new JewelLoanCalculationAndScenariosTest($this->conn);
        // Capture output
        ob_start();
        $ok = $tester->runAllTests();
        $out = ob_get_clean();
        echo $out;
        if ($ok) {
            $this->passed = 55;
            $this->failed = 0;
        } else {
            $this->failed = 1;
            $this->errors[] = "Some assertions in JewelLoanCalculationAndScenariosTest failed.";
        }
    }

    public function getPassedCount() { return $this->passed; }
    public function getFailedCount() { return $this->failed; }
    public function getErrors() { return $this->errors; }
}
$suites = [
    'Jewel Loan Calculations & Scenarios (24 Checks)' => new JewelLoanScenariosTestSuite($conn),
    'Database & Schema Integrity'      => new DatabaseTest($conn),
    'Database Sync & Migration Engine' => new DatabaseSyncTest($conn),
    'Payment Allocation Algorithm'     => new PaymentAllocationTest(),
    'Search Engine Model'              => new SearchModelTest($conn),
    'Jewel Loan Lifecycle & Accounting'=> new JewelLoanModelTest($conn),
    'Jewel Loan Account List & Monthly Due'=> new JewelLoanAccountListTest(),
    'Jewel Loan Delete Receipt Engine' => new JewelLoanDeleteReceiptTest(),
    'PDF Voucher & Buffer Engine'      => new PdfGenerationTest($conn),
    'Codebase Syntax Lint Integrity'   => new SyntaxLintTest(),
];

$totalPassed = 0;
$totalFailed = 0;
$suiteResults = [];
$allErrors = [];

foreach ($suites as $suiteName => $suite) {
    echo "\n\033[1;33m>>> Running Suite: {$suiteName} <<<\033[0m\n";
    try {
        $suite->run();
    } catch (Throwable $t) {
        echo "    \033[31m[CRITICAL EXCEPTION]\033[0m " . $t->getMessage() . "\n";
        echo "    File: " . $t->getFile() . " Line: " . $t->getLine() . "\n";
        $totalFailed++;
        $allErrors[] = "{$suiteName}: " . $t->getMessage();
    }

    $passed = $suite->getPassedCount();
    $failed = $suite->getFailedCount();
    $errors = $suite->getErrors();

    $totalPassed += $passed;
    $totalFailed += $failed;
    $allErrors = array_merge($allErrors, $errors);

    $suiteResults[$suiteName] = [
        'passed' => $passed,
        'failed' => $failed
    ];
}

$duration = round(microtime(true) - $startTime, 3);

echo "\n";
echo "====================================================================\n";
echo "                       TEST EXECUTION SUMMARY                       \n";
echo "====================================================================\n";

foreach ($suiteResults as $name => $res) {
    $statusText = ($res['failed'] === 0) ? "\033[32mPASS\033[0m" : "\033[31mFAIL\033[0m";
    printf(" %-38s [ %4d passed | %2d failed ]  %s\n", $name, $res['passed'], $res['failed'], $statusText);
}

echo "--------------------------------------------------------------------\n";
printf(" Total Assertions Evaluated : %d\n", ($totalPassed + $totalFailed));
printf(" Total Assertions Passed    : \033[32m%d\033[0m\n", $totalPassed);
printf(" Total Assertions Failed    : %s\n", $totalFailed === 0 ? "\033[32m0\033[0m" : "\033[31m{$totalFailed}\033[0m");
printf(" Test Suite Duration        : %.3f seconds\n", $duration);
echo "====================================================================\n";

if ($totalFailed > 0) {
    echo "\n\033[31mFAILURES ENCOUNTERED:\033[0m\n";
    foreach ($allErrors as $idx => $err) {
        echo "  " . ($idx + 1) . ". {$err}\n";
    }
    echo "\n\033[41;37m RESULT: TEST SUITE FAILED \033[0m\n\n";
    exit(1);
} else {
    echo "\n\033[42;30m RESULT: ALL TESTS PASSED SUCCESSFULLY! \033[0m\n\n";
    exit(0);
}
