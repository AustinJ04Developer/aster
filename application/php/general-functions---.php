<?php
ob_start();
require_once '../settings/config.php';
//if (count(get_included_files()) == 1) define ('TEST_SUITE', __FILE__);
//session_start();
//include_once "../settings/config.php";
//include_once "../settings/constants.php";
//include_once "global-functions.php";
//error_reporting(0);
/**************************************************/
/**************************************************/
/****************ASTER FUNCTIONS START***************/
/**************************************************/
/**************************************************/

/*function GetMemberDetails($member_id)
{
global $dbh;
	try
	{
		$sql = "SELECT  AYID, AccountYear ,aaa,aadsd,
				FROM invt_account_year
				WHERE  MemberId=".$memberid;;
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_result->fetch();
		$ayid = $result["AYID"];
		$acc_start_date = $result["AccountYear"];
		$acc_start_date = substr($acc_start_date, 0, strpos($acc_start_date, "-"));
		return array($ayid,$acc_start_date);
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FindCurrentFinancialYearAndStartDateForADate',$e->getMessage());
	}
}*/

function getMemberFromMembersTable($memberid)
{
	global $dbh;
   	try
	{
		$sql = "SELECT 
				m.MemberId,m.FullName,m.DateOfBirth,m.Occupation,m.District,m.State,m.PinCode,m.NomineeName,m.NomineeAge,m.NomineeRelation,m.CreatedAt,d.DistrictName,s.StateName
				FROM ast_members m
				LEFT JOIN  ast_state s ON s.SID = m.State
				LEFT JOIN  ast_district d ON d.DID = m.District
				WHERE MemberId = ".$memberid;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		return array($result['MemberId'],$result['FullName'],$result['CreatedAt'],$result['DateOfBirth'],$result['Occupation'],$result['DistrictName'],$result['StateName'],
		$result['PinCode'],$result['NomineeName'],$result['NomineeAge'],$result['NomineeRelation']);
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FillUnitFields',$e->getMessage());
	}
}

// Find Current financial year and Start Date
function FindCurrentFinancialYearAndStartDateForADate($date)
{
global $dbh;
	try
	{
		$sql = "SELECT  AYID, AccountYear 
				FROM invt_account_year
				WHERE IsDeleted = 0 AND '".$date."' >= AccountStartDate AND '".$date."' <= AccountEndDate";
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_result->fetch();
		$ayid = $result["AYID"];
		$acc_start_date = $result["AccountYear"];
		$acc_start_date = substr($acc_start_date, 0, strpos($acc_start_date, "-"));
		return array($ayid,$acc_start_date);
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FindCurrentFinancialYearAndStartDateForADate',$e->getMessage());
	}
}
/**************************************************/
/**************************************************/
/****************ASTER FUNCTIONS END***************/
/**************************************************/
/**************************************************/

/*GENERAL FUNCTIONS*/
//This function is  used for show all data in grid format for the screens Designation, Entity and Division
function NumberToWords($number)
{
  // $number = 190908100.25;
   $no = floor($number);
   $point = round($number - $no, 2) * 100;
   $hundred = null;
   $digits_1 = strlen($no);
   $i = 0;
   $str = array();
   $words = array('0' => '', '1' => 'One', '2' => 'Two',
    '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
    '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
    '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
    '13' => 'Thirteen', '14' => 'Fourteen',
    '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
    '18' => 'Eighteen', '19' =>'Nineteen', '20' => 'Twenty',
    '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
    '60' => 'Sixty', '70' => 'Seventy',
    '80' => 'Eighty', '90' => 'Ninety');
   $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
   while ($i < $digits_1) {
     $divider = ($i == 2) ? 10 : 100;
     $number = floor($no % $divider);
     $no = floor($no / $divider);
     $i += ($divider == 10) ? 1 : 2;
     if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
     } else $str[] = null;
  }
  $str = array_reverse($str);
  $result = implode('', $str);
  $points = ($point) ?
    "." . $words[$point / 10] . " " . 
          $words[$point = $point % 10] : '';
  return $result;
}
// Find Current Financial Year
function convertDateFormat($inputDate) {
    // Create a DateTime object from the input date string
    $dateTime = DateTime::createFromFormat('d/m/Y H:i:s', $inputDate);

    // Check for errors in the conversion
    $errors = DateTime::getLastErrors();
    if ($errors['error_count'] > 0 || $errors['warning_count'] > 0) {
        // Handle errors as needed
        return false;
    }

    // Format the DateTime object to the desired format
    $outputDate = $dateTime->format('Y-m-d H:i:s');

    return $outputDate;
}

//This function is  used for show all data in grid format for the screens Designation, Entity and Division
function ListDataReportInGrid($key_field,$list_fields,$link,$table,$condition) // Ordedr Key Field, list of fields to show, link and table name, Condition
{
	global $dbh;
	try
	{
		$arr_field_list = explode(",",$list_fields);
		$sql = "SELECT ".$key_field.", ".$list_fields."
				FROM ".$table."
				WHERE ".$condition;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			foreach ($arr_field_list as $value) {
			if (CheckMyDate($result[$value])) {
					$table .= '<td>'.date('d/m/Y',strtotime($result[$value])).'</td>'; 
				}
			else {
					$table .= '<td>'.strtoupper($result[$value]).'</td>'; 
				}
			}
			$table .= '</tr>'; 
			$row_number++;
		}
		return $table;
	}
	catch(PDOException $e)
	{
      ErrorTracking('general-functions','ListDataReportInGrid',$e->getMessage());
	  //echo $e->getMessage();
	}
}


