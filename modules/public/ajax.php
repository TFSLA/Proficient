<?
include_once("modules/companies/companies.class.php");
include_once("modules/projects/projects.class.php");
include_once("modules/twitter/functions.php");

$xajax->registerFunction("addCanal");
$xajax->registerFunction("addProjects");
$xajax->registerFunction("addUsersProjects");
$xajax->registerFunction("addSelect_Departments");
$xajax->registerFunction("combo_UserSupervisor");
$xajax->registerFunction("changeTwitterMessage");
$xajax->registerFunction("changeTwitterStatus");
$xajax->registerFunction("getTwitterUserData");
$xajax->registerFunction("getTwitterPopUpData");
$xajax->registerFunction("changeTwitterShowHide");
$xajax->registerFunction("showFavoritesItems");
$xajax->registerFunction("showLogin");
$xajax->registerFunction("checkLogin");
$xajax->registerFunction("completeAssignment");

/**
 * Agrega canales de acuerdo al id de la empresa ingresada, verifica las empresas para las que tiene permiso
 *
 * @param String  $f_canal = Nombre del campo a actualizar
 * @param Integer $company_id = id de la empresa de la que se traerán los canales
 * @param Integer $selected = id del canal selecionado
 * @param Boolean $all_canal = TRUE, agrega la opción de "todos/all" en el inicio del combo
 * @param String  $modulo = En que módulo se muestra el combo para determinar los permisos en caso que sea necesario
 * @param String  $tab = En que tab dentro de un módulo se muestra el combo para determinar permisos en caso que sea necesario
 * 
 * @return Opciones para el select de canal, solo con las empresas que el usuario puede ver.
 */
function addCanal($f_canal, $company_id, $selected = "", $all_canal = TRUE , $modulo, $tab )
{
   global $AppUI;
   
   $objResponse = new myXajaxResponse();
    
      // Preparo el registro para "TODOS" //
	   if($all_canal == 'TRUE'){
		 $texto_all =  array( '0'=>$AppUI->_('All'));
	   }
			      
	   if($all_canal == ''){
		 $texto_all =  array( '0'=>'');
	   }
			      
	   if($all_canal == 'FALSE'){
		 $texto_all = array('' =>$AppUI->_("No data available"));
	   }
	   
	   
   
   if ($modulo == "")
   {   
   	   // Si no ingresa el modulo, me fijo a nivel general sobre que empresas tiene permisos de lectura
	   if($company_id == "" || $company_id == 0 )
	   {
		      $obj = new CCompany();
		      $allow = $obj->getAllowedRecords( $AppUI->user_id, 'company_id');
		      
		      if ($AppUI->user_type == '1')
		      {
		        $sql = "SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE p.project_canal = c.company_id  order by company_name ";
		      }
		      else 
		      {
		        $sql = "SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE p.project_canal = c.company_id  AND company_id IN (" . implode( ',', $allow ) . ") order by company_name ";
		      }
		      
		      $list = db_loadHashList( $sql );
		      
		      if($all_canal == 'TRUE' || $all_canal=="")
		      {
		      $list = arrayMerge( $texto_all, $list );
		      }
		      
		      
		      if($all_canal == 'FALSE'){
		      	
		      	if (count($list) == 0)
		      	{
		      	   $list = $texto_all;
		      	}
		      	
		      }
	   }
	   else 
	   {
	   	  if ($company_id >0)
	   	  {
		   	  $obj = new CCompany();
		      $allow = $obj->getAllowedRecords( $AppUI->user_id, 'company_id');
		      
		      if ($AppUI->user_type=='1')
		      {
		      	$sql = "SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE project_company = '".$company_id."' AND p.project_canal = c.company_id order by company_name ";
		      }
		      else 
		      {
		        $sql = "SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE project_company = '".$company_id."' AND p.project_canal = c.company_id  AND company_id IN (" . implode( ',', $allow ) . ") order by company_name ";
		      }
		      
		      $list = db_loadHashList( $sql );
		      
		      
		      if($all_canal == 'TRUE' || $all_canal=="")
		      {
		      $list = arrayMerge( $texto_all, $list );
		      }
		      
		      
		      if($all_canal == 'FALSE'){
		      	
		      	if (count($list) == 0)
		      	{
		      	   $list = $texto_all;
		      	}
		      	
		      }
	   	  }
	   	  else{
	   	  	    $list = $texto_all;
	   	  	    
	   	  }
	   
	   }
   }
  
   $objResponse->addCreateOptions($f_canal, $list,$selected);
   return $objResponse->getXML();
 
   	
}

