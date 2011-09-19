<?php /* ADMIN $Id: do_role_aed.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
import_request_variables("P", "p_");
$del = isset($p_del) ? $p_del : 0;

/*
echo "<pre>";
foreach($_POST as $name => $value){
	echo "$name = $value <br>";
}
echo "</pre>";
*/
$obj = new CJobs();

if (!($obj -> canEdit)){
	$AppUI->redirect( "m=public&a=access_denied" );
}

$AppUI->setMsg( 'Jobs' );
$msg="";
if ($del=="1"){
	$rdo = $obj->delete($p_job_id);
	if (is_null($rdo)){
		$ret = true;
		$msg = $AppUI->_("deleted");
	}else{
		$msg = $rdo;
	}
}elseif($p_add=="1"){
		$ret = $obj->update($msg,$p_job_id, $p_job_name, $p_job_company, $p_job_department, 
			$p_job_report_to, $p_job_main_functions, $p_job_requirements, $id);
		$AppUI->setMsg($msg, $ret ? ($del=="1" ? UI_MSG_ALERT:UI_MSG_OK ):UI_MSG_ERROR, true );
	/*}else{
		$ret=false;
		$msg = "A job with this name already exists";
	}*/
}else{
	$ret = $obj->update($msg,$p_job_id, $p_job_name, $p_job_company, $p_job_department, 
		$p_job_report_to, $p_job_main_functions, $p_job_requirements, $id);
}

$AppUI->setMsg($msg, $ret ? ($del=="1" ? UI_MSG_ALERT:UI_MSG_OK ):UI_MSG_ERROR, true );
if(!empty($_GET['a'])){
	if($ret){
		if($p_add == "1"){
			$AppUI->redirect("m=hhrr&a=addeditrole&id=$ret");
		}else{
			$AppUI->redirect("m=hhrr&a=viewrole&id=$ret");
		}
	}
	else
		$AppUI->redirect("m=hhrr&a=addeditrole");
}
?>