<?php
class CurrentSDModel {
    private $conn;

    public function __construct($conn) { 	
        $this->conn = $conn;
    }
	
    public function getAllMemberDetails() {
        $sql = "SELECT SCID,MemberId,MemberName,CreatedDate,Amount,Remarks FROM ast_savings_current ORDER BY SCID";
        $result = $this->conn->query($sql);
        $savings_current = [];
        while ($row = $result->fetch_assoc()) {
            $savings_current[] = $row;
        }
        return $savings_current;
    }
	
    public function getMemberDetailsById($scid) {
        $sql = "SELECT SCID,MemberId,MemberName,CreatedDate,Amount,Remarks FROM ast_savings_current WHERE SCID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

	public function addMemberDetails($memberid,$membername,$createddate,$amount,$remarks) {
        $sql = "INSERT INTO ast_savings_current (MemberId,MemberName,CreatedDate,Amount,Remarks) VALUES (?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issds",$memberid,$membername,$createddate,$amount,$remarks);
        $result = $stmt->execute();
		$last_sdid = $this->conn->insert_id;  // Get last inserted TID
		// UPDATE ast_savings_current SET SCNO = ".$last_id." WHERE SCID = ".$last_id
		$q = "UPDATE ast_savings_current SET SCNO=? WHERE SCID=?";
		$st = $this->conn->prepare($q);
		$st->bind_param("ii", $last_sdid, $last_sdid);
		$result = $st->execute();
		return $last_sdid;
	}
    public function deleteMemberDetails($scid) {
        $sql = "DELETE FROM ast_savings_current WHERE SCID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
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


/****** SD PAYMENT START *********/
	
	
	public function searchMembers($scid)
	{
		$sql = "SELECT *
				FROM ast_savings_current
				WHERE SCID = ?
				AND Status = 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $scid);
		$stmt->execute();
		$result = $stmt->get_result();
		$data = [];
		while($row = $result->fetch_assoc())
		{
			$data[] = $row;
		}
		return $data;
	}
	
	
	// Fill Combo Box in search sd
    public function getSDNumbers()
    {
        $sql = "SELECT SCID,MemberId,MemberName,SCNO FROM ast_savings_current WHERE Status = 1 ORDER BY SCID";
        $result = mysqli_query($this->conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data;
    }
	
	 // Fetch balance using SCID
    public function getBalanceAmount($scid)
    {
        $balanceAmount = 0;
        $sql = "SELECT Amount
                FROM ast_savings_current
                WHERE SCID = ?
                AND Status = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $balanceAmount = $row['Amount'];
        }
        return $balanceAmount;
    }
	
	//Fetch balance from bank account
	
	public function getBankBalance($bankId)
	{
		$balance = 0;
	
		$sql = "SELECT Balance
				FROM ast_company_bank_account
				WHERE BankId = ?";
	
		$stmt = $this->conn->prepare($sql);
	
		$stmt->bind_param("i", $bankId);
	
		$stmt->execute();
	
		$result = $stmt->get_result();
	
		if ($row = $result->fetch_assoc()) {
	
			$balance = $row['Balance'];
		}
	
		return $balance;
	}
	
	// update amount after payment
	public function updateSDCurrentPayment($scid, $payAmount, $trans_type) 
	{
		// Get current balance
		$sql = "SELECT Amount
				FROM ast_savings_current
				WHERE SCID = ?";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $scid);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$currentBalance = $row['Amount'];
		
		// Remaining balance after payment
		if($trans_type == 1)
			$newBalance = $currentBalance + $payAmount;
		else
	    	$newBalance = $currentBalance - $payAmount;
		
		// Prevent negative balance
    	if ($newBalance < 0) 
		{
        	return false;
    	}
		
		// Update new balance
    	$updateSql = "UPDATE ast_savings_current
                  SET Amount = ?
                  WHERE SCID = ?";
    	$updateStmt = $this->conn->prepare($updateSql);
    	$updateStmt->bind_param("di", $newBalance, $scid);
    	$result = $updateStmt->execute();
    	$updateStmt->close();
    	return $result;
    }
/****** SD PAYMENT END *********/	
/*-------------------------------------------------------------------- SD current Delete payment code Start----------------------------------------------------*/