//Fill Values in Combobox
function FillValuesInComboBox($key_field,$show_label,$selected_value,$table_name,$condition)
{
global $dbh;
	try
	{
		$sql = "SELECT ".$key_field.", ".$show_label."  
				FROM ".$table_name."
				WHERE ".$condition;
	//	print $sql;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
		//$option .= '<option value="" disabled selected>&nbsp;</option>';
	    while ($result = $queryrersult->fetch())
		{
		  if($selected_value == $result[$key_field])
			  $option .= '<option value="'.$result[$key_field].'" selected>'.strtoupper($result[$show_label]).'</option>';
		  else
 		      $option .= '<option value="'.$result[$key_field].'">'.strtoupper($result[$show_label]).'</option>';
		}
		return $option;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FillValuesInComboBox',$e->getMessage());
	}
}
// Find whether the given value is date format or not
function CheckMyDate($date) {
  $tempDate = array();
  $is_date_or_not = false;
  $tempDate[1] = '';
  $tempDate[2] = '';
  $tempDate[0] = '';
  $tempDate = explode('-', $date);
  // checkdate(month, day, year)
  	if ( ! isset($tempDate[1])) {
	   $tempDate[1] = null;
	}
	if ( ! isset($tempDate[2])) {
	   $tempDate[1] = null;
	}
	if ( ! isset($tempDate[0])) {
	   $tempDate[1] = null;
	}
  if($tempDate[1] != null && $tempDate[2] != null && $tempDate[0] != null)
  {
  	$is_date_or_not = checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
  }
  return $is_date_or_not;
}
// Delete data from table
function DeleteDataFromTable($delete_key,$table_name,$key_field) // general-functions.php
{
	global $dbh;
   	try
	{
		$sql = "UPDATE ".$table_name." SET IsDeleted = 1 WHERE ".$key_field."=".$delete_key;
		$success = $dbh->exec($sql);
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','DeleteDataFromTable',$e->getMessage());
	}
}
//This function is  used for show all data in grid format for the screens Designation, Entity and Division
function ListDataInGrid($key_field,$list_fields,$link,$table,$condition) // Ordedr Key Field, list of fields to show, link and table name, Condition
{
	global $dbh;
	try
	{
		$arr_field_list = explode(",",$list_fields);
		$sql = "SELECT ".$key_field.", ".$list_fields."
				FROM ".$table."
				WHERE ".$condition;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			foreach ($arr_field_list as $value) {
			if (CheckMyDate($result[$value])) {
					$table .= '<td>'.date('d/m/Y',strtotime($result[$value])).'</td>'; 
				}
			else {
					$table .= '<td>'.$result[$value].'</td>'; 
				}
			}
			$table .= '<td align="center"><a href="'.$link.'?eid='.$result[$key_field].'" ><span style="color:#0000FF;font-size:16px;" class="glyphicon glyphicon-edit"></a></td>';
			$table .= '<td align="center"><a href="'.$link.'?did='.$result[$key_field].'" id = "deleteAlert">
					   <span style="color:#FF0000;font-size:16px;" class="glyphicon glyphicon-remove-circle"></a></td>';
			$table .= '</tr>'; 
			$row_number++;
		}
		return $table;
	}
	catch(PDOException $e)
	{
      ErrorTracking('general-functions','ListDataInGrid',$e->getMessage());
	  //echo $e->getMessage();
	}
}
// Find current Name of current user logged in
function FindCurrentUserName($user_id)
{
	global $dbh;
   	try
	{
		$sql = "SELECT Name
				FROM user
				WHERE IsDeleted = 0 AND UserId=".$_SESSION['UserId'];
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		return $result['Name'];
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FillUnitFields',$e->getMessage());
	}
}

/* END INVENTORY FUNCTIONS*/
function NoofDaysBetweenTwoDates($start_date,$end_date)
{
	global $dbh;
	try
	{
		$now = strtotime($end_date); // or your date as well
		$start_date = strtotime($start_date);
		$datediff = $now - $start_date;
		$no_of_days = round($datediff / (60 * 60 * 24))+1;
		return $no_of_days;
	}
	catch (PDOException $e)
	{
		 ErrorTracking('general-functions','GetJLDueList',$e->getMessage());
	}
}
// Age Calculation
function AgeFromDOB($dob)
{
	$current_date = date('Y-m-d');
	$d1 = new DateTime($current_date);
	$d2 = new DateTime($dob);
	$diff = $d2->diff($d1);
	$age = $diff->y;
	return $age;
}
// Convert Rupees in Indian Format
function RupeesIndianFormat($number)
{
	$result_value = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", bcadd($number,0,2));
	return $result_value;
}
// To find total number of pending users w
function GetPendingApprovalValue()
{
	global $dbh;
   	try
	{
		$sql = "SELECT count(UserId) AS PendingUsers
				FROM user 
				WHERE IsApproved = 0";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		$total_pending_users = $result['PendingUsers'];
		return $total_pending_users;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','GetPendingApprovalValue',$e->getMessage());
	}
}
// To find total number of blocked users
function GetBlockedUsers()
{
	global $dbh;
   	try
	{
		$sql = "SELECT count(UserId) AS BlockedUsers
				FROM user 
				WHERE IsBlocked = 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		$total_blocked_users = $result['BlockedUsers'];
		return $total_blocked_users;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','GetBlockedUsers',$e->getMessage());
	}
}
// To find total number of roles
function GetRoleCount()
{
   
   	global $dbh;
	try
	{
		$sql = "SELECT count(RoleId) AS Roles
				FROM role 
				WHERE 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		$total_roles = $result['Roles'];
		return $total_roles;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','GetRoleCount',$e->getMessage());
	}
}

