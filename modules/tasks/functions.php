<?php /* Funciones para el manejo de las tareas 2006-12-07 */
require_once( $AppUI->getModuleClass( 'system' ) );
require_once( $AppUI->getModuleClass( 'calendar' ) );

// Devuelve el wbs de una tarea , ingresandola desde un vector 

function wbs($a){
	//echo "en wbs <pre>";print_r($a);echo "</pre>";
	if ($a['task_wbs_level']!=0){
			$wbs_level=$a['task_wbs_level'];
			$wbs=$a['task_wbs_number'];
			$parent=$a['task_parent'];
			
			while ($wbs_level!=0){
				$sql_wbs="SELECT CONCAT(task_wbs_number,'.$wbs') AS wbs, task_parent FROM tasks t WHERE task_id=$parent";
				$vec_wbs=mysql_fetch_array(mysql_query($sql_wbs));
				$wbs=$vec_wbs['wbs'];
				$parent=$vec_wbs['task_parent'];
				$wbs_level=$wbs_level-1;
			}
	}
	else $wbs=$a['task_wbs_number'];

	return $wbs;
}

/**
 * Recalcula de forma recurrente el wbs de todas las tareas del arbol de tareas
 *
 * @param integer $current_level = nivel actual a calcular
 * @param integer $task_parent = id de la tarea padre
 * @param integer $project = id del projecto al que pertenecen las tareas
 * 
 * @return array  $tareas = Vector con todas las tareas del proyecto con los datos ordenadas, contiene los siguientes campos: task_id, levle y number
 */

function recalcula_wbs($current_level, $task_parent, $project, $task_edited = -1)
{
	global $AppUI, $tareas_wbs;
	echo "CALCULO EL NIVEL ".$current_level."<br>";
	
	if ($task_parent > 0)
	{
		$query_parent = "AND task_parent = '$task_parent' ";
	}
	
	if($current_level =='0')
	{
		$query_level = "AND task_wbs_level = '$current_level' ";
	}
	
	// Con el projecto, el nivel y el padre traigo la rama del arbol de tareas para ordenar
            $query = "SELECT task_id, task_wbs_number FROM tasks WHERE task_project = '$project'  AND task_wbs_level = '$current_level'  $query_parent order by task_wbs_number,task_start_date asc";
	
	echo "recalcula_wbs <pre>".$query."</pre>";
	
	$rama = db_loadHashList($query);
	
	$cont = 1;
	
	if(count($rama)>0 )
	{
		echo "Rama:<pre>"; print_r($rama); echo "</pre>";
		
		foreach ($rama as $id_task =>$wbs_number) {
		    
		    if($id_task == $task_edited){
		    	//echo  "La tarea editda es $id_task <br>";
		    	 $tareas_wbs[$id_task][level] = $current_level;
		             $tareas_wbs[$id_task][number] = $wbs_number; 
		             
		    	if(count($tareas_wbs)>0){
		    		 $no_ta = 0;
		                     
		    		 if ($task_parent > 0)
		   		 { $vn = $task_parent;}else{ $vn = "0"; }
		   		 
		    		 $tareas_wbs[$id_task][t_parent] = $vn;
		    		 
			             foreach ($tareas_wbs as $id=>$datos)
			             {
			                 if($tareas_wbs[$id][level]==$current_level && $tareas_wbs[$id][number]==$wbs_number && $task_edited != $id && $tareas_wbs[$id][t_parent]==$vn){
			                       $no_ta = 1;
			                       $tarea_dup = $id;
			                  }
			                  
			             }
			             
			            if($no_ta==1){$tareas_wbs[$tarea_dup][number]= $wbs_number + 1;} 
			          //  echo "La tarea estaba duplicada  (id: $tarea_dup , no_ta: $no_ta) entonces: tareas_wbs[tarea_dup][number] = ".$tareas_wbs[$tarea_dup][number]."<br>";
		    	}
		            
		    }else{ 
		    	echo  "La tarea no editda es $id_task <br>";
		            $no_ta = 0;
		            
		    	if(count($tareas_wbs)>0){
			             foreach ($tareas_wbs as $id=>$datos)
			             {
			                 if($tareas_wbs[$id][level]==$current_level && $tareas_wbs[$id][number]==$cont && $task_edited == $id){
			                       $no_ta = 1;
			                  }
			             }
		    	}
		           //if ($no_ta == 0) { $cont= $cont + 1; }
		       //    echo "La tarea $id ya estaba en el vector : ".$no_ta."<br>";
		      if ($task_parent > 0)
		      { $vn = $task_parent;}else{ $vn = "0"; }
		      
		           $tareas_wbs[$id_task][t_parent] = $vn;
		           $tareas_wbs[$id_task][level] = $current_level;
		           $tareas_wbs[$id_task][number] = $cont; 
		    }
		    
		    $cont= $cont + 1;
		     
		}
	            // Me aseguro que hayan quedado bien los numbers de la rama
		echo "tareas_wbs<pre>"; print_r($tareas_wbs); echo "</pre>";
		
		if ($task_parent > 0)
	            {
	                   $vn = $task_parent;
	            }else{
	                   $vn = "0";
	            }
	            
		foreach ($tareas_wbs as $id=>$datos){
		     if($datos[level]==$current_level){ 
		     	if($current_level>0){
		     		if($task_parent==$datos[t_parent])
		     		{
		     		$vec_r2[$vn][$id] = $datos['number'];
		     		}
		     	}else{
		     		$vec_r2[$vn][$id] = $datos['number'];
		     	}
		     $vec_r[$id] = $datos['number'];
		     }
		 }
		
		 if($current_level>0){
		 	$vec_r = $vec_r2[$vn];
		 }
		 
		asort( $vec_r);
		
		 echo " Vec temp del nivel (parent: $task_parent): <pre>"; print_r( $vec_r); echo "</pre>";
		 
		$i = 1;
		foreach ( $vec_r as $id=>$datos){
		     $vec_rama[$i] = $id;
		     $i ++;
		 }
		 
		echo "vec_rama<pre>"; print_r($vec_rama); echo "</pre>";
		   
		for($i =1; $i <=count($vec_rama); $i++)
		{
		      $tareas_wbs[$vec_rama[$i]][level] = $current_level;
		      $tareas_wbs[$vec_rama[$i]][number] = $i; 
		      $rama[$vec_rama[$i]][level] = $current_level;
		      $rama[$vec_rama[$i]][number] = $i; 
		}
		
	           echo "Rama ordenada<pre>"; print_r($tareas_wbs); echo "</pre>";
              		
	           echo "Recorro la rama para ir bajando por nivel <br>";
	       
		// Recorro las tareas del nivel para ver si tienen hijas
		foreach ($rama as $id_task =>$wbs_number) {
		      $new_level = $current_level + 1 ;	        
		      //echo "ID: $id_task - Level: $current_level Number: ".$wbs_number['number']."<br>";
		      //echo "recalcula_wbs(".$new_level.", ".$id_task.", ".$project.", ".$task_edited.");<br>";
		      recalcula_wbs($new_level, $id_task, $project, $task_edited);
		}
		
	}else{
		return;
	}
	//return;
	return $tareas_wbs;
}


function actualiza_niveles($task_parent, $task_wbs_level)
{
     global $AppUI;
     
     $siguiente_nivel = $task_wbs_level + 1;
     // Updateo a todas las tareas que tengan como tarea padre a $task_parent con $siguiente_nivel
     $query = "UPDATE tasks SET task_wbs_level = '$siguiente_nivel' WHERE task_parent = '$task_parent' AND task_id<>'$task_parent'  ";
     $sql = db_exec($query);
     
     // Traigo las tareas que tienen como tarea padre a $task_parent (id, y tarea dinamica)
     $select = "SELECT task_id, task_dynamic FROM tasks WHERE task_parent = '$task_parent' AND task_id<>'$task_parent'  ";
     $tareas = db_loadHashList($select);
     
     if(count($tareas)>0)
     {
	     // Recorro el vec que aacbo de traer
	     foreach ($tareas as $id_t=>$dynamic) {
	          if($dynamic){
	          actualiza_niveles($id_t, $siguiente_nivel );
	          }
	     }
     }else{
           return;
     }

 }   


#Esta función arma los vectores de tareas y sus dependencias. Luego recalcula todas las fechas tempranas y tardias del proyecto

