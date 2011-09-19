<?php /* ADMIN $Id: vw_usr_assigned_proj.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
GLOBAL  $user_id;

/* para agregarlo como admin a los proyectos */

if ($AppUI->user_type == 1){
	$sql = "SELECT DISTINCT project_id, project_name FROM projects where  project_active <> 0";
	$oprojects=db_loadHashList($sql);
}else{ 
	$oprojects = cUser::getOwnedProjects($AppUI->user_id);
}


$canEdit = count($oprojects) > 0;


/*
$sql = "
SELECT DISTINCT projects.*
FROM projects LEFT JOIN project_owners ON projects.project_id = project_owners.project_id
WHERE ( project_owners.project_owner = $user_id OR projects.project_owner = $user_id )
	AND project_active <> 0
ORDER BY project_name
";
*/



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

//--></script>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="">
<form name="editFrm" action="" method="post">
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="project_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_projectadm_aed" />

<tr class="tableHeaderGral">
	<th colspan="3"><?php echo $AppUI->_('Assigned Projects');?></th>
</tr>
<tr class="tableHeaderGral">
	<th><?php echo $AppUI->_('Name');?></th>
	<th><?php echo $AppUI->_('Status');?></th>
	<th><?php echo "&nbsp;";?></th>
</tr>
<?php 
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
		<td><?php echo $pstatus[$row["project_status"]]; ?></td>
		<td><?php 
		
		if (isset($oprojects[$row["project_id"]])){
			echo "<a href=# onClick=\"delIt({$row['project_id']});\" title=\"".$AppUI->_('delete')."\">"
				. dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' )
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
		$cbo = arraySelect( $oprojects, 'adm_projects', 'class="text" size="1"', '', false);
		echo "<tr>
						<td colspan=\"3\">$cbo&nbsp;&nbsp;
							<input type=\"button\" value=\"".$AppUI->_('add')."\" class=\"button\" onclick=\"submitIt();\" />
						</td>
						</tr>";
	}	
	?>
	
	
</table>

