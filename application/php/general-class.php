<?php
//include_once ("../settings/config.php");
class PDNDataInsertUpdateDelete
{
	private $field_list_arr = array();
	private $field_value_list_arr = array();
	private $field_data_type_list_arr = array();
	
	/*public function __constructor($arrField=array(),$arrData=array(),$arrDataType=array()){
     $this->field_list_arr = $arrField;
	 $this->field_value_list_arr = $arrData;
	 $this->field_data_type_list_arr = $arrDataType;
  	}*/
	function PDNDataUpdate($field_list_arr,$field_value_list_arr,$field_data_type_list_arr)
	{
		global $dbh;
		try{
			//print_r($field_value_list_arr);
			$no_of_fields_in_array = sizeof($field_list_arr);
				// Query Generation
			if($no_of_fields_in_array > 2)
			{
				$sql_field_list = '';
				$redirect_page_link = $field_list_arr[0]; // Return Page Link
				$table_name = $field_list_arr[1]; // Return Table Name
				$primary_key_field = $field_list_arr[2]; // Return Primary Key
				$primary_key_value = $field_value_list_arr[0]; // Return Primary Key Value
				$size_of_field_value_list_arr = sizeof($field_value_list_arr);
				$sql = "UPDATE ".$table_name." SET ";
				for($iFields = 1;$iFields<$size_of_field_value_list_arr;$iFields++)
				{
					$arr_field_starting_value = $iFields+2;
					$sql .= $field_list_arr[$arr_field_starting_value]."=";
					if($field_data_type_list_arr[$iFields] == 1)
						$sql .= $field_value_list_arr[$iFields].",";
					elseif($field_data_type_list_arr[$iFields] == 2)
						$sql .= "'".$field_value_list_arr[$iFields]."',";
					elseif($field_data_type_list_arr[$iFields] == 3)
						$sql .= "'".checkIsEmpty($field_value_list_arr[$iFields])."',";
				}
				$sql_field_list = substr($sql,0,strlen($sql)-1);
				$sql_field_list = $sql_field_list." WHERE ".$primary_key_field."=".$primary_key_value."";
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  				$dbh->beginTransaction();
				if(!$dbh->exec($sql_field_list)){
					$dbh->rollback();
					// other clean-up goes here
					return 0;
				}
				/*print $sql_field_list;
				exit;*/
				$dbh->commit();
				return 1;
				//return array($table_lists;
			}
		}
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function PDNDataUpdateWithDateTimeFormat($field_list_arr,$field_value_list_arr,$field_data_type_list_arr)
	{
		global $dbh;
		try{
			//print_r($field_value_list_arr);
			$no_of_fields_in_array = sizeof($field_list_arr);
				// Query Generation
			if($no_of_fields_in_array > 2)
			{
				$sql_field_list = '';
				$redirect_page_link = $field_list_arr[0]; // Return Page Link
				$table_name = $field_list_arr[1]; // Return Table Name
				$primary_key_field = $field_list_arr[2]; // Return Primary Key
				$primary_key_value = $field_value_list_arr[0]; // Return Primary Key Value
				$size_of_field_value_list_arr = sizeof($field_value_list_arr);
				$sql = "UPDATE ".$table_name." SET ";
				for($iFields = 1;$iFields<$size_of_field_value_list_arr;$iFields++)
				{
					$arr_field_starting_value = $iFields+2;
					$sql .= $field_list_arr[$arr_field_starting_value]."=";
					if($field_data_type_list_arr[$iFields] == 1)
						$sql .= $field_value_list_arr[$iFields].",";
					else if($field_data_type_list_arr[$iFields] == 2)
						$sql .= "'".$field_value_list_arr[$iFields]."',";
					else if($field_data_type_list_arr[$iFields] == 3)
						$sql .= "'".$field_value_list_arr[$iFields]."',";
				}
				$sql_field_list = substr($sql,0,strlen($sql)-1);
				$sql_field_list = $sql_field_list." WHERE ".$primary_key_field."=".$primary_key_value."";
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  				$dbh->beginTransaction();
				if(!$dbh->exec($sql_field_list)){
					$dbh->rollback();
					// other clean-up goes here
					return 0;
				}
				/*print $sql_field_list;
				exit;*/
				$dbh->commit();
				return 1;
				//return array($table_lists;
			}
		}
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	// INSERT TABLE DATA
	// Field List Array Values Order ==>>>> Table Name, Field List (Except Key Field)
	// Field List Values Array Order ==>>>> Key Field Values, Field List
	// Field Data Type List 1=> Number   2=> Character  3 => Date 
	function PDNDataInsert($field_list_arr,$field_value_list_arr,$field_data_type_list_arr)
	{
		global $dbh;
		$no_of_fields_in_array = sizeof($field_list_arr);
		
		// Query Generation
		try
		{
			if($no_of_fields_in_array > 2)
			{
				$sql_field_list = '';
				$redirect_page_link = $field_list_arr[0]; // Return Page Link
				$table_name = $field_list_arr[0]; // Return Table Name
				$size_of_field_value_list_arr = sizeof($field_value_list_arr);
				$size_of_field_list_arr = sizeof($field_list_arr);
				$sql = "INSERT INTO ".$table_name." (";
				for($iFields = 1;$iFields<$size_of_field_list_arr;$iFields++)
				{
					$sql .=  $field_list_arr[$iFields].",";
				}
				$sql = substr($sql,0,strlen($sql)-1);
				$sql .= ") VALUES ("; 
				for($iFields = 0;$iFields<$size_of_field_value_list_arr;$iFields++)
				{
					if($field_data_type_list_arr[$iFields] == 1)
						$sql .= $field_value_list_arr[$iFields].",";
					elseif($field_data_type_list_arr[$iFields] == 2)
						$sql .= "'".$field_value_list_arr[$iFields]."',";
					elseif($field_data_type_list_arr[$iFields] == 3)
						$sql .= "'".checkIsEmpty($field_value_list_arr[$iFields])."',";
				}
				$sql_field_list = substr($sql,0,strlen($sql)-1);
				$sql_field_list .= ")";
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbh->beginTransaction();
				if(!$dbh->exec($sql_field_list)){
					$dbh->rollback();
					// other clean-up goes here
					return 0;
				}
				
				$current_primary_key = $dbh->lastInsertId(); // Savings Deposit table primary key. This is used in Transaction tables 
				$dbh->commit();
				return $current_primary_key;
			}
		}
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	function PDNDataInsertWithDateTimeFormat($field_list_arr,$field_value_list_arr,$field_data_type_list_arr)
	{
		global $dbh;
		$no_of_fields_in_array = sizeof($field_list_arr);
		
		// Query Generation
		try
		{
			if($no_of_fields_in_array > 2)
			{
				$sql_field_list = '';
				$redirect_page_link = $field_list_arr[0]; // Return Page Link
				$table_name = $field_list_arr[0]; // Return Table Name
				$size_of_field_value_list_arr = sizeof($field_value_list_arr);
				$size_of_field_list_arr = sizeof($field_list_arr);
				$sql = "INSERT INTO ".$table_name." (";
				for($iFields = 1;$iFields<$size_of_field_list_arr;$iFields++)
				{
					$sql .=  $field_list_arr[$iFields].",";
				}
				$sql = substr($sql,0,strlen($sql)-1);
				$sql .= ") VALUES ("; 
				for($iFields = 0;$iFields<$size_of_field_value_list_arr;$iFields++)
				{
					if($field_data_type_list_arr[$iFields] == 1)
						$sql .= $field_value_list_arr[$iFields].",";
					else if($field_data_type_list_arr[$iFields] == 2)
						$sql .= "'".$field_value_list_arr[$iFields]."',";
					else if($field_data_type_list_arr[$iFields] == 3)
						$sql .= "'".$field_value_list_arr[$iFields]."',";
				}
				$sql_field_list = substr($sql,0,strlen($sql)-1);
				$sql_field_list .= ")";
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbh->beginTransaction();
				if(!$dbh->exec($sql_field_list)){
					$dbh->rollback();
					// other clean-up goes here
					return 0;
				}
				
				$current_primary_key = $dbh->lastInsertId(); // Savings Deposit table primary key. This is used in Transaction tables 
				$dbh->commit();
				return $current_primary_key;
			}
		}
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	// Field Data Type List 1=> Number   2=> Character  3 => Date 
	function PDNDeleteData($keyfield,$table_name,$key_field_name)
	{
		global $dbh;
		// Query Generation
		try
		{
				$sql = "UPDATE ".$table_name." SET IsDeleted = 1 WHERE ".$key_field_name." = ".$keyfield;
			//	print 'ddddd'.$sql;
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbh->beginTransaction();
				if(!$dbh->exec($sql)){
					$dbh->rollback();
					// other clean-up goes here
					return 0;
				}
				$dbh->commit();
				return 1;
		}
		catch (PDOException $e)
		{
			 // ErrorTracking('global-functions','checkIsEmpty',$e->getMessage());
			 $dbh->rollBack();
			 echo "Failed: " . $e->getMessage();
		}
	}// End Function
	//code added by jino
// SELECT TABLE DATA
// All data are filtered
// First Field must be Table name
// Second field must be Primary Key Name
// Third field must be Primary Key Value
// After that display remaining fields
function GetDataBaseTableFieldValues($array_field_list)
{
	global $dbh;
	$no_of_fields_in_array = sizeof($array_field_list);
	// Query Generation
	if($no_of_fields_in_array > 2)
	{
		$table_name = $array_field_list[0];
		$primary_key_field = $array_field_list[1];
		$sql = "SELECT ";
		$iFlag = 1;
		$array_field_list_incremented_variable = 0;
		foreach ($array_field_list as $value) 
		{
			if($iFlag == 1)
			{
				$table_name = $value;
			}
			elseif($iFlag == 2)
			{
				$key_field_name = $value;
			}
			elseif($iFlag == 3)
			{
				$key_field_value = $value;
			}
			else
			{
				$array_field_list[$array_field_list_incremented_variable] = $value;
				$array_field_list_incremented_variable++;
			}
			if($iFlag > 3)
			{
				$sql = $sql.$value.",";
			}
			$iFlag++;
		}
		$sql_field_list = substr($sql,0,strlen($sql)-1);
		$sql_query = $sql_field_list." FROM ".$table_name. " WHERE ".$key_field_name." = ".$key_field_value." AND IsDeleted = 0";
		//print $sql_query;
		// Generated Query to Assign Values in the table
		$query_result = $dbh->query($sql_query);
		$query_result->setFetchMode(PDO::FETCH_ASSOC);
		$result_table_list = $query_result->fetch();
		return($result_table_list);
		//return array($table_lists;
	}
 } // End Function
} // End Class


