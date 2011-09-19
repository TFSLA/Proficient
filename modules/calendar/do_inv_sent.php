<?php /* CALENDAR $Id: do_inv_sent.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$msg = '';
//$AppUI->setMsg( 'Invitation' );
$event_id = dpGetParam($_GET, "event_id");
require_once( "./includes/config.php" );

require_once( $AppUI->getModuleClass( "admin" ) );
require_once( $AppUI->getModuleClass( "contacts" ) );

$event = new CEvent();
if (!$event->load( $event_id ) && $event_id)
{
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$host = new CUser();
$host->load( $event->event_owner );

$invitaciones = $event->getInvitations();

foreach( $invitaciones as $inv )
{
	if($inv["invitation_sent"] == 0)
	{
		$objInv = new CEventInvitation();
		$objInv->load( $inv["invitation_id"] );
	
		if($inv["user_id"])
		{
			$user = new CUser();
			$user->load( $inv["user_id"] );
			
			$to = $user->user_email;
		}
		else if($inv["contact_id"])
		{
			$contact = new CContact();
			$contact->load( $inv["contact_id"] );
				
			$to = $contact->contact_email;
		}
		else
		{
			$to = $inv["invitation_mail"];
		}

		$objInv->invitation_sent = 1;
		$objInv->store();		
		enviarMail( $to, $host, $objInv->invitation_hash, $event_id );
	}
}

$tmpUrl = "m=calendar&a=view&event_id=$event_id&delegator_id=".$delegator_id."&dialog=".$dialog;
$AppUI->redirect($tmpUrl);

function enviarMail( $to, $host, $inv, $event_id)
{
	$sql="SELECT event_title, event_start_date, event_location, event_description FROM events WHERE event_id=$event_id";
	$rc=db_exec($sql);
	$vec=db_fetch_array($rc);
	global $AppUI;	
	$message = $AppUI->_("Title").": ".$vec['event_title']."<br>\r\n";
	
	$dateEmail = new CDate($vec['event_start_date']);
	
	
	$message .= $AppUI->_("Date").": ".$dateEmail->format("%d/%m/%y %H:%M:%S")."<br>\r\n";
	$message .= $AppUI->_("Location").": ".$vec['event_location']."<br>\r\n";
	$message .= $AppUI->_("Description").": ".$vec['event_description']."<br>\r\n";
	$message .= $AppUI->_("automaticallyinvited");
	$message .= " ".$host->user_first_name." ".$host->user_last_name." ";
	$message .= $AppUI->_("to participate in the event")."<br><br>";
	
	$procesarInvitacion = "modules/public/invitation.php";
	$id = "&invitation=$inv";
	$linkAceptar = $AppUI->getConfig( "base_url" )."/$procesarInvitacion?action=accept".$id;
	$linkRechazar = $AppUI->getConfig( "base_url" )."/$procesarInvitacion?action=reject".$id;	
	
	$message .= $AppUI->_("In order to accept the invitation click").' <a href="'.$linkAceptar.'">'.$AppUI->_("here")."</a><br>\r\n";
	$message .= $AppUI->_("In order to reject the invitation click").' <a href="'.$linkRechazar.'">'.$AppUI->_("here")."</a><br>\r\n";
		
	$cabeceras  = "MIME-Version: 1.0\r\n";
	$cabeceras .= "Content-type: text/html; charset=iso-8859-1\r\n";

	/* cabeceras adicionales */
	$cabeceras .= "From: $host->user_first_name $host->user_last_name <$host->user_email>\r\n";	
	
	echo($message);
	echo("</br>");
	echo("</br>");
	echo("</br>");
	echo("</br>");
	
	mail( $to, $vec['event_title'], $message, $cabeceras);
}

?>