<?php /* TASKS $Id: mysheets.php,v 1.5 2009-07-13 16:12:36 nnimis Exp $ */
global $timexp_type;

// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpLstTSTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpLstTSTab' ) !== NULL ? $AppUI->getState( 'TxpLstTSTab' ) : 0;

if (isset( $_GET['project'] )) {
	$AppUI->setState( 'TxpLstTSproject', $_GET['project'] );
}
$project = $AppUI->getState( 'TxpLstTSproject' ) !== NULL ? $AppUI->getState( 'TxpLstTSproject' ) : -1;
if (isset( $_GET['status'] )) {
	$AppUI->setState( 'TxpLstTSstatus', $_GET['status'] );
}
$status = $AppUI->getState( 'TxpLstTSstatus' ) !== NULL ? $AppUI->getState( 'TxpLstTSstatus' ) : -1;

$tmpPrj = new CProject();

$allowed_projects = CTimesheet::getMyTimesheetProjects();
// $tmpPrj->getAllowedRecords($AppUI->user_id,"project_id, project_name");

natcasesort($allowed_projects);
$allowed_projects = arrayMerge(array("-1"=>$AppUI->_("All Projects")), $allowed_projects);

natcasesort($timexp_status);
$supervised_status = arrayMerge(array("-1"=>$AppUI->_("All Status")), $timexp_status);

$cbo_projects = arraySelect($allowed_projects, 'project','size="1" class="text" onchange="javascript: this.form.submit();"', $project,'',false );

$cbo_status = arraySelect($supervised_status, 'status','size="1" class="text" onchange="javascript: this.form.submit();"', $status, true,false );


$filters = $cbo_projects."&nbsp;";
$filters .= $cbo_status;


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
$titleBlock = new CTitleBlock( 'My Sheets', 'timexp.gif', $m, "timexp.index" );
$titleBlock->addCell();
$titleBlock->addCell(
	$filters, '',
	'<form action="" method="get">
	<input type="hidden" name="m" value="timexp" />
	<input type="hidden" name="a" value="'.$a.'" />
	', '</form>'
);
$titleBlock->addCell(
		$addtime."&nbsp;".$addexpense."&nbsp;".$addlicense, '',
		'', ''
);
$titleBlock->addCrumb("?m=timexp&a=vw_myday", "my daily view");
if ($canSupervise){
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");	
	$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");
}

$titleBlock->show();


// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
$tabBox->add( 'vw_mysheets_times', 'Times' );
$tabBox->add( 'vw_mysheets_expenses', 'Expenses' );
$tabBox->add( 'vw_mysheets_licenses', 'Licenses' );
$tabBox->add( 'vw_mysheets_all', 'All' );
$tabBox->show();



$AppUI->savePlace();
?>