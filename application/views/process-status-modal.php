<?php
/**
 * Shared Process Status Modal & In-page Visual Confirmation
 * Displays an animated big tick mark inside a green circle and full database process status
 * whenever an account is created, existing details are edited, or receipts are saved.
 */

// Normalize $processStatus from $data or direct variable if set
if (!isset($processStatus) && isset($data['processStatus'])) {
    $processStatus = $data['processStatus'];
}
if (empty($processStatus) && !empty($success)) {
    $processStatus = [
        'type' => 'general',
        'title' => 'Saved Successfully in Database!',
        'message' => strip_tags($success),
        'dNumber' => $savedDNumber ?? ($dNumParam ?? ($displayDNum ?? ($dNumber ?? ''))),
        'customerName' => $savedCustomerName ?? ($displayName ?? ($custName ?? '')),
        'timestamp' => date('d/m/Y h:i A')
    ];
}
?>

<!-- STYLES FOR PROCESS STATUS & ANIMATED BIG GREEN CIRCLE TICK MARK -->
<style>
/* Container & Circle */
.ps-circle-container {
    width: 96px;
    height: 96px;
    margin: 10px auto 20px auto;
    position: relative;
}

/* SVG Animated Checkmark */
.ps-checkmark-svg {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    display: block;
    stroke-width: 3.5;
    stroke: #28a745;
    stroke-miterlimit: 10;
    box-shadow: 0 8px 24px rgba(40, 167, 69, 0.25);
    animation: ps-fill 0.4s ease-in-out 0.4s forwards, ps-scale 0.3s ease-in-out 0.85s both;
}

.ps-circle-bg {
    fill: #28a745;
    opacity: 0;
    animation: ps-fade-bg 0.3s ease-in 0.35s forwards;
}

.ps-circle-outline {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 3;
    stroke-miterlimit: 10;
    stroke: #28a745;
    fill: none;
    animation: ps-stroke-circle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.ps-checkmark-path {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke: #ffffff;
    stroke-width: 4.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
    animation: ps-stroke-check 0.4s cubic-bezier(0.65, 0, 0.45, 1) 0.5s forwards;
}

@keyframes ps-stroke-circle {
    100% { stroke-dashoffset: 0; }
}

@keyframes ps-stroke-check {
    100% { stroke-dashoffset: 0; }
}

@keyframes ps-fade-bg {
    100% { opacity: 1; }
}

@keyframes ps-fill {
    100% { box-shadow: 0 10px 28px rgba(40, 167, 69, 0.35); }
}

@keyframes ps-scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
}

/* Modal Custom Styling */
#processStatusModal .modal-dialog {
    margin-top: 60px;
    max-width: 560px;
}

#processStatusModal .modal-content {
    border-radius: 14px;
    border: none;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.28);
    overflow: hidden;
    background: #ffffff;
}

#processStatusModal .modal-header {
    border-bottom: none;
    padding: 16px 20px 0 20px;
    background: #ffffff;
}

#processStatusModal .modal-header .close {
    font-size: 26px;
    color: #999;
    opacity: 0.7;
    transition: opacity 0.2s;
}

#processStatusModal .modal-header .close:hover {
    opacity: 1;
}

#processStatusModal .modal-body {
    padding: 0 32px 28px 32px;
    text-align: center;
}

.ps-title {
    font-size: 22px;
    font-weight: 700;
    color: #204d74;
    margin-top: 5px;
    margin-bottom: 6px;
    letter-spacing: -0.3px;
}

.ps-subtitle {
    font-size: 13.5px;
    color: #555555;
    margin-bottom: 18px;
    line-height: 1.4;
}

/* Process Status Badge */
.ps-badge {
    display: inline-block;
    background-color: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #c8e6c9;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 16px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 18px;
}

.ps-badge i {
    margin-right: 5px;
    color: #28a745;
}

/* Details Summary Table */
.ps-table-summary {
    width: 100%;
    margin-bottom: 20px;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    text-align: left;
    font-size: 13px;
}

