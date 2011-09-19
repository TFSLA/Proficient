<?php 
//$debugsql=1;
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id);
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );
$mod_id = 18;
$lead_id = dPgetParam( $_POST, "lead_id", 0 );
$id = dPgetParam( $_POST, "id", 0 );

@include_once( "leads.class.php" );

$lead = new CLead();
if ( !$lead->load( $lead_id ) && $lead_id > 0 )  
{
	$AppUI->setMsg( 'Lead' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} 

if ( $delegator_id != $AppUI->user_id )
{	
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canEdit = !$id;
	$canEdit = $canEdit || ( $permisos == "AUTHOR" && $lead->lead_owner == $delegator_id && $lead->lead_creator == $AppUI->user_id );
	$canEdit = $canEdit || ( $permisos == "EDITOR" && $lead->lead_owner == $delegator_id);
	$canEdit = $canEdit || $AppUI->user_type == 1;
}
/*echo "canEdit = $canEdit, permsisos = $permisos";
exit();*/
if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

$del = isset($_POST['del']) ? $_POST['del'] : 0;

$contact = new CLeadContact();
if (($msg = $contact->bind( $_POST ))) 
{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Lead Contact' );
if ($del) {
	if (($msg = $contact->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
} 
else 
{
 	$contact->idsalespipeline = $lead_id; //En la tabla se llama idsalespipeline 	
	if (($msg = $contact->store())) 
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	else 
	{
		$isNotNew = $id;
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}
$AppUI->redirect("m=pipeline&a=addedit&delegator_id=$delegator_id&dialog=$dialog&lead_id=$lead_id");
?>