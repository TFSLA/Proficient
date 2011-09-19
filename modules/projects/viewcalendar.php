<?php

global $m, $project_id, $calendar_type, $canEdit;
require_once( $AppUI->getModuleClass( 'system' ) );

if (!isset($project_id) && isset($calendar_id)){
	$obj = new CCalendar();
	
	if (!$obj->load($calendar_id, false)){
		$AppUI->setMsg( 'Calendar' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
	
	}	
	$project_id = $obj->calendar_project;
}	

$obj = new CProject();
if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$canEdit = $obj->canEdit();



$calendar_type = "2";
include_once( "./modules/system/viewcalendar.php");

?>