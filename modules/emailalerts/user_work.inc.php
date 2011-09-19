<?
$subject   = "Trabajo Pendiente.";
$message   = "";

$sql = "
SELECT project_todo.*, projects.project_name as project, DATE_FORMAT(project_todo.date,'%d/%m/%Y') as datef, DATE_FORMAT(project_todo.due_date,'%d/%m/%Y') as due_datef
FROM project_todo, projects
WHERE projects.project_id = project_todo.project_id
AND project_todo.status <> '1'
AND user_owner = '$user_id'
ORDER BY date
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $xml.= "<todos>";
  foreach ($rows as $a) {
        $priority="";
	if($langpref=="en"){
        	if($a["priority"]==1) $priority="High";
		else if($a["priority"]==2) $priority="Normal";
		else if($a["priority"]==3) $priority="Low";
	}
	else{
        	if($a["priority"]==1) $priority="Alta";
		else if($a["priority"]==2) $priority="Normal";
		else if($a["priority"]==3) $priority="Baja";
	}
	$xml.= "<todo>";
	$xml.= "<date><![CDATA[".$a["datef"]."]]></date>";
	$xml.= "<duedate><![CDATA[".$a["due_datef"]."]]></duedate>";
	$xml.= "<priority><![CDATA[".$priority."]]></priority>";
	$xml.= "<project><![CDATA[".$a["project"]."]]></project>";
	$xml.= "<description><![CDATA[".$a["description"]."]]></description>";
	$xml.= "</todo>";
  }
  $xml.= "</todos>";
}


if($params=="") $params = "7";
$sql = "SELECT * FROM btpsa_user_table WHERE user_id = '$user_id'";
$rowsu = db_loadList( $sql, NULL );
if (count( $rowsu)) {
   foreach ($rowsu as $a) { $btuser_id = $a["id"];}

   $projectid   = $a["project_id"];
   $projectname = $a["project"];
   $sql = "
   SELECT btpsa_bug_table.*, projects.project_name as project, DATE_FORMAT(btpsa_bug_table.date_submitted,'%d/%m/%Y') as date_submittedf
   FROM btpsa_bug_table, projects
   WHERE btpsa_bug_table.project_id = projects.project_id
   AND btpsa_bug_table.handler_id = '$btuser_id'
   AND btpsa_bug_table.status <> '80'
   AND btpsa_bug_table.status <> '90'
   ";

   $rowsb = db_loadList( $sql, NULL );
   if (count( $rowsb)) {
    $xml.= "<bugs>";
    foreach ($rowsb as $b) {
        $projectname = $b["project"];
	$xml.= "<bug>";
	$xml.= "<date>".$b["date_submittedf"]."</date>";
	$xml.= "<summary><![CDATA[".$b["summary"]."]]></summary>";
	$xml.= "<project><![CDATA[".$projectname."]]></project>";
	$xml.= "</bug>";
    }
    $xml.= "</bugs>";
   }
}


$sql = "
SELECT tasks.*, projects.project_name as project, DATE_FORMAT(tasks.task_start_date,'%d/%m/%Y') as task_start_datef, DATE_FORMAT(tasks.task_end_date,'%d/%m/%Y') as task_end_datef
FROM user_tasks, tasks, projects
WHERE tasks.task_id = user_tasks.task_id
AND projects.project_id = tasks.task_project
AND tasks.task_complete = '0'
AND user_tasks.user_id = '$user_id'
ORDER BY task_start_date
";
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
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

if($xml!="") $xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><work>".$xml."</work>";

?>
