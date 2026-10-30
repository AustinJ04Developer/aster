<?php
session_start();
// Secure session handling
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}
if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once 'controller/JewelLoanController.php';

$controller = new JewelLoanController($conn);
$data = $controller->handleRequest();
extract($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Jewel Loan Creation</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
    <style>
        .section-heading {
            font-size: 16px;
            font-weight: bold;
            color: #337ab7;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e5e5;
        }
        .subsection-heading {
            font-size: 14px;
            font-weight: bold;
            color: #333333;
            margin-top: 15px;
            margin-bottom: 12px;
        }
        .table-gold-items {
            margin-top: 10px;
            margin-bottom: 15px;
            width: 100%;
        }
        .table-gold-items thead tr th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #ddd;
        }
        .table-gold-items tbody td {
            vertical-align: middle !important;
            border: 1px solid #ddd;
            padding: 6px 8px;
        }
        .table-gold-items tfoot td {
            vertical-align: middle !important;
            border: 1px solid #ddd;
            padding: 8px;
            background-color: #fafafa;
        }
        .table-gold-items input.form-control {
            height: 32px;
            padding: 4px 8px;
            font-size: 13px;
        }
        .btn-table-action {
            padding: 4px 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div id="wrapper">

    <?php include_once "header.php"; ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Jewel Loan Creation</h2>
                <hr class="hr-background">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption">
                    <span class="mandatory">*</span> Fields are mandatory
                </h6>
            </div>
        </div>

        <!-- Process Status Modal & In-Page Visual Confirmation -->
        <?php include_once "process-status-modal.php"; ?>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php foreach ($errors as $err): ?>
                    <p style="margin: 0;"><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- MAIN FORM -->
        <form method="post" action="aster-jewel-loan.php" id="jewel-loan-form" enctype="multipart/form-data">

            <!-- Customer Details Fields -->
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>D Number <span class="mandatory">*</span></label>
                        <input type="text" name="d_number" id="d_number" class="form-control" readonly
                               value="<?php echo htmlspecialchars($dNumber); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Name <span class="mandatory">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required
                               placeholder="Full Name"
                               value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Father's Name / Husband's Name</label>
                        <input type="text" name="fathers_name" id="fathers_name" class="form-control"
                               placeholder="Father's Name / Husband's Name"
                               value="<?php echo htmlspecialchars($form['fathers_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Job</label>
                        <input type="text" name="job" id="job" class="form-control"
                               placeholder="Job / Occupation"
                               value="<?php echo htmlspecialchars($form['job'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="form-control"
                               value="<?php echo htmlspecialchars($form['dob'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Mobile Number <span class="mandatory">*</span></label>
                        <input type="text" name="mobile_number" id="mobile_number" maxlength="15" class="form-control" required
                               placeholder="Mobile Number"
                               value="<?php echo htmlspecialchars($form['mobile_number'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Aadhaar Number</label>
                        <input type="text" name="aadhaar_number" id="aadhaar_number" maxlength="12" class="form-control"
                               placeholder="12-digit Aadhaar Number"
                               value="<?php echo htmlspecialchars($form['aadhaar_number'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Pincode</label>
                        <input type="text" name="pincode" id="pincode" maxlength="10" class="form-control"
                               placeholder="Pincode"
                               value="<?php echo htmlspecialchars($form['pincode'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Address <span class="mandatory">*</span></label>
                        <textarea name="address" id="address" rows="2" class="form-control" required
                                  placeholder="Full Residential Address"><?php echo htmlspecialchars($form['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nominee Address</label>
                        <textarea name="nominee_address" id="nominee_address" rows="2" class="form-control"
                                  placeholder="Nominee Residential Address"><?php echo htmlspecialchars($form['nominee_address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nominee Name</label>
                        <input type="text" name="nominee_name" id="nominee_name" class="form-control"
                               placeholder="Nominee Name"
                               value="<?php echo htmlspecialchars($form['nominee_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nominee Relation</label>
                        <input type="text" name="nominee_relation" id="nominee_relation" class="form-control"
                               placeholder="e.g. Spouse, Son, Mother"
                               value="<?php echo htmlspecialchars($form['nominee_relation'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>If Nominee is Minor?</label>
                        <select name="if_nominee_minor" id="if_nominee_minor" class="form-control" onchange="toggleGuardianField()">
                            <option value="No" <?php echo (($form['if_nominee_minor'] ?? 'No') === 'No') ? 'selected' : ''; ?>>No</option>
                            <option value="Yes" <?php echo (($form['if_nominee_minor'] ?? '') === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3" id="guardian_container" style="display: <?php echo (($form['if_nominee_minor'] ?? '') === 'Yes') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" id="guardian_name" class="form-control"
                               placeholder="Guardian Name"
                               value="<?php echo htmlspecialchars($form['guardian_name'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Loan Date <span class="mandatory">*</span></label>
                        <input type="date" name="loan_date" id="loan_date" class="form-control" required
                               value="<?php echo htmlspecialchars($form['loan_date'] ?? $loanDate); ?>"
                               onchange="syncSubtableDate(this.value)">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Packet Number</label>
                        <input type="text" name="packet_number" id="packet_number" class="form-control"
                               placeholder="Packet / Locker No."
                               value="<?php echo htmlspecialchars($form['packet_number'] ?? ''); ?>">
                    </div>
                </div>

<div class="col-md-3">
                    <div class="form-group">
                        <label>Gold Quality</label>
                        <input type="text" name="gold_quality" id="gold_quality" class="form-control"
                               placeholder="e.g. 22 Karat (916)"
                               value="<?php echo htmlspecialchars($form['gold_quality'] ?? '22K (916)'); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Total Weight (Grams)</label>
                        <input type="number" step="0.001" min="0" name="total_weight" id="main_total_weight" class="form-control"
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['total_weight']) && $form['total_weight'] !== '' ? $form['total_weight'] : '0'); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>No of Items</label>
                        <input type="number" min="0" name="no_of_items" id="main_no_of_items" class="form-control"
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['no_of_items']) && $form['no_of_items'] !== '' ? $form['no_of_items'] : '0'); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Permissible per Gram (₹)</label>
                        <input type="number" step="0.01" min="0" name="max_amount_per_gram" id="main_max_amount_per_gram" class="form-control"
                               placeholder="0" readonly
                               value="<?php echo htmlspecialchars(isset($form['max_amount_per_gram']) && $form['max_amount_per_gram'] !== '' && (float)$form['max_amount_per_gram'] != 0 ? $form['max_amount_per_gram'] : (string)$initialSchemeAmount); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Permissible Amount (₹)</label>
                        <input type="number" step="0.01" min="0" name="allowed_amount" id="main_allowed_amount" class="form-control"
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['allowed_amount']) && $form['allowed_amount'] !== '' ? $form['allowed_amount'] : '0'); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Amount (in rupees) <span class="mandatory">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" id="main_amount" class="form-control" required
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['amount']) && $form['amount'] !== '' ? $form['amount'] : '0'); ?>"
                               oninput="syncMainAmountToSub(this.value)">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Reason for Loan</label>
                        <input type="text" name="reason_for_loan" id="reason_for_loan" class="form-control"
                               placeholder="e.g. Agriculture, Business, Medical"
                               value="<?php echo htmlspecialchars($form['reason_for_loan'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Upload Sign</label>
                        <input type="file" name="upload_sign" id="upload_sign" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Upload Aadhaar</label>
                        <input type="file" name="upload_aadhaar" id="upload_aadhaar" class="form-control" accept="image/*,.pdf">
                    </div>
                </div>
            </div>

            <!-- LOAN DETAILS HEADING -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">Loan Details</div>
                </div>
            </div>

            <!-- Loan Account Details -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="subsection-heading">Loan Account Details</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>D Number</label>
                        <input type="text" id="sub_d_number_display" class="form-control" readonly
                               value="<?php echo htmlspecialchars($dNumber); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date <span class="mandatory">*</span></label>
                        <input type="date" name="sub_loan_date" id="sub_loan_date" class="form-control" required
                               value="<?php echo htmlspecialchars($form['sub_loan_date'] ?? $loanDate); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Interest Rate <span class="mandatory">*</span></label>
                        <select name="interest_rate" id="interest_rate" class="form-control" required onchange="fetchSchemeAmount(this.value);">
                            <option value="18" <?php echo (($form['interest_rate'] ?? '18') == '18') ? 'selected' : ''; ?>>18%</option>
                            <option value="24" <?php echo (($form['interest_rate'] ?? '') == '24') ? 'selected' : ''; ?>>24%</option>
                            <option value="30" <?php echo (($form['interest_rate'] ?? '') == '30') ? 'selected' : ''; ?>>30%</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Gold Items -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="subsection-heading">Gold Items</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-gold-items" id="gold-items-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Sl. No.</th>
                                    <th>Item</th>
                                    <th style="width: 130px;">No of Items</th>
                                    <th style="width: 150px;">Gram</th>
                                    <th style="width: 150px;">Wastage</th>
                                    <th style="width: 160px;">Net Weight</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="gold-items-body">
                                <!-- Default Row 1 -->
                                <tr class="gold-item-row" data-row="1">
                                    <td class="text-center row-slno">1</td>
                                    <td>
                                        <input type="text" name="item_name[]" class="form-control item-name" placeholder="Item Name (e.g. Ring, Chain)" required>
                                    </td>
                                    <td>
                                        <input type="number" min="1" step="1" name="item_no[]" class="form-control text-right item-count" value="1" oninput="calculateItemNetWeight(this)" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="0.001" name="item_gram[]" class="form-control text-right item-gram" placeholder="0" oninput="calculateItemNetWeight(this)" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="0.001" name="item_wastage[]" class="form-control text-right item-wastage" placeholder="0" value="0" oninput="calculateItemNetWeight(this)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" name="item_net_weight[]" class="form-control text-right item-net-weight" placeholder="0" readonly>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-table-action remove-row-btn" onclick="removeGoldRow(this)" title="Remove row">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-left" style="vertical-align: middle;">
                                        <button type="button" class="btn btn-primary btn-sm btn-table-action" id="add-more-item-btn" onclick="addGoldRow()">
                                            <i class="fa fa-plus"></i> Add More Item
                                        </button>
                                    </td>
                                    <td class="text-right" style="font-weight: bold; vertical-align: middle;">
                                        Total Items: <span id="summary-total-items">1</span>
                                    </td>
                                    <td class="text-right" style="font-weight: bold; vertical-align: middle;">
                                        Gross: <span id="summary-total-gram">0</span>
                                    </td>
                                    <td class="text-right" style="font-weight: bold; vertical-align: middle;">
                                        Wastage: <span id="summary-total-wastage">0</span>
                                    </td>
                                    <td class="text-right" style="font-weight: bold; vertical-align: middle;">
                                        Net: <span id="summary-total-net">0</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gold Valuation and Loan Amount -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="subsection-heading">Gold Valuation and Loan Amount</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Total Net Weight</label>
                        <input type="number" step="0.001" min="0" name="sub_total_net_weight" id="sub_total_net_weight" class="form-control"
                               placeholder="0" readonly
                               value="<?php echo htmlspecialchars(isset($form['sub_total_net_weight']) && $form['sub_total_net_weight'] !== '' && (float)$form['sub_total_net_weight'] != 0 ? $form['sub_total_net_weight'] : '0'); ?>">
                    </div>
                </div>
            </div>
            <!-- Hidden fields for backend compatibility (AmountPerGram and TotalAmount still saved to DB) -->
            <input type="hidden" name="sub_amount_per_gram" id="sub_amount_per_gram" value="<?php echo htmlspecialchars(isset($form['sub_amount_per_gram']) && $form['sub_amount_per_gram'] !== '' ? $form['sub_amount_per_gram'] : '0'); ?>">
            <input type="hidden" name="sub_total_amount" id="sub_total_amount" value="<?php echo htmlspecialchars(isset($form['sub_total_amount']) && $form['sub_total_amount'] !== '' && (float)$form['sub_total_amount'] != 0 ? $form['sub_total_amount'] : '0'); ?>">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Permissible per Gram (₹)</label>
                        <input type="number" step="0.01" min="0" name="sub_max_amount_per_gram" id="sub_max_amount_per_gram" class="form-control"
                               placeholder="0" readonly
                               value="<?php echo htmlspecialchars(isset($form['sub_max_amount_per_gram']) && $form['sub_max_amount_per_gram'] !== '' && (float)$form['sub_max_amount_per_gram'] != 0 ? $form['sub_max_amount_per_gram'] : (string)$initialSchemeAmount); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Permissible Amount (₹)</label>
                        <input type="number" step="0.01" min="0" name="sub_allowed_amount" id="sub_allowed_amount" class="form-control"
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['sub_allowed_amount']) && $form['sub_allowed_amount'] !== '' && (float)$form['sub_allowed_amount'] != 0 ? $form['sub_allowed_amount'] : '0'); ?>"
                               oninput="syncAllowedToMain()">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Loan Amount <span class="mandatory">*</span></label>
                        <input type="number" step="0.01" min="0" name="sub_loan_amount" id="sub_loan_amount" class="form-control" required
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['sub_loan_amount']) && $form['sub_loan_amount'] !== '' && (float)$form['sub_loan_amount'] != 0 ? $form['sub_loan_amount'] : '0'); ?>"
                               oninput="onLoanAmountChange(this.value)">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Balance Loan Amount <span class="mandatory">*</span></label>
                        <input type="number" step="0.01" min="0" name="sub_balance_loan_amount" id="sub_balance_loan_amount" class="form-control" required
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['sub_balance_loan_amount']) && $form['sub_balance_loan_amount'] !== '' && (float)$form['sub_balance_loan_amount'] != 0 ? $form['sub_balance_loan_amount'] : (isset($form['sub_loan_amount']) && $form['sub_loan_amount'] !== '' ? $form['sub_loan_amount'] : '0')); ?>"
                               oninput="onBalanceLoanAmountChange(this.value)">
                    </div>
                </div>
            </div>

            <!-- Financial Charges (Appraiser Charge, Processing Fee Percentage, Processing Fee) -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Appraiser Charge (₹)</label>
                        <input type="number" step="0.01" min="0" name="sub_appraiser_charge" id="sub_appraiser_charge" class="form-control" readonly
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['sub_appraiser_charge']) && $form['sub_appraiser_charge'] !== '' ? $form['sub_appraiser_charge'] : '0'); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Processing Fee Percentage (%)</label>
                        <input type="text" id="sub_processing_fee_percentage_display" class="form-control" readonly
                               placeholder="0%"
                               value="<?php 
                                   $pctVal = isset($form['sub_processing_fee_percentage']) && $form['sub_processing_fee_percentage'] !== '' ? (float)$form['sub_processing_fee_percentage'] : (float)$processingFeePercentage;
                                   echo htmlspecialchars(rtrim(rtrim(number_format($pctVal, 2, '.', ''), '0'), '.') . '%'); 
                               ?>">
                        <input type="hidden" name="sub_processing_fee_percentage" id="sub_processing_fee_percentage"
                               value="<?php echo htmlspecialchars((string)$pctVal); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Processing Fee (₹)</label>
                        <input type="number" step="0.01" min="0" name="sub_processing_fee" id="sub_processing_fee" class="form-control" readonly
                               placeholder="0"
                               value="<?php echo htmlspecialchars(isset($form['sub_processing_fee']) && $form['sub_processing_fee'] !== '' ? $form['sub_processing_fee'] : '0'); ?>">
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="display: block;">Payment Method <span class="mandatory">*</span></label>
                        <label class="radio-inline">
                            <input type="radio" name="payment_method" value="Cash" checked onclick="togglePaymentFields('Cash')"> Cash
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="payment_method" value="Cheque" onclick="togglePaymentFields('Cheque')"> Cheque
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="payment_method" value="RTGS" onclick="togglePaymentFields('RTGS')"> RTGS
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="payment_method" value="UPI" onclick="togglePaymentFields('UPI')"> UPI
                        </label>
                    </div>
                </div>
            </div>

            <!-- Conditional Cheque Fields: Cheque Date, Cheque Number, From Bank (dropdown), To Bank (typing/text), Amount to pay -->
            <div id="payment-cheque-box" style="display: none; margin-bottom: 15px;">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Cheque Date <span class="mandatory">*</span></label>
                            <input type="date" name="cheque_date" id="cheque_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Cheque Number <span class="mandatory">*</span></label>
                            <input type="text" name="cheque_number" id="cheque_number" class="form-control" placeholder="Cheque No.">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From Bank <span class="mandatory">*</span></label>
                            <select name="cheque_from_bank" id="cheque_from_bank" class="form-control">
                                <option value="">Select From Bank</option>
                                <?php foreach ($companyBanks as $b): ?>
                                    <option value="<?php echo htmlspecialchars($b['BankName']); ?>">
                                        <?php echo htmlspecialchars($b['BankName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To Bank <span class="mandatory">*</span></label>
                            <input type="text" name="cheque_to_bank" id="cheque_to_bank" class="form-control" placeholder="Enter Bank Name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Amount to pay <span class="mandatory">*</span></label>
                            <input type="number" step="0.01" min="0" name="cheque_payment_amount" id="cheque_payment_amount" class="form-control payment-amount-field" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conditional RTGS Fields: RTGS Date, RTGS/NEFT Details, Reference no, Amount to pay -->
            <div id="payment-rtgs-box" style="display: none; margin-bottom: 15px;">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>RTGS Date <span class="mandatory">*</span></label>
                            <input type="date" name="rtgs_date" id="rtgs_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>RTGS/NEFT Details <span class="mandatory">*</span></label>
                            <input type="text" name="rtgs_details" id="rtgs_details" class="form-control" placeholder="RTGS / NEFT Details">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Reference no <span class="mandatory">*</span></label>
                            <input type="text" name="rtgs_reference_no" id="rtgs_reference_no" class="form-control" placeholder="Reference Number / UTR">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Amount to pay <span class="mandatory">*</span></label>
                            <input type="number" step="0.01" min="0" name="rtgs_payment_amount" id="rtgs_payment_amount" class="form-control payment-amount-field" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conditional UPI Fields: UPI Date, UPI Type (dropdown), UPI Id, To Bank (dropdown), Amount to pay -->
            <div id="payment-upi-box" style="display: none; margin-bottom: 15px;">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>UPI Date <span class="mandatory">*</span></label>
                            <input type="date" name="upi_date" id="upi_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>UPI Type <span class="mandatory">*</span></label>
                            <select name="upi_type" id="upi_type" class="form-control">
                                <option value="">Select UPI Type</option>
                                <?php if (!empty($upiTypes)): ?>
                                    <?php foreach ($upiTypes as $u): ?>
                                        <option value="<?php echo htmlspecialchars($u['UPITypeName']); ?>">
                                            <?php echo htmlspecialchars($u['UPITypeName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="GPay">GPay</option>
                                    <option value="PhonePe">PhonePe</option>
                                    <option value="PayTM">PayTM</option>
                                    <option value="BHIM">BHIM</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>UPI Id <span class="mandatory">*</span></label>
                            <input type="text" name="upi_id" id="upi_id" class="form-control" placeholder="UPI ID / VPA (e.g. name@okhdfcbank)">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To Bank <span class="mandatory">*</span></label>
                            <select name="upi_to_bank" id="upi_to_bank" class="form-control">
                                <option value="">Select Company Bank</option>
                                <?php foreach ($companyBanks as $b): ?>
                                    <option value="<?php echo htmlspecialchars($b['BankName']); ?>">
                                        <?php echo htmlspecialchars($b['BankName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Amount to pay <span class="mandatory">*</span></label>
                            <input type="number" step="0.01" min="0" name="upi_payment_amount" id="upi_payment_amount" class="form-control payment-amount-field" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRIMARY SUBMIT / ACTION BUTTONS (Positioned cleanly at the bottom) -->
            <div class="row mt-3" style="margin-top: 25px; margin-bottom: 30px;">
                <div class="col-md-12">
                    <a href="aster-jewel-loan.php" class="btn btn-primary">New</a>

                    <button type="submit" name="jewel-loan-submit" id="save-jewel-loan" class="btn btn-success">
                        Submit
                    </button>

                    <a href="aster-jewel-loan-receipt.php" class="btn btn-info" style="margin-left: 5px;">
                        <i class="fa fa-ticket"></i> Jewel Loan Receipts
                    </a>
                </div>
            </div>

        </form>

    </div>
    <!-- /#page-wrapper -->

    <?php include_once "footer.php"; ?>
</div>

<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>

<script>
// Format numeric value for display: single '0' if zero or empty, otherwise precision formatted or trimmed
function formatDisplayZero(val, decimals) {
    if (val === '' || val === null || val === undefined) return '0';
    var num = parseFloat(val);
    if (isNaN(num) || num === 0) return '0';
    return decimals !== undefined ? num.toFixed(decimals) : num.toString();
}

// Toggle guardian field
function toggleGuardianField() {
    var isMinor = document.getElementById('if_nominee_minor').value;
    var container = document.getElementById('guardian_container');
    if (isMinor === 'Yes') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        document.getElementById('guardian_name').value = '';
    }
}

// Sync main date to subtable date
function syncSubtableDate(val) {
    if (val) {
        document.getElementById('sub_loan_date').value = val;
    }
}

// Sync max rate from main to sub
function syncMaxRateToSub(val) {
    document.getElementById('sub_max_amount_per_gram').value = val;
    calculateAllowedAmount();
}

// Fetch Permissible per Gram from master scheme by interest rate
function fetchSchemeAmount(rate) {
    if (!rate) return;
    $.ajax({
        url: 'aster-jewel-loan.php',
        type: 'GET',
        data: { action: 'get_scheme_amount', rate: rate },
        dataType: 'json',
        success: function(res) {
            if (res && res.status === 'success') {
                var amt = parseFloat(res.amount_to) || 0;
                var display = amt > 0 ? amt.toFixed(2) : '0';
                $('#sub_max_amount_per_gram').val(display);
                $('#main_max_amount_per_gram').val(display);
                calculateAllowedAmount();
            }
        },
        error: function(err) {
            console.error('Failed to fetch scheme amount', err);
        }
    });
}


// Sync amount from main to sub
function syncMainAmountToSub(val) {
    document.getElementById('sub_loan_amount').value = val;
    updateFinancialCharges(val);
}

// Sync sub valuation to loan amount
function syncValuationToLoan() {
    var totalVal = parseFloat(document.getElementById('sub_total_amount').value) || 0;
    if (totalVal > 0 && (!document.getElementById('sub_loan_amount').value || parseFloat(document.getElementById('sub_loan_amount').value) === 0)) {
        document.getElementById('sub_loan_amount').value = totalVal.toFixed(2);
        document.getElementById('main_amount').value = totalVal.toFixed(2);
    }
}

// Sync allowed to main
function syncAllowedToMain() {
    var allowed = document.getElementById('sub_allowed_amount').value;
    document.getElementById('main_allowed_amount').value = allowed;
}

// Dynamic calculation and master lookup for Appraiser Charge and Processing Fee
function updateFinancialCharges(val) {
    var amt = parseFloat(val) || 0;
    if (amt <= 0) {
        $('#sub_appraiser_charge').val('0');
        $('#sub_processing_fee').val('0');
        return;
    }
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
                $('#sub_processing_fee_percentage_display').val((pct % 1 === 0 ? pct.toFixed(0) : pct.toFixed(2)) + '%');
                $('#sub_processing_fee').val(fee > 0 ? fee.toFixed(2) : '0');
            }
        },
        error: function(err) {
            console.error('Failed to update financial charges', err);
        }
    });
}

var isBalanceLoanManuallyEdited = false;

function onBalanceLoanAmountChange(val) {
    isBalanceLoanManuallyEdited = true;
}

// On Loan Amount change
function onLoanAmountChange(val) {
    var amt = parseFloat(val) || 0;
    document.getElementById('main_amount').value = amt > 0 ? amt.toFixed(2) : '0';
    
    // Auto-update Balance Loan Amount if user hasn't manually modified it
    if (!isBalanceLoanManuallyEdited) {
        document.getElementById('sub_balance_loan_amount').value = amt > 0 ? amt.toFixed(2) : '0';
    }

    // Auto-fill payment amount across payment methods if currently empty or matching previous loan amount
    $('.payment-amount-field').each(function() {
        if (!$(this).val() || parseFloat($(this).val()) === 0) {
            $(this).val(amt > 0 ? amt.toFixed(2) : '');
        }
    });

    updateFinancialCharges(amt);
}

// Toggle Payment Method Fields
function togglePaymentFields(method) {
    $('#payment-cheque-box').hide();
    $('#payment-rtgs-box').hide();
    $('#payment-upi-box').hide();

    // Remove required attributes from all conditional payment fields
    $('#cheque_date, #cheque_number, #cheque_from_bank, #cheque_to_bank, #cheque_payment_amount').prop('required', false);
    $('#rtgs_date, #rtgs_details, #rtgs_reference_no, #rtgs_payment_amount').prop('required', false);
    $('#upi_date, #upi_type, #upi_id, #upi_to_bank, #upi_payment_amount').prop('required', false);

    var curLoan = parseFloat($('#sub_loan_amount').val()) || 0;

    if (method === 'Cheque') {
        $('#payment-cheque-box').show();
        $('#cheque_date, #cheque_number, #cheque_from_bank, #cheque_to_bank, #cheque_payment_amount').prop('required', true);
        if (!$('#cheque_payment_amount').val() && curLoan > 0) {
            $('#cheque_payment_amount').val(curLoan.toFixed(2));
        }
    } else if (method === 'RTGS') {
        $('#payment-rtgs-box').show();
        $('#rtgs_date, #rtgs_details, #rtgs_reference_no, #rtgs_payment_amount').prop('required', true);
        if (!$('#rtgs_payment_amount').val() && curLoan > 0) {
            $('#rtgs_payment_amount').val(curLoan.toFixed(2));
        }
    } else if (method === 'UPI') {
        $('#payment-upi-box').show();
        $('#upi_date, #upi_type, #upi_id, #upi_to_bank, #upi_payment_amount').prop('required', true);
        if (!$('#upi_payment_amount').val() && curLoan > 0) {
            $('#upi_payment_amount').val(curLoan.toFixed(2));
        }
    }
}

// Gold Items calculation
function calculateItemNetWeight(elem) {
    var row = $(elem).closest('tr');
    var gram = parseFloat(row.find('.item-gram').val()) || 0;
    var wastage = parseFloat(row.find('.item-wastage').val()) || 0;
    var net = gram - wastage;
    if (net < 0) net = 0;
    row.find('.item-net-weight').val(formatDisplayZero(net, 3));
    recalculateAllTotals();
}

function recalculateAllTotals() {
    var totalCount = 0;
    var totalGram = 0;
    var totalWastage = 0;
    var totalNet = 0;

    $('#gold-items-body tr').each(function() {
        var count = parseInt($(this).find('.item-count').val()) || 0;
        var gram = parseFloat($(this).find('.item-gram').val()) || 0;
        var wastage = parseFloat($(this).find('.item-wastage').val()) || 0;
        var net = parseFloat($(this).find('.item-net-weight').val()) || (gram - wastage);
        if (net < 0) net = 0;

        totalCount += count;
        totalGram += gram;
        totalWastage += wastage;
        totalNet += net;
    });

    $('#summary-total-items').text(totalCount > 0 ? totalCount : 0);
    $('#summary-total-gram').text(formatDisplayZero(totalGram, 3));
    $('#summary-total-wastage').text(formatDisplayZero(totalWastage, 3));
    $('#summary-total-net').text(formatDisplayZero(totalNet, 3));

    $('#sub_total_net_weight').val(formatDisplayZero(totalNet, 3));
    $('#main_total_weight').val(formatDisplayZero(totalNet, 3));
    $('#main_no_of_items').val(totalCount > 0 ? totalCount : 0);

    calculateValuation();
    calculateAllowedAmount();
}

function calculateValuation() {
    var netWeight = parseFloat($('#sub_total_net_weight').val()) || 0;
    var ratePerGram = parseFloat($('#sub_amount_per_gram').val()) || 0;
    if (netWeight > 0 && ratePerGram > 0) {
        var total = netWeight * ratePerGram;
        $('#sub_total_amount').val(total.toFixed(2));
        
        // Auto-fill loan amount if blank or zero
        var currentLoan = parseFloat($('#sub_loan_amount').val()) || 0;
        if (currentLoan === 0) {
            $('#sub_loan_amount').val(total.toFixed(2));
            $('#main_amount').val(total.toFixed(2));
        }
    } else {
        if ($('#sub_amount_per_gram').val() === '' || ratePerGram === 0) {
            $('#sub_total_amount').val('0');
        }
    }
}

function calculateAllowedAmount() {
    var netWeight = parseFloat($('#sub_total_net_weight').val()) || 0;
    var maxRate = parseFloat($('#sub_max_amount_per_gram').val()) || 0;
    if (netWeight > 0 && maxRate > 0) {
        var allowed = netWeight * maxRate;
        $('#sub_allowed_amount').val(allowed.toFixed(2));
        $('#main_allowed_amount').val(allowed.toFixed(2));
    } else {
        if ($('#sub_max_amount_per_gram').val() === '' || maxRate === 0) {
            $('#sub_allowed_amount').val('0');
            $('#main_allowed_amount').val('0');
        }
    }
}

// Add row to gold items
function addGoldRow() {
    var rowCount = $('#gold-items-body tr').length + 1;
    var newRow = `
        <tr class="gold-item-row" data-row="${rowCount}">
            <td class="text-center row-slno">${rowCount}</td>
            <td>
                <input type="text" name="item_name[]" class="form-control item-name" placeholder="Item Name (e.g. Ring, Chain)" required>
            </td>
            <td>
                <input type="number" min="1" step="1" name="item_no[]" class="form-control text-right item-count" value="1" oninput="calculateItemNetWeight(this)" required>
            </td>
            <td>
                <input type="number" min="0" step="0.001" name="item_gram[]" class="form-control text-right item-gram" placeholder="0" oninput="calculateItemNetWeight(this)" required>
            </td>
            <td>
                <input type="number" min="0" step="0.001" name="item_wastage[]" class="form-control text-right item-wastage" placeholder="0" value="0" oninput="calculateItemNetWeight(this)">
            </td>
            <td>
                <input type="number" step="0.001" name="item_net_weight[]" class="form-control text-right item-net-weight" placeholder="0" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-table-action remove-row-btn" onclick="removeGoldRow(this)" title="Remove row">
                    Remove
                </button>
            </td>
        </tr>
    `;
    $('#gold-items-body').append(newRow);
    renumberRows();
}

// Remove row from gold items
function removeGoldRow(elem) {
    if ($('#gold-items-body tr').length <= 1) {
        alert("At least one gold item row is required.");
        return;
    }
    $(elem).closest('tr').remove();
    renumberRows();
    recalculateAllTotals();
}

function renumberRows() {
    $('#gold-items-body tr').each(function(index) {
        $(this).find('.row-slno').text(index + 1);
    });
}

$(document).ready(function() {
    recalculateAllTotals();
    var curRate = $('#interest_rate').val();
    if (curRate && (!$('#sub_max_amount_per_gram').val() || parseFloat($('#sub_max_amount_per_gram').val()) === 0)) {
        fetchSchemeAmount(curRate);
    }
    var currentLoanAmt = parseFloat($('#sub_loan_amount').val()) || 0;
    if (currentLoanAmt > 0) {
        updateFinancialCharges(currentLoanAmt);
    }
});
</script>
</body>
</html>
<?php if (isset($conn) && $conn instanceof mysqli) { $conn->close(); } ?>