function GetDateTaTe($project_id, $edit = false)
{	
   global $AppUI,$tasks, $predecesoras, $sucesoras ;
   
   echo "<br>Funcion para calcular fechas tempranas y tardias<br>";
   //$tiempo_micro[1]= microtime(); 
   //$q_espacios = explode(" ",$tiempo_micro[1]); 
   //$tiempo_[1]= $q_espacios[1]+$q_espacios[0]; 
	
  // echo "<br><b>Entra a GetDateTaTe : </b>".date('H:i:s')."<br>";
  
   if (!$edit)
   {
	// Traigo todas las tareas
    
	$query = "SELECT task_id, DATE_FORMAT(task_start_date, '%Y%m%d%H%i%s')as task_start_date, DATE_FORMAT(task_end_date, '%Y%m%d%H%i%s')as task_end_date, task_duration, task_duration_type, task_project, task_parent,task_work ,DATE_FORMAT(task_ftei, '%Y%m%d%H%i%s')as task_ftei, DATE_FORMAT(task_ftef, '%Y%m%d%H%i%s')as task_ftef,DATE_FORMAT(task_ftai, '%Y%m%d%H%i%s')as task_ftai,DATE_FORMAT(task_ftaf, '%Y%m%d%H%i%s')as task_ftaf,DATE_FORMAT(task_constraint_date, '%Y%m%d%H%i%s')as task_constraint_date, task_constraint_type, task_dynamic, task_type, task_wbs_level, task_wbs_number, task_effort_driven, task_owner
	FROM tasks
	WHERE
	task_project = '$project_id'
	";
           //echo "<pre>".$query."</pre>";
    
	$sql = db_exec( $query );
    
	// Armo el vector de tareas
   
	while($vec = mysql_fetch_array($sql))
	{ 
	  $cant_resources = 0;
	  $resources = '';
	  
	  $tasks[$vec[task_id]][id] = $vec[task_id];
	  $tasks[$vec[task_id]][start_date] = $vec[task_start_date];
	  $tasks[$vec[task_id]][end_date] = $vec[task_end_date];
	  $tasks[$vec[task_id]][duration] = $vec[task_duration];
	  $tasks[$vec[task_id]][duration_type] = $vec[task_duration_type];
	  $tasks[$vec[task_id]][project] = $vec[task_project];
	  $tasks[$vec[task_id]][parent] = $vec[task_parent];
	  $tasks[$vec[task_id]][dynamic] = $vec[task_dynamic];
	  
	  if ($vec[task_dynamic] == "1")
	  {
	    $tasks[$vec[task_id]][task_constraint_date] = "00000000000000";
	    $tasks[$vec[task_id]][task_constraint_type] = "3";
	  }
	  else 
	  {
	    $tasks[$vec[task_id]][task_constraint_date] = $vec[task_constraint_date];
	    $tasks[$vec[task_id]][task_constraint_type] = $vec[task_constraint_type];
	  }
	  
	  $tasks[$vec[task_id]][work] = $vec[task_work];
	  $tasks[$vec[task_id]][type] = $vec[task_type];
	  $tasks[$vec[task_id]][FTeI] = $vec[task_ftei];
	  $tasks[$vec[task_id]][FTeF] = $vec[task_ftef];
	  $tasks[$vec[task_id]][FTaI] = $vec[task_ftai];
	  $tasks[$vec[task_id]][FTaF] = $vec[task_ftaf];
	  $tasks[$vec[task_id]][task_effort_driven] = $vec[task_effort_driven];
	  $tasks[$vec[task_id]][task_owner] = $vec[task_owner];
     
	  settype($tasks[$vec[task_id]][work],string);
	  // Con cada tarea traigo los recursos 
	  
	  if ($vec[task_dynamic] != "1")
	  {
	 
		  $query_r = "SELECT user_id, user_units FROM user_tasks WHERE task_id='$vec[task_id]' ";
		  $sql_r = db_exec($query_r);
	      
		    while ($vec_r = mysql_fetch_array($sql_r))
			{
			$resources[$vec_r[user_id]] = $vec_r[user_units];
			$cant_resources = $cant_resources + 1 ; 
			}
		  
		  if ($cant_resources >0)
		  {
		   $tasks[$vec[task_id]][resources] = $resources;
		  }
	  }
	  
	  if($vec[task_effort_driven]=='0' &&  $vec[task_dynamic] != "1")
	  {
	  	unset($resources);
	  	$resources[$vec[task_owner]] = 100;
	  	$tasks[$vec[task_id]][resources] = $resources;
	  }
	  
	  $tasks[$vec[task_id]][wbs_level] = $vec[task_wbs_level];
	  $tasks[$vec[task_id]][wbs_number] = $vec[task_wbs_number];
	  $tasks[$vec[task_id]][edit] = '0';
	  $tasks[$vec[task_id]][check_te] = "0";
              $tasks[$vec[task_id]][check_ta] = "0";
	  
	}

	
  }else{

  $tasks = $AppUI->tasks;
  
  } 
  
  unset($AppUI->tasks[""]);
     
  echo "<br>Vector de tareas inicial";
  echo "<pre>"; print_r($tasks); echo "</pre><br><br>";
 
   if (!$edit)
   {
    if(count($tasks)>0){
		foreach($tasks as $key=>$valores)
		{
           
		   // Con cada tarea traigo las predecesoras (dependencias)	

		   $query_pre = "SELECT dependencies_req_task_id FROM task_dependencies WHERE dependencies_task_id ='$key' ";

		   $t = db_exec($query_pre);
		   $cant1 = mysql_num_rows($t);
		   
		   if($cant1==0)
		   {
		   $predecesoras[$key][0] = '';
		   }
		   else
		   {
			  $a = 0;
			  while( $vec2 =  mysql_fetch_array($t))
			  { 
			   $predecesoras[$key][$a] = $vec2[0];
			   $a = $a + 1;
			  }
		   }

		   
		   // Con cada tarea traigo las sucesoras	

			$query_suc = "SELECT dependencies_task_id FROM task_dependencies WHERE dependencies_req_task_id ='$key' ";
			//echo "<pre>$query_suc</pre>";
			
			$t_s = db_exec($query_suc);
			$cant2 = mysql_num_rows($t_s);
		   
			if($cant2==0)
			{
			$sucesoras[$key][0] = '';
			}
			else
			{
				$b = 0;
				while( $vec3 =  mysql_fetch_array($t_s))
				{ 
				  $sucesoras[$key][$b] = $vec3[0];

				  $b = $b + 1;
				}
			}
			
		}
		
    }
     
	 $AppUI->tasks_predecesoras =  $predecesoras;
	 $AppUI->tasks_sucesoras = $sucesoras;

   }
   else
   { 
	 $predecesoras = $AppUI->tasks_predecesoras;
	 $sucesoras = $AppUI->tasks_sucesoras;
	 
   }
   
   

	echo "Predecesoras<br><pre>"; print_r($predecesoras); echo "</pre><br><br>";

	echo "Sucesoras<br><pre>"; print_r($sucesoras); echo "</pre><br><br>";
    
   // echo "Ordena hijas <br><pre>"; print_r($tasks_sort); echo "</pre>";
    
    
    // Armo el arbol de tareas de acuerdo a su wbs
	 if(count($tasks)>0)
	 {
	     
	    // Las tareas que son dinamicas las meto en un vector (id_tarea, level_wbs) para saber en que orden actualizar las tareas padres
	   foreach($tasks as $key=>$valores)
	   {   
			if($valores[dynamic]==1)
			{
				$dinamicas[$key]= $valores[wbs_level];
			}
	   }
	   
	   if (count($dinamicas)>0)
	   {
	   	arsort($dinamicas);
	   }
	   
	  /* echo "Vector de dinamicas<pre>";
	     print_r($dinamicas);
	   echo "</pre>";*/
	  
	   
    // De acuerdo al vector con las tareas ordenadas, me fijo que ninguna tarea padre tenga dependencias , si es asi se las paso a sus tareas hijas.
	 foreach($tasks as $key=>$valores)
	 {   
		 if($tasks[$key][wbs_level] == '0')
		 {
		   //$rama_sort = $tasks_t[$tasks[$key][wbs_number]][$key];
		   //$AppUI->task_pred[$key] = $predecesoras[$key];
		   $AppUI->task_pred[$key] = Array();
		    	
		   hereda_dependencias($key);
		 }
	 }
	 
	
	foreach ($AppUI->task_pred as $key=>$pred)
	 {
	 	if (count($pred)==0)
	 	{
	 	 $AppUI->task_pred[$key][0] = "";
	 	}
	 }
		
		
	  echo "Nuevo vec pred <pre>";
		print_r($AppUI->task_pred);
	  echo "</pre>";  
	
	 
	 // Inicializo el vector de sucesoras
	 foreach ($AppUI->task_pred as $key=>$pred)
	 {
	 	$AppUI->task_suces[$key] = Array();
	 }
	 
	
	 // Recorro el vec de predecesoras y voy armando el de sucesoras
	 foreach ($AppUI->task_pred as $key=>$pred)
	 {
	 	if ($pred[0]!="")
	 	{
	 		foreach ($pred as $ind=>$id_pred)
	 		{
	 			if($id_pred != $key){
	 			$nuevo_ind = count($AppUI->task_suces[$id_pred]);
	 			$suces_n[$nuevo_ind] = $key;
	 			
	 			$AppUI->task_suces[$id_pred][] = $key;
	 			}
	 		}
	 		
	 		
	 	}
	 }
	 
	 foreach ($AppUI->task_suces as $key=>$suces)
	 {
	 	if (count($suces)==0)
	 	{
	 	 $AppUI->task_suces[$key][0] = "";
	 	}
	 }
		
	 echo "Nuevo vec suces <pre>";
		   print_r($AppUI->task_suces);
	 echo "</pre>"; 
		
	 $predecesoras = $AppUI->task_pred;
	 $sucesoras = $AppUI->task_suces;	
    }
    
    
   //echo "Predecesoras (".count($predecesoras).")<br><pre>"; print_r($predecesoras); echo "</pre><br><br>";
   //echo "Sucesoras (".count($sucesoras).")<br><pre>"; print_r($sucesoras); echo "</pre><br><br>";
   
	
	//Recorrer el vector de predecesoras y calcular recursivamente las fechas tempranas, arrancando por aquellas que no tienen predecesoras, primero calculo las hijas
	//$AppUI->cont_Fte = 0;
	
	if(count($predecesoras)>0)
	{   
		arsort($predecesoras);
		
		foreach($predecesoras as $key=> $predecesora)
		{   
			//$tasks_s = $tasks_sort[$key];
			
			if($predecesora[0]=='')
			{  
				$task_sucesoras = $sucesoras[$key];
				$task_predecesoras = $predecesoras[$key];
				 
				if($tasks[$key][dynamic]==0){
				CalculaFechasTempranas($tasks[$key], $task_sucesoras, $task_predecesoras);
				}
			}

		}
    } 
     
	//Recorrer el vector de sucesoras y calcular recursivamente las fechas tardias, arrancando por aquellas que no tienen sucesoras, primero calculo las hijas
    
	if(count($sucesoras)>0)
	{
		foreach($sucesoras as $key=> $sucesora)
		{   
			//$tasks_s = $tasks_sort[$key];
			
			if($sucesora[0]=='')
			{   
				$task_sucesoras = $sucesoras[$key];
				$task_predecesoras = $predecesoras[$key];
				
				if($tasks[$key][dynamic]==0)
				CalculaFechasTardias($tasks[$key], $task_sucesoras, $task_predecesoras);
			}

		}
    }
    
    
    // Recorro el vector de tareas, y tras haber actualizado las fechas tardias y tempranas actualizo las fechas de inicio y fin.
    
	if(count($tasks)>0){

	  foreach($tasks as $key=>$task)
	  {
        if (!$tasks[$key][edit] && $tasks[$key][dynamic] == '0'){ 
        	
	       CalculaFechas($tasks[$key]);
        }
	  }
	}

	
	//Recorrer el vector de predecesoras y calcular recursivamente las fechas tempranas, arrancando por aquellas que no tienen predecesoras, primero calculo las tareas padres
   
	if (count($dinamicas)>0)
	{
		foreach ($dinamicas as $id_t => $level)
		{
			$task_sucesoras = $sucesoras[$id_t];
			$task_predecesoras = $predecesoras[$id_t];
			
			CalculaFechasTempranas($tasks[$id_t], $task_sucesoras, $task_predecesoras);
		}
	}
    
	//Recorrer el vector de sucesoras y calcular recursivamente las fechas tardias, arrancando por aquellas que no tienen sucesoras, primero calculo las tareas padres
	
	if (count($dinamicas)>0)
	{
		foreach ($dinamicas as $id_t => $level)
		{
			$task_sucesoras = $sucesoras[$id_t];
			$task_predecesoras = $predecesoras[$id_t];
			
			CalculaFechasTardias($tasks[$id_t], $task_sucesoras, $task_predecesoras);
		}
	}
  

	if(count($tasks)>0){

		foreach($tasks as $key=>$task)
		{
			
         $tasks[$key][check_te] = "0";
         $tasks[$key][check_ta] = "0";
         
	       if (isset($tasks[$key][id]))
			{
		    $tasks_p[$key] = $tasks[$key];
			}
		}
		$tasks = $tasks_p;
	}
	
	
    //asort($tasks);
    $AppUI->tasks = $tasks; 
    
	/*
	$tiempo_micro[2]= microtime(); 
	$q_espacios = explode(" ",$tiempo_micro[2]); 
	$tiempo_[2]= $q_espacios[1]+$q_espacios[0];  
	$tiempo_utilizado = number_format(($tiempo_[2] - $tiempo_[1]),3, "." ,","); 
    */
    //echo "<br>Array de tareas procesado<pre>"; print_r($AppUI->tasks); echo "</pre>";   
    //echo "<b>Sale de GetDateTaTe :</b> ".date('H:i:s')."( $tiempo_utilizado seg )<br>";
 
	return;  
}


