<?php
include_once "../settings/config.php";
global $dbh;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT FullName FROM ast_members WHERE MemberId = " . $id;
$queryresult = $dbh->query($sql);
$queryresult->setFetchMode(PDO::FETCH_ASSOC);
$result = $queryresult->fetch();

echo $result ? $result['FullName'] : '';

?>