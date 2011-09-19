<?php /* TASKS $Id: gantt2.0.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

/*
 * Gantt.php - by J. Christopher Pereira
 * TASKS $Id: gantt2.0.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $
 */

error_reporting( E_ALL & ~E_NOTICE);	// this only for development testing


include ("{$AppUI->cfg['root_dir']}/lib/jpgraph2.0/src/jpgraph.php");
include ("{$AppUI->cfg['root_dir']}/lib/jpgraph2.0/src/jpgraph_gantt.php");
//include ("lib/jpgraph-1.17beta2/src/jpgraph.php");
//include ("lib/jpgraph-1.17beta2/src/jpgraph_gantt.php");

if (!class_exists("CTasks")){
	require_once( $AppUI->getModuleClass( 'tasks' ) );
}


//obtengo los proyectos permitidos
$objPrj = new CProject();
$allowedprjs = $objPrj->getAllowedRecords($AppUI->user_id);

//obtengo las tareas negadas
$obj = new CTask();
$deny = $obj->getDeniedRecords( $AppUI->user_id );
$whereTasks = count($deny) > 0 ? "\n\tAND tasks.task_id NOT IN (" . implode( ',', $deny ) . ')' : '';


$project_id = defVal( @$_REQUEST['project_id'], 0 );
$f = defVal( @$_REQUEST['f'], 0 );

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name
FROM projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_active <> 0
	".(count($allowedprjs) > 0 ? "\nAND projects.project_id IN (" . implode( ',', array_keys($allowedprjs) ) . ')' : '')."
GROUP BY project_id
ORDER BY project_name
";
// echo "<pre>$psql</pre>";
if (! ($prc = db_exec( $psql ))){
	echo db_error();
	exit;
}
$pnums = db_num_rows( $prc );

$projects = array();
for ($x=0; $x < $pnums; $x++) {
	$z = db_fetch_assoc( $prc );
	$projects[$z["project_id"]] = $z;
}

// get any specifically denied tasks
/*$dsql = "
SELECT task_id
FROM tasks, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'tasks'
	AND permission_item = task_id
	AND permission_value = 0
";
$drc = db_exec( $dsql );
echo db_error();
$deny = array();
while ($row = db_fetch_row( $drc )) {
        $deny[] = $row[0];
}
*/
// pull tasks

$select = "
tasks.task_id, task_parent, task_name, task_start_date, task_end_date, task_duration, task_duration_type,
task_priority, task_manual_percent_complete, task_order, task_project, task_milestone,
project_name, task_dynamic
";

$from = "tasks";
$join = "LEFT JOIN projects ON projects.project_id = task_project";
$where = "project_active <> 0".($project_id ? "\nAND task_project = $project_id" : '');

switch ($f) {
	case 'all':
		$where .= "\nAND task_status > -1";
		break;
	case 'myproj':
		$from .= " LEFT JOIN project_owners po ON projects.project_id = po.project_id";
		$where .= "\nAND task_status > -1";
		$where .= "\nAND (projects.project_owner = $AppUI->user_id OR po.project_owner = $AppUI->user_id)";
		break;
	case 'mycomp':
		$where .= "\nAND task_status > -1\n	AND project_company = $AppUI->user_company";
		break;
	case 'myinact':
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_status > -1
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
}
//filtro las tareas que tiene denegadas
$where .= $whereTasks;
$tsql = "SELECT $select,task_wbs_level,task_wbs_number FROM $from $join WHERE $where ORDER BY project_id, task_wbs_level,task_wbs_number, task_start_date";
##echo "<pre>$tsql</pre>".mysql_error();##

$ptrc = db_exec( $tsql );
if (! ($ptrc = db_exec( $tsql ))){
	echo db_error();
	exit;
}
$nums = db_num_rows( $ptrc );
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	
	// calculate or set blank task_end_date if unset
	if($row["task_end_date"] == "0000-00-00 00:00:00") {
		if($row["task_duration"]) {
			$row["task_end_date"] = db_unix2dateTime ( db_dateTime2unix( $row["task_start_date"] ) + SECONDS_PER_DAY * convert2days( $row["task_duration"], $row["task_duration_type"] ) );
		} else {
			$row["task_end_date"] = "";
		}
	}
		
	$projects[$row['task_project']]['tasks'][] = $row;
}

$width = dPgetParam( $_GET, 'width', 600 );
$start_date = dPgetParam( $_GET, 'start_date', 0 );
$end_date = dPgetParam( $_GET, 'end_date', 0 );
$type = dPgetParam( $_GET, 'gantttype', 'full' );

$count = 0;
$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
//$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY);
$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

$jpLocale = $AppUI->getConfig( 'jpLocale' );
if ($jpLocale) {
	$graph->scale->SetDateLocale( $jpLocale );
}
//$graph->scale->SetDateLocale("sp");
if ($start_date && $end_date) {
	$graph->SetDateRange( $start_date, $end_date );
}

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) {
	/* Add tasks to gantt chart */

	global $gantt_arr;

	$gantt_arr[] = array($a, $level);

}

function findchild( &$tarr, $parent, $level=0 ){
	GLOBAL $projects;
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
			showtask( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["task_id"], $level);
		}
	}
}

