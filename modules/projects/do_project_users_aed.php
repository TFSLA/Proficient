<?php /* PROJECTS $Id: do_project_users_aed.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$obj = new CProject();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

//echo "<pre>"; print_r($_POST); echo "</pre>";

$del = dPgetParam( $_POST, 'del', 0 );
$role_id = dPgetParam( $_POST, 'role_id', 2 );
$user_id = dPgetParam( $_POST, 'user_id', 0 );
$user_units = dPgetParam( $_POST, 'user_units', 0 );

echo "<pre>"; print_r($_POST); echo "</pre>";
// prepare (and translate) the module name ready for the suffix
if ($del) {
	echo "STOP 1";
	if (!$obj->canEdit( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	
	if (($msg = $obj->deleteAssignedUser($role_id, $user_id))) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "User deleted from project", UI_MSG_ALERT);
		$AppUI->redirect();
	}
} else {
	$isNotNew = $_POST['add'];
	 
	if($isNotNew){
	         
	        echo  "$obj->updateAssignedUser(".$role_id.",".$user_id.",".$user_units.")<br>";
	        if (($msg = $obj->updateAssignedUser($role_id, $user_id, $user_units))) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	        } else {
		$AppUI->setMsg( $isNotNew ? 'User inserted' : 'User updated', UI_MSG_OK);
	       }
	}else{
	     
	     $query_update = "UPDATE project_roles SET role_id='".$role_id."' WHERE user_id='".$user_id."' ";
	     echo "$query_update";
	     $sql_update = db_exec($query_update);
	     $AppUI->setMsg( $isNotNew ? 'User inserted' : 'User updated', UI_MSG_OK);
	}
	
	//  Agrego o actualizo el nivel de acceso del usuario para webtracking
	$query_1 = "SELECT * FROM btpsa_project_user_list_table WHERE user_id='".$user_id."' AND project_id='".$_POST['project_id']."' ";
	$result_1 = db_loadResult($query_1);
	
	if($result_1 =="")
	{
	     $query_access_level = "INSERT INTO btpsa_project_user_list_table VALUES('".$_POST['project_id']."', '".$user_id."','".$_POST['access_level']."') ";
	}else{
	     $query_access_level = "UPDATE btpsa_project_user_list_table SET access_level ='".$_POST['access_level']."' WHERE  user_id='".$user_id."' AND project_id='".$_POST['project_id']."'  ";
	}
	
	$sql_access_level = db_exec( $query_access_level );
	
	 $AppUI->redirect();
}
?>
