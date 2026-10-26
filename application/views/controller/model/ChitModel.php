<?php
class ChitModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }

    public function getAllChit() {
        $sql = "SELECT CHID,UserId,MonthNo,MonthlyAmount,ChitAmount FROM ast_master_chit ORDER BY CHID";
        $result = $this->conn->query($sql);
        $chits = [];
        while ($row = $result->fetch_assoc()) {
            $chits[] = $row;
        }
        return $chits;
    }

    public function getChitById($chid) {
        $sql = "SELECT CHID,UserId,MonthNo,MonthlyAmount,ChitAmount FROM ast_master_chit WHERE CHID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $chid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addChit($userid,$monthno,$monthlyamount,$chitamount) {
        $sql = "INSERT INTO ast_master_chit (UserId,MonthNo,MonthlyAmount,ChitAmount) VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $userid,$monthno,$monthlyamount,$chitamount);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateChit($chid,$userid,$monthno,$monthlyamount,$chitamount) {
	    $sql = "UPDATE ast_master_chit SET UserId = ?, MonthNo = ?,MonthlyAmount = ?,ChitAmount = ? WHERE CHID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiii",$userid,$monthno,$monthlyamount,$chitamount,$chid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteChit($chid) {
        $sql = "DELETE FROM ast_master_chit WHERE CHID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $chid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>