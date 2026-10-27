<?php
class SearchModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllSearch($search_field,$search_value) {

    // Prevent empty query
    if (empty($search_field) || empty($search_value)) {
        return [];
    }

    $val = $this->conn->real_escape_string(trim($search_value));

    // Build SQL searching both registered members and jewel loan accounts
    if ($search_field == 1) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo, '' AS DNumber, 'member' AS RecordSource
                FROM ast_members
                WHERE MemberCode LIKE '%".$val."%'
                UNION
                SELECT 0 AS MemberId, DNumber AS MemberCode, Name AS FullName, AadhaarNumber AS Aadhaar, MobileNumber AS MobileNo, DNumber, 'jewel' AS RecordSource
                FROM ast_jewel_loan_customer
                WHERE DNumber LIKE '%".$val."%'
                ORDER BY FullName ASC";
    }
    else if ($search_field == 2) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo, '' AS DNumber, 'member' AS RecordSource
                FROM ast_members
                WHERE FullName LIKE '%".$val."%'
                UNION
                SELECT 0 AS MemberId, DNumber AS MemberCode, Name AS FullName, AadhaarNumber AS Aadhaar, MobileNumber AS MobileNo, DNumber, 'jewel' AS RecordSource
                FROM ast_jewel_loan_customer
                WHERE Name LIKE '%".$val."%'
                ORDER BY FullName ASC";
    }
    else if ($search_field == 3) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo, '' AS DNumber, 'member' AS RecordSource
                FROM ast_members
                WHERE Aadhaar LIKE '%".$val."%'
                UNION
                SELECT 0 AS MemberId, DNumber AS MemberCode, Name AS FullName, AadhaarNumber AS Aadhaar, MobileNumber AS MobileNo, DNumber, 'jewel' AS RecordSource
                FROM ast_jewel_loan_customer
                WHERE AadhaarNumber LIKE '%".$val."%'
                ORDER BY FullName ASC";
    }
    else if ($search_field == 4) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo, '' AS DNumber, 'member' AS RecordSource
                FROM ast_members
                WHERE MobileNo LIKE '%".$val."%'
                UNION
                SELECT 0 AS MemberId, DNumber AS MemberCode, Name AS FullName, AadhaarNumber AS Aadhaar, MobileNumber AS MobileNo, DNumber, 'jewel' AS RecordSource
                FROM ast_jewel_loan_customer
                WHERE MobileNumber LIKE '%".$val."%'
                ORDER BY FullName ASC";
    }
    else {
        return [];  // invalid field
    }
        // Execute query
    $result = $this->conn->query($sql);

    $searchmember = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $searchmember[] = $row;
        }
    }

    return $searchmember;
}

}
?>
