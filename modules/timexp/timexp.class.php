<?php /* Hours $Id: timexp.class.php,v 1.8 2009-07-14 15:29:01 nnimis Exp $ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 1.8 $
*/

require_once( $AppUI->getModuleClass( 'admin' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getSystemClass( 'libmail' ) );
include_once( "config.php");


/**
 *	Rendition Class
 *	@todo Move the 'address' fields to a generic table
 */
class CTimExp extends CDpObject {
/** @var int Primary Key */
	var $timexp_id = NULL;
/** @var string */
// these next fields should be ported to a generic address book
	var $timexp_type = NULL;	
	var $timexp_name = NULL;
	var $timexp_description = NULL;
	var $timexp_creator = NULL;
	var $timexp_date = NULL;
	var $timexp_value = NULL;
	var $timexp_applied_to_type = NULL;
	var $timexp_applied_to_id = NULL;
	var $timexp_billable = NULL;
	var $timexp_last_status = NULL;
	var $timexp_start_time = NULL;
	var $timexp_end_time = NULL;
	var $timexp_contribute_task_completion = NULL;
	var $timexp_timesheet = NULL;
	var $timexp_save_date = NULL;
	var $timexp_cost = NULL;
	var $timexp_company = NULL;
	var $timexp_expense_category = NULL;
	
	function CTimExp() {
		$this->CDpObject( 'timexp', 'timexp_id' );
	}

// overload check
	function check() {
		if ($this->timexp_id === NULL) {
			return 'timexp id is NULL';
		}
		$this->timexp_id = intval( $this->timexp_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		global $AppUI;
		
		if($this->timexp_creator == $AppUI->user_id && $this->isAvailable()){
			return true;
		}
		
		$supervised_users = CTimexpSupervisor::getSupervisedUsers();
		$is_creator = ($AppUI->user_id == $this->timexp_creator );
		$is_admin = ($AppUI->user_type == 1);
		$is_supervisor = ($this->canSupervise());
		if ( !($is_creator || $is_admin || $is_supervisor)){
			return false;
		}	
			
		if ($this->timexp_applied_to_type == 3)
			return true;
		else 	
			return $this->canEdit($msg, $oid);

	}
	
	function canRead(){
		global $AppUI;
		
		$supervised_users = CTimexpSupervisor::getSupervisedUsers();
		$is_creator = ($AppUI->user_id == $this->timexp_creator );
		$is_admin = ($AppUI->user_type == 1);
		$is_supervisor = ($this->canSupervise());
		
		//echo "<pre> $is_creator || $is_admin || $is_supervisor </pre>";
		// cuando el usuario no sea ni el creador, ni un supervisor, ni el system admin
		// no puede ver el registro
		if ( !($is_creator || $is_admin || $is_supervisor)){
			return false;
		}
		$rta = false;
		switch ($this->timexp_applied_to_type){
			case "1":
				$perms=CTask::getTaskAccesses($this->timexp_applied_to_id);
				$item =  $this->timexp_type == "1" ? "log" : "expense";
				$rta = ($perms[$item] != PERM_DENY);
				break;
			case "2":
				$rta = true;
				break;
			case "3":
				$rta = true;
				break;
		}

		return $rta;
	}


// overload canEdit
	function canEdit( &$msg, $oid=null ) {
		global $AppUI;
		
		if($this->timexp_creator == $AppUI->user_id)
		{
			if ($this->timexp_applied_to_type != 3){
			        $sql = "SELECT timexp_supervisor FROM users WHERE user_id='".$this->timexp_creator."' ";
                                            $timexp_supervisor = db_loadResult($sql);
                
			        //echo "Reporte directo 1 ".$report_direct."<br>";
			       if ($timexp_supervisor == '-1')
			       {
			        return true;
			        }
			}
		}
		
		if($this->timexp_creator == $AppUI->user_id && $this->isAvailable()){
			return true;
		}
		
		//si el registro se aplica a nada puede modificarse
		if ($this->timexp_applied_to_type == 3)
			return true;		

		if (!$this->isAvailable()){
				return false;
		} 
		
		// cuando el usuario no sea ni el creador, ni un supervisor, ni el system admin
		// no puede editar
		if ($AppUI->user_id != $this->timexp_creator ){
			return false;
		}		
		if ($AppUI->user_id != $this->timexp_creator 
			&& $AppUI->user_type != 1
			&& !$this->canSupervise()){
			return false;
		}

		$rta = false;
		switch ($this->timexp_applied_to_type){
			case "1":
				$perms=CTask::getTaskAccesses($this->timexp_applied_to_id);
				//echo "<pre>"; var_dump($perms); echo "</pre>";
				$item =  $this->timexp_type == "1" ? "log" : "expense";
				$rta = ($perms[$item] == PERM_EDIT);
				break;
			case "2":
				$rta = true;
				break;
			case "3":
				$rta = true;
				break;
			case "4":
				$rta = true;
				break;
		}
		return $rta;
	}	

	function canSupervise(){
		global $AppUI;
		$timexp_id = $this->timexp_id;
		
		if (!$timexp_id)
			return false;

		//si es system admin puede supervisar
		if ( $AppUI->user_type==1 ){
			return true;
		}

		//obtengo el tipo de supervisi? seleccionada del usuario creador
		$sql = "SELECT timexp_supervisor 
				FROM users
				WHERE user_id = $this->timexp_creator";
			//echo "<pre>";var_dump($this); echo "</pre>";
		switch (db_loadResult($sql)){
			//los project admins
			case "-1":
				$po = CUser::getOwnedProjects($AppUI->user_id);
			//echo "<pre>";var_dump($po); echo "</pre>";
				$rta = isset($po[$this->getAppliedToProjectID()]);
				break;
			// autoaprobaci?
			case "-2":
				$rta = true;
				break;
			// un usuario espec?ico
			case $AppUI->user_id:
				$rta=true;
				break;
			default:
				$rta=false;
				break;
		}
		return $rta;	
	}
	
	
	// devuelve si el registro est?disponible para asignarse a un nuevo timesheet
	function isAvailable(){
		
		return in_array($this->timexp_last_status, array(0,2,4)) &&
							($this->timexp_timesheet === NULL || 
							in_array($this->timexp_last_status, array(2,4)) );
	
	}

	function delete($oid=null){
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (!$this->canDelete( $msg )) {
			return $msg;
		}

		$sql = "DELETE FROM timexp WHERE timexp_id = '$this->timexp_id';";
  		if (!db_exec( $sql )) {
			return db_error();
		} else {	
			$sql = "DELETE FROM timexp_status WHERE timexp_id = '$this->timexp_id';";
			if (!db_exec( $sql )) {
				return db_error();
			} else {
				return NULL;
			}
		}
	}

// overload store
	function store() {
        
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}

		$k = $this->_tbl_key;
		$new = $this->$k;
		
		$save_date=new CDate();
		$this->timexp_save_date = $save_date->format(FMT_DATETIME_MYSQL);

		if( $this->$k ) {
			// verifico sa que estaba aplicado el registro que se edita
			$sql = "select timexp_applied_to_type from timexp where timexp_id = '".$this->$k."'";
			$old_app_type = db_loadResult($sql);
			
			// si se modifica la aplicacion de un registro aplicado a nada
			// cambio el estado del registro a pendiente
			if ( $old_app_type == "3" && $old_app_type != $this->timexp_applied_to_type){
				$new_status = "0";
			}
			
			//si se edita un registro y se lo aplica a nada este se debe aprobar si es que el usuario que reporta a nadie no es supervisado en reporte directo	
			if ($this->timexp_applied_to_type == 3){

				// Antes de ponerle status de aprobado, me fijo si lo supervisan o no (reporte directo) 
				$sql = "SELECT user_supervisor FROM users WHERE user_id='".$this->timexp_creator."' ";
				$report_direct = db_loadResult($sql);
                
				//echo "Reporte directo 1 ".$report_direct."<br>";
				if ($report_direct == '-1')
				{
				$new_status = "3";
				}
			}else{
				$sql = "SELECT timexp_supervisor FROM users WHERE user_id='".$this->timexp_creator."' ";
				$timexp_supervisor = db_loadResult($sql);
                
				//echo "Reporte directo 1 ".$report_direct."<br>";
				if ($timexp_supervisor == '-1')
				{
				$new_status = "3";
				}
			}
			
			if (isset($new_status) && $new_status != $this->timexp_last_status){
				global $AppUI;
				$status = new CTimExpStatus();
				$status->timexp_status_id = '';
				$status->timexp_id = $this->$k;
				$status->timexp_status_datetime = $this->timexp_save_date;
				$status->timexp_status_user = $AppUI->user_id;
				$status->timexp_status_value = $new_status;
				if (($msg = $status->store())) {
					return $msg;
				}				
				$this->timexp_last_status = $new_status;
			}

			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			// establezco el estado para nuevos registros
			$this->timexp_last_status = "0";
			$this->timexp_cost = 0;
			
			if ($this->timexp_type == 1){
				// grabo el costo hora del recurso que carga la hora
				$sql = "select user_cost_per_hour 
						from users 
						where user_id='$this->timexp_creator'";
				$cost_per_hour = db_loadResult($sql);
				$cost_per_hour = $cost_per_hour ? $cost_per_hour : 0;
				
				$this->timexp_cost = $cost_per_hour;
			}
			
			/*
			// si el registro se aplica a nada o el usuario no necesita aprobacion se 
			// ingresa aprobada
			if ($this->timexp_applied_to_type=="3" || 
				db_loadResult("select user_supervisor 
								from users 
								where user_id = ".$this->timexp_creator)=="-1"){*/
			
			// si el registro se aplica a nada se 
			// ingresa aprobada
			if ($this->timexp_applied_to_type=="3"){	

				$sql = "SELECT user_supervisor FROM users WHERE user_id='".$this->timexp_creator."' ";
				$report_direct = db_loadResult($sql);

                //echo "<br>Reporte directo 1 :".$report_direct."<br>";
				if ($report_direct == '-1')
				{
				$this->timexp_last_status = "3";
				}
			}else{
				$sql = "SELECT timexp_supervisor FROM users WHERE user_id='".$this->timexp_creator."' ";
				$timexp_supervisor = db_loadResult($sql);
                
				//echo "Reporte directo 1 ".$report_direct."<br>";
				if ($timexp_supervisor == '-1')
				{
				$this->timexp_last_status = "3";
				}
			}
			
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			$ret = get_class( $this )."::store failed <br />" . db_error();
			return $ret;
		} else {
			$ret = NULL;
		}
		

		//actualizo la completitud de tareas si se aplica a una tarea y contribuye a su finalizaci?
		if ($this->timexp_applied_to_type=="1" &&
			$this->timexp_contribute_task_completion == "1"){
			$sql = "select distinct
						sum(te.timexp_value * pr.user_units / 100)
					from timexp te
					inner join tasks ta on ta.task_id = te.timexp_applied_to_id  
					inner join project_roles pr on pr.project_id = ta.task_project and pr.user_id = te.timexp_creator and pr.role_id = 2
					where	ta.task_id = $this->timexp_applied_to_id
					and		te.timexp_applied_to_type = 1
					and 	te.timexp_type = 1
					and 	te.timexp_contribute_task_completion = 1;";
			$task_hours_worked = (float) db_loadResult( $sql );

			$tskObj = new CTask();

			$tskObj->load($this->timexp_applied_to_id);
			if ($ret = $tskObj->updateHoursWorked()){
				return $ret;
			}else {
				$ret = NULL;
			}
		}
			
		if (!$new){
			$date = new CDate();
			$status = new CTimExpStatus();
			$status->timexp_status_id = "";
			$status->timexp_status_datetime = $date->format(FMT_DATETIME_MYSQL);
			$status->timexp_status_value = $this->timexp_last_status;
			$status->timexp_status_user = $this->timexp_creator;
			$status->timexp_id = $this->timexp_id;
			if ($ret = $status->store()){
				return $ret;
			}else {
				$ret = NULL;
			}
		}	
		return $ret;
	}

	function getMyHoursDates(){
		global $AppUI;
		return CTimExp::getTimExpDates( $AppUI->user_id, "1");	
	}	
	function getMyExpensesDates(){
		global $AppUI;
		return CTimExp::getTimExpDates( $AppUI->user_id, "2");	
	}		
	
	function getMyHours($from_date, $to_date, $status){
		global $AppUI;
		
		return CTimExp::getTimExpList($AppUI->user_id,$from_date,$to_date, $status, "1" );
	}
	
	function getMyExpenses($from_date, $to_date, $status){
		global $AppUI;
		
		return CTimExp::getTimExpList($AppUI->user_id,$from_date,$to_date, $status, "2" );
	}

	function getMyTimesDay($date){
		global $AppUI;
		return CTimExp::getTimExpDateList($AppUI->user_id,$date, "1" );
	}

	function getMyExpensesDay($date){
		global $AppUI;
		return CTimExp::getTimExpDateList($AppUI->user_id,$date, "2" );
	}

	function getTimExpDates($user_id, $type, $timexp_id=NULL){
		global $AppUI;

		$where ="";
		if ($timexp_id != NULL)
			$where .="and timexp_id in ($timexp_id) \n\t";

		$sql="
		select distinct timexp_date 
		from timexp 
		where 	timexp_creator = $user_id
		and 	timexp_type in ($type) 
		$where
		order by timexp_date desc";
		return db_loadList( $sql );	
	
	}	
	
	function getTimExpList($user_id=NULL, $from_date=NULL, $to_date=NULL, $status_id=NULL
		, $timexp_type=NULL, $project_id=NULL, $task_id=NULL
		, $bug_id=NULL, $timexp_id=NULL ){
		$select = "";$from = "";$join = "";$where = " 1=1 \n\t";
	
		$select .= "p.project_id, \n\t";
		$select .= "p.project_name, \n\t";
		$select .= "ta.task_id, \n\t";
		$select .= "ta.task_name, \n\t";
		$select .= "bt.id bug_id, \n\t";
		$select .= "bt.summary, \n\t";
		$select .= "'Nothing' nothing,\n\t";
		$select .= "concat(u.user_last_name,', ', u.user_first_name) user_name, \n\t";
		$select .= "te.timexp_name, \n\t";
		$select .= "te.timexp_applied_to_type, \n\t";
		$select .= "te.timexp_date, \n\t";
		$select .= "te.timexp_billable,";
		$select .= "te.timexp_last_status,";
		$select .= "sum(te.timexp_value) total \n\t";

		$from .= "timexp te";
		$join .= "left join tasks ta on te.timexp_applied_to_id=ta.task_id and te.timexp_applied_to_type = 1 \n\t";
		$join .= "left join btpsa_bug_table bt on te.timexp_applied_to_id=bt.id and te.timexp_applied_to_type = 2 \n\t";
		$join .= "left join projects p on p.project_id = ta.task_project or  p.project_id = bt.project_id \n\t";
		$join .= "left join users u on te.timexp_creator = u.user_id \n\t";

		
		if ($timexp_type != NULL)
			$where .=" and te.timexp_type in ( $timexp_type ) \n\t";
		if ($project_id != NULL)
			$where .=" and p.project_id in ($project_id) \n\t";
		if ($task_id != NULL)
			$where .=" and ta.task_id in ($task_id) \n\t";
		if ($timexp_id != NULL)
			$where .=" and te.timexp_id in ($timexp_id) \n\t";
		if ($status_id != NULL)
			$where .=" and te.timexp_last_status = $status_id \n\t";			
		if ($user_id != NULL)
			$where .=" and te.timexp_creator = $user_id \n\t";
		if ($from_date!=NULL and $to_date!=NULL){
			$f = new CDate($from_date);
			$f->setTime(0,0,0);
			$from_str = $f->format(FMT_DATETIME_MYSQL);
			$t = new CDate($to_date);
			$t->setTime(23,59,59);
			$to_str = $t->format(FMT_DATETIME_MYSQL);
			$where .=" and te.timexp_date >= '$from_str' \n\t";
			$where .=" and te.timexp_date <= '$to_str' \n\t";			
		}
			
			
				
		$sql = "select $select from $from $join where $where";
		$sql .= ($groupby ? " group by $groupby": " group by timexp_date, timexp_billable, timexp_applied_to_type, timexp_applied_to_id");
		$sql .= ($orderby ? " order by $orderby": " order by project_id, task_id, bug_id, timexp_billable");
		return db_loadList( $sql );	
	}

	function getTimexpLog(){
		global $AppUI;
		$user = $AppUI->user_id;

		$sql="select distinct te.*, ta.*, bt.* , ts.*
				from timesheets ts 
				inner join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet
				";
		$sql .= "left join tasks ta on te.timexp_ts_applied_to_id=ta.task_id and te.timexp_ts_applied_to_type = 1 \n\t";
		$sql .= "left join btpsa_bug_table bt on te.timexp_ts_applied_to_id=bt.id and te.timexp_ts_applied_to_type = 2 \n\t";
		$sql.="\n\t where timexp_ts_timexp = \"$this->timexp_id\""; 

		$sql .= "\n\t order by timesheet_date";

		//echo "<pre>";var_dump($sql); echo "</pre>";
		return db_loadList($sql);		
	
	}
	
	//<agregado>Parametros:$timexp_status=NULL, $billable=NULL</agregado>
	function getTimExpDateList($user_id=NULL, $date=NULL, $timexp_type=NULL, $project_id=NULL, $task_id=NULL, $bug_id=NULL , $timexp_id=NULL, $orderby=NULL, $from_date=NULL, $to_date=NULL, $timexp_status=NULL, $billable=NULL,$libre=NULL,$from_hora=NULL, $to_hora=NULL,$spvMode=NULL,$applied_to_types=NULL){
        
		global $AppUI;
        
		$desde = substr($from_hora,0,2).substr($from_hora,3,2)."00";
		$hasta = substr($to_hora,0,2).substr($to_hora,3,2)."00";

		$select = "";$from = "";$join = "";$where = " 1=1 \n\t";
        
		if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){
        
		if (($hasta=="000000")||($hasta=="235900"))
			{
			$hasta = "240000";
			}
		
		
		$select .= "
		           IF (
					((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					te.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					te.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					te.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					te.timexp_start_time))
							))/3600) > 0,
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					te.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					te.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					te.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					te.timexp_start_time))
							))/3600),
					 24 +
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					te.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					te.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					te.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					te.timexp_start_time))
							))/3600)
					 )
					 as timexp_value, \n\t	 
		           ";
				   
        }
		else{
	    $select .= "te.timexp_value, \n\t";
		}
 		$select .= "te.timexp_id, \n\t";
		$select .= "p.project_id, \n\t";
		$select .= "p.project_name, \n\t";
		$select .= "ta.task_id, \n\t";
		$select .= "ta.task_name, \n\t";
		$select .= "bt.id bug_id, \n\t";
		$select .= "bt.summary, \n\t";
		$select .= "'Nothing' nothing,\n\t";
		$select .= "concat(u.user_last_name,', ', u.user_first_name) user_name, \n\t";
		$select .= "te.timexp_name, \n\t";
		$select .= "te.timexp_type, \n\t";
		$select .= "te.timexp_description, \n\t";
		$select .= "te.timexp_applied_to_type, \n\t";
		$select .= "te.timexp_date, \n\t";
		$select .= "te.timexp_billable, \n\t";
		$select .= "te.timexp_last_status, \n\t";
		$select .= "te.timexp_contribute_task_completion, \n\t";
		$select .= "te.timexp_creator, \n\t";
		$select .= "td.id_todo, \n\t";
	    $select .= "td.description \n\t";

		$from .= "timexp te";
		$join .= "left join tasks ta on te.timexp_applied_to_id=ta.task_id and te.timexp_applied_to_type = 1 \n\t";
		$join .= "left join btpsa_bug_table bt on te.timexp_applied_to_id=bt.id and te.timexp_applied_to_type = 2 \n\t";
		$join .= "left join project_todo td on te.timexp_applied_to_id=td.id_todo and te.timexp_applied_to_type = 4 \n\t";
		$join .= "left join projects p on p.project_id = ta.task_project or  p.project_id = bt.project_id or  p.project_id = td.project_id \n\t";
		$join .= "left join users u on te.timexp_creator = u.user_id \n\t";
		

		
		if ($timexp_type != NULL)
			$where .=" and te.timexp_type in ( $timexp_type ) \n\t";
		if ($project_id != NULL)
			$where .=" and p.project_id in ($project_id) \n\t";
		if ($task_id != NULL)
			$where .=" and ta.task_id in ($task_id) \n\t";
		if ($id_todo != NULL)
			$where .=" and td.id_todo in ($id_todo) \n\t";
		//<agregado> by Ulises
		if ($bug_id != NULL){
			if($bug_id != "0"){
				$where .= " and bt.id = '$bug_id'";
			}else{
				$where .= " and bt.id IS NOT NULL";
			}
		}
		//</agregado>
		if ($timexp_id != NULL)
			$where .=" and te.timexp_id in ($timexp_id) \n\t";
		if ($status_id != NULL)
			$where .=" and te.timexp_last_status = $status_id \n\t";			
		if ($user_id != NULL){
			if ($user_id==0) $where .=" and timexp_creator IS NOT NULL \n\t";
			else $where .=" and te.timexp_creator = $user_id \n\t";
		}

		if($spvMode){
			$tmpprj = new CProject();
			$tmp_projects = $tmpprj->getAllowedRecords($AppUI->user_id, "project_id");
			unset($tmpprj);
 
			if (count($tmp_projects))
			foreach($tmp_projects as $pid => $perm){
				$list_projects[] = $pid;
			}
            
            if ($project_id == NULL){
			$where .=" and (te.timexp_applied_to_type='3' OR 
	                  ( p.project_id in (" . implode( ',', $list_projects) . "))
					  )  
					  \n\t";
			}
		}

		if ($date!=NULL ){
			$date_obj = new CDate($date);
			$date = $date_obj->format("%Y-%m-%d");
			unset ($date_obj);
			$where .=" and te.timexp_date >= '$date' \n\t";
			$where .=" and te.timexp_date <= '$date' \n\t";
		}
		
		if ($from_date!=NULL and $to_date!=NULL){
			$where .=" and te.timexp_date >= '$from_date 00:00:00' \n\t";
			$where .=" and te.timexp_date <= '$to_date 00:00:00' \n\t";
		}
        
		if (($applied_to_types!=NULL)&&($applied_to_types!="0"))
		{
		 $where .=" and te.timexp_applied_to_type = '$applied_to_types' \n\t";
		}

		if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){
					$where .="AND (EXTRACT(HOUR_SECOND FROM te.timexp_end_time) <> '$desde' ) \n\t";
					$where .="AND (
					   ((IF ((EXTRACT(HOUR_SECOND FROM
								te.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
								te.timexp_end_time))
									  - 
										IF ((EXTRACT(HOUR_SECOND FROM
								te.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
								te.timexp_start_time))
										)) > 0  
						)  \n\t";
		}	

		//<agregado>
		if ($timexp_status!=NULL){
			$where .=" and te.timexp_last_status = '$timexp_status' \n\t";
		}
		if ($billable!=NULL){
			$where .=" and te.timexp_billable = '$billable' \n\t";
		}				
		//</agregado>


		// Nuevo filtro, por nombre o descripción, campo libre //

		if ($libre != ""){
		
		//Reviso que no haya nada raro//
		$libre = eregi_replace("select","",$libre);
		$libre = eregi_replace("update","",$libre);	
		$libre = eregi_replace("delete","",$libre);
		$libre = eregi_replace("show","",$libre);
		$libre = eregi_replace("insert","",$libre);
		

		// busco en el nombre o la descripción del timexp, de la tarea y del bug
		$where .="and (te.timexp_name like '%$libre%' or te.timexp_description like '%$libre%' or ta.task_name like '%$libre%' or ta.task_description like '%$libre%' or bt.summary like '%$libre%' or td.description like '%$libre%') \n\t";
        
		}
				
		$sql = "select $select from $from $join where $where";
		$sql .= ($groupby ? " group by $groupby": " ");
		$sql .= ($orderby ? " order by $orderby": " order by project_id, task_id, bug_id, id_todo, timexp_id");
        
		
		//echo "<pre>".$sql."</pre>";

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
	}

	function getStatusLog($timexp_id=0){
		$timexp_id = $timexp_id ? $timexp_id : $this->timexp_id;
		$sql = "
			SELECT timexp_status_id
				, timexp_status_datetime
				, timexp_status_user
				, timexp_status_value
				, u.user_username
				, CONCAT_WS(' ',u.user_first_name,u.user_last_name) user_full_name
			FROM timexp_status inner join users u on u.user_id = timexp_status_user
			WHERE timexp_id = $timexp_id
			ORDER BY timexp_status_datetime DESC";
			//echo "<pre>";var_dump($sql);echo "</pre>";
		return db_loadList( $sql );	
	
	}

	function getAppliedToProjectId(){
		switch ($this->timexp_applied_to_type){
			case "1":
				$select = "task_project";
				$table = "tasks";
				$idfield  = "task_id";
				break;
			case "2":
				$select = "project_id";
				$table = "btpsa_bug_table";
				$idfield  = "id";
				break;
			default:
				return false;
				break;
		}
		$sql = "SELECT $select FROM $table WHERE $idfield = $this->timexp_applied_to_id";
		return db_loadResult($sql);
	
	}
	
	function getApprovedTaskHours($task_id, $user_id=""){
		$sql = "
			SELECT sum(timexp_ts_value)
			FROM timexp_ts
			WHERE
			    timexp_ts_type = 1
			and timexp_ts_applied_to_type = 1
			and timexp_ts_applied_to_id = $task_id
			".($user_id == "" ? "" : "and timexp_ts_creator = $user_id")."
			and timexp_ts_last_status = 3
			ORDER BY timexp_ts_save_date DESC		
		
		";
		return db_loadResult($sql);
	
	}

	function getTaskWorkedHours($task_id, $user_id=""){
		$sql = "
			SELECT sum(timexp_value)
			FROM timexp
			WHERE
			    timexp_type = 1
			and timexp_applied_to_type = 1
			and timexp_applied_to_id = $task_id
			".($user_id == "" ? "" : "and timexp_creator = $user_id")."
			and timexp_last_status in (0, 1, 3)		
		";
		$hours = db_loadResult($sql);
		return $hours ? $hours : 0;
	
	}
		
