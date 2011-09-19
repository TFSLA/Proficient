<?php /* TASKS $Id: tasks.php,v 1.11 2009-07-31 18:10:37 nnimis Exp $ */

require_once( "./modules/tasks/functions.php" );
require_once( "./classes/projects.class.php" );
if($_GET['m']=='projects'){
	require_once( "./modules/timexp/report_to_items.php" );
	require_once("./modules/projects/ajax.php");
	
	$xajax->printJavascript('./includes/xajax/');
}

function task_users($a){
	$users = CTask::getAssignedUsers($a["task_id"]);
	if (count($users)>0){
		$users_list = array();
		foreach ($users as $user_id => $user_value)
		{
			$users_list[] = trim($user_value["user_username"])." [".
							trim($user_value["user_units"])."%]";
		}		
		$task_users .=implode(", <br>",$users_list) .$stru;
	}
	else $task_users .= '-';
	return $task_users;
}

function dots($level){
	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$dots.= '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			$dots.= '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}
	return $dots;
}

function task_priority($task_priorities, $AppUI, $a){
	$priority_limits=array_keys($task_priorities);
	for($i=0; $i<count($priority_limits); $i++){
		if (intval($a["task_priority"]) <= $priority_limits[$i]){
			$priority_file = $task_priorities[$priority_limits[$i]];
			$i=count($priority_limits);
		}
	}
	$s .= "\n\t\t<img src=\"./images/icons/" . $priority_file .'.gif" width=13 height=16 title="'.$AppUI->_( 'Task Priority' ).': '.$a["task_priority"].'">';
}

function dependencies($a, $AppUI){
$sql3="	SELECT t.task_name, t.task_complete,t.* FROM task_dependencies AS td
					INNER JOIN tasks AS t
  					ON (td.dependencies_req_task_id=t.task_id)
					WHERE td.dependencies_task_id='".$a['task_id']."'
					ORDER BY task_complete ASC, t.task_name ASC";
	$rc3=db_exec($sql3);

	$num=db_num_rows($rc3);
	if ($num!=0){
		$tasks_dep=$AppUI->_( 'Dependencies' ).":";
		while ($vec3=db_fetch_array($rc3)){
		 if ($vec3['task_complete']==1) $color='green';
		 else $color='red';
		  $task_name = $vec3['task_name'];
		  $task_name=ereg_replace('"','&quot;',$task_name);
		  $task_name=ereg_replace("'","%27",$task_name);
		
		  $tasks_dep .="<BR><B><FONT COLOR=$color>".wbs($vec3)." - ".$task_name."</FONT></B>";
		}
		$t="<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>$tasks_dep</pre>', '');\" onmouseout=\"tooltipClose();\"><img src='./images/linked.jpg'></span>";
		
	}
	else $t='';
	return $t;
}

function task_checkbox ($a, $canEdit){
	if ($canEdit) {
		// nos fijamos si las tareas hijas(si las hay) estan marcadas como completadas
		$sql1 = "SELECT IF(COUNT(task_complete) = 0, '1', MIN(task_complete)) as task_complete_possible FROM tasks WHERE task_parent = ".$a['task_id']."
			AND task_id != ".$a['task_id'];
		$vec1 = db_fetch_array(db_exec($sql1));
		// traemos la tarea padre
		if($a['task_id'] != $a['task_parent']) {
			$sql2 = "SELECT task_complete as task_parent_is_complete FROM tasks WHERE task_id = ".$a['task_parent'];
			$vec2 = db_fetch_array(db_exec($sql2));
		}
		else $vec2['task_parent_is_complete'] == '0';
		$from_check="<form method='POST' action='#".$a['task_id']."' name='TaskComplete' id='".$a['task_id']."'><input type='hidden' name='task_id' value='".$a['task_id']."'><input type='hidden' name='task_complete' value='".$a['task_complete']."'><input type='checkbox' name='TaskComplete_' value='' onclick='submit();' ".($a['task_complete'] ? 'checked' : '')." ".($vec1['task_complete_possible'] ? 'disabled'  : '')."".($vec2['task_parent_is_complete'] ? '' : 'disabled')." ></form>";
	}
	return ($from_check);
}

GLOBAL $m, $a, $project_id, $f, $min_view, $query_string, $durnTypes;
GLOBAL $task_sort_item1, $task_sort_type1, $task_sort_order1;
GLOBAL $task_sort_item2, $task_sort_type2, $task_sort_order2;
/*
	tasks.php

	This file contains common task list rendering code used by
	modules/tasks/index.php and modules/projects/vw_tasks.php

	External used variables:

	* $min_view: hide some elements when active (used in the vw_tasks.php)
	* $project_id
	* $f
	* $query_string
*/

