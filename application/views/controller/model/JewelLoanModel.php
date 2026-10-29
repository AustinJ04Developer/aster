<?php
class JewelLoanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Normalize D-Number into standard format (e.g. 1 -> D-0001, d-1 -> D-0001, D-0001 -> D-0001)
     */
    public function normalizeDNumber($dNumber) {
        $d = trim((string)$dNumber);
        if ($d === '') return '';
        if (is_numeric($d)) {
            return 'D-' . str_pad((string)(int)$d, 4, '0', STR_PAD_LEFT);
        }
        if (preg_match('/^[dD][\s\-_]*(\d+)$/', $d, $matches)) {
            return 'D-' . str_pad((string)(int)$matches[1], 4, '0', STR_PAD_LEFT);
        }
        return strtoupper($d);
    }

    /**
     * Check if a D Number is already in use in any table (customers, details, or members)
     */
    public function isDNumberTakenAnywhere($dNumber) {
        $normD = $this->normalizeDNumber($dNumber);
        $rawD  = trim($dNumber);
        $sql = "SELECT 1 FROM ast_jewel_loan_customer WHERE DNumber = ? OR DNumber = ?
                UNION ALL 
                SELECT 1 FROM ast_jewel_loan_details WHERE DNumber = ? OR DNumber = ?
                UNION ALL 
                SELECT 1 FROM ast_members WHERE MemberCode = ? OR MemberCode = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssss", $normD, $rawD, $normD, $rawD, $normD, $rawD);
            $stmt->execute();
            $res = $stmt->get_result();
            $taken = ($res && $res->num_rows > 0);
            $stmt->close();
            return $taken;
        }
        return false;
    }

    /**
     * Check if a D Number already has an existing record in ast_jewel_loan_customer
     */
    public function isDNumberExists($dNumber) {
        $normD = $this->normalizeDNumber($dNumber);
        $rawD  = trim($dNumber);
        $sql = "SELECT CustomerId FROM ast_jewel_loan_customer WHERE DNumber = ? OR DNumber = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ss", $normD, $rawD);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = ($res && $res->num_rows > 0);
            $stmt->close();
            return $exists;
        }
        return false;
    }

    /**
     * Get the true maximum numeric sequence across sequence table, members, loan customers, and details
     */
    public function getMaxExistingDNumber() {
        $sqlMax = "SELECT GREATEST(
            COALESCE((SELECT current_val FROM ast_jewel_loan_sequence WHERE seq_name = 'd_number'), 0),
            COALESCE((SELECT MAX(CAST(SUBSTRING(DNumber, 3) AS UNSIGNED)) FROM ast_jewel_loan_customer WHERE DNumber LIKE 'D-%'), 0),
            COALESCE((SELECT MAX(CAST(SUBSTRING(DNumber, 3) AS UNSIGNED)) FROM ast_jewel_loan_details WHERE DNumber LIKE 'D-%'), 0),
            COALESCE((SELECT MAX(CAST(SUBSTRING(MemberCode, 3) AS UNSIGNED)) FROM ast_members WHERE MemberCode LIKE 'D-%'), 0)
        ) AS max_seq";
        $res = $this->conn->query($sqlMax);
        if ($res && $row = $res->fetch_assoc()) {
            return (int)$row['max_seq'];
        }
        return 0;
    }

    /**
     * Atomically get and increment the next unique sequential D Number (e.g. D-0015)
     */
    public function getNextDNumber() {
        $prefix = "D-";
        $this->conn->begin_transaction();
        try {
            $seqRes = $this->conn->query("SELECT current_val FROM ast_jewel_loan_sequence WHERE seq_name = 'd_number' FOR UPDATE");
            $seqVal = 0;
            if ($seqRes && $row = $seqRes->fetch_assoc()) {
                $seqVal = (int)$row['current_val'];
            }

            $maxExisting = $this->getMaxExistingDNumber();
            $baseVal = max($seqVal, $maxExisting);

            $candidateVal = $baseVal + 1;
            $candidateD = $prefix . str_pad((string)$candidateVal, 4, '0', STR_PAD_LEFT);

            while ($this->isDNumberTakenAnywhere($candidateD)) {
                $candidateVal++;
                $candidateD = $prefix . str_pad((string)$candidateVal, 4, '0', STR_PAD_LEFT);
            }

            $stmtUp = $this->conn->prepare("UPDATE ast_jewel_loan_sequence SET current_val = ? WHERE seq_name = 'd_number'");
            if ($stmtUp) {
                $stmtUp->bind_param("i", $candidateVal);
                $stmtUp->execute();
                $stmtUp->close();
            } else {
                $this->conn->query("INSERT INTO ast_jewel_loan_sequence (seq_name, current_val) VALUES ('d_number', $candidateVal) ON DUPLICATE KEY UPDATE current_val = $candidateVal");
            }

            $this->conn->commit();
            return $candidateD;
        } catch (Exception $e) {
            $this->conn->rollback();
            $fallbackVal = $this->getMaxExistingDNumber() + 1;
            $fallbackD = $prefix . str_pad((string)$fallbackVal, 4, '0', STR_PAD_LEFT);
            while ($this->isDNumberTakenAnywhere($fallbackD)) {
                $fallbackVal++;
                $fallbackD = $prefix . str_pad((string)$fallbackVal, 4, '0', STR_PAD_LEFT);
            }
            return $fallbackD;
        }
    }

    /**
     * Preview next unique sequential D Number for new registration without incrementing sequence
     */
    public function previewNextDNumber() {
        $prefix = "D-";
        $maxExisting = $this->getMaxExistingDNumber();
        $candidateVal = $maxExisting + 1;
        $candidateD = $prefix . str_pad((string)$candidateVal, 4, '0', STR_PAD_LEFT);

        while ($this->isDNumberTakenAnywhere($candidateD)) {
            $candidateVal++;
            $candidateD = $prefix . str_pad((string)$candidateVal, 4, '0', STR_PAD_LEFT);
        }

        return $candidateD;
    }

    /**
     * Look up appraiser charge tier for a given loan amount
     * Rules from ast_gl_appraiser:
     * - Up to ₹1,00,000: ₹200 (explicitly ₹200 at exactly ₹1,00,000)
     * - Above ₹1,00,000 and up to ₹2,00,000: ₹400
     * - Above ₹2,00,000 and up to ₹3,00,000: ₹600
     * Collected only at loan creation, never again at closure.
     */
    public function getAppraiserCharge($amount) {
        $amount = (float)$amount;
        if ($amount <= 0) return 0.00;

        $sql = "SELECT AmountFrom, AmountTo, AppraiserCharge FROM ast_gl_appraiser ORDER BY AmountTo ASC";
        $res = $this->conn->query($sql);
        if ($res && $res->num_rows > 0) {
            $lastCharge = 0.00;
            while ($row = $res->fetch_assoc()) {
                $to = (float)$row['AmountTo'];
                $charge = (float)$row['AppraiserCharge'];
                $lastCharge = $charge;
                if ($amount <= $to) {
                    return $charge;
                }
            }
            return $lastCharge;
        }

        if ($amount <= 100000) return 200.00;
        if ($amount <= 200000) return 400.00;
        return 600.00;
    }

    /**
     * Get permissible loan amounts per gram strictly permitted by policy:
     * - ₹10,000 per gram -> 18%
     * - ₹10,500 per gram -> 24%
     * - ₹11,000 per gram -> 30%
     * Loads available values from ast_gl_schemes.
     */
    public function getPermissiblePerGramRates() {
        $rates = [
            10000 => 18.00,
            10500 => 24.00,
            11000 => 30.00
        ];
        $sql = "SELECT AmountTo, InterestPercentage FROM ast_gl_schemes ORDER BY AmountTo ASC";
        $res = $this->conn->query($sql);
        if ($res && $res->num_rows > 0) {
            $dbRates = [];
            while ($row = $res->fetch_assoc()) {
                $amt = (int)round((float)$row['AmountTo']);
                if (in_array($amt, [10000, 10500, 11000])) {
                    $dbRates[$amt] = (float)$row['InterestPercentage'];
                }
            }
            if (!empty($dbRates)) {
                return $dbRates;
            }
        }
        return $rates;
    }

    /**
     * Check if a given permissible amount per gram is valid (only 10000, 10500, 11000 allowed)
     */
    public function isValidPermissibleAmountPerGram($amountPerGram) {
        $amt = (int)round((float)$amountPerGram);
        return in_array($amt, [10000, 10500, 11000], true);
    }

    /**
     * Get scheme interest rate for a given permissible amount per gram
     */
    public function getInterestRateForPermissibleAmount($amountPerGram) {
        $amt = (int)round((float)$amountPerGram);
        $rates = $this->getPermissiblePerGramRates();
        if (isset($rates[$amt])) {
            return $rates[$amt];
        }
        if ($amt <= 10000) return 18.00;
        if ($amt <= 10500) return 24.00;
        return 30.00;
    }

    /**
     * Get the currently active/latest Processing Fee Percentage from ast_gl_processing_fee
     */
    public function getLatestProcessingFeePercentage() {
        $sql = "SELECT ProcessingFeePercentage FROM ast_gl_processing_fee ORDER BY PFID DESC LIMIT 1";
        $res = $this->conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return (float)$row['ProcessingFeePercentage'];
        }
        return 1.00;
    }

    /**
     * Calculate Processing Fee from principal (collected only once at jewel release)
     */
    public function getProcessingFee($principal) {
        $pct = $this->getLatestProcessingFeePercentage();
        return round(((float)$principal * $pct) / 100.0, 2);
    }

    /**
     * Check if processing fee was already paid in any receipt for this loan
     */
    public function isProcessingFeePaid($loanDetailId) {
        $ldId = (int)$loanDetailId;
        $sql = "SELECT 1 FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? AND ProcessingFee > 0 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $ldId);
            $stmt->execute();
            $res = $stmt->get_result();
            $paid = ($res && $res->num_rows > 0);
            $stmt->close();
            return $paid;
        }
        return false;
    }

    /**
     * Standard Daily Interest Calculation using 365-day convention:
     * Interest = Principal * Annual Interest Rate * Eligible Days / 365
     */
    public function calculateDailyInterest($principal, $annualRate, $days) {
        $principal = (float)$principal;
        $annualRate = (float)$annualRate;
        $days = (int)$days;
        if ($principal <= 0 || $annualRate <= 0 || $days <= 0) {
            return 0.00;
        }
        return round(($principal * ($annualRate / 100.0) * $days) / 365.0, 2);
    }

    /**
     * Standard slab progression for overdue rates:
     * 18% -> 24%
     * 24% -> 30%
     * Capped at 30% (no 36% or 42% slabs)
     */
    public function getNextSlabRate($rate) {
        $r = (float)$rate;
        if ($r <= 18.0) {
            return 24.00;
        }
        return 30.00;
    }

    /**
     * Calculate monthly due date for installment number k using original loan-date day anchor.
     * Preserves the anchor day (e.g. 31 Dec -> 31 Jan -> 28 Feb -> 31 Mar -> 30 Apr -> 31 May).
     * February does not permanently shift all future due dates to the 28th.
     */
    public function calculateMonthlyInstallmentDueDate($loanDateStr, $installmentNumber) {
        if (empty($loanDateStr) || $loanDateStr === '0000-00-00' || (int)$installmentNumber < 1) {
            return '';
        }
        $d = new DateTime($loanDateStr);
        $anchorDay = (int)$d->format('d');
        $loanYear = (int)$d->format('Y');
        $loanMonth = (int)$d->format('m');

        $totalMonths = ($loanYear * 12 + ($loanMonth - 1)) + (int)$installmentNumber;
        $targetYear = intdiv($totalMonths, 12);
        $targetMonth = ($totalMonths % 12) + 1;
        $daysInTargetMonth = cal_days_in_month(CAL_GREGORIAN, $targetMonth, $targetYear);
        $targetDay = min($anchorDay, $daysInTargetMonth);

        return sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, $targetDay);
    }

    /**
     * Get scheme AmountTo for a given interest percentage (18, 24, or 30)
     */
    public function getSchemeByInterestRate($interestRate) {
        $rate = (float)$interestRate;
        $sql = "SELECT AmountTo FROM ast_gl_schemes 
                WHERE InterestPercentage = ? 
                ORDER BY LSID DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("d", $rate);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $stmt->close();
                return (float)$row['AmountTo'];
            }
            $stmt->close();
        }
        return 0.00;
    }

    /**
     * Fetch all company bank accounts for dropdowns
     */
    public function getCompanyBanks() {
        $sql = "SELECT BankId, BankName, Balance FROM ast_company_bank_account ORDER BY BankName ASC";
        $res = $this->conn->query($sql);
        $banks = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $banks[] = $row;
            }
        }
        return $banks;
    }

    /**
     * Fetch all UPI types for dropdowns
     */
    public function getUpiTypes() {
        $sql = "SELECT UPIType, UPITypeName FROM ast_upi_type ORDER BY UPIType ASC";
        $res = $this->conn->query($sql);
        $types = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $types[] = $row;
            }
        }
        return $types;
    }

    /**
     * Get Member by MemberId from ast_members
     */
    public function getMemberById($memberId) {
        $id = (int)$memberId;
        if ($id <= 0) return null;
        $sql = "SELECT * FROM ast_members WHERE MemberId = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row;
    }

    /**
     * Get Customer details by D Number with fallback and enrichment from ast_members
     */
    public function getCustomerByDNumber($dNumber) {
        $normD = $this->normalizeDNumber($dNumber);
        $rawD  = trim($dNumber);

        $sql = "SELECT * FROM ast_jewel_loan_customer WHERE DNumber = ? OR DNumber = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $row = null;
        if ($stmt) {
            $stmt->bind_param("ss", $normD, $rawD);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }

        $memSql = "SELECT m.*, d.DistrictName FROM ast_members m 
                   LEFT JOIN ast_district d ON d.DID = m.District 
                   WHERE m.MemberCode = ? OR m.MemberCode = ? LIMIT 1";
        $mStmt = $this->conn->prepare($memSql);
        if ($mStmt) {
            $mStmt->bind_param("ss", $normD, $rawD);
            $mStmt->execute();
            $mRes = $mStmt->get_result();
            $member = $mRes ? $mRes->fetch_assoc() : null;
            $mStmt->close();

            if ($member) {
                if (!$row) {
                    $row = [
                        'CustomerId'      => $member['MemberId'],
                        'DNumber'         => $member['MemberCode'],
                        'Name'            => $member['FullName'],
                        'FathersName'     => $member['SDWName'] ?? '',
                        'Job'             => $member['Occupation'] ?? '',
                        'DateOfBirth'     => $member['DateOfBirth'] ?? '',
                        'Address'         => $member['PermenantAddress'] ?: ($member['PresentAddress'] ?? ''),
                        'MobileNumber'    => $member['MobileNo'] ?? '',
                        'Pincode'         => $member['PinCode'] ?? '',
                        'NomineeName'     => $member['NomineeName'] ?? '',
                        'NomineeRelation' => $member['NomineeRelation'] ?? '',
                        'NomineeAddress'  => $member['NomineeAddress'] ?? '',
                        'IfNomineeIsMinor' => (!empty($member['NomineeAge']) && (int)$member['NomineeAge'] < 18) ? 'Yes' : 'No',
                        'GuardianName'    => '',
                        'AadhaarNumber'   => $member['Aadhaar'] ?? '',
                        'DistrictName'    => $member['DistrictName'] ?? ''
                    ];
                } else {
                    if (empty($row['Name'])) $row['Name'] = $member['FullName'];
                    if (empty($row['FathersName'])) $row['FathersName'] = $member['SDWName'] ?? '';
                    if (empty($row['Job'])) $row['Job'] = $member['Occupation'] ?? '';
                    if (empty($row['MobileNumber'])) $row['MobileNumber'] = $member['MobileNo'] ?? '';
                    if (empty($row['Address'])) $row['Address'] = $member['PermenantAddress'] ?: ($member['PresentAddress'] ?? '');
                    if (empty($row['Pincode'])) $row['Pincode'] = $member['PinCode'] ?? '';
                    if (empty($row['AadhaarNumber'])) $row['AadhaarNumber'] = $member['Aadhaar'] ?? '';
                    if (empty($row['NomineeName'])) $row['NomineeName'] = $member['NomineeName'] ?? '';
                    if (empty($row['NomineeRelation'])) $row['NomineeRelation'] = $member['NomineeRelation'] ?? '';
                    if (empty($row['NomineeAddress'])) $row['NomineeAddress'] = $member['NomineeAddress'] ?? '';
                    if (empty($row['DistrictName'])) $row['DistrictName'] = $member['DistrictName'] ?? '';
                }
            }
        }

        return $row;
    }

    /**
     * Get all Loans under a D Number
     */
    public function getLoansByDNumber($dNumber) {
        $normD = $this->normalizeDNumber($dNumber);
        $rawD  = trim($dNumber);
        $sql = "SELECT * FROM ast_jewel_loan_details WHERE DNumber = ? OR DNumber = ? ORDER BY LoanDetailId DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ss", $normD, $rawD);
        $stmt->execute();
        $res = $stmt->get_result();
        $loans = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $loans[] = $r;
            }
        }
        $stmt->close();
        return $loans;
    }

    /**
     * Save full jewel loan transaction atomically (Customer + Loan Detail + Gold Items)
     */
    public function saveJewelLoan($customerData, $loanData, $itemsData) {
        $this->conn->begin_transaction();

        try {
            $dNumber = $customerData['DNumber'];

            // Validate permissible amount per gram
            $permissiblePerGram = (float)$loanData['MaxAmountPerGram'];
            if (!$this->isValidPermissibleAmountPerGram($permissiblePerGram)) {
                // If not explicitly one of 10000, 10500, 11000, snap to valid scheme
                if ($permissiblePerGram <= 10000) $permissiblePerGram = 10000.00;
                elseif ($permissiblePerGram <= 10500) $permissiblePerGram = 10500.00;
                else $permissiblePerGram = 11000.00;
                $loanData['MaxAmountPerGram'] = $permissiblePerGram;
            }

            // Enforce interest rate from scheme
            $expectedRate = $this->getInterestRateForPermissibleAmount($permissiblePerGram);
            if (empty($loanData['InterestRate']) || (float)$loanData['InterestRate'] <= 0) {
                $loanData['InterestRate'] = $expectedRate;
            }

            // Calculate permissible loan amount (TotalNetWeight * PermissiblePerGram)
            $totalNetWeight = (float)($loanData['TotalNetWeight'] ?? 0);
            $allowedAmount = round($totalNetWeight * $permissiblePerGram, 2);
            $loanData['AllowedAmount'] = $allowedAmount;
            $customerData['AllowedAmount'] = $allowedAmount;
            $customerData['MaxAmountPerGram'] = $permissiblePerGram;

            // Sanctioned principal check: cannot exceed permissible amount unless override explicitly set
            $loanAmount = (float)$loanData['LoanAmount'];
            if ($allowedAmount > 0 && $loanAmount > $allowedAmount) {
                // Enforce allowed amount boundary
                $loanAmount = $allowedAmount;
                $loanData['LoanAmount'] = $allowedAmount;
                $customerData['Amount'] = $allowedAmount;
            }
            if (!isset($loanData['BalanceLoanAmount']) || $loanData['BalanceLoanAmount'] === '' || $loanData['BalanceLoanAmount'] === null) {
                $loanData['BalanceLoanAmount'] = $loanAmount;
            }

            // Appraiser charge from master (collected at loan creation)
            $appraiserCharge = $this->getAppraiserCharge($loanAmount);
            $loanData['AppraiserCharge'] = $appraiserCharge;

            // Processing fee percentage and fee from master (unpaid until final release)
            $pfPct = $this->getLatestProcessingFeePercentage();
            $processingFee = round(($loanAmount * $pfPct) / 100.0, 2);
            $loanData['ProcessingFeePercentage'] = $pfPct;
            $loanData['ProcessingFee'] = $processingFee;

            // 1. Insert or update customer
            if ($this->isDNumberExists($dNumber)) {
                $sqlCust = "UPDATE ast_jewel_loan_customer SET
                    Name = ?, FathersName = ?, Job = ?, DateOfBirth = ?, Address = ?,
                    MobileNumber = ?, Pincode = ?, NomineeName = ?, NomineeRelation = ?,
                    NomineeAddress = ?, IfNomineeIsMinor = ?, GuardianName = ?, LoanDate = ?,
                    MaxAmountPerGram = ?, AllowedAmount = ?, PacketNumber = ?, Amount = ?,
                    ReasonForLoan = ?, LoanPeriod = ?, AadhaarNumber = ?, TotalWeight = ?,
                    GoldQuality = ?, NoOfItems = ?,
                    UploadSign = IF(? != '', ?, UploadSign),
                    UploadAadhaar = IF(? != '', ?, UploadAadhaar)
                    WHERE DNumber = ?";
                $stCust = $this->conn->prepare($sqlCust);
                if (!$stCust) {
                    throw new Exception("Customer update statement prepare failed: " . $this->conn->error);
                }
                $stCust->bind_param(
                    "sssssssssssssddsdsdssdssssss",
                    $customerData['Name'],
                    $customerData['FathersName'],
                    $customerData['Job'],
                    $customerData['DateOfBirth'],
                    $customerData['Address'],
                    $customerData['MobileNumber'],
                    $customerData['Pincode'],
                    $customerData['NomineeName'],
                    $customerData['NomineeRelation'],
                    $customerData['NomineeAddress'],
                    $customerData['IfNomineeIsMinor'],
                    $customerData['GuardianName'],
                    $customerData['LoanDate'],
                    $customerData['MaxAmountPerGram'],
                    $customerData['AllowedAmount'],
                    $customerData['PacketNumber'],
                    $customerData['Amount'],
                    $customerData['ReasonForLoan'],
                    $customerData['LoanPeriod'],
                    $customerData['AadhaarNumber'],
                    $customerData['TotalWeight'],
                    $customerData['GoldQuality'],
                    $customerData['NoOfItems'],
                    $customerData['UploadSign'],
                    $customerData['UploadSign'],
                    $customerData['UploadAadhaar'],
                    $customerData['UploadAadhaar'],
                    $dNumber
                );
                if (!$stCust->execute()) {
                    throw new Exception("Failed to update customer: " . $stCust->error);
                }
                $stCust->close();
            } else {
                $sqlCust = "INSERT INTO ast_jewel_loan_customer 
                    (DNumber, Name, FathersName, Job, DateOfBirth, Address, MobileNumber, Pincode,
                     NomineeName, NomineeRelation, NomineeAddress, IfNomineeIsMinor, GuardianName,
                     LoanDate, MaxAmountPerGram, AllowedAmount, PacketNumber, Amount, ReasonForLoan,
                     LoanPeriod, AadhaarNumber, TotalWeight, GoldQuality, NoOfItems,
                     UploadSign, UploadAadhaar, CreatedBy)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stCust = $this->conn->prepare($sqlCust);
                if (!$stCust) {
                    throw new Exception("Customer insert statement prepare failed: " . $this->conn->error);
                }
                $stCust->bind_param(
                    "sssssssssssssddsdsdssdsissi",
                    $customerData['DNumber'],
                    $customerData['Name'],
                    $customerData['FathersName'],
                    $customerData['Job'],
                    $customerData['DateOfBirth'],
                    $customerData['Address'],
                    $customerData['MobileNumber'],
                    $customerData['Pincode'],
                    $customerData['NomineeName'],
                    $customerData['NomineeRelation'],
                    $customerData['NomineeAddress'],
                    $customerData['IfNomineeIsMinor'],
                    $customerData['GuardianName'],
                    $customerData['LoanDate'],
                    $customerData['MaxAmountPerGram'],
                    $customerData['AllowedAmount'],
                    $customerData['PacketNumber'],
                    $customerData['Amount'],
                    $customerData['ReasonForLoan'],
                    $customerData['LoanPeriod'],
                    $customerData['AadhaarNumber'],
                    $customerData['TotalWeight'],
                    $customerData['GoldQuality'],
                    $customerData['NoOfItems'],
                    $customerData['UploadSign'],
                    $customerData['UploadAadhaar'],
                    $customerData['CreatedBy']
                );
                if (!$stCust->execute()) {
                    throw new Exception("Failed to insert customer: " . $stCust->error);
                }
                $stCust->close();
            }

            // 2. Insert repeatable loan details subtable record
            $sqlLoan = "INSERT INTO ast_jewel_loan_details
                (DNumber, LoanDate, InterestRate, TotalNetWeight, AmountPerGram,
                 TotalAmount, MaxAmountPerGram, AllowedAmount, LoanAmount, BalanceLoanAmount, AppraiserCharge,
                 ProcessingFeePercentage, ProcessingFee,
                 PaymentMethod, PaymentDate, PaymentAmount, ChequeNumber, ChequeFromBank,
                 ChequeToBank, RtgsNumber, RtgsFromBank, RtgsToBank, UpiType, UpiTransactionNumber, UpiToBank)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stLoan = $this->conn->prepare($sqlLoan);
            if (!$stLoan) {
                throw new Exception("Loan subtable statement prepare failed: " . $this->conn->error);
            }
            $stLoan->bind_param(
                "ssdddddddddddssdsssssssss",
                $dNumber,
                $loanData['LoanDate'],
                $loanData['InterestRate'],
                $loanData['TotalNetWeight'],
                $loanData['AmountPerGram'],
                $loanData['TotalAmount'],
                $loanData['MaxAmountPerGram'],
                $loanData['AllowedAmount'],
                $loanData['LoanAmount'],
                $loanData['BalanceLoanAmount'],
                $loanData['AppraiserCharge'],
                $loanData['ProcessingFeePercentage'],
                $loanData['ProcessingFee'],
                $loanData['PaymentMethod'],
                $loanData['PaymentDate'],
                $loanData['PaymentAmount'],
                $loanData['ChequeNumber'],
                $loanData['ChequeFromBank'],
                $loanData['ChequeToBank'],
                $loanData['RtgsNumber'],
                $loanData['RtgsFromBank'],
                $loanData['RtgsToBank'],
                $loanData['UpiType'],
                $loanData['UpiTransactionNumber'],
                $loanData['UpiToBank']
            );
            if (!$stLoan->execute()) {
                throw new Exception("Failed to insert loan details: " . $stLoan->error);
            }
            $loanDetailId = $this->conn->insert_id;
            $stLoan->close();

            // 3. Insert repeatable gold items
            if (!empty($itemsData)) {
                $sqlItem = "INSERT INTO ast_jewel_loan_items 
                    (LoanDetailId, DNumber, SlNo, ItemName, NoOfItems, Gram, Wastage, NetWeight)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stItem = $this->conn->prepare($sqlItem);
                if (!$stItem) {
                    throw new Exception("Gold item prepare failed: " . $this->conn->error);
                }
                foreach ($itemsData as $item) {
                    $stItem->bind_param(
                        "isisiddd",
                        $loanDetailId,
                        $dNumber,
                        $item['SlNo'],
                        $item['ItemName'],
                        $item['NoOfItems'],
                        $item['Gram'],
                        $item['Wastage'],
                        $item['NetWeight']
                    );
                    if (!$stItem->execute()) {
                        throw new Exception("Failed to insert gold item: " . $stItem->error);
                    }
                }
                $stItem->close();
            }

            // 4. Automatically create initial disbursement receipt linked by LoanDetailId
            // Policy: Appraiser charge is collected at loan creation.
            // Policy: Processing fee is collected ONLY at final settlement/release (0.00 here).
            $receiptNo = $this->getNextReceiptNumber();
            $sqlReceipt = "INSERT INTO ast_jewel_loan_receipt (
                ReceiptNo, ReceiptDate, LoanDetailId, DNumber,
                TransactionType, PaymentMethod, PrincipalPaid, InterestPaid, DefaultInterestPaid,
                AppraiserCharge, ProcessingFee, TotalPaid, BalanceLoanAmount,
                ChequeNumber, ChequeFromBank, ChequeToBank,
                RtgsNumber, RtgsFromBank, RtgsToBank,
                UpiType, UpiTransactionNumber, UpiToBank,
                Remarks, CreatedBy
            ) VALUES (?, ?, ?, ?, 'LOAN_CREATION', ?, ?, 0.00, 0.00, ?, 0.00, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stR = $this->conn->prepare($sqlReceipt);
            if ($stR) {
                $rDate = $loanData['LoanDate'];
                $rPayMethod = $loanData['PaymentMethod'];
                $rPrin = (float)$loanData['LoanAmount'];
                $rAppr = (float)$loanData['AppraiserCharge'];
                $rTot = round($rPrin + $rAppr, 2);
                $rBal = (float)$loanData['BalanceLoanAmount'];
                $rChqNo = $loanData['ChequeNumber'] ?? '';
                $rChqFrom = $loanData['ChequeFromBank'] ?? '';
                $rChqTo = $loanData['ChequeToBank'] ?? '';
                $rRtgsNo = $loanData['RtgsNumber'] ?? '';
                $rRtgsFrom = $loanData['RtgsFromBank'] ?? '';
                $rRtgsTo = $loanData['RtgsToBank'] ?? '';
                $rUpiType = $loanData['UpiType'] ?? '';
                $rUpiTxn = $loanData['UpiTransactionNumber'] ?? '';
                $rUpiTo = $loanData['UpiToBank'] ?? '';
                $rRem = 'Initial Loan Disbursement Receipt';
                $rUser = (int)($customerData['CreatedBy'] ?? 1);

                $stR->bind_param(
                    "ssissddddssssssssssi",
                    $receiptNo,
                    $rDate,
                    $loanDetailId,
                    $dNumber,
                    $rPayMethod,
                    $rPrin,
                    $rAppr,
                    $rTot,
                    $rBal,
                    $rChqNo,
                    $rChqFrom,
                    $rChqTo,
                    $rRtgsNo,
                    $rRtgsFrom,
                    $rRtgsTo,
                    $rUpiType,
                    $rUpiTxn,
                    $rUpiTo,
                    $rRem,
                    $rUser
                );
                $stR->execute();
                $stR->close();
            }

            $this->conn->commit();
            return [
                'status' => true,
                'dNumber' => $dNumber,
                'loanDetailId' => $loanDetailId,
                'receiptNo' => $receiptNo
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update existing jewel loan record atomically
     */
    public function updateJewelLoan($loanDetailId, $customerData, $loanData = null, $itemsData = []) {
        $this->conn->begin_transaction();
        try {
            $dNumber = $customerData['DNumber'];

            $sqlCust = "UPDATE ast_jewel_loan_customer SET
                Name = ?, FathersName = ?, Job = ?, DateOfBirth = ?, Address = ?,
                MobileNumber = ?, Pincode = ?, NomineeName = ?, NomineeRelation = ?,
                NomineeAddress = ?, IfNomineeIsMinor = ?, GuardianName = ?,
                PacketNumber = ?, ReasonForLoan = ?, AadhaarNumber = ?, TotalWeight = ?,
                NoOfItems = ?,
                UploadSign = IF(? != '', ?, UploadSign),
                UploadAadhaar = IF(? != '', ?, UploadAadhaar)
                WHERE DNumber = ?";
            $stCust = $this->conn->prepare($sqlCust);
            if (!$stCust) {
                throw new Exception("Customer update statement prepare failed: " . $this->conn->error);
            }
            $stCust->bind_param(
                "sssssssssssssssdisssss",
                $customerData['Name'],
                $customerData['FathersName'],
                $customerData['Job'],
                $customerData['DateOfBirth'],
                $customerData['Address'],
                $customerData['MobileNumber'],
                $customerData['Pincode'],
                $customerData['NomineeName'],
                $customerData['NomineeRelation'],
                $customerData['NomineeAddress'],
                $customerData['IfNomineeIsMinor'],
                $customerData['GuardianName'],
                $customerData['PacketNumber'],
                $customerData['ReasonForLoan'],
                $customerData['AadhaarNumber'],
                $customerData['TotalWeight'],
                $customerData['NoOfItems'],
                $customerData['UploadSign'],
                $customerData['UploadSign'],
                $customerData['UploadAadhaar'],
                $customerData['UploadAadhaar'],
                $dNumber
            );
            if (!$stCust->execute()) {
                throw new Exception("Failed to update customer: " . $stCust->error);
            }
            $stCust->close();

            if (!empty($loanData) && (int)$loanDetailId > 0) {
                $sqlLoan = "UPDATE ast_jewel_loan_details SET
                    LoanDate = ?, InterestRate = ?, TotalNetWeight = ?, AmountPerGram = ?,
                    TotalAmount = ?, MaxAmountPerGram = ?, AllowedAmount = ?, LoanAmount = ?,
                    BalanceLoanAmount = ?, AppraiserCharge = ?, ProcessingFeePercentage = ?, ProcessingFee = ?,
                    PaymentMethod = ?, PaymentDate = ?, PaymentAmount = ?,
                    ChequeNumber = ?, ChequeFromBank = ?, ChequeToBank = ?,
                    RtgsNumber = ?, RtgsFromBank = ?, RtgsToBank = ?,
                    UpiType = ?, UpiTransactionNumber = ?, UpiToBank = ?
                    WHERE LoanDetailId = ?";
                $stLoan = $this->conn->prepare($sqlLoan);
                if (!$stLoan) {
                    throw new Exception("Loan detail update prepare failed: " . $this->conn->error);
                }
                $ldId = (int)$loanDetailId;
                $stLoan->bind_param(
                    "sdddddddddddssdsssssssssi",
                    $loanData['LoanDate'],
                    $loanData['InterestRate'],
                    $loanData['TotalNetWeight'],
                    $loanData['AmountPerGram'],
                    $loanData['TotalAmount'],
                    $loanData['MaxAmountPerGram'],
                    $loanData['AllowedAmount'],
                    $loanData['LoanAmount'],
                    $loanData['BalanceLoanAmount'],
                    $loanData['AppraiserCharge'],
                    $loanData['ProcessingFeePercentage'],
                    $loanData['ProcessingFee'],
                    $loanData['PaymentMethod'],
                    $loanData['PaymentDate'],
                    $loanData['PaymentAmount'],
                    $loanData['ChequeNumber'],
                    $loanData['ChequeFromBank'],
                    $loanData['ChequeToBank'],
                    $loanData['RtgsNumber'],
                    $loanData['RtgsFromBank'],
                    $loanData['RtgsToBank'],
                    $loanData['UpiType'],
                    $loanData['UpiTransactionNumber'],
                    $loanData['UpiToBank'],
                    $ldId
                );
                if (!$stLoan->execute()) {
                    throw new Exception("Failed to update loan details: " . $stLoan->error);
                }
                $stLoan->close();

                if (!empty($itemsData)) {
                    $delStmt = $this->conn->prepare("DELETE FROM ast_jewel_loan_items WHERE LoanDetailId = ?");
                    if ($delStmt) {
                        $delStmt->bind_param("i", $ldId);
                        $delStmt->execute();
                        $delStmt->close();
                    }

                    $sqlItem = "INSERT INTO ast_jewel_loan_items 
                        (LoanDetailId, DNumber, SlNo, ItemName, NoOfItems, Gram, Wastage, NetWeight)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stItem = $this->conn->prepare($sqlItem);
                    if ($stItem) {
                        foreach ($itemsData as $item) {
                            $stItem->bind_param(
                                "isisiddd",
                                $ldId,
                                $dNumber,
                                $item['SlNo'],
                                $item['ItemName'],
                                $item['NoOfItems'],
                                $item['Gram'],
                                $item['Wastage'],
                                $item['NetWeight']
                            );
                            $stItem->execute();
                        }
                        $stItem->close();
                    }
                }
            }

            $this->conn->commit();
            return ['status' => true, 'dNumber' => $dNumber, 'loanDetailId' => $loanDetailId];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Retrieve complete saved jewel loan record for PDF generation or printing
     */
    public function getReceiptsByLoanId($loanDetailId) {
        $receipts = [];
        $sql = "SELECT * FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? ORDER BY ReceiptId ASC";
        $st = $this->conn->prepare($sql);
        if ($st) {
            $loanDetailId = (int)$loanDetailId;
            $st->bind_param("i", $loanDetailId);
            $st->execute();
            $res = $st->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $receipts[] = $row;
                }
            }
            $st->close();
        }
        return $receipts;
    }

    public function getJewelLoanByDNumber($dNumber) {
        return $this->getLoanRecordForPdf(0, $dNumber);
    }

    public function getLoanRecordForPdf($loanDetailId = 0, $dNumber = '') {
        $loanDetail = null;
        if ((int)$loanDetailId > 0) {
            $loanDetailId = (int)$loanDetailId;
            $sql = "SELECT * FROM ast_jewel_loan_details WHERE LoanDetailId = ? LIMIT 1";
            $st = $this->conn->prepare($sql);
            if ($st) {
                $st->bind_param("i", $loanDetailId);
                $st->execute();
                $res = $st->get_result();
                $loanDetail = $res ? $res->fetch_assoc() : null;
                $st->close();
            }
        } elseif (!empty($dNumber)) {
            $normD = $this->normalizeDNumber($dNumber);
            $rawD  = trim($dNumber);
            $sql = "SELECT * FROM ast_jewel_loan_details 
                    WHERE DNumber = ? OR DNumber = ? OR TRIM(DNumber) = ? OR UPPER(DNumber) = ?
                    ORDER BY LoanDetailId DESC LIMIT 1";
            $st = $this->conn->prepare($sql);
            if ($st) {
                $st->bind_param("ssss", $normD, $rawD, $rawD, $normD);
                $st->execute();
                $res = $st->get_result();
                $loanDetail = $res ? $res->fetch_assoc() : null;
                $st->close();
            }

            if (!$loanDetail) {
                $likeD = '%' . $rawD . '%';
                $sql2 = "SELECT d.* FROM ast_jewel_loan_details d
                         LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = d.DNumber
                         LEFT JOIN ast_members m ON m.MemberCode = d.DNumber
                         WHERE d.DNumber LIKE ? 
                            OR c.DNumber = ? OR c.DNumber = ?
                            OR m.MemberCode = ? OR m.MemberCode = ?
                         ORDER BY d.LoanDetailId DESC LIMIT 1";
                $st2 = $this->conn->prepare($sql2);
                if ($st2) {
                    $st2->bind_param("sssss", $likeD, $normD, $rawD, $normD, $rawD);
                    $st2->execute();
                    $res2 = $st2->get_result();
                    $loanDetail = $res2 ? $res2->fetch_assoc() : null;
                    $st2->close();
                }
            }
        }

        if (!$loanDetail) {
            return null;
        }

        $dNumber = $loanDetail['DNumber'];
        $customer = $this->getCustomerByDNumber($dNumber);

        $items = [];
        $sqlItems = "SELECT * FROM ast_jewel_loan_items WHERE LoanDetailId = ? ORDER BY SlNo ASC";
        $stItems = $this->conn->prepare($sqlItems);
        if ($stItems) {
            $ldId = (int)$loanDetail['LoanDetailId'];
            $stItems->bind_param("i", $ldId);
            $stItems->execute();
            $resItems = $stItems->get_result();
            if ($resItems) {
                while ($it = $resItems->fetch_assoc()) {
                    $items[] = $it;
                }
            }
            $stItems->close();
        }

        return [
            'customer' => $customer,
            'loan' => $loanDetail,
            'items' => $items
        ];
    }

    /**
     * Generate the next Receipt Number with year sequence: JLR-YYYY-XXXX
     */
    public function getNextReceiptNumber() {
        $year = date('Y');
        $prefix = "JLR-{$year}-";

        $sql = "SELECT ReceiptNo FROM ast_jewel_loan_receipt 
                WHERE ReceiptNo LIKE ? 
                ORDER BY ReceiptId DESC LIMIT 1 FOR UPDATE";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $like = $prefix . '%';
            $stmt->bind_param("s", $like);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $lastNo = $row['ReceiptNo'];
                $parts = explode('-', $lastNo);
                if (count($parts) >= 3 && is_numeric($parts[2])) {
                    $nextSeq = (int)$parts[2] + 1;
                    $stmt->close();
                    return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
                }
            }
            $stmt->close();
        }
        return $prefix . "0001";
    }

    /**
     * Search loans for receipt processing (by D Number, Name, Mobile, or Aadhaar)
     */
    public function searchLoansForReceipt($query = '') {
        $query = trim($query);
        $results = [];

        if (empty($query)) {
            $sql = "SELECT d.LoanDetailId, d.DNumber, d.LoanDate, d.InterestRate, 
                           d.LoanAmount, d.BalanceLoanAmount, d.TotalNetWeight,
                           COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS Name, 
                           COALESCE(NULLIF(c.MobileNumber, ''), m.MobileNo, '-') AS MobileNumber, 
                           COALESCE(NULLIF(c.AadhaarNumber, ''), m.Aadhaar, '-') AS AadhaarNumber
                    FROM ast_jewel_loan_details d
                    LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = d.DNumber
                    LEFT JOIN ast_members m ON m.MemberCode = d.DNumber
                    ORDER BY d.LoanDetailId DESC LIMIT 20";
            $res = $this->conn->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            return $results;
        }

        $normD = $this->normalizeDNumber($query);
        $searchTerm = "%{$query}%";
        $sql = "SELECT d.LoanDetailId, d.DNumber, d.LoanDate, d.InterestRate, 
                       d.LoanAmount, d.BalanceLoanAmount, d.TotalNetWeight,
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS Name, 
                       COALESCE(NULLIF(c.MobileNumber, ''), m.MobileNo, '-') AS MobileNumber, 
                       COALESCE(NULLIF(c.AadhaarNumber, ''), m.Aadhaar, '-') AS AadhaarNumber
                FROM ast_jewel_loan_details d
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = d.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = d.DNumber
                WHERE d.DNumber LIKE ? 
                   OR d.DNumber = ?
                   OR c.Name LIKE ? 
                   OR m.FullName LIKE ?
                   OR c.MobileNumber LIKE ? 
                   OR m.MobileNo LIKE ?
                   OR c.AadhaarNumber LIKE ?
                   OR m.Aadhaar LIKE ?
                ORDER BY d.LoanDetailId DESC LIMIT 50";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssssss", $searchTerm, $normD, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            $stmt->close();
        }
        return $results;
    }

    /**
     * Get the date up to which interest has been paid for a given loan
     */
    public function getLoanPaidThroughDate($loanDetailId, $fallbackLoanDate) {
        $ldId = (int)$loanDetailId;
        $paidThrough = $fallbackLoanDate;

        $sql = "SELECT InterestPeriodTo, ReceiptDate FROM ast_jewel_loan_receipt 
                WHERE LoanDetailId = ? AND TransactionType = 'INTEREST_PAYMENT' AND InterestPaid > 0
                ORDER BY ReceiptId ASC";
        $st = $this->conn->prepare($sql);
        if ($st) {
            $st->bind_param("i", $ldId);
            $st->execute();
            $res = $st->get_result();
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $to = !empty($r['InterestPeriodTo']) ? $r['InterestPeriodTo'] : $r['ReceiptDate'];
                    if (!empty($to) && $to !== '0000-00-00') {
                        if ($to > $paidThrough) {
                            $paidThrough = $to;
                        }
                    }
                }
            }
            $st->close();
        }
        return $paidThrough;
    }

    /**
     * Shared interest calculation engine for Jewel Loan
     * Implements Scenario 1, Scenario 2 (overdue range rate escalation), Scenario 3 (long-term default),
     * admin waivers, and minimum 15-day release rule.
     */
    public function calculateLoanInterestSchedule($loanDetailId, $asOfDateStr = null, $targetPayThroughDate = null) {
        $loanDetailId = (int)$loanDetailId;
        $asOfDate = !empty($asOfDateStr) ? new DateTime($asOfDateStr) : new DateTime();
        $asOfDateStr = $asOfDate->format('Y-m-d');

        $sql = "SELECT * FROM ast_jewel_loan_details WHERE LoanDetailId = ? LIMIT 1";
        $st = $this->conn->prepare($sql);
        if (!$st) return null;
        $st->bind_param("i", $loanDetailId);
        $st->execute();
        $res = $st->get_result();
        $loan = $res ? $res->fetch_assoc() : null;
        $st->close();
        if (!$loan) return null;

        $dNumber = $loan['DNumber'];
        $loanDateStr = $loan['LoanDate'];
        $loanDate = new DateTime($loanDateStr);
        $originalPrincipal = (float)$loan['LoanAmount'];
        $baseRate = (float)$loan['InterestRate'];

        $isReleased = false;
        $chkSql = "SELECT 1 FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? AND TransactionType = 'JEWEL_RELEASE' LIMIT 1";
        $chkSt = $this->conn->prepare($chkSql);
        if ($chkSt) {
            $chkSt->bind_param("i", $loanDetailId);
            $chkSt->execute();
            $chkRes = $chkSt->get_result();
            if ($chkRes && $chkRes->num_rows > 0) {
                $isReleased = true;
            }
            $chkSt->close();
        }

        $currentPrincipal = $isReleased ? 0.00 : (float)($loan['BalanceLoanAmount'] ?? $originalPrincipal);
        if ($currentPrincipal <= 0.00 && !$isReleased) {
            $currentPrincipal = $originalPrincipal;
        }

        $paidThroughDateStr = $this->getLoanPaidThroughDate($loanDetailId, $loanDateStr);
        $paidThroughDate = new DateTime($paidThroughDateStr);

        // Find next scheduled installment due date
        $pendingInstallmentNum = 1;
        $installmentDueDateStr = '';
        for ($k = 1; $k <= 120; $k++) {
            $dueK = $this->calculateMonthlyInstallmentDueDate($loanDateStr, $k);
            if ($paidThroughDateStr < $dueK) {
                $pendingInstallmentNum = $k;
                $installmentDueDateStr = $dueK;
                break;
            }
        }
        if (empty($installmentDueDateStr)) {
            $installmentDueDateStr = $this->calculateMonthlyInstallmentDueDate($loanDateStr, 1);
        }
        $installmentDueDate = new DateTime($installmentDueDateStr);

        // Scenario 3: Check long-term default (> 6 months overdue without payment)
        $overdueFromLoanDateDays = $paidThroughDate->diff($asOfDate)->days;
        $isLongTermDefault = ($asOfDate > $installmentDueDate && $overdueFromLoanDateDays > 180);
        $applicableBaseRate = $baseRate;
        if ($isLongTermDefault && $applicableBaseRate < 30.0) {
            $applicableBaseRate = 30.00;
        }

        // Check for admin waiver in ast_jewel_loan_rate_history
        $waiverRow = null;
        $wSql = "SELECT * FROM ast_jewel_loan_rate_history WHERE LoanDetailId = ? AND IsWaived = 1 ORDER BY HistoryId DESC LIMIT 1";
        $wSt = $this->conn->prepare($wSql);
        if ($wSt) {
            $wSt->bind_param("i", $loanDetailId);
            $wSt->execute();
            $wRes = $wSt->get_result();
            if ($wRes && $wRow = $wRes->fetch_assoc()) {
                $waiverRow = $wRow;
            }
            $wSt->close();
        }

        $overdueBreakdown = [
            'isOverdue' => false,
            'periodFrom' => null,
            'periodTo' => null,
            'days' => 0,
            'rate' => $baseRate,
            'interest' => 0.00,
            'isEscalated' => false,
            'reason' => ''
        ];

        $targetEndDateStr = !empty($targetPayThroughDate) ? $targetPayThroughDate : $asOfDateStr;
        $targetEndDate = new DateTime($targetEndDateStr);
        if ($targetEndDate < $paidThroughDate) {
            $targetEndDate = clone $paidThroughDate;
            $targetEndDateStr = $paidThroughDateStr;
        }

        // Scenario 2: Overdue early partial installment or missed installment deadline
        if ($asOfDate > $installmentDueDate && $paidThroughDate < $installmentDueDate) {
            $overdueFrom = clone $paidThroughDate;
            $overdueTo = clone $installmentDueDate;
            $overdueDays = $overdueFrom->diff($overdueTo)->days;

            if ($overdueDays > 0) {
                $overdueRate = $this->getNextSlabRate($baseRate);
                $reason = "Scenario 2: Overdue period from {$overdueFrom->format('d/m/Y')} to {$overdueTo->format('d/m/Y')} missed scheduled deadline ({$installmentDueDate->format('d/m/Y')}). Rate escalated from {$baseRate}% to {$overdueRate}%.";

                $waivedAmount = 0.00;
                if ($waiverRow && !empty($waiverRow['NewRate'])) {
                    $overdueRate = (float)$waiverRow['NewRate'];
                    $reason .= " (Admin waiver applied: rate {$overdueRate}%)";
                }
                if ($waiverRow && (float)$waiverRow['WaivedAmount'] > 0) {
                    $waivedAmount = (float)$waiverRow['WaivedAmount'];
                }

                $overdueInterest = $this->calculateDailyInterest($currentPrincipal, $overdueRate, $overdueDays);
                $overdueInterest = max(0.00, round($overdueInterest - $waivedAmount, 2));

                $overdueBreakdown = [
                    'isOverdue' => true,
                    'periodFrom' => $overdueFrom->format('Y-m-d'),
                    'periodTo' => $overdueTo->format('Y-m-d'),
                    'days' => $overdueDays,
                    'rate' => $overdueRate,
                    'interest' => $overdueInterest,
                    'isEscalated' => true,
                    'reason' => $reason
                ];
            }

            // Subsequent regular period from installmentDueDate to targetEndDate
            if ($targetEndDate > $installmentDueDate) {
                $regFrom = clone $installmentDueDate;
                $regTo = clone $targetEndDate;
                $regDays = $regFrom->diff($regTo)->days;
                $regInterest = $this->calculateDailyInterest($currentPrincipal, $applicableBaseRate, $regDays);

                $regularBreakdown = [
                    'periodFrom' => $regFrom->format('Y-m-d'),
                    'periodTo' => $regTo->format('Y-m-d'),
                    'days' => $regDays,
                    'rate' => $applicableBaseRate,
                    'interest' => $regInterest
                ];
            } else {
                $regularBreakdown = [
                    'periodFrom' => $installmentDueDate->format('Y-m-d'),
                    'periodTo' => $installmentDueDate->format('Y-m-d'),
                    'days' => 0,
                    'rate' => $applicableBaseRate,
                    'interest' => 0.00
                ];
            }
        } else {
            // Standard / regular interest calculation
            $regFrom = clone $paidThroughDate;
            $regTo = clone $targetEndDate;
            $regDays = $regFrom->diff($regTo)->days;
            $regInterest = $this->calculateDailyInterest($currentPrincipal, $applicableBaseRate, $regDays);

            $regularBreakdown = [
                'periodFrom' => $regFrom->format('Y-m-d'),
                'periodTo' => $regTo->format('Y-m-d'),
                'days' => $regDays,
                'rate' => $applicableBaseRate,
                'interest' => $regInterest
            ];
        }

        $totalInterestDue = round($overdueBreakdown['interest'] + $regularBreakdown['interest'], 2);

        $processingFeePercent = $this->getLatestProcessingFeePercentage();
        $processingFee = round(($originalPrincipal * $processingFeePercent) / 100.0, 2);
        $processingFeePaid = $this->isProcessingFeePaid($loanDetailId);

        $closureProcessingFee = $processingFeePaid ? 0.00 : $processingFee;
        $totalClosureAmount = round($currentPrincipal + $totalInterestDue + $closureProcessingFee, 2);

        return [
            'loanDetailId' => $loanDetailId,
            'dNumber' => $dNumber,
            'loanDate' => $loanDateStr,
            'originalPrincipal' => $originalPrincipal,
            'currentPrincipal' => $currentPrincipal,
            'baseRate' => $baseRate,
            'applicableBaseRate' => $applicableBaseRate,
            'paidThroughDate' => $paidThroughDateStr,
            'nextScheduledDueDate' => $installmentDueDateStr,
            'asOfDate' => $asOfDateStr,
            'targetEndDate' => $targetEndDateStr,
            'overdueBreakdown' => $overdueBreakdown,
            'regularBreakdown' => $regularBreakdown,
            'totalInterestDue' => $totalInterestDue,
            'processingFee' => $processingFee,
            'processingFeePercent' => $processingFeePercent,
            'processingFeePaid' => $processingFeePaid,
            'appraiserCharge' => 0.00,
            'totalAmountDue' => round($currentPrincipal + $totalInterestDue, 2),
            'totalClosureAmount' => $totalClosureAmount,
            'isReleased' => $isReleased
        ];
    }

    /**
     * Allocate payment: interest due is settled first, and any excess strictly reduces principal balance.
     * Excess payment is NEVER diverted to processing fee or appraiser charge.
     */
    public function allocatePayment($loanDetailId, $amountPaid, $asOfDateStr = null, $targetPayThroughDate = null) {
        $amountPaid = (float)$amountPaid;
        $schedule = $this->calculateLoanInterestSchedule($loanDetailId, $asOfDateStr, $targetPayThroughDate);
        if (!$schedule) return null;

        $currentPrincipal = (float)$schedule['currentPrincipal'];
        $totalInterestDue = (float)$schedule['totalInterestDue'];
        $overdueInterest = (float)$schedule['overdueBreakdown']['interest'];
        $regularInterest = (float)$schedule['regularBreakdown']['interest'];

        $overdueInterestPaid = 0.00;
        $regularInterestPaid = 0.00;
        $totalInterestPaid = 0.00;
        $principalPaid = 0.00;
        $excessPayment = 0.00;

        if ($amountPaid >= $totalInterestDue) {
            $overdueInterestPaid = $overdueInterest;
            $regularInterestPaid = $regularInterest;
            $totalInterestPaid = $totalInterestDue;
            $excessPayment = round($amountPaid - $totalInterestDue, 2);
            $principalPaid = min($currentPrincipal, $excessPayment);
            $newPrincipalBalance = max(0.00, round($currentPrincipal - $principalPaid, 2));
        } else {
            $rem = $amountPaid;
            $overdueInterestPaid = min($overdueInterest, $rem);
            $rem = max(0.00, $rem - $overdueInterestPaid);
            $regularInterestPaid = min($regularInterest, $rem);
            $totalInterestPaid = round($overdueInterestPaid + $regularInterestPaid, 2);
            $principalPaid = 0.00;
            $newPrincipalBalance = $currentPrincipal;
        }

        return [
            'schedule' => $schedule,
            'amountPaid' => $amountPaid,
            'overdueInterestPaid' => $overdueInterestPaid,
            'regularInterestPaid' => $regularInterestPaid,
            'totalInterestPaid' => $totalInterestPaid,
            'principalPaid' => $principalPaid,
            'excessPayment' => $excessPayment,
            'previousPrincipalBalance' => $currentPrincipal,
            'newPrincipalBalance' => $newPrincipalBalance
        ];
    }

    /**
     * Record an admin waiver for escalated rate or interest in ast_jewel_loan_rate_history
     */
    public function recordRateHistoryWaiver($loanDetailId, $waivedAmount, $waivedRate, $waivedBy, $reason, $remarks = '') {
        $loanDetailId = (int)$loanDetailId;
        $waivedAmount = (float)$waivedAmount;
        $waivedRate = (float)$waivedRate;
        $waivedBy = (int)$waivedBy;

        $loan = $this->getLoanRecordForPdf($loanDetailId);
        $dNumber = $loan['loan']['DNumber'] ?? '';
        $prevRate = (float)($loan['loan']['InterestRate'] ?? 18.0);

        $sql = "INSERT INTO ast_jewel_loan_rate_history 
                (LoanDetailId, DNumber, PreviousRate, NewRate, EffectiveDate, Reason, CalculatedInterest, IsWaived, WaivedAmount, WaivedBy, WaiverRemarks)
                VALUES (?, ?, ?, ?, CURDATE(), ?, 0.00, 1, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isddsdis", $loanDetailId, $dNumber, $prevRate, $waivedRate, $reason, $waivedAmount, $waivedBy, $remarks);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    /**
     * Fetch complete details of a loan including receipt history and unified schedule
     */
    public function getLoanReceiptSummary($loanDetailId = 0, $dNumber = '', $asOfDateStr = null) {
        $loanRecord = $this->getLoanRecordForPdf($loanDetailId, $dNumber);
        if (!$loanRecord || empty($loanRecord['loan'])) {
            return null;
        }

        $ldId = (int)$loanRecord['loan']['LoanDetailId'];

        $receipts = [];
        $sqlR = "SELECT * FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? ORDER BY ReceiptId DESC";
        $stR = $this->conn->prepare($sqlR);
        if ($stR) {
            $stR->bind_param("i", $ldId);
            $stR->execute();
            $resR = $stR->get_result();
            if ($resR) {
                while ($r = $resR->fetch_assoc()) {
                    $receipts[] = $r;
                }
            }
            $stR->close();
        }

        $schedule = $this->calculateLoanInterestSchedule($ldId, $asOfDateStr);

        $loan = $loanRecord['loan'];
        $loanDateStr = $loan['LoanDate'];
        $today = !empty($asOfDateStr) ? new DateTime($asOfDateStr) : new DateTime();
        $originalLoanDate = new DateTime($loanDateStr);

        $totDaysElapsed = $originalLoanDate->diff($today)->days;
        if ($today < $originalLoanDate) {
            $totDaysElapsed = 0;
        }

        $closingDueDate = clone $originalLoanDate;
        $origDay = (int)$originalLoanDate->format('d');
        $closingDueDate->modify('+1 year');
        if ((int)$closingDueDate->format('d') !== $origDay) {
            $closingDueDate->modify('last day of previous month');
        }
        $closingDueDateStr = $closingDueDate->format('Y-m-d');
        $dueDateStr = $closingDueDateStr;

        $monthlyDueDateStr = $schedule['nextScheduledDueDate'] ?? $this->calculateNextMonthlyDueDate($loanDateStr, $ldId);

        $overdueDays = 0;
        $gracePeriodDays = 0;
        if ($today > $closingDueDate) {
            $overdueDays = $closingDueDate->diff($today)->days;
            $gracePeriodDays = 7;
        }

        if ($overdueDays > 0) {
            $daysStatusLabel = "{$overdueDays} Days Overdue";
            $totDaysDisplay = "{$totDaysElapsed} Days ({$overdueDays} Days Overdue)";
        } else {
            $daysLeft = $today->diff($closingDueDate)->days;
            $daysStatusLabel = "Active ({$daysLeft} Days to Maturity)";
            $totDaysDisplay = "{$totDaysElapsed} Days ({$daysLeft} Days Remaining)";
        }

        $customer = $loanRecord['customer'];
        $memberSql = "SELECT m.*, d.DistrictName FROM ast_members m 
                      LEFT JOIN ast_district d ON d.DID = m.District 
                      WHERE m.MemberCode = ? LIMIT 1";
        $mStmt = $this->conn->prepare($memberSql);
        if ($mStmt) {
            $mStmt->bind_param("s", $loan['DNumber']);
            $mStmt->execute();
            $mRes = $mStmt->get_result();
            if ($mRes && $mRow = $mRes->fetch_assoc()) {
                if (empty($customer['Name']) || $customer['Name'] === '0') $customer['Name'] = $mRow['FullName'];
                if (empty($customer['FathersName'])) $customer['FathersName'] = $mRow['SDWName'];
                if (empty($customer['Job'])) $customer['Job'] = $mRow['Occupation'];
                if (empty($customer['MobileNumber'])) $customer['MobileNumber'] = $mRow['MobileNo'];
                if (empty($customer['Pincode'])) $customer['Pincode'] = $mRow['PinCode'];
                if (empty($customer['Address'])) $customer['Address'] = $mRow['PermenantAddress'];
                $customer['DistrictName'] = $mRow['DistrictName'] ?? '';
            }
            $mStmt->close();
        }

        $principalBalance = $schedule['currentPrincipal'];
        $accruedInterest = $schedule['totalInterestDue'];
        $totalAmountDue = $schedule['totalAmountDue'];
        $totalClosureAmount = $schedule['totalClosureAmount'];

        return [
            'loan' => $loanRecord['loan'],
            'customer' => $customer,
            'items' => $loanRecord['items'],
            'receipts' => $receipts,
            'schedule' => $schedule,
            'calculation' => [
                'principalBalance' => $principalBalance,
                'interestRate' => $schedule['applicableBaseRate'],
                'dueDate' => $dueDateStr,
                'closingDueDate' => $closingDueDateStr,
                'monthlyDueDate' => $monthlyDueDateStr,
                'lastCalculatedDate' => $schedule['paidThroughDate'],
                'calculatedDays' => $schedule['regularBreakdown']['days'] + $schedule['overdueBreakdown']['days'],
                'calculatedMonths' => round(($schedule['regularBreakdown']['days'] + $schedule['overdueBreakdown']['days']) / 30.4167, 2),
                'totDaysElapsed' => $totDaysElapsed,
                'totDaysDisplay' => $totDaysDisplay,
                'daysStatusLabel' => $daysStatusLabel,
                'gracePeriodDays' => $gracePeriodDays,
                'overdueDays' => $overdueDays,
                'accruedInterest' => $accruedInterest,
                'overdueInterest' => $schedule['overdueBreakdown']['interest'],
                'regularInterest' => $schedule['regularBreakdown']['interest'],
                'defaultInterest' => 0.00,
                'processingFee' => $schedule['processingFee'],
                'processingFeePaid' => $schedule['processingFeePaid'],
                'appraiserCharge' => 0.00,
                'totalAmountDue' => $totalAmountDue,
                'totalClosureAmount' => $totalClosureAmount
            ]
        ];
    }

    /**
     * Standalone interest calculation for backwards compatibility
     */
    public function calculateInterestDue($loanDateStr, $principalBalance, $rate, $asOfDateStr = null, $lastPaymentDateStr = null) {
        $originalLoanDate = new DateTime($loanDateStr);
        $lastDateStr      = !empty($lastPaymentDateStr) ? $lastPaymentDateStr : $loanDateStr;
        $calcStartDate    = new DateTime($lastDateStr);
        $today            = !empty($asOfDateStr) ? new DateTime($asOfDateStr) : new DateTime();

        $interestDays = $calcStartDate->diff($today)->days;
        if ($today < $calcStartDate) {
            $interestDays = 0;
        }

        $calculatedInterest = $this->calculateDailyInterest($principalBalance, $rate, $interestDays);

        $closingDueDate = clone $originalLoanDate;
        $origDay = (int)$originalLoanDate->format('d');
        $closingDueDate->modify('+1 year');
        if ((int)$closingDueDate->format('d') !== $origDay) {
            $closingDueDate->modify('last day of previous month');
        }

        $monthlyDueDateStr = $this->calculateMonthlyInstallmentDueDate($loanDateStr, 1);

        return [
            'principalBalance' => $principalBalance,
            'interestRate' => $rate,
            'closingDueDate' => $closingDueDate->format('Y-m-d'),
            'monthlyDueDate' => $monthlyDueDateStr,
            'calculatedDays' => $interestDays,
            'calculatedMonths' => round($interestDays / 30.4167, 2),
            'totDaysElapsed' => $originalLoanDate->diff($today)->days,
            'accruedInterest' => $calculatedInterest,
            'defaultInterest' => 0.00,
            'totalAmountDue' => round($principalBalance + $calculatedInterest, 2)
        ];
    }

    /**
     * Save a new Receipt transaction (LOAN_CREATION, INTEREST_PAYMENT, JEWEL_RELEASE)
     * Handles payment allocation: excess paid reduces BalanceLoanAmount in ast_jewel_loan_details.
     * Records RateApplied, OverdueInterestRate, OverdueInterestPaid, OverduePeriodFrom, OverduePeriodTo, BreakdownJson.
     * Logs rate escalation audit history into ast_jewel_loan_rate_history.
     */
    public function saveReceipt($receiptData) {
        $this->conn->begin_transaction();
        try {
            $receiptNo = $this->getNextReceiptNumber();

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

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare receipt failed: " . $this->conn->error);
            }

            $receiptDate = !empty($receiptData['ReceiptDate']) ? $receiptData['ReceiptDate'] : date('Y-m-d');
            $loanDetailId = (int)$receiptData['LoanDetailId'];
            $dNumber = $receiptData['DNumber'];
            $txnType = $receiptData['TransactionType'];
            $payMethod = $receiptData['PaymentMethod'] ?? 'Cash';
            $principalPaid = (float)($receiptData['PrincipalPaid'] ?? 0);
            $interestPaid = (float)($receiptData['InterestPaid'] ?? 0);
            $defaultInterestPaid = (float)($receiptData['DefaultInterestPaid'] ?? 0);
            $appraiserCharge = (float)($receiptData['AppraiserCharge'] ?? 0);
            $processingFee = (float)($receiptData['ProcessingFee'] ?? 0);
            $totalPaid = (float)($receiptData['TotalPaid'] ?? ($principalPaid + $interestPaid + $defaultInterestPaid + $appraiserCharge + $processingFee));
            $balanceLoanAmount = (float)($receiptData['BalanceLoanAmount'] ?? 0);
            $interestMonths = (float)($receiptData['InterestMonths'] ?? 0);
            $periodFrom = !empty($receiptData['InterestPeriodFrom']) ? $receiptData['InterestPeriodFrom'] : null;
            $periodTo = !empty($receiptData['InterestPeriodTo']) ? $receiptData['InterestPeriodTo'] : null;
            $chequeNo = $receiptData['ChequeNumber'] ?? '';
            $chequeFrom = $receiptData['ChequeFromBank'] ?? '';
            $chequeTo = $receiptData['ChequeToBank'] ?? '';
            $rtgsNo = $receiptData['RtgsNumber'] ?? '';
            $rtgsFrom = $receiptData['RtgsFromBank'] ?? '';
            $rtgsTo = $receiptData['RtgsToBank'] ?? '';
            $upiType = $receiptData['UpiType'] ?? '';
            $upiTxn = $receiptData['UpiTransactionNumber'] ?? '';
            $upiTo = $receiptData['UpiToBank'] ?? '';
            $remarks = $receiptData['Remarks'] ?? '';
            $createdBy = (int)($receiptData['CreatedBy'] ?? ($_SESSION['UserId'] ?? 1));

            $rateApplied = isset($receiptData['RateApplied']) ? (float)$receiptData['RateApplied'] : null;
            $overdueRate = isset($receiptData['OverdueInterestRate']) ? (float)$receiptData['OverdueInterestRate'] : null;
            $overduePaid = isset($receiptData['OverdueInterestPaid']) ? (float)$receiptData['OverdueInterestPaid'] : 0.00;
            $overdueFrom = !empty($receiptData['OverduePeriodFrom']) ? $receiptData['OverduePeriodFrom'] : null;
            $overdueTo   = !empty($receiptData['OverduePeriodTo']) ? $receiptData['OverduePeriodTo'] : null;
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
                throw new Exception("Execute receipt failed: " . $stmt->error);
            }
            $receiptId = $this->conn->insert_id;
            $stmt->close();

            // Log rate escalation audit trail in ast_jewel_loan_rate_history if overdue escalation applied
            if ($overduePaid > 0 && $overdueRate !== null && $overdueRate > $rateApplied) {
                $histSql = "INSERT INTO ast_jewel_loan_rate_history
                    (LoanDetailId, DNumber, ReceiptId, InstallmentDueDate, OverduePeriodFrom, OverduePeriodTo, PreviousRate, NewRate, EffectiveDate, Reason, CalculatedInterest)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $hStmt = $this->conn->prepare($histSql);
                if ($hStmt) {
                    $reason = "Scenario 2: Overdue interest rate escalation on missed installment deadline";
                    $hStmt->bind_param(
                        "isisssddssd",
                        $loanDetailId,
                        $dNumber,
                        $receiptId,
                        $overdueTo,
                        $overdueFrom,
                        $overdueTo,
                        $rateApplied,
                        $overdueRate,
                        $receiptDate,
                        $reason,
                        $overduePaid
                    );
                    $hStmt->execute();
                    $hStmt->close();
                }
            }

            // Update balance loan amount in ast_jewel_loan_details
            if ($txnType === 'JEWEL_RELEASE') {
                $upSql = "UPDATE ast_jewel_loan_details SET BalanceLoanAmount = 0.00 WHERE LoanDetailId = ?";
                $upStmt = $this->conn->prepare($upSql);
                if ($upStmt) {
                    $upStmt->bind_param("i", $loanDetailId);
                    $upStmt->execute();
                    $upStmt->close();
                }
            } elseif ($txnType === 'INTEREST_PAYMENT') {
                $upSql = "UPDATE ast_jewel_loan_details SET BalanceLoanAmount = ? WHERE LoanDetailId = ?";
                $upStmt = $this->conn->prepare($upSql);
                if ($upStmt) {
                    $upStmt->bind_param("di", $balanceLoanAmount, $loanDetailId);
                    $upStmt->execute();
                    $upStmt->close();
                }
            }

            $this->conn->commit();
            return [
                'status' => true,
                'receiptId' => $receiptId,
                'receiptNo' => $receiptNo
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch a specific receipt by ReceiptId or ReceiptNo
     */
    public function getReceiptById($receiptId = 0, $receiptNo = '') {
        $receipt = null;
        if ((int)$receiptId > 0) {
            $rId = (int)$receiptId;
            $sql = "SELECT * FROM ast_jewel_loan_receipt WHERE ReceiptId = ? LIMIT 1";
            $st = $this->conn->prepare($sql);
            if ($st) {
                $st->bind_param("i", $rId);
                $st->execute();
                $res = $st->get_result();
                $receipt = $res ? $res->fetch_assoc() : null;
                $st->close();
            }
        } elseif (!empty($receiptNo)) {
            $sql = "SELECT * FROM ast_jewel_loan_receipt WHERE ReceiptNo = ? LIMIT 1";
            $st = $this->conn->prepare($sql);
            if ($st) {
                $st->bind_param("s", $receiptNo);
                $st->execute();
                $res = $st->get_result();
                $receipt = $res ? $res->fetch_assoc() : null;
                $st->close();
            }
        }

        if (!$receipt) {
            return null;
        }

        $loanRecord = $this->getLoanRecordForPdf((int)$receipt['LoanDetailId'], $receipt['DNumber']);

        return [
            'receipt' => $receipt,
            'loan' => $loanRecord['loan'] ?? [],
            'customer' => $loanRecord['customer'] ?? [],
            'items' => $loanRecord['items'] ?? []
        ];
    }

    /**
     * Calculate the next monthly due date for a jewel loan using original day anchor
     */
    public function calculateNextMonthlyDueDate($loanDateStr, $loanDetailId = 0) {
        if (empty($loanDateStr) || $loanDateStr === '0000-00-00') {
            return '';
        }
        $paidThroughDateStr = $this->getLoanPaidThroughDate($loanDetailId, $loanDateStr);

        for ($k = 1; $k <= 120; $k++) {
            $dueK = $this->calculateMonthlyInstallmentDueDate($loanDateStr, $k);
            if ($paidThroughDateStr < $dueK) {
                return $dueK;
            }
        }

        return $this->calculateMonthlyInstallmentDueDate($loanDateStr, 1);
    }

    /**
     * Get all jewel loan accounts created till a given date (inclusive)
     */
    public function getJewelLoansCreatedTillDate($tillDate) {
        $results = [];
        $sql = "SELECT d.LoanDetailId, d.DNumber, d.LoanDate, d.InterestRate, 
                       d.LoanAmount, d.BalanceLoanAmount, d.TotalNetWeight,
                       d.PaymentMethod, d.AppraiserCharge, d.ProcessingFee,
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS CustomerName,
                       COALESCE(NULLIF(c.MobileNumber, ''), m.MobileNo, '-') AS MobileNumber,
                       COALESCE(NULLIF(c.AadhaarNumber, ''), m.Aadhaar, '-') AS AadhaarNumber,
                       COALESCE(c.Address, m.PermenantAddress, m.PresentAddress, '-') AS Address
                FROM ast_jewel_loan_details d
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = d.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = d.DNumber
                WHERE d.LoanDate <= ?
                ORDER BY d.LoanDate DESC, d.LoanDetailId DESC";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $tillDate);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $loanDateObj = new DateTime($row['LoanDate']);
                    $row['LoanDateFormatted'] = $loanDateObj->format('d/m/Y');
                    $nextDueDateStr = $this->calculateNextMonthlyDueDate($row['LoanDate'], (int)$row['LoanDetailId']);
                    $row['DueDate'] = $nextDueDateStr;
                    $row['DueDateFormatted'] = !empty($nextDueDateStr) ? date('d/m/Y', strtotime($nextDueDateStr)) : '-';

                    $bal = (float)$row['BalanceLoanAmount'];
                    $hasRelease = false;
                    $checkSql = "SELECT 1 FROM ast_jewel_loan_receipt WHERE LoanDetailId = ? AND TransactionType = 'JEWEL_RELEASE' LIMIT 1";
                    $stCheck = $this->conn->prepare($checkSql);
                    if ($stCheck) {
                        $ldId = (int)$row['LoanDetailId'];
                        $stCheck->bind_param("i", $ldId);
                        $stCheck->execute();
                        $rChk = $stCheck->get_result();
                        if ($rChk && $rChk->num_rows > 0) {
                            $hasRelease = true;
                        }
                        $stCheck->close();
                    }

                    if ($hasRelease || $bal <= 0.00) {
                        $row['Status'] = 'Inactive';
                        $row['StatusBadge'] = '<span class="label label-default" style="font-size:12px;padding:3px 8px;">Inactive</span>';
                    } else {
                        $row['Status'] = 'Active';
                        $row['StatusBadge'] = '<span class="label label-success" style="font-size:12px;padding:3px 8px;">Active</span>';
                    }

                    $results[] = $row;
                }
            }
            $stmt->close();
        }
        return $results;
    }

    /**
     * Get complete transaction / receipt history for a given LoanDetailId
     */
    public function getTransactionHistoryByLoanDetailId($loanDetailId) {
        $txns = [];
        $sql = "SELECT r.*, 
                       DATE_FORMAT(r.ReceiptDate, '%d/%m/%Y') AS FormattedReceiptDate,
                       DATE_FORMAT(r.CreatedAt, '%d/%m/%Y %h:%i %p') AS FormattedCreatedAt
                FROM ast_jewel_loan_receipt r
                WHERE r.LoanDetailId = ?
                ORDER BY r.ReceiptId ASC";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $loanDetailId = (int)$loanDetailId;
            $stmt->bind_param("i", $loanDetailId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $txns[] = $r;
                }
            }
            $stmt->close();
        }
        return $txns;
    }

    public function getAllActiveLoanDNumbers() {
        $sql = "SELECT DISTINCT d.DNumber, d.LoanDetailId, 
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS Name, 
                       d.LoanAmount, d.BalanceLoanAmount 
                FROM ast_jewel_loan_details d
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = d.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = d.DNumber
                ORDER BY d.LoanDetailId DESC";
        $res = $this->conn->query($sql);
        $list = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $list[] = $row;
            }
        }
        return $list;
    }
}
?>