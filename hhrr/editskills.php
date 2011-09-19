<?php 
$title = $AppUI->_('Knowledge Matrix')." - ".$AppUI->_("Edit");
$titleBlock = new CTitleBlockHhrr( $title, 'hhrr.gif', "hhrr" );
$titleBlock->addCrumb( "hhrr/?a=myskills", "View" );
$titleBlock->show();

$hhrr_portal = true;
$_GET["id"] = $AppUI->user_id;
include_once($AppUI->getConfig("root_dir")."/modules/hhrr/addedituserskills.php");


?>