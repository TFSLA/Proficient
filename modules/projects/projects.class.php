<?php /* PROJECTS $Id: projects.class.php,v 1.6 2009-07-27 14:13:29 nnimis Exp $ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 1.6 $
*/

require_once( $AppUI->getSystemClass ('dp' ) );
require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'admin' ) );
require_once( $AppUI->getModuleClass( 'files' ) );
require_once( $AppUI->getModuleClass( 'forums' ) );
require_once( $AppUI->getModuleClass( 'system' ) );
require_once( $AppUI->getModuleClass( 'emailalerts' ) );
//require_once( "{$AppUI->cfg['root_dir']}/modules/projects/baselines.class.php");
if (!isset($pstatus))
	@include_once( "./functions/projects_func.php" );



function actual_budget ($project_id){

	/*$sql="SELECT SUM(CASE th.timexp_type
						WHEN 1 THEN (th.timexp_value*costperhour)
						WHEN 2 THEN th.timexp_value
						WHEN 3 THEN (th.timexp_value*costperhour)
						ELSE 0
					END) AS cost
				FROM projects AS p
				LEFT JOIN tasks AS t
					ON (t.task_project = p.project_id)
				LEFT JOIN timexp AS th
					ON (th.timexp_applied_to_id = t.task_id AND th.timexp_last_status = 3)
				LEFT JOIN users AS u
					ON (u.user_id=th.timexp_creator)
				WHERE p.project_id='$project_id'";
				$vec=db_fetch_array(db_exec($sql));*/

	$sql = "select DISTINCT task_id from tasks where task_project = '$project_id'";
	$query = mysql_query($sql);

	$tareas_del_projecto = db_loadColumn($sql);


	if(count($tareas_del_projecto)!=0)
    {
		$sql = "select
				timexp_id, timexp_creator, timexp_last_status, timexp_value, timexp_cost from timexp
				where
				timexp_applied_to_id IN (" . implode( ',', $tareas_del_projecto ) . ")
				and timexp_last_status = '3'
		";

		$query = mysql_query($sql);

		$cost_ap = 0;

		while($cost=mysql_fetch_array($query))
		{
		   $cost = $cost['timexp_cost'] * $cost['timexp_value'];

		   $cost_ap = $cost_ap + $cost;
		}
	}
	else
	{
	   $cost_ap = 0;
	}

	       $sql = "select distinct
					sum(te.timexp_value)
					from timexp te inner join tasks ta on te.timexp_applied_to_id = ta.task_id
					where	ta.task_project = $project_id
					and 	te.timexp_type = 2;";

			$total_expenses = db_loadResult($sql);

	$presupuesto_total_actual = $cost_ap + $total_expenses;

	$project->project_actual_budget= $presupuesto_total_actual;
	$cost= round ($presupuesto_total_actual, 2);
	return $cost;
}

function actual_rrhh_real_cost ($project_id){

	/*$sql="SELECT SUM(CASE th.timexp_type
						WHEN 1 THEN (th.timexp_value*costperhour)
						WHEN 3 THEN (th.timexp_value*costperhour)
						ELSE 0
					END) AS cost
				FROM projects AS p
				LEFT JOIN tasks AS t
					ON (t.task_project = p.project_id)
				LEFT JOIN timexp AS th
					ON (th.timexp_applied_to_id = t.task_id AND th.timexp_last_status = 3)
				LEFT JOIN users AS u
					ON (u.user_id=th.timexp_creator)
				WHERE p.project_id='$project_id'";
	$vec=db_fetch_array(db_exec($sql));
	$cost=round ($vec['cost'], 2);*/

    $sql = "select DISTINCT task_id from tasks where task_project = '$project_id'";
	$query = mysql_query($sql);

	$tareas_del_projecto = db_loadColumn($sql);


	if (count($tareas_del_projecto)!=0)
	{
		$sql = "select
				timexp_id, timexp_creator, timexp_last_status, timexp_value, timexp_cost from timexp
				where
				timexp_applied_to_id IN (" . implode( ',', $tareas_del_projecto ) . ")
				and timexp_last_status = '3'
		";

		$query = mysql_query($sql);

		$cost_ap = 0;

		while($cost=mysql_fetch_array($query))
		{
		   $cost = $cost['timexp_cost'] * $cost['timexp_value'];

		   $cost_ap = $cost_ap + $cost;
		}

		if ($cost_ap != 0)
		{
		$cost = number_format($cost_ap,2);
		}
		else
		{
		$cost = 0;
		}
	}
	else
	{
	  $cost = 0;
	}

	return $cost;
}

function project_percent_completed_work($project_id){

  /* suma de las tareas completadas/trabajo estimado total del proyecto.
     sumo las tareas del proyecto marcadas como terminadas lo multiplico por 100 y lo divido por la cantidad de tareas total del proyecyo.
  */

  $query1 = "select sum(task_work) from tasks where task_project='$project_id' and task_complete='1' and task_dynamic='0' ";
  $sql1 = mysql_query($query1);
  $cant_comp = mysql_fetch_array($sql1);

  $query2 = "select sum(task_work) from tasks where task_project='$project_id'  AND task_wbs_level = '0' ";
  $sql2 = mysql_query($query2);
  $cant_total = mysql_fetch_array($sql2);

   if ($cant_total[0]!=0)
	{
	$porc_comp_w = ($cant_comp[0] * 100)/$cant_total[0];
	}
    else
	{
	$porc_comp_w = "N/A";
	}

  return $porc_comp_w;

}


function actual_rrhh_estimated_cost ($project_id){

    $SQL0= "SELECT SUM(task_target_budget_hhrr) as est_hhrr
	FROM tasks
	WHERE task_project= '$project_id'
	";

	$tc=db_fetch_array(db_exec($SQL0));

	//echo "<pre>$SQL0</pre>";

	if(count($tc)!=0)
	{
	 $arec = $tc['est_hhrr'];
	}
    else
    {
	 $arec='N/A';
	}

	return $arec;
}

function presupuesto_total_estimado ($project_id,$other_estimated_cost){

	$presupuesto_total_estimado = actual_rrhh_estimated_cost ($project_id)+$other_estimated_cost;

	return $presupuesto_total_estimado;
}

function project_percent_completed_oozed_cost($project_id, $gastos){

	$costo_tareas_terminadas = actual_budget ($project_id);

	$costo_estimado_total = presupuesto_total_estimado($project_id,$gastos);

	IF ($costo_estimado_total!=0){
		$arec=($costo_tareas_terminadas/$costo_estimado_total)*100;
	}
	else $arec='N/A';

	return $arec;
}

function total_hours ($project_id){
	$sql="SELECT SUM(task_work) AS total_hours FROM tasks t WHERE task_project='$project_id' AND task_dynamic = 0";
	$vec=db_fetch_array(db_exec($sql));
	return $vec['total_hours'];
}

function total_exp ($project_id){
	$sql="SELECT SUM(th.timexp_value) as exp
				FROM projects AS p
				LEFT JOIN tasks AS t
					ON (t.task_project = p.project_id)
				LEFT JOIN timexp AS th
					ON (th.timexp_applied_to_id = t.task_id AND timexp_type=2)
				WHERE p.project_id='$project_id'";
		$vec=db_fetch_array(db_exec($sql));

		$total_expense = number_format($vec['exp'],2);

		if($total_expense =="")
	    {
		 $total_expense = "0";
	    }

	return $total_expense;
}


/**
 * The Project Class
 */
class CProject extends CDpObject {
	var $project_id = NULL;
	var $project_company = NULL;
	var $project_canal = NULL;
	var $project_department = NULL;
	var $project_name = NULL;
	var $project_short_name = NULL;
	var $project_owner = NULL;
	var $project_url = NULL;
	var $project_email_docs = NULL;
	var $project_email_support = NULL;
	var $project_email_todo = NULL;
	var $project_demo_url = NULL;
	var $project_start_date = NULL;
	var $project_end_date = NULL;
	var $project_actual_end_date = NULL;
	var $project_status = NULL;
	var $project_manual_percent_complete = NULL;
	var $project_color_identifier = NULL;
	var $project_description = NULL;
	var $project_target_budget = NULL;
	var $project_actual_budget = NULL;
	var $project_creator = NULL;
	var $project_active = NULL;
	var $project_private = NULL;
	var $project_target_budget_update = NULL;
	var $project_total_hours_update = NULL;
	var $project_other_estimated_cost = NULL;

