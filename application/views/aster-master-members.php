<?php
session_start();
// Secure session handling (same as your savings view)
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}
require_once '../settings/config.php';
require_once 'controller/MembersController.php';

$controller = new MembersController($conn);
if (isset($_GET['action']) && $_GET['action'] === 'getDistricts') {
    $controller->getDistricts();
    exit;
}
$data = $controller->handleRequest();
extract($data);
//$usernames = $data['usernames'] ?? [];
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Member Registration</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .form-group { margin-bottom: 15px; }
        textarea { resize: none; }
        .page-header { margin-top: 0; }
        .mt-3 { margin-top: 20px; }
        .mandatory { color: red; }
    </style>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
/* ---- Compact Modern Calendar ---- */
.flatpickr-calendar {
    font-size: 12px !important;        /* Smaller calendar */
    width: 230px !important;
}

.flatpickr-day {
    max-width: 28px !important;
    height: 28px !important;
    line-height: 28px !important;
    border-radius: 6px !important;
}

.flatpickr-months .flatpickr-month {
    height: 32px !important;
}

.flatpickr-monthDropdown-months,
.flatpickr-current-month input.cur-year {
    font-size: 12px !important;
}

.flatpickr-day.today {
    background: #ffba00 !important;
    color: black !important;
    font-weight: 600 !important;
}

.flatpickr-day:hover {
    background: rgba(255,255,255,0.3) !important;
}
</style>

</head>

<body>
<div id="wrapper">

<?php include_once "header.php"; ?>