.ps-table-summary tr:not(:last-child) td {
    border-bottom: 1px solid #eef2f6;
}

.ps-table-summary td {
    padding: 8px 14px;
    vertical-align: middle;
}

.ps-table-summary td.ps-lbl {
    color: #64748b;
    font-weight: 600;
    width: 38%;
}

.ps-table-summary td.ps-val {
    color: #1e293b;
    font-weight: bold;
}

.ps-dnumber-tag {
    display: inline-block;
    background: #1a4d79;
    color: #ffffff;
    font-weight: bold;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 13px;
    letter-spacing: 0.5px;
}

/* In-Page Success Card (Permanent visual state) */
.ps-inpage-card {
    background: #ffffff;
    border-left: 5px solid #28a745;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.12);
    padding: 18px 24px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.ps-inpage-circle {
    flex-shrink: 0;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #28a745;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.ps-inpage-circle i {
    color: #ffffff;
    font-size: 28px;
}

.ps-inpage-body {
    flex-grow: 1;
}

.ps-inpage-title {
    font-size: 16px;
    font-weight: bold;
    color: #1b5e20;
    margin-bottom: 3px;
}

.ps-inpage-meta {
    font-size: 13px;
    color: #475569;
}

/* Screen Overlay for saving progress indicator */
#processSavingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: #ffffff;
}

.ps-saving-box {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px 40px;
    text-align: center;
    box-shadow: 0 20px 45px rgba(0,0,0,0.35);
    color: #1e293b;
    max-width: 420px;
}

.ps-saving-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid #e2e8f0;
    border-top: 5px solid #28a745;
    border-radius: 50%;
    margin: 0 auto 18px auto;
    animation: ps-spin 0.8s linear infinite;
}

@keyframes ps-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- FULL SCREEN SAVING PROCESS OVERLAY -->
<div id="processSavingOverlay">
    <div class="ps-saving-box">
        <div class="ps-saving-spinner"></div>
        <h4 style="margin: 0 0 8px 0; font-weight: bold; color: #204d74;">Saving to Database...</h4>
        <p style="margin: 0; font-size: 13px; color: #64748b;">
            Writing records and updating database. Please wait...
        </p>
    </div>
</div>

