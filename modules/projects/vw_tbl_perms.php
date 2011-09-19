<?php /* PROJECTS $Id: vw_tbl_perms.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
GLOBAL $AppUI, $project_id, $tuid, $canEdit,$user_context;




$pvs = array(
'-1' => $AppUI->_('RW'),
//'0' => 'deny',
'1' => $AppUI->_('R')
);


$obj = new CProject();
if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
$perms=$obj->getPermissions($tuid);


$obj = new CTaskPermission();
$access=$obj->getTaskAccess();
$items=$obj->getItemsPermission();



$tbl_perm=array();
for ($i=0;$i<count($perms);$i++){
	$access_id=$perms[$i]["task_access_id"];
	$item_id=$perms[$i]["task_permission_on"];
	$task_user_id=$perms[$i]["task_user_id"];
	$tbl_perm[$access_id][$item_id][$task_user_id]=$perms[$i]["task_permission_value"];
}

?>
	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tr>
		<th>&nbsp;</th>
		<?php 
			//for($ii=0;$ii<count($items);$ii++){ 
			foreach($items as $item_id => $item_name){ ?>
			<th nowrap width="60"><?=$AppUI->_($item_name);?></th>
		<?php }?>
	</tr>
	</tr>
	<?php
		foreach ($access as $access_id => $access_name){
		//foreach($user_context as $task_user_id=>$context_name){
		?>
		<tr>
			<td nowrap><?=$AppUI->_($access_name); //$AppUI->_($context_name);?>
			</td>
			<?php //for($ii=0;$ii<count($items);$ii++){ 
				foreach($items as $item_id => $item_name){ 
//				$item_id=$items[$ii]["item_id"];
					$value = $tbl_perm[$access_id][$item_id][$tuid] ? $tbl_perm[$access_id][$item_id][$task_user_id] : "0";
					$lnkedit="";
					$lnkdel="";
					if ($value!="0" && ($canEdit) ){
						$lnkedit = "<a href='javascript: //' onClick=\"editPerm({$access_id},'{$task_user_id}',{$item_id},{$value},'{$context_name}');\" title=\""
						.$AppUI->_('edit')."\">"					
						.'<img src="./images/icons/edit_small.jpg" width="20" height="20" alt="" border="0" >'
						."</a>";
	
						$lnkdel .= "<a href=# onClick=\"delIt({$access_id},{$task_user_id},{$item_id});\" title=\""
						.$AppUI->_('delete')."\">"
						. '<img src="./images/icons/trash_small.gif" alt="" border="0">'
						. "</a>";						
					}
					$value = $tbl_perm[$access_id][$item_id][$task_user_id] ? 
							$pvs[$tbl_perm[$access_id][$item_id][$task_user_id]] : " - ";
					
					
					
					
				?>
				<td nowrap>
				<table border=0 cellpadding="0" cellspacing="1" width=100%>
				<tr><td><?=$lnkedit;?></td><td><?=$lnkdel;?></td><td nowrap align=center width="100%"><?=$AppUI->_($value);?></td></tr>
				</table>
				</td>
			<?php }?>
		</tr>
		<?php }?>
</table>