<?php /* TASKS $Id: do_task_aed.php,v 1.3 2009-05-23 23:22:01 ctobares Exp $ */

echo "<pre>"; var_dump($_POST);echo "</pre>";

$del = isset($_POST['del']) ? $_POST['del'] : 0;
$hassign = @$_POST['hassign'];
$hdependencies = @$_POST['hdependencies'];
$notify = isset($_POST['task_notify']) ? $_POST['task_notify'] : 0;
$dialog = intval(dPgetParam( $_GET, "dialog", 0 ));
$suppressLogo = intval(dPgetParam( $_GET, "suppressLogo", 0 ));
$callback = dPgetParam( $_GET, "callback", "" );


$obj = new CTask();

if($_POST[task_effort_driven] =="")
{
	$obj->task_effort_driven = '0';
}

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

$obj->task_departments = implode(",", dPgetParam($_POST, "dept_ids", array()));

$custom_fields = dPgetSysVal("TaskCustomFields");
$custom_field_data = array();

if ( count($custom_fields) > 0 ){
	foreach ( $custom_fields as $key => $array ) {
		$custom_field_data[$key] = $_POST["custom_$key"];
	}
	$obj->task_custom = serialize($custom_field_data);
}

if ($obj->task_start_date) {
	$date = new CDate( $obj->task_start_date );
	$obj->task_start_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->task_end_date) {
	$date = new CDate( $obj->task_end_date );
	$obj->task_end_date = $date->format( FMT_DATETIME_MYSQL );
}

if ($obj->task_constraint_date) {
	$date = new CDate( $obj->task_constraint_date );
	$date->hour = $_POST['constraint_hour'];
	$date->minute = $_POST['constraint_minute'];
	$obj->task_constraint_date = $date->format( FMT_DATETIME_MYSQL );


}


echo 'Control 1: <pre>';print_r( $hassign );echo '</pre>';

#------------------ Preparo el wbs de la tarea creada o editada ----------------------------#

