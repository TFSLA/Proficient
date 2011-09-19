<?php 
global $obj, $project_id, $task_id;

$project_id = $obj->task_project;
$task_id = $obj->task_id;

include("{$AppUI->cfg['root_dir']}/modules/projects/vw_todo.php");

?>