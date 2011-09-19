<?php
global $AppUI, $drow, $tab;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$ttl = $id > 0 ? "Edit Job" : "New Job";

require_once("./modules/companies/companies.class.php");

$sql = "
	SELECT *
	FROM hhrr_jobs
	WHERE job_id = $id";

if (!db_loadHash( $sql, $drow ) && $id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Job ID', 'hhrr.gif', $m, 'hhrr.index' );
	$titleBlock->addCrumb( "?m=hhrr", "Human Resources" );
	$titleBlock->addCrumb( "?m=hhrr&tab=2", "Jobs List" );
	$titleBlock->show();
} else {
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->addCrumb( "?m=hhrr&tab=2", strtolower($AppUI->_('Jobs List')) );
	$titleBlock->show();
	
	$tab = $_GET["tab"] !== NULL ? $_GET["tab"] : 0;
	
	$tabBox = new CTabBox( "?m=hhrr&a=addeditrole&id=$id", "{$AppUI->cfg['root_dir']}/modules/hhrr/", $tab );
	$tabBox->add( 'addeditrole_data', 'Job Data', TRUE );
	if($id > 0)
		$tabBox->add( 'addedituserskills', 'Job Matrix', TRUE );
	$tabBox->show();
}
?>