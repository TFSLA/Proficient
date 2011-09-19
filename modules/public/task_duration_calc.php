<?php 

require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'system' ) );

$hmtl_buffer = ob_get_contents();
ob_clean();

$tiempo_micro1[1]= microtime(); 
$q_espacios1 = explode(" ",$tiempo_micro1[1]); 
$tiempo1_[1]= $q_espacios1[1]+$q_espacios1[0]; 
echo "Entra a Task_duration_calc : ".date('H:i:s')."<br>";

$df = $AppUI->getPref('SHDATEFORMAT');	
$tf = $AppUI->getPref('TIMEFORMAT');
$tsformat = "%Y%m%d%H%M";
import_request_variables("G","");

echo "<B><U>Datos recibidos:</U></B><br>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

if($_GET[task_constraint_date_]=="0900")
{
	$_GET[task_constraint_date_] = "";
}


//echo "Vector de tareas recibido <pre>"; print_r($task_tmp); echo "</pre>";
   
$js_string ='';

echo "<B><U>Modificaron el campo:</U></B> ".$_GET[action]."<br>";

// Cargo las fechas en el calendario del sistema

$start_date= new CWorkCalendar(0,"","", $task_start_date);
$end_date= new CWorkCalendar(0,"","", $task_end_date );	
$duration =  $task_duration;


    // Muestro los datos recibidos para control //
    echo "<br><U><B>Datos recibidos:</B></U><br><br>";
    echo "Projecto: ".$task_project."<br>";
    echo "Start_date: ".$start_date->format(FMT_TIMESTAMP);
    echo "<br>End_date: &nbsp;".$end_date->format(FMT_TIMESTAMP);
    echo "<br>Duración: ".$duration;
    echo "<br>Unidades :";
	print_r($units);
    echo "<br>Accion: ".$action."<br>";
	//echo "Calendario del usuario : ".$user_id."<br>";
    echo "<pre>";
	//print_r($start_date);
    echo "</pre>";
    
    if (count($AppUI->tasks) > 0 && $_GET['firstTime'] == '0')
    {
    	$firstItem = reset($AppUI->tasks);
    	    	
    	if($firstItem[project] == $task_project)
    	{
			$project_tasks = $AppUI->tasks;
		}
		else
		{
			unset($AppUI->task_pred);
			unset($AppUI->tasks_sort);
			unset($AppUI->task_suces);
			unset($AppUI->tasks);
			
			$project_tasks = GetDateTaTe($task_project,false);

			if ($_GET['firstTime'] == '0')
				$AppUI->redirect($_SERVER['QUERY_STRING']);
		}
	}
	else
	{
			unset($AppUI->task_pred);
			unset($AppUI->tasks_sort);
			unset($AppUI->task_suces);
			unset($AppUI->tasks);
			
			$project_tasks = GetDateTaTe($task_project,false);
			
			if ($_GET['firstTime'] == '0')
				$AppUI->redirect($_SERVER['QUERY_STRING']);
	}

   // echo "Vector de tareas inicial <br><pre>";
   // print_r($AppUI->tasks);
   // echo "</pre>";
    
	// Se fija si hay usuarios asignados y los mete en un vector//
	if(count($units)>0){
		$users_assigned = array_keys($units);
	}
	
	echo "<br><u>Usuarios asignados:</u><br>";
	echo "task_effort_driven: ".$task_effort_driven."<br>";
	
	$AppUI->tasks[$task_id][task_effort_driven] = $task_effort_driven;
	
	// Si no es condicionada por el esfuerzo, le saco los recursos y solo le dejo asignado al owner de la tarea
	if(!$task_effort_driven && count($units)>1){
	       echo "Owner de la tarea: ".$task_owner."<br>";
	       
	       $users_assigned_orig = $users_assigned;
	       $units_orig = $units;
	       
	       unset($users_assigned);
	       unset($units);
	       
	       $units[$task_owner] = 100;
	       $users_assigned = array_keys($units);
	       
	       echo "<pre>"; print_r($units); echo "</pre>";
	}
           $obj = new CProject();
   
	// Me fijo que traiga el id del proyecto, de lo contrario lo saco de esta página
	if (!$obj->load( $task_project ) || !isset($action) ) 
		{
		$AppUI->setMsg( 'Project' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
		}


	// Traigo los datos del proyecto //
	$project_start_date = new CDate($obj->project_start_date); // Fecha de inicio del proyecto
	$project_end_date = new CDate($obj->project_end_date); // Fecha de fin del proyecto
	$project_actual_end_date = new CDate($obj->project_actual_end_date); // Fecha de fin real del proyecto
    
    //echo "<pre>";print_r($obj);echo "</pre><br>";

	$msg="";
    
	echo "<br><br><U><B>Datos del proyecto</B></U><br><br>";
	echo "Inicio del proyecto: ".$project_start_date->format(FMT_TIMESTAMP);
    
	
           /* ----- obtengo las tareas que tienen a la actual (o a sus padres) como predecesora ---- */

	// Todas las tareas dependientes a un array
	$dep_tasks = explode(",",$dependencies);

	// si tiene una tarea padre y no es la misma tarea 
	if($task_parent!=$task_id && $task_parent > 0)
	{
		$objTask = new CTask();
		if($objTask->load($task_parent))
		{
			// obtengo las dependencias de esa tarea padre 
			$parent_dep = explode(",",$objTask->getDependencies());
			$dep_tasks = array_merge($dep_tasks, $parent_dep);
		}
		
	}
    
   
    echo "<br><br>Dependecias : <pre>";
    print_r($dep_tasks);
    echo "</pre><br>";
    
    if (count($dep_tasks)>1)
    {
    	$cant_dep = 0;
    	
    	foreach ($dep_tasks as $ind=>$id_dep )
    	{
    	    if ($id_dep != "")
    	    {
    	    	$dep_tasks_c[$cant_dep] = $dep_tasks[$ind];
    	    	$cant_dep = $cant_dep + 1;
    
    	    }
    	}
    	
    	$dep_tasks = $dep_tasks_c;
    }
    
    
    
    echo "<br><br>Dependecias limpia: <pre>";
    print_r($dep_tasks);
    echo "</pre><br>";
    
    if (count($dep_tasks)>0)
    {
	    // Con la fecha de inicio del proyecto y las dependencias establezco cual sera la fecha de inicio minima que podra tener esta tarea.
							
	    $fecha_min = "00000000000000";
	    
	    foreach ($dep_tasks as $ind=>$id_dep) 
	    {
	    	if($id_dep != "")
	    	{
	    		if ($AppUI->tasks[$id_dep][end_date] > $fecha_min)
	    		{
	    		$fecha_min = $AppUI->tasks[$id_dep][end_date];
	    		}
	    	}
	    }
    }
    
    if($fecha_min == "00000000000000" || $fecha_min =="" )
	{
	   $fecha_min = $project_start_date->format(FMT_TIMESTAMP_DATE)."090000";
	}
	
	// Mi fijo que no caiga en el fin del turno.
	
	$turno = hora_turno($AppUI->tasks[$task_id], $fecha_min , "0", "1", true);
	
	if($turno != "")
	{
	  $fecha_min = $turno;
	}
	
	$fecha_min = new CWorkCalendar(0,"","", $fecha_min );
	
    
    // Meto las dependencias en el vector de predecesoras
	if ($task_id !=''){
	   $AppUI->tasks_predecesoras[$task_id] = $dep_tasks;
    }
    
    // Actualizo el vector de sucesoras
	for ($i=0;$i<count($dep_tasks);$i++)
	{  
	   if($dep_tasks[$i]!='' && $task_id != "")
	   {
               $AppUI->tasks_sucesoras[$dep_tasks[$i]][0] = $task_id;
	   }
	}
	
    // Si no tiene sucesoras, igual creo el registro en el vec de sucesoras
	if(!isset($AppUI->tasks_sucesoras[$task_id]) && $task_id !='')
	{  
	   $suc_task[0] = '';
	   $AppUI->tasks_sucesoras[$task_id] = $suc_task;
	}
  

    // Me fijo si ingreso fecha de delimitación
    
	$task_constraint_type = $_GET[task_constraint_type_];
    
	if ($_GET[task_constraint_date_] !="" )
	{ 
	 $task_constraint_date = $_GET[task_constraint_date_]."00";
	}
    
    // Cargo los datos de la tarea guardados en la bd, sin ninguna modificacion
	$objCurrent = new CTask();
	$objCurrent->load($task_id);
    
	
	$wbs_level = "0";
	  
    // Si la tarea tiene parent, marco a esa parent como dinamica, le pongo por defecto el constraint 3 y le saco los recursos
   if ($_GET[task_parent]!='0' && $_GET[task_parent]!= $objCurrent->task_parent)
  {
     $AppUI->tasks[$_GET[task_parent]][dynamic] = 1;
     $AppUI->tasks[$_GET[task_parent]][task_constraint_type] = "3";
     $AppUI->tasks[$_GET[task_parent]][task_constraint_date] = "00000000000000";
     unset($AppUI->tasks[$_GET[task_parent]][resources]);
     
     $wbs_level = $AppUI->tasks[$_GET[task_parent]][wbs_level] + 1;
    }
	
    // Si a una tarea padre le saco su hija me fijo si le quedo alguna hija ademas de esta que termina de sacar	
    if ($_GET[task_parent]=='0' && $_GET[task_parent]!= $objCurrent->task_parent)
   {
       $cant_hijas = 0;
       
       $tareas_vec = $AppUI->tasks;
       
       reset( $tareas_vec );
       
       
       // Recorro la lista de tareas para ver si alguna otra tarea tiene el mismo padre que esta
	   while( (list( $key, $tarea ) = each( $tareas_vec )) &&  $cant_hijas == 0  ) 
	   {   
	   	   // Si la tarea padre es igual a la tarea padre actual y el id es distinto de la id de esta tarea y el id es distinto del id de la tarea padre
		   if ($tarea[parent] == $objCurrent->task_parent && $key != $task_id && $key != $tarea[parent] )
		   {
		   	$cant_hijas = $cant_hijas + 1;
		   }
	   }
       
	   if ($cant_hijas == 0)
	   {
	   	  $AppUI->tasks[$objCurrent->task_parent][dynamic] = 0;
	   }
	   
	   $wbs_level = "0";
       
    }
	
	
    // Actualizo el vector de tareas de memoria
    if ($task_id != "")
	{      
		if ($task_id == '0')
		{
		$AppUI->tasks[0][id] = '0';
		}

		$AppUI->tasks[$task_id][start_date] = $start_date->format(FMT_TIMESTAMP);
		$AppUI->tasks[$task_id][end_date] = $end_date->format(FMT_TIMESTAMP);
		$AppUI->tasks[$task_id][duration] = $duration;
                        $AppUI->tasks[$task_id][duration_type] = $task_duration_type;
		$AppUI->tasks[$task_id][project] = $task_project;
		
		
		if ($_GET[task_parent]!='' && $_GET[task_parent]!='0')
		{
	              $AppUI->tasks[$task_id][parent] = $_GET[task_parent];
		}else{	
		  $AppUI->tasks[$task_id][parent] = $task_id;
		}
	    
		
		
        $AppUI->tasks[$task_id][dynamic] = 0;
        $AppUI->tasks[$task_id][task_constraint_date] = $task_constraint_date;
        $AppUI->tasks[$task_id][task_constraint_type] = $task_constraint_type;
        $AppUI->tasks[$task_id][work] = $_GET[task_work];
        $AppUI->tasks[$task_id][type] = $_GET[task_type];
        $AppUI->tasks[$task_id][wbs_level] = $wbs_level;
 
        if ($units != ''){
		$AppUI->tasks[$task_id][resources] = $units;
        }
        $AppUI->tasks[$task_id][edit] = '1';

    }
   
    echo "<pre>"; print_r($AppUI->tasks); echo "</pre><br><br>";
 
    $FTeI = $AppUI->tasks[$task_id][FTeI];
    $FTeF = $AppUI->tasks[$task_id][FTeF];
    $FTaI = $AppUI->tasks[$task_id][FTaI];
    $FTaF = $AppUI->tasks[$task_id][FTaF];
    
    echo "FTeI : ".$FTeI."<br>";
    echo "FTeF : ".$FTeF."<br>";
    echo "FTaI : ".$FTaI."<br>";
    echo "FTaF : ".$FTaF."<br>";


    echo "<br>Tipo de  limitación: ".$task_constraint_type;
    echo "<br>Fecha de limitación: ".$task_constraint_date;
   
   $tiempo_microa[1]= microtime(); 
   $q_espaciosa = explode(" ",$tiempo_microa[1]); 
   $tiempoa_[1]= $q_espaciosa[1]+$q_espaciosa[0]; 
   echo "<br>a - Inicia recalculos : ".date('H:i:s')."<br>";
	
   echo "<br><br><U><B>Calculos - Recalculos</B></U><br><br>";
   
   if ($action=="task_end_date" && $task_constraint_type == "1")
   {
   $action = "task_start_date";
   }
   
   if ($action=="task_start_date" && $task_constraint_type == "2")
   {
   $action = "task_end_date";
   }
   
   if ($action=="task_constraint_date" && $task_constraint_type == "3")
   {
    // Tan pronto como sea posible
		    
     echo "<br><br><u>Tipo de limitación: Tan pronto como sea posible</u><br>";
			
     echo "<br>Fecha de Inicio sin procesar : ".$start_date->format(FMT_TIMESTAMP)."<br>";

     /*----------------------------------
     Fecha de inicio = Fecha Temprana de Inicio
     Fecha de fin = Fecha Temprana de fin
     ------------------------------------*/
           
      $task_constraint_date = $FTeI; 
            
      $start_date->year = substr($task_constraint_date,0,4);
      $start_date->month = substr($task_constraint_date,4,2);   
      $start_date->day = substr($task_constraint_date,6,2);
      $start_date->hour = substr($task_constraint_date,8,2);
      $start_date->minute = substr($task_constraint_date,10,2);

      echo "La fecha de inicio es: ".$start_date->format(FMT_TIMESTAMP)."<br><br>";
      $action = "task_start_date";
   }
     

   if($task_constraint_type == "3" && $action!="task_constraint_date")
   {
	  $curd = $start_date->format(FMT_TIMESTAMP);
	  $dep_d = $FTeI;

	  if($curd < $dep_d)
	  {
		$start_date->year = substr($FTeI,0,4);
		$start_date->month = substr($FTeI,4,2);   
		$start_date->day = substr($FTeI,6,2);
		$start_date->hour = substr($FTeI,8,2);
		$start_date->minutes = substr($FTeI,10,2);
        
	  }

   }
   
	switch($action){
		case "task_duration":
		
			echo "<u>Modificó el campo duración</u>: <br><br>";

			echo "112 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
			$cur_date = $start_date->format(FMT_TIMESTAMP);
            
		            // Verifica que sea un dia laborable
			$start_date->fitDateToCalendar();		
			echo "113 - Task Start Date (Verifica dias laborables): ".$start_date->format(FMT_TIMESTAMP)."<br>";
			
			echo "114 - Task End Date: ".$end_date->format(FMT_TIMESTAMP)."<br>";	
			
			// Si no tiene recursos asignados, calculo igual usando como recurso al owner de la tarea
			if(count($units)==0)
			{
			     $units[$task_owner] = 100;
	                             $users_assigned = array_keys($units);
	       
			}		
			
			if(count($units)>0)
			{
			 echo "Recorro el vector de unidades:<br>";
			
			   foreach($units as $user =>$u)
				{
				$start_date =  new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP));
				
				// Me fijo que la fecha no caiga en feriado o en exclusiones
			           $start_date = verifica_feriados($start_date,$hollidays,false);	

				$end_date =  new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
		        
				$dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
						
				$start_date = new CWorkCalendar(3, $user, $task_project,$dates[sd]->format(FMT_TIMESTAMP));
				$end_date = new CWorkCalendar(3, $user, $task_project,$dates[ed]->format(FMT_TIMESTAMP));

				echo "Fecha de fin de la unidad: ".$end_date->format(FMT_TIMESTAMP)."<br><br>";

				// Verifico que no sea feriado
				$end_date = verifica_feriados($end_date,$hollidays,true);
				echo "Fecha de fin (Verifica feriados y exclusiones) : ".$end_date->format(FMT_TIMESTAMP)."<br>";

				}
			}
			else{
			    $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
	            
			    $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
			    $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
					
			    // Verifico que no sea feriado
			   $end_date = verifica_feriados($end_date,$hollidays,true);
				
			    echo "Fecha de fin (Verifica feriados y exclusiones) :".$end_date->format(FMT_TIMESTAMP)."<br>";
			
			}

			//Me fijo si tiene recursos asignados para recalcular el trabajo o las unidades
			echo "<pre>";
			print_r($units);
			echo "</pre>";
			
			if (count($units) > 0){
				$start_date->_hollidays = $hollidays;
				$end_date->_hollidays = $hollidays;
				
				$objTask = new CTask();
				$data = $_GET;
				$data["task_duration"] = $duration;
				$data["task_duration_type"] = $task_duration_type ;
				$data["task_start_date"] = $start_date->format(FMT_TIMESTAMP);
				$data["task_end_date"] = $end_date->format(FMT_TIMESTAMP);
				$objTask->bind($data);
				$objTask->_assigned_users = $units;
				
				$objTask->updateSchedule(3);
				
				echo "110 - Task work (sin procesar) : ".$data["task_work"]."<br>";
				echo "111 - Task work (Procesado): ".$objTask->task_work."<br>";

				if ($data["task_work"] != $objTask->task_work)	
				$task_work = $objTask->task_work; 
				
				
					for ($i=0; $i<count($users_assigned); $i++)
					{
						$user = $users_assigned[$i];

                                                                        $objTask->_assigned_users[$user] = ceil($objTask->_assigned_users[$user]);

						echo "112 - Unidades por usuario: ".$objTask->_assigned_users[$user]."<br>";

						if ($units[$user] != $objTask->_assigned_users[$user])
						{   
							$unidades = ceil($objTask->_assigned_users[$user]);
							
							$js_string .= "
							window.parent.update_units('".$user."', '".$objTask->_assigned_users[$user]."');
							";			
						}
					
				    }
                    
				    if ($objTask->task_type == '1' || $objTask->task_type == '2')
				    {   
						$Fecha = "00000000000000";

						foreach($units as $user =>$u)
						{
						
							if ($objTask->task_type == '2'){
							$start_d =  new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP));
							}else{
							$start_d =  new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP));
							}

							
							// Me fijo que la fecha no caiga en feriado o en exclusiones
							$start_d = verifica_feriados($start_d,$hollidays,false);	
                            
							if ($objTask->task_type == '2'){
							$end_d =  new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP) );
							}else{
							$end_d =  new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
							}
							
							$dates = update_end_date($start_d,$end_d,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
							
							if ($objTask->task_type == '2')
							{
								$start_date = new CWorkCalendar(3, $user, $task_project,$dates[sd]->format(FMT_TIMESTAMP));
					            $end_date = new CWorkCalendar(3, $user, $task_project,$dates[ed]->format(FMT_TIMESTAMP));
							}
							else{
				            	$start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
				                        $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
				            }
				            
				
		   //echo "Fecha de fin de la unidad: ".$end_d->format(FMT_TIMESTAMP)."<br><br>";

                                     if ($end_d->format(FMT_TIMESTAMP) > $Fecha)
			 {
			     $Fecha = $end_d->format(FMT_TIMESTAMP);
			  }
		}

                        echo "La fecha mas grande es :".$Fecha."<br>";
		$end_date->setDate($Fecha, DATE_FORMAT_TIMESTAMP);
                        $w = 0; 

				    }

			}
			else{
			 $objTask->task_work = "0";
			}
          
		 $task_work = $objTask->task_work;
		 
		 echo "115 - Task end date procesado : ".$end_date->format(FMT_TIMESTAMP)."<br>";
                         echo "116 - Task start date procesado : ".$start_date->format(FMT_TIMESTAMP)."<br>";
                         echo "117 - Task work procesado: ".$task_work."<br>";

	           // Si cambia el trabajo, tengo que actualizar el presupuesto
	           if(count($units_orig)>0){
                       $objTask->task_target_budget_hhrr = calcula_target_budget_hhrr($users_assigned,$objTask);
	           }
                       $task_target_budget_hhrr = $objTask->task_target_budget_hhrr; 
         
                        echo "118 - Task work procesado: ".$task_work."<br>";
                        
		break; // Fin de modicaciones en el campo Duración 

		case "task_start_date":
		
			echo "<u>Modificó el campo Fecha de Inicio</u>: <br><br>";

			echo "212 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
			$cur_date = $start_date->format(FMT_TIMESTAMP);
			
			$start_date->fitDateToCalendar();		
			echo "213 - Task Start Date (Verifica dias laborables): ".$start_date->format(FMT_TIMESTAMP)."<br>";
			
			// Me fijo que la fecha no caiga en feriado o en exclusiones
			$start_date = verifica_feriados($start_date,$hollidays,false);

			echo "214 - Task Start Date (Verifica feriados y exclusiones): ".$start_date->format(FMT_TIMESTAMP)."<br>";
			
			if(count($units)>0)
			{
			echo "Recorro el vector de unidades:";
			
			   $Fecha = "00000000000000";
			   
			   foreach($units as $user =>$u)
			   {				
				$start_date =  new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP) );
				$end_date =  new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );

				$dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
				
				$start_date = new CWorkCalendar(3, $user, $task_project,$dates[sd]->format(FMT_TIMESTAMP));
				$end_date = new CWorkCalendar(3, $user, $task_project,$dates[ed]->format(FMT_TIMESTAMP));
				
				echo "Fecha de fin ".$end_date->format(FMT_TIMESTAMP)."<br>";
				
				  if ($end_date->format(FMT_TIMESTAMP) > $Fecha)
				  {
					$Fecha = $end_date->format(FMT_TIMESTAMP);
				  }
			   }
				
				$end_date->setDate($Fecha, DATE_FORMAT_TIMESTAMP);
				
			}
			else{
			  $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
              
			  $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
			  $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));		
			}

			echo "215 - Task start date procesado : ".$start_date->format(FMT_TIMESTAMP)."<br>";
			echo "216 - Task end date procesado : ".$end_date->format(FMT_TIMESTAMP)."<br>";

                                    $task_work = $_GET[task_work];
            
		break; // Fin de modificaciones en el campo Fecha de Inicio

		case "task_end_date":
		
			echo "<u>Modificó el campo Fecha de Fin</u>: <br><br>";
			
			echo "312 - Task End Date: ".$end_date->format(FMT_TIMESTAMP)."<br>";
			$cur_date = $end_date->format(FMT_TIMESTAMP);
			
			$end_date->fitDateToCalendar(true);		
            
                                   if($duration > 0)
			{
			$end_date->hour = substr($cur_date,8,2);
                                    $end_date->minute = substr($cur_date,10,2);
			}
            
			echo "313 - Task End Date (Verifica dias laborables): ".$end_date->format(FMT_TIMESTAMP)."<br>";
			
			// Me fijo que la fecha no caiga en feriado o en exclusiones
			$end_date = verifica_feriados($end_date,$hollidays,true);

			echo "314 - Task End Date (Verifica feriados y exclusiones): ".$end_date->format(FMT_TIMESTAMP)."<br><br>";
			
			
			if(count($units)>0)
			{
			echo "Recorro el vector de unidades:";
			
			
			  foreach($units as $user =>$u)
			  {
				$start_date =  new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP) );
				$end_date =  new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
		
				$start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);

				echo "<br>314-a Fecha de Inicio para $user: ".$start_date->format(FMT_TIMESTAMP)."<br>";
				echo "<br>314-a Fecha de fin para $user: ".$end_date->format(FMT_TIMESTAMP)."<br>";
				
			  }
				
			    echo "315a - Task start date procesado : ".$start_date->format(FMT_TIMESTAMP)."<br>";
			    echo "316a - Task end date procesado : ".$end_date->format(FMT_TIMESTAMP)."<br>";
			}
			else{
			   $start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);
            
			   echo "315b - Task start date procesado : ".$start_date->format(FMT_TIMESTAMP)."<br>";
			   echo "316b - Task end date procesado : ".$end_date->format(FMT_TIMESTAMP)."<br>";
			}
			
			$objTask->task_work = $_GET[task_work];
			$task_work = $objTask->task_work;

		break; // Fin de modificaciones en el campo Fecha de Fin

		case "changed_work": 
			echo "<u>Modificó el campo Trabajo </u>: <br><br>";
            
			// Si no tiene recursos asignados, calculo igual usando como recurso al owner de la tarea
			if(count($units)==0)
			{
			     $units[$task_owner] = 100;
	                             $users_assigned = array_keys($units);
	       
	                             echo "<pre>"; print_r($units); echo "</pre>";
			}
			
			// Si no tiene unidades asignadas no necesito recalcular nada
			if(count($units)>0)
			{   
				echo "511 - Duración inicial: ".$duration."<br>";
				echo "512 - Fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)."<br>";
				echo "513 - Fecha de fin: ".$end_date->format(FMT_TIMESTAMP)."<br>";
                                                echo "524 - Tipo de constraint ".$task_constraint_type."<br>";

                                                $objTask = new CTask();
				$data = $_GET;
				$data["task_duration"] = $duration;
				$data["task_duration_type"] = $task_duration_type ;
				$data["task_start_date"] = $start_date->format(FMT_TIMESTAMP);
				$data["task_end_date"] = $end_date->format(FMT_TIMESTAMP);
				$objTask->bind($data);
				
				$objTask->_assigned_users = $units;
				
				echo "Usuarios asignados: <br>";
				echo "<pre>"; print_r($objTask->_assigned_users); echo "</pre>";
				
				$objTask->updateSchedule(4);

                
				if($data["task_duration"]!= $objTask->task_duration)
				{
				 echo "<br>514 - La duracion es: $objTask->task_duration<br>";
				 $duration = $objTask->task_duration;
				}

				if ($objTask->task_start_date !=  $data["task_start_date"])
				{
				$start_date->setDate($objTask->task_start_date, DATE_FORMAT_TIMESTAMP);	
				}

			   if ($objTask->task_end_date !=  $data["task_end_date"])
			   {
				$end_date->setDate($objTask->task_end_date, DATE_FORMAT_TIMESTAMP);	
			   }	

				if ($units != $objTask->_assigned_users)	
				{
					for ($i=0; $i<count($users_assigned); $i++)
					{
						$user = $users_assigned[$i];

                                                                        $objTask->_assigned_users[$user] = ceil($objTask->_assigned_users[$user]);

						echo "517 - Unidades por usuario: ".$objTask->_assigned_users[$user]."<br>";

						if ($units[$user] != $objTask->_assigned_users[$user])
						{   
						$unidades = ceil($objTask->_assigned_users[$user]);
							
						$js_string .= "
						window.parent.update_units('".$user."', '".$objTask->_assigned_users[$user]."');
						";			
						}
					}
			    }


			}
          
		 echo "514 - Duración : ".$objTask->task_duration."<br>";
		 echo "515 - Fecha de inicio procesada : ".$start_date->format(FMT_TIMESTAMP)."<br>";
		 echo "516 - Fecha de fin procesada : ".$end_date->format(FMT_TIMESTAMP)."<br>";

		 // Si cambia el trabajo, tengo que actualizar el presupuesto
		 if(count($units_orig)>0){
                        $objTask->task_target_budget_hhrr = calcula_target_budget_hhrr($users_assigned,$objTask);
		 }
			
		 echo "Presupuesto hhrr nuevo: ".$objTask->task_target_budget_hhrr;
		 $duration = $objTask->task_duration;
		 
		break; // Fin de modificaciones en el campo work

		case "changed_resources":

		    echo "<u>Modificó los recursos </u>: <br><br>";
            
		    echo "610 - Condicionada por el esfuerzo: ".$task_effort_driven."<br>";
                            echo "Cant. de Recursos : ".count($users_assigned)."<br>";
                            
                            // Si no tiene recursos asignados, calculo igual usando como recurso al owner de la tarea
			if(count($units)==0)
			{
			     $units[$task_owner] = 100;
	                             $users_assigned = array_keys($units);
	       
	                             echo "<pre>"; print_r($units); echo "</pre>";
			}
			
			   if (count($users_assigned)>0)	
				{
					for ($i=0; $i<count($users_assigned); $i++)
					{
					$user = $users_assigned[$i];

                                                            $start_date = new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP) );
                    
					$start_date->fitDateToCalendar();
                    
					$cur_date = $end_date->format(FMT_TIMESTAMP);
					$end_date = new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
                    
					$end_date->fitDateToCalendar(true);
					
                                                           $hora = substr($cur_date,8,2);
                                                           $min = substr($cur_date,10,2);
                    
					$end_date->hour = $hora;
                                                            $end_date->minute = $min;					
					}      
					
			    }
 

			// Reviso la fechas de inicio

			$objTask = new CTask();
			$data = $_GET;
			$data["task_duration"] = $duration;
			$data["task_duration_type"] = $task_duration_type ;
			$data["task_start_date"] = $start_date->format(FMT_TIMESTAMP);
			$data["task_end_date"] = $end_date->format(FMT_TIMESTAMP);
			$objTask->bind($data);
			$objTask->_assigned_users = $units;
           
			$objTask->updateSchedule(1);

			echo "611 - Duración: ".$objTask->task_duration."<br>";
			echo "612 - Fecha de inicio: ".$objTask->task_start_date."<br>";
			echo "613 - Fecha de fin: ".$objTask->task_end_date."<br>"; 
			echo "614 - Trabajo: ".$objTask->task_work."<br>";
			echo "615 - Recursos: <br>";
			echo "<pre>";print_r($objTask->_assigned_users);echo "</pre>";
                                    $duration = $objTask->task_duration;
            
                                    // Guardo el cambio en el vector de tareas en la memoria
			$AppUI->tasks[$task_id][resources] = $objTask->_assigned_users;

			if(count($units_orig)>0){
                                       $objTask->task_target_budget_hhrr = calcula_target_budget_hhrr($users_assigned,$objTask);
		               $js_string .= "window.parent.update_budget_hhrr('".$objTask->task_target_budget_hhrr."')";	
			}else{
			    $objTask->task_target_budget_hhrr = 0;
		                $js_string .= "window.parent.update_budget_hhrr('".$objTask->task_target_budget_hhrr."')";	
			}

			echo "Presupuesto hhrr nuevo: ".$objTask->task_target_budget_hhrr;
						
			if ($units != $objTask->_assigned_users)	
			{
				for ($i=0; $i<count($users_assigned); $i++)
				{
				$user = $users_assigned[$i];
                                                $objTask->_assigned_users[$user] = ceil($objTask->_assigned_users[$user]);

			            echo "616 - Unidades por usuario: ".$objTask->_assigned_users[$user]."<br>";

					if ($units[$user] != $objTask->_assigned_users[$user])
					{   
					$unidades = ceil($objTask->_assigned_users[$user]);
							
					$js_string .= "
					window.parent.update_units('".$user."', '".$objTask->_assigned_users[$user]."');
					";			
					}
				}
			}
          

		    if ($objTask->task_type == '1' || $objTask->task_type == '2')
		      {   
			    if (count($users_assigned)>0)
		      	    {
						//$Fecha = "99999999999999";
						$Fecha = "00000000000000";

						foreach($units as $user =>$u)
						{
							if ($objTask->task_type == '2'){
							$start_d =  new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP));
							}else{
							$start_d =  new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP));
							}
						
							// Me fijo que la fecha no caiga en feriado o en exclusiones
							$start_d = verifica_feriados($start_d,$hollidays,false);	
							
							if ($objTask->task_type == '2'){
							$end_d =  new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP) );
							}else{
							$end_d =  new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
							}

							$dates = update_end_date($start_d,$end_d,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
							
							if ($objTask->task_type == '2'){
							    $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
							    $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
							}else{
								$start_date = new CWorkCalendar(3, $user, $task_project,$dates[sd]->format(FMT_TIMESTAMP));
								$end_date = new CWorkCalendar(3, $user, $task_project,$dates[ed]->format(FMT_TIMESTAMP));
							}
							
							echo "<br>$user - Fecha de fin de la unidad: ".$end_d->format(FMT_TIMESTAMP)."<br><br>";

                            if ($end_d->format(FMT_TIMESTAMP) > $Fecha)
		    {
		        $Fecha = $end_d->format(FMT_TIMESTAMP);
		    }
						
						}

                        //echo "La fecha mas chica es :".$Fecha."<br>";
						$end_date->setDate($Fecha, DATE_FORMAT_TIMESTAMP);
                        $w = 0; 

						foreach($units as $user =>$u)
						{
						 $sd = new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP) );
						 $ed = new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
						 $dif = $sd->dateDiff($ed, 24);

                         $w_tmp = ($dif * $u * 8)/100;
                         $w = $w + $w_tmp;
						}
                        echo "Trabajo total: ".$w."<br>";
					    // Con la fecha mas chica saco la dif para cada usuario y lo multiplico por su turmo para obtener su trabajo, de su sumatorio obtengo el trabajo total
                        
						$objTask->task_work = $w;

						$objTask->task_end_date = $Fecha;
						
						$task_work = $objTask->task_work;
				    }
                    
	            }
					//echo "La fecha mas chica es :".$end_date->format(FMT_TIMESTAMP)."<br>";

		     if ($data["task_work"] != $objTask->task_work)	
		    {
		        // Si cambia el trabajo, tengo que actualizar el presupuesto
                               $objTask->task_target_budget_hhrr = calcula_target_budget_hhrr($users_assigned,$objTask);
			
		       echo "Presupuesto hhrr nuevo: ".$objTask->task_target_budget_hhrr;
	             }
	             
	             if(count($units_orig)==0){
	             	$objTask->task_target_budget_hhrr = 0;
	             }

			$end_date->setDate($objTask->task_end_date, DATE_FORMAT_TIMESTAMP);
		    
			if($data["task_duration"]!= $objTask->task_duration)
			{
			 $duration = $objTask->task_duration;
			}
			
			$task_work =  $objTask->task_work;

		    echo "620 - Fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)."<br>";
		    echo "621 - Fecha de fin: ".$end_date->format(FMT_TIMESTAMP)."<br>";

		break;  // Fin de las modificaciones en los recursos
		
		case "changed_units":
            
		    echo "<u>Modificó los recursos </u>: <br><br>";

                                    $objTask = new CTask();
			$data = $_GET;
			$data["task_duration"] = $duration;
			$data["task_duration_type"] = $task_duration_type ;
			$data["task_start_date"] = $start_date->format(FMT_TIMESTAMP);
			$data["task_end_date"] = $end_date->format(FMT_TIMESTAMP);
			$objTask->bind($data);
			$objTask->_assigned_users = $units;

			$objTask->updateSchedule(2);

			echo "711 - Duración: ".$objTask->task_duration."<br>";
			echo "712 - Fecha de inicio: ".$objTask->task_start_date."<br>";
			echo "713 - Fecha de fin: ".$objTask->task_end_date."<br>"; 
			echo "714 - Trabajo: ".$objTask->task_work."<br>";
			echo "715 - Recursos: <br>";
			echo "<pre>";print_r($objTask->_assigned_users);echo "</pre>";
                                    $duration = $objTask->task_duration;
            
			if ($units != $objTask->_assigned_users)	
			{
				for ($i=0; $i<count($users_assigned); $i++)
				{
				$user = $users_assigned[$i];
                                                $objTask->_assigned_users[$user] = ceil($objTask->_assigned_users[$user]);

			            echo "716 - Unidades por usuario: ".$objTask->_assigned_users[$user]."<br>";

					if ($units[$user] != $objTask->_assigned_users[$user])
					{   
					$unidades = ceil($objTask->_assigned_users[$user]);
							
					$js_string .= "
					window.parent.update_units('".$user."', '".$objTask->_assigned_users[$user]."');
					";			
					}
				}
			}

			if ($objTask->task_end_date !=  $data["task_end_date"])
			{
			$end_date->setDate($objTask->task_end_date, DATE_FORMAT_TIMESTAMP);	
			}	
		    
			if($data["task_duration"]!= $objTask->task_duration)
			{
			$duration = $objTask->task_duration;
			}

			if ($data["task_work"] != $objTask->task_work)	
		            {  
			// Si cambia el trabajo, tengo que actualizar el presupuesto
			$objTask->task_target_budget_hhrr = calcula_target_budget_hhrr($users_assigned,$objTask);

			echo "Presupuesto hhrr nuevo: ".$objTask->task_target_budget_hhrr;
	                        }
            
			$task_work = $objTask->task_work;

		break; // Fin de las modificaciones en las unidades

		case "add_del_dependencies":

                                   echo "<u>Modificó las dependencias </u><br><br>";
            
			// si tiene una tarea padre y no es la misma tarea 
			if($task_parent!=$task_id && $task_parent > 0)
			{
				$objTask = new CTask();
				if($objTask->load($task_parent))
				{
					// obtengo las dependencias de esa tarea padre 
					$parent_dep = explode(",",$objTask->getDependencies());
					$dep_tasks = array_merge($dep_tasks, $parent_dep);
				}
				
			}

			$dep_tasks = explode(",",$dependencies);
			echo "801 - Dependencias de la actual tarea: <br><pre>";
			print_r($dep_tasks);
			echo "</pre>";
            
			$objTask = new CTask();
			$data = $_GET;
			$data["task_duration"] = $duration;
			$data["task_duration_type"] = $task_duration_type ;
			$data["task_start_date"] = $start_date->format(FMT_TIMESTAMP);
			$data["task_end_date"] = $end_date->format(FMT_TIMESTAMP);
			$objTask->bind($data);
            
			// Actualizo las fechas de la tarea actual de acuerdo a sus dependencias //
		    $objTask->updateDependencies($dependencies);
                            $duration = $objTask->task_duration;
			
            
		    if($data["task_start_date"]!= $objTask->task_start_date)
	   	    {   
				$st = new CDate( $objTask->task_start_date);				
				$start_date->setDate($st->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
				echo "802 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
                
				$start_date->fitDateToCalendar();			   		
				
				// Me fijo que la fecha no caiga en feriado o en exclusiones
				$start_date = verifica_feriados($start_date,$hollidays,false);

				echo "803 - Task Start Date (Verifica feriados y exclusiones): ".$start_date->format(FMT_TIMESTAMP)."<br>";
			
				// Actualizo la fecha de fin
				if(count($units)>0)
				{
				echo "Recorro el vector de unidades:";
				
				   foreach($units as $user =>$u)
					{
					
					$start_date =  new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP) );
					$end_date =  new CWorkCalendar(3, $user, $task_project,$end_date->format(FMT_TIMESTAMP) );
			        
					echo "<br>Fecha de inicio ".$start_date->format(FMT_TIMESTAMP)."<br>";
                                                            echo "Fecha de fin ".$end_date->format(FMT_TIMESTAMP)."<br>";
                                                            echo "Duracion ".$duration."<br>";
                    
					$dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
                    
					$start_date = new CWorkCalendar(3, $user, $task_project,$dates[sd]->format(FMT_TIMESTAMP));
					$end_date = new CWorkCalendar(3, $user, $task_project,$dates[ed]->format(FMT_TIMESTAMP));
                    
					
					}
				}
				else{
					$dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
	                                                $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
					$end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));

				}

				echo "803 - Task end date procesado : ".$end_date->format(FMT_TIMESTAMP)."<br>";
		    }
		

		break; // Fin de las modificaciones en las dependencias
        
		case "task_constraint_type":
		
		echo "<u>Modificó el tipo de limitación</u><br><br>";

		break; // Fin de las modificaciones en el tipo de limitación
		
	}
      
	$tiempo_microa[2]= microtime(); 
	$q_espaciosa = explode(" ",$tiempo_microa[2]); 
	$tiempoa_[2]= $q_espaciosa[1]+$q_espaciosa[0]; 
	$tiempo_utilizadoa = number_format(($tiempoa_[2] - $tiempoa_[1]),3, "." ,","); 
	echo "a - Finaliza recalculos : ".date('H:i:s')." ($tiempo_utilizadoa seg)<br>";
    
	 echo "Antes de recalcular constraint Duracion $duration - Trabajo: ".$task_work."<br>";
            
			$tasks = $AppUI->tasks;
			
			// Traigo la rama de padres de la tarea que estoy modificando
			$tasks_s[0]= $task_id;
		  
		    if($task_id!= $_GET[task_parent])
		    {					
			 $tasks_s[1] = $AppUI->tasks[$task_id][parent];
			 $tasks_s = ordena_hijas($tasks_s);
		             $tasks_sort[$task_id] = $AppUI->tasks_s;
		     }else {
			 $tasks_sort[$task_id][0] = $task_id;
		     }
			
			//echo "<br>Padres de $task_id <pre>"; print_r($tasks_sort[$task_id]); echo "</pre><br>";
			
			// Recalculo las FTe
			
			$start_proyect = $project_start_date->format("%Y%m%d")."090000";
			$FTeI = $start_proyect;
			
			$task_predecesoras = $AppUI->tasks_predecesoras[$task_id];
			
			//echo "Predecesoras 2<pre>"; print_r($task_predecesoras);echo "</pre>";
			
			// Recorro el vector de predecesoras y guardar la mayor 
		    if($task_predecesoras[0] != "" )
		    {  
			   foreach($task_predecesoras as $key=> $id_predecesora )
			   { 
				  $FTeF = $AppUI->tasks[$id_predecesora][FTeF]; 
		          
				  if($FTeF > $FTeI )
			      {
			       $FTeI = $FTeF;
				   
			      }  
			   }
			   //echo "<br>FTeI : $FTeI <br>";
			   $pred = 1;
		    }else{
		       
		    	// Si no tiene predecesoras me fijo si la tarea padre tiene predecesoras
			   	 
		           $padres =  $tasks_sort[$task_id];
		           $cant_p = count($padres)  - 1;
		           
			       // Recorro el vector de orden
			       for ($i=0; $i<= $cant_p; $i ++ )
			       {
			       
			         if ($task_id != $padres[$i])
				   	 {
					   	  $task_predecesoras_p = $AppUI->tasks_predecesoras[$padres[$i]]; 
					   	  
					   	  if ($task_predecesoras_p[0]!='')
					   	  {
						      foreach($task_predecesoras_p as $key=> $id_predecesora )
							   {  
								  $FTeF = $tasks[$id_predecesora][FTeF]; 
						          
								  if($FTeF > $FTeI )
							      {
							       $FTeI = $FTeF;			   
							      }			  
						       }
						       
						       $pred = 1;
						       $i = $cant_p;
						   }			   
				   	 }
				   }
		   	    
		     
		    }
		    
		    $turno = hora_turno($AppUI->tasks[$task_id], $FTeI, "0", $pred);
			//echo "<br>FTeI : $FTeI / Turno $turno <br><br>";
		    
		    if($turno != "")
		    {
		         $FTeI = $turno;
		    }
	
		    $FTeI = new CWorkCalendar(0, '', '',$FTeI);	
		    $FTeF = new CWorkCalendar(0, '', '',$FTeF);
		   
		   // echo "$task_id - FTeI: ".$FTeI->format(FMT_TIMESTAMP)." / Turno ".$turno."<br>";
		   $dates_FTe = update_end_date($FTeI,$FTeF,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
		   
		   $FTeI = $dates_FTe[sd]->format(FMT_TIMESTAMP);
                           $FTeF = $dates_FTe[ed]->format(FMT_TIMESTAMP);
    	    
			
	        // Fin Recalculo las FTe //
	        
	        // Recalculo las FTa //
	        
	       $task_sucesoras =  $AppUI->tasks_sucesoras[$task_id];      
	       //echo "suce<pre>"; print_r($task_sucesoras); echo "</pre>";
	       
	       // Recorro el vector de sucesoras y guardar la menor FTaI
		   if($task_sucesoras[0] != "" )
		   {   
			   $FTaF = "99999999999999";
		
			   foreach($task_sucesoras as $key=>$id_sucesora )
			   {  
				  //echo "prede de $key <pre>"; print_r($AppUI->tasks[$id_sucesora]); echo "</pre>";
				   
				  $FTaI = $AppUI->tasks[$id_sucesora][FTaI]; 
		          
			      //echo "if( FTaI ".$FTaI." < FTaF".$FTaF." )<br>";
			      if($FTaI < $FTaF )
			      {
			       $FTaF = $FTaI;
			      }
				  
			   }
			   
			   $suc = 1 ;
	       }else {
	       	 
	         // me fijo si tiene tarea padre, para buscar la menor FTaI de su sucesora
		   	  
	         $padres =  $tasks_sort[$task_id];
	         $cant_p = count($padres) - 1;
	         
	         //echo "Padres de $task_id<pre>"; print_r($padres); echo "</pre>";
	           
		       // Recorro el vector de orden
		       for ($i=0; $i<= $cant_p; $i ++ )
		       {
		         
		         if ($task_id != $padres[$i])
			   	 {
				    $task_sucesoras = $AppUI->tasks_sucesoras[$padres[$i]];
			   	      $FTaF = "99999999999999";
				   	  
				   	 foreach($task_sucesoras as $key=> $id_sucesora )
					   {  
						  $FTaI = $tasks[$id_sucesora][FTaI]; 
				        
						  if($FTaI < $FTaF )
					      {
					       $FTaF = $FTaI;
					      }
						  
					   }
				       $suc = 1;
				       $i = $cant_p;
			   	 }
			   }
			   
		   	  
	       }
	     
	    //echo "echo $task_id - FTaF1: $FTaF $suc<br>";
	      
         if ($FTaF == "")
         {
         // Si no tiene tareas sucesoras, se asigna la FTeF mas grande del proyecto en el caso que no tenga padre
         //echo "<br>No tiene sucesoras<br>";
         //$MTeF = $FTeF;
         
		   foreach ($AppUI->tasks as $key => $t)
		   {   
		   	   //echo "if(".$t[FTeF]." >= ".$MTeF." )<br>";
			   if($t[FTeF] >= $MTeF  )
			   {
			   $MTeF = $t[FTeF];
			   }
				
		   }   
       
	     $FTaF = $MTeF;
	     $suc = 0;
        }
     
        
        if ($FTaF < $FTeF)
        {
        	$FTaF = $FTeF;
        }
        
        
        $turno = hora_turno($AppUI->tasks[$task_id], $FTaF, "1", $suc);
        //echo "echo $task_id - FTaF: $FTaF / Turno ".$turno."<br>";
        
        if($turno != "" && $duration!="0")
		{
		$FTaF = $turno;
		}
	
        $FTaI = new CWorkCalendar(0, '', '',$FTaI);	
        $FTaF = new CWorkCalendar(0, '', '',$FTaF);
		    
        $FTaI = update_start_date($FTaI,$FTaF,$duration,$task_duration_type,$msg, $hollidays);
	    
        $FTaI = $FTaI->format(FMT_TIMESTAMP);
        $FTaF = $FTaF->format(FMT_TIMESTAMP);
        
	        // Fin de Recalculos de FTa //
	        
			echo "FTeI : ".$FTeI."<br>";
			echo "FTeF : ".$FTeF."<br>";
			echo "FTaI : ".$FTaI."<br>";
			echo "FTaF : ".$FTaF."<br>";

			$current_sd = $start_date->format(FMT_TIMESTAMP);
			$current_ed = $end_date->format(FMT_TIMESTAMP);
            
			echo "<br><b>Reviso los constraint</b><br>";

			echo "Tipo de constraint: ".$task_constraint_type."<br>";
			
		    $tiempo_microc[1]= microtime(); 
		    $q_espaciosc = explode(" ",$tiempo_microc[1]); 
		    $tiempoc_[1]= $q_espaciosc[1]+$q_espaciosc[0]; 
            echo "c - Inicio Verificacion resultados : ".date('H:i:s')."<br>";
            
            echo "Fecha de inicio del proyecto: ".$project_start_date->format(FMT_TIMESTAMP_DATE)."<br>";
            echo "Trabajo: ".$task_work."<br>";
            
            $project_sd = $project_start_date->format(FMT_TIMESTAMP_DATE)."090000";
            
		    // Reviso las constraint 
		    switch($task_constraint_type)
		    {   
				case "1": // Debe comenzar en 

					/*
					Fecha Inicio = Fecha especificada
					Fecha Fin = Fecha de inicio + duracion
					*/
				
					$curd = $_GET[start_date];
				    
					if($fecha_min->format(FMT_TIMESTAMP) <= $task_constraint_date )
					{
					 $dates = update_end_date_con_recursos( $task_constraint_date , $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
                     
					 $start_date = new CWorkCalendar(0, '', '',$dates[sd]);
                    				 $end_date = new CWorkCalendar(0, '', '',$dates[ed]);
                     
					}
					
					echo "<br>Fecha minima de inicio: ".$fecha_min->format(FMT_TIMESTAMP)."<br>";
					
					if ($fecha_min->format(FMT_TIMESTAMP) > $task_constraint_date)
					{
						$msg_error = $AppUI->_('ErrorConstraintStartMinDate')." ".$fecha_min->format('%d/%m/%Y %H:%M');
						$js_string .= "
					window.parent.update_constraints('1','".$fecha_min->format('%d/%m/%Y')."','".$fecha_min->format("%H")."', '".$fecha_min->format("%M")."');
					";	
                        				$dates = update_end_date_con_recursos( $fecha_min->format(FMT_TIMESTAMP) , $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
                        
					    	$start_date = new CWorkCalendar(0, '', '',$dates[sd] );
                        				$end_date = new CWorkCalendar(0, '', '',$dates[ed] );
                        
                        				$task_constraint_date = $fecha_min->format(FMT_TIMESTAMP);
					}
					
					echo "fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)." FTaI".$FTaI."<br>";
					
					 
                     		 $FTeI = $start_date->format(FMT_TIMESTAMP);
	                 	 $FTeF = $end_date->format(FMT_TIMESTAMP);
		             $FTaI = $start_date->format(FMT_TIMESTAMP);
		             $FTaF = $end_date->format(FMT_TIMESTAMP);
		             
		             echo "<br>Modifico las fechas Tardias y tempranas tambien <br>";
                     
                     			echo "FTeI : ".$FTeI."<br>";
				echo "FTeF : ".$FTeF."<br>";
				echo "FTaI : ".$FTaI."<br>";
				echo "FTaF : ".$FTaF."<br>";

				break;
		
				case "2": // Debe terminar en
					
					/*
					Fecha de fin = Fecha especificada
					Fecha de inicio = Fecha de fin - duracion
					*/
					echo "<br>Debe terminar en: <br>";
                    
					$start_date = new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP) );
					
					$cur_sd = $start_date->format(FMT_TIMESTAMP);

					//$end_date->setDate($task_constraint_date, DATE_FORMAT_TIMESTAMP);
					$end_date = new CWorkCalendar(0, '', '',$task_constraint_date);
				    
				    	$start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);
				    echo "Nueva fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)."<br>";
				    echo "Nueva fecha de fin :".$end_date->format(FMT_TIMESTAMP)."<br>";

				    if($fecha_min->format(FMT_TIMESTAMP) > $start_date->format(FMT_TIMESTAMP))
				    {
					  $start_date->setDate($fecha_min->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

					  $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
				              $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
                      				  $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
                      
                      				  $msg_error = $AppUI->_('ErrorConstraintEndMinDate')." ".$end_date->format('%d/%m/%Y %H:%M');
					  $js_string .= "
					window.parent.update_constraints('2','".$end_date->format('%d/%m/%Y')."','".$end_date->format("%H")."', '".$end_date->format("%M")."');
					";	
                      				$task_constraint_date = $end_date->format(FMT_TIMESTAMP);
				    } 
				    
				    	
				    $FTeI  = $start_date->format(FMT_TIMESTAMP);
	                  		    $FTeF = $end_date->format(FMT_TIMESTAMP);
		              	    $FTaI  = $start_date->format(FMT_TIMESTAMP);
		                            $FTaF = $end_date->format(FMT_TIMESTAMP);
		             
		                            echo "<br>Modifico las fechas Tardias y tempranas tambien <br>";
                     
                                                              echo "FTeI : ".$FTeI."<br>";
					  echo "FTeF : ".$FTeF."<br>";
					  echo "FTaI : ".$FTaI."<br>";
					  echo "FTaF : ".$FTaF."<br>";

				break;
				case "3": // Tan pronto como sea posible
				/*
				Fecha de inicio = Fecha temprana de inicio
				Fecha de fin = Fecha temprana de fin
				*/
				
				if($start_date->format(FMT_TIMESTAMP_DATE)!= $FTeI )
				{
				$dates = update_end_date_con_recursos( $FTeI, $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
					
		                        $start_date = new CWorkCalendar(0, '', '',$dates[sd] );
		                        $end_date = new CWorkCalendar(0, '', '',$dates[ed] );
				}
                                                echo "Del calculo de constraint el trabajo sale: ".$task_work."<br>";
				break;

				case "4": // Tan tarde como sea posible
                                                /*
                			Fecha de inicio = Fecha tardia de inicio
				Fecha de fin = Fecha tardia de fin
                			*/
				
				echo "Recalculo el constraint Tan tarde como sea posible<br>";

				$end_date->setDate($FTaF, DATE_FORMAT_TIMESTAMP);
				$start_date = new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP_DATE) );
				$end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
				
				$start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);
                 
				break;

				case "5": // No comenzar antes de 
                   			   /*
				   Fecha de inicio = fecha esp cuando sea mayor a la FTeI
				   Fecha de fin = Fecha de inicio + Duracion
				   */

                   			   $curd = $start_date->format(FMT_TIMESTAMP);
		           
				   if($FTeI < $task_constraint_date )
					{
					 $start_date = new CWorkCalendar(0, '', '',$task_constraint_date );
					 $end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP) );
                     
					 $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
                     $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
					 $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
					}		
					
				   if($fecha_min->format(FMT_TIMESTAMP) > $start_date->format(FMT_TIMESTAMP))
				    {
					  $start_date->setDate($fecha_min->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

					  $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
                     				 $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
                      				 $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
                      
                      				 $msg_error = $AppUI->_('ErrorConstraintStartMinDate')." ".$fecha_min->format('%d/%m/%Y %H:%M');
					 $js_string .= "
					window.parent.update_constraints('5','".$start_date->format('%d/%m/%Y')."','".$start_date->format("%H")."', '".$end_date->format("%M")."');
					";	
					  $task_constraint_date = $start_date->format(FMT_TIMESTAMP);
                      
				    } 
				    
				    if ($AppUI->task_suces[$task_id][0] != "" && $start_date->format(FMT_TIMESTAMP) > $FTaI )
					{
						echo "fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)." FTaI".$FTaI."<br>";
						
						$fecha_max = new CWorkCalendar(0, '', '',$FTaI );
						$end_max_date = new CWorkCalendar(0, '', '',$FTaF );
						
						$msg_error = $AppUI->_('ErrorConstraintStartMaxDate')." ".$fecha_max->format('%d/%m/%Y %H:%M');
						
						$js_string .= "
					window.parent.update_constraints('5','".$fecha_max->format('%d/%m/%Y')."','".$fecha_max->format("%H")."', '".$fecha_max->format("%M")."');
					";	
						$task_constraint_date = $fecha_max->format(FMT_TIMESTAMP);
						 
                        $dates = update_end_date_con_recursos( $fecha_max->format(FMT_TIMESTAMP) , $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
                        
					            $start_date = new CWorkCalendar(0, '', '',$dates[sd] );
                        				$end_date = new CWorkCalendar(0, '', '',$dates[ed] );
						
					}
					
					$FTeI = $start_date->format(FMT_TIMESTAMP);
	                			$FTeF = $end_date->format(FMT_TIMESTAMP);
		          			$FTaI = $start_date->format(FMT_TIMESTAMP);
		           			$FTaF = $end_date->format(FMT_TIMESTAMP);
					 
				break;

				case "6": // No comenzar después de
				   /*
				   Fecha de inicio = fecha esp cuando sea menor a la FTeI
				   Fecha de fin = Fecha de inicio + duracion
				   */

				   if(($FTeI > $task_constraint_date) && ($start_date->format(FMT_TIMESTAMP_DATE) > $task_constraint_date ) )
					{
					 $start_date->setDate($task_constraint_date, DATE_FORMAT_TIMESTAMP);
					 $start_date = new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP_DATE) );
				    	 $end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
                     
					 $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
                     $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
					 $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
					 
					}		
					
					if($fecha_min->format(FMT_TIMESTAMP) > $start_date->format(FMT_TIMESTAMP))
				    {
					  $start_date->setDate($fecha_min->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

					  $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
			                          $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
			                          $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
                      
                      $msg_error = $AppUI->_('ErrorConstraintStartMinDate')." ".$fecha_min->format('%d/%m/%Y %H:%M');
					  $js_string .= "
					window.parent.update_constraints('6','".$end_date->format('%d/%m/%Y')."','".$end_date->format("%H")."', '".$end_date->format("%M")."');
					";	
					  $task_constraint_date = $end_date->format(FMT_TIMESTAMP);
                      
				    } 
				    
				    if ($AppUI->task_suces[$task_id][0] != "" && $start_date->format(FMT_TIMESTAMP) > $FTaI )
					{
						echo "fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)." FTaI".$FTaI."<br>";
						
						$fecha_max = new CWorkCalendar(0, '', '',$FTaI );
						$end_max_date = new CWorkCalendar(0, '', '',$FTaF );
						
						$msg_error = $AppUI->_('ErrorConstraintStartMaxDate')." ".$fecha_max->format('%d/%m/%Y %H:%M');
						
						$js_string .= "
					window.parent.update_constraints('6','".$fecha_max->format('%d/%m/%Y')."','".$fecha_max->format("%H")."', '".$fecha_max->format("%M")."');
					";	
						$task_constraint_date = $fecha_max->format(FMT_TIMESTAMP);
						
                        $dates = update_end_date_con_recursos( $fecha_max->format(FMT_TIMESTAMP) , $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
                        
					    $start_date = new CWorkCalendar(0, '', '',$dates[sd] );
                        $end_date = new CWorkCalendar(0, '', '',$dates[ed] );
						
					}
					
					$FTeI = $start_date->format(FMT_TIMESTAMP);
	                $FTeF = $end_date->format(FMT_TIMESTAMP);
		            $FTaI = $start_date->format(FMT_TIMESTAMP);
		            $FTaF = $end_date->format(FMT_TIMESTAMP);

				break;

				case "7": // No terminar antes de
				    /*
					Fecha de fin = fecha esp cuando sea mayor a la FTeF
					Fecha de inicio = fecha de fin - duracion
					*/
				   
				    echo "<br>No terminar antes de:<br>";
				    echo "if(".$FTeF." < ".$task_constraint_date." )<br>";
				    
					if($FTeF < $task_constraint_date )
					{
					 $end_date->setDate($task_constraint_date, DATE_FORMAT_TIMESTAMP);
					 
					 $start_date = new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP_DATE) );
				     $end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
				
				  
				     $start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);
				  
					}
					
					if($fecha_min->format(FMT_TIMESTAMP) > $start_date->format(FMT_TIMESTAMP))
				    {
					  $start_date->setDate($fecha_min->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

					  $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
                      $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
                      $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
                      
                      $msg_error = $AppUI->_('ErrorConstraintStartMinDate')." ".$fecha_min->format('%d/%m/%Y %H:%M');
					  $js_string .= "
					window.parent.update_constraints('7','".$end_date->format('%d/%m/%Y')."','".$end_date->format("%H")."', '".$end_date->format("%M")."');
					";	
					  $task_constraint_date = $end_date->format(FMT_TIMESTAMP);
                      
				    } 
				    
				    if ($AppUI->task_suces[$task_id][0] != "" && $start_date->format(FMT_TIMESTAMP) > $FTaI )
					{
						echo "fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)." FTaI".$FTaI."<br>";
						
						$fecha_max = new CWorkCalendar(0, '', '',$FTaI );
						$end_max_date = new CWorkCalendar(0, '', '',$FTaF );
						
						$msg_error = $AppUI->_('ErrorConstraintStartMaxDate')." ".$fecha_max->format('%d/%m/%Y %H:%M');
						
						$js_string .= "
					window.parent.update_constraints('7','".$end_max_date->format('%d/%m/%Y')."','".$end_max_date->format("%H")."', '".$end_max_date->format("%M")."');
					";	
						$task_constraint_date = $end_max_date->format(FMT_TIMESTAMP);
						
                        $dates = update_end_date_con_recursos( $fecha_max->format(FMT_TIMESTAMP) , $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
                        
					    $start_date = new CWorkCalendar(0, '', '',$dates[sd] );
                        $end_date = new CWorkCalendar(0, '', '',$dates[ed] );
						
					}
					
					$FTeI = $start_date->format(FMT_TIMESTAMP);
	                $FTeF = $end_date->format(FMT_TIMESTAMP);
		            $FTaI = $start_date->format(FMT_TIMESTAMP);
		            $FTaF = $end_date->format(FMT_TIMESTAMP);


				break;

				case "8": // No terminar después de
                    /*
					Fecha de fin = Fecha esp cuando sea menor a FTeF
					Fecha de inicio = fecha de fin - duracion
					*/
				    
					$cur_sd = $start_date->format(FMT_TIMESTAMP_DATE);

				    if ($FTeF > $task_constraint_date)
					{
					$end_date->setDate($task_constraint_date, DATE_FORMAT_TIMESTAMP);
					
					$start_date = new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP_DATE) );
				    $end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
				  
				     $start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);
					}		
					
					if($fecha_min->format(FMT_TIMESTAMP) > $start_date->format(FMT_TIMESTAMP))
				    {
					  $start_date->setDate($fecha_min->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

					  $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
                      $start_date = new CWorkCalendar(0, '', '',$dates[sd]->format(FMT_TIMESTAMP));
                      $end_date = new CWorkCalendar(0, '', '',$dates[ed]->format(FMT_TIMESTAMP));
                      
                      $msg_error = $AppUI->_('ErrorConstraintStartMinDate')." ".$fecha_min->format('%d/%m/%Y %H:%M');
					  $js_string .= "
					window.parent.update_constraints('8','".$end_date->format('%d/%m/%Y')."','".$end_date->format("%H")."', '".$end_date->format("%M")."');
					";	
                      $task_constraint_date = $end_date->format(FMT_TIMESTAMP);
				    } 
				    
				    if ($AppUI->task_suces[$task_id][0] != "" && $start_date->format(FMT_TIMESTAMP) > $FTaI )
					{
						echo "fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)." FTaI".$FTaI."<br>";
						
						$fecha_max = new CWorkCalendar(0, '', '',$FTaI );
						$end_max_date = new CWorkCalendar(0, '', '',$FTaF );
						
						$msg_error = $AppUI->_('ErrorConstraintStartMaxDate')." ".$fecha_max->format('%d/%m/%Y %H:%M');
						
						$js_string .= "
					window.parent.update_constraints('8','".$end_max_date->format('%d/%m/%Y')."','".$end_max_date->format("%H")."', '".$end_max_date->format("%M")."');
					";	
						$task_constraint_date = $end_max_date->format(FMT_TIMESTAMP);
						
                        $dates = update_end_date_con_recursos( $fecha_max->format(FMT_TIMESTAMP) , $end_date->format(FMT_TIMESTAMP), $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type );
                        
					    $start_date = new CWorkCalendar(0, '', '',$dates[sd] );
                        $end_date = new CWorkCalendar(0, '', '',$dates[ed] );
						
					}
					
				   
					$FTeI = $start_date->format(FMT_TIMESTAMP);
	                $FTeF = $end_date->format(FMT_TIMESTAMP);
	                
	                $cr_end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
	                $cr_start_date = new CWorkCalendar(0, '', '',$start_date->format(FMT_TIMESTAMP));
	                
	                $end_date->setDate($task_constraint_date, DATE_FORMAT_TIMESTAMP);
					
					$start_date = new CWorkCalendar(0, '', '', $end_FTa );
				    $end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
				  
				    $start_date = update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays);
				    $FTaI = $start_date->format(FMT_TIMESTAMP);
		            $FTaF = $end_date->format(FMT_TIMESTAMP);
		                
		            $end_date->setDate($cr_end_date, DATE_FORMAT_TIMESTAMP);
		            $start_date->setDate($cr_start_date, DATE_FORMAT_TIMESTAMP);
		            
		            $start_date = new CWorkCalendar(0, '', '', $start_date->format(FMT_TIMESTAMP) );
				    $end_date = new CWorkCalendar(0, '', '',$end_date->format(FMT_TIMESTAMP));
				    

				break;

		    }
           
		   
		   
		   // Guardo los datos en el vector si modifique alguna fecha de inicio o fin

			if ($task_id != "" && ($current_sd != $start_date->format(FMT_TIMESTAMP_DATE) || $current_ed != $end_date->format(FMT_TIMESTAMP_DATE) ) )
			{      
				$AppUI->tasks[$task_id][start_date] = $start_date->format(FMT_TIMESTAMP);
				$AppUI->tasks[$task_id][end_date] = $end_date->format(FMT_TIMESTAMP);
				$AppUI->tasks[$task_id][duration] = $duration;
				$AppUI->tasks[$task_id][duration_type] = $task_duration_type;
				
				echo "Trabajo antes de AppUI: ".$task_work."<br>";
				echo "if(strlen($task_work) >1)- es null:".is_null($task_work)."<br>";
				
				if(strlen($task_work) >0)
			           {
				  $AppUI->tasks[$task_id][work] = $task_work;
				}
				else
				{
				  $AppUI->tasks[$task_id][work] = $_GET[task_work];
				}
				
	    $AppUI->tasks[$task_id][task_constraint_date] = $task_constraint_date;
	    $AppUI->tasks[$task_id][task_constraint_type] = $task_constraint_type;

			
	    $AppUI->tasks[$task_id][FTeI] = $FTeI;
	    $AppUI->tasks[$task_id][FTeF] = $FTeF;
	    $AppUI->tasks[$task_id][FTaI] = $FTaI;
	    $AppUI->tasks[$task_id][FTaF] = $FTaF;
                $AppUI->tasks[$task_id][edit] = '1';
				
                $AppUI->tasks[$task_id][check_te] = '0';
                $AppUI->tasks[$task_id][check_ta] = '0';
				
                echo "Le mando <br><pre>"; print_r($AppUI->tasks[$task_id]); echo "</pre>";
                
				$project_tasks = GetDateTaTe($task_project, true);

				$AppUI->tasks[$task_id][edit] = '0';

			}

