<?php
class GlAppraiserModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllAppraisers() {
        $q = "SELECT * FROM ast_gl_appraiser ORDER BY AmountFrom ASC, AID ASC";
        $result = $this->conn->query($q);
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $q = "SELECT * FROM ast_gl_appraiser WHERE AID = ?";
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
        $q = "INSERT INTO ast_gl_appraiser 
              (`Date`, `AmountFrom`, `AmountTo`, `AppraiserCharge`, `UserId`)
              VALUES (?, ?, ?, ?, ?)";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("siiii",
            $data['date'],
            $data['amount_from'],
            $data['amount_to'],
            $data['appraiser_charge'],
            $data['user_id']
        );
        $res = $st->execute();
        $st->close();
        return $res;
    }

    public function update($id, $data) {
        $q = "UPDATE ast_gl_appraiser SET 
              `Date` = ?, `AmountFrom` = ?, `AmountTo` = ?, `AppraiserCharge` = ?
              WHERE `AID` = ?";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("siiii",
            $data['date'],
            $data['amount_from'],
            $data['amount_to'],
            $data['appraiser_charge'],
            $id
        );
        $res = $st->execute();
        $st->close();
        return $res;
    }

    public function delete($id) {
        $q = "DELETE FROM ast_gl_appraiser WHERE AID = ?";
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