// To find total number of Active Users
function GetActiveUsers()
{
	global $dbh;
	try
	{
   		$sql = "SELECT count(UserId) AS ActiveUsers
				FROM user 
				WHERE IsBlocked = 0 AND IsApproved = 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		$total_active_users = $result['ActiveUsers'];
		return $total_active_users;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','GetActiveUsers',$e->getMessage());
	}
}

// For every page insert current login id in logged table.  When the page unload the value deleted from the logged table
function InsertLoginIdInLogged($uid)
{
	global $dbh;
	if(!empty($uid))
	{
	$sql_force_logout = "SELECT loginid
                FROM logged  
				WHERE loginid = ".$uid;
	//print $sql_force_logout;
	$queryresult_force_logout = $dbh->query($sql_force_logout);
	$queryresult_force_logout->setFetchMode(PDO::FETCH_ASSOC);
	$result_logout = $queryresult_force_logout->fetch();
	if(empty($result_logout['loginid']))
	{
	$stmt = "INSERT INTO logged(loginid) VALUES(".$uid.")";
	$query = $dbh->prepare($stmt);
	$success = $query->execute();
	}
	}
}
//Get all values from designation table and filled it in controls during the edit process
function GetDesignationDetails($is_edit_mode)
{
	global $dbh;
	try
	{
		$sql = "SELECT Designation, Description,UserId
				FROM designation
				WHERE DesignationId = ".$is_edit_mode;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		return array($result["Designation"],$result["Description"]);
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','GetDesignationDetails',$e->getMessage());
	}
}

//The below function is used for delete a designation
function DeleteDesignation($designationid)
{
 global $dbh;
	   try
		{
			$stmt = "DELETE FROM designation WHERE DesignationId = :designationid";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':designationid'=> $designationid));
			if($success == 1)
			{
				header('Location: ../views/designation.php?s=3');
			}
			else
			{
				header('Location: ../views/designation.php?s=30');
			}
		}
		catch (PDOException $e)
		{
			ErrorTracking('general-functions','DeleteDesignation',$e->getMessage());
		}
}

//User is authorized or not to view each page
function UserAuthorizedToViewThisPage()
{
    global $dbh;
	include "../settings/config.php";
	$selected_role = $_SESSION['RoleId'];
	$current_page_name = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
	try
	{
		$roleid = $_SESSION['RoleId'];
		if($roleid == '')
			$roleid = 0;
		$sql = "SELECT page.Link,user_privilages.DView,user_privilages.DInsert,user_privilages.DEdit,user_privilages.DDelete 
                FROM page  
                LEFT JOIN user_privilages ON user_privilages.PageId = page.PageId
				WHERE page.Link = '".$current_page_name."' AND user_privilages.RoleId = ".$roleid."
                ";
		/*print $sql;
		exit;*/
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
 		$result = $queryrersult->fetch();
		if($result)
		{
			if($result['Link'] == $current_page_name && $result['DView'] == 1)
				return array($result['DView'],$result['DInsert'],$result['DEdit'],$result['DDelete']);
			else
				return array(0,0,0,0);
		}
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','UserAuthorizedToViewThisPage',$e->getMessage());
	}
}
function PageAuthorizationMessage()
{
$error_message =  '<div style="height:50px;margin-left:350px;margin-top:50px;font-size:18px;font-weight:bold;color:#FF0000;">Your are not authorized to view this page</div>';
return $error_message;
}
//This function is  used for show all data in grid format for the screens Designation, Entity and Division
function UserRelatedMaster($field1,$field2,$field3,$link,$table)
{
	global $dbh;
	try
	{
		$sql = "SELECT ".$field1.", ".$field2.", ".$field3." 
				FROM ".$table."
				WHERE 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
			if(strtoupper($result[$field2]) != 'ADMIN')
			{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result[$field2].'</td>';
			$table .= '<td>'.$result["Description"].'</td>';
			$table .= '<td align="center"><a href="'.$link.'?eid='.$result[$field1].'" ><img src="images/edit-icon.png"></a></td>';
			$table .= '<td align="center"><a href="'.$link.'?did='.$result[$field1].'" id = "deleteAlert"> <img src="images/delete-icon.png"></a></td>';
			$table .= '</tr>'; 
			$row_number++;
			}
		}
		return $table;
	}
	catch(PDOException $e)
	{
      ErrorTracking('general-functions','UserRleatedMaster',$e->getMessage());
	  //echo $e->getMessage();
	}
}

