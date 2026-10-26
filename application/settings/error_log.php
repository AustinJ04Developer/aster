<?php
function ErrorDataInsert($page,$function_name,$message)
{
	global $dbh;
	$message = str_replace("'","\'",$message);
	$sql = "INSERT INTO pdn_error_log(Date,PageName,FunctionName,Message) VALUES ('".date('Y-m-d')."','".$page."','".$function_name."','".$message."')";
	$insert_error_message = $dbh->exec($sql);

}
?>