	function CProject() {
		$this->CDpObject( 'projects', 'project_id' );
	}

	function check() {
	// ensure changes of state in checkboxes is captured
		$this->project_other_estimated_cost = intval( $this->project_other_estimated_cost );
		$this->project_active = intval( $this->project_active );
		$this->project_private = intval( $this->project_private );

		return NULL; // object is ok
	}

	function canRead(){
		global $AppUI;
		$canRead=false;
		if (@$this->project_id){
			$alw=$this->getAllowedRecords($AppUI->user_id);

			//$canReadCompany = !getDenyRead( 'companies', $this->project_company  );
			/*
			$owners=$this->getOwners();
			$isOwner = ($this->project_owner == $AppUI->user_id  || isset($owners[$AppUI->user_id]));		*/

			//$canRead = (isset($alw[$this->project_id]) && $canReadCompany); // || $isOwner ;
			$canRead = isset($alw[$this->project_id]);
		}
		return $canRead;
	}

	function canReadDetails(){
		global $AppUI;
		$canRead=false;
		if (@$this->project_id){
			$prmProject=$this->projectPermissions($AppUI->user_id);
			$canRead = $this->canRead() && ($prmProject[6]) && $prmProject[6]!= PERM_DENY;
		}
		return $canRead;
	}

	function canReadEcValues(){
		global $AppUI;
		$canRead=false;
		if (@$this->project_id){
			$prmProject=$this->projectPermissions($AppUI->user_id);
			$canRead = $this->canRead() && isset($prmProject[7]) && $prmProject[7]!= PERM_DENY;
		}
		return $canRead;
	}

	function canReadCompany(){
		global $AppUI;
		$canRead=false;
		if (@$this->project_company){
			$canRead = !getDenyRead( "companies", $obj->project_company );
		}
		return $canRead;
	}

	function canCreate(){
		return !getDenyEdit( "projects" );
	}

	function canEdit(){
		global $AppUI; //, $debuguser;
		$canEdit=false;
		if ($AppUI->user_type == 1)
			return true;
		if (@$this->project_id){
			$owners=$this->getOwners();
			$canEdit = $this->canRead() && ($this->project_owner == $AppUI->user_id  || isset($owners[$AppUI->user_id]));
		}
		return $canEdit;
	}

	function canManageRoles(){
		global $AppUI;
		$rta=false;
		if ($AppUI->user_type == 1)
			return true;
		if (@$this->project_id){
			$owners=$this->getOwners();
			$rta = ($this->project_owner == $AppUI->user_id  || isset($owners[$AppUI->user_id]));
		}
		return $rta;
	}

	function canAddTasks(){
		global $AppUI, $debuguser;
		$value=false;
		if (@$this->project_id){
			$prmProject=$this->projectPermissions($AppUI->user_id);
			$value = ($prmProject[1]==PERM_EDIT);
			//echo "<pre>";var_dump($prmProject);echo "</pre>";
		}
		return $value;
	}

	function getOwner($project_id=null){
		$project = ( $project_id ? $project_id : $this->project_id );

		$sql = "SELECT u.user_id , u.user_email
			   	FROM users u, projects p
			 	WHERE p.project_id = $project
			 	AND p.project_owner = u.user_id";

		$rta = db_loadHashList($sql);

		return $rta;
	}

	function getUsersMyOwnerProjects($selectId, $p_project_id, $company_id, $canal_id, $selected="", $allUsers = true, $usercompany = 0)
	{
		global $AppUI;

		$strUsersSqlWhere = "";

		if($p_project_id > 0){
			$strUsersSqlWhere .= " AND p.project_id='$p_project_id' ";
		}

		if($company_id > 0){
			$strUsersSqlWhere .= " AND p.project_company = '$company_id' ";
		}

		if ($canal_id > 0){
		   $strUsersSqlWhere .= "AND p.project_canal = '$canal_id' ";
		}

		if ($usercompany > 0){
			$strUsersSqlWhere .= "AND u.user_company = '$usercompany' ";
		}

		$sql="
		SELECT DISTINCT(u.user_id), CONCAT(u.user_last_name, ', ', u.user_first_name)AS name
		FROM users u
		LEFT JOIN project_roles pr ON pr.user_id = u.user_id
		LEFT JOIN project_owners po ON po.project_id = pr.project_id
		LEFT JOIN projects p ON p.project_id = pr.project_id
		WHERE u.user_type <> '5'
		AND (p.project_owner = $AppUI->user_id OR po.project_owner = $AppUI->user_id)
		AND (u.user_id <> $AppUI->user_id)
		AND (u.user_status = 0)
		$strUsersSqlWhere order by user_last_name,user_first_name
		";

		$list = db_loadHashList( $sql );

		//Agrego el elemento ALL al principio del vector.
		//Uso el + dado que como los indices son numericos(el id del usuario) al usar ARRAY_UNSHIFT o array_merge me reordena los indices y se pierde la asociasion con el ID.
		if($allUsers)
		{
			$all=array(0=> $AppUI->_("N/A"));
			$list = $all + $list;
		}

		return $list;
	}

	function getEditableRecords($uid, $fields='*', $orderby='', $index=null){
		global $AppUI;
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getEditableRecords failed" );

		$canEdit = !getDenyEdit( "projects", $this->project_id );

		$projects=array();
		$tmpprjlist = CProject::projectPermissions($uid);
		if (count($tmpprjlist)==0 || !$canEdit){
			return $projects;
		}
		$sql="select project_id, 1 from project_owners where project_owner = $uid";
		$userprojects=db_loadHashList($sql);

		foreach($tmpprjlist as $id=>$itemsperm){
			if (isset($itemsperm) && (	$itemsperm[1]==PERM_CHANGE ||
										($itemsperm[1]==PERM_EDIT && isset($userprojects[$id]))
										))
			{
				$projects[]=$id;
			}
		}

		$sql = "SELECT $fields"
			. "\nFROM projects"
			. "\nWHERE project_id ". (count($projects) > 0 ? "in (".implode( ',', $projects ) . ")"
									:	" is null")
			. ($orderby ? "\nORDER BY $orderby" : '')		;

		return db_loadHashList( $sql, $index );
	}

	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) {
		global $AppUI;
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
		$allowed = array();

		$usr = new CUser();
		if (!$usr->load($uid, false)){
			$AppUI->setMsg( 'User' );
			$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
			$AppUI->redirect();

		}
		//el SYSADMIN siempre puede ver todos los proyectos
		if ($usr->user_type == 1){
			$sql = "SELECT $fields, -1 permission_value"
				. "\nFROM projects ";

			if (@$extra['from']) {
			$sql .= ',' . $extra['from'];
		    }

			$sql .=  "\n WHERE 1=1 ";

			if (@$extra['where']) {
			$sql .= "\n\t" . $extra['where'];
		    }

			$sql .= ($orderby ? "\nORDER BY $orderby" : '');

			return db_loadHashList( $sql, $index );

		}

		//obtengo los proyectos en donde el usuario es responsable, administrador o usuario del proyecto
		$sql = "
		select project_id from projects where project_owner = $uid
		union
		select project_id from project_owners where project_owner = $uid
		union
		select project_id from project_roles where  role_id = 2 and user_id = $uid
		";
       //echo "<pre>$sql</pre>";
		$allowed =  db_loadColumn($sql);

		$sql = "select project_company from projects  where project_id IN (" . implode( ',', $allowed ) . ")";

		$companies = (count($allowed) > 0 ? db_loadColumn($sql) : array("-1"));
/*
echo "<pre>";
var_dump($companies);
echo "</pre>";
*/

		// Si el usuario tiene permisos sobre el m?ulo proyectos entonces lista el proyecto
		$sql = "SELECT $fields"
			. "\nFROM projects, permissions pperm, permissions cperm";

		if (@$extra['from']) {
			$sql .= ',' . $extra['from'];
		}

		$sql .= "\nWHERE pperm.permission_user = $uid"
			. "\n	AND pperm.permission_value <> 0"
			. "\n	AND ("
			. "\n		(pperm.permission_grant_on = 'all')"
			. "\n		OR (pperm.permission_grant_on = 'projects' AND pperm.permission_item = -1)"
			. "\n		OR (pperm.permission_grant_on = 'projects' AND pperm.permission_item = project_id)"
			. "\n	)"
			. (count($allowed) > 0 ? "\n\tAND project_id IN (" . implode( ',', $allowed ) . ')' : '');

		$sql .= "\n AND cperm.permission_user = $uid"
					. "\n	AND cperm.permission_value <> 0"
					. "\n	AND ("
					. "\n		(cperm.permission_grant_on = 'all')"
					. "\n		OR (cperm.permission_grant_on = 'companies' AND cperm.permission_item = -1)"
					. "\n		OR (cperm.permission_grant_on = 'companies' AND cperm.permission_item = project_company)"
					. "\n	)"
					. (count($companies) > 0 ? "\n\tAND project_company IN (" . implode( ',', $companies ) . ')' : '');

		if (@$extra['where']) {
			$sql .= "\n\t" . $extra['where'];
		}

		$sql .= ($orderby ? "\nORDER BY $orderby" : '');
		$prjAllowed = db_loadHashList( $sql, $index );
		
		return $prjAllowed;
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		// TODO: check if user permissions are considered when deleting a project
		return true;

		// NOTE: I uncommented the dependencies check since it is
		// very anoying having to delete all tasks before being able
		// to delete a project.

		/*
		$tables[] = array( 'label' => 'Tasks', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_project' );
		// call the parent class method to assign the oid
		return CDpObject::canDelete( $msg, $oid, $tables );
		*/
	}

