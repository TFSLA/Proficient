<?php /* PROJECTS $Id: do_import_tasks.php, Importacion de tareas desde un proyecto  */

global $AppUI;

$upload_dir = $AppUI->getConfig('tasks_uploads_dir');

$format_acept = false;
$upload_file = false;

echo "<pre>";
  print_r($_FILES);
echo "</pre>";

// Si el archivo es del tipo aceptado lo guardo
if($_FILES['formfile']['type']=="text/xml")
{
  $format_acept = true;
}

if($format_acept)
{
	if (!is_dir($upload_dir)){
		mkdir($upload_dir,0755);
	}
	
     $import_file =@$_FILES['formfile'];
     
	if ($import_file[size]!=0)
	{
			move_uploaded_file($import_file['tmp_name'], $upload_dir . "/". $import_file['name']); 
			$file = $upload_dir . "/". $import_file['name'];
			$upload_file = true;
	}
}

// Si fue subido el archivo en formato xml, lo proceso
if ($_FILES['formfile']['type']=="text/xml" && $upload_file)
{
	require_once("functions/xml_to_php.php");
	
	$xml_parser = new Simple_Parser;
    $xml_parser->parse(file_get_contents($file));
    
    $project = $xml_parser->data[PROJECT][0][child];
    $project_start_date = substr($xml_parser->data[PROJECT][0][child][STARTDATE][0][data],0,4).substr($xml_parser->data[PROJECT][0][child][STARTDATE][0][data],5,2).substr($xml_parser->data[PROJECT][0][child][STARTDATE][0][data],8,2)."0900";
    
    echo "Inicio del proyecto: $project_start_date <br>";
    
    $tasks = $project[TASKS][0][child][TASK];
    $resources = $project[RESOURCES][0][child][RESOURCE];
    $asignaciones = $project[ASSIGNMENTS][0][child][ASSIGNMENT];
    
    
    // Armo el vector de recursos
    foreach ($resources as $key_r => $recurso)
    {
    	$MSP_idResource = $recurso[child][UID][0][data];
    	
    	if ($MSP_idResource > 0)
    	{
    		$MSP_Resources[$MSP_idResource][name] = utf8_decode($recurso[child][NAME][0][data]);
    	}
    }
    
    echo "Recursos del proyecto:<pre>";
       print_r($MSP_Resources);
    echo "</pre><br>";
    
    $AppUI->MsProject_Resources = $MSP_Resources;
    
    // Recorro el vec de asignaciones
    foreach ($asignaciones as $key_as=>$asignacion)
    {
    	$MSP_UID_asignaciones = $asignacion[child][TASKUID][0][data];
    	
    	if($MSP_UID_asignaciones > 0)
    	{
    		$units = $asignacion[child][UNITS][0][data] * 100;
    		
    		if ($asignacion[child][RESOURCEUID][0][data] > 0){
    		$MSP_Asign[$asignacion[child][TASKUID][0][data]][$asignacion[child][RESOURCEUID][0][data]][name] = $MSP_Resources[$asignacion[child][RESOURCEUID][0][data]][name];
    		$MSP_Asign[$asignacion[child][TASKUID][0][data]][$asignacion[child][RESOURCEUID][0][data]][units] = $units;
    		}
    	}
    }
    
    echo "Asignaciones del proyecto:<pre>";
       print_r($MSP_Asign);
    echo "</pre><br>";
    
    /*echo "Tareas del proyecto:<pre>";
       print_r($tasks);
    echo "</pre><br>";*/
    
    // Recorro el vector de tareas
    foreach ($tasks as $key=>$tarea)
    {
    	$MSP_id =  $tarea[child][ID][0][data];
    	
    	if($MSP_id >0)
    	{
    		$MSP_UID = $tarea[child][UID][0][data];
	    	$MSP_name = utf8_decode($tarea[child][NAME][0][data]);
	    	$MSP_type = $tarea[child][TYPE][0][data];
	    	$MSP_wbs = $tarea[child][WBS][0][data];
	    	$MSP_wbs_number = $tarea[child][OUTLINENUMBER][0][data];
	    	$MSP_wbs_level = $tarea[child][OUTLINELEVEL][0][data];
	    	$MSP_priority = $tarea[child][PRIORITY][0][data];
	    	$MSP_start_date = substr($tarea[child][START][0][data],0,4).substr($tarea[child][START][0][data],5,2).substr($tarea[child][START][0][data],8,2).substr($tarea[child][START][0][data],11,2).substr($tarea[child][START][0][data],14,2);
	    	$MSP_end_date = substr($tarea[child][FINISH][0][data],0,4).substr($tarea[child][FINISH][0][data],5,2).substr($tarea[child][FINISH][0][data],8,2).substr($tarea[child][FINISH][0][data],11,2).substr($tarea[child][FINISH][0][data],14,2);
	    	$MSP_duration = substr(substr($tarea[child][DURATION][0][data],2),0,strpos(substr($tarea[child][DURATION][0][data],2),"H"));
	    	$MSP_duration_type = $tarea[child][DURATIONFORMAT][0][data];
	    	$MSP_work = substr(substr($tarea[child][WORK][0][data],2),0,strpos(substr($tarea[child][WORK][0][data],2),"H"));
	    	$MSP_effortdriven = $tarea[child][EFFORTDRIVEN][0][data];
	    	$MSP_milestone = $tarea[child][MILESTONE][0][data];
	    	$MSP_percent_complete = $tarea[child][PERCENTCOMPLETE][0][data];
	    	$MSP_constraint_type = $tarea[child][CONSTRAINTTYPE][0][data];
	    	$MSP_constraint_date = substr($tarea[child][CONSTRAINTDATE][0][data],0,4).substr($tarea[child][CONSTRAINTDATE][0][data],5,2).substr($tarea[child][CONSTRAINTDATE][0][data],8,2).substr($tarea[child][CONSTRAINTDATE][0][data],11,2).substr($tarea[child][CONSTRAINTDATE][0][data],14,2);
	    	$MSP_predecesoras = $tarea[child][PREDECESSORLINK];
	    	$MSP_resources = $MSP_Asign[$MSP_UID];
	    	
	    	
	    	echo "<br>Tarea $MSP_id - $MSP_name<br>";
	    	echo "UID: $MSP_UID<br>";
	    	echo "Tipo: $MSP_type<br>";
	    	echo "WBS: $MSP_wbs<br>";
	    	echo "WBS Number: $MSP_wbs_number<br>";
	    	echo "WBS Level: $MSP_wbs_level<br>";
	    	echo "Priority: $MSP_priority<br>";
	    	echo "Fecha de inicio: $MSP_start_date<br>";
	    	echo "Fecha de fin: $MSP_end_date<br>";
	    	echo "Duracion: ".$MSP_duration."<br>";
	    	echo "Tipo de duracion: $MSP_duration_type<br>";
	    	echo "Trabajo: $MSP_work<br>";
	    	echo "Tipo de constraint: $MSP_constraint_type<br>";
	    	echo "Fecha de constraint: $MSP_constraint_date<br>";
	    	echo "Porcentaje: $MSP_percent_complete<br>";
	    	
	    	   // Armo la wbs del padre para identificarlo
	    	   $level_parent = $MSP_wbs_level - 1;
	    	   $vec_parent = explode(".",$MSP_wbs_number);
	           $wbs_parent_tmp = "";
	        
		        for($i=0; $i<$level_parent; $i++)
		        {
		        	$wbs_parent_tmp = $wbs_parent_tmp.".".$vec_parent[$i];
		        }
	        
	        $wbs_parent = substr($wbs_parent_tmp,1);
	        
	    	echo "WBS del padre: $wbs_parent <br><br>";
	    	
	    	// Armo el vector de predecesoras
	    	$vec_pred = array();
	    	
	    	if(count($MSP_predecesoras)>0)
	    	{
	    		
	    		$k = 0;
	    		
	         	foreach ($MSP_predecesoras as $kd=>$pred)
	         	{
	         		$vec_pred[$k] = $pred[child][PREDECESSORUID][0][data];
	         		$k = $k + 1;
	         	}
	         	
	         	echo "Vector de predecesoras procesado: <pre>";
	    	      print_r($vec_pred);
	    	    echo "</pre>";
	    	}
	    	
	    	echo "Recursos:<br><pre>";
	    	  print_r($MSP_resources);
	    	echo "</pre>";
	    	
	    	$MSproject_tasks[$MSP_UID][name] = $MSP_name;
    		$MSproject_tasks[$MSP_UID][type] = $MSP_type;
    		$MSproject_tasks[$MSP_UID][wbs] = $MSP_wbs;
    		$MSproject_tasks[$MSP_UID][wbs_number] = $MSP_wbs_number;
    		$MSproject_tasks[$MSP_UID][wbs_level] = $MSP_wbs_level;
    		$MSproject_tasks[$MSP_UID][wbs_parent] = $wbs_parent;
    		$MSproject_tasks[$MSP_UID][priority] = $MSP_priority;
    		$MSproject_tasks[$MSP_UID][start_date] = $MSP_start_date;
    		$MSproject_tasks[$MSP_UID][end_date] = $MSP_end_date;
    		$MSproject_tasks[$MSP_UID][duration] = $MSP_duration;
    		$MSproject_tasks[$MSP_UID][duration_type] = $MSP_duration_type;
    		$MSproject_tasks[$MSP_UID][work] = $MSP_work;
    		$MSproject_tasks[$MSP_UID][effortdriven] = $MSP_effortdriven;
    		$MSproject_tasks[$MSP_UID][milestone] = $MSP_milestone;
    		$MSproject_tasks[$MSP_UID][constraint_type] = $MSP_constraint_type;
    		$MSproject_tasks[$MSP_UID][constraint_date] = $MSP_constraint_date;
    		$MSproject_tasks[$MSP_UID][predecesoras] = $vec_pred;
    		$MSproject_tasks[$MSP_UID][resources] = $MSP_resources;
    		$MSproject_tasks[$MSP_UID][id_proficient] = "";
    		$MSproject_tasks[$MSP_UID][dynamic] = "0";
    		$MSproject_tasks[$MSP_UID][task_parent] = "";
    		$MSproject_tasks[$MSP_UID][project_start] = $project_start_date;
    		$MSproject_tasks[$MSP_UID][percent_complete] = $MSP_percent_complete;
	    	
    	}
    	
    	$AppUI->MsProject_tasks = $MSproject_tasks;
    	 
    }
    
    
    /*
    echo "<br>Tareas<pre>";
      print_r($tasks);
    echo "</pre>";*/
    
    /*echo "<br>Recursos: <pre>";
      print_r($resources);
    echo "</pre>";*/
    
    /*echo "<br>Asignaciones: <pre>";
      print_r($asignaciones);
    echo "</pre>"; */
    
    echo "<br>Tareas a importar:<pre>";
      print_r($AppUI->MsProject_tasks);
    echo "</pre>";
    
}


if(count($AppUI->MsProject_tasks)>0)
{
	$AppUI->redirect("m=projects&a=import_tasks2&task_project=".$_POST['project_id']);
}else{
	$msg = "Hubo un error, no hay tareas para importar";
	
 	$AppUI->setMsg( $msg, UI_MSG_ERROR );
}


?>