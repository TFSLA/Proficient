<? 
$slrrelations = array(
	"0" => "Any",
	"<=" => "<=",
	"=" => "=",
	">=" => ">="
	);


$cphrelations = array(
	"0" => "Any",
	"<=" => "<=",
	"=" => "=",
	">=" => ">="
	);

//Devuelve un string con los ids de las empresas sobre las que se tiene permisos separados por,
//EJ "1,2,44,22"
function empresas_habilitadas_hhrr_str()
{
	global $AppUI;
	//Que por lo menos tenga permiso para alguna solapa
	$sql="SELECT company FROM hhrr_permissions 
	WHERE id_user=".$AppUI->user_id.
	" AND (personal IN(-1,1) OR matrix IN(-1,1) OR work_experience IN(-1,1) OR education IN(-1,1) OR performance_management IN(-1,1) OR compensations IN(-1,1) OR development IN(-1,1))";
	$array=db_loadColumn($sql);
	
	//Si es admin o tiene permisos para todas las empresas, devuelvo -1.
	//Esto significa que tiene permisos para todas las empresas
	if ( in_array(-1,$array) OR $AppUI->user_type == 1 )
		return -1;
		
	return implode(",", $array);
}

function empresas_habilitadas_hhrr()
{
	global $AppUI;
	
	//Que por lo menos tenga permiso para alguna solapa
	$sql="
	SELECT p.company, c.company_name 
	FROM hhrr_permissions AS p 
	LEFT JOIN companies AS c 
	ON p.company = c.company_id 
	WHERE id_user=".$AppUI->user_id."
	AND (personal IN(-1,1) OR matrix IN(-1,1) OR work_experience IN(-1,1) OR education IN(-1,1) OR performance_management IN(-1,1) OR compensations IN(-1,1) OR development IN(-1,1))";
	$array=db_loadHashList($sql);
	
	//Si es admin o tiene permisos para todas las empresas(company==-1), devuelvo todas las empresas
	if ( array_key_exists(-1,$array) OR $AppUI->user_type == 1 )
	{
		$sql="SELECT company_id,company_name FROM companies ORDER BY company_name;";
		$array=db_loadHashList($sql);
	}
	
	return $array;
}



//Devuelve un string con los ids de los departamentos sobre las que se tiene permisos separados por,
//EJ "1,2,44,22"
function deptos_habilitadas_hhrr_str($company)
{
	global $AppUI;
	//Que por lo menos tenga permiso para alguna solapa
	$sql="SELECT department FROM hhrr_permissions 
	WHERE id_user=".$AppUI->user_id." AND (company='$company' OR company=-1)".
	" AND (personal IN(-1,1) OR matrix IN(-1,1) OR work_experience IN(-1,1) OR education IN(-1,1) OR performance_management IN(-1,1) OR compensations IN(-1,1) OR development IN(-1,1))";
	$array=db_loadColumn($sql);
	
	//print_r($array);
	
	//Si es admin o tiene permisos para todas las empresas, devuelvo -1.
	//Esto significa que tiene permisos para todas las empresas
	if ( in_array(-1,$array) OR $AppUI->user_type == 1)
		return -1;
	
	return implode(",", $array);
}

//Devuelve una lista con los departamento sobre las que se tiene permisos
function deptos_habilitados_hhrr($company)
{
	global $AppUI;

	$sql="
	SELECT department,dept_name FROM hhrr_permissions
	LEFT JOIN departments ON hhrr_permissions.department = departments.dept_id
	WHERE id_user=".$AppUI->user_id." AND (company = '$company' OR company=-1);";

	$array=db_loadHashList($sql);
	
	//Si es admin o tiene permisos para todos los deptos(company==-1), devuelvo todos los deptos 
	if ( array_key_exists(-1,$array) OR $AppUI->user_type == 1 )
	{
		$sql="SELECT dept_id, dept_name,dept_parent FROM departments WHERE dept_company='$company'";
		$array=db_loadHashList($sql);
		//$array = arrayMerge( array( '-1'=>array( 0, $AppUI->_( 'Departments (All)' ), -1 ) ), db_loadHashList( $sql, 'dept_id' ));
	}
	
	return $array;
}


function validar_permisos_hhrr($user,$solapa,$accion)
{
	global $AppUI;
  
  if ($AppUI->user_type == 1)
		return TRUE;
   
	if ($user == 0 AND $solapa=='personal' ){
		return TRUE;
	}
    
	if ($user == $AppUI->user_id AND ($solapa=='personal' OR $solapa=='matrix' OR $solapa=='matrix' OR $solapa=='work_experience' OR $solapa=='education') ){
		return TRUE;
	}

	if ($user == 0 AND $solapa!='personal' ){
		return FALSE;
	}


	//Traemos los datos del usuario que se quiere editar
	$query="SELECT user_type, user_company, user_department FROM users WHERE user_id=$user;";
	//echo $query;
	$sql = mysql_query($query);
	$user_data = mysql_fetch_array($sql);
	$user_company = $user_data['user_company'];
	$user_department = $user_data['user_department'];
	/*
	echo "<pre>";
	print_r($user_data);
	echo "</pre>";
	*/
	
	//Verificamos si el usuario que se quiere editar en un candidato, si lo es regresa OK (Los recursos humanos tipo Candidato estarán disponibles para todos los usuarios con acceso al módulo)
	if ($user_data['user_type'] == 5)
	{
		//A los candidatos siempre le ocultamos estas solapas:
		if ($solapa=='compensations' OR $solapa=='performance_management' OR $solapa=='development')
			return FALSE;
		else
			return TRUE;
	}
			

	//Si quiere editar primero nos fijamos si tiene permisos de LEctura Escritura sobre el modulo.
	if ( $accion == -1 AND getDenyEdit("hhrr") )
		return FALSE;
		
	/*
	Si no es un candidato tenemos que verificar que tenga permisos sobre la empresa del usuario( o todas), el departamento del usuario ( si el usuario no tiene asignado ningún depto debe tener acceso a TODOS los deptos de esa empresa);
	A la solapa que quiere acceder y para hacer la acción que solicita (ver o editar)
	*/
	
	//Si pide permisos para VER el tipo de permisos puede ser 1(ver) o -1 (ver y crear)
	if ($accion == 1)
		$condicion = "$solapa=-1 OR $solapa=1";
	else
		$condicion = "$solapa=-1";
	
	$query="SELECT id FROM hhrr_permissions 
	WHERE id_user=".$AppUI->user_id."
	AND ($condicion)
	AND ( company = -1 OR (company=$user_company AND department=-1 ) OR (company=$user_company AND department=$user_department))";
	//echo "<br>".$query;
	
	if ( mysql_num_rows( mysql_query($query) ) > 0)
		return TRUE;
	else
		return FALSE;

}

//Traemos el titulo mas alto del flaco
function user_title($user_id)
{
	global $AppUI;
	
  if ($AppUI->user_locale == 'es')
		$name = 'name_es';
	else
		$name = 'name_en';
	
	$sql="
	SELECT a.".$name." FROM hhrr_education e
	JOIN hhrr_academic_level a ON e.level=a.id
	WHERE id_user= $user_id AND e.status = '1' AND e.type = '0'
	ORDER BY a.level DESC
	LIMIT 1;
	";
	return db_loadResult( $sql );
}
?>