/**
 * Agrega projectos de acuerdo al id de la empresa indicado y verifica permisos de acuerdo al modulo
 *
 * @param Integer $company_id = id de la empresa de la que se traerán los projectos
 * @param Integer $canal_id = id de la empresa canal de la que se traerán los projectos
 * @param Integer $selected = id del proyecto que estaba seleccionado
 * @param Boolean $all_projects = True, al inicio del combo agrega la opción de todos los proyectos
 * @param String  $modulo = En que módulo se muestra el combo para determinar los permisos en caso que sea necesario
 * @param String  $tab = En que tab dentro de un módulo se muestra para determinar los permisos en caso que sea necesario para determinar que proyectos mostrar.
 * @param String $f_projects = nombre del campo de proyectos, si esta vacio no arma el combo
 * 
 * @return Opciones del Select, solo con los proyectos que el usuario puede ver o modificar.
 */
function addProjects($company_id, $canal_id, $selected="", $all_projects, $modulo, $tab, $f_projects ) 
{
	global $AppUI;
	
    $objResponse = new myXajaxResponse();
    
    	// Si no ingresa el modulo, me fijo a nivel general sobre que empresas tiene permisos de lectura
    	if($modulo =="")
    	{
    		
    		if($company_id > 0)
    		{
    			$company_sql = "AND project_company='".$company_id."'";
    		}
		        
	        if ($canal_id > 0)
	        {
	           $canal_sql = "AND project_canal = '$canal_id' ";
	        }
	            
	       
		     $obj = new CProject();
		     $allow = $obj->getAllowedRecords($AppUI->user_id, "project_id");
		            
		     if (count($allow)== 0)
		     {
		         $allow[0] = 0;
		     }
		            
		     if($AppUI->user_type == 1)
		     {
			     $sql = "SELECT project_id, project_name FROM projects WHERE 1=1 ".$company_sql." ".$canal_sql."  order by project_name ";
		     }
		     else
		     {
		         $sql = "SELECT project_id, project_name FROM projects WHERE 1=1 ".$company_sql." ".$canal_sql." AND project_id IN (" . implode( ',', $allow ) . ") order by project_name ";
		     }
	            
		      $list = db_loadHashList( $sql );
		        
		      if($all_projects == "TRUE")
		      {
		         $list = arrayMerge( array( '0'=>$AppUI->_('All') ), $list );
		      }
		      else
		      {
		           if (count($list) == 0)
		      	   {
		      	   $list=array('' =>$AppUI->_("No data available"));
		      	   }
		      }
	        
    	}
    	
    $objResponse->addCreateOptions($f_projects, $list,$selected);
    return $objResponse->getXML();
}

/**
 * Carga los usuarios asociados a un proyecto si se pasa el id del proyecto o todos los usuarios que se tengan permisos
 *
 * @param String  $selectId = Nombre del campo a modificar
 * @param Integer $p_project_id = Id del proyecto
 * @param Integer $canal_id = Id de la empresa canal del proyecto
 * @param Integer $company_id = Id de la empresa a la que pertenece el proyecto
 * @param Integer $selected = Id de la opcion del select que se encuentra pre-seleccionada
 * 
 * @return Opciones para el select de los usuarios de un proyecto
 */
function addUsersProjects($selectId, $p_project_id, $canal_id, $company_id, $selected="") 
{
	global $AppUI;
    $objResponse = new myXajaxResponse();
  
	$strUsersSqlWhere = "";
	
	if($p_project_id > 0){
		$strUsersSqlWhere .= " AND projects.project_id='$p_project_id' ";
	}
	
	if($canal_id > 0){
		$strUsersSqlWhere .= "AND projects.project_canal = '$canal_id' ";
	}
	
	if($company_id > 0){
		$strUsersSqlWhere .= "AND projects.project_company = '$company_id' ";
	}
	
	if ($strUsersSqlWhere=="")
	{
		$sql = "
		SELECT DISTINCT(user_id), CONCAT(user_last_name, ', ', user_first_name)AS name
		FROM users
		LEFT JOIN permissions ON user_id = permission_user
		LEFT JOIN companies ON company_id = user_company
		where user_type <> 5 order by user_last_name,user_first_name
		";
	}
	else
	{
		$sql="
		SELECT DISTINCT(users.user_id), CONCAT(user_last_name, ', ', user_first_name)AS name
		FROM projects
		LEFT JOIN project_roles ON projects.project_id = project_roles.project_id
		LEFT JOIN users ON project_roles.user_id = users.user_id
		WHERE users.user_type <> '5' $strUsersSqlWhere order by user_last_name,user_first_name
		";
	}
   
	$list = db_loadHashList( $sql );
	
	//Con esto lo que hago es codificar cada uno de los nombres en utf8
	//Intente que la conversion la haga mysql pero parece que con esta version no se puede.
	foreach($list as $row => $val)
	{
		$list[$row]=utf8_encode($val);
	}
	
	//Agrego el elemento ALL al principio del vector.
	//Uso el + dado que como los indices son numericos(el id del usuario) al usar ARRAY_UNSHIFT o array_merge me reordena los indices y se pierde la asociasion con el ID.
	$all=array(0=> $AppUI->_("All"));
    $list = $all + $list;
  
    $objResponse->addCreateOptions($selectId, $list,$selected);
    return $objResponse->getXML();
}