<?php if (!empty($processStatus)): ?>
    <?php
    $type         = $processStatus['type'] ?? 'general';
    $title        = $processStatus['title'] ?? 'Saved Successfully in Database!';
    $message      = $processStatus['message'] ?? 'Record has been committed to the database.';
    $dNum         = $processStatus['dNumber'] ?? '';
    $custName     = $processStatus['customerName'] ?? '';
    $loanAmount   = isset($processStatus['loanAmount']) && (float)$processStatus['loanAmount'] > 0 ? (float)$processStatus['loanAmount'] : 0;
    $totalPaid    = isset($processStatus['totalPaid']) && (float)$processStatus['totalPaid'] > 0 ? (float)$processStatus['totalPaid'] : 0;
    $totalWeight  = isset($processStatus['totalWeight']) && (float)$processStatus['totalWeight'] > 0 ? (float)$processStatus['totalWeight'] : 0;
    $receiptNo    = $processStatus['receiptNo'] ?? '';
    $viewUrl      = $processStatus['viewUrl'] ?? '';
    $pdfUrl       = $processStatus['pdfUrl'] ?? '';
    $receiptUrl   = $processStatus['receiptUrl'] ?? '';
    $newUrl       = $processStatus['newUrl'] ?? 'aster-jewel-loan.php';
    $timestamp    = $processStatus['timestamp'] ?? date('d/m/Y h:i A');

    $processLabel = ($type === 'new_account') ? 'New Account Registration' : (($type === 'edit_account') ? 'Account Details Update' : 'Payment Receipt Voucher');
    ?>

    <!-- 1. IN-PAGE PROCESS STATUS CONFIRMATION BANNER (Permanent & responsive) -->
    <div class="ps-inpage-card no-print">
        <div class="ps-inpage-circle">
            <i class="fa fa-check"></i>
        </div>
        <div class="ps-inpage-body">
            <div class="ps-inpage-title">
                <?php echo htmlspecialchars($title); ?> &bull; <span class="label label-success" style="font-size: 11px; vertical-align: middle;">Status: Stored in Database</span>
            </div>
            <div class="ps-inpage-meta">
                <strong>Process:</strong> <?php echo htmlspecialchars($processLabel); ?>
                <?php if (!empty($dNum)): ?>
                    &nbsp;|&nbsp; <strong>D-Number:</strong> <span class="ps-dnumber-tag"><?php echo htmlspecialchars($dNum); ?></span>
                <?php endif; ?>
                <?php if (!empty($custName)): ?>
                    &nbsp;|&nbsp; <strong>Customer:</strong> <?php echo htmlspecialchars($custName); ?>
                <?php endif; ?>
                <?php if ($loanAmount > 0): ?>
                    &nbsp;|&nbsp; <strong>Amount:</strong> ₹<?php echo number_format($loanAmount, 2); ?>
                <?php elseif ($totalPaid > 0): ?>
                    &nbsp;|&nbsp; <strong>Paid:</strong> ₹<?php echo number_format($totalPaid, 2); ?>
                <?php endif; ?>
                &nbsp;|&nbsp; <span class="text-muted"><i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($timestamp); ?></span>
            </div>
        </div>
        <div class="ps-inpage-actions">
            <?php if (!empty($viewUrl)): ?>
                <a href="<?php echo $viewUrl; ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-eye"></i> View Details
                </a>
            <?php endif; ?>
            <?php if (!empty($pdfUrl)): ?>
                <a href="<?php echo $pdfUrl; ?>" target="_blank" class="btn btn-sm btn-info" style="margin-left: 5px;">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. HIGH-IMPACT PROCESS STATUS MODAL WITH ANIMATED BIG TICK MARK IN GREEN CIRCLE -->
    <div class="modal fade process-status-modal" id="processStatusModal" tabindex="-1" role="dialog" aria-labelledby="processStatusModalLabel" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- BIG TICK MARK INSIDE GREEN CIRCLE -->
                    <div class="ps-circle-container">
                        <svg class="ps-checkmark-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="ps-circle-bg" cx="26" cy="26" r="25"/>
                            <circle class="ps-circle-outline" cx="26" cy="26" r="24"/>
                            <path class="ps-checkmark-path" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>

                    <!-- TITLE & PROCESS STATUS -->
                    <h3 class="ps-title" id="processStatusModalLabel"><?php echo htmlspecialchars($title); ?></h3>
                    <p class="ps-subtitle"><?php echo htmlspecialchars($message); ?></p>

                    <!-- PROCESS STATUS BADGE -->
                    <div>
                        <span class="ps-badge">
                            <i class="fa fa-database"></i> Status: Process Completed &bull; Stored in Database
                        </span>
                    </div>

                    <!-- PROCESS STATUS SUMMARY DETAILS -->
                    <table class="ps-table-summary">
                        <tr>
                            <td class="ps-lbl">Operation Process:</td>
                            <td class="ps-val"><?php echo htmlspecialchars($processLabel); ?></td>
                        </tr>
                        <?php if (!empty($dNum)): ?>
                            <tr>
                                <td class="ps-lbl">D-Number (Unique):</td>
                                <td class="ps-val"><span class="ps-dnumber-tag"><?php echo htmlspecialchars($dNum); ?></span></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($receiptNo)): ?>
                            <tr>
                                <td class="ps-lbl">Receipt Number:</td>
                                <td class="ps-val"><?php echo htmlspecialchars($receiptNo); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($custName)): ?>
                            <tr>
                                <td class="ps-lbl">Customer Name:</td>
                                <td class="ps-val"><?php echo htmlspecialchars($custName); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($loanAmount > 0): ?>
                            <tr>
                                <td class="ps-lbl">Sanction Loan Amount:</td>
                                <td class="ps-val" style="color: #2e7d32;">₹<?php echo number_format($loanAmount, 2); ?></td>
                            </tr>
                        <?php elseif ($totalPaid > 0): ?>
                            <tr>
                                <td class="ps-lbl">Amount Received:</td>
                                <td class="ps-val" style="color: #2e7d32;">₹<?php echo number_format($totalPaid, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($totalWeight > 0): ?>
                            <tr>
                                <td class="ps-lbl">Total Net Weight:</td>
                                <td class="ps-val"><?php echo number_format($totalWeight, 3); ?> Grams</td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="ps-lbl">Database Status:</td>
                            <td class="ps-val" style="color: #2e7d32;">
                                <i class="fa fa-check-circle"></i> Committed &amp; Saved Successfully
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-lbl">Process Timestamp:</td>
                            <td class="ps-val" style="color: #64748b; font-weight: normal; font-size: 12px;">
                                <i class="fa fa-calendar"></i> <?php echo htmlspecialchars($timestamp); ?>
                            </td>
                        </tr>
                    </table>

                    <!-- ACTION BUTTONS -->
                    <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                        <?php if (!empty($viewUrl)): ?>
                            <a href="<?php echo $viewUrl; ?>" class="btn btn-primary" style="font-weight: 600; padding: 7px 18px;">
                                <i class="fa fa-eye"></i> View Details
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($pdfUrl)): ?>
                            <a href="<?php echo $pdfUrl; ?>" target="_blank" class="btn btn-info" style="font-weight: 600; padding: 7px 18px;">
                                <i class="fa fa-file-pdf-o"></i> View / Print PDF
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($receiptUrl)): ?>
                            <a href="<?php echo $receiptUrl; ?>" class="btn btn-success" style="font-weight: 600; padding: 7px 18px;">
                                <i class="fa fa-ticket"></i> Issue Receipt
                            </a>
                        <?php endif; ?>

                        <?php if ($type === 'new_account'): ?>
                            <a href="<?php echo $newUrl; ?>" class="btn btn-default" style="font-weight: 600; padding: 7px 18px;">
                                <i class="fa fa-plus"></i> New Account
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 600; padding: 7px 18px;">
                                <i class="fa fa-check"></i> OK
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JAVASCRIPT FOR PROCESS STATUS AND SUBMIT BUTTON PROGRESS STATE -->
<script>
$(document).ready(function() {
    // 1. Auto launch Process Status Modal if status data is present
    <?php if (!empty($processStatus)): ?>
    if ($('#processStatusModal').length) {
        setTimeout(function() {
            $('#processStatusModal').modal('show');
        }, 250);
    }
    <?php endif; ?>

    // 2. Intercept form submissions to display saving process status
    $('form').on('submit', function(e) {
        var $form = $(this);

        // Check if this is a save/submit form (jewel loan creation, edit view, or receipt)
        var isTargetForm = $form.attr('id') === 'jewel-loan-form' ||
                           $form.find('button[name="jewel-loan-submit"]').length > 0 ||
                           $form.find('button[name="update_jewel_loan"]').length > 0 ||
                           $form.find('button[name="submit_receipt"]').length > 0;

        if (isTargetForm) {
            // Find active submit button
            var $submitBtn = $form.find('button[type="submit"]:focus, #save-jewel-loan, button[name="update_jewel_loan"], #btn-save-receipt').first();

            // Append hidden field to guarantee button name/value is posted even when disabled
            if ($submitBtn.length && $submitBtn.attr('name')) {
                if ($form.find('input[type="hidden"][name="' + $submitBtn.attr('name') + '"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: $submitBtn.attr('name'),
                        value: $submitBtn.val() || '1'
                    }).appendTo($form);
                }
            }

            // Show full screen saving progress overlay
            $('#processSavingOverlay').css('display', 'flex').hide().fadeIn(150);

            // Change button state
            if ($submitBtn.length) {
                $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving to Database...');
            }
        }
    });
});
</script>
