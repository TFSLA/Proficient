<?php

global $m, $user_id, $calendar_type, $canEdit;
require_once( $AppUI->getModuleClass( 'system' ) );

if (!isset($user_id) && isset($calendar_id)){
	$obj = new CCalendar();
	
	if (!$obj->load($calendar_id, false)){
		$AppUI->setMsg( 'Calendar' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
	
	}	
	$user_id = $obj->calendar_user;
}	

$canEdit = !getDenyEdit($m, $user_id) || $user_id==$AppUI->user_id;



$calendar_type = "3";
include_once( "./modules/system/addeditcalendar.php");

?>