/**
 * Agrega departamentos al SELECT de acuerdo al id de la empresa ingresado
 *
 * @param String  $selectId = Nombre del campo select con los departamentos
 * @param Integer $company = Id de empresa de la que se traeran los departamentos
 * @param Integer $selected = Id del departamento preseleccionado
 * @param String  $modulo = Nombre del modulo donde se muestra el combo
 * @param String  $tab = Nombre del tab dentro del modulo donde se muestra el combo
 * @param String  $all_dept = Texto para el campo "Todos", si se deja vacio no agrega el registro para todos.
 * 
 * @return Opciones del Select con los departamentos de acuerdo a la empresa.
 */
function addSelect_Departments($selectId, $company,$selected="", $modulo, $tab, $all_dept) {
  global $AppUI;
  $objResponse = new myXajaxResponse();
  
  if ($AppUI->user_type == 1 || ($modulo =="" && $tab=="")){
  $sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company='".$company."' order by dept_name";
 
  }
  
  # Traigo los departamentos de acuerdo al tab 
  if ($AppUI->user_type != 1 && ($modulo=="hhrr" && $tab!=""))
  {

  	// Departamentos en particular denegados
  	$sql_particularDeny = "SELECT department FROM hhrr_permissions WHERE id_user='".$AppUI->user_id."' AND company='".$company."' AND ".$tab."='0' AND department<>'-1' ";
  	$deny_dept =  db_loadHashList( $sql_particularDeny );
  	
  	if (count($deny_dept)>0)
  	{
  		$andNotIn_dept = "(". implode( ',', $deny_dept ) .")";
  	}else{
  		$andNotIn_dept = "('0')";
  	}
  	
  	
    // Empresas con el tab denegado para todos los departamentos
    $sql_AllDeny = "SELECT department FROM hhrr_permissions WHERE id_user='".$AppUI->user_id."' AND company='".$company."' AND ".$tab."='0' AND department='-1' ";
  	$deny_Alldept =  db_loadHashList( $sql_AllDeny );
  	
  	if (count($deny_Alldept)>0)
  	{
  		$DenyTab_Alldept = true;
  	}else{
  		$DenyTab_Alldept = false;
  	}
  	
  	// Todas las empresas con el tab denegado para todos los departamentos
    $sql_AllDeny = "SELECT department FROM hhrr_permissions WHERE id_user='".$AppUI->user_id."' AND company='-1' AND ".$tab."='0' AND department='-1' ";
  	$deny_Alldept =  db_loadHashList( $sql_AllDeny );
  	
  	if (count($deny_Alldept)>0)
  	{
  		$DenyTabAll_cia = true;
  	}else{
  		$DenyTabAll_cia = false;
  	}
  
  	// Si tiene el tab negado para todas las empresas/ todos los departamentos o para esta empresa y todos los departamentos
  	if ($DenyTab_Alldept && $DenyTabAll_cia)
  	{
  		$sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company='".$company."' AND dept_id ='-1' order by dept_name";
  	}
  	else
  	{
  	    $sql_particular = "SELECT department FROM hhrr_permissions WHERE id_user='".$AppUI->user_id."' AND company='".$company."' AND ".$tab." <>'0' AND department<>'-1' ";
  	    $allow_dept =  db_loadHashList( $sql_particular );
  	    
  	    if (count($allow_dept)>0)
	  	{
	  		$andIn_dept = "AND dept_id IN (". implode( ',', $allow_dept ) .")";
	  	}else{
	  		$andIn_dept = "AND dept_id IN ('-1')";
	  	}
  	    
	  	// Me fijo si tiene permisos para todos los dept
	  	$sql_Allallow = "SELECT department FROM hhrr_permissions WHERE id_user='".$AppUI->user_id."' AND ".$tab."<>'0' AND (( company='-1' AND  department='-1') OR ( company='".$company."' AND  department='-1'))";
  	    $allow_Alldept =  db_loadHashList( $sql_Allallow );
  	    
  	    if (count($allow_Alldept)>0)
  	    {
  		// Traigo todos los departamentos que no esten denegados
  		$sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company='".$company."' AND dept_id NOT IN $andNotIn_dept order by dept_name";
  	    }else{
  	    $sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company='".$company."' AND dept_id NOT IN $andNotIn_dept $andIn_dept order by dept_name";
  	    }
  	}
  	
  }
 // echo $sql_Allallow;
  
  $list = db_loadHashList( $sql );
  
    if(count($list)>0 && $all_dept !="")
    {
    	$list = arrayMerge( array( '-1'=>$all_dept ), $list );
    }
  
	if (!$list)//Si la empresa no tiene ningun sub area asociada lo informo
		$list=array(0=>$AppUI->_("No data available"));
    if ($company == "")
        $list=array(-1=>$AppUI->_("Any"));
        
   
  $objResponse->addCreateOptions($selectId, $list,$selected);
  
  return $objResponse->getXML();
}


