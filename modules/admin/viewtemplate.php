<?php /* ADMIN $Id: viewtemplate.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
$AppUI->savePlace();

$securitytempate_id = isset( $_GET['securitytempate_id'] ) ? $_GET['securitytempate_id'] : 0;

/*
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserVwTab' ) !== NULL ? $AppUI->getState( 'UserVwTab' ) : 0;
*/

// pull data
$sql = "
SELECT securitytemplates.*, 
	securitytemplate_id, securitytemplate_name
FROM securitytemplates
WHERE securitytemplate_id = $securitytemplate_id
";
if (!db_loadHash( $sql, $user )) {
	$titleBlock = new CTitleBlock( 'Invalid Template ID', 'user_management.jpg', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=admin", "Template list" );
	$titleBlock->show();
} else {

// setup the title block
	$titleBlock = new CTitleBlock( 'View template', 'user_management.gif', $m, "$m.$a" );
	if(!getDenyRead('users')){
		$titleBlock->addCrumb( "?m=admin&tab=0", "users list" );
		$titleBlock->addCrumb( "?m=admin", "template list" );
	}
	if ($canEdit)
		$titleBlock->addCrumb( "?m=admin&a=addedittemplate", "add template");	
	if ($canEdit || $securitytemplate_id==$AppUI->securitytemplate_id) {
		$titleBlock->addCrumb( "?m=admin&a=addedittemplate&securitytemplate_id=$securitytemplate_id", "edit this template");
	}
	if ($canEdit)
		$titleBlock->addCrumb( "?m=admin&a=addedituser", "add user");
		
	$titleBlock->show();
?>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr valign="top">
	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td nowrap><strong><?php echo $AppUI->_('Template Name');?>:</strong></td>
		</tr><tr>
			<td class="hilite" width="100%"><?php echo $user["securitytemplate_name"];?></td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<table width="100%">
		<tr>
			<td colspan="2"><strong><?php echo $AppUI->_('Description');?>:</strong></td>
		</tr>
		<tr>
			<td class="hilite" width="100%" colspan="2">
				<?php echo str_replace( chr(10), "<br />", $user["securitytemplate_description"]);?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<?

GLOBAL $AppUI, $securitytemplate_id, $canEdit, $tab;

$pgos = array(
	'files' => 'file_name',
	'users' => 'user_username',
	'projects' => 'project_name',
	'tasks' => 'task_name',
	'companies' => 'company_name',
	'forums' => 'forum_name'
);

$pvs = array(
'-1' => 'read-write',
//'0' => 'deny',
'1' => 'read only'
);


//Pull User perms
$sql = "
SELECT u.securitytemplate_id, u.securitytemplate_name,
	p.template_permission_item, p.template_permission_id, p.template_permission_grant_on, p.template_permission_value,
	c.company_id, c.company_name,
	pj.project_id, pj.project_name,
	t.task_id, t.task_name,
	f.file_id, f.file_name,
	fm.forum_id, fm.forum_name,
	u2.securitytemplate_id, u2.securitytemplate_name
FROM securitytemplates u, securitytemplate_permissions p
LEFT JOIN companies c ON c.company_id = p.template_permission_item and p.template_permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.template_permission_item and p.template_permission_grant_on = 'projects'
LEFT JOIN tasks t ON t.task_id = p.template_permission_item and p.template_permission_grant_on = 'tasks'
LEFT JOIN files f ON f.file_id = p.template_permission_item and p.template_permission_grant_on = 'files'
LEFT JOIN securitytemplates u2 ON u2.securitytemplate_id = p.template_permission_item and p.template_permission_grant_on = 'users'
LEFT JOIN forums fm ON fm.forum_id = p.template_permission_item and p.template_permission_grant_on = 'forums'
WHERE u.securitytemplate_id = p.template_permission_template
	AND u.securitytemplate_id = $securitytemplate_id
";

$res = db_exec( $sql );

//pull the projects into an temp array
$tarr = array();
while ($row = db_fetch_assoc( $res )) {
	$item = @$row[@$pgos[$row['template_permission_grant_on']]];
	if (!$item) {
		$item = $row['template_permission_item'];
	}
	if ($item == -1) {
		$item = 'all';
	}
	$tarr[] = array_merge( $row, array( 'grant_item'=>$item ) );
}

// read the installed modules
$modules = arrayMerge( array( 'all'=>'all' ), $AppUI->getActiveModules( 'modules' ));
?>

<script language="javascript">
function editPerm( id, gon, it, vl, nm ) {
/*
	id = template_permission_id
	gon =template_permission_grant_on
	it =template_permission_item
	vl =template_permission_value
	nm = text representation of template_permission_value
*/
//alert( 'id='+id+'\ngon='+gon+'\nit='+it+'\nvalue='+vl+'\nnm='+nm);
	var f = document.frmPerms;

	f.sqlaction2.value="edit";
	
	f.template_permission_id.value = id;
	f.template_permission_item.value = it;
	f.template_permission_item_name.value = nm;
	for(var i=0, n=f.template_permission_grant_on.options.length; i < n; i++) {
		if (f.template_permission_grant_on.options[i].value == gon) {
			f.template_permission_grant_on.selectedIndex = i;
			break;
		}
	}
	f.template_permission_value.selectedIndex = vl+1;
	f.template_permission_item_name.value = nm;
}

function clearIt(){
	var f = document.frmPerms;
	f.sqlaction2.value = "add";
	f.template_permission_id.value = 0;
	f.template_permission_grant_on.selectedIndex = 0;
}

function delIt(id) {
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var f = document.frmPerms;
		f.del.value = 1;
		f.template_permission_id.value = id;
		f.submit();
	}
}

var tables = new Array;
tables['companies'] = 'companies';
tables['departments'] = 'departments';
tables['projects'] = 'projects';
tables['tasks'] = 'tasks';
tables['forums'] = 'forums';

function popPermItem() {
	var f = document.frmPerms;
	var pgo = f.template_permission_grant_on.options[f.template_permission_grant_on.selectedIndex].value;
	if (!(pgo in tables)) {
		alert( 'No list associated with this Module.' );
		return;
	}
	window.open('./index.php?m=public&a=selector&dialog=1&callback=setPermItem&table=' + tables[pgo], 'selector', 'left=50,top=50,height=250,width=400,resizable')
}

// Callback function for the generic selector
function setPermItem( key, val ) {
	var f = document.frmPerms;
	if (val != '') {
		f.template_permission_item.value = key;
		f.template_permission_item_name.value = val;
	} else {
		f.template_permission_item.value = '-1';
		f.template_permission_item_name.value = 'all';
	}
}
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="50%" valign="top">

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="">
<tr class="tableHeaderGral">
	<td>&nbsp;</td>
	<td nowrap="nowrap"><?php echo $AppUI->_('Module');?></td>
	<td width="100%"><?php echo $AppUI->_('Item');?></td>
	<td nowrap><?php echo $AppUI->_('Type');?></td>
	<td>&nbsp;</td>
</tr>

<?php
foreach ($tarr as $row){
	$buf = '';

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=# onClick=\"editPerm({$row['template_permission_id']},'{$row['template_permission_grant_on']}',{$row['template_permission_item']},{$row['template_permission_value']},'{$row['grant_item']}');\" title=\"".$AppUI->_('edit')."\">"
			. dPshowImage( './images/icons/edit_small.gif', 20, 20, '' )
			. "</a>";
	}
	$buf .= '</td>';

	$style = '';
	if($row['template_permission_grant_on'] == "all" && $row['template_permission_item'] == -1 && $row['template_permission_value'] == -1) {
		$style =  'style="background-color:#ffc235"';
	} else if($row['template_permission_item'] == -1 && $row['template_permission_value'] == -1) {
		$style = 'style="background-color:#ffff99"';
	}

	$buf .= "<td $style>" . $row['template_permission_grant_on'] . "</td>";

	$buf .= "<td>" . $row['grant_item'] . "</td><td nowrap>" . $pvs[$row['template_permission_value']] . "</td>";

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=# onClick=\"delIt({$row['template_permission_id']});\" title=\"".$AppUI->_('delete')."\">"
			. dPshowImage( './images/icons/trash_small.gif', 16, 16, '' )
			. "</a>";
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


</td><td width="50%" valign="top">

<?php if ($canEdit) {?>

<table cellspacing="1" cellpadding="0" border="0" class="std" width="100%">
<form name="frmPerms" method="post" action="?m=admin">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_tpl_perms_aed" />
	<input type="hidden" name="securitytemplate_id" value="<?php echo $securitytemplate_id;?>" />
	<input type="hidden" name="template_permission_template" value="<?php echo $securitytemplate_id;?>" />
	<input type="hidden" name="template_permission_id" value="0" />
	<input type="hidden" name="template_permission_item" value="-1" />
<tr class="tableHeaderGral">
	<td colspan="2"><?php echo $AppUI->_('Add or Edit Permissions');?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Module');?>:</td>
	<td width="100%"><?php echo arraySelect($modules, 'template_permission_grant_on', 'size="1" class="text"', 'all');?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Item');?>:</td>
	<td>
		<input type="text" name="template_permission_item_name" class="text" size="30" value="all" disabled>
		<input type="button" name="" class="text" value="..." onclick="popPermItem();">
	</td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Level');?>:</td>
	<td><?php echo arraySelect($pvs, 'template_permission_value', 'size="1" class="text"', 0);?></td>
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
<br><br>



<?php 

$users = CUser::getAssignableUsersPerm();
$users_perm = array();
$selected_users = array();

?>
	<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
	<form name="frmapplytpl" method="post" action="">
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="dosql" value="do_perms_batch_aed" />
		<input type="hidden" name="securitytemplate_id" value="<?php echo $securitytempate_id?>" />
		<input type="hidden" name="template_permission_template" value="<?php echo $securitytemplate_id;?>" />
		<input type="hidden" name="applytemplate" value="1" />
		<input type="hidden" name="redirect" value="" />		
		<input type="hidden" name="userlist" value="" />	
		<tr class="tableHeaderGral">
			<td nowrap="nowrap" colspan="2"><?php echo $AppUI->_('Apply template');?></td>
		</tr>			
		<tr>
			<td><?php echo $AppUI->_( 'All Users' );?></td>
			<td><?php echo $AppUI->_( 'Selected Users' );?></td>
		</tr>
		<tr>
			<td>
			<select name= "all_users" style="width:180px" size="10" class="text" multiple="multiple">
			<?php 
			for($i=0;$i<count($users);$i++){
				extract($users[$i]);
				if ($cant == "0"){
					echo "<option value=\"$user_id\" style=\"font-weight: bold;\">
							$fullname</option>\n";
				}else {
					$users_perm[] = $user_id;
					echo "<option value=\"$user_id\">
							$fullname</option>\n";
				}
			}
				?>
			</select>
				
			</td>
			<td>
				<?php echo arraySelect( $selected_users, 'users', 'style="width:180px" size="10" class="text" multiple="multiple"', null ); ?>
			</td>
		</tr>
		<tr>
			<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser()" /></td>
			<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser()" /></td>
		</tr>
		<tr>
			<td>
				<input type="reset" value="<?php echo $AppUI->_('clear');?>" class="button" name="sqlaction" onclick="clearUsers()">
			</td>		
			<td align="right">
				<input type="button" class="button" value="<?php echo $AppUI->_( 'Apply Now' );?>" onClick="applyNow()" />
			</td>
		</tr>		
		<tr>
			<td colspan="2">
			<?php echo $AppUI->_('noteTemplateUsersList');?>
			</td>
		</tr>		
	</form>	
	</table>
		
	
<script language="Javascript"><!-- 

var user_perm = new Array();

<?php 
for($i=0;$i<count($users_perm);$i++){
	echo "user_perm[".$users_perm[$i]."] = 1;\n";
}?>


function addUser() {
	var form = document.frmapplytpl;
	var at = form.all_users.length -1;
	var td = form.users.length -1;
	var tasks = "x";

	
	//build array of task dependencies
	for (var i=0; i < form.users.length; i++) {
		tasks = tasks + "," + form.users[i].value + ","
	}

	//Pull selected resources and add them to list
	for (var at=0; at < form.all_users.length; at++) {
		if (form.all_users.options[at].selected 
			&& tasks.indexOf( "," + form.all_users.options[at].value + "," ) == -1) {
			

			t = form.users.length
			opt = new Option( form.all_users.options[at].text, form.all_users.options[at].value );
			form.users.options[t] = opt
			if (!user_perm[opt.value])
				form.users.options[t].style.fontWeight = "bold";
		}
	}	
}
function removeUser() {
	var form = document.frmapplytpl;
	td = form.users.length -1;

	for (td; td > -1; td--) {
		if (form.users.options[td].selected) {
			form.users.options[td] = null;
		}
	}
}

function clearUsers() {
	var form = document.frmapplytpl;
	td = form.users.length -1;

	for (td; td > -1; td--) {
			form.users.options[td] = null;
	}
}

function applyNow(){
	var form = document.frmapplytpl;
	td = form.users.length -1;
	
	if( td == -1 ){
          alert("<?php echo $AppUI->_('Please select the users to apply this template');?>");
          form.all_users.focus();	
          return;
	}
	var msg = '';
	var users = '';
	form.userlist.value ='';
	for (td; td > -1; td--) {
		form.userlist.value += form.users.options[td].value + ",";
		if (user_perm[form.users.options[td].value]) {
			users += '· '+ form.users.options[td].text + '\n';
		}
	}	
	
	if(users!=""){
		if(!confirm("<?php echo $AppUI->_('templateUsersWthPerm');?>" + "\n\n"+users)){
			return;
		}
	}
		
	form.submit();

}
// --></script>	
<?php } ?>

</td>
</tr>
</table>

<?
}
?>
