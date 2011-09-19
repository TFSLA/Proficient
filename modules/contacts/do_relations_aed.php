<?php /* CONTACTS $Id: do_relations_aed.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */

include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
$contact_id = $_POST["contact_id"];

global $AppUI;


$date = date("Y-m-d h:i:s");

$permisos = "";

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canEdit = $canEdit || ( $permisos == "AUTHOR" && ( ( $obj->contact_creator == $AppUI->user_id && $obj->contact_owner == $delegator_id ) || !$obj->contact_id ) );
	$canEdit = $canEdit || ( $permisos == "EDITOR" && ( $obj->contact_owner == $delegator_id || !$obj->contact_id ) );
	$canEdit = $canEdit || $AppUI->user_type == 1;
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Contact' );

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

$type = $_POST["relation_type"];

$sql = "DELETE FROM contacts_relations WHERE contact_id = $contact_id AND relation_type = '$type'";
mysql_query($sql);

if(mysql_error()){
	$AppUI->setMsg( mysql_error() , UI_MSG_ERROR );
	$AppUI->redirect();
}

$registers = explode (",", $_POST["related_items"]);

//die("<pre>".print_r($registers)."</pre>");

for ($i=0; $i < count($registers)-1; $i++){		
	if($msg = CContact::saveRelation($type,$registers[$i],$contact_id)){
		$AppUI->setMsg( $msg , UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

if($delegator_id!=$AppUI->user_id){
	do_log($delegator_id, $mod_id, $AppUI, 5);
}

$AppUI->setMsg( 'updated' , UI_MSG_OK, true );
$AppUI->redirect();

