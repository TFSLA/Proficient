<?php /* ADMIN $Id: do_projectadm_aed.php,v 1.2 2009-08-10 13:43:54 nnimis Exp $ */
import_request_variables("P", "p_");
$del = isset($p_del) ? $p_del : 0;
$project_id = $_POST["project_id"];
/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";
*/

$obj = new CProject();
if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
	

$AppUI->setMsg( 'User' );
unset($msg);
if ($del=="1"){
	$msg = $obj->deleteAdministrator($p_user_id);
}else{
	$msg = $obj->addAdministrator($p_user_id);
}

if (!$msg){
	$ret = ( UI_MSG_ERROR );
	$msg = db_error();
}else{
	$ret = ($del=="1" ? UI_MSG_ALERT : UI_MSG_OK );
	$msg = ($del=="1" ? "deleted" : "updated" );
}

$AppUI->setMsg($msg, $ret, true );
//$AppUI->redirect();
?>