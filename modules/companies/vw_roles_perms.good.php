<?php 
require_once( $AppUI->getModuleClass( 'tasks' ) );
GLOBAL $AppUI, $company_id, $canEdit, $user_context, $role;
//import_request_variables("P","p_");
$role = isset($_POST['role']) ? $_POST['role'] : 0;


$pvs = array(
'-1' => $AppUI->_('RW'),
'0' => $AppUI->_('D'),
'1' => $AppUI->_('R'),
'na'=> " - " 
);



$obj = new CCompany();
if (!$obj->load($company_id, false)){
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}

$perms=$obj->getPermissions();
//var_dump($perms);
$obj = new CTaskPermission();
$access=$obj->getTaskAccess();
$items=$obj->getItemsPermission();

$obj = new CRoles();
if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}
$canEdit=$obj -> canEdit;

$roleslist=$obj->getRoles($company_id);
foreach ($roleslist as $rl)
	$roles[$rl['role_id']]=$rl['role_name'];
	

$tbl_perm=array();
/*
for ($i=0;$i<count($perms);$i++){
	$tbl_perm	[$perms[$i]["access_id"]]
				[$perms[$i]["item_id"]]
				[$perms[$i]["role_id"]]	=	$perms[$i]["permission_value"];
}
*/
foreach ($perms as $rl=>$a){
	foreach ($a[-1] as $acc=>$b){
		foreach ($b as $it=>$perm_val){
			$tbl_perm[$acc][$it][$rl]=$perm_val;
		}
	}
}

?>



<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="40%" valign="top">

<?php
$jsHTMPerm="";
$jsPerm="
var access = new Array('".implode("', '",$access)."');
var items = new Array('".implode("', '",$items)."');
var permval = new Array();
permval[0] = '".$pvs['-1']."';
permval[1] = '".$pvs['0']."';
permval[2] = '".$pvs['1']."';
permval['na'] = ' - ';
var cantUsers = ".count($roles).";
";
//foreach($user_context as $role_id=>$context_name){
foreach($roles as $role_id=>$role_name){

	foreach ($access as $access_id => $access_name){
		foreach($items as $item_id => $item_name){ 
			// Construyo el array en JS para utilizar al seleccionar el combo de rolesn y llenar la tabla de permisos
			$value = $tbl_perm[$access_id][$item_id][$role_id]!=2 ? 
						strval(intval($tbl_perm[$access_id][$item_id][$role_id]) + 1) : 
						"'na'";
			$jsPerm .= "var jsPerm_{$access_id}_{$item_id}_{$role_id}={$value};\n";
			unset($value);				
		}
	}
}
?>

<script language="JavaScript1.2">
<?=$jsPerm.$jsHTMPerm;?>

