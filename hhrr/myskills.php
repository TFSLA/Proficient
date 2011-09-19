<?php 
$title = $AppUI->_('Knowledge Matrix')." - ".$AppUI->_("View");
$titleBlock = new CTitleBlockHhrr( $title, 'hhrr.gif', "hhrr" );
$titleBlock->addCrumb( "hhrr/?a=editskills", "Edit" );
$titleBlock->show();

$hhrr_portal = true;
$_GET["id"] = $AppUI->user_id;
include_once($AppUI->getConfig("root_dir")."/modules/hhrr/viewskills.php");


?>