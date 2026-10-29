<?php
/**
 * Jewel Loan Delete Receipt Model
 * Handles receipt lookup, validation, loan balance reversal, and safe receipt deletion
 * Completely independent to ensure zero conflicts with other modules
 */

require_once __DIR__ . "/JewelLoanSidebarSync.php";

class JewelLoanDeleteReceiptModel {
    private $conn;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
    }

    /**
     * Auto-ensure Jewel Loan sidebar tabs, order, and purposeful icons are synchronized
     */
    public function ensurePageRegistered() {
        if (!$this->conn) {
            return;
        }
        JewelLoanSidebarSync::sync($this->conn);
    }

    /**
     * Search receipts strictly restricted to the current date (today)
     * Filters by Receipt Number or D-Number if provided
     */
    public function searchReceipts($receiptId = 0, $searchTerm = '') {
        $results = [];
        if (!$this->conn) {
            return $results;
        }

        $today = date('Y-m-d');
        $conditions = ["r.ReceiptDate = ?"];
        $params = [$today];
        $types = "s";

        if ((int)$receiptId > 0) {
            $conditions[] = "r.ReceiptId = ?";
            $params[] = (int)$receiptId;
            $types .= "i";
        } elseif (!empty($searchTerm)) {
            $term = "%" . trim($searchTerm) . "%";
            $conditions[] = "(r.ReceiptNo LIKE ? OR r.DNumber LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $types .= "ss";
        }

        $whereClause = implode(" AND ", $conditions);

        $sql = "SELECT r.*, 
                       d.LoanDate, d.LoanAmount, d.BalanceLoanAmount AS CurrentLoanBalance, d.InterestRate,
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS CustomerName,
                       COALESCE(NULLIF(c.MobileNumber, ''), m.MobileNo, '-') AS MobileNumber,
                       COALESCE(NULLIF(c.AadhaarNumber, ''), m.Aadhaar, '-') AS AadhaarNumber,
                       COALESCE(NULLIF(c.Address, ''), m.PermenantAddress, m.PresentAddress, '-') AS Address
                FROM ast_jewel_loan_receipt r
                LEFT JOIN ast_jewel_loan_details d ON r.LoanDetailId = d.LoanDetailId
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = r.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = r.DNumber
                WHERE {$whereClause}
                ORDER BY r.ReceiptId DESC
                LIMIT 100";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $currBal = (float)$row['BalanceLoanAmount'];
                    $prinPaid = (float)$row['PrincipalPaid'];
                    $row['BalanceBefore'] = ($row['TransactionType'] === 'LOAN_CREATION') ? $currBal : ($currBal + $prinPaid);
                    $results[] = $row;
                }
                $stmt->close();
            }
        } else {
            $res = $this->conn->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $currBal = (float)$row['BalanceLoanAmount'];
                    $prinPaid = (float)$row['PrincipalPaid'];
                    $row['BalanceBefore'] = ($row['TransactionType'] === 'LOAN_CREATION') ? $currBal : ($currBal + $prinPaid);
                    $results[] = $row;
                }
            }
        }

        return $results;
    }

    /**
     * Get specific receipt with full details and projected balance restoration
     */
    public function getReceiptForDelete($receiptId) {
        $receiptId = (int)$receiptId;
        if ($receiptId <= 0 || !$this->conn) {
            return null;
        }

        $sql = "SELECT r.*, 
                       d.LoanDate, d.LoanAmount, d.BalanceLoanAmount AS CurrentLoanBalance, d.InterestRate,
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS CustomerName,
                       COALESCE(NULLIF(c.MobileNumber, ''), m.MobileNo, '-') AS MobileNumber,
                       COALESCE(NULLIF(c.AadhaarNumber, ''), m.Aadhaar, '-') AS AadhaarNumber,
                       COALESCE(NULLIF(c.Address, ''), m.PermenantAddress, m.PresentAddress, '-') AS Address
                FROM ast_jewel_loan_receipt r
                LEFT JOIN ast_jewel_loan_details d ON r.LoanDetailId = d.LoanDetailId
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = r.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = r.DNumber
                WHERE r.ReceiptId = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($data) {
            $currBal = (float)$data['CurrentLoanBalance'];
            $origLoan = (float)$data['LoanAmount'];
            $prinPaid = (float)$data['PrincipalPaid'];
            $txnType = $data['TransactionType'];

            // Calculate exact restored balance from previous receipt state
            $prevBalSql = "SELECT BalanceLoanAmount FROM `ast_jewel_loan_receipt` WHERE `LoanDetailId` = ? AND `ReceiptId` != ? ORDER BY `ReceiptId` DESC LIMIT 1";
            $pbStmt = $this->conn->prepare($prevBalSql);
            $projBal = $origLoan;
            if ($pbStmt) {
                $ldId = (int)$data['LoanDetailId'];
                $pbStmt->bind_param("ii", $ldId, $receiptId);
                $pbStmt->execute();
                $pbRes = $pbStmt->get_result();
                if ($pbRes && $pbRow = $pbRes->fetch_assoc()) {
                    $projBal = (float)$pbRow['BalanceLoanAmount'];
                }
                $pbStmt->close();
            }
            $data['ProjectedBalance'] = $projBal;
            $data['BalanceBefore'] = ($txnType === 'LOAN_CREATION') ? (float)$data['BalanceLoanAmount'] : ((float)$data['BalanceLoanAmount'] + $prinPaid);
        }

        return $data;
    }

    /**
     * Fetch receipts list for dropdown selection - strictly filtered to today's date only
     */
    public function getAllReceiptsDropdown($limit = 100) {
        $list = [];
        if (!$this->conn) {
            return $list;
        }

        $sql = "SELECT r.ReceiptId, r.ReceiptNo, r.ReceiptDate, r.DNumber, r.TransactionType, r.TotalPaid,
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS CustomerName
                FROM ast_jewel_loan_receipt r
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = r.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = r.DNumber
                WHERE r.ReceiptDate = CURDATE()
                ORDER BY r.ReceiptId DESC
                LIMIT " . (int)$limit;

        $res = $this->conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $list[] = $row;
            }
        }

        return $list;
    }

    /**
     * Safely delete a receipt and reverse its financial impact
     * Strictly restricted: ONLY receipts generated on the current day can be deleted
     *
     * @param int $receiptId
     * @param bool $forceDeleteLoanCreation
     * @return array ['status' => bool, 'message' => string, 'restoredBalance' => float, 'receiptNo' => string]
     */
    public function deleteReceipt($receiptId, $forceDeleteLoanCreation = false) {
        $receiptId = (int)$receiptId;
        if ($receiptId <= 0 || !$this->conn) {
            return ['status' => false, 'message' => 'Invalid receipt ID provided.'];
        }

        // 1. Fetch receipt details
        $receipt = $this->getReceiptForDelete($receiptId);
        if (!$receipt) {
            return ['status' => false, 'message' => 'Receipt record not found. It may have already been deleted.'];
        }

        $receiptNo = $receipt['ReceiptNo'];
        $receiptDate = $receipt['ReceiptDate'];
        $today = date('Y-m-d');

        // Strictly enforce: only receipts created on that day (today) can be deleted - no other date can be deleted
        if ($receiptDate !== $today) {
            return [
                'status' => false,
                'message' => "Deletion strictly denied: Under system policy, only receipts generated on the current day (" . date('d/m/Y') . ") can be deleted. "
                           . "Receipt '{$receiptNo}' was generated on " . date('d/m/Y', strtotime($receiptDate)) . " and cannot be deleted."
            ];
        }

        $dNumber = $receipt['DNumber'];
        $loanDetailId = (int)$receipt['LoanDetailId'];
        $txnType = $receipt['TransactionType'];
        $principalPaid = (float)$receipt['PrincipalPaid'];
        $currentBalance = (float)$receipt['CurrentLoanBalance'];
        $origLoanAmount = (float)$receipt['LoanAmount'];
        $projectedBalance = (float)$receipt['ProjectedBalance'];

        // Guard against accidental LOAN_CREATION deletion
        if ($txnType === 'LOAN_CREATION' && !$forceDeleteLoanCreation) {
            return [
                'status' => false,
                'message' => "Receipt '{$receiptNo}' is the initial Loan Creation disbursement receipt. "
                           . "Deleting the loan creation receipt would invalidate the loan inception. "
                           . "To close or remove the entire loan account, please use the Delete Account feature."
            ];
        }

        // Begin Transaction
        $this->conn->begin_transaction();
        try {
            // 2. Revert loan balance on ast_jewel_loan_details to exact previous receipt state
            $prevBalSql = "SELECT BalanceLoanAmount FROM `ast_jewel_loan_receipt` WHERE `LoanDetailId` = ? AND `ReceiptId` != ? ORDER BY `ReceiptId` DESC LIMIT 1";
            $pbStmt = $this->conn->prepare($prevBalSql);
            $restoredBalance = $origLoanAmount;
            if ($pbStmt) {
                $pbStmt->bind_param("ii", $loanDetailId, $receiptId);
                $pbStmt->execute();
                $pbRes = $pbStmt->get_result();
                if ($pbRes && $pbRow = $pbRes->fetch_assoc()) {
                    $restoredBalance = (float)$pbRow['BalanceLoanAmount'];
                }
                $pbStmt->close();
            }

            $upStmt = $this->conn->prepare("UPDATE `ast_jewel_loan_details` SET `BalanceLoanAmount` = ? WHERE `LoanDetailId` = ?");
            if ($upStmt) {
                $upStmt->bind_param("di", $restoredBalance, $loanDetailId);
                if (!$upStmt->execute()) {
                    throw new Exception("Failed to restore loan balance in ast_jewel_loan_details: " . $upStmt->error);
                }
                $upStmt->close();            }

            // 3. Clean up rate history if table exists (graceful check so no error if table not yet integrated)
            $checkTable = $this->conn->query("SHOW TABLES LIKE 'ast_jewel_loan_rate_history'");
            if ($checkTable && $checkTable->num_rows > 0) {
                $histStmt = $this->conn->prepare("DELETE FROM `ast_jewel_loan_rate_history` WHERE `ReceiptId` = ?");
                if ($histStmt) {
                    $histStmt->bind_param("i", $receiptId);
                    $histStmt->execute();
                    $histStmt->close();
                }
            }

            // 4. Delete the receipt record
            $delStmt = $this->conn->prepare("DELETE FROM `ast_jewel_loan_receipt` WHERE `ReceiptId` = ?");
            if (!$delStmt) {
                throw new Exception("Failed to prepare receipt deletion: " . $this->conn->error);
            }
            $delStmt->bind_param("i", $receiptId);
            if (!$delStmt->execute()) {
                throw new Exception("Failed to delete receipt: " . $delStmt->error);
            }
            $delStmt->close();

            // Commit Transaction
            $this->conn->commit();

            return [
                'status' => true,
                'message' => "Receipt <strong>{$receiptNo}</strong> (D-Number: <strong>{$dNumber}</strong>) deleted successfully."
                           . ($principalPaid > 0 ? " Loan balance has been restored to <strong>₹" . number_format($restoredBalance, 2) . "</strong>." : ""),
                'receiptNo' => $receiptNo,
                'dNumber' => $dNumber,
                'restoredBalance' => $restoredBalance
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'status' => false,
                'message' => "Transaction failed: " . $e->getMessage()
            ];
        }
    }
}