<div id="page-wrapper">

    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Member Registration</h2>
            <hr class="hr-background">
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <h6 class="madatory-caption">
                <span class="mandatory">*</span> Fields are mandatory
            </h6>
        </div>
    </div>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <p><?php echo htmlspecialchars($err); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- FORM START -->
    <form method="post" action="aster-master-members.php" id="member-form" onSubmit="return validateMemberForm()" enctype="multipart/form-data">

        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['UserId']; ?>">

        <!-- Full Name / Aadhaar / Mobile -->
        <div class="row">
            <div class="col-md-6">
                <label> Full Name <span class="mandatory">*</span></label>
                <input type="text" name="FullName" id="FullName" class="form-control"
      			 required oninput="blockInvalidName(this)"value="<?php echo $form['FullName'] ?? ''; ?>">
				<small id="FullNameWarn" class="text-danger d-none">Only alphabets and spaces allowed</small>


            </div>

            <div class="col-md-3">
                <label>Aadhaar </label>
                <input type="text" name="Aadhaar" id="Aadhaar" maxlength="12"
				   class="form-control" onfocusout="validateAadhaarFinal()" value="<?php echo $form['Aadhaar'] ?? ''; ?>">
				<small id="AadhaarWarn" class="text-danger d-none">Enter 12 digit Aadhaar number</small>

            </div>

            <div class="col-md-3">
                <label>Mobile No <span class="mandatory">*</span></label>
                <input type="text" name="MobileNo" id="MobileNo" maxlength="10"
					   class="form-control" required oninput="blockMobileInput(this)"
					   onfocusout="validateMobileLength()"value="<?php echo $form['MobileNo'] ?? ''; ?>">
				<small id="MobileWarn" class="text-danger d-none">Enter 10 digit mobile number</small>
			</div>
        </div>

        <!-- Address Row -->
        <div class="row">
            <div class="col-md-6">
                <label>Permanent Address <span class="mandatory">*</span></label>
                <textarea name="PermenantAddress" id="PermenantAddress" rows="3" class="form-control" required><?php echo $form['PermenantAddress'] ?? ''; ?></textarea>
            </div>

            <div class="col-md-6">
			
                <label>Present Address <span class="mandatory">*</span></label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="myCheck"  onclick="checkStatus()"> Same as Permanent Address
                <textarea name="PresentAddress" id="PresentAddress" rows="3" class="form-control" required><?php echo $form['PresentAddress'] ?? ''; ?></textarea>
            </div>
        </div>

        <!-- Nominee -->
        <div class="row">
            <div class="col-md-3">
                <label>Nominee Name <span class="mandatory">*</span></label>
                <input type="text" name="NomineeName" id="NomineeName" class="form-control"
					   required oninput="validateName(this)"value="<?php echo $form['NomineeName'] ?? ''; ?>">
				<small id="NomineeNameWarn" class="text-danger d-none">Only alphabets and spaces allowed</small>

            </div>

            <div class="col-md-2">
                <label>Nominee Age <span class="mandatory">*</span></label>
                <input type="number" name="NomineeAge" id="NomineeAge" maxlength="3"
       			class="form-control" required oninput="validateAge(this)"
                       value="<?php echo $form['NomineeAge'] ?? ''; ?>" required>
				
            </div>

            <div class="col-md-3">
                <label>Nominee Mobile</label>
               <input type="text" name="NomineeMobiles" id="NomineeMobiles"
						   maxlength="10" class="form-control"
						   oninput="validateOptionalMobile(this)"
                        value="<?php echo $form['NomineeMobiles'] ?? ''; ?>">
			
            </div>

            <div class="col-md-4">
                <label>Nominee Address</label>
                <textarea name="NomineeAddress" rows="3" class="form-control"><?php echo $form['NomineeAddress'] ?? ''; ?></textarea>
            </div>
        </div>

        <!-- SDW / DOB / PAN -->
        <div class="row">
            <div class="col-md-4">
                <label>Relation Son / Daughter / Wife of (SDW) <span class="mandatory">*</span></label>
                <input type="text" name="SDWName" id="SDWName" class="form-control"
       				required oninput="validateName(this)"
                       required value="<?php echo $form['SDWName'] ?? ''; ?>">
            </div>

			<div class="col-md-4">
				<label>Date Of Birth</label>
				<!--<input type="text" name="DateOfBirth" id="dob" class="form-control"
					   value="<?php echo date('d/m/Y',strtotime($form['DateOfBirth'])) ?? ''; ?>" autocomplete="off">-->
				<input type="text" id="current-date" value="<?php if(!empty($form['DateOfBirth'])) echo date('d/m/Y',strtotime($form['DateOfBirth'])) ; else ''; ?>" name="DateOfBirth" class="form-control">
			</div>



            <div class="col-md-4">
                <label>PAN Number</label>
                <input type="text" name="PanNumber" id="PanNumber" maxlength="10"
				  class="form-control" onfocusout="validatePAN()"
				  value="<?php echo $form['PanNumber'] ?? ''; ?>">
				<small id="PanWarn" class="text-danger d-none">Enter 10 digit PAN Number</small>
				
            </div>
        </div>

        <!-- Education / Occupation -->
        <div class="row">
            <div class="col-md-6">
                <label>Educational Qualification</label>
                <input type="text" name="EducatinalQualification" class="form-control"
                       value="<?php echo $form['EducatinalQualification'] ?? ''; ?>">
            </div>

            <div class="col-md-6">
                <label>Occupation</label>
                <input type="text" name="Occupation" class="form-control"
                       value="<?php echo $form['Occupation'] ?? ''; ?>">
            </div>
        </div>

        <!-- State / District / Pin -->
        <div class="row">
		<div class="col-md-4">
			<label>State <span class="mandatory">*</span></label>
			<select name="State" id="State" class="form-control" required onChange="loadDistricts()">
				<option value="">Select State</option>
				<?php foreach($states as $state): ?>
					<option value="<?php echo $state['SID']; ?>"
						<?php echo isset($form['State']) && $form['State'] == $state['SID'] ? 'selected' : ''; ?>>
						<?php echo $state['StateName']; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="col-md-4">
			<label>District <span class="mandatory">*</span></label>
			<select name="District" id="District" class="form-control">
				<option value="">--Select District--</option>

				<?php foreach ($districts as $d) { ?>
					<option value="<?php echo $d['DID']; ?>" 
						<?php if (($form['District'] ?? '') == $d['DID']) { echo "selected"; } ?>>
						<?php echo $d['DistrictName']; ?>
					</option>
				<?php } ?>
			</select>


		</div>

            <div class="col-md-4">
                <label>Pin Code <span class="mandatory">*</span></label>
				<input type="text" name="PinCode" id="PinCode" maxlength="6"
       				class="form-control" required oninput="validatePin(this)"
       				value="<?php echo $form['PinCode'] ?? ''; ?>">
				<small id="PinWarn" class="text-danger d-none">Enter 6 digit PIN code</small>
        </div>
        
			