$tiempo_microc[2]= microtime(); 
$q_espaciosc = explode(" ",$tiempo_microc[2]); 
$tiempoc_[2]= $q_espaciosc[1]+$q_espaciosc[0]; 
$tiempo_utilizadoc = number_format(($tiempoc_[2] - $tiempoc_[1]),3, "." ,","); 
echo "c - Finaliza Verificacion resultados : ".date('H:i:s')." ( $tiempo_utilizadoc seg)<br>";

$js_string .= "
				window.parent.update_field('task_start_date', '".$start_date->format(FMT_TIMESTAMP_DATE)."');
				window.parent.update_field('start_date', '".$start_date->format($df)."');
				window.parent.selectTime('start', '".$start_date->format("%H")."', '".$start_date->format("%M")."');
				";	
	
$js_string .= "
				window.parent.update_field('task_end_date', '".$end_date->format(FMT_TIMESTAMP_DATE)."');
				window.parent.update_field('end_date', '".$end_date->format($df)."');
				window.parent.selectTime('end', '".$end_date->format("%H")."', '".$end_date->format("%M")."');
						";
$js_string .= "
			    window.parent.update_field('task_work', '".$task_work."');
			  ";	

if($duration!=0){
       $separado_por_puntos = explode(".", $duration);
       
       if (count($separado_por_puntos)>1)
       {
       	if(strlen($separado_por_puntos[1])>3){
       	$duration = number_format($duration, 3, '.', '');
       	}
       }
}

