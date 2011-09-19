<?php /* ADMIN $Id: vw_usr_proj.php,v 1.2 2009-08-10 13:43:54 nnimis Exp $ */
GLOBAL  $user_id;

/* para agregarlo como admin a los proyectos */

if ($AppUI->user_type == 1){
	$sql = "SELECT DISTINCT project_id, project_name FROM projects where  project_active <> 0";
	$oprojects=db_loadHashList($sql);
}else{ 
	$oprojects = cUser::getOwnedProjects($AppUI->user_id);
}

$owned_projects = $oprojects;
$canEdit = count($oprojects) > 0;

$pstatus = dPgetSysVal( 'ProjectStatus' );

?>

<script language="javascript"><?php echo "<!-- ";?>

function delIt(id){

	if (confirm("<?php echo $AppUI->_("Are you sure you want this user not to be administrator of the selected project?") ?>")){
		var form = document.editFrm;
    form.project_id.value = id;
    form.del.value	 = "1";
    form.submit();		
	}
}

function submitIt(){
    var form = document.editFrm;

    form.project_id.value = form.adm_projects.options[form.adm_projects.selectedIndex].value;
    form.del.value	 = "0";
    form.submit();
}


function delUserProject(id, has_tasks){
	if (has_tasks=="1"){
		var msg = "<?php echo $AppUI->_("The user has one or more tasks assigned in this project.\\\\n Do you really want to delete this user from this project and all his assignations to tasks on it?");?>"
	}else{
		var msg = "<?php echo $AppUI->_("Are you sure you want to delete the user from the project?");?>";
	}		
	if (confirm( msg )) {		
		var form = document.editFrmProjects;
	    form.project_id.value = id;
	    form.del.value	 = "1";
	    form.submit();	
	}
}

function submitProjects(){
    var form = document.editFrmProjects;
	
    form.project_id.value = form.usr_projects.options[form.usr_projects.selectedIndex].value;
    form.del.value = "0";
    form.add.value = "1";
    form.submit();
}

//--></script>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="">
<col><col><col width="30px">
<form name="editFrm" action="" method="post">
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="project_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_projectadm_aed" />

<tr class="tableHeaderGral">
	<th colspan="3"><?php echo $AppUI->_('As Owner');?></th>
</tr>
<tr class="tableHeaderGral">
	<th><?php echo $AppUI->_('Name');?></th>
	<th><?php echo $AppUI->_('Status');?></th>
	<th><?php echo "&nbsp;";?></th>
</tr>

<?php 
	$sql = "
	SELECT DISTINCT projects.*
	FROM projects 
	WHERE ( projects.project_owner = $user_id )
		AND project_active <> 0
	ORDER BY project_name
	";
	$projects = db_loadList( $sql );
	
	if (count($projects)==0)
		echo "<tr><td colspan=\"3\">".$AppUI->_("No data available")."</td></tr>";
	else	
		foreach ($projects as $row) {	?>
	<tr>
		<td>
			<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
				<?php echo $row["project_name"];?>
			</a>
		<td><?php echo $AppUI->_($pstatus[$row["project_status"]]); ?></td>
		<td>&nbsp;</td>
		
	</tr>
	<?php } ?>
<tr class="tableHeaderGral">
	<th colspan="3"><?php echo $AppUI->_('As Administrator');?></th>
</tr>
<tr class="tableHeaderGral">
	<th><?php echo $AppUI->_('Name');?></th>
	<th><?php echo $AppUI->_('Status');?></th>
	<th><?php echo "&nbsp;";?></th>
</tr>
<?php 
	$sql = "
	SELECT DISTINCT projects.*
	FROM projects INNER JOIN project_owners ON projects.project_id = project_owners.project_id
	WHERE ( project_owners.project_owner = $user_id  )
		AND project_active <> 0
	ORDER BY project_name
	";
	$projects = db_loadList( $sql );
	
	if (count($projects)==0)
		echo "<tr><td colspan=\"3\">".$AppUI->_("No data available")."</td></tr>";
	else{
		foreach ($projects as $row) {	?>
	<tr>
		<td>
			<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
				<?php echo $row["project_name"];?>
			</a>
		<td><?php echo $AppUI->_($pstatus[$row["project_status"]]); ?></td>
		<td><?php 
		
		if (isset($oprojects[$row["project_id"]])){
			echo "<a href='javascript: void(0)' onClick=\"delIt({$row['project_id']});\" title=\"".$AppUI->_('delete')."\">"
				. dPshowImage( './images/icons/trash_small.gif', 16, 16, '' )
				. "</a>";
			unset ($oprojects[$row["project_id"]]);
		}else{
			echo "&nbsp;";
		}
		
		?></td>
		
	</tr>
	<?php } 
	}
	if (count($oprojects)){
		$cbo = arraySelect( $oprojects, 'adm_projects', 'class="text" size="1"', '', false);
		echo "<tr>
						<td colspan=\"3\">$cbo&nbsp;&nbsp;
							<input type=\"button\" value=\"".$AppUI->_('add')."\" class=\"button\" onclick=\"submitIt();\" />
						</td>
						</tr>";
	}	
	?>
	
	
</form>
<form name="editFrmProjects" action="" method="post">
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="role_id" value="<?php echo 2;?>" />
	<input type="hidden" name="project_id" value="<?php echo ''?>" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_project_users_aed" />
	<input type="hidden" name="add" value="0" />
<tr class="tableHeaderGral">
	<th colspan="3"><?php echo $AppUI->_('As Project User');?></th>
</tr>
<tr class="tableHeaderGral">
	<th><?php echo $AppUI->_('Name');?></th>
	<th><?php echo $AppUI->_('Status');?></th>
	<th><?php echo "&nbsp;";?></th>
</tr>
<?php 
	$oprojects = $owned_projects;
	$projects = array();
	$prj_ids = array_keys(cUser::getAssignedProjects($user_id));
	$prj_ids[] = "-1";
	$sql = "
	SELECT DISTINCT projects.*
	FROM projects 
	WHERE projects.project_id in (".implode(", ",$prj_ids).")
		AND project_active <> 0
	ORDER BY project_name
	";

	$projects = db_loadList( $sql );

	if (count($projects)==0)
		echo "<tr><td colspan=\"3\">".$AppUI->_("No data available")."</td></tr>";
	else{
		foreach ($projects as $row) {	?>
	<tr>
		<td>
			<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
				<?php echo $row["project_name"];?>
			</a>
		<td><?php echo $AppUI->_($pstatus[$row["project_status"]]); ?></td>
		<td><?php 
		
		if (isset($oprojects[$row["project_id"]])){
			$has_tasks = count(CUser::getAssignedTasks($row["project_id"], $user_id)) > 0 ? 1 : 0;
			echo "<a href='javascript: void(0)' onClick=\"delUserProject({$row['project_id']}, {$has_tasks});\" title=\"".$AppUI->_('delete')."\">"
				. dPshowImage( './images/icons/trash_small.gif', 16, 16, '' )
				. "</a>";
			unset ($oprojects[$row["project_id"]]);
		}else{
			echo "&nbsp;";
		}
		
		?></td>
		
	</tr>
	<?php } 
	}

	if (	count($oprojects)){
		$cbo = arraySelect( $oprojects, 'usr_projects', 'class="text" size="1"', '', false);
		echo "<tr>
						<td colspan=\"3\">$cbo&nbsp;&nbsp;
							<input type=\"button\" value=\"".$AppUI->_('add')."\" class=\"button\" onclick=\"submitProjects();\" />
						</td>
						</tr>";
	}	
	?>
	
</form>	
</table>