/**
 * Arma el combo de usuarios supervisores de acuerdo al id de la empresa ingresada
 *
 * @param String  $selectID = Nombre del campo select que contendra los usuarios
 * @param Integer $company = Id de la empresa de la que se traen los usuarios supervisores
 * @param Integer $selected = Id del usuario supervisor preseleccionado
 * @param String  $type = Tipo de usarios supervisores (reporte directo o Times & expenses)
 */
function combo_UserSupervisor($selectID, $company, $selected, $type)
{
	global $AppUI;
	
	if($type == "direct_report")
	{
	$sql =" SELECT DISTINCT user_id, concat(user_first_name,' ', user_last_name) 
	        FROM users 
	        WHERE user_type <> 5 
	        AND user_status = '0' ";
	
	if($_GET["a"] == "addedituser_admin" && !empty($_GET["user_id"]))
	{
		$sql .= "AND user_id <> $_GET[user_id] ";
	}
	
	if($company !="")
	{
	$sql .= " AND user_company = '".$company."'";
	}
	
	/*sql .=" AND user_id != '".$AppUI->user_id."' 
	        ORDER BY user_first_name";*/
	
	$sql .=" ORDER BY user_first_name";
	
	$list = db_loadHashList( $sql );
	
	//echo "<pre>$sql</pre>";
	
	$list = arrayMerge( array( -1=>$AppUI->_("Not Supervised")), $list );
	
	}else{
		
		$sql =" SELECT DISTINCT user_id, concat(user_first_name,' ', user_last_name)
	        FROM users 
	        WHERE user_type <> 5 
	        AND user_status = '0' ";
		
		if($_GET["a"] == "addedituser_admin" && !empty($_GET["user_id"]))
		{
			$sql .= "AND user_id <> $_GET[user_id] ";
		}
		
		if($company !="")
		{
		$sql .= " AND user_company = '".$company."'";
		}
		
	    /*$sql .= " AND user_id != '".$AppUI->user_id."' 
	        ORDER BY user_first_name";*/
	    
	    $sql .= "ORDER BY user_first_name";
	    
		$list = db_loadHashList( $sql );
		
		if (!$list){
		$list=array(0=>$AppUI->_("No data available"));
		}
	}
	
	$objResponse = new myXajaxResponse();
	
	$objResponse->addCreateOptions($selectID, $list,$selected);
    
    return $objResponse->getXML();
}

function changeTwitterMessage($user_id, $message)
{
	global $AppUI;

	$objResponse = new myXajaxResponse();
	
	$message = mb_convert_encoding($message, "ISO-8859-1", "UTF-8");

	$twitterData = getTwitterUserData($user_id);

	$AppUI->setState( 'twitter_message', $message );
	$AppUI->setState( 'twitter_save', true );

	if(count($twitterData) > 0)
		$sql = "UPDATE twitter SET twitter_message = '".$message."' WHERE twitter_user_id = ".$AppUI->user_id;
	else
		$sql = "INSERT INTO twitter (twitter_user_id, twitter_message) VALUES ( ".$AppUI->user_id.", '".$message."')";

	$ret = db_exec( $sql );
	
	$AppUI->setMsg($AppUI->_('Message saved'), UI_MSG_OK);
	$message = str_replace("'",'', $AppUI->getMsg());
	$message = str_replace('"','\"', $message);

	$objResponse->addScript("showGenericMessage('".$message."');");

	return $objResponse;
}

function changeTwitterStatus($user_id, $status)
{
	global $AppUI;

	$objResponse = new myXajaxResponse();

	$twitterData = getTwitterUserData($user_id);
	
	$AppUI->setState( 'twitter_status', ($status == 'true' ? 1 : 0));
	$AppUI->setState( 'twitter_save', true );	

	if(count($twitterData) > 0)
		$sql = "UPDATE twitter SET twitter_status = ".($status == 'true' ? 1 : 0)." WHERE twitter_user_id = ".$AppUI->user_id;
	else
		$sql = "INSERT INTO twitter (twitter_user_id, twitter_status) VALUES ( ".$AppUI->user_id.", ".($status == 'true' ? 1 : 0).")";

	$ret = db_exec( $sql );
	
	$AppUI->setMsg($AppUI->_('The state has been updated'), UI_MSG_OK);
	$message = str_replace("'",'', $AppUI->getMsg());
	$message = str_replace('"','\"', $message);
	

	$objResponse->addScript("showGenericMessage('".$message."');");

	return $objResponse;
}