$js_string .= "
			    window.parent.update_field('task_duration', '".$duration."');
			 ";	


if ($objTask->task_target_budget_hhrr != '')
{
$js_string .= "
			    window.parent.update_budget_hhrr('".$objTask->task_target_budget_hhrr."')
			  ";	
}

$js_string .= "window.parent.show_message('".$msg_error."');";

echo "<pre>".$js_string."</pre>";

$tiempo_micro1[2]= microtime(); 
$q_espacios1 = explode(" ",$tiempo_micro1[2]); 
$tiempo1_[2]= $q_espacios1[1]+$q_espacios1[0]; 
$tiempo_utilizado1 = number_format(($tiempo1_[2] - $tiempo1_[1]),3, "." ,","); 
echo "<br>Sale Task_duration_calc : ".date('H:i:s')." ($tiempo_utilizado1 seg)<br>";


?>
<script language="javascript">



function goback(){
	window.parent.progress_msg('ocultar');
    <?php echo $js_string; ?>
}

window.setTimeout("goback()", 1);
</script>


<?



#########################################################
#                     FUNCIONES                         #
#########################################################

function verifica_feriados($fecha,$feriados,$end){
  
  /*echo "<br>verifica_feriados<br>";
  echo "<pre>";
  print_r($fecha);
  echo "</pre>";*/

  $cur_date = substr($fecha->format(FMT_TIMESTAMP),0,8);
  
  $exclusiones = $fecha->_exclusions;
  
  // Reviso los feriados 
  if(count($feriados)>0){
	  foreach($feriados as $key => $valores)
		{
			if($key==$cur_date)
			{
			  $fecha->day = $fecha->day + 1;
			  $cur_date = substr($fecha->format(FMT_TIMESTAMP),0,8);
			}	
		}
  }
 
  // Reviso las exclusiones del usuario
  if(count($exclusiones)>0)
  {
	  foreach($exclusiones as $key => $valores)
		{
			if($key==$cur_date)
			{
			  $fecha->day = $fecha->day + 1;
			  $cur_date = substr($fecha->format(FMT_TIMESTAMP),0,8);
			}
		}
  }
    
	return $fecha;

}

