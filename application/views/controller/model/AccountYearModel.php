<?php
class AccountYearModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }

    public function getAllAccountYear() {
        $sql = "SELECT AYID ,AccountYear,IsDefault FROM ast_account_year ORDER BY AYID";
        $result = $this->conn->query($sql);
        $accountyears = [];
        while ($row = $result->fetch_assoc()) {
            $accountyears[] = $row;
        }
        return $accountyears;
    }

    public function getAccountYearById($ayid) {
        $sql = "SELECT AYID ,AccountYear,IsDefault FROM ast_account_year WHERE AYID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ayid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addAccountYear($accountyear,$is_default) {
        $sql = "INSERT INTO ast_account_year (AccountYear,IsDefault) VALUES (?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $accountyear,$is_default);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function updateAccountYear($ayid,$is_default) {
	if($is_default == 1)
	{
		$is_default_ayid = 0;
		$sql = "UPDATE ast_account_year SET IsDefault = ? WHERE 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i",$is_default_ayid);
		$result = $stmt->execute();
	}
	$sql = "UPDATE ast_account_year SET IsDefault = ? WHERE AYID = ?";
	$stmt = $this->conn->prepare($sql);
	$stmt->bind_param("ii",$is_default,$ayid);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
    }

    public function deleteAccountYear($ayid) {
        $sql = "DELETE FROM ast_account_year WHERE AYID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ayid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>