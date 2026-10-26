<?php
class GeneralClassModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }
    public function addTransactionCashChequeRTGSUPI($current_date, $account_no, $amount, $ayid, $atmid, $trans_type, 
							$trans_method, $chellan_no, $acode, $asubcode, $interest_type,$pr_int_penalty, $remarks, $narration, $user_id,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$memberid)
	{
        $bank_id = 0;
	    if($trans_type == 1)
			$bank_id = $to_bank;
		else
			$bank_id = $from_bank;
		$sql = "INSERT INTO ast_transaction (Date,
											 AccountNo, 
											 Amount,
											 AYID,
											 ATMID,
											 TransactionType,
											 TransactionMethod,
											 ChallanNo,
											 ACode,
											 ASubCode,
											 InterestType,
											 PROrIntOrPen,
											 Remarks,
											 Narration,
											 UserId,MemberId,BankId) VALUES (
											 ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiiiiisiiiissiii", $current_date, $account_no, $amount, $ayid, $atmid, $trans_type,$trans_method, $chellan_no, $acode, $asubcode, $interest_type, 
							$pr_int_penalty, $remarks, $narration, $user_id, $memberid,$bank_id);
        $result = $stmt->execute();
		$last_tid = $this->conn->insert_id;  // Get last inserted TID
		if($trans_method == 1)
			$insert_data_in_transaction_method=$this->addTransactionMethodCash($current_date, $last_tid, $trans_method, $amount, $trans_type);
		else
			$insert_data_in_transaction_method=$this->addTransactionMethodChequeRTGSUPI($current_date, $last_tid, $trans_method, $amount,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$trans_type);
        $stmt->close();
        return $last_tid;
    }
	 public function addTransactionMethodCash($date, $tid, $transaction_method, $amount, $trans_type) {
        $sql = "INSERT INTO ast_transaction_method (TransactionDate,
											 TID,
											 MethodType, 
											 Amount,
											 TransType) VALUES (
											 ?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiii", $date, $tid, $transaction_method, $amount, $trans_type);
        $result = $stmt->execute();
       // $stmt->close();
        return $result;
    }
	public function addTransactionMethodChequeRTGSUPI($date, $last_tid, $trans_method, $amount,$cheque_date,
							$cheque_rtgs_upi_number,$upi_type,$from_bank,$to_bank,$upi_id,$trans_type)
	{
		$sql = "INSERT INTO ast_transaction_method (TransactionDate,
											 TID,
											 MethodType, 
											 Amount,
											 ChequeDate,
											 FromBank,
											 ToBank,
											 ChequeNumber,
											 UPIType,
											 UPITransactionId,
											 TransType) VALUES (
											 ?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiissssisi", $date, $last_tid, $trans_method, $amount, $cheque_date, $from_bank, $to_bank, $cheque_rtgs_upi_number, $upi_type, $upi_id, $trans_type);
        $result = $stmt->execute();
		// UPDATE COMPANY BANK TABLE
		if($trans_type == 1) // Receipt Mode (Credit account)
		{
			//$sql_update_company_bank = "UPDATE st_company_bank_account SET Balance = Balance + ".$amount." WHERE BankId = ".$to_bank;
			$sql_update_company_bank = "UPDATE ast_company_bank_account SET Balance = Balance + ? WHERE BankId=?";
			$stmt = $this->conn->prepare($sql_update_company_bank);
			$stmt->bind_param("ii", $amount, $to_bank);
			$result = $stmt->execute();
			// INSERT RECORD IN COMPANY BANK TRANSACTIOPN
			$sql = "INSERT INTO ast_company_bank_transaction (Date,
												 BankId,
												 TransactionType, 
												 Amount, TID) VALUES (
												 ?,?,?,?,?)";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("siiii", $date, $to_bank, $trans_type, $amount,$last_tid);
			$result = $stmt->execute();
		}
		else // Payment mode (Debit account)
		{
			$sql_update_company_bank = "UPDATE ast_company_bank_account SET Balance = Balance - ? WHERE BankId=?";
			$stmt = $this->conn->prepare($sql_update_company_bank);
			$stmt->bind_param("ii", $amount, $from_bank);
			$result = $stmt->execute();
			// INSERT RECORD IN COMPANY BANK TRANSACTIOPN
			$sql = "INSERT INTO ast_company_bank_transaction (Date,
												 BankId,
												 TransactionType, 
												 Amount,TID) VALUES (
												 ?,?,?,?,?)";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("siiii", $date, $from_bank, $trans_type, $amount,$last_tid);
			$result = $stmt->execute();
		}
       // $stmt->close();
        return $result;
	}
	
	// Payment challan number get last number and update new number 
	function generatePaymentChallan() {
		$year = date("Y");
		mysqli_begin_transaction($this->conn,);
		$sql = "SELECT payment_last_no FROM ast_challan_series 
				WHERE series_year = $year FOR UPDATE";
		$result = mysqli_query($this->conn,$sql);
		if (mysqli_num_rows($result) == 0) {
			mysqli_query($this->conn,
				"INSERT INTO ast_challan_series (series_year, receipt_last_no, payment_last_no)
				 VALUES ($year, 0, 0)"
			);
			$last = 0;
		} else {
			$row = mysqli_fetch_assoc($result);
			$last = $row['payment_last_no'];
		}
		$next = $last + 1;
		mysqli_query($this->conn,
			"UPDATE ast_challan_series SET payment_last_no = $next 
			 WHERE series_year = $year"
		);
	   mysqli_commit($this->conn);
	   return "PC-$year-" . str_pad($next, 4, '0', STR_PAD_LEFT);
	}// End payment challan no
	// Receipt challan number get last number and update new number 
	function generateReceiptChallan() {
		$year = date("Y");
		mysqli_begin_transaction($this->conn);
		// Check if series for current year exists
		$sql = "SELECT receipt_last_no FROM ast_challan_series 
				WHERE series_year = $year FOR UPDATE";
		$result = mysqli_query($this->conn, $sql);
		if (mysqli_num_rows($result) == 0) {
			// Year not found create new year row
			mysqli_query($this->conn,
				"INSERT INTO ast_challan_series (series_year, receipt_last_no, payment_last_no)
				 VALUES ($year, 0, 0)"
			);
			$last = 0;
		} else {
			$row = mysqli_fetch_assoc($result);
			$last = $row['receipt_last_no'];
		}
		// Next number
		$next = $last + 1;
		// Update table
		mysqli_query($this->conn,
			"UPDATE ast_challan_series SET receipt_last_no = $next 
			 WHERE series_year = $year"
		);
		mysqli_commit($this->conn);
		// Format: RC/2025/0001
		return "RC-$year-" . str_pad($next, 4, '0', STR_PAD_LEFT);
	} // End Receipt challan no
}
?>