<?
require_once('./includes/xajax/xajax.inc.php');
require_once('./includes/permissions.php');

class myXajaxResponse extends xajaxResponse  {
  function addCreateOptions($sSelectId, $options,$selected) {
    $this->addScript("document.getElementById('".$sSelectId."').length=0");
    if (sizeof($options) >0) {
       foreach ($options as $k => $v) {
       	 $sel=($selected==$k)?"true":"false";
         $this->addScript("addOption('".$sSelectId."','".$k."','".$v."',".$sel.");");
       }
     }
  }
}
$xajax = new xajax();
//$xajax->debugOn();

// adds an option to the select
function addSubAreas($selectId, $area_parent,$selected="") {
	global $AppUI;
  $objResponse = new myXajaxResponse();

  $sql = "SELECT * from hhrr_functional_area WHERE area_parent='".$area_parent."'";
	$list = db_loadHashList( $sql );
	if (!$list)//Si la empresa no tiene ningun sub area asociada lo informo
		$list=array(0=>$AppUI->_("No data available"));

  $objResponse->addCreateOptions($selectId, $list,$selected);
  return $objResponse->getXML();
}

function addTitle($selectId, $level_title,$selected="") {
	global $AppUI;
    $objResponse = new myXajaxResponse();

    $sql = "SELECT title_id, name_es from hhrr_education_title WHERE level_id ='".$level_title."'";
	$list = db_loadHashList( $sql );
	
	//print_r($list);
	
	if (!$list)//Si el nivel academico no tiene ningun titulo asociado lo informo
		$list=array(0=>$AppUI->_("No data available"));

  $objResponse->addCreateOptions($selectId, $list,$selected);
  return $objResponse->getXML();
}

function addDepartments($selectId, $company,$selected="") {
  global $AppUI;
  $objResponse = new myXajaxResponse();

  $sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company='".$company."'";
  $list = db_loadHashList( $sql );
	
	if (!$list)//Si la empresa no tiene ningun sub area asociada lo informo
		$list=array(0=>$AppUI->_("No data available"));
    if ($company == "")
        $list=array(0=>$AppUI->_("Any"));

  $objResponse->addCreateOptions($selectId, $list,$selected);
  
  return $objResponse->getXML();
}

function hideDep($path="", $resource_type="")
{
	global $AppUI;
	$objResponse = new myXajaxResponse();

	$company=substr($path,0,strpos ($path,'_'));
	$ultima=substr($path,strrpos ($path,'_')+1);

	$objResponse->addEvent("img_".$path, "onClick","xajax_showDep('".$path."','$resource_type');");
	$objResponse->addAssign("img_".$path, "src", './images/icons/expand.gif');
	$objResponse->addAssign("img_".$path, "alt", $AppUI->_('Show'));
	$objResponse->addRemove("table_".$path);

  return $objResponse;
}

