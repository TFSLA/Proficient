<?php
global $m, $project_id;
$obj = new CProject();

if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
$canEdit = $obj->canEdit();
require_once( $AppUI->getModuleClass( 'system' ) );

include_once( "./modules/system/do_calendar_aed.php");

?>