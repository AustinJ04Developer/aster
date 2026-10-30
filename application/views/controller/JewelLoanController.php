<?php
require_once '../settings/db.php';
require_once 'model/JewelLoanModel.php';

class JewelLoanController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new JewelLoanModel($conn);
    }

    /**
     * AJAX endpoint handler for dynamic requests (appraiser charge calculation, scheme amount)
     */
    public function handleAjax() {
        if (isset($_GET['action'])) {
            header('Content-Type: application/json');
            $action = $_GET['action'];

            if ($action === 'get_appraiser_charge') {
                $amount = (float)($_GET['amount'] ?? 0);
                $charge = $this->model->getAppraiserCharge($amount);
                echo json_encode(['status' => 'success', 'appraiser_charge' => $charge]);
                exit;
            }

            if ($action === 'get_processing_fee_percentage') {
                $percentage = $this->model->getLatestProcessingFeePercentage();
                echo json_encode(['status' => 'success', 'processing_fee_percentage' => $percentage]);
                exit;
            }

            if ($action === 'get_financial_charges') {
                $amount = (float)($_GET['amount'] ?? 0);
                $charge = $this->model->getAppraiserCharge($amount);
                $percentage = $this->model->getLatestProcessingFeePercentage();
                $fee = round(($amount * $percentage) / 100, 2);
                echo json_encode([
                    'status' => 'success',
                    'appraiser_charge' => $charge,
                    'processing_fee_percentage' => $percentage,
                    'processing_fee' => $fee
                ]);
                exit;
            }

            if ($action === 'get_rate_for_permissible_amount') {
                $amt = (float)($_GET['amount_per_gram'] ?? 0);
                if (!$this->model->isValidPermissibleAmountPerGram($amt)) {
                    echo json_encode(['status' => 'error', 'message' => 'Permissible amount per gram must be strictly ₹10,000, ₹10,500, or ₹11,000.']);
                    exit;
                }
                $rate = $this->model->getInterestRateForPermissibleAmount($amt);
                echo json_encode(['status' => 'success', 'rate' => $rate, 'amount_per_gram' => $amt]);
                exit;
            }

            if ($action === 'get_scheme_amount') {
                $rate = trim($_GET['rate'] ?? '');
                $allowedRates = ['18', '24', '30'];
                if (!in_array($rate, $allowedRates, true)) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid interest rate']);
                    exit;
                }
                $amountTo = $this->model->getSchemeByInterestRate((float)$rate);
                echo json_encode(['status' => 'success', 'amount_to' => $amountTo]);
                exit;
            }

            if ($action === 'get_customer') {
                $dNum = trim($_GET['dnumber'] ?? '');
                $customer = $this->model->getCustomerByDNumber($dNum);
                echo json_encode(['status' => 'success', 'customer' => $customer]);
                exit;
            }

            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            exit;
        }
    }

    /**
     * Upload file helper following Aster Bank convention
     */
    private function uploadFile($fileInputKey, $folderName) {
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

    /**
     * Process main form request
     */
    public function handleRequest() {
        $this->handleAjax();

        $companyBanks = $this->model->getCompanyBanks();
        $upiTypes = $this->model->getUpiTypes();

        $nextDNumber = $this->model->previewNextDNumber();
        $initialRate = 18;
        $initialSchemeAmount = $this->model->getSchemeByInterestRate($initialRate);
        $currentProcessingFeePercentage = $this->model->getLatestProcessingFeePercentage();

        $form = [];
        $selectedMember = null;
        $memberIdParam = $_GET['id'] ?? ($_GET['member_id'] ?? ($_REQUEST['id'] ?? ''));
        if (!empty($memberIdParam)) {
            $selectedMember = $this->model->getMemberById($memberIdParam);
            if ($selectedMember) {
                $form['name'] = $selectedMember['FullName'] ?? '';
                $form['fathers_name'] = $selectedMember['SDWName'] ?? '';
                $form['job'] = $selectedMember['Occupation'] ?? '';
                $form['dob'] = !empty($selectedMember['DateOfBirth']) && $selectedMember['DateOfBirth'] !== '0000-00-00' ? $selectedMember['DateOfBirth'] : '';
                $form['address'] = $selectedMember['PermenantAddress'] ?? ($selectedMember['PresentAddress'] ?? '');
                $form['mobile_number'] = $selectedMember['MobileNo'] ?? '';
                $form['aadhaar_number'] = $selectedMember['Aadhaar'] ?? '';
                $form['pincode'] = !empty($selectedMember['PinCode']) ? (string)$selectedMember['PinCode'] : '';
                $form['nominee_name'] = $selectedMember['NomineeName'] ?? '';
                $form['nominee_relation'] = $selectedMember['NomineeRelation'] ?? '';
                $form['nominee_address'] = $selectedMember['NomineeAddress'] ?? '';
            }
        }

        $dNumParam = trim($_GET['dnum'] ?? ($_REQUEST['dnum'] ?? ''));
        if (!empty($dNumParam) && empty($selectedMember)) {
            $existingCustomer = $this->model->getCustomerByDNumber($dNumParam);
            if ($existingCustomer) {
                $form['name'] = $existingCustomer['Name'] ?? '';
                $form['fathers_name'] = $existingCustomer['FathersName'] ?? '';
                $form['job'] = $existingCustomer['Job'] ?? '';
                $form['dob'] = !empty($existingCustomer['DateOfBirth']) && $existingCustomer['DateOfBirth'] !== '0000-00-00' ? $existingCustomer['DateOfBirth'] : '';
                $form['address'] = $existingCustomer['Address'] ?? '';
                $form['mobile_number'] = $existingCustomer['MobileNumber'] ?? '';
                $form['aadhaar_number'] = $existingCustomer['AadhaarNumber'] ?? '';
                $form['pincode'] = $existingCustomer['Pincode'] ?? '';
                $form['nominee_name'] = $existingCustomer['NomineeName'] ?? '';
                $form['nominee_relation'] = $existingCustomer['NomineeRelation'] ?? '';
                $form['nominee_address'] = $existingCustomer['NomineeAddress'] ?? '';
            }
        }

        $data = [
            'dNumber' => $nextDNumber,
            'loanDate' => date('Y-m-d'),
            'interestRate' => $initialRate,
            'initialSchemeAmount' => $initialSchemeAmount,
            'processingFeePercentage' => $currentProcessingFeePercentage,
            'companyBanks' => $companyBanks,
            'upiTypes' => $upiTypes,
            'success' => '',
            'errors' => [],
            'savedDNumber' => '',
            'form' => $form
        ];

        if (isset($_POST['jewel-loan-submit'])) {
            $dNumber = trim($_POST['d_number'] ?? '');
            if (empty($dNumber) || $this->model->isDNumberTakenAnywhere($dNumber)) {
                $dNumber = $this->model->getNextDNumber();
            }

            $name = trim($_POST['name'] ?? '');
            $fathersName = trim($_POST['fathers_name'] ?? '');
            $job = trim($_POST['job'] ?? '');
            $dob = !empty($_POST['dob']) ? trim($_POST['dob']) : null;
            $address = trim($_POST['address'] ?? '');
            $mobileNumber = trim($_POST['mobile_number'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $nomineeName = trim($_POST['nominee_name'] ?? '');
            $nomineeRelation = trim($_POST['nominee_relation'] ?? '');
            $nomineeAddress = trim($_POST['nominee_address'] ?? '');
            $ifNomineeIsMinor = trim($_POST['if_nominee_minor'] ?? 'No');
            $guardianName = trim($_POST['guardian_name'] ?? '');
            $loanDate = !empty($_POST['loan_date']) ? trim($_POST['loan_date']) : date('Y-m-d');
            $maxAmountPerGram = (float)($_POST['max_amount_per_gram'] ?? 0);
            $allowedAmount = (float)($_POST['allowed_amount'] ?? 0);
            $packetNumber = trim($_POST['packet_number'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $reasonForLoan = trim($_POST['reason_for_loan'] ?? '');
            $loanPeriod = trim($_POST['loan_period'] ?? '');
            $aadhaarNumber = trim($_POST['aadhaar_number'] ?? '');
            $totalWeight = (float)($_POST['total_weight'] ?? 0);
            $goldQuality = trim($_POST['gold_quality'] ?? '');
            $noOfItems = (int)($_POST['no_of_items'] ?? 0);

            // Repeatable loan subtable fields
            $interestRateRaw = trim($_POST['interest_rate'] ?? '18');
            $allowedInterestRates = ['18', '24', '30'];
            if (!in_array($interestRateRaw, $allowedInterestRates, true)) {
                $interestRateRaw = '18';
            }
            $interestRate = (float)$interestRateRaw;

            $totalNetWeight = (float)($_POST['sub_total_net_weight'] ?? 0);
            $amountPerGram = (float)($_POST['sub_amount_per_gram'] ?? 0);
            $totalAmount = (float)($_POST['sub_total_amount'] ?? 0);
            // Server-side: fetch permissible per gram from master scheme (do not trust client value)
            $subMaxAmountPerGram = $this->model->getSchemeByInterestRate($interestRate);
            if ($subMaxAmountPerGram <= 0) {
                // fallback: use client value if no scheme is configured
                $subMaxAmountPerGram = (float)($_POST['sub_max_amount_per_gram'] ?? $maxAmountPerGram);
            }
            $subAllowedAmount = (float)($_POST['sub_allowed_amount'] ?? $allowedAmount);
            $loanAmount = (float)($_POST['sub_loan_amount'] ?? $amount);
            $balanceLoanAmount = isset($_POST['sub_balance_loan_amount']) && $_POST['sub_balance_loan_amount'] !== '' 
                ? (float)$_POST['sub_balance_loan_amount'] 
                : $loanAmount;

            // Server-side verification for Appraiser Charge (do not trust unverified client value)
            $verifiedAppraiserCharge = $this->model->getAppraiserCharge($loanAmount);
            $clientAppraiserCharge = (float)($_POST['sub_appraiser_charge'] ?? 0);
            $appraiserCharge = $verifiedAppraiserCharge;

            // Server-side verification for Processing Fee Percentage & Calculation
            $masterFeePercentage = $this->model->getLatestProcessingFeePercentage();
            $clientFeePercentage = isset($_POST['sub_processing_fee_percentage']) ? (float)$_POST['sub_processing_fee_percentage'] : $masterFeePercentage;
            $processingFeePercentage = $masterFeePercentage; // Enforce master percentage for new applications
            $processingFee = round(($loanAmount * $processingFeePercentage) / 100, 2);

            $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
            $paymentDate = !empty($_POST['payment_date']) ? trim($_POST['payment_date']) : $loanDate;

            // Payment method specific field mapping and validation
            $paymentAmount = (float)($_POST['payment_amount'] ?? $loanAmount);
            $chequeNumber = '';
            $chequeFromBank = '';
            $chequeToBank = '';
            $rtgsNumber = '';
            $rtgsFromBank = '';
            $rtgsToBank = '';
            $upiType = '';
            $upiTxn = '';
            $upiToBank = '';

            if ($paymentMethod === 'Cheque') {
                $paymentDate = !empty($_POST['cheque_date']) ? trim($_POST['cheque_date']) : $loanDate;
                $chequeNumber = trim($_POST['cheque_number'] ?? '');
                $chequeFromBank = trim($_POST['cheque_from_bank'] ?? '');
                $chequeToBank = trim($_POST['cheque_to_bank'] ?? '');
                if (isset($_POST['cheque_payment_amount']) && $_POST['cheque_payment_amount'] !== '') {
                    $paymentAmount = (float)$_POST['cheque_payment_amount'];
                }
            } elseif ($paymentMethod === 'RTGS') {
                $paymentDate = !empty($_POST['rtgs_date']) ? trim($_POST['rtgs_date']) : $loanDate;
                $rtgsFromBank = trim($_POST['rtgs_details'] ?? ($_POST['rtgs_from_bank'] ?? ''));
                $rtgsNumber = trim($_POST['rtgs_reference_no'] ?? ($_POST['rtgs_number'] ?? ''));
                if (isset($_POST['rtgs_payment_amount']) && $_POST['rtgs_payment_amount'] !== '') {
                    $paymentAmount = (float)$_POST['rtgs_payment_amount'];
                }
            } elseif ($paymentMethod === 'UPI') {
                $paymentDate = !empty($_POST['upi_date']) ? trim($_POST['upi_date']) : (!empty($_POST['payment_date']) ? trim($_POST['payment_date']) : $loanDate);
                $upiType = trim($_POST['upi_type'] ?? '');
                $upiTxn = trim($_POST['upi_id'] ?? ($_POST['upi_transaction_number'] ?? ''));
                $upiToBank = trim($_POST['upi_to_bank'] ?? '');
                if (isset($_POST['upi_payment_amount']) && $_POST['upi_payment_amount'] !== '') {
                    $paymentAmount = (float)$_POST['upi_payment_amount'];
                }
            }

            $userId = isset($_SESSION['UserId']) && !empty($_SESSION['UserId']) ? (int)$_SESSION['UserId'] : 1;

            // Basic validation
            if (empty($name)) {
                $data['errors'][] = "Customer Name is required.";
            }
            if (empty($mobileNumber)) {
                $data['errors'][] = "Mobile Number is required.";
            }
            if (empty($address)) {
                $data['errors'][] = "Address is required.";
            }
            if ($loanAmount <= 0) {
                $data['errors'][] = "Valid Loan Amount is required.";
            }

            // File uploads
            $uploadSign = $this->uploadFile('upload_sign', 'signatures');
            $uploadAadhaar = $this->uploadFile('upload_aadhaar', 'aadhaar');

            // Parse Gold Items
            $itemsData = [];
            $itemNames = $_POST['item_name'] ?? [];
            $itemCounts = $_POST['item_no'] ?? [];
            $itemGrams = $_POST['item_gram'] ?? [];
            $itemWastages = $_POST['item_wastage'] ?? [];
            $itemNetWeights = $_POST['item_net_weight'] ?? [];

            if (is_array($itemNames)) {
                for ($i = 0; $i < count($itemNames); $i++) {
                    $iName = trim($itemNames[$i] ?? '');
                    if ($iName === '') continue;
                    $itemsData[] = [
                        'SlNo' => $i + 1,
                        'ItemName' => $iName,
                        'NoOfItems' => (int)($itemCounts[$i] ?? 1),
                        'Gram' => (float)($itemGrams[$i] ?? 0),
                        'Wastage' => (float)($itemWastages[$i] ?? 0),
                        'NetWeight' => (float)($itemNetWeights[$i] ?? 0)
                    ];
                }
            }

            if (empty($data['errors'])) {
                $customerData = [
                    'DNumber' => $dNumber,
                    'Name' => $name,
                    'FathersName' => $fathersName,
                    'Job' => $job,
                    'DateOfBirth' => $dob,
                    'Address' => $address,
                    'MobileNumber' => $mobileNumber,
                    'Pincode' => $pincode,
                    'NomineeName' => $nomineeName,
                    'NomineeRelation' => $nomineeRelation,
                    'NomineeAddress' => $nomineeAddress,
                    'IfNomineeIsMinor' => $ifNomineeIsMinor,
                    'GuardianName' => $guardianName,
                    'LoanDate' => $loanDate,
                    'MaxAmountPerGram' => $maxAmountPerGram,
                    'AllowedAmount' => $allowedAmount,
                    'PacketNumber' => $packetNumber,
                    'Amount' => $amount > 0 ? $amount : $loanAmount,
                    'ReasonForLoan' => $reasonForLoan,
                    'LoanPeriod' => $loanPeriod,
                    'AadhaarNumber' => $aadhaarNumber,
                    'TotalWeight' => $totalWeight > 0 ? $totalWeight : $totalNetWeight,
                    'GoldQuality' => $goldQuality,
                    'NoOfItems' => $noOfItems > 0 ? $noOfItems : count($itemsData),
                    'UploadSign' => $uploadSign,
                    'UploadAadhaar' => $uploadAadhaar,
                    'CreatedBy' => $userId
                ];

                $loanData = [
                    'LoanDate' => $loanDate,
                    'InterestRate' => $interestRate,
                    'TotalNetWeight' => $totalNetWeight,
                    'AmountPerGram' => $amountPerGram,
                    'TotalAmount' => $totalAmount,
                    'MaxAmountPerGram' => $subMaxAmountPerGram,
                    'AllowedAmount' => $subAllowedAmount,
                    'LoanAmount' => $loanAmount,
                    'BalanceLoanAmount' => $balanceLoanAmount,
                    'AppraiserCharge' => $appraiserCharge,
                    'ProcessingFeePercentage' => $processingFeePercentage,
                    'ProcessingFee' => $processingFee,
                    'PaymentMethod' => $paymentMethod,
                    'PaymentDate' => $paymentDate,
                    'PaymentAmount' => $paymentAmount,
                    'ChequeNumber' => $chequeNumber,
                    'ChequeFromBank' => $chequeFromBank,
                    'ChequeToBank' => $chequeToBank,
                    'RtgsNumber' => $rtgsNumber,
                    'RtgsFromBank' => $rtgsFromBank,
                    'RtgsToBank' => $rtgsToBank,
                    'UpiType' => $upiType,
                    'UpiTransactionNumber' => $upiTxn,
                    'UpiToBank' => $upiToBank
                ];

                $saveResult = $this->model->saveJewelLoan($customerData, $loanData, $itemsData);

                if ($saveResult['status']) {
                    $dNumSafe = htmlspecialchars($saveResult['dNumber']);
                    $loanId = (int)$saveResult['loanDetailId'];
                    $pdfUrl = "aster-jewel-loan-pdf.php?dnum=" . urlencode($saveResult['dNumber']);
                    $viewUrl = "aster-jewel-loan-view.php?dnum=" . urlencode($saveResult['dNumber']);
                    $receiptUrl = "aster-jewel-loan-receipt.php?dnum=" . urlencode($saveResult['dNumber']) . "#tab-create";

                    $data['success'] = "Jewel Loan submitted successfully! Assigned D-Number: <strong>" . $dNumSafe . "</strong> &nbsp;&nbsp; "
                        . "<a href='" . $viewUrl . "' class='btn btn-warning btn-xs' style='margin-left: 10px;'><i class='fa fa-eye'></i> View Details</a> "
                        . "<a href='" . $pdfUrl . "' target='_blank' class='btn btn-info btn-xs' style='margin-left: 5px;'><i class='fa fa-file-pdf-o'></i> View Loan Sanction PDF</a> "
                        . "<a href='" . $receiptUrl . "' class='btn btn-success btn-xs' style='margin-left: 5px;'><i class='fa fa-ticket'></i> View Receipt</a>";
                    $data['savedDNumber'] = $saveResult['dNumber'];
                    $data['savedLoanDetailId'] = $saveResult['loanDetailId'] ?? 0;

                    $data['processStatus'] = [
                        'type' => 'new_account',
                        'title' => 'New Account Created & Saved!',
                        'message' => 'The new Jewel Loan Account details have been successfully saved and committed to the database.',
                        'dNumber' => $saveResult['dNumber'],
                        'customerName' => $name,
                        'loanAmount' => $loanAmount,
                        'totalWeight' => $totalWeight > 0 ? $totalWeight : $totalNetWeight,
                        'loanDetailId' => $saveResult['loanDetailId'] ?? 0,
                        'viewUrl' => $viewUrl,
                        'pdfUrl' => $pdfUrl,
                        'receiptUrl' => $receiptUrl,
                        'newUrl' => 'aster-jewel-loan.php',
                        'timestamp' => date('d/m/Y h:i A')
                    ];
                    
                    // Preview new D-Number for next form
                    $data['dNumber'] = $this->model->previewNextDNumber();
                } else {
                    $data['errors'][] = "Database submission error: " . $saveResult['error'];
                    $data['form'] = $_POST;
                }
            } else {
                $data['form'] = $_POST;
            }
        }

        return $data;
    }
}
?>
