<?php
session_start();
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once '../settings/db.php';
require_once 'controller/model/JewelLoanModel.php';

$model = new JewelLoanModel($conn);

$loanIdParam   = (int)($_GET['loan_id'] ?? 0);
$dNumParam     = trim($_GET['dnum'] ?? '');
$idParam       = (int)($_GET['id'] ?? 0);
$isEditRequested = isset($_GET['edit']) && $_GET['edit'] == 1;

$success = '';
$errors = [];

// File upload helper
if (!function_exists('uploadViewFile')) {
    function uploadViewFile($fileInputKey, $folderName) {
        if (isset($_FILES[$fileInputKey]) && $_FILES[$fileInputKey]['error'] == UPLOAD_ERR_OK) {
            $fileName = $_FILES[$fileInputKey]['name'];
            $fileTmp  = $_FILES[$fileInputKey]['tmp_name'];

            $uploadDir = "uploads/" . $folderName . "/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $safeFileName = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($fileName));
            $destination = $uploadDir . $safeFileName;

            if (move_uploaded_file($fileTmp, $destination)) {
                return $destination;
            }
        }
        return '';
    }
}

// ==========================================
// HANDLE EDIT / UPDATE FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_jewel_loan'])) {
    $editDNumber   = trim($_POST['d_number'] ?? '');
    $editLoanId    = (int)($_POST['loan_detail_id'] ?? 0);

    if (empty($editDNumber)) {
        $errors[] = "D-Number is required.";
    }

    $editName        = trim($_POST['name'] ?? '');
    $editFathersName = trim($_POST['fathers_name'] ?? '');
    $editJob         = trim($_POST['job'] ?? '');
    $editDob         = !empty($_POST['dob']) ? trim($_POST['dob']) : null;
    $editAddress     = trim($_POST['address'] ?? '');
    $editMobile      = trim($_POST['mobile_number'] ?? '');
    $editPincode     = trim($_POST['pincode'] ?? '');
    $editNomineeName = trim($_POST['nominee_name'] ?? '');
    $editNomineeRel  = trim($_POST['nominee_relation'] ?? '');
    $editNomineeAddr = trim($_POST['nominee_address'] ?? '');
    $editIsMinor     = trim($_POST['if_nominee_minor'] ?? 'No');
    $editGuardian    = trim($_POST['guardian_name'] ?? '');
    $editPacketNo    = trim($_POST['packet_number'] ?? '');
    $editReason      = trim($_POST['reason_for_loan'] ?? '');
    $editAadhaar     = trim($_POST['aadhaar_number'] ?? '');

    if (empty($editName)) {
        $errors[] = "Customer Name is required.";
    }

    // Process uploads or preserve existing
    $newSign = uploadViewFile('upload_sign', 'signatures');
    $newAadhaarDoc = uploadViewFile('upload_aadhaar', 'aadhaar');
    $finalSign = !empty($newSign) ? $newSign : trim($_POST['existing_upload_sign'] ?? '');
    $finalAadhaarDoc = !empty($newAadhaarDoc) ? $newAadhaarDoc : trim($_POST['existing_upload_aadhaar'] ?? '');

    // Ornament Items
    $itemsData = [];
    $totalWeight = 0.0;
    $totalItemsCount = 0;

    if (isset($_POST['item_name']) && is_array($_POST['item_name'])) {
        $itemNames    = $_POST['item_name'];
        $itemCounts   = $_POST['item_no'] ?? [];
        $itemGrams    = $_POST['item_gram'] ?? [];
        $itemWastages = $_POST['item_wastage'] ?? [];
        $itemNets     = $_POST['item_net_weight'] ?? [];

        for ($i = 0; $i < count($itemNames); $i++) {
            $iName = trim($itemNames[$i] ?? '');
            if (empty($iName)) continue;

            $iCount   = (int)($itemCounts[$i] ?? 1);
            $iGram    = (float)($itemGrams[$i] ?? 0);
            $iWastage = (float)($itemWastages[$i] ?? 0);
            $iNet     = (float)($itemNets[$i] ?? ($iGram - $iWastage));
            if ($iNet < 0) $iNet = 0;

            $itemsData[] = [
                'SlNo'      => count($itemsData) + 1,
                'ItemName'  => $iName,
                'NoOfItems' => $iCount > 0 ? $iCount : 1,
                'Gram'      => $iGram,
                'Wastage'   => $iWastage,
                'NetWeight' => $iNet
            ];

            $totalWeight += $iNet;
            $totalItemsCount += $iCount;
        }
    }

    // Customer Data Payload
    $customerData = [
        'DNumber'          => $editDNumber,
        'Name'             => $editName,
        'FathersName'      => $editFathersName,
        'Job'              => $editJob,
        'DateOfBirth'      => $editDob,
        'Address'          => $editAddress,
        'MobileNumber'     => $editMobile,
        'Pincode'          => $editPincode,
        'NomineeName'      => $editNomineeName,
        'NomineeRelation'  => $editNomineeRel,
        'NomineeAddress'   => $editNomineeAddr,
        'IfNomineeIsMinor' => $editIsMinor,
        'GuardianName'     => $editGuardian,
        'PacketNumber'     => $editPacketNo,
        'ReasonForLoan'    => $editReason,
        'AadhaarNumber'    => $editAadhaar,
        'TotalWeight'      => $totalWeight,
        'NoOfItems'        => $totalItemsCount,
        'UploadSign'       => $finalSign,
        'UploadAadhaar'    => $finalAadhaarDoc
    ];

    // Loan Data Payload (if loan exists)
    $loanData = null;
    if ($editLoanId > 0) {
        $editLoanDate    = !empty($_POST['sub_loan_date']) ? trim($_POST['sub_loan_date']) : date('Y-m-d');
        $editInterestRate= (float)($_POST['interest_rate'] ?? 18);
        $editAmountPerGm = (float)($_POST['sub_amount_per_gram'] ?? 0);
        $editTotalAmount = (float)($_POST['sub_total_amount'] ?? 0);
        $editMaxPerGm    = (float)($_POST['sub_max_amount_per_gram'] ?? 0);
        $editAllowedAmt  = (float)($_POST['sub_allowed_amount'] ?? 0);
        $editLoanAmount  = (float)($_POST['sub_loan_amount'] ?? 0);
        $editBalanceAmt  = isset($_POST['sub_balance_amount']) ? (float)$_POST['sub_balance_amount'] : $editLoanAmount;
        $editAppraiserChg= (float)($_POST['sub_appraiser_charge'] ?? 0);
        $editPFPct       = (float)($_POST['sub_processing_fee_percentage'] ?? 0);
        $editPF          = (float)($_POST['sub_processing_fee'] ?? 0);
        $editPayMethod   = trim($_POST['payment_method'] ?? 'Cash');
        $editPayDate     = date('Y-m-d');

        $loanData = [
            'LoanDate'                => $editLoanDate,
            'InterestRate'            => $editInterestRate,
            'TotalNetWeight'          => $totalWeight,
            'AmountPerGram'           => $editAmountPerGm,
            'TotalAmount'             => $editTotalAmount,
            'MaxAmountPerGram'        => $editMaxPerGm,
            'AllowedAmount'           => $editAllowedAmt,
            'LoanAmount'              => $editLoanAmount,
            'BalanceLoanAmount'       => $editBalanceAmt,
            'AppraiserCharge'         => $editAppraiserChg,
            'ProcessingFeePercentage' => $editPFPct,
            'ProcessingFee'           => $editPF,
            'PaymentMethod'           => $editPayMethod,
            'PaymentDate'             => $editPayDate,
            'PaymentAmount'           => $editLoanAmount,
            'ChequeNumber'            => trim($_POST['cheque_number'] ?? ''),
            'ChequeFromBank'          => trim($_POST['cheque_from_bank'] ?? ''),
            'ChequeToBank'            => trim($_POST['cheque_to_bank'] ?? ''),
            'RtgsNumber'              => trim($_POST['rtgs_number'] ?? ''),
            'RtgsFromBank'            => trim($_POST['rtgs_from_bank'] ?? ''),
            'RtgsToBank'              => trim($_POST['rtgs_to_bank'] ?? ''),
            'UpiType'                 => trim($_POST['upi_type'] ?? ''),
            'UpiTransactionNumber'    => trim($_POST['upi_id'] ?? ''),
            'UpiToBank'               => trim($_POST['upi_to_bank'] ?? '')
        ];
    }

    if (empty($errors)) {
        $updateResult = $model->updateJewelLoan($editLoanId, $customerData, $loanData, $itemsData);
        if ($updateResult['status']) {
            // Synchronously update initial LOAN_CREATION receipt to reflect the edited loan amount and charges
            if (!empty($conn) && $editLoanId > 0 && !empty($loanData)) {
                $newLoanAmt = (float)$loanData['LoanAmount'];
                $newAppr = (float)$loanData['AppraiserCharge'];
                $newPF = (float)$loanData['ProcessingFee'];
                $newTotPaid = $newLoanAmt + $newAppr + $newPF;

                $upRecStmt = $conn->prepare("UPDATE ast_jewel_loan_receipt SET 
                    PrincipalPaid = ?, 
                    TotalPaid = ?, 
                    BalanceLoanAmount = ?, 
                    AppraiserCharge = ?, 
                    ProcessingFee = ? 
                    WHERE LoanDetailId = ? AND TransactionType = 'LOAN_CREATION'");
                if ($upRecStmt) {
                    $upRecStmt->bind_param("dddddi", $newLoanAmt, $newTotPaid, $newLoanAmt, $newAppr, $newPF, $editLoanId);
                    $upRecStmt->execute();
                    $upRecStmt->close();
                }
            }
            $success = "Jewel Loan details updated successfully for D-Number <strong>" . htmlspecialchars($editDNumber) . "</strong>!";
            $dNumParam = $editDNumber;
            $loanIdParam = $editLoanId;
            $isEditRequested = false; // Switch back to View mode on success

            $processStatus = [
                'type' => 'edit_account',
                'title' => 'Changes Saved Successfully!',
                'message' => 'The jewel loan customer details, loan information, and pledged ornaments have been updated in the database.',
                'dNumber' => $editDNumber,
                'customerName' => $editName,
                'loanAmount' => $loanData ? $editLoanAmount : 0,
                'totalWeight' => $totalWeight,
                'loanDetailId' => $editLoanId,
                'viewUrl' => 'aster-jewel-loan-view.php?dnum=' . urlencode($editDNumber),
                'pdfUrl' => 'aster-jewel-loan-pdf.php?id=' . $editLoanId,
                'receiptUrl' => 'aster-jewel-loan-receipt.php?dnum=' . urlencode($editDNumber),
                'timestamp' => date('d/m/Y h:i A')
            ];
        } else {
            $errors[] = "Update failed: " . $updateResult['error'];
        }
    }
}

