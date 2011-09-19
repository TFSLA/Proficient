<?php /* ADMIN $Id: admin.class.php,v 1.2 2009-08-10 13:43:54 nnimis Exp $ */
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'system' ) );

//valores posibles de permiso
$pvs = array(
'0' => $AppUI->_('Denied'),
//'-2' => $AppUI->_('Full Control'),
'-1' => $AppUI->_('Read Write'),
'1' => $AppUI->_('Read Only'),
'9' => ' - '
);

//valores posibles de permiso para tareas
$pvsTask = array(
'0' => $AppUI->_('Denied'),
'-1' => $AppUI->_('View & Create'),
'1' => $AppUI->_('View'),
'9' => ' - '
);

//valores posibles de permiso para items de proyectos
$pvsProj = array(
'0' => $AppUI->_('Hide'),
'1' => $AppUI->_('Show'),
'9' => ' - '
); 

/**
* User Class
*/
class CUser extends CDpObject {
	var $user_id = NULL;
	var $user_username = NULL;
	var $user_password = NULL;
	var $user_parent = NULL;
	var $user_type = NULL;
	var $user_first_name = NULL;
	var $user_last_name = NULL;
	var $user_company = NULL;
	var $user_department = NULL;
	var $user_job_title = NULL;
	var $user_email = NULL;
	var $user_email_alternative1 = NULL;
	var $user_email_alternative2 = NULL;
	var $user_phone = NULL;
	var $user_home_phone = NULL;
	var $user_mobile = NULL;
	var $user_address1 = NULL;
	var $user_address2 = NULL;
	var $user_city = NULL;
	var $user_state_id = NULL;
	var $user_state = NULL; //<uenrico>se va a eliminar porque es reemplazado por su correspondiente id</uenrico>
	var $user_zip = NULL;
	var $user_country_id = NULL;
	var $user_country = NULL; //<uenrico>se va a eliminar porque es reemplazado por su correspondiente id</uenrico>
	var $user_im_type = NULL;
	var $user_im_id = NULL;
	var $user_birthday = NULL;
	var $user_pic = NULL;
	var $user_owner = NULL;
	var $user_signature = NULL;
	var $user_smtp = NULL;
	var $user_smtp_auth = NULL;
	var $user_smtp_use_pop_values = NULL;
	var $user_smtp_username = NULL;
	var $user_smtp_password = NULL;	
	var $user_mail_server_port = NULL;
	var $user_pop3 = NULL;
	var $user_imap = NULL;
	var $user_email_user = NULL;
	var $user_email_password = NULL;
	var $user_webmail_autologin = NULL;
	var $user_cost_per_hour = NULL;
	var $user_status = NULL;
	var $start_time_am = NULL;
	var $end_time_am = NULL;
	var $start_time_pm = NULL;
	var $end_time_pm = NULL;
	var $daily_working_hours = NULL;
	var $user_supervisor = NULL;

	var $date_created = NULL;
	var $date_updated = NULL;
	var $last_visit   = NULL;
	var $enabled = NULL;
	var $protected = NULL;
	var $access_level = NULL;
	var $cookie_string = NULL;
	
	var $user_input_date_company = NULL; 

// Campos agregados del modulo de rrhh	
	var $doctype = NULL;
	var $docnumber = NULL;
	var $maritalstate  = NULL;
	var $nationality = NULL;
	var $children  = NULL;
	var $taxidtype = NULL;
	var $taxidnumber = NULL;
	var $resume  = NULL;
	var $costperhour = NULL;
	var $actualjob = NULL;
	var $actualcompany = NULL;
	var $workinghours  = NULL;
	var $hoursavailableperday  = NULL;
	var $wantsfulltime = NULL;
	var $wantsparttime = NULL;
	var $wantsfreelance  = NULL;
	var $salarywanted  = NULL;
	var $wasinterviewed  = NULL;
	var $candidatestatus = NULL;
	var $timexp_supervisor = NULL;
	var $hhrr_password = NULL;
	var $hhrr_id = NULL;
// fin Campos agregados del modulo de rrhh	
	
	function CUser() {
		$this->CDpObject( 'users', 'user_id' );
	}

	function check() {
		if ($this->user_id === NULL) {
			return 'user id is NULL';
		}
		if ($this->user_password !== NULL) {
			$this->user_password = db_escape( trim( $this->user_password ) );
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function delete() {
		global $AppUI;
		$sql = "DELETE FROM users WHERE user_id = '$this->user_id'";
  		if (!db_exec( $sql )) {
  			return db_error();
		} else {
				
                   //Cascade delete of user's dashboard:
	           $result = mysql_query("SELECT * from db_rows WHERE user_id = '{$this->user_id}';");
	           while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	             $result2 = mysql_query("SELECT * from db_cols WHERE db_row_id = {$row["db_row_id"]};");
	             while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
	               $result3 = mysql_query("DELETE FROM db_cells WHERE db_col_id = {$row2["db_col_id"]};");
	               $result3 = mysql_query("DELETE FROM db_cols  WHERE db_col_id = {$row2["db_col_id"]};");
	             }
	             $result3 = mysql_query("DELETE FROM db_rows  WHERE db_row_id = {$row["db_row_id"]};");
	           }
						 // borrado de skills
						$sql = "DELETE FROM hhrrskills WHERE user_id = '$this->user_id';";
						if (!db_exec( $sql ))		return db_error();

						// borrado de horas y gastos cargados por el usuario
						$sql = "delete timexp_status.*, timexp.* 
						FROM timexp_status inner join timexp on timexp_status.timexp_id = timexp.timexp_id
						WHERE timexp_creator = '$this->user_id'";
						if (!db_exec( $sql )) {
							return "a".db_error();
						}
						
						
						// borrado de timesheets
						$sql = "delete timesheets.*, timesheetstatus.* 
						FROM timesheets inner join timesheetstatus on timesheets.timesheet_id = timesheetstatus.timesheetstatus_timesheet
						WHERE timesheet_user = '$this->user_id'";
						if (!db_exec( $sql )) {
							return db_error();
						}						

						// borrado de historial horas y gastos asignados a timesheets
						$sql = "delete timexp_ts.*
						FROM timexp_ts 
						WHERE timexp_ts_creator = '$this->user_id'";
						if (!db_exec( $sql )) {
							return db_error();
						}
												
						// actualización de usuarios que tenian al que se ha borrado como supervisor
						$sql = "UPDATE users SET timexp_supervisor = -1 WHERE timexp_supervisor = '$this->user_id'";
						if (!db_exec( $sql )) {
							return db_error();
						}
						
						// Borro las exclusiones
						$sql = "DELETE * FROM calendar_exclusions WHERE user_id = '$this->user_id' ";

						// borrado de los calendarios del usuario
						$cals = $this->getCalendars();
						if($cals !== false){
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
						}						

		   return NULL;
		}
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}

