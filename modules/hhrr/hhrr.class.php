<?php /* DEPARTMENTS $Id: hhrr.class.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */

if (!class_exists("CUser"))
	require_once( $AppUI->getModuleClass( 'admin' ) );
##
## CDepartment Class
##
class CHhrr
{
	var $id             = NULL;
	var $firstname      = NULL;
	var $lastname       = NULL;
	var $birthday       = NULL;
	var $doctype        = NULL;
	var $docnumber      = NULL;
	var $maritalstate   = NULL;
	var $nationality    = NULL;
	var $email          = NULL;
	var $homephone      = NULL;
	var $cellphone      = NULL;
	var $address        = NULL;
	var $city           = NULL;
	var $state          = NULL;
	var $zip            = NULL;
	var $country        = NULL;
	var $children       = NULL;
	var $taxidtype      = NULL;
	var $msmessenger    = NULL;
	var $icq            = NULL;
	var $yahoomessenger = NULL;
	var $comments       = NULL;
	var $resume         = NULL;
	var $photo          = NULL;
	var $inputdate      = NULL;
	var $costperhour    = NULL;
	var $username       = NULL;
	var $password       = NULL;
	var $salarywanted   = NULL;
	var $wantsfulltime  = NULL;
	var $wantsparttime  = NULL;
	var $wantsfreelance = NULL;
	var $hoursavailableperday = NULL;
	var $workinghours   = NULL;
	var $actualcompany  = NULL;
	var $actualjob      = NULL;
	var $wasinterviewed = NULL;
	var $interviewcomments = NULL;

