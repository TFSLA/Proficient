<?php /* Roles $Id: vw_roles.php,v 1.5 2009-07-29 19:07:34 nnimis Exp $ */
##
##	Companies: View User sub-table
##
GLOBAL $AppUI, $company_id,$m,$tab,$form;


$form = $form  ? $form : 1;


if($form==2){
	include_once("./modules/$m/vw_roles_perms.php");
}else{


$obj = new CRoles();
if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}

$rolesList=$obj->getRoles($company_id, 1);
if (count($rolesList)>0)
	foreach ($rolesList as $i=>$rl){
		//if ($AppUI->user_type==1 || $rl['role_type']==1)
			$roles[]=$rl;
	}
?>

<script language="javascript">
<?="<!--";?>


function editIt( id, name, desc ) {

	var f = document.frmRoles;
	
	f.sqlaction2.value="<?php echo $AppUI->_('Edit');?>";
	f.add.value=0;
	f.role_id.value = id;
	f.role_name.value = name;
	f.role_description.value = desc;

	return true;
}

function clearIt(){
	var f = document.frmRoles;
	f.role_id.value = 0;
	f.add.value=1;
	f.role_name.value = "";
	f.role_description.value = "";

	f.sqlaction2.value = "<?php echo $AppUI->_('Add');?>";
}

function delRole(id) {
	if (confirm( '<?php echo $AppUI->_('confirmRoleDelete');?>' )) {
		var f = document.frmDel;
		f.role_id.value = id;
		f.del.value = "1";
		f.submit();
	}
}

function validateDel() {
	if (!confirm( 'Are you sure you want to delete this role?' )) {
		return false;
	}
}

function validateForm(){
	var f = document.frmRoles;
	var msg = "";
	f.role_name.value = trim(f.role_name.value);
	f.role_description.value = trim(f.role_description.value);
	
	var length = f.role_name.size;
	var regstr = "^([a-zA-Z0-9Ò—·ÈÌÛ˙¡…Õ”⁄∞\\s]{1," + length + "})$" ;
	var regex = new RegExp( regstr );
	var ret= false;
	if ( !( f.role_name.value.match(regex) ) )
	{
		msg += "<?php echo $AppUI->_('companyRoleValidName');?>";
	}
	var length = "255";
	var regstr = "^([a-zA-Z0-9Ò—·ÈÌÛ˙¡…Õ”⁄∞\\s]{0," + length + "})$" ;
	var regex = new RegExp( regstr );
	if ( !(f.role_description.value.match(regex) ) )
	{
		msg += "<?php echo $AppUI->_('companyRoleValidDescription');?>";
	}

	if (msg==""){
		ret= true;
		if (f.add.value == 1){
			msg="<?=$AppUI->_("Please, remember to assign security permissions to this role. Then, you can add users to it at the project level." );?>";
			alert(msg);
		}		
	}else{
		alert (msg);
	}
	

	
	return ret;
	
}

function changeMode(adv){
	var f = document.pickUser;
	f.vwAdvMode.value = adv;
	f.submit();
}

//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
<tr class="tableHeaderGral" style="height:15px">
	<th width="50px">&nbsp;</th>
	<th><?php echo $AppUI->_( 'Name' );?></th>
	<th width="100%">&nbsp;</th>
</tr>
<tr>
<td></td>
<td valign="top">
	<table width="450" cellpadding="2" cellspacing="0" border="0">
	<?php
	$s = '';
	$s .="<form name=\"frmDel\" method=\"post\">
			<input type=\"hidden\" name=\"del\" value=\"0\" />
			<input type=\"hidden\" name=\"role_id\" value=\"\" />
			<input type=\"hidden\" name=\"dosql\" value=\"do_roles_aed\" />
			<input type=\"hidden\" name=\"company\" value=\"$company_id\" />";
	if (count($roles)>0)
		foreach ($roles as $role){
			$s .= '<tr><td align="left">';
				
			$s .= "<a href='./index.php?m=companies&a=view&company_id=$company_id&tab=$tab&role_id={$role[role_id]}&form=2' title=\""
				.$AppUI->_('Edit permissions')."\">"					
				.'<img src="./images/obj/edit_permissions_small.gif" width="20" height="20" alt="" border="0" >'
				."</a> &nbsp;";
			if ($role[role_type]!=0){
				$s .= "<a href='javascript: //' onClick=\"editIt({$role[role_id]},'".$AppUI->_($role[role_name])."','{$role[role_description]}');\" title=\""
						.$AppUI->_('Edit')."\">"
						.'<img src="./images/icons/edit_small.gif" width="20" height="20" alt="" border="0" >'
						."</a>";
			}else{
				$s .= "&nbsp;";
			}					
			$s .= '&nbsp;&nbsp;&nbsp;'.$AppUI->_($role[role_name]).'</td>';
			$s .= '<td align=center>';
			if ($role[role_type]!=0){
				$s .= "<a href=\"javascript: delRole('{$role[role_id]}');\" > <img " 
						."src='./images/icons/trash_small.gif' border=0 title=\""
						.$AppUI->_('Delete')."\" alt=\"".$AppUI->_('Delete')."\"> </a>";
			}else{
				$s .= "&nbsp;";
			}
			$s .= '</td></tr>';
	        $s .= "<tr class=\"tableRowLineCell\"><td colspan=\"3\"></td></tr>";
		}
	$s .= '</form>';
	echo $s;
	?>
	</table>
</td>
<td>
	<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tableForm_bg">
		<form name="frmRoles" method="post" onSubmit="return validateForm();">
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="add" value="1" />
		<input type="hidden" name="role_id" value="0" />
		<input type="hidden" name="dosql" value="do_roles_aed" />
		<input type="hidden" name="company" value="<?=$company_id;?>" />
	<tr>
		<td nowrap align="right">
		<?=$AppUI->_("Name");?>:</td>
		<td nowrap><input type="text" name="role_name" size=24 class="text" value="">
		</td>	
	</tr>
	<tr>
		<td nowrap align="right">
		<?=$AppUI->_("Description");?>:</td>
		<td nowrap><textarea name="role_description" class="small" rows="4" cols="40" ></textarea>
		</td>	
	</tr>
	<tr>
		<td>
			<input type="reset" value="<?php echo $AppUI->_('clear');?>" class="button" name="sqlaction" onClick="clearIt();">
		</td>
		<td align="right">
			<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
		</td>
	</tr>
	</FORM>
	</table>
</td>
</tr>
</table>

<?php
}
?>