// chequeamos si se modifico la completitud de alguna tarea con un check de la listaWork
//echo "task_id= ".$_POST['task_id'];
//echo "<BR>task_complete= ".$_POST['task_complete'];

if($_POST['task_complete']=='0') {
	$sql = "UPDATE tasks SET task_complete = '1' WHERE task_id = ".$_POST['task_id'];
	$rc=db_exec($sql);
}
if($_POST['task_complete']=='1') {
	$sql = "UPDATE tasks SET task_complete = '0' WHERE task_id = ".$_POST['task_id'];
	$rc=db_exec($sql);
}

//echo "<h1>Project $project_id</h1>";
if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}

$durnTypes = dPgetSysVal( 'TaskDurationType' );

$task_project = intval( dPgetParam( $_GET, 'task_project', null ) );
$task_id = intval( dPgetParam( $_GET, 'task_id', null ) );

$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', '' );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', '' );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', '' );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', '' );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', 0 ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', 0 ) );

$where = '';
$join = winnow( 'projects', 'project_id', $where );

// pull valid projects and their percent complete information

$objprj = new CProject();
$lstprjs = $objprj->getAllowedRecords($AppUI->user_id, "project_id");
$where = (count($lstprjs) > 0 ? "\n\tWHERE project_id IN (" . implode( ',', array_keys($lstprjs) ) . ')' : '');

$psql = "
	SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	ROUND(
			(
				SUM(
					CASE
						WHEN t1.task_complete='1' THEN 1
						WHEN t1.task_manual_percent_complete='100' THEN 1
						ELSE 0
					END
				)
				/
				SUM(
					CASE
						WHEN t1.task_id!=0 THEN 1
						ELSE 0
					END
				)
			)
			*20
		)
	*5
	AS project_percent_complete
	FROM projects
	LEFT JOIN tasks t1 ON projects.project_id = t1.task_project 
	$where
	GROUP BY project_id ORDER BY project_name ";

//echo "<pre>$psql</pre>";
//$prc = mysql_query($psql ) or die(mysql_erro());
$prc = db_exec( $psql );
echo db_error();

$projects = array();
while ($row = db_fetch_assoc( $prc )) {
	$projects[$row["project_id"]] = $row;
	if($prjlist=="")$prjlist=$prjlist.$row["project_id"];
	else $prjlist=$prjlist.", ".$row["project_id"];
}

if($prjlist=="")$prjlist="-10";
include("possible_tasks.inc.php");

// pull tasks
$select = "
tasks.task_id, task_parent, task_wbs_number, task_wbs_level, task_name, task_start_date, task_end_date, 
task_work, task_priority, task_duration, task_duration_type, task_project,
task_description, task_owner, user_username, task_milestone, task_hours_worked AS task_worked_hours,
task_complete, IF(task_complete like '1', '100', task_manual_percent_complete) AS task_manual_percent_complete
";

$from = "tasks";
$join = "\nLEFT JOIN projects ON projects.project_id = task_project";
$join .= " \nLEFT JOIN users as usernames ON task_owner = usernames.user_id";
$join .= " \nLEFT JOIN timexp as horas ON task_owner = usernames.user_id";
$where = "";


$where .= $project_id ? "task_project = $project_id" : ' project_active != 0';

//Filtro por estado de los proyectos
if($project_status_id >= 0 && $project_status_id!="")
	$where .= "\nAND projects.project_status = $project_status_id";