</div>
         <div class="row">
            <div class="col-md-3">
               <div class="form-group">
                <label for="image_file">Photo Upload</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" >
                    <?php if(!empty($form['Photo'])): ?>
        <a href="../uploads/photos/<?php echo $form['Photo']; ?>" target="_blank">
            View Photo
        </a>
    <?php endif; ?>
                </div>
            </div>
                        <div class="col-md-3">
               <div class="form-group">
                <label for="image_file">Signature Upload</label>
                    <input type="file" class="form-control" id="signature" name="signature" accept="image/*" >
                    <?php if(!empty($form['Signature'])): ?>
        <a href="../uploads/signatures/<?php echo $form['Signature']; ?>" target="_blank">
            View Signature
        </a>
    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
               <div class="form-group">
                <label for="image_file">Aadhar Upload</label>
                    <input type="file" class="form-control" id="aadhaar_file" name="aadhaar_file" accept="image/*" >
                    <?php if(!empty($form['AadhaarFile'])): ?>
        <a href="../uploads/aadhaar/<?php echo $form['AadhaarFile']; ?>" target="_blank">
            View Aadhaar
        </a>
    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
               <div class="form-group">
                <label for="image_file">PAN Upload</label>
                    <input type="file" class="form-control" id="pan_file" name="pan_file" accept="image/*" >
                    <?php if(!empty($form['PanFile'])): ?>
        <a href="../uploads/pan/<?php echo $form['PanFile']; ?>" target="_blank">
            View PAN
        </a>
    <?php endif; ?>
                </div>
            </div>
                    </div>
  <div class="row">
 <div class="col-md-4">
              <div class="form-group">
              	<label for="role">Associate<span class="mandatory">*</span></label>
      				<select name="associate" class="form-control" required>
                    <option value="">Select </option>
                    
        				<?php foreach ($usernames as $username): ?>
                                        <option value="<?php echo $username['UserId']; ?>" 
                                                <?php echo ($username['UserId'] == $selectedUserId) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($username['UserName']); ?>
                                        </option>
                                    <?php endforeach; ?>
     			    </select>
              </div>
            </div>
 </div>
        <!-- Buttons -->
        <div class="row mt-3">
            <div class="col-md-12">
                <a href="aster-master-members.php" class="btn btn-primary">New</a>

                <button type="submit" name="submit-member" class="btn btn-success">
                    <?php echo ($editId > 0) ? "Update" : "Add"; ?> Member
                </button>

                <?php if ($editId > 0): ?>
                    <a href="aster-master-members.php" class="btn btn-danger">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </div>

    </form>

    <br>

    <!-- TABLE -->
	<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr class="header-row">
                <th>SlNo</th>
                <th>MemberCode</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Aadhaar</th>
                <th>Permanent Address</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php if (!empty($members)): ?>
            <?php foreach ($members as $i => $row): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $row['MemberCode']; ?></td>
                    <td><?php echo $row['FullName']; ?></td>
                    <td><?php echo $row['MobileNo']; ?></td>
                    <td><?php echo $row['Aadhaar']; ?></td>
                    <td><?php echo nl2br($row['PermenantAddress']); ?></td>
                    <td>
                        <a href="?edit_id=<?php echo $row['MemberId']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete_id=<?php echo $row['MemberId']; ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure to delete?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
  </div>
</div>

    <?php include_once "footer.php"; ?>
	
</div>

<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
 function checkStatus() {
    var checkBox = document.getElementById("myCheck");
    if (checkBox.checked == true){
	  document.getElementById("PresentAddress").value = document.getElementById("PermenantAddress").value;
    } else {
     document.getElementById("PresentAddress").value = '';
    }
  }
</script>

