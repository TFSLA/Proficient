<?php 
$AppUI->savePlace();

$titleBlock = new CTitleBlockHhrr( 'Personal Info', 'hhrr.gif', "hhrr" );
$titleBlock->show();
	
$hhrr_portal = true;
$_GET["id"] = $AppUI->user_id;

include_once($AppUI->getConfig("root_dir")."/functions/hhrr_func.php");
include_once($AppUI->getConfig("root_dir")."/modules/hhrr/addedit.php");


?>