function calcula_target_budget_hhrr($users_assigned,$objTask)
{
           $budget_hhrr = 0;
           
		   if(count($users_assigned)>0){
           $unidades_total = array_sum($objTask->_assigned_users);
		   }else{
		   $unidades_total = "0";
		   }


		   //echo "<br>Unidades total: ".$unidades_total."<br>";
 
			for ($i=0; $i<count($users_assigned); $i++)
			{
              $user = $users_assigned[$i];
              $objTask->_assigned_users[$user] = ceil($objTask->_assigned_users[$user]);

			  $sql_user = mysql_query("SELECT user_cost_per_hour FROM users WHERE user_id = '$user' ");
			  $cost_per_hour = mysql_result($sql_user,0);
              
			  $presupuesto = (($objTask->_assigned_users[$user] * $objTask->task_work ) * $cost_per_hour) / $unidades_total ;

			  echo "Presupuesto = (".$objTask->_assigned_users[$user]."*".$objTask->task_work.")/ 100 * ".count($users_assigned).") *".$cost_per_hour."= ".$presupuesto."<br>"; 
              
			  $budget_hhrr = $presupuesto + $budget_hhrr;
			  $budget_hhrr = number_format($budget_hhrr,2);
			 		
            }
            
			if(count($users_assigned)== 0 )
		    {
             $budget_hhrr = 0;
		    }

		return $budget_hhrr;
}

