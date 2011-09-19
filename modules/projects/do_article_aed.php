<?php 
global $m, $project_id;
$obj = new CProject();

if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
$canEdit = $obj->canEdit();
require_once( $AppUI->getModuleClass( 'articles' ) );

include_once( "./modules/articles/do_article_aed.php");


?>