//Get all values from Role table and filled it in controls during the edit process BY RB
function GetRoleDetails($is_edit_mode)
{
	global $dbh;
	try
	{
		$sql = "SELECT RoleName,Description,Date,UserId
				FROM role
				WHERE RoleId = ".$is_edit_mode;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		return array($result["RoleName"],$result["Description"],$result["Date"]);
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','GetRoleDetails',$e->getMessage());
	}
}

//The below function is used for delete a role BY RB
function DeleteRole($roleid)
{
 global $dbh;
	   try
		{
			$stmt = "DELETE FROM role WHERE RoleId = :roleid";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':roleid'=> $roleid));
			if($success == 1)
			{
				header('Location: ../views/role.php?s=3');
			}
			else
			{
				header('Location: ../views/role.php?s=30');
			}
		}
		catch (PDOException $e)
		{
			ErrorTracking('general-functions','DeleteRole',$e->getMessage());
		}
}
//Admin Approval List
function UserApprovalList($field1,$field2,$field3,$field4,$link,$table1)
{
	global $dbh;
	try
	{
		$sql = "SELECT u.".$field1.", u.".$field2.", u.".$field3.", d.".$field4."
				FROM ".$table1." u, designation d 
				WHERE IsApproved = 0 AND u.DesignationId = d.DesignationId";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["Name"].'</td>';
			$table .= '<td>'.$result["UserName"].'</td>';
			$table .= '<td>'.$result["Designation"].'</td>';
			$table .= '<td align="center"><a href="'.$link.'?eid='.$result[$field1].'"><img src="images/user-approval.png"></a></td>';
			$table .= '</tr>'; 
			$row_number++;
		}
		return $table;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','UserApprovalList',$e->getMessage());
	}
}
//Edit Profile Data
//Filled Combo boxes in Edit Profile page
function EditUserProfileFields($field1,$field2,$selectedfield,$table1) //General Functions order by field list, selected id and table name
{
global $dbh;
	try
	{
		$sql = "SELECT ".$field1.", ".$field2."  
				FROM ".$table1."
				WHERE 1";
			//	print $role;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
	    while ($result = $queryrersult->fetch())
		{
		  if($selectedfield == $result[$field1])
			  $option .= '<option value="'.$result[$field1].'" selected>'.$result[$field2].'</option>';
		  else
	  	      $option .= '<option value="'.$result[$field1].'">'.$result[$field2].'</option>';
		}
		return $option;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','EditUserProfileFields',$e->getMessage());
	}
}

//User List BY RB on 23/2/16
function BlockUser($field1,$field2,$field3,$field4,$link1,$link2,$link3,$table1)
{
	global $dbh;
	try
	{
		$sql = "SELECT u.".$field1.", u.".$field2.", u.".$field3.",u.IsBlocked,d.".$field4."
				FROM ".$table1." u, designation d 
				WHERE IsApproved = 1 AND u.DesignationId = d.DesignationId";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    if(strtoupper($result["UserName"]) != 'ADMIN')
			{
			$table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["Name"].'</td>';
			$table .= '<td>'.$result["UserName"].'</td>';
			$table .= '<td>'.$result["Designation"].'</td>';
			if($result["IsBlocked"]==0)
			   $status="ACTIVE";
			 else
			   $status="BLOCKED";
			 
			$table .= '<td>'.$status.'</td>'; 
			if($status=="ACTIVE")
			{
				$table .= '<td align="center"><img src="images/active-user.png" style="opacity:0.2;"></td>';
				$table .= '<td align="center"><a href="'.$link1.'?eid='.$result[$field1].'"><img src="images/blocked-user.png"> </a></td>';
				$table .= '<td align="center"><a href="'.$link3.'?eid='.$result[$field1].'"><img src="images/active-user.png"></a></td>';
			}
			else
			{
				$table .= '<td align="center"><a href="'.$link2.'?eid='.$result[$field1].'"><img src="images/active-user.png"></a></td>';
				$table .= '<td align="center"><img src="images/blocked-user.png" style="opacity:0.2;"></td>';
				$table .= '<td align="center"><img src="images/blocked-user.png" style="opacity:0.2;"></td>';
			}
			$table .= '</tr>'; 
			$row_number++;
		}
		}
		return $table;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','BlockUser',$e->getMessage());
	}
}

//Fill Values in Page
//Fill Roles in Page
function FillValuesInPage($field1,$field2,$selected_role,$table1)
{
global $dbh;
	try
	{
		$sql = "SELECT ".$field1.", ".$field2."  
				FROM ".$table1."
				WHERE 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
		//$option .= '<option value="" disabled selected>&nbsp;</option>';
	    while ($result = $queryrersult->fetch())
		{
		  if(strtoupper($result[$field2]) != 'ADMIN')
		  {
		  if($selected_role == $result[$field1])
			  $option .= '<option value="'.$result[$field1].'" selected>'.$result[$field2].'</option>';
		  else
 		      $option .= '<option value="'.$result[$field1].'">'.$result[$field2].'</option>';
		  }
		  else
		  {
		  	if($_SESSION['UserId'] == 1)
			{
			if($selected_role == $result[$field1])
			  $option .= '<option value="'.$result[$field1].'" selected>'.$result[$field2].'</option>';
		    else
 		      $option .= '<option value="'.$result[$field1].'">'.$result[$field2].'</option>';
			}
		  }
		}
		return $option;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FillValuesInPage',$e->getMessage());
	}
}