function update_end_date(&$sd, &$ed, $duration, $duration_type, &$msg, $hollidays,$task_constraint_type)
{
   global $AppUI;
        
	$df = $AppUI->getPref('SHDATEFORMAT');	
	$tf = $AppUI->getPref('TIMEFORMAT');

	$cur_sd = $sd->format(FMT_TIMESTAMP);
	$cur_ed = $ed;
	$sd->fitDateToCalendar();
    
   //	echo "<br><U>Update end date:</U><br>";
   // echo "<br>385 - Fecha de inicio :".$sd->format(FMT_TIMESTAMP)."<br>";
	
	if ($duration_type == 24)
	    {
			//echo "<br>389 - La duracion es: $duration";
            
			$hs = hours_man($duration , $sd);
			
			//echo "<br>Horas hombre: $hs <br>";
            
			//echo "<br>Tipo de delimitación: ".$task_constraint_type."<br>";
			
			//if($task_constraint_type == "2" || $task_constraint_type == "4")
			//{
			//$hs = $hs * (-1);
			//}
			//else{
			$ed->setDate($sd->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
		//	}
            
			
            //echo "<br>390b - Le agrego hs : ".$hs."<br>";
            //echo "<br>388 - Fecha de fin : ".$ed->format(FMT_TIMESTAMP);
			
			$ed->addHours($hs);

		    //echo  "<br>390b - Fecha de fin mas duración: ".$ed->format(FMT_TIMESTAMP)."<br>";	
		}
	    else 
	    {
			//echo "<br>390b - Le agrego hs : ".$duration."<br>";
			$ed->setDate($sd->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
			$ed->addHours($duration);
			//echo  "<br>390 - Fecha de fin mas duración: ".$ed->format(FMT_TIMESTAMP)."<br>";
		}


	$cur_ed = $ed;
    
	//if($task_constraint_type == "2" || $task_constraint_type == "4" )
	//{
	//	$sd = $ed;
	//	$ed = $cur_ed;	
	//}
    
	//echo "<br>392 - Fecha de fin : ".$ed->format(FMT_TIMESTAMP)."<br>";
    //echo "<br>393 - Fecha de inicio :".$sd->format(FMT_TIMESTAMP)."<br>";

	// Me aseguro que la fecha de fin no sea menor que la de inicio
	if ($ed->format(FMT_TIMESTAMP) < $sd->format(FMT_TIMESTAMP))
    {
		$cur_sd = $sd;
		$sd = $ed;
		$ed = $cur_sd;
	}
    
	$fecha[sd] = $sd;
	$fecha[ed] = $ed;
	
	return $fecha; 

}

function hours_man($duration , $start_date)
{   
    
	$system_calendar = new CWorkCalendar(0,"","",$start_date);
    
	$total_hours = 0;
	$days = 0;

    $tmp = explode(".", $duration);
	$duration_dia = $tmp[0];
    echo $duratio_dia;
	if(isset($tmp[1]))
	{   
	  $duration_dia = $tmp[0] + 1;
	}
    
	$eval_date = $system_calendar;
	$cal_dates = array_keys($system_calendar->_work_calendar);

	for ($i=0;$i<count($cal_dates);$i++){
		
		$start_calendar = $cal_dates[$i];
		if ($i+1 == count($cal_dates)){
			//es el ultimo calendario entonces la fecha de fin es muy grande
			$end_calendar = "29991231";
		}else{
			$end_calendar = $cal_dates[$i+1];
		}		
		
		while ($start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) && $end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)&&  $days < $duration_dia ){

				$cal = new CCalendar();		
				$cal = $system_calendar->_work_calendar[$start_calendar];
				$calday = new CCalendarDay();

				if(isset($date) ){
				$day_id = $date->getDayOfWeek() + 1;
				}
				else{
				$day_id = $eval_date->getDayOfWeek() + 1;
				}
				
				$calday = $cal->_calendar_days[$day_id];

				if($calday->calendar_day_working && !isset($system_calendar->_hollidays[$day_id]) && !isset($system_calendar->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) )
				{
					$total_hours = $calday->calendar_day_hours + $total_hours;
					$days = $days + 1;
                }


        }

		
	}

	if(isset($tmp[1]))
	{
		$hs = ($total_hours * $duration)/$days;
	}
	else{
		$hs = $total_hours;
	}
    
	return $hs;
}