switch ($f) {
	case 'all':
		break;
	case 'children':
		$where .= "\nAND task_parent = $task_id AND task_id != $task_id";	
		break;
	case 'myproj':
		// Aca abarca todos los proyectos en los cuales el usuario sea:
		// OWNER, ADMIN o USER

		$join .= "\nLEFT JOIN project_owners AS po ON projects.project_id = po.project_id";
		$where .= "\nAND ( po.creator_user = $AppUI->user_id OR po.project_owner = $AppUI->user_id)";
		break;
	case 'mycomp':
		$where .= "\nAND project_company = $AppUI->user_company";
		break;
	case 'myunfinished':
		//$from .= ", user_tasks";
		$sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		// This filter checks all tasks that are not already in 100% 
		// and the project is not on hold nor completed
		$where .= "
					AND task_project             = projects.project_id
					AND user_tasks.user_id       = $AppUI->user_id
					AND user_tasks.task_id       = tasks.task_id
					AND task_complete	= '0'
					AND task_start_date < '".$date_sd."'
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
		break;
	case 'allunfinished':
		//$from .= ", user_tasks";
		$sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
					AND task_project             = projects.project_id
					AND user_tasks.task_id       = tasks.task_id
					AND task_complete	= '0'
					AND task_start_date < '".$date_sd."'
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
		break;
            case 'allfinished':
		//$from .= ", user_tasks";
		$sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
					AND task_project             = projects.project_id
					AND user_tasks.task_id       = tasks.task_id
					AND (task_complete = '1' OR task_manual_percent_complete = '100')";
		break;
	case 'unassigned':
		$join .= "\n LEFT JOIN user_tasks ON tasks.task_id = user_tasks.task_id";
		$where .= "
					AND task_status > -1
					AND user_tasks.task_id IS NULL";
		break;
	case 'myovercome':
		//Este filtro detecta las tareas vencidas.
		//$from  .= ", user_tasks";
		$sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
					AND task_project = projects.project_id
					AND user_tasks.user_id = $AppUI->user_id
					AND user_tasks.task_id = tasks.task_id
					AND task_end_date < '".$date_sd."'
					AND (task_complete <> '1' AND task_manual_percent_complete <> '100')
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5' ";
		break;
	case 'myoverworked':
		//Este filtro detecta las tareas sobretrabajadas
		//$from  .= ", user_tasks";
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
					AND task_project = projects.project_id
					AND user_tasks.user_id = $AppUI->user_id
					AND task_hours_worked > task_work
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
		break;
	case 'mynotstarted':
		//Este filtro detecta las tareas atrasadas
		
		//$from  .= ", user_tasks";
		$sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
					AND task_project = projects.project_id
					AND user_tasks.user_id = $AppUI->user_id
					AND task_manual_percent_complete = '0'
					AND task_complete = '0'
					AND task_hours_worked = '0' ";
		break;
	case 'my':
		//$from .= ", user_tasks";
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
					AND task_project = projects.project_id
					AND user_tasks.user_id = $AppUI->user_id ";
		break;
	
	case 'myfinished':
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
				AND task_project             = projects.project_id
				AND user_tasks.task_id       = tasks.task_id
				AND (task_complete = '1' OR task_manual_percent_complete = '100')
				AND user_tasks.user_id = $AppUI->user_id ";
		
		break;
	case 'mystarted':
		// MIS TAREAS COMENZADAS (Independientemente de la fecha, son las que tiene horas reportadas y no estan completadas)
		$sd = new CDate( $date );
		$today_sd = $sd->format("%Y-%m-%d %H:%M:00"); 
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
				AND task_project = projects.project_id
				AND user_tasks.user_id = $AppUI->user_id
				AND task_complete <> '1' AND task_manual_percent_complete <> '100'
				AND task_hours_worked <> '0' ";
		break;
		
	case 'myfinishearly':
		//MIS TAREAS FINALIZADAS ANTES DE LO PREVISTO (hay que tener en cuenta que en tareas, hay relacionados to-do's e incidencias, donde tambien se reportan horas...)  My tasks finish before end date
		$sd = new CDate( $date );
		$today_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$join .= "\nINNER JOIN user_tasks ON user_tasks.task_id = tasks.task_id";
		$where .= "
				AND task_project = projects.project_id
				AND user_tasks.user_id = $AppUI->user_id
				AND (task_complete ='1' OR task_manual_percent_complete = '100')
				AND task_end_date >'".$today_sd."' ";
		break;	
	
	case 'allstarted':
		//TODAS LAS TAREAS COMENZADAS (Independientemente de la fecha, son las que tiene horas reportadas y no estan completas)
		$sd = new CDate( $date );
		$today_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$where .= "      AND task_project = projects.project_id
				AND task_complete <>'1' AND task_manual_percent_complete <> '100'
		                        AND task_hours_worked <> '0' ";
		break;
		
	case 'allnotstarted':
		// TODAS LAS TAREAS NO COMENZADAS (son las que por fecha aun no comenzaron, y no tienen ni horas reportadas, ni estan vencidas)
		$sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$where .= "      AND task_project = projects.project_id
				AND task_complete = '0' AND task_manual_percent_complete = '0' 
				AND task_hours_worked = '0' ";
		
		break;
		
	case 'allovercome':
	            $sd = new CDate( $date );
		$date_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$where .= "
				AND task_project = projects.project_id
				AND task_end_date < '".$date_sd."'
				AND (task_complete <> '1' AND task_manual_percent_complete <> '100')
				AND projects.project_active  = '1'
				AND projects.project_status != '4'
				AND projects.project_status != '5' ";
	       break;	
	 
	case 'alloverworked':
		//TODAS LAS TAREAS CON SOBRETRABAJO (hay que tener en cuenta que en tareas, hay relacionados to-do's e incidencias, donde tambien se reportan horas...)
		
		$where .= "
				AND task_project = projects.project_id
				AND task_hours_worked > task_work
				AND projects.project_active  = '1'
				AND projects.project_status != '4'
				AND projects.project_status != '5'";
		break;
	case 'allfinishearly':
		//TODAS LAS TAREAS FINALIZADAS ANTES DE LO PREVISTO (hay que tener en cuenta que en tareas, hay relacionados to-do's e incidencias, donde tambien se reportan horas...)
		$sd = new CDate( $date );
		$today_sd = $sd->format("%Y-%m-%d %H:%M:00");
		
		$where .= "
				AND task_project = projects.project_id
				AND (task_complete ='1' OR task_manual_percent_complete = '100')
				AND task_end_date >'".$today_sd."' ";
		break;
}

