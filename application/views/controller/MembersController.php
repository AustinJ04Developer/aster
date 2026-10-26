<?php
require_once '../settings/db.php';
require_once '../php/global-functions.php';
require_once 'model/MembersModel.php';

class MembersController {

    private $model;

    public function __construct($conn) {
        $this->model = new MembersModel($conn);
    }
	public function getDistricts() {
		if (!isset($_GET['state_id'])) {
			echo json_encode([]);
			exit;
		}

		$stateId = $_GET['state_id'];
		$districts = $this->model->getDistrictsByState($stateId);

		header('Content-Type: application/json');
		echo json_encode($districts);
		exit;
	}

    public function handleRequest() {
		

        $data = [
            "members" => $this->model->getAll(),
            "editId" => 0,
            "form" => [],
            "success" => "",
            "errors" => []
        ];
		$data['states'] = $this->model->getStates();
		$data['usernames'] = $this->model->getAllUsers();
		$data['selectedUserId'] = '';
	    $selectedUserId = '';
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    	$data['selectedUserId'] = $_POST['associate'] ?? '';
		}
        // EDIT
        if (isset($_GET['edit_id'])) {	
            $row = $this->model->getById($_GET['edit_id']);
            if ($row) {
                $data['editId'] = $row['MemberId'];
                $data['form'] = $row;
				$data['selectedUserId'] = $row['associate'];
            }
        }

        // DELETE
        if (isset($_GET['delete_id'])) {
            $this->model->delete($_GET['delete_id']);
            header("Location: aster-master-members.php");
            exit;
        }

        // SUBMIT FORM
        if (isset($_POST['submit-member'])) {
				$photo_upload = '';
				$signature_upload='';
				$aadhaar_upload='';
				$pancard_upload='';
				$accociate = 0;
			// Upload files details
			if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
				$fileName = $_FILES['photo']['name'];
				$fileTmp  = $_FILES['photo']['tmp_name'];
				$fileSize = $_FILES['photo']['size'];
				$fileType = $_FILES['photo']['type'];
			
				// Define upload folder
				$uploadDir = "uploads/photo/";
				// Create folder if not exists
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0777, true);
				}
				// Create unique file name
				$newFileName = time() . "_" . basename($fileName);
			
				$photo_upload_path = $uploadDir . $newFileName;
			
				if (move_uploaded_file($fileTmp, $photo_upload_path)) {
					// Save file path into form array
					$photo_upload = $photo_upload_path;
				} else {
					$photo_upload = '';
				}
			}
			if (isset($_FILES['signature']) && $_FILES['signature']['error'] == 0) {
				$fileName = $_FILES['signature']['name'];
				$fileTmp  = $_FILES['signature']['tmp_name'];
				$fileSize = $_FILES['signature']['size'];
				$fileType = $_FILES['signature']['type'];
			
				// Define upload folder
				$uploadDir = "uploads/signature/";
				// Create folder if not exists
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0777, true);
				}
				// Create unique file name
				$newFileName = time() . "_" . basename($fileName);
			
				$signature_upload_path = $uploadDir . $newFileName;
			
				if (move_uploaded_file($fileTmp, $signature_upload_path)) {
					// Save file path into form array
					$signature_upload = $signature_upload_path;
				} else {
					$signature_upload = '';
				}
			}
			if (isset($_FILES['aadhaar']) && $_FILES['aadhaar']['error'] == 0) {
				$fileName = $_FILES['aadhaar']['name'];
				$fileTmp  = $_FILES['aadhaar']['tmp_name'];
				$fileSize = $_FILES['aadhaar']['size'];
				$fileType = $_FILES['aadhaar']['type'];
			
				// Define upload folder
				$uploadDir = "uploads/aadhaar/";
				// Create folder if not exists
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0777, true);
				}
				// Create unique file name
				$newFileName = time() . "_" . basename($fileName);
			
				$aadhaar_upload_path = $uploadDir . $newFileName;
			
				if (move_uploaded_file($fileTmp, $aadhaar_upload_path)) {
					// Save file path into form array
					$aadhaar_upload = $aadhaar_upload_path;
				} else {
					$aadhaar_upload = '';
				}
			}
			
			if (isset($_FILES['pancard']) && $_FILES['pancard']['error'] == 0) {
				$fileName = $_FILES['pancard']['name'];
				$fileTmp  = $_FILES['pancard']['tmp_name'];
				$fileSize = $_FILES['pancard']['size'];
				$fileType = $_FILES['pancard']['type'];
			
				// Define upload folder
				$uploadDir = "uploads/pancard/";
				// Create folder if not exists
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0777, true);
				}
				// Create unique file name
				$newFileName = time() . "_" . basename($fileName);
			
				$pancard_upload_path = $uploadDir . $newFileName;
			
				if (move_uploaded_file($fileTmp, $pancard_upload_path)) {
					// Save file path into form array
					$pancard_upload = $pancard_upload_path;
				} else {
					$pancard_upload = '';
				}
			}
        	    
			$form = [
                "FullName" => trim($_POST["FullName"]),
                "Aadhaar" => trim($_POST["Aadhaar"]),
                "MobileNo" => trim($_POST["MobileNo"]),
                "PermenantAddress" => trim($_POST["PermenantAddress"]),
                "PresentAddress" => trim($_POST["PresentAddress"]),
                "NomineeName" => trim($_POST["NomineeName"]),
                "NomineeAge" => trim($_POST["NomineeAge"]),
                "NomineeAddress" => trim($_POST["NomineeAddress"]),
                "NomineeMobiles" => trim($_POST["NomineeMobiles"]),
                "SDWName" => trim($_POST["SDWName"]),
                "DateOfBirth" =>  checkIsEmpty(trim($_POST["DateOfBirth"])),
                "PanNumber" => trim($_POST["PanNumber"]),
                "EducatinalQualification" => trim($_POST["EducatinalQualification"]),
                "Occupation" => trim($_POST["Occupation"]),
                "District" => trim($_POST["District"]),
                "State" => trim($_POST["State"]),
                "PinCode" => trim($_POST["PinCode"]),
                "ApprovedBy" => $_SESSION["UserId"],
				"PhotoImg" => trim($photo_upload),
				"SignatureImg" => trim($signature_upload),
				"AadhaarImg" => trim($aadhaar_upload),
				"PancardImg" => trim($pancard_upload),
				"Associate" => trim($_POST["associate"])
            ];

            if ($_POST["edit_id"] > 0) {
                $this->model->update($_POST["edit_id"], $form);
                $data["success"] = "Member updated successfully.";
            } else {
                $newId = $this->model->insert($form);

                // Auto-generate MemberCode
                $this->model->updateMemberCode($newId, "AST-" . $newId);

                $data["success"] = "Member registered successfully.";
            }

            $data["members"] = $this->model->getAll();
        }

        return $data;
    }
}
?>
