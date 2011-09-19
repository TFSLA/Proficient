<?php
global $project_id, $canManageRoles;

// load the record data
$row = new CProject();

if (!$row->load( $project_id, false ) && $project_id > 0) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if (count( $companies ) < 2 && $project_id == 0) {
	$AppUI->setMsg( "noCompanies", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// Traigo los roles
$query_rol1 = "SELECT distinct(role_id) FROM role_permissions WHERE project_id = '$project_id' ";
$sql_rol1 = db_loadHashList($query_rol1);
$rjk1 = implode("','", $sql_rol1);
                 
$query_rol2 = "SELECT  distinct(role_permissions.role_id) FROM role_permissions, projects WHERE role_permissions.company_id = projects.project_company and projects.project_id='$project_id' and role_id NOT IN ('".$rjk1."')";
$sql_rol2 = db_loadHashList($query_rol2);
$rjk2 = implode("','", $sql_rol2); 
                 
if($rjk1!="" && $rjk2!=""){
       $roles = "'".$rjk1."','".$rjk2."'";
}else{
      if($rjk1!="") $roles = "'".$rjk1."'";
      if($rjk2!="") $roles = "'".$rjk2."'";
}
                 
$query_roles = "SELECT role_id, role_name FROM roles WHERE role_id IN (".$roles.") ";
$vec_roles = db_loadHashList($query_roles);

$canEdit = $row->canEdit();
$prole = new CProjectRoles();

//obtengo usuarios asignados a tareas
$assigned_users = $row->getUsersAssignedToTasks($project_id);

$unassigned_users = CUser::getAssignableUsers("user_id, CONCAT_WS(', ',user_last_name,user_first_name)");

//obtengo lista de usuarios asignados como usuarios del proyecto
//$project_users_list = $prole->getList(2 ,$project_id);
//echo "<pre>"; print_r($vec_roles); echo "</pre>";

foreach($vec_roles as $rid =>$rdesc)
{
     $project_users_list_tmp_{$rid} = $prole->getList($rid  ,$project_id);
    //echo "$rid:<pre>"; print_r($project_users_list_tmp_{$rid} ); echo "</pre>";
    
    $project_users_list = array_merge((array) $project_users_list, (array) $project_users_list_tmp_{$rid} );
}

//Reordeno la lista de usuarios asignados como usuarios del proyecto
$project_users = array();
for($i=0; $i < count($project_users_list); $i++){
	
	$project_users[$project_users_list[$i]["user_id"]] = $project_users_list[$i];
	
	$query_u = "SELECT access_level FROM btpsa_project_user_list_table WHERE user_id='".$project_users_list[$i]["user_id"]."' AND project_id ='".$project_id."' ";
		
	$result = db_loadResult( $query_u);
	
	$project_users[$project_users_list[$i]["user_id"]]['access_level'] = $result; 
	
	// si el usuario ya está asignado lo quito de la lista de disponibles para asignar
	if (isset($unassigned_users[$project_users_list[$i]["user_id"]]))
		unset($unassigned_users[$project_users_list[$i]["user_id"]]);
}
unset($project_users_list);

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

//echo "<h1>".$AppUI->_("Project Users")."</h1>";
?>
<script language="javascript">
<?="<!--";?>

function editUser( id, name, role_id, access_level) {

	var f = document.editFrm;
	
	if(access_level=="") access_level = '10';
	
	f.sqlaction2.value="<?php echo $AppUI->_('Edit');?>";
	f.add.value=0;
	f.user_id.value = id;
	f.user_name.value = name;
	f.role_id.value = role_id;
	f.access_level.value = access_level;
	f.user_name.style.display = "";
	f.new_user.style.display = "none";
	return true;
}

function clearUser(){
	var f = document.editFrm;
	
	f.add.value=1;
	f.user_id.value = 0;
	f.user_name.value = "";
	f.user_units.value = "0.00";
	f.user_name.style.display = "none";
	f.new_user.style.display = "";
	
	f.sqlaction2.value = "<?php echo $AppUI->_('Add');?>";
}

function delUser(id, has_tasks) {
	if (has_tasks=="1"){
		var msg = "<?php echo $AppUI->_("The selected user has one or more tasks assigned to him. Do you really want to delete the user from this project and all his assignations to tasks on it?");?>"
	}else{
		var msg = "<?php echo $AppUI->_("Are you sure you want to delete this user?");?>";
	}
	if (confirm1( msg )) {
		var f = document.delFrm;
		f.user_id.value = id;
		f.del.value = "1";
		f.submit();
	}
}

function selectUser(){
	var f = document.editFrm;
	var cbo = f.new_user;
	var user_id = cbo.options[cbo.selectedIndex].value;
	f.user_id.value = user_id;
}


function validateForm(){
	var f = document.editFrm;
	var msg = "";
	var ret = false;
	var unit = parseFloat(f.user_units.value);
	if (isNaN(unit) || unit > 100 || unit < 0 ) {
		msg += "The units must be an integer between 0 and 100" + "\n";
	}
	if (!( f.user_id.value > 0)) {
		msg += "There is no selected user." + "\n";
	}

	if (msg==""){
		ret= true;	
	}else{
		alert (msg);
	}
	
	return ret;
	
}

//-->
</script>
<TABLE width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="500" valign="top">
<tr class="tableHeaderGral">
	<th width="3%">&nbsp;</th>
	<th width="3%px">&nbsp;</th>
	<th align="left" width="11%"><?php echo $AppUI->_( 'User' );?></th>
	<th align="left"><?php echo $AppUI->_( 'Project Roles' );?></th>
	<th width="30px">&nbsp;</th>
	<th >&nbsp;</th>
</tr>
<tr>
<td valign="top" colspan="5">
<table width="100%" border=0 cellpadding="0" cellspacing="0" class="" >
<tr class="tableHeaderGral" style="height:1px">
	<th width="3%"></th>
	<th width="10%"></th>
	<th align="left" width="25%"></th>
	<th align="left"></th>
	<th width="30px"></th>
</tr>
<?php
//echo "<pre>"; print_r($project_users); echo "</pre>";
$s = '';
$s .="<form name=\"delFrm\" method=\"post\">
		<input type=\"hidden\" name=\"del\" value=\"0\" />
		<input type=\"hidden\" name=\"user_id\" value=\"\" />
		<input type=\"hidden\" name=\"dosql\" value=\"do_project_users_aed\" />
		<input type=\"hidden\" name=\"project_id\" value=\"$project_id\" />";

if (count($project_users)>0)
	foreach ($project_users as $user_id => $row){
		$user_name = $row["user_first_name"]." ".$row["user_last_name"];
		$user_units = number_format($row["user_units"],0);
		$is_assigned_to_task = isset($assigned_users[$user_id]) ? "1" : "0";
		$access_level = $row['access_level'];
		
		$params["calendar_user"]= $user_id;
		$params["calendar_status"]= 1;
		
		$userCalendars = CCalendar::getCalendars($params);
		
        $s .= "<tr><td colspan=\"5\"></td></tr>";
        $s .= '<tr>';
		$s .= '<td align="center" width="1">';
		
		
		if(count($userCalendars) > 0)
		{
			$s .= "<a href=\"index.php?m=admin&a=calendars&user_id=".$user_id."\" title=\"".$AppUI->_('Calendar')."\">"
				."<img src=\"./images/icons/calendar_ico.gif\" width=\"20\" height=\"20\" alt=\"".$AppUI->_('Work calendar')."\" border=\"0\" />"
				."</a>";
		}
		else
		{
			$s .= "<img src=\"./images/icons/calendar_ico_disa.gif\" width=\"20\" height=\"20\" alt=\"".$AppUI->_('Work calendar')."\" border=\"0\" />";
		}
		
		$s .= '</td>';
		$s .= '<td align="center" width="1">';
		$s .= "<a href=\"javascript: //\" onClick=\"editUser({$user_id},'".$AppUI->_($user_name)."','".$row['role_id']."','".$row['access_level']."');\" title=\""
			.$AppUI->_('Edit')."\">"					
			."<img src=\"./images/icons/edit_small.gif\" width=\"20\" height=\"20\" alt=\"".$AppUI->_('Edit')."\" border=\"0\" />"
			."</a></td>";					
		$s .= '<td nowrap="nowrap">'.$user_name.'</td>';
		//$s .= '<td nowrap="nowrap" align="right">'.$user_units.' %</td>';
		
		$s .= '<td nowrap="nowrap" >'.$AppUI->_($vec_roles[$row['role_id']]).'</td>';
		$s .= '<td align="center">';
		
		$s .= "<a href=\"javascript: delUser('{$user_id}', '{$is_assigned_to_task}');\" >";
		$s .= "<img src=\"./images/icons/trash_small.gif\" border=\"0\" title=\""
			.$AppUI->_('Delete')."\" alt=\"".$AppUI->_('Delete')."\"></a>";
		$s .= '</td></tr>';
        $s .= "<tr class=\"tableRowLineCell\"><td colspan=\"4\"></td></tr>";
	}
$s .= '</form>';
echo $s;
?>

</table>
</td>
<td valign="top">
	<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tableForm_bg">
		<form name="editFrm" method="post" onSubmit="return validateForm();">
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="add" value="1" />
		<input type="hidden" name="dosql" value="do_project_users_aed" />
		<input type="hidden" name="project_id" value="<?=$project_id;?>" />
		<input type="hidden" name="user_units" class="text" size="4" value="100"  />
		<input type="hidden" name="user_id" value="" />
		<tr>
			<td nowrap align="right">
			<?=$AppUI->_("User");?>:</td>
			<td nowrap>
				<?php echo arraySelect( $unassigned_users, 'new_user', 'class="text" size="1" onchange="selectUser()"', null ); ?>
				<input type="text" name="user_name" size="24" class="text" value="" disabled="true" style="display: none;" />
			</td>	
		</tr>
		<tr>
	        <td nowrap align="right"><?=$AppUI->_("Rol");?>:</td>
	        <td nowrap>
	           <?
	                 if($role_id ==""){
	                 	$role_id = '2';
	                 }
	                
	                 echo arraySelect(  $vec_roles , 'role_id', 'class="text" size="1" ', $role_id,true); 
	                 
	           ?>
	        </td>
		</tr>
		<tr>
		     <td nowrap align="right"><?=$AppUI->_("access_level_webtraking")?>:</td>
		     <td nowrap>
		           <?
		               // Traigo los accesos para webtracking
		               require_once( "./modules/webtracking/config_defaults_inc.php" );
		               
		               $ac_tmp = explode(",",$g_access_levels_enum_string);
		              
		               foreach ($ac_tmp as $id=>$val)
		               {
		                    $ac = explode(":",$val);
		                    $access_w[$ac['0']] = $AppUI->_($ac['1']);
		               }
		          
		               if($access_level=="")
		               {
		                   $access_level = '25';
		               }
		               
		               echo arraySelect(  $access_w, 'access_level', 'class="text" size="1" ', $access_level); 
		               
		           ?>
		     </td>
		</tr>
		<tr>
			<td>
				<input type="reset" value="<?php echo $AppUI->_('clear');?>" class="button" name="sqlaction" onClick="clearUser();">
			</td>
			<td align="right">
				<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
			</td>
		</tr>
	</FORM>
	</table>
	</td>
</tr>
</TABLE>

<? if ($canManageRoles){ ?>
<br>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="">
     <tr>
      <td width="100%" class="tableForm_bg" height='20'><strong><? echo $AppUI->_('Permissions');?></strong></td>
      </tr>
      <tr>
          <td> 
              <? require_once( "vw_perms.php" ); ?> 
          </td>
      </tr>
</table>
<? } ?>

<script language="javascript"><!-- 
selectUser();
//--></script>
