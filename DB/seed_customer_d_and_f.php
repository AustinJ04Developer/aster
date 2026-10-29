<?php
/**
 * Seeder for Dataset 1 (Customer D) and Dataset 2 (Customer F)
 * Idempotent: can be executed repeatedly without creating duplicate customer or loan records.
 */

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once __DIR__ . '/../application/settings/config.php';
require_once __DIR__ . '/../application/settings/db.php';
require_once __DIR__ . '/../application/views/controller/model/JewelLoanModel.php';

$model = new JewelLoanModel($conn);

echo "====================================================\n";
echo "Starting Seeder: Customer D & Customer F\n";
echo "====================================================\n\n";

// Helper function to insert receipt with specific receipt number if needed
function insertSeedReceipt($conn, $model, $receiptData) {
    $receiptNo = $model->getNextReceiptNumber();

    $sql = "INSERT INTO ast_jewel_loan_receipt (
        ReceiptNo, ReceiptDate, LoanDetailId, DNumber,
        TransactionType, PaymentMethod, PrincipalPaid, InterestPaid, DefaultInterestPaid,
        AppraiserCharge, ProcessingFee, TotalPaid, BalanceLoanAmount,
        InterestMonths, InterestPeriodFrom, InterestPeriodTo,
        ChequeNumber, ChequeFromBank, ChequeToBank,
        RtgsNumber, RtgsFromBank, RtgsToBank,
        UpiType, UpiTransactionNumber, UpiToBank,
        Remarks, CreatedBy,
        RateApplied, OverdueInterestRate, OverdueInterestPaid, OverduePeriodFrom, OverduePeriodTo, BreakdownJson
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Receipt prepare failed: " . $conn->error);
    }

    $receiptDate = $receiptData['ReceiptDate'];
    $loanDetailId = (int)$receiptData['LoanDetailId'];
    $dNumber = $receiptData['DNumber'];
    $txnType = $receiptData['TransactionType'];
    $payMethod = $receiptData['PaymentMethod'] ?? 'Cash';
    $principalPaid = (float)($receiptData['PrincipalPaid'] ?? 0);
    $interestPaid = (float)($receiptData['InterestPaid'] ?? 0);
    $defaultInterestPaid = (float)($receiptData['DefaultInterestPaid'] ?? 0);
    $appraiserCharge = (float)($receiptData['AppraiserCharge'] ?? 0);
    $processingFee = (float)($receiptData['ProcessingFee'] ?? 0);
    $totalPaid = (float)($receiptData['TotalPaid'] ?? 0);
    $balanceLoanAmount = (float)($receiptData['BalanceLoanAmount'] ?? 0);
    $interestMonths = (float)($receiptData['InterestMonths'] ?? 0);
    $periodFrom = !empty($receiptData['InterestPeriodFrom']) ? $receiptData['InterestPeriodFrom'] : null;
    $periodTo = !empty($receiptData['InterestPeriodTo']) ? $receiptData['InterestPeriodTo'] : null;
    $chequeNo = '';
    $chequeFrom = '';
    $chequeTo = '';
    $rtgsNo = '';
    $rtgsFrom = '';
    $rtgsTo = '';
    $upiType = '';
    $upiTxn = '';
    $upiTo = '';
    $remarks = $receiptData['Remarks'] ?? '';
    $createdBy = 1;

    $rateApplied = isset($receiptData['RateApplied']) ? (float)$receiptData['RateApplied'] : null;
    $overdueRate = isset($receiptData['OverdueInterestRate']) ? (float)$receiptData['OverdueInterestRate'] : null;
    $overduePaid = isset($receiptData['OverdueInterestPaid']) ? (float)$receiptData['OverdueInterestPaid'] : 0.00;
    $overdueFrom = !empty($receiptData['OverduePeriodFrom']) ? $receiptData['OverduePeriodFrom'] : null;
    $overdueTo = !empty($receiptData['OverduePeriodTo']) ? $receiptData['OverduePeriodTo'] : null;
    $breakdownJson = !empty($receiptData['BreakdownJson']) ? $receiptData['BreakdownJson'] : null;

    $stmt->bind_param(
        "ssisssddddddddssssssssssssiddsdss",
        $receiptNo,
        $receiptDate,
        $loanDetailId,
        $dNumber,
        $txnType,
        $payMethod,
        $principalPaid,
        $interestPaid,
        $defaultInterestPaid,
        $appraiserCharge,
        $processingFee,
        $totalPaid,
        $balanceLoanAmount,
        $interestMonths,
        $periodFrom,
        $periodTo,
        $chequeNo,
        $chequeFrom,
        $chequeTo,
        $rtgsNo,
        $rtgsFrom,
        $rtgsTo,
        $upiType,
        $upiTxn,
        $upiTo,
        $remarks,
        $createdBy,
        $rateApplied,
        $overdueRate,
        $overduePaid,
        $overdueFrom,
        $overdueTo,
        $breakdownJson
    );

    if (!$stmt->execute()) {
        throw new Exception("Receipt execution failed: " . $stmt->error);
    }
    $recId = $conn->insert_id;
    $stmt->close();
    return ['receiptId' => $recId, 'receiptNo' => $receiptNo];
}