/**
*	List the approved hours given the following parameters
*
*	@author	rodrigo <rfuentes@omnisciens.com>
*	@param	date	fecha a partir de la cual se consultaran los datos
*	@param	date	fecha hasta de la cual se consultaran los datos
*	@param	int		id del usuario, si es vac? devuelve todos
*	@param	int		id del proyecto, si es vac? devuelve todos
*	@param	int		0: no facturables; 1:facturables. si es vacio devuelve todas
*	@return	array	un array que contiene las filas y columnas de resultado (o un mensaje de error)
**/
	function getApprovedHours($from_date, $to_date, $user_id="", $project_id="", $billables=""){
		if (! in_array($billables, array("", 1, 0)))
			return "Invalid parameter billables, must be: 0, 1, or ''.";
		if (!is_integer($user_id) && $user_id!="")
			return "Invalid parameter user id, must be an integer or ''.";
		if (!is_integer($project_id) && $project_id!="")
			return "Invalid parameter project id, must be an integer or ''.";	
			
		$f = new CDate($from_date);
		$f->setTime(0,0,0);
		$from_str = $f->format(FMT_DATETIME_MYSQL);
		$t = new CDate($to_date);
		$t->setTime(23,59,59);
		$to_str = $t->format(FMT_DATETIME_MYSQL);
		$dates ="  and timexp_ts_date >= '$from_str' \n\t"
			. " and timexp_ts_date <= '$to_str' \n\t";
									
		$sql = "
			SELECT 
				timexp_ts_timexp 	timexp_id,
				timexp_ts_name 		timexp_name,
				timexp_ts_creator 	timexp_creator,
				timesheet_project 	timexp_project,
				timexp_ts_billable 	timexp_billable,		
				timexp_ts_date 		timexp_date,
				timexp_ts_value 	timexp_hours
		
			FROM timexp_ts tts inner join timesheets t on tts.timexp_ts_timesheet = t.timesheet_id
			WHERE
			    timexp_ts_type = 1
			".($project_id == "" ? "" : "and timesheet_project = $project_id")."
			".($user_id == "" ? "" : "and timexp_ts_creator = $user_id")."
			".($billables == "" ? "" : "and timexp_ts_billable = $billables")."
		timexp_ts_billable
			$dates
			and timexp_ts_last_status = 3
			ORDER BY timexp_date ASC		
		
		";

		return db_loadList($sql);
	
	}
	
	function getUsersWithTimexp($type, $ap_type, $ap_id){
		$sql = "select user_id, CONCAT_WS(' ',u.user_first_name,u.user_last_name) fullname
				  FROM users u inner join timexp te on timexp_creator = user_id 
				  where timexp_type = $type
				  and	timexp_applied_to_type = $ap_type
				  and	timexp_applied_to_id = $ap_id 
		";
		return db_loadHashList($sql);
	
	}
	
}


