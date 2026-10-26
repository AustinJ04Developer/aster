<?php
function LogFile($sdid,$interest)
{
	$log_file = $_SERVER["DOCUMENT_ROOT"]."/mpdn/application/files/log/log.txt";
	$timestamp = time(); 
 $txt = date("F d, Y h:i:s A", $timestamp).'|'.'SearchSDInterestData'.'|'.'ACCNO'.'|'.$sdid.'|'.$interest;

 $myfile = file_put_contents($log_file, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
 $error_while_insert++;
}
?>