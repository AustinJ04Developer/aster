<?php
 	ob_start();
	session_start();
 	include_once("../settings/config.php");
	include_once("global-functions.php");
	include_once("global-settings.php");
	global $dbh;
	try{
		if(isset($_POST['login']))//User Login
		{
		   $result = VerifyUser($_POST['username'],$_POST['password'],$_POST['branch']); 
		}
		if(isset($_POST['submit']))//Insert New User
		{
		   $password = md5($_POST['password']);
		   $date = checkIsEmpty($_POST['current-date']);
		   $result = InsertUser($date,$_POST['name'], $_POST['username'], $password, $_POST['role'], $_POST['staffcodenumber'], $_POST['designation'], $_POST['emailid'], $_POST['mobilenumber'], $_POST['phonenumber'], $_POST['branch']); 
		}
		/*if(isset($_POST['editprofile']))//Edit Profile
		{
			$date = checkIsEmpty($_POST['current-date']);
		   $password = md5($_POST['password']);
		   $result = UpdateUser($date,$_POST['name'],$_POST['staffcodenumber'], $_POST['designation'],$_POST['emailid'], $_POST['mobilenumber'], $_POST['phonenumber'],$_POST['userid']); 
		}*/
		if(isset($_POST['forgetpassword']))//Forget Password
		{
		   $emailid = trim($_POST['emailid']);
		   $result = UpdatePassword($emailid); 
		}
		if(isset($_POST['confirmpassword']))//Confirm Password
		{
		   $newpassword = trim($_POST['newpassword']);
		   $userid = $_POST['userid'];
		   $result = UpdateNewPassword($newpassword,$userid); 
		}
		if(isset($_POST['denoyprofile']))//Deny User Approval
		{
		   $uid = $_POST['userid'];
		   $success = DenyUser($uid);
		   header('Location: ../views/user-approval-list.php');
		}
		if(isset($_POST['approvedprofile']))//Approved User Approval
		{
		   $userid = $_POST['userid'];
		   $roleid = $_POST['role'];
		   ApprovedUser($userid,$roleid);
		}
		if(isset($_POST['loginconfirm']))//Confirm User
		{
		   
		   $password = $_POST['password'];
		   $result = ConfirmedPassword($password,$_SESSION['UserId']);
		}
		if(isset($_POST['blockuser']))//block user BY RB on 23/2/16
		{
		    $userid = $_POST['userid'];
			BlockUser($userid);
		}
		if(isset($_POST['canceluser']))//cancel block user BY RB on 23/2/16
		{
		   header('Location: ../views/user-list.php');
		}
		if(isset($_POST['activeuser']))//block user BY RB on 23/2/16
		{
		    $userid = $_POST['userid'];
			ActiveUser($userid);
		}
		if(isset($_POST['userprivilages']))//block user BY RB on 23/2/16
		{
		    $userid = $_POST['userid'];
			$roleid = $_POST['role'];
			$success = UpdateUserPrivilages($userid,$roleid);	
				 header('Location: ../views/user-privilages.php?r='.$roleid);			
		}
		if(isset($_POST['promoteuser']))
		{
		    $userid = $_POST['userid'];
			$roleid = $_POST['role'];
			$success = UpdateUserPromotedRole($userid,$roleid);	
				 header('Location: ../views/user-list.php');			
		}
/*		if(isset($_POST['submit']))
		{
			$password = md5(trim($_POST['password']));print 'gggggg';
			$stmt = $dbh->prepare('CALL insertuser(:Date, :Name, :UserName, :Password, :RoleId, :StaffCodeNumber, :DesignationId, :DivisionId, :EntityId, :EmailId,   
			:MobileNumber, :PhoneNumber)');
			$stmt->bindParam(':Date',$_POST['current-date'],PDO::PARAM_STR);
			$stmt->bindParam(':Name',$_POST['name'],PDO::PARAM_STR);
			$stmt->bindParam(':UserName',$_POST['username'],PDO::PARAM_STR);
			$stmt->bindParam(':Password',$password,PDO::PARAM_STR);
			$stmt->bindParam(':RoleId',$_POST['role'],PDO::PARAM_INT);
			$stmt->bindParam(':StaffCodeNumber',$_POST['staffcodenumber'],PDO::PARAM_STR);
			$stmt->bindParam(':DesignationId',$_POST['designation'],PDO::PARAM_INT);
			$stmt->bindParam(':DivisionId',$_POST['division'],PDO::PARAM_INT);
			$stmt->bindParam(':EntityId',$_POST['entity'],PDO::PARAM_INT);
			$stmt->bindParam(':EmailId',$_POST['emailid'],PDO::PARAM_STR);
			$stmt->bindParam(':MobileNumber',$_POST['mobilenumber'],PDO::PARAM_STR);
			$stmt->bindParam(':PhoneNumber',$_POST['phonenumber'],PDO::PARAM_STR);
			if($stmt->execute()) { 
			   header('Location: ../views/index.php?s=1');
			} else {
			   header('Location: ../views/index.php?s=0');
			}
		}
*/	}
	catch (PDOException $e)
		{
			ErrorTracking('user-process','submit',$e->getMessage());
		}
	function InsertUser($date,$name, $username, $password, $role, $staffcodenumber, $designation, $emailid, $mobile, $phone, $branch)
	{
	   
/*print	 $date.'-'.$name.'-'.$username.'-'.$password.'-'.$role.'-'.$staffcodenumber.'-'.$designation.'-'.$emailid.'-'.$mobile.'-'.$phone.'-'.$branch;
exit;*/


	   global $dbh;
	   try
		{
			if(isset($_FILES['signature'])){
			$file_name = $_FILES['signature']['name'];
			$file_size =$_FILES['signature']['size'];
			$file_tmp =$_FILES['signature']['tmp_name'];
			$file_type=$_FILES['signature']['type'];
			$signature_name = 	$username.'-'.$file_name;
			}
			$stmt = "INSERT INTO user (Date, 
			Name,
			UserName,
			Password,
			RoleId,
			StaffCodeNumber,
			DesignationId,
			EmailId,
			MobileNumber,
			PhoneNumber,
			Signature
			) VALUES (:Date, 
			:Name,
			:UserName,
			:Password,
			:RoleId,
			:StaffCodeNumber,
			:DesignationId,
			:EmailId,
			:MobileNumber,
			:PhoneNumber,
			:Signature
			)";
			/*$sql = "INSERT INTO user (Date, 
			Name,
			UserName,
			Password,
			RoleId,
			StaffCodeNumber,
			DesignationId,
			EmailId,
			MobileNumber,
			PhoneNumber,
			Signature 
			) VALUES ('".$date."', 
			'".$name."',
			'".$username."',
			'".$password."',
			".$role.",
			'".$staffcodenumber."',
			".$designation.",
			'".$emailid."',
			'".$mobile."',
			'".$phone."',
			'".$signature_name."')";*/
			//exit;
			/* Rename the Signature file and stored in Seperate Folder */
			$desired_dir=STAFFPHOTO;
            if(empty($errors)==true){
				if(is_dir($desired_dir)==false){
					mkdir("$desired_dir", 0700);		// Create directory if it does not exist
				}
			$temp = explode(".", $signature_name);
			$newfilename = $signature_name;
            if(is_dir("$desired_dir/".$newfilename)==false){
                move_uploaded_file($file_tmp,"$desired_dir/".$newfilename);
            }else{									// rename the file if another one exist
                $new_dir="$desired_dir/".$file_name.time();
                 rename($file_tmp,$new_dir) ;				
            }
			}else{
				 //   print_r($errors);
			}
			/**/
			//$query = $dbh->exec($sql);
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':Date'=> $date,':Name'=> $name,':UserName'=> $username,':Password'=> $password,':RoleId'=> $role,':StaffCodeNumber'=> $staffcodenumber,':DesignationId'=> $designation,':EmailId'=> $emailid,':MobileNumber'=> $mobile,':PhoneNumber'=> $phone,':Signature'=> $signature_name));
			/*print $success;
			exit;*/
			if($success == 1)
			{
				SendRegistrationEmail($emailid,$name);
				header('Location: ../views/index.php?s=1');
			}
			else
			{
				header('Location: ../views/index.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','InsertUser',$e->getMessage());
		}
		//$dbh = null;
	}
	
	//Data Update in User