if ( $min_view )
	$task_status = intval( dPgetParam( $_GET, 'task_status', null ) );
else
	$task_status = intval( $AppUI->getState( 'inactive' ) );

if($task_status == -1){
$where .="\nAND (task_complete ='1' OR task_manual_percent_complete='100' ) ";
}

IF ($f3!='all' && $f3 !='') $where .= " AND projects.project_canal = '$f3'";
IF ($f4!='all' && $f4 !='') $where .= " AND projects.project_id = '$f4'";

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';


$where .=" \nAND projects.project_id IN (" . $prjlist . ") ";
$where .=" \nAND tasks.task_id IN (" . $tasklist . ") ";

// echo "<pre>$where</pre>";

// Filter by company
if ( ! $min_view && $f2 != 'all' ) {
	 $join .= "\nLEFT JOIN companies ON company_id = projects.project_company";
         $where .= "\nAND company_id = $f2  ";
}

	$tsql = "SELECT $select \nFROM $from $join \nWHERE $where" .
  "\nGROUP BY task_id ORDER BY tasks.task_project, task_wbs_number, task_name";

//echo "<pre>$tsql</pre>";

$ptrc = db_exec( $tsql );
$nums = db_num_rows( $ptrc );
echo db_error();
//pull the tasks into an array

for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	if(!getDenyRead("tasks",$row["task_id"]))
	  $projects[$row['task_project']]['tasks'][] = $row;
}



