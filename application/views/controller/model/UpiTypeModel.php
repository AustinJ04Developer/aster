<?php
class UpiTypeModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }

    public function getAllUpi() {
        $sql = "SELECT UPIType,UPITypeName FROM aste_upi_type ORDER BY UPIType";
        $result = $this->conn->query($sql);
        $upis = [];
        while ($row = $result->fetch_assoc()) {
            $upis[] = $row;
        }
        return $upis;
    }

    public function getUpiById($upitype) {
        $sql = "SELECT UPIType,UPITypeName FROM aste_upi_type WHERE UPIType = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $upitype);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addUpi($upiname) {
        $sql = "INSERT INTO aste_upi_type (UPITypeName) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $upiname);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateUpi($upitype,$upiname) {
        $sql = "UPDATE aste_upi_type SET UPITypeName = ? WHERE UPIType = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si",$upiname,$upitype);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteUpi($upitype) {
        $sql = "DELETE FROM aste_upi_type WHERE UPIType = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $upitype);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>