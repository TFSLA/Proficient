<?php
require_once( $AppUI->getModuleClass( 'tasks' ) );
GLOBAL $AppUI, $company_id, $canEdit, $user_context, $role,$form,$tab,$role_id, $amode,$pvs,$pvsProj,$pvsTask;
//import_request_variables("P","p_");

$form = $form ? $form : 1;
$amode = $amode ? $amode : 1;

$flgNoTab = ($form==2);
if ($flgNoTab){
	$role = $role_id;
	$orientation="horizontal";
}else{
	$role = isset($_POST['role']) ? $_POST['role'] : 2;
}


$obj = new CCompany();
if (!$obj->load($company_id, false)){
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}

$perms=$obj->getPermissions($role);
//var_dump($perms);
$obj = new CTaskPermission();
$access=$obj->getTaskAccess();
$items=$obj->getItemsPermission();
$itemsTasks=$items;

unset($itemsTasks[1]);
unset($itemsTasks[6]);
unset($itemsTasks[7]);


$obj = new CRoles();
if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}
$canEdit=$obj -> canEdit;


$roleslist=$obj->getRoles($company_id);
	foreach ($roleslist as $rl){
		if ($AppUI->user_type==1 || $rl['role_type']==1)
			$roles[$rl['role_id']]=$rl['role_name'];
	}


$tbl_perm=array();


foreach ($perms as $rl=>$a){
	foreach ($a[-1] as $acc=>$b){
		foreach ($b as $it=>$perm_val){
			//echo "<br>tbl_perm[$acc][$it][$rl]=".strval($perm_val)."";
			$tbl_perm[$acc][$it][$rl]=$perm_val;
		}
	}
}

if (!($sm[$role]))
	$amode=1;
?>
<script language="javascript">
<!--

function unifier(acc, it){
	var accesses = new Array(2, 3);
	var f = document.frmPerm;
	for (var j=0; j< f.elements.length; j++){
		if(f.elements[j].name =="permission_value[" + acc + "][" + it + "]"){
			var cboSel = f.elements[j];
			j = f.elements.length
		} 
	}
	if (f.chkUnifica.checked){
		for (var i=0; i < 2; i++){
			for (var j=0; j< f.elements.length; j++){
				if(f.elements[j].name =="permission_value[" + accesses[i] + "][" + it + "]"){
					f.elements[j].selectedIndex = cboSel.selectedIndex;	
				} 			
			}
		}
	}
	

}
//-->
</script>


		<form name="frmPerm" method="post">
		<input type="hidden" name="role" class="" value="<?=$role;?>" />
		<input type="hidden" name="dosql" value="do_role_perms_aed" />
		<input type="hidden" name="amode" value="<?=$amode;?>" />
		<input type="hidden" name="company" value="<?=$company_id;?>" />