if (! function_exists('showtask') ) {
//file:///mnt/tfsla_beta/modules/tasks/tasks.php
/*
	Function: showtask

	This kludgy function echos children tasks as threads

   Parameters:

	a - un array generado por la funcion array_csort
	level - Por defecto =0

   Returns:

      none

   See Also:

      <array_csort>
*/
function showtask( &$a, $level=0 ) {
	global $AppUI, $done, $query_string, $durnTypes, $task_priorities, $allowedTimexpTasks;

	$df = $AppUI->getPref( 'SHDATEFORMAT' );
	$done[] = $a['task_id'];
	$start_date = intval( $a["task_start_date"] ) ? new CDate( $a["task_start_date"] ) : null;
	$end_date = intval( $a["task_end_date"] ) ? new CDate( $a["task_end_date"] ) : null;
	IF ($a['task_complete']==1) $task_color="color='#3366CC'";
	ELSE $task_color="color='black'";
	$s = "<tr class='tableRowLineCell' id='ptsk_".$a["task_project"]."_".$a['task_id']."_sep'><td colspan='12'></td></tr>";
	$s .= "\n<tr id=\"ptsk_".$a["task_project"]."_".$a['task_id']."\" valign=\"top\">";

  
// edit icon
	$perms=CTask::getTaskAccesses($a["task_id"]);
	$canEdit=$perms["edit"];
	
	if($AppUI->user_type == 1 || (is_assigned($a['task_id']) && !getDenyEdit("timexp") ) ){
		$s .= "\n\t<td><a href='javascript:report_hours(".$a['task_id'].",3,$a[task_complete]);' >";
		$s .= "<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0></a></td>";
	}else{
		$s .= "\n\t<td></td>";
	}
	
	IF ($canEdit){
		$s .= "\n\t<td><a href='?m=tasks&a=addedit&task_id=".$a['task_id']."'><img src='./images/icons/edit_small.gif' alt='".$AppUI->_( 'Edit Task' )."' border='0' width='20' height='20'></a>\n\t</td>";
	}
	ELSE $s .= "\n\t<td></td>";
	$s .= "\n\t<td>".task_checkbox($a, $canEdit)."\n\t</td>";																																//task_complete checkbox
	$s .= "<td align=\"right\"><font $task_color>".intval($a["task_manual_percent_complete"] )."%</font></td>";							// percent complete
	$s .= "\n\t<td>".task_priority($task_priorities, $AppUI, $a)."\n\t</td>";																								// priority
	$s .= "<td width='1'>".dependencies($a, $AppUI)."</td>";																																// DEPENDENCIAS
	$s .= "<td>".dots($level)."<a href='./index.php?m=tasks&a=view&task_id=".$a["task_id"]."' title='".htmlspecialchars($a["task_description"])."'><b>"."<font $task_color>".wbs($a)." - ".$a['task_name']."</font></td>";
	
     if($a['task_duration']!=0){
        $a['task_duration'] = number_format($a['task_duration'], 3, '.', '');
       $separado_por_puntos = explode(".", $a['task_duration']);
       
       if (count($separado_por_puntos)>1)
       {
       	$decimal1 = substr($separado_por_puntos[1], 0,1);
       	$decimal2 = substr($separado_por_puntos[1], 1,1);
       	$decimal3 = substr($separado_por_puntos[1], 2,1);
       	
       	if($separado_por_puntos[1]=="000"){
       	$a['task_duration'] = $separado_por_puntos[0];
       	}elseif ($decimal2=="0" && $decimal3=="0"){
       	    $a['task_duration'] = $separado_por_puntos[0].".".$decimal1;
       	}elseif ($decimal2!="0" && $decimal3=="0"){
       	    $a['task_duration'] = $separado_por_puntos[0].".".$decimal1.$decimal2;
       	}
       }
    }
	
	$s .= "<td><font $task_color>".$a['task_duration'] . ' ' . substr($AppUI->_( $durnTypes[$a['task_duration_type']] ), 0,1)."</font></td>"; 	// duration or milestone //$s .= $AppUI->_("Milestone");
        
      if($a['task_work']!=0){
      	      $a['task_work'] = number_format($a['task_work'], 3, '.', '');
      	      
	       $separado_por_puntos = explode(".", $a['task_work']);
	       
	       if (count($separado_por_puntos)>1)
	       {
	       	$decimal1 = substr($separado_por_puntos[1], 0,1);
       	            $decimal2 = substr($separado_por_puntos[1], 1,1);
       	            $decimal3 = substr($separado_por_puntos[1], 2,1);
       	
	       	if($separado_por_puntos[1]=="000"){
	       	    $a['task_work'] =$separado_por_puntos[0];
	       	}elseif ($decimal2=="0" && $decimal3=="0"){
	       	    $a['task_work'] = $separado_por_puntos[0].".".$decimal1;
	       	}elseif ($decimal2!="0" && $decimal3=="0"){
	       	    $a['task_work'] = $separado_por_puntos[0].".".$decimal1.$decimal2;
	       	}
	     }
      }
    
	$s .= "<td><font $task_color>".$a['task_work']." h</font></td>";
	$s .= "<td nowrap='nowrap'><font $task_color>".($start_date?$start_date->format('%d/%m/%Y %H:%M'):'-')."</font></td>";							// start date
	$s .= "<td nowrap='nowrap'><font $task_color>".($end_date?$end_date->format('%d/%m/%Y %H:%M'):'-')."</font></td>";									// end date
	$s .="<td nowrap='nowrap'><font $task_color>".task_users($a)."</font></td>";																																																		// task assigned users
	
	$s .= "</tr>";
	echo $s;
	}

}

if (! function_exists('is_assigned') ) {
	function is_assigned($task_id){
		global $AppUI;
		if($AppUI->user_type==1) return true;
		
		$sql = "SELECT user_id FROM user_tasks WHERE task_id = $task_id";
		$result = mysql_query($sql);
		
		if(mysql_num_rows($result)>0) return true;
		else return false;
	}
}

if (! function_exists('findchild') ) {
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
}

/* please throw this in an include file somewhere, its very useful */

