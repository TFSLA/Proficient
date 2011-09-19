<?php 

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

//include ("vw_myweek.php");
include ("mysheets.php");

$AppUI->savePlace();
?>