# Esta función calcula FTeI y FTeF para la tarea pasada por parámetro.  Las actualiza directamente en el vector global de tareas.

function CalculaFechasTempranas($task, $task_sucesoras, $task_predecesoras)
{  
   global $AppUI,$tasks, $predecesoras, $sucesoras ;
  //echo "<b>Entra a CalculaFechasTempranas [".$task[id]."] : </b>".date('H:i:s')."<br>";
  
   //echo "<b>Entra".$task[id]." ,dinamica : ".$task[dynamic].", check ".$tasks[$task[id]][check_te]."<br>";
    	
   if($task[dynamic] =='1')
   {
   # Si es dinamica FTeF: La mayor de las FTeF de sus hijas ??
   # Si es dinamica FTeI: La menor de las FTeI de sus hijas ??
   
   if(count($tasks)>0)
   {    
   	    
   	    $FTeF_tm = "00000000000000";
		$FTeI_tm = "99999999999999";
   	    
        $parent = $task[id];
        
		foreach($tasks as $key=>$valores)
		{  
		   $FTeI_t = new CWorkCalendar(0,"","", $tasks[$key][start_date]);
           
		   //echo "padre: $parent $key - Fecha de inicio: ".$tasks[$key][start_date]."<br>";

	               if ($tasks[$key][parent]== $parent && $tasks[$key][parent]!=$tasks[$key][id] && $FTeI_t->format(FMT_TIMESTAMP) < $FTeI_tm )
			{
				//echo "padre: $parent $key  entro al if :".$FTeI_t->format(FMT_TIMESTAMP)."<br>";
				
			    $FTeI_tm = $FTeI_t->format(FMT_TIMESTAMP);
			    
			} 
			
			
		}
		
		//echo "$task[id] fteI: - $FTeI_tm  <br>";
        
		foreach($tasks as $key=>$valores)
		{  
		   $FTeF_t = new CWorkCalendar(0,"","", $tasks[$key][end_date]);
           
	       if ($tasks[$key][parent]== $parent && $tasks[$key][parent]!=$tasks[$key][id] && $FTeF_t->format(FMT_TIMESTAMP) > $FTeF_tm )
			{
		    $FTeF_tm = $FTeF_t->format(FMT_TIMESTAMP);
			} 
			
		}
        
		//echo "$task[id] ftef: - $FTeF_tm <br>";
		
		$FTeI = $FTeI_tm;  
		$tasks[$task[id]][FTeI] = $FTeI; 
		$tasks[$task[id]][start_date] = $FTeI;
		
		$FTeF = $FTeF_tm;  
		$tasks[$task[id]][FTeF] = $FTeF; 
		$tasks[$task[id]][end_date] = $FTeF; 
		
        $start_d = new CWorkCalendar(0, "", "", $FTeI);
        $end_d = new CWorkCalendar(0, "", "", $FTeF);
       $duracion = $start_d->dateDiff($end_d, 24);
        $tasks[$task[id]][duration] =  $duracion;

		$work = SumaWork($task[id]);	
		
		$tasks[$task[id]][work] =  $work;
	    
		//echo "Fecha de inicio: ".$start_d->format(FMT_TIMESTAMP)."<br>";
		//echo "Fecha de fin: ".$end_d->format(FMT_TIMESTAMP)."<br>";
		//echo "Trabajo: $work <br>";

	}
    
   }
   else
   {   
       if ( $tasks[$task[id]][edit] == '0')
	   {
	   
	   #Calculo la FTeI de la tarea
	   $FTeI = CalculaFTeI($task, $task_predecesoras);

	   #Calculo la FTeF de la tarea
	   $FTeF = CalculaFTeF($task);
	   
	   }else{
          // Ya calculo las fechas desde el archivo de recalculo de tareas
          $FTeF = $tasks[$task[id]][FTeF];
          $FTeI = $tasks[$task[id]][FTeI];
	   }
       
       
   }
   
   $tasks[$task[id]][check_te] = '1';
   
   if($task_sucesoras[0]!= ""){

   	//$tasks_sort = $AppUI->tasks_sort;
   	
    #Calculo las fechas tempranas de sus sucesoras
	foreach($task_sucesoras as $key=> $id_sucesora )
    {   
    	//$tasks_s = $tasks_sort[$id_sucesora];
			
		if($id_sucesora != "")
		{	
			$task = $tasks[$id_sucesora];
			$t_sucesoras = $sucesoras[$id_sucesora];
			$t_predecesoras = $predecesoras[$id_sucesora];
			
			 if($tasks[$id_sucesora][check_te]!='1')
			 {
		 	CalculaFechasTempranas($task, $t_sucesoras,$t_predecesoras);
			 }
			
	   }
	}

  }
  
   //echo "<b>Sale de CalculaFechasTempranas [".$task[id]."] : </b>".date('H:i:s')."<br>";
   return; 
 }


# Esta función calcula FTaI y FTaF para la tarea pasada por parámetro.  Las actualiza directamente en el vector global de tareas.