/**
 *	TimExp Status Class
 *	@todo Move the 'address' fields to a generic table
 */
class CTimExpStatus extends CDpObject {
/** @var int Primary Key */
	var $timexp_status_id = NULL;
/** @var string */

// these next fields should be ported to a generic address book
	var $timexp_status_user = NULL;
	var $timexp_id = NULL;
	var $timexp_status_datetime = NULL;
	var $timexp_status_value = NULL;

	function CTimExpStatus() {
		$this->CDpObject( 'timexp_status', 'timexp_status_id' );
	}
// overload check
	function check() {
		if ($this->timexp_status_id === NULL) {
			return 'timexp status id is NULL';
		}
		$this->timexp_status_id = intval( $this->timexp_status_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		return true;
	}

}



class CTimexpSupervisor {
	
	var $users = NULL;
	
	function CTimexpSupervisor(){

	}


	function getSupervisedUsers(){
		global $AppUI;

		// si es system admin puede supervisar todos los usuarios
		if ($AppUI->user_type==1){
			$sql="SELECT u.user_id, CONCAT_WS(' ',u.user_first_name,u.user_last_name) fullname
				  FROM users u 
				  where u.user_type <> 5";
			$users =  db_loadHashList($sql);	
			asort($users);
			return $users;	
		}

		
		// lista de usuarios como supervisor directo
		$sql="SELECT u.user_id, CONCAT_WS(' ',u.user_first_name,u.user_last_name)
			 FROM users u
			 WHERE u.timexp_supervisor = $AppUI->user_id and u.user_type <> 5
			 OR u.user_supervisor = $AppUI->user_id and u.user_type <> 5
			 ";
		//echo $sql;
		$users = db_loadHashList($sql);

		// usuarios supervisados como project admin
		// listo proyectos administrados
		$projects = CUser::getOwnedProjects($AppUI->user_id);
		$where = count($projects) ? "and p.project_id in (".implode(array_keys($projects), ', ').")":"and 1=0";

		$sql = "
SELECT  distinct u.user_id, CONCAT_WS(' ',u.user_first_name,u.user_last_name)
FROM timexp te 
	inner join users u on u.user_id = te.timexp_creator
	inner join project_roles pr on role_id = 2 and pr.user_id = u.user_id
	inner join projects p on p.project_id = pr.project_id
WHERE (u.timexp_supervisor = -2
and u.user_type <> 5)
OR (u.user_supervisor = -2
and u.user_type <> 5)
$where
		";
        //echo $sql;
		$users = arrayMerge($users, db_loadHashList($sql));
		asort($users);
		return $users;
	}

