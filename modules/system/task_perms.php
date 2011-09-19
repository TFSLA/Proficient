<?php /* PROJECTS $Id: task_perms.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
require_once( $AppUI->getModuleClass( 'tasks' ) );
GLOBAL $AppUI, $canEdit,$user_context;

$pvs = array(
'-1' => $AppUI->_('RW'),
//'0' => 'deny',
'1' => $AppUI->_('R')
);

$project_id=-1;

$obj = new CTaskPermission();

$perms=$obj->getPermissions(0,$project_id);
$access=$obj->getTaskAccess();
$items=$obj->getItemsPermission();



$tbl_perm=array();
$user_names=$user_context;
for ($i=0;$i<count($perms);$i++){
	$access_id=$perms[$i]["task_access_id"];
	$item_id=$perms[$i]["task_permission_on"];
	$task_user_id=$perms[$i]["task_user_id"];
	$tbl_perm[$access_id][$item_id][$task_user_id]=$perms[$i]["task_permission_value"];
	
	if ($task_user_id>0)
		$user_names[$task_user_id]= $perms[$i]["user_name"];
		
}

$titleBlock = new CTitleBlock( 'Edit Default Task Permissions', 'administration.jpg', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
?>
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std"><tr><td>


<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="40%" valign="top">

<?php
$jsHTMPerm="";
$jsPerm="
var access = new Array('".implode("', '",$access)."');
var items = new Array('".implode("', '",$items)."');
var permval = new Array();
permval[0] = '".$pvs['-1']."';
permval[1] = ' - ';
permval[2] = '".$pvs['1']."';
var cantUsers = ".count($user_context).";
";
//foreach($user_context as $task_user_id=>$context_name){
foreach($user_names as $task_user_id=>$context_name){
	foreach ($access as $access_id => $access_name){
		foreach($items as $item_id => $item_name){ 
					$value = $tbl_perm[$access_id][$item_id][$task_user_id] ? $tbl_perm[$access_id][$item_id][$task_user_id] : "0";

					$jsPerm .= "var jsPerm_{$access_id}_{$item_id}_".str_replace("-", "_", $task_user_id)."=".strval(intval($value) + 1).";\n";

					$lnkedit="";
					$lnkdel="";
					if ($value!="0" && ($canEdit) ){
						$lnkedit = "<a href='javascript: void(0)' onClick=\"editPerm({$access_id},'{$task_user_id}',{$item_id},{$value},'{$context_name}');\" title='"
						.$AppUI->_('Edit')."'>"					
						.'<img src=\'./images/icons/edit_small.jpg\' width=20 height=20 alt=\'\' border=0 >'
						."</a>";
	
						$lnkdel .= "<a href='javascript: void(0)' onClick='delIt({$access_id},{$task_user_id},{$item_id});' title='"
						.$AppUI->_('Delete')."'>"
						. '<img src=\'./images/icons/trash_small.jpg\'  width=20 height=20 alt=\'\' border=0 >'
						. "</a>";						
					}
					$value = $tbl_perm[$access_id][$item_id][$task_user_id] ? 
							$pvs[$tbl_perm[$access_id][$item_id][$task_user_id]] : " - ";					
		}
	}
}
?>

<script language="JavaScript1.2">
<?=$jsPerm.$jsHTMPerm;?>

function selectTable(){
	var a,i,re, value, cname, cel;
	var cbotuid = document.frmCons.tuid;
	var usr0 = cbotuid[cbotuid.selectedIndex].value;
	cname = cbotuid[cbotuid.selectedIndex].text;
	re = /-/i;
	var usr = usr0.replace(re, "_");

	for(var a=1, n=access.length; a <= n; a++){	
		for(var i=1, m=items.length; i <= m; i++){
			value = eval("jsPerm_"+a+"_"+i+"_"+usr).toString();
			/*desc = getEditLink(a,i,usr0, parseFloat(value) - 1, '');
			desc += getDelLink(a,i,usr0);
			desc += permval[value];*/
			
			cel = '<table border=0 cellpadding="0" cellspacing="1" width="100%"><tr><td>';
			if (parseFloat(value) - 1!=0) 
				cel += getEditLink(a,i,usr0, parseFloat(value) - 1, cname) ;
			cel += '</td><td>';
			if (parseFloat(value) - 1!=0) 
				cel += getDelLink(a,i,usr0) ;
			cel += '</td><td nowrap align=center width="100%">'+ permval[value] ;
			cel += '</td></tr></table>';

			if (document.all) {
				var cell = eval("document.all.cel_"+a+"_"+i+";");
				cell.innerHTML = cel;


			}	else if (document.getElementById ){
				var cell = eval("document.getElementById('cel_"+a+"_"+i+"');");
				cell.replaceChild(document.createTextNode(cel), cell.firstChild);

			}
		}	
	}
	clearIt();
}