	public function searchSDPayment($tid,$transaction_type)//grid view for after selecting the chellan num
	{
		$sql = "SELECT
                sc.SCID,
                sc.MemberId,
                sc.SCNO,
                tr.TID,
                tr.Amount
            FROM ast_transaction tr
            LEFT JOIN ast_savings_current sc
            ON sc.SCID = tr.AccountNo
            WHERE tr.TID=? AND tr.TransactionType=?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ii", $tid,$transaction_type);
		$stmt->execute();
	
		return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	
	public function getTodayChallanNumbers($atmid,$transactionType) //Get the chellan num in combobox
	{
		$today = date('Y-m-d');
	
		$sql = "SELECT DISTINCT
					tr.TID,
					tr.ChallanNo
				FROM ast_transaction tr
				WHERE tr.Date = ?
				AND tr.ATMID = ?
				AND tr.TransactionType = ?
				AND tr.ChallanNo != ''
				ORDER BY tr.TID DESC";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("sii",$today,$atmid,$transactionType);
		$stmt->execute();
	
		return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}
			
	public function deleteSDPayment($tid, $scid) //if you click delete payment this function will execute
	{
		mysqli_begin_transaction($this->conn);
		try {
			// ==========================================
			// GET TRANSACTION DETAILS
			// ==========================================
			$sql = "SELECT * FROM ast_transaction WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			$transaction = $stmt->get_result()->fetch_assoc();
			if (!$transaction) {
				throw new Exception("Transaction not found");
			}
			$amount = $transaction['Amount'];
			$accountNo = $transaction['AccountNo'];
			$transactionMethod = $transaction['TransactionMethod'];
			// ==========================================
			// GET TRANSACTION METHOD
			// ==========================================
			$sql = "SELECT * FROM ast_transaction_method WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			$method = $stmt->get_result()->fetch_assoc();
			$fromBank = $method['FromBank'] ?? '';
			// ==========================================
			// RETURN AMOUNT TO SD TABLE
			// ==========================================
			$sql = "UPDATE ast_savings_current
					SET Amount = Amount + ?
					WHERE SCID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("di", $amount, $scid);
			$stmt->execute();
			// ==========================================
			// RETURN AMOUNT TO COMPANY BANK
			// ==========================================
			if (!empty($fromBank)) {
				$sql = "UPDATE ast_company_bank_account
            			SET Balance = Balance + ?
            			WHERE BankId = ?";
				$stmt = $this->conn->prepare($sql);
				$stmt->bind_param("di", $amount, $fromBank);
				$stmt->execute();
			}
			// ==========================================
			// DELETE COMPANY BANK TRANSACTION
			// ==========================================
			$sql = "DELETE FROM ast_company_bank_transaction
					WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			// ==========================================
			// DELETE TRANSACTION METHOD
			// ==========================================
			$sql = "DELETE FROM ast_transaction_method
					WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			// ==========================================
			// DELETE TRANSACTION
			// ==========================================
			$sql = "DELETE FROM ast_transaction
					WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			mysqli_commit($this->conn);
			return [
				'status' => true,
				'message' => 'Payment deleted successfully'
			];
		} catch (Exception $e) {
			mysqli_rollback($this->conn);
			return [
				'status' => false,
				'message' => $e->getMessage()
			];
		}
	}
/******************** END SD DELETE PAYAMENT ***********/
/*-------------------------------------------------------------------- SD current Delete receipt code Start----------------------------------------------------*/

	public function searchSDReceipt($tid)//grid view for after selecting the chellan num
	{
		$sql = "SELECT
                sc.SCID,
                sc.MemberId,
                sc.SCNO,
                tr.TID,
                tr.Amount
            FROM ast_transaction tr
            LEFT JOIN ast_savings_current sc
            ON sc.SCID = tr.AccountNo
            WHERE tr.TID=?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $tid);
		$stmt->execute();
	
		return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	
	public function getTodayReceiptChallanNumbers() //Get the chellan num in combobox
	{
		$today = date('Y-m-d');
	
		$sql = "SELECT DISTINCT
					tr.TID,
					tr.ChallanNo
				FROM ast_transaction tr
				WHERE tr.Date = ?
				AND tr.ChallanNo != ''
				AND tr.TransactionType = 1
				AND tr.ATMID = 1
				ORDER BY tr.TID DESC";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $today);
		$stmt->execute();
		return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}
			
	public function deleteSDReceipt($tid, $scid) //if you click delete receipt this function will execute
	{
		mysqli_begin_transaction($this->conn);
		try {
			// ==========================================
			// GET TRANSACTION DETAILS
			// ==========================================
			$sql = "SELECT * FROM ast_transaction WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			$transaction = $stmt->get_result()->fetch_assoc();
			if (!$transaction) {
				throw new Exception("Transaction not found");
			}
			$amount = $transaction['Amount'];
			$accountNo = $transaction['AccountNo'];
			$transactionMethod = $transaction['TransactionMethod'];
			// ==========================================
			// GET TRANSACTION METHOD
			// ==========================================
			$sql = "SELECT * FROM ast_transaction_method WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			$method = $stmt->get_result()->fetch_assoc();
			$toBank = $method['ToBank'] ?? '';
			// ==========================================
			// REDUCE AMOUNT FROM SD TABLE
			// ==========================================
			$sql = "UPDATE ast_savings_current
					SET Amount = Amount - ?
					WHERE SCID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("di", $amount, $scid);
			$stmt->execute();
			// ==========================================
			// REDUCE AMOUNT FROM COMPANY BANK
			// ==========================================
			if (!empty($toBank)) {
				$sql = "UPDATE ast_company_bank_account
            			SET Balance = Balance - ?
            			WHERE BankId = ?";
				$stmt = $this->conn->prepare($sql);
				$stmt->bind_param("di", $amount, $toBank);
				$stmt->execute();
			}
			// ==========================================
			// DELETE COMPANY BANK TRANSACTION
			// ==========================================
			$sql = "DELETE FROM ast_company_bank_transaction
					WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			// ==========================================
			// DELETE TRANSACTION METHOD
			// ==========================================
			$sql = "DELETE FROM ast_transaction_method
					WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			// ==========================================
			// DELETE TRANSACTION
			// ==========================================
			$sql = "DELETE FROM ast_transaction
					WHERE TID = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("i", $tid);
			$stmt->execute();
			mysqli_commit($this->conn);
			return [
				'status' => true,
				'message' => 'Receipt deleted successfully'
			];
		} catch (Exception $e) {
			mysqli_rollback($this->conn);
			return [
				'status' => false,
				'message' => $e->getMessage()
			];
		}
	}
/******************** END SD DELETE RECEIPT ***********/
	
	/*-------------------------------------------------------------------- SD current Account Close code Start----------------------------------------------------*/
	//function for close account data in ast_savings_current table
	public function updateSDAccountClosed($scid,$payAmount,$trans_type)
	{
		// Get current balance
		$sql = "SELECT Amount
				FROM ast_savings_current
				WHERE SCID=?";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i",$scid);
		$stmt->execute();
	
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
	
		if(!$row)
		{
			return false;
		}
	
		$currentBalance = (float)$row['Amount'];
	
		// Calculate remaining amount
		$newBalance = $currentBalance - $payAmount;
	
		// Prevent negative value
		if($newBalance < 0)
		{
			$newBalance = 0;
		}
	
		$status = 0; // Closed
		$closedBy = $_SESSION['UserId'];
		$closedDate = date('Y-m-d');
	
		$sql = "UPDATE ast_savings_current
				SET
					Status=?,
					ClosedBy=?,
					ClosedDate=?,
					Amount=?
				WHERE SCID=?";
	
		$stmt = $this->conn->prepare($sql);
	
		$stmt->bind_param(
			"iisdi",
			$status,
			$closedBy,
			$closedDate,
			$newBalance,
			$scid
		);
	
		$result = $stmt->execute();
	
		return $result;
	}
	/******** END ACCOUNT CLOSING SD CURRENT *****/
	
	
/*-------------------------------------------------------------------- SD CURRENT DELETE ACCOUNT START----------------------------------------------------*/