function selectTable(){
	var a,i,value, cname, cel;
	
	var cborole = document.frmCons.role;
	var role = cborole[cborole.selectedIndex].value;
	cname = cborole[cborole.selectedIndex].text;

	//document.all.debug.innerHTML = role+" - "+access.length.toString()+" - " +items.length.toString();
	//document.all.debug.innerHTML += "<hr>";
	for(var a=1, n=access.length; a <= n; a++){	
		for(var i=1, m=items.length; i <= m; i++){
			value = eval("jsPerm_"+a+"_"+i+"_"+role).toString();
			//document.all.debug.innerHTML += "jsPerm_"+a+"_"+i+"_"+role+"...a["+a+"] - i["+i+"] = "+value+"<br>";
			//document.all.debug.innerHTML += (value != 'na');
			cel = '<table border=0 cellpadding="0" cellspacing="1" width="100%"><tr><td>';
			if ( value != 'na') {
				cel += getEditLink(a,i,role, parseFloat(value) - 1, cname) ;}
			else{
				cel += getAddLink(a,i,role, value , cname) ;}
			cel += '</td><td>';

			if ( value != 'na') {
				cel += getDelLink(a,i,role) ;}
			else{
				cel += "&nbsp;" ;	}
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
function getAddLink(ac,it,rl,val,cname){
	var text;
	if (val!='na') return "";
	text = "<a href='javascript: void(0)' onClick=\"editPerm(";
	text += ac.toString() + ",";
	text += rl.toString() + ",";
	text += it.toString() + ",";
	text += "'" + val.toString() + "',";
	text += "'" + cname.toString() + "'";
	text += ");\" title='<?=$AppUI->_('Add');?>'>";
	text += '<img src=\'./images/icons/expand.gif\' width=16 height=16 alt=\'<?=$AppUI->_('Add');?>\' border=0 ></a>';

	return text;
}

function getEditLink(ac,it,rl,val,cname){
	var text;
	if (val=='na') return "";
	text = "<a href='javascript: void(0)' onClick=\"editPerm(";
	text += ac.toString() + ",";
	text += rl.toString() + ",";
	text += it.toString() + ",";
	text += val.toString() + ",";
	text += "'" + cname.toString() + "'";
	text += ");\" title='<?=$AppUI->_('Edit');?>'>";
	text += '<img src=\'./images/icons/edit_small.jpg\' width=20 height=20 alt=\'<?=$AppUI->_('Edit');?>\' border=0 ></a>';
	return text;
}

function getDelLink0(ac,it,us){
	var text;
	if (val=='na') return "";
	text = "<a href='javascript: void(0)' onClick=\"delIt2(";
	text += ac.toString() + ",";
	text += rl.toString() + ",";
	text += it.toString() ;
	text += ");\" title='<?=$AppUI->_('Delete');?>'>";
	text += '<img src=\'./images/icons/trash_small.jpg\' width=20 height=20 alt=\'<?=$AppUI->_('Delete');?>\' border=0 ></a>';
	return text;
}

function getDelLink(ac,it,rl){
	var text;
	text = "<input type='image' onClick=\"delIt2(";
	text += ac.toString() + ",";
	text += rl.toString() + ",";
	text += it.toString() ;
	text += ");\" alt='<?=$AppUI->_('Delete');?>'";
	text += ' src=\'./images/icons/trash_small.jpg\' width=20 height=20  border=0 />';
	return text;
}
<?php
}
?>

		

function editPerm( access, role, it, vl, nm ) {
/*
	access = access_id
	role = role
	it =permission_item
	vl =permission_value
	nm = text representation of permission_value
*/
//alert( 'id='+id+'\ngon='+gon+'\nit='+it+'\nvalue='+vl+'\nnm='+nm);
	var f = document.frmPerms;

	
	f.access_id.selectedIndex = getItemIndex(f.access_id, access);
	f.item_id.selectedIndex = getItemIndex(f.item_id, it);
	f.role.selectedIndex = getItemIndex(f.role, role);
	if (vl=='na'){
		f.permission_value.selectedIndex = getItemIndex(f.permission_value, vl);
		f.sqlaction2.value="<?php echo $AppUI->_('Add');?>";
	}else{
		f.permission_value.selectedIndex = getItemIndex(f.permission_value, vl);
		f.sqlaction2.value="<?php echo $AppUI->_('Edit');?>";
	}
	f.sqlaction2.disabled=false;
	f.access_id.disabled=true;
	f.item_id.disabled=true;
	f.user_type.disabled=true;
	f.role.disabled=true;		
	return true;
}

function popPermItem() {
	var f = document.frmPerms;
	window.open('./index.php?m=public&a=selector&dialog=1&callback=setPermItem&table=users', 'selector', 'left=50,top=50,height=250,width=400,resizable')
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
	f.access_id.disabled=true;
	f.item_id.disabled=true;
	f.role.disabled=true;	
	f.sqlaction2.value = "<?php echo $AppUI->_('Add');?>";
	f.sqlaction2.disabled=true;	
	f.access_id.selectedIndex = 0;
	f.item_id.selectedIndex = 0;
	f.role.selectedIndex=0;
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
		f.user_type.value = "2";
		f.role.value=user;
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

function getItemIndex(combo, value){
	var n;
	for(var i=0, n=combo.options.length; i < n; i++) {
		if (combo.options[i].value == value) {
			return i;
		}
	}
	return false;
}

function postForm(){
	var f = document.frmPerms;
	f.access_id.disabled=false;
	f.item_id.disabled=false;
	f.role.disabled=false;	
	return true;
}
</script>








	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tr>
		<form name=frmCons action="" method="post">
		<th nowrap align=center>
			<?=$AppUI->_("Role").":".arraySelect($roles, 'role', 'size="1" class="text" onchange="javascript: selectTable();"', $role,true);?>
		</th>
		</form>
	</tr>
	<tr>
	<td>
		<table id=tblPerm  width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
		<form name="frmDel" method="post" action="">
		<input type="hidden" name="role" class="" value="" />
		<input type="hidden" name="access_id" class="" value="" />
		<input type="hidden" name="item_id" class="" value="" />
		<input type="hidden" name="permission_value" class="" value="0" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="dosql" value="do_role_perms_aed" />
		<input type="hidden" name="company" value="<?=$company_id;?>" />		
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
				?>
					<td id=cel_<?=$access_id;?>_<?=$item_id;?> nowrap align=center>
				<?php
						$value = $tbl_perm[$access_id][$item_id][$role_id] ? $tbl_perm[$access_id][$item_id][$role_id] : "0";
						$lnkedit="";
						$lnkdel="";
						if ($value!="0" && ($canEdit) ){
							$lnkedit = "<a href='javascript: //' onClick=\"editPerm({$access_id},'{$role_id}',{$item_id},{$value},'{$context_name}');\" title=\""
							.$AppUI->_('Edit')."\">"					
							.'<img src="./images/icons/edit_small.jpg" width="20" height="20" alt="" border="0" >'
							."</a>";
		
							$lnkdel .= "<a href=# onClick=\"delIt({$access_id},{$role_id},{$item_id});\" title=\""
							.$AppUI->_('Delete')."\">"
							. '<img src="./images/icons/trash_small.jpg" width="20" height="20" alt="" border="0">'
							. "</a>";						

						}
						$value = $tbl_perm[$access_id][$item_id][$role_id] ? 
								$pvs[$tbl_perm[$access_id][$item_id][$role_id]] : " - ";
						
						
						
					?>

					</td>
				<?php }?>
			</tr>
			<?php }
			?>
		</form>
		</table>
	</td>
	</tr>
	</table>
</td><td width="300" valign="top">

<script language="JavaScript" type="text/JavaScript">
<!--

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>


<form name="frmPerms" method="post" action="" onSubmit="javascript: return postForm();">

<input type="hidden" name="del" value="0" />
<input type="hidden" name="dosql" value="do_role_perms_aed" />
<input type="hidden" name="company" value="<?=$company_id;?>" />
	<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<tr>
		<td nowrap align="right"><?=$AppUI->_("Role");?>:</td>
		<td nowrap><?php echo arraySelect($roles, 'role', 'size="1" class="text" disabled', $role,true);?>
			<!--<input type="text" name="access_name" class="text" size="30" value="" disabled>
			<input type="hidden" name="access_id" class="text" value="">-->
		</td>
	</tr>
<!--	<tr>
		<td nowrap align="right">
		
		<?=$AppUI->_("User Name");?>:</td>
		<td nowrap>
		<input type="text" name="permission_user_name" class="text" size="30" value="<?=$AppUI->_("All");?>" disabled>
		<input type="button" name="btnUsrSelect" class="text" value="..." onclick="popPermItem();">		
		</td>
	</tr>
-->	
	
	<tr>
		<td nowrap align="right"><?=$AppUI->_("Task access");?>:</td>
		<td nowrap><?php echo arraySelect($access, 'access_id', 'size="1" class="text" disabled', '',true);?>
			<!--<input type="text" name="access_name" class="text" size="30" value="" disabled>
			<input type="hidden" name="access_id" class="text" value="">-->
		</td>
	</tr>
	<tr>
		<td nowrap align="right"><?=$AppUI->_("Item Permission");?>:</td>
		<td nowrap><?php echo arraySelect($items, 'item_id', 'size="1" class="text" disabled', '',true);?>
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
		<input type="submit" value="<?php echo $AppUI->_('Add');?>" class="button" name="sqlaction2" disabled>
	</td>
</tr>
	
	</table>
</form>
</td>
</tr></table>
<div id=debug></div>
<script language="JavaScript1.2" type="text/javascript">
<!-- 
document.onload=selectTable();
//-->
</script>