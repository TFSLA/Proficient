<?php

GLOBAL  $canEdit, $tpl_stub, $tpl_where, $tpl_orderby;

$sql = "SELECT DISTINCT(securitytemplate_id), securitytemplate_name, template_permission_template FROM securitytemplates
LEFT JOIN securitytemplate_permissions ON securitytemplate_id = template_permission_template";

//$sql .= "\nORDER by $tpl_orderby";

$templates = db_loadList( $sql );

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="">
<col><col width="400px">
<tr>
	<td valign="top">
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th width="90" align="right">
		<?php echo $AppUI->_('sort by');?>:
	</th>
	<th class="tableHeaderText">
		<a href="?m=admin&a=index&tpl_orderby=securitytemplates_name" class="">&nbsp;<?php echo $AppUI->_('Template Name');?></a>
	</th>
<?php if ($canEdit) { ?>	
	<th width="50px">
		<?php echo $AppUI->_('Select');?>
	</th>	
<?php } ?>	
</tr>
<?php 
foreach ($templates as $row) {
?>
<tr>
	<td align="right" nowrap="nowrap" width=70 >
<?php if ($canEdit) { ?>
		<table align=center width="100%" cellspacing="1" cellpadding="1" border="0">
		<tr>
			<td>
				<a href="./index.php?m=admin&a=addedittemplate&securitytemplate_id=<?php echo $row["securitytemplate_id"];?>" title="<?php echo $AppUI->_('edit');?>">
					<?php echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' ); ?>
				</a> 
			</td>
			<td>
				<a href="?m=admin&a=viewtemplate&securitytemplate_id=<?php echo $row["securitytemplate_id"];?>&tab=1" title="">
					<img src="images/obj/edit_permissions_small.gif" width="20" height="20" border="0" alt="<?php echo $AppUI->_('edit permissions');?>">
				</a> 
			</td>
			<td>
				<a href="javascript:delTemplate(<?php echo $row["securitytemplate_id"];?>, '<?php echo $row["securitytemplate_name"];?>')" title="<?php echo $AppUI->_('delete');?>">
					<?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?>
				</a>
			</td>
		</tr>
		</table>
<?php } ?>
	</td>
	<td>
		<a href="./index.php?m=admin&a=viewtemplate&securitytemplate_id=<?php echo $row["securitytemplate_id"];?>"><?php echo $row["securitytemplate_name"];?></a>
	</td>
<?php if ($canEdit) { ?>	
	<td align="center">
		<a href="javascript: //" onclick="selectTemplate('<?php echo $row["securitytemplate_id"];?>');" title="<?php echo $AppUI->_("select for apply")?>"><?php echo htmlentities(">>");?></a>	
	</td>
<?php } ?>	
</tr>
<tr>
    <td colspan="3" bgcolor="#E9E9E9"></td>
</tr>
<?php }?>

</table>

	</td>	

<?php 

if ($canEdit){
	$users = CUser::getAssignableUsersPerm();
	$users_perm = array();
	$selected_users = array();
	$templates = CTemplate::getHash();
	$templates = arrayMerge(array("0"=>""), $templates);

?>
	<form name="frmapplytpl" method="post" action="">
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="dosql" value="do_perms_batch_aed" />
		<input type="hidden" name="applytemplate" value="1" />
		<input type="hidden" name="redirect" value="" />		
		<input type="hidden" name="userlist" value="" />
	<td valign="top">
	<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
	
		<tr class="tableHeaderGral">
			<td rowspan="1">&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td nowrap="nowrap" colspan="2"><?php echo $AppUI->_('Apply template');?></td>
		</tr>	
		<tr>
			<td rowspan="10">&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td><b><?php echo $AppUI->_( 'Template' );?>:</b></td>
			<td>
			<?php echo arraySelect( $templates, 'template_permission_template', 'size="1" class="text"', null ); ?></td>
		</tr>
		<tr>
		    <td colspan="2">&nbsp;</td>
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
	</table>
	</td>	
	</form>	
<?php }else{ ?>
	<td>&nbsp;</td>
<?php } ?>
</tr>
</table>

<?php if ($canEdit){ ?>

	
	
<script language="Javascript"><!-- 

var user_perm = new Array();

<?php 
for($i=0;$i<count($users_perm);$i++){
	echo "user_perm[".$users_perm[$i]."] = 1;\n";
}?>


function selectTemplate(id){
	var form = document.frmapplytpl;
	for (var i=0; i < form.template_permission_template.length; i++) {
		if (form.template_permission_template.options[i].value == id){
			form.template_permission_template.selectedIndex = i;
			return;
		}
	}	

}
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
	
	if(form.template_permission_template.selectedIndex==0){
          alert("<?php echo $AppUI->_('Please select a valid template');?>");
          form.template_permission_template.focus();	
          return;	
	} else if( td == -1 ){
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
// -->
</script>	
<?php } ?>