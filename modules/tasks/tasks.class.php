<?php /* TASKS $Id: tasks.class.php,v 1.5 2009-05-26 15:06:46 pkerestezachi Exp $ */

require_once( $AppUI->getSystemClass( 'libmail' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'admin' ) );
require_once( $AppUI->getModuleClass( 'timexp' ) );
require_once( $AppUI->getModuleClass( 'system' ) );
require_once( $AppUI->getConfig('root_dir')."/modules/tasks/functions.php" );


global $pvs;
//valor por defecto de no asignado
$nadefval=9;

$task_access=CTaskPermission::getTaskAccess();

// user rol
$user_context = array(
	'-1'=>'Company Users',
	'-2'=>'Project Users',
	'-3'=>'Only Assigned',
	'-4'=>'Owner'
);

// this var is intended to track new status in task
$new_status = null;

$task_priorities = array(
"199"=>"low",
"799"=>"normal",
"1000"=>"high"
);

$task_types = array(
"1"=>"Fixed Units",
"2"=>"Fixed Duration",
"3"=>"Fixed Work"
);

$task_constraints = array (
"1"=>"Must start on",
"2"=>"Must finish on",
"3"=>"As soon as possible",
"4"=>"As late as possible",
"5"=>"Start no earlier than",
"6"=>"Start no later than",
"7"=>"Finish no earlier than",
"8"=>"Finish no later than"
);

$dynamic_constraints = "3,5,8";

$mandatory_constraint_dates = "1,2,5,6,7,8";

/*
* CTask Class
*/
class CTask extends CDpObject {
/** @var int */
	var $task_id = NULL;
/** @var string */
	var $task_name = NULL;
/** @var int */
	var $task_parent = NULL;
	var $task_milestone = NULL;
	var $task_project = NULL;
	var $task_owner = NULL;
	var $task_start_date = NULL;
	var $task_duration = NULL;
	var $task_duration_type = NULL;
/** @deprecated */
	var $task_hours_worked = NULL;
	var $task_end_date = NULL;
	var $task_status = NULL;
	var $task_priority = NULL;
	var $task_percent_complete = NULL;
# fcastagnini
	var $task_manual_percent_complete = NULL;
	var $task_complete = NULL;
	var $task_overcome = NULL;
	var $task_overworked = NULL;
	var $task_not_started = NULL;
# /fcastagnini
	var $task_description = NULL;
	var $task_target_budget = NULL;
	var $task_related_url = NULL;
	var $task_creator = NULL;

	var $task_order = NULL;
	var $task_client_publish = NULL;
	var $task_dynamic = NULL;
	var $task_access = NULL;
	var $task_notify = NULL;
	var $task_departments = NULL;
	var $task_contacts = NULL;
	var $task_custom = NULL;
	var $task_type = NULL;
	var $task_effort_driven = NULL;
	var $task_work = NULL;
	var $task_wbs_number = NULL;
	var $task_wbs_level = NULL;
	var $task_constraint_type = NULL;
	var $task_constraint_date = NULL;
	var $perm_items = NULL;
	var $_dependencies_list = NULL;
	var $_assigned_users = NULL;
	var $task_target_budget_hhrr = NULL;

	function CTask() {
		$this->CDpObject( 'tasks', 'task_id' );
		$this->perm_items=CTaskPermission::getItemsPermission();
	}


/*
	Function: task_manual_percent_complete_insert

	Guarda en la base de datos el valor del porcentaje de avance manual, y
	luego asigna en la variable *task_manual_percent_complete* dicho valor.

   Parameters:

      percent - Porcentaje de avance o progeso a cargar en la base de datos.

   Returns:

      none

   See Also:

      <task_manual_percent_complete_select>
*/
	function task_manual_percent_complete_insert($percent) {
		//if ($this->task_complete==0)
		//{
			$percent = (integer) $percent;
			$sql = "UPDATE tasks SET task_manual_percent_complete = '".$percent."' WHERE task_id = ".$this->task_id;
			$rc=db_exec($sql);
			$this->task_manual_percent_complete_select();
		//}
	}

/*
   Function: task_manual_percent_complete_select

	Carga en la variable *task_manual_percent_complete*
	el valor del porcentaje de avance manual desde la base de datos.

   Parameters:

      valor_real - por defecto *FALSE*, si es *TRUE* devuelve del valor que figura en la db, sin impotar task_complete

   Returns:

      none

   See Also:

      <task_manual_percent_complete_insert>
*/
	function task_manual_percent_complete_select($valor_real = FALSE) {

		if($valor_real)
		{
			$sql = "SELECT task_manual_percent_complete
						FROM tasks
						WHERE task_id = " . $this->task_id;
		}
		else {
			$sql = "SELECT IF(
							task_complete like '1',
							'100',
							task_manual_percent_complete
						) AS task_manual_percent_complete
						FROM tasks
						WHERE task_id = " . $this->task_id;
		}
		$vec = db_fetch_array(db_exec($sql));
		echo db_error();
		$this->task_manual_percent_complete = $vec['task_manual_percent_complete'];
	}

/*
   Function: task_complete_possible_get

	Detecta si existen tareas hijas, y carga un valor en la variable *task_complete_possible*, 0 si no tiene tareas hijas o 1 si las tiene.

   Parameters:

    none

   Returns:

      none

*/
	function task_complete_possible_get() {

		$sql = "SELECT IF(COUNT(task_complete) = 0, '1', MIN(task_complete)) as task_complete_possible
				FROM tasks
				WHERE
				task_parent = " . $this->task_id . "
				AND task_id != '". $this->task_id."'";
		$vec = db_fetch_array(db_exec($sql));
		echo db_error();
		return $vec['task_complete_possible'];
	}

	/*
   Function: task_complete_set_parents_propagation

	Cuando se marca una terea como completada o como incompleta, propaga esta modificacion hacia las tareas superiores o inferiores, segun corresponda.

   Parameters:

      none

   Returns:

      none

   See Also:

      <task_complete_set>

*/
	function task_complete_set_parents_propagation() {

		// caso primero: si la tarea tiene hijas

		$parent_task = new CTask();

		if ( $fromParent ){
			$parent_task = &$this;
			//echo "<pre>"; var_dump($parent_task);echo "</pre>";
		} else {
			$parent_task->load($this->task_parent);
		}

		$children = CTask::getChildren($parent_task->task_id);
		for($i=0; $i< count($children); $i++) {
			$parent_task->task_complete = $status;
		}

		$sql = "
			SELECT
			FROM tasks
			WHERE task_id = " . $this->task_parent;

		$sql2 = "
			SELECT task_complete as task_parent_is_complete
			FROM tasks
			WHERE
			task_id = ".$a['task_parent'];

		//$vec = db_fetch_array(db_exec($sql));
		//echo db_error();
		//this->$task_task_not_started = $vec['result'];
	}

/*
   Function: task_complete_set

	Marca la tarea como completa o incompleta.

   Parameters:

    status - 1 (tarea completa), 0 (tarea incompleta)

   Returns:

      none

   See Also:

      <task_complete_get>
*/
	function task_complete_set($status) {
		# Valida los parametros,  status de ser 1 ó 0
		$status = (integer) $status;

		//Si se esta marcando como incompleta una tarea hija, se tienen que desmarcar los padres tb
// 		if (($status == 0) AND ($this->task_parent != $this->task_id) AND is_numeric($this->task_parent)){
// 			$prt = new CTask();
// 			$prt->load($this->task_parent);
// 			$prt->task_complete_set('0');
// 			$msg = "<pre>ENTRE EN LA TAREA PADRE</pre>";
// 		}else $msg = "<pre>NO ENTRE EN LA TAREA PADRE</pre>";

		if (($status == 0 OR $status == 1) AND $this->task_complete_possible_get()) {
			$sql = "UPDATE tasks SET task_complete = '".$status."' WHERE task_id = ".$this->task_id;
			$rc=db_exec($sql);
			echo db_error();
		}
		//$this->task_complete_get();
		// checkeamos las tareas padres o hijas como completas o incompletas
		//$this->task_complete_set_parents_propagation();
	}

/*
   Function: task_complete_get

	Carga en la variable *task_complete* (1,0) el valor desde la base de datos.

   Parameters:

      none

   Returns:

      none

   See Also:

      <task_complete_set>
*/
	function task_complete_get() {
		$sql = "SELECT task_complete FROM tasks WHERE task_id = " . $this->task_id;
		$vec = db_fetch_array(db_exec($sql));
		echo db_error();
		$this->task_complete = $vec['task_complete'];
	}

/*
   Function: task_overcome

	Detecta si la tarea esta vencida.
	Son aquellas que no estan marcadas como completadas y la fecha de finalizacion expiro.

   Parameters:

	none

   Returns:

    none

   See Also:

    <task_overworked>
    <task_not_started>
*/
	function task_overcome()	{
		$sql = "
		SELECT IF(task_complete = 0,0,IF(current_timestamp() > task_end_date,1,0))
		AS result
		FROM tasks
		WHERE task_id = " . $task_id;

		//$vec = db_fetch_array(db_exec($sql));
		//echo db_error();
		//this->$task_overcome = $vec;
	}

/*
   Function: task_overworked

	Detecta si la tarea tiene sobretrabajo.\n
	Son aquellas cuyas horas reportadas son superiores a las horas asignadas a la tarea.

   Parameters:

      none

   Returns:

      none

   See Also:

      <task_overcome>
      <task_not_started>
*/
	function task_overworked()	{
		$sql = "
		SELECT IF(task_hours_worked > task_work,1,0) AS result
		FROM tasks
		WHERE task_id = " . $task_id;

		//$vec = db_fetch_array(db_exec($sql));
		//echo db_error();
		//this->$task_overworked = $vec['result'];
	}

/*
   Function: task_not_started

	Detecta si la tarea no fue iniciada.
	Son aquellas donde la fecha de inicio fue alcanzada y no tiene horas reportadas o progreso declarado.

   Parameters:

      none

   Returns:

      none

   See Also:

      <task_overcome>
      <task_overworked>
*/
	function task_not_started()	{
		$sql = "
		SELECT	IF(task_complete= 0,0,
			IF(task_hours_worked,0,
			IF(current_timestamp() > task_start_date,1,0)
			)) AS result
		FROM tasks
		WHERE task_id = " . $this->task_id;

		//$vec = db_fetch_array(db_exec($sql));
		//echo db_error();
		//this->$task_task_not_started = $vec['result'];
	}

/*
   Function: check

		"overload check"
		Esto es todo lo que dice...aparentemente carga algunas variables del objeto.

   Parameters:

      none

   Returns:

      NULL - siempre devuelve NULL
*/
	function check() {
		global $new_status;

		if ($this->task_id === NULL) {
			return 'task id is NULL';
		}
		// ensure changes to checkboxes are honoured
		$this->task_milestone = intval( $this->task_milestone );
		$this->task_dynamic   = intval( $this->task_dynamic );

		if (!$this->task_duration) {
			$this->task_duration = '0';
		}
		if (!$this->task_duration_type) {
			$this->task_duration_type = 1;
		}
		if (!$this->task_related_url) {
			$this->task_related_url = '';
		}
		if (!$this->task_notify) {
			$this->task_notify = 0;
		}

		$this->task_complete_get();
		$this->task_manual_percent_complete_select();

		$actual_status = db_loadResult("select task_status from tasks where task_id='$this->task_id'");
		if($actual_status != $this->task_status){
			$new_status = $this->task_status;
		}
		return NULL;
	}

	function updateDynamics( $fromParent = false ) {
		//Has a parent or children, we will check if it is dynamic so that it's info is updated also
        echo "<br><b>Función updateDynamics: </b><br>";

		$parent_task = new CTask();

		if ( $fromParent ){
			$parent_task = &$this;
			//echo "<pre>"; var_dump($parent_task);echo "</pre>";
		} else {
			$parent_task->load($this->task_parent);
		}

		if ( $parent_task->task_dynamic == 1 ) {
			//Update allocated hours based on children
			$parent_task->getChildrensMaxMinDate("",$min_start_date, $max_start_date);

			$start_date = new CWorkCalendar(2, $parent_task->task_project,"",$min_start_date);
			$end_date = new CWorkCalendar(2, $parent_task->task_project,"",$max_start_date);

			$parent_task->task_duration = $start_date->dateDiff($end_date, $parent_task->task_duration_type);

			/* completar con los ajustes a realizar en base a los constraints */

			$parent_task->task_hours_worked = CTimExp::getApprovedTaskHours($parent_task->task_id);


			// sumo las horas trabajadas en las tareas hijas
			$children = CTask::getChildren($parent_task->task_id);
			for($i=0; $i< count($children); $i++){
				$parent_task->task_hours_worked += CTimExp::getApprovedTaskHours($children[$i]["task_id"]);
			}

			$parent_task->task_start_date = $start_date->format(FMT_DATETIME_MYSQL);
			$parent_task->task_end_date = $end_date->format(FMT_DATETIME_MYSQL);

			//If we are updating a dynamic task from its children we don't want to store() it
			//when the method exists the next line in the store calling function will do that
			if ( $fromParent == false ) $parent_task->store();
		}
	}

