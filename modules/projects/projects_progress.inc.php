<?
/*
En este archivo se calculan los progresos
de los proyectos.
*/

global $AppUI;

if (!class_exists("CProject")){
	require_once( $AppUI->getModuleClass( 'projects' ) );
}


if(!isset($company_id)) $company_id="";
elseif ($company_id!=0) $sql_company=" AND cpn.company_id=$company_id ";

if(!isset($canal_id)) $canal_id="";
elseif ($canal_id!=0) $sql_canal=" AND cnl.company_id=$canal_id ";

if(!isset($orderby)) $orderby="project_name";

if($AppUI->user_locale=='en')$sgm="sgm.description_en";
elseif ($AppUI->user_locale=='es') $sgm="sgm.description_es";


//$total_hours = CProject::getTotalHours($project_id);

$sql_temporal="SELECT task_id FROM tasks group by task_project order by task_start_date DESC";
$res = db_loadList($sql_temporal);
$tareas = implode(",", array_keys($res));


$obj = new CProject();
$prjs = $obj->getAllowedRecords($AppUI->user_id, "project_id");
$where = (count($prjs) > 0 ? "\n\tWHERE project_id IN (" . implode( ',', array_keys($prjs) ) . ')' : "\n\tWHERE project_id is null");
$where_project = " AND project_status = project_status";
//$where_project .= " AND t2.task_id IN ($tareas)";
$where=$where.$sql_company.$sql_canal.$where_project;
echo $dp;

$sql = "
SELECT
	cnl.company_name AS channel,
	$sgm AS sgm_desc,
	cpn.company_name,
	project_id, project_active,
	project_status,
	project_color_identifier,
	project_name, project_description,
	project_start_date,
	project_end_date,
	project_actual_end_date,
	project_color_identifier,
	project_company,
	project_status,
	DATE_FORMAT(t2.task_end_date, '%H') as task_end_date_H,
	DATE_FORMAT(t2.task_end_date, '%i') as task_end_date_i,
	DATE_FORMAT(t2.task_end_date, '%s') as task_end_date_s,
	DATE_FORMAT(t2.task_end_date, '%m') as task_end_date_m,
	DATE_FORMAT(t2.task_end_date, '%d') as task_end_date_d,
	DATE_FORMAT(t2.task_end_date, '%Y') as task_end_date_Y,

	DATE_FORMAT(project_end_date, '%H') as project_end_date_H,
	DATE_FORMAT(project_end_date, '%i') as project_end_date_i,
	DATE_FORMAT(project_end_date, '%s') as project_end_date_s,
	DATE_FORMAT(project_end_date, '%m') as project_end_date_m,
	DATE_FORMAT(project_end_date, '%d') as project_end_date_d,
	DATE_FORMAT(project_end_date, '%Y') as project_end_date_Y,

	DATE_FORMAT(project_start_date, '%H') as project_start_date_H,
	DATE_FORMAT(project_start_date, '%i') as project_start_date_i,
	DATE_FORMAT(project_start_date, '%s') as project_start_date_s,
	DATE_FORMAT(project_start_date, '%m') as project_start_date_m,
	DATE_FORMAT(project_start_date, '%d') as project_start_date_d,
	DATE_FORMAT(project_start_date, '%Y') as project_start_date_Y,

	COUNT(distinct t1.task_id) AS total_tasks,
	SUM(
		IF( t1.task_owner = $AppUI->user_id, 1,0)
		) AS my_tasks,
	user_username,

	IF(
		COUNT(distinct t1.task_id) = 0, 'N/A',
		CONCAT_WS('/',
			(
				SUM(
					CASE
						WHEN t1.task_complete='1' THEN 1
						ELSE 0
					END
				)
			),
			COUNT(distinct t1.task_id)
		)
	) AS project_percent_completed_tasks,

	IF(
		project_target_budget = 0, 0,
		IF(
			COUNT(distinct t1.task_id) = 0, 0,
			ROUND(
				(
					SUM(
						CASE
							WHEN t1.task_complete='1' THEN t1.task_target_budget
							ELSE 0
						END
					)
					/
					project_target_budget
				)
				*20
			)
			*5
		)
	)
	AS project_percent_completed_oozed_cost,

	IF(
		COUNT(distinct t1.task_id) = 0, 0,
		IF( SUM(t1.task_duration) < 1, 0,
			ROUND((
				SUM(
					CASE
						WHEN t1.task_complete='1' THEN t1.task_duration
						ELSE 0
					END
				)
				/
				SUM(t1.task_duration)
			)*100)
		)
	)
	AS project_percent_completed_work

