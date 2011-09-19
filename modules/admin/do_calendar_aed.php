<?php
global $m, $user_id, $calendar_type, $canEdit;

/*$obj = new CProject();

if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
$canEdit = $obj->canEdit();*/
require_once( $AppUI->getModuleClass( 'system' ) );

$calendar_type = "3";
require_once( $AppUI->getModuleClass( 'system' ) );

include_once( "./modules/system/do_calendar_aed.php");

?>