<?php  /* TICKETSMITH $Id: infobox_mytickets.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */

GLOBAL $AppUI;
if (! class_exists("CProject"))
	require_once( $AppUI->getModuleClass( 'projects' ) ); 

$type = "All";
$user = $AppUI->user_id;
include("infobox_tickets.php");



?>
