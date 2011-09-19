<?php /* Roles $Id: vw_proj_roles.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
##
##	Companies: View User sub-table
##
//import_request_variables("P","p_");
GLOBAL  $project_id, $user_id, $_POST;
require_once( $AppUI->getModuleClass( 'companies' ) );
/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";
*/

$project = new CProject();
$projects = $project->getAllowedRecords( $uid, 'project_id, project_name', 'project_name', null, null );


$obj = new CProjectRoles();

if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}

$assigned=$obj->getAssignedRoles($project_id);
$unassigned=$obj->getUnassignedRoles($project_id);
$roles = arrayMerge($assigned, $unassigned);

$asgRole_ids = array_keys($roles);

$p_role_id = $_POST['role'] ? 
				$_POST['role']  :
				( $asgRole_ids[0] ? 
					$asgRole_ids[0] :
					0 );
//echo $p_role_id;
//var_dump($asgRole_ids);

// si no existen roles para la empresa deben crearse primero
if ($p_role_id==0){
	$prj = new CProject();
	//si el usuario no tiene
	if (!$prj->load($project_id, false)){
		$AppUI->setMsg( 'Project' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
		#echo $AppUI->_("There is no Roles created for this company").". &nbsp";
		#echo $AppUI->_("Please contact the project responsible").". &nbsp";
	
	}else{
		$company_id = $prj->project_company;
		echo $AppUI->_("There is no Roles created for this company").". &nbsp";
		echo $AppUI->_("Click")."&nbsp;<a href='./index.php?m=companies&a=view&company_id=$company_id&tab=5"."'>";
		echo $AppUI->_("here")."</a>&nbsp;".$AppUI->_("to manage roles").". &nbsp";
	}
}else{
//inicio parte si existen roles

$roleUsers = $obj->getAssignedUsers($p_role_id,$project_id);
$freeUsers = $obj->getUnassignedUsers($p_role_id,$project_id);

?>
<script language="javascript">
<?="<!--\n";?>
<?
/*
function validateDel() {
	if (!confirm( 'Are you sure you want to delete this user from the role?' )) {
		return false;
	}
} 
*/ ?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this user from the role?' )) {
		var f = document.frmUsers;
		f.del.value = 1 ;
		f.user_del.value=id;
		f.submit();
	}
}
//-->
</script>
<TABLE width="100%" border="0" cellpadding="0" cellspacing="0">
<tr valign="top"><td width="350" valign="top">
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr valign="top">
	<form name=pickRole method=post>
	<th colspan=3 align="center"><?php echo $AppUI->_( 'Role' );?>:
	<?php echo arraySelect( $roles, 'role', 'style="width:180px" style="font-size:9pt;" class="small" onchange="javascript: document.pickRole.submit();"', $p_role_id ); ?>
	</th>
	</form>
</tr>

<tr>
	<th width="300px"><?php echo $AppUI->_( 'Users' );?></th>
	<th width="30px">&nbsp;</th>
</tr>
<form name="frmUsers" method="post" onSubmit="return validateDel();">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="user_del" value="0" />
	<input type="hidden" name="role" value="<?=$p_role_id;?>" />
	<input type="hidden" name="dosql" value="do_role_users_aed" />
	<input type="hidden" name="project" value="<?=$project_id;?>" />
<?php
foreach ($roleUsers as $user_id => $user_name){?>

	<tr>
		<td nowrap><?=$user_name;?></td>
<!--	<form name="delUsers" method="post" onSubmit="return validateDel();">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user" value="<?=$user_id;?>" />
	<input type="hidden" name="role" value="<?=$p_role_id;?>" />
	<input type="hidden" name="dosql" value="do_role_users_aed" />
	<input type="hidden" name="project" value="<?=$project_id;?>" />		
		<td align="center">
			<input type=image src='./images/icons/trash_small.gif' width=20 height=20 border=0
			name=user_id_val value="<?=$user_id;?>" 
			title="<?=$AppUI->_('Delete');?>" alt="<?=$AppUI->_('Delete');?>"></td>
	</form>-->
			<td align="center">
			<img src='./images/icons/trash_small.gif' border=0
			onclick="javascript: delIt(<?=$user_id;?>);" 
			title="<?=$AppUI->_('Delete');?>" alt="<?=$AppUI->_('Delete');?>"></td>

	</tr>

	<?php 
}
?>


<tr>
<!--	<form method="post" name="addUsers" >
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="role" value="<?=$p_role_id;?>" />
	<input type="hidden" name="project" value="<?=$project_id;?>" />
	<input type="hidden" name="dosql" value="do_role_users_aed" />-->
		<td><?php echo arraySelect( $freeUsers, 'user', 'style="width:100%" style="font-size:9pt;" class="small"', null ); ?></td>
		<td width="30px"><input type="submit" value="<?php echo $AppUI->_('add');?>" class="button"></td>
	<!--</form>	-->
</tr>
</form>
</table>
</td>
</tr>
</TABLE>
<?php
}
//fin parte si existen roles
?>