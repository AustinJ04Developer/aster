<?php
class GlProcessingFeeModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllFees() {
        $q = "SELECT * FROM ast_gl_processing_fee ORDER BY PFID DESC";
        $result = $this->conn->query($q);
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $q = "SELECT * FROM ast_gl_processing_fee WHERE PFID = ?";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return null;
        }
        $st->bind_param("i", $id);
        $st->execute();
        $res = $st->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $st->close();
        return $row;
    }

    public function insert($data) {
        $q = "INSERT INTO ast_gl_processing_fee 
              (`Date`, `ProcessingFeePercentage`, `UserId`)
              VALUES (?, ?, ?)";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("sdi",
            $data['date'],
            $data['processing_fee_percentage'],
            $data['user_id']
        );
        $res = $st->execute();
        $st->close();
        return $res;
    }

    public function update($id, $data) {
        $q = "UPDATE ast_gl_processing_fee SET 
              `Date` = ?, `ProcessingFeePercentage` = ?
              WHERE `PFID` = ?";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("sdi",
            $data['date'],
            $data['processing_fee_percentage'],
            $id
        );
        $res = $st->execute();
        $st->close();
        return $res;
    }

    public function delete($id) {
        $q = "DELETE FROM ast_gl_processing_fee WHERE PFID = ?";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("i", $id);
        $res = $st->execute();
        $st->close();
        return $res;
    }
}
?>