function calculateWork($units, $task_duration_type, $duration, $assigned_users, $start_date, $end_date, $task_project){	
	    
		echo "<u>Función calculateWork: </u><br>";
		echo "Unidades : <br>";
		print_r($units);
		echo "<br>Tipo de duración : ".$task_duration_type;
		echo "<br>Duración :".$duration."<br><br>";
   
		$accu_work = 0;
		if (count($units) > 0 && $duration > 0)
		{
            
			//$start_date = new CWorkCalendar(2, $this->task_project,"",$this->task_start_date);
			//$end_date = new CWorkCalendar(2, $this->task_project,"",$this->task_end_date);

			$users = array_keys($assigned_users);
			$units = $assigned_users;
			$users_work=array();
			$total_units = array_sum($units);
			echo $total_units."<br>";
			
				for ($i=0; $i < count($users); $i++){
					$user_id = $users[$i];
					$user_start_date = new CWorkCalendar(3, $user_id, $task_project, $start_date);
					$user_end_date = new CWorkCalendar(3, $user_id, $task_project,$end_date);					
					$user_work[$i] = $user_start_date->dateDiff($user_end_date, 1);

					if ($user_work[$i] > 0 ){
						$accu_work += $user_work[$i] * $units[$user_id] / 100;
					}				
				}
			
		}		
		
		return $accu_work;
}


