<?php

//Valido que tenga permisos para el modulo
if (getDenyEdit("admin") && $_GET['user_id']!= $AppUI->user_id)
	 $AppUI->redirect( "m=public&a=access_denied" );

// check permissions
if (!$canEdit && $user_id!=$AppUI->user_id)
    $AppUI->redirect( "m=public&a=access_denied" );


//Con esto verifico que el usuario edite a su nive: usuario solo pueden editar usuarios, s pueden editar cualquier cosa
if ( !edit_admin($AppUI->user_id) )
	$AppUI->redirect( "m=public&a=access_denied" );	

if($_GET[user_id]==$AppUI->user_id)
	$AppUI->redirect( "m=admin&a=addedituser_personal&user_id=".$_GET['user_id']);
else
	$AppUI->redirect( "m=admin&a=addedituser_admin&user_id=".$_GET['user_id']);

?>
