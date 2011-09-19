<?php /* CONTACTS $Id: do_contacts_export.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;

$msg = '';

$permisos = "";
require_once( $AppUI->getModuleClass( "admin" ) );
if ( $delegator_id != $AppUI->user_id )
{	
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
	$canEdit = $canEdit || ( $permisos == "AUTHOR" || $permisos == "EDITOR" );
	do_log($delegator_id, $mod_id, $AppUI, 4);
}

// prepare (and translate) the module name ready for the suffix

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}		

$usr = new CUser();
$usr->load( $delegator_id );
$contacts = $usr->getContacts();

switch ( $_POST["format"] )
{
	case "HTML": 
		exportToHTML( $contacts );
		break;
	case "CSV":
		exportToCSV( $contacts );
		break;
	case "XLS":
		exportToXLS( $contacts );
		break;
	case "Outlook":
		exportToOutlook( $contacts );
		break;
}

function exportToTable( $contacts )
{
	GLOBAL $AppUI, $usr;
	?>
    <head>
    <title><?php echo $AppUI->_("Contacts of")." ".$usr->user_first_name." ".$usr->user_last_name;?></title>
    </head>
    <body>
	<table border="1">
		<tr>
			<td><?php echo $AppUI->_("First Name"); ?></td>
			<td><?php echo $AppUI->_("Last Name"); ?></td>
			<td><?php echo $AppUI->_("Title"); ?></td>
			<td><?php echo $AppUI->_("Birthday"); ?></td>
			<td><?php echo $AppUI->_("Company"); ?></td>
      <td><?php echo $AppUI->_("Company Phone"); ?></td>
      <td><?php echo $AppUI->_("Type"); ?></td>
			<td><?php echo $AppUI->_("Email"); ?></td>
			<td><?php echo $AppUI->_("Email"); ?>2</td>
			<td><?php echo $AppUI->_("Phone"); ?></td>
			<td><?php echo $AppUI->_("Phone"); ?>2</td>
			<td><?php echo $AppUI->_("Mobile Phone"); ?></td>
			<td><?php echo $AppUI->_("Address"); ?></td>
			<td><?php echo $AppUI->_("Address"); ?>2</td>
			<td><?php echo $AppUI->_("City"); ?></td>
			<td><?php echo $AppUI->_("State"); ?></td>
			<td><?php echo $AppUI->_("Zip"); ?></td>
			<td><?php echo $AppUI->_("Country"); ?></td>
			<td><?php echo $AppUI->_("Contact Notes"); ?></td>
			<td><?php echo $AppUI->_("Business Phone"); ?></td>
			<td><?php echo $AppUI->_("Business Phone"); ?>2</td>
			<td><?php echo $AppUI->_("Fax"); ?></td>
      <td><?php echo $AppUI->_("IM"); ?></td>
			<td><?php echo $AppUI->_("Web Site address"); ?></td>
			<td><?php echo $AppUI->_("Department"); ?></td>
			<td><?php echo $AppUI->_("Manager's name"); ?></td>
			<td><?php echo $AppUI->_("Assistant's name"); ?></td>
		</tr>
	<?php	
	$df = $AppUI->getPref('SHDATEFORMAT');
	foreach ($contacts as $c)
	{
		$contact = new CContact();
		$contact->load( $c["contact_id"] );
		?>
		<tr>
			<td><?php echo $contact->contact_first_name;?></td>
			<td><?php echo $contact->contact_last_name;?></td>			
			<td><?php echo ($contact->contact_title ? $contact->contact_title : "&nbsp;"); ?></td>
		<?php $bd = new CDate($contact->contact_birthday); ?>
			<td><?php echo ($contact->contact_birthday ? $bd->format($df) : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_company ? $contact->contact_company : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_company_phone ? $contact->contact_company_phone : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_type ? $contact->contact_type : "&nbsp;"); ?></td>
      <td><?php echo ($contact->contact_email ? $contact->contact_email : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_email2 ? $contact->contact_email2 : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_phone ? $contact->contact_phone : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_phone2 ? $contact->contact_phone2 : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_mobile ? $contact->contact_mobile : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_address1 ? $contact->contact_address1 : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_address2 ? $contact->contact_address2 : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_city ? $contact->contact_city : "&nbsp;"); ?></td>
			<td><?php echo ($contact->state_name ? $contact->state_name : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_zip ? $contact->contact_zip : "&nbsp;"); ?></td>
			<td><?php echo ($contact->country_name ? $contact->country_name : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_notes ? $contact->contact_notes : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_business_phone ? $contact->contact_business_phone : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_business_phone2 ? $contact->contact_business_phone2 : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_fax ? $contact->contact_fax : "&nbsp;"); ?></td>
      <td><?php echo ($contact->contact_icq ? $contact->contact_icq : "&nbsp;"); ?></td>
      <td><?php echo ($contact->contact_website ? $contact->contact_website : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_department ? $contact->contact_department : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_manager ? $contact->contact_manager : "&nbsp;"); ?></td>
			<td><?php echo ($contact->contact_assistant ? $contact->contact_assistant : "&nbsp;"); ?></td>
            <?php
	}
	?>
	</table>
     </body>
	<?php

}

function exportToHTML( $contacts )
{	
	GLOBAL $AppUI, $usr;
	
	header( 'Content-Disposition: attachment; filename="contacts.html" ' );
	exportToTable( $contacts );	
	exit;
}

function exportToCSV( $contacts )
{	
	GLOBAL $AppUI, $usr;
	
	header( 'Content-type: text/comma-separated-values' );
	header( 'Content-Disposition: attachment; filename="contacts.csv"' );
	
	//Primero hay que guardar los nombres de los campos
    require_once("mapeo_outlook_express_es.php");
		$contenido = $mapeo["contact_first_name"].",";
		$contenido .= $mapeo["contact_last_name"].",";
		$contenido .= $mapeo["contact_title"].",";
		$contenido .= $mapeo["contact_birthday"].",";
		$contenido .= $mapeo["contact_company"].",";
		$contenido .= $mapeo["contact_type"].",";
		$contenido .= $mapeo["contact_email"].",";
		$contenido .= $mapeo["contact_email2"].",";
		$contenido .= $mapeo["contact_phone"].",";
		$contenido .= $mapeo["contact_phone2"].",";
		$contenido .= $mapeo["contact_mobile"].",";
		$contenido .= $mapeo["contact_address1"].",";
		$contenido .= $mapeo["contact_address2"].",";
		$contenido .= $mapeo["contact_city"].",";
		$contenido .= $mapeo["contact_state"].",";
		$contenido .= $mapeo["contact_zip"].",";
		$contenido .= $mapeo["contact_country"].",";
		$contenido .= $mapeo["contact_notes"].",";
		$contenido .= $mapeo["contact_business_phone"].",";
		$contenido .= $mapeo["contact_business_phone2"].",";
		$contenido .= $mapeo["contact_fax"].",";
		$contenido .= $mapeo["contact_website"].",";
		$contenido .= $mapeo["contact_department"].",";
		$contenido .= $mapeo["contact_manager"].",";
		$contenido .= $mapeo["contact_assistant"].",";
		$contenido .= "\r\n";
		
    $df = $AppUI->getPref('SHDATEFORMAT');
	foreach ($contacts as $c)
	{
		$contact = new CContact();
		$contact->load( $c["contact_id"] );
		
		$contenido .= prepareCsvField($contact->contact_first_name).",";
		$contenido .= prepareCsvField($contact->contact_last_name).",";
		$contenido .= prepareCsvField($contact->contact_title).",";
		$bd = $contact->contact_birthday ? new CDate($contact->contact_birthday) : null;
		$contenido .= prepareCsvField(($bd ? $bd->format($df) : "")).",";
		$contenido .= prepareCsvField($contact->contact_company).",";
		$contenido .= prepareCsvField($contact->contact_type).",";
		$contenido .= prepareCsvField($contact->contact_email).",";
		$contenido .= prepareCsvField($contact->contact_email2).",";
		$contenido .= prepareCsvField($contact->contact_phone).",";
		$contenido .= prepareCsvField($contact->contact_phone2).",";
		$contenido .= prepareCsvField($contact->contact_mobile).",";
		$contenido .= prepareCsvField($contact->contact_address1).",";
		$contenido .= prepareCsvField($contact->contact_address2).",";
		$contenido .= prepareCsvField($contact->contact_city).",";
		$contenido .= prepareCsvField($contact->state_name).",";
		$contenido .= prepareCsvField($contact->contact_zip).",";
		$contenido .= prepareCsvField($contact->country_name).",";
		$contenido .= prepareCsvField($contact->contact_notes).",";
		$contenido .= prepareCsvField($contact->contact_business_phone).",";
		$contenido .= prepareCsvField($contact->contact_business_phone2).",";
		$contenido .= prepareCsvField($contact->contact_fax).",";
		$contenido .= prepareCsvField($contact->contact_website).",";
		$contenido .= prepareCsvField($contact->contact_department).",";
		$contenido .= prepareCsvField($contact->contact_manager).",";
		$contenido .= prepareCsvField($contact->contact_assistant).",";
		$contenido .= "\r\n";
	}
	echo $contenido;
	exit;
}

function exportToXLS( $contacts )
{	
	GLOBAL $AppUI, $usr;
	
	header( 'Content-type: application/ms-excel' );
	header( 'Content-Disposition: attachment; filename="contacts.xls"' );
	
	exportToTable( $contacts );
	exit;
}

function exportToOutlook( $contacts )
{
	header( 'Content-type: text/comma-separated-values' );
	header( 'Content-Disposition: attachment; filename="contacts_ol.txt"' );
	@require_once("mapeo_outlook.php");
	
	foreach( $mapeo as $k=>$v )
	{
		echo $v."\t";
	}
	echo "\r\n";
	foreach ( $contacts as $c )
	{
		$contact = new CContact();
		$contact->load( $c["contact_id"] );
		
		foreach ( $mapeo as $k=>$v )
		{
			echo $contact->$k."\t";
		}
		echo "\r\n";
	}
	exit;
}

function prepareCsvField($value){
	/* the bat char code export */
	/*
	$charscode = array(
	'"'=>'\\22',
	","=>'\\2C',
	chr(13)=>"\\0D",
	chr(10)=>"\\0A",
	"\\"=>"\\5C")
	;
	
	*/
	$charscode = array(
	'"'=>"'",
	chr(13)=>" ",
	chr(10)=>" ");	
	return strtr($value, $charscode);

}

?>