<?php 
if ($canEdit){?>
function getEditLink(ac,it,us,val,cname){
	var text;
	if (val==0) return "";
	text = "<a href='javascript: void(0)' onClick=\"editPerm(";
	text += ac.toString() + ",";
	text += us.toString() + ",";
	text += it.toString() + ",";
	text += val.toString() + ",";
	text += "'" + cname.toString() + "'";
	text += ");\" title='<?=$AppUI->_('Edit');?>'>";
	text += '<img src=\'./images/icons/edit_small.jpg\' width=20 height=20 alt=\'<?=$AppUI->_('Edit');?>\' border=0 ></a>';
	return text;
}

function getDelLink0(ac,it,us){
	var text;
	text = "<a href='javascript: void(0)' onClick=\"delIt2(";
	text += ac.toString() + ",";
	text += us.toString() + ",";
	text += it.toString() ;
	text += ");\" title='<?=$AppUI->_('Delete');?>'>";
	text += '<img src=\'./images/icons/trash_small.jpg\' width=20 height=20 alt=\'<?=$AppUI->_('Delete');?>\' border=0 ></a>';
	return text;
}

function getDelLink(ac,it,us){
	var text;
	text = "<input type='image' onClick=\"delIt2(";
	text += ac.toString() + ",";
	text += us.toString() + ",";
	text += it.toString() ;
	text += ");\" alt='<?=$AppUI->_('Delete');?>'";
	text += ' src=\'./images/icons/trash_small.jpg\' width=20 height=20  border=0 />';
	return text;
}
<?php
}
?>

		

function editPerm( access, user, it, vl, nm ) {
/*
	access = access_id
	user =user_id
	it =permission_item
	vl =permission_value
	nm = text representation of permission_value
*/
//alert( 'id='+id+'\ngon='+gon+'\nit='+it+'\nvalue='+vl+'\nnm='+nm);
	var f = document.frmPerms;

	f.sqlaction2.value="<?php echo $AppUI->_('Edit');?>";
	
	for(var i=0, n=f.access_id.options.length; i < n; i++) {
		if (f.access_id.options[i].value == access) {
			f.access_id.selectedIndex = i;
			break;
		}
	}
	for(var i=0, n=f.item_id.options.length; i < n; i++) {
		if (f.item_id.options[i].value == it) {
			f.item_id.selectedIndex = i;
			break;
		}
	}
	for(var i=0, n=f.permission_value.options.length; i < n; i++) {
		if (f.permission_value.options[i].value ==  vl) {
			f.permission_value.selectedIndex = i;
			break;
		}
	}
	if (user < 0){
		f.user_type[0].checked = true
		f.permission_user_name.value = "<?=$AppUI->_("All")?>";
		f.permission_user.value = user;
		for(var i=0, n=f.user_id.options.length; i < n; i++) {
			if (f.user_id.options[i].value == user) {
				f.user_id.selectedIndex = i;
				break;
			}
		}
	}else{
		f.user_type[1].checked = true
		f.permission_user.value = user;
		f.permission_user_name.value = nm;
	}	

	f.access_id.disabled=true;
	f.item_id.disabled=true;
	f.user_type.disabled=true;
	f.user_id.disabled=true;	
	f.btnUsrSelect.disabled=true;
	return true;
}

