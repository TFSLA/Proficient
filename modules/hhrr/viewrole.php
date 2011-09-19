<?php
global $canEditHHRR;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$ttl = $id > 0 ? "Edit Job" : "New Job";

require_once("./modules/companies/companies.class.php");

$sql = "
	SELECT *
	FROM hhrr_jobs
	WHERE job_id = $id
";

if (!db_loadHash( $sql, $drow ) && $id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Job ID', 'hhrr.gif', $m, 'hhrr.index' );
	$titleBlock->addCrumb( "?m=hhrr", "Human Resources" );
	$titleBlock->show();
} else {
	$tab = $_GET["tab"] !== NULL ? $_GET["tab"] : 0;
	
	$titleBlock = new CTitleBlock( 'New Job', 'hhrr.gif', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->addCrumb( "?m=hhrr&tab=2", strtolower($AppUI->_('Jobs List')) );
	$titleBlock->addCrumb( "?m=hhrr&a=addeditrole&id=$id&tab=$tab", strtolower($AppUI->_('Edit Job')) );
	$titleBlock->show();
	
	$tabBox = new CTabBox( "?m=hhrr&a=viewrole&id=$id", "{$AppUI->cfg['root_dir']}/modules/hhrr/", $tab );
	$tabBox->add( 'viewrole_data', 'Job Data', TRUE );
	$tabBox->add( 'viewskills', 'Job Matrix', TRUE );
	$tabBox->show();
}
?>