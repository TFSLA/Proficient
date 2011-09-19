<?
require_once("getadminprojects.inc.php");
$prjs = getAdminProjects($user_id);
if (count($prjs)) {

if($params=="") $params = "5";
$params2 = $params-1;
$sql = "
SELECT tasks.*, projects.project_name as project, DATE_FORMAT(tasks.task_start_date,'%d/%m/%Y') as task_start_datef, DATE_FORMAT(tasks.task_end_date,'%d/%m/%Y') as task_end_datef
FROM user_tasks, tasks, projects
WHERE tasks.task_id = user_tasks.task_id
AND projects.project_id = tasks.task_project
AND projects.project_id  IN (" . implode( ',', $prjs ) . ") 
AND tasks.task_milestone = '1'
AND SUBSTRING(tasks.task_end_date,1,10) <= DATE_ADD(CURDATE(), INTERVAL ". $params ." DAY)
AND SUBSTRING(tasks.task_end_date,1,10) > DATE_ADD(CURDATE(), INTERVAL ". $params2 ." DAY)
ORDER BY task_start_date 
";

//AND tasks.task_complete = '0'

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Tareas Hito por vencer en ".$param." dias.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
  $xml.= "<tasks>";
  foreach ($rows as $a) {
	$xml.= "<task>";
	$xml.= "<project><![CDATA[".$a["project"]."]]></project>";
	$xml.= "<name><![CDATA[".$a["task_name"]."]]></name>";
	$xml.= "<startdate><![CDATA[".$a["task_start_datef"]."]]></startdate>";
	$xml.= "<enddate><![CDATA[".$a["task_end_datef"]."]]></enddate>";
	$xml.= "</task>";
  }
  $xml.= "</tasks>";
}

}
?>
