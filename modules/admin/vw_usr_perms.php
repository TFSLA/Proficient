<?php /* ADMIN $Id: vw_usr_perms.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
GLOBAL  $user_id, $canEdit, $tab;

$pgos = array(
	'files' => 'file_name',
	'users' => 'user_username',
	'projects' => 'project_name',
//	'tasks' => 'task_name',
	'companies' => 'company_name',
	'forums' => 'forum_name',
	'calendar' => 'delegado'
);

$pvs = array(
'-1' => $AppUI->_('read-write'),
//'0' => 'deny',
'1' => $AppUI->_('read only')
);


//Pull User perms
$sql = "
SELECT u.user_id, u.user_username,
	p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
	c.company_id, c.company_name,
	pj.project_id, pj.project_name,
	t.task_id, t.task_name,
	f.file_id, f.file_name,
	fm.forum_id, fm.forum_name,
	u2.user_id, u2.user_username,
	u3.user_id, concat(u3.user_first_name,' ',u3.user_last_name) delegado
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN tasks t ON t.task_id = p.permission_item and p.permission_grant_on = 'tasks'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
LEFT JOIN forums fm ON fm.forum_id = p.permission_item and p.permission_grant_on = 'forums'
LEFT JOIN users u3 ON u3.user_id = p.permission_item and p.permission_grant_on = 'calendar'
WHERE u.user_id = p.permission_user
	AND u.user_id = $user_id
";

$res = db_exec( $sql );

//pull the projects into an temp array
$tarr = array();
while ($row = db_fetch_assoc( $res )) {
	$item = @$row[@$pgos[$row['permission_grant_on']]];
	if (!$item) {
		$item = $row['permission_item'];
	}
	if ($item == -1) {
		$item = $AppUI->_('All');
	}
	$tarr[] = array_merge( $row, array( 'grant_item'=>$item ) );
}

// read the installed modules
$modules = arrayMerge( array( 'all'=>$AppUI->_('All') ), $AppUI->getActiveModules( 'modules' ));
//ordenamos la lista de opciones del drop down automaticamente

//natcasesort($modules);
//print_r($modules);

unset($modules['tasks']);
?>

<script language="javascript">
function editPerm( id, gon, it, vl, nm ) {
/*
	id = Permission_id
	gon =permission_grant_on
	it =permission_item
	vl =permission_value
	nm = text representation of permission_value
*/
//alert( 'id='+id+'\ngon='+gon+'\nit='+it+'\nvalue='+vl+'\nnm='+nm);
	var f = document.frmPerms;

	f.sqlaction2.value="<?=$AppUI->_("edit")?>";
	
	f.permission_id.value = id;
	f.permission_item.value = it;
	f.permission_item_name.value = nm;
	for(var i=0, n=f.permission_grant_on.options.length; i < n; i++) {
		if (f.permission_grant_on.options[i].value == gon) {
			f.permission_grant_on.selectedIndex = i;
			break;
		}
	}
	for(var i=0, n=f.permission_value.options.length; i < n; i++) {
		if (f.permission_value.options[i].value == vl) {
			f.permission_value.selectedIndex = i;
			break;
		}
	}

	f.permission_item_name.value = nm;
}

function clearIt(){
	var f = document.frmPerms;
	f.sqlaction2.value = "<?=$AppUI->_("add")?>";
	f.permission_id.value = 0;
	f.permission_grant_on.selectedIndex = 0;
}

function delIt(id) {
	if (confirm('<?=$AppUI->_('Are you sure you want to delete this permission?')?>' )) {
		var f = document.frmPerms;
		f.del.value = 1;
		f.permission_id.value = id;
		f.submit();
	}
}

var tables = new Array;
tables['companies'] = '<?=$AppUI->_("companies")?>';
tables['departments'] = 'departments';
//tables['projects'] = 'projects';
//tables['tasks'] = 'tasks';
tables['forums'] = 'forums';
tables['calendar'] = 'users';

function popPermItem() {
	var f = document.frmPerms;
	var pgo = f.permission_grant_on.options[f.permission_grant_on.selectedIndex].value;
	if (!(pgo in tables)) {
		alert1( '<?=$AppUI->_('No list associated with this Module.')?>' );
		return;
	}
	window.open('./index.php?m=public&a=selector&suppressLogo=1&dialog=1&callback=setPermItem&table=' + tables[pgo], 'selector', 'left=50,top=50,height=250,width=400,resizable')
}

// Callback function for the generic selector
function setPermItem( key, val ) {
	var f = document.frmPerms;
	if (val != '') {
		f.permission_item.value = key;
		f.permission_item_name.value = val;
	} else {
		f.permission_item.value = '-1';
		f.permission_item_name.value = '<?=$AppUI->_("all")?>';
	}
}
</script> 

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td width="40%" valign="top">

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
<tr bgcolor="#333333" class="boldblanco">
	<th>&nbsp;</th>
	<th nowrap="nowrap" width="130"><?php echo $AppUI->_('Module');?></th>
	<th width="80%"><?php echo $AppUI->_('Item');?></th>
	<th nowrap><?php echo $AppUI->_('Type');?></th>
	<th>&nbsp;</th>