	public function getTodayAccountNumbers() //Get the Account number in combobox
	{
		$sql = "SELECT
					SCID,
					MemberId,
					SCNO,
					MemberName,
					CreatedDate
				FROM ast_savings_current
				WHERE DATE(CreatedDate) = DATE(CONVERT_TZ(NOW(), @@session.time_zone, '+05:30'))
				AND ClosedBy = 0
				ORDER BY SCID DESC";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
		return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	
	public function searchSDaccount($scid)//grid view for after selecting the account number
	{
    $sql = "SELECT
                SCID,
                MemberId,
                SCNO,
                MemberName,
                CreatedDate,
                Amount
            FROM ast_savings_current
            WHERE SCID = ?
			AND ClosedBy = 0";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $scid);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	
	public function deleteSDAccount($scid)  //Delete function will execute
	{
    mysqli_begin_transaction($this->conn);
    try {
        // =================================================
        //  CHECK TODAY ONLY
        // =================================================
        $sql = "SELECT SCID 
                FROM ast_savings_current 
                WHERE SCID = ? 
                AND DATE(CreatedDate) = CURDATE()";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
        $stmt->execute();

        if ($stmt->get_result()->num_rows == 0) {
            throw new Exception("Only today's Savings Current Accounts can be deleted.");
        }

        // =================================================
        //  CHECK RECEIPTS
        // =================================================
        $sql = "SELECT COUNT(*) AS TotalReceipt
				FROM ast_transaction
				WHERE AccountNo = ?
				AND TransactionType = 1
				AND ATMID = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
        $stmt->execute();
        $receipt = $stmt->get_result()->fetch_assoc();
        if ($receipt['TotalReceipt'] > 1) {
            throw new Exception("This account cannot be deleted because receipt transactions exist.");
        }
       // =================================================
		// GET ALL TRANSACTIONS
		// =================================================
		$sql = "SELECT
					TID,
					Amount
				FROM ast_transaction
				WHERE AccountNo = ?
				AND ATMID = 1";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $scid);
		$stmt->execute();
		
		$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		
		foreach ($transactions as $transaction)
		{
			$tid = $transaction['TID'];
			$amount = $transaction['Amount'];
		
			// GET COMPANY BANK
			$sql = "SELECT
					BankId
					FROM ast_company_bank_transaction
					WHERE TID = ?";
		
			$stmt2 = $this->conn->prepare($sql);
			$stmt2->bind_param("i", $tid);
			$stmt2->execute();
		
			$bank = $stmt2->get_result()->fetch_assoc();
			$bankId = $bank['BankId'] ?? 0;
		
			// REVERSE BANK BALANCE
			if ($bankId > 0)
			{
				$sql = "UPDATE ast_company_bank_account
						SET Balance = Balance - ?
						WHERE BankId = ?";
		
				$stmt3 = $this->conn->prepare($sql);
				$stmt3->bind_param("di", $amount, $bankId);
				$stmt3->execute();
			}
		
		// DELETE METHOD
				$sql = "DELETE FROM ast_transaction_method WHERE TID = ?";
				$stmt2 = $this->conn->prepare($sql);
				$stmt2->bind_param("i", $tid);
				$stmt2->execute();
		
				// DELETE COMPANY BANK TRANSACTIONS
				$sql = "DELETE FROM ast_company_bank_transaction
						WHERE TID = ?";
				$stmt2 = $this->conn->prepare($sql);
				$stmt2->bind_param("i", $tid);
				$stmt2->execute();
		}			
        // =================================================
        // 4. DELETE ALL TRANSACTIONS
        // =================================================
        $sql = "DELETE FROM ast_transaction
				WHERE AccountNo = ?
				AND ATMID = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
        $stmt->execute();

        // =================================================
        // 5. DELETE ACCOUNT
        // =================================================
        $sql = "DELETE FROM ast_savings_current WHERE SCID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $scid);
        $stmt->execute();

        mysqli_commit($this->conn);

        return [
            'status' => true,
            'message' => 'Savings Current Account deleted successfully'
        ];

    } catch (Exception $e) {
        mysqli_rollback($this->conn);

        return [
            'status' => false,
            'message' => $e->getMessage()
        ];
    }
}
/********************  SD CURRENT DELETE ACCOUNT END***********/
/*-------------------------------------------------------SD current Ledger Report code Start-----------------------------------------------------------------*/
//Code added by jino
//added date 6-6-26		
	public function getAllSDCurrentAccounts()
	{
		$sql = "SELECT
					SCID,
					SCNO,
					MemberName,
					Status
				FROM ast_savings_current
				ORDER BY Status DESC, SCNO ASC";
		$result = $this->conn->query($sql);
		return $result->fetch_all(MYSQLI_ASSOC);
	}
//Code added by jino
//added date 5-6-26	
	public function getSDCurrentAccountDetails($scid)
	{
		$sql = "SELECT
					a.SCID ,
					a.SCNO,
					a.CreatedDate,
					a.MemberId,
					a.MemberName,
					a.Amount,
					a.Status
				FROM ast_savings_current a
				WHERE a.SCID = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i",$scid);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}
	
//Code added by jino
//added date 5-6-26		
	public function getSDCurrentLedgerTransactions($sdid,$atmid)
	{
		$sql = "SELECT
					t.TID,
					t.Date,
					t.ChallanNo,
					t.TransactionType,
					t.Amount,
					t.Narration,
					t.Remarks,
					tm.MethodType
				FROM ast_transaction t
				LEFT JOIN ast_transaction_method tm ON tm.TID = t.TID
				WHERE t.AccountNo = ?
				AND t.ATMID = ?
				ORDER BY t.Date ASC, t.TID ASC";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('ii', $sdid,$atmid);
		$stmt->execute();	
		$result = $stmt->get_result();
		$data = [];
		while($row = $result->fetch_assoc())
		{
			$data[] = $row;
		}
		return $data;
	}

//Code added by jino
//added date 6-6-26		
	public function getSDCurrentOpeningTransaction($sdid,$atmid)
	{
		$sql = "SELECT *
				FROM ast_transaction
				WHERE AccountNo = ?
				AND ATMID = ?
				ORDER BY Date ASC,TID ASC
				LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('ii',$sdid,$atmid);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}

}
?>