<table width="700" border="0" cellpadding="2" cellspacing="0">
<tr><td width="100%" valign="top"> 
<?
if ($flgNoTab){
	echo "<h1>".$AppUI->_("Roles Permissions")." : ".$AppUI->_($roles[$role])."</h1>";
	echo "<a href=\"./index.php?m=companies&a=view&company_id=$company_id&tab=$tab\">".$AppUI->_('list roles')."</a>";
}
?>

	<table class="" cellspacing="0" cellpadding="2" width="100%" border="0">
	  <tr class="tableHeaderGral">
	      <th width="100%" colspan="2" align="middle" nowrap="nowrap"><?=$AppUI->_("Project Information");?></th>
	  </tr>
	  <tr class="tableForm_bg">
	  		<td width="50%"><?=$AppUI->_($items[6]).":".arraySelect($pvsProj, 'permission_value[-1][6]', 'size="1" class="text"', isset($tbl_perm[-1][6][$role]) ?  $tbl_perm[-1][6][$role] : 9); ?>
	  		
	  		</td>
	  		<td width="50%"><?=$AppUI->_($items[7]).":".arraySelect($pvsProj, 'permission_value[-1][7]', 'size="1" class="text"', isset($tbl_perm[-1][7][$role]) ?  $tbl_perm[-1][7][$role] : 9); ?>
	  		</td>
	  </tr>	
	</table>
	<br />
	
	<table class="" cellspacing="0" cellpadding="2" width="100%" border="0">
	  <tr class="tableHeaderGral">
	      <th width="100%" colspan="2" align="middle" nowrap="nowrap"><?=$AppUI->_("Tasks Actions");?></th>
	  </tr>
	  <tr class="tableForm_bg">
	  		<td><?=arraySelect($pvsTask, 'permission_value[-1][1]', 'size="1" class="text"', isset($tbl_perm[-1][1][$role]) ?  $tbl_perm[-1][1][$role] : 9); ?>
	  		
	  		</td>
	  		<td width="100%"><?=$AppUI->_("msgOwner"); ?>
	  		</td>
	  </tr>	
	</table>
	<br />  

	<table class="" cellspacing="0" cellpadding="2" width="100%" border="0">
	<tr>
		<th class="tableHeaderGral" nowrap align=center>
			<?php /*
			$s = $AppUI->_("Role").":";
			if ($flgNoTab){
				$s .= "&nbsp;".$AppUI->_($roles[$role])."<input type=hidden name=role value='$role'>";
			}else{
				$s .= arraySelect($roles, 'role', 'size="1" class="text" onchange="javascript: document.frmCons.submit();"', $role,true);
			}
				*/
				$s = $AppUI->_("Permissions on not owned tasks");
			//	$AppUI->_("Role").":".arraySelect($roles, 'role', 'size="1" class="text" onchange="javascript: document.frmCons.submit();"', $role,true);
			echo $s;
			?>
		</th>
	</tr>
	<tr class="tableForm_bg">
	<td>
		<input name="chkUnifica" type="checkbox" value="checkbox" checked><?=$AppUI->_("msgUnify"); ?>
				<table id=tblPerm  width="100%" border="0" cellpadding="2" cellspacing="0" class="">
			
				<?php
				if (!(!($amode) && $sm[$role])){ 
					?>
				<tr class="tableHeaderGral">
					<th>&nbsp;</th>
					<?php 
						foreach($itemsTasks as $item_id => $item_name){ ?>
						<th nowrap width="80" ><?=$AppUI->_($item_name);?></th>
					<?php }?>
				</tr>
				</tr>
				<?php
				
					
						foreach ($access as $access_id => $access_name){
						?>
						<tr class="tableForm_bg">
							<td nowrap ><?=$AppUI->_($access_name);?>
							</td>
							<?php 
								foreach($itemsTasks as $item_id => $item_name){ 
							?>
								<td id=cel_<?=$access_id;?>_<?=$item_id;?> nowrap align=center>
							<?php
								echo arraySelect($pvs, 'permission_value['.$access_id.']['.$item_id.']', 'size="1" class="text" onchange="javascript: unifier('.$access_id.','.$item_id.');"', $tbl_perm[$access_id][$item_id][$role]);
							?>
								</td>
							<?php }?>
						</tr>
						<?php }
					}else{ 
						$access_keys=array_keys($access);
						$access_id=$access_keys[1];				
						if ($orientation=="vertical"){
						?>
					
					
						<tr><th nowrap><?=$AppUI->_("Item");?></th><th nowrap><?=$AppUI->_("Level");?></th></tr>
						<?php
						foreach($itemsTasks as $item_id => $item_name){ ?>
						<tr><td nowrap><?=$AppUI->_($item_name);?></th>
							<td id=cel_-1_<?=$item_id;?> nowrap align=center>
							<?php
							echo arraySelect($pvs, 'permission_value['.$item_id.']', 'size="1" class="text"', $tbl_perm[$access_id][$item_id][$role]);
							?>
							</td>
						</tr>
						<?php }?>
									
					
					
					<?php
						}elseif($orientation=="horizontal"){
						?>
						<tr>
							<?php 
								foreach($itemsTasks as $item_id => $item_name){ ?>
								<th nowrap width="80"><?=$AppUI->_($item_name);?></th>
							<?php }?>
						</tr>			
						<tr>
						<?php
						foreach($itemsTasks as $item_id => $item_name){ ?>
							<td id=cel_-1_<?=$item_id;?> nowrap align=center>
							<?php
							echo arraySelect($pvs, 'permission_value['.$item_id.']', 'size="1" class="text"', $tbl_perm[$access_id][$item_id][$role]);
							?>
							</td>
		
						<?php }?>
						</tr>			
					
					
					<?php
						}						
					}
					
						?>
		
						
				</table>
		</td>
	</tr>
	</table>
	
		<table width="100%" border="0" cellpadding="2" cellspacing="1">
		<tr>
			<td>
				<input type="reset" value="<?php echo $AppUI->_('Clear');?>" class="button" name="sqlaction">
			</td>
			<td align="right">
				<input type="submit" value="<?php echo $AppUI->_('Update');?>" class="button" name="sqlaction2">
			</td>
		</tr>
		</table>


	

	</td>
	</tr>
</table>
</form>
<div id=debug></div>
