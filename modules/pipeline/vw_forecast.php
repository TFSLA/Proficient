<?php /* COMPANIES $Id: vw_forecast.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $canEdit, $delegator_id, $dialog, $permisos, $canAdd;

@require_once( "showrowlead.php" );

$s = '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<tr class="tableHeaderGral">';
require_once( $AppUI->getModuleClass( "admin" ) );
$usr = new CUser();
if ( !$usr->load($delegator_id) )
{
	$AppUI->setMsg( "User" );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$s .= '<th width="45" nowrap>&nbsp;</th>';
$s .= '<th valign="middle" align="left" nowrap>'.$AppUI->_( 'Code' ).'</th>';
$s .= '<th valign="middle" align="left" nowrap>'.$AppUI->_( 'Account Manager' ).'</th>';
$s .= '<th valign="middle" align="left" width="250">'.$AppUI->_( 'Name' ).'</th>';
$s .= '<th valign="middle" align="left" nowrap>'.$AppUI->_( 'Project Type' ).'</th>';
$s .= '<th valign="middle" align="left" width="80">'.$AppUI->_( 'Total Income' ).'</th>';
$s .= '<th valign="middle" align="left" width="50">'.$AppUI->_( 'Probability of Winning' ).'</th>';
$s .= '<th width="70" valign="top" nowrap>'.$AppUI->_( 'Close date' ).'</th>';

if(!$delegator_id || ($delegator_id && $AppUI->user_id == $delegator_id))
{
	$usr->load( $AppUI->user_id );
	$delegs = $usr->getDelegators();

	foreach( $delegs as $deleg )
	{
		if ( !count( $rows) )
			$rows = $usr->getSalesPipelines($deleg["delegator_id"], "status in ('Opportunity', 'On Hold', 'Negotiation', 'Decision')" );
		else
			$rows = array_merge($usr->getSalesPipelines($deleg["delegator_id"], "status in ('Opportunity', 'On Hold', 'Negotiation', 'Decision')" ), $rows);
	}
}

if ( !count( $rows) )
	$rows = $usr->getSalesPipelines( 0, "status in ('Opportunity', 'On Hold', 'Negotiation', 'Decision')" );
else
	$rows = array_merge($usr->getSalesPipelines( 0, "status in ('Opportunity', 'On Hold', 'Negotiation', 'Decision')" ), $rows);

if ( !count( $rows) )
{
    $s .= "</tr><tr><td colspan=97>";
    $s .= $AppUI->_('No data available');
    $s .= "</td>";
}
/*
$s .= '<!--td nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canAdd) {
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new lead' ).'" onClick="javascript:window.location=\'./index.php?m=pipeline&a=addedit&type=Opportunity&delegator_id='.$delegator_id.'&dialog='.$dialog.'\';">';
}
$s .= '</td-->';
*/
$s .= '</tr>';
echo $s;

foreach ($rows as $row) 
{
	showrowlead( $row );
}
echo '</table>';
?>
