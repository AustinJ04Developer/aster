<?php
require_once __DIR__ . '/application/settings/db.php';
require_once __DIR__ . '/application/views/controller/model/JewelLoanModel.php';

$model = new JewelLoanModel($conn);
$summary = $model->getLoanReceiptSummary(0, 'D-0001');

echo "=== LOAN SUMMARY FOR D-0001 ===" . PHP_EOL;
if ($summary && !empty($summary['loan'])) {
    echo "DNumber: " . $summary['loan']['DNumber'] . PHP_EOL;
    echo "LoanDetailId: " . $summary['loan']['LoanDetailId'] . PHP_EOL;
    echo "Customer Name: " . $summary['customer']['Name'] . PHP_EOL;
    echo "Loan Amount: " . $summary['loan']['LoanAmount'] . PHP_EOL;
    echo "Balance: " . $summary['loan']['BalanceLoanAmount'] . PHP_EOL;
    echo "Interest Rate: " . $summary['loan']['InterestRate'] . PHP_EOL;
    echo "Items Count: " . count($summary['items']) . PHP_EOL;
    echo "=== TEST SAVE RECEIPT ===" . PHP_EOL;
    $testData = [
        'ReceiptDate' => date('Y-m-d'),
        'LoanDetailId' => 1,
        'DNumber' => 'D-0001',
        'TransactionType' => 'INTEREST_PAYMENT',
        'PaymentMethod' => 'Cash',
        'PrincipalPaid' => 0.00,
        'InterestPaid' => 500.00,
        'DefaultInterestPaid' => 0.00,
        'AppraiserCharge' => 0.00,
        'ProcessingFee' => 0.00,
        'TotalPaid' => 500.00,
        'BalanceLoanAmount' => 452999.55,
        'Remarks' => 'Automated test receipt',
        'CreatedBy' => 1
    ];
    $saveRes = $model->saveReceipt($testData);
    print_r($saveRes);

    if ($saveRes['status']) {
        $rFetch = $model->getReceiptById($saveRes['receiptId']);
        echo "Saved Receipt Details: ReceiptNo=" . $rFetch['receipt']['ReceiptNo'] . ", TotalPaid=" . $rFetch['receipt']['TotalPaid'] . ", DefaultInterest=" . $rFetch['receipt']['DefaultInterestPaid'] . PHP_EOL;
        // Clean up test receipt
        $conn->query("DELETE FROM ast_jewel_loan_receipt WHERE ReceiptId = " . (int)$saveRes['receiptId']);
        echo "Cleaned up test receipt cleanly." . PHP_EOL;
    }
} else {
    echo "Loan not found!" . PHP_EOL;
}
