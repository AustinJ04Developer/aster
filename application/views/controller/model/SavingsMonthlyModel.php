<?php
class SavingsMonthlyModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSavingsList() {
        $q = "SELECT * FROM ast_master_savings_monthly ORDER BY SMID ASC";
        return $this->conn->query($q)->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $q = "SELECT * FROM ast_master_savings_monthly WHERE SMID = ?";
        $st = $this->conn->prepare($q);
        $st->bind_param("i", $id);
        $st->execute();
        return $st->get_result()->fetch_assoc();
    }

    public function insert($data) {
        $q = "INSERT INTO ast_master_savings_monthly 
              (InstallmentAmount, Years2, Years3, Years4, Years5, UserId)
              VALUES (?, ?, ?, ?, ?, ?)";

        $st = $this->conn->prepare($q);
        $st->bind_param("iiiiii",
            $data['installment_amount'],
            $data['years2'], $data['years3'], $data['years4'], $data['years5'],
            $data['user_id']
        );
        return $st->execute();
    }

    public function update($id, $data) {
        $q = "UPDATE ast_master_savings_monthly SET 
              InstallmentAmount=?, Years2=?, Years3=?, Years4=?, Years5=?
              WHERE SMID=?";

        $st = $this->conn->prepare($q);
        $st->bind_param("iiiiii",
            $data['installment_amount'],
            $data['years2'], $data['years3'], $data['years4'], $data['years5'],
            $id
        );
        return $st->execute();
    }

    public function delete($id) {
        $q = "DELETE FROM ast_master_savings_monthly WHERE SMID = ?";
        $st = $this->conn->prepare($q);
        $st->bind_param("i", $id);
        return $st->execute();
    }
	/*-------------------------------------------------------------------- SD Daily Create Account code Start---------------------------------------------------*/
	
	public function Interestcalculate($savingType,$installmentAmount,$interestPercentage = 0) // Calculate interest in create account monthly 
	{
		// Get Days from scheme type table
		$sql = "SELECT Years2,Years3,Years4,Years5
				FROM ast_master_savings_monthly
				WHERE SMID  = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i",$savingType);
		$stmt->execute();	
		$row = $stmt->get_result()->fetch_assoc();	
		if(!$row)
		{
			return [
				'total_interest' => 0,
				'matured_amount' => 0
			];
		}
		$years = (int,int,int,int)$row['years2','Years3','Years4','Years5'];	
		// Get matured amount from master table
		$sql2 = "SELECT *
				 FROM ast_master_savings_monthly
				 WHERE InstallmentAmount = ?";
		$stmt2 = $this->conn->prepare($sql2);
		$stmt2->bind_param("d",$installmentAmount);
		$stmt2->execute();	
		$master = $stmt2->get_result()->fetch_assoc();	
		if(!$master)
		{
			return [
				'total_interest' => 0,
				'matured_amount' => 0
			];
		}	
		switch($years)
		{
			case 2:
				$maturedAmount = $master['years2'];
				break;	
			case 3:
				$maturedAmount = $master['Years3'];
				break;	
			case 4:
				$maturedAmount = $master['Years4'];
				break;	
			case 5:
				$maturedAmount = $master['Years5'];
				break;
			default:
				$maturedAmount = 0;
		}	
		$depositAmount = $installmentAmount * $years;	
		$totalInterest = $maturedAmount - $depositAmount;	
		return [
			'total_interest' => $totalInterest,
			'matured_amount' => $maturedAmount
		];
	}


	
	public function saveMonthlySavingsAccount($memberId,$memberName,$createdDate,$smid,$savingType,$interestPercentage,
									$actualInstallmentAmount,$totalInterest,$maturedAmount,$userId,$amount) //create account daily save in ast_savings_monthly table
	{
		$sql = "SELECT IFNULL(MAX(SDNO),0)+1 AS NextSDMNO
				FROM ast_savings_monthly";	
		$result = mysqli_query($this->conn,$sql);
		$row = mysqli_fetch_assoc($result);	
		$sdmno = $row['NextSDMNO'];	
		$insert = "
			INSERT INTO ast_savings_monthly
			(
				SDMNO,
				SMID,
				MemberId,
				MemberName,
				CreatedDate,
				SavingsType,
				InterestPercentage,
				InstallMentAmount,
				TotalInterest,
				MaturedAmount,
				BalanceAmount
			)
			VALUES
			(
				?,?,?,?,?,?,?,?,?,?,?
			)
		";	
		$stmt = $this->conn->prepare($insert);	
		$stmt->bind_param(
							"iiissiddddd",
							$sdno,
							$dsid,
							$memberId,
							$memberName,
							$createdDate,
							$savingType,
							$interestPercentage,
							$actualInstallmentAmount,
							$totalInterest,
							$maturedAmount,
							$amount
		);	
		if($stmt->execute())
		{
			$newsdmid = $this->conn->insert_id;

			return [
				'sdmno' => $sdmno,
				'sdmid' => $newsdmid
			];
		}	
		return false;
	}
	
	
	public function getInstallmentAmountBySMID($smid) //Fetch installment amount from ast_master_savings_monthly table
	{
		$sql = "SELECT InstallmentAmount
				FROM ast_master_savings_monthly
				WHERE SMID = ?";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $smid);
		$stmt->execute();
	
		$row = $stmt->get_result()->fetch_assoc();
	
		return $row ? $row['InstallmentAmount'] : 0;
	}
	
	
	public function getLastInstallmentNo($sdid)  //Get last installment no from paid table 
	{
		$sql = "SELECT IFNULL(MAX(InstallmentNo),0) AS LastNo
				FROM ast_savings_paid_details
				WHERE SavingsAccountNo = ?";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i",$sdid);
		$stmt->execute();
	
		$row = $stmt->get_result()->fetch_assoc();
	
		return $row['LastNo'];
	}
	
	
	public function getAccountCreatedDate($sdid)  //Get Account Created date from ast_savings_daily table
	{
		$sql = "SELECT CreatedDate
				FROM ast_savings_daily
				WHERE SDID = ?";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $sdid);
		$stmt->execute();
	
		$row = $stmt->get_result()->fetch_assoc();
	
		return $row['CreatedDate'];
	}
	
	
	public function getNextInstallmentDate($sdid)  //Get Next installment date
	{
		$sql = "SELECT MAX(InstallmentNo) AS LastNo
				FROM ast_savings_paid_details
				WHERE SavingsAccountNo = ?";
	
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i",$sdid);
		$stmt->execute();
	
		$row = $stmt->get_result()->fetch_assoc();
	
		$createdDate = $this->getAccountCreatedDate($sdid);
	
		return date(
			'Y-m-d',
			strtotime($createdDate . " +" . $row['LastNo'] . " day")
		);
	}
	
}
?>