/*
   Function: updateHoursWorked

	Aparentemente hace update worked hours. Creo que itera en las tareas hijas.

   Parameters:

      save - Le indica a la funcion si guarda o no lo que sea que hace. Por defecto es *true*.

   Returns:

      none

*/
	function updateHoursWorked($save = true){
		$msg = NULL;
		if (! (is_numeric($this->task_id) && $this->task_id > 0)){
			return "This is an object method, there is no task loaded.";
		}

		// obtengo las horas trabajadas que se aplicaron a la tarea
		// y contribuyen a su completitud
		$sql = "select distinct sum(te.timexp_value)
				from timexp te
				where	te.timexp_applied_to_id = $this->task_id
				and		te.timexp_applied_to_type = 1
				and 	te.timexp_type = 1
				and 	te.timexp_contribute_task_completion = 1;";
		$task_hours_worked = (float) db_loadResult( $sql );
		// si no hay horas trabajadas cargo 0
		$this->task_hours_worked = $task_hours_worked ? $task_hours_worked : 0;

		// obtengo el trabajo de la tarea en horas
		$task_work = (float) $this->task_work;

		//cuando tenga duracion calculo el grado de avance
		if ($task_work > 0 ){
			// segun sismonda, busca la ultima fecha donde se cargaron horas para usarla como fecha de
			// finalizacion, en caso de que no este especificado en la base
			// Esto sucede cuando las tareas estan marcadas como completadas
			// FIXME: deberia usar el campo task_complete
			if ($this->task_manual_percent_complete >= 100 && $this->task_end_date == '0000-00-00 00:00:00'){
				$sql = "select max(te.timexp_date)
						from timexp te
						inner join tasks ta on ta.task_id = te.timexp_applied_to_id
						inner join project_roles pr on pr.project_id = ta.task_project and pr.user_id = te.timexp_creator and pr.role_id = 2
						where	ta.task_id = $this->task_id
						and		te.timexp_applied_to_type = 1
						and 	te.timexp_type = 1
						and 	te.timexp_contribute_task_completion = 1";
				//echo "<pre>$sql</pre>";
				$end_date = db_loadResult($sql);
				if (!is_null($end_date))
					$this->task_end_date = $end_date ;
				else
					$this->task_end_date = $this->task_start_date;
			}
		}

		//graba las modificaciones
		if ($save){
			// $msg = $this->store();
			$ret = db_updateObject( 'tasks', $this, 'task_id', false );
			if( !$ret ) {
				$msg = get_class( $this )."::update worked hours failed <br />" . db_error();
			}
		}
			if ($msg = CProject::checkOverWorked($this->task_project))
				return $msg;
			else
				return NULL;

		//return $msg;

	}

	function canSuperviseTimexp(){
		global $AppUI;

		if ($AppUI->user_type == 1)
			return true;

		$objTMP = new CProject();
		$objTMP->load($this->task_project);
		if ($objTMP->canEdit()){
			return true;
		}

		$sql = "select count(timexp_id)
				from timexp te
					inner join users u on u.user_id = te.timexp_creator
				where
						timexp_applied_to_type = \"1\"
				and		timexp_applied_to_id = $this->task_id
				and		timexp_supervisor = $AppUI->user_id";

		if (db_loadResult($sql)>0){
			return true;
		}else{
			return false;
		}
	}

/**
*	Copy the current task
*
*	@author	handco <handco@users.sourceforge.net>
*	@param	int		id of the destination project
*	@return	object	The new record object or null if error
**/
	function copy($destProject_id = 0) {
		
		$newObj = clone $this;

		//Fix the parent task
		if ($newObj->task_parent == $this->task_id)
		{
			$newObj->task_parent = $newObj->task_id;
		}
		else
		{
			//Hay que poner la tarea como hija del clon
			$padreOriginal = new CTask();
			$padreOriginal->load($newObj->task_parent);
			$sql = "SELECT task_id FROM tasks WHERE task_name = '$padreOriginal->task_name'";
			if ( $destProject_id )
			{
				$sql .=" AND task_project = $destProject_id";
			}
			$res = db_loadResult( $sql );
			$newObj->task_parent = $res;
		}

		// Copy this task to another project if it's specified
		if ($destProject_id != 0)
			$newObj->task_project = $destProject_id;

		//Para que el store no los calcule automaticamente
		//$newObj->task_wbs_level = $this->task_wbs_level;
		//$newObj->task_wbs_number = $this->task_wbs_number;

		//$msg = $newObj->store();

		return $newObj;
	}// end of copy()

/**
* @todo Parent store could be partially used
*/
	function store($update_dependencies=true, $update_dependants=true, $update_wbs=true) {
		GLOBAL $AppUI, $new_status;

		//echo "<p>storeando</p>";

		$msg = $this->check();
		if( $msg )
		{
			return get_class( $this )."::store-check failed - $msg";
		}

		if ($this->task_parent == "0")
        {
		$this->task_parent = $this->task_id;
		}

		if( $this->task_id )
		{
			echo "<p>es tarea vieja</p>";
			$this->_action = 'updated';

			// if task_status changed, then update subtasks
			if(!is_null($new_status))
			{
				$this->updateSubTasksStatus($new_status);
			}


			//$this->updateHoursWorked(false);

			// Cuando la tarea tiene hijas es din?ica
			/*
			$sql =	"select count(task_id) ".
					"from tasks ".
					"where task_parent = $this->task_id and task_id != task_parent";
			$this->task_dynamic = db_loadResult($sql) > 0 ? "1" : "0";

			$this->updateDynamics(true);
			*/


			//echo "<p>Se hicieron algunos updates</p>";
			$aux_task = new CTask();
			$aux_task->load( $this->task_id );
			
			/*
			if ( $aux_task->task_wbs_number != $this->task_wbs_number )
			{
				echo "<pre>";print_r($this);echo "</pre>";
				echo "<br>";
				echo "<p>Buscando si hay que reorganizar los numeros</p>";


				//Hay que chequear si el nro coincide con el de otra tarea.

				$sql = "select task_id from tasks
				where ( task_wbs_number = $this->task_wbs_number )
				and ( task_id != $this->task_id )
				and ( task_parent = $this->task_parent )";

				if ( db_loadResult( $sql ) || ( $this->task_parent == $aux_task->task_parent ) )
				{
					//Encontre una tarea con ese mismo numero, hay que mover todas las demas
					//Correr todas las demas tareas para ajustarlos la nueva numeraci?

					if( $this->task_parent == $aux_task->task_parent && $this->task_wbs_number > $aux_task->task_wbs_number)
                    {


					echo "<br> Resto 1 al wbs <br>";
					$this->task_wbs_number = $this->task_wbs_number -1;


					$sql = "update tasks set task_wbs_number = task_wbs_number - 1
					where ( task_project = $this->task_project )
					and ( task_wbs_number <= $this->task_wbs_number )
					and ( task_wbs_level = $this->task_wbs_level )
					and (task_wbs_number > 1)
					";

					echo "<br>El nuevo WBS number es: ".$this->task_wbs_number;
					echo "Consulta 2 : <br>".$sql."<br>";


				    }

					if( $this->task_parent == $aux_task->task_parent && $this->task_wbs_number < $aux_task->task_wbs_number)
                    {
					$sql = "update tasks set task_wbs_number = task_wbs_number + 1
					where ( task_project = $this->task_project )
					and ( task_wbs_number >= $this->task_wbs_number )
					and ( task_id != $this->task_id )
					and ( task_wbs_level = $this->task_wbs_level );
					";

					echo "<br>El nuevo WBS number es: ".$this->task_wbs_number;
					echo "Consulta 1 : <br>".$sql."<br>";


				    }

                    if($update_wbs)
					db_exec( $sql );
				}

			}

			//si cambia de parent
		   if($update_wbs)
		   {
			if ( $aux_task->task_parent != $this->task_parent )
			{
				//Si lo movi en el arbol tambien hay que reacomodar las cosas
				if ( $this->task_parent != $this->task_id )
				{
					//Es hijo de otro padre
					$tsk = new CTask();
					$tsk->load( $this->task_parent );

					echo "<br><b>Llego</b><br>";

					$sql = "update tasks set task_wbs_number = task_wbs_number + 1
					where ( task_project = $this->task_project )
					and ( task_wbs_number >= $this->task_wbs_number )
					and ( task_id != $this->task_id )
					and ( task_parent = $this->task_parent )
					and ( task_wbs_level = $this->task_wbs_level );
					";

					db_exec( $sql );
				}
				else
				{
					echo "No es hijo !!<br>";
					// Actualizo el WBS de las tareas que eran hermanas
                    $sql = "update tasks set task_wbs_number = task_wbs_number -1
					where ( task_project = $this->task_project )
					and ( task_wbs_number > $aux_task->task_wbs_number )
					and ( task_id != $this->task_id )
					and ( task_parent = $aux_task->task_parent )
					and ( task_wbs_level = $aux_task->task_wbs_level );
					";
					echo $sql;
					db_exec( $sql );

					//No es hijo de nadie
					$prj = new CProject();
					$prj->load( $this->task_project );
					$this->task_wbs_level = 0;

					$sql = "update tasks set task_wbs_number = task_wbs_number + 1
					where ( task_project = $this->task_project )
					and ( task_wbs_number >= $this->task_wbs_number )
					and ( task_id != $this->task_id )
					and ( task_wbs_level = $this->task_wbs_level );
					";
					echo $sql;
					db_exec( $sql );

				}

			}
		   }
		   */

			if ($update_dependencies)
				$this->updateDependencies();


			$ret = db_updateObject( 'tasks', $this, 'task_id', false );

			/*
			//si es hija de otra tarea actualiza la misma
			if ($ret && $this->task_parent != $this->task_id){
				$tsk = new CTask();
				$tsk->load( $this->task_parent );
				$tsk->task_dynamic = "1";

				echo "<br> Actualiza al padre : <br>";
				$tsk->updateDynamics(true);
				//echo "<pre>"; var_dump($tsk);echo "</pre>";
				$ret = db_updateObject( 'tasks', $tsk, 'task_id', false );
			}

			//luego de guardar la tarea debo actualizar a las tareas que dependen de ella
			if ($ret && $update_dependants){
				$this->updateDependants();
			}
			if ($ret){
				echo "<br> Actualiza sus hijas : <br>";
				$this->updateChildren();

				// si se modifica la duracion
				if ( ($aux_task->task_duration * $aux_task->task_duration_type) !=
					($this->task_duration * $this->task_duration_type) ){
					    echo "<br>Modifica la duración total de las horas :<br>";
						CProject::updatedTotalHours($this->task_project);
				}

			}
			*/
		}
		else
		{
			//echo "<p>nueva tarea</p>";
			$this->_action = 'added';

			$aux_task = new CTask();
			$aux_task->load( $this->task_id );

			$ret = db_insertObject( 'tasks', $this, 'task_id' );

			//echo $aux_task->task_wbs_number." != ".$this->task_wbs_number."<br>";
			
			if ( $aux_task->task_wbs_number != $this->task_wbs_number )
			{
				//echo "<pre>";print_r($this);echo "</pre>";
				//echo "<br>";
				//echo "<p>Buscando si hay que reorganizar los numeros</p>";


				//Hay que chequear si el nro coincide con el de otra tarea.

				$sql = "select task_id from tasks
				where ( task_wbs_number = $this->task_wbs_number )
				and ( task_id != $this->task_id )
				and (task_wbs_level = $this->task_wbs_level)
				and ( task_project = $this->task_project )";
				//echo "<pre>$sql</pre>";

				if ( db_loadResult( $sql ) )
				{
					//Encontre una tarea con ese mismo numero, hay que mover todas las demas
					//Correr todas las demas tareas para ajustarlos la nueva numeraci?

					$sql = "update tasks set task_wbs_number = task_wbs_number + 1
					where ( task_project = $this->task_project )
					and ( task_wbs_number >= $this->task_wbs_number )
					and ( task_id != $this->task_id )
					and ( task_wbs_level = $this->task_wbs_level )
					";
					//echo "<br>".$sql."<br>";

					db_exec( $sql );
				}

			}

			if ($ret){
				if ($update_dependencies)

				$this->updateDependencies();
				$ret = db_updateObject( 'tasks', $this, 'task_id', false );

			}

			if($ret && $this->task_duration != 0){
				CProject::updatedTotalHours($this->task_project);
			}

			/*
			//luego de guardar la tarea debo actualizar a las tareas que dependen de ella
			if ($ret  &&  $update_dependants){
				$this->updateDependants();
			}
			*/

			//echo "<p>Tarea creada, su id es $this->task_id</p>";
			if (!$this->task_parent)
			{

				// new task, parent = task id
				$this->task_parent = $this->task_id;
				$prj = new CProject();
				$prj->load( $this->task_project );
				if ( !$this->task_wbs_number ) //Para el caso de las clonaciones
					$this->task_wbs_number = ($prj->getMaxTaskNumber() + 1);
				$this->task_wbs_level = 0;
				$sql = "UPDATE tasks SET task_parent = $this->task_id, task_wbs_number = ".$this->task_wbs_number.", task_wbs_level = 0 WHERE task_id = $this->task_id";
				//echo "<pre>$sql</pre>";
				db_exec( $sql );
			}
			else
			{
				$tsk = new CTask();
				$tsk->load( $this->task_parent );
				if ( !$this->task_wbs_number ) //Para las clonaciones
					$this->task_wbs_number = $tsk->getMaxSubTaskNumber($this->task_id)+ 1;
				$this->task_wbs_level = $tsk->task_wbs_level + 1;
				//echo "<p>Soy tarea hija de $tsk->task_name, el maximo anterior es: ".$tsk->getMaxSubTaskNumber()."</p>";
				$sql = "UPDATE tasks SET task_wbs_number = ".$this->task_wbs_number.", task_wbs_level = ".$this->task_wbs_level." WHERE task_id = $this->task_id";
				db_exec( $sql );
			}
			// insert entry in user tasks
			$sql = "INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($AppUI->user_id, $this->task_id, -1)";
			//echo "<pre>$sql</pre>";
			db_exec( $sql );
			//echo "<p>Sql ejecutado</p>";
		}

		//echo "<p>fin store</p>";
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		}

		if($msg = CProject::updateProgress($this->task_project)	)
			return $msg;

		/*
		if ($msg = CProject::checkOverWorked($this->task_project))
			return $msg;*/

		return NULL;

	}

	function getMaxSubTaskNumber($task_id) {
		$sql = "SELECT MAX( `task_wbs_number` )
				FROM `tasks`
				WHERE `task_parent` = '$task_id' and `task_id` <> '$task_id' ";
		echo $sql."<br>";
		return db_loadResult( $sql );
	}