	function getSupervisedTimexpId(){
		global $AppUI;

		// si es system admin puede supervisar todos los usuarios
		if ($AppUI->user_type==1){
			$sql="SELECT timexp_id, timexp_id
				  FROM timexp u";
			return db_loadHashList($sql);	
		}

		
		// lista de usuarios como supervisor directo
		$sql="SELECT timexp_id, timexp_id
			 FROM users u inner join timexp te on u.user_id = te.timexp_creator
			 WHERE u.timexp_supervisor = $AppUI->user_id and u.user_type <> 5";
		$timexps = db_loadHashList($sql);

		// usuarios supervisados como project admin
		// listo proyectos administrados
		$projects = CUser::getOwnedProjects($AppUI->user_id);
		$where = count($projects) ? "and p.project_id in (".implode(array_keys($projects), ', ').")":"and 1=0";

		$sql = "
SELECT   timexp_id, timexp_id
FROM timexp te 
	inner join users u on u.user_id = te.timexp_creator
	inner join project_roles pr on role_id = 2 and pr.user_id = u.user_id
	inner join projects p on p.project_id = pr.project_id
WHERE u.timexp_supervisor = -1
and u.user_type <> 5		
$where
		";
//echo "<pre>$sql</pre>";
		$timexps = arrayMerge($timexps, db_loadHashList($sql));

		return $timexps;
	}
}


/**
 *	Timesheet Class
 *	
 */
class CTimesheet extends CDpObject {
/** @var int Primary Key */
	var $timesheet_id = NULL;
/** @var string */

// these next fields should be ported to a generic address book
	var $timesheet_user = NULL;
	var $timesheet_project = NULL;
	var $timesheet_type = NULL;
	var $timesheet_date = NULL;
	var $timesheet_start_date = NULL;
	var $timesheet_end_date = NULL;
	var $timesheet_last_status = NULL;
	var $timesheet_sent_to = NULL;
	var $timesheet_modified_by = NULL;

	function CTimesheet() {
		$this->CDpObject( 'timesheets', 'timesheet_id' );
	}
// overload check
	function check() {
		if ($this->timesheet_id === NULL) {
			return 'timesheet id is NULL';
		}
		$this->timesheet_id = intval( $this->timesheet_id );

		return NULL; // object is ok
	}

	
	function canChangeStatus($to_status){
		global $AppUI, $ts_status_transition;
		
		$is_owner = $this->timesheet_user == $AppUI->user_id;
		$is_supervisor = $this->canSupervise();

		if ($this->timesheet_project == "0")
		{
		 // Si son hs internas, y las tiene listadas, es porque puede cambiar sus estados.
         $is_supervisor = 1;
		}
		
		$next_status = explode(",",$ts_status_transition[$this->timesheet_last_status]);
		
		$rta = false;
		
		//estados a los que puede cambiar s?o un supervisor
		if ( in_array($to_status,array("2","3")) 
				&& $is_supervisor 
				&& in_array($to_status, $next_status) ){
			$rta=true;
		}
		if (in_array($to_status,array("0","1","4")) 
				&& $is_owner 
				&& in_array($to_status, $next_status)){
			$rta=true;
		}

		// si el timesheet esta desaprobado
		if ($rta && $this->timesheet_last_status == "2"){

			$sql="select 
						sum(if(	te.timexp_save_date is null
							, 1
							, abs((CURRENT_TIMESTAMP - te.timexp_save_date ) - (CURRENT_TIMESTAMP - tets.timexp_ts_save_date ))) ) is_modified
						from timesheets ts
						inner join timexp_ts tets on tets.timexp_ts_timesheet = ts.timesheet_id
						left join timexp te on te.timexp_timesheet = ts.timesheet_id
						where
						timesheet_id = \"$this->timesheet_id\";";

			if (db_loadResult($sql)){
				$rta = false;
			}
		
		}
		
		
		return $rta;
	
	}
// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		return true;
	}

	function canRead(){
		
	}


	function canSupervise(){
		global $AppUI;
		$timesheet_id = $this->timesheet_id;
		
		if (!$timesheet_id)
			return false;

		//si es system admin puede supervisar
		if ( $AppUI->user_type==1 ){
			return true;
		}

		//obtengo el tipo de supervisi? seleccionada del usuario creador
		$sql = "SELECT timexp_supervisor 
				FROM users
				WHERE user_id = $this->timesheet_user";
			//echo "<pre>";var_dump($this); echo "</pre>";
		switch (db_loadResult($sql)){
			
			//los project admins
			case "-1":
				$po = CUser::getOwnedProjects($AppUI->user_id);
			//echo "<pre>";var_dump($po); echo "</pre>";
				$rta = isset($po[$this->timesheet_project]);
				break;
			// autoaprobaci?
			case "-2":
				$rta = $AppUI->user_id == $this->timesheet_user;
				break;
			// un usuario espec?ico
			case $AppUI->user_id:
				$rta=true;
				break;
			default:
				$rta=false;
				break;
		}
		return $rta;	
	}
    
	// Se fija si el usuario es el supervisor para reporte directo del registro que intenta ver
	function canSupervise_directReport($timexp_id=null)
	{
	  global $AppUI;
	  
	  $can_read = '0';
	  
	  if($timexp_id == null)
	  {
	  	 $query = "SELECT  timexp_ts_applied_to_type FROM timexp_ts WHERE timexp_ts_timesheet = '".$this->timesheet_id."' ";
	  	 $sql = db_loadColumn($query);
		 $user_supervised = $this->timesheet_user;
		 $applied_to = $sql[0];
	  }
	  else{
		  $query = "SELECT timexp_ts_applied_to_type FROM timexp_ts WHERE timexp_ts_id = '$timexp_id' ";
		  $sql = db_loadColumn($query); 
		  $user_supervised = $this->timesheet_user;
		  $applied_to = $sql[0];
	  }

	  // Con el usuario creador del timesheet, me fijo en usuarios a quien tiene asignado como reporte directo
	  $query = "SELECT user_supervisor FROM users WHERE user_id = '$user_supervised' ";
	  $sql = db_loadColumn($query);
	  $asigned_report = $sql[0];
	  
	  //si es system admin puede supervisar
	  if ( $AppUI->user_type==1 ){
			$can_read = '1';
			return $can_read;
	  }
	  
	  if ($AppUI->user_id == $asigned_report && $applied_to == '3')
	  {
	  	$can_read = '1';
	  }

      return $can_read;
    }
	