function changeTwitterShowHide($show)
{
	global $AppUI;

	$objResponse = new myXajaxResponse();
	
	setcookie('TWITTER_VIEW',$show,time()+86400*365);

	return $objResponse;
}

function getTwitterPopUpData($user_id, $fullname, $picture, $positionX)
{
	global $AppUI;
	
	$fullname = mb_convert_encoding($fullname, "ISO-8859-1", "UTF-8");

	$objResponse = new myXajaxResponse();
	
	//Obtengo mi asignación
	$sql = "SELECT * FROM myassigments_active WHERE user_id = ".$user_id;
		
	$user_assigments = db_loadList($sql);
	
	//Si tengo una asignación, entro
	if(count($user_assigments) > 0)
	{
		$assigment_id = $user_assigments[0]['myassigment_id'];
		$assigment_type = $user_assigments[0]['myassigment_type'];
		$dateAssigment = new CDate($user_assigments[0]['myassigment_date']);
		
		//Dependiendo del tipo de asignacion, busco si tiene asociada una tarea.
		switch($assigment_type)
		{
			case 'ta':
				$sql = "SELECT ta.task_id, CONCAT('[tarea] ', ta.task_name) as name_assigment";
				$sql .= " FROM tasks ta";
				$sql .= " WHERE ta.task_id = ".$assigment_id;				
				break;
			case 't':
				$sql = "SELECT ta.task_id, CONCAT('[to-do] ', todo.description) as name_assigment";
				$sql .= " FROM myassigments_active mya";
				$sql .= " INNER JOIN project_todo todo ON todo.id_todo = mya.myassigment_id";
				$sql .= " LEFT JOIN tasks ta ON ta.task_id = todo.task_id";
				$sql .= " WHERE mya.user_id = ".$user_id;
				break;

			case 'b':
				$sql = "SELECT ta.task_id, CONCAT('[incidencia] ', bug.summary) as name_assigment";
				$sql .= " FROM myassigments_active mya";
				$sql .= " INNER JOIN btpsa_bug_table bug ON bug.id = mya.myassigment_id";
				$sql .= " LEFT JOIN tasks ta ON ta.task_id = bug.task_id";
				$sql .= " WHERE mya.user_id = ".$user_id;
				break;
		}
		
		if($sql != '')
		{
			$task = db_loadList($sql);
		
			//Guardo la descripción de la asignación
			$name_assigment = $task[0]['name_assigment'];
			
			if ($task[0]['task_id'] > 0)
				$task_id = $task[0]['task_id'];
		}
		
		if($task_id > 0)
		{
			//Busco TODOS relacionados a la tarea
			$sql = "SELECT todo.id_todo";
			$sql .= " FROM project_todo todo";
			$sql .= " WHERE todo.task_id = ".$task_id;
			
			$todos = db_loadColumn($sql);
			
			if(count($todos) == 0)
				$todos = array(0=>0);

			//Busco INCIDENCIAS relacionados a la tarea
			$sql = "SELECT bug.id";
			$sql .= " FROM btpsa_bug_table bug";
			$sql .= " WHERE bug.task_id = ".$task_id;
			
			$bugs = db_loadColumn($sql);
			
			if(count($bugs) == 0)
				$bugs = array(0=>0);
			
			//Busco asignaciones relacionadas a mi tarea
			$sql = "SELECT mya.user_id";
			$sql .= " FROM myassigments_active mya";
			$sql .= " WHERE ((mya.myassigment_id IN (".implode(',',$todos).") AND mya.myassigment_type = 't')";
			$sql .= " OR (mya.myassigment_id IN (".implode(',',$bugs).") AND mya.myassigment_type = 'b')";
			$sql .= " OR (mya.myassigment_id = ".$task_id." AND mya.myassigment_type = 'ta'))";
			$sql .= " AND (mya.user_id != ".$user_id.")";
		}
		else
		{
			//Si mi asignación no tiene tarea asociada, busco asignaciones que tengan el mismo id y tipo
			$sql = "SELECT mya.user_id";
			$sql .= " FROM myassigments_active mya";
			$sql .= " WHERE mya.myassigment_id = ".$assigment_id;
			$sql .= " AND mya.myassigment_type = '".$assigment_type."'";
			$sql .= " AND mya.user_id != ".$user_id;
		}

		$usersColaboration = db_loadColumn($sql);
		
		if (count($usersColaboration) > 0)
		{
			$usersFullName = CUser::getUsersFullName($usersColaboration);
			
			for($i=0;$i<count($usersFullName);$i++)
					$colaboration .= $usersFullName[$i]['fullname']." / ";
					
			if ($colaboration != '')
				$colaboration = substr($colaboration, 0, (strlen($colaboration)-3));
		}
	}
	
	//Obtengo los eventos del usuario
	$startDateEvent = new CDate(date("Ymd"));
	$endDateEvent = new CDate(date("Ymd"));
	$endDateEvent->addSeconds(60 * 60 * 24 * 1 - 1);
		
	$eventsTwitter = CEvent::getEventsForPeriod($startDateEvent, $endDateEvent, $user_id);
		
	//Si hay evento para el día entro.
	if(count($eventsTwitter) > 0)
	{	
		//Recorro los eventos
		for($i=0;$i<count($eventsTwitter);$i++)
		{
			//Cargo evento
			$eventTwitter = new CEvent();
			$eventTwitter->load($eventsTwitter[$i]['event_id']);
						
			//Si no es recursivo, saco la hora de inicio y fin de la fecha de inicio y fin
			if(!$eventTwitter->event_recurse_type)
			{
				$startTimeEvent = substr($eventTwitter->event_start_date, 11, 5);
				$endTimeEvent = substr($eventTwitter->event_end_date, 11, 5);
			}
			else//Si es recursivo, saco la hora de inicio y fin de la hora de inicio y fin de recursividad
			{
				$startTimeEvent = substr($eventTwitter->event_start_time, 0, 5);
				$endTimeEvent = substr($eventTwitter->event_end_time, 0, 5);
			}
			
			//Me fijo si la hora actual esta dentro del rango del evento
			if((date("Hi") >= str_replace(':', '', $startTimeEvent)) && (date("Hi") <= str_replace(':', '', $endTimeEvent))
				&& $eventTwitter->event_type != 4 && $eventTwitter->event_type != 5)
			{
				$typesEventsTwitter = dPgetSysVal( 'EventType' );
				
				if($eventTwitter->event_type == "3")
					$eventHtml = $AppUI->_('In')." [".$AppUI->_('event')."] ".$AppUI->_($typesEventsTwitter[$eventTwitter->event_type]);
				else
					$eventHtml = $AppUI->_('In')." [".$AppUI->_('event')."] ".$AppUI->_($typesEventsTwitter[$eventTwitter->event_type])." ".$AppUI->_('of')." ".$startTimeEvent." ".$AppUI->_('to')." ".$endTimeEvent;
					
			}
		}
	}
	
	$twitterUserData = getTwitterUserData($user_id);
	
	$email = CUser::getUserEmail($user_id);
	
	$html = "<div style=\"position:absolute; top:-07px; left:".$positionX."px; z-index:1;\"><img src=\"../../images/arrow-up.gif\" /></div>";

	$html .= "<table bgColor=\"white\" width=\"255\" border=\"1\" bordercolor=\"gray\" cellpadding=\"0\" cellspacing=\"0\">";
	$html .= "<tr><td>";

	$html .= "<table width=\"100%\" align=\"center\" border=\"0\">";
	
	if($name_assigment != '')
	{
		$html .= "<tr><td colspan=\"2\" style=\"font-size: 11px; color: gray;\"><b>".$AppUI->_('Working in')." ".$name_assigment.".</b></td></tr>";
		
		if($colaboration)
			$html .= "<tr><td colspan=\"2\" style=\"font-size: 11px; color: gray;\">".$AppUI->_('Collaborating with')." ".$colaboration."</td></tr>";
			
		$html .= "<tr><td colspan=\"2\" style=\"font-size: 11px; color: gray;\"><i>".$dateAssigment->format($AppUI->user_prefs['SHDATEFORMAT'].' '.$AppUI->user_prefs['TIMEFORMAT'])."</i></td></tr>";
	}
	else
		$html .= "<tr><td colspan=\"2\" style=\"font-size: 11px; color: gray;\">".$AppUI->_('The user does not have active assigments').".<br/></td></tr>";
		
	if($eventHtml != '')
		$html .= "<tr><td colspan=\"2\" style=\"font-size: 11px; color: gray;\">".$eventHtml.".</td></tr>";
	else
		$html .= "<tr><td colspan=\"2\" style=\"font-size: 11px; color: gray;\">".$AppUI->_('The user does not have events').".<br/></td></tr>";		
		
	$html .= "<tr><td colspan=\"2\"><hr/></td></tr>";
	$html .= "<tr>";
	$html .= "	<td valign=\"top\" width=\"1%\" rowspan=\"2\">";
	$html .= "		<img width=\"50\" height=\"50\" src=\"".$picture."\" />";
	$html .= "	</td>";
	$html .= "	<td style=\"font-size: 11px; color: #FF9900;\" align=\"left\" valign=\"top\">";
	$html .= "		<b>".$fullname."</b><br/>".$email."<br/>";
	$html .= "	</td>";
	$html .= "</tr>";
	$html .= "<tr>";	
	$html .= "	<td style=\"font-size: 11px; color: #FF9900;\" valign=\"top\">";
	$html .= "		<font style=\"font-size: 11px; color: gray;\"><i>".$twitterUserData[0]['twitter_message']."<i><br/><br/></font>";
	$html .= "	</td>";
	$html .= "</tr>";	
	$html .= "</table>";

	$html .= "</td></tr></table>";
	
	$objResponse->addAssign('hidden_htmlToolTip',"value", $html);
	$objResponse->addScript("showDialogTwitter();");

	return $objResponse;
}