function showDep($path="", $resource_type="") {
	global $AppUI;
	$objResponse = new myXajaxResponse();

	/*
	Ejemplo de path: "22_1_2_3332323_88"
	El primer numero antes del _ representa el id de la compania, mientras que el ultimo es el del departamento en particular.
	Los intermedios son todos los sub deptos hasta llegar al ultimo.
	*/
	$company=substr($path,0,strpos ($path,'_'));
	$ultima=substr($path,strrpos ($path,'_')+1);
    
	$objResponse->addEvent("img_".$path, "onClick","xajax_hideDep('".$path."','$resource_type');");//Cambiamos la accion que ejecuta al hacer click
	$objResponse->addAssign("img_".$path, "src", './images/icons/collapse.gif');//Cambiamos la imagen
	$objResponse->addAssign("img_".$path, "alt",$AppUI->_('Hide'));//Cambiamos el alt

	$objResponse->addCreate("div_".$path, 'TABLE', "table_".$path);//Creamos la tabla en el div que esta para esto
	$objResponse->addAssign("table_".$path, 'width', "100%");
    $objResponse->addCreate("table_".$path, 'TBODY', "tbody_".$path);//Creamos el cuerpo de la tabla
    
	/*--------------------Traemos la lista de usuarios de ese depto----------------------------*/	
	
	if($resource_type == ""){
		$sql = "SELECT concat(u1.user_last_name ,', ', u1.user_first_name) name, u1.user_id, u1.user_job_title,
		u1.user_supervisor
		FROM users as u1
		WHERE u1.user_company = $company AND u1.user_department = $ultima AND user_type <> '5'
		ORDER BY name";
	}else{
		$sql = "SELECT job_name, job_id 
		FROM hhrr_jobs WHERE job_company = $company AND job_department = $ultima 
		ORDER BY job_name";
	}
	
	$users = db_loadList( $sql, NULL );
	//die(print_r($users));
	foreach ($users as $user)
	{
		if($resource_type==""){
			//$user_title = mb_convert_encoding(user_title( $user['user_id'] ), "ISO-8859-1", "UTF-8");
			$user_title = user_title( $user['user_id'] );
			//$user_name = mb_convert_encoding($user['name'], "ISO-8859-1", "UTF-8");
			$user_name = $user['name'];
			$user_job_title = $user['user_job_title'];
			$user_id = $user['user_id'];
		}else{
			//$user_name = mb_convert_encoding($user['job_name'], "ISO-8859-1", "UTF-8");
			$user_name = $user['job_name'];
			$user_id = $user['job_id'];
		}
		
		$objResponse->addCreate("tbody_".$path, 'TR', "tr3_".$user_id);//Cremos una fila
		$objResponse->addCreate("tr3_".$user_id, 'td', "td3_".$user_id);//Creamos una columna
		
		$objResponse->addAssign("td3_".$user_id, 'colSpan', "2");
		$objResponse->addAssign("td3_".$user_id, 'width', "1%");
        
		if ($user['user_supervisor']!="-1" && $resource_type == ""){
			
		    $sql_supervisor = "SELECT concat(u2.user_first_name,' ', u2.user_last_name ) user_supervisor_name
			FROM users as u2
			WHERE u2.user_id='".$user['user_supervisor']."'
			";
		    
		    $user_s = db_loadColumn( $sql_supervisor, NULL );
		    
		    //$user_supervisor = mb_convert_encoding($user_s[0], "ISO-8859-1", "UTF-8");
		    $user_supervisor = $user_s[0];
		}else{
			$user_supervisor = "";
		}
		
		$contenido="
		<table width='100%' style='color:#3366cc'>
			<tr>";
			if($resource_type==""){
			$contenido .= "	<td>
					<a href='?m=hhrr&a=viewhhrr&id=".$user_id."' target='_new' style='text-decoration:none; color:#3366cc;' onmouseover=\"this.style.textDecoration='underline'\" onmouseout=\"this.style.textDecoration='none'\">".$user_name."</a>
				</td>
				<td width='200'>
					".$user_job_title."
				</td>
				<td width='100'>
					".$user_title."
				</td>
				<td width='140'>
					".$user_supervisor;
			}else{
				$contenido .= "	<td align='left' colspan=4 >";
				if(!getDenyEdit( "hhrr" ) && !getDenyEdit( "companies" )){
				$contenido .= "
					<a href='?m=hhrr&a=addeditrole&id=$user_id'><img src='./images/icons/edit_small.gif' alt='".$AppUI->_("Edit")."' border='0'></a>
					<a href='#' onclick='delRole($user_id);'><img src='./images/icons/trash_small.gif' alt='".$AppUI->_("delete")."' border='0'></a>
					&nbsp;";
				}
				$contenido .= "<a href='?m=hhrr&a=viewrole&id=$user_id' style='text-decoration:none; color:#3366cc;' onmouseover=\"this.style.textDecoration='underline'\" onmouseout=\"this.style.textDecoration='none'\">".$user_name."</a>";
			}
			$contenido .= "
				</td>
			</tr>
			<tr class='tableRowLineCell'><td colspan='4'></td></tr>
		</table>
		";
		$objResponse->addAssign("td3_".$user_id, 'innerHTML', $contenido );//Agregamos el nombre del usuario
	}
	/*--------------------------------------------------------------------------------------------------*/


	/*--------------------Traemos la lista de SUB Departamentos de ese depto----------------------------*/

	//Si devolvio -1 significa que tiene permiso para todos los deptos de la empresa.
	//Sino devuelve un string con el formato "12, 55, 34"
	$department=deptos_habilitadas_hhrr_str($company);
	if ($department == -1)
		$sql="SELECT dept_id, dept_name FROM departments WHERE dept_company=$company AND dept_parent=$ultima ORDER BY dept_name";
	else
		$sql="SELECT dept_id, dept_name FROM departments WHERE dept_company=$company AND dept_parent=$ultima AND dept_id IN ('$department') ORDER BY dept_name";
		

	$rows = db_loadList( $sql, NULL );
	foreach ($rows as $row)
	{
		$path2=$path."_".$row['dept_id'];//Le agregamos al path el id de este departamento

		$objResponse->addCreate("tbody_".$path, 'TR', "tr_".$path2);//Cremos una fila

		$objResponse->addCreate("tr_".$path2, 'td', "td_".$path2);//Creamos una columna
		$objResponse->addAppend("td_".$path2, 'width', '1%');//Le ponemos el minimo de anchpo
		$contenido="<img id='img_".$path2."' onClick=\"xajax_showDep('$path2','$resource_type')\" src='./images/icons/expand.gif' width='16' height='16' border='0' alt='".$AppUI->_('Show')."'>";
		$objResponse->addAssign("td_".$path2, 'innerHTML', $contenido);//Agregamos el contenido de la columna

		$objResponse->addCreate("tr_".$path2, 'td', "td2_".$path2);//Creamos una columna
		$objResponse->addAppend ("td2_".$path2, 'width', '100%');
		$contenido="
		<a href='?m=hhrr&tab=1&company_id=".$company."&dept_id=".$row['dept_id']."' target='_new' style='text-decoration:none; color:#342D7E;' onmouseover=\"this.style.textDecoration='underline'\" onmouseout=\"this.style.textDecoration='none'\">
			<i>
			".$row['dept_name']."
			</i>
		</a>
		";
		$objResponse->addAssign("td2_".$path2, 'innerHTML', $contenido );//Agregamos el nombre del usuario. utf8_encode Codifica una cadena ISO_8859_1 a UTF_8
		$objResponse->addCreate("tbody_".$path, 'TR', "tr2_".$path2);//Cremos una fila
		$objResponse->addCreate("tr2_".$path2, 'td', "td3_".$path2); //Creamos una columna
		$objResponse->addAssign("td3_".$path2, 'width', '1%');

		$objResponse->addCreate("tr2_".$path2, 'td', "td4_".$path2);//Creamos una columna
		$objResponse->addAssign("td4_".$path2, 'width', '100%');
		$objResponse->addCreate("td4_".$path2, 'div', "div_".$path2);//Agregamos el div para los hijos de este depto
	}

	/*--------------------------------------------------------------------------------------------------*/

	//Si no ni sub deptos ni usuario imprimo el msg
	if (count($rows) == 0 && count($users) == 0)
	{
		$cant_u = count($users);
		$aleatorio=rand(); //Creo un numero aleatorio

		$objResponse->addCreate("tbody_".$path, 'TR', "tr_".$aleatorio);//Cremos una fila
		$objResponse->addCreate("tr_".$aleatorio, 'td', "td_".$aleatorio);//Creamos una columna
		$objResponse->addAssign("td_".$aleatorio, 'innerHTML', $AppUI->_("No data available" ) );//Agregamos el contenido de la columna
	}

  return $objResponse;
}

