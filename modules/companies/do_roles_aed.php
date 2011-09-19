<?php /* ADMIN $Id: do_roles_aed.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
import_request_variables("P", "p_");
$del = isset($p_del) ? $p_del : 0;

/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";
*/
$obj = new CRoles();

if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}


$AppUI->setMsg( 'Roles' );
$msg="";
if ($del=="1"){
	$rdo = $obj->delete($p_role_id);
	if (is_null($rdo)){
		$ret = true;
		$msg = $AppUI->_("deleted");
	}else{
		$msg = $rdo;
	}
}elseif($p_add=="1"){
	/*$roles=$obj->getRoles( $p_company);
	$pk = array_keys($roles);
	$flg=true;
	for($rl=0;$rl<count($roles);$rl++){
		if ($roles[$pk[$rl]]["role_name"]==trim($p_role_name)){
			$flg = false;
		};
	}
	if ($flg){*/
		$ret = $obj->update($msg,$p_role_id, $p_role_name, $p_company, $p_role_description,$newrole);
		$AppUI->setMsg($msg, $ret ? ($del=="1" ? UI_MSG_ALERT:UI_MSG_OK ):UI_MSG_ERROR, true );
		$AppUI->redirect( "m=companies&a=view&company_id=$p_company&tab=6&role_id=$newrole&form=2" );
	/*}else{
		$ret=false;
		$msg = "A role with this name already exists";
	}*/
	
}else{
	$ret = $obj->update($msg,$p_role_id, $p_role_name, $p_company, $p_role_description,$newrole);
}

$AppUI->setMsg($msg, $ret ? ($del=="1" ? UI_MSG_ALERT:UI_MSG_OK ):UI_MSG_ERROR, true );
?>