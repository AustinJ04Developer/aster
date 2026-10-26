<?php
include_once("../settings/config.php");
include_once("../php/user-process.php");
global $dbh;
$id=$_REQUEST["id"];
$is_page_exist_in_current_role = 0;

if($id != 0)
{
	$is_page_exist_in_current_role = FindIfRoleExist($id);
	if($is_page_exist_in_current_role <=0)
		InsertPageInPrivilages($id);
	else
		InsertNewPageInPrivilages($id);

$sql = "SELECT u.PageId, u.Date, p.PageName as PageName,u.DInsert, u.DView, u.DEdit, u.DDelete, pm.MainHeading,p.PSMID
                FROM user_privilages u
				LEFT JOIN page p
				ON u.PageId = p.PageId
				LEFT JOIN page_sidemenu_main_heading pm
				ON pm.PSMID = p.PSMID
                WHERE u.RoleId = ".$id." ORDER BY p.PSMID";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
	//	$result = $queryrersult->fetch();
		$row_number = 1;
		$table = '';
		$table .=  '<table id="designation-details" class="table table-striped table-bordered" cellspacing="0"><thead>
            <tr class="header-row">
              <th class="table-header">SlNo</th>
              <th class="table-header">Page</th>
			  <th class="table-header">Catetory</th>
              <th class="table-header">View</th>
              <th class="table-header">Add</th>
              <th class="table-header">Edit</th>
              <th class="table-header">Delete</th>
            </tr>
          </thead>
          <tbody class="table-coloum-style">';
	    while ($result = $queryrersult->fetch())
		{
		    $checkbox_column_no = 1;
			if($result["PSMID"] == 1)
				$table .= '<tr style="background:#dbecfc;">';
			elseif($result["PSMID"] == 2)
				$table .= '<tr style="background:#edeff2;">';
			elseif($result["PSMID"] == 3)
				$table .= '<tr style="background:#f9e4d6;">';
			elseif($result["PSMID"] == 4)
				$table .= '<tr style="background:#d9f1f7;">';
			elseif($result["PSMID"] == 5)
				$table .= '<tr style="background:#fbf2ea;">';
			elseif($result["PSMID"] == 6)
				$table .= '<tr style="background:#e6f2ff;">';
			
			
			elseif($result["PSMID"] == 9)
				$table .= '<tr style="background:#ffb3b3;">';
			elseif($result["PSMID"] == 20)
				$table .= '<tr style="background:#d9ffcc;">';
			elseif($result["PSMID"] == 21)
				$table .= '<tr style="background:#e6f9ff;">';
			elseif($result["PSMID"] == 22)
				$table .= '<tr style="background:#f7e6ff;">';
			elseif($result["PSMID"] == 23)
				$table .= '<tr style="background:#ffffe6;">';
			elseif($result["PSMID"] == 24)
				$table .= '<tr style="background:#ffb3e6;">';
			elseif($result["PSMID"] == 44)
				$table .= '<tr style="background:#ccf2ff;">';
/*			if($row_number % 2 == 0)
				$table .= '<tr style="background:#dbecfc;">';
			else
				$table .= '<tr style="background-color:#fff;">';
*/			$table .= '<td>'.$row_number.'</td>';
			$table .= '<td>'.$result["PageName"].'</td>';
			$table .= '<td>'.$result["MainHeading"].'</td>';
			
			if($result["DView"] == 1)
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="vn" /><input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="v" checked /></td>';
			}
			else
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="vn" /><input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="v" /></td>';
			}
			$checkbox_column_no++;
			if($result["PageId"] == 32 or $result["PageId"] == 33 or $result["PageId"] == 34 or $result["PageId"] == 35  or $result["PageId"] == 36  or $result["PageId"] == 37  or $result["PageId"] == 38  or $result["PageId"] == 39  or $result["PageId"] == 40  or $result["PageId"] == 41  or $result["PageId"] == 42  or $result["PageId"] == 43  or $result["PageId"] == 44  or $result["PageId"] == 45  or $result["PageId"] == 46  or $result["PageId"] == 47  or $result["PageId"] == 48 or $result["PageId"] == 57 or $result["PageId"] == 49 or $result["PageId"] == 54 or $result["PageId"] == 55 or $result["PageId"] == 56)
			{
			$table .= '<td align="center"> - </td>';
			$table .= '<td align="center"> - </td>';
			$table .= '<td align="center"> - </td>';
			}
			else
			{
			if($result["DInsert"] == 1)
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="in" /><input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="i" checked /></td>';
			}
			else
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="in" /><input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="i" /></td>';
			}
			$checkbox_column_no++;
			if($result["DEdit"] == 1)
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="un" />
			<input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="u" checked /></td>';
			}
			else
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="un" />
			<input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="u" /></td>';
			}
			$checkbox_column_no++;
			if($result["DDelete"] == 1)
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="dn" />
			<input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="d" checked /></td>';
			}
			else
			{
				$table .= '<td align="center"><input type="hidden" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="dn" />
			<input type="checkbox" class="check-width"  name="checkbox'.$row_number.'-'.$checkbox_column_no.'" value="d" /></td>';
			$table .= '</tr>'; 
			}
			}
			$table .= '<td align="center" style="display:none;"><input type="hidden" class="check-width"  name="pageid'.$row_number.'" value="'.$result["PageId"].'" /></td></tr>';
			$row_number++;
		}
		$table .= '</tbody></table>';
		$table .= ' <div class="form-group"> 
                    <button type="submit" class="btn btn-md btn-info btn-font" id="submit" name="userprivilages">Save</button>
                    </div>';
}
else
{
	$table='';
}
echo $table;

?>
