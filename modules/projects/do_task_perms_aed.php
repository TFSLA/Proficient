<?php /* ADMIN $Id: do_task_perms_aed.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
import_request_variables("P", "p_");
$del = isset($p_del) ? $p_del : 0;

/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";
*/

$obj = new CProject();

if (!$obj->load($p_task_project, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$AppUI->setMsg( 'Permission' );


//modo avanzado
//if ($p_amode){
	if (!is_array($p_permission_value)){
		$AppUI->setMsg( 'Permission' );
		if ($del==1 or $p_permission_value=='9'){
			$obj->setPermission($p_user_id,  $p_access_id, $p_item_id, 9);
		}else{
			$obj->setPermission($p_user_id,  $p_access_id, $p_item_id, $p_permission_value);
		}
	}else{
	/*
		$tp = new CTaskPermission();
		$access=$tp->getTaskAccess();
		$items=$tp->getItemsPermission();
		foreach ($access as $access_id => $access_name){
			foreach ($items as $item_id => $item_name){
				#echo "p_permission_value[$access_id][$item_id]=".$p_permission_value[$access_id][$item_id]."<br>";
				$pv=$p_permission_value[$access_id][$item_id];
				$obj->setPermission($p_user_id,  $access_id, $item_id, $pv);
				unset($pv);
			}
		}
		*/
		if ($p_perm_type=="r"){

			foreach ($p_permission_value as $access_id => $fila){
				foreach ($fila as $item_id => $pv){
					//echo "p_permission_value[$access_id][$item_id]=".$pv." *** ->setRolePermission($p_role_id,  $access_id, $item_id, $pv)<br>";
					//$pv=($p_permission_value[$access_id][$item_id]) ? $p_permission_value[$access_id][$item_id] : 9;
					$obj->setRolePermission($p_role_id,  $access_id, $item_id, $pv);
					unset($pv);
				}
			}	
		
		}elseif($p_perm_type=="u"){
	
			foreach ($p_permission_value as $access_id => $fila){
				foreach ($fila as $item_id => $pv){
					//echo "p_permission_value[$access_id][$item_id]=".$pv."<br>";
					//$pv=($p_permission_value[$access_id][$item_id]) ? $p_permission_value[$access_id][$item_id] : 9;
					$obj->setPermission($p_user_id,  $access_id, $item_id, $pv);
					unset($pv);
				}
			}
			if($msg = CUser::setWebtrackingPermissions($p_user_id)){
				$AppUI->setMsg( $msg, UI_MSG_ERROR );
			}else{
				$AppUI->setMsg('updated', UI_MSG_OK, true );
			}
		}
		
	}
	/*
}else{
	
	if (!is_array($p_permission_value)){
		$AppUI->setMsg( 'Permission' );
		if ($del==1 or $p_permission_value=='9'){
			$obj->setPermission($p_user_id,  $p_access_id, $p_item_id, 9);
		}else{
			$obj->setPermission($p_user_id,  $p_access_id, $p_item_id, $p_permission_value);
		}
	}else{
	
		$tp = new CTaskPermission();
		$access=$tp->getTaskAccess();
		$items=$tp->getItemsPermission();
		foreach ($access as $access_id => $access_name){
			foreach ($items as $item_id => $item_name){
				#echo "p_permission_value[$item_id]=".$p_permission_value[$item_id]."<br>";
				$pv=$p_permission_value[$item_id];
				$obj->setPermission($p_user_id,  $access_id, $item_id, $pv);
				unset($pv);
			}
		}
	}

}*/


?>