function hideSection($section)
{
	global $AppUI;
	$objResponse = new myXajaxResponse();

	$objResponse->addAssign("table_$section", "style.visibility", 'hidden');//Oculto la tabla
	$objResponse->addAssign("table_$section", "style.position", 'absolute');//Con esto hago que no ocupe lugar
	$objResponse->addEvent("img_$section", "onClick","xajax_showSection('$section');");
	$objResponse->addAssign("img_$section", "src", './images/icons/expand.gif');
	$objResponse->addAssign("img_$section", "alt", $AppUI->_('Show'));

	$_SESSION['vec_sections'][$section] = 1;

	return $objResponse;
}

function showSection($section)
{
	global $AppUI;
	$objResponse = new myXajaxResponse();

	$objResponse->addAssign("table_$section", "style.visibility", 'visible');//Muesto la tabla
	$objResponse->addAssign("table_$section", "style.position", 'static');//Que ocupe el tamaño original
	$objResponse->addEvent("img_$section", "onClick","xajax_hideSection('$section');");
	$objResponse->addAssign("img_$section", "src", './images/icons/collapse.gif');
	$objResponse->addAssign("img_$section", "alt", $AppUI->_('Hide'));

	$_SESSION['vec_sections'][$section] = 0;

	return $objResponse;
}

