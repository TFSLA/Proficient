<?php /* PROJECTS $Id: vw_perms.php,v 1.2 2009-07-30 15:50:43 nnimis Exp $ */
GLOBAL $AppUI, $project_id, $canEdit,$option_id, $pvs,$pvsProj,$pvsTask,$seluser;

if (! class_exists("CTask"))
	require_once( $AppUI->getModuleClass( 'tasks' ) );

extract($_GET);
extract($_POST); 

/*
$pvs = array(
'-1' => $AppUI->_('RW'),
'0' => $AppUI->_('D'),
'1' => $AppUI->_('R'),
'9' => ' - '
);*/

$amode = 1;
$orientation = "horizontal";


$option_id = !isset($option_id) ? "0" : $option_id;

$user_id = str_replace("u","",strstr($option_id,"u"));
$role_id = str_replace("r","",strstr($option_id,"r"));
/*
if ($user_id){
	$option_type = "u";
}else{
	$option_type = "r";
	$role_id = strstr($option_id,"r");
}
*/
$continue = true;
if ($seluser){
	$sql="
	select p.project_id, p.project_name 
	from projects p left join project_owners po on p.project_id = po.project_id
	where
	$AppUI->user_id in (po.project_owner , p.project_owner) or $AppUI->user_type=1
	order by project_name
	";
	$lstprojects = db_loadHashList($sql);
	if (count($lstprojects)==0){
		echo $AppUI->_("msgNoOwnedProjects");
		$continue = false;
	}else{
		if (!$project_id){
			$prjk = array_keys($lstprojects);
			$project_id = $prjk[0];
		}
		$user_id = $seluser;
		$option_id = "u".$seluser;
		$role_id = "";
	}
}

