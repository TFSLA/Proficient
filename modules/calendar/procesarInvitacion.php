<?php /* CALENDAR $Id: procesarInvitacion.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$inv = intval(dPgetParam( $_GET, "inv", "" ));
$rta = dPgetParam( $_GET, "rta", "");

if ( $inv == 0 || $rta == "" )
{
	//El link vino mal formado
}
?>