function generar_pdf($id)
{
	include('summary_pdf.php');//Este archivo contiene la funcion generar_pdf_resumen()
	global $AppUI;
	$objResponse = new myXajaxResponse();

	/*
	La funcion generar_pdf_resumen($id) regresa el path del archivo de donde guardo fisicamente el pdf.
	Lo que hacemos es redirigir al usuario a esa ubicacion para que se lo muestre x pantalla.
	*/

	$objResponse->addRedirect("fileviewer.php?file_type=pdf_report&file_name=".generar_pdf_resumen($id));//Con esto le aparece el dialogo de descarga del explorador al usuario
	//$objResponse->addScriptCall("window.open", generar_pdf_resumen($id), "", "scrollbars=auto,resizable=yes,maximized=yes");//Con esta le abrimos un popUp maximizado
	//$objResponse->addScriptCall("window.open", generar_pdf_resumen($id));//Con esta le abrimos en una nueva ventana
	//$objResponse->addAlert("msg: "."fileviewer.php?/file_type=pdf_report&file_name=".generar_pdf_resumen($id));

	return $objResponse;
}

function myPreFunction()
{
	global $AppUI;

	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("div_xajax_debug", "innerHTML", $AppUI->getMsg());
	return $objResponse;
}

function changeCompany($company_id, $department_field, $report_to_field, $sel_dep, $sel_rep){
	global $AppUI;
	
	if ($company_id > 0)
	{
		$sql = "SELECT dept_id, dept_name FROM departments WHERE dept_company = $company_id
				AND dept_name <> ''
				ORDER BY dept_name";
		$departments = db_loadHashList($sql);
		if(count($departments) == 0){
			$canBlankDepartments = 1;
		}
		$departments["0"] = $AppUI->_("none");
		
		$sql = "SELECT job_id, job_name AS name FROM hhrr_jobs
				WHERE job_company = $company_id
				ORDER BY job_name";
		$users = db_loadHashList($sql);
		
		$users["0"] = $AppUI->_("none");
		
		$objResponse = new myXajaxResponse();
    	$objResponse->addCreateOptions($department_field, $departments, $sel_dep);
    	$objResponse->addCreateOptions($report_to_field, $users , $sel_rep);
		
    	return $objResponse->getXML();
	}
}

function changeDepartment($company, $department, $fieldDest){
	global $AppUI;
	
	if ( $department > 0 )
	{
		$sql = "SELECT job_id, job_name AS name FROM hhrr_jobs
				WHERE job_company = $company
				AND job_department = $department
				ORDER BY job_name";
	}else{
		$sql = "SELECT job_id, job_name AS name FROM hhrr_jobs
				WHERE job_company = $company
				ORDER BY job_name";
	}
	
	$users = db_loadHashList($sql);
	
	$users["0"] = $AppUI->_("none");
	
	$objResponse = new myXajaxResponse();
	$objResponse->addCreateOptions($fieldDest, $users , '');
	
	return $objResponse->getXML();
}

