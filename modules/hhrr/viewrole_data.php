<?php 
global $drow, $id;
?>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<tr>
	<td><br></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Denomination' );?>:</td>
	<td><input type="text" class="text" name="job_name" value="<?php echo @$drow["job_name"];?>" maxlength="24" style="width:250px;" disabled ></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Company' );?>:</td>
	<td>
	<?php
		$sql = "SELECT company_name FROM companies WHERE company_id = ".$drow["job_company"];
		$data = mysql_fetch_array(mysql_query($sql));
	?>
	<input type="text" class="text" name="company" value="<?php echo @$data["company_name"];?>" style="width:250px;" disabled >
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Department' );?>:</td>
	<td>
	<?php
		if($drow["job_department"] != 0 && !empty($drow["job_department"])){
			$sql = "SELECT dept_name FROM departments WHERE dept_id = ".$drow["job_department"];
			$data = mysql_fetch_array(mysql_query($sql));
			$department = $data["dept_name"];
		}else{
			$department = $AppUI->_("none");
		}
	?>
	<input type="text" class="text" name="company" value="<?php echo @$department;?>" style="width:250px;" disabled >
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Report to' );?>:</td>
	<td>
	<?php
		if($drow["job_report_to"] != 0 && !empty($drow["job_report_to"])){
			$sql = "SELECT job_name as name
				FROM hhrr_jobs WHERE job_id = ".$drow["job_report_to"];
			$data = mysql_fetch_array(mysql_query($sql));
			$user = $data["name"];
		}else{
			$user = $AppUI->_("none");
		}
	?>
	<input type="text" class="text" name="company" value="<?php echo @$user;?>" style="width:250px;" disabled >
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Main functions' );?>:</td>
	<td>
		<textarea class="text" name="job_main_functions" rows="8" style="width:500px;" disabled><?php echo @$drow["job_main_functions"];?></textarea>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Requirements for the position' );?>:</td>
	<td>
		<textarea class="text" name="job_requirements" rows="8" style="width:500px;" disabled><?php echo @$drow["job_requirements"];?></textarea>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
</tr>
</table>