// -------------------------------------------------------------
// 1. SEED DATASET 1 — CUSTOMER D
// -------------------------------------------------------------
echo "1. Processing Dataset 1: Customer D...\n";

// Check if Customer D already exists
$dCustQuery = "SELECT CustomerId, DNumber FROM ast_jewel_loan_customer WHERE Name = 'Customer D' LIMIT 1";
$dRes = $conn->query($dCustQuery);
$dNumberCustD = null;
$existingLoanDId = null;

if ($dRes && $dRow = $dRes->fetch_assoc()) {
    $dNumberCustD = $dRow['DNumber'];
    echo "   Customer D already exists with DNumber: {$dNumberCustD}. Cleaning previous test loan/receipt records to reseed cleanly...\n";
    // Clean old records for clean idempotency
    $lRes = $conn->query("SELECT LoanDetailId FROM ast_jewel_loan_details WHERE DNumber = '{$dNumberCustD}'");
    while ($lr = $lRes->fetch_assoc()) {
        $ldId = (int)$lr['LoanDetailId'];
        $conn->query("DELETE FROM ast_jewel_loan_receipt WHERE LoanDetailId = {$ldId}");
        $conn->query("DELETE FROM ast_jewel_loan_items WHERE LoanDetailId = {$ldId}");
        $conn->query("DELETE FROM ast_jewel_loan_rate_history WHERE LoanDetailId = {$ldId}");
    }
    $conn->query("DELETE FROM ast_jewel_loan_details WHERE DNumber = '{$dNumberCustD}'");
} else {
    $dNumberCustD = $model->getNextDNumber();
    echo "   Allocated new DNumber for Customer D: {$dNumberCustD}\n";

    // Insert Customer D
    $insCustSql = "INSERT INTO ast_jewel_loan_customer
        (DNumber, Name, FathersName, Job, DateOfBirth, Address, MobileNumber, Pincode,
         NomineeName, NomineeRelation, NomineeAddress, IfNomineeIsMinor, GuardianName,
         LoanDate, MaxAmountPerGram, AllowedAmount, PacketNumber, Amount, ReasonForLoan,
         LoanPeriod, AadhaarNumber, TotalWeight, GoldQuality, NoOfItems, CreatedBy)
        VALUES (?, 'Customer D', 'Father of Customer D', 'Business', '1985-05-15', '123 Cross Street, Aster Nagar', '9876543210', '600001',
                'Nominee D', 'Spouse', '123 Cross Street, Aster Nagar', 'No', '',
                '2026-03-15', 10000.00, 100000.00, 'PKT-D-01', 100000.00, 'Business Expansion',
                '12 Months', '123456789012', 10.000, '22K', 2, 1)";
    $st = $conn->prepare($insCustSql);
    $st->bind_param("s", $dNumberCustD);
    $st->execute();
    $st->close();
}

