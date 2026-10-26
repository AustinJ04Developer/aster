<?php
//include_once("../settings/config.php");

/*function CheckIfCurrentUserManagerIncharge($userid)
{
global $dbh; 
	try
	{
		$sql = "SELECT ToRoleId FROM  pdn_manager_incharge_setup WHERE IsDeleted = 0 AND Status = 1 AND DATE(DateFrom) >= '".date("Y-m-d")."' AND 
					DATE(DateTo) <= '".date("Y-m-d")."' AND ToUser = ".$userid;
		$query_sql = $dbh->query($sql);
		$query_sql->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_sql->fetch();	
	//	print $sql;
	//	exit;
		return $result["ToRoleId"];
		//if($result["ToRoleId"] === NULL)
		
		//exit;
		//return array($result["AYID"],$result["AccountStartDate"]);
	}
	catch (PDOException $e)
	{
		 ErrorTracking('global-functions','CheckIfCurrentUserManagerIncharge',$e->getMessage());
	}
}
*/


function checkIsEmpty($date)
{
	try
	{
		if($date == '')
		{
			$dateField = 'NULL';
		}
		else
		{
			$new_date = str_replace('/', '-', $date);
			$dateField = date("Y-m-d", strtotime($new_date) );
		}
		return $dateField;
	}
	catch (PDOException $e)
	{
		 ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
	}
}
function checkIsEmptyDateTime($date)
{
	try
	{
		if($date == '')
		{
			$dateField = 'NULL';
		}
		else
		{
			$new_date = str_replace('/', '-', $date);
			$dateField = date("Y-m-d H:i:s", strtotime($new_date) );
		}
		return $dateField;
	}
	catch (PDOException $e)
	{
		 ErrorTracking('global-functions','checkIsEmptyDateTime',$e->getMessage());
	}
}
function logoutuser($uid)
{
 global $dbh;
 	try
	{
	if($uid != 1)
	{
	$stmt = "DELETE FROM logged where loginid='".$uid."'";
	$query = $dbh->prepare($stmt);
	$success = $query->execute();
	header('Location: index.php');
	}
	}
	catch (PDOException $e)
	{
		 ErrorTracking('global-functions','logoutuser',$e->getMessage());
	}
}


function maxOfAll($table,$field) // Maximum value of primary key and is used to show the account number ex: SD1, SD2..FD1,FD2,...
{
 	global $dbh;
	try
	{
		$sql = "SELECT MAX(".$field.") AS maxid
				FROM ".$table."
				WHERE 1";// do not user IsDeleted = 0 because it is primary key and if deleted any reocord flag is applied instead of delete
		$queryresult = $dbh->query($sql);
		$queryresult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryresult->fetch();
		if (($result['maxid'])>=1) 
		{
			$maxid = $result['maxid'];
			return $maxid ;
			
		}
		else
		{
			return 0;
		}
	}
	catch (PDOException $e)
	{
		ErrorTracking('global-functions','maxOfAll',$e->getMessage());
	}
}


//Find the current balance for any Account Type Pass TableName,BalanceField,accountnoField,accountNo

//Find EQNO List waiting for 
function FindEditWaitingForRMApproval($field_name,$table_name,$condition_field,$condition_value)
{
 global $dbh;
 	try
	{
		$sql = "SELECT ".$field_name." FROM ".$table_name." WHERE ".$condition_field."=".$condition_value ." AND IsDeleted=0";
		$query_sql = $dbh->query($sql);
		$query_sql->setFetchMode(PDO::FETCH_ASSOC);
		$arr_value_list = array();
		$i_array_counter = 0;
		while($result = $query_sql->fetch())
		{
			$arr_value_list[$i_array_counter] = $result[$field_name];
			$i_array_counter++;
		}		
		return $arr_value_list;
	}
	catch (PDOException $e)
	{
		 ErrorTracking('account-functions','FindCurrentBalance',$e->getMessage());
	}
}
//Find user name who modify the current page
function FindModifiedUserName($user_id)
{
 global $dbh;
 	try
	{
		$sql = "SELECT Name FROM user WHERE UserId = ".$user_id." AND IsDeleted=0";
		$query_sql = $dbh->query($sql);
		$query_sql->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_sql->fetch();		
		if($query_sql->rowCount() > 0)
			$user_name = $result["Name"];
		return $user_name;
	}
	catch (PDOException $e)
	{
		 ErrorTracking('account-functions','FindCurrentBalance',$e->getMessage());
	}
}
function formatDate($date)
{
	try
	{
		if($date == '')
		{
			$dateField = 'NULL';
		}
		else
		{
			$dateField = date("d/m/Y",strtotime($date));
		}
		return $dateField;
	}
	catch (PDOException $e)
	{
		 ErrorTracking('global-functions','formatDate',$e->getMessage());
	}
}

function dateDiffInDays($date1, $date2)  
{ 
    // Calulating the difference in timestamps 
    $diff = strtotime($date2) - strtotime($date1); 
    // 1 day = 24 hours 
    // 24 * 60 * 60 = 86400 seconds 
    return abs(round($diff / 86400)); 
} 



?>
