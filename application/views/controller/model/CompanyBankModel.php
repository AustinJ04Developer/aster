<?php
class CompanyBankModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }

    public function getAllCompanybank() {
        $sql = "SELECT BankId ,BankName,Balance FROM ast_company_bank_account ORDER BY BankId";
        $result = $this->conn->query($sql);
        $companybanks = [];
        while ($row = $result->fetch_assoc()) {
            $companybanks[] = $row;
        }
        return $companybanks;
    }

    public function getCompanyBankById($bankid) {
        $sql = "SELECT BankId ,BankName,Balance FROM ast_company_bank_account WHERE BankId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bankid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addCompanybank($bankname,$balance) {
        $sql = "INSERT INTO ast_company_bank_account (BankName,Balance) VALUES (?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $bankname,$balance);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateCompanybank($bankid,$bankname,$balance) {
        $sql = "UPDATE ast_company_bank_account SET BankName = ? , Balance = ? WHERE BankId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi",$bankname,$balance,$bankid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteCompanybank($bankid) {
        $sql = "DELETE FROM ast_company_bank_account WHERE BankId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bankid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>