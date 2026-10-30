<?php
/**
 * Jewel Loan Receipt List Controller
 * Manages receipt reports, search criteria, and aggregate metrics
 */
require_once __DIR__ . '/../../settings/db.php';
require_once __DIR__ . '/model/JewelLoanReceiptListModel.php';

class JewelLoanReceiptListController {
    private $conn;
    private $model;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
        $this->model = new JewelLoanReceiptListModel($this->conn);
        if ($this->conn && !isset($_GET['ajax_receipt_voucher'])) {
            $this->model->ensurePageRegistered();
        }
    }

    public function handleRequest() {
        if (isset($_GET['ajax_receipt_voucher'])) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            $rId = (int)$_GET['ajax_receipt_voucher'];
            require_once __DIR__ . '/model/JewelLoanModel.php';
            $jlModel = new JewelLoanModel($this->conn);
            $receiptData = $jlModel->getReceiptById($rId);
            if ($receiptData && !empty($receiptData['receipt'])) {
                $html = $this->renderVoucherHtml($receiptData);
                echo json_encode([
                    'status' => 'success',
                    'receipt_no' => $receiptData['receipt']['ReceiptNo'],
                    'receipt_id' => $receiptData['receipt']['ReceiptId'],
                    'html' => $html
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => "Receipt #{$rId} not found."]);
            }
            exit;
        }

        $filters = [
            'query' => trim($_GET['query'] ?? ($_POST['query'] ?? '')),
            'receipt_no' => trim($_GET['receipt_no'] ?? ($_POST['receipt_no'] ?? '')),
            'd_number' => trim($_GET['d_number'] ?? ($_POST['d_number'] ?? '')),
            'transaction_type' => trim($_GET['transaction_type'] ?? ($_POST['transaction_type'] ?? '')),
            'payment_method' => trim($_GET['payment_method'] ?? ($_POST['payment_method'] ?? '')),
            'date_from' => trim($_GET['date_from'] ?? ($_POST['date_from'] ?? '')),
            'date_to' => trim($_GET['date_to'] ?? ($_POST['date_to'] ?? ''))
        ];

        $receipts = $this->model->getReceipts($filters);
        $summary = $this->model->getSummaryStats($filters);

        return [
            'filters' => $filters,
            'receipts' => $receipts,
            'summary' => $summary,
            'todayDate' => date('Y-m-d')
        ];
    }

    public function renderVoucherHtml($receiptView) {
        if (empty($receiptView) || empty($receiptView['receipt'])) {
            return '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Receipt details could not be loaded.</div>';
        }

        $r = $receiptView['receipt'];
        $l = $receiptView['loan'] ?? [];
        $c = $receiptView['customer'] ?? [];
        $itms = $receiptView['items'] ?? [];

        $rNo = htmlspecialchars($r['ReceiptNo']);
        $rDate = !empty($r['ReceiptDate']) ? date('d-m-Y', strtotime($r['ReceiptDate'])) : date('d-m-Y');
        $dNum = htmlspecialchars($r['DNumber']);
        $custName = htmlspecialchars($c['Name'] ?? 'Customer');
        $mobile = htmlspecialchars($c['MobileNumber'] ?? '-');
        $address = htmlspecialchars($c['Address'] ?? '-');
        $loanDateStr = !empty($l['LoanDate']) ? date('d-m-Y', strtotime($l['LoanDate'])) : '-';

        $principalPaid = (float)$r['PrincipalPaid'];
        $interestPaid = (float)$r['InterestPaid'];
        $defaultInterestPaid = (float)$r['DefaultInterestPaid'];
        $appraiserCharge = (float)$r['AppraiserCharge'];
        $processingFee = (float)$r['ProcessingFee'];
        $totalPaid = (float)$r['TotalPaid'];
        $remainingBalance = (float)$r['BalanceLoanAmount'];
        $wordsAmount = $this->getIndianCurrencyWords($totalPaid);

        ob_start();
        ?>
        <div class="voucher-card" id="printable-voucher-card">
            <div class="voucher-header">
                <div class="voucher-org-title">ASTER PUBLIC DEVELOPMENT FOUNDATION</div>
                <div class="voucher-org-sub">Jewel Loan Division &bull; Panachamoodu, Kanyakumari District</div>
                <div class="voucher-badge">
                    <?php
                        $badgeTitle = 'JEWEL LOAN RECEIPT VOUCHER';
                        if ($r['TransactionType'] === 'JEWEL_RELEASE') {
                            $badgeTitle = 'JEWEL RELEASE / CLOSURE RECEIPT';
                        } elseif ($r['TransactionType'] === 'PRINCIPAL_PAYMENT') {
                            $badgeTitle = 'JEWEL LOAN PRINCIPAL PAYMENT RECEIPT';
                        } elseif ($r['TransactionType'] === 'LOAN_CREATION') {
                            $badgeTitle = 'JEWEL LOAN CREATION RECEIPT';
                        }
                    ?>
                    <?= htmlspecialchars($badgeTitle) ?>
                </div>
            </div>

            <table class="voucher-table-meta">
                <tr>
                    <td style="width: 20%;"><strong>Receipt No:</strong></td>
                    <td style="width: 30%; color: #d9534f; font-weight: bold; font-size: 15px;"><?= $rNo ?></td>
                    <td style="width: 20%;"><strong>Receipt Date:</strong></td>
                    <td style="width: 30%; font-weight: 600;"><?= $rDate ?></td>
                </tr>
                <tr>
                    <td><strong>D Number:</strong></td>
                    <td><span class="label label-primary" style="font-size: 13px;"><?= $dNum ?></span></td>
                    <td><strong>Loan Pledge Date:</strong></td>
                    <td><?= $loanDateStr ?></td>
                </tr>
                <tr>
                    <td><strong>Customer Name:</strong></td>
                    <td style="font-weight: bold; font-size: 14px;"><?= $custName ?></td>
                    <td><strong>Mobile Number:</strong></td>
                    <td><?= $mobile ?></td>
                </tr>
                <tr>
                    <td><strong>Customer Address:</strong></td>
                    <td colspan="3"><?= $address ?></td>
                </tr>
                <tr>
                    <td><strong>Payment Mode:</strong></td>
                    <td><span class="label label-default" style="font-size: 12px;"><?= htmlspecialchars($r['PaymentMethod']) ?></span></td>
                    <td><strong>Transaction Type:</strong></td>
                    <td>
                        <span class="label label-<?= ($r['TransactionType'] === 'JEWEL_RELEASE') ? 'success' : 'info' ?>">
                            <?= htmlspecialchars($r['TransactionType']) ?>
                        </span>
                    </td>
                </tr>
                <?php if (!empty($r['ChequeNumber'])): ?>
                    <tr>
                        <td><strong>Cheque Details:</strong></td>
                        <td colspan="3">Cheque No: <?= htmlspecialchars($r['ChequeNumber']) ?> | Customer Bank: <?= htmlspecialchars($r['ChequeFromBank']) ?> | Deposited: <?= htmlspecialchars($r['ChequeToBank']) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($r['RTGSNumber'])): ?>
                    <tr>
                        <td><strong>RTGS / UTR No:</strong></td>
                        <td colspan="3"><?= htmlspecialchars($r['RTGSNumber']) ?> | Bank: <?= htmlspecialchars($r['RTGSToBank']) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($r['UPIType'])): ?>
                    <tr>
                        <td><strong>UPI Reference:</strong></td>
                        <td colspan="3">App: <?= htmlspecialchars($r['UPIType']) ?> | Txn ID: <?= htmlspecialchars($r['UPITransactionNumber']) ?> | Bank: <?= htmlspecialchars($r['UPIToBank']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <!-- Financial Breakdown -->
            <table class="voucher-table-breakdown">
                <thead>
                    <tr>
                        <th style="width: 8%;">Sl</th>
                        <th>Description</th>
                        <th class="text-right" style="width: 30%;">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>Interest Payment Paid</td>
                        <td class="text-right">&#8377; <?= number_format($interestPaid, 2) ?></td>
                    </tr>
                    <?php if ($defaultInterestPaid > 0): ?>
                        <tr>
                            <td class="text-center">2</td>
                            <td>Default / Overdue Interest Paid</td>
                            <td class="text-right">&#8377; <?= number_format($defaultInterestPaid, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($principalPaid > 0): ?>
                        <tr>
                            <td class="text-center"><?= ($defaultInterestPaid > 0) ? '3' : '2' ?></td>
                            <td>Principal Amount Paid / Reduced</td>
                            <td class="text-right">&#8377; <?= number_format($principalPaid, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($appraiserCharge > 0): ?>
                        <tr>
                            <td class="text-center">#</td>
                            <td>Appraiser Charges</td>
                            <td class="text-right">&#8377; <?= number_format($appraiserCharge, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($processingFee > 0): ?>
                        <tr>
                            <td class="text-center">#</td>
                            <td>Processing Fee</td>
                            <td class="text-right">&#8377; <?= number_format($processingFee, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr style="background-color: #f7fbfd; font-size: 15px; font-weight: bold;">
                        <td colspan="2" class="text-right" style="color: #204d74;">TOTAL AMOUNT RECEIVED:</td>
                        <td class="text-right" style="color: #d9534f;">&#8377; <?= number_format($totalPaid, 2) ?></td>
                    </tr>
                    <tr style="background-color: #fafafa;">
                        <td colspan="2" class="text-right"><strong>Remaining Principal Balance:</strong></td>
                        <td class="text-right"><strong>&#8377; <?= number_format($remainingBalance, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 15px; margin-bottom: 20px; font-size: 13px;">
                <strong>Amount in Words:</strong> <span style="font-style: italic; color: #204d74;"><?= $wordsAmount ?></span>
            </div>

            <?php if (!empty($itms)): ?>
                <div style="margin-top: 20px;">
                    <div style="font-weight: bold; font-size: 13px; color: #204d74; margin-bottom: 6px;">Pledged Ornaments Snapshot:</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed" style="font-size: 12px; margin-bottom: 0;">
                            <tr style="background: #f0f0f0;">
                                <th>Sl</th>
                                <th>Item</th>
                                <th>No of Items</th>
                                <th class="text-right">Gross (g)</th>
                                <th class="text-right">Net Wt (g)</th>
                            </tr>
                            <?php foreach ($itms as $idx => $it): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= htmlspecialchars($it['ItemName']) ?></td>
                                    <td><?= (int)$it['NoOfItems'] ?></td>
                                    <td class="text-right"><?= number_format((float)$it['Gram'], 3) ?></td>
                                    <td class="text-right"><?= number_format((float)$it['NetWeight'], 3) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row voucher-signatures">
                <div class="col-xs-4 text-center">
                    <div class="signature-col">Customer Signature</div>
                </div>
                <div class="col-xs-4 text-center">
                    <div class="signature-col">Cashier / Prepared By</div>
                </div>
                <div class="col-xs-4 text-center">
                    <div class="signature-col">Authorized Officer</div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getIndianCurrencyWords($number) {
        $number = round((float)$number, 2);
        if ($number <= 0) {
            return 'Zero Rupees Only';
        }
        $no = floor($number);
        $decimal = (int)round(($number - $no) * 100);
        $digits_length = strlen((string)$no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $numPart = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($numPart) {
                $plural = (($counter = count($str)) && $numPart > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($numPart < 21) ? $words[$numPart] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                                         : $words[floor($numPart / 10) * 10] . ' ' . $words[$numPart % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        $Rupees = implode('', array_reverse($str));
        $paise = '';
        if ($decimal > 0) {
            $paiseWords = ($decimal < 21)
                ? $words[$decimal]
                : trim($words[floor($decimal / 10) * 10] . ' ' . $words[$decimal % 10]);
            $paise = ($Rupees ? ' and ' : '') . $paiseWords . ' Paise';
        }
        return trim(($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' Only');
    }
}

