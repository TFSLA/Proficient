<?php /* PROJECTS $Id: vw_files.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
GLOBAL $AppUI, $task_project, $task_id, $deny, $canRead, $canEdit;

$showProject = false;
$project_id = $task_project;
require( "{$AppUI->cfg['root_dir']}/modules/files/index_table.php" );
?>