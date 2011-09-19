<?php 
//print_r( $_POST );
require_once( $AppUI->getModuleClass( "admin" ) );
$usr = new CUser();
$usr->load( $AppUI->user_id );

$permisos = $_POST["permisos"];
if ( $permisos )
{
	foreach( $permisos as $delegado=>$modulos )
	{	
		$usr->removeDelegations( $delegado );
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
			$usr->addDelegation( $nuevoDelegado, $modulo, $permiso );
	}
}
//exit();
//$AppUI->redirect("m=system&a=addeditdeleg");
?>