if ($continue){

$obj = new CProject();
if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
// check permissions for this record
$canEdit=$obj->canManageRoles();
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

//Mas bien todos los usuarios
//Pull all users
/*
$sql = "
SELECT CONCAT('u',user_id) user_id , CONCAT(user_last_name,', ', user_first_name) user_name
FROM users
ORDER BY user_name
";
$user_names = db_loadHashList( $sql );
*/
$user_names = CUser::getAssignableUsers("CONCAT('u',user_id) user_id , CONCAT(user_last_name,', ', user_first_name) user_name");


$options["0"]=$AppUI->_("Choose an item");
$options["-9"]="----- ".$AppUI->_("Roles")." -----";
$options["r2"]=$AppUI->_("Project users");

// Traigo los roles relacionados al proyecto
$sql_roles = "SELECT role_id, role_name FROM roles, projects WHERE role_company = project_company AND project_id ='$project_id'  ORDER BY role_name";
$roles_cia =  db_loadHashList($sql_roles);

if(count($roles_cia)>0)
{
     foreach($roles_cia as $r_id=>$r_desc)
     {
     	$id_r = "r$r_id";
     	$options[$id_r] = $r_desc;
     }
}

$options["-8"]="----- ".$AppUI->_("Users")." -----";

//echo "<pre>"; print_r($options); echo "</pre>";

$options = arrayMerge($options,$user_names);

if ( $option_id != "0" ) {
	$objPR = new CProjectRoles();
	$prjUsers = $objPR->getAssignedUsers(2 ,$project_id);
	unset($objPR);
	
	$objTask = new CTaskPermission();
	
	$access=$objTask->getTaskAccess();
	$items=$objTask->getItemsPermission();
	$itemsTasks=$items;

	unset($objTask);
	unset($itemsTasks[1]);
	unset($itemsTasks[6]);
	unset($itemsTasks[7]);
	
	unset ($usr);
	$tbl_perm=array();
	if ($user_id){
		$perms = $obj->getPermissions($user_id);
		//$user_names=$user_context;
		for ($i=0;$i<count($perms);$i++){
			$access_id=$perms[$i]["task_access_id"];
			$item_id=$perms[$i]["task_permission_on"];
			$task_user_id=$perms[$i]["task_user_id"];
			$tmpperm[$access_id][$item_id][$task_user_id]=$perms[$i]["task_permission_value"];	
		}

		foreach($items as $it => $it_name){
			$tbl_perm[-1][$it][$user_id]=isset($tmpperm[-1][$it][$user_id]) ? $tmpperm[-1][$it][$user_id] : 9 ;
			foreach ($access as $acc=>$acc_name){
				$tbl_perm[$acc][$it][$user_id]=isset($tmpperm[$acc][$it][$user_id]) ? $tmpperm[$acc][$it][$user_id] : 9 ;
			}	
		}
		
	}
	if ($role_id){
		
		/*$objCpy = new CCompany();
		if (!$objCpy->load($obj->project_company, false)){
			$AppUI->setMsg( 'Company' );
			$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
			$AppUI->redirect();
		
		}	
		$perms=$objCpy->getPermissions($role_id);	
		unset($objCpy);*/
		//echo "Traigo los permisos del rol seleccionado :$role_id<br>";
		
		$perms=$obj->getRolePermissions($role_id);
		
		//echo "<pre>"; print_r($perms);echo "</pre>";
		
		if(is_array($perms) && count($perms))
		foreach ($perms as $rl=>$a){
			if(is_array($a[$project_id]) && count($a[$project_id]))
			foreach ($a[$project_id] as $acc=>$b){
				if(is_array($b) && count($b))
				foreach ($b as $it=>$perm_val){
					//echo "<br>tbl_perm[$acc][$it][$rl]=".strval($perm_val)."";
					$tbl_perm[$acc][$it][$rl]=$perm_val;
				}
			}
		}
		
				
	}	
	//var_dump($tbl_perm);
	
	// Si no hay roles cargados para el proyecto, me fijo si hay roles cargados para la empresa del proyecto
	if (count($tbl_perm)==0)
	{
	     // Inicializo el vector
	     $tbl_perm[-1][1][$role_id] = '9';
	     $tbl_perm[-1][6][$role_id] = '9';
	     $tbl_perm[-1][7][$role_id] = '9';
	     
	     for($i=2; $i <=3; $i++)
	     {
	     	for ($j=2; $j<=5; $j ++)
	     	{
	     	     $tbl_perm[$i][$j][$role_id] = '9';
	     	}
	     }
	     
	     $query_roles = "SELECT * FROM role_permissions WHERE role_id = '".$role_id."' ";
	     $sql_rol = db_exec($query_roles);
	    
	     while($vec = mysql_fetch_array($sql_rol))
	     {   
	     	$tbl_perm[$vec['access_id']][$vec['item_id']][$vec['role_id']]=$vec['permission_value'];
	     }
	     
	}
	
	//echo "<pre>"; print_r($tbl_perm); echo "</pre>";
	
	$oid=$user_id?$user_id:$role_id;
	//echo "<pre> option_id: ".strval($oid)." ".$tbl_perm[-1][6][$oid]." ".$tbl_perm[-1][7][$oid]."</pre>";

?>
<SCRIPT language="javascript1.2">
<!--
<? 
 		echo "var projectUsers = new Array();\n";
		foreach (array_keys($prjUsers) as $campo=>$uid)
			echo "projectUsers[$uid] = 1;\n";

?>

function viewPerms(cbo){
	if (!parseFloat(cbo.value) && cbo.value!="0")
		cbo.form.submit();
}
function unifier(acc, it){
	var accesses = new Array(2, 3);
	var f = document.frmPerms;
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
function submitIt(){
	var f = document.frmPerms;
	var msg = '';
	var rta = true;
	
	if (projectUsers[f.user.value] != 1 ) {
		msg = f.user.options[f.user.selectedIndex].innerHTML;
		rta=confirm ("<?=$AppUI->_("Do you want to add the following users to this project?");?>" + "\n" + msg);
	}
	
	if (rta)
		f.submit();


}
//-->
</SCRIPT>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><form name=frmCons action="" method="post">
	<input type="hidden" name="amode" class="" value="<?=($amode==0 ? "0" : "1");?>" />
	<td valign="top" align="center">
	<?php
	if ($seluser){
		echo $AppUI->_("Project").":".arraySelect($lstprojects, 'project_id', 'size="1" class="text" onchange="javascript: this.form.submit();"', $project_id,true,false);
		?>
		<input type="hidden" name="amode" class="" value="<?=($amode==0 ? "0" : "1");?>" />
		<input type="hidden" name="option_id" class="" value="<?=$option_id;?>" />
		<?
	}else{
	?>
		<input type="hidden" name="amode" class="" value="<?=($amode==0 ? "0" : "1");?>" /><? echo $AppUI->_("Item").":".arraySelect($options, 'option_id', 'size="1" class="text" onchange="viewPerms(this);"', $option_id,true,false);?>
	<?
	}
	?>
	</td>
	</form>
</tr>
<tr>
		<form name="frmPerms" method="post" action="">	
		<input type="hidden" name="option_id" class="" value="<?=$option_id;?>" />
		<input type="hidden" name="user_id" class="" value="<?=$user_id;?>" />
		<input type="hidden" name="role_id" class="" value="<?=$role_id;?>" />
		<input type="hidden" name="perm_type" class="" value="<?=$role_id?"r":"u";?>" />
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="amode" class="" value="<?=($amode==0 ? "0" : "1");?>" />
		<input type="hidden" name="dosql" value="do_task_perms_aed" />
		<input type="hidden" name="task_project" value="<?=$project_id;?>" />		

<td valign="top">
	<table class="" cellspacing="1" cellpadding="2" width="100%" border="0">
	  <tr class="tableHeaderGral">
	      <th width="100%" colspan="2" align="middle" nowrap="nowrap"><?=$AppUI->_("Project Information");?></th>
	  </tr>
	  <tr class="tableForm_bg">
	  		<td width="50%"><?=$AppUI->_($items[6]).":".arraySelect($pvsProj, 'permission_value[-1][6]', 'size="1" class="text"', (isset($tbl_perm[-1][6][$oid]) ? $tbl_perm[-1][6][$oid] : 9),false,false ); ?>
	  		
	  		</td>
	  		<td width="50%"><?=$AppUI->_($items[7]).":".arraySelect($pvsProj, 'permission_value[-1][7]', 'size="1" class="text"', ( isset($tbl_perm[-1][7][$oid]) ? $tbl_perm[-1][7][$oid] : 9),false,false); ?>
	  		</td>
	  </tr>	
	</table>
	<br />	
	<table class="" cellspacing="1" cellpadding="2" width="100%" border="0">
	  <tr class="tableHeaderGral">
	      <th width="100%" colspan="2" align="middle" nowrap="nowrap"><?=$AppUI->_("Tasks Actions");?></th>
	  </tr>
	  <tr class="tableForm_bg">
	  		<td><?=arraySelect($pvsTask, 'permission_value[-1][1]', 'size="1" class="text"', (isset($tbl_perm[-1][1][$oid]) ? $tbl_perm[-1][1][$oid] : 9),false,false); ?>
	  		
	  		</td>
	  		<td width="100%"><?=$AppUI->_("msgOwner"); ?>
	  		</td>
	  </tr>	
	</table>
	<br />  
	<table class="" cellspacing="1" cellpadding="2" width="100%" border="0">
	<tr class="tableHeaderGral">
		<th nowrap align=center>
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
	<tr class="tableForm_bg"><td  width="100%">
		<input name="chkUnifica" type="checkbox" value="checkbox" checked><?=$AppUI->_("msgUnify"); ?>
		<table id=tblPerm  width="100%" border="0" cellpadding="2" cellspacing="1" class="">
		
			<?php
			if (!(!($amode) && $sm[$user_id])){ 
				?>
			<tr class="tableHeaderGral">
				<th>&nbsp;</th>
				<?php 
					foreach($itemsTasks as $item_id => $item_name){ ?>
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
							foreach($itemsTasks as $item_id => $item_name){
						?>
							<td id="cel_<?=$access_id;?>_<?=$item_id;?>" nowrap align=center>
						<?php
							echo arraySelect($pvs, 'permission_value['.$access_id.']['.$item_id.']', 'size="1" class="text" onchange="javascript: unifier('.$access_id.','.$item_id.');"', $tbl_perm[$access_id][$item_id][$oid],false,false);
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
			
			
				<tr class="tableHeaderGral"><th nowrap><?=$AppUI->_("Item");?></th><th nowrap><?=$AppUI->_("Level");?></th></tr>
				<?php
				foreach($itemsTasks as $item_id => $item_name){ ?>
				<tr><td nowrap><?=$AppUI->_($item_name);?></th>
					<td id=cel_-1_<?=$item_id;?> nowrap align=center>
					<?php
					echo arraySelect($pvs, 'permission_value['.$item_id.']', 'size="1" class="text" onchange="javascript: unifier('.$access_id.','.$item_id.');"', $tbl_perm[$access_id][$item_id][$oid],false,false);
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
					echo arraySelect($pvs, 'permission_value['.$item_id.']', 'size="1" class="text" onchange="javascript: unifier('.$access_id.','.$item_id.');"', $tbl_perm[$access_id][$item_id][$oid],false,false);
					?>
					</td>

				<?php }?>
				</tr>			
			
			
			<?php
				}						
			}
			
				?>

		</table>					
		</td></tr>
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
</tr></form></td>
</table>
<? }else{  
?>
<SCRIPT language="javascript1.2">
function viewPerms(cbo){
	if (!parseFloat(cbo.value) && cbo.value!="0")
		cbo.form.submit();
}
</SCRIPT>


<table width="650" border="0" cellpadding="2" cellspacing="0">
<tr><form name=frmCons action="" method="post">
	<td valign="top" align="center">
	<?php
	if ($seluser){
		echo $AppUI->_("Project").":".arraySelect($lstprojects, 'project_id', 'size="1" class="text" onchange="javascript: this.form.submit();"', $project_id,true,false);
		?>
		<input type="hidden" name="amode" class="" value="<?=($amode==0 ? "0" : "1");?>" />
		<input type="hidden" name="option_id" class="" value="<?=$option_id;?>" />
		<?
	}else{
	?>
		<input type="hidden" name="amode" class="" value="<?=($amode==0 ? "0" : "1");?>" /><? echo $AppUI->_("Item").":".arraySelect($options, 'option_id', 'size="1" class="text" onchange="viewPerms(this);"', $option_id,true,false);?>
	<?
	}
	?>
	</td>
	</form>
</tr>
</table>
	<? 
	
	
	
}

}
?>
