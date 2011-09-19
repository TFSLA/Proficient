<?php /* ADMIN $Id: do_perms_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */

//echo "<pre>"; var_dump($_POST);echo "</pre>";
include_once("{$AppUI->cfg['root_dir']}/modules/admin/admin.class.php");

$p_user_id = $_POST["user_id"];
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$redirect = $_POST['redirect'];
$obj = new CPermission();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect($redirect);
}

$canRead = !getDenyRead( $obj->permission_grant_on, $obj->permission_item );
$canEdit = !getDenyEdit( $obj->permission_grant_on, $obj->permission_item );

$AppUI->setMsg( 'Permission' );
if ($del) {

	$result = mysql_query("SELECT * FROM  permissions WHERE permission_id = $permission_id") or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$canRead = !getDenyRead( $row["permission_grant_on"], $row["permission_item"] );
	$canEdit = !getDenyEdit( $row["permission_grant_on"], $row["permission_item"] );	

	if($row["permission_item"]==-1){
		$result = mysql_query("SELECT * FROM  permissions WHERE 
			  (permission_grant_on = '{$row["permission_grant_on"]}' AND permission_user = {$AppUI->user_id} AND permission_item = -1 AND permission_value <> 0) 
			   OR permission_grant_on = 'all' AND permission_user = {$AppUI->user_id}")  
			  or die(mysql_error());
		if(mysql_num_rows($result) == 0){
			$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
			$AppUI->redirect($redirect);
		}
	}
	//RW:
	if($row["permission_value"]==-1 && !$canEdit){
		$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}
	//RO:
	if($row["permission_value"]==1 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}
	//DENY:
	if($row["permission_value"]==0 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}

	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );			
		$AppUI->redirect($redirect);
	} else {
		$obj->updateWebtrackingEnabled($p_user_id);
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect($redirect);
	}
} else if($applytemplate=="1"){
	
	//Si esta variable en verdadera borro todos los permisos anteriores del usuario.
	if ( $_POST['del_old'] )
	{
		$sql = "DELETE FROM permissions WHERE permission_user = $user_id ";
		$error = db_exec( $sql );
		if (!$error){
			$AppUI->setMsg( mysql_errno().": ".mysql_error(), UI_MSG_ERROR, true );
			$flag = false;
		} 
	}

	$result = mysql_query("SELECT * FROM  securitytemplate_permissions WHERE  template_permission_template = $securitytemplate_id") or die(mysql_error());
	$flag=true;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
 
		$permission_grant_on = $row["template_permission_grant_on"];
		$permission_item     = $row["template_permission_item"];
		$permission_value    = $row["template_permission_value"];
		$result2 = mysql_query("REPLACE INTO `permissions` (`permission_user` , `permission_grant_on` , `permission_item` , `permission_value` )  VALUES ('$user_id', '$permission_grant_on', '$permission_item', '$permission_value') ");
		if (!$result){
			$AppUI->setMsg( mysql_errno().": ".mysql_error(), UI_MSG_ERROR, true );
			$flag = false;
		} 
		
	}
	if($flag){
		$obj->updateWebtrackingEnabled($p_user_id);
		$AppUI->setMsg( 'updated', UI_MSG_OK, true );
	}
	$AppUI->redirect($redirect);
} else {
	if($obj->permission_item==-1){
		$result = mysql_query("SELECT * FROM  permissions WHERE 
			  (permission_grant_on = '{$obj->permission_grant_on}' AND permission_user = {$AppUI->user_id} AND permission_item = -1 AND permission_value <> 0) 
			   OR permission_grant_on = 'all' AND permission_user = {$AppUI->user_id}")  
			  or die(mysql_error());
		if(mysql_num_rows($result) == 0){
			$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
			$AppUI->redirect($redirect);
		}
	}
	//RW:
	if($obj->permission_value==-1 && !$canEdit){
		$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}
	//RO:
	if($obj->permission_value==1 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}
	//DENY:
	if($obj->permission_value==0 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}

	$isNotNew = @$_POST['permission_id'];
	//echo "<pre>"; var_dump($obj);echo "</pre>";
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$obj->updateWebtrackingEnabled($p_user_id);
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect($redirect);
}
?>