	function CHhrr() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM hhrr WHERE id = $oid";
		return db_loadObject( $sql, $this );
	}

	function canAdd(){
		return !getDenyEdit( "hhrr" );	
	}

	function canEdit($idhhrr){
		global $AppUI;

		$sql = "select count(*) from users where user_id = '$idhhrr' and user_type='5' ";
		$candidate=db_loadResult($sql); //Trae si el usuario que se quiere editar es un candidato o no
		
		return !getDenyEdit( "hhrr" ) OR $candidate OR ($AppUI->user_type == 1);
	/*
		return !getDenyEdit( "admin" ) or //Que tenga permisos para el modulo de USUARIOS
				($candidate and !getDenyEdit( "hhrr" )) or // O que sea un candidato y tenga permisos para el modulo de RRHH
				$idhhrr == $AppUI->user_id; //Si es el mismo usuario
	*/
	}
	
	/*
	* Esta funcion pregunta si se puede editar al usuario que se pasa en POR LO MENOS UNA SOLAPA
	*/
	function canEditOneTab($idhhrr)
	{
		global $AppUI;
		
		//Traemos los datos del usuario que se quiere editar
		$query="SELECT user_type, user_company, user_department FROM users WHERE user_id=$idhhrr;";
		//echo $query;
		$sql = mysql_query($query);
		$user_data = mysql_fetch_array($sql);
		$user_company = $user_data['user_company'];
		$user_department = $user_data['user_department'];
	
		$sql="
			SELECT 
			if((if(personal='-1',1,0) + if(matrix='-1',1,0) + if(work_experience='-1',1,0) + if(education='-1',1,0) + if(performance_management='-1',1,0) + if(compensations='-1',1,0) + if(development='-1',1,0))>0,1,0) AS can_edit 
			FROM hhrr_permissions 
			WHERE id_user=".$AppUI->user_id."
			AND ( company = -1 OR (company=$user_company AND department=-1 ) OR (company=$user_company AND department=$user_department))
			GROUP BY id_user;";
			//echo "<br>SQL: $sql<br>";
		$can_edit=db_loadResult($sql);	

		$sql = "select count(*) from users where user_id = '$idhhrr' and user_type='5' ";
		$candidate=db_loadResult($sql); //Trae si el usuario que se quiere editar es un candidato o no
	
		return $can_edit OR $candidate OR ($AppUI->user_type == 1);
		
	}
	
	function bind( $hash ) {
		
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() 
	{
		if ($this->id == NULL) {
			return 'HHRR id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if($this->wantsparttime=="")$this->wantsparttime=" ";
		if($this->wantsfulltime=="")$this->wantsfulltime=" ";
		if($this->wantsfreelance=="")$this->wantsfreelance=" ";
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'hhrr', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'hhrr', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM hhrr WHERE id = $this->id";
		
		if (!db_exec( $sql )) {
			return db_error();
		} else {
                        //Cascade delete of skill matrix:
			$sql = "DELETE FROM hhrrskills WHERE idhhrr = $this->id";
			
			if (!db_exec( $sql )) 
				return db_error();
			else	return NULL;
		}
	}
	
	function getUserSkills($user_id, $cat_id){
		
		$sql="
			SELECT 
				skills.id idskill
			  , skills.description
			  , skills.type
			  , skills.idskillcategory
			  , skills.valuedesc
			  , skills.valueoptions
			  , skills.hidelastuse
			  , skills.hidemonthsofexp
			  , hhrrskills.user_id
			  , hhrrskills.value
			  , hhrrskills.perceived_value
			  , hhrrskills.comment
			  , hhrrskills.lastuse
			  , hhrrskills.monthsofexp
			  ,	skillcategories.name
			  , skillcategories.sort		
			FROM skillcategories
			INNER JOIN skills ON skillcategories.id = skills.idskillcategory
			LEFT JOIN hhrrskills ON hhrrskills.idskill = skills.id AND hhrrskills.user_id = '$user_id'
			WHERE skillcategories.id = '$cat_id'
			ORDER BY skillcategories.sort, skills.valuedesc, skills.description;		
		";
		
		return db_loadList($sql); 
	
	}
}


class CSkill {
	var $id              = NULL;
	var $description     = NULL;
	var $valuedesc       = NULL;
	var $valueoptions    = NULL;
	var $idskillcategory = NULL;
	var $hidemonthsofexp = NULL;
	var $hidelastuse     = NULL;

	function CSkill() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM skills WHERE id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->id == NULL) {
			return 'skill id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'skills', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'skills', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT * FROM hhrrskills WHERE idskill = $this->id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "skillWithSkillMatrix";
		}
		$sql = "DELETE FROM skills WHERE id = $this->id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
class CSkillcategory {
	var $id = NULL;
	var $name = NULL;

	function CSkillcategory() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM skillcategories WHERE id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->id == NULL) {
			return 'skill category id is NULL';
		}
		// TODO MORE
		
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'skillcategories', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'skillcategories', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}
    
	
    /**
	* Borra un grupo de matriz , verifica:
	* Se fija si tiene items de matriz (skills)
	*     a - Si no tiene lo borra directamente
	*     b - Si tiene items tiene que verificar:
	*          * Si hay usuarios con items de este grupo , no borra el grupo
	*          * Si no hay usuarios con items de este grupo lo borra.
	* 
	* @return mensaje de status ( se borro o no)
	*/
	function delete() {
		$sql = "SELECT * FROM skills WHERE skills.idskillcategory = '$this->id' ";
		$res = db_exec( $sql );
		
		if (db_num_rows($res)>0){
			
			// Si hay items de este grupo se fija si algun usuario las usa
	        $sql_u = "SELECT h.user_id FROM hhrrskills as h, skills as c  WHERE h.idskill = c.id and c.idskillcategory='$this->id' ";
		    $res_u = db_exec( $sql_u );
		    
		    $items = true;
		    
		    if (db_num_rows($res_u)>0){
		      // Si hay usuarios que usen un item de este grupo , no permite borrar
		      $msg = "skillcatWithSkills";
		       
		      $delete = false;
		    }else{
		      // Si no hay usuario que usen un item de este grupo se borra
		      $delete = true;
		    }
		    
		}else{
			$delete = true;
			$items = false;
		}
		
		if ($delete){
			$sql = "DELETE FROM skillcategories WHERE id = $this->id";
			
			if (!db_exec( $sql )) {
				$msg = db_error();
			} else {
				// Si tenia items, borro tambien los items del grupo
				if ($items){
				$sql_items = "DELETE FROM skills WHERE idskillcategory = '$this->id'";
				db_exec( $sql_items );
				$msg = null;
				}
			}
		}
		
		return $msg;
		/*if (db_num_rows( $res )) {
			return "skillcatWithSkills";
		}
		$sql = "DELETE FROM skillcategories WHERE id = $this->id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}*/
	}
	
	function getSkillCategories(){
		$sql= "SELECT id, name, sort FROM skillcategories ORDER BY name;";
		return db_loadHashList($sql);
	}
}

