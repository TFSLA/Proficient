<?php /* CONTACTS $Id: do_multiplerelations_aed.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
global $AppUI, $delegator_id;

//die("<pre>".print_r($_POST["related_items"])."</pre>");

include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
$contact_id = $_POST["contact_id"];
$action = $_POST["ContactListAction"];
$contactList = explode(',',$_POST["ContactList"]);

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

$registers = explode (",", $_POST["related_items"]);

//die("<pre>".print_r($registers)."</pre>");

for ($j=0; $j < count($registers)-1; $j++){
	for ($i=0; $i<count($contactList); $i++){
		$sql = "SELECT contact_id FROM contacts_relations WHERE relation_type = '$action'
		AND contact_id = $contactList[$i] AND relation_type_id = $registers[$j]";
		$result = mysql_query($sql);
		
		if(mysql_num_rows($result) == 0){  //si no existe la relación
			$sql = "INSERT INTO contacts_relations (contact_id, relation_type_id, date, relation_creator, relation_type) VALUES (
					$contactList[$i], $registers[$j], '$date', $AppUI->user_id, '$action')";
			mysql_query($sql);
		}
		if(mysql_error()){
			$AppUI->setMsg( mysql_error() , UI_MSG_ERROR );
			$AppUI->redirect();
		}
	}
}

if($delegator_id!=$AppUI->user_id){
	do_log($delegator_id, $mod_id, $AppUI, 5);
}

$AppUI->setMsg( 'updated' , UI_MSG_OK, true );
$AppUI->redirect("m=contacts&delegator_id=$delegator_id");