/**
* @todo Parent store could be partially used
* @todo Can't delete a task with children
*/
	function delete( $deleteChildren = false ) {
		$this->_action = 'deleted';
	// delete linked user tasks
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}

		$sql = "delete timexp_status.*, timexp.*, timexp_ts.*
						FROM timexp_status inner join timexp on timexp_status.timexp_id = timexp.timexp_id
                                                LEFT JOIN timexp_ts on timexp_status.timexp_id = timexp_ts.timexp_ts_id
						WHERE timexp_applied_to_type = 1 and  timexp_applied_to_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}

/*
		$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
*/

		//Aca se borran las dependencias de otras tareas para con esta, no tiene sentido guardarlas si la tarea se fue
		//en todo caso habria que ver si se puede borrar una tarea de la cual dependen otras.
		$sql = "DELETE FROM task_dependencies WHERE dependencies_req_task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}

		//load it before deleting it because we need info on it to update the parents later on
		$this->load($this->task_id);

		$sql = "DELETE FROM tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			if ( $this->task_parent != $this->task_id ){
				// Has parent, run the update sequence, this child will no longer be in the
				// database
				$this->updateDynamics();
			}
			$children = $this->getChildren();
			if ( count($children) )
			{
				$t = new CTask();
				foreach( $children as $child_id )
				{
					$t->load( $child_id["task_id"] );
					if ( $deleteChildren )
					{
						$t->delete( true );
					}
					else
					{
						$t->task_parent = $this->task_parent != $this->task_id ? $this->task_parent : $t->task_id;
						$t->store('','',false);
					}
				}
			}
			return NULL;
		}
	}

	function updateAssigned( $cslist, $clistunits ) {

		if (! ( is_array($cslist) && is_array($clistunits) &&
				count($clistunits) == count($cslist))){
			$tarr = explode( ",", $cslist );
			$tunits = explode( ",", $clistunits );
		}else{
			$tarr =  $cslist ;
			$tunits =  $clistunits ;
		}

	// delete all current entries
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
		db_exec( $sql );

	// process assignees
		foreach ($tarr as $id => $user_id) {
			// Busco el valor por hora del usuario
            $query = "SELECT user_cost_per_hour from users where user_id = '$user_id'";
			$sql_cost = mysql_query($query);

			$cost_per_hour = mysql_fetch_array($sql_cost);

			if (intval( $user_id ) > 0) {
				$sql = "REPLACE INTO user_tasks (user_id, task_id, user_units, user_cost_per_hour) VALUES ($user_id, $this->task_id, ".$tunits[$id].", ".$cost_per_hour[0].")";
				db_exec( $sql );
				$sql = "REPLACE INTO project_roles ( project_id, role_id, user_id) VALUES ($this->task_project, 2 ,  $user_id)";
				db_exec( $sql );
			}
		}
	}

	//Si no se le pasan dependencias toma las que ya estan
	function updateDependencies( $cslist = null ) {
		// process dependencies
		//echo "<u>Función UpdateDependencies</u><br>";


		//echo "<p>Updateando las dependencias, nuevas dependencias = </p><pre>";print_r($cslist);echo "</pre>";

		if ( is_null($cslist) )
		{
			if (is_null($this->_dependencies_list))
			{
				//echo "<p>No habia dependencias</p>";
				$cslist = $this->getDependencies();
			}
			else
			{
				$cslist = $this->_dependencies_list;
				//Es una edicion, se supone que cambiaron las dependencias
				// delete all current entries
				$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
				db_exec( $sql );
			}

		}
		else
		{
			//Es una edicion, se supone que cambiaron las dependencias
			// delete all current entries
			$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
			db_exec( $sql );
		}

		$task_dep = explode( ",", $cslist );
        //echo "<pre>";print_r($task_dep);echo "</pre>";

		// si es una tarea hija, cargo las dependencias de la tarea padre
		$parent_dep = explode(",",$this->getParentDependencies());

		$tarr = array_merge($task_dep, $parent_dep);

		/*
		echo "<p>Actualizando las predecesoras de la tarea <b>'$this->task_id'</b></p>";
		$st = new CDate( $this->task_start_date );
		echo "<p>Fecha de inicio: '".$st->format( FMT_DATETIME_MYSQL )."'</p>";
		$end = new CDate( $this->task_end_date );
		echo "<p>Fecha de fin: '".$end->format( FMT_DATETIME_MYSQL )."'</p>";

		$dif = $st->dateDiff($end, $this->task_duration_type);
		echo "<p>Duracion: '".$this->task_duration."'</p>";

		//Hay que buscar la maxima fecha de fin de las tareas dependientes para fijar la fecha de comienzo de esta
		$t = new CTask();
		$newStart = new CDate($this->task_start_date); //null;
		*/

		foreach ($tarr as $task_id)
		{
			if (intval( $task_id ) > 0)
			{
				// si la dependencia es propia de la tarea la agrego
				if (in_array($task_id, $task_dep) && $this->task_id > 0){
					$sql = "REPLACE INTO task_dependencies (dependencies_task_id, dependencies_req_task_id) VALUES ($this->task_id, $task_id)";
					db_exec($sql);
				}

				/*
				$t->load( $task_id );
				$ed = new CDate( $t->task_end_date );
				echo "<hr>Tarea: $t->task_name <br>";
				echo "<p>La fecha de fin de la tarea predecesora '$t->task_name' es '".$ed->format( FMT_DATETIME_MYSQL )."'</p>";
				if ( true )
				{
					echo "<p>Comparando la fecha '".$ed->format( FMT_DATETIME_MYSQL )."' con '".$newStart->format( FMT_DATETIME_MYSQL )."'</p>";

					//cuando la fecha de fin de la predecesora es mayor a la de inicio de la tarea,
					// debo ajustar el inicio de la tarea
					if ( CDate::compare( $ed, $newStart ) > 0 )
					{
						echo "<p>La primera es mas grande</p>";
						echo "<p>Seteando la fecha max como '".$newStart->format( FMT_DATETIME_MYSQL )."'</p>";
						$newStart->setDate($ed->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP);
                        $newStart->hour = '09';
						$newStart->minute = '00';
						$newStart->addDays(1); //Para que la tarea empiece al dia siguiente de la ultima predecesora.
						echo "<p>La fecha maxima es '".$newStart->format( FMT_DATETIME_MYSQL )."'</p>";

					}
				}
				else
				{
					$newStart = $ed;
					echo "<p>Seteando la fecha max como '".$newStart->format( FMT_DATETIME_MYSQL )."'</p>";
				}
				*/
			}
		}



		//Si no hay newStart es que no habia dependencias
		/*
		if ( $newStart )
		{
			$this->task_start_date = $newStart->format( FMT_DATETIME_MYSQL );
			echo "<p>Seteando la fecha de comienzo de la tarea como '".$this->task_start_date."'</p>";
			echo get_class($newStart);

			if($this->task_duration_type==1){
                $duration = $this->task_duration / 8;
				$newStart->addDays( $duration );

			}
			else{
			$newStart->addDays( $this->task_duration );
			}
			//$this->task_end_date = $newStart->format( FMT_DATETIME_MYSQL );
			echo "<p>Seteando la fecha de fin de la tarea como '".$this->task_end_date."'</p>";
			//$this->store();
		}
		*/

		//echo "<br>-------------------------------------------------------<br><br>";

	}

	/**
	*	Update the shcedule of the tasks that depends of the current task
	*
	*	@author	Rodrigo Fuentes
	*	@return	string	comma delimited list of tasks id's
	**/

	function updateDependants(){
		//Aca hay que volver a calcular las fechas de cada una de las dependencias.
		$t = new CTask();

		echo "<p><u>Actualizando las tareas sucesoras de $this->task_name</u></p>";
		$dep = $this->getDependants();
		$end_date = new CWorkCalendar(0, "","",$this->task_end_date );
		if ( $dep )
		{
			foreach ( $dep as $dep_task_id )
			{
				if ($t->load( $dep_task_id )){
/*  reacomodar las fechas de las tareas dependientes de la que estoy actualizando  */
					$update_start_end = false;
					$update_end_start = false;
					echo "<p>Actualizando las dependendientes de la tarea <b>'$this->task_name'</b></p>";
					$st = new CWorkCalendar(0, "","",$t->task_start_date );
					$ed = new CWorkCalendar(0, "","",$t->task_end_date );

					switch($t->task_constraint_type){
					case "1": // MSO
						$update_start_end = true;
						$st->setDate( $t->task_constraint_date );
						break;
					case "3": // ASAP
						$update_start_end = true;
						$st->setDate( $end_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP );
						break;
					case "5": // SNET
						$update_start_end = true;
						$st->setDate( $t->task_constraint_date , DATE_FORMAT_TIMESTAMP );
						// solo si la fecha de fin de la tarea es posterior a la constraint date
						if ( $end_date->dateDiff( $st, 1) < 0 )
							$st->setDate( $end_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP );
						break;
					case "6": // SNLT
						$update_start_end = true;
						$st->setDate( $t->task_constraint_date , DATE_FORMAT_TIMESTAMP );
						// solo si la fecha de fin de la tarea es anterior a la constraint date
						if ( $end_date->dateDiff( $st, 1) > 0 )
							$st->setDate( $end_date->format(FMT_TIMESTAMP), DATE_FORMAT_TIMESTAMP );
						break;

					case "2": // MFO
						$update_end_start = true;
						$ed->setDate( $t->task_constraint_date , DATE_FORMAT_TIMESTAMP );
						break;
					}

					echo "&nbsp;&nbsp;&nbsp;&nbsp;".$t->task_name." - ";
					if ( $update_start_end ){
						echo "update_start_end";
						$st->fitDateToCalendar();
						$t->task_start_date = $st->format( FMT_TIMESTAMP );
						if($t->task_duration_type==1)
							$st->addHours( $t->task_duration );
						else
							$st->addDays( $t->task_duration );

						$t->task_end_date = $st->format( FMT_TIMESTAMP );
						echo " ( $t->task_start_date ) -- ( $t->task_end_date )";
						$t->store(false, true);
					}
					if ( $update_end_start ){
						echo "update_end_start";
						$ed->fitDateToCalendar();
						$t->task_end_date = $ed->format( FMT_TIMESTAMP );
						if($ed->task_duration_type==1)
							$ed->addHours( -1 * $t->task_duration );
						else
							$ed->addDays( -1 * $t->task_duration );

						$t->task_start_date = $ed->format( FMT_TIMESTAMP );
						echo " ( $t->task_start_date ) -- ( $t->task_end_date )";
						$t->store(false, true);
					}

					echo "<br>";
				}
			}
		}
	} //end updateDependants

	/**
	*	Update the shcedule of the tasks that depends of the current task
	*
	*	@author	Rodrigo Fuentes
	*	@return	string	comma delimited list of tasks id's
	**/
	function updateChildren(){
		//Aca hay que volver a calcular las fechas de cada una de las dependencias.
		$t = new CTask();

		echo "<p><u>Actualizando las tareas dependientes</u></p>";
		$children = $this->getChildren();
		if ( $children )
		{
			foreach ( $children as $child )
			{
				if ($t->load( $child["task_id"] )){
					$t->store();
				}
			}
		}
	} //end updateChildren

	/**
	*	Retrieve the tasks dependencies
	*
	*	@author	handco	<handco@users.sourceforge.net>
	*	@return	string	comma delimited list of tasks id's
	**/
	function getDependencies () {
		// Call the static method for this object
		$result = $this->staticGetDependencies ($this->task_id);
		return $result;
	} // end of getDependencies ()


	/**
	*	Retrieve the parent task dependencies
	*
	*	@author	Rodrigo Fuentes
	*	@return	string	comma delimited list of tasks id's
	**/
	function getParentDependencies () {
		// Call the static method for this object
		if ($this->task_parent != $this->task_id)
			$result = $this->staticGetDependencies ($this->task_parent, true);
		else
			$result = "";
		return $result;
	} // end of getParentDependencies ()

	/**
	*	Retrieve the parent dependants task
	*
	*	@author	Rodrigo Fuentes
	*	@return	string	comma delimited list of tasks id's
	**/
	function getParentDependants ($task_) {
		// Call the static method for this object
		if ($this->task_parent != $this->task_id)
			$result = $this->staticGetDependencies ($this->task_parent, true);
		else
			$result = "";
		return $result;
	} // end of getParentDependants ()

	/**
	*	Devuelve las tareas de las cuales esta es dependiente en algun grado
	*
	*	@author Mauro
	*	@return string igual que el metodo getDependencies, pero toma todas las dependencias transitivas
	*
	**/
	function getTransitiveDependencies()
	{
		$directas = $this->getDependencies();
		$ret = "";

		if ( $directas != "" )
		{
			//echo "<p>Dependencias de la tarea '$this->task_name': $directas</p>";
			$tasks = explode( ",", $directas );
			$t = new CTask();

			$ret = $directas;
			//echo "<p>ret = $ret</p>";
			foreach ( $tasks as $task_id )
			{
				if ( $task_id )
				{
					$t->load( $task_id );
					$aux = $t->getTransitiveDependencies();
					if ( $aux != "" )
					{
						$ret .= ",".$aux;
					}
				}
			}
		}
		return $ret;
	}
	//}}}

	//{{{ staticGetDependencies ()
	/**
	*	Retrieve the tasks dependencies
	*
	*	@author	handco	<handco@users.sourceforge.net>
	*	@param	integer	ID of the task we want dependencies
	*	@return	string	comma delimited list of tasks id's
	**/
	function staticGetDependencies ($taskId, $retrieve_all=false) {

		$parent_hash_dep = array();
		if ($retrieve_all){
			/* si la tarea es hija de otra debe obtener las dependencias de esa tambien */
			$sql = "select task_parent from tasks where task_id = '$taskId'";
			$parent_task = db_loadResult($sql);

			if ($parent_task != $taskId){
				$result = CTask::staticGetDependencies($parent_task, true);
				$parent_hash_dep = explode(",", $result);
			}
		}

		$sql = "SELECT dependencies_req_task_id
            FROM task_dependencies td
            WHERE td.dependencies_task_id = '$taskId'";
		$hashList = db_loadHashList ($sql);
		/* uno las dependencias propias con las de sus padres*/
		$hashList = arrayMerge($parent_hash_dep, $hashList);
		$result = implode (',', array_keys ($hashList));

		return $result;
	} // end of staticGetDependencies ()

	function getDependants($task_id=NULL, $parent_dependats=false)
	{
		if ($task_id === NULL){
			if (@$this->task_id > 0){
				$task_id = $this->task_id;
			}else{
				return array();
			}
		}

		$parent_hash_dep = array();
		if ($parent_dependats){
			/* si la tarea es hija de otra debe obtener las que dependen de esa tambien */
			$sql = "select task_parent from tasks where task_id = '$task_id'";
			$parent_task = db_loadResult($sql);

			if ($parent_task != $task_id && $parent_task > 0){
				$parent_hash_dep = CTask::getDependants($parent_task, true);
			}
		}


		$sql = "SELECT dependencies_task_id
            FROM task_dependencies td
            WHERE td.dependencies_req_task_id = '$task_id'";

		$directs = db_loadColumn($sql);

        return array_unique(array_merge($parent_hash_dep, $directs));
	}

	function getTransitiveDependants()
	{
		$directas = $this->getDependants();

		$deps = array();

		$t = new CTask();

		foreach ( $directas as $task_id )
		{
			$t->load( $task_id["dependencies_task_id"] );
			$indirectas = $t->getTransitiveDependants();
			$deps = array_merge( $deps, $indirectas );
		}

		return array_merge( $directas, $deps );
	}

	/**
	*	Retrieve the list of posible task that can be predecesors of a task with the given parent task
	*
	*	@author			Rodrigo Fuentes
	*	@task_project	integer	ID of the project
	*	@task_parent	integer	ID of the parent task
	*	@task_id		integer ID of the task
	* 	@desc 			Devuelve un array con la lista de tareas que pueden ser asignadas como predecesoras
	**/
	function getListPosibleDependences($task_project=NULL, $task_parent=NULL, $task_id=NULL){
		if ($task_id === NULL){
			if (@$this->task_id > 0){
				$task_id = $this->task_id;
			}
		}
		if ($task_parent === NULL){
			if (@$this->task_parent > 0){
				$task_parent = $this->task_parent;
			}elseif ($task_id !== NULL){
				return "";
			}
		}
		if ($task_project === NULL){
			if (@$this->task_project > 0){
				$task_project = $this->task_project;
			}
		}
		if ($task_project=== NULL)
			return false;

		/*$task_project = db_loadResult("select max(task_project)
										from tasks
										where task_id in ('$task_id','$task_parent')");*/
		$direct_dependants = array();
		$children=array();
		if ($task_id!==NULL){
			$direct_dependants = ctask::getDependants($task_id);
			$tmp_children = CTask::getChildren($task_id, true);
			for ($i=0; $i < count($tmp_children); $i++){
				$children[] = $tmp_children[$i]["task_id"];
			}
			$children = array_unique($children);

		}


		$parent_dependants = array();
		if ($task_parent!==NULL)
			$parent_dependants = ctask::getDependants($task_parent, true);


		$dependants = array_merge(array_merge($direct_dependants, $parent_dependants), $children);
		$dependants = array_unique($dependants);
			// las hijas y sucesoras de las dependientes tampoco pueden ser dependientes
			$children=array();
			for ($i=0; $i < count($dependants); $i++){
				$tmp_children = CTask::getChildren($dependants[$i], true);


				for ($j=0; $j < count($tmp_children); $j++){
					$children[] = $tmp_children[$j]["task_id"];
				}
				$indirect_dependants = ctask::getDependants($dependants[$i], true);
				for ($j=0; $j < count($indirect_dependants); $j++){
					$children[] = $indirect_dependants[$j];
				}
			}
			$children = array_unique($children);
			$dependants = array_merge($dependants, $children);
			$dependants = array_unique($dependants);

		$sql="
		SELECT task_id, task_name
		FROM tasks
		WHERE 	task_project = '$task_project'
		AND 	task_id <> '$task_id' AND ".
		(count($dependants) ? " task_id not in (".implode(",", $dependants).")":"1=1")
		." GROUP BY task_parent ,task_wbs_level, task_wbs_number
           ORDER BY task_wbs_level, task_wbs_number
		";

		//echo "<pre>".$sql."</pre>";

		$possible =  db_loadHashList($sql);

		foreach($possible as $key => $val )
		{
		   $sql = "SELECT * FROM tasks WHERE task_id = '$key' ";
		   $res = db_exec( $sql );
		   $row = db_fetch_array( $res );

		   $wbs = wbs($row);
		   $pos_par[$key] = $wbs." - ".$row[task_name];

		}

		/*if(count($pos_par)>0){
        asort($pos_par);
		}*/

		$possible = $pos_par;

		return $possible;

	} //getListPosibleDependences

	/**
	*	Retrieve the list of posible task that can be parent of a task
	*
	*	@author			Rodrigo Fuentes
	*	@task_project	integer	ID of the project
	*	@task_id		integer ID of the task
	* 	@desc 			Devuelve un array con la lista de tareas que pueden ser asignadas como padres
	**/
	function getListPosibleParents($task_project=NULL, $task_id=NULL){
		if ($task_id === NULL){
			if (@$this->task_id > 0){
				$task_id = $this->task_id;
			}
		}
		if ($task_project === NULL){
			if (@$this->task_project > 0){
				$task_project = $this->task_project;
			}
		}
		if ($task_project=== NULL && $task_id===NULL)
			return false;

		$children=array();
		if ($task_id!==NULL){
			/* debo quitar la posibilidad de que se asigne un hijo como padre*/
			$tmp_children = CTask::getChildren($task_id, true);
			for ($i=0; $i < count($tmp_children); $i++){
				$children[] = $tmp_children[$i]["task_id"];
			}
			$children = array_unique($children);

			/* tampoco seria correcto que se asigne una tarea que tiene a esta como predecesora como padre de la misma */
			$dependants = ctask::getDependants($task_id, true);
		}

		$unallowed = array_merge((array)$children, (array)$dependants);
		$unallowed = array_unique($unallowed);

		$sql="
		SELECT task_id, task_name
		FROM tasks
		WHERE 	task_project = '$task_project'
		AND ".
		(count($unallowed) ? " task_id not in (".implode(",", $unallowed).")":"1=1")
		." GROUP BY task_parent ,task_wbs_level, task_wbs_number
           ORDER BY task_wbs_number, task_wbs_level
		";

		$possible =  db_loadHashList($sql);

		foreach($possible as $key => $val )
		{
		   $sql = "SELECT * FROM tasks WHERE task_id = '$key' ";
		   $res = db_exec( $sql );
		   $row = db_fetch_array( $res );

		   $wbs = wbs($row);
		   $pos_par[$key] = $wbs." - ".$row[task_name];

		}

		if(count($pos_par)>0){
		asort($pos_par);
		}

	    $possible = $pos_par;

		return $possible;

	} //getListPosibleParents

	function notifyOwner() {
		GLOBAL $AppUI, $locale_char_set;

		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;

		$mail->Subject( "$projname::$this->task_name ".$AppUI->_($this->_action), $locale_char_set);

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\nc.user_email as creator_email,"
		."\nc.user_first_name as creator_first_name,"
		."\nc.user_last_name as creator_last_name,"
		."\no.user_email as owner_email,"
		."\no.user_first_name as owner_first_name,"
		."\no.user_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\na.user_email as assignee_email,"
		."\na.user_first_name as assignee_first_name,"
		."\na.user_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			$body .= "\n\n" . $AppUI->_('Creator').":" . $AppUI->user_first_name . " " . $AppUI->user_first_name;

			$body .= "\n\n" . $AppUI->_('Progress') . ": " . $this->task_manual_percent_complete . "%";
			$body .= "\n\n" . dPgetParam($_POST, "task_log_description");


			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name
				. '" <' . $AppUI->user_email . '>'
			);
		}

		if ($mail->ValidEmail($users[0]['owner_email'])) {
			$mail->To( $users[0]['owner_email'], true );
			$mail->Send();
		}

		return '';
	}

	function notify() {
		GLOBAL $AppUI, $locale_char_set;

		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;

		$mail->Subject( "$projname::$this->task_name ".$AppUI->_($this->_action), $locale_char_set);

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\nc.user_email as creator_email,"
		."\nc.user_first_name as creator_first_name,"
		."\nc.user_last_name as creator_last_name,"
		."\no.user_email as owner_email,"
		."\no.user_first_name as owner_first_name,"
		."\no.user_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\na.user_email as assignee_email,"
		."\na.user_first_name as assignee_first_name,"
		."\na.user_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			if ($users[0]['creator_email']) {
				$body .= "\n\n" . $AppUI->_('Creator').":"
					. "\n" . $users[0]['creator_first_name'] . " " . $users[0]['creator_last_name' ]
					. ", " . $users[0]['creator_email'];
			}
			$body .= "\n\n" . $AppUI->_('Owner').":"
				. "\n" . $users[0]['owner_first_name'] . " " . $users[0]['owner_last_name' ]
				. ", " . $users[0]['owner_email'];

			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name
				. '" <' . $AppUI->user_email . '>'
			);
		}

		foreach ($users as $row) {
			if ($row['assignee_id'] != $AppUI->user_id) {
				if ($mail->ValidEmail($row['assignee_email'])) {
					$mail->To( $row['assignee_email'], true );
					$mail->Send();
				}
			}
		}
		return '';
	}

	function getTaskDetails(){
		$rta=array();
		$sql = "
		SELECT tasks.*,
			project_name, project_color_identifier,
			u1.user_username as username,
			sum(timexp_value) log_hours_worked
		FROM tasks
		LEFT JOIN users u1 ON u1.user_id = task_owner
		LEFT JOIN projects ON project_id = task_project
		LEFT JOIN timexp as horas ON task_id = timexp_applied_to_id
					and timexp_applied_to_type = 1
					and timexp_type = 1
		WHERE tasks.task_id = $this->task_id
		and 	timexp_last_status in (0, 1, 3)
		GROUP BY task_id
		";

		$rta['Detail'] = db_loadList( $sql );
		db_loadObject( $sql, $this, true );

		// get the users on this task
		$sql = "
		SELECT u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email
		FROM users u, user_tasks t
		WHERE t.task_id = $this->task_id AND
			t.user_id = u.user_id
		ORDER by u.user_last_name, u.user_first_name
		";
		$rta['users'] = db_loadList( $sql );

		//Pull files on this task
		$sql = "
		SELECT file_id, file_name, file_size,file_type
		FROM files
		WHERE file_task = $this->task_id
			AND file_task <> 0
		ORDER by file_name
		";
		$rta['files'] = db_loadList( $sql );

		$sql = "
		SELECT t.task_id, t.task_name
		FROM tasks t, task_dependencies td
		WHERE td.dependencies_task_id = $this->task_id
		AND t.task_id = td.dependencies_req_task_id
		";
		$rta['dependencies'] = db_loadHashList( $sql );

		$rta['contacts'] = array();
		if($this->task_contacts != ""){
			$sql="
			select contact_id, contact_first_name, contact_last_name,
			contact_email, contact_phone, contact_department
			from contacts
			where contact_id in ( $this->task_contacts )
			and (contact_owner = '$AppUI->user_id' or contact_private='0')";
			$rta['contacts'] = db_loadHashList( $sql , "contact_id");
		}

		return $rta;
	}

