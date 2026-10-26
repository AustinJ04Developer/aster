<?php
class GlSchemeModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllSchemes() {
        $q = "SELECT * FROM ast_gl_schemes ORDER BY Dnumber ASC, LSID ASC";
        $result = $this->conn->query($q);
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $q = "SELECT * FROM ast_gl_schemes WHERE LSID = ?";
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

    public function getNextDnumber() {
        $q = "SELECT MAX(Dnumber) AS max_d FROM ast_gl_schemes";
        $res = $this->conn->query($q);
        if ($res && $row = $res->fetch_assoc()) {
            $max = (int)$row['max_d'];
            return $max > 0 ? ($max + 1) : 1;
        }
        return 1;
    }

    public function isDnumberExists($dnumber, $excludeLsid = 0) {
        if ($excludeLsid > 0) {
            $q = "SELECT LSID FROM ast_gl_schemes WHERE Dnumber = ? AND LSID != ?";
            $st = $this->conn->prepare($q);
            if (!$st) return false;
            $st->bind_param("ii", $dnumber, $excludeLsid);
        } else {
            $q = "SELECT LSID FROM ast_gl_schemes WHERE Dnumber = ?";
            $st = $this->conn->prepare($q);
            if (!$st) return false;
            $st->bind_param("i", $dnumber);
        }
        $st->execute();
        $st->store_result();
        $exists = $st->num_rows > 0;
        $st->close();
        return $exists;
    }

    public function insert($data) {
        $q = "INSERT INTO ast_gl_schemes 
              (`Date`, `Dnumber`, `InterestPercentage`, `AmountFrom`, `AmountTo`, `UserId`)
              VALUES (?, ?, ?, ?, ?, ?)";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("sidiii",
            $data['date'],
            $data['dnumber'],
            $data['interest_percentage'],
            $data['amount_from'],
            $data['amount_to'],
            $data['user_id']
        );
        $res = $st->execute();
        $st->close();
        return $res;
    }

    public function update($id, $data) {
        $q = "UPDATE ast_gl_schemes SET 
              `Date` = ?, `Dnumber` = ?, `InterestPercentage` = ?, `AmountFrom` = ?, `AmountTo` = ?
              WHERE `LSID` = ?";
        $st = $this->conn->prepare($q);
        if (!$st) {
            return false;
        }
        $st->bind_param("sidiii",
            $data['date'],
            $data['dnumber'],
            $data['interest_percentage'],
            $data['amount_from'],
            $data['amount_to'],
            $id
        );
        $res = $st->execute();
        $st->close();
        return $res;
    }

    public function delete($id) {
        $q = "DELETE FROM ast_gl_schemes WHERE LSID = ?";
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
