<?
/**
* Script que va a corregir error en el campo dynamic en la tabla de tareas.
* 
*/

// Trae todos los proyectos

$query_project = "SELECT project_id, project_name FROM projects";
$sql_project =  db_exec($query_project);

// Primero reseteo el campo dynamic de todas las tareas del proyecto
$query_update = "UPDATE tasks SET task_dynamic = '0' ";
$sql_update = db_exec($query_update);

echo "Correcci&oacute;n tabla tasks<br><br>";

while ($vec_project = mysql_fetch_array($sql_project))
{  
	echo "<B>Proyecto [".$vec_project['project_id']."] - ".$vec_project['project_name']." :</B><br>";
	
	// Por cada proyecto , trae todas las tareas
    $query_tasks = "SELECT task_id, task_parent FROM tasks WHERE task_project = '".$vec_project['project_id']."' ";
    $sql_tasks = db_exec($query_tasks);
    
    $cont = 0;
    $tareas = 0;
    
    while($vec_task = mysql_fetch_array($sql_tasks))
    {
	    // Con cada tarea se va a fijar si tiene hijas si es asi la marca como dinamica = 1, de lo contrario le pone dinamica = 0
	    
	    if ($vec_task['task_id'] != $vec_task['task_parent'])
	    {   
	    	// Me fijo si existe la tarea padre
	        $query_ck = "SELECT count(*) FROM tasks WHERE task_id = '".$vec_task['task_parent']."' ";
	        $sql_ck = db_loadColumn($query_ck);
	        
	        if($sql_ck['0'] > '0')
	        {
	        	$cont = $cont + 1;
	    	    $query_update = "UPDATE tasks SET task_dynamic = '1' WHERE task_id = '".$vec_task['task_parent']."' ";
	    	    $sql_update = db_exec($query_update);
	        }else{
	        	$query_update = "UPDATE tasks SET task_parent = '".$vec_task['task_id']."'  WHERE task_id = '".$vec_task['task_id']."' ";
	    	    $sql_update = db_exec($query_update);
	        }
	        
	    	
	    }
	    
	    $tareas = $tareas + 1;
    
    }
    
    echo "Total de tareas : ".$tareas."<br>";
    echo "Tareas hijas : ".$cont."<br><br>";
    
	 
}

echo "Actualizaci&oacute;n terminada.";

?>
	