/**
* Returns the list of available tasks
*		Input Variables:
*			project_id		int				filter the selected project
*			tasks_filter		array
*										(0)string Filter type
*										(1)array	(Filter fields)=> filter value
*/
	function getTasksList($tasks_filter, $user_id=0, $company_id=0, $project_id=0, $task_id=0, $task_status="0", $paged_results=false){
		GLOBAL $AppUI;

		$user_id = @$user_id != 0 ? $user_id : $AppUI->user_id;


/*		// filter tasks for not allowed projects
		$where = '';
		$join = winnow( 'projects', 'project_id', $where );*/
		$select = "
		tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
		task_priority, task_manual_percent_complete, task_duration,
		task_duration_type, task_project, task_description, task_owner, user_username,
		task_milestone, sum(timexp_value) task_worked_hours_old, task_hours_worked task_worked_hours,
		task_work";
		$from = "tasks";
		$join = "\n\tLEFT JOIN projects ON projects.project_id = task_project";
		$join .= "\n\tLEFT JOIN users as usernames ON task_owner = usernames.user_id";
		$join .= "\n\tLEFT JOIN timexp as horas ON timexp_applied_to_id = tasks.task_id
						and timexp_applied_to_type = 1 and timexp_type = 1 and timexp_last_status in (0, 1, 3)";
		$where = " task_project = projects.project_id";
		$where .= @$company_id != 0 ? "\n\tAND project_company = $company_id" : '';
		$where .= @$project_id != 0 ? "\n\tAND task_project = $project_id" : '';



		switch ($tasks_filter) {
			case 'all':
				break;
			case 'children':
				$where .= "\n	AND task_parent = ".$task_id." AND task_id != ".$task_id;
				break;
			case 'myproj':
				//$where .= "\n	AND project_owner = ".$user_id;
				$join .= "\n\tINNER JOIN project_owners ON projects.project_id = project_owners.project_id";
				$where .= "\n	AND ( projects.project_owner = ".$user_id."	OR	project_owners.project_owner = ".$user_id." )";
				break;
			case 'mycomp':
				$company_id = @$company_id != 0 ? $company_id : $AppUI->user_company;
				$where .= "\n	AND project_company = ".$company_id;
				break;
			case 'myunfinished':
				$from = "user_tasks, ".$from;
				// This filter checks all tasks that are not already in 100%
				// and the project is not on hold nor completed
				$where .= "
							AND task_project             = projects.project_id
							AND user_tasks.user_id       = $user_id
							AND user_tasks.task_id       = tasks.task_id
							AND task_manual_percent_complete < '100'
							AND projects.project_active  = '1'
							AND projects.project_status != '4'
							AND projects.project_status != '5'";
				break;
			case 'allunfinished':
				$from = "user_tasks, ".$from;
				$where .= "
							AND task_project             = projects.project_id
							AND user_tasks.task_id       = tasks.task_id
							AND task_manual_percent_complete < '100'
							AND projects.project_active  = '1'
							AND projects.project_status != '4'
							AND projects.project_status != '5'";
				break;
			case 'unassigned':
				$join .= "\n\t LEFT JOIN user_tasks ON tasks.task_id = user_tasks.task_id";
				$where .= "
							AND task_status > -1
							AND user_tasks.task_id IS NULL";
				break;
			default:
				$from = "user_tasks, ".$from;
				$where .= "
			AND task_project = projects.project_id
			AND user_tasks.user_id = $user_id
			AND user_tasks.task_id = tasks.task_id";
				break;
		}

		$where .= "\n	AND task_status = '$task_status'";

	// get any specifically denied tasks
		$obj = new CTask();
		$deny = $obj->getDeniedRecords( $user_id );
		$where .= count($deny) > 0 ? "\n\tAND tasks.task_id NOT IN (" . implode( ',', $deny ) . ')' : '';

	// assemble query
		$sql = "SELECT DISTINCT $select FROM $from $join WHERE $where
				GROUP BY task_id
				ORDER BY projects.project_name, task_wbs_level, task_wbs_number";
	 //echo "<pre>$sql</pre>";
	// execute and return
		if ($paged_results){
			$dp = new DataPager($sql, $paged_results);
			$dp->showPageLinks = true;
			$rows = $dp->getResults();
			$pager_links = $dp->RenderNav();
			return array ( "rows" => $rows,
						   "pager_links"=>$pager_links);
		}else{
			return db_loadList($sql);
		}
		//return array();
	}