//Logout user by manually if user close the browser without logout the system
function ForceToLogout($logout_user_id)
{
	global $dbh;
	try
	{
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   	    $sql_force_to_logout = "DELETE FROM logged WHERE loginid = ".$logout_user_id;
	    $success = $dbh->exec($sql_force_to_logout);
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','ForceToLogout',$e->getMessage());
	}
}

// Find Currently Logged in users list
function LoggedInUsers()
{
	global $dbh;
	try
	{
		$sql = "SELECT logged.loginid, user.Name, user.UserName, user.StaffCodeNumber, user.EmailId
				FROM logged 
				LEFT JOIN user 
				ON logged.loginid = user.UserId 
				ORDER BY logged.loginid";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    if(strtoupper($result["UserName"]) != 'ADMIN')
			{
			$table .= '<tr valign="center">';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["Name"].'</td>';
			$table .= '<td>'.$result["UserName"].'</td>';
			$table .= '<td>'.$result["StaffCodeNumber"].'</td>';
			$table .= '<td>'.$result["EmailId"].'</td>';
			$table .= '<td><label>Logged In</label></td>';
			$table .= '<td align="center"><a href="current-active-users.php?lid='.$result["loginid"].'"><img src="images/logout-icon.png"></a></td>';
			}
		}
		return $table;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','LoggedInUsers',$e->getMessage());
	}
}
//User Registration
//Fill Designations in User Registeration Page
function UserRegistrationMasterFields($field1,$field2,$table1)
{
global $dbh;
	try
	{
		$sql = "SELECT ".$field1.", ".$field2."  
				FROM ".$table1."
				WHERE 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
	    while ($result = $queryrersult->fetch())
		{
		  $option .= '<option value="'.$result[$field1].'">'.$result[$field2].'</option>';
		}
		return $option;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
	}
}
//Edit Profile
//Get all values from user table and filled it in controls during the edit profile process
function FillAllFieldsInUserProfile($userid)
{
	global $dbh;
	try
	{
		$sql = "SELECT Date, Name, UserName, RoleId, Password, StaffCodeNumber, DesignationId, DivisionId, EntityId, EmailId, MobileNumber, PhoneNumber, Signature,Branch
                FROM user 
                WHERE UserId = ".$userid;
		//print $sql;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		$sql_branch = "SELECT Branch FROM invt_branch WHERE BranchId = ".$result["Branch"];
	//	print $sql_branch;
		$query_branch = $dbh->query($sql_branch);
		$query_branch->setFetchMode(PDO::FETCH_ASSOC);
		$result_branch = $query_branch->fetch();
		return array($result["Name"],$result["UserName"],$result["Password"],$result["RoleId"],$result["StaffCodeNumber"],$result["DesignationId"],$result["DivisionId"],$result["EntityId"],$result["EmailId"],$result["MobileNumber"],$result["PhoneNumber"],$result_branch["Branch"]);
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','FillAllFieldsInUserProfile',$e->getMessage());
	}
}
//Active Uers list
function UserName()
{
	global $dbh;
	try
	{
		$sql = "SELECT Name,UserName
				FROM user 
				WHERE UserId = ".$_SESSION['UserId'];
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $queryrersult->fetch();
		$user_name =  $result["UserName"];
		return $user_name;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','UserName',$e->getMessage());
	}
}

//Show Dashboard Left Menu
function Dashboard($selected_role)
{
    global $dbh;
	$main_heading = '';
	$side_menu = '';
	try
	{
		$sql = "SELECT user_privilages.RoleId,  user_privilages.PageId,  user_privilages.DInsert,  user_privilages.DView, user_privilages.DEdit, 
		        user_privilages.DDelete,  page.PageName, page.Link, page_sidemenu_main_heading.MainHeading,page_sidemenu_main_heading.IconName as MainIcon, 
				page_sidemenu_main_heading.PSMID, page.IconName 
                FROM user_privilages 
                LEFT JOIN page ON user_privilages.PageId = page.PageId
                LEFT JOIN page_sidemenu_main_heading ON page_sidemenu_main_heading.PSMID = page.PSMID
                WHERE user_privilages.RoleId = ".$selected_role." AND user_privilages.DView = 1
                ORDER BY page_sidemenu_main_heading.PSMID, page.PageId";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$side_menu = '';
		$first_heading = 0;
	    while ($result = $queryrersult->fetch())
		{
		  if($main_heading != $result['MainHeading'] and $first_heading != 0)
		  {
			  $first_heading = 0;
			  $side_menu = $side_menu.'</ul></li>';
		  }
		  if($first_heading == 0)
		  {
			$main_heading = $result['MainHeading'];
		  }
		  if($main_heading == $result['MainHeading'] and $first_heading == 0)
		  {
		  	
				$side_menu = $side_menu.' <li>
							<a href="#"><i class="glyphicon '.$result["MainIcon"].'"></i> '.$result['MainHeading'].'<span class="fa arrow"></span></a>
							<ul class="nav nav-second-level collapse">';
				if(trim(strtoupper($result['PageName'])) == 'PAGE' or trim(strtoupper($result['PageName'])) == 'SIDE MENU')
				{
					
					if ($_SESSION['UserId'] == 1)
					{
						
							$side_menu = $side_menu.'<li>
				  	    	<a href="'.$result['Link'].'" ><i class="glyphicon '.$result['IconName'].'"></i> '.$result['PageName'].'</a>
				        	</li>';	
					}
				}
				else
				{
					/*if($result['Link'] == "share-master.php")
					{
						$side_menu = $side_menu.'<li>
						<a href="https://192.168.8.222/mpdn/application/views/'.$result['Link'].'" ><i class="glyphicon '.$result['IconName'].'"></i> '.$result['PageName'].'</a>
						</li>';
					}
					else
					{*/
						$side_menu = $side_menu.'<li>
						<a href="'.$result['Link'].'" ><i class="glyphicon '.$result['IconName'].'"></i> '.$result['PageName'].'</a>
						</li>';
					/*}*/
				}
		  }
		  else
		  {
			$side_menu = $side_menu. '<li>
					<a href="'.$result['Link'].'" ><i class="glyphicon '.$result['IconName'].'"></i> '.$result['PageName'].'</a>
				</li>';
		  }
		  
		  $first_heading++;
		  
		}
		return $side_menu;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','Dashboard',$e->getMessage());
	}
}

