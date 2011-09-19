<?php /* COMPANIES $Id: companies.class.php,v 1.2 2009-07-17 18:53:19 nnimis Exp $ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 1.2 $
*/

require_once( $AppUI->getModuleClass( 'admin' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'system' ) );
require_once( $AppUI->getSystemClass( 'location' ) );

/**
 *	Companies Class
 *	@todo Move the 'address' fields to a generic table
 */
class CCompany extends CDpObject {
/** @var int Primary Key */
	var $company_id = NULL;
/** @var string */
	var $company_name = NULL;

// these next fields should be ported to a generic address book
	var $company_phone1 = NULL;
	var $company_phone2 = NULL;
	var $company_fax = NULL;
	var $company_address1 = NULL;
	var $company_address2 = NULL;
	var $company_city = NULL;
	var $company_state_id = 0;
    var $company_state = NULL;
    var $company_zip = NULL;  
    var $company_country_id = 0;
    var $company_country = NULL;
	var $company_email = NULL;

/** @var string */
	var $company_primary_url = NULL;
/** @var int */
	var $company_owner = NULL;
/** @var string */
	var $company_description = NULL;
/** @var int */
	var $company_type = null;
	
	var $company_supplier_status = 0;
	
	var $company_supplier_change_status_user = NULL;
	var $company_supplier_change_status_date = NULL;
	
	var $company_custom = null;
	var $company_smtp = NULL;
	var $company_mail_server_port = NULL;
	var $company_pop3 = NULL;
	var $company_imap = NULL;
	var $company_own_hollidays = NULL;
	var $company_start_time = NULL;
	var $company_end_time = NULL;
	var $_hollidays = NULL;
	var $_calendar = NULL;
	var $contact_id = NULL;