/**
* @param Date Start date of the period
* @param Date End date of the period
* @param integer The target company
*/
	function getTasksForPeriod( $start_date, $end_date, $company_id=0, $project_id=0, $user_id=0, $tasklist=0 ) {
		GLOBAL $AppUI;

		$uid= @$user_id != 0 ? $user_id : $AppUI->user_id;

	// convert to default db time stamp
		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_end = $end_date->format( FMT_DATETIME_MYSQL );

		// filter tasks for not allowed projects
		$tasks_filter = '';
		$join = winnow('projects', 'task_project', $tasks_filter);

	// assemble where clause
		$where = "task_project = project_id"
			. "\n\tAND ("
			. "\n\t\t(task_start_date <= '$db_start' AND task_end_date >= '$db_end')"
			. "\n\t\tOR (task_start_date BETWEEN '$db_start' AND '$db_end')"
			. "\n\t\tOR (task_end_date BETWEEN '$db_start' AND '$db_end')"
			. "\n\t)";

		$where .= $company_id ? "\n\tAND project_company = $company_id" : '';
		$where .= $project_id ? "\n\tAND project_id = $project_id" : '';


        if ($tasklist ==0)
        {
		    // get any specifically denied tasks
			$obj = new CTask();
			$deny = $obj->getDeniedRecords( $uid );
			$where .= count($deny) > 0 ? "\n\tAND task_id NOT IN (" . implode( ',', $deny ) . ')' : '';
        }else{
        	if ($tasklist !="")
        	{
        	    $where .= "\n\tAND task_id IN (" . $tasklist . ")";
        	}
        }

	    // assemble query
		$sql = "SELECT distinct(task_id),task_name, task_start_date, task_end_date,"
			. "\n\ttask_duration, task_duration_type,"
			. "\n\tproject_color_identifier AS color,"
			. "\n\tproject_name,"
			. "\n\ttask_work"
			. "\nFROM tasks"
		    . "\n$join"
		    ."\n,projects"
			. "\nWHERE $where"
			. "\nORDER BY task_start_date";
		//Habria que ver si aca tambien se quieren ordenar las tareas por el wbs_id
                    //echo "<pre>$sql</pre>";

	    // execute and return
		return db_loadList( $sql );
	}

	function canAccess( $user_id ) {
		$perm=$this->getTaskAccesses(null, $user_id);
		return $perm["read"];

		//$deny = $this->getDeniedRecords($user_id);
		//echo "Tarea: $this->task_id; canAccess($user_id) = ".!(in_array($this->task_id,$deny));
		//return !(in_array($this->task_id,$deny));

/*
		//echo intval($this->task_access);
		switch ($this->task_access) {
			case 0:
				// public
				return true;
				break;
			case 1:
				// protected
				$sql = "SELECT user_company FROM users WHERE user_id=$user_id";
				$user_company = db_loadResult( $sql );
				$sql = "SELECT user_company FROM users WHERE user_id=$this->task_owner";
				$owner_company = db_loadResult( $sql );
				//echo "$user_company,$owner_company";die;

				$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
				$count = db_loadResult( $sql );
				return (($owner_company == $user_company && $count > 0) || $this->task_owner == $user_id);
				break;
			case 2:
				// participant
				$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
				$count = db_loadResult( $sql );
				return ($count > 0 || $this->task_owner == $user_id);
				break;
			case 3:
				// private
				return ($this->task_owner == $user_id);
				break;
		}
		*/

	}

	function getUserRol($user_id){
		//	OUTPUT VALUES
		//------------------------------
		//	null	if no rol
		//	3			- owner
		//	2			-	asigned to the task
		//	1			- with access to the proyect
		//	0			- belongs to the same company
		$rol=null;
		if($user_id == $this->task_owner){
			$rol = 4;
		}else{
			$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
			$assignedToTask = (db_loadResult( $sql )== 1) ? true : false;
			$canEditProject=!getDenyEdit( 'projects', $this->task_project );
			$sql = "SELECT count( * ) FROM projects INNER JOIN users ON user_company = project_company WHERE project_id = $this->task_project AND user_id = $user_id ";
			$sameCompany = (db_loadResult( $sql ) == 1) ? true : false;
			if ($assignedToTask ){
				$rol = 3;
			}elseif($canEditProject){
				$rol = 2;
			}elseif($sameCompany){
				$rol = 1;
			}
		}
		return $rol;
	}

	function listTasksForRole($role_id=NULL, $task_id=NULL, $user_id=NULL){
		$where = "\n\t WHERE 1=1 ";
		if (!(is_null($user_id))){
			$where .= "\n\tAND us.user_id = $user_id";
		}
		if (!(is_null($task_id))){
			$where .= "\n\tAND ta.task_id = $task_id";
		}

		$sql[1]="
		/* display the company wide users */
		select distinct 1 role_id, task_id, user_id , ta.task_access, ta.task_project
		from 	tasks ta inner join projects on task_project = project_id
			inner join users us on project_company = user_company
		$where";
		$sql[2]="
		/*display the project wide users*/
		select distinct 2 role_id, ta.task_id, us.user_id , ta.task_access, ta.task_project
		from 	tasks ta inner join tasks ta2 on ta.task_project = ta2.task_project
			inner join user_tasks us on ta2.task_id = us.task_id
		$where";
		$sql[3]="
		/*display the participant users of each task*/
		select distinct 3 role_id, ta.task_id, us.user_id , ta.task_access, ta.task_project
		from 	tasks ta
			inner join user_tasks us on ta.task_id = us.task_id
		$where";
		$sql[4]="
		/*display the task owners*/
		select distinct 4 role_id, task_id, user_id, ta.task_access, ta.task_project
		from 	tasks ta inner join users us on task_owner = user_id
		$where";

		if (!(is_null($role_id)) and array_key_exists($role_id, $sql)){
			$query = $sql[$role_id];
		}else{
			$query = implode("\n union \n",$sql);
		}
		//echo "<pre>$query</pre>";
		$rta=db_loadList( $query );
		return $rta;
	}

	/**
	* Function that obtain the denied tasks for the user
	*
	*/
	function getDeniedRecords($uid){
		global $AppUI;
		// get the permissions for the user over all tasks
		$deniedtasks = $AppUI->pmPermissions["deniedtasks$uid"];
		if (isset($deniedtasks)){
			//echo "LEE: deniedtasks de AppUI<br>";
			return $deniedtasks;
		}
		//echo "LEE: deniedtasks de la BD<br>";
		$perms = CTask::getPermissions( $uid );
		//echo "<pre>";var_dump($perms);echo "</pre>";
		$readabletasks=array();
		for($i=0; $i< count($perms); $i++){
			//check tasks permissions to read
			if ($perms[$i]['task_permission_on']==1 && in_array($perms[$i]['task_permission_value'],array(-2,-1,1))){
				$readabletasks[]=$perms[$i]['task_id'];
			}
		}
		//echo "<pre>";var_dump($readabletasks);echo "</pre>";
		$where = count($readabletasks)>0 ? "\n\tAND task_id not IN (".implode($readabletasks,',' ).')':'';

		$deny = array();
		$sql = "
		SELECT DISTINCT task_id
		FROM tasks, permissions
		WHERE permission_user = $uid AND (
			not (		permission_grant_on = 'all'
							AND permission_item = -1
							AND permission_value in (-1,1))
			or not(	permission_grant_on = 'projects'
							AND permission_item = -1
							AND permission_value in (-1,1))
			) $where
		";
		/*$sql = "
		SELECT DISTINCT task_id
		FROM tasks, permissions
		WHERE permission_user = $uid AND (
			not (		permission_grant_on = 'all'
							AND permission_item = -1
							AND permission_value in (-1,1))
			or not(	permission_grant_on = 'tasks'
							AND permission_item = task_id
							AND permission_value in (-1,1))
			) $where
		";*/
		//if ($uid==6)
		//	echo "<pre>$sql</pre>";
		$deniedtasks = db_loadColumn( $sql );
		$AppUI->pmPermissions["deniedtasks$uid"] = $deniedtasks;
		return $deniedtasks;
	}

	/**
	* Function that returns the permissions
	* on a task for a user
	*/
	function getPermissionTask( $user_id  , $task_id=null){
		global $AppUI;
		$rta=Array();

		if(is_null($task_id) && !@$this->task_id){
			return null;
		}
		$task_id = ! is_null($task_id) ? $task_id : $this->task_id;


		// get the permissions for the user over all tasks
		/*
		$taskpermissions = $AppUI->pmPermissions["taskpermissions$task_id"];
		if (isset($taskpermissions)){
			return $taskpermissions;
		}
*/
                       // echo "permisos en memoria: <pre>";  print_r($AppUI->pmPermissions); echo "</pre>";
                       
		$perm=CTask::getPermissions($user_id,0,0,0,$task_id);
		
		//echo "perm(user: $user_id , task_id: $task_id): <pre>";print_r($perm); echo "</pre>";
		
		$items=CTaskPermission::getItemsPermission();
		//echo "items: <pre>";print_r($items); echo "</pre>";
		/*for($i=0; $i< count($perm); $i++){
			echo "if (perm[".$i."]['task_id']==".$task_id."){<br>";
			if ($perm[$i]['task_id']==$task_id){
				echo "itid = ".$perm[$i]['task_permission_on'].";<br>";
			}
			$itid = $perm[$i]['task_permission_on'];
			$rta[$items[$itid]]=$perm[$i]['task_permission_value'];
			//}
		}*/
		foreach ($perm as $key=>$p)
		{
		     // echo "if (".$p['task_id']."==".$task_id."){<br>";
		      if ($p['task_id']==$task_id){
		      $itid = $p['task_permission_on'];
		      $rta[$items[$itid]]=$p['task_permission_value'];
		      }
		}
                        
		//echo "rta<pre>"; print_r($rta); echo "</pre>";
		//$AppUI->pmPermissions["taskpermissions$task_id"] = $rta;
		return $rta;
	}

	function getPermissions( $user_id,  $project_id=0, $access_id=0, $item_id=0, $task_id=0){
	//function getPermissions($user_id=0,  $project_id=0, $access_id=0, $item_id=0){
		global $AppUI, $nadefval;

		//echo "<pre>getPermissions( $user_id,  $project_id=0, $access_id=0, $item_id=0, $task_id=0) <br></pre>";
		// get the permissions for the user over all tasks
		if ($project_id==0 && $access_id==0 && $item_id==0 && $task_id==0){
			$taskpermissions = $AppUI->pmPermissions["taskpermissions{$user_id}_0"];
			if (isset($taskpermissions)){
				//echo "LEE: taskpermissions de AppUI<br>";
				return $taskpermissions;
			}
		} elseif ($task_id!=0 ){
			$taskpermissions = $AppUI->pmPermissions["taskpermissions{$user_id}_0"];
			if (isset($taskpermissions)){
				//echo "LEE: taskpermissions$task_id de AppUI<br>";
				//echo "<pre>"; print_r($taskpermissions); echo "</pre>";
				$exist = false;
				foreach ($taskpermissions as $key=>$p){
				      if ($p['task_id']!=$task_id){ unset($p);}
				      if ($p['task_id']==$task_id){ $exist = true; }
				}
				//echo "<pre>"; print_r($taskpermissions); echo "</pre>";
				if($exist) 
				return $taskpermissions;
			}
		}

		//echo "LEE: taskpermissions de BD<br>";
		$rta=Array();

		//establezco los items a obtener permisos
		$prmItems = array( 1, 2, 3, 4, 5);
		//obtengo para cada valor de prioridad que permiso corresponde
		$sql= "select priority_level , permission_value from permission_priorities";
		$prmPriorities = db_loadHashList($sql);


		$where = "and	(ta.task_project = $project_id or $project_id = 0)\n";
		$where.= "and	(ta.task_access = $access_id or $access_id = 0)\n";
		$where.= "and	(ti.item_id = $item_id or $item_id = 0)\n";
		$where.= "and	(ta.task_id = $task_id or $task_id = 0)\n";
		/*** Administrator permissions ***/
		$sql = "select count(*) from users where user_id = $user_id and user_type = 1";
		if (db_loadResult($sql) == 1) {
			$sql="select
					task_id
			, 		item_id task_permission_on
			, 		-1 task_permission_value
			,		ta.task_owner
			,		$user_id user_id
			, 		p.project_id
			, 		p.project_company company_id
			,		6 perm_type
			from tasks ta
				inner join projects p on ta.task_project = p.project_id
			, 		task_permission_items ti
			where  1=1
			$where
			";
			$permTable = db_loadList($sql);
			if ($project_id==0 && $access_id==0 && $item_id==0 && $task_id==0){
				$AppUI->pmPermissions["taskpermissions{$user_id}_0"] = $permTable;
			}
			return $permTable;

		} else{
			// si el usr no es administrador lleno el array de rta con valores de perm no asignados (9)
			$sql="select
					task_id
			, 		item_id task_permission_on
			, 		9 task_permission_value
			,		ta.task_owner
			,		$user_id user_id
			, 		p.project_id
			, 		p.project_company company_id
			,		0 perm_type
			from tasks ta
				inner join projects p on ta.task_project = p.project_id
			, 		task_permission_items ti
			where  1=1
			$where
			";
			$rta = db_loadList($sql);
		}
		/*** end Administrator permissions ***/


		/*** Propietario y administradores de proyecto pueden hacer todo (-1)***/
		$sql="
		/*** Propietario y administradores de proyecto pueden hacer todo (-1)***/
		select 	distinct
				ta.task_id
			,	item_id
			,	-1 permission_value
			,	ta.task_owner
		from projects p
			left join  project_owners po on p.project_id = po.project_id
			inner join tasks ta on ta.task_project = p.project_id
                         ,       task_permission_items ti
		where (po.project_owner = $user_id
		or 		p.project_owner = $user_id)
		and 	item_id in (".implode($prmItems,", ").")
		$where
		order by task_id,  item_id
		";
		$prmPrjOwner=db_loadList($sql);
		/*** FIN Propietario y administradores de proyecto ***/

		/*** Propietario de Tarea pueden hacer todo (-1)***/
		$sql="
		/*** Propietario de Tarea pueden hacer todo (-1)***/
		select 	distinct
				ta.task_id
			,	item_id
			,	-1 permission_value
		from  	task_permission_items ti
			, tasks ta
		where (ta.task_owner = $user_id)
		and 	item_id in (".implode($prmItems,", ").")
		$where
		order by task_id,  item_id
		";
		$prmTaskOwner=db_loadList($sql);
		/*** FIN  Propietario de Tarea ***/


		/*** obtengo las tareas visibles ***/
		$sql="
		select distinct
		ta.task_id
		from  user_tasks ut , tasks ta
		inner join project_roles pr on pr.project_id = ta.task_project
		where
		pr.role_id = 2 and pr.user_id = $user_id
		and 	(ta.task_access = 2
			or 	ta.task_access = 3
				and ut.task_id = ta.task_id
				and ut.user_id = pr.user_id);";
		$allowedTasks=db_loadColumn($sql);

		//incorporo esa cl?sula al where
		if (count($allowedTasks)>0)
			$where.= "and	ta.task_id in (".implode($allowedTasks,", ").") \n";

		/*** Permisos especificos de usuario ***/
		$sql="
		/*** Permisos especificos de usuario ***/
		select distinct
			task_id
		,	item_id
		,	tp.task_permission_value permission_value
		from tasks ta
		inner join task_permission_items ti
		inner join  task_permissions tp on tp.task_permission_on=ti.item_id
					and  ta.task_project=tp.task_project
		where
				item_id in (".implode($prmItems,", ").")
		and 	(ti.item_id <> 1 and ta.task_access = tp.task_access_id
				or ti.item_id = 1)
		and 	task_user_id = $user_id
		$where
		order by task_id,  item_id
		";
		$prmUser = db_loadList($sql);
		/*** FIN Permisos especificos de usuario ***/

		/*** Permisos de roles espec?icos ***/
		$sql="
		/*** Permisos de roles espec?icos ***/
			SELECT distinct
				ta.task_id
			,	ti.item_id
			,	max(pp.priority_level) priority_level
			FROM tasks ta
			inner join project_roles pr on ta.task_project = pr.project_id
				and	pr.user_id =  $user_id

			inner join roles r on r.role_id = pr.role_id
			inner join role_permissions rp on rp.role_id = r.role_id
				and	rp.project_id in (pr.project_id, -1)
				and	rp.item_id in (".implode($prmItems,", ").")
			inner join task_permission_items ti on ti.item_id = rp.item_id
				and 	(ti.item_id <> 1 and ta.task_access = rp.access_id
					or ti.item_id =1)
			inner join permission_priorities pp on pp.permission_value = rp.permission_value
			where
		/*Filtro los roles espec?icos*/
				r.role_type=1
		/*Filtro los roles activos */
			and 	r.role_status = 0
			$where
			group by task_id,  item_id;
		";


		$prmRolEsp = db_loadList($sql);
		// de acuerdo a la prioridad del valor de permiso obtengo el mismo
		for($i=0; $i<count($prmRolEsp); $i++){
			$prmRolEsp[$i]["permission_value"] = $prmPriorities[$prmRolEsp[$i]["priority_level"]];
		}


		/*** Permisos de Usuarios del Proyecto ***/
		/***
		En primer lugar se buscan los permisos para usuarios del proyecto
		que fueron configurados para el proyecto espec?ico (rpc)
		en caso de que no estuvieran los mismos definidos se recurre
		a los definidos a nivel predeterminado en el modulo system (rpg)
		***/
			$sql="
			/*** Permisos de Usuarios del Proyecto ***/
				select distinct
					ta.task_id
				, 	ti.item_id
				,	max(coalesce(ppc.priority_level, ppg.priority_level )) priority_level
				from
					task_permission_items as ti
				  	inner join project_roles as pr
                                        inner join tasks ta on pr.project_id = ta.task_project
						inner join projects p on  pr.project_id = p.project_id
						left join role_permissions rpc on rpc.item_id = ti.item_id
						and pr.role_id = rpc.role_id
						and p.project_id  = rpc.project_id
						and rpc.company_id = p.project_company
							left join permission_priorities ppc on  ppc.permission_value = rpc.permission_value
						left join role_permissions rpg on rpg.item_id = ti.item_id
						and pr.role_id = rpg.role_id
							left join permission_priorities ppg on  ppg.permission_value = rpg.permission_value
				where
					ti.item_id in (".implode($prmItems,", ").")
				and	pr.role_id = 2
				and	(rpc.access_id = -1 and ti.item_id=1 or rpc.access_id = ta.task_access)
				and 	(rpg.access_id = -1 and ti.item_id=1 or rpg.access_id = ta.task_access)
				and 	((	rpc.company_id > 0)
					or (	rpg.project_id = -1
					and 	rpg.company_id = -1 ))
				and	pr.user_id =  $user_id
				$where
				group by task_id,  item_id;
			";
			$prmUsrPrj = db_loadList($sql);
			// de acuerdo a la prioridad del valor de permiso obtengo el mismo
			for($i=0; $i<count($prmUsrPrj); $i++){
				$prmUsrPrj[$i]["permission_value"] = $prmPriorities[$prmUsrPrj[$i]["priority_level"]];
			}
		/*** FIN Permisos de Usuarios del Proyecto ***/


		$tmpperm[1]  = $prmUsrPrj;
		$tmpperm[2]  = $prmRolEsp;
		$tmpperm[3]  = $prmUser;
		$tmpperm[4]  = $prmTaskOwner;
		$tmpperm[5]  = $prmPrjOwner;
		$hashPerm=array();
		//unifico todos los permisos obtenidos segun la prioridad
		foreach($tmpperm as $j => $perms){
			for($i=0; $i<count($perms); $i++){
				$tid = $perms[$i]["task_id"];
				$item = $perms[$i]["item_id"];
				$pv = $perms[$i]["permission_value"];
				if ( $pv <> 9 ){
					//if ( $item = 1 && $j <= 4
					$hashPerm[$tid][$item]["pv"] = $pv;
					$hashPerm[$tid][$item]["pt"] = $j;
				}
			}
		}

		for($i=0; $i<count($rta); $i++){
			$tid = $rta[$i]["task_id"];
			$item = $rta[$i]["task_permission_on"];
			if (isset($hashPerm[$tid][$item])){
				$rta[$i]["task_permission_value"] =  $hashPerm[$tid][$item]["pv"] ;
				$rta[$i]["perm_type"] =  $hashPerm[$tid][$item]["pt"] ;

				/*
				if ($item==1 && $rta[$i]["task_owner"]!=$rta[$i]["user_id"] && $hashPerm[$tid][$item] = -1)
					$rta[$i]["task_permission_value"] =  1 ;
				else
					$rta[$i]["task_permission_value"] =  $hashPerm[$tid][$item] ;
					*/
			}
		}
		//echo "<pre>VARDUMPUING</pre>";
		//var_dump($rta);
		//echo "<pre>VARDUMPUING<br>";
		//var_dump($rta);
		//echo "</pre>";
		if ($project_id==0 && $access_id==0 && $item_id==0 && $task_id==0){
			$AppUI->pmPermissions["taskpermissions{$user_id}_0"] = $rta;
		}
		return $rta;

	}

	/**
	* Function that returns the amount of hours this
	* task consumes per user each day
	*/
	function getTaskDurationPerDay(){
		/*
		$duration              = $this->task_duration*$this->task_duration_type;
		$task_start_date       = new CDate($this->task_start_date);
		$task_finish_date      = new CDate($this->task_end_date);
		*/
		$task_start_date       = new CWorkCalendar(2, $this->task_project,"", $this->task_start_date);
		$task_finish_date      = new CWorkCalendar(2, $this->task_project,"", $this->task_end_date);

		$assigned_users = $this->getAssignedUsers();
		$number_assigned_users = count($this->getAssignedUsers());
		$total_units = 0;
		if ($number_assigned_users > 0 ){
			foreach ($assigned_users as $user_id => $user_data) {
				extract($user_data, "");
				$total_units += $user_units;
			}
			$total_units = $total_units / 100;
			$total_days = $task_start_date->dateDiff($task_finish_date, 1 );
			if ($total_days == 0 || $total_units == 0 || $this->task_work == 0) return 0;

			return ( $this->task_work / $total_days ) / $total_units;
		}else{
			return 0;
		}
		/*
		$day_diff              = $task_finish_date->dateDiff($task_start_date);
		$number_of_days_worked = 0;
		$actual_date           = $task_start_date;

		for($i=0; $i<=$day_diff; $i++){
			if($actual_date->isWorkingDay()){
				$number_of_days_worked++;
			}
			$actual_date->addDays(1);
		}
		// May be it was a Sunday task
		if($number_of_days_worked == 0) $number_of_days_worked = 1;
		if($number_assigned_users == 0) $number_assigned_users = 1;
		return ($duration/$number_assigned_users) / $number_of_days_worked;*/
	}

	function getAssignedUsers($task_id=0){
		$task_id = $task_id ? $task_id : $this->task_id;
		if ($task_id){
			$sql = "select u.*, ut.user_units
			        from users as u, user_tasks as ut
			        where ut.task_id = '$task_id'
			              and ut.user_id = u.user_id
					order by user_first_name";
			return db_loadHashList($sql, "user_id");
		}else{
			return false;
		}

	}

	//Returns task children IDs
	/**
	* @return unknown
	* @param unknown $task_id
	* @desc Devuelve un array con la lista de tareas hijas (
	*/
	function getChildren($task_id="", $transitive_children=false) {
		if ($task_id==""){
			$msg = $this->check();
			if( $msg )
			{
				return false;
			}
			$task_id = $this->task_id;
		}

		$sql = "select task_id from tasks where task_id != '$task_id'
				and task_parent = '$task_id'";
		$children = db_loadList($sql);


		if ($transitive_children){
			$t_children = array();
			for ($i=0; $i<count($children); $i++){

				$t_children = array_merge($t_children, CTask::getChildren($children[$i]["task_id"], true));
			}
			$children = array_merge($children, $t_children);
		}

		return $children;
	}

	/**
	* This function, recursively, updates all tasks status
	* to the one passed as parameter
	*/
	function updateSubTasksStatus($new_status, $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}

		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";

		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;

		$sql = "update tasks set task_status = '$new_status' where task_parent = '$task_id'";

		db_exec($sql);
		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksStatus($new_status, $id);
			}
		}
	}