function showFavoritesItems()
{
	global $AppUI;
	
	$objResponse = new myXajaxResponse();

	include_once('modules/public/itemToFavorite_functions.php');
	
	if (!$AppUI->getState('ItemsFavorites'))
		refreshFavoriteData();
		
	$favorites = $AppUI->getState('ItemsFavorites');
	
	$html ="<div style=\"border: 1px solid rgb(102, 102, 102);\">";
	
	$html .="<table style=\"background-color: #FF9900;\" width=\"300\">";
	
	$html .="	<tr height=\"15px\" align=\"right\">";
	$html .="		<td colspan=\"2\" nowrap=\"nowrap\" style=\"color: #FFFFFF;\" onclick=\"javascript:tooltipClose();\" style=\"cursor:pointer;\"><b>".$AppUI->_('Close')."</b></td>";
	$html .="	</tr>";	
	
	if(count($favorites) > 0)
	{
		for ($i=0;$i<count($favorites);$i++)
		{
			$favoriteLink = $favorites[$i]['item_link'];

			if($favorites[$i]['item_type'] == 7 || $favorites[$i]['item_type'] == 8)
				$favoriteLink = "javascript:popUp('".$favorites[$i]['item_link']."'); tooltipClose();";

			if($item_type_temp != $favorites[$i]['item_type'])
			{
				$html .="	<tr height=\"15px\">";
				$html .="		<td colspan=\"2\" nowrap=\"nowrap\" style=\"color: #FFFFFF;\"><b>".($i==0 ? '' : '<br/>').$AppUI->_(getFavoriteItemTypeLabel($favorites[$i]['item_type']))."</b></td>";
				$html .="	</tr>";
			}

			$html .="	<tr height=\"15px\">";
			$html .="		<td><img src=\"images/1x1.gif\" height=\"15\" width=\"3\"></td>";
			$html .="		<td nowrap=\"nowrap\"><a href=\"".$favoriteLink."\" class=\"specialwhite\">- ".$favorites[$i]['item_label']."</a></td>";
			$html .="	</tr>";		

			$item_type_temp = $favorites[$i]['item_type'];
		}
	}
	else
	{
		$html .="	<tr height=\"15px\">";
		$html .="		<td colspan=\"2\" nowrap=\"nowrap\" style=\"color: #FFFFFF;\">".$AppUI->_('No favorites')."</td>";
		$html .="	</tr>";
	}
	
	$html .="	<tr height=\"15px\" align=\"right\">";
	$html .="		<td colspan=\"2\" nowrap=\"nowrap\" style=\"color: #FFFFFF;\" onclick=\"javascript:tooltipClose();\" style=\"cursor:pointer;\"><b>".$AppUI->_('Close')."</b></td>";
	$html .="	</tr>";	
	
	$html .="</table>";
	$html .="</div>";

	$objResponse->addAssign('favorites_hidden',"value", $html);
	$objResponse->addScript("showFavoritesItems();");

	return $objResponse;		
	
}

