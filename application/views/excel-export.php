<?php
session_start();
if(isset($_POST['submit']))
{
$filename = $_POST['excel-file-nmae'].'.xls';
//$export_type = $_POST['export-type'];
header('Content-type: application/excel');
header('Content-Disposition: attachment; filename='.$filename);
//echo "\xEF\xBB\xBF"; // UTF-8 BOM
/*if($export_type == 1)
	$data = $_SESSION['PRINTREPORT'];
else*/
	$data = $_SESSION['PRINTREPORT'];
echo $data;
}
?>