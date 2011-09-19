<?php /* PROJECTS $Id: index.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
//error_reporting( E_ALL );

$project_id = intval( dPgetParam( $_REQUEST, "project_id", 0 ) );
$report_type = dPgetParam( $_REQUEST, "report_type", '' );

// check permissions for this record
$canRead = !getDenyRead( $m, $project_id );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');


$reports = $AppUI->readFiles( $AppUI->getConfig( 'root_dir' )."/modules/reports/reports", "\.php$" );
/*
foreach ($reports as $v) {
	echo $v;
}
*/
// setup the title block
if ($report_type) {	

	$titleBlock = new CTitleBlock( $AppUI->_("$report_type"), 'tasks.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=reports&project_id=$project_id", "reports index" );
	//$titleBlock->addCell( $AppUI->_("$report_type") );
}else{
	$titleBlock = new CTitleBlock( ucfirst($AppUI->_("reports index")), 'tasks.gif', $m, "$m.$a" );
}
$titleBlock->show();

if ($report_type) {
	$report_type = $AppUI->makeFileNameSafe( $report_type );
	$report_type = str_replace( ' ', '_', $report_type );
	require( $AppUI->getConfig( 'root_dir' )."/modules/reports/reports/$report_type.php" );
} else {	
	?>
	<?/*
	<tr><td><h2><?=$AppUI->_( "Reports Available" )?></h2></td></tr>
	<?*/
	/*
	echo "<table>";
	foreach ($reports as $v) {
		echo "<tr><td>";
		$type = str_replace( ".php", "", $v );
		$desc_file = str_replace( ".php", ".$AppUI->user_locale.txt", $v );
		$desc = @file( $AppUI->getConfig( 'root_dir' )."/modules/reports/reports/$desc_file" );	
		echo "<a href='index.php?m=reports&report_type=$type'>".$AppUI->_($type)."</a></td>";
		echo "<td>".$AppUI->_($type."_desc")."</td>";
	}
	echo "</ul></td></tr></table>";
	*/
    

	$rname='rname_'.$AppUI->user_locale;
	$rdesc='rdesc_'.$AppUI->user_locale;
	$sql="SELECT $rname AS name, $rdesc AS descr, rfile_name FROM reports r ORDER BY $rname";
	$rc=db_exec($sql);
	echo "<br><table>";

	WHILE ($vec=db_fetch_array($rc)){
		
		/*Los casos particulares que estan a continuacion los puse por que la pagina de reporte no es un archivo aparte
		* sino que usa los propios de cada modulo, x lo que tengo que verificar (para mostrar el link) que tenga permisos
		* sobre ese modulo, sino putea desde el otro lado.
		* FedeR
		*/
		
		//Si el reporte es de webtracking verifico que tenga permisos para el modulo de webtraking
		if(  $vec['rfile_name']== "webtracking")
		{
			if (getDenyEdit("webtracking"))
				continue;
		}

		if(  $vec['rfile_name']== "vw_sup_day")
		{
			if (getDenyEdit("timexp"))
				continue;
		}
		
		$ahref="<a href='index.php?m=reports&report_type=".$vec['rfile_name']."'>".$vec['name']."</a>";
		echo "<tr>
					<td width='26%'><b>$ahref<b></td><td>".$vec['descr']."</td></tr>
				<tr>
					<td colspan='5' bgcolor='#e9e9e9' height='3'></td>
				</tr>";
	}
	echo "</table>";
}
?>