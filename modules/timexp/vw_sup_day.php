<?
global  $timexp_type, $supervise_user;

// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpSupDyTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpSupDyTab' ) !== NULL ? $AppUI->getState( 'TxpSupDyTab' ) : 0;

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
	$AppUI->setState( 'TxpLstDD1', NULL);
	$AppUI->setState( 'TxpLstDD2', NULL);
	$AppUI->setState( 'TxpLstDP1', NULL);
	$AppUI->setState( 'TxpLstDP2', NULL);
}


// setup the title block
$titleBlock = new CTitleBlock( $AppUI->_('Daily Supervision'), 'timexp.gif', $m, "timexp.index" );
$titleBlock->addCell();

//$titleBlock->addCrumbRight( $select_user, '', '<form action="" method="post">', '</form>' );
//$titleBlock->addCrumb("?m=timexp&a=vw_myweek", "my weekly view");
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb("?m=timexp&a=vw_myday", "my daily view");
//$titleBlock->addCrumb("?m=timexp&a=vw_sup_week", "weekly supervision");


$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");


$titleBlock->show();
IF ($_POST['sup_user']!='') $sup_user=$_POST['sup_user'];
ELSEIF ($_GET['sup_user']!='') $sup_user=$_GET['sup_user'];

?>
<table width="650" border="0" cellpadding="2" cellspacing="0" >
<tr class="tableForm_bg">
<form action="" method="POST">
	<th colspan="10"><strong><?php

		echo $AppUI->_("Supervised User").": ";
		$supervised_users = CTimexpSupervisor::getSupervisedUsers();

		echo "<select name='sup_user' size='1' class='text' onchange='javascript: this.form.submit(); '>";
	    echo "<option value='0'>".$AppUI->_("All Users")."</option>\n";
        
		foreach ($supervised_users as $key => $val) {
		if ($sup_user== $key) $sel='selected';
		else $sel='';
		echo "<option value='".$key."' $sel>".$val."</option>\n";
	    }

		echo "</select>";

		?></strong></th>
</form>
</tr>
</table>




<?php
global $disable_edition;
$disable_edition = true;

// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
$tabBox->add( 'vw_daytimes', 'Times' );
$tabBox->add( 'vw_dayexpenses', 'Expenses' );
$tabBox->add( 'vw_daylicense', 'Licenses' );
//$tabBox->add( 'vw_dayall', 'All' );
$tabBox->show();
$AppUI->savePlace();

?>
