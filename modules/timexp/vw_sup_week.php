<?
global  $timexp_type, $supervise_user;

// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpSupWkTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpSupWkTab' ) !== NULL ? $AppUI->getState( 'TxpSupWkTab' ) : 0;

$users = CTimexpSupervisor::getSupervisedUsers();

if (!count($users)){
		$AppUI->setMsg( "There is no users under your supervision", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

$user_ids=array_keys($users);

// retrieve any status parameters
if (isset( $_POST['sup_user'] )) {
	$AppUI->setState( 'TxpSupUsr', $_POST['sup_user'] );
	//al cambiar de usuario supervisado borro cookies anteriores
	$AppUI->setState( 'TxpLstWD1', NULL);
	$AppUI->setState( 'TxpLstWD2', NULL);
}
$supervise_user = $AppUI->getState( 'TxpSupUsr' ) !== NULL ? $AppUI->getState( 'TxpSupUsr' ) : $user_ids[0];
// seteo el modo supervisor
$spvMode = true;

$select_user = $AppUI->_("Supervise User").": ".arraySelect( $users, "sup_user", 'size="1" class="text" onchange="javascript: this.form.submit();"', $supervise_user, false );


// setup the title block
$titleBlock = new CTitleBlock( $AppUI->_('Weekly Supervision') ." - ".$users[$supervise_user], 'timexp.gif', $m, "$m.$a" );
$titleBlock->addCell();
/*
$titleBlock->addCell(
		$select_user, '',
		'<form action="" method="post">', '</form>'
);
*/
$titleBlock->addCrumbRight( $select_user, '', '<form action="" method="post">', '</form>' );
$titleBlock->addCrumb("?m=timexp&a=vw_myweek", "my weekly view");
$titleBlock->addCrumb("?m=timexp&a=vw_myday", "my daily view");
$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");
$titleBlock->show();

// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
$tabBox->add( 'vw_weektimes', 'Times' );
$tabBox->add( 'vw_weekexpenses', 'Expenses' );
$tabBox->add( 'vw_weekall', 'All' );
$tabBox->show();
$AppUI->savePlace();
?>