function CalculaFechasTardias($task, $task_sucesoras, $task_predecesoras)
{  
   global $tasks, $predecesoras, $sucesoras, $AppUI;
  // echo "<b>Entra a CalculaFechasTardias [".$task[id]."] : </b>".date('H:i:s')."<br>";
 
   if($task[dynamic] =='1')
   {
   # Si es dinamica FTaF: La mayor de las FTaF de sus hijas
   # Si es dinamica FTaI: La menor de las FTaI de sus hijas

   if(count($tasks)>0)
   {    
	    $FTaF_tm = "00000000000000";
	    $FTaI_tm = "99999999999999";
                $parent = $task[id];

		foreach($tasks as $key=>$valores)
		{  
		   $FTaF_t = new CWorkCalendar(0,"","", $tasks[$key][end_date]);
           
		   // Busca la FTaF mas grande de las hijas de la tarea
		 
	       if ($tasks[$key][parent]== $parent && $tasks[$key][parent] !=$tasks[$key][id] && $FTaF_t->format(FMT_TIMESTAMP) >= $FTaF_tm )
			{
		    $FTaF_tm = $FTaF_t->format(FMT_TIMESTAMP);
		    
			}
		   
		}   
		
		$FTaF = $FTaF_tm;
		$tasks[$task[id]][FTaF] = $FTaF;
	   
		#Calculo la FTaF de la tarea
	    $FTaI = CalculaFTaI($task);
                $tasks[$task[id]][FTaI] = $FTaI;
	}
	
	/*echo "Tarea ".$task[id].": <br>";
	echo "FTaI: ".$FTaI."<br>";
	echo "FTaF: ".$FTaF."<br>";*/

   }
   else
   {   
	   if ( $tasks[$task[id]][edit] == '0')
	   {
	   #Calculo la FTaF de la tarea
	   $FTaF = CalculaFTaF($task, $task_sucesoras);
	   
	   #Calculo la FTeF de la tarea
	   $FTaI = CalculaFTaI($task);
	   }
	   else{
                $FTaF = $tasks[$task[id]][FTaF];
                $FTaI = $tasks[$task[id]][FTaI];
	   }
	   
	   
   }
   
   $tasks[$task[id]][check_ta] = '1';
  
   //echo $task[id]." - FTaF : ".$FTaF."<br>";
   
   if($task_predecesoras[0]!= ""){
  
   	//$tasks_sort = $AppUI->tasks_sort;
   	
    #Calculo las fechas tardias de sus predecesoras
	foreach($task_predecesoras as $key=> $id_predecesora )
    {   
    	
        //$tasks_s = $tasks_sort[$id_predecesora];
        
		if($id_predecesora != ""){
			
		      $task = $tasks[$id_predecesora];
		      $t_sucesoras = $sucesoras[$id_predecesora];
		      $t_predecesoras = $predecesoras[$id_predecesora];
		     
		       if($tasks[$id_predecesora][check_ta]!='1')
			   {
		                CalculaFechasTardias($task, $t_sucesoras,$t_predecesoras );
			   }
		}
	}

   }
   
  // echo "<b>Sale de CalculaFechasTardias [".$task[id]."] : </b>".date('H:i:s')."<br>";
   
   return; 
 }

 
 # Calcula la fecha temprana de inicio de la tarea pasada por parámetro. La mayor FTeF de sus tareas predecesoras. Si no tiene tareas predecesoras, se asigna la Fecha de Inicio del proyecto. Actualiza la fecha directamente en el vector global de tareas

 function CalculaFTeI($task, $task_predecesoras)
 {
   global $AppUI,$tasks,$predecesoras;   
   
   //$tasks_sort = $AppUI->tasks_sort;
   
   $sql = "SELECT project_start_date FROM projects WHERE project_id = '".$task[project]."' ";
   $result = db_loadColumn( $sql );
   $project_start_date = $result[0];

   $project_sd = new CDate($project_start_date);	
   $project_sd->hour = "09";    
   $project_sd = $project_sd->format(FMT_TIMESTAMP);
	
   $FTeI = $project_sd;
   $pred = 0;
   
   if ($task[task_constraint_type] == '3' || $task[task_constraint_type] =='4')
   {
	   // Recorro el vector de predecesoras y guardar la mayor 
	   if($task_predecesoras[0] != "" )
	   {  
	   	   //echo $task[id]." - Tiene predecesoras <br>";
	   	   
		   foreach($task_predecesoras as $key=> $id_predecesora )
		   {  
			  //echo "prede<pre>"; print_r($tasks[$id_predecesora]); echo "</pre>";
			   
			  $FTeF = $tasks[$id_predecesora][FTeF]; 
			  
			  if($FTeF > $FTeI )
		      {
		       $FTeI = $FTeF;
			   
		      }
			  
		   }
		   $pred = 1;
	
	   }
	   
   }
   else
   {
   	 //CalculaFechas($tasks[$task[id]]);
   	 $start_date = new CWorkCalendar(0,"","",$tasks[$task[id]][start_date]);
   	 
   	 $FTeI = $start_date->format(FMT_TIMESTAMP);
   	 
   	 $pred = 0;
   }
    
   	# Me fijo que no sea en el fin del turno
    $turno = hora_turno($task, $FTeI, "0", $pred);
    //echo "hora_turno($task, $FTeI, '0', $pred)<br>";
    //echo "$task[id] - FTeI: $FTeI /  Turno ".$turno."<br>";
    
	if($turno != "")
	{
	$FTeI = $turno;
	}
	else{
		$date = new CWorkCalendar(0,"","",$FTeI);
		$date->fitDateToCalendar(true);
		
		$FTeI = $date->format(FMT_TIMESTAMP);
	}
	
   
   $tasks[$task[id]][FTeI] = $FTeI;

    return $FTeI;
  }
 

# Calcula la fecha temprana de inicio de la tarea pasada por parámetro. FTeI + Duración de la Tarea. Actualiza la fecha directamente en el vector global de tareas

function CalculaFTeF($task)
{ 
   global $tasks, $predecesoras, $sucesoras;
   
   $task_duration = $task[duration];
   $task_duration_type = $task[duration_type];
   
   //Sacar la fecha del vector de tareas
   $FTeI = $tasks[$task[id]][FTeI];
  
   $FTeF = new CDate($FTeI);	

    if ($task_duration_type == 24)
	    { 
			$hs = $task_duration * 8 ;
			
			$f = AddDuration($task[id], $FTeF->format(FMT_TIMESTAMP) , $hs, '1');
			
		}
	    else 
	    {
		$hs = $task_duration;
		$f = AddDuration($task[id], $FTeF->format(FMT_TIMESTAMP) , $hs, '1');
		
		}
   
   $FTeF = $f;
    
    if ($hs > 0){
	   	# Me fijo que no sea en el fin del turno
	    $turno = hora_turno($task, $FTeF, "1", 0);
    }
    
	if($turno != "")
	{
	$FTeF = $turno;
	}

   $tasks[$task[id]][FTeF] = $FTeF;
   
   return $FTeF;
}
 
# Calcula la Fecha tardia de fin, se ingresa la tarea sobre la que se quiere obtener la fecha y sus sucesoras
# El resultado lo guarda directamente en el vector de tareas en memoria

function CalculaFTaF($task, $task_sucesoras)
{
   global $AppUI, $tasks, $predecesoras, $sucesoras;
   
   //$tasks_sort = $AppUI->tasks_sort;
   $suc = 0;
  
   
   if ($task[task_constraint_type]=='3' || $task[task_constraint_type]=='4')
   {
	   // Recorro el vector de sucesoras y guardar la menor FTaI
	  /* echo $task[id]." -Sucesoras <pre>";
	      print_r($task_sucesoras);
	   echo "</pre>"; */
	   
	   
	   if($task_sucesoras['0'] != "" || $task_sucesoras['0'] =='0' )
	   {   
	   	   //echo "<br>".$task[id]." - Tiene sucesoras <br>";
	   	   
		   $FTaF = "99999999999999";
	
		   foreach($task_sucesoras as $key=> $id_sucesora )
		   {  
			  //echo "Suces de $task[id] <pre>"; print_r($tasks[$id_sucesora]); echo "</pre>";
			   
			  $FTaI = $tasks[$id_sucesora][FTaI]; 
	          
			  //echo "if(".$FTaI." < ".$FTaF." )<br>";
			  
			  if($FTaI < $FTaF )
		      {
		       $FTaF = $FTaI;
		      }
			  
		   }
	       $suc = 1 ;
	    }
	   else 
	   {
	   	 $FTaF = $tasks[$task[id]][FTeF];
	   }
   }
   else 
   {
   	 
   	 //CalculaFechas($tasks[$task[id]]);
   	 $end_date = new CWorkCalendar(0,"","",$tasks[$task[id]][end_date]);
   	 
   	 $FTaF = $end_date->format(FMT_TIMESTAMP);
   	 
   	 $suc = 1 ;
   }
   
   //echo "<br>".$task[id]." - <br>FTaF: $FTaF<br>";
   
   //if ($task_sucesoras[0] == "" && $task_sucesoras['0'] !='0' && $FTaF =="")
   if ($FTaF =="")
   {
     // Si no tiene tareas sucesoras y su tarea padre tampoco tiene sucesoras, se asigna la FTeF mas grande del proyecto en el caso que no tenga padre
     
       //echo "<br>".$task[id]." - No tiene sucesoras ni padre<br>";
     // $MTeF = "00000000000000";
      
	   foreach ($tasks as $key => $t)
	   {   
	   	  // echo "<br>if(".$t[FTeF]." >= ".$MTeF."  )";
	   	   
		   if($t[FTeF] >= $MTeF && $t[id]!="" )
		   {
		   $MTeF = $t[FTeF];
		   }
			
	   }   
      // echo "No tiene sucesoras $task[id] - $MTeF <br>";
	   $FTaF = $MTeF;
   }
   
   
   $turno = hora_turno($task, $FTaF, 1, $suc);
   //echo "hora_turno($task, ".$FTaF.", 1, ".$suc.");<br>";
   //echo $task[id]."- FTaF: $FTaF / Turno ".$turno."<br>";
  
	
	if($turno != "" && $task[duration]>0)
	{
	$FTaF = $turno;
	}

   $tasks[$task[id]][FTaF] = $FTaF;

    return $FTaF;

}

# Calcula la Fecha tardia de inicio, se ingresa la tarea sobre la que se quiere obtener la fecha
# El resultado lo guarda directamente en el vector de tareas en memoria
function CalculaFTaI($task)
{
  global $tasks, $predecesoras, $sucesoras;

   $task_duration = $task[duration];
   $task_duration_type = $task[duration_type];
   
   //Sacar la fecha del vector de tareas
   $FTaF = $tasks[$task[id]][FTaF];
   //echo "FTaI: $task[id] - $FTaF <br>";
   
   $FTaI = new CDate($FTaF);	

    if ($task_duration_type == 24)
	    { 
			$hs = (-1)*($task_duration *8) ;
			$f = AddDuration($task[id], $FTaI->format(FMT_TIMESTAMP) , $hs, '0');
			

		}
	    else 
	    {
		$hs = (-1)* $task_duration;
		$f = AddDuration($task[id], $FTaI->format(FMT_TIMESTAMP), $hs, '0');
		
		}
   
    $FTaI = $f;
    $FTaI_bak = $FTaI;
    
    if ($hs !=0 && $hs !="")
    {
	    # Me fijo que no sea en el fin del turno
	    $turno = hora_turno($task, $FTaI, "0",0);
	    //echo "$task[id] - FTaI: $f / Turno".$turno."<br>";
    }
    
    if($turno != "")
	{
	  $FTaI = $turno;
	}else{
      $date = new CWorkCalendar(0,"","",$FTaI);
	  $date->fitDateToCalendar(true);
	  $FTaI = $date->format(FMT_TIMESTAMP);
	}
    
	// Si cambio la FTaI actualizo tambien la FTaF
	if($FTaI != $FTaI_bak)
	{
		$hs_pos = $hs * (-1);
		$FTF =  AddDuration($task[id], $FTaI, $hs_pos, '0');
		//echo "$task[id] - FTaI $FTaI FTaF $FTF \ duracion $hs_pos <br>";
		$tasks[$task[id]][FTaF] = $FTF;
	}
			
   $tasks[$task[id]][FTaI] = $FTaI;
   //echo "$task[id] - $FTaI \ duracion $hs <br>";
 
   return $FTaI;


}