		if($this->user_status == "0")
			$this->enabled = "1";
		else
			$this->enabled = "0";

		if( $this->user_id ) {
		// save the old password
			$sql = "SELECT user_password FROM users WHERE user_id = $this->user_id";
			db_loadHash( $sql, $hash );
			$pwd = $hash['user_password'];	// this will already be encrypted

			$ret = db_updateObject( 'users', $this, 'user_id', false );

		// update password if there has been a change
			$sql = "UPDATE users SET user_password = MD5('$this->user_password')"
				."\nWHERE user_id = $this->user_id AND user_password != '$pwd'";
			db_exec( $sql );
		} else {

			$t_seed = $this->user_email.$this->user_username;
			$notuniq=true;
			while($notuniq){
				$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
				$t_val = md5( $t_val ).md5( time() );

				$this->cookie_string = substr( $t_val, 0, 64 );

				$query = "SELECT COUNT(*) as cnt
						  FROM users
						  WHERE cookie_string='{$this->cookie_string}'";
				db_loadHash( $query, $hash );
	
				if ( $hash['cnt'] <= 0 )  $notuniq = false;
			}
            
			$ret = db_insertObject( 'users', $this, 'user_id' );
		   // encrypt password
			$sql = "UPDATE users SET user_password = MD5('$this->user_password')"
				."\nWHERE user_id = $this->user_id";
			db_exec( $sql );
            
			// Al nuevo usuario le creo un calendario // 
			$sql = "INSERT INTO calendar (calendar_name, calendar_company, calendar_project, calendar_user, calendar_propagate, calendar_status) VALUES ('Default', 0, 0, $this->user_id ,  1, 1)";
			db_exec( $sql );
			$id_c = mysql_insert_id();
			
			$query = "SELECT calendar_id FROM calendar WHERE calendar_company ='0' AND calendar_project='0' AND calendar_user='0' AND calendar_status='1' ";
            $sql = mysql_query($query);
            $data = mysql_fetch_array($sql);

			$default_calendar = $data[calendar_id];

			$query = "SELECT * FROM calendar_days WHERE calendar_id = '$default_calendar' ";
			$sql = mysql_query($query);

			while($vec = mysql_fetch_array($sql))
			{ 
              if($vec[calendar_day_from_time1]!="")
			  {
			  $day_from_time1 = "'$vec[calendar_day_from_time1]'";
			  $day_to_time1 = "'$vec[calendar_day_to_time1]'";
			  }
			  else{
			  $day_from_time1 = "NULL";
			  $day_to_time1 = "NULL";
			  }

			  if($vec[calendar_day_from_time2]!="")
			  {
			  $day_from_time2 = "'$vec[calendar_day_from_time2]'";
			  $day_to_time2 = "'$vec[calendar_day_to_time2]'";
			  }
			  else{
			  $day_from_time2 = "NULL";
			  $day_to_time2 = "NULL";
			  }

			  if($vec[calendar_day_from_time3]!="")
			  {
			  $day_from_time3 = "'$vec[calendar_day_from_time3]'";
			  $day_to_time3 = "'$vec[calendar_day_to_time3]'";
			  }
			  else{
			  $day_from_time3 = "NULL";
			  $day_to_time3 = "NULL";
			  }

			  if($vec[calendar_day_from_time4]!="")
			  {
			  $day_from_time4 = "'$vec[calendar_day_from_time4]'";
			  $day_to_time4 = "'$vec[calendar_day_to_time4]'";
			  }
			  else{
			  $day_from_time4 = "NULL";
			  $day_to_time4 = "NULL";
			  }

			  if($vec[calendar_day_from_time5]!="")
			  {
			  $day_from_time5 = "'$vec[calendar_day_from_time5]'";
			  $day_to_time5 = "'$vec[calendar_day_to_time5]'";
			  }
			  else{
			  $day_from_time5 = "NULL";
			  $day_to_time5 = "NULL";
			  }

			  $query2 = "INSERT INTO calendar_days 
			  (calendar_day_id, calendar_id, calendar_day_day, calendar_day_working, calendar_day_from_time1, calendar_day_to_time1, calendar_day_from_time2, calendar_day_to_time2, calendar_day_from_time3, calendar_day_to_time3, calendar_day_from_time4, calendar_day_to_time4, calendar_day_from_time5, calendar_day_to_time5, calendar_day_hours) 
			  VALUES (NULL, '$id_c', '$vec[calendar_day_day]', '$vec[calendar_day_working]', $day_from_time1, $day_to_time1, $day_from_time2, $day_to_time2, $day_from_time3, $day_to_time3, $day_from_time4, $day_to_time4, $day_from_time5, $day_to_time5, '$vec[calendar_day_hours]')";
              
			  $sql2 = mysql_query($query2);
			  
			}
              
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function canReadAllProjects($user_id=null){
		global $AppUI;
		
		$user_id = !is_null($user_id) ? $user_id : $AppUI->user_id;
		
		$prj = new CProject();
		$projects = $prj->getAllowedRecords( $AppUI->user_id, 'project_id, project_name', 'project_name', null, null );
		$cantPerm = count($projects);
		
		$sql = "select count(project_id) from projects";
		$cantExist = db_loadResult($sql);
		return ($cantExist == $cantPerm);
	
	}
	
	function getAssignedProjects($user_id=null){
	
		$user_id = !is_null($user_id) ? $user_id : $AppUI->user_id;
		
		if (is_null($user_id))
			return false;

		$sql = "select distinct pr.project_id, pr.project_name
from projects pr
inner join project_roles pro on pro.project_id=pr.project_id 
/*
inner join tasks ta on ta.task_project=pr.project_id 
inner join user_tasks ut on ut.task_id = ta.task_id*/
where 
pro.role_id = 2 and
pro.user_id = $user_id";
		return db_loadHashList($sql);			
		
	}

	function getAdminOwnerProjects($user_id=null)
	{
		global $AppUI;
	
		$user_id = !is_null($user_id) ? $user_id : $AppUI->user_id;
		
		if (is_null($user_id))
			return false;

		$sql = "select distinct project_id
				from project_owners
				where project_owner = $user_id";
				
		return db_loadHashList($sql);
	}
	
	function getContacts( $where = "1", $order = "" )
	{
		$sql = "SELECT *
				FROM contacts
				WHERE ( contact_public = 1
				OR ( contact_public = 0 AND contact_owner = $this->user_id ) )
				AND ( $where )
				$order";
					
		//echo "<pre>$sql</pre>";		
		return db_loadList( $sql );
	}
	
	function isDelegator( $u, $m=0 )
	{
		$sql = "SELECT delegator_id
				FROM delegations
				WHERE ( delegate_id = $this->user_id )
				AND ( delegator_id = $u )";
				
		if ( $m )
		{
			$sql .= " AND ( module_id = $m )";
		}
		//echo "<pre>$sql</pre>";
		$ret = db_loadResult( $sql );
		return $ret == $u;			
	}
	
	function getDelegators( $m = 0 )
	{
		$sql = "SELECT DISTINCT delegator_id
			FROM delegations d
			INNER JOIN modules m ON m.mod_id = d.module_id
			WHERE delegator_id != $this->user_id";
		/*if ( $this->user_type != 1 )
		{*/
			$sql .= " AND (delegate_id = $this->user_id)";
		//}
		if ( $m )
		{
			$sql .= " AND (d.module_id = $m)";
		}
		//echo "<pre>$sql</pre>";					
		return db_loadList( $sql );
	}
	
	function getDelegates()
	{
		$sql = "SELECT DISTINCT delegate_id
				FROM delegations
				WHERE delegator_id = $this->user_id";								
		return db_loadList( $sql );
	}
	
	function getNonDelegates()
	{
		$delegados = $this->getDelegates();		
		$str_del = "$this->user_id";
		foreach ( $delegados as $d )
		{
			$str_del .= ",".$d["delegate_id"];
		}
		
		$sql = "SELECT user_id FROM users 
				WHERE user_id NOT IN ($str_del)
				AND user_type <> 5";
		//echo "<pre>$sql</pre>";
		return db_loadList( $sql );
	}
	
	function getDelegatePermission( $delegate_id, $module_id )
	{
		$sql = "SELECT permission_level
				FROM delegations
				WHERE ( delegator_id = $this->user_id )
				AND ( delegate_id = $delegate_id )
				AND ( module_id = $module_id )
				";
			//echo "<pre>$sql</pre>";
		return db_loadResult( $sql );
	}
	
	function addDelegation( $delegate_id, $module_id, $permission_level )
	{
		$sql = "INSERT INTO delegations (delegator_id, delegate_id, module_id, permission_level)
			VALUES ( $this->user_id, $delegate_id, $module_id, '$permission_level' )
		";
		return db_exec( $sql );
	}
	
	function removeDelegations( $delegate_id )
	{
		$sql = "DELETE FROM delegations WHERE delegate_id = $delegate_id AND delegator_id = $this->user_id";
		
		return db_exec( $sql );
	}	
	
	//Este metodo por ahora no se usa
	function getAllowedModules( $user_id = 0 )
	{
		if ( !$user_id )
		{
			$user_id = $this->user_id;
		}
		
		$sql = "SELECT `permission_grant_on`
				FROM `permissions`
				WHERE (`permission_user` = $user_id)
				AND (`permission_value` != 0 )";
				
		return db_loadList( $sql );				
	}
	
	function getModulesDelegatedBy( $user_id = 0 )
	{
		$sql = "SELECT mod_directory, mod_ui_name
				FROM  `delegations` 
				INNER  JOIN modules ON mod_id = module_id
				";
		/*if ( $this->user_type != 1 )
		{*/
			$sql .= "WHERE delegate_id = $this->user_id";
		//}
		
		if ( $user_id )
		{
			$sql .= " AND delegator_id = $user_id";
		}
	
		return db_loadList( $sql );			
	}
	
	function getDelegatedModules( $user_id = 0 )
	{
		if ( !$user_id )
			$user_id = $this->user_id;		
		
		$sql = "SELECT `module_id`
				FROM `delegations`
				WHERE `delegate_id` = $user_id";
		
		return db_loadList( $sql );
		}
	
	function getSalesPipelines( $user_id = 0, $where="1" )
	{
		if ( !$user_id )
			$user_id = $this->user_id;
			
		$sql = "SELECT
                    salespipeline.*,
                    CONCAT(users.user_first_name, ' ', users.user_last_name) AS _accountmanagername,
                    CONCAT(users2.user_last_name, ', ', users2.user_first_name) AS _leadOwner
                FROM
                    salespipeline LEFT JOIN users ON salespipeline.accountmanager = users.user_id
                    LEFT JOIN users AS users2 ON salespipeline.lead_owner = users2.user_id
                WHERE ( salespipeline.lead_owner = $user_id )
                        AND ( $where )
                ORDER BY salespipeline.probability, salespipeline.totalincome";
		
		return db_loadList( $sql, NULL );
	}

	function getAssignableUsers($columns){
		$sql = "
		SELECT $columns
		FROM users 
		WHERE user_type <> 5 
		/*AND		user_status = 0*/
		ORDER BY user_last_name";
		return db_loadHashList( $sql );	
	}
	
	/*
		$utypes = array(
	// DEFAULT USER (nothing special)
		0 => '',
	// DO NOT CHANGE ADMINISTRATOR INDEX !
		1 => 'Administrador',
	// you can modify the terms below to suit your organisation
		2 => 'Empleado',
		3 => 'Contratado',
		4 => 'Otro',
		5 => 'Candidato'
	);
	*/
	
	function getEmpleados($columns){
		$sql = "
		SELECT $columns
		FROM users 
		WHERE ( user_type = 2 OR user_type = 1)
		/*AND		user_status = 0*/
		ORDER BY user_last_name";
		return db_loadHashList( $sql );
		
	}
	

	function getOwnedProjects($user_id=null){
		global $AppUI;
		$rta=array();
		if(is_null($user_id) && is_null($AppUI->user_id)){
			return 	$rta;		
		}	
		$user_id = is_null($user_id) ?  $AppUI->user_id : $user_id ;
		
		$sql="
			SELECT DISTINCT project_id, project_name FROM projects WHERE project_owner = $user_id
			UNION 
			SELECT p.project_id, p.project_name 
			FROM project_owners po inner join projects p ON p.project_id = po.project_id
			WHERE po.project_id <> 0 AND po.project_owner = $user_id
		";
		
		$rta=db_loadHashList($sql);
		
		return $rta;
	}

	function getListEditableProjects(){
		$tmpprj = new CProject();
		$perms = $tmpprj->projectPermissions();
		$projects = array();
		if (count($perms)){
			foreach ($perms as $pid =>$rows){
				if ($rows[1]==PERM_EDIT){
					$projects[]=$pid;
				}
			}
		}	
		return $projects;
	}
	
	function getUserPrefs($user_id){
		
		/*$sql= "	SELECT pref_name, pref_value
						FROM user_preferences 
						where pref_user = $user_id";*/
		$sql = "
			SELECT  pp.pref_name pref_name, 
					coalesce(pu.pref_value, pp.pref_value) pref_value
			FROM user_preferences pp 
				left join user_preferences pu on 
					pp.pref_name = pu.pref_name and 
					pu.pref_user = '$user_id'
			WHERE pp.pref_user = 0		
		";
		return db_loadHashList($sql);
	}
	
	function getEditableUsers($user_id){
		
		$sql= "	SELECT u.user_id, concat(u.user_last_name, ', ' , u.user_first_name)
				FROM users u, users u2
				where u2.user_id = $user_id
				and 	u.user_type < 5
				and		(u2.user_id = u.user_id
						or	u2.user_type = 1)
		";
	
		return db_loadHashList($sql);	
	}
	
	function getActiveCalendars($user_id=0)
	{

		$user_id = $user_id ? $user_id : $this->user_id;
		if (is_null($user_id) || $user_id==0){
			return false;	
		}
		
		return CCalendar::getActiveCalendars(3, $user_id);
	}	
	
	function getCalendars($user_id=0){
		
		if ($user_id == 0){
			if ($this->user_id){
				$user_id = $this->user_id;
			}
		}
		if ($user_id){
			return CCalendar::getUserCalendars($this->user_id);	
		}		
		return false;
	}


	function getHollidays($user_id=0,$year="", $project =""){

		if ($user_id == 0){
			if ($this->user_id){
				$user_id = $this->user_id;
			}
		}
		
		if ($user_id){ 

			if($project != "")
			{
				// Traigo la fecha de inicio del proyecto
				$query = "SELECT DATE_FORMAT(project_start_date, '%Y-%m-%d') FROM projects WHERE project_id = '".$project."' ";
				//echo "<pre>$query</pre><br>";
				$sql = db_exec($query);
				$d = mysql_fetch_array($sql);
				$fecha_project = $d[0];
				
				if($fecha_project !=""){
				$query_year = " AND from_date > '".$fecha_project."' ";
				}
			}
			
            $query = "SELECT DATE_FORMAT(from_date,  '%Y%m%d')as from_date, DATE_FORMAT(to_date,  '%Y%m%d')as to_date, exclusion_id FROM calendar_exclusions WHERE user_id= '$user_id' $query_year";
            
            //echo "<pre>$query</pre>";
            
            $sql = mysql_query($query);

			$list = array();

            while($vec = mysql_fetch_array($sql))
			{
			  $from = $vec[from_date];
			  $to = $vec[to_date];
			  $id_exc = $vec[exclusion_id];

			    for( $i= $from ; $i<=$to; $i ++)
				{
				  $list[$i] = $id_exc;
				}
			}

			return $list;

		}		
		return false;
	}
	
	function getEmailFieldsFromIdList($user_id_list){
	
		$sql = "select CONCAT(u.user_first_name,' ',u.user_last_name, ' <', u.user_email, '>')
				from users u
				where user_id in($user_id_list)";
		return db_loadColumn($sql);
	
	}
	
	function clearWebtrackingPermission($user_id, $project_id){
		$query = "DELETE FROM `btpsa_project_user_list_table`
				    WHERE project_id = '$project_id'
				    AND user_id = '$user_id'";
			
		return db_exec( $query );	
	}
	
	function setWebtrackingPermissions($user_id=0){
		global $AppUI;
		if ($user_id == 0){
			if (@$this->user_id){
				$user_id = $this->user_id;
			}
		}		
		if ($user_id){
			$projects = cuser::getAssignedProjects($user_id);
			if (count($projects)){
				// Obtengo el nivel de permisos por defecto en webtracking para los usuarios de un proyecto
				$pu_access_level = $AppUI->getConfig('projects_users_default_webtracking_permission');
							
				foreach ($projects as $project_id => $row) {
					//si no tiene permisos para el proyecto en webtracking se los colocamos
					$wt_perms = db_loadResult("select access_level 
												from `btpsa_project_user_list_table` 
												where project_id = $project_id 
												and user_id = $user_id");
			
					if ($wt_perms == NULL || $wt_perms < $pu_access_level ){
						$query = "REPLACE 
								  INTO `btpsa_project_user_list_table`
								    ( project_id, user_id, access_level )
								  VALUES
								    ( '$project_id', '$user_id', '$pu_access_level')";
							
						$rta = db_exec( $query );	
						if (!$rta){
							return db_error();
						}else{
							$rta = 0;					
								}			
					}else{
						$rta = 0;		
					}			
				}
			}
			$projects = cuser::getOwnedProjects($user_id);
			if (count($projects)){
				// Obtengo el nivel de permisos por defecto en webtracking para los administradores de un proyecto
				$pa_access_level = $AppUI->getConfig('projects_admins_default_webtracking_permission');
							
				foreach ($projects as $project_id => $row) {
					//si no tiene permisos para el proyecto en webtracking se los colocamos
					$wt_perms = db_loadResult("select access_level 
												from `btpsa_project_user_list_table` 
												where project_id = $project_id 
												and user_id = $user_id");
			
					if ($wt_perms == NULL || $wt_perms < $pa_access_level ){
						$query = "REPLACE 
								  INTO `btpsa_project_user_list_table`
								    ( project_id, user_id, access_level )
								  VALUES
								    ( '$project_id', '$user_id', '$pa_access_level')";
							
						$rta = db_exec( $query );	
						if (!$rta){
							return db_error();
						}else{
							$rta = 0;					
								}			
					}else{
						$rta = 0;		
					}			
				}
			}			
			return NULL;
		}
		return "Invalid user_id";	
	}
	
	function getAssignedTasks($project_id,$user_id=""){
		if ($user_id == 0){
			if (@$this->user_id){
				$user_id = $this->user_id;
			}
		}		

		$sql = "	
select t.task_id from tasks t inner join user_tasks ut on t.task_id = ut.task_id
where t.task_project = '$project_id' and ut.user_id = '$user_id'";
		return db_loadColumn($sql);

	}
	
	function getAssignableUsersPerm(){
		$sql = "
		SELECT user_id, concat(user_last_name, ', ', user_first_name) fullname, 
			if(count(permission_id)=0,0,1) cant
		FROM users left join permissions on permission_user = user_id
		WHERE user_type <> 5 
		GROUP BY user_id
		ORDER BY cant, user_last_name, user_first_name";
		return db_loadList( $sql );	
	}

	/*
	<uenrico>
	*/
	function getUserCountry($user_id=NULL, $country_id=NULL){
		global $AppUI;
		$vReturn = "";
		$arCountry = array();
		
		$user_id = !is_null($user_id) ? $user_id : $this->user_id;

		if(is_null($user_id)) return false;
		
		if(!is_null($country_id)){
			$arCountry = CLocation::getCountryName($country_id);
		}else{
			$strSql = "	SELECT location_countries.country_id, location_countries.country_name
						FROM users
							LEFT JOIN location_countries ON
								users.user_country_id = location_countries.country_id
						WHERE users.user_id = '$user_id'
						";
			db_loadHash($strSql, $arCountry);
		}
		
		if(count($arCountry) > 0){
			if($arCountry["country_id"] == 0 || is_null($arCountry["country_id"])) $arCountry["country_name"] = $AppUI->_('Not Specified');
			$vReturn = $arCountry["country_name"];
		}
		
		return $vReturn;
		
	}

	function getUserState($user_id=NULL, $country_id=NULL, $state_id=NULL){
		global $AppUI;
		$vReturn = "";
		$arState = array();
		
		$user_id = !is_null($user_id) ? $user_id : $this->user_id;

		if(is_null($user_id)) return false;
		
		if(!is_null($state_id) && !is_null($country_id)){
			$arState = CLocation::getStateName($country_id, $state_id);
		}else{
			$strSql = "	SELECT location_states.country_id, location_states.state_id, location_states.state_name
						FROM users
							LEFT JOIN location_states ON
								users.user_country_id = location_states.country_id AND
								users.user_state_id = location_states.state_id
						WHERE users.user_id = '$user_id'
						";
			db_loadHash($strSql, $arState);
		}
		
		if(count($arState) > 0){
			if($arState["state_id"] == 0 || is_null($arState["state_id"])) $arState["state_name"] = $AppUI->_('Not Specified');
			$vReturn = $arState["state_name"];
		}
		
		return $vReturn;
	}
	/*
	</uenrico>
	*/

	function getUsersPicture($users)
	{
		$sql = "SELECT user_id, user_pic FROM users WHERE user_id IN (".implode(',',$users).")";
		return db_loadList($sql);
	}	
	
	function getUsersFullName($users)
	{
		$sql = "SELECT CONCAT(user_last_name,', ', user_first_name) as fullname FROM users WHERE user_id IN (".implode(',',$users).")";
		return db_loadList($sql);
	}
	
	function getUserEmail($user_id)
	{
		$sql = "SELECT user_email FROM users WHERE user_id = ".$user_id;
		
		$userData = db_loadList($sql);
		
		if (count($userData[0]) > 0)
			return $userData[0]['user_email'];
		else
			return '';
	}	
}

/**
* Permissions class
*/
class CPermission extends CDpObject {
	var $permission_id = NULL;
	var $permission_user = NULL;
	var $permission_grant_on = NULL;
	var $permission_item = NULL;
	var $permission_value = NULL;

	function CPermission() {
		$this->CDpObject( 'permissions', 'permission_id' );
	}
	
	function check(){
		global $AppUI;
		$sql = "
			select count(*)
			from $this->_tbl
			where permission_user = '$this->permission_user'
			and   permission_grant_on = '$this->permission_grant_on'
			and   permission_item = '$this->permission_item'
		";
		//echo "<pre>$sql</pre>";
		if (db_loadResult($sql) && ! $this->permission_id) {
			return $AppUI->_('Permission already exists. Edit it instead of adding a new record.');
		}
		// TODO MORE
		return NULL; // object is ok
	}
	
	function updateWebtrackingEnabled($user_id){

		$sql = "select min(permission_value) from permissions 
						where permission_user = '$user_id'
						and permission_grant_on in( 'all', 'webtracking')";
		$perm = db_loadResult($sql);
	
		// generate cookie_string
		$cookie_string = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$cookie_string = md5( $cookie_string ).md5( time() );
		$cookie_string =  substr( $cookie_string, 0, 64 );	
		$sql = "select count(*) 
				from users
				where user_type = 1 and user_id ='$user_id'";
		//si el usuario es admin le doy acceso como admin del sist
		if (db_loadResult($sql) > 0 ){
			$sql = "update users 
					set enabled = 1 , access_level = 90,
					cookie_string = '$cookie_string'
					where user_id = $user_id; ";
			$rta = db_exec($sql);		
		}else{
					
			if ( $perm != NULL){
				$perm = ($perm==-1? "25" : "10");
				$sql = "update users 
						set enabled = 1 ,
						cookie_string = '$cookie_string'
						where user_id = $user_id; ";
				$rta = db_exec($sql);
				$sql= "update users set access_level = $perm ";
				$sql.= "where user_id = $user_id and access_level <= 25;";
				$rta = $rta && db_exec($sql);
			} else {
				$sql = "update users 
						set enabled = 0, 
						access_level = 10,
						cookie_string = '$cookie_string' 
						where user_id = $user_id";
				$rta = db_exec($sql);
			}
		}
		
		
		
		if ( $rta ){
			if($msg = CUser::setWebtrackingPermissions($user_id)){
				return $msg;
			}else{
				return NULL;
			}
			return NULL;
		}else{
			return db_error();
		}				
	}	

}

/**
* Task Permissions class
*/
class CTaskPermission {
	var $task_project = NULL;
	var $task_access_id = NULL;
	var $task_permission_on = NULL;
	var $task_user_id = NULL;
	var $task_permission_value = NULL;

	function CTaskPermission() {
		
	}
	

	/**
	* Function that returns the permissions
	* for a user on the different tasks access
	*/
	function getPermissions($user_id=0,  $project_id=0, $access_id=0, $perm_on=0){
		GLOBAL $AppUI;
		$rta=Array();
//echo "CTaskPermission::getPermissions()<br>";

		$whereTasks="";
		$whereProjects ="";
		$where="";
		if ( @$user_id != 0 ){
			// Get Assigned tasks
			$sql = "SELECT task_id FROM user_tasks WHERE user_id=$user_id";
			$Tasks = db_loadColumn( $sql );
			$whereTasks = count($Tasks)>0 ? "\n\tAND task_id IN (".implode(',',$Tasks).')':'';
			// Get Denied Projects
			$obj = new CProject();
			$deny = $obj->getDeniedRecords( $user_id );
			$whereProjects = count($deny) > 0 ? "\n\tAND tasks.task_project NOT IN (".implode(',',$deny).')':'';
			$where .= "\n\tAND 	task_user_id	= $user_id ";
			if ($user_id>0){
			
			}
		}	
		
		

		// set project's filter
		if (@$project_id>0){
			$where.= "\n\tAND task_permissions.task_project = $project_id";
		}elseif(	@$project_id<0)	{
			$where.= "\n\tAND task_permissions.task_project = -1";
		}
		//$where.= "\n\tAND task_permissions.task_project = ";
		//$where.= @$project_id != 0 ? $project_id : "-1";

		// set access's filter
		if (@$access_id!=0){
			$where.= "\n\tAND task_permissions.task_access_id = $access_id";
		}

		// set permission's filter
		if (@$perm_on!=0){
			$where.= "\n\tAND task_permissions.task_permission_on = $perm_on";
		}


		$sql="
SELECT	task_user_id,  
		CONCAT(user_first_name,' ',user_last_name) user_name, 
		task_access_id,
		access_name, 
		item_name, 
		min(task_permission_value) task_permission_value, 
		task_permission_on,
		p.project_id ,
		p.project_name
FROM	task_permissions
		LEFT JOIN tasks 
			INNER JOIN projects 
			ON tasks.task_project = projects.project_id
		ON task_access_id=task_access
		INNER JOIN task_permission_items ON task_permission_on = item_id
		LEFT JOIN task_access ON task_access_id = access_id
		LEFT JOIN users on task_user_id = users.user_id
		LEFT JOIN projects p on p.project_id = task_permissions.task_project
											
WHERE		
		users.user_id = task_user_id		
$where			

Group BY 	task_user_id, task_access_id  desc ,access_name,  item_name, task_permission_on, project_id , project_name

	";
		//echo "<pre>$sql</pre>";
		$rta=db_loadList( $sql );		
		return $rta;	
	}	

	function getItemsPermission(){
		$sql="select item_id, item_name from task_permission_items order by item_id";
		$rta=db_loadHashList( $sql );		
		return $rta;	
	}

	function getTaskAccess(){
		$sql="select access_id, access_name from task_access order by access_id";
		$rta=db_loadHashList( $sql );		
		return $rta;
	}

	function setPermission($project_id, $user_id, $access_id, $item_id, $permission_value){
		global $AppUI, $user_context, $pvs;

		$msg="";
		$ret=true;
		// Check if the item exists
		$items = $this->getItemsPermission();
		if (!($items[$item_id])){
			$msg .= "The item doesn´t exist - ";
			$ret=false;
		}
		// Check if the access exists
		$access = $this->getTaskAccess();
		if (!($access[$access_id]) && $access_id!=-1){
			$msg .= "The task access doesn´t exist - ";
			$ret=false;
		}
		// Check if the project exists
		$sql="select count(*) from projects where project_id = $project_id";
		if (db_loadResult($sql) == 0 && $project_id != "-1"){
			$msg .= "The project doesn´t exist - ";
			$ret=false;
		}

		// Check if the user exists
		$sql="select count(*) from users where user_id = $user_id";
		if ((db_loadResult($sql) == 0) && !($user_context[$user_id])) {
			$msg .= "The user doesn´t exist - ";
			$ret=false;
		}

		// Check if the user has permission to set permissions
		$canEditProject = !getDenyEdit( "projects", $project_id) && $project_id > 0;
		$canEditPermissions = !getDenyEdit( "admin" ) && $project_id < 0;
		if (!($canEditProject || $canEditPermissions)){
			$msg .= "No enought permissions - ";
			$ret=false;
		}

		//check if permission_value is valid
		if (!isset($pvs[$permission_value])){
			$msg .= "The permission value ($permission_value) is not valid - ";
			$ret=false;
		}


		$action ="";
/*
		$sql="select count(*) from task_permissions 
				where		task_project = $project_id 
				and			task_access_id = $access_id
				and			task_permission_on = $item_id
				and			task_user_id = $user_id
					";
		if (db_loadResult($sql) == 0 ){
			if ($permission_value==9){
				//$msg .= "The permission can´t be deleted because it doesn´t exist - ";
				$ret=false;
			}else{
				$action="add";
			}
		}else{
			if ($permission_value==9){
				$action = "del";
			}else{
				$action="edit";
			}
		}
*/
		if ($permission_value==9){
				$action = "del";
		}else{
			$sql="select count(*) from task_permissions 
				where		task_project = $project_id 
				and			task_access_id = $access_id
				and			task_permission_on = $item_id
				and			task_user_id = $user_id
					";	
			if (db_loadResult($sql) == 0 ){
				$action="add";
			}else{
				$action="edit";
			}	
		}
		//echo "return: $ret - Action: $action <br>";
		if ($ret){
			switch ($action){
				case "del":
					$sql="delete from task_permissions 
								where		task_project = $project_id 
								and			task_access_id = $access_id
								and			task_permission_on = $item_id
								and			task_user_id = $user_id
								";
					if (!(db_exec($sql))){
						//$msg .= "<pre>$sql</pre><br>"//.db_error();//"Error while deleting the permission - ";
						$msg .= "Error while deleting the permission - ";
						$ret=false;
					}else{
						$msg = 'updated';
					}
					break;
				case "edit":
					$sql="update task_permissions set
								task_permission_value = $permission_value
								where		task_project = $project_id 
								and			task_access_id = $access_id
								and			task_permission_on = $item_id
								and			task_user_id = $user_id
								";
					if (!(db_exec($sql))){
						//$msg .= db_error();//
						$msg.="Error while updating the permission - ";
						$ret=false;
					}else{
						$msg = "updated";
					}
					break;
				case "add":
					$sql="insert into task_permissions 
								values(	$project_id 
								,			$access_id
								,			$item_id
								,			$user_id
								,			$permission_value)
								";
					if (!(db_exec($sql))){
						//$msg .= "<pre>$sql</pre><br>";//.db_error();//
						$msg.="Error while adding the permission - ";
						$ret=false;
					}else{
						$msg = 'updated';
					}
					break;
			}
		}
//echo "<pre>$sql</pre>";
//		$AppUI->setMsg($msg, $ret ? ($action=="del" ? UI_MSG_ALERT:UI_MSG_OK ):UI_MSG_ERROR, true );
		$AppUI->setMsg( 'Role Permission' );
		$AppUI->setMsg($msg, $ret ? UI_MSG_OK :UI_MSG_ERROR, true );

		return $ret;
	}
}

/**
* Template Class
*/
class CTemplate extends CDpObject {
	var $securitytemplate_id = NULL;
	var $securitytemplate_name = NULL;
	var $securitytemplate_description = NULL;

	function CTemplate() {
		$this->CDpObject( 'securitytemplates', 'securitytemplate_id' );
	}

	function check() {
		if ($this->securitytemplate_id === NULL) {
			return 'security template id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->securitytemplate_id ) {
		// save the old password
			$ret = db_updateObject( 'securitytemplates', $this, 'securitytemplate_id', false );
		} else {
			$ret = db_insertObject( 'securitytemplates', $this, 'securitytemplate_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}
	
	function getHash(){
		$sql = "select securitytemplate_id, securitytemplate_name 
				from securitytemplates
				order by securitytemplate_name";
		return db_loadHashList($sql);
		
	}
}

/**
* Termplate Permissions class
*/
class CTemplatePermission extends CDpObject {
	var $template_permission_id = NULL;
	var $template_permission_template = NULL;
	var $template_permission_grant_on = NULL;
	var $template_permission_item = NULL;
	var $template_permission_value = NULL;

	function CTemplatePermission() {
		$this->CDpObject( 'securitytemplate_permissions', 'template_permission_id' );
	}
}


/*
* Roles Permissions class
*/
class CProjectRolesPermission {
	var $role_id = NULL;
	var $project_id = NULL;
	var $access_id = NULL;
	var $item_id = NULL;
	var $permission_value = NULL;
	var $canRead = false;
	var $canEdit = false;
	

	function CProjectRolesPermission() {

		$this->canRead = !getDenyRead( "projects" );
		$this->canEdit = !getDenyEdit( "projects" ) || !getDenyEdit( "companies" );

	}

	#
	# Function that returns the permissions
	# for a role on the different tasks access
	#
	function getPermissions($company_id = 0, $role_id=0,  $project_id=0, $access_id=0, $item_id=0){
		GLOBAL $AppUI;
		$rta=Array();

		// set project's filter
		$whereProject.= "\n\tAND rp.project_id = ";
		$whereProject .= @$project_id != 0 ? $project_id : "-1";
		$whereCompany .= "\n\tAND rp.company_id = ";
		$whereCompany .= @$company_id != 0 ? $company_id : "-1";
		// set access's filter
		$where="";
		if (@$company_id!=0){
			$where.= "\n\tAND (r.role_type=0 or r.role_type=1  and r.role_company=$company_id)";
		}
		
						
		// set access's filter
		if (@$access_id!=0){
			$where.= "\n\tAND rp.access_id = $access_id";
		}

		// set permission's filter
		if (@$item_id!=0){
			$where.= "\n\tAND rp.item_id = $item_id";
		}

		if (@$role_id!=0){
			$where.= "\n\tAND (r.role_id=$role_id)";
		}
		$sql="
(select DISTINCT 
r.role_id
, r.role_name
, -1 project_id 
, 'All' project_name
, ta.access_id
, ta.access_name
, tpi.item_id
, tpi.item_name
, 9 permission_value
from 
roles r
,task_access ta
,task_permission_items tpi 
WHERE	1=1
		$where
order by
role_id,project_id,access_id,item_id,permission_value)
union
(select DISTINCT 
r.role_id
, r.role_name
, rp.project_id 
, 'All' project_name
, rp.access_id
, ta.access_name
, tpi.item_id
, tpi.item_name
, rp.permission_value

from 
roles r
,role_permissions rp left join task_access ta on ta.access_id=rp.access_id
,task_permission_items tpi 
where
		rp.role_id = r.role_id 
	and	tpi.item_id=rp.item_id
	and	(ta.access_id=rp.access_id or rp.access_id=-1)
$whereProject
$whereCompany
$where
order by
role_id,project_id,access_id,item_id,permission_value
)
	";
		//echo "<pre>$sql</pre>";
		$rta=db_loadList( $sql );		
		$out=array();
		$r="role_id";
		$p="project_id";
		$a="access_id";
		$i="item_id";
		$v="permission_value";
		foreach($rta as $j=>$fila){
			$curval = $out[intval($fila[$r])][intval($fila[$p])][intval($fila[$a])][intval($fila[$i])];
			if (isset($curval)){
				if( $curval > $fila[$v]){
					//echo "$curval - $fila[$v] {[$fila[$r]][$fila[$p]][$fila[$a]][$fila[$i]]}";
					$out[intval($fila[$r])][intval($fila[$p])][intval($fila[$a])][intval($fila[$i])]=$fila[$v];
				}
			}else{
				$out[intval($fila[$r])][intval($fila[$p])][intval($fila[$a])][intval($fila[$i])]=$fila[$v];
			}
			unset($curval);
		}
		//$curval =$out["4"]["-1"]["1"]["1"];
		//$curval =$out[4][-1][1][1];
		//echo "PRM= $curval <br>";
		
		return $out;	
	}	

	function getItemsPermission(){
		$sql="select item_id, item_name from task_permission_items order by item_id";
		$rta=db_loadHashList( $sql );		
		return $rta;	
	}

	function getTaskAccess(){
		$sql="select access_id, access_name from task_access order by access_id";
		$rta=db_loadHashList( $sql );		
		return $rta;
	}

	function setPermission($role_id, $company_id, $project_id, $access_id, $item_id, $permission_value){
		global $AppUI, $pvs;
		//var_dump($pvs);
		$msg="";
		//echo "setPermission($role_id, $company_id, $project_id, $access_id, $item_id, $permission_value)";
		$ret=true;
		
		/*
		* Esto lo comento (FedeR) x que traia problemas, los permisos los verificamos desde afuera, si tienen permisos
		* sobre el modulo (admin) puede operar, sino no.
		// Check if the user is administrator to edit permission on general roles
		if (($role_id>0 && $role_id<5) && ($AppUI->user_type!=1) && $company_id == -1){
			$msg .="You have no permission to set this permissions - ";
			$ret=false;
		}	
		*/
		// Check if the item exists
		$items = $this->getItemsPermission();
		if (!($items[$item_id])){
			$msg .= "The item doesn´t exist - ";
			$ret=false;
		}
		// Check if the access exists
		$access = $this->getTaskAccess();
		if (!($access[$access_id]) && $access_id!=-1){
			$msg .= "The task access doesn´t exist - ";
			$ret=false;
		}
		// Check if the company exists
		$sql="select count(*) from companies where company_id = $company_id";
		if (db_loadResult($sql) == 0 && $company_id != "-1"){
			$msg .= "The company doesn´t exist - ";
			$ret=false;
		}
		
		// Check if the project exists
		$sql="select count(*) from projects where project_id = $project_id";
		if (db_loadResult($sql) == 0 && $project_id != "-1"){
			$msg .= "The project doesn´t exist - ";
			$ret=false;
		}

		// Check if the role exists
		$sql="select count(*) from roles where role_id = $role_id";
		if ((db_loadResult($sql) == 0)) {
			$msg .= "The role doesn´t exist - ";
			$ret=false;
		}

		// Check if the user has permission to set permissions
		//$canEditProject = !getDenyEdit( "projects", $project_id) && $company_id > 0;
		//Comente la linea de arriba (FedeR) si tiene permisos sobre el modulo de admin, puede operar sino no.
		$canEditProject = TRUE;
		$canEditPermissions = !getDenyEdit( "admin" ) && $company_id < 0;
		if (!($canEditProject || $canEditPermissions)){
			$msg .= "No enought permissions - ";
			$ret=false;
		}

		//check if permission_value is valid
		if (!isset($pvs[$permission_value])){
			$msg .= "The permission value ($permission_value) is not valid - ";
			$ret=false;
		}


		$action ="update";

		
		
		if ($permission_value==9){
				$action = "del";
		}

		if ($ret){
			switch ($action){
				case "del":
					$sql="delete  from role_permissions 
						where		company_id = $company_id
						and			project_id = $project_id 
						and			access_id = $access_id
						and			item_id = $item_id
						and			role_id = $role_id
								";
					if (!(db_exec($sql))){
						//$msg .= "<pre>$sql</pre><br>";//.db_error();//"Error while deleting the permission - ";
						$msg .= "Error while deleting the permission - ";
						$ret=false;
					}else{
						$msg = 'updated';
					}
					break;
				case "update":
					$sql="replace into role_permissions 
								values(	$role_id
								,			$company_id
								,			$project_id 
								,			$access_id
								,			$item_id
								,			$permission_value)
								";
					if (!(db_exec($sql))){
						//$msg .= "<pre>$sql</pre><br>";//.db_error();//
						echo "<pre>$sql</pre><br>".db_error();
						$msg.="Error while adding the permission - ";
						$ret=false;
					}else{
						$msg = 'updated';
					}
					break;
			}
		}
		/*
		if (!$ret){
			echo "<pre>$sql</pre> $msg".db_error();
		}*/
		//$AppUI->setMsg( 'Role Permission' ."$sql<br>");
		$AppUI->setMsg( 'Role Permission' );
		$AppUI->setMsg($msg, $ret ? UI_MSG_OK : UI_MSG_ERROR, true );

		return $ret;
	}
}



?>
