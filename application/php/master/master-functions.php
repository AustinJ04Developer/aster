<?php
// Master Functions
// Handle edit request
function EditDegree($editId)
{
	try
	{
		global $dbh;
		$stmt = $dbh->prepare('SELECT Date, DegreeName, NoOfYear FROM coe_master_degree WHERE DID = ?');
		$stmt->execute([$editId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
	}
	catch (PDOException $e)
	{
		ErrorTracking('master-functions','EditDegree',$e->getMessage());
	}
}


?>