function update_duration(&$sd, &$ed, $duration_type, &$msg)
{
	global $AppUI;
	$df = $AppUI->getPref('SHDATEFORMAT');		
	$tf = $AppUI->getPref('TIMEFORMAT');

	echo "<br><U>Update Duration:</U><br><br>";
	echo "07 - Start Date: ".$sd->format(FMT_TIMESTAMP)."<br>";
	echo "08 - End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";	
    $cur_sd = $sd->format(FMT_TIMESTAMP);
	$duration = $sd->dateDiff($ed, $duration_type);

	echo "09 - End date (dif): ".$ed->format(FMT_TIMESTAMP)."<br>";	
	echo "11 - Duración : ".$duration."<br>";	
	$sd->fitDateToCalendar();
	
	echo "15 - End date : ".$cur_sd."<br>";

	if ($cur_sd != $sd->format(FMT_TIMESTAMP)){
		$sd_f = new CDate($cur_sd);
		$msg .= "<!-- cur_sd:".$cur_sd." - sd:".$sd->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskStartAdjust"),$sd_f->format($df." ".$tf),$sd->format($df." ".$tf));
		$msg .= "</li>";	
		echo "MSG - 09 ".$msg;
	}	

	$cur_ed = $ed->format(FMT_TIMESTAMP);
	$ed->fitDateToCalendar(true);

	if(($ed->hour=="09")&&($duration_type==24)&&($duration!="0")){
		$duration = $duration - 1;
	}
    echo "12 - Duración : ".$duration."<br>";	
	echo "24 - End Date (Ajustado) : ".$ed->format(FMT_TIMESTAMP)."<br>";	

	if ($cur_ed != $ed->format(FMT_TIMESTAMP)){
		$ed_f = new CDate($cur_ed);
		$msg .= "<!-- cur_ed:".$cur_ed." - ed:".$ed->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskEndAdjust"),$ed_f->format($df." ".$tf),$ed->format($df." ".$tf));
		$msg .= "</li>";	
		echo "MSG - 24 ".$msg;
	}

	return "
			window.parent.update_field('task_duration', '$duration');
			";			
}