function showLogin()
{
	global $AppUI;
	
	$objResponse = new myXajaxResponse();
	
	$html = "<script type=\"text/javascript\">";
	$html .= "function ismaxlength(obj){";
	$html .= "	var mlength = obj.getAttribute? parseInt(obj.getAttribute(\"maxlength\")) : \"\"";
	$html .= "	if (obj.getAttribute && obj.value.length>mlength)";
	$html .= "		obj.value=obj.value.substring(0,mlength)";
	$html .= "	}";
	$html .= "</script>";

	$html .= "<table border=\"0\" bgcolor=\"#E9E9E9\">";
	$html .= "	<tr>";
	$html .= "		<td colspan=\"2\">";
	$html .= "			".$AppUI->_('Username').": ";
	$html .= "			<input type=\"hidden\" name=\"hdnUsername\" value=\"".$AppUI->user_username."\">";
	$html .= "			<b>".$AppUI->user_username."</b>";
	$html .= "		</td>";
	$html .= "	</tr>";
	$html .= "	<tr>";
	$html .= "		<td colspan=\"2\">";
	$html .= "			".$AppUI->_('Password').": ";
	$html .= "			<input type=\"password\" class=\"text\" name=\"txtPassword\">";
	$html .= "		</td>";
	$html .= "	</tr>";
	$html .= "	<tr>";
	$html .= "		<td colspan=\"2\">";
	$html .= "			".strtolower($AppUI->_('Comment')).": ";
	$html .= "			<textarea type=\"text\" onkeyup=\"return ismaxlength(this)\" maxlength=\"100\" cols=\"30\" rows=\"5\" class=\"text\" name=\"txtComment\"></textarea>";
	$html .= "		</td>";
	$html .= "	</tr>";
	$html .= "	<tr>";
	$html .= "		<td align=\"left\">";
	$html .= "			<input type=\"button\" class=\"button\" value=\"".$AppUI->_('sign')."\" onclick=\"javascript:processLogin(document.getElementById('hdnUsername').value, document.getElementById('txtPassword').value, document.getElementById('txtComment').value);\">";
	$html .= "		</td>";
	$html .= "		<td align=\"right\">";
	$html .= "			<input type=\"button\" class=\"button\" value=\"".$AppUI->_('close')."\" onclick=\"javascript:tooltipClose();\">";
	$html .= "		</td>";	
	$html .= "	</tr>";	
	$html .= "</table>";
	
	$objResponse->addAssign('login_show_hidden',"value", $html);
	$objResponse->addScript("showLogin();");

	return $objResponse;	
}

