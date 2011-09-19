<?php /* CALENDAR $Id: do_event_inv_del.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
//$debugsql=1;
$delegator_id = dpGetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 4;

$obj = new CEventInvitation();
// bind the POST parameter to the object record
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();	
}

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );	
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator( $delegator_id, $mod_id ) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg("Delegator");
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$usr->load( $delegator_id );
	$permiso = $usr->getDelegatePermission( $AppUI->user_id, $mod_id );
	
	$canEdit = $canEdit || ( $permiso == "AUTHOR" && $obj->event_creator == $AppUI->user_id );
	$canEdit = $canEdit || ( $permiso == "EDITOR" );	
	$canEdit = $canEdit || $AppUI->user_type == 1;
}

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}
$msg = '';

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Invitation' );
if (!$obj->canDelete( $msg )) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
if (($msg = $obj->delete())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
} else {
	$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
}
$AppUI->redirect( 'm=calendar&a=inv_addedit&event_id='.$_POST["event_id"]."&delegator_id=$delegator_id&dialog=$dialog" );
?>