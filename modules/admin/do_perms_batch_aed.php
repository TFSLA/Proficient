<?php /* ADMIN $Id: do_perms_batch_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */

//echo "<pre>"; var_dump($_POST);echo "</pre>";
$applytemplate = dPgetParam($_POST, "applytemplate",0);
$securitytemplate_id = dPgetParam($_POST, "template_permission_template",0);

$users = explode(",",$_POST["userlist"]);


$AppUI->setMsg( 'Permission' );
$msg ="";
for($i=0;$i<count($users);$i++){
	
	if(! (intval($users[$i]) > 0)) continue;
		
	$p_user_id = $users[$i];
	
	
	$redirect = ""; //$_POST['redirect'];
	$obj = new CPermission();
	
	if (!$obj->bind( $_POST )) {
		$msg .= $obj->getError()."<br>";
		continue;
	}
	
	$canRead = !getDenyRead( $obj->permission_grant_on, $obj->permission_item );
	$canEdit = !getDenyEdit( $obj->permission_grant_on, $obj->permission_item );
	
	
	if($applytemplate=="1"){
		$result = mysql_query("delete FROM permissions WHERE permission_user = '$p_user_id'") or die(mysql_error());		
		$result = mysql_query("SELECT * FROM  securitytemplate_permissions WHERE  template_permission_template = $securitytemplate_id") or die(mysql_error());
		$flag=true;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	 
			$permission_grant_on = $row["template_permission_grant_on"];
			$permission_item     = $row["template_permission_item"];
			$permission_value    = $row["template_permission_value"];
			$sql = "REPLACE INTO `permissions` (`permission_user` , `permission_grant_on` , `permission_item` , `permission_value` )  VALUES ('$p_user_id', '$permission_grant_on', '$permission_item', '$permission_value') ";
			/*echo "<pre>";
			var_dump($sql);
			echo "</pre>";*/
			$result2 = mysql_query($sql);
			if (!$result){
				$msg .= mysql_errno().": ".mysql_error()."<br>";
				$flag = false;
			} 
			
		}
		if($flag){
			$obj->updateWebtrackingEnabled($p_user_id);
		}
	}
}

if ($msg == ""){
	$AppUI->setMsg( 'updated', UI_MSG_OK, true );
}else{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
}

$AppUI->redirect();

?>