	function CCompany() {
		$this->CDpObject( 'companies', 'company_id' );
	}

// overload check
	function check() {
		if ($this->company_id === NULL) {
			return 'company id is NULL';
		}
		$this->company_id = intval( $this->company_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		
		return true;
		
		/*global $AppUI;
		
		$tables[] = array( 'label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company' );
		$tables[] = array( 'label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company' );
		$tables[] = array( 'label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company' );
		
		// call the parent class method to assign the oid
		$rta = CDpObject::canDelete( $msg, $oid, $tables );
		
		// verifica que no tenga permisos asignados por roles
		$sql="SELECT count(*) FROM role_permissions WHERE company_id = $this->company_id";
		if (db_loadResult($sql)>0){
			$msg = $rta ? 
						$AppUI->_( "noDeleteRecord" ) . ": " . $AppUI->_("Role Permissions"):
						$msg.", ".$AppUI->_("Role Permissions");
						
			$rta=false;
		}
			
		return $rta;
		*/
	}
	
	function delete() {
		global $AppUI;
		
		
		$sql = "/****START COMPANY DELETION****/
				select project_id 
				FROM projects p
				WHERE project_company = $this->company_id"; 
		$lst = db_loadColumn($sql);
		for($i = 0; $i < count($lst); $i++){
			$obj = new CProject();
			if (!$obj->load($lst[$i], false)){
				$AppUI->setMsg( 'Projects' );
				$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
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
		
		
		$sql = "DELETE FROM departments WHERE dept_company = $this->company_id";
		db_exec($sql);	
		$sql = "UPDATE users SET user_status = 1, user_company = 0 WHERE user_company = $this->company_id";
		db_exec($sql);	
		$sql = "DELETE FROM roles WHERE role_company = $this->company_id and role_type = 1";
		db_exec($sql);	
		$sql = "DELETE FROM role_permissions WHERE company_id = $this->company_id";
		db_exec($sql);
		$sql = "DELETE FROM hhrr_ant WHERE company = $this->company_id";	
		db_exec($sql);				
		$sql = "DELETE FROM companies WHERE company_id = $this->company_id ";
		
		/****END COMPANY DELETION****/
		
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function getUsersCompany($company_id){
		
		$sql = "  SELECT DISTINCT user_id, CONCAT(user_last_name, ', ', user_first_name) as name FROM users";
		$sql .= " WHERE user_company = ".$company_id;

		return db_loadHashList( $sql );	
	}	

	function getCompanies($user_id,$strSearch=0,$order_by=0){
		$deny = $this->getDeniedRecords( $user_id );
		$orderby = $order_by ? $order_by : 'company_name';

		$sql = "SELECT company_id, company_name, company_type, company_description,"
			. "count(distinct projects.project_id) as countp, count(distinct projects2.project_id) as inactive,"
			. "user_first_name, user_last_name"

			. " FROM permissions, companies"

			. " LEFT JOIN projects ON companies.company_id = projects.project_company and projects.project_active <> 0"
			. " LEFT JOIN users ON companies.company_owner = users.user_id"
			. " LEFT JOIN projects AS projects2 ON companies.company_id = projects2.project_company AND projects2.project_active = 0"
			. " WHERE permission_user = $user_id"
			. "	AND permission_value <> 0"
			. " AND (
				(permission_grant_on = 'all')
				OR (permission_grant_on = 'companies' and permission_item = -1)
				OR (permission_grant_on = 'companies' and permission_item = company_id)
				)"
			. (count($deny) > 0 ? ' AND company_id NOT IN (' . implode( ',', $deny ) . ')' : '');

		if(@$strSearch){
			$sql .= $strSearch;
		}

		$sql .= " GROUP BY company_id 
				 ORDER BY $orderby";	
		//echo "<pre>$sql</pre>";
		return db_loadList( $sql );	
	}
	
	function getPermissions($role_id=0){
		global $user_context;
		if (@$this->company_id){
			$obj=new CProjectRolesPermission();
			$perms=$obj->getPermissions($this->company_id, $role_id);
		}else{
			$perms= null;
		}
		return $perms;
	}
	
	function setPermission($role_id, $project_id, $access_id, $item_id, $permission_value){
		//echo " setPermission($role_id, $this->company_id, $project_id, $access_id, $item_id, $permission_value);";
		if (@$this->company_id){
			
			$obj=new CProjectRolesPermission();	
			$rta =$obj->setPermission($role_id, $this->company_id, $project_id, $access_id, $item_id, $permission_value);
			//$rta =CProjectRolesPermission::setPermission($role_id, $this->company_id, $project_id, $access_id, $item_id, $permission_value);
		}else{
			$rta=false;
		}
		return $rta;
	}	

	function assigndefaultpermissions($company_id=0){
		
		$company_id= $company_id ? $company_id : $this->company_id;
		
		if (is_null($company_id) || $company_id==0){
			return false;
		}
		
		$sql = "delete from role_permissions where company_id = $company_id";
		//echo "<pre>$sql</pre>"."<br>";
		
		if (!db_exec($sql)){echo db_error();return false;}
		$sql="	drop table if exists tmp999;";
		//echo "<pre>$sql</pre>"."<br>";
		if (!db_exec($sql)){echo db_error();return false;}
		$sql="create temporary table tmp999 
				select role_id, 
						$company_id as `company_id`,
						project_id,
						access_id,
						item_id,
						permission_value 
				from role_permissions
				where company_id=-1;";	
		//echo "<pre>$sql</pre>"."<br>";
		if (!db_exec($sql)){echo db_error();return false;}
		$sql="insert into role_permissions 
				select role_id,
						company_id,
						project_id,
						access_id,
						item_id,
						permission_value 		
				from tmp999;";
		//echo "<pre>$sql</pre>"."<br>";
		if (!db_exec($sql)){echo db_error();return false;}
		$sql="	drop table if exists tmp999;";
		//echo "<pre>$sql</pre>"."<br>";
		if (!db_exec($sql)){echo db_error();return false;}
		
		return true;
	}

/*	function getHollidays($company_id=0, $holliday_year="")
	{
		$sql = "SELECT holliday_id 
		FROM hollidays
		WHERE holliday_year=$holliday_year AND holliday_company = $this->company_id";
		
		return db_loadHashList( $sql );
	}	
*/	
	function getHollidays($company_id=0, $year=""){

		if ($company_id == 0){
			if ($this->company_id){
				$company_id = $this->company_id;
			}
		}
		if ($company_id){
			return CHolliday::getHollidays($company_id,$year);			
		}		
		return false;
	}
		
	function loadHollidays()
	{
		if ($msg = $this->check){
			return $msg;
		}
		
		$sql = "
			SELECT 
			DATE_FORMAT(concat(holliday_year, '-', holliday_month,'-',  holliday_day),  '%Y%m%d'), holliday_id
			FROM `hollidays`
			WHERE holliday_company ";
		
		$sql .= ($this->company_own_hollidays == 0 ? "= $this->company_id" : "IS NULL");

		//echo "<pre>$sql</pre>";
		$holl_list = db_loadHashList( $sql );
		$this->_hollidays=array();
		$holl_days = array_keys($holl_list);
		for ($i=0; $i<count($holl_days);$i++){
			$holliday = $holl_days[$i];
			$holliday_id = $holl_list[$holl_days[$i]];
			$this->_hollidays[$holliday] = new CHolliday();
			$this->_hollidays[$holliday]->load($holliday_id);
		}
	}
	
	function loadCalendar()
	{
		if ($msg = $this->check){
			return $msg;
		}
		
		$sql = "
			SELECT 
			calendar_day, calendar_id
			FROM `calendar`
			WHERE calendar_company = $this->company_id";
		//echo "<pre>$sql</pre>";
		$cal_list = db_loadHashList( $sql );
		$this->_calendar=array();
		$cal_days = array_keys($cal_list);
		for ($i=0; $i<count($cal_days);$i++){
			$day = $cal_days[$i];
			$calendar_id = $cal_list[$cal_days[$i]];
			$this->_calendar[$day] = new CCalendar();
			$this->_calendar[$day]->load($calendar_id);
		}
	}	
	
	function getCalendars(){
		if ($msg = $this->check){
			return $msg;
		}
		
		return CCalendar::getCompanyCalendars($this->company_id);	
	}
	
	function getActiveCalendars($company_id=0)
	{

		$company_id = $company_id ? $company_id : $this->company_id;
		if (is_null($company_id) || $company_id==0){
			return false;	
		}
		
		return CCalendar::getActiveCalendars(1, $company_id);
	}	
	
/**
 *	Inserts a new row if id is zero or updates an existing row in the database table
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function store( $updateNulls = false ) {
		global $AppUI;
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		$k = $this->_tbl_key;
		if( $this->$k ) {
			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
			if( $ret ){
				if (!$this->assigndefaultpermissions($this->company_id)){
					$msg = "Problems assigning permissions. Remember to assign the permissions manualy.";
					return get_class( $this )."::store failed <br />".$AppUI->_($msg);
				}
				// obtengo los calendarios del sistema	
				$params = array();
				$params["calendar_company"] = "0";
				$params["calendar_project"] = "0";
				$params["calendar_user"] = "0";
				$params["calendar_from_date"] = "'0000-00-00 00:00:00'";
				//echo "<pre>"; var_dump($params);echo "</pre>";
				$cal = CCalendar::getCalendars($params);
				//echo "<pre>"; var_dump($cal);echo "</pre>";
				if (count($cal) == 0){
					$msg = "The systems calendar by default doesn't exists. Contact the administrator";
					return get_class( $this )."::store failed <br />".$AppUI->_($msg);
				}
				
				$calendar_id = $cal[0]["calendar_id"];
				
				// creo el nuevo calendario para la empresa
				$cal = new CCalendar();
				$cal->load($calendar_id);
				$cal->loadCalendarDays();
				$cal->calendar_id="";
				$cal->calendar_company = $this->company_id;
				if (($msg = $cal->store())) {
					return get_class( $this )."::store failed <br />".$AppUI->_($msg);
				}
				$calendar_id = $cal->calendar_id;
				
				//agregos los dias al calendario
				for($i=1;$i<=7;$i++){
					$calday = new CCalendarDay();
					$calday->load($cal->_calendar_days[$i]->calendar_day_id);
					$calday->calendar_day_id ="";
					$calday->calendar_id = $calendar_id;
					if (($msg = $calday->store())) {
						return get_class( $this )."::store failed <br />".$AppUI->_($msg);
					}					
					unset($calday);
				
				}
				unset($calday);			
			}
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}
	
	function getCountry($id=NULL, $country_id=NULL){
		$vReturn = "";
		$arCountry = array();
		
		$id = !is_null($id) ? $id : $this->company_id;

		if(is_null($id)) return false;
		
		if(!is_null($country_id)){
			$arCountry = CLocation::getCountryName($country_id);
		}else{
			$strSql = "	SELECT location_countries.country_id, location_countries.country_name
						FROM companies
							LEFT JOIN location_countries ON
								companies.company_country_id = location_countries.country_id
						WHERE companies.company_id = '$id'
						";
			db_loadHash($strSql, $arCountry);
		}
		
		if(count($arCountry) > 0){
			if($arCountry["country_id"] == 0 || is_null($arCountry["country_id"])) $arCountry["country_name"] = "Not Specified"; 
			$vReturn = $arCountry["country_name"];
		}
		
		return $vReturn;
		
	}
	
	function getState($id=NULL, $country_id=NULL, $state_id=NULL){
		$vReturn = "";
		$arState = array();
		
		$id = !is_null($id) ? $id : $this->company_id;

		if(is_null($id)) return false;
		
		if(!is_null($state_id) && !is_null($country_id)){
			$arState = CLocation::getStateName($country_id, $state_id);
		}else{
			$strSql = "	SELECT location_states.country_id, location_states.state_id, location_states.state_name
						FROM companies
							LEFT JOIN location_states ON
								companies.company_country_id = location_states.country_id AND
								companies.company_state_id = location_states.state_id
						WHERE companies.company_id = '$id'
						";
			db_loadHash($strSql, $arState);
		}
		
		if(count($arState) > 0){
			if($arState["state_id"] == 0 || is_null($arState["state_id"])) $arState["state_name"] = "Not Specified";
			$vReturn = $arState["state_name"];
		}
		
		return $vReturn;
	}
	
	function getSuppliersStatusTypes()
	{
		global $AppUI;
		
		$sql = "SELECT suppliers_status_type_id, suppliers_status_type_name_".$AppUI->user_prefs['LOCALE']." as name ";
		$sql .= "FROM suppliers_status_types";

		return db_loadHashList($sql, $index);		
	}
}



/**
 *	Roles Class
 *	@todo Move the 'address' fields to a generic table
 */
class CRoles extends CDpObject {
/** @var int Primary Key */
	var $role_id = NULL;
/** @var string */
	var $role_name = NULL;

// these next fields should be ported to a generic address book
	var $role_description = NULL;
	var $role_company = NULL;
	var $role_type = NULL;
	var $role_module = NULL;
	var $canRead = false;
	var $canEdit = false;
	

	function CRoles() {
		$this->canRead = !getDenyRead( "projects" );
		$this->canEdit = !getDenyEdit( "projects" ) || !getDenyEdit( "companies" );
		$this->CDpObject( 'roles', 'role_id' );
	}

// overload check
	function check() {
		if ($this->role_id === NULL) {
			return 'role id is NULL';
		}
		$this->role_id = intval( $this->role_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		global $AppUI;
		
		if (!($this -> canEdit))
			$msg[]= "No permission to delete roles.";
		
		$sql = "SELECT COUNT(*) FROM project_roles WHERE role_id = ".$this->role_id;
		// si el rol no tiene proyectos asociados puede borrarse		
		if (db_loadResult($sql)!=0){
			$msg[]=$AppUI->_( "Project Roles" );
		}

		/*$sql = "SELECT COUNT(*) FROM role_permissions WHERE role_id = ".$this->role_id;
		// si el rol no tiene proyectos asociados puede borrarse		
		if (db_loadResult($sql)!=0){
			$msg[]=$AppUI->_( "Role Permissions" );   //ESTO ESTA FALLANDO
		}*/
				
		if (count( $msg )) {
			$msg = $AppUI->_( "noDeleteRecord" ) . ": " . $AppUI->_(implode( ', ', $msg ));
			return false;
		} else {
			return true;
		}
	}
	
		
	function update(&$msg, $role_id=0, $role_name, $role_company, $role_description='', &$new_id){
		global $AppUI;
		if (!($this->canEdit)){
			$msg="No permission to update roles";	
			return false;
		}
		
		if (($role_id>0 && $role_id<5) && ($AppUI->user_type!=1)){
			$msg="No permission to update this role";	
			return false;
		}
				
		$roles=CRoles::getRoles($role_company);
		$pk = array_keys($roles);
		$flg=true;
		for($rl=0;$rl<count($roles);$rl++){
			if ($roles[$pk[$rl]]["role_name"]==trim($role_name)){
				
				$flg = false;
			}
		}	
		if (!$flg && $role_id==0){
			$msg.="Already exists a role with this name";
			return false;
		}
			
		$ret=true;			
		$sql = "select count(*) from roles where role_id = $role_id";
		// si no existen registros se debe inserta
		//echo "<pre>$sql <br> ".db_loadResult($sql);
		if (db_loadResult($sql) == 0){
			$sql="insert into roles (role_name, role_company, role_description, role_type)
						values(		'$role_name'
						,			$role_company
						,			'$role_description'
						, 			1)
						";
			if (!(db_exec($sql))){
				$msg.="Error while adding the role";
				$ret=false;
			}else{
				$msg = 'added';				
				$sql2="SELECT LAST_INSERT_ID();";
				$new_id = db_loadResult($sql2);
			}
		}else{
		// cuando existe el rol actualizo sus campos
			$sql="update roles set
				role_name = '$role_name'
				,	role_company = $role_company
				,	role_description = '$role_description'
				where	role_id = $role_id";
			
			if (!(db_exec($sql))){
				$msg.="Error while updating the role";
				$ret=false;
			}else{
				$msg = "updated";
			}
		}
		/*if (!$ret)
			echo "<pre>$sql</pre>";*/
		return $ret;
	}
	
	function getRoles($company_id, $role_type=null){
		$sql = "select * from roles where role_status = 0 and role_company in (-1, $company_id)";
		if (!is_null($role_type)){
			$sql .= " and role_type = $role_type";
		}
		return db_loadList($sql);
	}

}


/**
 *	ProjectRoles Class
 *	@todo Move the 'address' fields to a generic table
 */
class CProjectRoles {

	var $role_id = NULL;
	var $project_id = NULL;
	var $user_id = NULL;

	var $role_name = NULL;
	var $project_name = NULL;
	var $user_first_name = NULL;
	var $user_last_name = NULL;	
	
	var $canRead = false;
	var $canEdit = false;

	function CProjectRoles() {
		$this->canRead = !getDenyRead( "projects" );
		$this->canEdit = !getDenyEdit( "projects" ) || !getDenyEdit( "companies" );
	}

	function load($role_id, $project_id){
		
		if(!$this->canRead){
			return false;
		}
		
		$sql = "SELECT project_roles.role_id, project_roles.project_id, project_roles.user_id 
					, role_name, project_name, user_first_name, user_last_name
				FROM project_roles 
				NATURAL JOIN roles
				NATURAL JOIN projects
				NATURAL JOIN users
				WHERE 	role_id = '$role_id'
				AND 	project_id = '$project_id'";
		
		$list = db_loadList($sql);
		foreach ($list as $field=>$value){
			$this->$field = $value;
		}
		return true;
	}

	function getList($role_id=NULL,$project_id=NULL){
		if(!$this->canRead){
			return false;
		}
		$where = "";
		$where .= is_null($role_id)? "" : "\n\t AND \t project_roles.role_id = '$role_id'";
		$where .= is_null($project_id)? "" : "\n\t AND \t project_roles.project_id = '$project_id'";
		
		$sql = "SELECT project_roles.* 
					, role_name, project_name, user_first_name, user_last_name
				FROM project_roles 
				INNER JOIN roles ON roles.role_id = project_roles.role_id
				INNER JOIN projects ON projects.project_id = project_roles.project_id
				INNER JOIN users on users.user_id = project_roles.user_id
				WHERE 	1=1 $where";
		//echo "<pre>$sql</pre>";
		return db_loadList($sql);
	}
	
	function getAssignedRoles($project_id, $company_id=0){
		if(!$this->canRead){
			return false;
		}
		
		if($project_id==-1){
			if($company_id!=0){
			$sql = "SELECT project_roles.role_id,  role_name, role_type
					FROM project_roles 
					NATURAL JOIN roles
					WHERE 	project_roles.project_id = $project_id 
					AND role_type <> 0 
					AND	role_company = $company_id";
			#echo "<pre>$sql</pre>";
			$rdo =  db_loadHashList($sql);	
			#echo "anduvo";
			}
		}else{
			$sql = "SELECT project_roles.role_id,  role_name, role_type
					FROM project_roles 
					NATURAL JOIN roles
					WHERE 	project_roles.project_id = $project_id AND role_type <> 0 ";
			#echo "<pre>$sql</pre>";
			$rdo =  db_loadHashList($sql);	
			#echo "anduvo";		
		}
		return $rdo;	
	}
	
	function getUnassignedRoles($project_id, $company_id=0){
		if(!$this->canRead){
			return false;
		}		
	
		$assigned = $this->getAssignedRoles($project_id, $company_id);
		$assigned = array_keys($assigned);

		$where= count($assigned)>0 ? 
			"AND roles.role_id NOT IN (" . implode( ',', $assigned ) . ")":
			"";		
		#echo "NOASIG";
		if($project_id==-1){
			if($company_id!=0){			
					$sql = "SELECT role_id, role_name, role_type
							FROM roles inner join  projects on project_company = role_company
							WHERE 	project_id = $project_id AND role_type <> 0 $where";
					#echo "<pre>$sql</pre>";
					$rdo = db_loadHashList($sql);
			}
		}else{
			$sql = "SELECT role_id, role_name, role_type
					FROM roles inner join  projects on project_company = role_company
					WHERE 	project_id = $project_id AND role_type <> 0 $where";
			#echo "<pre>$sql</pre>";		
			$rdo = db_loadHashList($sql);
		}					
		return $rdo;			
	}
	
	function getAssignedUsers($role_id, $project_id){
		if(!$this->canRead){
			return false;
		}		
		$sql="	SELECT pr.user_id , CONCAT(user_last_name,', ', user_first_name) user_full_name
				FROM project_roles pr NATURAL JOIN users
				WHERE project_id = $project_id 
				AND		role_id = $role_id";
		#echo "<pre>$sql</pre>";
		return db_loadHashList($sql);
	}
		
	function getUnassignedUsers($role_id, $project_id){
		if(!$this->canRead){
			return false;
		}		
		$assigned = $this->getAssignedUsers($role_id, $project_id);
		$assigned = array_keys($assigned);

		$where= count($assigned)>0 ? 
			"WHERE users.user_id NOT IN (" . implode( ',', $assigned ) . ") and user_type <> 5":
			"";
		
		$sql="	SELECT user_id , CONCAT(user_last_name,', ', user_first_name) user_full_name
				FROM  users
				$where";
		#echo "<pre>$sql</pre>";
		return db_loadHashList($sql);	
	}
	
	function update(&$msg, $role_id, $project_id, $user_id){
		if(!$this->canEdit){
			$msg.= "You have no permission.";
			return false;
		}	
		$sql = "select count(*) from roles where role_id = $role_id";
		if (db_loadResult($sql)==0){
			$msg.= "The role doesn't exist.";
			return false;
		}
		$sql = "select count(*) from projects where project_id = $project_id";
		if (db_loadResult($sql)==0){
			$msg.= "The project doesn't exist.";
			return false;
		}				
		$sql = "select count(*) from users where user_id = $user_id";
		if (db_loadResult($sql)==0){
			$msg.= "The user doesn't exist.";
			return false;
		}
		
		$sql="REPLACE INTO project_roles ( project_id, role_id, user_id) values ( $project_id, 2, $user_id); ";
		if (!(db_exec($sql))){
			$msg.="Error while adding the user to the project";
			return false;
		}else{				
			$sql="REPLACE INTO project_roles ( project_id, role_id, user_id) values ( $project_id, $role_id, $user_id); ";
			#echo "<pre>$sql</pre>";
			if (!(db_exec($sql))){
				$msg.="Error while adding the user";
				return false;
			}else{
				$msg = 'added';
				return true;
			}		
		}	

	}
	
	function delete(&$msg, $role_id, $project_id, $user_id){
		if(!$this->canEdit){
			$msg.= "You have no permission.";
			return false;
		}			
		
		$unassigned = $this->getAssignedUsers($role_id, $project_id);
		if (!isset($unassigned[$user_id])){
			$msg.= "The user is not already assigned.";
			return false;
		}
		$sql="SELECT task_id from tasks where task_project = $project_id";
		$task_list = db_loadColumn($sql);
		if (count($task_list)>0){
			$sql="	DELETE FROM user_tasks 
					WHERE task_id IN (".implode($task_list,", ").")  
					AND user_id = $user_id";
			
			if (!(db_exec($sql))){
				echo "<pre>$sql</pre>";
				$msg.="Error while deleting the assigned tasks";
				return false;
			}	
		}
					
		$sql="	DELETE FROM project_roles 
				WHERE project_id = $project_id 
				AND role_id = $role_id 
				AND user_id = $user_id";
		#echo "<pre>$sql</pre>";
		if (!(db_exec($sql))){
			$msg.="Error while deleting the user";
			return false;
		}else{
			$msg = 'deleted';
			return true;
		}		
		
		
	}
		
	
		
}


?>
