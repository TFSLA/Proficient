<?php 
function update_duration(&$sd, &$ed, $duration_type, &$msg){
	global $AppUI;
	$df = $AppUI->getPref('SHDATEFORMAT');		
	$tf = $AppUI->getPref('TIMEFORMAT');
	echo "&nbsp;&nbsp;&nbsp;&nbsp;7 - Task Start Date: ".$sd->format(FMT_TIMESTAMP)."<br>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;8 - Task End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";	
	$duration = $sd->dateDiff($ed, $duration_type);
	echo "&nbsp;&nbsp;&nbsp;&nbsp;9 - Task Start Date: ".$sd->format(FMT_TIMESTAMP)."<br>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;10 - Task End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";	
	$cur_sd = $sd->format(FMT_TIMESTAMP);
	$sd->fitDateToCalendar();
	
	echo "&nbsp;&nbsp;&nbsp;&nbsp;12 - Task Start Date: ".$sd->format(FMT_TIMESTAMP)."<br>";

	if ($cur_sd != $sd->format(FMT_TIMESTAMP)){
		$sd_f = new CDate($cur_sd);
		$msg .= "<!-- cur_sd:".$cur_sd." - sd:".$sd->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskStartAdjust"),$sd_f->format($df." ".$tf),$sd->format($df." ".$tf));
		$msg .= "</li>";		
	}	
	echo "&nbsp;&nbsp;&nbsp;&nbsp;20 - Task End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";	
	$cur_ed = $ed->format(FMT_TIMESTAMP);
	echo "&nbsp;&nbsp;&nbsp;&nbsp;22 - Task End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";	
	$ed->fitDateToCalendar(true);
	echo "&nbsp;&nbsp;&nbsp;&nbsp;24 - Task End Date: ".$ed->format(FMT_TIMESTAMP)."<br>";	


	if ($cur_ed != $ed->format(FMT_TIMESTAMP) ){
		$ed_f = new CDate($cur_ed);
		//$msg .= "La fecha de fin (".$ed_f->format($df." ".$tf).") se ha desplazado al próximo día laborable (".$ed->format($df." ".$tf).").<br>";
		$msg .= "<!-- cur_ed:".$cur_ed." - ed:".$ed->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskEndAdjust"),$ed_f->format($df." ".$tf),$ed->format($df." ".$tf));
		$msg .= "</li>";		
	}
	return "
				window.parent.update_field('task_duration', '$duration');
				";			
}

