<?php /* PROJECTS $Id: im_log_projects.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
//GLOBAL AppUI;

include_once("modules/tasks/tasks.class.php");
$list = CTask::getPermissions($AppUI->user_id);

//Filtro los que tienen permisos para escribir logs
reset($list);
foreach ($list as $value){
	if ($value["task_permission_on"]==3 && $value["task_permission_value"]==-1){
		$task_list[] =  $value["task_id"];
	}
}
$rta = array();
if (count($task_list)!=0){
		
	$sql = "
	SELECT 	p.*, t.* 
	FROM 	tasks t INNER JOIN projects p 
	ON 		t.task_project = p.project_id
	WHERE	t.task_id IN (".implode($task_list,", ")." )
	AND		t.task_status = 0
	ORDER BY project_id, task_id
	;";
	
	$rta = db_loadlist($sql);
}
$print_header=true;
//echo count($rta)." Tareas obtenidas<br />"; 
//echo "<table border=\"1\">";
reset($rta);


echo "<" . "?xml version=\"1.0\" encoding=\"iso-8859-1\" ?" . ">\n";
echo "<psa>\n";
$lastpid=-1;


foreach($rta as $fila){
  if($fila["project_id"]!=$lastpid){
    if($lastpid!=-1) echo "</project>\n";
    $lastpid=$fila["project_id"];

    echo "<project>\n";
    echo "  <id>".$fila["project_id"]."</id>\n";
    echo "  <company>".$fila["project_company"]."</company>\n";
    echo "  <department>".$fila["project_department"]."</department>\n";
    echo "  <name>".$fila["project_name"]."</name>\n";
    echo "  <short_name>".$fila["project_short_name"]."</short_name>\n";
    echo "  <owner>".$fila["project_owner"]."</owner>\n";
    echo "  <url>".$fila["project_url"]."</url>\n";
    echo "  <demo_url>".$fila["project_demo_url"]."</demo_url>\n";
    echo "  <start_date>".$fila["project_start_date"]."</start_date>\n";
    echo "  <end_date>".$fila["project_end_date"]."</end_date>\n";
    echo "  <actual_end_date>".$fila["project_actual_end_date"]."</actual_end_date>\n";
    echo "  <status>".$fila["project_status"]."</status>\n";
    echo "  <percent_complete>".$fila["project_percent_complete"]."</percent_complete>\n";
    echo "  <color_identifier>".$fila["project_color_identifier"]."</color_identifier>\n";
    echo "  <description>".$fila["project_description"]."</description>\n";
  }
    echo "  <task>\n";
    echo "    <id>".$fila["task_id"]."</id>\n";
    echo "    <name>".$fila["task_name"]."</name>\n";
    echo "    <parent>".$fila["task_parent"]."</parent>\n";
    echo "    <milestone>".$fila["task_milestone"]."</milestone>\n";
    echo "    <owner>".$fila["task_owner"]."</owner>\n";
    echo "    <start_date>".$fila["task_start_date"]."</start_date>\n";
    echo "    <duration>".$fila["task_duration"]."</duration>\n";
    echo "    <duration_type>".$fila["task_duration_type"]."</duration_type>\n";
    echo "    <hours_worked>".$fila["task_hours_worked"]."</hours_worked>\n";
    echo "    <end_date>".$fila["task_end_date"]."</end_date>\n";
    echo "    <status>".$fila["task_status"]."</status>\n";
    echo "    <priority>".$fila["task_priority"]."</priority>\n";
    echo "    <percent_complete>".$fila["task_manual_percent_complete"]."</percent_complete>\n";
    echo "    <description>".$fila["task_description"]."</description>\n";
    echo "    <related_url>".$fila["task_related_url"]."</related_url>\n";
    echo "    <client_publish>".$fila["task_client_publish"]."</client_publish>\n";
    echo "    <notify>".$fila["task_notify"]."</notify>\n";
    echo "  </task>\n";
}
if($lastpid!=-1) echo "</project>\n";

echo "</psa>\n";

?>