function array_csort()   //coded by Ichier2003
{
    $args = func_get_args();
    $marray = array_shift($args);
	
	if ( empty( $marray )) return array();
	
	$i = 0;
	$msortline = "return(array_multisort(";
	$sortarr = array();
	foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            foreach ($marray as $row) {
                $sortarr[$i][] = $row[$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        $msortline .= "\$sortarr[".$i."],";
    }
    $msortline .= "\$marray));";

    eval($msortline);
    return $marray;
}

function sort_by_item_title( $title, $item_name, $item_type )
{
	global $AppUI,$project_id,$min_view,$m;
	global $task_sort_item1,$task_sort_type1,$task_sort_order1;
	global $task_sort_item2,$task_sort_type2,$task_sort_order2;

	if ( $task_sort_item2 == $item_name ) $item_order = $task_sort_order2;
	if ( $task_sort_item1 == $item_name ) $item_order = $task_sort_order1;

	if ( isset( $item_order ) ) {
		if ( $item_order == SORT_ASC )
			echo '<img src="./images/arrow-down.gif" border=0 alt="'.$AppUI->_("Ascending").'">';
		else
			echo '<img src="./images/arrow-up.gif" border=0 alt="'.$AppUI->_("Descending").'">';
	} else
		$item_order = SORT_DESC;

	/* flip the sort order for the link */
	$item_order = ( $item_order == SORT_ASC ) ? SORT_DESC : SORT_ASC;
	if ( $m == 'tasks' )
		echo '<a href="./index.php?m=tasks';
	else
		echo '<a href="./index.php?m=projects&a=view&project_id='.$project_id;

	echo '&task_sort_item1='.$item_name;
	echo '&task_sort_type1='.$item_type;
	echo '&task_sort_order1='.$item_order;
	if ( $task_sort_item1 == $item_name ) {
		echo '&task_sort_item2='.$task_sort_item2;
		echo '&task_sort_type2='.$task_sort_type2;
		echo '&task_sort_order2='.$task_sort_order2;
	} else {
		echo '&task_sort_item2='.$task_sort_item1;
		echo '&task_sort_type2='.$task_sort_type1;
		echo '&task_sort_order2='.$task_sort_order1;
	}
	echo '" class="">';
	
	echo $AppUI->_($title);
	
	echo '</a>';
}

?>
<script language="Javascript"><!-- 

	function close_div(div_name){
		document.getElementById(div_name).style.display='none';
	}
	
	function progress_msg(visibility_st){ 
	
	var f = document.editFrm;
	
	if(visibility_st == 'mostrar')
	{
        // Muestro el cartel de procesando	  
		document.getElementById('progress').style.display='';
		document.getElementById('add_hours').style.display='none';
		
	   setTimeout("progress_msg('error')", 60*1000); 
			
	}else{
		   
	  // Oculto el mensaje de error
	  document.getElementById('progress').style.display = "none"; 		  
		}	
	}

   function show_hide_project(pro){
   		if (document.getElementById(name)){
   			var vis = document.getElementById(name).style.display;
   			if (vis=='none'){
   				document.getElementById(name).style.display = '';
				document.getElementById('img' + name).src ='./images/icons/collapse.gif';
				document.getElementById('img' + name).alt = '<?php echo $AppUI->_('Hide');?>';
   			}else{
    			document.getElementById(name).style.display = 'none';
				document.getElementById('img' + name).src ='./images/icons/expand.gif'; 
				document.getElementById('img' + name).alt = '<?php echo $AppUI->_('Show');?>';
   			}
   		}
   }

		function show_hide_tasks(prj_id){
			var tb = document.getElementById("tbtasks");
			var vis = '';
			for(var i = 0; i < tb.rows.length; i++ ){

				if (tb.rows[i].parentNode.parentNode.id == "tbtasks"
				&& tb.rows[i].id.indexOf('ptsk_'+prj_id+"_") > -1){
					vis = tb.rows[i].style.display;
					if(vis==""){
						vis = 'none';
					}else{
						vis = ''
					}
					tb.rows[i].style.display = vis;
				
				}
			}			
			if (vis==""){
				var img = imgCollapse;
			}else{
				var img = imgExpand;
			}
			document.getElementById('imgprj_' + prj_id).src = img.src;
			document.getElementById('imgprj_' + prj_id).alt = img.alt;	
		}

		var arTRs = new Array();
		var imgExpand = new Image;
		var imgCollapse = new Image;
		imgExpand.src = './images/icons/expand.gif';
		imgExpand.alt = '<?php echo $AppUI->_('Show');?>';
		imgCollapse.src = './images/icons/collapse.gif'; 
		imgCollapse.alt = '<?php echo $AppUI->_('Hide');?>';

//-->
</script>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="" id="tbtasks">
<col width="10"><col width="10"><col width="20"><col width="30"><col width="10"><col width="20">
<col width="120px"><col width="60"><col width="60"><col width="80"><col width="80">	

<tr class="tableHeaderGral">
	<td class="tableHeaderText" align="center"><!-- Icono de Reportar Horas --></td>
	<td class="tableHeaderText" align="center"><!-- Icono de Edición --></td>
	<td class="tableHeaderText" align="center"><!-- Check Compleatado --></td>
	<td nowrap="nowrap" class="tableHeaderText"><!--Porcentaje de compleatado--></td>
	<td class="tableHeaderText" align="center"><!-- Prioridad --></td>
	<td class="tableHeaderText" align="center"><!-- Dependecias --></td>
	<td width="40%"class="tableHeaderText"><?php sort_by_item_title( 'Task Name', 'task_name', SORT_STRING );?></td>
	<td nowrap="nowrap" class="tableHeaderText"><?php sort_by_item_title( 'Duration', 'task_duration', SORT_NUMERIC );?>&nbsp;&nbsp;</td>
	<td class="tableHeaderText" align="center"><?php echo $AppUI->_('Work');?></td>
	<td nowrap="nowrap" class="tableHeaderText"><?php sort_by_item_title( 'Start Date', 'task_start_date', SORT_NUMERIC );?></td>
	<td nowrap="nowrap" class="tableHeaderText"><?php sort_by_item_title( 'Finish Date', 'task_end_date', SORT_NUMERIC );?></td>
	<td nowrap="nowrap" class="tableHeaderText"><?php echo $AppUI->_( 'Users');?>&nbsp;&nbsp;</td>
	<td nowrap="nowrap" class="tableHeaderText">&nbsp;</td>
</tr>
<?php
//aqui imprimen la matriz $projects en pantalla
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );
$tr_index = 1; 
foreach ($projects as $k => $p) {
	$tnums = count( @$p['tasks'] );
	$tr_index++; //salto la fila del projecto

	$js_string .= "arTRs[$k] = new Array();";
	$js_string .= "arTRs[$k][0] = $tr_index;";
	$tr_index = $tr_index + $tnums * 2;
	$js_string .= "arTRs[$k][1] = $tr_index;";
	$tr_index++;
	

// don't show project if it has no tasks
	if ($tnums) {
		//chequeo los permisos para el proyecto 
		$objPrj = new CProject();
		$canDelete = $objPrj->canDelete( $msg, $k );
		if (!$objPrj->load($k, false)){
			$AppUI->setMsg( 'Project' );
			$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
			$AppUI->redirect();
		
		}
		//get and check permissions
		$canRead =$objPrj->canRead();
		if (!$canRead) {
			//echo "Project ($k deny)<br>";
			continue;
			//$AppUI->redirect( "m=public&a=access_denied" );
		}
		$canEdit = $objPrj->canEdit();
		$canReadDetails = $objPrj->canReadDetails();
		$canReadEcValues = $objPrj->canReadEcValues();
		$canReadCompany = $objPrj->canReadCompany();
		$canAddTasks = $objPrj->canAddTasks();
//echo '<pre>'; print_r($p); echo '</pre>';
		if (!$min_view) {
?>
<tr>
	<td >
		<a href="javascript: //" onclick="javascript: show_hide_tasks('<?php echo $k;?>');">
		<img id="imgprj_<?php echo $project_id ? 0 : $k;?>" src="./images/icons/collapse.gif" width="16" height="16" border="0" alt="<?php echo $AppUI->_('Hide');?>">
		</a>
	</td>
	<td colspan="11">
		<table width="100%" border="0">
		<tr>
			<td nowrap style="border: outset #eeeeee 2px;background-color:#<?php echo @$p["project_color_identifier"];?>">
				<a href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
				<span style='color:<?php echo bestColor( @$p["project_color_identifier"] ); ?>;text-decoration:none;'><strong><?php echo @$p["project_name"];?></strong></span></a>
			</td>
			<td width="<?php echo (101 - intval(@$p["project_percent_complete"]));?>%">
				<!--<?php echo (intval(@$p["project_percent_complete"]));?>% -->
			</td>
			<td nowrap="nowrap" align="right">
<?php		if( $canAddTasks ) { ?>
						<input type="button" class="button" value="<?php echo $AppUI->_('new task');?>"
						onclick="javascript:window.location='index.php?m=tasks&a=addedit&task_project=<?php echo $k;?>';" />
<?php		} 
			if( $AppUI->cfg['enable_gantt_charts'] ) { ?>
						<input type="button" class="button" value="<?php echo $AppUI->_('gantt chart');?>" onclick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $k;?>';" />
<?php		} ?>			
			</td>
		</tr>
		</table>
	</td>
</tr>
<?php
		}
		global $done;
		$done = array();
		if ( $task_sort_item1 != "" )
		{
			if ( $task_sort_item2 != "" && $task_sort_item1 != $task_sort_item2 )
				$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1
										  , $task_sort_item2, $task_sort_order2, $task_sort_type2 );
			else $p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1 );
		}
		
		for ($i=0; $i < $tnums; $i++) {
			$t = $p['tasks'][$i];
			if ($t["task_parent"] == $t["task_id"]) {
				showtask( $t );
				findchild( $p['tasks'], $t["task_id"] );
			}
		}