//List all roles
//Role List
function RoleList()
{
	global $dbh;
	try
	{
		$sql = "SELECT RoleName, Description
				FROM role 
				WHERE 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["RoleName"].'</td>';
			$table .= '<td>'.$result["Description"].'</td>';
			$table .= '</tr>'; 
			$row_number++;
		}
		return $table;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','RoleList',$e->getMessage());
	}
}
//List all Blocked Users
function BlockedUsersList()
{
	global $dbh;
	try
	{
		$sql = "SELECT Name,UserName,StaffCodeNumber,MobileNumber
				FROM user 
				WHERE IsBlocked = 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["Name"].'</td>';
			$table .= '<td>'.$result["UserName"].'</td>';
			$table .= '<td>'.$result["StaffCodeNumber"].'</td>';
			$table .= '<td>'.$result["MobileNumber"].'</td>';
			$table .= '</tr>'; 
			$row_number++;
		}
		return $table;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','BlockedUsersList',$e->getMessage());
	}
}
//Active Uers list
function ActiveUsersList()
{
	global $dbh;
	try
	{
		$sql = "SELECT u.Name,u.UserName,u.StaffCodeNumber,u.MobileNumber,r.RoleName
				FROM user u, role r
				WHERE u.RoleId = r.RoleId AND u.IsBlocked = 0 AND u.IsApproved = 1";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$row_number = 1;
		$table = '';
	    while ($result = $queryrersult->fetch())
		{
		    $table .= '<tr>';
			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["Name"].'</td>';
			$table .= '<td>'.$result["UserName"].'</td>';
			$table .= '<td>'.$result["RoleName"].'</td>';
			$table .= '<td>'.$result["StaffCodeNumber"].'</td>';
			$table .= '<td>'.$result["MobileNumber"].'</td>';
			$table .= '</tr>'; 
			$row_number++;
		}
		return $table;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','ActiveUsersList',$e->getMessage());
	}
}
//This function is used to fill all main headings in the side menu
function FillSideMenuMainHeadingInPage($field1,$field2,$selected_role,$table1,$selected_heading)
{
global $dbh;
	try
	{
		$sql = "SELECT ".$field1.", ".$field2."  
				FROM ".$table1."
				WHERE RoleId=".$selected_role;
		/*print $sql;
		exit;*/
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
		$option .= '<option value="" disabled selected>&nbsp;</option>';
	    while ($result = $queryrersult->fetch())
		{
		  if($selected_heading == $result[$field1])
			  $option .= '<option value="'.$result[$field1].'" selected>'.$result[$field2].'</option>';
		  else
 		      $option .= '<option value="'.$result[$field1].'">'.$result[$field2].'</option>';
		}
		return $option;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FillSideMenuMainHeadingInPage',$e->getMessage());
	}
}
//Get all values from page table and filled it in controls during the edit process BY RB
function GetPageDetails($is_edit_mode)
{
	global $dbh;
	try
	{
		$sql = "SELECT PageName,Link,Description,UserId,PSMID 
				FROM page
				WHERE PageId = ".$is_edit_mode;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		return array($result["PageName"],$result["Link"],$result["Description"],$result["PSMID"]);
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','GetPageDetails',$e->getMessage());
	}
}

// Return all state names
function FillStateValues($selected_heading)
{
	global $dbh;
	try
	{
		$sql = "SELECT STID, State 
				FROM state
				WHERE IsDeleted = 0";
		$sql_query = $dbh->query($sql);
		$sql_query->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
		$option .= '<option value="" disabled selected>&nbsp;</option>';
	    while ($result = $sql_query->fetch())
		{
		  if($selected_heading == $result['STID'])
			  $option .= '<option value="'.$result["STID"].'" selected>'.$result["State"].'</option>';
		  else
 		      $option .= '<option value="'.$result["STID"].'">'.$result["State"].'</option>';
		}
		return $option;
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','GetPageDetails',$e->getMessage());
	}
}


