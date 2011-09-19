<?php /* ADMIN $Id: do_project_users_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
//echo "<pre>";
//var_dump($_POST);
//echo "</pre>";
if (! class_exists("CProject"))
	require_once( $AppUI->getModuleClass( 'projects' ) );
	
require_once("modules/projects/do_project_users_aed.php");
?>