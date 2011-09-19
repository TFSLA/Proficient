<?php
require_once( $AppUI->getModuleClass("admin") );
$usr = new CUser();
$usr->load( $AppUI->user_id );
$delegs = $usr->getDelegators();
$titleBlock = new CTitleBlock( 'Delegator selection', 'delegates.gif' );
$titleBlock->show();
$tab = dPgetParam( $_GET, "tab", 0 );

$tabBox = new CTabBox( "?m=public&a=delegation_selector", "{$AppUI->cfg['root_dir']}/modules/delegates/", $tab );
$tabBox->add( 'my_delegators', 'Modules delegated by other users' );
$tabBox->add( 'addeditdeleg', 'My delegated modules' );
$tabBox->show();
?>