// Find Modified List
// Find count of modified list
	function GetModifiedList($field_name,$table_name,$condition_field,$condition_value) 
	{
	 global $dbh;
		try
		{
			$sql = "SELECT COUNT(".$field_name.") AS ".$field_name." FROM ".$table_name." WHERE IsDeleted = 0 AND ".$condition_field." > ".$condition_value."";
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();		
			if($query_sql->rowCount() > 0)
				$count = $result[$field_name];
			else
				$count = 0;
			return $count;
		}
		catch (PDOException $e)
		{
			 ErrorTracking('general-functions','GetModifiedList',$e->getMessage());
		}
	}// End Function


//Fill values in the options 
function FillValuesGeneral($field1,$field2,$selectedfield,$table1,$where_field,$where_value) //General Functions order by field list, selected id and table name
{
global $dbh;
	try
	{
		$sql = "SELECT ".$field1.", ".$field2."  
				FROM ".$table1."
				WHERE IsDeleted = 0 AND ".$where_field." = ".$where_value;
		//print $sql;
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
	    while ($result = $query_result->fetch())
		{
		  if($selectedfield == $result[$field1])
			  $option .= '<option value="'.$result[$field1].'" selected>'.$result[$field2].'</option>';
		  else
	  	      $option .= '<option value="'.$result[$field1].'">'.$result[$field2].'</option>';
		}
		return $option;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FillValuesGeneral',$e->getMessage());
	}
}

//Age Calculator
function AgeCalculator($dob){
    if(!empty($dob)){
        $birthdate = new DateTime($dob);
        $today   = new DateTime('today');
        $age = $birthdate->diff($today)->y;
        return $age;
    }else{
        return 0;
    }
}

// Find Current financial year
function FindCurrentFinancialYear()
{
global $dbh;
	try
	{
		$date = date("Y-m-d");
		$sql = "SELECT  AYID 
				FROM pdn_account_year
				WHERE IsDeleted = 0 AND '".$date."' >= AccountStartDate AND '".$date."' <= AccountEndDate";
		
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_result->fetch();
		$ayid = $result["AYID"];
		return $ayid;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FindCurrentFinancialYear',$e->getMessage());
	}
}
// Find Current financial year
function FindCurrentFinancialYearForADate($date)
{
global $dbh;
	try
	{
		$sql = "SELECT  AYID 
				FROM pdn_account_year
				WHERE IsDeleted = 0 AND '".$date."' >= AccountStartDate AND '".$date."' <= AccountEndDate";
		
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_result->fetch();
		$ayid = $result["AYID"];
		return $ayid;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FindCurrentFinancialYear',$e->getMessage());
	}
}
// Find Current financial year
function FindCurrentFinancialYearName()
{
global $dbh;
	try
	{
		$sql = "SELECT  AYID, AccountYear
				FROM pdn_account_year
				WHERE IsDeleted = 0 AND IsDefault = 1";
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_result->fetch();
		$ayid = $result["AccountYear"];
		return $ayid;
	}
	catch (PDOException $e)
	{
		ErrorTracking('general-functions','FindCurrentFinancialYear',$e->getMessage());
	}
}

// Fill all month names with selected current month
function FillMonthValues($selected_month)
{
	 global $dbh;
		try
		{
			$time=strtotime(date('Y-m-d'));
			$month=date("m",$time);
			$year=date("Y",$time);
			$current_month = (int)$month;
			if(!empty($selected_month))
				$current_month = (int)$selected_month;
			if($current_month == 1)
				$option .=  '<option selected value="1">January</option>';
			else
				$option .=  '<option value="1">January</option>';
			if($current_month == 2)
				$option .=  '<option selected value="2">February</option>';
			else
				$option .=  '<option value="2">February</option>';
			if($current_month == 3)
				$option .=  '<option selected value="3">March</option>';
			else
				$option .=  '<option value="3">March</option>';
			if($current_month == 4)
				$option .=  '<option selected value="4">April</option>';
			else
				$option .=  '<option value="4">April</option>';
			if($current_month == 5)
				$option .=  '<option selected value="5">May</option>';
			else
				$option .=  '<option value="5">May</option>';
			if($current_month == 6)
				$option .=  '<option selected value="6">June</option>';
			else
				$option .=  '<option value="6">June</option>';
			if($current_month == 7)
				$option .=  '<option selected value="7">July</option>';
			else
				$option .=  '<option value="7">July</option>';
			if($current_month == 8)
				$option .=  '<option selected value="8">August</option>';
			else
				$option .=  '<option value="8">August</option>';
			if($current_month == 9)
				$option .=  '<option selected value="9">September</option>';
			else
				$option .=  '<option value="9">September</option>';
			if($current_month == 10)
				$option .=  '<option selected value="10">October</option>';
			else
				$option .=  '<option value="10">October</option>';
			if($current_month == 11)
				$option .=  '<option selected value="11">November</option>';
			else
				$option .=  '<option value="11">November</option>';
			if($current_month == 12)
				$option .=  '<option selected value="12">December</option>';
			else
				$option .=  '<option value="12">December</option>';
			return $option;
		}
		catch (PDOException $e)
		{
			 ErrorTracking('general-functions','GetModifiedList',$e->getMessage());
		}
}// End Function
// Fill all month names with selected current month
function FillYearValues($selected_year)
{
	 global $dbh;
		try
		{
			$time=strtotime(date('Y-m-d'));
			$month=date("m",$time);
			$year=date("Y",$time);
			$current_year = (int)$year;
			if(!empty($selected_year))
				$current_year = (int)$selected_year;
			for($i_year=2010;$i_year<=2050;$i_year++)
			{
				if($current_year == $i_year)
					$option .=  '<option selected value="'.$i_year.'">'.$i_year.'</option>';
				else
					$option .=  '<option value="'.$i_year.'">'.$i_year.'</option>';
			}			
			return $option;
		}
		catch (PDOException $e)
		{
			 ErrorTracking('general-functions','GetModifiedList',$e->getMessage());
		}
}// End Function

