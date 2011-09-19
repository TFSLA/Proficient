<?php /* PROJECTS $Id: do_import_tasks_save.php, Importacion de tareas desde un proyecto, procesa las tareas y las guarda  */

global $AppUI;

unset($AppUI->task_pred);
unset($AppUI->tasks_sort);
unset($AppUI->task_suces);
unset($AppUI->tasks);

require_once( "./modules/tasks/functions.php" );

  // Preparo los recursos
  if(count($_POST[resources])>0)
  {
  	 foreach ($_POST[resources] as $id_MSp => $id_psa)
  	 {
  	 $AppUI->MsProject_Resources[$id_MSp][id_proficient] = $id_psa;
  	 }
  }
  
$project_id = $_POST[project_id];
$project_owner = $_POST[task_owners];
$task_access = $_POST[task_access];

// Verifico que un usuario no este asignado mas de una vez a una misma tarea
if(count($AppUI->MsProject_tasks)>0)
{
	foreach ($AppUI->MsProject_tasks as $uid_project => $task_project)
    {
    	$id_user_proficient = "";
    	
    	if (count($task_project[resources])>0)
    	{
    		foreach ($task_project[resources] as $id_user_project=> $values_resources)
    		{
    			$id_user_proficient = $AppUI->MsProject_Resources[$id_user_project][id_proficient];
    			
    			if(!isset($users_proficient_tasks[$id_user_proficient][$uid_project])){
    			$users_proficient_tasks[$id_user_proficient][$uid_project][units]=$AppUI->MsProject_Resources[$id_user_project][units];
    			}else{
    				$msg = "No se importaron las tareas, es necesario asignar mas usuarios.";
    				$AppUI->setMsg( $msg, UI_MSG_ERROR );
    				
    				$AppUI->redirect("m=projects&a=view&project_id=$project_id");
    			}
    			
    		}
    	}
		
    }
    
}


//echo "Recursos<pre>";
//   print_r($AppUI->MsProject_Resources);
//echo "</pre>";

//echo "Tareas<pre>";
//   print_r($AppUI->MsProject_tasks);
//echo "</pre>";

