<?
global $AppUI;

if (!class_exists("CProject")){
	require_once( $AppUI->getModuleClass( 'projects' ) );
}

if(!isset($company_id)) $company_id="";
elseif ($company_id!=0) $sql_company=" AND cpn.company_id=$company_id ";

if(!isset($canal_id)) $canal_id="";
elseif ($canal_id!=0) $sql_canal=" AND cnl.company_id=$canal_id ";

if(!isset($orderby)) $orderby="project_name";
if(isset($_GET["revert"])) $orderby .= " DESC";

if($AppUI->user_locale=='en')$sgm="sgm.description_en";
elseif ($AppUI->user_locale=='es') $sgm="sgm.description_es";

$obj = new CProject();
$prjs = $obj->getAllowedRecords($AppUI->user_id, "project_id");
$where = (count($prjs) > 0 ? "\n\tWHERE project_id IN (" . implode( ',', array_keys($prjs) ) . ')' : "\n\tWHERE project_id is null");
$where_project = " AND project_status = project_status";
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
	COUNT(distinct t1.task_id) AS total_tasks,
	SUM(
		IF( t1.task_owner = 489, 1,0)
		) AS my_tasks
	
FROM projects
INNER JOIN companies AS cpn ON cpn.company_id = projects.project_company
LEFT JOIN companies AS cnl ON projects.project_canal=cnl.company_id
LEFT JOIN segment AS sgm ON sgm.id_segment=cpn.company_segment
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
$where
GROUP BY project_id ". ($orderby!="" ? "ORDER BY $orderby" : "").";";
//die($sql);

$projects = db_loadList( $sql );

//echo $sql."<BR><BR>";
//print_r($projects);

// se calcula el progreso de los proyectos
include("projects_progress.inc.php");

foreach ($project_progress as $a => $b) {

	$projects[$a] = $b;
	
}

?>