</tr>

<?php
foreach ($tarr as $row){
	$buf = '';

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=# onClick=\"editPerm({$row['permission_id']},'{$row['permission_grant_on']}',{$row['permission_item']},{$row['permission_value']},'{$row['grant_item']}');\" title=\"".$AppUI->_('edit')."\">"
			. dPshowImage( './images/icons/edit_small.gif', 20, 20, '' )
			. "</a>";
	}
	$buf .= '</td>';

	$style = '';
	if($row['permission_grant_on'] == "all" && $row['permission_item'] == -1 && $row['permission_value'] == -1) {
		$style =  'style="background-color:#ffc235"';
	} else if($row['permission_item'] == -1 && $row['permission_value'] == -1) {
		$style = 'style="background-color:#ffff99"';
	}
           
	$buf .= "<td $style>" .$AppUI->_( $modules[$row['permission_grant_on']]). "</td>";

	$buf .= "<td>" . $row['grant_item'] . "</td><td nowrap>" . $pvs[$row['permission_value']] . "</td>";

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=# onClick=\"delIt({$row['permission_id']});\" title=\"".$AppUI->_('delete')."\"><img src='./images/icons/trash_small.gif' alt='Delete' border='0' /></a>";
	}
	$buf .= '</td>';
	
	echo "<tr>$buf</tr>";
    echo "<tr><td colspan=\"5\" bgcolor=\"#E9E9E9\"></td></tr>";
}
?>

</table>
<table>
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffc235">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Super User');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffff99">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('full access to module');?></td>
</tr>
</table>
</td><td width="30%" valign="top">

<?php if ($canEdit) {?>

<table cellspacing="0" cellpadding="0" border="0" class="std" width="100%">
<form name="frmPerms" method="post" action="">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_perms_aed" />
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="permission_user" value="<?php echo $user_id;?>" />
	<input type="hidden" name="permission_id" value="0" />
	<input type="hidden" name="permission_item" value="-1" />
	<input type="hidden" name="redirect" value="<?=$_SERVER['QUERY_STRING'];?>" />
<tr class="tableHeaderGral">
	<td colspan="2"><?php echo $AppUI->_('Add or Edit Permissions');?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Module');?>:</td>
	<td width="100%"><?php echo arraySelect($modules, 'permission_grant_on', 'size="1" class="text"', 'all',true);?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Item');?>:</td>
	<td>
		<input type="text" name="permission_item_name" class="text" size="30" value="<?=$AppUI->_('All')?>" disabled>
		<input type="button" name="" class="text" value="..." onclick="popPermItem();">
	</td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Level');?>:</td>
	<td><?php echo arraySelect($pvs, 'permission_value', 'size="1" class="text"', 0);?></td>
</tr>
<tr>
	<td>
		<input type="reset" value="<?php echo $AppUI->_('clear');?>" class="button" name="sqlaction" onClick="clearIt();">
	</td>
	<td align="right">
		<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
	</td>
</tr>
</form>
</table>

<?php } ?>

</td>











</td><td width="30%" valign="top">

<?php if ($canEdit) {?>

<?
$sql = "
SELECT securitytemplate_id, securitytemplate_name
FROM securitytemplates
ORDER BY securitytemplate_name
";
$securitytemplates = db_loadHashList( $sql );

$sql = "
SELECT mod_id, mod_name
FROM modules WHERE mod_has_security = 1
ORDER BY mod_ui_order
";
$moduleswithsecurity = db_loadHashList( $sql );
?>

<table cellspacing="0" cellpadding="0"  border="0" class="std" width="100%">
<form name="frmapplytpl" method="post" action="">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_perms_aed" />
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="applytemplate" value="1" />
	<input type="hidden" name="redirect" value="<?=$_SERVER['QUERY_STRING'];?>" />
<tr>
	<td class="tableHeaderGral" colspan="2"><?php echo $AppUI->_('Apply permission template');?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Template');?>:</td>
	<td width="100%"><?php echo arraySelect($securitytemplates, 'securitytemplate_id', 'size="1" class="text"', '',true);?><input type="submit"  value="<?php echo $AppUI->_('Apply');?>" class="button" name="sqlaction2"></td>
</tr>
<tr><td colspan=2 >&nbsp;</td></tr>
<!--
<tr>
	<th colspan="2"><?php echo $AppUI->_('Go to module specific permissions');?></th>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Module');?>:</td>
	<td width="100%"><?php echo arraySelect($moduleswithsecurity, 'mod_id', 'size="1" class="text"', '');?><input type="submit"  value="<?php echo $AppUI->_('Go');?>" class="button" name="gotomodulesecurity"></td>
</tr>
-->
<tr><td colspan=2 >&nbsp;</td></tr>
</table>

<?php } ?>

</td>


</tr>
</form>
</table>