FROM projects
INNER JOIN companies AS cpn ON cpn.company_id = projects.project_company
LEFT JOIN companies AS cnl ON projects.project_canal=cnl.company_id
LEFT JOIN segment AS sgm ON sgm.id_segment=cpn.company_segment
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
LEFT JOIN tasks t2 ON (t1.task_id=t2.task_id)
$where
GROUP BY project_id ". ($orderby!="" ? "ORDER BY $orderby" : "").";";

$project_progress = db_loadList( $sql );

//echo $sql;
//print_r($projects);

/*
$i=0;
reset ($projects);
foreach ($projects as $p) {

	//preparamos las fechas en formato unix para poder restarlas
	$tmp_task_end_date		= mktime($p['task_end_date_H'],$p['task_end_date_i'],$p['task_end_date_s'],$p['task_end_date_m'],$p['task_end_date_d'],$p['task_end_date_Y']);
	$tmp_project_end_date	= mktime($p['project_end_date_H'],$p['project_end_date_i'],$p['project_end_date_s'],$p['project_end_date_m'],$p['project_end_date_d'],$p['project_end_date_Y']);
	$tmp_project_start_date = mktime($p['project_start_date_H'],$p['project_start_date_i'],$p['project_start_date_s'],$p['project_start_date_m'],$p['project_start_date_d'],$p['project_start_date_Y']);

	$diff_a = $tmp_task_end_date - $tmp_project_start_date;
	$diff_b = $tmp_project_end_date - $tmp_project_start_date;

	if($diff_b > 0)
		$projects[$i]['project_percent_completed_duration'] = round($diff_a / $diff_b);
	else
		$projects[$i]['project_percent_completed_duration'] = 0;

	//echo $tmp_task_end_date." - ".$tmp_project_start_date."= $diff_a <BR>";
	//echo $tmp_project_end_date." - ".$tmp_project_start_date."= $diff_b <BR>";

	//echo $projects[$i]['project_percent_completed_duration']."<BR>";
	$i++;
}
*/

/*
					SUM(
						IF(
							t1.task_duration_type = 1 AND t1.task_milestone  ='0' AND t1.task_dynamic = 0,
							t1.task_duration,
							0
						)
					)
*/

function pdc ($project_id){

	$SQL0 = "SELECT UNIX_TIMESTAMP(task_end_date) AS ted FROM tasks t WHERE task_project='$project_id' AND task_complete='1' ORDER BY task_end_date DESC LIMIT 0,1";
	$task = db_fetch_array(db_exec($SQL0));

	$SQL1 = "SELECT UNIX_TIMESTAMP(project_start_date) AS psd, UNIX_TIMESTAMP(project_actual_end_date) AS ped FROM projects p WHERE project_id='$project_id'";
	$proj = db_fetch_array(db_exec($SQL1));

	if ($proj['psd']!=0 AND $proj['ped']!=0 AND ($proj['ped']-$proj['psd'])>0 AND ($task['ted']-$proj['psd'])>0){

		// $arriba = fecha estimada de fin de última tarea terminada -  fecha de inicio de proyecto
		//$abajo = fecha de fin de proyecto - fecha de inicio de proyecto;

	           $arriba = $task['ted']-$proj['psd'];
	           $abajo = $proj['ped'] -$proj['psd'];

	           $tmp = $arriba / $abajo;

		if ($task['ted'] < $proj['ped'])
		{
		$pdc=($task['ted']-$proj['psd'])/($proj['ped']-$proj['psd']);

		$pdc=sprintf( "%.2f%%", $pdc);

		$mult = $pdc * 100;

		$pdc = sprintf( "%.1f%%", $mult);
		}
		else
		{
		 $pdc=($task['ted']-$proj['psd'])/($proj['ped']-$proj['psd']);
		 $exd = $pdc + 100;
		 $pdc = "<FONT COLOR=\"#FF0000\" onmouseover=\"tooltipLink('Proyecto excedido en tiempo');\" onMouseOut =\"tooltipClose();\" >".sprintf( "%.1f%%", $exd)."</FONT>";
		}

		if ($task['ted'] == $proj['ped']) $pdc= "100.0%";

	}
	else {$pdc='N/A'; }

	return $pdc;
}

?>