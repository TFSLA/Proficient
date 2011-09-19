<?php /* ADMIN $Id: do_role_perms_aed.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
import_request_variables("P", "p_");
require_once( $AppUI->getModuleClass( 'tasks' ) );

/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";


foreach($_POST as $name => $value){
	$nm="p_".$name;
	$$nm=$value;
}
*/
$del = isset($p_del) ? $p_del : 0;
$obj=new CProjectRolesPermission();	

//modo avanzado
//if ($p_amode){
	if (!is_array($p_permission_value)){
		$AppUI->setMsg( 'Permission' );
		if ($del==1 or $p_permission_value=='9'){
			$obj->setPermission($p_role, -1, -1, $p_access_id, $p_item_id, 9);
		}else{
			$obj->setPermission($p_role, -1, -1, $p_access_id, $p_item_id, $p_permission_value);
		}
	}else{
	
		$tp = new CTaskPermission();
		$access=$tp->getTaskAccess();
		$items=$tp->getItemsPermission();
		foreach ($p_permission_value as $access_id => $fila){
			foreach ($fila as $item_id => $pv){
				//echo "p_permission_value[$access_id][$item_id]=".$pv."<br>";

				$obj->setPermission($p_role, -1, -1,  $access_id, $item_id, $pv);
				unset($pv);
			}
		}
	}
	
/*	
}else{
	
	if (!is_array($p_permission_value)){
		$AppUI->setMsg( 'Permission' );
		if ($del==1 or $p_permission_value=='9'){
			$obj->setPermission($p_role, -1, -1, $p_access_id, $p_item_id, 9);
		}else{
			$obj->setPermission($p_role, -1, -1,  $p_access_id, $p_item_id, $p_permission_value);
		}
	}else{
	
		$tp = new CTaskPermission();
		$access=$tp->getTaskAccess();
		$items=$tp->getItemsPermission();
		foreach ($access as $access_id => $access_name){
			foreach ($items as $item_id => $item_name){
				//echo "p_permission_value[$item_id]=".$p_permission_value[$item_id]."<br>";
				$pv=$p_permission_value[$item_id];
				$obj->setPermission($p_role, -1, -1, $access_id, $item_id, $pv);
				unset($pv);
			}
		}
	}

}
*/
?>