# Entra el id de una tarea, la fecha, duracion y si es fecha de inicio o fecha de fin
# Si es fecha de inicio, le suma la duracion y devuelve la fecha de fin
# Si es fecha de fin, le resta la duracion y devuelve la fecha de inicio

function AddDuration($id_task, $date, $duration, $sd)
{
	global $tasks;
    
    //echo "[$id_task] - $date / $duration <br>";
    //echo "Usuarios <pre>"; print_r( $tasks[$id_task][resources]); echo "</pre>";
    
    if(count($tasks[$id_task][resources])==0)
    {
         $resources[$tasks[$id_task][task_owner]] = 100;
         $users_task = $resources;
    }else{
          $users_task = $tasks[$id_task][resources];
    }
    
    
    //$hs_duration = $duration;
	
    $total_hours = 0;
	
	if($sd) // Suma Fecha de inicio mas duracion
	{
		
	  // Recorro el vector
	  if(count($users_task)>0)
	  { 
        //$Fe = "99999999999999";
        $Fe = "00000000000000";
        
        foreach($users_task as $id_user=> $units)
		{  

			 if($tasks[$id_task][type]== '2')
			  {
				$Fecha =  new CWorkCalendar(0,'', '' , $date );
			  }
			  else
			  { 
				$Fecha =  new CWorkCalendar(3,$id_user,$tasks[$id_task][project] ,$date );
				//if ($id_task == '1210')echo "[$id_task] - ".$Fecha->format(FMT_TIMESTAMP)."<br>";
			  }
			  
			  //if ($id_task == '1210') { echo "<pre>";print_r($Fecha); echo "</pre>"; }
              
			  $total_hours += ($duration * $units)/100;
              
			  $Fecha->addHours($duration);
			  
			  if($Fecha->format(FMT_TIMESTAMP) > $Fe)
			  {
			  $Fe = $Fecha->format(FMT_TIMESTAMP);
			  }
		}

		$Fecha = $Fe;

        if($tasks[$id_task][type]== '1' || $tasks[$id_task][type]== '2')
		{
			$tasks[$id_task][work] = $total_hours;
        } 
		   
	  }
	  else
	  {
	    $Fecha =  new CWorkCalendar(0,"", "" ,$date );
	    
	    $Fecha->addHours($duration);
		$Fecha = $Fecha->format(FMT_TIMESTAMP);
        $tasks[$id_task][work] =0;
        
	  }
      
	  
	}
	else
	{// Resta, fecha de fin menos duracion

	  // Recorro el vector
	  if(count($users_task)>0)
	  { 
		//$ed = "99999999999999";
		$ed = "00000000000000";

        foreach($users_task as $id_user=> $units)
		  {
			 if($tasks[$id_task][type]== '2')
			  {
			    $Fecha = new CWorkCalendar(0,'', '' , $date );
			  }
			  else
			  {
				$Fecha = new CWorkCalendar(3,$id_user,$tasks[$id_task][project] ,$date );
			  }

			  
			  //$total_hours += ($duration * $units)/100;
              
			  $Fecha->addHours($duration);

		      if($Fecha->format(FMT_TIMESTAMP) > $ed)
			  {
			  
			   $ed = $Fecha->format(FMT_TIMESTAMP);
			  }
		  }
         
		  $Fecha = $ed;
		  
          //if($tasks[$id_task][type]== '1' || $tasks[$id_task][type]== '2')
		  //{             
		  //	$total_hours = ($total_hours * -1);
		  //$tasks[$id_task][work] = $total_hours;
          //}

	  }
	  else
	  {
	    $Fecha =  new CWorkCalendar(0,"", "" ,$date );
	    $Fecha->addHours($duration);
		$Fecha = $Fecha->format(FMT_TIMESTAMP);
	  }

	}

	return $Fecha;

}


# Recibe una tarea, la fecha, si la fecha ingresada es de inicio o fin y si tiene sucesoras o predecesoras
# Si la hora ingresada cae en el final del turno y es una fecha de inicio, pasa la fecha al inicio del siguiente turno
# Si la hora ingresada cae en el inicio del turno y es una fecha de fin, pasa la fecha al fin del siguiente turno 

function hora_turno($task, $date, $fin, $suc, $calendario_sys = false)
{ 
	global $tasks;
	
	//echo "<br>Hora turno<br>";
	
	$users_task = $task[resources];

	if(count($users_task)> 0 && !$calendario_sys )
	 {  
	 	//echo "<pre>"; print_r($users_task); echo "</pre>";
	 	
		foreach($users_task as $id_user => $units)
		{
		//echo "CWorkCalendar(3,$id_user,$task[project] , $FTeI )<br>";
        $Fecha =  new CWorkCalendar(3,$id_user,$task[project] , $FTeI );
    
            //echo "<pre>"; print_r($Fecha); echo "</pre>";
		    foreach ($Fecha->_work_calendar as $work_calendar)
			{
             
			    for($i = 1; $i<=5; $i++)
				{   
					$k = 0;

                    for($j = 1; $j <=5; $j++ )
					{  
					   $to = "calendar_day_to_time".$j; 
					   $from = "calendar_day_from_time".$j; 

                       $turno_fin[$i][$k] = $work_calendar->_calendar_days[$i]->$to;
                       $turno_inicio[$i][$k] = $work_calendar->_calendar_days[$i]->$from;
					   $k = $k + 1; 
					}
				}

			}
	    }

	 }
	 else
	 {
	 	   //echo "Usa cal de sistema<br>";
           $Fecha =  new CWorkCalendar(0,"","", $FTeI );

		    foreach ($Fecha->_work_calendar as $work_calendar)
			{
             
			    for($i = 1; $i<=5; $i++)
				{   
					$k = 0;

                    for($j = 1; $j <=5; $j++ )
					{  
					   $to = "calendar_day_to_time".$j; 
					   $from = "calendar_day_from_time".$j; 

                       $turno_fin[$i][$k] = $work_calendar->_calendar_days[$i]->$to;
                       $turno_inicio[$i][$k] = $work_calendar->_calendar_days[$i]->$from;
					   $k = $k + 1; 
					}
				}

			}
	 }
    

	 // Comparo la fecha ingresada con la hora de fin o inicio del turno
	if($fin)
	{   
		// Fecha de fin no puede tener como hora de inicio 9 o las 14, si es asi le resto una hora
        $hora_date = substr($date, 8,4);

		$sta = 0;

		for($i=1; $i<=5; $i++)
		{
          //echo "<pre>"; print_r($turno_fin[$i]); echo "</pre>";

		   for($j=0; $j<=4; $j++)
			{
			   if($turno_inicio[$i][$j] !="")
				{
				 $hora_turno = $turno_inicio[$i][$j];
				 $hora_turno = substr($hora_turno,11,2).substr($hora_turno,14,2);
                 
				// echo "if($hora_turno == $hora_date)<br>";
				 if($hora_turno == $hora_date)
					{
					 $sta = 1;
				     $day = $i;
					 $turno = $j;
					}
				 
				 // Obtengo el turno mayor
				 if($turno_fin[$i][$j] != '')
				 {
				   $max_turno = $j;
				 }
				 
				}
			}
		}
        
		//echo "$task[id] - $date , esta ? $sta <br>";
		
        if($sta)
		{ 
          
		  $prev_shift = $turno - 1;		  
		  
			  if ($prev_shift >= 0)
			  {
			    $suc = 0;		  
			  }
			  else{
			  	$prev_shift = $turno;
			  }
		  
		  
		  // Paso al dia anterior
          $f = substr($date,6,2) - $suc;
          
          // Si paso al dia anterior, voy al final del ultimo turno
          if($suc > 0)
          {
          	$prev_shift = $max_turno;
          }
          
		  if($f < '10' )
			{
			 $f = "0".$f;
			}
          
		  //echo "El turno es $f -  $turno - $max_turno<br>";
		  
		  $hora = substr($turno_fin[$day][$prev_shift],11,2).substr($turno_fin[$day][$prev_shift],14,2);
		  $fecha = substr($date,0,6).$f.$hora."00";
		  $hora = substr($fecha,8,6);

		  $cur_date = $fecha;

		    // Me fijo que la fecha no caiga en feriado
			if($fecha != ""){
			$date = new CWorkCalendar(0,"","",$fecha);
			$date->fitDateToCalendar(true);

            
			if( $date->format(FMT_TIMESTAMP) > $fecha )
		    { 
			  $new = substr($date->format(FMT_TIMESTAMP),0,8);
			  $cur = substr($fecha,0,8);
			  $dif = ($new - $cur);
              
			  if($dif=="1")
				{
				$d = ($new - $cur) + 2;
				}
			   
			  if($dif=="2")
				{
				$d = ($new - $cur) + 1;
				}
              
			  $fecha_t = date ("Ymd" , mktime(0,0,0,$date->month ,$date->day - $d,$date->year));
              
			  $fecha = $fecha_t.$hora;
			}
			else{
			$fecha = $cur_date;
			}
            
		  }

		}
   
	}else{
        
		// Fecha temprana o tardia de inicio, no puede tener como hora las 18 o las 13, si es asi le sumo una hora
		$hora_date = substr($date, 8,4);
		
		//echo "<br>".$task[id]." - Entra: ".$date."<br>";

		$sta = 0;
        
		for($i=1; $i<=5; $i++)
		{
		   for($j=0; $j<=4; $j++)
			{   
			   if($turno_fin[$i][$j] !="")
				{
				 $hora_turno = $turno_fin[$i][$j];
				 $hora_turno = substr($hora_turno,11,2).substr($hora_turno,14,2);
                
				// echo "<br>if(".$hora_turno." == ".$hora_date.")<br>";
				 if($hora_turno == $hora_date)
					{
				    $sta = 1;
					$day = $i;
					$turno = $j;
					}
				
				 // Obtengo el turno mayor
				 if($turno_fin[$i][$j] != '')
				 {
				   $max_turno = $j;
				 }
				 
				}
			}
		}

		if($sta)
		{ 
		  
		  $next_shift = $turno + 1;
		  
		
		  if ($next_shift <= $max_turno)
		  {
		  	$suc = 0;
		  
		  }
		  else{
		  	$next_shift = $turno-1;
		  }
			
		  // Paso a la hora inicio del siguiente turno
          $f = substr($date,6,2) + $suc ;
          //echo "$task[id] : $date - $f / $turno <br>";
           
		  if($f < '10' )
			{
			 $f = "0".$f;
			}

		  $hora = substr($turno_inicio[$day][$next_shift],11,2).substr($turno_inicio[$day][$next_shift],14,2);
		  $fecha = substr($date,0,6).$f.$hora."00";

		    // Me fijo que la fecha no caiga en feriado
			if($fecha != ""){
			$date = new CWorkCalendar(0,"","",$fecha);
			$date->fitDateToCalendar();

			$fecha = $date->format(FMT_TIMESTAMP);
			}
		}


	}
   
	return $fecha;
}