echo "<br>Tarea padre: ". $obj->task_parent;

	$nivel = mysql_query("SELECT task_id, task_parent, task_wbs_level, task_wbs_number FROM tasks
						 WHERE task_project = '$obj->task_project'
						 ORDER BY task_project");

	while($vec = mysql_fetch_array($nivel) )
	{
		$tareas[$vec[task_id]][0] = $vec[task_parent];
		$tareas[$vec[task_id]][1] = $vec[task_wbs_level];
		$tareas[$vec[task_id]][2] = $vec[task_wbs_number];
	}
    
	$nivel_1_p =  $tareas[$obj->task_parent][0];
	$nivel_1 = $tareas[$obj->task_parent][1];
	$nivel_1_n =  $tareas[$obj->task_parent][2];

	$wbs_t = $nivel_1_n;

	for($i = 1; $i <= $nivel_1; $i ++ )
	{  
		$sig_nivel = $tareas[$nivel_1_p][1];
		$sig_nivel_p =  $tareas[$nivel_1_p][0];
		$sig_nivel_n =  $tareas[$nivel_1_p][2];
           
		$wbs_t = $sig_nivel_n.".".$wbs_t;	   

		$nivel_1_p = $sig_nivel_p;
	}
    
	if($_POST['task_before']==$obj->task_parent)
	{
	$wbs_n = "1";
	}
	else
	{
	 $wbs_n = $tareas[$_POST['task_before']][2] + 1;

	 echo "<br>".$_POST['task_before']." + 1<br>";
	}
    
	$wbs = $wbs_t.".".$wbs_n;

	if($obj->task_parent=="0")
	{
	$obj->task_wbs_level = 0;
	}
	else{
	$obj->task_wbs_level = $nivel_1 + 1;
	}

	$obj->task_wbs_number = $wbs_n; 
	

	echo "<br>Nivel: ".$obj->task_wbs_level."<br>";
	echo "<br>Number: ".$obj->task_wbs_number."<br>";

echo "<br><br>--------------------------------<br>";
#-------------------- Fin del cálculo de WBS ---------------------------#


if ($del) {
	$deleteChildren = $_POST["deleteChildren"] == "true" ? true : false;
	
	// antes de borrar, traigo las hijas de la que borro
	
	echo "<pre>";
	  print_r($obj);
	echo "</pre>";
	
	// Acomodo los wbs_level de las tareas del proyecto
	    
	$query_wbs = "SELECT task_id, task_wbs_number FROM tasks WHERE task_parent = '$obj->task_id' order by task_wbs_number asc";
	
	$wbs_tasks = db_loadHashList($query_wbs);
	
	
	 if(count($wbs_tasks)>0)
             {
	    foreach($wbs_tasks as $key=> $number)
	    {
		if($key != $obj->task_id)
		$hijas_de_la_borrada[$key] = $number;
	    }
	    
	    echo "Hijas de la borrada<pre>";
	      print_r($hijas_de_la_borrada);
	    echo "</pre>";
     }
     
    $query_wbs_actual = "SELECT task_id, task_wbs_number FROM tasks WHERE task_id = '".$obj->task_id."' order by task_wbs_number asc";
	$wbs_tasks_actual = db_loadHashList($query_wbs_actual);
	$wbs_number_borrada = $wbs_tasks_actual[$obj->task_id];
	
	echo "wbs_number_borrada: ".$wbs_number_borrada."<br>";
  
	if (($msg = $obj->delete($deleteChildren))) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		 
		/*echo "2 - <pre>";
		  print_r($obj);
		echo "</pre>";*/
		
		 
		// Antes de entrar deberia verificar si la tarea padre de la que borre tiene otras hijas, si no es asi le pongo el campo dynamic en 0 (cero)
		$tarea_padre = $obj->task_parent;
		
	
	          unset( $AppUI->predecesoras);  
	          unset( $AppUI->sucesoras); 
	          unset( $AppUI->task_pred); 
	          unset($AppUI->task_suces);
	          
	          $query_del = "DELETE FROM task_dependencies WHERE dependencies_req_task_id ='$obj->task_id' OR  dependencies_task_id ='$obj->task_id' ";
	          db_exec($query_del);
		
		//echo "Entro a GetDateTaTe <br>";
		GetDateTaTe($obj->task_project, false);
		
		$project_tasks = $AppUI->tasks;
		
		if(count($project_tasks)>0 && $obj->task_id != $obj->task_parent)
		{ 
			$tiene_mas_hijas = 0;
			
			foreach ($project_tasks as $key=> $tareas) 
			{
			      if($tareas['parent'] == $tarea_padre && $tareas['id'] != $tarea_padre && $tareas['id'] != $obj->task_id )
			      {
			      	$tiene_mas_hijas = $tiene_mas_hijas +1;
			      }
			}
			
			if ($tiene_mas_hijas == 0)
			{
			    $AppUI->tasks[$tarea_padre]['dynamic'] = 0;
			    $AppUI->tasks[$tarea_padre][edit] = '0';
				
		                $AppUI->tasks[$tarea_padre][check_te] = '0';
		                $AppUI->tasks[$tarea_padre][check_ta] = '0';
			}
		}
		
		unset($AppUI->tasks[$obj->task_id]);
		
		//echo "Vector de tareas antes de entrar a GetDateTate<pre>"; print_r($AppUI->tasks); echo "</pre>";
		
		//echo "Vector de predecesoras antes de entrar a GetDateTate<pre>"; print_r($AppUI->tasks_predecesoras); echo "</pre>";
		//echo "Vector de sucesoras antes de entrar a GetDateTate<pre>"; print_r($AppUI->tasks_sucesoras); echo "</pre>";
	             
		//echo "Entro a GetDateTaTe <br>";
		//GetDateTaTe($obj->task_project, true);
	      
		//echo "Salgo de GetDateTaTe <br>";
		
	    $project_tasks = $AppUI->tasks;
	    
	    if(count($hijas_de_la_borrada) > 0)
		 {
			 	asort($hijas_de_la_borrada);
			 	
			 	$count = 0;
			 	
			 	foreach ($hijas_de_la_borrada as $id_t=>$wbs_numb)
			 	{
			 		$new_wbs_number = $wbs_number_borrada + $count;
			 		
			 		if($id_t != $obj->task_id)
			 		{
			 		$update_number_wbs = "UPDATE tasks SET task_wbs_number ='".$new_wbs_number."', task_wbs_level='".$obj->task_wbs_level."' WHERE task_id='".$id_t."' ";		 		
			 		echo "<pre>".$update_number_wbs."</pre>";
			 		
	    			db_exec($update_number_wbs);
	    			
	    			$count = $count + 1;
			 		}
			 	}
		 }
	    
	    // Acomodo los wbs_level de las tareas del proyecto
	    
	    $query_wbs = "SELECT task_id, task_parent FROM tasks WHERE task_project = '$obj->task_project' order by task_wbs_level, task_wbs_number asc";
	    $wbs_sql = db_loadHashList($query_wbs);
	    
	    if(count($wbs_sql)>0)
	    {
	    	foreach ($wbs_sql as $t_id=> $t_parent)
	    	{
	    		if($t_id != $t_parent)
	    		{
	    			$query_level_parent = "SELECT task_id, task_wbs_level FROM tasks WHERE task_id = '$t_parent' order by task_wbs_level, task_wbs_number asc";
	                $wbs_level_sql = db_loadHashList($query_level_parent);
	    
	    			$new_level = $wbs_level_sql[$t_parent] + 1 ;
	    			
	    			$update_wbs = "UPDATE tasks SET task_wbs_level='".$new_level."' WHERE task_id='".$t_id."' ";
	    			//echo $update_wbs."<br>";
	    			$t = db_exec($update_wbs);
	    		}
	    	}
	    }
	    
	    
        //echo "Seteo todas las tareas del proyecto como no dinamicas<br>";
		 
        if(count($project_tasks)>0)
        {
			 foreach($project_tasks as $key=> $tareas)
			 {
			 	if($key =='0' || $key =='')
			    {
				 $key = $obj->task_id;
			    }
			    
			 	$sql_dynamic = "
				       UPDATE tasks set task_dynamic = '0'
					   WHERE task_id = '".$key."'
				       ";
			 	//echo "<pre>".$sql_dynamic."</pre>";
			 	
			    db_exec($sql_dynamic);
			 }
			 
			
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
			     
			   if ($key != "" )
			   {
			          if($tareas['parent']== $obj->task_id ){
			          	   
			          	   if ($obj->task_id !=$obj->task_parent){
			          	   	$parent = $obj->task_parent;
			          	   }else{
			          	   	$parent = $tareas['task_id'];
			          	   }
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
				   task_parent = '".$parent."',
				   task_constraint_date = '".$tareas['task_constraint_date']."',
				   task_constraint_type = '".$tareas['task_constraint_type']."'
				   WHERE task_id= '$key'
				   "; 
				   
				   $AppUI->tasks[$tareas['task_id']]['task_parent'] = $parent;
				   
			   	  }else{
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
			   	  }
			   	  
				//   echo "<pre>".$query."</pre><br>";
				   db_exec($query);
				   
				   if(!isset($tareas[resources]))
				   {
				   	$query2 = "DELETE FROM user_tasks WHERE task_id = '$tareas[id]' ";
				   	$sql2 = db_exec($query2);
				   }
				   
				   // Si la tarea tiene padre, marco la tarea padre como dinamica 
				   if ($tareas['parent'] != $key && $tareas['parent']!="" && ($key !='0' && $key !='' && $key != $obj->task_id))
				   {  
					   $sql_dynamic2 = "
						       UPDATE tasks set task_dynamic = '1'
							   WHERE task_id ='".$tareas["parent"]."' 
						       ";
					   echo $sql_dynamic2."<br>";
					   $dynamic_parent2 = db_exec($sql_dynamic2);
				   }
			   }
			   
			 }
        }
        // Actualizo todos los wbs del proyecto
		// echo "Funcion recalcula_wbs <br>";
		 
		 $tareas_wbs = recalcula_wbs(0,'', $obj->task_project, '');
		 
		/* echo "<pre>";
		   print_r($tareas_wbs);
		 echo "</pre>";*/
		 
		 foreach ($tareas_wbs as $k => $val)
		 {
		 	$sql_wbs =" UPDATE tasks set task_wbs_number = '".$val[number]."', task_wbs_level = '".$val[level]."' WHERE task_id ='".$k."' ";
		 	db_exec($sql_wbs);
		 }
  
		$AppUI->setMsg( 'Task deleted' );
		$AppUI->redirect( 'm=projects&a=view&project_id='.$obj->task_project );
		//$AppUI->redirect( '', -1 );
	} 
} else {
	$obj->_dependencies_list = $hdependencies;
	echo "Control 2:<br>";
	
    
	if ($obj->task_id != '0' || $obj->task_id !="")
	{
	 $aux_task = new CTask();
             $aux_task->load( $obj->task_id );
	}
	
	// Si la tarea es nuevo me aseguro que se guarde como no dinamica
	if ($obj->task_id == '0' || $obj->task_id =="")
	{
		$obj->task_dynamic = '0';
	}

	echo "Tarea nueva: <pre>";print_r($obj);echo "</pre>";
    
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( (@$_POST['task_id'] ? 'Task updated' : 'Task added'), UI_MSG_OK);

		if (($obj->task_dynamic=="1")){
			$AppUI->setMsg( " - ",null,true);
			$AppUI->setMsg( " ",UI_MSG_OK,true);
		}			
	}
	
	

	$users_task = explode(",",$_POST["hassign"]);
	$users_units = explode(",", $_POST["hunits"]);


	if (isset($users_task) && isset($users_units)) 
	{
		echo "Users tasks: <pre>"; print_r($users_task); echo "</pre><br>";
		echo "Users units: <pre>"; print_r($users_units); echo "</pre><br>";
		$obj->updateAssigned( $users_task, $users_units  );
	}

	if ($notify) 
	{
		if ($msg = $obj->notify()) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}
	}
	
}


 echo "Control 3:<br>";
 echo "<pre>";print_r($obj);echo "</pre>";
 echo "POST[task_complete_2] = ".$_POST["task_complete_2"]."<br>";
 echo "obj->task_complete    = ".$obj->task_complete."<br>";
 echo "_POST[task_manual_percent_complete_2] = ".$_POST["task_manual_percent_complete_2"]."<br>";
 echo "ISSET_POST[task_manual_percent_complete_2] = ".isset($_POST["task_manual_percent_complete_2"])."<br>";
 echo "obj->[task_manual_percent_complete] = ".$obj->task_manual_percent_complete."<br>";


 #------------------- Guardo las fechas tempranas y tardias ( Actualizo tareas hijas )--------------------------------#
 
  echo "El proyecto es ".$obj->task_project."<br>";
 
  //GetDateTaTe($obj->task_project, true);
 
  $project_tasks = $AppUI->tasks;
 
 echo "Vector de tareas: <pre>"; print_r($project_tasks); echo "</pre>";
 // Blanqueo el campo de tarea dinamica para todas las tareas del proyecto
 
 //echo "Seteo todas las tareas del proyecto como no dinamicas<br>";
 $sql_dynamic = "
	       UPDATE tasks set task_dynamic = '0'
		   WHERE task_project = '".$obj->task_project."'
	       ";
 db_exec($sql_dynamic);
 
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
	   task_parent = '".$tareas['parent']."',
	   task_constraint_date = '".$tareas[task_constraint_date]."',
	   task_constraint_type = '".$tareas[task_constraint_type]."'
	   WHERE task_id= '$key'
	   "; 
	   echo "<pre>".$query."</pre><br>";
	   db_exec($query);
	   
	   // Si la tarea tiene padre, marco la tarea padre como dinamica 
	   if ($tareas['parent'] != $key && $tareas['parent']!="" && ($key !='0' && $key !=''))
	   {  
		   // Reemplazar por vector 
		   $dynamic_tasks[$tareas['parent']] = $tareas['parent'];
	   }
	   
	   
	   if(!isset($tareas[resources]))
	   {
	   	$query2 = "DELETE FROM user_tasks WHERE task_id = '$tareas[id]' ";
	   	$sql2 = db_exec($query2);
	   }
	   
	   
   }
   
 }

 // Actualizo las tareas dinamicas
 if(count($dynamic_tasks)>0){
 $query_dynamic = "UPDATE tasks SET task_dynamic = '1' WHERE task_id IN (" . implode( ',', $dynamic_tasks ) . ")";
 $sql_dynamic = db_exec($query_dynamic);
 }

 #------------------------- Fin de fechas tempranas y tardias ------------------------------#

 // Si la tarea que edite tiene hijas entonces verifico que se hayan corrido los niveles
 if($obj->task_dynamic){
   $tasks_levels = actualiza_niveles($obj->task_id,$obj->task_wbs_level);

    echo "<pre>".$tasks_levels ."</pre>";
 }

 // Actualizo todos los wbs del proyecto
 echo "Funcion recalcula_wbs <br>";
 
 echo "(".$obj->task_id.") - Tarea padre: ".$obj->task_parent." La tarea que estoy editando tiene el wbs: ".$obj->task_wbs_level." - ".$obj->task_wbs_number."<br>";
 
 $tareas_wbs = recalcula_wbs(0,'', $obj->task_project,$obj->task_id);
 /*
 $query_tmp = "SELECT task_wbs_level, task_wbs_number FROM tasks WHERE task_id='".$obj->task_id."' ";
 $sql_tmp = db_exec($query_tmp);
 
 echo "Lo que tengo en la base de datos es: <br>";
 while($vec_tmp = mysql_fetch_array($sql_tmp))
 {
 	echo "Level :".$vec_tmp['task_wbs_level']." Number :".$vec_tmp['task_wbs_number']."<br>";
 }*/
 
 echo "<br> Wbs acomodados <pre>";
   print_r($tareas_wbs);
 echo "</pre>";
 
 
 foreach ($tareas_wbs as $k => $val)
 {
 	$sql_wbs =" UPDATE tasks set task_wbs_number = '".$val[number]."', task_wbs_level = '".$val[level]."' WHERE task_id ='".$k."' ";
 	db_exec($sql_wbs);
 }
 
 
 echo "Seteamos el estado de la tarea: ";