/**
 *	Jobs Class
 *	@todo Move the 'address' fields to a generic table
 */
class CJobs extends CDpObject {
/** @var int Primary Key */
	var $job_id = NULL;
/** @var string */
	var $job_name = NULL;

// these next fields should be ported to a generic address book
	var $job_company = NULL;
	var $job_department = NULL;
	var $job_report_to = NULL;
	var $job_main_functions = NULL;
	var $job_requirements = NULL;
	var $canEdit = false;
	
	function CJobs() {
		$this->canEdit = !getDenyEdit( "hhrr" );
		$this->CDpObject( 'hhrr_jobs', 'job_id' );
	}

// overload check
	function check() {
		if ($this->job_id === NULL) {
			return 'job id is NULL';
		}
		$this->job_id = intval( $this->job_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		global $AppUI;
		
		if (!($this -> canEdit))
			$msg[]= "No permission to delete jobs.";
				
		if (count( $msg )) {
			$msg = $AppUI->_( "noDeleteRecord" ) . ": " . $AppUI->_(implode( ', ', $msg ));
			return false;
		} else {
			return true;
		}
	}
		
	function update(&$msg, $job_id=0, $job_name, $job_company, $job_department=0, $job_report_to=0, 
						$job_main_functions='', $job_requirements='', &$new_id){
		global $AppUI;
		if (!($this->canEdit)){
			$msg="No permission to update roles";
			return false;
		}
		
		$jobs=CJobs::getJobsBycompany($job_company);
		$pk = array_keys($jobs);
		$flg=true;
		for($rl=0;$rl<count($jobs);$rl++){
			if ($jobs[$pk[$rl]]["job_name"]==trim($job_name)){
				$flg = false;
			}
		}
		if (!$flg && $job_id==0){
			$msg.="Already exists a job with this name";
			return false;
		}
		
		$ret=true;
		$sql = "SELECT COUNT(*) FROM hhrr_jobs WHERE job_id = $job_id";
		// si no existen registros se debe insertar
		//echo "<pre>$sql <br> ".db_loadResult($sql);
		if (db_loadResult($sql) == 0){
			$sql="INSERT INTO hhrr_jobs (job_name, job_company, job_department, job_report_to, job_main_functions, job_requirements)
						VALUES (	'$job_name'
						,			$job_company
						,			$job_department
						,			$job_report_to
						,			'$job_main_functions'
						,			'$job_requirements' )
						";
			if (!(db_exec($sql))){
				$msg.="Error while adding the job";
				$ret=false;
			}else{
				$msg = 'added';
				$sql2="SELECT LAST_INSERT_ID();";
				$new_id = db_loadResult($sql2);
				$ret = $new_id;
			}
		}else{
		// cuando existe el rol actualizo sus campos
			$sql="UPDATE hhrr_jobs SET
				job_name = '$job_name'
				,	job_company = $job_company
				,	job_department = $job_department
				,	job_report_to = $job_report_to
				,	job_main_functions = '$job_main_functions'
				,	job_requirements = '$job_requirements'
				WHERE	job_id = $job_id";
			
			if (!(db_exec($sql))){
				$msg.="Error while updating the job";
				$ret=false;
			}else{
				$msg = "updated";
				$ret = $job_id;
			}
		}
		/*if (!$ret)
			echo "<pre>$sql</pre>";*/
		return $ret;
	}
	
	function getJobsByCompany($company_id){
		$sql = "SELECT * FROM hhrr_jobs WHERE job_company IN (-1, $company_id)";
		
		return db_loadList($sql);
	}
	
	function getJobs(){
		$sql = "SELECT job_id, job_name FROM hhrr_jobs ORDER BY job_name";
		
		return db_loadHashList($sql);
	}
	
}
?>