<?php

global $timexp_type, $timexp_types, $spvMode, $task_id;
$timexp_type="1";
$AppUI->savePlace(); 
$spvMode = 1;

$sql = "select task_project from tasks where task_id = '$task_id'";
$project_id = db_loadResult($sql);
$prj = new CProject();
$prj->load($project_id);
$spvMode = $prj->canManageRoles() ? 1 : 0;
if ($spvMode){

}
unset($prj);

include("vw_tasktimexp.php");
?>