if (isset($_POST["task_complete_2"])) {
	echo "Incompleta -> Completa<br>";
	$obj->task_complete_set("1"); //marcamos la tarea como completa
	$obj->task_manual_percent_complete_select(TRUE);
}
else {
	echo "Completa -> Incompleta<br>";
	$obj->task_complete_set("0"); //marcamos la tarea como incompleta
}

if (isset($_POST["task_manual_percent_complete_2"])) {
	//si la tarea esta marcada como completada no permite editar el progreso
	echo "Guardamos el porcentaje de avance: ".$_POST["task_manual_percent_complete_2"]."%<br>";
	$obj->task_manual_percent_complete_insert($_POST["task_manual_percent_complete_2"]);
}


//si esta creando una nueva tarea, nos aseguramos que las tareas padre sean marcadas como incompletas
if(($obj->task_id != $obj->task_parent) AND ($obj->task_complete==0)) {
	echo "<BR>Es una nueva tarea, blanqueamos los bits de completitud en las tareas padre:";
	echo "<br>task project:".$_POST['task_project'];
	$sql = "
			UPDATE tasks set task_complete='0'
			WHERE task_id = '".$obj->task_parent."'";
	$rc=db_exec($sql);
	$sql = "
			SELECT task_parent
			FROM tasks
			WHERE task_id = '".$obj->task_parent."'
			AND task_parent != '".$obj->task_parent."'";
	$rc=db_exec($sql);
	while ($rc=db_fetch_array($rc)) {
		echo ".".$rc['task_parent'];
		$sql = "
			UPDATE tasks set task_complete='0'
			WHERE task_id = '".$rc['task_parent']."'";
		$rc2=db_exec($sql);
		//echo "<BR>$sql";
		$sql = "
			SELECT task_parent
			FROM tasks
			WHERE task_id = '".$rc['task_parent']."'
			AND task_parent != '".$rc['task_parent']."'";
		$rc=db_exec($sql);
		//echo "<BR>$sql";
	}
}


if ($callback!="" && $dialog=="1"){
   $AppUI->redirect("m=tasks&a=return_task&dialog=$dialog&suppressLogo=$suppressLogo&callback=$callback&task_id=".$obj->task_id);
}else{
    $AppUI->redirect("");
}

?>  
