<?php /* TASKS $Id: addedit.php,v 1.4 2009-06-23 01:37:45 ctobares Exp $ */
/**
* Tasks :: Add/Edit
*/
global $dynamic_constraints, $task_constraints;

require_once( "./modules/tasks/functions.php" );

$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$task_parent = intval( dPgetParam( $_GET, "task_parent", 0 ) );
$debug = intval( dPgetParam( $_GET, "debug", 0 ) );
$debug_js = intval(dPgetParam( $_GET, "debug_js", 0 ) );

/*----   Titulo y barra de link's -----*/
$ttl = $task_id > 0 ? "Edit Task" : "Add Task";
$titleBlock = new CTitleBlock( $ttl, 'tasks.gif', $m, "projects.index" );
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
if ( $canReadProject ) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$task_project", "view this project" );
}
if ($task_id > 0)
  $titleBlock->addCrumb( "?m=tasks&a=view&task_id=$task_id", "view this task" );
$titleBlock->show();

/*--------------------------------------*/


$obj = new CTask();

// Si va a editar se fija si el id es válido
if (!$obj->load( $task_id ) && $task_id > 0)
{
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

/*-------------------  Datos del proyecto  --------------------------------------*/

$task_project = intval( $obj->task_project );

if (!$task_project)
{
	$task_project = dPgetParam( $_GET, 'task_project', 0 );
	if (!$task_project)
	{
		$AppUI->setMsg( "badTaskProject", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

$project = new CProject();
$project->load($task_project, false);
$canManageRoles=$project->canManageRoles();


/*--------------------------------------------------------------------------------*/


if($debug){
echo "1 - Projecto:<pre>";
print_r($project);
echo "</pre>";
}

// Si es un atarea nueva limpio el vector de memoria de tareas con id 0
if (!$task_id ) {
unset($AppUI->tasks[0]);
unset($AppUI->tasks_predecesoras[0]);
unset($AppUI->tasks_sucesoras[0]);
unset($AppUI->task_pred[0]);
unset($AppUI->task_suces[0]);
}

/*-----------------  Permisos  -----------------*/
if ( $task_id ) {
    $perms_tasks = CTask::getTaskAccesses($task_id);
	$canEdit = $perms_tasks["edit"];
	$canEditDetails = $perms_tasks["detail"] == PERM_EDIT;
	$canEditEcValues = $perms_tasks["values"] == PERM_EDIT;
} else {
	$canEdit = $project->canAddTasks();
	$canEditDetails = $canEdit;
	$canEditEcValues = $canEdit;

}


if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
/*-----------------------------------------------*/


/*---------- Valores por defecto para tareas ----------*/

    //ingreso fechas manual
    $objManualDate = new CDate();

    $defaul_task_access = 3;
    $task_access = Array ( "2"=> $AppUI->_("Project Wide"), "3"=> $AppUI->_("Only Assigned"));

	if($task_id == 0){

        // Recorro el vector, y me fijo cual es la FTeI mas chica

        if(count($project_tasks)>0)
		{
			$FTeI = "99999999999999";

			foreach($project_tasks as $key=> $tareas)
			{
				if( $tareas[FTeI] < $FTeI )
				{
				 $FTeI = $tareas[FTeI];
				}
			}

			$start_date_tmp = $FTeI;
		}else{

		$FTeI = $project->project_start_date;
		$start_date_tmp = substr($FTeI,0,4)."-".substr($FTeI,5,2)."-".substr($FTeI,8,2)." 09:00:00";

		}


		$obj->task_start_date = $start_date_tmp;

		$obj->task_end_date = $obj->task_start_date;
		$obj->task_duration_type = 24;

		if ($_GET[task_parent]!="")
		{
		$task_before = $_GET[task_parent];
		}

		// Todavía no tiene asignados
		$assigned = array();

	}
	else{
	    $assigned = $obj->getAssignedUsers();
	}


    $task_parent = isset( $obj->task_parent ) ? $obj->task_parent : $task_parent;

	// si la tarea tiene hijos es din?ica obligatoriamente
	$is_dynamic_task = $obj->getNumberOfChildrens();

	if ($is_dynamic_task){
		foreach ($task_constraints as $cid => $cname) {
			if (! (in_array($cid, explode(",", $dynamic_constraints)))){
				unset ($task_constraints[$cid]);
			}
		}
	}


if($debug){
echo "2 - Tarea:<pre>";
print_r($obj);
echo "</pre>";
}

/*--------------------- Tareas del proyecto -------------------------*/

$sql="
	SELECT *
	FROM tasks
	WHERE task_project = $task_project
	GROUP BY task_parent ,task_wbs_level, task_wbs_number
    ORDER BY task_wbs_level,task_wbs_number
";

$res = db_exec( $sql );

$strJs_ao =  "var tasksP = new Array();\n";

while ($row = db_fetch_array( $res )) {

	//$projTasks[$row[task_id]] = wbs($row)." - ".$row[task_name];

	if($row[task_id] == $task_id && $task_id !=0)
	{
	 $current_wbs = wbs($row);
	}

}

$projTasks = ordena_tasks(0,0);


if(count($projTasks)>0){
	foreach($projTasks as $key=>$val)
	{
	   $tpJs = explode(" - ", $val);
	   $wbs_t = explode(".",$tpJs[0]);

	   $wbJsF = implode("",$wbs_t);

	   // Traigo el padre de la tarea y tambien lo guardo en el vector
	   $query_parent = "SELECT task_parent FROM tasks WHERE task_id = '$key' ";
	   $parent_sql = db_loadColumn($query_parent);

	   $parentId = $parent_sql[0];
	   $val_task = ereg_replace('\"','"',$val);
	   $val_task = ereg_replace('"','\"',$val);
	   $val_task = ereg_replace('&quot;','\"',$val);
	   //$val_task  = ereg_replace("\"","&qu",$val);

	   $strJs_ao .= "tasksP[tasksP.length] = new Array($key, \"".$wbJsF."\",\"".$val_task."\",$parentId);\n";

	}
}

//echo " strJs_ao : <pre>"; print_r( $strJs_ao ); echo "</pre>";

$possible_parents = $obj->getListPosibleParents($task_project);

if(count($projTasks)>0){
  foreach($projTasks as $key=>$val)
  {
  	 if (count($possible_parents)>0)
  	 {
		 if (array_key_exists($key, $possible_parents))
		 {
			$pos_parents[$key] = checkpost($possible_parents[$key]);
		 }
  	 }
  }
}

$possible_parents = $pos_parents;

$possible_parents = arrayMerge( array(  "0"  => $AppUI->_('None')),$possible_parents );

if($task_id!=0)
{
unset($possible_parents[$task_id]);
}

// Posibles dependencias //
$possible_dependences = $obj->getListPosibleDependences($task_project, ($task_parent>0?$task_parent:NULL));
unset($possible_dependences[$obj->task_parent]);

if(count($projTasks)>0){
  foreach($projTasks as $key=>$val)
  {
  	 if (count($possible_dependences)>0)
  	 {
		 if (array_key_exists($key, $possible_dependences))
		 {
			$pos_dependences[$key] = checkpost($possible_dependences[$key]);
		 }
  	 }
  }
}

$possible_dependences = $pos_dependences;

$possible_dependences = arrayMerge( array( ($obj->task_id ? $obj->task_id :"0") => $AppUI->_('None')), $possible_dependences );


	// Dependencias actuales //
	$sql = "
	SELECT t.task_id, t.task_name
	FROM tasks t, task_dependencies td
	WHERE td.dependencies_task_id = $task_id
	AND t.task_id = td.dependencies_req_task_id
	";

	$taskDep = db_loadHashList( $sql );

	if(count($taskDep)>0)
	{
		foreach($taskDep as $key_dep => $val_dep )
			{
			   $sql_dep = "SELECT * FROM tasks WHERE task_id = '$key_dep' ";
			   $res_dep = db_exec( $sql_dep );
			   $row_dep = db_fetch_array( $res_dep );

			   $wbs = wbs($row_dep);
			   $pos_par[$key_dep] = $wbs." - ".checkpost($row_dep[task_name]);

			   unset($possible_dependences[$key_dep]);
			}

		$taskDep = $pos_par;
	}

if($task_id == "0" || $obj->task_wbs_level == "0" )
{
$sql="
	SELECT *
	FROM tasks
	WHERE task_project = $task_project
    AND task_wbs_level = '0'
	ORDER BY task_wbs_level,task_wbs_number,task_parent
";

$res = db_exec( $sql );

while ($row = db_fetch_array( $res )) {

	if($row[task_id] != $task_id)
    {
	$projT[$row[task_id]] = wbs($row)." - ".checkpost($row[task_name]);
	}

	if($row[task_id] == $task_id && $task_id !=0)
	{
	$current_wbs = wbs($row);
	}

}


$projTasks = $projT;

if($task_id != 0)
{
 //$projTasks = arrayMerge( array( $task_id => $current_wbs." - ".$obj->task_name), $projTasks);
}

$projTasks = arrayMerge( array( "0" => $AppUI->_('None')), $projTasks);

}else{
	$sql="
	SELECT *
	FROM tasks
	WHERE task_project = '$task_project'
            AND task_parent = '".$obj->task_parent."'
            OR task_id = '".$obj->task_parent."'
	ORDER BY task_wbs_level,task_wbs_number,task_parent
";

//echo "<pre>$sql</pre>";
$res = db_exec( $sql );

while ($row = db_fetch_array( $res )) {

	//echo "if(".$row['task_id']." != ".$task_id.")<br>";
	if($row['task_id'] != $task_id)
            {
            $task_name = ereg_replace('"','&quot;',$row['task_name']);
	$projT[$row[task_id]] = wbs($row)." - ".$task_name;
	}

	if($row['task_id'] == $task_id && $task_id !=0)
	{
	$current_wbs = wbs($row);
	}

}

$projTasks = $projT;

}

if($debug){
echo "3 - Tareas del proyecto:<pre>";
print_r($projTasks);
echo "</pre>";
}


/*-------------------------------------------------------------------*/


/*-------------------------- Usuarios --------------------------------*/

	$sql = "
	SELECT u.user_id, CONCAT_WS(' ',user_first_name,user_last_name)
	FROM users u inner join project_roles pr on u.user_id = pr.user_id
	WHERE pr.project_id = $task_project and pr.role_id=2 and u.user_type <>5
	ORDER BY user_first_name, user_last_name
	";

	$users = db_loadHashList( $sql );
	//$users_owner = db_loadHashList( $sql );

	if ($canManageRoles){

		/*$sql = "
		SELECT u.user_id, CONCAT_WS(' ',user_first_name,user_last_name)
		FROM users u left join project_roles pr
			on u.user_id = pr.user_id and pr.project_id=$task_project and pr.role_id=2
		WHERE pr.user_id is null and u.user_type <>5
		ORDER BY user_first_name, user_last_name
		";
        $users_not_added = db_loadHashList( $sql );

		$users_owner = arrayMerge($users, $users_not_added);*/

		$sql = "
		SELECT u.user_id, CONCAT_WS(' ',user_first_name,user_last_name)
		FROM users u
		left join project_owners pr on u.user_id = pr.project_owner and pr.project_id='$task_project'
		left join projects p on u.user_id = p.project_owner and p.project_id='$task_project'
		WHERE (pr.project_owner is not null and u.user_type <>5 ) OR
		u.user_id='$AppUI->user_id' OR
		(p.project_owner is not null and u.user_type <>5 )
		ORDER BY user_first_name, user_last_name
		";
		$users_not_added = db_loadHashList( $sql );

		$users = arrayMerge($users, $users_not_added);
	}

	if (count($users)>0)
	{
		asort($users);
	}

	$sql = "
	SELECT u.user_id, u.user_cost_per_hour
	FROM users u inner join project_roles pr on u.user_id = pr.user_id
	WHERE pr.project_id = $task_project and pr.role_id=2 and u.user_type <>5
	ORDER BY user_first_name, user_last_name
	";
	$users_cost = db_loadHashList( $sql );

	if ($canManageRoles){
	$sql = "
	SELECT u.user_id, u.user_cost_per_hour
	FROM users u
	left join project_owners pr on u.user_id = pr.project_owner and pr.project_id='$task_project'
	left join projects p on u.user_id = p.project_owner and p.project_id='$task_project'
	WHERE (pr.project_owner is not null and u.user_type <>5 ) OR
	u.user_id='$AppUI->user_id' OR
	(p.project_owner is not null and u.user_type <>5 )
	ORDER BY user_first_name, user_last_name
	";

	$users_not_added = db_loadHashList( $sql );

	$users_cost = arrayMerge($users_cost, $users_not_added);
    }


     if (($task_parent == "")&&($_POST[task_before] == ""))
				{
					if($task_id== "0"){
					$task_before = end(array_keys($projTasks));
					}
					else{
					    $cant = count($projTasks);
						$pos = 0;

					    foreach($projTasks as $key=> $pTasks)
						{
						  if(($task_id > $key)&&($key > 0))
							{
							 $task_before = $key;
							}
						}
					}
				}

			  if (($task_parent != $task_id )&&($task_id!= 0))
			  {
                $query = "SELECT task_wbs_level FROM tasks WHERE task_id = '$task_parent' ";
                $sql = mysql_query($query);
                $wbs_level_parent = mysql_result($sql,0);

				$query = "SELECT task_wbs_number FROM tasks WHERE task_id = '$task_id' ";
                $sql = mysql_query($query);
                $wbs_number_actual = mysql_result($sql,0);

				$level = $wbs_level_parent + 1;
				$query = "SELECT task_id FROM tasks WHERE task_parent = '$task_parent' AND task_wbs_level='$level' AND task_wbs_number < '$wbs_number_actual' order by task_wbs_number desc";
				$sql = mysql_query($query);
                $data = mysql_fetch_array($sql);

				  if($data =="")
				  {
					$task_before = $task_parent;
				  }
				  else
				  {
					$task_before = $data[0];
				  }

			  }

			  if ($task_parent == $task_id && $task_id != 0 )
			  {
                $query = "SELECT task_wbs_level FROM tasks WHERE task_id = '$task_parent' ";
                $sql = mysql_query($query);

                $wbs_level_parent = mysql_result($sql,0);

				$query = "SELECT task_wbs_number FROM tasks WHERE task_id = '$task_id' ";
                $sql = mysql_query($query);
                $wbs_number_actual = mysql_result($sql,0);

				$level = $wbs_level_parent;
				$query = "SELECT task_id FROM tasks WHERE task_project = '$task_project' AND task_wbs_level='$level' AND task_wbs_number < '$wbs_number_actual - 1' order by task_wbs_number desc";
				$sql = mysql_query($query);
                $data = mysql_fetch_array($sql);

				$task_before = $data[0];

			  }

/*--------------------------------------------------------------------*/

?>

<script language="javascript">

   <?php
    //ingreso fecha manual
    echo $objManualDate->buildManualDateValidationJS();

    $start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : new CDate();
    $end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : new CDate();
   ?>


   function task (id,name,project,start_date,end_date, duration, duration_type, work,min_start_date, max_end_date, is_dynamic,is_milestone, task_parent, type, effort_driven,constraint_type, constraint_date){

		this.id              = id;
		this.name			 = name;
		this.project		 = project;
		this.start_date		 = start_date;
		this.end_date		 = end_date;
		this.duration		 = duration;
		this.duration_type	 = duration_type;
		this.work			 = work;
		this.min_start_date	 = min_start_date;
		this.max_end_date	 = max_end_date;
		this.is_dynamic		 = is_dynamic;
		this.is_milestone	 = is_milestone;
		this.task_parent	 = task_parent;
		this.dependences	 = new Array();
		this.resources		 = new Array();
		this.type			 = type;
		this.effort_driven	 = effort_driven;
		this.last_schedule_changes = new Array();
		this.constraint_type = constraint_type;
		this.constraint_date = constraint_date;

	  this.addDependence =
		function (id, name) {
			this.dependences[id] = name;
			/* hacer update dynamics */
		}

	   this.delDependence =
		function (id) {
			this.dependences.splice(id, 1);
			/* hacer update dynamics */
		}

	   this.addResource =
		function (id, name, units, cost) {
			this.resources[id] = new resource(id,name,units, cost);
			/* hacer update dynamics */
		}

	   this.editResource =
		function (id, units){
			if (this.resources[id]) return;
			if (this.resources[id].units == units) return;
			this.resources[id].units = units;
			/* hacer update dynamics */
		}

	   this.delResource =
		function (id){
			this.resources.splice(id, 1);
			/* hacer update dynamics */
		}

	   this.changeDuration	=
		function (duration){
			if (this.duration == duration) return;
			this.duration = duration;
			/* hacer update dynamics */
		}

	   this.changeDurationType =
		function (duration_type){
			if (this.duration_type == duration_type) return;
			this.duration_type = duration_type;
			/* hacer update dynamics */
		}

	   this.changeStartDate =
		function (start_date){
			if (this.start_date == start_date) return;
			this.start_date = start_date;
			/* hacer update dynamics */
		}

	   this.changeEndDate =
		function (end_date){
			if (this.end_date == end_date) return;
			this.end_date = end_date;
			/* hacer update dynamics */
		}

	   this.changeWork =
		function (work){
			if (this.work == work) return;
			this.work = work;
			/* hacer update dynamics */
		}

	   this.changeType =
		function (type){
			if (this.type == type) return;
			this.type = type;
			/* hacer update dynamics */
		}

	   this.changeEffortDriven =
		function (effort_driven){
			if (this.effort_driven == effort_driven) return;
			this.effort_driven = effort_driven;
			/* hacer update dynamics */
		}

}

   function resource(id, name, units){
		this.id = id;
		this.name = name;
		this.units = units;
   }


   var curtask =
	new task (
		'<?php	echo $obj->task_id;?>',
		'<?php	echo checkpost($obj->task_name);?>',
		'<?php	echo $obj->task_project;?>',
		'<?php  echo $start_date ? $start_date->format(FMT_TIMESTAMP):"";?>',
		'<?php	echo $end_date ? $end_date->format(FMT_TIMESTAMP):"";?>',
		<?php	echo $obj->task_duration ? "$obj->task_duration" : "0";?>,
		<?php	echo $obj->task_duration_type ? "$obj->task_duration_type":"0";?>,
		<?php	echo $obj->task_work?"$obj->task_work":"0";?>,
		'<?php	echo ""/*min_start_date*/;?>',
		'<?php	echo ""/*max_end_date*/;?>',
		<?php	echo ($obj->task_dynamic!="0" ? "true":"false" );?>,
		'<?php	echo $obj->task_parent;?>',
		'<?php	echo $obj->task_type;?>',
		<?php	echo ($obj->task_effort_driven ? "true":"false" );?>,
		'<?php	echo $obj->task_constraint_type;?>',
		'<?php	echo ($constraint_date ? $constraint_date->format( FMT_TIMESTAMP ): '');?>');

	    <?php
		while(list($resource_id, $resource_row) = each($assigned)){?>
		curtask.resources["<?php echo $resource_id?>"] =
			new resource (<?php echo $resource_id;?>,'<?php echo $users[$resource_id];?>',<?php echo $resource_row["user_units"];?>);
		<?php }
		while(list($dep_id, $dep_name) = each($taskDep)){?>
		curtask.dependences["<?php echo $dep_id?>"] = "<?php echo $dep_name?>";
		<?php
		}
		?>

		var assigned = new Array();
		var units = new Array();
		var assignedid = new Array();
		var users = new Array();
		var usersid = new Array();
		var cost = new Array();


		<?php
		$assigned_uid = array_keys($assigned);
		for ($i = 0; $i < count($assigned); $i++){
			echo "units[".$assigned_uid[$i]."]=".$assigned[$assigned_uid[$i]]["user_units"].";";
			echo "assignedid[$i]=".$assigned_uid[$i].";";
			echo "cost[".$assigned_uid[$i]."]=".$users_cost[$assigned_uid[$i]].";";
		}

		$users_ids = array_keys($users);
		for ($i = 0; $i < count($users); $i++){
			echo "users[".$users_ids[$i]."]='".$users[$users_ids[$i]]."';";
			echo "usersid[$i]=".$users_ids[$i].";";
			echo "cost[".$users_ids[$i]."]='".$users_cost[$users_ids[$i]]."';";
		}

?>

        var calendarField = '';
        var calWin = null;
        var selected_contacts_id = "<?= $obj->task_contacts; ?>";
		<? if ($canManageRoles){
			echo "var users_not_added = new Array();\n";
			if ( count($users_not_added) > 0 ){
				foreach ($users_not_added as $uid => $uname)
					echo "users_not_added[$uid]= '$uname';\n";}

			echo "var hollidays = new Array();\n";
			if ( count($holliday_list) > 0 ){
				foreach ($holliday_list as $holliday )
					echo "hollidays[$holliday]= '1';\n";}
		}?>


   var sed_modif = new Array();

   function show_tab(name){
   		document.getElementById('tabgeneral').style.display='none';
   		document.getElementById('tabdependences').style.display='none';

   		document.getElementById('tab' + name).style.display='';
   }

   function execute_remote_script(action){
	  //  document.editFrm.btnFuseAction.disabled = true;

	    progress_msg('mostrar');

		var f = document.editFrm;
		var fme = document.getElementById("fmeSEDCalcs");
		var dl = f.task_dependencies.length -1;
		var dependencies = new Array();
		var url = 'index.php?m=public&a=task_duration_calc&suppressHeaders=1&dialog=1&task_project=' + f.task_project.value;

		if ( fme ){
			url += '&action='+action;
			url += '&task_start_date='+f.task_start_date.value;
			url += f.start_hour.options[f.start_hour.selectedIndex].value;
			url += f.start_minute.options[f.start_minute.selectedIndex].value;
			url += '&task_end_date='+f.task_end_date.value;
			url += f.end_hour.options[f.end_hour.selectedIndex].value;
			url += f.end_minute.options[f.end_minute.selectedIndex].value;
			url += '&task_duration='+f.task_duration.value;
			url += '&task_duration_type='+f.task_duration_type.options[f.task_duration_type.selectedIndex].value;
			url += '&min_start_date='+f.min_start_date.value;
			url += '&max_end_date='+f.max_end_date.value;
			url += '&task_dynamic='+(f.task_dynamic.checked?f.task_dynamic.value:0);
			url += '&task_id=<?php echo $task_id?>';
			url += '&task_constraint_type =';
            url += f.task_constraint_type.options[f.task_constraint_type.selectedIndex].value;
            url += '&task_constraint_date ='+f.task_constraint_date.value + f.constraint_hour.options[f.constraint_hour.selectedIndex].value + f.constraint_minute.options[f.constraint_minute.selectedIndex].value;
			url += '&task_parent=';
			url += f.task_parent.options[f.task_parent.selectedIndex].value; // esta es la que eligo como tarea padre
			url += (!fme.src ? '&firstTime=1' : '&firstTime=0');


			for (dl; dl > -1; dl--){
				dependencies[dependencies.length] = f.task_dependencies.options[dl].value;
			}
			url += '&dependencies=' + dependencies.join(",");

			for (var i = 0 ; i < assignedid.length; i++){
				url += '&units['+assignedid[i]+']=' + units[assignedid[i]];
			}
			url += '&task_type='+f.task_type.options[f.task_type.selectedIndex].value;
			url += '&task_effort_driven='+(f.task_effort_driven.checked?f.task_effort_driven.value:0);
			url += '&task_work='+f.task_work.value;
			url += '&task_owner='+f.task_owner.value;

			f.form_checked.value="0";

			fme.src=url;

			if(f.debug_js.value == "1")
			{
			prompt(action,url);
                                     }


		}

   }


   function swap_changed_fields(field){

		var f = document.editFrm;

		var is_ok = true;

		// Si no hizo cambios no hace nada
		switch(field.name)
		{
			case "task_duration":
			   if( f.task_duration.value =="") {f.task_duration.value =0 }
			   if(curtask.duration == f.task_duration.value && curtask.duration_type == f.task_duration_type.value){ return;}
			break;

			case "task_start_date":
			   var fecha = f.task_start_date.value + f.start_hour.value + f.start_minute.value + "00";

			   if(curtask.start_date  == fecha) {return;}
			break;

			case "task_end_date":
			   var fecha = f.task_end_date.value + f.end_hour.value + f.end_minute.value + "00";

			   if(curtask.end_date == fecha) {return;}
			break;
		}

		if (field.name == 'task_duration'){

			is_ok = valida_numero(field, 'task_duration');
			if(!is_ok){
			     f.task_duration.focus();
		                 alert1('<?=$AppUI->_('NaN_task_duration')?>');
			}
		}


		if(is_ok){

		var tsstart = f.task_start_date.value + f.start_hour.options[f.start_hour.selectedIndex].value + f.start_minute.options[f.start_minute.selectedIndex].value + "00";

		var tsend = f.task_end_date.value + f.end_hour.options[f.end_hour.selectedIndex].value + f.end_minute.options[f.end_minute.selectedIndex].value+ "00";

		var duration = f.task_duration.value;
		var duration_type = f.task_duration_type.options[f.task_duration_type.selectedIndex].value;

		var task_constraint_date = f.task_constraint_date.value + f.constraint_hour.value + f.constraint_minute.value;
		var task_constraint_type = f.task_constraint_type.options[f.task_constraint_type.selectedIndex].value;

		curtask.start_date = tsstart;
		curtask.end_date = tsend;
		curtask.duration = duration;
		curtask.duration_type = duration_type;
		curtask.constraint_date = task_constraint_date;
		curtask.task_constraint_type = task_constraint_type;

                        execute_remote_script(field.name);
		}
}



function update_field(field, value){
	fld = eval( 'document.editFrm.'+field);
	fld.value = value;

	switch(field){
	case "task_start_date":
		curtask.start_date = getTSStartDate();
		break;
	case "task_end_date":
		curtask.end_date = getTSEndDate();
		break;
	case "task_duration":
		curtask.duration = value;
		break;
	case "task_work":
		curtask.work = value;
		break;
	}



}

function show_message(msg)
{
	if(msg != '')
	{
	alert(msg);
	}else{


		if((assignedid.length == 0 || !document.editFrm.task_effort_driven.checked ) && document.editFrm.task_work.value !=0)
		{
		    msg_calendar('1');
	            }else{
	                msg_calendar('0');
	            }
	}
}


function update_units(user, value){
	units[user]=value;
	refreshfilters();
}

function getTSStartDate(){
	var f = document.editFrm;
	return f.task_start_date.value +
			f.start_hour.options[f.start_hour.selectedIndex].value +
			f.start_minute.options[f.start_minute.selectedIndex].value + "00";

}


function getTSEndDate(){
	var f = document.editFrm;
	return f.task_constraint_date.value +
			f.end_hour.options[f.end_hour.selectedIndex].value +
			f.end_minute.options[f.end_minute.selectedIndex].value+ "00";
}

function getTSConstraintDate(){
	var f = document.editFrm;
	return f.task_end_date.value +
			f.constraint_hour.options[f.constraint_hour.selectedIndex].value +
			f.constraint_minute.options[f.constraint_minute.selectedIndex].value+ "00";
}

function selectTime(fieldname, hour, minute){
	var f = document.editFrm;
	var cboHours = eval( 'document.editFrm.' + fieldname + '_hour' );
	var cboMinutes = eval( 'document.editFrm.' + fieldname + '_minute' );

	if (cboHours && cboMinutes){
		for(var i = 0; i<cboHours.options.length; i++){
			if (hour == cboHours.options[i].value){
				cboHours.selectedIndex = i;
				i = cboHours.options.length;
			}
		}
		for(var i = 0; i<cboMinutes.options.length; i++){
			if (minute == cboMinutes.options[i].value){
				cboMinutes.selectedIndex = i;
				i = cboMinutes.options.length;
			}
		}
	}

	if (fieldname=="start"){
		curtask.start_date = getTSStartDate();
	}else{
		curtask.end_date = getTSEndDate();
	}

}

function isValidDate(strField){

    var bMDVok = true;
    var strMDVparam1 = eval( 'document.editFrm.' + strField );
    var strMDVparam2 = eval( 'document.editFrm.task_' + strField +'_format');
    var strMDVparam3 = eval( 'document.editFrm.task_' + strField );
    if (trim(strMDVparam1.value)!=""){
	    if(<?php echo $objManualDate->buildFunctionMDVJS(); ?>){
	    	if (strField == "start_date"){
	        	alert("<?php echo $AppUI->_('taskStartDateError'); ?>");
	    	}else{
	    		alert("<?php echo $AppUI->_('taskEndDateError'); ?>");
	    	}

	        bMDVok = false;
	    }

    }
    return bMDVok;
}

function update_afterof(obj){

   var f = document.editFrm;
   <? echo $strJs_ao; ?>
   var tbp = tasksP.length -1;
   var tb = f.task_before.length -1;

   // Vacia el select actual de task_before
   for(tb; tb > -1; tb--){
    f.task_before.options[tb] = null;
   }


   // Recorro el vector con tareas del proyecto y carga la tarea que ingreso por obj.
   for(tbp = 0 ; tbp <= tasksP.length -1; tbp++ )
   {
	 if(obj == tasksP[tbp][0])
	 {
	 opt = new Option( tasksP[tbp][2], tasksP[tbp][0] );
     f.task_before.options[0] = opt;
	 var leng = tasksP[tbp][1].length;
	 var ind = tasksP[tbp][1];
	 }
   }

   var tbp = tasksP.length -1;
   var t = 0;
   var sta = -1;
    // Recorre el vector con tareas de nuevo
   for(tbp = 0 ; tbp <= tasksP.length -1; tbp ++ )
   {
   	 if(obj == tasksP[tbp][3] && obj != tasksP[tbp][0] && tasksP[tbp][0]!= '<?=$obj->task_id?>' )
	 {
	 	t = t + 1;
		opt = new Option( tasksP[tbp][2], tasksP[tbp][0] );
		f.task_before.options[t] = opt;

		 if (tasksP[tbp][0] == '<?=$task_before?>')
		 {
		 	sta = t;
		 }
	 }

   }

   if(obj==0)
	{
	   var t = 0;
       opt = new Option( "<?php echo $AppUI->_('None');?>" , 0 );
	   f.task_before.options[t] = opt;

	   var tbp = tasksP.length -1;
       for(tbp = 0 ; tbp <=  tasksP.length -1 ; tbp ++ )
	   {
		   if(tasksP[tbp][1].length == 1 )
		   {
			 t = t + 1;
			 opt = new Option( tasksP[tbp][2], tasksP[tbp][0] );
			 f.task_before.options[t] = opt;

		   }
	   }
	}

	if (sta >0 )
	{
	   f.task_before.selectedIndex = sta;
	}else{
	   f.task_before.selectedIndex = t;
	}

 }


 function submitIt()
 {
	var form = document.editFrm;
	var dl = form.task_dependencies.length -1;
	var msg = "";
	var rta=true;
	var rta1 = true;
	var mandatory_constraint_dates = "<?php echo $mandatory_constraint_dates?>";
	var valid_constraint_date = true;

    var bMDVok = true;
    var strMDVparam1 = null;
    var strMDVparam2 = null;
    var strMDVparam3 = null;

	var st = '';
	var et = '';
	st = form.task_start_date.value;
	st += form.start_hour.options[form.start_hour.selectedIndex].value;
	st += form.start_minute.options[form.start_minute.selectedIndex].value;
	et = form.task_end_date.value;
	et += form.end_hour.options[form.end_hour.selectedIndex].value;
	et += form.end_minute.options[form.end_minute.selectedIndex].value;


   /* <? if ($canManageRoles){ ?>
	var i=0;
	for(i=0; i<assignedid.length && rta1; i++){
		if(assignedid[i]!=-1){
			var ut = units[assignedid[i]];
			rta1 = rta1 && checkunits(ut);
			rta1 = rta1;
			ut = null;
			if (users_not_added[assignedid[i]]){
				msg += users_not_added[assignedid[i]] + "\n";
			}
		}

	}

	rta=true;
	if (msg.length > 0 ){
		rta=confirm ("<?=$AppUI->_("Do you want to add the following users to this project?");?>" + "\n" );
	}
	<? } ?>		*/

	var prio = parseFloat(form.task_priority.value);

    //ingreso fecha manual
    strMDVparam1 = form.start_date;
    strMDVparam2 = form.task_start_date_format;
    strMDVparam3 = form.task_start_date;
    if(<?php echo $objManualDate->buildFunctionMDVJS(); ?>){

        alert("<?php echo $AppUI->_('taskStartDateError'); ?>");
        strMDVparam1.focus();
        bMDVok = false;
    }

    strMDVparam1 = form.end_date;
    strMDVparam2 = form.task_end_date_format;
    strMDVparam3 = form.task_end_date;
    if(<?php echo $objManualDate->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('taskEndDateError'); ?>");
        strMDVparam1.focus();
        bMDVok = false;
    }
    //fin ingreso fecha manual

    strMDVparam1 = form.constraint_date;
    strMDVparam2 = form.task_constraint_date_format;
    strMDVparam3 = form.task_constraint_date;
    if(<?php echo $objManualDate->buildFunctionMDVJS(); ?>){
        valid_constraint_date = false;
    }

	if (trim(form.task_name.value).length < 3) {
		alert( "<?php echo $AppUI->_('taskName');?>" );
		form.task_name.focus();
	}
	else if (!(prio >= 0 && prio <=1000)) {
		alert( "<?php echo $AppUI->_('taskValidPriority');?>" );
		form.task_priority.focus();
	}
	else if (!rta){
		form.resources.focus();
	}

	else if (!rta1){
		form.resources.focus();
	}

	else if (!trim(form.task_start_date.value)) {
		alert( "<?php echo $AppUI->_('taskValidStartDate');?>" );

	}
	else if (!trim(form.task_end_date.value) ) {
		alert( "<?php echo $AppUI->_('taskValidEndDate');?>" );

	}


	else if (st > et){
		alert( "<?php echo $AppUI->_('taskValidEndDate');?>" );
	}

	/*else if (form.form_checked.value=="0"){
    	alert( "<?php echo $AppUI->_('taskFormCheckIncomplete');?>" );
    }*/
	else if (mandatory_constraint_dates.indexOf(form.task_constraint_type.options[form.task_constraint_type.selectedIndex].value) > -1 && !valid_constraint_date){
		alert( "<?php echo $AppUI->_('taskMandatoryConstraintDate');?>" );
    	show_tab('general');
       	strMDVparam1.focus();
	}
	else {

		trim(form.hdependencies).value = "";
		for (dl; dl > -1; dl--){
			form.hdependencies.value = "," + form.hdependencies.value +","+ form.task_dependencies.options[dl].value
		}

		if ( trim(form.task_start_date.value).length > 0 ) {
			form.task_start_date.value += form.start_hour.value + form.start_minute.value;
		}

		if ( trim(form.task_end_date.value).length > 0 ) {
			form.task_end_date.value += form.end_hour.value + form.end_minute.value;
		}

        //ingreso fecha manual
        if(bMDVok) form.submit();
	}
}

function disable(disableIt)
{
document.editFrm.task_manual_percent_complete_2.disabled = disableIt;
}

function progress_msg(visibility_st){

	var f = document.editFrm;

	document.editFrm.btnFuseAction.disabled = true;
	document.editFrm.cancel.disabled = true;

	if(visibility_st == 'mostrar')
	{
                        // Muestro el cartel de procesando
		document.getElementById('progress').style.display='';

		// Inhabilito los campos del formulario
		<? if ($canManageRoles){ ?>
	            document.getElementById("taskusers").disabled = true;
	            <? } ?>
		document.getElementById("tabgeneral").disabled = true;
		document.getElementById("tabdependences").disabled = true;

		// Me aseguro que no quede el mensaje colgado en caso de error
	           setTimeout("progress_msg('error')", 60*1000);

	}else{

		// Habilito los campos del formulario

		document.getElementById("tabgeneral").disabled = false;
		document.getElementById("tabdependences").disabled = false;
		<? if ($canManageRoles){ ?>
		document.getElementById("taskusers").disabled = false;
	           <? } ?>
	            // Oculto el mensaje de error
	            document.getElementById('progress').style.display = "none";

	            document.editFrm.btnFuseAction.disabled = false;
	            document.editFrm.cancel.disabled = false;
	}

	//document.editFrm.btnFuseAction.disabled = false;

}

function valida_numero(valor,campo)
{
	var f = document.editFrm;

	//parseFloat toma el valor introducido y lo convierte en real
	var number = parseFloat(valor.value);
	var is_ok = true;

	if (isNaN(number)==true){ // comprueba si es número y si no lo es abre una ventana

		is_ok = false;
	}else{
		if (campo == 'task_duration')
		f.task_duration.value = number; // si el valor es numérico, lo confirma
		if (campo == 'task_work')
		f.task_work.value = number;

		is_ok = true;
	}

	return is_ok;
}


function msg_calendar(tipo)
{
      var f = document.editFrm;

      if(tipo=='1')
      {
      	msg_c =" ** El calendario usado para realizar los calculos es el del propietario de la tarea :"+users[f.task_owner.value];
      }else{
      	msg_c =" ";
      }

      content = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" >" ;
      content = content + "<tr><td>" ;
      content = content +""+msg_c;
      content = content + "</td></tr>" ;
      content = content + "</table>" ;

      document.getElementById("msg_calendars").innerHTML = content;

}

</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<form name="editFrm" action="" method="post">
	<input name="dosql" type="hidden" value="do_task_aed" />
	<input name="task_id" type="hidden" value="<?php echo $task_id;?>" />
	<input name="task_project" type="hidden" value="<?php echo $task_project;?>" />
	<input name='task_contacts' type='hidden' value="<?php echo $obj->task_contacts; ?>" />

	<input type="hidden" name="all_task" value="" />
	<input type="hidden" name="hassign" />
	<input type="hidden" name="hunits" />
	<input type="hidden" name="hdependencies" />
	<input type="hidden" name="min_start_date" value="" />
	<input type="hidden" name="max_end_date" value="" />
	<input type="hidden" name="form_checked" value="1" />
	<input type="hidden" name="debug_js" value="<?=$debug_js;?>">

<tr>
	<td colspan="2" style="border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier;?>" >
		<font color="<?php echo bestColor( $project->project_color_identifier ); ?>">
			<strong><?php echo $AppUI->_('Project');?>: <?php echo @$project->project_name;?></strong>
		</font>
	</td>
</tr>
<tr valign="top">
	<td colspan="100">

        <table cellspacing="2" cellpadding="0" border="0" width="98%" class="tableForm_bg">
		  <tr>
			<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_('Task Name');?>:</td>
			<td>
			<?
			  $task_name = checkpost($obj->task_name);
			?>
			<input type="text" class="text" name="task_name" value="<?php echo $task_name;?>" size="40" maxlength="255" />*
			</td>
			<td align="right" style="font-weight: bold;">
			  <table border="0">
			  <tr>
			  	<td style="font-weight: bold;" nowrap="nowrap">
				  <b><?php echo $AppUI->_('wbs_current');?>:</b>
				</td>
			  	<td width="100">
				  <input type="text" size="5" class="text" name="wbs_current" value="<? echo $current_wbs; ?>" disabled>
				</td>
			  	<td style="font-weight: bold;" nowrap="nowrap">
				  <?php echo $AppUI->_('after_of');?> :
				</td>
			  </tr>
			  </table>
			</td>
			<td>
			 <?
			  //echo "Tareas: <pre>"; print_r($projTasks); echo "</pre>";
			  echo arraySelect($projTasks, 'task_before', 'size="1" class="text "', $task_before, true, false,'190px' );
			 ?>
			</td>
		  </tr>
		  <tr>
		    <td valign="top" align="right" nowrap="nowrap" style="font-weight: bold;">
			  <?php echo $AppUI->_( 'Description' );?>:
			</td>
			<td colspan ="3" >
				<textarea  name="task_description" class="textarea" cols="118" rows="5" wrap="virtual"><?php echo @$obj->task_description;?></textarea>
			</td>
		  </tr>

		  <tr>
            <td colspan="3" valign="bottom">
				<?php
				// tabbed information boxes
				$tabBox = new CTabBox( "?m=$m&a=$a", "{$AppUI->cfg['root_dir']}/modules/tasks/", $tab );
				$tabBox->add( "javascript: show_tab('general');", 'General' );
				$tabBox->add( "javascript: show_tab('dependences');", 'Task Dependencies' );
				$tabBox->showtabbuttons();
				?>
			</td>
			<td width="175">
			   <? if($task_id > 0) { ?>
			   <b><?php echo $AppUI->_( 'Task Complete' );?>:</b>
			   <input type='checkbox' name='task_complete_2' value=<?php echo "'".$obj->task_complete."' ".
			   ($obj->task_complete ? 'checked' : ' ' )." ".
			   ($vec2['task_parent_is_complete'] ? 'disabled'  : '')."".
			   ($obj->task_complete_possible_get() ? '' : 'disabled'); ?> onclick='disable(this.checked)' >
			   <? }else{?>
                &nbsp;
			   <?}?>
			</td>
		  </tr>

		  <tr>
            <td colspan="4">

              <table cellspacing="2" cellpadding="0" width="100%" class="tableForm_bg" style="border: 1px #222222 solid;">
			   <tr >
				 <td height="60%" >
				 <div style="overflow: auto; width: 100%; height: 450px; padding:0px; margin: 0px">

				  <?  /*------------------- Mensaje de procesando --------- */ ?>
				  <div id="progress" name="progress" style='display:none;position:absolute;padding:0px;width:350px;height:70px;background-color: #E9E9E9; left: 40%; top: 40%; border:1px solid;'>
				     <br><center><b>Cargando, por favor espere un momento...</b></center>
				     <br>
				     <center><? echo dPshowImage( './images/loadinfo-4.net.gif', 24, 24, '' ); ?></center>
				  </div>
                  <? /*-----------------------------------------------------*/ ?>

				  <? /*-------------------- Tab General --------------------*/ ?>
				  <div name="tabgeneral" id="tabgeneral" >
					  <? require_once( "addedit_gral.php" ); ?>
				  </div>
				  <? /*-----------------------------------------------------*/ ?>


				  <? /*-------------------- Tab Dependencias --------------------*/ ?>
				  <div name="tabdependences" id="tabdependences">
					  <? require_once( "addedit_dep.php" ); ?>
				  </div>
				  <? /*----------------------------------------------------------*/ ?>


				 </div>
                 </td>
			   </tr>

			   <? if (@$_GET["debuginteraction"] == "123"){ ?>
				   <iframe name="fmeSEDCalcs" id="fmeSEDCalcs" height="200" width="800" scrolling="auto" style="border: 1px;" src="./index.php?m=public&a=task_duration_calc&suppressHeaders=1&dialog=1&task_project=<?php echo $task_project?>">
			   <? } else { ?>
				   <iframe name="fmeSEDCalcs" id="fmeSEDCalcs" style="width:0px; height:0px; border: 0px">
			   <? } ?>
			   </iframe>


			   <tr>
			     <td>
			           <div id="msg_calendars" name="msg_calendars"></div>

				   <table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td height="40" width="35%">
							* <?php echo $AppUI->_( 'requiredField' );?>
						</td>
						<td height="40" width="30%">&nbsp;</td>
						<td  height="40" width="35%" align="right">
							<table>
							<tr>
								<td>
									<!--<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('<?php echo $AppUI->_('taskCancel');?>')){location.href = '?<?php echo $AppUI->getPlace();?>';}" />-->
									<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('<?php echo $AppUI->_('taskCancel');?>')){die();}" />
								</td>
								<td>
									<!--<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save');?>" <?php echo $task_id > 0 ? '' : 'disabled';?> onClick="submitIt();" />-->
									<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save');?>"   onClick="submitIt();" />
								</td>
							</tr>
							</table>
						</td>
					</tr>
				   </table>

				 </td>
			   </tr>
              </table>

			</td>
		  </tr>

        </table>

    </td>
</tr>

</table>
<script>
show_tab('general');
//changedconstrainttype();

function exit( status ) {
    var i;

    if (typeof status === 'string') {
        alert(status);
    }

    if(window.addEventListener) {
    window.addEventListener('error', function (e) {e.preventDefault();e.stopPropagation();}, false);
    }
    var handlers = [
        'copy', 'cut', 'paste',
        'beforeunload', 'blur', 'change', 'click', 'contextmenu', 'dblclick', 'focus', 'keydown', 'keypress', 'keyup', 'mousedown', 'mousemove', 'mouseout', 'mouseover', 'mouseup', 'resize', 'scroll',
        'DOMNodeInserted', 'DOMNodeRemoved', 'DOMNodeRemovedFromDocument', 'DOMNodeInsertedIntoDocument', 'DOMAttrModified', 'DOMCharacterDataModified', 'DOMElementNameChanged', 'DOMAttributeNameChanged', 'DOMActivate', 'DOMFocusIn', 'DOMFocusOut', 'online', 'offline', 'textInput',
        'abort', 'close', 'dragdrop', 'load', 'paint', 'reset', 'select', 'submit', 'unload'
    ];

    function stopPropagation (e) {
        e.stopPropagation();
        // e.preventDefault(); // Stop for the form controls, etc., too?
    }
    for (i=0; i < handlers.length; i++) {
      if(window.addEventListener) {
        window.addEventListener(handlers[i], function (e) {stopPropagation(e);}, true);
      }
    }

    if (window.stop) {
        window.stop();
    }

    location.href = '?<?php echo $AppUI->getPlace();?>';

    //throw '';


}

function die( status ) {
    return exit(status);
}

</script>

<? if($task_id!="0"){ ?>
<!-- <script>alert("Edita");</script> -->
<? } ?>

<?

function ordena_tasks($level, $parent)
{
	global $AppUI, $task_project, $prTasks;

	if($parent != '0')
	{
		$query_parent = " AND task_parent = '$parent' ";
	}

	$query ="
	SELECT *
	FROM tasks
	WHERE task_project = '$task_project'
	AND task_wbs_level = '$level'
	$query_parent
    ORDER BY task_wbs_level,task_wbs_number
    ";

    $sql = db_exec($query);
	$cant = mysql_num_rows($sql);

    if ($cant > 0)
	{
		while($data = mysql_fetch_array($sql))
		{
			$task_name = ereg_replace('"','&quot;',$data['task_name']);

			$prTasks[$data[task_id]] = wbs($data)." - ".$task_name;
			$next_level = $data[task_wbs_level] + 1;
			ordena_tasks($next_level , $data[task_id]);
		}

    }else{
    	return;
    }

    return $prTasks;
}

?>