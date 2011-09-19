<?php /* PROJECTS $Id: reports.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
error_reporting( E_ALL );

$project_id = intval( dPgetParam( $_REQUEST, "project_id", 0 ) );
$report_type = dPgetParam( $_REQUEST, "report_type", '' );

// check permissions for this record
$canRead = !getDenyRead( $m, $project_id );

// funci�n deshabilitada, comente la siguiente linea para habilitarla
$canRead =  false;
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$reports = $AppUI->readFiles( $AppUI->getConfig( 'root_dir' )."/modules/projects/reports", "\.php$" );

// setup the title block
$titleBlock = new CTitleBlock( 'Project Reports', 'projects.jpg', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=projects", "projects list" );
$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view this project" );
if ($report_type) {
	$titleBlock->addCrumb( "?m=projects&a=reports&project_id=$project_id", "reports index" );
}
$titleBlock->show();

if ($report_type) {
	$report_type = $AppUI->checkFileName( $report_type );
	$report_type = str_replace( ' ', '_', $report_type );
	require( $AppUI->getConfig( 'root_dir' )."/modules/projects/reports/$report_type.php" );
} else {
	echo "<table>";
	echo "<tr><td><h2>" . $AppUI->_( 'Reports Available' ) . "</h2></td></tr>";
	foreach ($reports as $v) {
		$type = str_replace( ".php", "", $v );
		$desc_file = str_replace( ".php", ".$AppUI->user_locale.txt", $v );
		$desc = @file( $AppUI->getConfig( 'root_dir' )."/modules/projects/reports/$desc_file" );

		echo "\n<tr>";
		echo "\n	<td><a href=\"index.php?m=projects&a=reports&project_id=$project_id&report_type=$type\">";
		echo @$desc[0] ? $desc[0] : $v;
		echo "</a>";
		echo "\n</td>";
		echo "\n<td>" . (@$desc[1] ? "- $desc[1]" : '') . "</td>";
		echo "\n</tr>";
	}
	echo "</table>";
}
?>
