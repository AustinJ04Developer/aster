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

    // Build SQL 
    if ($search_field == 1) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo
                FROM ast_members
                WHERE MemberCode LIKE '%".$this->conn->real_escape_string($search_value)."%'
                ORDER BY MemberId";
    }
    else if ($search_field == 2) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo
                FROM ast_members
                WHERE FullName LIKE '%".$search_value."%'
                ORDER BY MemberId";
    }
    else if ($search_field == 3) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo
                FROM ast_members
                WHERE Aadhaar = '".$search_value."'
                ORDER BY MemberId";
    }
    else if ($search_field == 4) {
        $sql = "SELECT MemberId, MemberCode, FullName, Aadhaar, MobileNo
                FROM ast_members
                WHERE MobileNo = '".$search_value."'
                ORDER BY MemberId";
    }
    else {
        return [];  // invalid field
    }
        // Execute query
    $result = $this->conn->query($sql);

    $searchmember = [];
    while ($row = $result->fetch_assoc()) {
        $searchmember[] = $row;
    }

    return $searchmember;
}

}
?>