// ==========================================
// FETCH CURRENT RECORD DATA FOR VIEW / EDIT
// ==========================================
$customer = null;
$loan = null;
$items = [];
$receipts = [];
$prinPaidSoFar = 0.00;
$member = null;

// 1. Fetch by loan_id or dnum
if ($loanIdParam > 0 || !empty($dNumParam)) {
    $record = $model->getLoanRecordForPdf($loanIdParam, $dNumParam);
    if ($record && !empty($record['loan'])) {
        $loan = $record['loan'];
        $customer = $record['customer'];
        $items = $record['items'];

        // Calculate principal paid so far on this loan for synchronous balance adjustment
        if (!empty($loan['LoanDetailId']) && !empty($conn)) {
            $pStmt = $conn->prepare("SELECT COALESCE(SUM(PrincipalPaid), 0) AS pPaid FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? AND TransactionType = 'INTEREST_PAYMENT'");
            if ($pStmt) {
                $pStmt->bind_param("i", $loan['LoanDetailId']);
                $pStmt->execute();
                $pRes = $pStmt->get_result();
                if ($pRes && $pRow = $pRes->fetch_assoc()) {
                    $prinPaidSoFar = (float)$pRow['pPaid'];
                }
                $pStmt->close();
            }
        }
    }
}

// 2. Fallback: by member ID if provided
if (!$loan && $idParam > 0) {
    $member = $model->getMemberById($idParam);
    if ($member) {
        $memCode = $member['MemberCode'] ?? '';
        $memAadhaar = $member['Aadhaar'] ?? '';
        $memMobile = $member['MobileNo'] ?? '';

        if (!empty($memCode)) {
            $record = $model->getLoanRecordForPdf(0, $memCode);
            if ($record && !empty($record['loan'])) {
                $loan = $record['loan'];
                $customer = $record['customer'];
                $items = $record['items'];
            }
        }

        if (!$loan && (!empty($memAadhaar) || !empty($memMobile))) {
            $sqlLookup = "SELECT d.LoanDetailId, d.DNumber FROM ast_jewel_loan_details d
                          LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = d.DNumber
                          WHERE (c.AadhaarNumber = ? AND c.AadhaarNumber != '')
                             OR (c.MobileNumber = ? AND c.MobileNumber != '')
                          ORDER BY d.LoanDetailId DESC LIMIT 1";
            $stLookup = $conn->prepare($sqlLookup);
            if ($stLookup) {
                $stLookup->bind_param("ss", $memAadhaar, $memMobile);
                $stLookup->execute();
                $resLookup = $stLookup->get_result();
                if ($resLookup && $rLookup = $resLookup->fetch_assoc()) {
                    $record = $model->getLoanRecordForPdf((int)$rLookup['LoanDetailId'], $rLookup['DNumber']);
                    if ($record && !empty($record['loan'])) {
                        $loan = $record['loan'];
                        $customer = $record['customer'];
                        $items = $record['items'];
                    }
                }
                $stLookup->close();
            }
        }
    }
}