/**
* @todo Parent store could be partially used
*/
	function store() {
		GLOBAL $AppUI, $new_status;

		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}
		if( $this->project_id ) {
			$this->_action = 'updated';

			// check if target budget was modified
			$sql = "select project_target_budget
					from projects
					where project_id = '$this->project_id'";
			$old_target_budget = db_loadResult($sql);
			if ( floatval($this->project_target_budget) != floatval($old_target_budget)){
				$today = new CDate();
				$this->project_target_budget_update = $today->format(FMT_DATETIME_MYSQL);
			}


			$ret = db_updateObject( 'projects', $this, 'project_id', false );

			//update the permissions on roles for the project users role
			$sql = "update role_permissions
					set company_id = $this->project_company
					where project_id=$this->project_id and role_id=2";
			db_exec( $sql );

			//delete the permissions on roles whe the company_id changes
			/*$sql = "delete role_permissions.*
					from  role_permissions rp inner join projects p on rp.project_id = p.project_id
					where company_id <> -1 and company_id <> project_company
					and p.project_id = $this->project_id
					";*/

			$sql = "delete role_permissions.*
					from  role_permissions
					where company_id <> -1 and company_id <> '".$this->project_company."'
					and project_id = '".$this->project_id."'
					";

			db_exec( $sql );


			//delete the user from de assigned role when (only specific roles)
			/*$sql = "delete project_roles.*
					from  project_roles pr inner join roles r on r.role_id = pr.role_id
					where
						pr.project_id = $this->project_id and r.role_type = 1
					";
			echo "<pre>$sql</pre>";
			db_exec( $sql ); */

			$sql = "SELECT role_id FROM roles WHERE role_type ='1' ";
			$roles_type1 = db_loadColumn($sql);

			if (count($roles_type1)>0)
			{
				$query_role_type = "AND role_id IN (" . implode( ',', $roles_type1 ) . ") ";
			}
			else
			{
				$query_role_type = "-1";
			}

			$sql = "delete project_roles.*
					from  project_roles
					where
						project_id = '".$this->project_id."' $query_role_type
					";

			db_exec( $sql );


			$retperm = true;
		} else {
			$this->_action = 'added';
			$today = new CDate();
			$this->project_target_budget_update = $today->format(FMT_DATETIME_MYSQL);
			$this->project_total_hours_update = $today->format(FMT_DATETIME_MYSQL);

			$ret = db_insertObject( 'projects', $this, 'project_id' );
			$enab = "UPDATE projects SET enabled ='1' WHERE project_id='$this->project_id'";
			$mysl = mysql_query($enab);

			$retperm = $this->assigndefaultpermissions($this->project_id);
			if (!$retperm){
				$msg .= $AppUI->_("Problems assigning permissions. Remember to assign the permissions manualy.");
			}
		}

		if( !$ret || !$retperm ) {
			$msg = get_class( $this )."::store failed <br />" . $msg . db_error();
		} else {
			if ($msg = $this->checkOverWorked())
				return $msg;
			else
				return NULL;
		}
	}

	function delete() {
		global $AppUI;
		$sql = "/****START PROJECT DELETION****/
				select task_id
				FROM tasks t
				WHERE task_project = $this->project_id";
		$lst = db_loadColumn($sql);
		for($i = 0; $i < count($lst); $i++){
			$obj = new CTask();
			if (!$obj->load($lst[$i], false)){
				$AppUI->setMsg( 'Tasks' );
				$AppUI->setMsg( "invalidID ".$lst[$i], UI_MSG_ERROR, true );
				$AppUI->redirect();
			}else{
				$obj->delete();
			}
			unset($obj);
		}

		$cals = $this->getCalendars();
		for($i = 0; $i < count($cals); $i++){
			$obj = new CCalendar();
			if (!$obj->load($cals[$i]["calendar_id"], false)){
				$AppUI->setMsg( 'Calendars' );
				$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
				$AppUI->redirect();
			}else{
				$obj->delete();
			}
			unset($obj);
		}

		$sql = "delete baselines.*, baseline_tasks.* , baseline_task_expenses.*
				FROM baselines b left join baseline_tasks bt on b.id = bt.baseline_id
				left join baseline_task_expenses bte on bt.id = bte.baseline_task_id
				where b.project_id = $this->project_id";

		//db_exec($sql);
	           $sql = "DELETE events.*, events_invitations.*
				FROM events e left join events_invitations ei on e.event_id = ei.event_id
				WHERE event_project = $this->project_id";

		//echo "<pre>$sql</pre>";
		//db_exec($sql);

		$sql = "DELETE FROM project_owners WHERE project_id = $this->project_id";
		db_exec($sql);
		$sql = "DELETE FROM role_permissions WHERE project_id = $this->project_id";
		db_exec($sql);
		$sql = "DELETE FROM project_roles WHERE project_id = $this->project_id";
		db_exec($sql);
		$sql = "DELETE FROM task_permissions WHERE task_project = $this->project_id";
		db_exec($sql);

/*		//borro todas las lineas base asociadas al proyecto
		$sql = "SELECT id FROM baselines WHERE project_id = $this->project_id";
		$lst = db_loadColumn($sql);
		for($i = 0; $i < count($lst); $i++){
			$obj = new CBaseline();
			if (!$obj->load($lst[$i], false)){
				$AppUI->setMsg( 'Baseline' );
				$AppUI->setMsg( "invalidID ".$lst[$i], UI_MSG_ERROR, true );
				$AppUI->redirect();
			}else{
				$obj->delete();
			}
			unset($obj);
		}
*/
		//borro todos los foros asociados al proyecto
		$sql = "SELECT forum_id FROM forums WHERE forum_project = $this->project_id";
		$lst = db_loadColumn($sql);
		for($i = 0; $i < count($lst); $i++){
			$obj = new CForum();
			if (!$obj->load($lst[$i], false)){
				$AppUI->setMsg( 'Forum' );
				$AppUI->setMsg( "invalidID ".$lst[$i], UI_MSG_ERROR, true );
				$AppUI->redirect();
			}else{
				$obj->delete();
			}
			unset($obj);
		}

		//borro todos los archivos asociados al proyecto
		$sql = "SELECT file_id FROM files WHERE file_project = $this->project_id";
		$lst = db_loadColumn($sql);
		for($i = 0; $i < count($lst); $i++){
			$obj = new CFile();
			if (!$obj->load($lst[$i], false)){
				$AppUI->setMsg( 'File' );
				$AppUI->setMsg( "invalidID ".$lst[$i], UI_MSG_ERROR, true );
				$AppUI->redirect();
			}else{
				$obj->delete();
			}
			unset($obj);
		}

		$sql = "DELETE FROM projects WHERE project_id = $this->project_id
		/****END PROJECT DELETION****/
		";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function updateUsers( $cslist ) {
		global $AppUI;
		//echo "Entra a updateUsers $cslist <br>";
	// Obtengo el nivel de permisos por defecto en webtracking para los usuarios de un proyecto
		$pu_access_level = $AppUI->getConfig('projects_users_default_webtracking_permission');

	// delete all current entries
		$sql = "DELETE FROM project_roles WHERE project_id = $this->project_id and role_id=2";
		//echo "Borro los usuarios: $sql <br>";
		db_exec( $sql );


	// process assignees
		$usrlst = array();
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $user_id) {
			if (intval( $user_id ) > 0) {
				echo "updateAssignedUser - $user_id <br>";
				$this->updateAssignedUser("2", $user_id, "100");
				// asignamos el usuario al proyecto
				//$sql = "REPLACE INTO project_roles (project_id, role_id, user_id) VALUES ( $this->project_id, 2,  $user_id)";
				//db_exec( $sql );
				$usrlst[]=$user_id;
			}
		}
	// delete all asignations in tasks to not assigned users
	          if(count($usrlst)>0){
		$sql = "
				delete user_tasks.*
				from tasks ta, user_tasks
				where ta.task_project = $this->project_id
				and ta.task_id = user_tasks .task_id
				and user_tasks .user_id not in (".implode($usrlst,", ").");";
		 // echo "<pre>$sql</pre>";
		  db_exec( $sql );
	          }else{
	          	$sql = "
				delete user_tasks.*
				from tasks ta, user_tasks
				where ta.task_project = $this->project_id
				and ta.task_id = user_tasks .task_id ";
		 // echo "<pre>$sql</pre>";
		  db_exec( $sql );
	          }

	}

	function updateAssignedUser($role_id, $user_id, $units){
		global $AppUI;
		$sql = "REPLACE INTO project_roles (project_id, role_id, user_id, user_units) VALUES ( '$this->project_id', '$role_id',  '$user_id', '$units')";
		$rta = db_exec( $sql );
		if (!$rta)
			$rta = db_error();
		else{
			$rta = 0;
				// Obtengo el nivel de permisos por defecto en webtracking para los usuarios de un proyecto
			$pu_access_level = $AppUI->getConfig('projects_users_default_webtracking_permission');

			//si no tiene permisos para el proyecto en webtracking se los colocamos
			$wt_perms = db_loadResult("select access_level from `btpsa_project_user_list_table` where project_id = $this->project_id and user_id = $user_id");

			if ($wt_perms == NULL || $wt_perms < $pu_access_level ){
				$query = "REPLACE
						  INTO `btpsa_project_user_list_table`
						    ( project_id, user_id, access_level )
						  VALUES
						    ( '$this->project_id', '$user_id', '$pu_access_level')";

				$rta = db_exec( $query );
				if (!$rta)
					$rta = db_error();
				else{
					$rta = 0;
						}
			}else{
				$rta = 0;
			}
		}
		return $rta;
	}

	function deleteAssignedUser($role_id, $user_id){
		$sql = "select user_tasks.task_id
				from tasks ta
					inner join user_tasks on ta.task_id = user_tasks .task_id
				where ta.task_project = '$this->project_id'
				and user_tasks.user_id = '$user_id';";
		$tasks = db_loadColumn($sql);

	// delete all asignations in tasks to the users
		$sql = "delete user_tasks.*
				from tasks ta, user_tasks
				where ta.task_project = $this->project_id
				and ta.task_id = user_tasks .task_id
				and user_tasks.user_id = $user_id;";
		//echo "<pre>$sql</pre>";
		$rta = db_exec( $sql );
		if (!$rta) $rta = db_error();
		else{
			$sql = "DELETE FROM project_roles
					WHERE	project_id = '$this->project_id'
					and		role_id = $role_id
					and		user_id	= $user_id;";
			$rta = db_exec( $sql );
			if (!$rta)	$rta = db_error();
			else{

				$query = "DELETE FROM `btpsa_project_user_list_table`
						  WHERE
						  		project_id = '$this->project_id'
						  and 	user_id = '$user_id'";
				$rta = db_exec( $query );
				if (!$rta)	$rta = db_error();
				else $rta = 0;

			}
		}

		// actualizo las tareas en las cuales el usuario estaba asignado
		if (!$rta){

			for($i=0; $i<count($tasks); $i++){
				$obj = new CTask();
				$obj->load($tasks[$i]);
				$obj->loadAssignedUsers();
				$obj->updateSchedule("1"); //cuando cambian los recursos
				$obj->store();
			}

		}
		return $rta;
	}

	function addAdministrator($user_id){
		global $AppUI;

		if ($this->canEdit()){
			$dt = getdate();
			$cr_date = db_datetime(($dt[0]));
			$sql = "REPLACE INTO project_owners (project_id, project_owner, creator_user, date_creation) VALUES ( $this->project_id, $user_id, $AppUI->user_id, '$cr_date')";
			$ret = db_exec( $sql );
			if(!$ret)
				return $ret;
			else{
				return (CUser::setWebtrackingPermissions($user_id)==NULL);
			}

		}

		return $AppUI->_("You have not enough permissions to perform this action.");

	}

	function deleteAdministrator($user_id){
		global $AppUI;

		if ($this->canEdit()){
			$admins = $this->getOwners();
			if (! isset($admins[$user_id]))
				return $AppUI->_("The selected user is not administrator on this project.");

			$sql = "DELETE FROM project_owners WHERE project_id = $this->project_id and project_owner = '$user_id'";
			$ret = db_exec( $sql );
			if(!$ret)
				return $ret;
			else{
				CUser::clearWebtrackingPermission($user_id, $this->project_id);
				return true;
			}
		}

		return $AppUI->_("You have not enough permissions to perform this action.");

	}

	function updateOwners( $cslist ) {
		global $AppUI;
	// delete all current entries
		$sql = "DELETE FROM project_owners WHERE project_id = $this->project_id";
		echo "Actualiza a los owners: <pre>".$sql."</pre> ";
		db_exec( $sql );

		$dt = getdate();
		$cr_date = db_datetime(($dt[0]));

	// process assignees
	            echo "Actualiza los usuarios <br>";
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $user_id) {
			if (intval( $user_id ) > 0) {
				$sql = "REPLACE INTO project_owners (project_id, project_owner, creator_user, date_creation) VALUES ( $this->project_id, $user_id, $AppUI->user_id, '$cr_date')";
				echo "$sql<br>";
				db_exec( $sql );

				CUser::setWebtrackingPermissions($user_id);
			}
		}

		echo "Sale de updateOwners";
	}


	function getOwners($project_id=null, $mail=null){
		$rta=array();
		if(is_null($project_id) && $this && is_null($this->project_id)){
			return 	$rta;
		}

		if(is_null($mail)){
			$selectField = ", CONCAT_WS(' ',u.user_first_name,u.user_last_name)";
		}else{
			$selectField = ", u.user_email";
		}
		$project_id = is_null($project_id) ?  ($this ? $this->project_id : 0) : $project_id ;

		$sql="SELECT u.user_id $selectField
			   FROM users u, project_owners t
			 WHERE t.project_id =$project_id
			 AND t.project_id <> 0
			 AND t.project_owner = u.user_id";

		$rta=db_loadHashList($sql);

		return $rta;
	}


	function getUsers($project_id=null, $mail=null){
		$rta=array();
		if(is_null($project_id) && is_null($this->project_id)){
			return 	$rta;
		}

		if(is_null($mail)){
			$selectField = ", CONCAT_WS(' ',u.user_first_name,u.user_last_name)";
		}else{
			$selectField = ", u.user_email";
		}

		$project_id = is_null($project_id) ?  $this->project_id : $project_id ;
		$sql = "
				SELECT u.user_id $selectField
				FROM users u inner join project_roles pr on u.user_id = pr.user_id
				WHERE pr.project_id = $project_id and pr.role_id=2 and u.user_type <>5
				ORDER BY user_first_name, user_last_name
				";

		$rta = db_loadHashList( $sql );

		return $rta;
	}


	function projectPermissions($task_user_id=null, $project_id=null){
		global $user_context, $AppUI;
		$where = "";
		$prmItems = array(1, 6, 7);
		if(is_null($task_user_id) && !isset($AppUI)){
			return null;
		}
		$task_user_id = is_null($task_user_id) ? $AppUI->user_id : $task_user_id ;

		if(!is_null($project_id) || (!is_null($this) && !is_null($this->project_id))){
			$project_id = is_null($project_id) ?  $this->project_id : $project_id ;
			foreach ($prmItems as $item){
				$rta[$item] = 9;
			}
		}else{
			$prjs=CProject::getAllowedRecords($task_user_id);
			foreach ($prjs as $pid => $datos){
				foreach ($prmItems as $item){
					$rta[$pid][$item] = 9;
				}
			}
		}
		//echo "<pre>projectPermissions( user_id=$task_user_id,  project_id=$project_id) <br></pre>";
		$projpermissions = $AppUI->pmPermissions["projpermissions$task_user_id"."_0"];
		//echo $projpermissions;
		// get the permissions for the user over all tasks
		if (!is_null($project_id)){
			if (isset($AppUI->pmPermissions["projpermissions$task_user_id"."_$project_id"]))
				$projpermissions = $AppUI->pmPermissions["projpermissions$task_user_id"."_$project_id"];
		}else{
		}


		//echo "<pre>";var_dump($projpermissions);echo "</pre>";
		if (is_array($projpermissions)){

			//echo "<pre> projpermissions :".sizeof($projpermissions)."  </pre>";
			if (sizeof($projpermissions) == 3){
				if (is_null($project_id) || !is_array($projpermissions[$project_id]) ){
					//echo "LEE: projpermissions de AppUI 1<br>";
					return $projpermissions;
				}else{
					if (isset($projpermissions[$project_id])){
						//echo "LEE: projpermissions de AppUI 2 <br>";
						return $projpermissions[$project_id];
					}
				}
			}else{
				if (!is_null($project_id)){
					//echo "LEE: projpermissions de AppUI 3 <br>";
					return $projpermissions;
				}
			}
		}
		//echo "LEE: projpermissions de la BD<br>";




		/*** System Administrator permissions ***/
		if ($AppUI->user_type == 1) {
			if (is_null($project_id)){
				$sql="
				select 	distinct
					t1.project_id
				,	item_id
				,	-1 permission_value
				from projects t1, task_permission_items
				where item_id in (".implode($prmItems,", ").")
				";
				$temp=db_loadList($sql);
				for($i=0; $i<count($temp); $i++){
					$rta[$temp[$i]["project_id"]][$temp[$i]["item_id"]]=$temp[$i]["permission_value"];
				}
			}else{
				$sql="
				select item_id, -1 permission_value
				FROM task_permission_items
				where item_id in (".implode($prmItems,", ").")
				";

				$rta = db_loadHashList($sql);
			}

			if (is_null($project_id)){
				$AppUI->pmPermissions["projpermissions$task_user_id"."_0"] = $rta;
			}else{
				$AppUI->pmPermissions["projpermissions$task_user_id"."_$project_id"] = $rta;
			}
			return $rta;
		}
		/*** end System Administrator permissions ***/

		/*** Propietario y administradores permissions pueden hacer todo (-1)***/
		if (is_null($project_id)){
			$sql="
			select 	distinct
					p.project_id
				,	item_id
				,	-1 permission_value
			from projects p
				left join  project_owners po on p.project_id = po.project_id
				, 	task_permission_items
			where (po.project_owner = $task_user_id
			or 		p.project_owner = $task_user_id)
			and 	item_id in (".implode($prmItems,", ").")
			";

			$prmOwner=db_loadList($sql);
		}else{
			$sql="
			select 	distinct
					item_id
				,	-1 permission_value
			from projects p
				left join  project_owners po on p.project_id = po.project_id
				, 	task_permission_items
			where (po.project_owner = $task_user_id
			or 		p.project_owner = $task_user_id)
			and 	p.project_id=$project_id
			and 	item_id in (".implode($prmItems,", ").")
			";
			//echo "<pre>$sql</pre>";

			$prmOwner = db_loadHashList($sql);

		}
		/*** FIN Propietario y administradores permissions ***/


		/*** Permisos especificos de usuario ***/
		if (is_null($project_id)){
			$sql="
				select distinct
					project_id
				,	item_id
				,	COALESCE(tp.task_permission_value, 9) permission_value
				from projects p
				left join( task_permission_items tpi, task_permissions tp ) ON
				(tp.task_permission_on=tpi.item_id and p.project_id = tp.task_project)
				where
						item_id in (".implode($prmItems,", ").")
				and 	task_user_id = $task_user_id
				and 	task_access_id=-1
				order by project_id, item_id
			";
			$prmUser = db_loadList($sql);

		}else{
			$sql="
				select distinct
					item_id
				,	COALESCE(tp.task_permission_value, 9) permission_value
				from projects p
				LEFT JOIN (task_permission_items tpi ,task_permissions tp) ON
				(tp.task_permission_on=tpi.item_id and p.project_id = tp.task_project)
				where 	item_id in (".implode($prmItems,", ").")
				and 	p.project_id = $project_id
				and 	task_user_id = $task_user_id
				and 	task_access_id=-1
				order by project_id, item_id
			";
			$prmUser = db_loadHashList($sql);
		}
		/*** FIN Permisos especificos de usuario ***/


		/*** Permisos de roles espec?icos ***/
		if (is_null($project_id)){
			$sql="
				SELECT distinct
					pr.project_id
				,	tpi.item_id
				,	max(pp.priority_level) priority_level
				FROM project_roles pr
				inner join roles r on r.role_id = pr.role_id
					and r.role_status = 0
					and r.role_type=1
					and	pr.user_id =  $task_user_id
				inner join role_permissions rp on rp.role_id = r.role_id
					and	rp.project_id in (pr.project_id, -1)
					and	rp.item_id in (".implode($prmItems,", ").")
					and rp.access_id = -1

				inner join task_permission_items tpi on tpi.item_id = rp.item_id
				inner join permission_priorities pp on pp.permission_value = rp.permission_value
				group by project_id,  item_id;
			";
			$prmRolEsp = db_loadList($sql);

			// de acuerdo a la prioridad del valor de permiso obtengo el mismo
			for($i=0; $i<count($prmRolEsp); $i++){
				$sql = "select permission_value from permission_priorities where priority_level = ".$prmRolEsp[$i]["priority_level"];
				$prmRolEsp[$i]["permission_value"] = db_loadResult($sql);
			}
		}else{
			$sql="
				SELECT distinct
					tpi.item_id
				,	max(pp.priority_level) priority_level
				FROM project_roles pr
				inner join roles r on r.role_id = pr.role_id
					and r.role_status = 0
					and r.role_type=1
					and	pr.user_id =  $task_user_id
				inner join role_permissions rp on rp.role_id = r.role_id
					and	rp.project_id in (pr.project_id, -1)
					and	rp.item_id in (".implode($prmItems,", ").")
					and	rp.project_id = $project_id
					and rp.access_id = -1

				inner join task_permission_items tpi on tpi.item_id = rp.item_id
				inner join permission_priorities pp on pp.permission_value = rp.permission_value
				group by item_id;
			";
			$prmRolEsp = db_loadHashList($sql);
		// de acuerdo a la prioridad del valor de permiso obtengo el mismo
			if (count($prmRolEsp)>0){
				foreach($prmRolEsp as $item => $pl){
					$sql = "select permission_value from permission_priorities where priority_level = ".$pl;
					$prmRolEsp[$item] = db_loadResult($sql);
				}
			}
		}

		/*** FIN Permisos de roles espec?icos ***/


		/*** Permisos de Usuarios del Proyecto ***/
		/***
		En primer lugar se buscan los permisos para usuarios del proyecto
		que fueron configurados para el proyecto espec?ico (rpc)
		en caso de que no estuvieran los mismos definidos se recurre
		a los definidos a nivel predeterminado en el modulo system (rpg)
		***/
		if (is_null($project_id)){
			$sql="
				select distinct
					p.project_id
				, 	tpi.item_id
				,	max(coalesce(ppc.priority_level, ppg.priority_level )) priority_level
				from
				           project_roles pr inner join projects p on pr.project_id = p.project_id
							and	pr.role_id = 2
							and	pr.user_id =  $task_user_id
					left join (task_permission_items tpi, role_permissions rpc)  on
					                                   (rpc.item_id = tpi.item_id
								and pr.role_id = rpc.role_id
								and p.project_id = rpc.project_id
								and rpc.company_id = p.project_company
								and	rpc.access_id = -1
								and rpc.company_id > 0)
					left join permission_priorities ppc on  ppc.permission_value = rpc.permission_value
					left join role_permissions rpg on rpg.item_id = tpi.item_id
								and pr.role_id = rpg.role_id
								and rpg.access_id = -1
								and rpg.project_id = -1
								and rpg.company_id = -1
							left join permission_priorities ppg on  ppg.permission_value = rpg.permission_value
				where
					tpi.item_id in (".implode($prmItems,", ").")

				group by p.project_id,  item_id;
			";
			$prmUsrPrj = db_loadList($sql);

			// de acuerdo a la prioridad del valor de permiso obtengo el mismo
			for($i=0; $i<count($prmUsrPrj); $i++){
				$sql = "select permission_value from permission_priorities where priority_level ='".$prmUsrPrj[$i]["priority_level"]."'";
				$prmUsrPrj[$i]["permission_value"] = db_loadResult($sql);
			}

		}else{
			$sql="
				select distinct
				 	tpi.item_id
				,	max(coalesce(ppc.priority_level, ppg.priority_level )) priority_level
				from
					project_roles pr inner join projects p on pr.project_id = p.project_id
							and	pr.role_id = 2
							and	pr.user_id =  $task_user_id
							and p.project_id = $project_id
						left join (task_permission_items tpi,role_permissions rpc) on (rpc.item_id = tpi.item_id
								and pr.role_id = rpc.role_id
								and p.project_id = rpc.project_id
								and rpc.company_id = p.project_company
								and	rpc.access_id = -1
								and rpc.company_id > 0 )
							left join permission_priorities ppc on  ppc.permission_value = rpc.permission_value
						left join role_permissions rpg on rpg.item_id = tpi.item_id
								and pr.role_id = rpg.role_id
								and rpg.access_id = -1
								and rpg.project_id = -1
								and rpg.company_id = -1
							left join permission_priorities ppg on  ppg.permission_value = rpg.permission_value
				where
					tpi.item_id in (".implode($prmItems,", ").")
				group by  item_id";
			//echo "<br><br>$sql<br><br>";
			$prmUsrPrj = db_loadHashList($sql);
		// de acuerdo a la prioridad del valor de permiso obtengo el mismo
			if (count($prmUsrPrj)>0){
				foreach($prmUsrPrj as $item => $pl){
					$sql = "select permission_value from permission_priorities where priority_level = ".$pl;
					$prmUsrPrj[$item] = db_loadResult($sql);
				}
			}
		}

		/*** FIN Permisos de Usuarios del Proyecto ***/

		$tmpperm[1]  = $prmUsrPrj;
		$tmpperm[2]  = $prmRolEsp;
		$tmpperm[3]  = $prmUser;
		$tmpperm[4]  = $prmOwner;

		if (is_null($project_id)){
			foreach($tmpperm as $perms){
				for($i=0; $i<count($perms); $i++){
					$pid = $perms[$i]["project_id"];
					$item = $perms[$i]["item_id"];
					$pv = $perms[$i]["permission_value"];
					if ( $pv <> 9 )
						$rta[$pid][$item] = $pv;
				}
			}
		}else{
			foreach($tmpperm as $perms){
				foreach($perms as $item => $pv){
					if ( $pv <> 9 )
						$rta[$item] = $pv;
				}
			}
		}

		//if ($caca) echo "<pre>";var_dump($rta);echo "</pre>";
		if (is_null($project_id)){
			$AppUI->pmPermissions["projpermissions$task_user_id"."_0"] = $rta;
		}else{
			$AppUI->pmPermissions["projpermissions$task_user_id"."_$project_id"] = $rta;
		}
		return $rta;
	}

	function getPermissions($task_user_id=0){
		global $user_context;
		if (@$this->project_id){
			$obj=new CTaskPermission();
			$perms=$obj->getPermissions($task_user_id, $this->project_id);

		}else{
			$perms= null;
		}
		return $perms;
	}

	/**
	 * @return array $perms[role][project][access][item]=permission
	 * @param role_id = null unknown
	 * @desc Devuelve los permisos de un rol determinado en el proyecto cargado
	 */
	function getRolePermissions($role_id=0){
		global $user_context;
		if (@$this->project_id){
			$obj=new CProjectRolesPermission();
			$perms=$obj->getPermissions($this->project_company, $role_id, $this->project_id);
		}else{
			$perms= null;
		}
		return $perms;
	}

	function getUsersAssignedToTasks($project_id){

		$rta = array();

		if (@$project_id){
			$sql = "select distinct ut.user_id
					from user_tasks ut inner join tasks t on t.task_id=ut.task_id
						inner join project_roles pr on t.task_project = pr.project_id
					where pr.role_id=2 and pr.project_id=$project_id";
			$rta = db_loadHashList($sql);
		}

		return $rta;
	}

	function setPermission($user_id, $access_id, $item_id, $permission_value){
		$obj=new CTaskPermission();
		// aseguro que el usuario est?asignado al proyecto
		$sql = "replace `project_roles`  values (
				$this->project_id ,	2,	$user_id, 100);";
		db_exec($sql);
		$rta =$obj->setPermission($this->project_id, $user_id, $access_id, $item_id, $permission_value);
		return $rta;
	}

	function setRolePermission($role_id, $access_id, $item_id, $permission_value){
		if (@$this->project_id){
			$obj=new CProjectRolesPermission();
			$rta =$obj->setPermission($role_id, $this->project_company, $this->project_id, $access_id, $item_id, $permission_value);
		}else{
			$rta=false;
		}
		return $rta;
	}


	function getBaselines() {
		$sql = "SELECT id
		FROM baselines
		WHERE project_id = $this->project_id
		Order by date desc
		";

		return db_loadList( $sql );
	}

	function getTasks($orderby="task_id") {
		$sql = "SELECT task_id
		FROM tasks
		WHERE task_project = $this->project_id ORDER BY $orderby";

		return db_loadList( $sql );
	}

	function assigndefaultpermissions($project_id=0){
		$rta = true;
		$project_id = $project_id ? $project_id : $this->project_id;

		if (is_null($project_id) || $project_id==0){
			$rta = false;
		}else{

			$sql = "delete from role_permissions where role_id = 2 and project_id = $project_id";
			if (!db_exec($sql)){
				echo db_error();
				$rta = false;
			}else{
				$sql="
				insert into role_permissions
				select role_id, p.project_company,p.project_id,access_id,item_id,permission_value
				from role_permissions, projects p where company_id=-1 and role_id = 2 and p.project_id = $project_id
				";
				if (!db_exec($sql)){
					echo db_error();
					$rta = false;
				}
			}
		}
		return $rta;
	}

	/*
	Devuelve una lista de los ids de las tareas que conforman el camino critico.
	Se esta calculando suponiendo que el comienzo del proyecto es el dia 0 y cada tarea tiene un offset respecto
	de este en su ES, EF, LS y LF.
	*/

	function getCriticalPath()
	{
		$grafo = new CTaskGraph( $this );

		return $grafo->getCriticalPath();
	}

	function getMaxTaskNumber()
	{
		$sql = "SELECT MAX( `task_wbs_number` )
				FROM `tasks`
				WHERE `task_parent`=`task_id` and `task_project` = $this->project_id";

		return db_loadResult( $sql );
	}

	function getActiveCalendars($project_id=0)
	{

		$project_id = $project_id ? $project_id : $this->project_id;
		if (is_null($project_id) || $project_id==0){
			return false;
		}

		return CCalendar::getActiveCalendars(2, $project_id);
	}

	function getCalendars($project_id=0){
		if ($project_id == 0){
			if ($this->project_id){
				$project_id = $this->project_id;
			}
		}
		if ($project_id){
			return CCalendar::getProjectCalendars($this->project_id);
		}
		return false;
	}

	function getHollidays($project_id=0, $year=""){

		if ($project_id == 0){
			if ($this->project_id){
				$project_id = $this->project_id;
			}
		}
		if ($project_id){
			$sql = "select project_company from projects where project_id = '$project_id';";
			$company_id = db_loadResult($sql);
			if ($company_id ){
				return CHolliday::getHollidays($company_id,$year);
			}
		}
		return false;
	}

	function getWorkedHours($project_id=0){
		if ($project_id == 0){
			if ($this->project_id){
				$project_id = $this->project_id;
			}
		}
		if ($project_id){
			$sql = "select  sum(te.timexp_ts_value) totbil
				from timesheets ts
				left outer join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet
                                                left join users u on u.user_id = ts.timesheet_user
	                                    left join projects p on p.project_id = ts.timesheet_project
	                                    where
                                                p.project_id = '$project_id'
			           AND te.timexp_ts_type = '1'
			           AND te.timexp_ts_last_status = 3
				";

			$worked_hours = db_loadResult($sql);

			if ($worked_hours ){

			      $worked_hours = number_format($worked_hours, 3);

			      $separado_por_puntos = explode(".", $worked_hours);

			       if (count($separado_por_puntos)>1)
			       {
			       	$decimal1 = substr($separado_por_puntos[1], 0,1);
		       	            $decimal2 = substr($separado_por_puntos[1], 1,1);
		       	            $decimal3 = substr($separado_por_puntos[1], 2,1);

			       	if($separado_por_puntos[1]=="000"){
			       	     $worked_hours =$separado_por_puntos[0];
			       	}elseif ($decimal2=="0" && $decimal3=="0"){
			       	     $worked_hours = $separado_por_puntos[0].".".$decimal1;
			       	}elseif ($decimal2!="0" && $decimal3=="0"){
			       	     $worked_hours = $separado_por_puntos[0].".".$decimal1.$decimal2;
			       	}
			       }

			      return $worked_hours;
			}else{
                             $worked_hours = 0;
			     return $worked_hours;
                        }
		}
		return false;
	}

	function getExpenses($project_id=0){
		if ($project_id == 0){
			if ($this->project_id){
				$project_id = $this->project_id;
			}
		}
		if ($project_id){
			/*$sql = "select distinct
						sum(te.timexp_value)
					from timexp te inner join tasks ta on te.timexp_applied_to_id = ta.task_id
					where	ta.task_project = $project_id
					and		te.timexp_applied_to_type = 1
					and 	te.timexp_type = 2
					and 	te.timexp_contribute_task_completion = 1;";*/

			$sql = "select distinct
						sum(te.timexp_value)
					from timexp te inner join tasks ta on te.timexp_applied_to_id = ta.task_id
					where	ta.task_project = $project_id
					and 	te.timexp_type = 2;";

			$total_expenses = db_loadResult($sql);
			if ($total_expenses ){
				return $total_expenses;
			}
		}
		return false;
	}

	function getTotalHours($project_id=0){
		if ($project_id == 0){
			if ($this->project_id){
				$project = $this;
			}
		}else{
			$project = new CProject();
			$project->load($project_id);
		}
		if ($project->check() === null){
			$sql = "SELECT ROUND(SUM(task_duration),2) FROM tasks WHERE task_project = $project->project_id AND task_duration_type = 24 AND task_milestone ='0' AND task_dynamic = 0";
			$days = db_loadResult($sql);
			$sql = "SELECT ROUND(SUM(task_duration),2) FROM tasks WHERE task_project = $project->project_id AND task_duration_type = 1 AND task_milestone  ='0' AND task_dynamic = 0";
			$hours = db_loadResult($sql);

			$cal = new CWorkCalendar(2, $project->project_id,'', $project->project_start_date);
			$from = $cal;
			$from->addDays($days);
			$from->addHours($hours);

			$total_hours = $cal->dateDiff($from);

			if ($total_hours ){
				return $total_hours;
			}
		}
		return false;
	}

	function checkOverWorked($project_id=0){
		global $AppUI, $pstatus;
		if ($project_id == 0){
			if ($this->project_id){
				$project = $this;
			}
		}else{
			$project = new CProject();
			$project->load($project_id);
		}
		if ($project->check() !== null) return "No project loaded";

		$worked_hours = floatval($project->getWorkedHours());
		$total_hours = floatval($project->getTotalHours());



		if ($worked_hours > $total_hours){
			$owners[$project->project_owner] = "";
			$owners = $project->getOwners();
			$uids = array_keys($owners);
			$emails = CUser::getEmailFieldsFromIdList(implode(", ",$uids));

			$recips = implode(", ", $emails);
			Notifier::ProjectOverWorked(
						$uids,
						$project->project_id,
						$project->project_name,
						$project->project_start_date,
						$project->project_end_date,
						$AppUI->_($pstatus[$project->project_status]),
						$project->project_manual_percent_complete,
						$total_hours,
						$worked_hours);
			return NULL;
		}
		return NULL;

	}

	function updateProgress($project_id = 0){
		global $AppUI;

		if ($project_id == 0){
			if ($this->project_id){
				$project = $this;
			}
		}else{
			$project = new CProject();
			$project->load($project_id);
		}
		if ($project->check() !== null) return "No project loaded";

		// consulta modificada para detectar si esta seteado el progreso manual
		$sql = "
		SELECT ROUND(
				COALESCE(
			SUM(
			task_duration*
			task_duration_type*
			task_manual_percent_complete)
			/
			SUM(
			task_duration*
			task_duration_type)
			)
		) AS percent
		FROM projects
		LEFT JOIN tasks
		ON project_id = task_project
		WHERE project_id = '$project->project_id'
		GROUP BY project_id
		";


		$project->project_percent_complete = db_loadResult($sql);
		$ret = db_updateObject( 'projects', $project, 'project_id', false );


		if( !$ret ) {
			$msg = get_class( $project )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function updateRealBudget($project_id = 0){
		global $AppUI;
		if ($project_id == 0){
			if ($this->project_id){
				$project = $this;
			}
		}else{
			$project = new CProject();
			$project->load($project_id);
		}
		if ($project->check() !== null) return "No project loaded";

		$sql="SELECT SUM(CASE th.timexp_type
						WHEN 1 THEN (th.timexp_value*costperhour)
						WHEN 2 THEN th.timexp_value
						WHEN 3 THEN (th.timexp_value*costperhour)
						ELSE 0
					END) AS cost
				FROM projects AS p
				LEFT JOIN tasks AS t
					ON (t.task_project = p.project_id)
				LEFT JOIN timexp AS th
					ON (th.timexp_applied_to_id = t.task_id)
				LEFT JOIN users AS u
					ON (u.user_id=th.timexp_creator)
				WHERE p.project_id='$project_id'";
		$vec=db_fetch_array(db_exec($sql));
		$project->project_actual_budget = $vec['cost'];

		$ret = db_updateObject( 'projects', $project, 'project_id', false );


		if( !$ret ) {
			$msg = get_class( $project )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function updatedTotalHours($project_id){

		if($project_id){

			$today = new CDate();

			CProject::updateField($project_id, 'project_total_hours_update', "'".$today->format(FMT_DATETIME_MYSQL)."'");
		}
	}

	function updateField($project_id, $fieldName, $fieldValue)
	{
		$sql = "UPDATE projects SET ".$fieldName." = ".$fieldValue." WHERE project_id = ".$project_id;

		db_exec($sql);
	}

	function getAssessmentSatisfactionTypes()
	{
		global $AppUI;

		$sql = "SELECT projects_assessment_satisfaction_type_id, projects_assessment_satisfaction_type_name_".$AppUI->user_prefs['LOCALE']." as name ";
		$sql .= "FROM projects_assessment_satisfaction_types";

		return db_loadHashList($sql, $index);
	}
}


class CBaseline extends CDpObject
{
	var $id 			= NULL;
	var $name 			= NULL;
	var $project_id 		= NULL;
	var $date 			= NULL;
	var $project_actual_end_date	= NULL;
	var $project_actual_budget	= NULL;
	var $project_status		= NULL;
	var $project_manual_percent_complete 	= NULL;

	function check()
	{
		if ( !$this->id )
		{
			$sql = "SELECT id FROM baselines where project_id = $this->project_id AND name = '$this->name'";

			$baselines = db_loadList( $sql );
			if ( count( $baselines ) )
			{
				return "That name allready exists";
			}
		}
		return NULL;
	}

	function CBaseline()
	{
		$this->CDpObject( 'baselines', 'id' );
	}

	function getTasks()
	{
		$sql = "SELECT id
		FROM baseline_tasks
		WHERE baseline_id = $this->id";

		return db_loadList( $sql );
	}

	function store()
	{
		$clonarTareas = !$this->id;
		if ( $msg = CDpObject::store() )
		{
			return $msg;
		}
		else
		{
			if ( $clonarTareas )
			{
				$p = new CProject();
				$p->load( $this->project_id );
				$ts = $p->getTasks();
				$t = new CTask();
				foreach ( $ts as $trow )
				{
					$t->load( $trow["task_id"] );
					$blt = new CBaseLineTask();
					$blt->baseline_id = $this->id;
					$blt->cloneObject( $t );
					if ( $msg = $blt->store() )
					{
						return $msg;
					}
				}
			}
		}
		return NULL;
	}

	function delete()
	{
		if ( $msg = CDpObject::delete() )
		{
			return $msg;
		}
		else
		{
			$misTasks = $this->getTasks();

			$task = new CBaselineTask();
			foreach( $misTasks as $miTaskId )
			{
				$task->load( $miTaskId["id"] );
				if ( $msg = $task->delete() )
				{
					return $msg;
				}
			}
			return NULL;
		}
	}
}


class CBaselineTask extends CDpObject
{
	var $id						= NULL;
	var $_task_id 				= NULL;
	var $baseline_id 			= NULL;
	var $task_name				= NULL;
	var $task_parent			= NULL;
	var $task_hours_worked 		= NULL;
	var $task_start_date		= NULL;
	var $task_duration			= NULL;
	var $task_duration_type 	= NULL;
	var $task_end_date			= NULL;
	var $task_percent_complete 	= NULL;
	//var $task_manual_percent_complete 	= NULL;
	//var $task_complete 			= NULL;
	var $task_target_budget		= NULL;
	var $task_status			= NULL;
  	var $task_milestone			= NULL;
  	var $task_work		 		= NULL;

	function CBaselineTask()
	{
		$this->CDpObject( "baseline_tasks", "id" );
	}

	function cloneObject( $task )
	{
		foreach( get_object_vars( $this ) as $k => $v )
		{
			if ( substr($k, 0, strlen("task") ) == "task" )
			{
				//echo "Hay task";
				$this->$k = $task->$k;
			}
		}
		$this->_task_id = $task->task_id;
	}

	function delete()
	{
		if ( $msg = CDpObject::delete() )
		{
			return $msg;
		}
		else
		{
			$sql = "DELETE FROM baseline_task_expenses WHERE baseline_task_id = $this->id;";
			if ( !db_exec( $sql ) )
			{
				return db_error();
			}
			return NULL;
		}
	}

	function store()
	{
		if ( $msg = CDpObject::store() )
		{
			return $msg;
		}
		else
		{
			//Ahora hay que clonar los expenses
			$task = new CTask();
			$task->load( $this->_task_id );
			$teids = $task->getExpenses();
			//echo "<p>Gastos asociados a la tarea: '$task->task_name'";print_r( $teids );echo "</p>";
			$te = new CTimExp();
			foreach ( $teids as $teid )
			{
				$te->load( $teid["timexp_id"] );
				//echo "<p>Clonando el gasto '$te->task_expense_description'</p>";
				$bste = new CBaselineTimexp();
				$bste->baseline_task_id = $this->id;
				$bste->cloneObject( $te );
				if ( $msg = $bste->store() )
				{
					return $msg;
				}
			}
		}

		return NULL;
	}


	/*function getExpenses()
	{
		$sql = "SELECT timexp_id FROM baseline_timexp WHERE baseline_task_id = $this->id";
		return db_loadList( $sql );
	}*/
}


class CBaselineTimexp extends CDpObject
{
/** @var int Primary Key */
	var $id = NULL;
/** @var string */
// these next fields should be ported to a generic address book
	var $timexp_id = NULL;
	var $baseline_task_id			= NULL;
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

	function CBaselineTimexp() {
		$this->CDpObject( 'baseline_timexp', 'id' );
	}

// overload check
	function check() {
		if ($this->timexp_id === NULL) {
			return 'timexp id is NULL';
		}
		$this->timexp_id = intval( $this->timexp_id );

		return NULL; // object is ok
	}

  	function cloneObject( $timexp )
	{
		//echo "<p>Clonando el gasto: ";print_r( $expense );echo "</p>";
		foreach( get_object_vars( $this ) as $k => $v )
		{
			if ( substr($k, 0, strlen("timexp") ) == "timexp" )
			{
				$this->$k = $timexp->$k;
			}
		}
	}
	function store()
	{
		return CDpObject::store();
	}
}

class CTaskGraph
{
	var $project 			= NULL;
	var $bop				= NULL;
	var $eop				= NULL;

	function CTaskGraph( &$project )
	{
		echo "<p>Construyendo un objeto CTaskGraph</p>";
		$this->project = $project;

		$this->bop = new CTaskNode();
		$this->bop->is_bop = true;

		$this->eop = new CTaskNode();
		$this->eop->is_eop = true;

		$tasks = $this->project->getTasks();
		$t = new CTask();
		foreach( $tasks as $task_id )
		{
			$t->load( $task_id["task_id"] );
			$node = new CTaskNode( $t );
			if ( $t->getDependencies() == "" )
			{
				$this->bop->addSuccessor( $node );
				$node->addPredecessor( $this->bop );
			}
		}
		$this->makeGraph( $this->bop );
	}

	function makeGraph( $root )
	{
		//echo "<p>Armando el grafo a partir de '".$root->task->task_name."'</p>";
		if ( count( $root->successors ) )
		{
			foreach ( $root->successors as $node )
			{
				//echo "<p>'".$node->task->task_name."' es un sucesor de '".$root->task->task_name."'</p>";
				$node->fillSuccessors();
				$this->makeGraph( $node );
			}
		}
		else
		{
			echo "<p>Es un nodo final, le agrego eop como sucesor</p>";
			$root->addSuccessor( $this->eop );
			$this->eop->addPredecessor( $root );
		}
	}

	function getNextForwardNode()
	{

	}

	function getCriticalPath()
	{
		//Empiezo marcando los nodos iniciales, paso hacia adelante
		foreach( $this->bop->successors as $node )
		{
			$node->early_start = 1;
			$node->early_finish = $node->early_start + $node->task->task_duration;
		}

		return NULL;
	}
}


class CTaskNode
{
	var $task				= NULL;
	var $early_start 		= NULL;
	var $early_finish		= NULL;
	var $late_start			= NULL;
	var $late_finish		= NULL;
	var $activity_slack		= NULL;
	var $successors			= NULL;
	var $predecessors		= NULL;
	var $is_eop;
	var $is_bop;

	function CTaskNode( $task = NULL )
	{
		if ( $task )
		{
			$this->task = &$task;
		}
		$this->is_eop = false;
		$this->is_bop = false;
	}

	function found( $arr, $taskNode )
	{
		$ret = false;

		foreach ( $arr as $elem )
		{
			if ( $elem->is_bop || $elem->is_eop )
			{
				$ret = $elem->is_bop == $taskNode->is_bop && $elem->is_eop == $taskNode->is_eop;
			}
			else
			{
				$ret = $elem->task->task_id == $taskNode->task->task_id;
			}
			if ( $ret )
				break;
		}

		return $ret;
	}

	function isSuccessor( $taskNode )
	{
		return $this->found( $this->successors, $taskNode );
	}

	function isPredecessor( $taskNode )
	{
		return $this->found( $this->predecessors, $taskNode );
	}

	function addSuccessor( $taskNode )
	{
		echo "<p>Agregando sucesor, de '".($this->is_bop ? "bop" : $this->task->task_name)."' a '".($taskNode->is_eop ? "eop" : $taskNode->task->task_name)."'</p>";

		if ( !$this->successors )
		{
			$this->successors = array();
		}
		if ( !$this->isSuccessor( $taskNode ) )
		{
			$this->successors[] = &$taskNode;
			echo "<p>Sucesor agregado</p>";
		}
		else
		{
			echo "<p>Ya era sucesor</p>";
		}
	}

	function addPredecessor( $taskNode )
	{
		echo "<p>Agregando predecesor, de '".($this->is_eop ? "eop" : $this->task->task_name)."' a '".($taskNode->is_bop ? "bop" : $taskNode->task->task_name)."'</p>";

		if ( !$this->predecessors )
		{
			$this->predecessors = array();
		}
		if ( !$this->isPredecessor( $taskNode ) )
		{
			$this->predecessors[] = &$taskNode;
			echo "<p>Predecesor agregado</p>";
		}
		else
		{
			echo "<p>Ya era predecesor</p>";
		}
	}

	function fillSuccessors()
	{
		$suc = $this->task->getDependants();
		$t = new CTask();

		foreach ( $suc as $task_id )
		{
			$t->load( $task_id );
			$node = new CTaskNode( $t );
			$node->addPredecessor( $this );
			$this->addSuccessor( $node );
		}
	}
}

?>