/*
	Function: getTaskAccesses

	Aparentemente carga todos los permisos de la tarea en el vector que devuelve.

   Parameters:

	$task_id - Por defecto =NULL
	$user_id - Por defecto =NULL

   Returns:

      rta - Un vector con los permisos de la tarea

*/
	function getTaskAccesses($task_id=null, $user_id=null){
		global $AppUI, $debuguser;
                        
		if(is_null($task_id) && !isset($this)){
			return null;
		}
		if(is_null($user_id) && !isset($AppUI)){
			return null;
		}
		
		$task_id = is_null($task_id) ? $this->task_id : $task_id;
		$user_id = is_null($user_id) ? $AppUI->user_id : $user_id;
                        
		$perm=CTask::getPermissionTask($user_id, $task_id);
                        //if ($debuguser)  echo "Usuario: $user_id <pre>"; print_r($perm); echo "</pre><br />";
                        
		$accessTask = $perm["Task"] ? $perm["Task"] : PERM_DENY;
		$accessDetail = $perm["Detail"] ? $perm["Detail"] : PERM_DENY;
		$accessLog = $perm["Log"] ? $perm["Log"] : PERM_DENY;
		$accessExpense = $perm["Expense"] ? $perm["Expense"] : PERM_DENY;
		$accessValues = $perm["Ec.Values"] ? $perm["Ec.Values"] : PERM_DENY;

		$canRead = $accessTask != PERM_DENY;
		//$canCreate = $accessTask == PERM_EDIT;
		$canEdit = $perm["Detail"] == PERM_EDIT || $perm["Ec.Values"] == PERM_EDIT;

		/*
		$sql = "select project_owner
		from projects inner join tasks on task_project=project_id
		where task_id = $task_id;";
		$canManageUsers = $accessTask==PERM_CHANGE || db_loadResult($sql)==$user_id;*/

		$rta=array();
		$rta["read"]=$canRead;
		$rta["edit"]=$canEdit;
		//$rta["create"]=$canCreate;
		$rta["detail"]=$accessDetail;
		$rta["log"]=$accessLog;
		$rta["expense"]=$accessExpense;
		$rta["values"]=$accessValues;
		//$rta["users"]= $canManageUsers ;
		$rta["all"] = "-1";

		return $rta;
	}

	function getExpenses()
	{
/*		$sql = "SELECT task_expense_id FROM task_expense WHERE task_expense_task = $this->task_id";

		return db_loadList( $sql );
		*/
		$sql="select distinct te.timexp_id
				from timexp te \n\t";
		$sql .= "left join tasks ta on te.timexp_applied_to_id=ta.task_id and te.timexp_applied_to_type = 1 \n\t";
		$sql.="\n\t where te.timexp_type = 2
						and te.timexp_applied_to_type = 1
						and te.timexp_applied_to_id = \"$this->task_id\"";

		return db_loadList( $sql );

	}

	function getApprovedExpenses(){
		$sql="select distinct te.timexp_id
				from timexp te \n\t";
		$sql .= "left join tasks ta on te.timexp_applied_to_id=ta.task_id and te.timexp_applied_to_type = 1 \n\t";
		$sql.="\n\t where te.timexp_type = 2
						and te.timexp_applied_to_type = 1
						and te.timexp_applied_to_id = \"$this->task_id\"
						and te.timexp_last_status = '3'
		";

		return db_loadList( $sql );
	}

	function getWBSPrefix()
	{
		//echo "<p>Viendo el prefijo de la tarea $this->task_name</p>";
		$ret = "";
		$hija = $this;
		$padre = new CTask();
		$padre->load( $this->task_parent );
		while ( $hija->task_id != $padre->task_id )
		{
			$ret = $padre->task_wbs_number.".".$ret;
			$hija = $padre;
			$padre->load( $padre->task_parent );
		}
		return $ret;
	}

	function getWBSId()
	{
		return $this->getWBSPrefix().$this->task_wbs_number;
	}

	//Me voy a fijar en el arbol hacia arriba a ver si encuentro el ancestro
	//Devuelve true al preguntar si una tarea es ancestro de si mismo.
	//En principio sirve para listar los candidatos a padre de una tarea cuando se modifica
	//Porque si se asigna como nuevo padre un descendiente de la tarea original se hace un bardo
	function isAncestor( $ancestor_id )
	{
		//echo "<p>chequeando si la tarea '$ancestor_id' es ancestro de '$this->task_id'</p>";
		$t = $this;

		while ( $t->task_parent != $t->task_id && $t->task_id != $ancestor_id )
		{
			//echo "<p>ahora soy la tarea '$t->task_id'</p>";
			$t->load( $t->task_parent );
		}

		return $t->task_id == $ancestor_id;
	}

	function getConstraints()
	{
		$sql = "SELECT `constraint_id` FROM `task_constraints` WHERE `task_id` = $this->task_id";

		return db_loadList( $sql );
	}

	/**
	* @author Mauro
	* @param El objeto constraint que quiero chequear
	* @return true o false segun si se cumple o no el constraint
	*/
	function keepsConstraint( $cons )
	{
		$ret = true;

		if ( $cons->constraint_type != "ASAP" && $cons->constraint_type != "ALAP" )
		{
			$sd = new CDate( $this->task_start_date );
			$sd->setTime( 0, 0, 0 );
			$fd = new CDate( $this->task_end_date );
			$fd->setTime( 0, 0, 0 );
			$pd = new CDate( $cons->constraint_parameter );
			$pd->setTime( 0, 0, 0 );

			switch ( $cons->constraint_type )
			{
				case "FNET":
					$ret = CDate::compare( $fd, $pd ) >= 0;
					break;
				case "FNLT":
					$ret = CDate::compare( $fd, $pd ) <= 0;
					break;
				case "MFO":
					$ret = CDate::compare( $fd, $pd ) == 0;
					break;
				case "MSO":
					$ret = CDate::compare( $sd, $pd ) == 0;
					break;
				case "SNET":
					$ret = CDate::compare( $sd, $pd ) >= 0;
					break;
				case "SNLT":
					$ret = CDate::compare( $sd, $pd ) <= 0;
					break;
			}
		}

		return $ret;
	}

	function getNumberOfChildrens($task_id=""){

		if ($task_id==""){
			$msg = $this->check();
			if( $msg )
			{
				return false;
			}
			$task_id = $this->task_id;
		}
		// Cuando la tarea tiene hijas es din?ica
		$sql =	"select count(task_id) ".
				"from tasks ".
				"where task_parent = $task_id and task_id != task_parent";

		return db_loadResult($sql);
	}

	function getChildrensMaxMinDate($task_id="", &$min_start_date, &$max_end_date ){
		if ($task_id==""){
			$msg = $this->check();
			if( $msg )
			{
				return false;
			}
			$task_id = $this->task_id;
		}


		// Cuando la tarea tiene hijas es din?ica
		$sql =	"select concat(min(task_start_date), ',' , max(task_end_date) ) ".
				"from tasks ".
				"where task_parent = $task_id and task_id != task_parent";

		$dates = db_loadResult($sql);
		if ($dates===NULL)
			return false;

		$dates = explode(",",$dates);
		$min_start_date = $dates[0];
		$max_end_date = $dates[1];
		return true;

	}
    /**
     * Calculate the schedule of the task
     * Changed field (1-add or del resource, 2-units, 3-duration, 4-work)
     *
     * @param  Changed field (1-add or del resource, 2-units, 3-duration, 4-work)
     * @access public
     * @return true or false
     * @author Rodrigo Fuentes
     */
	function updateSchedule($changed){

		$msg = $this->check();
		if( $msg || !in_array($changed, array(1,2,3,4)))
		{
			return false;
		}

		// Si la tarea no tiene especificado el trabajo
		// hay que calcular el mismo
		if ( $this->task_work == 0 && $changed!="4" ){
			$this->calculateWork();
		}else{
			switch ($changed){
			case "1": // add or delete resources

				switch($this->task_type){
				case "1":
					if ($this->task_effort_driven){
						//echo "<br><b>Recalcula duracion</b><br>";
						$this->calculateDuration();
					}else{
						//echo "<br><b>Recalcula trabajo</b><br>";
						$this->calculateWork();
					}
					break;
				case "2":
					if ($this->task_effort_driven)
						$this->calculateUserUnits();
					else
						$this->calculateWork();
					break;
				case "3":
					$this->calculateDuration();
					break;
				}
				break;
			case "2": //user units changed
				switch($this->task_type){
				case "1":
					$this->calculateDuration();
					break;
				case "2":
					$this->calculateWork();
					break;
				case "3":
					$this->calculateDuration();
					break;
				}
				break;
			case "3": //duration changed
				switch($this->task_type){
				case "1":
					$this->calculateWork();
					break;
				case "2":
					$this->calculateWork();
					break;
				case "3":
					$this->calculateUserUnits();
					break;
				}
				break;
			case "4": //work changed
			          echo "Tipo de tarea: ".$this->task_type."<br>";
				switch($this->task_type){
				case "1":
					$this->calculateDuration();
					break;
				case "2":
					$this->calculateUserUnits();
					break;
				case "3":
					$this->calculateDuration();
					break;
				}
				break;
			}


		}
	}/*   END function updateSchedule()  */

    /**
     * Calculate start time, end time and duration of a task based on the work and user units
     *
     *
     *
     * @access public
     * @return true or false
     * @author Rodrigo Fuentes
     */
     function calculateDuration(){
            // echo "<br>-------- calculateDuration ----------------<br>";
		$msg = $this->check();

		if( $msg )
		{
			return false;
		}

		if($this->task_work == 0)
		{
			$this->task_duration = 0;
			$this->task_end_date = $this->task_start_date;
			
			return;
		}
		$remaining_work = $this->task_work;
		$accu_work = 0;

		if (count($this->_assigned_users) > 0 && $this->_assigned_users !==  NULL && $remaining_work > 0){
                                    //echo "remaining_work: ".$remaining_work."<br>";
                                    
			$users = array_keys($this->_assigned_users);
			$units = $this->_assigned_users;
			$users_day=array();
			$total_units = array_sum($units);

			for ($i=0; $i < count($users); $i++){
				$user_id = $users[$i];

				$start_date = new CWorkCalendar(3, $user_id, $this->task_project,$this->task_start_date);
			            $end_date = new CWorkCalendar(3, $user_id, $this->task_project, $this->task_start_date);

				$users_day[$user_id] = new CWorkCalendar(3,
												$user_id,
												$this->task_project,
												$this->task_start_date);
				$users_day[$user_id]->fitDateToCalendar();

				// Distribuyo el trabajo entre los usuarios en funci? a las unidades
				$user_assigned_hours[$user_id] = $remaining_work * $units[$user_id] / $total_units;
				$user_worked_hours[$user_id] = 0;

			}

			for ($i=0; $i < count($users); $i++){
				$user_id = $users[$i];

				if ($user_assigned_hours[$user_id] > $user_worked_hours[$user_id]){
					$users_day[$user_id]->addHours($user_assigned_hours[$user_id] * 100 / $units[$user_id] );

					// Si la fecha de finalizaci? del usuario es posterior
					// la seteo como fecha de finalizaci?
					$dif_tmp = $users_day[$user_id]->dateDiff($end_date, 1);
					
					if ($dif_tmp < 0){
						$dif = $dif_tmp * (-1);
						$end_date->setDate($users_day[$user_id]->format(FMT_TIMESTAMP),DATE_FORMAT_TIMESTAMP);
					}
				}

			}

			//echo "<pre>";print_r($start_date);echo "</pre>";

			//echo "<br>Fecha de inicio: ".$start_date->format(FMT_TIMESTAMP)."<br>";
			//echo "Fecha de fin: ".$end_date->format(FMT_TIMESTAMP)."<br>";

			$this->task_duration = $start_date->dateDiff($end_date, $this->task_duration_type);

			$cur_date = $end_date->format(FMT_TIMESTAMP);
                                    $end_date->fitDateToCalendar(true);

			if($this->task_duration > 0)
			{
			$end_date->hour = substr($cur_date,8,2);
                                    $end_date->minute = substr($cur_date,10,2);
			}

			$this->task_end_date = $end_date->format(FMT_TIMESTAMP);

		}
		
		if (count($this->_assigned_users) == 0)
		{
		      $this->task_work = 0;
		}

	       //echo "<br>-------- Fin calculateDuration ----------------<br>";
	}/*   END function calculateDuration()  */

	/**
     * Calculate the necessary work to complete task based on the duration of the task and user units
     *
     *
     *
     * @access public
     * @return true or false
     * @author Rodrigo Fuentes
     */
	function calculateWork(){

		$msg = $this->check();
		if( $msg )
		{
			return false;
		}

		$accu_work = 0;
		if (count($this->_assigned_users) > 0 && $this->_assigned_users !==  NULL && $this->task_duration > 0){

			$obj = new CProject();
			$obj->load( $this->task_project );

			$cpy = new CCompany();
			$cpy->load( $obj->project_company );

			$cpy->loadHollidays();
			$hollidays = $cpy->_hollidays;

			$users = array_keys($this->_assigned_users);
			$units = $this->_assigned_users;
			$users_work=array();
			$total_units = array_sum($units);

				for ($i=0; $i < count($users); $i++){
					$user_id = $users[$i];

					$user_start_date = new CWorkCalendar(3, $user_id, $this->task_project,$this->task_start_date);

					$user_end_date = new CWorkCalendar(3, $user_id, $this->task_project,$this->task_end_date);

					$user_start_date->_hollidays = $hollidays;
                    $user_end_date->_hollidays = $hollidays;

					//$user_work[$i] = $user_start_date->dateDiff($user_end_date, 1);

                  if($this->task_duration_type=='24'){
                    $user_work[$i] = $this->task_duration * 8;
                   }else{
                   	 $user_work[$i] = $this->task_duration;
                   }

					if ($user_work[$i] > 0 ){
						$accu_work += $user_work[$i] * $units[$user_id] / 100;
					}
				}
		}

		$this->task_work = $accu_work;

	}/*   END function calculateWork()  */

	/**
     * Calculate the distribution of user units necessary to complete the work of a task in a given duration
     *
     *
     *
     * @access public
     * @return true or false
     * @author Rodrigo Fuentes
     */
	function calculateUserUnits(){

		//echo "<br>------------------ Function calculateUserUnits -----------------<br>";
		$msg = $this->check();
		if( $msg )
		{
			return false;
		}

		$total_work = $this->task_work;
		$accu_work = 0;

		if (count($this->_assigned_users) > 0 && $this->_assigned_users !==  NULL && $total_work > 0){

			$users = array_keys($this->_assigned_users);
			$units = $this->_assigned_users;
			$users_work=array();
			$total_units = array_sum($units);

			for ($i=0; $i < count($users); $i++){
				$user_id = $users[$i];

				$user_start_date = new CWorkCalendar(3, $user_id, $this->task_project,$this->task_start_date);
				$user_end_date = new CWorkCalendar(3, $user_id, $this->task_project,$this->task_end_date);

				//echo "<pre>";print_r($user_end_date);echo "</pre>";

				$user_work[$i] = $user_start_date->dateDiff($user_end_date, 1);
               //$user_work[$i] = $this->task_work;

				if ($user_work[$i] > 0 ){

					$user_work[$i] = $user_work[$i] * $units[$user_id] / 100;
					$accu_work += $user_work[$i];
					//echo  $user_work[$i]." * ".$units[$user_id]."/ 100 = <br>";

				}
			}

			for ($i=0; $i < count($users) && $accu_work > 0; $i++){
				$user_id = $users[$i];
				$prop_work = $user_work[$i] / $accu_work;
				$new_work = $prop_work * $total_work;

				$units[$user_id] = $new_work * $units[$user_id] / $user_work[$i];

			}

			$this->_assigned_users = $units;
		}
		//echo "<br>------------------ Fin Function calculateUserUnits -----------------<br>";

	}/*   END function calculateUserUnits()  */

	/**
     * Load the assigned users and its units to the array $this->_assigned_users.
     * Format: $this->_assigned_users[user_id] = user_units
     *
     *
     * @access private
     * @return true or false
     * @author Rodrigo Fuentes
     */
	function loadAssignedUsers(){
		$msg = $this->check();
		if( $msg )
		{
			return false;
		}

		$sql = "select user_id, user_units
				from user_tasks
				where task_id = '$this->task_id'";
		if ( ! $assigned_users = db_loadHashList($sql))
			return false;
		else
			$this->_assigned_users = $assigned_users;

		return true;

	}/*   END function loadAssignedUsers()  */

	/**
     * Gets the minimum start date for the task given its dependences.
     * Returns false if error.
     *
     * @access private
     * @return timestamp YYYYMMDDHHMMSS
     * @author Rodrigo Fuentes
     */
	function getMinStartDate($load_dependences_from_DB = false){
		$msg = $this->check();
		if( $msg ) return false;

		$prj = new CProject();
		if (!$prj->load($this->task_project)) return false;
		$psd = new CDate($prj->project_start_date);
		$prj_start_date = $psd->format(FMT_TIMESTAMP);
		unset($psd);

		if ($this->task_parent != $this->task_id && is_numeric($this->task_parent)){
			$prt = new CTask();
			if(!$prt->load($this->task_parent)) return false;
			$parent_min_start = $prt->getMinStartDate();
		}

		if (is_null($this->_dependencies_list)){
			if (!$load_dependences_from_DB)
				return $prj_start_date;
			$this->_dependencies_list = $this->getDependencies();
		}

		$task_dep = explode( ",", $this->_dependencies_list );


		return true;

	}/*   END function loadAssignedUsers()  */
}

class CTaskConstraint extends CDpObject
{
	var $constraint_id 			= NULL;
	var $task_id				= NULL;
	var $constraint_type		= NULL;
	var $constraint_parameter	= NULL;

	function CTaskConstraint() {
		$this->CDpObject( 'task_constraints', 'constraint_id' );
	}

	function check()
	{
		switch ( $this->constraint_type )
		{
			case "ASAP":
			case "ALAP":
				break;
			default:
				if ( !$this->constraint_parameter )
				{
					return "task_parameter is NULL";
				}
				break;
		}

		return NULL;
	}
}

?>
