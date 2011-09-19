<?php /* PROJECTS $Id: do_project_aed.php,v 1.8 2009-06-01 21:54:32 ctobares Exp $ */
$obj = new CProject();
$msg = '';

echo "<pre>"; print_r($_POST); echo "</pre>";
 
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
// convert dates to SQL format first
$date = new CDate( $obj->project_start_date );
$obj->project_start_date = $date->format( FMT_DATETIME_MYSQL );
$start_date_form = $date->format("%Y%m%d")."0900";

if ($obj->project_end_date) {
	$date = new CDate( $obj->project_end_date );
	$obj->project_end_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->project_actual_end_date) {
	$date = new CDate( $obj->project_actual_end_date );
	$obj->project_actual_end_date = $date->format( FMT_DATETIME_MYSQL );
}
 
if($obj->project_id > 0)
	$sqlWhereEmail = " AND project_id <> ".$obj->project_id;
else
	$sqlWhereEmail .= "";
	
$obj->project_email_docs = trim($obj->project_email_docs);
$obj->project_email_support = trim($obj->project_email_support);
$obj->project_email_todo = trim($obj->project_email_todo);
	
//Valite documentation email.
if(strlen($obj->project_email_docs) > 0)
{
	$queryEmail = " SELECT project_id FROM projects ";
	$queryEmail .= "WHERE (project_email_docs = '".$obj->project_email_docs."' OR project_email_support = '".$obj->project_email_docs."' OR project_email_todo = '".$obj->project_email_docs."')";
	$queryEmail .= $sqlWhereEmail;

	$resultEmail = db_loadColumn($queryEmail);
	
	if($resultEmail[0] > 0)
	{
		$AppUI->setMsg( $AppUI->_('Project documentation e-mail already exists'), UI_MSG_ERROR );
		$AppUI->redirect();
	}	
}

//Valite support email.
if(strlen($obj->project_email_support) > 0)
{
	$queryEmail = " SELECT project_id FROM projects ";
	$queryEmail .= "WHERE (project_email_docs = '".$obj->project_email_support."' OR project_email_support = '".$obj->project_email_support."' OR project_email_todo = '".$obj->project_email_support."')";
	$queryEmail .= $sqlWhereEmail;
	
	$resultEmail = db_loadColumn($queryEmail);
	
	if($resultEmail[0] > 0)
	{
		$AppUI->setMsg( $AppUI->_('Project support e-mail already exists'), UI_MSG_ERROR );
		$AppUI->redirect();
	}	
}

//Valite todo email.
if(strlen($obj->project_email_todo) > 0)
{
	$queryEmail = " SELECT project_id FROM projects ";
	$queryEmail .= "WHERE (project_email_docs = '".$obj->project_email_todo."' OR project_email_support = '".$obj->project_email_todo."' OR project_email_todo = '".$obj->project_email_todo."')";
	$queryEmail .= $sqlWhereEmail;

	$resultEmail = db_loadColumn($queryEmail);
	
	if($resultEmail[0] > 0)
	{
		$AppUI->setMsg( $AppUI->_('Project ToDo e-mail already exists'), UI_MSG_ERROR );
		$AppUI->redirect();
	}	
}

// Antes de guardar los datos traigo la fecha de inicio actual del proyecto, para saber si la va a modificar
$query_start_date = "SELECT project_id, DATE_FORMAT(project_start_date,'%Y%m%d%H%i') as start_date  FROM projects WHERE project_id='".$obj->project_id."' ";
$project_bd = db_loadHashList($query_start_date);
$start_date_bd = $project_bd[$obj->project_id];

$modify_start_date = false;

if($start_date_bd != $start_date_form ){
$modify_start_date = true;
}

$del = dPgetParam( $_POST, 'del', 0 );