// check that any 'orphaned' user tasks are also display
		for ($i=0; $i < $tnums; $i++) {
			if ( !in_array( $p['tasks'][$i]["task_id"], $done )) {
				showtask( $p['tasks'][$i], 1 );
			}
		}
    echo  "<tr class=\"tableRowLineCell\" style=\"background-color: #bbbbbb;\"><td colspan=\"12\"></td></tr>"; 
		
		//$canEdit = !getDenyEdit( "tasks", $k );
	
		/*
?>

		<tr>
			<td colspan="11" align="right">&nbsp;

<?php		if($tnums && $canAddTasks && !$min_view) { ?>
						<input type="button" class="button" value="<?php echo $AppUI->_('new task');?>"
						onclick="javascript:window.location='index.php?m=tasks&a=addedit&task_project=<?php echo $k;?>';" />
<?php		}
				if($tnums && $AppUI->cfg['enable_gantt_charts'] && !$min_view) { ?>
						<input type="button" class="button" value="<?php echo $AppUI->_('gantt chart');?>" onclick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $k;?>';" />
<?php		} ?>
			</td>
		</tr>
		<?php*/
	}
	$tr_index++; //incremento una fila mas por la fila de los botones
}


?>
 
</table>
<?php 
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>
<script language="Javascript"><!-- 
<?php echo $js_string?>
// -->
</script>
<?






