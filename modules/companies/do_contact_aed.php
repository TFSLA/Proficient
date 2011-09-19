<?php include_once( './modules/contacts/contacts.class.php' ); ?>

<?php /* CONTACTS $Id: do_contact_aed.php,v 1.2 2009-07-17 20:11:15 nnimis Exp $ */
global $AppUI;
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;

$obj = new CContact();
$msg = '';

if (!$obj->bind( $_POST )) 
{
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

?>

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

if ($contact_exists!=0 && $contact_exists!=$obj->contact_id)
{
	$AppUI->setMsg( "ContactExists", UI_MSG_ERROR );
	$AppUI->redirect('m=companies&a=edit_contact&contact_id='.$obj->contact_id.'&dialog=1&suppressLogo=1&callback=setContact&table=contacts');
}
else
{
	$obj->contact_birthday = $_POST["contact_birthday"];
	if ( ($msg = $obj->store() ) )
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	else 
	{  
		
		/* Parte correspondiente a contactos */
       
		// Con el id del contacto busco en companies //
       $query = "select * from companies where contact_id = '$_POST[contact_id]'";
		$sql = mysql_query($query);
        
		$company_id = mysql_fetch_array($sql);

		if (count($company_id)>0)
		{
			// Si es 1 significa que cambi?la empresa de este contacto //

			if ($_POST[contact_company_ch]=="1")
			{
			   $query2 = "UPDATE companies SET 
		        contact_id = '' 
			    WHERE company_id = '$company_id[0]'  ";

				$slq2 = mysql_query($query2);
			}
			else
			{
				// Si no lo cambió actualizó los datos //

               $query2 = "UPDATE companies SET 
		        company_phone1 = '$_POST[contact_company_phone]' 
			    WHERE company_id = '$company_id[0]'  ";

				$slq2 = mysql_query($query2);
			}
		}
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
}

$obj->contact_id = CContact::getContactId($obj->contact_first_name,$obj->contact_last_name,$obj->contact_company,$obj->contact_public);

//echo "<pre>";print_r($obj);echo "</pre>";die;

//---------------------------------------------------------------------------------------------------------------------

?>

<script language="javascript">
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

</script>