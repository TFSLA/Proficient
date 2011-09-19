<?php

global $m, $company_id, $calendar_type, $canEdit;
/*
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this record

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


// check if this record has dependancies to prevent deletion
$msg = '';



// setup the title block
$titleBlock = new CTitleBlock( 'Company Calendar', 'handshake.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=companies", "view company" );
$titleBlock->show();
*/

require_once( $AppUI->getModuleClass( 'system' ) );

$calendar_type = "1";
include_once( "./modules/system/addeditcalendar.php");

?>