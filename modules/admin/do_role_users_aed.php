<?php /* ADMIN $Id: do_role_users_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
import_request_variables("P", "p_");
$del = isset($p_del) ? $p_del : 0;

/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";
*/

$obj = new CProjectRoles();

if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}


$AppUI->setMsg( 'User Role' );

if ($del=="1"){

	$ret = $obj->delete($msg, $p_role, $p_project, $p_user_del);
}else{

	$ret = $obj->update($msg, $p_role, $p_project, $p_user);
}

$msg = $AppUI->_($msg);
$AppUI->setMsg($msg, $ret ? ($del=="1" ? UI_MSG_ALERT:UI_MSG_OK ):UI_MSG_ERROR, true );
?>