if(isset($_POST['editprofile']))
{
	$date = checkIsEmpty($_POST['current-date']);
	$password = md5($_POST['password']);
	$name = $_POST['name'];
	$staff_code = $_POST['staffcodenumber'];
	$designation = $_POST['designation'];
	$emailid = $_POST['emailid'];
	$mobile_num = $_POST['mobilenumber'];
	$phone_num = $_POST['phonenumber'];
	$uid = $_POST['userid'];
	
	$success = UpdateUser($date,$name,$staff_code,$designation,$emailid,$mobile_num,$phone_num,$uid);
	if($success)
	header('Location: ../views/edit-profile.php?s=1');
  else
	header('Location: ../views/edit-profile.php?s=0'); 
}
	function  UpdateUser($date,$name,$staff_code,$designation,$emailid,$mobile_num,$phone_num,$uid) 
	{
	   global $dbh;
	   try
		{
			if(isset($_FILES['signature'])){
			$file_name = $_FILES['signature']['name'];
			$file_size =$_FILES['signature']['size'];
			$file_tmp =$_FILES['signature']['tmp_name'];
			$file_type=$_FILES['signature']['type'];
			$signature_name = 	$username.'-'.$file_name;
			}
			/* Rename the Signature file and stored in Seperate Folder */
			$desired_dir=USERPHOTO;
            if(empty($errors)==true){
				if(is_dir($desired_dir)==false){
					mkdir("$desired_dir", 0700);		// Create directory if it does not exist
				}
			$temp = explode(".", $signature_name);
			$newfilename = $signature_name;
            if(is_dir("$desired_dir/".$newfilename)==false){
                move_uploaded_file($file_tmp,"$desired_dir/".$newfilename);
            }else{									// rename the file if another one exist
                $new_dir="$desired_dir/".$file_name.time();
                 rename($file_tmp,$new_dir);				
            }
			}else{
				    //print_r($errors);
			}
			// Insert data into the table
		     $sql = "UPDATE user SET 
			   	    Date = '".$date."', 
					Name = '".$name."',
					StaffCodeNumber = '".$staff_code."',
					DesignationId = ".$designation.",
					EmailId = '".$emailid."',
					MobileNumber = '".$mobile_num."',
					PhoneNumber = '".$phone_num."',
					Signature = '".$signature_name."'
				    WHERE UserId = ".$uid;
				/*print  $sql;
				exit;*/	
		$success = $dbh->exec($sql);
		
	}
	catch (PDOException $e)
	{
		 ErrorTracking('user-process','DataUpdate',$e->getMessage());
	}
	//$dbh = null;
}


	//Send First Mail to User
	function SendRegistrationEmail($mail,$name)
	{
		$to = $mail;
		$subject = "Account details for ".$name." at MPDN (pending admin approval)";
		$message = "
		<html>
		<head>
		<title>Account Details</title>
		</head>
		<body>
		<p>Dear <b>".$name."</b>,</p>
		<p>Thank you for registering at MPDN. Your application for an account is currently pending approval. Once it has been approved, you will receive 
		another e-mail containing information about how to log in.
		</p>
		<p>Regards</p>
		<p><b>MPDN Team</b></p>
		</body>
		</html>
		";
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// More headers
		$headers .= 'From: <support@mpdn.com>' . "\r\n";
		mail($to,$subject,$message,$headers);
	}
	
	//Forget Password
	function UpdatePassword($emailid)
	{
	 global $dbh; 
	 try
	 {
	    $sql = "SELECT Name, UserName, Password, EmailId, UserId, IsApproved, IsBlocked
				FROM user
				WHERE EmailId = '".$emailid."'";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result['IsApproved'] == 0)
		{
			header('Location: ../views/forget-password.php?s=2');
		}
		elseif($result['IsBlocked'] == 1)
		{
			header('Location: ../views/forget-password.php?s=3');
		}
		else
		{
		$min = 333;
		$newpassword = $result['UserName'].rand($min, $min * 255);
		$newpassword1 = md5($newpassword);
		$userid = $result['UserId'];
	  
		     $is_first_field = 0;
			 $stmt = "UPDATE user SET 
					Password = :Password, IsFirstLogin = :IsFirstLogin 
				    WHERE UserId = :UserId";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':Password'=> $newpassword1,':IsFirstLogin'=> $is_first_field,':UserId'=> $userid));
			UpdateDailyPassword($userid,$newpassword1);
			SendForgetPasswordMail($emailid, $newpassword, $result['Name'], $result['UserName']);
			if($success == 1)
			{
				header('Location: ../views/forget-password.php?s=1');
			}
			else
			{
				header('Location: ../views/forget-password.php?s=0');
			}
		}
		
	}
	catch (PDOException $e)
	{
		 ErrorTracking('user-process','UpdatePassword',$e->getMessage());
	}
	//$dbh = null;
}
	//Send Forget Password Mail
	//Send Forget Password  Mail to User
	function SendForgetPasswordMail($mail,$newpassword, $name, $username)
	{
		$to = $mail;
		$subject = "Replacement login information for  ".$name." at MPDN";
		$message = "
		<html>
		<head>
		<title>Reset Password</title>
		</head>
		<body>
		<p>Dear <b>".$name."</b>,</p>
		<p>A request to reset the password for your account has been made at MPDN.
		</p>
		<p>
		<b>New Password :  ".$newpassword."</b>
		</p>
		<p>Regards</p>
		<p><b>MPDN Team</b></p>
		</body>
		</html>
		";
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// More headers
		$headers .= 'From: <support@cussoft.com>' . "\r\n";
		mail($to,$subject,$message,$headers);
	}
	//Change Password
	//Change New Password
	function UpdateNewPassword($password,$userid)
	{
	 global $dbh;
	   try
		{
		     $password = md5($password);
			 $is_first_login = 1;
			 $stmt = "UPDATE user SET Password = :Password, IsFirstLogin = :IsFirstLogin 
				      WHERE UserId = :UserId";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':Password'=> $password,':IsFirstLogin'=> $is_first_login,':UserId'=> $userid));
			if($success == 1)
			{
				if(trim(strtoupper($_SESSION['dashboard'])) == 'ADMIN')
				{
				header('Location: ../views/admin-dashboard.php?s=1');
				}
				elseif (trim(strtoupper($_SESSION['dashboard'])) == 'STOREMANAGER')
				{
				header('Location: ../views/store-manager-dashboard.php?s=1');
				}
				elseif (trim(strtoupper($_SESSION['dashboard'])) == 'MECHANICALENGINEER')
				{
				header('Location: ../views/mechanical-engineer-dashboard.php?s=1');
				}
				
			}
			else
			{
				header('Location: ../views/change-password.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','UpdateNewPassword',$e->getMessage());
		}
		//$dbh = null;
	}
    // Approved User
	function ApprovedUser($userid,$roleid)	
	{
	 global $dbh;
	   try
		{
		     $approved = 1; 
			 $stmt = "UPDATE user SET 
					  IsApproved = :IsApproved,RoleId = :RoleId, ApprovedBy = :ApprovedBy 
				      WHERE UserId = :UserId";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':IsApproved'=> $approved,':RoleId'=> $roleid,':ApprovedBy'=> $_SESSION['UserId'],':UserId'=> $userid));
			SendUserApprovalMail($userid);
			if($success == 1)
			{
				header('Location: ../views/user-approval-list.php?s=1');
			}
			else
			{
				header('Location: ../views//user-approval-list.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','ApprovedUser',$e->getMessage());
		}
		//$dbh = null;
	}
	//Send User Approval Mail
	function SendUserApprovalMail($userid)
	{
		global $dbh;
	    $sql = "SELECT Name, UserName, Password, EmailId 
				FROM user
				WHERE UserId = ".$userid;
		
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		
		$name = $result['UserName'];
		$to = $result['EmailId'];
		$subject = "Account details for   ".$name." at MPDN (Approved)";
		$min = 255;
		$security_password = $result['UserName'].rand($min, $min * 255);
		$date = date("Y/m/d");
		$is_set_password = 1;
		InsertSecurityPassword($userid,$security_password,$is_set_password,$date);
		$message = "
		<html>
		<head>
		<title>User Approval</title>
		</head>
		<body>
		<p>Dear <b>".$name."</b>,</p>
		<p>Your account at MPDN has been activated.
		</p>
		<p>Please find the Daily Password below</p>
		<p>
		<b>Security Password :  ".$security_password."</b>
		</p>
		<p>Regards</p>
		<p><b>MPDN Team</b></p>
		</body>
		</html>
		";
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// More headers
		$headers .= 'From: <support@cussoft.com>' . "\r\n";
		mail($to,$subject,$message,$headers);
	}
	function  InsertSecurityPassword($userid,$password,$issetpassword ,$date)//Data Insert in Daily Password
	{
	   global $dbh;
	   try
		{
			$password = md5($password);
			$stmt = "INSERT INTO daily_password(Date, UserId, Password,IsSetPassword) VALUES (:Date, :UserId, :Password, :IsSetPassword)";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':Date'=> $date, ':UserId'=> $userid,':Password'=> $password, ':IsSetPassword'=> $issetpassword));
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','InsertSecurityPassword',$e->getMessage());
		}
		//$dbh = null;
	}
	//Verify User
	function VerifyUser($username,$password,$branch)
	{
	   global $dbh;
	   try
		{
			$password = md5($password);
		    $sql = "SELECT UserId, Name, UserName, Password, EmailId, IsApproved, IsBlocked, IsFirstLogin, Branch 
				    FROM user
				    WHERE UserName = '".$username."' AND Password = '".$password."'";
			
			$queryrersult = $dbh->query($sql);
			$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
			$result = $queryrersult->fetch();
			if($result['UserName'] != '')
			{
				if($result['IsApproved'] == 1)
				{
					if($result['IsBlocked'] == 0)
					{
							$_SESSION['UserId'] = $result['UserId'];
							$_SESSION['uid'] = $_SESSION['UserId'];
							$_SESSION['TimeIdleMax'] = intval(825);
							
							$uid=$result['UserId'];
							//BY RB on 30/6/16
							if(isset($_SESSION['LoginMultipleClick']))
							{
								//header('Location: ../views/admin-dashboard.php?c=1');
								$result = ConfirmedPassword($password,$_SESSION['UserId']);
							}
							else
							{
							$sql="select loginid from logged where loginid=".$uid."";
							$queryrersult = $dbh->query($sql);
							$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
							$result = $queryrersult->fetch();
							/*print 'aaaa'.$result['loginid'].$sql;
							exit;*/
							if(empty($result['loginid']))
							{
								if($_SESSION['UserId'] != 1)
								{
								$sql_login="INSERT INTO logged(loginid) VALUES (".$uid.")";
								//$stmt = "INSERT INTO logged(loginid) VALUES (:logid)";
								$success = $dbh->exec($sql_login);
								}
								//$password_avaialable = DailyPasswordSetup($uid);
								if($password_avaialable <= 0 or $password_avaialable = '')
								{
									//SendConfirmPassword($result['UserId'],$result['UserName'],$result['EmailId'],$result['Name']);
								}
								$result = ConfirmedPassword($password,$_SESSION['UserId']);
								//header('Location: ../views/admin-dashboard.php?c=1');
					
							}
							else
							{
							  if($uid == 1)
							  {
							  	//header('Location: ../views/admin-dashboard.php?c=1');
								$result = ConfirmedPassword($password,$_SESSION['UserId']);
							  }
							  else
							  {
							  	header('Location: ../views/index.php?r=5');
							  }
							}
							$_SESSION['LoginMultipleClick'] = 1;
							}
					}
					else
					{
						header('Location: ../views/index.php?r=3');
					}
				}
				else
				{
					header('Location: ../views/index.php?r=2');				
				}
			}
			else
			{
				 $sql = "SELECT UserId, Name, UserName, Password, EmailId, IsApproved, IsBlocked, IsFirstLogin  
							    FROM user
							    WHERE UserName = '".$username."' OR Password = '".$password."'";
								$queryrersult = $dbh->query($sql);
								$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
								$result = $queryrersult->fetch();
								if($result['UserName'] == '')
								{
									header('Location: ../views/index.php?r=1');
								}
								else
								{
									header('Location: ../views/index.php?r=4');
								}
			}
		}
		catch (PDOException $e)
		{
		//	 ErrorTracking('user-process','VerifyUser',$e->getMessage());
		}
		//$dbh = null;
	}
	//Verify User
	/*function VerifyUser($username,$password)
	{
	   global $dbh;
	   try
		{
			$password = md5($password);
		    $sql = "SELECT UserId, Name, UserName, Password, EmailId, IsApproved, IsBlocked, IsFirstLogin  
				    FROM user
				    WHERE UserName = '".$username."' AND Password = '".$password."'";
			$queryrersult = $dbh->query($sql);
			$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
			$result = $queryrersult->fetch();
			if($result['UserName'] != '')
			{
				if($result['IsApproved'] == 1)
				{
					if($result['IsBlocked'] == 0)
					{
						    $_SESSION['UserId'] = $result['UserId'];
							$password_avaialable = DailyPasswordSetup($result['UserId']);
							if($password_avaialable <= 0 or $password_avaialable = '')
							{
								SendConfirmPassword($result['UserId'],$result['UserName'],$result['EmailId'],$result['Name']);
							}
							
							header('Location: ../views/login-confirm.php?c=1');
					}
					else
					{
						header('Location: ../views/index.php?r=3');
					}
				}
				else
				{
					header('Location: ../views/index.php?r=2');				
				}
			}
			else
			{
				 $sql = "SELECT UserId, Name, UserName, Password, EmailId, IsApproved, IsBlocked, IsFirstLogin  
							    FROM user
							    WHERE UserName = '".$username."' OR Password = '".$password."'";
								$queryrersult = $dbh->query($sql);
								$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
								$result = $queryrersult->fetch();
								if($result['UserName'] == '')
								{
									header('Location: ../views/index.php?r=1');
								}
								else
								{
									header('Location: ../views/index.php?r=4');
								}
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','VerifyUser',$e->getMessage());
		}
		//$dbh = null;
	}*/
	function DailyPasswordSetup($userid)
	{
		global $dbh;
		try
		{
			 $sql = "SELECT count(*) AS UserAvailable 
						FROM daily_password
						WHERE Date = '".date("Y/m/d")."' AND UserId = ".$userid;
				$queryrersult = $dbh->query($sql);
				$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
				$result = $queryrersult->fetch();
				return $result['UserAvailable'];
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','DailyPasswordSetup',$e->getMessage());
		}
		//$dbh = null;
	}
	function SendConfirmPassword($userid,$username,$emailid,$name)
	{
	    $to = $emailid;
		$subject = "Todays (".date("d/m/Y").") Password for   ".$name." at MPDN ";
		$min = 255;
		$security_password = $username.rand($min, $min * 255);
		$date = date("Y/m/d");
		$is_set_password = 1;
		//InsertSecurityPassword($userid,$security_password,$is_set_password,$date);
		$message = "
		<html>
		<head>
		<title>Todays Password</title>
		</head>
		<body>
		<p>Dear <b>".$name."</b>,</p>
		<p>Please find the Daily Password below</p>
		<p>
		<b>Security Password :  ".$security_password."</b>
		</p>
		<p>Regards</p>
		<p><b>MPDN Team</b></p>
		</body>
		</html>
		";
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// More headers
		$headers .= 'From: <support@cussoft.com>' . "\r\n";
		mail($to,$subject,$message,$headers);
	}
	function ConfirmedPassword($password,$userid)
	{
		global $dbh;
		try
		{
		/* $sql = "SELECT u.IsFirstLogin, d.Password  
		         FROM user u, daily_password d 
				 WHERE d.date = '".date("Y/m/d")."' and d.UserId = ".$userid." And u.UserId = d.UserId";*/
		 $sql = "SELECT u.IsFirstLogin
		         FROM user u
				 WHERE u.UserId = ".$userid;
		//print $sql;		 
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		/*if($result["Password"] == md5(trim($password)))
		{*/
		/*$sql1="SELECT ExpiedDate FROM hos_pharm_medicine_purchase_stock_details WHERE IsClosed = 0 AND IsDeleted = 0";
		$queryrersult = $dbh->query($sql1);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result['ExpiedDate'] = date("Y/m/d"))
		{
		 	$sql2="UPDATE hos_pharm_medicine_purchase_stock_details SET IsExpired = 1 WHERE  ExpiedDate <='".date("Y/m/d")."'";
			$dbh->exec($sql2);
			
			$sql3="UPDATE hos_pharm_medicine_purchase_stock_details SET IsExpired = 0 WHERE  ExpiedDate = '0000-00-00'";
			$dbh->exec($sql3);
		}*/
			
				//Find Role Id
				$sql = "SELECT RoleId  
		        FROM user 
				WHERE UserId = ".$userid."";
				
				$queryresult = $dbh->query($sql);
				$queryresult->setFetchMode(PDO::FETCH_ASSOC);
				$result_role = $queryresult->fetch();
				$roleid = $result_role['RoleId'];
				$_SESSION['RoleId'] = $roleid;
				//Find if the current user is assigne to manager incharge then assign manager role to this user
				/*$is_current_user_manager_incharge = CheckIfCurrentUserManagerIncharge($userid);
				if(!empty($is_current_user_manager_incharge))
				{
					$_SESSION['RoleId'] = $is_current_user_manager_incharge;
					$roleid = $_SESSION['RoleId'];
				}*/
				
				/*print 'aaa'.$_SESSION['RoleId'];
				exit;*/
				/*$sql = "SELECT StageId  
		        FROM invt_stage_1 
				WHERE Status = 0 ORDER BY StageId LIMIT 1";
				$queryresult = $dbh->query($sql);
				$queryresult->setFetchMode(PDO::FETCH_ASSOC);
				$result_activeid = $queryresult->fetch();
				$_SESSION['ActiveStageNo'] = $result_activeid['StageId'];
				$sql = "SELECT IdentificationNumber  
		        FROM invt_stage_1  
				WHERE StageId = ".$_SESSION['ActiveStageNo']."";
				$queryresult = $dbh->query($sql);
				$queryresult->setFetchMode(PDO::FETCH_ASSOC);
				$result_stageid = $queryresult->fetch();
				$_SESSION['ActiveStageId'] = $result_stageid['IdentificationNumber'];*/
				//Find active stage no for getting all table name dynamically
				/*$sql = "SELECT ASID,StageId  
		        FROM invt_current_stage 
				WHERE Status = 'ACTIVE'";
				$queryresult = $dbh->query($sql);
				$queryresult->setFetchMode(PDO::FETCH_ASSOC);
				$result_activeid = $queryresult->fetch();
				if(empty($result_activeid['ASID']))
					$active_id = 1;
				else
					$active_id = $result_activeid['ASID'];
				
				$active_stage_id = $result_activeid['StageId'];
				//$active_id = $result_activeid['StageId'];
				//$active_stage_id = $result_activeid['StageId'];
				//Case 1 redirect to admin dashboard
				//Case 2 redirect to Store Manager dashboard
				$_SESSION['RoleId'] = $roleid;
				$_SESSION['ActiveStageNo'] = $active_id;
				$_SESSION['ActiveStageId'] = $active_stage_id;*/
				//$_SESSION['ActiveStageId'] = $active_stage_id;
				switch ($roleid)
				{
					//Switch to Admin Dashboard
					case 1:
						$_SESSION['dashboard'] = 'Admin';
						header('Location: ../views/admin-dashboard.php');
						break;
					//Switch to MD Dashboard
					case 2:
						$_SESSION['dashboard'] = 'Managing Director';
						header('Location: ../views/managing-director-dashboard.php');
						break;
					//Switch to Mechanical Engineer Dashboard
					case 3:
						$_SESSION['dashboard'] = 'Manager';
						header('Location: ../views/manager-dashboard.php');
						break;
					case 4:
						$_SESSION['dashboard'] = 'Operator';
						header('Location: ../views/data-entry-operator-dashboard.php');
						break;
					case 5:
						$_SESSION['dashboard'] = 'Clerk';
						header('Location: ../views/clerk-dashboard.php');
						break;
					case 6:
						$_SESSION['dashboard'] = 'Office Assistant';
						header('Location: ../views/office-assistant-dashboard.php');
						break;
					case 7:
						$_SESSION['dashboard'] = 'Appraiser';
						header('Location: ../views/appraiser-dashboard.php');
						break;
					case 8:
						$_SESSION['dashboard'] = 'Sr Manager';
						header('Location: ../views/manager-in-charge-dashboard.php');
						break;
				}
				
	  }
	  catch (PDOException $e)
	  {
			 ErrorTracking('user-process','ConfirmedPassword',$e->getMessage());
	  }
	  //$dbh = null;
	  
	}
    function UpdateDailyPassword($userid,$password)
	{
	 global $dbh;
	 try
	 {
	 $sql = "SELECT count(*) AS UserAvailable 
				    FROM daily_password
				    WHERE Date = '".date("Y/m/d")."' AND UserId = ".$userid;
			$queryrersult = $dbh->query($sql);
			$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
			$result = $queryrersult->fetch();
			if($result["UserAvailable"] > 0)
			{
				 $stmt = "UPDATE daily_password SET Password = :Password 
				      WHERE UserId = :UserId";
				$query = $dbh->prepare($stmt);
				$success = $query->execute(array(':Password'=> $password,':UserId'=> $userid));							
			}
			else
			{
				$date = date("Y/m/d");
				$stmt = "INSERT INTO daily_password(Date, UserId, Password,IsSetPassword) VALUES (:Date, :UserId, :Password, :IsSetPassword)";
				$query = $dbh->prepare($stmt);
				$success = $query->execute(array(':Date'=> $date, ':UserId'=> $userid,':Password'=> $password, ':IsSetPassword'=> $issetpassword));			
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','UpdateDailyPassword',$e->getMessage());
		}
		//$dbh = null;
	}
	
	function BlockUser($userid)	//BY RB on 23/2/16
	{
	   global $dbh;
	   try
		{
		     $blocked = 1; 
			 $stmt = "UPDATE user SET 
					  IsBlocked= :IsBlocked
				      WHERE UserId = :UserId";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':IsBlocked'=> $blocked,':UserId'=> $userid));
			
			if($success == 1)
			{
				header('Location: ../views/user-list.php?s=1');
			}
			else
			{
				header('Location: ../views/user-list.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','BlockUser',$e->getMessage());
		}
		//$dbh = null;
	}
	function ActiveUser($userid)	//BY RB on 23/2/16
	{
	 global $dbh;
	   try
		{
		     $blocked = 0; 
			 $stmt = "UPDATE user SET 
					  IsBlocked= :IsBlocked
				      WHERE UserId = :UserId";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':IsBlocked'=> $blocked,':UserId'=> $userid));
			
			if($success == 1)
			{
				header('Location: ../views/user-list.php?s=1');
			}
			else
			{
				header('Location: ../views/user-list.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','ActiveUser',$e->getMessage());
		}
		//$dbh = null;
	}
	//Update User Privilages
	function  UpdateUserPrivilages($userid,$role)//Data Update User Privilages
	{
	   global $dbh;
	   try
		{
			$sql = "SELECT count(*) AS TotalPages
				FROM user_privilages
				WHERE RoleId = ".$role;
				
		    $queryresult = $dbh->query($sql);
		    $queryresult->setFetchMode(PDO::FETCH_ASSOC);
		    $result = $queryresult->fetch();
			$total_pages = $result['TotalPages'];
			for($ipage = 1; $ipage <= $total_pages; $ipage++)
			{
				$checkboxcolumn = 1;
				$pageidname = 'pageid'.$ipage;
				$pageid = $_POST[$pageidname];
				$checkbox = 'checkbox'.$ipage.'-'.$checkboxcolumn;
				/*print 'checkbox'.$checkbox.'pageid'.$pageid;
				print $_POST[$checkbox];
				print 'pageid :'.$_POST[$pageidname];
				print 'pageidname :'.$pageidname;
				exit;*/
				if($_POST[$checkbox] == 'v')
				{
					UpdatePrivilages($pageid,$userid,$role,'v','view');
				}
				elseif($_POST[$checkbox] == 'vn')
				{
					UpdatePrivilages($pageid,$userid,$role,'vn','view');
				}
				$checkboxcolumn++;
				$checkbox = 'checkbox'.$ipage.'-'.$checkboxcolumn;
				if($_POST[$checkbox] == 'i')
				{
					UpdatePrivilages($pageid,$userid,$role,'i','insert');
				}
				elseif($_POST[$checkbox] == 'in')
				{
					UpdatePrivilages($pageid,$userid,$role,'in','insert');
				}
				$checkboxcolumn++;
				$checkbox = 'checkbox'.$ipage.'-'.$checkboxcolumn;
				if($_POST[$checkbox] == 'u')
				{
					UpdatePrivilages($pageid,$userid,$role,'u','update');
				}
				elseif($_POST[$checkbox] == 'un')
				{
					UpdatePrivilages($pageid,$userid,$role,'un','update');
				}
				$checkboxcolumn++;
				$checkbox = 'checkbox'.$ipage.'-'.$checkboxcolumn;
				if($_POST[$checkbox] == 'd')
				{
					UpdatePrivilages($pageid,$userid,$role,'d','delete');
				}
				elseif($_POST[$checkbox] == 'dn')
				{
					UpdatePrivilages($pageid,$userid,$role,'dn','delete');
				}
			}
		}
		catch (PDOException $e)
		{
			print $e->getMessage();
			 ErrorTracking('user-process','UpdateUserPrivilages',$e->getMessage());
		}
		//$dbh = null;
	}
	//Find if role exist or not
	function  FindIfRoleExist($roleid)//Check whether role exist or not
	{
	   global $dbh;
	   try
		{
			$sql = "SELECT count(*) AS Roles
				FROM user_privilages
				WHERE RoleId = ".$roleid;
		    $queryresult = $dbh->query($sql);
		    $queryresult->setFetchMode(PDO::FETCH_ASSOC);
		    $result = $queryresult->fetch();
			$total_roles = $result['Roles'];
			return $total_roles;
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','FindIfRoleExist',$e->getMessage());
		}
		//$dbh = null;
	}
	//Insert Page Details in Privilage for a particular role
	function  InsertPageInPrivilages($roleid)//Check whether role exist or not
	{
	   global $dbh;
	   try
		{
 			$date = date("Y/m/d");
			$userid = $_SESSION['UserId'];
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   			$sql = "INSERT INTO user_privilages(Date,PageId,RoleId,UserId) SELECT '".$date."',PageId,".$roleid.",".$userid." FROM page";
			//$sql = "INSERT INTO user_privilages(Date,PageId,RoleId,UserId) SELECT '".$date."',p.PageId,u.RoleId,p.UserId FROM page p, user_privilages u WHERE u.RoleId = ".$roleid." AND u.PageId != p.PageId";

   			$success = $dbh->exec($sql);
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','InsertPageInPrivilages',$e->getMessage());
		}
		//$dbh = null;
	}
	//Insert Page Details in Privilage for a particular role
	function  InsertNewPageInPrivilages($roleid)//Check whether role exist or not
	{
	   global $dbh;
	   try
		{
 			$date = date("Y/m/d");
			$userid = $_SESSION['UserId'];
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   			//$sql = "INSERT INTO user_privilages(Date,PageId,RoleId,UserId) SELECT '".$date."',PageId,".$roleid.",".$userid." FROM page";
			$sql = "INSERT INTO user_privilages(Date,PageId,RoleId,UserId) 
			SELECT '".$date."',p.PageId,".$roleid.",p.UserId FROM page p 
			WHERE  p.PageId NOT IN (SELECT PageId FROM user_privilages WHERE RoleId = ".$roleid.")";

   			$success = $dbh->exec($sql);
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','InsertNewPageInPrivilages',$e->getMessage());
		}
		//$dbh = null;
	}
	//Update User Id and Role Id for User Promotion
	function UpdateUserPromotedRole($userid,$roleid)
	{
	   global $dbh;
	   try
		{
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "UPDATE user SET RoleId = ".$roleid." WHERE UserId = ".$userid;
   			$success = $dbh->exec($sql);
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','UpdateUserPromotedRole',$e->getMessage());
		}
		//$dbh = null;
	}
  // Deny User
	function DenyUser($userid)
	{
	   global $dbh;
	   try
		{
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "UPDATE user SET IsDeleted = 1,IsApproved = 2 WHERE UserId = ".$userid;
   			$success = $dbh->exec($sql);
		}
		catch (PDOException $e)
		{
			 ErrorTracking('user-process','UpdateUserPromotedRole',$e->getMessage());
		}
		//$dbh = null;
	}
	
	//Page Id, User id, Role id, Value, Field
	function UpdatePrivilages($pageid,$userid,$roleid,$select_value,$field)
	{
		global $dbh;
		try
		{
		if($select_value == 'v')
			$selected_value = 1;
		elseif($select_value == 'i')
			$selected_value = 1;
		elseif($select_value == 'u')
			$selected_value = 1;
		elseif($select_value == 'd')
			$selected_value = 1;
		elseif($select_value == 'vn')
			$selected_value = 0;
		elseif($select_value == 'in')
			$selected_value = 0;
		elseif($select_value == 'un')
			$selected_value = 0;
		elseif($select_value == 'dn')
			$selected_value = 0;
		else
			$selected_value = 0;
		switch ($field)
		{
			case "view":
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);				
	   			$sql = "UPDATE user_privilages SET DView = ".$selected_value." WHERE PageId = ".$pageid." and RoleId = ".$roleid;
				$success = $dbh->exec($sql);
   				break;
			case "insert":
			
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	   			$sql = "UPDATE user_privilages SET DInsert = ".$selected_value." WHERE PageId = ".$pageid." and RoleId = ".$roleid;
				$success = $dbh->exec($sql);
				
				break;
			case "update":
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	   			$sql = "UPDATE user_privilages SET DEdit = ".$selected_value." WHERE PageId = ".$pageid." and RoleId = ".$roleid;
				$success = $dbh->exec($sql);
				break;
			case "delete":
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	   			$sql = "UPDATE user_privilages SET DDelete = ".$selected_value." WHERE PageId = ".$pageid." and RoleId = ".$roleid;
				$success = $dbh->exec($sql);
				break;
		}
	 }
	catch (PDOException $e)
	{
		 ErrorTracking('user-process','UpdatePrivilages',$e->getMessage());
	}
	

	//$dbh = null;
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
