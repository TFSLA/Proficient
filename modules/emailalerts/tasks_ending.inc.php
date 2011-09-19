<?  
if($params=="") $params = "5";
$params2 = $params - 1;
$sql = "
SELECT tasks.*, projects.project_name as project, DATE_FORMAT(tasks.task_end_date,'%d/%m/%Y') as edatef, DATE_FORMAT(tasks.task_start_date,'%d/%m/%Y') as sdatef
FROM user_tasks, tasks, projects
WHERE tasks.task_id = user_tasks.task_id
AND projects.project_id = tasks.task_project
AND tasks.task_complete = '0'
AND user_tasks.user_id = '$user_id'
AND SUBSTRING(tasks.task_end_date,1,10) <= DATE_ADD(CURDATE(), INTERVAL ". $params ." DAY)
AND SUBSTRING(tasks.task_end_date,1,10) > DATE_ADD(CURDATE(), INTERVAL ". $params2 ." DAY)
ORDER BY task_start_date 
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Tareas con fecha limite en ".$param." dias.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
  $xml.= "<tasks>";
  foreach ($rows as $a) {
	$xml.= "<task>";
	$xml.= "<project><![CDATA[".$a["project"]."]]></project>";
	$xml.= "<name><![CDATA[".$a["task_name"]."]]></name>";
	$xml.= "<startdate><![CDATA[".$a["sdatef"]."]]></startdate>";
	$xml.= "<enddate><![CDATA[".$a["edatef"]."]]></enddate>";
	$xml.= "</task>";
  }
  $xml.= "</tasks>";
}


?>