<script>
    // Initialize datepicker (same as your other page)
    $(function () {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    $(document).ready(function () {
        $('#members-table').DataTable({
            responsive: true,
            ordering: true,
            searching: true,
            pageLength: 10
        });
    });



	// ✔ Only alphabets + space
	function blockInvalidName(input) {
    // Allow only alphabets and space
    input.value = input.value.replace(/[^A-Za-z ]/g, '');

    // Optional warning (shows only when user tries invalid input)
    if (/[^A-Za-z ]/.test(input.value)) {
        document.getElementById("FullNameWarn").classList.remove("d-none");
    } else {
        document.getElementById("FullNameWarn").classList.add("d-none");
    }
}

	// ✔ Aadhaar live – limit to digits
	function validateAadhaarLive(input) {
		input.value = input.value.replace(/\D/g, ""); // digits only
		if (input.value.length !== 12) {
			document.getElementById("AadhaarWarn").classList.remove("d-none");
		} else {
			document.getElementById("AadhaarWarn").classList.add("d-none");
		}
	}

	// ✔ Aadhaar final check – 12 digits
	function validateAadhaarFinal() {
		let a = document.getElementById("Aadhaar").value;
		if(a === "")
			return true;
		if (a.length !== 12) {
			alert("Invalid Aadhaar Number (must be 12 digits)");
			document.getElementById("Aadhaar").value = "";
		}
	}

	//PINCODE
	
		function validatePin(input) {
		// Allow only numbers
		input.value = input.value.replace(/[^0-9]/g, '');

		const warn = document.getElementById("PinWarn");

		if (input.value.length === 6) {
			warn.classList.add("d-none"); // hide warning
		} else {
			warn.classList.remove("d-none"); // show warning
		}
								}

	
			// ✔ Mobile – 10 digits

		// 1️⃣ Block invalid input live
		function blockMobileInput(input) {
			// Allow only digits
			input.value = input.value.replace(/[^0-9]/g, '');
			
		}

		// 2️⃣ Validate length on focus out
		function validateMobileLength() {
			const input = document.getElementById("MobileNo");
			if(input.value === "")
				return true;
			if (input.value.length !== 10) {
				alert("Mobile number must be exactly 10 digits.");
				document.getElementById("MobileNo").value = "";
			} else {
				document.getElementById("MobileWarn").classList.add("d-none");
			}
		}



	// ✔ Nominee Age – only digits, max 3
	function validateAge(input) {
		input.value = input.value.replace(/\D/g, "");
		if (input.value.length > 3) input.value = input.value.slice(0, 3);
	}

	// ✔ Optional Mobile – validate only if entered
	function validateOptionalMobile(input) {
		input.value = input.value.replace(/\D/g, "");
		if (input.value !== "" && input.value.length !== 10) {
			document.getElementById("NomineeMobileWarn").classList.remove("d-none");
		} else {
			document.getElementById("NomineeMobileWarn").classList.add("d-none");
		}
	}

	// ✔ PAN – alphanumeric, 10 characters
	function validatePAN() {
		let pan = document.getElementById("PanNumber").value;
		const regex = /^[A-Za-z0-9]{10}$/;

		if (pan === "") return;

		if (!regex.test(pan)) {
			alert("Invalid PAN Number (10 alphanumeric characters)");
			document.getElementById("PanNumber").value = "";
		}
	}

	// ✔ PIN – numeric, 6 digits
	function validatePin(input) {
    let warn = document.getElementById("PinWarn");

    // If non-numeric char entered
    if (/[^0-9]/.test(input.value)) {
        input.value = input.value.replace(/[^0-9]/g, "");
        warn.classList.remove("d-none");
        warn.textContent = "Only numbers allowed";
        return;
    }

    // If digits less than 6
    if (input.value.length > 0 && input.value.length < 6) {
        warn.classList.remove("d-none");
        warn.textContent = "Enter 6 digit PIN code";
        return;
    }

    // If exactly 6 digits → OK
    if (input.value.length === 6) {
        warn.classList.add("d-none");
        return;
    }
}




		function updateDistrict() {
		var stateId = document.getElementById('State').value;

		if(stateId) {
			$.ajax({
				url: 'get_district.php',
				type: 'GET',
				data: { sid: stateId },
				success: function(response) {
					// Assuming response is JSON { "district": "District Name" }
					var data = JSON.parse(response);
					document.getElementById('District').value = data.district;
				}
			});
		} else {
			document.getElementById('District').value = '';
		}
	}
document.addEventListener("DOMContentLoaded", function () {
    let state = document.getElementById("State").value;
    let district = "<?php echo $form['District'] ?? ''; ?>";

    if (state !== "") {
        loadDistricts(state, district);
    }
});
function loadDistricts(stateId, selectedDistrict = "") {
    if (stateId === "") {
        document.getElementById("District").innerHTML = '<option value="">-- Select --</option>';
        return;
    }

    $.ajax({
        url: "get_districts.php",
        type: "POST",
        data: { SID: stateId },
        success: function(response) {
            let districts = JSON.parse(response);
            let options = '<option value="">-- Select --</option>';

            districts.forEach(function(d) {
                options += `<option value="${d.DID}" ${selectedDistrict == d.DID ? "selected" : ""}>${d.DistrictName}</option>`;
            });

            document.getElementById("District").innerHTML = options;
        }
    });
}

</script>
<script>



</script>
<script>
<?php if ($editId > 0): ?>
    loadDistricts();
<?php endif; ?>
</script>
<script>
    flatpickr("#dob", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        minDate: "1900-01-01",
        maxDate: "2025-12-31",
        defaultDate: "<?php echo $form['DateOfBirth'] ?? ''; ?>",
        disableMobile: false, // shows mobile-friendly date picker
    });
</script>

<script>
function loadDistricts() {
    let stateId = document.getElementById("State").value;

    if (stateId === "") {
        document.getElementById("District").innerHTML = '<option value="">Select District</option>';
        return;
    }

    fetch("aster-master-members.php?action=getDistricts&state_id=" + stateId)
        .then(response => response.json())
        .then(data => {
            let districtDropdown = document.getElementById("District");
            districtDropdown.innerHTML = '<option value="">Select District</option>';

            data.forEach(district => {
                districtDropdown.innerHTML += 
                    `<option value="${district.DID}">${district.DistrictName}</option>`;
            });
        })
        .catch(err => {
            console.error("Error loading districts:", err);
        });
}

// your validation functions here…

function loadDistricts() {
    const stateId = document.getElementById("State").value;
    const districtDropdown = document.getElementById("District");

    if (!stateId) {
        districtDropdown.innerHTML = "<option value=''>--Select District--</option>";
        return;
    }

    fetch("aster-master-members.php?action=getDistricts&state_id=" + stateId)
        .then(res => res.json())
        .then(data => {
            districtDropdown.innerHTML = "<option value=''>--Select District--</option>";

            data.forEach(item => {
                let selected = "<?php echo $form['District'] ?? ''; ?>" == item.DID ? "selected" : "";
                districtDropdown.innerHTML += `<option value="${item.DID}" ${selected}>${item.DistrictName}</option>`;
            });
        });
}


window.onload = function () {
    if ("<?php echo $editId; ?>" > 0) {
        loadDistricts(); // auto-load on Edit
    }
};
	function checkDuplicate(type, value, warningId) {
    const editId = document.querySelector("input[name='edit_id']").value || 0;
    if (!value) return;

    fetch(`check_duplicate.php?type=${type}&value=${value}&editId=${editId}`)
        .then(res => res.json())
        .then(data => {
            const warn = document.getElementById(warningId);
            if (data.exists) {
                warn.classList.remove("d-none");
                warn.textContent = `${type.toUpperCase()} already exists!`;
            } else {
                warn.classList.add("d-none");
            }
        })
        .catch(err => console.error(err));
}

// Real-time checks
document.getElementById("Aadhaar").addEventListener("blur", function() {
    checkDuplicate('aadhaar', this.value, 'AadhaarWarn');
});

document.getElementById("PanNumber").addEventListener("blur", function() {
    checkDuplicate('pan', this.value, 'PanWarn');
});

document.getElementById("MobileNo").addEventListener("blur", function() {
    checkDuplicate('mobile', this.value, 'MobileWarn');
});

</script>
<script type="text/javascript">
function validateMemberForm()
{

	return true;
}
 
</script>
</body>
</html>
<?php $conn->close(); ?>