	function getMyTimesheets($params, $order=NULL, $paged_results = false){
		global $AppUI;
		$user = $AppUI->user_id;

		$sql="select ts.*
			,p.project_name
			, sum(if(te.timexp_ts_billable=1, te.timexp_ts_value,0)) totbil
			, sum(if(te.timexp_ts_billable=0, te.timexp_ts_value,0)) totnobil
				from timesheets ts 
				left outer join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet ";
		$sql.="\n\t inner join projects p on ts.timesheet_project = p.project_id";
		$sql.="\n\t where timesheet_user=$user"; 
		if(count($params)){
			foreach($params as $field => $value){
				if ($value!="")
					$sql .= "\n\t and $field in ($value)";
				else 
					$sql .= "\n\t and $field <> $field";					
			}
		}
		if(isset($order)){
		//	$sql .= "\n\t order by $order";
		}
		$sql .="\n\t group by timesheet_id";

		$sql .= "\n\t union
			     select ts.*, ts.timesheet_project
						, sum(if(te.timexp_ts_billable=1, te.timexp_ts_value,0)) totbil
						, sum(if(te.timexp_ts_billable=0, te.timexp_ts_value,0)) totnobil
				from timesheets ts 
				left outer join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet
				where timesheet_user='$user' ";
	  
	  if(count($params)){
			foreach($params as $field => $value){
				if ($value!="")
					$sql .= "\n\t and $field in ($value)";
				else
					$sql .= "\n\t and $field <> $field";
			}
		}

		$sql .=  "\n\t and timesheet_project = '0' ";

		$sql .="\n\t group by timesheet_id";
        $sql .="\n\t order by timesheet_date desc";


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
	}

	function getMyUnassignedTimexpProjects($type){
		global $AppUI;
		$user = $AppUI->user_id;

		$sql="select distinct p.project_id, 
				p.project_name, 
				min( te.timexp_date ) first_date , 
				max( te.timexp_date ) last_date 
				from timexp te 
				left join tasks ta on 
					te.timexp_applied_to_id=ta.task_id 
					and te.timexp_applied_to_type = 1 
				left join btpsa_bug_table bt on 
					te.timexp_applied_to_id=bt.id 
					and te.timexp_applied_to_type = 2 
				left join project_todo td on 
					te.timexp_applied_to_id=td.id_todo 
					and te.timexp_applied_to_type = 4 
				inner join projects p on 
					p.project_id = ta.task_project or  
					p.project_id = bt.project_id or  
					p.project_id = td.project_id 
				where (te.timexp_timesheet is NULL or te.timexp_last_status in (2,4))
						and te.timexp_creator = $user 
						and te.timexp_type = $type
						and te.timexp_last_status in (0, 2, 4)
				group by project_id
				order by project_name
				";

		//echo "<pre>";var_dump($sql); echo "</pre>";
		return db_loadList($sql);	
	}
	
	function getSupTimesheets($params){
		global $AppUI;

		$sql="select distinct ts.timesheet_id
					from timesheets ts
					inner join users u on u.user_id = ts.timesheet_user 
					where timesheet_last_status > 0
					";
		//si no es system admin debe verificarse que pueda supervisar
		if ( $AppUI->user_type != 1 ){
			$po = CUser::getOwnedProjects($AppUI->user_id);
			$project_ids = count($po) ? implode(array_keys($po),",") : "-1";
			
			//verifica el tipo de supervisi? que tiene el creador del TS	
			//		si fuera -1 (project administrators) se valida que el proyecto 
			//								sea uno de los que el user es owner
			//		sino valida que el supervisor sea el usuario que consulta	
			$sql.="and	(		(u.timexp_supervisor = -2  and	ts.timesheet_project in ($project_ids))
											or u.timexp_supervisor = $AppUI->user_id or (u.user_supervisor = $AppUI->user_id and ts.timesheet_project='0') )
			";
		}

		if(count($params)){
			foreach($params as $field => $value){
				if ($value!="")
					$sql .= "\n\t and $field in ($value)";
				else 
					$sql .= "\n\t and $field <> $field";
			}
		}
		if(isset($order)){
			$sql .= "\n\t order by $order";
		}
		
