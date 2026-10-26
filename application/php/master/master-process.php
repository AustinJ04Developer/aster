<?php
// Master Process
// Handle form submission
require_once '../../settings/config.php';
require_once '../sql-class/master-sql-class.php';
global $dbh;
// DEGREE ADD EDIT CODE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors) && isset($_POST['degree-submit'])) {
    $degreeName = filter_input(INPUT_POST, 'degree_name', FILTER_SANITIZE_STRING);
    $noOfYear = filter_input(INPUT_POST, 'no_of_year', FILTER_VALIDATE_INT);
    $editId = filter_input(INPUT_POST, 'edit_id', FILTER_VALIDATE_INT) ?: 0;
    // Validation
    if (empty($degreeName)) {
        $errors[] = 'Degree Name is required.';
    }
    if ($noOfYear === false || $noOfYear <= 0) {
        $errors[] = 'Number of Years must be a positive integer.';
    }
    if (empty($errors)) {
        try {
				$cls_master = new COEMasterSQL; // sql-class/sl-class.php
	   			list($success,$mode) = $cls_master->InsertUpdateDegreeMaster($degreeName,$noOfYear,$editId);
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
	if($mode == 1)
		header('Location: ../../views/coe-master-degree.php?s=1');
	else
		header('Location: ../../views/coe-master-degree.php?s=2');
}

// BATCH ADD EDIT CODE
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors) && isset($_POST['batch-submit'])) {
    $batchName = filter_input(INPUT_POST, 'batch_name', FILTER_SANITIZE_STRING);
    $fromYear = filter_input(INPUT_POST, 'from_year', FILTER_VALIDATE_INT);
    $toYear = filter_input(INPUT_POST, 'to_year', FILTER_VALIDATE_INT);
    $did = filter_input(INPUT_POST, 'did', FILTER_VALIDATE_INT);
    $editId = filter_input(INPUT_POST, 'edit_id', FILTER_VALIDATE_INT) ?: 0;
    // Validation
    if (empty($batchName)) {
        $errors[] = 'Batch Name is required.';
    }
    if ($fromYear === false || $fromYear < 1900 || $fromYear > date('Y')) {
        $errors[] = 'From Year must be a valid year between 2020 and ' . date('Y') . '.';
    }
    if ($toYear === false || $toYear < $fromYear || $toYear > date('Y') + 10) {
        $errors[] = 'To Year must be a valid year after From Year and not more than 10 years in the future.';
    }
    if ($did === false || $did <= 0) {
        $errors[] = 'Please select a valid Degree.';
    }

    if (empty($errors)) {
        try {
			$cls_master = new COEMasterSQL; // sql-class/sl-class.php
			list($success,$mode) = $cls_master->InsertUpdateBatchMaster($did,$batchName,$fromYear,$toYear,$editId);
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    } // end if
	if($mode == 1)
		header('Location: ../../views/coe-master-batch.php?s=1');
	else
		header('Location: ../../views/coe-master-batch.php?s=2');
}
?>