function switchUserType(){
	var f = document.frmPerms;
	var utype = getChecked(document.forms.frmPerms.user_type);
	if ((utype == "1")) {
		f.user_id.disabled=false;
		f.btnUsrSelect.disabled=true;
	}else{
		f.user_id.disabled=true;
		f.btnUsrSelect.disabled=false;
	}
}

function popPermItem() {
	var f = document.frmPerms;
	var utype = getChecked(document.forms.frmPerms.user_type);
	if ((utype == "2")) {
		window.open('./index.php?m=public&a=selector&dialog=1&callback=setPermItem&table=users', 'selector', 'left=50,top=50,height=250,width=400,resizable')
	}
}

function setPermItem( key, val ) {
	var f = document.frmPerms;
	if (val != '') {
		f.permission_user.value = key;
		f.permission_user_name.value = val;
	} else {
		f.permission_user.value = '-1';
		f.permission_user_name.value = '<?=$AppUI->_("All")?>';
	}
}
function clearIt(){
	var f = document.frmPerms;
	f.access_id.disabled=false;
	f.item_id.disabled=false;
	f.user_type.disabled=false;
	f.user_id.disabled=false;	
	f.btnUsrSelect.disabled=false;	
	f.sqlaction2.value = "<?php echo $AppUI->_('Add');?>";
	f.access_id.selectedIndex = 0;
	f.item_id.selectedIndex = 0;
}

function delIt(access,user,it) {
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var f = document.frmPerms;
		editPerm(access,user,it,"0")
		f.del.value = "1";
		postForm();
		document.forms.frmPerms.submit();
	}
}

function delIt2(access,user,it) {
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var f = document.frmDel;
		if (parseFloat(user) < 0)
			f.user_type.value = "1";
		else
			f.user_type.value = "2";
		f.user_id.value=user;
		f.permission_user.value=user;
		f.access_id.value=access;
		f.item_id.value=it;
		f.del.value = "1";
		f.submit();
	}
}

function getChecked(radio) {   
	if (radio){ 
		for (var i = 0; i < radio.length; i++) {
			if (radio[i].checked){
				return radio[i].value
			}
		}   
	}
}

function postForm(){
	var f = document.frmPerms;
	f.access_id.disabled=false;
	f.item_id.disabled=false;
	f.user_type.disabled=false;
	f.user_id.disabled=false;	
	f.btnUsrSelect.disabled=false;
	return true;
}
</script>








	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tr>
		<th nowrap align=center><form name=frmCons action="" method="post">
			<?=$AppUI->_("User").":".arraySelect($user_names, 'tuid', 'size="1" class="text" onchange="javascript: selectTable();"', '',true);?></form></th>
	</tr>
	<tr>
	<td>
		<form name="frmDel" method="post" action="">
		<input type="hidden" name="user_type" class="" value="" />
		<input type="hidden" name="user_id" class="" value="" />
		<input type="hidden" name="permission_user" value="" />
		<input type="hidden" name="access_id" class="" value="" />
		<input type="hidden" name="item_id" class="" value="" />
		<input type="hidden" name="permission_value" class="" value="0" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="dosql" value="do_task_perms_aed" />
		<input type="hidden" name="task_project" value="<?=$project_id;?>" />

		<table id=tblPerm  width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
		<tr>
			<th>&nbsp;</th>
			<?php 

				foreach($items as $item_id => $item_name){ ?>
				<th nowrap width="80"><?=$AppUI->_($item_name);?></th>
			<?php }?>
		</tr>
		</tr>
		<?php
			foreach ($access as $access_id => $access_name){
			?>
			<tr>
				<td nowrap ><?=$AppUI->_($access_name);?>
				</td>
				<?php 
					foreach($items as $item_id => $item_name){ 
						$value = $tbl_perm[$access_id][$item_id][$tuid] ? $tbl_perm[$access_id][$item_id][$task_user_id] : "0";
						$lnkedit="";
						$lnkdel="";
						if ($value!="0" && ($canEdit) ){
							$lnkedit = "<a href='javascript: //' onClick=\"editPerm({$access_id},'{$task_user_id}',{$item_id},{$value},'{$context_name}');\" title=\""
							.$AppUI->_('Edit')."\">"					
							.'<img src="./images/icons/edit_small.jpg" width="20" height="20" alt="" border="0" >'
							."</a>";
		
							$lnkdel .= "<a href=# onClick=\"delIt({$access_id},{$task_user_id},{$item_id});\" title=\""
							.$AppUI->_('Delete')."\">"
							. '<img src="./images/icons/trash_small.jpg" width="20" height="20" alt="" border="0">'
							. "</a>";						

						}
						$value = $tbl_perm[$access_id][$item_id][$task_user_id] ? 
								$pvs[$tbl_perm[$access_id][$item_id][$task_user_id]] : " - ";
						
						
						
					?>
					<td id=cel_<?=$access_id;?>_<?=$item_id;?> nowrap align=center>
<?php /*
					<table border=0 cellpadding="0" cellspacing="1" width="100%">
					<tr><td><?=$lnkedit;></td><td><?=$lnkdel;></td><td nowrap align=center width="100%"><?=$AppUI->_($value);></td></tr>
					</table> */?>

					</td>
				<?php }?>
			</tr>
			<?php }
			?>
		</table>
		</form>
	</td>
	</tr>
	</table>
