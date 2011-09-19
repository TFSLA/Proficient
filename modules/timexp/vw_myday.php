<?php /* TASKS $Id: vw_myday.php,v 1.5 2009-07-13 16:12:36 nnimis Exp $ */
global $timexp_type, $canSupervise;

// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpLstDayTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpLstDayTab' ) !== NULL ? $AppUI->getState( 'TxpLstDayTab' ) : 0;

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$canSupervise = count($supervised_users) > 0;


$addtime = '<table height="1">
    <tr>
    	<td>
    	<form action="index.php?m=timexp&a=addtime" method="POST"><td>
    	<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new time').'"';
//$addtime .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=addtime&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );\"";
$addtime .= ' /></td></form>
    	<form action="index.php?m=timexp&a=addeditexpense" method="POST"><td>';
$addexpense = '<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new expense').'"';
$addexpense .= ' /></td></form><td>
		<form action="index.php?m=timexp&a=new_license" method="POST"><td>';

$addlicense .= '<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new license').'"';
//$addlicense .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=new_license&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );\"";
$addlicense .= ' /></td></form></td></tr></table>';

// setup the title block
$titleBlock = new CTitleBlock( 'Detailed View', 'timexp.gif', $m, "timexp.index" );
$titleBlock->addCell();
$titleBlock->addCell(
		$addtime."&nbsp;".$addexpense."&nbsp;".$addlicense, '',
		'', ''
);
//$titleBlock->addCrumb("?m=timexp&a=vw_myweek", "my weekly view");
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
if ($canSupervise){
	//$titleBlock->addCrumb("?m=timexp&a=vw_sup_week", "weekly supervision");
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");
	$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");
}

include_once('./modules/public/itemToFavorite_functions.php');
$deleteFavorite = HasItemInFavorites(0, 6);

$titleBlock->addCrumb( "javascript:itemToFavorite(0, 6, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('remove from favorites') : $AppUI->_('add to favorites') );

$titleBlock->show();

// include the re-usable sub view


// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
$tabBox->add( 'vw_daytimes', 'Times' );
$tabBox->add( 'vw_dayexpenses', 'Expenses' );
$tabBox->add( 'vw_daylicense', 'Licenses' );
$tabBox->show();



$AppUI->savePlace();
?>
<script language="javascript">
	function itemToFavorite(item_id, item_type, item_delete)
	{
		window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
	}
</script>