function checkLogin($username,$password)
{
	global $AppUI;
	
	$objResponse = new myXajaxResponse();
	
	if($AppUI->login($username,$password, true, false))
		$objResponse->addScript("successLogin();");
	else
		$objResponse->addScript("failedLogin();");
		

	return $objResponse;
}

/**
 * Completa la asignación ingresada en AssignmentId
 *
 * @param Integer $AssignmentId = Id de la asignación a completar
 * 
 * @param Integer $AssignmentType = Tipo de asignación (ToDo, Bug, Task)
 * 
 * @return Empty
 */
function completeAssignment($AssignmentId, $AssignmentType, $url)
{
	global $AppUI;
	$objResponse = new myXajaxResponse();
	
	if( ($AssignmentType == "t") || ($AssignmentType == 4) || ($_GET["m"] == "todo"))
	{
		completeToDo($AssignmentId);
	}
	elseif ( ($AssignmentType == "ta") || ($AssignmentType == 3) || ($_GET["m"] == "tasks"))
	{
		completeTask($AssignmentId);
	}
	elseif ( ($AssignmentType== "b") || ($AssignmentType == 6) || ($_GET["m"] == "webtracking"))
	{
		resolveBug($AssignmentId);
	}
	
	$url = split("#",$url);
	$objResponse->addScript("redirectUrl('$url[0]')");
	
	return $objResponse;
}

/**
 * Completa el ToDo ingresado en idTodo
 *
 * @param Integer $idTodo = Id del ToDo a completar
 * 
 * @return Empty
 */
function completeToDo($idTodo)
{
	$sql = "UPDATE project_todo 
			SET status = '1'
			WHERE id_todo = $idTodo";
	db_Exec($sql);
}

/**
 * Completa la tarea ingresada en idTask
 *
 * @param Integer $idTask = Id de la Tarea a completar
 * 
 * @return Empty
 */
function completeTask($idTask)
{
	$sql = "UPDATE tasks
			SET task_complete = '1'
			WHERE task_id = $idTask";
	db_Exec($sql);
}

/**
 * Resuelve la incidencia ingresada en idBug
 *
 * @param Integer $idBug = Id de la Incidencia a resolver
 * 
 * @return Empty
 */
function resolveBug($idBug)
{
	global $AppUI;
	
	$sql = "UPDATE btpsa_bug_table
			SET status = 80, resolution = 20
			WHERE id = $idBug";
	db_Exec($sql);
	
	$sql = "INSERT INTO btpsa_bug_history_table 
				( user_id, bug_id, date_modified, field_name, old_value, new_value ) 
			VALUES ( '$AppUI->user_id', '$idBug', NOW(), 'status', '20', '80' )";
	db_Exec($sql);
}
?>