		//echo "<pre>".$sql."</pre>";
		return db_loadColumn($sql);
	}

	function getSupervisors(){
		global $AppUI;
		
		if($this->timesheet_project != '0' )
		{
			$project = new CProject();
			$project->load($this->timesheet_project);
			
			$owners = $project->getOwners();
			$owners[$project->project_owner]="";

			$sup_ids = implode(array_keys($owners),", ");
			
			// listo los supervisores del timesheet (incluido el SYSADMIN)
			$sql="select distinct s.*
						from users u 
						inner join users s on (u.timexp_supervisor= s.user_id and u.timexp_supervisor > 0 )
																	or (s.user_id in ($sup_ids) and u.timexp_supervisor = -1)
																	or (s.user_type = 1)
						where 
						u.user_id = \"$this->timesheet_user\" 
						";
			$sql .= "\n\t order by user_id";
		}else{

			// listo los supervisores del timesheet (incluido el SYSADMIN)
			$sql="select distinct s.*
						from users u 
						inner join users s on (u.user_supervisor= s.user_id and u.user_supervisor > 0 )
																	or (s.user_type = 1)
						where 
						u.user_id = \"$this->timesheet_user\" 
						";
			$sql .= "\n\t order by user_id";
		}

		return db_loadList($sql);	
	}
	
	
	function annul($desc){
		global $AppUI;
		
		if (!$this->canChangeStatus("4")){
			return "Invalid Operation.You have no permission or is not possible to change the status.";
		}
		return $this->changeStatus("4", $desc);
	}
	
	function changeStatus($to_status, $description=""){
		global $AppUI, $ts_log_required;
		
        //echo "<pre>";var_dump($this);echo "</pre>";
		if($this->timesheet_project !='0')
		{
		   $sql = "select timexp_supervisor 
				from users 
				where user_id = ".$this->timesheet_user;
		}else{
		   $sql = "select user_supervisor 
				from users 
				where user_id = ".$this->timesheet_user;
		}
		
		$user_supervisor = db_loadResult($sql);
		$is_not_supervised = $user_supervisor == "-2" ? true : false;
		
		if (!$this->canChangeStatus($to_status) && !$is_not_supervised){
			return "Invalid Operation.You have no permission or is not possible to change the status.";
		}
		
		$date = new CDate();
		
		$status = new CTimesheetStatus();
		$status->timesheetstatus_timesheet=$this->timesheet_id;
		$status->timesheetstatus_id="";
		$status->timesheetstatus_date = $date->format( FMT_DATETIME_MYSQL );
		$status->timesheetstatus_status = $to_status;
		$status->timesheetstatus_user = $AppUI->user_id;
		$status->timesheetstatus_description = $description;		
		
		if (($msg = $status->store())) {
			return $msg;
		}
		
		$this->timesheet_last_status = $to_status;
		if($to_status == 1)	$this->timesheet_sent_to = $user_supervisor;
		if(($to_status != 1) && ($to_status != 0))	$this->timesheet_modified_by = $AppUI->user_id;
		
		if (($msg = $this->store())) {
			return $msg;
		}
		
		// actualizo el estado de las horas asignadas
		$sql="update timexp
			set	timexp_last_status = $to_status
			where timexp_timesheet = $this->timesheet_id; 
			";
		if (! db_exec($sql)) {
			return db_error();
		}

		// actualizo el estado de las horas en historial
		$sql="update timexp_ts
			set	timexp_ts_last_status = $to_status
			where timexp_ts_timesheet = $this->timesheet_id; 
			";
		if (! db_exec($sql)) {
			return db_error();
		}
		
		//si se aprueban horas actualizo el presupuesto real del proyecto
		if($to_status == 3 && $this->timesheet_project !='0'){
			CProject::updateRealBudget($this->timesheet_project);
		}
		
		$this->notify($to_status);
		
		return null;
	}	
	
	function assignTimexp(){
		$msg = NULL;
		if (! (is_numeric($this->timesheet_id) && $this->timesheet_id > 0)){
			return "This is an object method, there is no timesheet loaded.";
		}
		
		// obtengo la lista de todos los registros de hora o gastos del usuario
		// que caigan dentro del per?do del timesheet y no está aprobado
		// y no est? asignado a un timesheet o el mismo haya sido anulado o 
		// desaprobado

		if ($this->timesheet_project !='0')
		{
		$sql="select timexp_id
					from timexp te
					left join tasks ta on te.timexp_applied_to_id=ta.task_id 
					left join btpsa_bug_table bt on te.timexp_applied_to_id=bt.id
                    left join project_todo td on te.timexp_applied_to_id=td.id_todo
					where timexp_creator = '$this->timesheet_user'
					and		timexp_type = '$this->timesheet_type'
					and		timexp_date	>= '$this->timesheet_start_date'
					and		timexp_date	<= '$this->timesheet_end_date'
					and		timexp_last_status <> 3
					and		(te.timexp_applied_to_type = 2  
							and bt.project_id = '$this->timesheet_project' 
							or 
							te.timexp_applied_to_type = 1 
							and ta.task_project = '$this->timesheet_project'
					        or 
							te.timexp_applied_to_type = 4 
							and td.project_id = '$this->timesheet_project'
					        )					
					and		timexp_applied_to_type in (1,2,4)
					and   (timexp_timesheet is NULL 
								or timexp_last_status in (2,4)); 
					";
		}
		else
		{
		$sql = "select timexp_id
					from timexp te
					where timexp_creator = '$this->timesheet_user'
					and		timexp_type = '$this->timesheet_type'
					and		timexp_date	>= '$this->timesheet_start_date'
					and		timexp_date	<= '$this->timesheet_end_date'	
					and		timexp_applied_to_type='3'
					and		timexp_last_status <> 3
					and   (timexp_timesheet is NULL 
								or timexp_last_status in (2,4)); 
					";
		}
		
		$list = db_loadList($sql);
		//echo "<pre>$sql<br>";var_dump($list);echo "</pre>";		
		for ($i=0;$i<count($list);$i++){
			$obj = new CTimExp();
			
			$obj->load($list[$i]["timexp_id"]);
			
			// cambio el timesheet que tiene asignado el registro
			// y el estado pasa a pendiente
			$obj->timexp_timesheet = $this->timesheet_id;
			$obj->timexp_last_status = $this->timesheet_last_status;
			
			if (($msg = $obj->store())) {
				return $msg;
			}				

			// Hago una copia de cada registro para el TS y lo guardo
			$new = new CTimExp_TS();
			$new->timexp_ts_id="";
			
			$new->timexp_ts_timexp = $obj->timexp_id;	
			$new->timexp_ts_type = $obj->timexp_type;	
			$new->timexp_ts_name = $obj->timexp_name;	
			$new->timexp_ts_description = $obj->timexp_description;	
			$new->timexp_ts_creator = $obj->timexp_creator;	
			$new->timexp_ts_date = $obj->timexp_date;	
			$new->timexp_ts_value = $obj->timexp_value;	
			$new->timexp_ts_applied_to_type = $obj->timexp_applied_to_type;	
			$new->timexp_ts_applied_to_id = $obj->timexp_applied_to_id;	
			$new->timexp_ts_billable = $obj->timexp_billable;	
			$new->timexp_ts_last_status = $obj->timexp_last_status;	
			$new->timexp_ts_start_time = $obj->timexp_start_time;	
			$new->timexp_ts_end_time = $obj->timexp_end_time;	
			$new->timexp_ts_contribute_task_completion = $obj->timexp_contribute_task_completion;	
			$new->timexp_ts_timesheet = $obj->timexp_timesheet;	
			$new->timexp_ts_save_date = $obj->timexp_save_date;			
			
			
			if (($msg = $new->store())) {
				return $msg;
			}				

			unset ($obj);
			unset ($new);
								
		}
		
		// envia mails notificando la creacion del nuevo timesheet
		//$this->notify();
		
		return $msg;

	}

	function existsUnassignedTimexp(){

		if($this->timesheet_project != '0'){
		$sql="select count(timexp_id)
					from timexp te
					left join tasks ta on te.timexp_applied_to_id=ta.task_id 
					left join btpsa_bug_table bt on te.timexp_applied_to_id=bt.id
                    left join project_todo td on te.timexp_applied_to_id=td.id_todo
					where timexp_creator = '$this->timesheet_user'
					and		timexp_type = '$this->timesheet_type'
					and		timexp_date	>= '$this->timesheet_start_date'
					and		timexp_date	<= '$this->timesheet_end_date'
					and		(te.timexp_applied_to_type = 2  
							and bt.project_id = '$this->timesheet_project' 
							or 
							te.timexp_applied_to_type = 1 
							and ta.task_project = '$this->timesheet_project'
					        or 
							te.timexp_applied_to_type = 4 
							and td.project_id = '$this->timesheet_project'
					        )					
					and		timexp_applied_to_type in (1,2,4)
					and   (timexp_timesheet is NULL 
								or	timexp_last_status in (2,4) )
					and		timexp_last_status in(0, 2, 4)";
		}
		else{
		$sql = "SELECT count(timexp_id)
		FROM timexp 
		WHERE 
		timexp_type ='$this->timesheet_type'
		and timexp_applied_to_type = '3' 
		and (timexp_timesheet is NULL or timexp_last_status in (2,4) )
		and timexp_last_status in(0, 2, 4) AND timexp_creator = '$this->timesheet_user'
		";
		}

		return (db_loadResult($sql)==0 ? "No data available" : NULL );	
	
	}

	function getTimesheetData($ts_id=NULL){
		if ($ts_id===NULL || !is_numeric($ts_id) || $ts_id < 0 ){
			if (! (is_numeric($this->timesheet_id) && $this->timesheet_id > 0)){
				return "Invalid Id";
			}		
			$ts_id=$this->timesheet_id;
		}

		$sql="select ts.*, u.*, p.*, tss.timesheetstatus_id, tss.timesheetstatus_description
			,	max(tss.timesheetstatus_date) timesheetstatus_date
			, sum(if(te.timexp_ts_billable=1, te.timexp_ts_value,0)) totbil
			, sum(if(te.timexp_ts_billable=0, te.timexp_ts_value,0)) totnobil
				from timesheets ts 
				left outer join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet
				left join timesheetstatus tss on ts.timesheet_id = tss.timesheetstatus_timesheet ";
		$sql .= "left join users u on u.user_id = ts.timesheet_user \n\t";
		$sql .= "left join projects p on p.project_id = ts.timesheet_project \n\t";
		$sql.="\n\t where ts.timesheet_id = \"$ts_id\""; 
		$sql .= "\n\t group by timesheet_id, timesheetstatus_id";
		$sql .= "\n\t order by timesheetstatus_id desc limit 0,1";

		$data = db_loadList($sql);
		return $data [0];
	}

	function getListTimesheetsData($params, $order=NULL, $paged_results = false){
		
		$sql="select ts.*, u.*, p.*
			, sum(if(te.timexp_ts_billable=1, te.timexp_ts_value,0)) totbil
			, sum(if(te.timexp_ts_billable=0, te.timexp_ts_value,0)) totnobil
				from timesheets ts 
				left outer join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet ";
		$sql .= "left join users u on u.user_id = ts.timesheet_user \n\t";
		$sql .= "left join projects p on p.project_id = ts.timesheet_project \n\t";
		$sql.="\n\t where 1 = 1";

		if(count($params)){
			foreach($params as $field => $value){
				if ($value!="")
					$sql .= "\n\t and $field in ($value)";
				else 
					$sql .= "\n\t and $field <> $field";
			}
		}
		$sql .= "\n\t group by timesheet_id";
		if(isset($order)){
			$sql .= "\n\t order by $order";
		}
        
		//echo "<pre>".$sql."</pre>";
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
	}
	
	
	function getAssignedTimexp(){
		global $AppUI;
		$user = $AppUI->user_id;
        
		if($this->timesheet_project !='0')
		{
		$sql="select distinct te.*, ta.*, bt.*, tp.*
				from timesheets ts 
				inner join timexp_ts te on 
					ts.timesheet_id = te.timexp_ts_timesheet 
				left join tasks ta on 
					te.timexp_ts_applied_to_id=ta.task_id 
				left join btpsa_bug_table bt on 
					te.timexp_ts_applied_to_id=bt.id 
				left join project_todo tp on 
					te.timexp_ts_applied_to_id= tp.id_todo
				where ts.timesheet_id = \"$this->timesheet_id\"
				and		(te.timexp_ts_applied_to_type = 2  
						and bt.project_id = timesheet_project 
						or 
						te.timexp_ts_applied_to_type = 1 
						and ta.task_project = timesheet_project
					    or
					    te.timexp_ts_applied_to_type = 4 
						and tp.project_id = timesheet_project
					    )					
				"; 
		}
		else{
		$sql = "
		select distinct te.* 
				from timesheets ts 
				inner join timexp_ts te on 
					ts.timesheet_id = te.timexp_ts_timesheet 
				where ts.timesheet_id = \"$this->timesheet_id\"
				and	te.timexp_ts_applied_to_type = '3'  
				and timesheet_project = '0'			
				"; 
		}

		$sql .= "\n\t order by timexp_ts_date";

		//echo "<pre>";var_dump($sql); echo "</pre>";
		return db_loadList($sql);	
	}
	
	function getUnassignedTimexp($timexp_type, $project){
		global $AppUI;
		$user = $AppUI->user_id;

		$sql="select distinct te.*, ta.*, td.*, bt.*
					from	timexp te
					left join tasks ta on te.timexp_applied_to_id=ta.task_id
					left join btpsa_bug_table bt on te.timexp_applied_to_id=bt.id
					left join project_todo td on te.timexp_applied_to_id=td.id_todo
		    	where timexp_creator = '$user'
					and		timexp_type = '$timexp_type'
					and		timexp_applied_to_type in (1,2,4)
					and		(te.timexp_applied_to_type = 2  
							and bt.project_id = '$project' 
							or 
							te.timexp_applied_to_type = 1 
							and ta.task_project = '$project'
					        or 
							te.timexp_applied_to_type = 4 
							and td.project_id = '$project'
					        )						
					and   (timexp_timesheet is NULL 
								or	timexp_last_status in (2,4) )
					and		timexp_last_status in(0, 2, 4)
					order by timexp_date";		
		


		//echo "<pre>";var_dump($sql); echo "</pre>";
		return db_loadList($sql);	
	}
	
	function getListTimesheetStatus(){
		global $AppUI;
		$user = $AppUI->user_id;

		$sql="select tss.timesheetstatus_id
					, tss.timesheetstatus_user
					, tss.timesheetstatus_status
					, tss.timesheetstatus_date
					, tss.timesheetstatus_description
					, tss.timesheetstatus_timesheet
					, concat(u.user_last_name,', ',u.user_first_name) user_name
					
					from timesheetstatus tss
					inner join users u on u.user_id = tss.timesheetstatus_user

					where timesheetstatus_timesheet= \"$this->timesheet_id\""; 

		$sql .= "\n\t order by timesheetstatus_date";

		//echo "<pre>";var_dump($sql); echo "</pre>";
		return db_loadList($sql);	
	}

	
	/**
	 * @return NULL if success, error msg other
	 * @desc Replica los timexp asignados al timesheet en nuevos registros que quedan con el estado del timesheet
	 */
	function replyAssignedTimexp(){
		$msg = NULL;
		if (! (is_numeric($this->timesheet_id) && $this->timesheet_id > 0)){
			return "This is an object method, there is no timesheet loaded.";
		}
			
		$sql = "select distinct timexp_id from timexp where timexp_timesheet = $this->timesheet_id";
		$list = db_loadList($sql);
		for($i=0;$i<count($list) && $msg===NULL;$i++){
			$id = $list[$i]["timexp_id"];
			$obj = new CTimExp();
			$obj->load($id);
			$new = new CTimExp_TS();
			$new->timexp_ts_id="";
			
			//actualizo el estado del timexp
			$obj->timexp_last_status = $this->timesheet_last_status;
			
			$new->timexp_ts_ttimexp = $obj->timexp_id;	
			$new->timexp_ts_type = $obj->timexp_type;	
			$new->timexp_ts_name = $obj->timexp_name;	
			$new->timexp_ts_description = $obj->timexp_description;	
			$new->timexp_ts_creator = $obj->timexp_creator;	
			$new->timexp_ts_date = $obj->timexp_date;	
			$new->timexp_ts_value = $obj->timexp_value;	
			$new->timexp_ts_applied_to_type = $obj->timexp_applied_to_type;	
			$new->timexp_ts_applied_to_id = $obj->timexp_applied_to_id;	
			$new->timexp_ts_billable = $obj->timexp_billable;	
			$new->timexp_ts_last_status = $obj->timexp_last_status;	
			$new->timexp_ts_start_time = $obj->timexp_start_time;	
			$new->timexp_ts_end_time = $obj->timexp_end_time;	
			$new->timexp_ts_contribute_task_completion = $obj->timexp_contribute_task_completion;	
			$new->timexp_ts_timesheet = $obj->timexp_timesheet;	
			$new->timexp_ts_save_date = $obj->timexp_save_date;			
			
			
			if (($msg = $new->store())) {
				return $msg;
			}	

			if (($msg = $obj->store())) {
				return $msg;
			}			
			unset ($obj);
			unset ($new);
			unset ($id);
		}

		return $msg;
	
	}
	
	function notify($old_status=NULL){
		global 	$timexp_type, $timexp_types, $timexp_status, $AppUI,
						$timexp_status_color, $name_sheets, $ts_status_transition, $qty_units;
						
		$usr = new CUser();
		$usr->load($this->timesheet_user);
		$prefs = CUser::getUserPrefs($usr->user_id);
		$user_language = isset($prefs["LOCALE"]) ? $prefs["LOCALE"] : $AppUI->getConfig("host_locale");
		
		$Ccompany = new CCompany();
		if($Ccompany->load($usr->user_company)){
			$strEmailFrom = $Ccompany->company_email;
		}else{
			$strEmailFrom = $AppUI->getConfig("mailfrom");
		}
		
		$usr_mail = $usr->user_first_name." ".$usr->user_last_name." <".$usr->user_email.">";
		
		$df = $AppUI->getPref('SHDATEFORMAT');
		$tf = $AppUI->getPref( 'TIMEFORMAT' );
		
		$send_admin = $AppUI->getConfig("timexp_notify_admin");
		$send_creator = $AppUI->getConfig("timexp_notify_creator");
		$send_supervisors = $AppUI->getConfig("timexp_notify_supervisors");
		$test_notify = $AppUI->getConfig("timexp_notify_test");
		$test_notify_file = $AppUI->getConfig("timexp_notify_file");
		$mail_from = $strEmailFrom;
		
		$base_url = $AppUI->getConfig('base_url');
		$file_url = "index.php?m=timexp&a=viewsheet&timesheet_id=".$this->timesheet_id;
		$url = $base_url."/".$file_url;
		//$url = "<a href=\"".$url."\" >".$url."</a>";
		
		
		if ($old_status === NULL){
			$title["en"] = "[PSA] ".$AppUI->_to("en",$name_sheets[$this->timesheet_type])." - ".$AppUI->_to("en","New record");
			$title["es"] = "[PSA] ".$AppUI->_to("es",$name_sheets[$this->timesheet_type])." - ".$AppUI->_to("es","Nuevo Registro");
			$msgCreator = $AppUI->_("You have added a new ".$name_sheets[$this->timesheet_type]);
			$msgSuperv["en"] = $AppUI->_to("en","A new ".$name_sheets[$this->timesheet_type]." was created, and you are one of its supervisors.");
			$msgSuperv["es"] = $AppUI->_to("es","Una nueva".$name_sheets[$this->timesheet_type]." fue creada, y usted es uno de los supervisores.");
			
		}else{
			$title["en"] = "[PSA] ".$AppUI->_to("en",$name_sheets[$this->timesheet_type])." - ".$AppUI->_to("en","Status Changed by ".$AppUI->user_first_name." ".$AppUI->user_last_name);
			//$title["es"] = "[PSA] ".$AppUI->_to("es",$name_sheets[$this->timesheet_type])." - ".$AppUI->_to("es","Status Changed");
			$title["es"] = "[PSA] ".$AppUI->_to("es",$name_sheets[$this->timesheet_type])." - ".$AppUI->_to("es","Estado Modificado por ".$AppUI->user_first_name." ".$AppUI->user_last_name);
			$msgCreator = $AppUI->_("Your ".$name_sheets[$this->timesheet_type]." has a new status: ".$timexp_status[$this->timesheet_last_status].".");
			$msgSuperv["en"] = $AppUI->_to("en","One of your supervised ".$name_sheets[$this->timesheet_type]." changed its status.");
			//$msgSuperv["es"] = $AppUI->_to("es","One of your supervised ".$name_sheets[$this->timesheet_type]." changed its status.");
			$msgSuperv["es"] = $AppUI->_to("es", "La siguiente planilla a cambiado de estado y requiere supervisión");
		}

		$data = $this->getTimesheetData();
		$from_date = new CDate($data['timesheet_start_date']);
		$to_date = new CDate($data['timesheet_end_date']);
		$date = new CDate($data['timesheet_date']);
		$ts_date = new CDate($data['timesheetstatus_date']);
		$hour_total=number_format(($data["totbil"]+$data["totnobil"]),2);
		
		$query_cia = "SELECT company_name, company_canal FROM companies WHERE company_id = '".$data['project_company']."'";
		$sql_cia = db_exec($query_cia);
		$data_cia = mysql_fetch_array($sql_cia);
		$company_name = $data_cia['company_name'];

		$query_canal = "SELECT company_name FROM companies WHERE company_id = '".$data_cia['company_canal']."'";
		$sql_canal = db_exec($query_canal);
		$data_canal = mysql_fetch_array($sql_canal);
		$company_canal = $data_canal['company_name'];

		
		$msgDetails["en"] = $AppUI->_to("en",$name_sheets[$this->timesheet_type]." details")."\n";
		$msgDetails["en"].= str_repeat("=",70)."\n";
		$msgDetails["en"].= $AppUI->_to("en","Id").": ".$data['timesheet_id']."\n";
		$msgDetails["en"].= $AppUI->_to("en","Creator").": ".$data['user_last_name'].", ".$data['user_first_name']."\n";
        
		if( $data['project_name']!="")
		{
		$msgDetails["en"].= $AppUI->_to("en","Company").": ".$company_name."\n";
		$msgDetails["en"].= $AppUI->_to("en","Channel").": ".$company_canal."\n";
        $msgDetails["en"].= $AppUI->_to("en","Project").": ".$data['project_name']."\n";
		}
		else{
		$msgDetails["en"].= $AppUI->_to("en","Project").": Internal\n";
		}


		$msgDetails["en"].= $AppUI->_to("en","Project").": ".$project_name."\n";
		$msgDetails["en"].= $AppUI->_to("en","Creation Date").": ".$date->format($df)." ".$date->format($tf)."\n";
		$msgDetails["en"].= $AppUI->_to("en","Period From").": ".$from_date->format($df)." - ".$AppUI->_to("en","To").": ".$to_date->format($df)."\n";	
		$msgDetails["en"].= $AppUI->_to("en","Total ".$qty_units[$data["timesheet_type"]]).": ".$hour_total."\n";
		$msgDetails["en"].= $AppUI->_to("en","Billables").": ".number_format($data["totbil"],2)."\n";
		$msgDetails["en"].= $AppUI->_to("en","No billables").": ".number_format($data["totnobil"],2)."\n";
		

		if (($data["timesheet_last_status"]=="2")||($data["timesheet_last_status"]=="3"))
		{
		$msgDetails["en"].= $AppUI->_to("en","Status").": ".$AppUI->_to("en",$timexp_status[$data["timesheet_last_status"]])."\n".$AppUI->_to("en","Supervised by").": ".$AppUI->user_first_name." ".$AppUI->user_last_name."\n";
		}
		else{
		$msgDetails["en"].= $AppUI->_to("en","Status").": ".$AppUI->_to("en",$timexp_status[$data["timesheet_last_status"]])."\n";
		}
      //$msgDetails["en"].= "\t".$AppUI->_to("en","Date").": ".$ts_date->format($df)."\n";	
		$msgDetails["en"].= $AppUI->_to("en","Comments").": ".$data["timesheetstatus_description"]."\n";	

        

		$msgDetails["es"] = $AppUI->_to("es",$name_sheets[$this->timesheet_type]." details")."\n";
		$msgDetails["es"].= str_repeat("=",70)."\n";
		$msgDetails["es"].= $AppUI->_to("es","Id").": ".$data['timesheet_id']."\n";
		$msgDetails["es"].= $AppUI->_to("es","Creator").": ".$data['user_last_name'].", ".$data['user_first_name']."\n";

		if( $data['project_name']!="")
		{
		$msgDetails["es"].= $AppUI->_to("es","Company").": ".$company_name."\n";
		$msgDetails["es"].= $AppUI->_to("es","Channel").": ".$company_canal."\n";
		$msgDetails["es"].= $AppUI->_to("es","Project").": ".$data['project_name']."\n";
		}
		else{
        $msgDetails["es"].= $AppUI->_to("es","Project").": Interno \n";
		}

		$msgDetails["es"].= $AppUI->_to("es","Creation Date").": ".$date->format($df)." ".$date->format($tf)."\n";
		$msgDetails["es"].= $AppUI->_to("es","Period From").": ".$from_date->format($df)." - ".$AppUI->_to("es","To").": ".$to_date->format($df)."\n";	
		$msgDetails["es"].= $AppUI->_to("es","Total ".$qty_units[$data["timesheet_type"]]).": ".$hour_total."\n";
		$msgDetails["es"].= $AppUI->_to("es","Billables").": ".number_format($data["totbil"],2)."\n";
		$msgDetails["es"].= $AppUI->_to("es","No billables").": ".number_format($data["totnobil"],2)."\n";
		

		if (($data["timesheet_last_status"]=="2")||($data["timesheet_last_status"]=="3"))
		{
		$msgDetails["es"].= $AppUI->_to("es","Status").": ".$AppUI->_to("es",$timexp_status[$data["timesheet_last_status"]])."\n".$AppUI->_to("en","Supervisado por").": ".$AppUI->user_first_name." ".$AppUI->user_last_name."\n";
		}
		else{
		$msgDetails["es"].= $AppUI->_to("es","Status").": ".$AppUI->_to("es",$timexp_status[$data["timesheet_last_status"]])."\n";
		}

		//$msgDetails["es"].= "\t".$AppUI->_to("es","Date").": ".$ts_date->format($df)."\n";		
		$msgDetails["es"].= $AppUI->_to("es","Comments").": ".$data["timesheetstatus_description"]."\n";		
		$line = str_repeat("=",70);
		
		$msgBody["en"] = $line."\n".$url."\n".$line."\n".$msgDetails["en"]."\n".$line."\n";		
		$msgBody["es"] = $line."\n".$url."\n".$line."\n".$msgDetails["es"]."\n".$line."\n";	
		
		$msgBodySupervisor["en"] = $msgSuperv["en"]."\n".$msgBody["en"];
		$msgBodySupervisor["es"] = $msgSuperv["es"]."\n".$msgBody["es"];
		eval ("\$msgBodyCreator = \$msgCreator.\"\n\".\$msgBody[$user_language];");

		$spv="";

		// Envia para aprobar a todos los supervisores
		if ($data["timesheet_last_status"]=="1"){

			//mail para todos los supervisores
			$list = $this->getSupervisors();

			for($i=0; $i<=count($list); $i++){
                    
					$to_mail = $list[$i]["user_first_name"]." ".$list[$i]["user_last_name"]
												." <".$list[$i]["user_email"].">";
                    
					$spv_prefs = CUser::getUserPrefs($list[$i]["user_id"]);				
					$spv_locale = $spv_prefs["LOCALE"] ? $spv_prefs["LOCALE"] : $AppUI->cfg['host_locale'];
                    				    
					if(($send_supervisors) && ($list[$i]["user_type"]!= "1")){
					mail($to_mail, $title[$spv_locale],$msgBodySupervisor[$spv_locale], 'From: '.$mail_from);
					}

					if(($send_admin) && ($list[$i]["user_type"]== "1")){
					mail($to_mail, $title[$spv_locale],$msgBodySupervisor[$spv_locale], 'From: '.$mail_from);
					}
			}
		
		}
     
	    // El supervisor aprueba o desaprueba la planilla notifico al user que lleno la planilla
		if (($data["timesheet_last_status"]=="2")||($data["timesheet_last_status"]=="3"))
		{
	    	mail($usr_mail, $title[$AppUI->user_locale],$msgBodyCreator, 'From: '.$mail_from);
		}

		
		if ($test_notify){
			$msg = $line."\nFecha: ".date("d/m/Y")."\n";
			$msg .= "send_creator: $send_creator\n";
			$msg .= "send_supervisors: $send_supervisors\n";
			$msg .= "usuario: $usr_mail\n";
			$msg .= "supervisores: $spv \n";
			$msg .= "title[en]: {$title['en']} \n";
			$msg .= "title[es]: {$title['es']} \n";
			$msg .= 'From: '.$mail_from."\n";
			$msg .= "Body sup[en]: \n".$line."\n".$msgBodySupervisor["en"]."\n";
			$msg .= "Body sup[es]: \n".$line."\n".$msgBodySupervisor["es"]."\n";
			$msg .= "Body usr: \n".$line."\n".$msgBodyCreator."\n\n\n";
			
			$fp = fopen($test_notify_file, "a");
			fputs($fp, $msg,strlen($msg));
			fclose($fp);
		}
			
	}
	
	function getSupervisedProjects(){
		
		$timesheets = CTimesheet::getSupTimesheets(array());
		

		if (count($timesheets)>0){
			$sql = "select distinct project_id, project_name
					from timesheets inner join projects on timesheet_project = project_id
					";			
			$sql .= "where	timesheet_id in (".implode(",", $timesheets).")";
		}else{
			return array();
		}
		
		return db_loadHashList($sql);
	
	}
	
	function getMyTimesheetProjects(){
		$timesheets = CTimesheet::getMyTimesheets(array());	
		
		if (count($timesheets)>0){
			$ids = array();
			
			for($i=0; $i < count($timesheets); $i++)
				$ids[] = $timesheets[$i]["timesheet_id"];
			$sql = "select distinct project_id, project_name
					from timesheets inner join projects on timesheet_project = project_id
					";			
			$sql .= "where	timesheet_id in (".implode(",", $ids).")";
		}else{
			return array();
		}
		
		return db_loadHashList($sql);		
	}
	
}