function update_end_date(&$sd, &$ed, $duration, $duration_type, &$msg){
	global $AppUI;
	$df = $AppUI->getPref('SHDATEFORMAT');	
	$tf = $AppUI->getPref('TIMEFORMAT');
    echo "<br>Control 1<br>";

	$cur_sd = $sd->format(FMT_TIMESTAMP);

	$sd->fitDateToCalendar();
    
	if ($cur_sd != $sd->format(FMT_TIMESTAMP)){
		$sd_f = new CDate($cur_sd);
		$msg .= "<!-- cur_sd:".$cur_sd." - sd:".$sd->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskStartAdjust"),$sd_f->format($df." ".$tf),$sd->format($df." ".$tf));
		//$msg .= "La fecha de inicio (".$sd_f->format($df." ".$tf).") se ha desplazado al próximo día laborable (".$sd->format($df." ".$tf).").<br>";
	}	

	//$ed->setDate($sd->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
	
	   if ($duration_type==24)
	    {
		$ed->setDate($sd->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
		echo  "<br>Fecha de fin inicial: ".$ed->format(FMT_TIMESTAMP);
	    echo "<br>La duracion es: $duration<br>";
		$ed->addDays($duration);
		echo  "<br>Fecha de fin final: ".$ed->format(FMT_TIMESTAMP)."<br>";	
		}
	    else 
	    {
		$ed->addHours($duration);
		}


	$cur_ed = $ed->format(FMT_TIMESTAMP);

	$ed->fitDateToCalendar(true);    

	if ($cur_ed != $ed->format(FMT_TIMESTAMP)){
		$ed_f = new CDate($cur_ed);
		//$msg .= "La fecha de fin (".$ed_f->format($df." ".$tf).") se ha desplazado al próximo día laborable (".$ed->format($df." ".$tf).").<br>";
		$msg .= "<!-- cur_ed:".$cur_ed." - ed:".$ed->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskEndAdjust"),$ed_f->format($df." ".$tf),$ed->format($df." ".$tf));
		$msg .= "</li>";
	}

	// Me aseguro que la fecha de fin no sea menor que la de inicio
	if ($ed < $sd)
	{
	$ed = $sd;
	}

	return "
		window.parent.update_field('task_end_date', '".$ed->format(FMT_TIMESTAMP_DATE)."');
		window.parent.update_field('end_date', '".$ed->format($df)."');
		window.parent.selectTime('end', '".$ed->format('%H')."', '".$ed->format('%M')."');	
		";						
		
}

function update_start_date(&$sd, &$ed, $duration, $duration_type, &$msg){
	global $AppUI;
	$df = $AppUI->getPref('SHDATEFORMAT');	
	$tf = $AppUI->getPref('TIMEFORMAT');

	$cur_ed = $ed->format(FMT_TIMESTAMP);
	$ed->fitDateToCalendar(true);
    

	if ($cur_ed != $ed->format(FMT_TIMESTAMP)){
		$ed_f = new CDate($cur_ed);
		$msg .= "<!-- cur_ed:".$cur_ed." - ed:".$ed->format(FMT_TIMESTAMP)." -->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskEndAdjust"),$ed_f->format($df." ".$tf),$ed->format($df." ".$tf));
		$msg .= "</li>";
		//$msg .= "La fecha de fin (".$ed_f->format($df." ".$tf).") se ha desplazado al próximo día laborable (".$ed->format($df." ".$tf).").<br>";
	}

	//$sd->setDate($ed->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
	$diff =  $duration;
    echo "La diferencia es:".$diff;

	if ($duration_type==24){
        $sd->setDate($ed->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
		$sd->addDays($diff);	
	}
	else 
		$sd->addHours($diff);	

	$cur_sd = $sd->format(FMT_TIMESTAMP);
	$sd->fitDateToCalendar();


	if ($cur_sd != $sd->format(FMT_TIMESTAMP)){

		$sd_f = new CDate($cur_sd);
		//$msg .= "La fecha de inicio (".$sd_f->format($df." ".$tf).") se ha desplazado al próximo día laborable (".$sd->format($df." ".$tf).").<br>";
		$msg .= "<!-- cur_sd:".$cur_sd." - sd:".$sd->format(FMT_TIMESTAMP)."-->";
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskStartAdjust"),$sd_f->format($df." ".$tf),$sd->format($df." ".$tf));
		$msg .= "</li>";
	}			
    
	// Me aseguro que la fecha de fin no sea menor que la de inicio
	if ($ed < $sd)
	{
	$ed = $sd;
	}

	return "
		window.parent.update_field('task_start_date', '".$sd->format(FMT_TIMESTAMP_DATE)."');
		window.parent.update_field('start_date', '".$sd->format($df)."');
		window.parent.selectTime('start', '".$sd->format("%H")."', '".$sd->format("%M")."');	
		";	
}

require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'system' ) );

//error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING );
$hmtl_buffer = ob_get_contents();
ob_clean();

$df = $AppUI->getPref('SHDATEFORMAT');	
$tf = $AppUI->getPref('TIMEFORMAT');
$tsformat = "%Y%m%d%H%M";
import_request_variables("G","");