/********************************************************************************/
/********************************************************************************/
/********************************************************************************/
/* codigo agregado para debug en la programaci? de seguridad 
	de compa?a-proyectos-tareas												*/
/********************************************************************************/
/********************************************************************************/
/********************************************************************************/
/*
	*/

if (isset($debuguser)){
		
		//muestra tabla de permisos unificada
		$tbl= "";
		$perm = CTask::getPermissions( $debuguser);
				for($i=0;$i<count($perm);$i++){
					$task_id=$perm[$i]["task_id"];
					$item_id=$perm[$i]["task_permission_on"];
					$value=$perm[$i]["task_permission_value"];
					$permisos[$task_id][$item_id]=$value;
				}
		foreach($permisos as $task =>$datos){
			$tbl.="<tr><td>$task</td>";
			for($ii = 1; $ii <= 7 ; $ii++){
				$valor=$datos[$ii] ? $datos[$ii] : "&nbsp;" ;
				$tbl.="<td>";
				if (is_array($valor))
					foreach ($valor as $key=>$val){$tbl.=" $key = $val<br>";}
				else
					$tbl.="$valor";
				$tbl.="</td>";
			}
			$tbl.="</tr>";
		}
		$tit = "<tr><td>Tarea</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td></tr>";
		echo "<h1>perm Unificados</h1>";
		echo "<table border=1> $tit $tbl </table>";		

	}


//muestra tabla de permisos desglosada
/*
$tbl= "";
$rt = CTask::getPermissions( $AppUI->user_id,1);
$perm= $rt[0];
foreach($perm as $task =>$datos){
	$tbl.="<tr><td>$task</td>";
	foreach($datos as $item => $valor){	
		$tbl.="<td>";
		if (is_array($valor))
			foreach ($valor as $key=>$val){$tbl.="$key=$val<br>";}
		else
			$tbl.="$valor";
		$tbl.="</td>";
	}
	$tbl.="</tr>";
}
$tit = "<tr><td>Tarea</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td></tr>";
echo "<h1>perm Especificos</h1>";
echo "<table border=1> $tit $tbl </table>";


$tbl= "";
$perm= $rt[1];
$head=false;
foreach($perm as $task =>$datos){
	$tbl.="<tr><td>$task</td>";
	foreach($datos as $item => $valor){	
		$tbl.="<td>";		
		if (is_array($valor))
			foreach ($valor as $key=>$val){$tbl.="$key=$val<br>";}
		else
			$tbl.="$valor";
		$tbl.="</td>";
	}
	$tbl.="</tr>";
}

echo "<h1>perm Generales</h1>";
echo "<table border=1> $tit $tbl </table>";

$tbl= "";
$perm= $rt[2];
$head=false;
foreach($perm as $task =>$datos){
	$tbl.="<tr><td>$task</td>";
	foreach($datos as $item => $valor){	
		$tbl.="<td>";
		if (is_array($valor))
			foreach ($valor as $key=>$val){$tbl.="$key=$val<br>";}
		else
			$tbl.="$valor";
		$tbl.="</td>";
	}	
	$tbl.="</tr>";
}

echo "<h1>perm Users</h1>";
echo "<table border=1> $tit $tbl </table>";

/********************************************************************************/
/********************************************************************************/
/********************************************************************************/
/* fin del codigo agregado para debug en la programaci? de seguridad 
	de compa?a-proyectos-tareas												*/
/********************************************************************************/
/********************************************************************************/
/********************************************************************************/

?>