// Insert Loan Details for Customer D
$insLoanDSql = "INSERT INTO ast_jewel_loan_details
    (DNumber, LoanDate, InterestRate, TotalNetWeight, AmountPerGram, TotalAmount,
     MaxAmountPerGram, AllowedAmount, LoanAmount, BalanceLoanAmount, AppraiserCharge,
     ProcessingFeePercentage, ProcessingFee, PaymentMethod, PaymentDate, PaymentAmount)
    VALUES (?, '2026-03-15', 18.00, 10.000, 10000.00, 100000.00,
            10000.00, 100000.00, 100000.00, 100000.00, 200.00,
            1.00, 1000.00, 'Cash', '2026-03-15', 100000.00)";
$stLD = $conn->prepare($insLoanDSql);
$stLD->bind_param("s", $dNumberCustD);
$stLD->execute();
$loanDetailIdD = $conn->insert_id;
$stLD->close();
echo "   Created Loan Details for Customer D (LoanDetailId: {$loanDetailIdD})\n";

// Insert Gold Items (2 rings: 5.200g and 4.800g)
$conn->query("INSERT INTO ast_jewel_loan_items (LoanDetailId, DNumber, SlNo, ItemName, NoOfItems, Gram, Wastage, NetWeight)
              VALUES ({$loanDetailIdD}, '{$dNumberCustD}', 1, 'Gold Ring 1', 1, 5.200, 0.000, 5.200),
                     ({$loanDetailIdD}, '{$dNumberCustD}', 2, 'Gold Ring 2', 1, 4.800, 0.000, 4.800)");

// Insert Initial Disbursement Receipt (LOAN_CREATION)
// Appraiser charge: 200 (paid at creation). Processing fee: 0.00 (unpaid until release)
$recD0 = insertSeedReceipt($conn, $model, [
    'ReceiptDate' => '2026-03-15',
    'LoanDetailId' => $loanDetailIdD,
    'DNumber' => $dNumberCustD,
    'TransactionType' => 'LOAN_CREATION',
    'PaymentMethod' => 'Cash',
    'PrincipalPaid' => 100000.00,
    'InterestPaid' => 0.00,
    'AppraiserCharge' => 200.00,
    'ProcessingFee' => 0.00,
    'TotalPaid' => 100200.00,
    'BalanceLoanAmount' => 100000.00,
    'Remarks' => 'Initial Loan Disbursement with Appraiser Charge (Processing Fee unpaid until release)'
]);
echo "   Disbursement Receipt: {$recD0['receiptNo']} on 15/03/2026\n";

// 7 Monthly Interest Receipts for Customer D
$custDReceipts = [
    ['date' => '2026-04-15', 'from' => '2026-03-15', 'to' => '2026-04-15', 'days' => 31, 'int' => 1528.77],
    ['date' => '2026-05-15', 'from' => '2026-04-15', 'to' => '2026-05-15', 'days' => 30, 'int' => 1479.45],
    ['date' => '2026-06-15', 'from' => '2026-05-15', 'to' => '2026-06-15', 'days' => 31, 'int' => 1528.77],
    ['date' => '2026-07-15', 'from' => '2026-06-15', 'to' => '2026-07-15', 'days' => 30, 'int' => 1479.45],
    ['date' => '2026-08-15', 'from' => '2026-07-15', 'to' => '2026-08-15', 'days' => 31, 'int' => 1528.77],
    ['date' => '2026-09-15', 'from' => '2026-08-15', 'to' => '2026-09-15', 'days' => 31, 'int' => 1528.77],
    ['date' => '2026-10-15', 'from' => '2026-09-15', 'to' => '2026-10-15', 'days' => 30, 'int' => 1479.45],
];

$totalDInterestPaid = 0.00;
foreach ($custDReceipts as $idx => $cr) {
    $r = insertSeedReceipt($conn, $model, [
        'ReceiptDate' => $cr['date'],
        'LoanDetailId' => $loanDetailIdD,
        'DNumber' => $dNumberCustD,
        'TransactionType' => 'INTEREST_PAYMENT',
        'PaymentMethod' => 'Cash',
        'PrincipalPaid' => 0.00,
        'InterestPaid' => $cr['int'],
        'AppraiserCharge' => 0.00,
        'ProcessingFee' => 0.00,
        'TotalPaid' => $cr['int'],
        'BalanceLoanAmount' => 100000.00,
        'InterestPeriodFrom' => $cr['from'],
        'InterestPeriodTo' => $cr['to'],
        'InterestMonths' => round($cr['days'] / 30.4167, 2),
        'RateApplied' => 18.00,
        'Remarks' => "Monthly Interest Receipt #{$idx} ({$cr['days']} days @ 18% p.a.)"
    ]);
    $totalDInterestPaid += $cr['int'];
    echo "   Receipt " . ($idx + 1) . ": {$r['receiptNo']} on {$cr['date']} - ₹{$cr['int']} ({$cr['days']} days, {$cr['from']} to {$cr['to']})\n";
}
echo "   Total Interest Paid across 7 months: ₹" . number_format($totalDInterestPaid, 2) . "\n";
echo "   Customer D successfully seeded.\n\n";

// -------------------------------------------------------------
// 2. SEED DATASET 2 — CUSTOMER F
// -------------------------------------------------------------
echo "2. Processing Dataset 2: Customer F...\n";

// Check if Customer F already exists
$fCustQuery = "SELECT CustomerId, DNumber FROM ast_jewel_loan_customer WHERE Name = 'Customer F' LIMIT 1";
$fRes = $conn->query($fCustQuery);
$dNumberCustF = null;

if ($fRes && $fRow = $fRes->fetch_assoc()) {
    $dNumberCustF = $fRow['DNumber'];
    echo "   Customer F already exists with DNumber: {$dNumberCustF}. Cleaning previous test records to reseed cleanly...\n";
    $lRes = $conn->query("SELECT LoanDetailId FROM ast_jewel_loan_details WHERE DNumber = '{$dNumberCustF}'");
    while ($lr = $lRes->fetch_assoc()) {
        $ldId = (int)$lr['LoanDetailId'];
        $conn->query("DELETE FROM ast_jewel_loan_receipt WHERE LoanDetailId = {$ldId}");
        $conn->query("DELETE FROM ast_jewel_loan_items WHERE LoanDetailId = {$ldId}");
        $conn->query("DELETE FROM ast_jewel_loan_rate_history WHERE LoanDetailId = {$ldId}");
    }
    $conn->query("DELETE FROM ast_jewel_loan_details WHERE DNumber = '{$dNumberCustF}'");
} else {
    $dNumberCustF = $model->getNextDNumber();
    echo "   Allocated new DNumber for Customer F: {$dNumberCustF}\n";

    // Insert Customer F
    $insCustSqlF = "INSERT INTO ast_jewel_loan_customer
        (DNumber, Name, FathersName, Job, DateOfBirth, Address, MobileNumber, Pincode,
         NomineeName, NomineeRelation, NomineeAddress, IfNomineeIsMinor, GuardianName,
         LoanDate, MaxAmountPerGram, AllowedAmount, PacketNumber, Amount, ReasonForLoan,
         LoanPeriod, AadhaarNumber, TotalWeight, GoldQuality, NoOfItems, CreatedBy)
        VALUES (?, 'Customer F', 'Father of Customer F', 'Professional', '1990-08-20', '456 North Avenue, Aster City', '9876543211', '600002',
                'Nominee F', 'Sibling', '456 North Avenue, Aster City', 'No', '',
                '2025-12-31', 10000.00, 80000.00, 'PKT-F-01', 80000.00, 'Medical Expense',
                '12 Months', '987654321098', 8.000, '22K', 1, 1)";
    $stF = $conn->prepare($insCustSqlF);
    $stF->bind_param("s", $dNumberCustF);
    $stF->execute();
    $stF->close();
}

// Insert Loan Details for Customer F (Rate 24% authorized test override, principal 80,000)
$insLoanFSql = "INSERT INTO ast_jewel_loan_details
    (DNumber, LoanDate, InterestRate, TotalNetWeight, AmountPerGram, TotalAmount,
     MaxAmountPerGram, AllowedAmount, LoanAmount, BalanceLoanAmount, AppraiserCharge,
     ProcessingFeePercentage, ProcessingFee, PaymentMethod, PaymentDate, PaymentAmount)
    VALUES (?, '2025-12-31', 24.00, 8.000, 10000.00, 80000.00,
            10000.00, 80000.00, 80000.00, 80000.00, 200.00,
            1.00, 800.00, 'Cash', '2025-12-31', 80000.00)";
$stLF = $conn->prepare($insLoanFSql);
$stLF->bind_param("s", $dNumberCustF);
$stLF->execute();
$loanDetailIdF = $conn->insert_id;
$stLF->close();
echo "   Created Loan Details for Customer F (LoanDetailId: {$loanDetailIdF})\n";

// Insert Gold Item (1 chain: 8.000g)
$conn->query("INSERT INTO ast_jewel_loan_items (LoanDetailId, DNumber, SlNo, ItemName, NoOfItems, Gram, Wastage, NetWeight)
              VALUES ({$loanDetailIdF}, '{$dNumberCustF}', 1, 'Gold Chain', 1, 8.000, 0.000, 8.000)");

// Initial Disbursement Receipt (LOAN_CREATION)
$recF0 = insertSeedReceipt($conn, $model, [
    'ReceiptDate' => '2025-12-31',
    'LoanDetailId' => $loanDetailIdF,
    'DNumber' => $dNumberCustF,
    'TransactionType' => 'LOAN_CREATION',
    'PaymentMethod' => 'Cash',
    'PrincipalPaid' => 80000.00,
    'InterestPaid' => 0.00,
    'AppraiserCharge' => 200.00,
    'ProcessingFee' => 0.00,
    'TotalPaid' => 80200.00,
    'BalanceLoanAmount' => 80000.00,
    'Remarks' => 'Initial Loan Disbursement with Appraiser Charge (Processing Fee ₹800 unpaid until release)'
]);
echo "   Disbursement Receipt: {$recF0['receiptNo']} on 31/12/2025\n";

// First Payment: Early partial payment on 20 January 2026 (20 days @ 24% = ₹1,052.05)
// Unpaid remainder of Month 1: 21–31 Jan 2026 (11 days)
$recF1 = insertSeedReceipt($conn, $model, [
    'ReceiptDate' => '2026-01-20',
    'LoanDetailId' => $loanDetailIdF,
    'DNumber' => $dNumberCustF,
    'TransactionType' => 'INTEREST_PAYMENT',
    'PaymentMethod' => 'Cash',
    'PrincipalPaid' => 0.00,
    'InterestPaid' => 1052.05,
    'AppraiserCharge' => 0.00,
    'ProcessingFee' => 0.00,
    'TotalPaid' => 1052.05,
    'BalanceLoanAmount' => 80000.00,
    'InterestPeriodFrom' => '2025-12-31',
    'InterestPeriodTo' => '2026-01-20',
    'InterestMonths' => round(20 / 30.4167, 2),
    'RateApplied' => 24.00,
    'Remarks' => 'Scenario 2: Early partial payment (20 days covered from 31/12/2025 to 20/01/2026 @ 24%). Remaining 11 days (21-31 Jan) unpaid.'
]);
echo "   First Partial Payment: {$recF1['receiptNo']} on 20/01/2026 - ₹1,052.05 (20 days @ 24% p.a.)\n";
echo "   Customer F successfully seeded.\n\n";

echo "====================================================\n";
echo "Seeder Finished Successfully!\n";
echo "Customer D: DNumber = {$dNumberCustD}, LoanDetailId = {$loanDetailIdD}\n";
echo "Customer F: DNumber = {$dNumberCustF}, LoanDetailId = {$loanDetailIdF}\n";
echo "====================================================\n";
