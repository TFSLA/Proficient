<?php /* TASKS $Id: suptimesheets.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
global $timexp_status, $timexp_type;


// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpLstTSSTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpLstTSSTab' ) !== NULL ? $AppUI->getState( 'TxpLstTSSTab' ) : 0;

if (isset( $_GET['project'] )) {
	$AppUI->setState( 'TxpLstTSSproject', $_GET['project'] );
}
$project = $AppUI->getState( 'TxpLstTSSproject' ) !== NULL ? $AppUI->getState( 'TxpLstTSSproject' ) : -1;

if (isset( $_GET['user'] )) {
	$AppUI->setState( 'TxpLstTSSuser', $_GET['user'] );
}
$user = $AppUI->getState( 'TxpLstTSSuser' ) !== NULL ? $AppUI->getState( 'TxpLstTSSuser' ) : -1;

if (isset( $_GET['status'] )) {
	$AppUI->setState( 'TxpLstTSSstatus', $_GET['status'] );
}
$status = $AppUI->getState( 'TxpLstTSSstatus' ) !== NULL ? $AppUI->getState( 'TxpLstTSSstatus' ) : 1;


$supervised_users = CTimexpSupervisor::getSupervisedUsers();

natcasesort($supervised_users);
$supervised_users = arrayMerge(array("-1"=>$AppUI->_("All Users")), $supervised_users);

$supervised_projects = CTimesheet::getSupervisedProjects();

natcasesort($supervised_projects);
$supervised_projects = arrayMerge(array("-1"=>$AppUI->_("All Projects")), $supervised_projects);

natcasesort($timexp_status);
$supervised_status = arrayMerge(array("-1"=>$AppUI->_("All Status")), $timexp_status);
unset ($supervised_status[0]);

$cbo_projects = arraySelect($supervised_projects, 'project','size="1" class="text" onchange="javascript: this.form.submit();"', $project,'',false );
$cbo_users = arraySelect($supervised_users, 'user','size="1" class="text" onchange="javascript: this.form.submit();"', $user,'',false );
$cbo_status = arraySelect($supervised_status, 'status','size="1" class="text" onchange="javascript: this.form.submit();"', $status, true,false );


$filters = $cbo_projects."&nbsp;";
$filters .= $cbo_users."&nbsp;";
$filters .= $cbo_status;

// setup the title block
$titleBlock = new CTitleBlock( 'Sheets Supervision', 'timexp.gif', $m, "timexp.index" );
$titleBlock->addCell();
$titleBlock->addCell(
	$filters, '',
	'<form action="" method="get">
	<input type="hidden" name="m" value="timexp" />
	<input type="hidden" name="a" value="'.$a.'" />
	', '</form>'
);
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb("?m=timexp&a=vw_myday", "my daily view");
$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");

include_once('./modules/public/itemToFavorite_functions.php');
$deleteFavorite = HasItemInFavorites(0, 5);

$titleBlock->addCrumb( "javascript:itemToFavorite(0, 5, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('remove from favorites') : $AppUI->_('add to favorites') );

$titleBlock->show();

// include the re-usable sub view


// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
$tabBox->add( 'vw_supsheets_times', 'Times' );
$tabBox->add( 'vw_supsheets_expenses', 'Expenses' );
$tabBox->add( 'vw_supsheets_licenses', 'Licenses' );
$tabBox->add( 'vw_supsheets_all', 'All' );
$tabBox->show();

$AppUI->savePlace();
?>

<script language="javascript">
	function itemToFavorite(item_id, item_type, item_delete)
	{
		window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
	}
</script>