function isingantt($task){
	global $start_date, $end_date;
				/*$sdg = new CDate($start_date);
				$edg = new CDate($end_date);*/
	if ($start_date || $end_date) {	
/*		if(!$sdt || $sdt == "0000-00-00")
			$sdt = !$edt ? date("Y-m-d") : $edt;
		if(!$edt) 
			$edt = $sdt;
	
			return ($sdt >= $start_date && $sdt < $end_date ||
							$edt >= $start_date && $edt < $end_date );
								*/
		$sdt = substr($task["task_start_date"], 0, 10);
		$edt = substr($task["task_end_date"], 0, 10);
		
		$sdt = new cdate( $sdt == "0000-00-00" ? "" : $sdt);
		$edt = new cdate( $edt == "0000-00-00" ? "" : $edt);
		return ($sdt->format("%Y-%m-%d") >= $start_date && $sdt->format("%Y-%m-%d") < $end_date ||
				$edt->format("%Y-%m-%d") >= $start_date && $edt->format("%Y-%m-%d") < $end_date );						
	}else{
		return true;
	}
}

reset($projects);
$p = &$projects[$project_id];
$tnums = count( $p['tasks'] );

for ($i=0; $i < $tnums; $i++) {
	$t = $p['tasks'][$i];
	if ($t["task_parent"] == $t["task_id"]) {
		showtask( $t );
		findchild( $p['tasks'], $t["task_id"] );
	}
}

$hide_task_groups = false;

if($hide_task_groups) {
	for($i = 0; $i < count($gantt_arr); $i ++ ) {
		// remove task groups
		if($i != count($gantt_arr)-1 && $gantt_arr[$i + 1][1] > $gantt_arr[$i][1]) {
			// it's not a leaf => remove
			array_splice($gantt_arr, $i, 1);
			continue;
		}
	}
}
/*
echo "<pre>";
var_dump($start_date);
var_dump($end_date);
var_dump($gantt_arr);
var_dump($graph);
echo "</pre>";
*/
$row = 0;

for($i = 0; $i < count(@$gantt_arr); $i ++ ) {


	$a = $gantt_arr[$i][0];
	$level = $gantt_arr[$i][1];

	if($hide_task_groups) $level = 0;

	$a["task_name"] = utf8_encode($a["task_name"]);
	$name = strlen( utf8_decode($a["task_name"]) ) > 25 ? substr( utf8_decode($a["task_name"]), 0, 22 ).'...' : utf8_decode($a["task_name"]) ;
	//$start = substr($a["task_start_date"], 0, 10);
	//$end = substr($a["task_end_date"], 0, 10);
	
	$start = $a["task_start_date"];
	$end = $a["task_end_date"];
	$progress = $a["task_manual_percent_complete"];
	$flags = ($a["task_milestone"]?"m": ($a["task_dynamic"]?"d":""));
	
	//if ($start < $start_date)
	
	$cap = "";
	if(!$start || $start == "0000-00-00"){
		$start = !$end ? date("Y-m-d H:i:s") : $end;
		$cap .= "(no start date)";
	}
	
	if(!$end) {
		$end = $start;
		$cap .= " (no end date)";
	} else {
		$cap = "";
	}
	
	
	if($flags == "m") {
		$bar = new MileStone($row++, $name, $start, $start);
	}elseif ($flags == "d"){
		$bar = new GanttBar($row++, str_repeat("   ", $level) . $name, $start, $end, $cap,0.2);
		$bar->progress->Set($progress/100);
	    //$bar->title->SetFont($graph->iSimpleFont,FS_BOLD,$graph->iSimpleFontSize);
	    $bar->rightMark->Show();
	    $bar->rightMark->SetType(MARK_RIGHTTRIANGLE);
	    $bar->rightMark->SetWidth(8);
	    $bar->rightMark->SetColor('black');
	    $bar->rightMark->SetFillColor('black');
    
	    $bar->leftMark->Show();
	    $bar->leftMark->SetType(MARK_LEFTTRIANGLE);
	    $bar->leftMark->SetWidth(8);
	    $bar->leftMark->SetColor('black');
	    $bar->leftMark->SetFillColor('black');
    
	    $bar->SetPattern(BAND_SOLID,'black');		
	}else {
		$bar = new GanttBar($row++, str_repeat("   ", $level) . $name, $start, $end, $cap);
		$bar->progress->Set($progress/100);

	}

	$sql = "SELECT dependencies_task_id FROM task_dependencies WHERE dependencies_req_task_id=" . $a["task_id"];
	$query = db_exec($sql);

	while($dep = db_fetch_assoc($query)) {
		// find row num of dependencies
		for($d = 0; $d < count($gantt_arr); $d++ ) {
			$dep_task = $gantt_arr[$d][0]["task_id"] == $dep["dependencies_task_id"] ? $gantt_arr[$d][0]["task_id"] : false;
			if( $dep_task 
					//&&	(isingantt($dep_task) || isingantt($a))
					) {
				$bar->SetConstrain($d, CONSTRAIN_ENDSTART);
			}
		}
	}

	$graph->Add($bar);

	
	
}


//$graph->img->SetImgFormat("gif");
//$graph->img->SetQuality("120");
/*
echo "<pre>";
var_dump($start_date);
var_dump($end_date);
var_dump($gantt_arr);
var_dump($graph);
echo "</pre>";
/*
echo "<pre>";
var_dump($graph);
echo "</pre>";
*/
switch($type){
	case "labels":
		$graph->StrokeOnlyLabels();
		break;
	case "gantt":
		$graph->StrokeOnlyGantt();
		break;
	default:
		$graph->Stroke();
	
}

?>
