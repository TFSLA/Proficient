<? 
require_once("getadminprojects.inc.php");
if($params=="") $params = "7";
$prjs = getAdminProjects($user_id);
if (count($prjs)) {
$sql = "
SELECT projects.*, DATE_FORMAT(projects.project_start_date,'%d/%m/%Y') as project_start_datef, DATE_FORMAT(projects.project_end_date,'%d/%m/%Y') as project_end_datef
FROM projects
WHERE project_active = 1 
AND projects.project_id  IN (" . implode( ',', $prjs ) . ") 
ORDER BY project_name
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  foreach ($rows as $a) {
	$project_id = $a["project_id"];
	$sql = "SELECT ROUND(SUM(task_log_hours),2) FROM task_log, tasks WHERE task_log_task = task_id AND task_project = $project_id AND task_milestone ='0'";
	$worked_hours = db_loadResult($sql);
	$worked_hours = rtrim($worked_hours, "0");

	$sql = "SELECT ROUND(SUM(task_duration),2) FROM tasks WHERE task_project = $project_id AND task_duration_type = 24 AND task_milestone ='0' AND task_dynamic = 0";
	$days = db_loadResult($sql);
	$sql = "SELECT ROUND(SUM(task_duration),2) FROM tasks WHERE task_project = $project_id AND task_duration_type = 1 AND task_milestone  ='0' AND task_dynamic = 0";
	$hours = db_loadResult($sql);
	$total_hours = $days * $dPconfig['daily_working_hours'] + $hours;
	$total_hours = rtrim($total_hours, "0");
	
	if($worked_hours > $total_hours){
          $xml.= "<project>";
	  $xml.= "<projectname><![CDATA[".$a["project_name"]."]]></projectname>";
	  $xml.= "<description><![CDATA[&nbsp;".$a["project_description"]."]]></description>";
	  $xml.= "<targetbudget><![CDATA[".$total_hours."]]></targetbudget>";
	  $xml.= "<actualbudget><![CDATA[".$worked_hours."]]></actualbudget>";
	  $xml.= "<start><![CDATA[".$a["project_start_datef"]."]]></start>";
	  $xml.= "<end><![CDATA[".$a["project_end_datef"]."]]></end>"; 
	  $xml.= "</project>";
	}
  }
}
}
if($xml != ""){
  $subject   = "Proyectos con horas trabajadas superiores a las horas totales.";
  $message   = "";
  $xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><projects>" . $xml;
  $xml.= "</projects>";
}
?>
