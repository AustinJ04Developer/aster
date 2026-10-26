<?php
class SavingsDailyModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSavingsList() {
        $q = "SELECT * FROM ast_master_savings_daily ORDER BY DSID ASC";
        return $this->conn->query($q)->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $q = "SELECT * FROM ast_master_savings_daily WHERE DSID = ?";
        $st = $this->conn->prepare($q);
        $st->bind_param("i", $id);
        $st->execute();
        return $st->get_result()->fetch_assoc();
    }

    public function insert($data) {
        $q = "INSERT INTO ast_master_savings_daily 
              (InstallmentAmount, D100, D200, D300, UserId)
              VALUES (?, ?, ?, ?, ?)";

        $st = $this->conn->prepare($q);
        $st->bind_param("iiiii",
            $data['installment_amount'],
            $data['D100'], $data['D200'], $data['D300'],
            $data['user_id']
        );
        return $st->execute();
    }

    public function update($id, $data) {
        $q = "UPDATE ast_master_savings_daily SET 
              InstallmentAmount=?, D100=?, D200=?, D300=?
              WHERE DSID=?";

        $st = $this->conn->prepare($q);
        $st->bind_param("iiiii",
            $data['installment_amount'],
            $data['D100'], $data['D200'], $data['D300'],
            $id
        );
        return $st->execute();
    }

    public function delete($id) {
        $q = "DELETE FROM ast_master_savings_daily WHERE DSID = ?";
        $st = $this->conn->prepare($q);
        $st->bind_param("i", $id);
        return $st->execute();
    }
}

?>
