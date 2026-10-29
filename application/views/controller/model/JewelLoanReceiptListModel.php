<?php
/**
 * Jewel Loan Receipt List Model
 * Queries historical receipt records with comprehensive filtering and summary statistics
 */

class JewelLoanReceiptListModel {
    private $conn;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
    }

    /**
     * Auto-ensure the Receipt List page is registered in page & user_privilages tables
     */
    public function ensurePageRegistered() {
        if (!$this->conn) {
            return;
        }

        try {
            $check = $this->conn->query("SELECT PageId FROM `page` WHERE `Link` = 'aster-jewel-loan-receipt-list.php' LIMIT 1");
            if ($check && $check->num_rows > 0) {
                $row = $check->fetch_assoc();
                $pageId = (int)$row['PageId'];
            } else {
                $maxRes = $this->conn->query("SELECT MAX(PageId) as max_id FROM `page`");
                $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
                $pageId = max(2938, ($maxRow ? (int)$maxRow['max_id'] + 1 : 2938));

                $ins = $this->conn->prepare("INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                                             VALUES (?, 'Receipt List', 'aster-jewel-loan-receipt-list.php', 'Jewel Loan Receipt List & Historical Records', 1, 50, 'glyphicon-bookmark')");
                if ($ins) {
                    $ins->bind_param("i", $pageId);
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
        } catch (Exception $e) {
            // Silently ignore to avoid runtime disruptions
        }
    }

    /**
     * Build WHERE conditions based on provided filters
     */
    private function buildWhereClause($filters, &$params, &$types) {
        $conditions = ["1=1"];

        if (!empty($filters['query'])) {
            $term = "%" . trim($filters['query']) . "%";
            $conditions[] = "(r.ReceiptNo LIKE ? OR r.DNumber LIKE ? OR c.Name LIKE ? OR m.FullName LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $types .= "ssss";
        }

        if (!empty($filters['receipt_no'])) {
            $conditions[] = "r.ReceiptNo = ?";
            $params[] = trim($filters['receipt_no']);
            $types .= "s";
        }

        if (!empty($filters['d_number'])) {
            $conditions[] = "r.DNumber = ?";
            $params[] = trim($filters['d_number']);
            $types .= "s";
        }

        if (!empty($filters['transaction_type'])) {
            $conditions[] = "r.TransactionType = ?";
            $params[] = trim($filters['transaction_type']);
            $types .= "s";
        }

        if (!empty($filters['payment_method'])) {
            $conditions[] = "r.PaymentMethod = ?";
            $params[] = trim($filters['payment_method']);
            $types .= "s";
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "r.ReceiptDate >= ?";
            $params[] = trim($filters['date_from']);
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "r.ReceiptDate <= ?";
            $params[] = trim($filters['date_to']);
            $types .= "s";
        }

        return implode(" AND ", $conditions);
    }

    /**
     * Retrieve receipts based on filter settings (or all receipts by default)
     */
    public function getReceipts($filters = []) {
        $results = [];
        if (!$this->conn) {
            return $results;
        }

        $params = [];
        $types = "";
        $whereClause = $this->buildWhereClause($filters, $params, $types);

        $sql = "SELECT r.*, 
                       d.LoanDate, d.LoanAmount, d.BalanceLoanAmount AS CurrentLoanBalance, d.InterestRate,
                       COALESCE(NULLIF(c.Name, ''), m.FullName, 'Customer') AS CustomerName,
                       COALESCE(NULLIF(c.MobileNumber, ''), m.MobileNo, '-') AS MobileNumber,
                       COALESCE(NULLIF(c.AadhaarNumber, ''), m.Aadhaar, '-') AS AadhaarNumber
                FROM ast_jewel_loan_receipt r
                LEFT JOIN ast_jewel_loan_details d ON r.LoanDetailId = d.LoanDetailId
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = r.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = r.DNumber
                WHERE {$whereClause}
                ORDER BY r.ReceiptDate DESC, r.ReceiptId DESC";

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
     * Calculate summary statistics for filtered receipts
     */
    public function getSummaryStats($filters = []) {
        $stats = [
            'totalCount' => 0,
            'totalPaid' => 0.00,
            'totalPrincipal' => 0.00,
            'totalInterest' => 0.00
        ];

        if (!$this->conn) {
            return $stats;
        }

        $params = [];
        $types = "";
        $whereClause = $this->buildWhereClause($filters, $params, $types);

        $sql = "SELECT COUNT(*) AS totalCount,
                       COALESCE(SUM(r.TotalPaid), 0) AS totalPaid,
                       COALESCE(SUM(r.PrincipalPaid), 0) AS totalPrincipal,
                       COALESCE(SUM(r.InterestPaid), 0) AS totalInterest
                FROM ast_jewel_loan_receipt r
                LEFT JOIN ast_jewel_loan_details d ON r.LoanDetailId = d.LoanDetailId
                LEFT JOIN ast_jewel_loan_customer c ON c.DNumber = r.DNumber
                LEFT JOIN ast_members m ON m.MemberCode = r.DNumber
                WHERE {$whereClause}";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
                $stats = $res ? $res->fetch_assoc() : $stats;
                $stmt->close();
            }
        } else {
            $res = $this->conn->query($sql);
            if ($res) {
                $stats = $res->fetch_assoc();
            }
        }

        $stats['totalCount'] = (int)($stats['totalCount'] ?? 0);
        $stats['totalPaid'] = (float)($stats['totalPaid'] ?? 0.00);
        $stats['totalPrincipal'] = (float)($stats['totalPrincipal'] ?? 0.00);
        $stats['totalInterest'] = (float)($stats['totalInterest'] ?? 0.00);

        return $stats;
    }
}

