<?php
global $drow, $id;

$AppUI->savePlace();
?>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="" method="post">
	<input type="hidden" name="dosql" value="do_role_aed" />
	<input type="hidden" name="job_id" value="<?php echo $id;?>" />
	<input type="hidden" name="job_dep" value="<?php echo $drow["job_department"];?>" />
	<input type="hidden" name="job_rep" value="<?php echo $drow["job_report_to"];?>" />
	<input type="hidden" name="add" value="1" />
<tr>
	<td width="20%"><br></td>
	<td><br></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Denomination' );?>:</td>
	<td><input type="text" class="text" name="job_name" value="<?php echo @$drow["job_name"];?>" maxlength="24" style="width:250px;" ></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Company' );?>:</td>
	<td>
	<?php
		$Ccompany = new CCompany();
		$companies = $Ccompany->getCompanies($AppUI->user_id);
		
		foreach ($companies AS $a=>$comp){
			$Companies[$comp["company_id"]] = $comp["company_name"];
		}
		
		if ($drow["job_company"]==""){
			LIST($company) = EACH($Companies);
		}else{
	    	$company = $drow["job_company"];
		}
		echo arraySelect( $Companies, 'job_company', 'size="1" class="text" style="width:250px;" onchange="changeCompany();"', $company);
	?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Department' );?>:</td>
	<td>
	<?php
		$department = $drow["job_department"];
    	$sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company = $company
				AND dept_name <> ''
				ORDER BY dept_name";
		$departments = db_loadHashList($sql);
		
		if (empty($drow["job_department"])){
			$department = 0;
		}
		$departments["0"] = $AppUI->_("none");
		echo arraySelect( $departments, 'job_department', 'size="1" class="text" style="width:250px;" onchange="changeDepartment();"', $department);
	?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Report to' );?>:</td>
	<td>
	<?php
		if (empty($drow["job_report_to"])){
			$report_to = 0;
		}else{
	    	$report_to = $drow["job_report_to"];
		}
		
		if($department != 0){
			$AND = "AND job_department = $department";
		}else{
			$AND = "";
		}
		
		if(!empty($company)){
			$sql = "SELECT job_id, job_name AS name FROM hhrr_jobs
					WHERE job_company = $company 
					$AND 
					ORDER BY job_name";
			
			$users = db_loadHashList($sql);
		}
		$users["0"] = $AppUI->_("none");
		echo arraySelect( $users, 'job_report_to', 'size="1" class="text" style="width:250px;"', $report_to);
	?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Main functions' );?>:</td>
	<td>
		<textarea class="text" name="job_main_functions" rows="8" style="width:500px;" ><?php echo @$drow["job_main_functions"];?></textarea>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Requirements for the position' );?>:</td>
	<td>
		<textarea class="text" name="job_requirements" rows="8" style="width:500px;" ><?php echo @$drow["job_requirements"];?></textarea>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>

<script type="text/javascript">
function submitIt(){
	f = document.editFrm;
	var ok = true;
	
	if(f.job_name.value == ""){
		alert("<?=$AppUI->_("Please complete the Job name")?>");
		ok = false;
	}
	
	if(f.job_company.value == 0){
		alert("<?=$AppUI->_("Please complete the Job company")?>");
		ok = false;
	}
	
	if(ok){
		f.submit();
	}
}

function changeCompany(){
	f = document.editFrm;
	rep_sel = f.job_rep.value;
	dep_sel = f.job_dep.value;
	xajax_changeCompany(f.job_company.value,'job_department','job_report_to', dep_sel, rep_sel);
}

function changeDepartment(){
	f = document.editFrm;
	xajax_changeDepartment(f.job_company.value,f.job_department.value,'job_report_to');
}
</script>