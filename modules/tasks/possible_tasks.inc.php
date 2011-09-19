<?
require_once("tasks.class.php");

$obj = new CTask();
$deny = $obj->getDeniedRecords($AppUI->user_id); 

$div = count($deny)/2;

for ($i=0; $i<$div; $i++)
{
      $deny_1[$i] = $deny[$i]; 
}

$cont = 0;

for ($i=$div ; $i< count($deny); $i++)
{
      $deny_2[$cont] = $deny[$i]; 
      $cont ++;
}

if(count($deny)>1000){
	$where =  "where task_id NOT IN ('" . implode( "','", $deny_1 ) . "') AND task_id NOT IN ('" . implode( "','", $deny_2 ) . "')";
}else{
$where = count($deny) > 0 ? "where task_id NOT IN ('" . implode( "','", $deny ) . "')" : '';
}

//$where = count($deny) > 0 ? "where task_id NOT IN ('" . implode( "','", $deny ) . "')" : '';
$sql = "select task_id from tasks $where";
//echo "<pre>SQL: $sql</pre>";
$list=db_loadColumn( $sql );
$tasklist= count($list) > 0 ? implode( ',', $list )  : '-10'; 


//echo "<pre>";print_r($AppUI); echo "</pre>";
/*
$perms = CTask::getPermissions($AppUI->user_id);
$deny = CTask::getDeniedRecords($AppUI->user_id);
$tasklist="";
for($i=0;$i<count($perms);$i++){
	if (!(in_array($perms[$i]['task_id'],$deny))){
		$tasklist.=", ".$perms[$i]['task_id'];
	}
}


if($tasklist=="")$tasklist="-10";


*/






/*
// Lista de tareas para el usuario [owner o tareas publicas]
$sqltmp = "
SELECT tasks.*
FROM tasks
WHERE task_owner = $AppUI->user_id
OR task_access = 0
";
$rall = db_exec( $sqltmp );
echo db_error();

while ($row = db_fetch_assoc( $rall )) {

  $task_id=$row["task_id"];
  $sqltmp = "
  SELECT tasks.*,
  	project_name, project_color_identifier,
  	u1.user_username as username,
  	SUM(task_log_hours) as log_hours_worked
  FROM tasks
  LEFT JOIN users u1 ON u1.user_id = task_owner
  LEFT JOIN projects ON project_id = task_project
  LEFT JOIN task_log ON task_log_task=$task_id

  WHERE task_id = $task_id
  GROUP BY task_id
  ";

  $obj = new CTask();
  db_loadObject( $sqltmp, $obj, true );


  if ($obj->canAccess( $AppUI->user_id )) {
     if($tasklist=="")$tasklist=$tasklist.$obj->task_id;
     else $tasklist=$tasklist.", ".$obj->task_id;
  }  
}

// Lista de tareas para el usuario [asignadas]
$sqltmp = "
SELECT user_tasks.*
FROM user_tasks
WHERE user_id = $AppUI->user_id
";

$rall = db_exec( $sqltmp );
echo db_error();


while ($row = db_fetch_assoc( $rall )) {
  $task_id=$row["task_id"];
  $sqltmp = "
  SELECT tasks.*,
  	project_name, project_color_identifier,
  	u1.user_username as username,
  	SUM(task_log_hours) as log_hours_worked
  FROM tasks
  LEFT JOIN users u1 ON u1.user_id = task_owner
  LEFT JOIN projects ON project_id = task_project
  LEFT JOIN task_log ON task_log_task=$task_id

  WHERE task_id = $task_id
  GROUP BY task_id
  ";
  $obj = new CTask();
  db_loadObject( $sqltmp, $obj, true );


  if ($obj->canAccess( $AppUI->user_id )) {
     if($tasklist=="")$tasklist=$tasklist.$obj->task_id;
     else $tasklist=$tasklist.", ".$obj->task_id;
  }  
}

if($tasklist=="")$tasklist="-10";
*/






?>