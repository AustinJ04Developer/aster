<?php
class TransactionModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
	// Paramether order
	// Date, Account No, Amount, AYID, ATMID, TransactionType, TransactionMethod, Challanno, ACode, ASubCode, InterestType, PROrIntOrPen, UserId, Remarks
	// Date -> Current Date (YYYY-mm-dd Format)
	// AccountNo -> Primary key of current deposit / loan (Prmimary Key)
	// TransactionType -> 1 for Receipt and 2 for Payament (Numeric)
	// TransactionMethod  1->Cash, 2->Cheque, 3->RTGS, 4->UPI Payment (Numeric)
	// ACode and ASubCode -> Ref. Developer Document (Numeric)
	// InterestType -> 0-> NOne, 1-> Interest, 2 -> Penalty (Numeric)
	// PROrIntOrPen -> Currrent transaction 1-> Principal, 2->Interst, 3->Penalty (Numeric)
	// UserId -> Current user id (Numeric)
	// Remarks -> Remarks if any (Varchar)
	// Method Type =>  1-> Cash, 2-> Cheque, 3-> RTGS, 4->UPI Payment (Numeric)
	// $cheque_date => Date (YYYY-mm-dd)
	// $from_bank	-> Varchar
	// $to_bank	-> Varchar
	// $cheque_no	-> Varchar
	// $upi_type	-> 1->GPay, 2->PhonePay, 3->PayTM, 4->Bhim, 5->Razor Pay(Numeric)
	// $transaction_id -> Varchar
 public function InsertTransaction($date,$account_no,$amount,$ayid,$atmid,$transaction_type,$transaction_method,$challan_no,$acode,$asubcode,
 					$interest_type,$pr_int_pen,$remarks,$userid,$method_type,$cheque_date,$from_bank,$to_bank,$cheque_no,$upi_type, $transaction_id) {
	$this->conn->autocommit(false);
	$this->conn->begin_transaction();
	try{
			// Prevent empty query
			if (empty($date) || empty($account_no)  || empty($ayid)  || empty($atmid)  || empty($transaction_type)  || empty($amount)) {
				return [];
			}
		    // Find AYID
			$ayid = FindAYID();
			// Insert a record in the transaction table
			$sql1 = "INSERT INTO ast_transaction (Date, AccountNo, Amount, AYID, ATMID, TransactionType, TransactionMethod, ChallanNo, ACode, 
							ASubCode, InterestType, PROrIntOrPen, UserId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt1 = $conn->prepare($sql1);
			$stmt1->bind_param("siiiiiiiiiiii", $date,$account_no,$amount,$ayid,$atmid,$transaction_type,$transaction_method,$challan_no,$acode,$asubcode,
 					$interest_type,$pr_int_pen,$userid,$remarks);
			$stmt1->execute();	
			$tid = $this->db->insert_id;
            $stmt1->close();
		    if($method_type == 2 || $method_type == 3) // Cheque or RTGS
			{
				$sql2 = "INSERT INTO ast_transaction_method (TID, MethodType, ChequeDate, FromBank, ToBank, ChequeNumber) VALUES (?, ?, ?, ?, ?, ?)";
				$stmt2 = $conn->prepare($sql1);
				$stmt2->bind_param("iissss",$tid,$method_type,$cheque_date,$from_bank,$to_bank,$cheque_no);
				$stmt2->execute();	
				$tmid = $this->db->insert_id;
			}
			else if($method_type == 4) // UPIType
			{
				$sql2 = "INSERT INTO ast_transaction_method (TID, MethodType, UPIType, UPITransactionId) VALUES (?, ?, ?, ?)";
				$stmt2 = $conn->prepare($sql1);
				$stmt2->bind_param("iiis",$tid,$method_type,$cheque_date,$from_bank,$to_bank,$cheque_no);
				$stmt2->execute();	
				$tmid = $this->db->insert_id;
			}
		$this->conn->commit();
        $this->conn->autocommit(true);
		return $tid; // Return current transaction id
	  }
	  catch
	  {
			$conn->rollback();
			$this->conn->autocommit(true);
			error_log("Order creation failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Transaction failed. All changes reverted.',
                'error' => $e->getMessage()
            ];
	  }
}
 // Find AYID
  public function FindAYID() {
  		$defalut = 1;
        $sql = "SELECT AYID FROM ast_account_year WHERE IsDefault = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $defalut);
        $stmt->execute();
        $result_val = $stmt->get_result()->fetch_assoc();
		return $result_val['AYID'];
    }
?>
