<?php 
//print_r( $_POST );

if ( isset($_POST["user_id"]) )
	$user_id = $_POST["user_id"];
else
	$user_id = $AppUI->user_id;

require_once( $AppUI->getModuleClass( "admin" ) );
$usr = new CUser();
$usr->load( $user_id );

$permisos = $_POST["permisos"];
$AppUI->setMsg( "Delegate" );
if ( $permisos )
{
	foreach( $permisos as $delegado=>$modulos )
	{	
		if ( !$usr->removeDelegations( $delegado ) )
		{
			$AppUI->setMsg( "Remove delegations", UI_MSG_ERROR );			
		}
		foreach( $modulos as $m_id=>$perm )
		{
			if ( $perm != "NONE" )
				$usr->addDelegation( $delegado, $m_id, $perm );
		} 
	}
}

$nuevoDelegado = intval( substr($_POST["nuevoDelegado"], 1 ) );
if ( $nuevoDelegado )
{
	$permisosNuevo = $_POST["permisosNuevo"];
	foreach( $permisosNuevo as $modulo=>$permiso )
	{
		if ( $permiso != "NONE" )
		{
			if ( !$usr->addDelegation( $nuevoDelegado, $modulo, $permiso ) )
			{
				$AppUI->setMsg( "Add delegation", UI_MSG_ERROR );
			}			
		}
	}
}
$AppUI->setMsg( "updated", UI_MSG_OK, true );
//$AppUI->redirect( "m=public&a=delegation_selector&tab=1" );
//$AppUI->redirect( "m=delegates&tab=1" );
$AppUI->redirect(  );
?>