/**
 *	Timesheet Class
 *	
 */
class CTimesheetStatus extends CDpObject {
/** @var int Primary Key */
	var $timesheetstatus_id = NULL;
/** @var string */

// these next fields should be ported to a generic address book
	var $timesheetstatus_user = NULL;
	var $timesheetstatus_timesheet = NULL;
	var $timesheetstatus_date = NULL;
	var $timesheetstatus_status = NULL;
	var $timesheetstatus_description = NULL;

	function CTimesheetStatus() {
		$this->CDpObject( 'timesheetstatus', 'timesheetstatus_id' );
	}
	// overload check
	function check() {
		if ($this->timesheetstatus_id === NULL) {
			return 'timesheetstatus id is NULL';
		}
		$this->timesheetstatus_id = intval( $this->timesheetstatus_id );

		return NULL; // object is ok
	}

	// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		return true;
	}

}

	
/**
 *	Rendition Class
 *	@todo Move the 'address' fields to a generic table
 */
class CTimExp_TS extends CDpObject {
/** @var int Primary Key */
	var $timexp_ts_id = NULL;
/** @var string */
// these next fields should be ported to a generic address book
	var $timexp_ts_timexp = NULL;
	var $timexp_ts_type = NULL;	
	var $timexp_ts_name = NULL;
	var $timexp_ts_description = NULL;
	var $timexp_ts_creator = NULL;
	var $timexp_ts_date = NULL;
	var $timexp_ts_value = NULL;
	var $timexp_ts_applied_to_type = NULL;
	var $timexp_ts_applied_to_id = NULL;
	var $timexp_ts_billable = NULL;
	var $timexp_ts_last_status = NULL;
	var $timexp_ts_start_time = NULL;
	var $timexp_ts_end_time = NULL;
	var $timexp_ts_contribute_task_completion = NULL;
	var $timexp_ts_timesheet = NULL;
	var $timexp_ts_save_date = NULL;
	
	function CTimExp_TS() {
		$this->CDpObject( 'timexp_ts', 'timexp_ts_id' );
	}

// overload check
	function check() {
		if ($this->timexp_ts_id === NULL) {
			return 'timexp_ts id is NULL';
		}
		$this->timexp_ts_id = intval( $this->timexp_ts_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		
		return true;

	}
	
	function canRead(){
		global $AppUI;
		
		$timexp_ts_id = $this->timexp_ts_id;
		
		if (!$timexp_ts_id)
			return false;

		$timexp = new CTimExp();
		if ($timexp->load($this->timexp_ts_timexp)){
			return $timexp->canRead();
		}else{
			return false;
		}
				

	}

	function canSupervise(){
		global $AppUI;
		$timexp_ts_id = $this->timexp_ts_id;
		
		if (!$timexp_ts_id)
			return false;

		$timexp = new CTimExp();
		if ($timexp->load($this->timexp_ts_timexp)){
			return $timexp->canSupervise();
		}else{
			return false;
		}

	}	

}
	
	
	
	
	
	

?>
