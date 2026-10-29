<?php
/**
 * Jewel Loan Receipt List Model
 * Queries historical receipt records with comprehensive filtering and summary statistics
 */

require_once __DIR__ . "/JewelLoanSidebarSync.php";

class JewelLoanReceiptListModel {
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

