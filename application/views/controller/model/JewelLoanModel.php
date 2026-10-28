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
        // If purely numeric e.g. "1" or "15" -> "D-0001" or "D-0015"
        if (is_numeric($d)) {
            return 'D-' . str_pad((string)(int)$d, 4, '0', STR_PAD_LEFT);
        }
        // If starts with d or D followed by any spaces, hyphens, or underscores and numbers e.g. "d-1", "D 15", "d0001"
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
     * Guaranteed unique, sequential, and never re-using any existing/previously available D number.
     */
    public function getNextDNumber() {
        $prefix = "D-";
        $this->conn->begin_transaction();
        try {
            // Lock the sequence row for update
            $seqRes = $this->conn->query("SELECT current_val FROM ast_jewel_loan_sequence WHERE seq_name = 'd_number' FOR UPDATE");
            $seqVal = 0;
            if ($seqRes && $row = $seqRes->fetch_assoc()) {
                $seqVal = (int)$row['current_val'];
            }

            // Also check actual max across tables to ensure sequence is never behind existing records
            $maxExisting = $this->getMaxExistingDNumber();
            $baseVal = max($seqVal, $maxExisting);

            $candidateVal = $baseVal + 1;
            $candidateD = $prefix . str_pad((string)$candidateVal, 4, '0', STR_PAD_LEFT);

            // Double check that candidate is not taken anywhere
            while ($this->isDNumberTakenAnywhere($candidateD)) {
                $candidateVal++;
                $candidateD = $prefix . str_pad((string)$candidateVal, 4, '0', STR_PAD_LEFT);
            }

            // Update sequence table to candidateVal
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
     * Never returns a previously available or existing D number.
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
     */
    public function getAppraiserCharge($amount) {
        $amount = (float)$amount;
        $sql = "SELECT AppraiserCharge FROM ast_gl_appraiser 
                WHERE AmountFrom <= ? AND AmountTo >= ? 
                ORDER BY AmountFrom DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("dd", $amount, $amount);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $stmt->close();
                return (float)$row['AppraiserCharge'];
            }
            $stmt->close();
        }
        return 0.00;
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
        return 0.00;
    }

    /**
     * Get scheme AmountTo for a given interest percentage (18, 24, or 30)
     * Returns the most recently saved scheme's AmountTo for the given rate.
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

        // Always check ast_members to enrich or provide fallback customer data
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
            $receiptNo = $this->getNextReceiptNumber();
            $sqlReceipt = "INSERT INTO ast_jewel_loan_receipt (
                ReceiptNo, ReceiptDate, LoanDetailId, DNumber,
                TransactionType, PaymentMethod, PrincipalPaid, InterestPaid,
                AppraiserCharge, ProcessingFee, TotalPaid, BalanceLoanAmount,
                ChequeNumber, ChequeFromBank, ChequeToBank,
                RtgsNumber, RtgsFromBank, RtgsToBank,
                UpiType, UpiTransactionNumber, UpiToBank,
                Remarks, CreatedBy
            ) VALUES (?, ?, ?, ?, 'LOAN_CREATION', ?, ?, 0.00, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stR = $this->conn->prepare($sqlReceipt);
            if ($stR) {
                $rDate = $loanData['LoanDate'];
                $rPayMethod = $loanData['PaymentMethod'];
                $rPrin = (float)$loanData['LoanAmount'];
                $rAppr = (float)$loanData['AppraiserCharge'];
                $rPF = (float)$loanData['ProcessingFee'];
                $rTot = (float)$loanData['LoanAmount'];
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
                    "ssissdddddssssssssssi",
                    $receiptNo,
                    $rDate,
                    $loanDetailId,
                    $dNumber,
                    $rPayMethod,
                    $rPrin,
                    $rAppr,
                    $rPF,
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
     * Update existing jewel loan record (Customer + Loan Details + Gold Items) atomically
     */
    public function updateJewelLoan($loanDetailId, $customerData, $loanData = null, $itemsData = []) {
        $this->conn->begin_transaction();
        try {
            $dNumber = $customerData['DNumber'];

            // 1. Update customer record
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

            // 2. If loan details provided and loanDetailId > 0, update loan details
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

                // 3. Update gold items if provided
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
     * Primary lookup is by LoanDetailId (auto-increment id), fallback by DNumber.
     */
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

            // Fallback: search by join if DNumber format varied
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

        // Fetch gold items
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
     * Fetch complete details of a loan including receipt history
     */
    public function getLoanReceiptSummary($loanDetailId = 0, $dNumber = '') {
        $loanRecord = $this->getLoanRecordForPdf($loanDetailId, $dNumber);
        if (!$loanRecord || empty($loanRecord['loan'])) {
            return null;
        }

        $ldId = (int)$loanRecord['loan']['LoanDetailId'];

        // Fetch all receipt history
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

        // Calculate interest accrued from last receipt date or loan date to today
        $loan = $loanRecord['loan'];
        $loanDateStr = $loan['LoanDate'];
        $lastDateStr = $loanDateStr;

        $today = new DateTime();

        // Check if there was a previous interest payment
        $interestAlreadyPaidToDate = false;
        if (!empty($receipts)) {
            foreach ($receipts as $r) {
                if ($r['TransactionType'] === 'INTEREST_PAYMENT' && ((float)$r['InterestPaid'] > 0 || !empty($r['InterestPeriodTo']))) {
                    $lastDateStr = !empty($r['InterestPeriodTo']) ? $r['InterestPeriodTo'] : $r['ReceiptDate'];
                    $lastPeriodDate = new DateTime($lastDateStr);
                    if ($lastPeriodDate->format('Y-m-d') >= $today->format('Y-m-d')) {
                        $interestAlreadyPaidToDate = true;
                    }
                    break;
                }
            }
        }

        $originalLoanDate = new DateTime($loanDateStr);
        $calcStartDate    = new DateTime($lastDateStr);

        // 1. Contractual Closing Due Date: 12 calendar months (or 1 year) from original pledge date
        $closingDueDate = clone $originalLoanDate;
        $origDay = (int)$originalLoanDate->format('d');
        $closingDueDate->modify('+1 year');
        // Handle leap year & month-end normalization (e.g. Feb 29 -> Feb 28 on non-leap year)
        if ((int)$closingDueDate->format('d') !== $origDay) {
            $closingDueDate->modify('last day of previous month');
        }
        $closingDueDateStr = $closingDueDate->format('Y-m-d');
        $dueDateStr = $closingDueDateStr; // Backwards compatible key

        // 2. Next Monthly Interest Due Date:
        // Calculated on calendar month basis from the last interest-covered date (or original loan date)
        $monthlyDueDate = clone $calcStartDate;
        $calcStartDay = (int)$calcStartDate->format('d');
        $monthlyDueDate->modify('+1 month');
        if ((int)$monthlyDueDate->format('d') !== $calcStartDay) {
            $monthlyDueDate->modify('last day of previous month');
        }
        // If monthly due date already in past, advance until >= today or closing due date
        while ($monthlyDueDate < $today && $monthlyDueDate < $closingDueDate) {
            $prevMDay = (int)$monthlyDueDate->format('d');
            $monthlyDueDate->modify('+1 month');
            if ((int)$monthlyDueDate->format('d') !== $origDay) {
                $monthlyDueDate->modify('last day of previous month');
            }
        }
        $monthlyDueDateStr = $monthlyDueDate->format('Y-m-d');

        // Total Days elapsed since loan creation
        $totDaysElapsed = $originalLoanDate->diff($today)->days;
        if ($today < $originalLoanDate) {
            $totDaysElapsed = 0;
        }

        // Days for interest calculation since last payment/creation
        $interestDays = $calcStartDate->diff($today)->days;
        if ($today < $calcStartDate) {
            $interestDays = 0;
        }

        // Grace period days (standard 7 days after due date)
        $gracePeriodDays = 0;
        $overdueDays = 0;
        if ($today > $closingDueDate) {
            $overdueDays = $closingDueDate->diff($today)->days;
            $gracePeriodDays = 7;
        }

        // Clear, meaningful Days Information replacing the ambiguous label:
        // E.g. "9 Days Elapsed (Active / Regular)" or "380 Days (15 Days Overdue)"
        if ($overdueDays > 0) {
            $daysStatusLabel = "{$overdueDays} Days Overdue";
            $totDaysDisplay = "{$totDaysElapsed} Days ({$overdueDays} Days Overdue)";
        } else {
            $daysLeft = $today->diff($closingDueDate)->days;
            $daysStatusLabel = "Active ({$daysLeft} Days to Maturity)";
            $totDaysDisplay = "{$totDaysElapsed} Days ({$daysLeft} Days Remaining)";
        }

        $hasReleaseReceipt = false;
        if (!empty($receipts)) {
            foreach ($receipts as $r) {
                if ($r['TransactionType'] === 'JEWEL_RELEASE') {
                    $hasReleaseReceipt = true;
                    break;
                }
            }
        }
        if ($hasReleaseReceipt) {
            $principalBalance = 0.00;
        } else {
            $principalBalance = isset($loan['BalanceLoanAmount']) && $loan['BalanceLoanAmount'] !== null
                ? (float)$loan['BalanceLoanAmount']
                : (float)($loan['LoanAmount'] ?? 0);
            if ($principalBalance <= 0 && empty($receipts)) {
                $principalBalance = (float)($loan['LoanAmount'] ?? 0);
            }
        }
        $rate = (float)($loan['InterestRate'] ?? 0);

        // Interest calculation rules:
        if ($interestAlreadyPaidToDate || $interestDays <= 0) {
            $effectiveDays = 0;
            $months = 0;
            $calculatedInterest = 0.00;
        } else {
            // Policy: minimum 15 days interest. Released within 15 days -> 6% interest rate.
            $effectiveDays = $interestDays;
            $isUnder15Days = ($effectiveDays <= 15 && $totDaysElapsed <= 15);
            if ($effectiveDays < 15 && $effectiveDays > 0) {
                $effectiveDays = 15;
            }

            // Daily interest: (Principal * Rate / 100) / 365 * days
            // Or Monthly interest: (Principal * Rate / 100 / 12) * (days / 30)
            // Standard Nidhi / Banking monthly convention:
            $months = round($effectiveDays / 30.4167, 2);
            if ($months < 0.5 && $effectiveDays >= 15) {
                $months = 0.5;
            }

            if ($isUnder15Days && $effectiveDays <= 15) {
                // 6% annual rate for quick release within 15 days
                $calculatedInterest = round(($principalBalance * (6.0 / 100) / 365) * ($effectiveDays > 0 ? $effectiveDays : 15), 2);
            } else {
                // Standard rate: (Principal * (Rate / 100) / 12) * months
                $calculatedInterest = round(($principalBalance * ($rate / 100) / 12) * ($months > 0 ? $months : 1), 2);
            }
        }

        // Default interest: 2% p.a. default interest on overdue principal after due date + grace period
        $defaultInterest = 0.00;
        if ($overdueDays > $gracePeriodDays) {
            $defDays = $overdueDays - $gracePeriodDays;
            $defaultInterest = round(($principalBalance * (2.0 / 100) / 365) * $defDays, 2);
        }

        // Total amount due = Principal Balance + Interest + Default Interest
        $totalAmountDue = round($principalBalance + $calculatedInterest + $defaultInterest, 2);

        // Fetch customer personal details enriched from ast_members if available
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

        return [
            'loan' => $loanRecord['loan'],
            'customer' => $customer,
            'items' => $loanRecord['items'],
            'receipts' => $receipts,
            'calculation' => [
                'principalBalance' => $principalBalance,
                'interestRate' => $rate,
                'dueDate' => $dueDateStr,
                'closingDueDate' => $closingDueDateStr,
                'monthlyDueDate' => $monthlyDueDateStr,
                'lastCalculatedDate' => $lastDateStr,
                'calculatedDays' => $interestDays,
                'calculatedMonths' => $months > 0 ? $months : 1,
                'totDaysElapsed' => $totDaysElapsed,
                'totDaysDisplay' => $totDaysDisplay,
                'daysStatusLabel' => $daysStatusLabel,
                'gracePeriodDays' => $gracePeriodDays,
                'overdueDays' => $overdueDays,
                'accruedInterest' => $calculatedInterest,
                'defaultInterest' => $defaultInterest,
                'totalAmountDue' => $totalAmountDue,
                'totalClosureAmount' => $totalAmountDue
            ]
        ];
    }

    /**
     * Public standalone interest and due date calculation for loans
     */
    public function calculateInterestDue($loanDateStr, $principalBalance, $rate, $asOfDateStr = null, $lastPaymentDateStr = null) {
        $originalLoanDate = new DateTime($loanDateStr);
        $lastDateStr      = !empty($lastPaymentDateStr) ? $lastPaymentDateStr : $loanDateStr;
        $calcStartDate    = new DateTime($lastDateStr);
        $today            = !empty($asOfDateStr) ? new DateTime($asOfDateStr) : new DateTime();

        // 1. Contractual Closing Due Date: 12 calendar months (or 1 year) from original pledge date
        $closingDueDate = clone $originalLoanDate;
        $origDay = (int)$originalLoanDate->format('d');
        $closingDueDate->modify('+1 year');
        if ((int)$closingDueDate->format('d') !== $origDay) {
            $closingDueDate->modify('last day of previous month');
        }
        $closingDueDateStr = $closingDueDate->format('Y-m-d');

        // 2. Next Monthly Interest Due Date:
        $monthlyDueDate = clone $calcStartDate;
        $calcStartDay = (int)$calcStartDate->format('d');
        $monthlyDueDate->modify('+1 month');
        if ((int)$monthlyDueDate->format('d') !== $calcStartDay) {
            $monthlyDueDate->modify('last day of previous month');
        }
        while ($monthlyDueDate < $today && $monthlyDueDate < $closingDueDate) {
            $monthlyDueDate->modify('+1 month');
            if ((int)$monthlyDueDate->format('d') !== $origDay) {
                $monthlyDueDate->modify('last day of previous month');
            }
        }
        $monthlyDueDateStr = $monthlyDueDate->format('Y-m-d');

        // Total Days elapsed
        $totDaysElapsed = $originalLoanDate->diff($today)->days;
        if ($today < $originalLoanDate) {
            $totDaysElapsed = 0;
        }

        $interestDays = $calcStartDate->diff($today)->days;
        if ($today < $calcStartDate) {
            $interestDays = 0;
        }

        $gracePeriodDays = 0;
        $overdueDays = 0;
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

        $effectiveDays = $interestDays;
        $isUnder15Days = ($effectiveDays <= 15 && $totDaysElapsed <= 15);
        if ($effectiveDays < 15 && $effectiveDays > 0) {
            $effectiveDays = 15;
        }

        $months = round($effectiveDays / 30.4167, 2);
        if ($months < 0.5 && $effectiveDays >= 15) {
            $months = 0.5;
        }

        if ($isUnder15Days && $effectiveDays <= 15) {
            $calculatedInterest = round(($principalBalance * (6.0 / 100) / 365) * ($effectiveDays > 0 ? $effectiveDays : 15), 2);
        } else {
            $calculatedInterest = round(($principalBalance * ($rate / 100) / 12) * ($months > 0 ? $months : 1), 2);
        }

        $defaultInterest = 0.00;
        if ($overdueDays > $gracePeriodDays) {
            $defDays = $overdueDays - $gracePeriodDays;
            $defaultInterest = round(($principalBalance * (2.0 / 100) / 365) * $defDays, 2);
        }

        $totalAmountDue = round($principalBalance + $calculatedInterest + $defaultInterest, 2);

        return [
            'principalBalance' => $principalBalance,
            'interestRate' => $rate,
            'closingDueDate' => $closingDueDateStr,
            'monthlyDueDate' => $monthlyDueDateStr,
            'calculatedDays' => $interestDays,
            'calculatedMonths' => $months > 0 ? $months : 1,
            'totDaysElapsed' => $totDaysElapsed,
            'totDaysDisplay' => $totDaysDisplay,
            'daysStatusLabel' => $daysStatusLabel,
            'gracePeriodDays' => $gracePeriodDays,
            'overdueDays' => $overdueDays,
            'totalDays' => $totDaysElapsed,
            'accruedInterest' => $calculatedInterest,
            'defaultInterest' => $defaultInterest,
            'totalAmountDue' => $totalAmountDue
        ];
    }

    /**
     * Save a new Receipt transaction (LOAN_CREATION, INTEREST_PAYMENT, JEWEL_RELEASE)
     * Primary linkage to loan is LoanDetailId (internal auto-increment primary key).
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
                Remarks, CreatedBy
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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

            $stmt->bind_param(
                "ssisssddddddddssssssssssssi",
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
                $createdBy
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute receipt failed: " . $stmt->error);
            }
            $receiptId = $this->conn->insert_id;
            $stmt->close();

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

        // Fetch loan record & customer using internal primary key LoanDetailId
        $loanRecord = $this->getLoanRecordForPdf((int)$receipt['LoanDetailId'], $receipt['DNumber']);

        return [
            'receipt' => $receipt,
            'loan' => $loanRecord['loan'] ?? [],
            'customer' => $loanRecord['customer'] ?? [],
            'items' => $loanRecord['items'] ?? []
        ];
    }

    /**
     * Get all active loan D-Numbers for receipt search autocomplete / dropdown
     */
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
