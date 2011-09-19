<?php /* CALENDAR $Id: do_inv_add.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$msg = '';
//$AppUI->setMsg( 'Invitation' );
$event_id = dpGetParam($_POST, "event_id");
$usuarios = dpGetParam($_POST, "usuarios");
$contactos = dpGetParam($_POST, "contactos");
$delegator_id = dpGetParam( $_GET, "delegator_id", $AppUI->user_id );
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );
$mod_id = 4;
require_once( "./includes/config.php" );

//echo "<pre>";

require_once( $AppUI->getModuleClass( "admin" ) );

$ev = new CEvent();
if (!$ev->load( $event_id ) && $event_id) {
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
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
	$canEdit = ( $permiso == "AUTHOR" && $ev->event_creator == $AppUI->user_id && $ev->event_owner == $delegator_id );
	$canEdit = $canEdit || ( $permiso == "EDITOR" && $ev->event_owner == $delegator_id );	
	$canEdit = $canEdit || $AppUI->user_type == 1;
}

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}


$host = new CUser();
$host->load( $ev->event_owner );

$sql = "DELETE FROM events_invitations WHERE event_id = ".$event_id." AND invitation_sent = 0";

if (!db_exec( $sql ))
{
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalid delete pendings", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($usuarios != "" )
{
	$users = explode(";", $usuarios);
	foreach( $users as $u )
	{
		if (strlen(trim($u))){
			$obj = new CEventInvitation();
			$obj->user_id = $u;
            $obj->contact_id = 0;
			$obj->event_id = $event_id;
			$obj->invitation_sent = 0;
			$obj->invitation_locale = $AppUI->user_locale;
			if ( ( $msg = $obj->store() ) )
			{
				$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
				$AppUI->redirect();
			}
			/*
			else
			{
				$usr = new CUser();
				if ( !$usr->load( $u ) )
				{
					$AppUI->setMsg( "User" );
					$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
					$AppUI->redirect();
				}
				else
				{
					//enviarMail( $usr->user_email, $ev, $host, $obj->invitation_hash );
					$AppUI->setMsg( 'added', UI_MSG_OK, true );
				}
			}
			*/
		}
	}
}

if($contactos != ""){
    require_once( $AppUI->getModuleClass( "contacts" ) );
    $contacts = explode(";", $contactos);
    $date = date("Y-m-d h:i:s");
    foreach($contacts as $c){
        if (strlen(trim($c))){
            $obj = new CEventInvitation();
            $obj->contact_id = $c;
            $obj->event_id = $event_id;
            $obj->invitation_sent = 0;
            $obj->invitation_locale = $AppUI->user_locale;
            if ( ( $msg = $obj->store() ) )
            {
                $AppUI->setMsg( $msg, UI_MSG_ERROR, true );
                $AppUI->redirect();
            }
            $sql = "INSERT INTO contacts_relations (contact_id, relation_type_id, date, relation_creator, relation_type) VALUES (
            	$c, $event_id, '$date', $AppUI->user_id, 'event')";
            
            mysql_query($sql);
            if (mysql_error())
            {
                $AppUI->setMsg( mysql_error(), UI_MSG_ERROR, true );
                $AppUI->redirect();
            }
            /*
            else
            {
                $con = new CContact();
                if ( !$con->load( $c ) )
                {
                    $AppUI->setMsg( "Contact" );
                    $AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
                    $AppUI->redirect();
                }
                else
                {
                    //enviarMail( $con->contact_email, $ev, $host, $obj->invitation_hash, $event_id );
                    $AppUI->setMsg( 'added', UI_MSG_OK, true );
                }
            }
            */
        }
    }
}

$mails = dpGetParam($_POST, "mails");
if ( $mails != "" )
{
	$emails = explode(";", $mails);	
	foreach( $emails as $e )
	{
		if (strlen(trim($e))){
			$obj = new CEventInvitation();
			
			$obj->event_id = $event_id;
			$obj->invitation_sent = 0;
			$obj->invitation_locale = $AppUI->user_locale;
			$obj->invitation_mail = $e;
			
			if ( ( $msg = $obj->store() ) )
			{
				$AppUI->setMsg( $msg, UI_MSG_ERROR, true );	
				$AppUI->redirect();
			}
			/*
			else
			{
				//$obj->invitation_hash = randomString ($charSet, 32, strval( $obj->invitation_id ) );
				//enviarMail( $e, $ev, $host, $obj->invitation_hash, $event_id );
				$AppUI->setMsg( 'added', UI_MSG_OK, true );			
			}
			*/
		}
	}
}
/*echo "<p>dialog = $dialog</p>";*/
//echo "<pre>";

//echo "redireccionando a "."m=calendar&a=inv_addedit&event_id=$event_id&delegator_id=".$delegator_id."&dialog=".$dialog;
$tmpUrl = "m=calendar&a=inv_addedit&event_id=$event_id&delegator_id=".$delegator_id."&dialog=".$dialog;
//exit();
$AppUI->redirect($tmpUrl);

?>
