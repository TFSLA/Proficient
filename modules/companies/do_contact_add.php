<?php include_once( './modules/contacts/contacts.class.php' ); ?>

<?php /* CONTACTS $Id: do_contact_add.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
global $AppUI;
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;

$obj = new CContact();
$msg = '';

$back = $_POST[origen];

if (!$obj->bind( $_POST )) 
{
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect($back);
}

?>

<script language="javascript">
function setClose() {
	var contactid = <?php echo $obj->contact_id; ?>;
	var contactname = "<?php echo $obj->contact_first_name; ?>";
	var	last_name = "<?php echo $obj->contact_last_name; ?>";
	var email = "<?php echo $obj->contact_email; ?>";
	var	email2 = "<?php echo $obj->contact_email2; ?>";
	var title = "<?php echo $obj->contact_title; ?>";
	var phone = "<?php echo $obj->contact_phone; ?>";
	var type = "<?php echo $obj->contact_type; ?>";
	var icq = "<?php echo $obj->contact_icq; ?>";
	var address1 = "<?php echo $obj->contact_address1; ?>";
	var phone2 = "<?php echo $obj->contact_phone2; ?>";
	var business_phone = "<?php echo $obj->contact_business_phone; ?>";
	var city = "<?php echo $obj->contact_city; ?>";
	var fax = "<?php echo $obj->contact_fax; ?>";
	var zip = "<?php echo $obj->contact_zip; ?>";
	var mobile = "<?php echo $obj->contact_mobile; ?>";
	var state = "<?php echo CContact::getStateString($obj->contact_id); ?>";
	var country = "<?php echo CContact::getCountryString($obj->contact_id); ?>";
	var notes = "<?php echo $obj->contact_notes; ?>";
	var department = "<?php echo $obj->contact_department; ?>";
	var manager = "<?php echo $obj->contact_manager; ?>";
	var assistant = "<?php echo $obj->contact_assistant; ?>";
	var company = "<?php echo $obj->contact_company; ?>";
	
	var key = contactid;
	var fname = contactname;
	
	   window.opener.setContact(key,fname, last_name, email, email2, title, phone, type, icq, address1, phone2, business_phone, city, fax, zip, mobile, state, country, notes, department, manager, assistant,company );
	   window.close();
}

</script>
<script language="javascript">
	if(confirm("<?php echo $AppUI->_('Esta seguro'); ?>")) {
		setClose();
	}else{
		window.location='/index.php?m=companies&a=add_contact&dialog=1&suppressLogo=1&callback=setContact&table=contacts';
	}
</script>

<?php

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
	do_log($delegator_id, $mod_id, $AppUI, 5);
	
}

$del = dPgetParam( $_POST, 'del', 0 );

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Contact' );

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

?>

<?php
/*
Estas dos lineas de abajo fueron sacadas x que hacian que por cualquier cambio en el contacto se estan cambiando el owner y el creador del contacto
Y esto no tiene x que ser asi.
Vi vemos que trae problemas x otro lado avisen, pero no lo descomenten asi nomas.
Fravizzini*/
/*
	$obj->contact_owner = $delegator_id;
	$obj->contact_creator = $delegator_id;
*/

if(empty($obj->contact_public) || $obj->contact_public!="1" || $obj->contact_public!=1){
	$obj->contact_public=0;
}

$contact_exists = CContact::contact_exists($obj->contact_first_name,
									$obj->contact_last_name, $obj->contact_company, $obj->contact_public); 
									
if ($contact_exists)
{
	$AppUI->setMsg( "ContactExists", UI_MSG_ERROR );
}
else
{
	if ( ($msg = $obj->store() ) )
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	else
	{
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
}

$obj->contact_id = CContact::getContactId($obj->contact_first_name,$obj->contact_last_name,$obj->contact_company,$obj->contact_public);

//---------------------------------------------------------------------------------------------------------------------

?>