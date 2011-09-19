<?php /* CALENDAR $Id: do_process_inv.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$inv = intval(dPgetParam( $_GET, "inv", "" ));
$rta = dPgetParam( $_GET, "rta", "");

if ( $inv == 0 || ($rta != "A" && $rta != "R") )
{
	//El link vino mal formado
	$AppUI->redirect( "m=public&a=access_denied" );
}

require_once( $AppUI->getModuleClass( "calendar" ) );
$i = new CEventInvitation();
$i->load( $inv );
if ( !$i )
{
	$AppUI->setMsg("The invitation doesn't exist");
	$AppUI->redirect();
}
if ( $i->invitation_status )
{
	$AppUI->setMsg("The invitation has allready been answered");
	$AppUI->redirect();
}
$i->invitation_status = ($rta == "A" ? "ACCEPTED" : "REJECTED" );
$i->store();
echo "<p>".$AppUI->_("Your data has been received")."</p>";
?>