<?php /* ADMIN $Id: do_role_perms_aed.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
//import_request_variables("P", "p_");
/*
echo "<pre>";
foreach($_POST as $name => $value){
	if (is_array($value)){
		echo "$name ="; var_dump($value);echo "<br>";
	}else
		echo "$name = $value <br>";
}
echo "</pre>";
*/

foreach($_POST as $name => $value){
	$nm="p_".$name;
	$$nm=$value;
}
$del = isset($p_del) ? $p_del : 0;

$obj = new CCompany();

if (!$obj->load($p_company, false)){
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

//modo avanzado
if ($p_amode){
	if (!is_array($p_permission_value)){
		$AppUI->setMsg( 'Permission' );
		if ($del==1 or $p_permission_value=='9'){
			$obj->setPermission($p_role, -1, $p_access_id, $p_item_id, 9);
		}else{
			$obj->setPermission($p_role, -1, $p_access_id, $p_item_id, $p_permission_value);
		}
	}else{
	
		$tp = new CTaskPermission();
		$access=$tp->getTaskAccess();
		$items=$tp->getItemsPermission();
		foreach ($p_permission_value as $access_id => $fila){
			foreach ($fila as $item_id => $pv){
				//echo "p_permission_value[$access_id][$item_id]=".$pv."<br>";
				//$pv=($p_permission_value[$access_id][$item_id]) ? $p_permission_value[$access_id][$item_id] : 9;
				$obj->setPermission($p_role, -1, $access_id, $item_id, $pv);
				unset($pv);
			}
		}
		/*
		foreach ($access as $access_id => $access_name){
			foreach ($items as $item_id => $item_name){
				//echo "p_permission_value[$access_id][$item_id]=".$p_permission_value[$access_id][$item_id]."<br>";
				$pv=($p_permission_value[$access_id][$item_id]) ? $p_permission_value[$access_id][$item_id] : 9;
				$obj->setPermission($p_role, -1, $access_id, $item_id, $pv);
				unset($pv);
			}
		}
		*/
		
	}
}else{
	
	if (!is_array($p_permission_value)){
		$AppUI->setMsg( 'Permission' );
		if ($del==1 or $p_permission_value=='9'){
			$obj->setPermission($p_role, -1, $p_access_id, $p_item_id, 9);
		}else{
			$obj->setPermission($p_role, -1, $p_access_id, $p_item_id, $p_permission_value);
		}
	}else{
	
		$tp = new CTaskPermission();
		$access=$tp->getTaskAccess();
		$items=$tp->getItemsPermission();
		foreach ($access as $access_id => $access_name){
			foreach ($items as $item_id => $item_name){
				//echo "p_permission_value[$item_id]=".$p_permission_value[$item_id]."<br>";
				$pv=$p_permission_value[$item_id];
				$obj->setPermission($p_role, -1, $access_id, $item_id, $pv);
				unset($pv);
			}
		}
	}

}

//$AppUI->redirect();


/*
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
			$AppUI->redirect();
		}
	}
	//RW:
	if($row["permission_value"]==-1 && !$canEdit){
		$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	//RO:
	if($row["permission_value"]==1 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	//DENY:
	if($row["permission_value"]==0 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges to delete this item.", UI_MSG_ERROR );
		$AppUI->redirect();
	}

	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
} else if($applytemplate=="1"){
	$result = mysql_query("SELECT * FROM  securitytemplate_permissions WHERE  template_permission_template = $securitytemplate_id") or die(mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
 
		$permission_grant_on = $row["template_permission_grant_on"];
		$permission_item     = $row["template_permission_item"];
		$permission_value    = $row["template_permission_value"];
		$result2 = mysql_query("INSERT INTO `permissions` (`permission_user` , `permission_grant_on` , `permission_item` , `permission_value` )  VALUES ('$user_id', '$permission_grant_on', '$permission_item', '$permission_value') ") or $AppUI->setMsg( mysql_error(), UI_MSG_ERROR );
	}
	$AppUI->redirect();
} else {
	if($obj->permission_item==-1){
		$result = mysql_query("SELECT * FROM  permissions WHERE 
			  (permission_grant_on = '{$obj->permission_grant_on}' AND permission_user = {$AppUI->user_id} AND permission_item = -1 AND permission_value <> 0) 
			   OR permission_grant_on = 'all' AND permission_user = {$AppUI->user_id}")  
			  or die(mysql_error());
		if(mysql_num_rows($result) == 0){
			$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
			$AppUI->redirect();
		}
	}
	//RW:
	if($obj->permission_value==-1 && !$canEdit){
		$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	//RO:
	if($obj->permission_value==1 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	//DENY:
	if($obj->permission_value==0 && !$canRead){
		$AppUI->setMsg( "Your user have not enough privileges.", UI_MSG_ERROR );
		$AppUI->redirect();
	}

	$isNotNew = @$_POST['permission_id'];
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
*/
?>