function update_start_date(&$sd, &$ed, $duration, $duration_type, &$msg, $hollidays)
{                            
	global $AppUI;

	$df = $AppUI->getPref('SHDATEFORMAT');	
	$tf = $AppUI->getPref('TIMEFORMAT');

	//echo "<br><U>Update Start date:</U><br><br>";
	//echo "47 - Start Date sin restar: ".$sd->format(FMT_TIMESTAMP)."<br>";
	//echo "48 - End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";
    
	
	$cur_ed = $ed->format(FMT_TIMESTAMP);
	$ed_f = new CDate($cur_ed);
	//$ed->fitDateToCalendar();

	//echo "49 - End Date (Días laborables): ".$ed->format(FMT_TIMESTAMP)."<br>";

	$diff =  $duration;
    //echo "50 - Duración : ".$diff."<br>";

	if ($duration_type == 24)
	    {
			//echo "<br>488 - Fecha de fin : ".$ed->format(FMT_TIMESTAMP);
			//echo "<br>489 - La duracion es: $duration";

			$hs = (-1) * ($duration *8);

			//echo "<br>490b - Le agrego hs : ".$hs."<br>";
			$ed->addHours($hs);

			//echo "<pre>";print_r($ed);echo "</pre>";
		}
	    else 
	    {
		$hs = (-1) * $duration;
		$ed->addHours($hs);
		}

    //echo "490c Variación de hs: ".$hs;
    

	$cur_sd = $sd->format(FMT_TIMESTAMP);
	$sd->setDate($ed->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
	//$sd->fitDateToCalendar();
   
	$ed->setDate($ed_f->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

	//echo  "<br>491b - Fecha de Inicio procesada: ".$sd->format(FMT_TIMESTAMP)."<br>";	
    
	// Me aseguro que la fecha de fin no sea menor que la de inicio
	if ($ed < $sd)
	{
	$ed = $sd;
	}
    
	//echo "492 - End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";

	//echo "<br>---------------------------------------------------------<br>";
	
	return $sd;	
}


function update_end_date_con_recursos( $fecha_inicio, $fecha_fin, $duration, $task_duration_type,$msg, $hollidays,$task_constraint_type )
{
	global $AppUI,$units,$task_project;
	
	
	if (count($units)>0)
	{
	    $Fecha = "00000000000000";
		
		foreach ($units as $user=>$unit)
		{
			 
			$start_date = new CWorkCalendar(3, $user, $task_project, $fecha_inicio );
		    $end_date = new CWorkCalendar(3, $user, $task_project, $fecha_fin);
			                
		    $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
		
			$sd = $dates[sd]->format(FMT_TIMESTAMP);
			$ed = $dates[ed]->format(FMT_TIMESTAMP);
			
			if ($ed > $Fecha)
			{
				$Fecha = $ed;
			}
			
			//echo "<br>$user - Fecha de inicio que sale: ".$sd."<br>";
			//echo "$user - Fecha de fin que sale: ".$ed."<br>";
		}
		
		$fecha[sd] = $sd;
		$fecha[ed] = $Fecha;
	}
	else
	{
		   $start_date = new CWorkCalendar(0,'', '', $fecha_inicio );
		   $end_date = new CWorkCalendar(0, '', '', $fecha_fin);
			                
		   $dates = update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg, $hollidays,$task_constraint_type);
		   $fecha[sd] = $dates[sd]->format(FMT_TIMESTAMP);
		   $fecha[ed] = $dates[ed]->format(FMT_TIMESTAMP);
			
	}
	
	return $fecha;
}



?>