# Calcula las fechas de inicio y fin, recibe una tarea, y guarda el resultado en el vector de tareas guardado en la memoria
function CalculaFechas($task)
{       
	global $tasks;
         //echo "<b>Entra a CalculaFechas [".$task[id]."] : </b>".date('H:i:s')."<br>";
         
         $task_constraint_type = $task[task_constraint_type];
		 $task_constraint_date = new CWorkCalendar(0,"","",$task[task_constraint_date]);
         
		 $start_date = new CWorkCalendar(0,"","",$task[start_date]);
		 $start_date->fitDateToCalendar();
         
		 $end_date = new CWorkCalendar(0,"","",$task[end_date]);
		 $end_date->fitDateToCalendar(true);
		 
         
		 $duration = $task[duration];
		 $task_duration = $task[duration_type];
         
		 if ($task_duration == 24)
	     {
		  $hs = $duration * 8;
		 }else{
		  $hs = $duration;
		 }

		 $FTeI = new CWorkCalendar(0,"","",$task[FTeI]);
		 $FTeF = new CWorkCalendar(0,"","",$task[FTeF]);
		 $FTaI = new CWorkCalendar(0,"","",$task[FTaI]);
		 $FTaF = new CWorkCalendar(0,"","",$task[FTaF]);
	    
         switch($task_constraint_type)
		    {   
				case "1": // Debe comenzar en 

					/*
					Fecha Inicio = Fecha especificada
					Fecha Fin = Fecha de inicio + duracion
					*/
                    
					$curd = $FTeI->format(FMT_TIMESTAMP);
				   
					if($curd < $task_constraint_date->format(FMT_TIMESTAMP) )
					{
					 $start_date->setDate($task_constraint_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
                    
					 $end_date = AddDuration($task[id], $start_date->format(FMT_TIMESTAMP), $hs, '1');
					 $end_date = new CWorkCalendar(0,"","", $end_date);
					
                     
					}
                 
				break;
		
				case "2": // Debe terminar en
					
					/*
					Fecha de fin = Fecha especificada
					Fecha de inicio = Fecha de fin - duracion
					*/

					$cur_sd = $FTeI->format(FMT_TIMESTAMP);

					$end_date->setDate($task_constraint_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
				 
					$hs = (-1)*($hs);
					$start_date = AddDuration($task[id], $end_date->format(FMT_TIMESTAMP) , $hs, '0');
					$start_date = new CWorkCalendar(0,"","", $start_date);
					

					if($cur_sd > $start_date->format(FMT_TIMESTAMP_DATE))
				    {
					  $start_date->setDate($cur_sd, DATE_FORMAT_TIMESTAMP);
                      
					  $hs = (-1)*($hs);
					  $end_date = AddDuration($task[id], $start_date->format(FMT_TIMESTAMP), $hs, '1');
					  $end_date = new CWorkCalendar(0,"","", $end_date);
					 

				    }

				break;
				case "3": // Tan pronto como sea posible
				/*
				Fecha de inicio = Fecha temprana de inicio
				Fecha de fin = Fecha temprana de fin
				*/
                
				$start_date->setDate($FTeI->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
                $end_date->setDate($FTeF->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);

				break;

				case "4": // Tan tarde como sea posible
				/*
				Fecha Inicio = Fecha Tardia de Inicio
				Fecha Fin = Fecha Tardia de Fin
				*/
                
				$end_date->setDate($FTaF->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
				$start_date->setDate($FTaI->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
				 
				break;

				case "5": // No comenzar antes de 
                   /*
				   Fecha de inicio = fecha esp cuando sea mayor a la FTeI
				   Fecha de fin = Fecha de inicio + Duracion
				   */

                   $curd = $start_date->format(FMT_TIMESTAMP);
				  
				   if($FTeI->format(FMT_TIMESTAMP) < $task_constraint_date->format(FMT_TIMESTAMP) )
					{
					$start_date->setDate($task_constraint_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
					}else{
					
					$start_date->setDate($FTeI->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
					
					}
                    
					$end_date = AddDuration($task[id], $start_date->format(FMT_TIMESTAMP), $hs, '1');
					$end_date = new CWorkCalendar(0,"","", $end_date);

				break;

				case "6": // No comenzar después de
				   /*
				   Fecha de inicio = fecha esp cuando sea menor a la FTeI
				   Fecha de fin = Fecha de inicio + duracion
				   */

				   if($FTeI->format(FMT_TIMESTAMP) > $task_constraint_date->format(FMT_TIMESTAMP))
					{
					 $start_date->setDate($task_constraint_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
					}else{
					 $start_date->setDate($FTeI->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
					}

					$end_date = AddDuration($task[id], $start_date->format(FMT_TIMESTAMP), $hs, '1');
					$end_date = new CWorkCalendar(0,"","", $end_date);


				break;

				case "7": // No terminar antes de
				    /*
					Fecha de fin = fecha esp cuando sea mayor a la FTeF
					Fecha de inicio = fecha de fin - duracion
					*/
				   
					if($FTeF->format(FMT_TIMESTAMP) < $task_constraint_date->format(FMT_TIMESTAMP) )
					{
					 $end_date->setDate($task_constraint_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
					}else{
					 $end_date->setDate($FTeF->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
					}
                    
					$hs = (-1)*($hs);
					$start_date = AddDuration($task[id], $end_date->format(FMT_TIMESTAMP) , $hs, '0');
					$start_date = new CWorkCalendar(0,"","", $start_date);

				break;

				case "8": // No terminar después de
                    /*
					Fecha de fin = Fecha esp cuando sea menor a FTeF
					Fecha de inicio = fecha de fin - duracion
					*/
				    
					$cur_sd = $FTeI->format(FMT_TIMESTAMP);

				    if ($FTeF > $task_constraint_date->format(FMT_TIMESTAMP))
					{
					$end_date->setDate($task_constraint_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
				  
				    $hs = (-1)*($hs);
					$start_date = AddDuration($task[id], $end_date->format(FMT_TIMESTAMP) , $hs, '0');
					$start_date = new CWorkCalendar(0,"","", $start_date);

					 // Me aseguro que la fecha de inicio obtenida no sea anterior a la fecha de dep
					 if($cur_sd > $start_date->format(FMT_TIMESTAMP_DATE))
				      {
					  $start_date->setDate($cur_sd, DATE_FORMAT_TIMESTAMP);
                      $hs = (-1)*($hs);
					  $end_date = AddDuration($task[id], $start_date->format(FMT_TIMESTAMP), $hs, '1');
					  $end_date = new CWorkCalendar(0,"","", $end_date);
				      }

					}else{
					  $end_date->setDate($FTeF->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
				  
				      $hs = (-1)*($hs);
					  $start_date = AddDuration($task[id], $end_date->format(FMT_TIMESTAMP) , $hs, '0');
					  $start_date = new CWorkCalendar(0,"","", $start_date);
					}

				break;

		    }
            
			$tasks[$task[id]][start_date] = $start_date->format(FMT_TIMESTAMP);
            $tasks[$task[id]][end_date] = $end_date->format(FMT_TIMESTAMP);
            //echo "<b>Sale de CalculaFechas [".$task[id]."] : </b>".date('H:i:s')."<br>";
			return;

}
 


# Recibe el id del projecto, actualiza directamente sobre la base de datos
# Trae las tareas dinamicas unicamente

function update_parent($project_id)
{
	//echo "Funcion update_parent<br>";

   // Armo un vector, con las tareas dinamicas unicamente
   $query = "SELECT task_id
	FROM tasks
	WHERE
	task_project = '$project_id' AND
    task_dynamic = '1'
	";

	$tasks_p = db_loadHashList ( $query );
    
    //echo "Tareas dinamincas <pre>"; print_r($tasks_p); echo "</pre>";

	// Recorro el vector y calculo la fecha de inicio y fin

	 if(count($tasks_p)>0){

	     foreach($tasks_p as $key)
		  {
			// Fecha Inicio: La menor de las fechas de inicio de sus hijas.
			$start_date = MinimaFechaInicio($key);
            
		    // Fecha Fin: La mayor de las fechas de fin de sus hijas.   
		    $end_date = MaximaFechaFin($key);
             
            // De la diferencia entre la fecha de fin y la de inicio obtengo la duracion de la padre
			$start_date = new CWorkCalendar(0, "", "", $start_date);
	        $end_date = new CWorkCalendar(0, "", "", $end_date);
			$duracion = $start_date->dateDiff($end_date, 24);

			$work = SumaWork($key);

			$presup_hhrr = SumaHHRR($key);

        	// Guarda las fechas en la bd
			$query = "
			         UPDATE tasks set task_start_date = '".$start_date->format("%Y%m%d%H%M")."', task_end_date = '".$end_date->format("%Y%m%d%H%M")."', task_duration='$duracion' , task_work ='$work', task_target_budget_hhrr = '$presup_hhrr'
					 WHERE
					 task_id = '".$key."'
			         ";
		  }
	 }

}


# Recibe el id de una tarea y devulve la fecha minima de inicio de esa tarea o sus hijas en caso de tenerlas
function MinimaFechaInicio($key)
{

	 # Armo un vector con mis tareas hijas y sus fechas de inicio
	 $query = "SELECT task_id, task_start_date
	  FROM tasks
	  WHERE
	  task_parent = '$key' AND task_id != '$key'
	  ";

	 $vec = db_loadHashList ( $query ); 

	 # Si no tengo hijas, devuelvo mi fecha de inicio.
	 if ($vec == null)
	  {
	   $query = "SELECT task_id, task_start_date
		  FROM tasks
		  WHERE
		  task_id = '$key'
		  ";

	   $vec = db_loadHashList ( $query );
       
	   $date = new CWorkCalendar(0,"","",$vec[$key]);

	   $FI = $date->format("%Y%m%d%H%M");
      
	   return $FI;
	  }

	 # Si tengo hijas recorro el vector y devuelvo la fecha de inicio mas temprana
	 else
	 {
		  $FI = '999999990000';
		  $FI_TMP = '';

			   foreach($vec as $key=>$fecha)
			   {
			   $date = MinimaFechaInicio($key);
               $date = new CWorkCalendar(0,"","",$date);

			   $FI_TMP = $date->format("%Y%m%d%H%M");

			   if ($FI_TMP < $FI)
				{
				   $FI = $FI_TMP;
			    }

			   }
		  return $FI;
	 }

}


# Recibe el id de una tarea y devulve la fecha maxima de fin de esa tarea o sus hijas en caso de tenerlas

function MaximaFechaFin($key)
{ 
    # Armo un vector con mis tareas hijas y sus fechas de fin
    $query = "SELECT task_id, task_end_date
	  FROM tasks
	  WHERE
	  task_parent = '$key' AND task_id != '$key'
	  ";

	 $vec = db_loadHashList ( $query ); 

	 # Si no tengo hijas, devuelvo mi fecha de fin.
	 if ($vec == null)
	 {
		 $query = "SELECT task_id, task_end_date
		  FROM tasks
		  WHERE
		  task_id = '$key'
		  ";

	     $vec = db_loadHashList ( $query );

	     $date = new CWorkCalendar(0,"","",$vec[$key]);

	     $FM = $date->format("%Y%m%d%H%M");

		return $FM;
	 }
	 # Si tengo hijas recorro el vector y devuelvo la fecha de fin mas tardia
	 else
	  {
		  $FM = '111111110000';
		  $FF_TMP = '';

		   foreach($vec as $key=>$fecha)
			 {
				   $date = MaximaFechaFin($key);
				   $date = new CWorkCalendar(0,"","",$date);

				   $FF_TMP = $date->format("%Y%m%d%H%M");

				   if ($FF_TMP > $FM)
					{
					   $FM = $FF_TMP;
					}

			 }
		  return $FM;
	  }
}


# Recibe el id de una tarea y devuelve el trabajo de esa tarea y de sus hijas

function SumaWork($task_id)
{  
	 global $tasks;
     
	 //echo "Sumawork <br>";
	 
	 foreach($tasks as $key=>$task)
	 {  
	    if ($tasks[$key][parent] == $task_id && $tasks[$key][id] != $task_id)
		 {
			$vec[$key] = $tasks[$key][work];
            
		 }
	 }
	 
    //echo "<pre>"; print_r($vec);echo "</pre>";
    
	# Si no tengo hijas, devuelvo mi trabajo.
	 if ($vec == null)
	 {
	     $WS = $tasks[$task_id][work];

		return $WS;
	 }
	 # Si tengo hijas recorro el vector y devuelvo la dur 
	 else
	  {
		  $WS_TMP = 0;

		   foreach($vec as $key=>$work)
			 {
				   $WS = SumaWork($key);
				   $WS_TMP = $WS_TMP + $WS;
			 }
			 
		  return $WS_TMP;
	  }
}

# Ingresa el id de una tarea y devuelve la sumatoria de presupuesto de hhrr
function SumaHHRR($task_id)
{
	# Meto en un vector el id de la tarea y su trabajo
    $query = "SELECT task_id, task_target_budget_hhrr
	  FROM tasks
	  WHERE
	  task_parent = '$task_id' AND task_id != '$task_id'
	  ";

	 $vec = db_loadHashList ( $query ); 
     
	# Si no tengo hijas, devuelvo mi trabajo.
	 if ($vec == null)
	 {
		 $query = "SELECT task_id, task_target_budget_hhrr
		  FROM tasks
		  WHERE
		  task_id = '$task_id'
		  ";

	     $vec = db_loadHashList ( $query );

	     $WS = $vec[$task_id];

		return $WS;
	 }
	 # Si tengo hijas recorro el vector y devuelvo la fecha de fin mas tardia
	 else
	  {
		  $WS_TMP = 0;

		   foreach($vec as $key=>$work)
			 {
				   $WS = SumaHHRR($key);

				   $WS_TMP = $WS_TMP + $WS;
			 }
		  return $WS;
	  }
}


# ingresa vector, el primer elemento sera la tarea que quiero saber si tiene padres
# devuelve un vector, este vector solo indica el orden en que voy a ir actualizando las tareas, no voy a cambiar nada sobre este vector

function ordena_hijas($tasks_s)
{
   global $AppUI,$tasks;
   
   //echo "Funcion Ordena hijas<br>";
   //echo "<pre>"; print_r($tasks_s); echo "</pre>";
   
   $ultimo = count($tasks_s) - 1;
   $actual = count($tasks_s);
   
   if ($tasks[$tasks_s[$ultimo]][parent] != $tasks[$tasks_s[$ultimo]][id])
   {
     $tasks_s[$actual] = $tasks[$tasks_s[$ultimo]][parent];
     $tasks_s = ordena_hijas($tasks_s);
     
   }
   else
   {
   	  //echo "sale<pre>"; print_r($tasks_s); echo "</pre>";
   	  $AppUI->tasks_s = $tasks_s;
      return ;
   }
   
   
}

/**
 * Arma el arbol de tareas de un proyecto
 *
 * @param integer $parent = id de la tarea padre
 * @param integer $level = nivel de la tarea padre ingresada
 */
function ordena_arbol($parent,$level)
{
   global $AppUI,$tasks;
   
   //echo "Funcion Ordena Arbol <br>";
   //echo "<pre>"; print_r($tasks_s); echo "</pre>";
    
   $nivel = $level + 1;
   $tareas = $tasks;

   foreach($tareas as $ksort=>$valsort)
   {
	   	if( $tareas[$ksort][parent] == $parent && $ksort != $parent)
	   	{
	   		$tasks_tree[$tasks[$ksort][wbs_number]][$ksort] = array();
	   		
	   		$tasks_hijas_mas_nivel = ordena_arbol($ksort,$nivel);
	   		
	   		if (count($tasks_hijas_mas_nivel)>0)
	   		{
	   			ksort($tasks_hijas_mas_nivel);
	   			$tasks_tree[$tasks[$ksort][wbs_number]][$ksort] = $tasks_hijas_mas_nivel;
	   		}
	   	} 
   }
    
   return $tasks_tree;
   
}


/**
 * Devuelve el id de la hija que termine mas tarde de la tarea ingreasada por parametro
 *
 * @param integer $id_a_buscar = id de la tarea padre 
 * @return $hija = id de la tarea hija que termine mas tarde
 */

function busca_hija_mayor($id_a_buscar)
{
	global $AppUI,$tasks;
	
	//echo "Entra a busca_rama $id_a_buscar <br>";
	$tareas = $tasks;
	
	if($tasks[$id_a_buscar][dynamic] == 1)
	{ 
		if(count($tasks)>0)
		{
		   $Fecha_mg = "00000000000000";
		   
		   foreach ($tareas as $tarea_id=>$tarea)
		   {
		   	if ($tarea['parent']== $id_a_buscar && $tarea['id'] != $id_a_buscar)
		   	 {
		   	 	$fecha_hija = $tarea[end_date];
		   	 	
		   	 	if($fecha_hija > $Fecha_mg )
			     {
			        $Fecha_mg = $fecha_hija;
			        $id_hija_mg = $tarea[id];
			     }
		   	 }
		   }
		   
		   $id_hija = $id_hija_mg;
		
		}
		
		busca_hija_mayor($id_hija);
		
	}else{
		
		$return = 1;
	}
	
	
	if($return)
	{
		$hija = $id_a_buscar;
		
	}else{
		$hija = $id_hija;
	}
	
	return ($hija);
	
	
}

function busca_hija_menor($id_a_buscar)
{
	global $AppUI,$tasks;
	
	if($tasks[$id_a_buscar][dynamic] == 1)
	{ 
		if(count($tasks)>0)
		{
		   $Fecha_mg = "99999999999999";
		   
		   foreach ($tasks as $tarea_id=>$tarea)
		   {
		   	 if ($tarea[parent]== $id_a_buscar)
		   	 {
		   	 	$fecha_hija = $tarea[start_date];
		   	 	
		   	 	if($fecha_hija < $Fecha_mg )
			     {
			        $Fecha_mg = $fecha_hija;
			        $id_hija_mg = $tarea[id];
			     }
		   	 }
		   }
		   
		   $id_hija = $id_hija_mg;
		
		}
		
		busca_hija_menor($id_hija);
		
	}else{
		
		$return = 1;
	}
	
	
	if($return)
	{
		$hija = $id_a_buscar;
		
	}else{
		$hija = $id_hija;
	}
	
	return ($hija);
}

function hereda_dependencias($key)
{
	 global $AppUI,$tasks;
	 
	 //echo "Entra $key <br>";
	 $tareas = $tasks;
	
	 $dependencias_padre = array();
	 
	 // Me fijo si la tarea tiene dependencias
	 if($AppUI->tasks_predecesoras[$key][0]!="")
	 {
	 	$dependencias_padre = $AppUI->tasks_predecesoras[$key];
	 	
	 	//Recorro la lista de dependencias, si alguna es dinamica traigo la que termine ultimo
        $dependencias_padre_tmp = array();	 	
	 	
	 	 foreach ($dependencias_padre as $ord=>$dep_parent)
		 {    
		      if($tasks[$dep_parent][dynamic] == 1)
		      { 
		         $id_hija_mg = busca_hija_mayor($dep_parent);
		         //$id_hija_mr = busca_hija_menor($dep_parent);
		           	  
		        $dependencias_padre_tmp[] =  $id_hija_mg;
		        
		        /*if ($id_hija_mg != $id_hija_mr)
		        {
		          $dependencias_padre_tmp[] =  $id_hija_mr;
		        }*/
		        
		       }else{
		          $dependencias_padre_tmp[] =  $dep_parent;
		       } 
		 	  //$dependencias_padre_tmp[] =  $dep_parent;
		 }
	 }
	 
	 /*
	 echo "Dependencias de $key :<pre>";
	    print_r($dependencias_padre_tmp);
	 echo "</pre>";
	 */
	 
	 // Me fijo si la tarea tiene hijas
	 if($tasks[$key][dynamic] == 1)
	 {
	 	//echo "$key Es dinamica <br>";
	 	
	 	// Le borro las dep a la tarea padre ( la ingresada por parametro)	
	 	unset($AppUI->task_pred[$key]);
	 	$AppUI->task_pred[$key][0] = "";
	 	
	 	// Recorro las tareas
	 	foreach ($tareas as $id_task=>$task)
		{
			
		   // Si tiene como padre a la ingresada, entonces traigo las dep de la hija
		    if ($task[parent] == $key && $task[id] != $key)
		   	{	
		   		//echo "&nbsp;&nbsp Hijas de $key ".$task[id]."<br>";
		   		$dependencias_hija = array();
		   		
		   		// Cargo las dep de cada hija
		   		 if($AppUI->tasks_predecesoras[$task[id]][0]!="")
		   		 {
	 			      $dependencias_hija = $AppUI->tasks_predecesoras[$task[id]];
	 			      
	 			      $dependencias_hija_tmp = array();
	 			      $dependencias_tmp = array();
	 			     
	 			      /*echo "Dependencias de la hija - ".$task[id]."<pre>";
	 			        print_r($dependencias_hija);
	 			      echo "</pre>"; */
	 			      
	 			      foreach ($dependencias_hija as $n_ord=>$dep_hija)
		              {
		                	if($tasks[$dep_hija][dynamic] == 1)
		                    {
				           	  $id_hija_mg2 = busca_hija_mayor($dep_hija);
				           	  //$id_hija_mr2 = busca_hija_menor($dep_hija);
				           	  
				           	  $dependencias_hija_tmp[] =  $id_hija_mg2;
				           	  
				           	  /* if ($id_hija_mg2 != $id_hija_mr2)
						        {
						          $dependencias_hija_tmp[] =  $id_hija_mr2;
						        }*/
				           	  
		                    }else{
		                	  $dependencias_hija_tmp[] =  $dep_hija;
		                    }
		                   
		                    //$dependencias_hija_tmp[] =  $dep_hija;
		              }
		           
		             if (count($dependencias_padre_tmp)>0 && count($dependencias_hija_tmp)>0)
		             {
		             	// Antes de hacer la interseccion me fijo si la fecha de dep de las hijas es mayor que
		             	
		             	# Traigo la dep del padre que termine ultimo
		             	
		             	$Fecha_M = "00000000000000";
		                $id_dep_M = "";
		                
						foreach ($dependencias_padre_tmp as $ind_tmp =>$dep_padre)
						{
						   $fecha_dep = $tasks[$dep_padre][end_date];
						   	 	
						   if($fecha_dep > $Fecha_M )
						   {
							 $Fecha_M = $fecha_dep;
							 $id_dep_M = $dep_padre;
						   }
						}
						
						if ($id_dep_M != "")
						{
							$dependencias_padre_tmp = array();
							$dependencias_padre_tmp[0] = $id_dep_M;
						}
						
						$add_parent_dep = true;
						$cant_dep_hijas = count($dependencias_hija_tmp);
						$fecha_dep_padre = $tasks[$id_dep_M][end_date];
						
						for ($z=0; $z <=$cant_dep_hijas; $z ++)
						{
							$fecha_dep_hija = $tasks[$dependencias_hija_tmp[$z]][end_date];
							
							if ($fecha_dep_hija > $fecha_dep_padre )
							{
								$add_parent_dep = false;
								$cant_dep_hijas = count($dependencias_hija_tmp);
							}
						}
						
		             	
		             	// ---------------------------------------------------------------------------------//
		             	if ($add_parent_dep){
		             	$interseccion = array_intersect($dependencias_padre_tmp,$dependencias_hija_tmp);
		             
			             	if(count($interseccion)>0)
			             	{
			             		for ($i=0; $i<=count($interseccion);$i++)
			             		{
			             			// Recorro el vec $dependencias_hija_tmp y borro las que se repiten
			             			$contador = count($dependencias_hija_tmp);
			             			
			             			for($j=0; $j<=$contador; $j++)
			             			{
			             				if ($interseccion[$i] == $dependencias_hija_tmp[$j])
			             				{
			             					unset($dependencias_hija_tmp[$j]);
			             					$contador = count($dependencias_hija_tmp);
			             				}
			             			}
			             		}
			             	}
		             	}
		             	
		             }
		            
		            if ($add_parent_dep){
		                   $AppUI->task_pred[$task[id]] = array_merge($dependencias_padre_tmp, $dependencias_hija_tmp);
		            }else{
		                  $AppUI->task_pred[$task[id]] = $dependencias_hija_tmp;
		            }
		             
		           
	 		     }else{
	 		     	
	 		     	if(count($dependencias_padre_tmp)>0){
	 		     	   $AppUI->task_pred[$task[id]] = $dependencias_padre_tmp;
	 		     	}else{
	 		     	   $AppUI->task_pred[$task[id]][0] = "";
	 		     	}
	 		     	 /*echo "Dependencias de la hija - ".$tarea_vec[id]."<br>";
	 			        echo "No tiene";
	 			      echo "</br><br>";*/
	 		     }
	 		     
	 		     hereda_dependencias($task[id]);
	 		     
		   	} 
		}
	 	
	 }else{
	 	
	 	// Si no tiene hijas armo el vec y salgo
	 	if(count($dependencias_padre_tmp)>0){
	 		/*
	 		  echo "task_pred[]<pre>";
	 		      print_r($AppUI->task_pred[$key]);
	 		  echo "</pre>";*/
	 		     	
	 		if(count($AppUI->task_pred[$key])>0){
		 		
		 		$interseccion2 = array_intersect($dependencias_padre_tmp,$AppUI->task_pred[$key]);
		           /*
		 			echo "La interseccion es <pre>"; 
		 			   print_r($interseccion2);
		 			echo "</pre>";*/
		 		 
		        if(count($interseccion2)>0)
		        {
		           for ($l=0; $l<=count($interseccion2);$l++)
		           {
		             // Recorro el vec $dependencias_hija_tmp y borro las que se repiten
		             $cont = count($dependencias_padre_tmp);
		             			
		             for($t=0; $t<=$cont; $t++)
		             {
		             	if ($interseccion2[$l] == $dependencias_padre_tmp[$t])
		             	{   
		             		if ($key)
		             		unset($dependencias_padre_tmp[$t]);
		             		$cont = count($dependencias_padre_tmp);
		             	}
		             }
		           }
		        }
		        
		        $AppUI->task_pred[$key] = array_merge($AppUI->task_pred[$key],$dependencias_padre_tmp);
		 		
		 		
	 		}else{
	 			$AppUI->task_pred[$key] = $dependencias_padre_tmp;
	 		}
	 	}
	 	
        return;
	 }

}



function hereda_sucesoras($rama_sort, $key )
{
	 global $AppUI,$tasks;
	 
	 /*echo "hereda sucesoras - $key <pre>";
	   print_r($rama_sort);
	 echo "</pre>";*/

	 if (count($rama_sort) > 0)
	 {
	 	unset($AppUI->task_suces[$key]);
	 	
	 	$vec_vacio[0] = "";
	 	
	 	$AppUI->task_suces[$key] = $vec_vacio;
	 	
	 	foreach ($rama_sort as $number => $wbsn)
	 	{
	 		
	 		foreach ($wbsn as $id_hija => $nueva_rama)
	 		{
	 			// Traigo las sucesoras de esta tarea y le agrego las de su tarea padre
	 			$sucesoras_hija = $AppUI->tasks_sucesoras[$id_hija];
	 			$sucesoras_padre = $AppUI->tasks_sucesoras[$key];
	 			
	 			if($sucesoras_hija[0] != "" ){
	 			   $AppUI->task_suces[$id_hija] = array_merge($sucesoras_padre, $sucesoras_hija);
	 			}else{
	 			   $AppUI->task_suces[$id_hija] = $sucesoras_padre;
	 			}
	 			
	 			$nueva_rama = $rama_sort[$number][$id_hija];
	 			
	 			hereda_sucesoras($nueva_rama, $id_hija); 
	 		}
	 		
	 	}
	 	
	 }else{
	 	return;
	 }
	 
	 
}


?>