// prepare (and translate) the module name ready for the suffix
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Project deleted", UI_MSG_ALERT);
		$AppUI->redirect( "m=projects" );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['project_id'];
		$AppUI->setMsg( $isNotNew ? 'Project updated' : 'Project inserted', UI_MSG_OK);
	}
	 
	if (isset($hassign)) {
		$obj->updateOwners( $hassign );
	}
	 
	if (isset($husersassign)) {
		$obj->updateUsers( $husersassign );
	}
	 
	if ( $from = $_POST['import_tasks_from'])
	{
		$source = new CProject();
		$source->load( $from );
		//echo "<p>Importando de $source->project_name</p>";
		$tasks = $source->getTasks("task_wbs_level, task_wbs_number");
		
		//echo "Tareas del proyecto <pre>"; print_r($tasks); echo "</pre>";
		/*foreach ( $tasks as $t_id )
		{
		         $t = new CTask();
		         $t->load($t_id["task_id"]);
		         
		         echo "<pre>"; print_r($t);echo "</pre>";
			
		           // Por cada usuario traigo los usuarios y los meto en un vector
		           if ($t->task_dynamic != "1")
		           {
			  $query_r = "SELECT user_id, user_units FROM user_tasks WHERE task_id='$t->task_id' ";
			  $sql_r = db_exec($query_r);
			  
			    while ($vec_r = mysql_fetch_array($sql_r))
			     {
			      $users_orig_project[$vec_r[user_id]] = $vec_r[user_id];
			     }
		           }
		}*/
		
		// Recorro la lista de usuarios de las tareas que voy a copiar para asegurarme que esos usuarios esten asignados en mi proyecto
	/*	if(count($users_orig_project)>0)
		{
		    foreach ($users_orig_project as $id_u)
		    {
		    	if(!isset($users_new_project[$id_u]))
		    	{
		    	   $no_esta[$id_u]=$id_u;
		    	}
		    }
		    
		    if(count($no_esta)>0)
		    {
		        $msg = "No se copiaron las tareas. Faltan asignar usuarios al proyecto";
		        
		        
		       $AppUI->setMsg( $msg, UI_MSG_ERROR );
		        $AppUI->redirect();
		    }
		}*/
		
		// Copio las nuevas tareas, guardo la equivalencia entre las nuevas y las otras
		foreach ( $tasks as $t_id )
		{
			$t = new CTask();
			$t->load($t_id["task_id"]);
		
			//echo "Entra la tarea de nivel: ".$t->task_wbs_level." wbs_number: ".$t->task_wbs_number;
			
			$nt = $t->copy( $obj->project_id );
			$new_t = clone $nt;
			
			$new_t->task_id = "0";
			$new_t->task_parent = "";
			
			$msg_copy = $new_t->store();
			
			$vec_refer[$t_id["task_id"]] = $new_t->task_id;
			
			if($t->task_wbs_level==0)
			{
			     $new_t->task_parent = $new_t->task_id;
			}else{
			     $new_t->task_parent = $vec_refer[$t->task_parent];
			    // echo "La tarea padre de ".$new_t->task_id." [".$t->task_parent."] es: ".$vec_refer[$t->task_parent]."<br>";
			}
			
			$new_t->task_wbs_level = $t->task_wbs_level;
		            $new_t->task_wbs_number = $t->task_wbs_number;
		            
		            // Acomodo los usuarios
		            $sql_u1 = db_exec("DELETE FROM user_tasks WHERE task_id = $new_t->task_id");
		           // $sql_u2 = db_exec("SELECT * FROM user_tasks WHERE task_id= $t->task_id ");
		            
		         /*   while($vu = mysql_fetch_array($sql_u2))
		            {
		                 $qiu = "INSERT INTO user_tasks VALUES ('".$vu['user_id']."','".$vu['user_type']."','".$new_t->task_id."','".$vu['user_units']."','".$vu['user_cost_per_hour']."')";
		                 $s = db_exec($qiu);
		            }*/
		            
			$msg_copy = $new_t->store();
			
			$tareas_wbs[$new_t->task_id][task_wbs_level] = $t->task_wbs_level;
			$tareas_wbs[$new_t->task_id][task_wbs_number] = $t->task_wbs_number;
			
			//echo "El objeto original queda entonces : <pre>"; print_r($t); echo "</pre>";
			//echo "<p>La copia es '$nt->task_name', con wbs_id = $nt->task_wbs_number</p><pre>"; print_r($new_t);echo "</pre>";
		}
		
		echo "Tareas wbs: <pre>"; print_r($tareas_wbs); echo "<pre>";
		
		foreach ($tareas_wbs as $id_t=>$data)
		{
		    $sql_wbs =" UPDATE tasks set task_wbs_number = '".$data[task_wbs_number]."', task_wbs_level = '".$data[task_wbs_level]."' WHERE task_id ='".$id_t."' ";	
		    db_exec($sql_wbs);
		}
		
		$AppUI->redirect(); 
	}
	
	include_once('./modules/public/satisfaction_suppliers_customers.php');

	//Verify client and canal satisfaction
	if(($project_level_customer_satisfaction_original != $project_level_customer_satisfaction && $project_level_customer_satisfaction != 0)
		|| ($project_level_customer_satisfaction == 0 && $project_level_customer_satisfaction_original))
	{
		addSatisfaction(1, $project_level_customer_satisfaction, $obj->project_owner, $obj->project_id);
	}

	if(($project_level_canal_satisfaction_original != $project_level_canal_satisfaction && $project_level_canal_satisfaction != 0)
		|| ($project_level_canal_satisfaction == 0 && $project_level_canal_satisfaction_original))
	{
		addSatisfaction(2, $project_level_canal_satisfaction, $obj->project_canal, $obj->project_id);
	}
		
	// Antes de salir si modifico la fecha de inicio, actualizo las tareas del proyecto
	if ($modify_start_date  || $_POST['import_tasks_from']!='0')
	{
		GetDateTaTe($obj->project_id, false);
	 
	    $project_tasks = $AppUI->tasks;
	    
	    foreach($project_tasks as $key=> $tareas)
	    {
			   if($key =='0')
			    {
				 $key = $obj->task_id;
			    }
			   
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
				   task_parent = '".$tareas['parent']."'
				   WHERE task_id= '$key'
				   "; 
				   echo "<pre>".$query."</pre><br>";
				   db_exec($query);
				   
				   if(!isset($tareas[resources]))
				   {
				   	$query2 = "DELETE FROM user_tasks WHERE task_id = '$tareas[id]' ";
				   	$sql2 = db_exec($query2);
				   }
				   
			   }
			   
	    }
        
	} // fin if  
		
	$AppUI->redirect();  
} 
?>