// detecta cuando es llamada para actualizar (y no cuando se carga la pantalla)
if (isset($sed)){
$start_date = $task_start_date;
$end_date = $task_end_date;
$start_date= new CWorkCalendar(2, $task_project,"",$task_start_date );
$end_date= new CWorkCalendar(2, $task_project,"",$task_end_date );	
$duration =  $task_duration;	
$sed = explode(",",$sed);
$fields = array();
$fields["task_start_date"] = 0;
$fields["task_end_date"] = 1;
$fields["task_duration"] = 2;		

if(count($units)>0){
	$users_assigned = array_keys($units);
}
echo "Accion1: ".$action."<br>";
echo "144 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
echo "145 - Task End Date: ".$end_date->format(FMT_TIMESTAMP)."<br>";
$obj = new CProject();
if (!$obj->load( $task_project )) 
{
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
$calendars = $obj->getActiveCalendars($task_project);
$project_start_date = new CDate($obj->project_start_date);

$cpy = new CCompany();
if (!$cpy->load( $obj->project_company )) 
{
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$cpy->loadHollidays();
$hollidays = $cpy->_hollidays;
$cal_dates = array_keys($calendars);
sort($cal_dates);
$msg="";

if(strlen($min_start_date)==0){
	$min_start_date = $project_start_date->format($tsformat);
	$min_start_date_type = "project";
}

$update_task_dates = false;

// obtengo las tareas que tienen a la actual (o a sus padres) como predecesora


/* todas las tareas dependientes a un array*/
$dep_tasks = explode(",",$dependencies);
/* si tiene una tarea padre y no es la misma tarea */
if($task_parent!=$task_id && $task_parent > 0){
	$objTask = new CTask();
	if($objTask->load($task_parent)){
		/* obtengo las dependencias de esa tarea padre */
		$parent_dep = explode(",",$objTask->getDependencies());
		$dep_tasks = array_merge($dep_tasks, $parent_dep);
	}
	
}


echo "<pre>";	

	
//si hay tareas dependientes entonces hay una fecha de inicio limite para la tarea
for ($i=0;$i<count($dep_tasks);$i++){
	$objTask = new CTask();
	if ($objTask->load($dep_tasks[$i])) 
	{
			
		$dep_end_date = new CDate($objTask->task_end_date);	
		$dep_end_date = $dep_end_date->format($tsformat);
		if ($min_start_date < $dep_end_date){
			$min_start_date = $dep_end_date;
			$min_start_date_type = "task";
		}
	}		
}

// si la fecha de inicio de la tarea es anterior a la fecha de finalizacion de alguna de sus predecesoras
if ($min_start_date > $start_date->format($tsformat)){
	
	/*	Existen varias maneras de adoptar un comportamiento
		1) en principio se podría modificar la fecha de inicio para que 
		se ajuste a la fecha de fin mas tardía de sus predecesoras 
		2) dar opciones y que el usuario decida entre:
			a) Ajustar la fecha de inicio de la tarea 
			b) Dejar las tareas solapadas		
	*/
	$update_task_dates = true;
	echo "$min_start_date > ".$start_date->format($tsformat)."\n";
	$task_start_date = $min_start_date;
	$cur_sd = $start_date->format($df." ".$tf);
	$start_date= new CWorkCalendar(2, $task_project,"",$min_start_date );
	echo "229 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
	$start_date->fitDateToCalendar();		
	echo "231 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
	if ($min_start_date_type == "task"){
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskStartPriorEndDependence"),$cur_sd,$start_date->format($df." ".$tf));
		$msg .= "</li>";
	}else{
		$msg .= "<li>";
		$msg .= sprintf($AppUI->_("taskStartPriorStartProject"),$cur_sd,$start_date->format($df." ".$tf));
		$msg .= "</li>";
	}

	
	$js_string = "/*  */
		window.parent.update_field('task_start_date', '".$start_date->format(FMT_TIMESTAMP_DATE)."');
		window.parent.update_field('start_date', '".$start_date->format($df)."');
		window.parent.selectTime('start', '".$start_date->format("%H")."', '".$start_date->format("%M")."');
		";	
	
	$js_string .= "
		window.parent.update_field('min_start_date', '".$start_date->format($tsformat)."');	
		";	


	$refresh = 0;
	for ($i=0;$i<count($sed) && $refresh == 0;$i++){
		$refresh = $fields[$sed[$i]];
	}
	$refresh = $refresh == 0 ? 2 : $refresh;
	// cuando se vaya a actualizar la duracion pero la fecha de inicio sea mayor o igual a la de fin
	// en su lugar se actualizara la fecha de fin
	if ($refresh==1 && $start_date->format(FMT_TIMESTAMP) >= $end_date->format(FMT_TIMESTAMP))
		$refresh = 2;		
	echo "Auto_Update: $refresh\n";
	//si el ultimo que se modifico fue la fecha de fin, actualizo la duracion
	switch ($refresh){
	case "1":
		//si el ultimo que se modifico fue la fecha de fin, actualizo la duracion
		$duration = $start_date->dateDiff($end_date, $task_duration_type);
		$end_date->fitDateToCalendar(true);		

		$js_string .= "
			window.parent.update_field('task_duration', '$duration');
			";	
		break;
	case "2":
		$end_date = new CWorkCalendar(2, $task_project,"",$start_date->format(FMT_TIMESTAMP) );
		if ($task_duration_type==24)
			$end_date->addDays($duration);
		else 
			$end_date->addHours($duration);					
		
		$js_string .= "
			window.parent.update_field('task_end_date', '".$end_date->format(FMT_TIMESTAMP_DATE)."');
			window.parent.update_field('end_date', '".$end_date->format($df)."');
			window.parent.selectTime('end', '".$end_date->format("%H")."', '".$end_date->format("%M")."');	
			";		
		break;
	}
}

echo "</pre>";	

//si la tarea es dinámica
if ($task_dynamic=="1"){

	$cld_min_start_date="299912312359";
	$cld_max_end_date="000000000000";

	if (strlen($task_id)>0){
		//obtengo todas las tareas hijas
		$children = CTask::getChildren($task_id);

		for($i=0; $i< count($children); $i++){
			$objTask = new CTask();
			if ($objTask->load($children[$i]["task_id"])) 
			{
					
				$cld_start_date = new CDate($objTask->task_start_date);	
				$cld_end_date = new CDate($objTask->task_end_date);	

				if ($cld_min_start_date > $cld_start_date->format($tsformat)){
					$cld_min_start_date = $cld_start_date->format($tsformat);
				}
				if ($cld_max_end_date < $cld_end_date->format($tsformat)){
					$cld_max_end_date = $cld_end_date->format($tsformat);
				}				
				
			}			
		}
	}
	
	if ($cld_min_start_date!="299912312359")
		$start_date->setDate($cld_min_start_date."00", DATE_FORMAT_TIMESTAMP);
		echo "324 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
	if ($cld_max_end_date!="000000000000")
		$end_date->setDate($cld_max_end_date."00", DATE_FORMAT_TIMESTAMP);

	    /* --------------------------------------------------------------------
		si la tarea tiene una predecesora  con fecha de fin posterior a la 
		minima fecha de inicio de las tareas hijas desplazo la fecha de inicio 
		de la tarea a la mayor de estas (fecha de fin de la predecesora).
		----------------------------------------------------------------------*/

	if ($min_start_date > $cld_min_start_date){
		
		if ($min_start_date_type == "task"){
			$msg .= "<li>".$AppUI->_("taskStartChildrenPriorDependence")."</li>";
		}else{
			$msg .=  "<li>".$AppUI->_("taskStartChildrenPriorProject")."</li>";
		}		

		$sd2 = $start_date;
		//cambio la fecha de inicio
		$start_date->setDate($min_start_date."00", DATE_FORMAT_TIMESTAMP);
		//calculo la diferencia con la fecha de inicio anterior
		$diff = $sd2->dateDiff($start_date, $task_duration_type);
		//incremento a la fecha de fin la misma diferencia
		if ($task_duration_type == 24)
			$end_date->addDays($diff);
		else 
			$end_date->addHours($diff);
	}
	
	echo "350 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
	if 	($start_date->format(FMT_TIMESTAMP) > $end_date->format(FMT_TIMESTAMP)){
		$last_modif = isset($sed[0]) ? $sed[0] : null;
		switch ($last_modif){
			case "task_start_date": 
				$js_string .= update_end_date($start_date, $end_date, 
						$task_duration, $task_duration_type, $msg);
				
				break;
			case "task_end_date":
				$js_string .= update_start_date($start_date, $end_date, 
						$task_duration, $task_duration_type, $msg);
				break;
		}
				
	}else{
		$js_string .= update_duration($start_date, $end_date, 
						$task_duration_type, $msg);
	}
	


}else{
	
	/*  si se deberia actualizar la duracion pero se ha modificado la fecha de inicio de la tarea
		y ahora es posterior a la de fin, debo calcular la nueva fecha de fin tomando la duración como fija
	*/
	if 	($action=="update_duration" && $start_date->format(FMT_TIMESTAMP) > $end_date->format(FMT_TIMESTAMP)){
		$action="update_end_date";
	}
	echo "377 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
	echo "378 - Task End Date: ".$end_date->format(FMT_TIMESTAMP)."<br>"; 

	if ($action == "update_duration" && $task_type == 2){
		$last_modif = isset($sed[0]) ? $sed[0] : null;
		switch ($last_modif){
			case "task_start_date": $action = "update_end_date";break;
			case "task_end_date": $action = "update_start_date";break;
			default: $action = "update_end_date";}
	}
    echo "Accion2: ".$action."<br>";
	switch ($action){
	case "update_duration":
        
		if ($task_type == 2){
			$action = "changed_resources";
		}else{
			echo "Task duration 1:<br>";
			$js_string .= update_duration($start_date,$end_date,$task_duration_type,$msg);	
		}
	echo "381 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
		break;
	
	case "update_end_date":
        echo "Task duration 2:<br>";
		$js_string .= update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg);	
	echo "386 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
		break;
		
	case "update_start_date":	
	echo "390 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";

		$js_string .= update_start_date($start_date,$end_date,$duration,$task_duration_type,$msg);

	echo "391 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
		
		//verifico que la fecha de inicio no sea superior a la minima x las predecesoras	
		if ($min_start_date > $start_date->format($tsformat)){
			// desplazo la fecha de inicio al fin mas tardio de las tareas predecesoras
			$cur_sd = $start_date->format($df." ".$tf);

			$start_date->setDate($min_start_date."00", DATE_FORMAT_TIMESTAMP);
			echo "399 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
			$start_date->fitDateToCalendar();
			echo "401 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
			//$msg = "La fecha de inicio (".$cur_sd.") es anterior a la finalizacion de una de las tareas precedentes, por ello se ha desplazado a la fecha de fianlizacion de la misma (".$start_date->format($df).").<br>";
			if ($min_start_date_type == "task"){
				$msg = "<li>";
				$msg .= sprintf($AppUI->_("taskStartPriorEndDependence"),$cur_sd,$start_date->format($df." ".$tf));
				$msg .= "</li>";
			}else{
				$msg = "<li>";
				$msg .= sprintf($AppUI->_("taskStartPriorStartProject"),$cur_sd,$start_date->format($df." ".$tf));
				$msg .= "</li>";
			}
			
			$js_string = "
				window.parent.update_field('task_start_date', '".$start_date->format(FMT_TIMESTAMP_DATE)."');
				window.parent.update_field('start_date', '".$start_date->format($df)."');
				window.parent.selectTime('start', '".$start_date->format("%H")."', '".$start_date->format("%M")."');
				";

			//busco cual es el ultimo campo que se modifico 		
			$refresh = 0;
			for ($i=0;$i<count($sed) && $refresh == 0;$i++){
				$refresh = $fields[$sed[$i]];
			}
			$refresh = $refresh == 0 ? 2 : $refresh;	
			// cuando se vaya a actualizar la duracion pero la fecha de inicio >= fin 
			// en su lugar se actualizara la fecha de fin
			if ($refresh==1 && 
				($start_date->format(FMT_TIMESTAMP) >= $end_date->format(FMT_TIMESTAMP)
				//o si el tipo de tarea es Duracion Fija
				|| $task_type == 2))
				$refresh = 2;

			switch ($refresh){
			case "1":
				// si se modifico la fecha de fin debo actualizar la duracion
				$js_string .= update_duration($start_date,$end_date,$task_duration_type,$msg);	
				echo "433 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";
				break;
			case "2":
				// si se modifico la duracion debo actualizar la fecha de fin
				$js_string .= update_end_date($start_date,$end_date,$duration,$task_duration_type,$msg);	
				echo "438 - Task Start Date: ".$start_date->format(FMT_TIMESTAMP)."<br>";

				break;
			}						
		}

		break;
	}
	
}


if ($task_start_date != $start_date->format($tsformat)){
	$js_string .= "
		window.parent.update_field('task_start_date', '".$start_date->format(FMT_TIMESTAMP_DATE)."');
		window.parent.update_field('start_date', '".$start_date->format($df)."');
		window.parent.selectTime('start', '".$start_date->format("%H")."', '".$start_date->format("%M")."');
		";
}
if ($task_end_date != $end_date->format($tsformat)){
	$js_string .= "
		window.parent.update_field('task_end_date', '".$end_date->format(FMT_TIMESTAMP_DATE)."');
		window.parent.update_field('end_date', '".$end_date->format($df)."');
		window.parent.selectTime('end', '".$end_date->format("%H")."', '".$start_date->format("%M")."');
		";
}


/* obtengo la lista de las posibles tareas dependientes y posibles tareas padres */
$possible_dependences = CTask::getListPosibleDependences($task_project, $task_parent, $task_id);
$possible_parents = CTask::getListPosibleParents($task_project, $task_id);
echo "CTask::getListPosibleDependences($task_project, $task_parent, $task_id) = ";
var_dump($possible_dependences);


$js_string .="
	var f = window.parent.getEditForm();
	var all_tasks = f.all_tasks;
	var task_dependencies = f.task_dependencies;
	var task_parent = f.task_parent;
	var dependences = '$dependencies';
	 /* quito todas las tareas posibles anteriores */    
	for (var q=all_tasks.options.length; q>=0; q--) 
	{
	  	all_tasks.options[q]=null;
	}
	/* quito todas las dependecias anteriores */
	for (var q=task_dependencies.options.length; q>=0; q--) 
	{
	  	task_dependencies.options[q]=null;
	}
	/* quito todas las posibles tareas padres */
	for (var q=task_parent.options.length; q>=0; q--) 
	{
	  	task_parent.options[q]=null;
	}

	all_tasks.options[all_tasks.length] = new Option( '".$AppUI->_('None')."','$task_id' );
	task_parent.options[task_parent.length] = new Option( '".$AppUI->_('None')."','$task_id' );
	var myEle;
";

/* Agrego las posibles tareas dependientes*/
if (count($possible_dependences)){
	foreach ($possible_dependences as $dep_id=>$dep_name) {
		//agrego cada posible tarea
		$js_string .="
    all_tasks.options[all_tasks.length] = new Option( '$dep_name', '$dep_id' );
    if ( dependences.indexOf('$dep_id' ) > -1 )
    {     
		task_dependencies.options[task_dependencies.length] = new Option( '$dep_name', '$dep_id' ); 
    }
		";
	}
	
}

/*Agrego las posibles tareas padre*/
if (count($possible_parents)){
	foreach ($possible_parents as $parent_id=>$parent_name) {
		//agrego cada posible tarea
		$js_string .="
	myEle = new Option( '$parent_name', '$parent_id' );
    task_parent.options[task_parent.length] = myEle;
    if ( myEle.value == $task_parent )
    {     
		myEle.selected = true;
    }
		";
	}
	
}

/* Obtengo las tareas que estaban agregadas como dependientes y ahora no lo pueden ser */
$pos_dep_id = array_keys($possible_dependences);
$del_dep = array_diff(explode(",",$dependencies), $pos_dep_id);
$del_dep_name = array();
for($i=0; $i< count($del_dep); $i++){
	$tmpTask = new CTask();
	if ($tmpTask->load($del_dep[$i]))
		$del_dep_name[]= $tmpTask->task_name;
}
if(count($del_dep_name)>0){
	$msg .= "<li>";
	$msg .= sprintf($AppUI->_("taskNoAllowedDependences"), implode(", ", $del_dep_name ));
	$msg .= "</li>";
}

if($update_task_dates || $msg != ""){
	$msg ="<ul>".$msg."</ul><br><center><a href=javascript:hide_message();>ok</a></center>";
	$js_string = '
		
		window.parent.show_message(\'alert\', \''.$AppUI->_("Warning").'\', "'.$msg.'");
		window.parent.update_field(\'form_checked\', \'1\');'.$js_string;	
}else{
	$js_string .= "
		window.parent.update_field('form_checked', '1');
		setTimeout('window.parent.hide_message()',1000);"; 
}



/*
Recalculo en función de las asignaciones de usuarios a la tarea

*/
/*
for($i=0; $i < count($users_assigned); $i++){
	$user = $users_assigned[$i];
	$user_calendar = new CWorkCalendar(3, $user, $task_project,$start_date->format(FMT_TIMESTAMP));

}
*/
//if ($action=="changed_resources"){


$changed_motives = array(
"changed_resources"=>1,
"changed_units"=>2,
"changed_duration"=>3,
"update_duration"=>3,
"changed_work"=>4
);

$changed_id = $changed_motives[$action] ? $changed_motives[$action] : 0 ;
if (!$changed_id){
	$changed_id = $sed[0] == "task_duration" ? 3 : 0;
}
if (count($units) > 0 && $changed_id > 0){
	$objTask = new CTask();
	$data = $_GET;
	$data["task_duration"] = $duration;
	$data["task_duration_type"] = $task_duration_type ;
	$data["task_start_date"] = $start_date->format(FMT_TIMESTAMP);
	$data["task_end_date"] = $end_date->format(FMT_TIMESTAMP);
	$objTask->bind($data);
	$objTask->_assigned_users = $data["units"];
	
	echo "<pre>";
	var_dump($objTask);
	echo "</pre>";
	$objTask->updateSchedule($changed_id);
	
	if ($data["task_duration"] != $objTask->task_duration)
		$js_string .= "
			window.parent.update_field('task_duration', '".$objTask->task_duration."');
			";	
	
	if ($objTask->task_start_date !=  $data["task_start_date"]){
		$start_date->setDate($objTask->task_start_date, DATE_FORMAT_TIMESTAMP);
		$js_string .= "
			window.parent.update_field('task_start_date', '".$start_date->format(FMT_TIMESTAMP_DATE)."');
			window.parent.update_field('start_date', '".$start_date->format($df)."');
			window.parent.selectTime('start', '".$start_date->format("%H")."', '".$start_date->format("%M")."');
			";		
	}
	if ($objTask->task_end_date !=  $data["task_end_date"]){
		$end_date->setDate($objTask->task_end_date, DATE_FORMAT_TIMESTAMP);
		$js_string .= "
			window.parent.update_field('task_end_date', '".$end_date->format(FMT_TIMESTAMP_DATE)."');
			window.parent.update_field('end_date', '".$end_date->format($df)."');
			window.parent.selectTime('end', '".$end_date->format("%H")."', '".$end_date->format("%M")."');
			";		
	}	
	
	if ($data["task_work"] != $objTask->task_work)	
		$js_string .= "
			window.parent.update_field('task_work', '".$objTask->task_work."');
			";	
	for ($i=0; $i<count($users_assigned); $i++){
		$user = $users_assigned[$i];
		if ($data["units"][$user] != $objTask->_assigned_users[$user]){
			$js_string .= "
			window.parent.update_units('".$user."', '".$objTask->_assigned_users[$user]."');
			";			
		}
	
	}
	if ($data["task_work"] != $objTask->task_work)	
		$js_string .= "
			window.parent.update_field('task_work', '".$objTask->task_work."');
			";			
		
	//$objTask->calculateDuration();
	echo "<br>".$objTask->task_start_date." - ".$objTask->task_end_date;
}


unset($cpy);
unset($obj);		

echo "<pre>
<!-- http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']} -->

CALENDARIOS
***********
accion: $action

Inicio: ";
echo is_a($start_date, "CDate") ? $start_date->format(FMT_DATETIME_MYSQL):$start_date; 
echo "
Fin: ";
echo is_a($end_date, "CDate") ? $end_date->format(FMT_DATETIME_MYSQL):$end_date; 
echo "
Duracion: $duration ".($task_duration_type == 1 ? "horas" : "dias");
echo "\n";
var_dump($_GET);
echo "</pre>";		
		
}else{
	$js_string .= "setTimeout('window.parent.hide_message()',1000);";
}


$debug_text = ob_get_contents();
ob_clean();
echo $hmtl_buffer;
if (@$_GET["debuginteraction"] == "123"){
	echo $debug_text;
}
	?>
<script language="javascript">
		
function goback(){
<?php echo $js_string; ?>
}


window.setTimeout("goback()", 1000);
</script>