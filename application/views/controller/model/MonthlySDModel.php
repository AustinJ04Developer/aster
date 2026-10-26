<?php
class monthlySDModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }
	public function getAllMonthlyAmount() {
        $sql = "SELECT SMID ,InstallmentAmount FROM ast_master_savings_monthly ORDER BY SMID";
        $result = $this->conn->query($sql);
        $monthlyamount = [];
        while ($row = $result->fetch_assoc()) {
            $monthlyamount[] = $row;
        }
        return $monthlyamount;
    }
    public function getAllMemberDetails() {
        $sql = "SELECT SMID ,MemberId,MemberName,CreatedDate,SavingsType,InterestPercentage,InstallMentAmount,TotalInterest,MaturedAmount FROM ast_master_savings_monthly ORDER BY SMID";
        $result = $this->conn->query($sql);
        $savings_monthly = [];
        while ($row = $result->fetch_assoc()) {
            $savings_monthly[] = $row;
        }
        return $savings_monthly;
    }
	
    public function getMemberDetailsById($smid) {
        $sql = "SELECT SMID ,MemberId,MemberName,CreatedDate,SavingsType,InterestPercentage,InstallMentAmount,TotalInterest,MaturedAmount FROM ast_master_savings_monthly WHERE SMID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $smid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

	public function addMemberDetails($memberid,$membername,$createddate,$savingstype,$interestpercentage,$installmentamount,$totalinterest,$maturedamount) {
        $sql = "INSERT INTO ast_master_savings_monthly (MemberId,MemberName,CreatedDate,SavingsType,InterestPercentage,InstallMentAmount,TotalInterest,MaturedAmount) 
				VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issidddd",$memberid,$membername,$createddate,$savingstype,$interestpercentage,$installmentamount,$totalinterest,$maturedamount);
        $result = $stmt->execute();
		if ($result) {
			$last_id = $this->conn->insert_id;
			}
		// UPDATE ast_savings_current SET SCNO = ".$last_id." WHERE SCID = ".$last_id
		$q = "UPDATE ast_master_savings_monthly SET SCNO=? WHERE SMID=?";
		$st = $this->conn->prepare($q);
		$st->bind_param("ii", $last_id, $last_id);
		$result = $st->execute();
		 return $result;
		}
    public function deleteMemberDetails($smid) {
        $sql = "DELETE FROM ast_master_savings_monthly WHERE SMID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $smid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
	public function insertTransaction($date, $accountNo, $amount, $ayid, $atmid, $transactionType, $transactionMethod, $remarks, $userId) {

    $sql = "INSERT INTO ast_transaction 
            (Date, AccountNo, Amount, AYID, ATMID, TransactionType, TransactionMethod, Remarks, UserId)VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param("sidiiiisi",
        $date,
        $accountNo,
        $amount,
        $ayid,
        $atmid,
        $transactionType,
        $transactionMethod,
        $remarks,
        $userId
    );

    if ($stmt->execute()) {
        return $this->conn->insert_id; //  RETURN TID
    }

    return false;
}

	/* public function insertTransactionMethod($date,$tid,$methodType,$amount,$chequeDate,$fromBank,$toBank,$chequeNumber,$upiType,$upiTransactionId) {
        $sql = "INSERT INTO ast_transaction_method (TransactionDate,TID, MethodType,ChequeDate,FromBank, ToBank,ChequeNumber,UPIType,UPITransactionId,Amount,TransactionDate) 
		VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siissssidss",
		 $date,
		 $tid,
		 $methodType,
		 $amount,
		 $chequeDate,
		 $fromBank,
		 $toBank,
		 $chequeNumber,
		 $upiType,
		 $upiTransactionId
		 );
        $result = $stmt->execute();
       // $stmt->close();
        return $result;
    }*/
	public function insertTransactionMethod(
    $date,
    $tid,
    $methodType,
    $amount,
    $chequeDate,
    $fromBank,
    $toBank,
    $chequeNumber,
    $upiType,
    $upiTransactionId
) {

    $sql = "INSERT INTO ast_transaction_method
    (
        TID,
        MethodType,
        ChequeDate,
        FromBank,
        ToBank,
        ChequeNumber,
        UPIType,
        UPITransactionId,
        Amount,
        TransactionDate
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "iissssisds",
        $tid,
        $methodType,
        $chequeDate,
        $fromBank,
        $toBank,
        $chequeNumber,
        $upiType,
        $upiTransactionId,
        $amount,
        $date
    );

    return $stmt->execute();
}

}
?>