function GetCount($table_name,$field_name,$condition) 
{
	 global $dbh;
		try
		{
			$sql = "SELECT COUNT(".$field_name.") AS ".$field_name." FROM ".$table_name." WHERE IsDeleted = 0 AND ".$condition;
	//	print $sql.'<br>';
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();		
			if($query_sql->rowCount() > 0)
				$count = $result[$field_name];
			else
				$count = 0;
			return $count;
		}
		catch (PDOException $e)
		{
			 ErrorTracking('general-functions','GetCount',$e->getMessage());
		}
}// End Function


function GetCounts($table_name,$field_name,$condition) 
{
	 global $dbh;
		try
		{
			$sql = "SELECT COUNT(".$field_name.") AS ".$field_name." FROM ".$table_name." WHERE ".$condition;
		
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();		
			if($query_sql->rowCount() > 0)
				$count = $result[$field_name];
			else
				$count = 0;
			return $count;
		}
		catch (PDOException $e)
		{
			 ErrorTracking('general-functions','GetCount',$e->getMessage());
		}
}// End Function

function GetPhotoWithPath($search_key)//Calculate total interest to be paid for a particular pserson
{
	global $dbh;
 	try
	{
			$sql = "SELECT PhotoPath FROM pdn_share WHERE IsDeleted = 0 AND EQNO = ".$search_key;
		//	print $sql;
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();
			return $result["PhotoPath"];
			
	}
	catch (PDOException $e)
	{
		 //ErrorTracking('share-functions','FindLastEQNO',$e->getMessage());
	}
}
function GetSignatureWithPath($search_key)//Calculate total interest to be paid for a particular pserson
{
	global $dbh;
 	try
	{
			$sql = "SELECT Signature FROM pdn_share WHERE IsDeleted = 0 AND EQNO = ".$search_key;
			$query_sql = $dbh->query($sql);
			$query_sql->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_sql->fetch();
			return $result["Signature"];
			
	}
	catch (PDOException $e)
	{
		 //ErrorTracking('share-functions','GetSignatureWithPath',$e->getMessage());
	}
}

function FindUserNameFromUserId($userid)
{
	global $dbh;
	try
	{
		$sql = "SELECT  Name
                FROM user 
                WHERE UserId = ".$userid;
		//print $sql;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		return $result["Name"];
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','FindUserNameFromUserId',$e->getMessage());
	}
}
function GetCountForDashboards($table,$field,$condition)
{
	global $dbh;
	try
	{
		$sql= "SELECT ".$field." FROM ".$table." WHERE ".$condition."";
		//print $sql.'<br>';
		$query_result = $dbh->query($sql);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result = $query_result->fetch();
		return $result["TOT"];
	}
	catch(PDOException $e)
	{
     ErrorTracking('general-functions','FindUserNameFromUserId',$e->getMessage());
	}
}
function convert_number_to_words($number) 
{
  $no = floor($number);
        $decimal = round($number - $no, 2) * 100;
        $decimal_part = $decimal;
        $hundred = null;
        $hundreds = null;
        $digits_length = strlen($no);
        $decimal_length = strlen($decimal);
        $i = 0;
        $str = array();
        $str2 = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }

        $d = 0;
        while( $d < $decimal_length ) {
            $divider = ($d == 2) ? 10 : 100;
            $decimal_number = floor($decimal % $divider);
            $decimal = floor($decimal / $divider);
            $d += $divider == 10 ? 1 : 2;
            if ($decimal_number) {
                $plurals = (($counter = count($str2)) && $decimal_number > 9) ? 's' : null;
                $hundreds = ($counter == 1 && $str2[0]) ? ' and ' : null;
                @$str2 [] = ($decimal_number < 21) ? $words[$decimal_number].' '. $digits[$decimal_number]. $plural.' '.$hundred:$words[floor($decimal_number / 10) * 10].' '.$words[$decimal_number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str2[] = null;
        }
        
        $Rupees = implode('', array_reverse($str));
        $paise = implode('', array_reverse($str2));
        $paise = ($decimal_part > 0) ? $paise . ' Paise' : '';
        return ($Rupees ? $Rupees : '') . $paise;
}

function ErrorTracking($pagename,$functionname,$message)
{
 global $dbh;
 	try
	{
	$date = date('Y-m-d');
	$stmt = "INSERT INTO error_tracking(PageName, FunctionName, Message,Date) VALUES (:pagename, :functionname, :message,:date)";
	$query = $dbh->prepare($stmt);
	$success = $query->execute(array(':pagename'=> $pagename,':functionname'=> $functionname,':message'=> $message,':date'=>$date));
	}
	catch (PDOException $e)
	{
		ErrorTracking('global-functions','ErrorTracking',$e->getMessage());
	}
}
 
?>