// 3. Fetch receipts history
if ($loan) {
    $ldId = (int)$loan['LoanDetailId'];
    $sqlR = "SELECT * FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? ORDER BY ReceiptId DESC";
    $stR = $conn->prepare($sqlR);
    if ($stR) {
        $stR->bind_param("i", $ldId);
        $stR->execute();
        $resR = $stR->get_result();
        if ($resR) {
            while ($rowR = $resR->fetch_assoc()) {
                $receipts[] = $rowR;
            }
        }
        $stR->close();
    }
}

// Fallback: If only customer exists in ast_jewel_loan_customer without loan details
if (!$loan && !$customer && !empty($dNumParam)) {
    $customer = $model->getCustomerByDNumber($dNumParam);
}

// Dropdowns for edit mode
$companyBanks = $model->getCompanyBanks();
$upiTypes     = $model->getUpiTypes();

$isEditMode = $isEditRequested && ($loan || $customer);
$activeDNum = $loan['DNumber'] ?? ($customer['DNumber'] ?? ($member['MemberCode'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - <?php echo $isEditMode ? 'Edit Jewel Loan Details' : 'View Jewel Loan Details'; ?></title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <style>
        .details-card {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .card-header-title {
            font-size: 16px;
            font-weight: 700;
            color: #337ab7;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e5e5;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 14px;
            font-weight: 500;
            color: #222;
            word-wrap: break-word;
            margin-bottom: 12px;
        }
        .field-value-highlight {
            font-size: 15px;
            font-weight: bold;
            color: #d9534f;
        }
        .badge-dnumber {
            font-size: 15px;
            padding: 5px 12px;
            border-radius: 3px;
            background-color: #337ab7;
            color: #fff;
            display: inline-block;
        }
        .table-custom-items {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        .table-custom-items th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
            text-align: center;
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 13px;
        }
        .table-custom-items td {
            border: 1px solid #ddd;
            padding: 7px 10px;
            font-size: 13px;
            vertical-align: middle;
        }
        .table-custom-items tfoot td {
            background-color: #fafafa;
            font-weight: bold;
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .mandatory-star {
            color: #d9534f;
            font-weight: bold;
        }
        .edit-banner {
            background: #fcf8e3;
            border: 1px solid #faebcc;
            color: #8a6d3b;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        @media print {
            .no-print, .navbar, #side-menu, footer {
                display: none !important;
            }
            #page-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            .details-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">

    <?php include_once "header.php"; ?>

    <div id="page-wrapper">
        <div class="row no-print">
            <div class="col-sm-6">
                <h3 style="color: #337ab7; margin-top: 15px; margin-bottom: 5px; font-weight: 600;">
                    <?php if ($isEditMode): ?>
                        <i class="fa fa-pencil-square-o"></i> Edit Jewel Loan Details
                    <?php else: ?>
                        <i class="fa fa-file-text-o"></i> View Jewel Loan Details
                    <?php endif; ?>
                </h3>
            </div>
            <div class="col-sm-6 text-right" style="margin-top: 15px;">
                <?php if ($isEditMode): ?>
                    <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($activeDNum); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-eye"></i> Cancel Edit (View Mode)
                    </a>
                <?php else: ?>
                    <?php if ($loan || $customer): ?>
                        <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($activeDNum); ?>&edit=1" class="btn btn-warning btn-sm">
                            <i class="fa fa-pencil"></i> Edit Details
                        </a>
                    <?php endif; ?>
                    <a href="aster-jewel-loan-search.php" class="btn btn-default btn-sm" style="margin-left: 5px;">
                        <i class="fa fa-arrow-left"></i> Back to Search
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print();" style="margin-left: 5px;">
                        <i class="fa fa-print"></i> Print
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <hr class="hr-background no-print" style="margin-top: 8px; margin-bottom: 20px;">

        <!-- Process Status Modal & In-Page Visual Confirmation -->
        <?php include_once "process-status-modal.php"; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible no-print" role="alert">
                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                <?php foreach ($errors as $e): ?>
                    <p style="margin: 0;"><i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$loan && !$customer && !$member): ?>
            <div class="alert alert-warning text-center">
                <h4><i class="fa fa-exclamation-triangle"></i> Record Not Found</h4>
                <p>No customer, member, or jewel loan record found for the provided identifier.</p>
                <div style="margin-top: 15px;">
                    <a href="aster-jewel-loan-search.php" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search Members
                    </a>
                    <a href="aster-jewel-loan.php" class="btn btn-success" style="margin-left: 8px;">
                        <i class="fa fa-plus"></i> New Loan Application
                    </a>
                </div>
            </div>
        <?php else: ?>

            <?php
            $displayDNum = $loan['DNumber'] ?? ($customer['DNumber'] ?? ($member['MemberCode'] ?? ''));
            $displayName = $customer['Name'] ?? ($member['FullName'] ?? '');
            ?>

            <!-- Identifier Header Bar -->
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-8 col-xs-12">
                    <span class="badge-dnumber">D Number: <?php echo htmlspecialchars($displayDNum); ?></span>
                    <span style="font-size: 18px; font-weight: bold; margin-left: 12px; color: #333;">
                        <?php echo htmlspecialchars($displayName); ?>
                    </span>
                    <?php if ($isEditMode): ?>
                        <span class="label label-warning" style="margin-left: 10px; font-size: 12px;">Editing Mode</span>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 col-xs-12 text-right no-print" style="margin-top: 5px;">
                    <?php if (!empty($loan['LoanDetailId']) && !$isEditMode): ?>
                        <a href="aster-jewel-loan-pdf.php?id=<?php echo urlencode($loan['LoanDetailId']); ?>" target="_blank" class="btn btn-info btn-sm">
                            <i class="fa fa-file-pdf-o"></i> Sanction PDF
                        </a>
                        <a href="aster-jewel-loan-receipt.php?dnum=<?php echo urlencode($loan['DNumber']); ?>" class="btn btn-success btn-sm" style="margin-left: 5px;">
                            <i class="fa fa-ticket"></i> Receipts
                        </a>
                    <?php elseif (!empty($member['MemberId']) && !$loan): ?>
                        <a href="aster-jewel-loan.php?id=<?php echo urlencode($member['MemberId']); ?>" class="btn btn-success btn-sm">
                            <i class="fa fa-plus-circle"></i> Create Jewel Loan
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-BRANCH 1: EDIT MODE FORM                                   -->
            <!-- ============================================================== -->
            <?php if ($isEditMode): ?>
                <div class="edit-banner no-print">
                    <i class="fa fa-info-circle"></i> <strong>Editing Details:</strong> Modify the customer, loan, or pledged ornament fields below and click <strong>Save Changes</strong>. The D-Number is strictly unique and sequential and cannot be changed.
                </div>

                <form method="post" action="aster-jewel-loan-view.php?dnum=<?php echo urlencode($displayDNum); ?>&edit=1" enctype="multipart/form-data" id="jewel-loan-edit-form">
                    <input type="hidden" name="d_number" value="<?php echo htmlspecialchars($displayDNum); ?>">
                    <input type="hidden" name="loan_detail_id" value="<?php echo htmlspecialchars($loan['LoanDetailId'] ?? 0); ?>">
                    <input type="hidden" name="existing_upload_sign" value="<?php echo htmlspecialchars($customer['UploadSign'] ?? ''); ?>">
                    <input type="hidden" name="existing_upload_aadhaar" value="<?php echo htmlspecialchars($customer['UploadAadhaar'] ?? ''); ?>">
                    <input type="hidden" id="edit_prin_paid_so_far" value="<?php echo (float)$prinPaidSoFar; ?>">

                    <!-- Customer Profile Edit Card -->
                    <div class="details-card">
                        <div class="card-header-title">
                            <i class="fa fa-user"></i> Customer Personal Details (Editable)
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>D Number</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($displayDNum); ?>" readonly style="background-color: #f7f7f7;">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Full Name <span class="mandatory-star">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($displayName); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Father's Name / Husband's Name</label>
                                    <input type="text" name="fathers_name" class="form-control" value="<?php echo htmlspecialchars($customer['FathersName'] ?? ($member['SDWName'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Occupation / Job</label>
                                    <input type="text" name="job" class="form-control" value="<?php echo htmlspecialchars($customer['Job'] ?? ($member['Occupation'] ?? '')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($customer['DateOfBirth'] ?? ($member['DateOfBirth'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Mobile Number <span class="mandatory-star">*</span></label>
                                    <input type="text" name="mobile_number" class="form-control" required value="<?php echo htmlspecialchars($customer['MobileNumber'] ?? ($member['MobileNo'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Aadhaar Number</label>
                                    <input type="text" name="aadhaar_number" class="form-control" value="<?php echo htmlspecialchars($customer['AadhaarNumber'] ?? ($member['Aadhaar'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Pincode</label>
                                    <input type="text" name="pincode" class="form-control" value="<?php echo htmlspecialchars($customer['Pincode'] ?? ($member['PinCode'] ?? '')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>Residential Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($customer['Address'] ?? ($member['PermenantAddress'] ?? ($member['PresentAddress'] ?? ''))); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Packet Number</label>
                                    <input type="text" name="packet_number" class="form-control" value="<?php echo htmlspecialchars($customer['PacketNumber'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Reason for Loan</label>
                                    <input type="text" name="reason_for_loan" class="form-control" value="<?php echo htmlspecialchars($customer['ReasonForLoan'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Nominee Name</label>
                                    <input type="text" name="nominee_name" class="form-control" value="<?php echo htmlspecialchars($customer['NomineeName'] ?? ($member['NomineeName'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Nominee Relation</label>
                                    <input type="text" name="nominee_relation" class="form-control" value="<?php echo htmlspecialchars($customer['NomineeRelation'] ?? ($member['NomineeRelation'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>If Nominee is Minor?</label>
                                    <select name="if_nominee_minor" id="edit_if_nominee_minor" class="form-control" onchange="toggleEditGuardian(this.value)">
                                        <option value="No" <?php echo (($customer['IfNomineeIsMinor'] ?? 'No') !== 'Yes') ? 'selected' : ''; ?>>No</option>
                                        <option value="Yes" <?php echo (($customer['IfNomineeIsMinor'] ?? '') === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6" id="edit_guardian_container" style="<?php echo (($customer['IfNomineeIsMinor'] ?? '') === 'Yes') ? '' : 'display: none;'; ?>">
                                <div class="form-group">
                                    <label>Guardian Name</label>
                                    <input type="text" name="guardian_name" class="form-control" value="<?php echo htmlspecialchars($customer['GuardianName'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>Nominee Address</label>
                                    <input type="text" name="nominee_address" class="form-control" value="<?php echo htmlspecialchars($customer['NomineeAddress'] ?? ($member['NomineeAddress'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Update Signature File</label>
                                    <input type="file" name="upload_sign" class="form-control" accept="image/*">
                                    <?php if (!empty($customer['UploadSign'])): ?>
                                        <small class="text-muted">Current file: <?php echo basename($customer['UploadSign']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>Update Aadhaar File</label>
                                    <input type="file" name="upload_aadhaar" class="form-control" accept="image/*,.pdf">
                                    <?php if (!empty($customer['UploadAadhaar'])): ?>
                                        <small class="text-muted">Current file: <?php echo basename($customer['UploadAadhaar']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($loan): ?>
                        <!-- Loan Details Edit Card -->
                        <div class="details-card">
                            <div class="card-header-title">
                                <i class="fa fa-money"></i> Loan Account Details (Editable)
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Loan Date <span class="mandatory-star">*</span></label>
                                        <input type="date" name="sub_loan_date" class="form-control" required value="<?php echo htmlspecialchars($loan['LoanDate']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Interest Rate <span class="mandatory-star">*</span></label>
                                        <select name="interest_rate" class="form-control" required>
                                            <option value="18" <?php echo ((float)$loan['InterestRate'] == 18) ? 'selected' : ''; ?>>18%</option>
                                            <option value="24" <?php echo ((float)$loan['InterestRate'] == 24) ? 'selected' : ''; ?>>24%</option>
                                            <option value="30" <?php echo ((float)$loan['InterestRate'] == 30) ? 'selected' : ''; ?>>30%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Loan Sanctioned Amount (₹) <span class="mandatory-star">*</span></label>
                                        <input type="number" step="0.01" min="1" name="sub_loan_amount" id="sub_loan_amount" class="form-control text-right" required value="<?php echo htmlspecialchars($loan['LoanAmount']); ?>" oninput="onEditLoanAmountChange(this.value)">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Balance Loan Amount (₹) <span class="mandatory-star">*</span></label>
                                        <input type="number" step="0.01" min="0" name="sub_balance_amount" id="sub_balance_amount" class="form-control text-right" required value="<?php echo htmlspecialchars($loan['BalanceLoanAmount']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Amount / Gram (₹)</label>
                                        <input type="number" step="0.01" min="0" name="sub_amount_per_gram" id="sub_amount_per_gram" class="form-control text-right" value="<?php echo htmlspecialchars($loan['AmountPerGram']); ?>" oninput="recalculateEditTotals()">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Total Valuation Amount (₹)</label>
                                        <input type="number" step="0.01" min="0" name="sub_total_amount" id="sub_total_amount" class="form-control text-right" value="<?php echo htmlspecialchars($loan['TotalAmount']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Max Amount / Gram (₹)</label>
                                        <input type="number" step="0.01" min="0" name="sub_max_amount_per_gram" id="sub_max_amount_per_gram" class="form-control text-right" value="<?php echo htmlspecialchars($loan['MaxAmountPerGram']); ?>" oninput="recalculateEditTotals()">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Allowed Amount (₹)</label>
                                        <input type="number" step="0.01" min="0" name="sub_allowed_amount" id="sub_allowed_amount" class="form-control text-right" value="<?php echo htmlspecialchars($loan['AllowedAmount']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Appraiser Charge (₹)</label>
                                        <input type="number" step="0.01" min="0" name="sub_appraiser_charge" id="sub_appraiser_charge" class="form-control text-right" value="<?php echo htmlspecialchars($loan['AppraiserCharge']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Processing Fee (%)</label>
                                        <input type="number" step="0.01" min="0" name="sub_processing_fee_percentage" id="sub_processing_fee_percentage" class="form-control text-right" value="<?php echo htmlspecialchars($loan['ProcessingFeePercentage']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Processing Fee (₹)</label>
                                        <input type="number" step="0.01" min="0" name="sub_processing_fee" id="sub_processing_fee" class="form-control text-right" value="<?php echo htmlspecialchars($loan['ProcessingFee']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Disbursement Method</label>
                                        <select name="payment_method" id="edit_payment_method" class="form-control" onchange="toggleEditPayment(this.value)">
                                            <option value="Cash" <?php echo (($loan['PaymentMethod'] ?? '') === 'Cash') ? 'selected' : ''; ?>>Cash</option>
                                            <option value="Cheque" <?php echo (($loan['PaymentMethod'] ?? '') === 'Cheque') ? 'selected' : ''; ?>>Cheque</option>
                                            <option value="RTGS" <?php echo (($loan['PaymentMethod'] ?? '') === 'RTGS') ? 'selected' : ''; ?>>RTGS</option>
                                            <option value="UPI" <?php echo (($loan['PaymentMethod'] ?? '') === 'UPI') ? 'selected' : ''; ?>>UPI</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Cheque Details -->
                            <div id="edit_box_cheque" style="<?php echo (($loan['PaymentMethod'] ?? '') === 'Cheque') ? '' : 'display:none;'; ?> background: #fbfbfb; border: 1px dashed #ccc; padding: 10px 15px; margin-bottom: 12px; border-radius: 4px;">
                                <div class="row">
                                    <div class="col-sm-4"><label>Cheque Number</label><input type="text" name="cheque_number" class="form-control" value="<?php echo htmlspecialchars($loan['ChequeNumber'] ?? ''); ?>"></div>
                                    <div class="col-sm-4">
                                        <label>From Bank</label>
                                        <select name="cheque_from_bank" class="form-control">
                                            <option value="">Select Company Bank</option>
                                            <?php foreach ($companyBanks as $b): ?>
                                                <option value="<?php echo htmlspecialchars($b['BankName']); ?>" <?php echo (($loan['ChequeFromBank'] ?? '') === $b['BankName']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['BankName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4"><label>To Bank</label><input type="text" name="cheque_to_bank" class="form-control" value="<?php echo htmlspecialchars($loan['ChequeToBank'] ?? ''); ?>"></div>
                                </div>
                            </div>

                            <!-- RTGS Details -->
                            <div id="edit_box_rtgs" style="<?php echo (($loan['PaymentMethod'] ?? '') === 'RTGS') ? '' : 'display:none;'; ?> background: #fbfbfb; border: 1px dashed #ccc; padding: 10px 15px; margin-bottom: 12px; border-radius: 4px;">
                                <div class="row">
                                    <div class="col-sm-4"><label>RTGS Number</label><input type="text" name="rtgs_number" class="form-control" value="<?php echo htmlspecialchars($loan['RtgsNumber'] ?? ''); ?>"></div>
                                    <div class="col-sm-4">
                                        <label>From Bank</label>
                                        <select name="rtgs_from_bank" class="form-control">
                                            <option value="">Select Company Bank</option>
                                            <?php foreach ($companyBanks as $b): ?>
                                                <option value="<?php echo htmlspecialchars($b['BankName']); ?>" <?php echo (($loan['RtgsFromBank'] ?? '') === $b['BankName']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['BankName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4"><label>To Bank</label><input type="text" name="rtgs_to_bank" class="form-control" value="<?php echo htmlspecialchars($loan['RtgsToBank'] ?? ''); ?>"></div>
                                </div>
                            </div>

                            <!-- UPI Details -->
                            <div id="edit_box_upi" style="<?php echo (($loan['PaymentMethod'] ?? '') === 'UPI') ? '' : 'display:none;'; ?> background: #fbfbfb; border: 1px dashed #ccc; padding: 10px 15px; margin-bottom: 12px; border-radius: 4px;">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <label>UPI Type</label>
                                        <select name="upi_type" class="form-control">
                                            <option value="">Select UPI Type</option>
                                            <?php foreach ($upiTypes as $u): ?>
                                                <option value="<?php echo htmlspecialchars($u['UPITypeName']); ?>" <?php echo (($loan['UpiType'] ?? '') === $u['UPITypeName']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['UPITypeName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4"><label>UPI ID / Txn Ref</label><input type="text" name="upi_id" class="form-control" value="<?php echo htmlspecialchars($loan['UpiTransactionNumber'] ?? ''); ?>"></div>
                                    <div class="col-sm-4">
                                        <label>To Bank</label>
                                        <select name="upi_to_bank" class="form-control">
                                            <option value="">Select Company Bank</option>
                                            <?php foreach ($companyBanks as $b): ?>
                                                <option value="<?php echo htmlspecialchars($b['BankName']); ?>" <?php echo (($loan['UpiToBank'] ?? '') === $b['BankName']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['BankName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pledged Ornaments Editable Table Card -->
                        <div class="details-card">
                            <div class="card-header-title">
                                <i class="fa fa-diamond"></i> Pledged Gold Ornaments (Editable)
                                <button type="button" class="btn btn-xs btn-primary pull-right" onclick="addEditOrnamentRow()">
                                    <i class="fa fa-plus"></i> Add Item Row
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="edit-ornaments-table">
                                    <thead>
                                        <tr style="background-color: #f5f5f5;">
                                            <th style="width: 50px; text-align: center;">Sl</th>
                                            <th>Item Description</th>
                                            <th style="width: 120px;">No. of Items</th>
                                            <th style="width: 140px;">Gross (g)</th>
                                            <th style="width: 140px;">Wastage (g)</th>
                                            <th style="width: 150px;">Net Wt (g)</th>
                                            <th style="width: 60px; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit-ornaments-body">
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $idx => $it): ?>
                                                <tr class="edit-ornament-row">
                                                    <td class="text-center row-sl"><?php echo $idx + 1; ?></td>
                                                    <td>
                                                        <input type="text" name="item_name[]" class="form-control input-sm" value="<?php echo htmlspecialchars($it['ItemName']); ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="item_no[]" class="form-control input-sm text-center row-count" value="<?php echo (int)$it['NoOfItems']; ?>" min="1" oninput="recalculateEditTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.001" min="0" name="item_gram[]" class="form-control input-sm text-right row-gram" value="<?php echo number_format((float)$it['Gram'], 3, '.', ''); ?>" required oninput="recalculateEditTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.001" min="0" name="item_wastage[]" class="form-control input-sm text-right row-wastage" value="<?php echo number_format((float)$it['Wastage'], 3, '.', ''); ?>" oninput="recalculateEditTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.001" min="0" name="item_net_weight[]" class="form-control input-sm text-right row-net" value="<?php echo number_format((float)$it['NetWeight'], 3, '.', ''); ?>" readonly style="background: #f7f7f7; font-weight: bold;">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-danger" onclick="removeEditOrnamentRow(this)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr class="edit-ornament-row">
                                                <td class="text-center row-sl">1</td>
                                                <td><input type="text" name="item_name[]" class="form-control input-sm" placeholder="Item Name" required></td>
                                                <td><input type="number" name="item_no[]" class="form-control input-sm text-center row-count" value="1" min="1" oninput="recalculateEditTotals()"></td>
                                                <td><input type="number" step="0.001" min="0" name="item_gram[]" class="form-control input-sm text-right row-gram" placeholder="0.000" required oninput="recalculateEditTotals()"></td>
                                                <td><input type="number" step="0.001" min="0" name="item_wastage[]" class="form-control input-sm text-right row-wastage" value="0.000" oninput="recalculateEditTotals()"></td>
                                                <td><input type="number" step="0.001" min="0" name="item_net_weight[]" class="form-control input-sm text-right row-net" value="0.000" readonly style="background: #f7f7f7; font-weight: bold;"></td>
                                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeEditOrnamentRow(this)"><i class="fa fa-trash"></i></button></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr style="background-color: #fafafa; font-weight: bold;">
                                            <td colspan="2" class="text-center">Total</td>
                                            <td class="text-center" id="edit_tot_items">0</td>
                                            <td class="text-right" id="edit_tot_gross">0.000 g</td>
                                            <td class="text-right" id="edit_tot_wastage">0.000 g</td>
                                            <td class="text-right" id="edit_tot_net" style="color: #337ab7;">0.000 g</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form Action Toolbar -->
                    <div class="row no-print" style="margin-top: 20px; margin-bottom: 40px;">
                        <div class="col-md-12">
                            <button type="submit" name="update_jewel_loan" class="btn btn-success" style="padding: 7px 25px; font-weight: bold;">
                                <i class="fa fa-check"></i> Save Changes
                            </button>
                            <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($displayDNum); ?>" class="btn btn-default" style="padding: 7px 20px; margin-left: 10px;">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>

                </form>

            <!-- ============================================================== -->
            <!-- SUB-BRANCH 2: NORMAL VIEW MODE                                 -->
            <!-- ============================================================== -->
            <?php else: ?>

                <!-- Customer Details Panel -->
                <div class="details-card">
                    <div class="card-header-title">
                        <i class="fa fa-user"></i> Customer Personal Details
                        <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($displayDNum); ?>&edit=1" class="btn btn-xs btn-warning pull-right no-print">
                            <i class="fa fa-pencil"></i> Edit Details
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Full Name</div>
                            <div class="field-value"><?php echo htmlspecialchars($displayName ?: '-'); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Father / Husband Name</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['FathersName'] ?? ($member['SDWName'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Occupation</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['Job'] ?? ($member['Occupation'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Date of Birth</div>
                            <div class="field-value">
                                <?php 
                                $dobVal = $customer['DateOfBirth'] ?? ($member['DateOfBirth'] ?? '');
                                echo (!empty($dobVal) && $dobVal !== '0000-00-00') ? date('d-m-Y', strtotime($dobVal)) : '-';
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Mobile Number</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['MobileNumber'] ?? ($member['MobileNo'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Aadhaar Number</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['AadhaarNumber'] ?? ($member['Aadhaar'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Pincode</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['Pincode'] ?? ($member['PinCode'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Address</div>
                            <div class="field-value"><?php echo nl2br(htmlspecialchars($customer['Address'] ?? ($member['PermenantAddress'] ?? ($member['PresentAddress'] ?? '-')))); ?></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 5px;">
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Nominee Name</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['NomineeName'] ?? ($member['NomineeName'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Nominee Relation</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['NomineeRelation'] ?? ($member['NomineeRelation'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Nominee Address</div>
                            <div class="field-value"><?php echo htmlspecialchars($customer['NomineeAddress'] ?? ($member['NomineeAddress'] ?? '-')); ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="field-label">Guardian (If Minor)</div>
                            <div class="field-value">
                                <?php 
                                $minor = $customer['IfNomineeIsMinor'] ?? 'No';
                                echo ($minor === 'Yes') ? htmlspecialchars($customer['GuardianName'] ?? 'Yes (Minor)') : 'No';
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($customer['UploadSign']) || !empty($customer['UploadAadhaar'])): ?>
                        <div class="row" style="margin-top: 10px; border-top: 1px dashed #eee; padding-top: 10px;">
                            <?php if (!empty($customer['UploadSign']) && file_exists($customer['UploadSign'])): ?>
                                <div class="col-sm-3">
                                    <div class="field-label">Customer Signature</div>
                                    <img src="<?php echo htmlspecialchars($customer['UploadSign']); ?>" alt="Sign" style="max-height: 60px; max-width: 160px; border: 1px solid #ddd; padding: 2px;">
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($customer['UploadAadhaar']) && file_exists($customer['UploadAadhaar'])): ?>
                                <div class="col-sm-3">
                                    <div class="field-label">Aadhaar Document</div>
                                    <a href="<?php echo htmlspecialchars($customer['UploadAadhaar']); ?>" target="_blank" class="btn btn-default btn-xs">
                                        <i class="fa fa-external-link"></i> View Document
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($loan): ?>
                    <!-- Loan Account Details Panel -->
                    <div class="details-card">
                        <div class="card-header-title">
                            <i class="fa fa-money"></i> Loan Account Details
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Loan Date</div>
                                <div class="field-value">
                                    <?php echo !empty($loan['LoanDate']) ? date('d-m-Y', strtotime($loan['LoanDate'])) : '-'; ?>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Interest Rate</div>
                                <div class="field-value"><?php echo number_format((float)$loan['InterestRate'], 2); ?>% p.a.</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Sanctioned Loan Amount</div>
                                <div class="field-value field-value-highlight">₹<?php echo number_format((float)$loan['LoanAmount'], 2); ?></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Balance Loan Amount</div>
                                <div class="field-value field-value-highlight">₹<?php echo number_format((float)$loan['BalanceLoanAmount'], 2); ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Total Net Weight</div>
                                <div class="field-value"><?php echo number_format((float)$loan['TotalNetWeight'], 3); ?> g</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Amount / Gram</div>
                                <div class="field-value">₹<?php echo number_format((float)$loan['AmountPerGram'], 2); ?></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Total Valuation Amount</div>
                                <div class="field-value">₹<?php echo number_format((float)$loan['TotalAmount'], 2); ?></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Allowed / Max Loan Amount</div>
                                <div class="field-value">₹<?php echo number_format((float)$loan['AllowedAmount'], 2); ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Appraiser Charge</div>
                                <div class="field-value">₹<?php echo number_format((float)$loan['AppraiserCharge'], 2); ?></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Processing Fee</div>
                                <div class="field-value">
                                    ₹<?php echo number_format((float)$loan['ProcessingFee'], 2); ?>
                                    <small class="text-muted">(<?php echo (float)$loan['ProcessingFeePercentage']; ?>%)</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Disbursement Method</div>
                                <div class="field-value">
                                    <span class="label label-info"><?php echo htmlspecialchars($loan['PaymentMethod'] ?? 'Cash'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="field-label">Disbursement Date</div>
                                <div class="field-value">
                                    <?php echo !empty($loan['PaymentDate']) ? date('d-m-Y', strtotime($loan['PaymentDate'])) : '-'; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Payment reference details if Cheque/RTGS/UPI -->
                        <?php if (($loan['PaymentMethod'] ?? '') === 'Cheque'): ?>
                            <div class="row" style="margin-top: 5px; background: #f9f9f9; padding: 8px; border-radius: 4px;">
                                <div class="col-sm-3"><div class="field-label">Cheque Number</div><div class="field-value"><?php echo htmlspecialchars($loan['ChequeNumber']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">From Bank</div><div class="field-value"><?php echo htmlspecialchars($loan['ChequeFromBank']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">To Bank</div><div class="field-value"><?php echo htmlspecialchars($loan['ChequeToBank']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">Paid Amount</div><div class="field-value">₹<?php echo number_format((float)$loan['PaymentAmount'], 2); ?></div></div>
                            </div>
                        <?php elseif (($loan['PaymentMethod'] ?? '') === 'RTGS'): ?>
                            <div class="row" style="margin-top: 5px; background: #f9f9f9; padding: 8px; border-radius: 4px;">
                                <div class="col-sm-3"><div class="field-label">RTGS Ref Number</div><div class="field-value"><?php echo htmlspecialchars($loan['RtgsNumber']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">From Bank</div><div class="field-value"><?php echo htmlspecialchars($loan['RtgsFromBank']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">To Bank</div><div class="field-value"><?php echo htmlspecialchars($loan['RtgsToBank']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">Paid Amount</div><div class="field-value">₹<?php echo number_format((float)$loan['PaymentAmount'], 2); ?></div></div>
                            </div>
                        <?php elseif (($loan['PaymentMethod'] ?? '') === 'UPI'): ?>
                            <div class="row" style="margin-top: 5px; background: #f9f9f9; padding: 8px; border-radius: 4px;">
                                <div class="col-sm-3"><div class="field-label">UPI Type</div><div class="field-value"><?php echo htmlspecialchars($loan['UpiType']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">UPI Transaction No</div><div class="field-value"><?php echo htmlspecialchars($loan['UpiTransactionNumber']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">Receiving Bank</div><div class="field-value"><?php echo htmlspecialchars($loan['UpiToBank']); ?></div></div>
                                <div class="col-sm-3"><div class="field-label">Paid Amount</div><div class="field-value">₹<?php echo number_format((float)$loan['PaymentAmount'], 2); ?></div></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pledged Gold Items Panel -->
                    <div class="details-card">
                        <div class="card-header-title">
                            <i class="fa fa-diamond"></i> Pledged Gold Ornaments
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-custom-items">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">Sl. No.</th>
                                        <th>Ornament Description</th>
                                        <th style="width: 130px;">No. of Items</th>
                                        <th style="width: 160px;">Gross Weight (g)</th>
                                        <th style="width: 160px;">Wastage (g)</th>
                                        <th style="width: 170px;">Net Weight (g)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalItemsCount = 0;
                                    $totalGrossWeight = 0;
                                    $totalWastageWeight = 0;
                                    $totalNetWeight = 0;
                                    ?>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach ($items as $idx => $it): ?>
                                            <?php 
                                            $cnt = (int)$it['NoOfItems'];
                                            $g = (float)$it['Gram'];
                                            $w = (float)$it['Wastage'];
                                            $net = (float)$it['NetWeight'];
                                            $totalItemsCount += $cnt;
                                            $totalGrossWeight += $g;
                                            $totalWastageWeight += $w;
                                            $totalNetWeight += $net;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?php echo $idx + 1; ?></td>
                                                <td><?php echo htmlspecialchars($it['ItemName']); ?></td>
                                                <td class="text-center"><?php echo $cnt; ?></td>
                                                <td class="text-right"><?php echo number_format($g, 3); ?></td>
                                                <td class="text-right"><?php echo number_format($w, 3); ?></td>
                                                <td class="text-right" style="font-weight: 600;"><?php echo number_format($net, 3); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No gold items recorded.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-center">Total</td>
                                        <td class="text-center"><?php echo $totalItemsCount; ?></td>
                                        <td class="text-right"><?php echo number_format($totalGrossWeight, 3); ?> g</td>
                                        <td class="text-right"><?php echo number_format($totalWastageWeight, 3); ?> g</td>
                                        <td class="text-right" style="color: #337ab7;"><?php echo number_format($totalNetWeight, 3); ?> g</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Receipt History Panel -->
                    <div class="details-card">
                        <div class="card-header-title">
                            <i class="fa fa-ticket"></i> Transaction & Receipts History
                        </div>
                        <?php if (!empty($receipts)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr style="background-color: #f5f5f5;">
                                        <th>Receipt No</th>
                                        <th>Receipt Date</th>
                                        <th>Transaction Type</th>
                                        <th>Payment Method</th>
                                        <th class="text-right">Balance Amount (₹)</th>
                                        <th class="text-right">Total Paid (₹)</th>
                                        <th class="text-right">Principal (₹)</th>
                                        <th class="text-right">Interest (₹)</th>
                                        <th class="text-right">Current Bal (₹)</th>
                                        <th class="text-center no-print" style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($receipts as $r): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($r['ReceiptNo']); ?></strong></td>
                                            <td><?php echo date('d-m-Y', strtotime($r['ReceiptDate'])); ?></td>
                                            <td>
                                                <span class="label label-<?php echo ($r['TransactionType'] === 'JEWEL_RELEASE') ? 'success' : 'primary'; ?>">
                                                    <?php echo htmlspecialchars($r['TransactionType']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($r['PaymentMethod']); ?></td>
                                            <td class="text-right">₹<?php echo number_format(($r['TransactionType'] === 'LOAN_CREATION') ? (float)$r['BalanceLoanAmount'] : ((float)$r['BalanceLoanAmount'] + (float)$r['PrincipalPaid']), 2); ?></td>
                                            <td class="text-right" style="font-weight: bold; color: #337ab7;">
                                                ₹<?php echo number_format((float)$r['TotalPaid'], 2); ?>
                                            </td>
                                            <td class="text-right">₹<?php echo number_format((float)$r['PrincipalPaid'], 2); ?></td>
                                            <td class="text-right">₹<?php echo number_format((float)$r['InterestPaid'], 2); ?></td>
                                            <td class="text-right">₹<?php echo number_format((float)$r['BalanceLoanAmount'], 2); ?></td>
                                            <td class="text-center no-print">
                                                <a href="aster-jewel-loan-receipt.php?receipt_id=<?php echo urlencode($r['ReceiptId']); ?>&view_only=1" 
                                                   class="btn btn-xs btn-info" title="View Receipt Voucher">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                                <a href="aster-jewel-loan-receipt-pdf.php?receipt_id=<?php echo urlencode($r['ReceiptId']); ?>" 
                                                   target="_blank" class="btn btn-xs btn-default" title="PDF Receipt">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center" style="margin-bottom: 0;">
                            No receipt transactions recorded for this jewel loan yet. 
                            <a href="aster-jewel-loan-receipt.php?dnum=<?php echo urlencode($loan['DNumber']); ?>" class="btn btn-xs btn-success" style="margin-left: 8px;">
                                <i class="fa fa-plus"></i> Create First Receipt
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Member without active loan -->
                <div class="alert alert-info text-center" style="margin-top: 20px;">
                    <h4><i class="fa fa-info-circle"></i> Member Found - No Active Jewel Loan</h4>
                    <p>This member does not currently have an active jewel loan account.</p>
                    <div style="margin-top: 15px;">
                        <a href="aster-jewel-loan.php?id=<?php echo urlencode($member['MemberId']); ?>" class="btn btn-success">
                            <i class="fa fa-plus-circle"></i> Create New Jewel Loan for this Member
                        </a>
                        <a href="aster-jewel-loan-search.php" class="btn btn-default" style="margin-left: 8px;">
                            <i class="fa fa-arrow-left"></i> Back to Search
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php endif; ?> <!-- End of View / Edit switch -->

        <?php endif; ?>

    </div>
    <!-- /#page-wrapper -->

    <?php include_once "footer.php"; ?>
</div>

<script src="js/jquery-1.9.1.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>

<script>
// Minor guardian toggler for edit mode
function toggleEditGuardian(val) {
    if (val === 'Yes') {
        $('#edit_guardian_container').slideDown(150);
    } else {
        $('#edit_guardian_container').slideUp(150);
    }
}

// Payment method fields toggler for edit mode
function toggleEditPayment(method) {
    $('#edit_box_cheque').hide();
    $('#edit_box_rtgs').hide();
    $('#edit_box_upi').hide();

    if (method === 'Cheque') {
        $('#edit_box_cheque').slideDown(150);
    } else if (method === 'RTGS') {
        $('#edit_box_rtgs').slideDown(150);
    } else if (method === 'UPI') {
        $('#edit_box_upi').slideDown(150);
    }
}

// Recalculate totals on ornament edit synchronously
function recalculateEditTotals() {
    var totItems = 0;
    var totGross = 0;
    var totWastage = 0;
    var totNet = 0;

    $('.edit-ornament-row').each(function() {
        var $row = $(this);
        var cnt = parseInt($row.find('.row-count').val()) || 0;
        var g   = parseFloat($row.find('.row-gram').val()) || 0;
        var w   = parseFloat($row.find('.row-wastage').val()) || 0;
        var net = g - w;
        if (net < 0) net = 0;

        $row.find('.row-net').val(net.toFixed(3));

        totItems += cnt;
        totGross += g;
        totWastage += w;
        totNet += net;
    });

    $('#edit_tot_items').text(totItems);
    $('#edit_tot_gross').text(totGross.toFixed(3) + ' g');
    $('#edit_tot_wastage').text(totWastage.toFixed(3) + ' g');
    $('#edit_tot_net').text(totNet.toFixed(3) + ' g');

    // 1. Auto update valuation amount if amount per gram is present
    var ratePerGm = parseFloat($('#sub_amount_per_gram').val()) || 0;
    if (ratePerGm > 0 && totNet > 0) {
        var valuation = totNet * ratePerGm;
        $('#sub_total_amount').val(valuation.toFixed(2));
    }

    // 2. Auto update allowed loan amount if max amount per gram is present
    var maxPerGm = parseFloat($('#sub_max_amount_per_gram').val()) || 0;
    if (maxPerGm > 0 && totNet > 0) {
        var allowed = totNet * maxPerGm;
        $('#sub_allowed_amount').val(allowed.toFixed(2));
    }
}

// Synchronously update financial charges and balance when loan amount is modified
function onEditLoanAmountChange(val) {
    var amt = parseFloat(val) || 0;
    if (amt <= 0) return;

    // Synchronize balance loan amount (deducting principal already repaid)
    var prinPaid = parseFloat($('#edit_prin_paid_so_far').val()) || 0;
    var newBal = Math.max(0, amt - prinPaid);
    $('#sub_balance_amount').val(newBal.toFixed(2));

    // Fetch updated financial charges from masters
    $.ajax({
        url: 'aster-jewel-loan.php',
        type: 'GET',
        data: { action: 'get_financial_charges', amount: amt },
        dataType: 'json',
        success: function(res) {
            if (res && res.status === 'success') {
                var charge = parseFloat(res.appraiser_charge) || 0;
                var pct = parseFloat(res.processing_fee_percentage) || 0;
                var fee = parseFloat(res.processing_fee) || 0;

                $('#sub_appraiser_charge').val(charge > 0 ? charge.toFixed(2) : '0');
                $('#sub_processing_fee_percentage').val(pct);
                $('#sub_processing_fee').val(fee > 0 ? fee.toFixed(2) : '0');
            }
        }
    });
}

// Add row to edit ornaments table
function addEditOrnamentRow() {
    var nextSl = $('#edit-ornaments-body tr').length + 1;
    var html = '<tr class="edit-ornament-row">' +
        '<td class="text-center row-sl">' + nextSl + '</td>' +
        '<td><input type="text" name="item_name[]" class="form-control input-sm" placeholder="Item Name" required></td>' +
        '<td><input type="number" name="item_no[]" class="form-control input-sm text-center row-count" value="1" min="1" oninput="recalculateEditTotals()"></td>' +
        '<td><input type="number" step="0.001" min="0" name="item_gram[]" class="form-control input-sm text-right row-gram" value="0.000" required oninput="recalculateEditTotals()"></td>' +
        '<td><input type="number" step="0.001" min="0" name="item_wastage[]" class="form-control input-sm text-right row-wastage" value="0.000" oninput="recalculateEditTotals()"></td>' +
        '<td><input type="number" step="0.001" min="0" name="item_net_weight[]" class="form-control input-sm text-right row-net" value="0.000" readonly style="background: #f7f7f7; font-weight: bold;"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeEditOrnamentRow(this)"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#edit-ornaments-body').append(html);
    recalculateEditTotals();
}

// Remove row from edit ornaments table
function removeEditOrnamentRow(btn) {
    if ($('#edit-ornaments-body tr').length > 1) {
        $(btn).closest('tr').remove();
        // Renumber serials
        $('#edit-ornaments-body tr').each(function(idx) {
            $(this).find('.row-sl').text(idx + 1);
        });
        recalculateEditTotals();
    } else {
        alert('At least one ornament item row must remain.');
    }
}

// Run totals on page load
$(document).ready(function() {
    if ($('#edit-ornaments-table').length) {
        recalculateEditTotals();
    }
});
</script>
</body>
</html>
