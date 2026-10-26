<?php
include_once("../settings/config.php");
global $dbh;
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
// Handle AJAX request
if (isset($_POST['degree'])) {
	 	$degree = $_POST['degree'];
        $stmt = $dbh->prepare("SELECT NoOfYear FROM coe_master_degree WHERE DID =  :degree");
        $stmt->execute(['degree' => $degree]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $noofyear = $row['NoOfYear'];
		$current_year = date('Y');
		$to_year = (int)$current_year+$noofyear;
		echo $to_year;
    } else {
        echo "No degree available.";
    }
}
?>