</td><td width="300" valign="top">

<script language="JavaScript" type="text/JavaScript">
<!--
document.onload=selectTable();

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>


<form name="frmPerms" method="post" action="" onSubmit="javascript: return postForm();">
<input type="hidden" name="permission_user" value="-1" />
<input type="hidden" name="del" value="0" />
<input type="hidden" name="dosql" value="do_task_perms_aed" />
<input type="hidden" name="task_project" value="<?=$project_id;?>" />
	<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<tr>
		<td nowrap align="right">
		<input type="radio" name="user_type" class="" value="1" checked onclick="switchUserType();">
		<?=$AppUI->_("User Context");?>:</td>
		<td nowrap><?php echo arraySelect($user_context, 'user_id', 'size="1" class="text"', '',true);?>
		</td>
	</tr>
	<tr>
		<td nowrap align="right">
		<input type="radio" name="user_type" class="" value="2" onclick="switchUserType();">
		<?=$AppUI->_("User Name");?>:</td>
		<td nowrap>
		<input type="text" name="permission_user_name" class="text" size="30" value="<?=$AppUI->_("All");?>" disabled>
		<input type="button" name="btnUsrSelect" class="text" value="..." onclick="popPermItem();" disabled>		
		</td>
	</tr>
	<tr>
		<td nowrap align="right"><?=$AppUI->_("Task access");?>:</td>
		<td nowrap><?php echo arraySelect($access, 'access_id', 'size="1" class="text"', '',true);?>
			<!--<input type="text" name="access_name" class="text" size="30" value="" disabled>
			<input type="hidden" name="access_id" class="text" value="">-->
		</td>
	</tr>
	<tr>
		<td nowrap align="right"><?=$AppUI->_("Item Permission");?>:</td>
		<td nowrap><?php echo arraySelect($items, 'item_id', 'size="1" class="text"', '',true);?>
		</td>
	</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Level');?>:</td>
	<td><?php echo arraySelect($pvs, 'permission_value', 'size="1" class="text"', 0);?></td>
</tr>	
<tr>
	<td>
		<input type="reset" value="<?php echo $AppUI->_('Clear');?>" class="button" name="sqlaction" onClick="clearIt();">
	</td>
	<td align="right">
		<input type="submit" value="<?php echo $AppUI->_('Add');?>" class="button" name="sqlaction2">
	</td>
</tr>
	
	</table>
</form>
</td>
</tr></table>

</td></tr></table>