<?php /* SYSTEM $Id: calendars.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
global $m, $user_id, $calendar_type, $canEdit;

$canEdit = !getDenyEdit($m, $user_id) || $user_id==$AppUI->user_id;

require_once( $AppUI->getModuleClass( 'system' ) );

$calendar_type = "3";

if($_GET['project_id'] > 0)
{
	$owners = CProject::getOwners($_GET['project_id']);
	
	$owner = CProject::getOwner($_GET['project_id']);
	
      if($AppUI->user_type!="1"){ 
	if(!$owners[$AppUI->user_id] && !$owner[$AppUI->user_id])
		$AppUI->redirect( "m=public&a=access_denied");
		
	$assignedUsers = CProject::getUsers($_GET['project_id']);
	
	if(!$assignedUsers[$user_id])
		$AppUI->redirect( "m=public&a=access_denied");
      }
}

include_once( "./modules/system/calendars.php");


?>