<?php
class MembersModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $q = "SELECT * FROM ast_members ORDER BY MemberId DESC";
        return $this->conn->query($q)->fetch_all(MYSQLI_ASSOC);
    }
	public function getAllUsers() {
        $sql = "SELECT UserId,UserName FROM user ORDER BY Name";
        $result = $this->conn->query($sql);
        $usernames = [];
        while ($row = $result->fetch_assoc()) {
            $usernames[] = $row;
        }
        return $usernames;
    }
    public function getById($id) {
        $q = "SELECT * FROM ast_members WHERE MemberId=?";
        $st = $this->conn->prepare($q);
        $st->bind_param("i", $id);
        $st->execute();
        return $st->get_result()->fetch_assoc();
    }
	// Fetch all states
	public function getStates() {
		$q = "SELECT * FROM ast_state ORDER BY SID ASC";
		return $this->conn->query($q)->fetch_all(MYSQLI_ASSOC);
	}

	// Fetch districts by SID
	public function getDistrictsByState($stateId) {
		$sql = "SELECT DID, DistrictName FROM ast_district WHERE SID = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $stateId);
		$stmt->execute();
		$result = $stmt->get_result();

		$districts = [];
		while ($row = $result->fetch_assoc()) {
			$districts[] = $row;
		}
		return $districts;
	}



    public function insert($data) {
        $q = "INSERT INTO ast_members 
        (FullName, Aadhaar, MobileNo, PermenantAddress, PresentAddress, 
        NomineeName, NomineeAge, NomineeAddress, NomineeMobiles, SDWName,
        DateOfBirth, PanNumber, EducatinalQualification, Occupation,
        District, State, PinCode, ApprovedBy, PhotoImg, SignatureImg, AadhaarImg, PancardImg, Associate)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $st = $this->conn->prepare($q);
		/*if(empty($data["DateOfBirth"]))
			$data["DateOfBirth"] = '0000-00-00';*/
        $st->bind_param(
            "ssssssissssssssiiissssi",
            $data["FullName"],
            $data["Aadhaar"],
            $data["MobileNo"],
            $data["PermenantAddress"],
            $data["PresentAddress"],
            $data["NomineeName"],
            $data["NomineeAge"],
            $data["NomineeAddress"],
            $data["NomineeMobiles"],
            $data["SDWName"],
            $data["DateOfBirth"],
            $data["PanNumber"],
            $data["EducatinalQualification"],
            $data["Occupation"],
            $data["District"],
            $data["State"],
            $data["PinCode"],
            $data["ApprovedBy"],
			$data["PhotoImg"],
			$data["SignatureImg"],
			$data["AadhaarImg"],
			$data["PancardImg"],
			$data["Associate"]
        );

        $st->execute();
        return $this->conn->insert_id;
    }

    public function updateMemberCode($id, $code) {
        $q = "UPDATE ast_members SET MemberCode=? WHERE MemberId=?";
        $st = $this->conn->prepare($q);
        $st->bind_param("si", $code, $id);
        return $st->execute();
    }

    public function update($id, $data) {
		if(!empty($data["PhotoImg"]))
		{
				$q = "UPDATE ast_members SET 
						PhotoImg=?
					  WHERE MemberId=?";
				$st = $this->conn->prepare($q);
				
				$st->bind_param(
				"si",
				$data["PhotoImg"],
				$id
				);
				$st->execute();
		}
		if(!empty($data["SignatureImg"]))
		{
			$q = "UPDATE ast_members SET 
						SignatureImg=?
					  WHERE MemberId=?";
				$st = $this->conn->prepare($q);
				
				$st->bind_param(
				"si",
				$data["SignatureImg"],
				$id
				);
				$st->execute();
				
		}
		if(!empty($data["AadhaarImg"]))
		{
			$q = "UPDATE ast_members SET 
						AadhaarImg=?
					  WHERE MemberId=?";
				$st = $this->conn->prepare($q);
				
				$st->bind_param(
				"si",
				$data["AadhaarImg"],
				$id
				);
				$st->execute();
		}
		if(!empty($data["PancardImg"]))
		{
			$q = "UPDATE ast_members SET 
						PancardImg=?
					  WHERE MemberId=?";
				$st = $this->conn->prepare($q);
				
				$st->bind_param(
				"si",
				$data["PancardImg"],
				$id
				);
				$st->execute();
		}
		
			$q = "UPDATE ast_members SET 
			FullName=?, Aadhaar=?, MobileNo=?, PermenantAddress=?, PresentAddress=?,
			NomineeName=?, NomineeAge=?, NomineeAddress=?, NomineeMobiles=?, SDWName=?,
			DateOfBirth=?, PanNumber=?, EducatinalQualification=?, Occupation=?,
			District=?, State=?, PinCode=?, Associate=?
			WHERE MemberId=?";
	
			$st = $this->conn->prepare($q);
	
			$st->bind_param(
				"ssisssissssssssiiii",
				$data["FullName"],
				$data["Aadhaar"],
				$data["MobileNo"],
				$data["PermenantAddress"],
				$data["PresentAddress"],
				$data["NomineeName"],
				$data["NomineeAge"],
				$data["NomineeAddress"],
				$data["NomineeMobiles"],
				$data["SDWName"],
				$data["DateOfBirth"],
				$data["PanNumber"],
				$data["EducatinalQualification"],
				$data["Occupation"],
				$data["District"],
				$data["State"],
				$data["PinCode"],
				$data["Associate"],
				$id
			);
        return $st->execute();
    }

    public function delete($id) {
        $q = "DELETE FROM ast_members WHERE MemberId=?";
        $st = $this->conn->prepare($q);
        $st->bind_param("i", $id);
        return $st->execute();
    }
}
?>