// Ordeno las tareas de acuerdo a su wbs
if(count($AppUI->MsProject_tasks)>0)
{
	foreach ($AppUI->MsProject_tasks as $uid_project => $task_project)
    {
		$ord_project_tasks[$task_project[wbs]][uid] = $uid_project;
    }
    
}

	// Recorro el vector de tareas y voy procesandolas en el vector de tareas
	foreach ($ord_project_tasks as $id_wbs => $uid_task_project)
	{
		  $task_project = $AppUI->MsProject_tasks[$uid_task_project[uid]];
		  
		  $obj = new CTask();

		  ##--- Campos de fechas ---##
		  
		  $date = new CDate( $task_project[start_date] );
		  $obj->task_start_date = $date->format( FMT_DATETIME_MYSQL );
		  
		  $date = new CDate( $task_project[end_date] );
		  $obj->task_end_date = $date->format( FMT_DATETIME_MYSQL );
		  
		  if ($task_project[dynamic] == "1")
		  {
		    $obj->task_constraint_date = "00000000000000";
		    $obj->task_constraint_type = "3";
		    $obj->task_dynamic = "1";
		    $date = "";
		    $obj->task_constraint_date = "";
		  }
		  else 
		  {
		  	$obj->task_constraint_date = $task_project[constraint_date];
		    $obj->task_constraint_type = $task_project[constraint_type];
		    $obj->task_dynamic = "0";
		    
		    if($task_project[constraint_type] != '3' && $task_project[constraint_type] != '4' )
		    {
		       $date = new CDate( $obj->task_constraint_date );
	           $obj->task_constraint_date = $date->format( FMT_DATETIME_MYSQL );
		    }else{
		       $date = "";
		       $obj->task_constraint_date = "";
		    }
		  }
		  
		  
	      ##--- Tarea padre ---##
	      $uid_project = $task_project[task_parent];
	      $obj->task_parent = $AppUI->MsProject_tasks[$uid_project][id_proficient];
	      
	      
	      ##--- WBS ---##
	      $obj->task_wbs_level = $task_project[wbs_level] - 1;
	      
	      $vec_wbs_number = explode(".",$task_project[wbs_number]);
	      $cant_wbs_number = count($vec_wbs_number);
	      $obj->task_wbs_number = $vec_wbs_number[$cant_wbs_number-1];
	      
	      ##--- Lista de dependencias --##
	      
	      if(count($task_project[predecesoras])>0)
	      {
	       $list_dependencies = "";
	      
	           foreach ($task_project[predecesoras] as $id_vec => $id_pred)
	           {
	           	 if($id_pred >0)
	           	 {
	           	   $list_dependencies = $list_dependencies.",".$AppUI->MsProject_tasks[$id_pred][id_proficient];
	           	 }
	           }
	      
	       $obj->_dependencies_list = substr($list_dependencies,1);
	      }
		  
	      $obj->task_id = 0;
	      $obj->task_project = $project_id;
	      $obj->task_name = $task_project[name];
	      $obj->task_type = $task_project[type];
	      $obj->task_priority = $task_project[priority];
	      $obj->task_duration = $task_project[duration] / 8;
	      $obj->task_duration_type = "24";
	      $obj->task_work = $task_project[work];
	      $obj->task_effort_driven = $task_project[effortdriven];
          $obj->task_milestone = $task_project[milestone];
          $obj->task_owner = $project_owner;
          $obj->task_access = $task_access;
          $obj->task_notify = "1";
		
		
		  if (($msg = $obj->store())) {
				//echo $obj->task_id." - Error , no se guardo <br>";
			} else {
				//echo $obj->task_id." - Se guardo <br>";
			}
		 
		  $AppUI->MsProject_tasks[$uid_task_project[uid]][id_proficient] = $obj->task_id;
		  
		  // Guardo el porcentaje de completitud
		  $sql_percent = "UPDATE tasks SET task_manual_percent_complete = '".$task_project[percent_complete]."' WHERE task_id='".$obj->task_id."' ";
		  db_exec($sql_percent);
		  
		  ##--- Asigno los recursos en cada tarea ---##
		  
		  $resources = $AppUI->MsProject_tasks[$uid_task_project[uid]][resources];
		  $users_task = "";
	      $users_units = "";
	      
		  if (count($resources)>0)
		  {
		  	   foreach ($resources as $id_user_in_project=> $values) 
		  	   {
		  	   	   $users_task[] = $AppUI->MsProject_Resources[$id_user_in_project][id_proficient];
		  	   	   $users_units[] = $values[units];
		  	   }
		  	   
		  	   if (isset($users_task) && isset($users_units)) 
	           {
		         $obj->updateAssigned( $users_task, $users_units );
	           }
		  }
		  
		  $project_start_date = $AppUI->MsProject_tasks[$uid_task_project[uid]][project_start];
		   
	}
 	
	//Actualizo la fecha de inicio del proyecto
	$date_project = new CDate( $project_start_date );
	$query_project = "UPDATE projects SET project_start_date= '".$date_project->format( FMT_DATETIME_MYSQL )."' WHERE project_id='".$project_id."' ";
		   	
	$sql_project = db_exec($query_project);
	
	$tareas_wbs = recalcula_wbs(0,'', $project_id);
 
	foreach ($tareas_wbs as $k => $val)
	{
	 	$sql_wbs =" UPDATE tasks set task_wbs_number = '".$val[number]."', task_wbs_level = '".$val[level]."' WHERE task_id ='".$k."' ";
	 	db_exec($sql_wbs);
	} 
	
	// Traigo las tareas recien creadas, para acomodarles las dependencias y los recursos
	$query = "SELECT task_id, task_parent, task_dynamic FROM tasks WHERE task_project = '".$project_id."' AND task_dynamic = '1' order by task_id asc ";
	
	$sql = db_exec($query);
	
	while($vec = mysql_fetch_array($sql))
	{
		$query2 = "DELETE FROM user_tasks WHERE task_id = '".$vec[task_id]."' ";
		   	
		$sql2 = db_exec($query2);
	}
	
	
	GetDateTaTe($project_id, false);
 
    $project_tasks = $AppUI->tasks;
    
    
    foreach($project_tasks as $key=> $tareas)
	 {
	   
	   if ($tareas['parent'] =="" || $tareas['parent'] =="0" )
	    {
	   	   $tareas['parent'] = $key;
	    }
	     
	   if ($key != "")
	   {
		   $query = " UPDATE tasks SET 
		   task_ftei = '".$tareas[FTeI]."',
		   task_ftef = '".$tareas[FTeF]."',
		   task_ftai = '".$tareas[FTaI]."',
		   task_ftaf = '".$tareas[FTaF]."',
		   task_start_date = '".$tareas[start_date]."',
		   task_end_date = '".$tareas[end_date]."',
		   task_duration = '".$tareas[duration]."',
		   task_work = '".$tareas[work]."',
		   task_duration_type = '".$tareas[duration_type]."',
		   task_parent = '".$tareas['parent']."',
		   task_constraint_date = '".$tareas['task_constraint_date']."',
		   task_constraint_type = '".$tareas['task_constraint_type']."'
		   WHERE task_id= '$key'
		   "; 
		   
		   //echo "<pre>".$query."</pre>";
		   
		   db_exec($query);
		   
		   if( $tareas[dynamic] == "1")
		   {
		   	$query2 = "DELETE FROM user_tasks WHERE task_id = '$tareas[id]' ";
		   	//echo "<pre>$query2</pre>";
		   	
		   	$sql2 = db_exec($query2);
		   }
		   
	   }
	   
	 }
	 
	 $AppUI->redirect("m=projects&a=view&project_id=$project_id");

?>