function saveEvaluation($divMSG){
	global $AppUI;
	$objResponse = new myXajaxResponse();
	
	//die("<pre>".print_r($AppUI->evaluation)."</pre>");
	
	$sql = "INSERT INTO hhrr_skills_evaluations (
			evaluation_user, evaluated_user, comparing_job, evaluation_date, evaluation_comments)
			VALUES( '"
			.$AppUI->evaluation["evaluation_user"]."', '"
			.$AppUI->evaluation["evaluating_user"]."', '"
			.$AppUI->evaluation["comparing_job"]."', '"
			.$AppUI->evaluation["evaluation_date"]."', '' )";
	mysql_query($sql);
	if(mysql_error()){
		$objResponse->addAssign($divMSG,"innerHTML", $sql.$html);
		unset ($AppUI->evaluation);
		return $objResponse;
	}else{
		$id = mysql_insert_id();
		for($i=0; $i<count($AppUI->evaluation["items"]);$i++){
			if(!empty($AppUI->evaluation["items"][$i]["item_name"]) && !empty($AppUI->evaluation["items"][$i]["item_group"])){
				$sql = "INSERT INTO hhrr_skills_evaluations_items (
						        evaluation_id,
						        item_name,
						        item_group,
						        autoevaluated_value,
						        perceived_value,
						        job_required_value,
						        last_use,
						        experience,
						        user_comments
						 ) VALUES (
								  $id,
						          '".$AppUI->evaluation["items"][$i]["item_name"]."',
						          '".$AppUI->evaluation["items"][$i]["item_group"]."',
						          '".$AppUI->evaluation["items"][$i]["autoevaluated_value"]."',
						          '".$AppUI->evaluation["items"][$i]["perceived_value"]."',
						          '".$AppUI->evaluation["items"][$i]["job_required_value"]."',
						          '".$AppUI->evaluation["items"][$i]["last_use"]."',
						          '".$AppUI->evaluation["items"][$i]["experience"]."',
						          '".$AppUI->evaluation["items"][$i]["user_comments"]."'
						 )";
				
				mysql_query($sql);
				if(mysql_error()){
					$objResponse->addAssign($divMSG,"innerHTML", mysql_error().$html);
					unset ($AppUI->evaluation);
					return $objResponse;
				}
			}
		}
		$html = "<img src='./images/icons/stock_ok-16.png' border='0'>";
		$msg = $AppUI->_("Evaluation saved correctly")."!";
		$objResponse->addAssign($divMSG, "innerHTML", "<br><center>$html".$AppUI->_("Evaluation saved correctly")."</center>");
		
		unset ($AppUI->evaluation);
	}
	return $objResponse;
}

/*********Se usa en Summary*******************/
$xajax->registerFunction("hideSection");
$xajax->registerFunction("showSection");
$xajax->registerFunction("generar_pdf");
/*********************************************/

$xajax->registerFunction("showDep");
$xajax->registerFunction("saveEvaluation");
$xajax->registerFunction("hideDep");
$xajax->registerFunction("addSubAreas");
$xajax->registerFunction("addTitle");
$xajax->registerFunction("changeCompany");
$xajax->registerFunction("changeDepartment");

//$xajax->registerPreFunction("myPreFunction");//Con esta instruccion hago que SIEMPRE se ejecute la funcion que le estoy indicando antes de procesar todo el Requests

// Incluyo Funciones Empresa/Canal/Proyecto
include("./modules/public/ajax.php");

$xajax->processRequests();

$xajax->printJavascript('../includes/xajax/');
//$xajax->errorHandlerOn();
//$xajax->setLogFile("xajax_error_log.log");

?>
<script type="text/javascript">
  function addOption(selectId, val, txt, sel) {
    var objOption = new Option(txt, val,false,sel);
     document.getElementById(selectId).options.add(objOption);
   }
</script>