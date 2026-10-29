<?php
/**
 * Jewel Loan Delete Receipt Model
 * Handles receipt lookup, validation, loan balance reversal, and safe receipt deletion
 * Completely independent to ensure zero conflicts with other modules
 */

class JewelLoanDeleteReceiptModel {
    private $conn;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
    }

    /**
     * Auto-ensure Delete Receipt and Receipt List pages are registered in page & user_privilages tables
     */
    public function ensurePageRegistered() {
        if (!$this->conn) {
            return;
        }

        try {
            $pagesToRegister = [
                [
                    'defaultId' => 2937,
                    'name' => 'Delete Receipt',
                    'link' => 'aster-jewel-loan-delete-receipt.php',
                    'desc' => 'Jewel Loan Delete Receipt',
                    'psmid' => 50,
                    'icon' => 'glyphicon-bookmark'
                ],
                [
                    'defaultId' => 2938,
                    'name' => 'Receipt List',
                    'link' => 'aster-jewel-loan-receipt-list.php',
                    'desc' => 'Jewel Loan Receipt List & Historical Records',
                    'psmid' => 50,
                    'icon' => 'glyphicon-bookmark'
                ]
            ];

            foreach ($pagesToRegister as $pInfo) {
                $check = $this->conn->query("SELECT PageId FROM `page` WHERE `Link` = '{$pInfo['link']}' LIMIT 1");
                if ($check && $check->num_rows > 0) {
                    $row = $check->fetch_assoc();
                    $pageId = (int)$row['PageId'];
                } else {
                    $maxRes = $this->conn->query("SELECT MAX(PageId) as max_id FROM `page`");
                    $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
                    $pageId = max($pInfo['defaultId'], ($maxRow ? (int)$maxRow['max_id'] + 1 : $pInfo['defaultId']));

                    $ins = $this->conn->prepare("INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                                                 VALUES (?, ?, ?, ?, 1, ?, ?)");
                    if ($ins) {
                        $ins->bind_param("isssis", $pageId, $pInfo['name'], $pInfo['link'], $pInfo['desc'], $pInfo['psmid'], $pInfo['icon']);
                        $ins->execute();
                        $ins->close();
                    }
                }

                if ($pageId > 0) {
                    $roles = [1, 2, 3, 4, 5, 8];
                    foreach ($roles as $roleId) {
                        $chkPriv = $this->conn->query("SELECT 1 FROM `user_privilages` WHERE `RoleId` = {$roleId} AND `PageId` = {$pageId} LIMIT 1");
                        if ($chkPriv && $chkPriv->num_rows === 0) {
                            $this->conn->query("INSERT INTO `user_privilages` (`RoleId`, `Date`, `PageId`, `DInsert`, `DEdit`, `DDelete`, `DView`, `UserId`, `IsDeleted`) 
                                                VALUES ({$roleId}, CURDATE(), {$pageId}, 1, 1, 1, 1, 1, 0)");
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Silently ignore registration errors to avoid disrupting runtime
        }
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
                    $results[] = $row;
                }
                $stmt->close();
            }
        } else {
            $res = $this->conn->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
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

            // Calculate projected balance after deletion
            if ($txnType === 'JEWEL_RELEASE') {
                $data['ProjectedBalance'] = $prinPaid > 0 ? $prinPaid : $origLoan;
            } elseif ($txnType === 'INTEREST_PAYMENT') {
                $data['ProjectedBalance'] = min($origLoan, round($currBal + $prinPaid, 2));
            } else {
                $data['ProjectedBalance'] = $currBal;
            }
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
            // 2. Revert loan balance on ast_jewel_loan_details if applicable
            $restoredBalance = $currentBalance;
            if ($txnType === 'JEWEL_RELEASE') {
                $restoredBalance = $projectedBalance;
                $upStmt = $this->conn->prepare("UPDATE ast_jewel_loan_details SET BalanceLoanAmount = ? WHERE LoanDetailId = ?");
                if ($upStmt) {
                    $upStmt->bind_param("di", $restoredBalance, $loanDetailId);
                    if (!$upStmt->execute()) {
                        throw new Exception("Failed to restore loan balance on release reversal: " . $upStmt->error);
                    }
                    $upStmt->close();
                }
            } elseif ($txnType === 'INTEREST_PAYMENT' && $principalPaid > 0) {
                $restoredBalance = $projectedBalance;
                $upStmt = $this->conn->prepare("UPDATE ast_jewel_loan_details SET BalanceLoanAmount = ? WHERE LoanDetailId = ?");
                if ($upStmt) {
                    $upStmt->bind_param("di", $restoredBalance, $loanDetailId);
                    if (!$upStmt->execute()) {
                        throw new Exception("Failed to restore loan balance on payment reversal: " . $upStmt->error);
                    }
                    $upStmt->close();
                }
            }

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
