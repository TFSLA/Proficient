<?php /* TASKS $Id: vw_myweek.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
global $timexp_type, $canSupervise;

// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpLstTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpLstTab' ) !== NULL ? $AppUI->getState( 'TxpLstTab' ) : 0;

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$canSupervise = count($supervised_users) > 0;


$addtime = '<input type="button" class="button" value="'.$AppUI->_('new time').'"';
$addtime .= " onclick=\"javascript:window.location = './index.php?m=timexp&a=addedittime';\"";
$addtime .= '" />';

$addexpense = '<input type="button" class="button" value="'.$AppUI->_('new expense').'"';
$addexpense .= " onclick=\"javascript:window.location='./index.php?m=timexp&a=addeditexpense';\"";
$addexpense .= '" />';

// setup the title block
$titleBlock = new CTitleBlock( 'Weekly View', 'timexp.jpg', $m, "$m.$a" );
$titleBlock->addCell();
$titleBlock->addCell(
		$addtime."&nbsp;".$addexpense, '',
		'', ''
);
$titleBlock->addCrumb("?m=timexp&a=vw_myday", "my daily view");
if ($canSupervise){
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_week", "weekly supervision");
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");
}
$titleBlock->show();

// include the re-usable sub view


// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
$tabBox->add( 'vw_weektimes', 'Times' );
$tabBox->add( 'vw_weekexpenses', 'Expenses' );
$tabBox->add( 'vw_weekall', 'All' );
$tabBox->show();



$AppUI->savePlace();
?>