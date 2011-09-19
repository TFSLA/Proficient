<?php
session_start();
require_once("./includes/xajax/xajax.inc.php");
require_once("./classes/projects.class.php");
require_once( $AppUI->getModuleClass( 'articles' ));

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

include("./modules/public/ajax.php");

$xajax->registerFunction("addnotes");
$xajax->registerFunction("clear");
$xajax->registerFunction("notes");
$xajax->registerFunction("edit");
$xajax->registerFunction("delnote");
$xajax->registerFunction("addCompany");
$xajax->registerFunction("delCompany");
$xajax->registerFunction("addProyect");
$xajax->registerFunction("delProyect");
$xajax->registerFunction("setComboTasks");
$xajax->registerFunction("setComboSections");

function setComboTasks($project_id, $field){	
	global $AppUI;
	if($project_id > 0){
		$Cproject = new CProjects();
		$Cproject->loadTasks($project_id);
		$tasks = $Cproject->Tasks();
		
		$vecTasks = array(0=>$AppUI->_("Tasks (None)"));
		for($i=0; $i<count($tasks); $i++){
			if($tasks[$i]["project_id"] == $project_id)
				$vecTasks[$tasks[$i]["task_id"]] = $tasks[$i]["task_name"];
		}
	}else{
		$vecTasks = array(0=>$AppUI->_("Tasks (None)"));
	}
	
	$objResponse = new myXajaxResponse();
	$objResponse->addCreateOptions($field, $vecTasks , '');
	
	return $objResponse->getXML();
}

function setComboSections($project_id, $section_id, $file_user_id, $field){
	global $AppUI;

	$arrSections = CSection::getComboData($project_id, $section_id, $file_user_id);
	$disabled_section = $arrSections[0];
	$selected = $arrSections[1];
	$sections = (array)($arrSections[2]);
	
	$objResponse = new myXajaxResponse();
	$objResponse->addCreateOptions($field, $sections, $selected);
	
	$objResponse->addScript("document.getElementById('".$field."').disabled = ".($disabled_section == '' ? 'false' : 'true'));
	
	return $objResponse->getXML();
}

function addnotes($rows, $item, $text, $comment_id, $type){
	$item=checkpost($item);
	$rows=checkpost($rows);
	$text=checkpost($text);
	if ($comment_id==0) $sql="INSERT INTO know_base_note (user_id, know_base_note, know_base_type, know_base_item_id) VALUES (".$_SESSION['AppUI']->user_id.", \"$text\", \"$type\",\"$item\");";
	else $sql="UPDATE know_base_note SET know_base_note=\"$text\" WHERE know_base_note_id='$comment_id'";
	if (db_exec($sql)) {
		$entre='entre1';
		if ($comment_id==0){
			$entre='entre2';
			if ($type==1) $sql2="UPDATE files SET file_comments=file_comments+1 WHERE file_id=$item";
			if ($type==2) $sql2="UPDATE articles SET article_comments=article_comments+1 WHERE article_id=$item";
		}
		db_exec($sql2);
		//echo "($rows, $item, $text, $comment_id, $type)";
		include ('./includes/kbnotes.php');
		//$log='<table><tr><td>'.$sql.'</td></tr><tr><td>'.$sql2.'</td></tr></table>';
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $notes);
		return $objResponse;
	}
	else {
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $sql);
		return $objResponse;
	}
}

function delnote($rows, $item, $comment_id, $type){
	$sql="DELETE FROM know_base_note WHERE know_base_note_id='$comment_id'";
	if (db_exec($sql)) {
		$sql="SELECT COUNT(know_base_note_id) AS citems FROM know_base_note WHERE know_base_type=$type AND know_base_item_id=$item";
		$vec=db_fetch_array(db_exec($sql));
		if ($type==1) $sql="UPDATE files SET file_comments=".$vec['citems']." WHERE file_id=$item";
		if ($type==2) $sql="UPDATE articles SET article_comments=".$vec['citems']." WHERE article_id=$item";		//echo "<br>$sql<br>";
		db_exec($sql);
		include ('./includes/kbnotes.php');
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $notes);
		return $objResponse;
	}
	else {
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $sql);
		return $objResponse;
	}
}

/**
 * Cuando se selecciona una empresa pasa al combo de empresas activas y completa los proyectos pertenecientes a esta empresa en el combo de proyectos inactivos
 *
 * @param $origen = nombre del campo de origen, del se se borrara la empresa
 * @param $destino = nombre del campo en el que se agregaran los registros.
 * @param $f_proyect = nombre del campo que contiene los proyectos de destino
 * @param $id_company = id de la empresa a sumar en los combos.
 * 
 */
function addCompany($origen, $destino, $f_proyect , $id_company )
{
  global $AppUI;
  
  if($id_company >0){
  	
	  if(!isset($AppUI->companies_d[$id_company])){
	  $AppUI->companies_d = arrayMerge( array( $id_company=>$AppUI->companies_o[$id_company] ), $AppUI->companies_d );
	  
	  // Traigo los proyectos de esa empresa
  	 $sql_p = "SELECT project_id, project_name FROM projects WHERE project_company = '".$id_company."' ";
  	 $list_p = db_loadHashList( $sql_p );
  	
  	 $id_p = -1 * $id_company;
  	 $id_name = "== ".$AppUI->companies_d[$id_company]." ==";
  	
  	 $AppUI->project_o = arrayMerge( $list_p, $AppUI->project_o );
  	 $AppUI->project_o = arrayMerge( array( $id_p =>$id_name ), $AppUI->project_o );
	  
	  }
  
  }
  
  unset($AppUI->companies_d['-1']);
  
  if(count($AppUI->companies_d)>0)
  {
  asort($AppUI->companies_d);
  }
  
  $objResponse = new myXajaxResponse();
  $objResponse->addCreateOptions($origen, $AppUI->companies_o , '');
  $objResponse->addCreateOptions($destino, $AppUI->companies_d , '');
  $objResponse->addCreateOptions($f_proyect, $AppUI->project_o,'');
  
  return $objResponse->getXML();
}

/**
 * Al sacar una empresa del combo de empresas activas, saca el proyecto de los dos combos de proyecto y la agrega en el combo de empresas inactivas
 *
 * @param unknown_type $origen = nombre del campo del que voy a sacar la empresa
 * @param unknown_type $destino = nombre del campo donde voy a poner la empresa
 * @param unknown_type $f_proyect = nombre del campo de origen de proyectos
 * @param unknown_type $d_proyect = nombre del campo de destino de proyectos
 * @param unknown_type $id_company = id de la empresa que se va a borrar de los combos
 */

function delCompany($origen, $destino, $f_proyect , $d_proyect , $id_company  )
{
	global $AppUI;
	
	if ($id_company > 0)
	{
	  unset($AppUI->companies_o['0']);
	   // Agrego la empresa en el combo de empresas inactivas
	   //$AppUI->companies_o = arrayMerge( array( $id_company=>$AppUI->companies_d[$id_company] ), $AppUI->companies_o );
	   $AppUI->companies_o = arrayMerge( array( '0'=>'' ), $AppUI->companies_o );
	  
	  // Saco la empresa del combo de empresas activas
	  unset($AppUI->companies_d[$id_company]);
	  
	  // Saco todos los proyectos pertenecientes a esta empresa del combo de proyectos 
	  $id_all = -1 * $id_company;
	  unset($AppUI->project_o[$id_all]);
	  unset($AppUI->project_d[$id_all]);
	  
	  $sql_ip = "SELECT project_id FROM projects WHERE project_company='".$id_company."' ";
	  $colum = db_loadColumn($sql_ip);
	  
	  foreach ($colum as $proj)
	  {
	  	 unset($AppUI->project_o[$proj]);
	  	 unset($AppUI->project_d[$proj]);
	  }
	  
	  $objResponse = new myXajaxResponse();
  
	  $objResponse->addCreateOptions($origen, $AppUI->companies_d,'');
	  $objResponse->addCreateOptions($destino, $AppUI->companies_o , '');
	  $objResponse->addCreateOptions($f_proyect, $AppUI->project_o,'');
	  $objResponse->addCreateOptions($d_proyect, $AppUI->project_d,'');
	  
	  return $objResponse->getXML();
	  
	}
}

/**
 * Saca un proyecto del combo activo, y lo pongo en el combo de proyectos activos
 *
 * @param unknown_type $origen = Campo en donde saco el proyecto
 * @param unknown_type $destino = Campo en donde pongo el proyecto
 * @param unknown_type $id_project = Id del proyecto que voy a mover
 */
function addProyect($origen, $destino, $id_project)
{
	global $AppUI;
	
	if ($id_project > 0)
	{
	    
		// traigo el id de la cia del proyecto ingresado
		$sql_p = "SELECT project_company FROM projects WHERE project_id = '".$id_project."' ";
		$list_p = db_loadColumn( $sql_p );
		
		// Me fijo si en el vector destino existe un registro con la cia negativa
		foreach ($list_p as $row)
		{
			$id_cia = -1 * $row;
			
			if(!isset($AppUI->project_d[$id_cia]))
			{
			   $AppUI->project_d = arrayMerge( array( $id_project=>$AppUI->project_o[$id_project] ), $AppUI->project_d );
			}
		}
	}
	
	if ($id_project < 0)
	{   
	   $id_cia = -1 * $id_project;
	
	   // Traigo todos los proyectos de la empresa y los sumo en el vector de proyecto activos
	   $sql_p = "SELECT project_id FROM projects WHERE project_company = '".$id_cia."' ";
	   $list_p = db_loadColumn( $sql_p );
	   
	   $AppUI->project_d = arrayMerge( array( $id_project=>$AppUI->project_o[$id_project] ), $AppUI->project_d );
	   
	   foreach ($list_p as $row)
		{
			$id_cia = -1 * $row;
			unset($AppUI->project_d[$row]);
		
		}
	    
	   
	}
	
	if(count($AppUI->project_d)>0){
	asort($AppUI->project_d);
	}
	
	$objResponse = new myXajaxResponse();
  
    $objResponse->addCreateOptions($origen, $AppUI->project_o,'');
    $objResponse->addCreateOptions($destino, $AppUI->project_d , '');
  
    return $objResponse->getXML();
	
	
}

/**
 * Saca un proyecto del combo de proyectos activos y lo pone en el combo de proyectos inactivos
 *
 * @param unknown_type $origen = nombre del campo de donde saco el proyecto
 * @param unknown_type $destino = nombre del campo a donde pongo el proyecto
 * @param unknown_type $id_project = id del proyecto que voy a mover
 */
function delProyect($origen, $destino, $id_project)
{
	global $AppUI;
	
	if ($id_project != 0)
	{   
		
		unset($AppUI->project_d[$id_project]);
	}
	
	$objResponse = new myXajaxResponse();
  
    $objResponse->addCreateOptions($origen, $AppUI->project_d , '');
  
    return $objResponse->getXML();
	
}



function notes($rows, $item, $type){
	include ('./includes/kbnotes.php');
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign($rows,"innerHTML", $notes);
	return $objResponse;
}

function clear($rows){
	$clear='';
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign($rows,"innerHTML", $clear);
	return $objResponse;
}

function edit($rows, $item, $comment_id, $type){
	IF ($comment_id>1){
		$sql="SELECT know_base_note, know_base_note_id FROM know_base_note WHERE know_base_note_id='$comment_id'";
		$rc=db_exec($sql);
		$vec=db_fetch_array($rc);
	}
	ELSE $comment_id=0;
	IF ($_SESSION['AppUI']->user_prefs['LOCALE']=='en'){
		IF ($comment_id=='')$add='Add';
		ELSE $add='Edit';
		$clear='Clear';
	}
	ELSE {
		IF ($comment_id=='')$add='Agregar';
		ELSE $add='Editar';
		$clear='Borrar';
	}
	$edit = "<table width='98%' border='0' align='right' >";
	$edit .= "<tr><td>";
	$edit .= "<form name='edit$rows'>\n";
	$edit .= "<table width='100%' border='0' align='right' bgcolor='#F9F9F9'>\n";
	$edit .= "<tr id='show_$rows'>\n
					<td width='5'></td>
					<td style='background:#F7F7F7' align='left'>
						<textarea rows='2' cols='120' name='text$rows'>".$vec['know_base_note']."</textarea>
					</td>
				</tr>
				<tr id='show_$rows'>
					<td align='right' colspan='2'>\n
						<a href='javascript: //' onclick=\"var text=document.forms['edit$rows']['text$rows'].value; xajax_addnotes($rows, $item, text, $comment_id, $type); document.forms['edit$rows']['text$rows'].value=''; open_rows[$rows][1]=0; openclose_edit(open_rows, $rows, $item, $type);\">[$add]</a>
						<a href='javascript: //' onclick=\"document.forms['edit$rows']['text$rows'].value=''\">[$clear]</a>\n
					</td>\n
				</tr>\n";
	$edit .= "</table></form>\n";
	$edit .= "</td></tr>";
	$edit .= "</table>\n";
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("new_".$rows,"innerHTML", $edit);
	return $objResponse;
}

$xajax->registerFunction("save_data");

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type, $document_type, $task){
	global $AppUI;
	
	switch ($document_type){
		case 0:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$documentType="article";
			$document_title="title";
		break;
		
		case 1:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$documentType="link";
			$document_title="title";
		break;
			
		default:
			$table="files";
			$fieldName="file_id";
			$task_field="file_task";
			$documentType="file";
			$document_title="file_name";
		break;
	}
	
	$sql = "SELECT * FROM ".$table." WHERE ".$fieldName." = ".$applied_to_id;
	$document_data = mysql_fetch_array(mysql_query($sql));	
	
	$date = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
	
	$start_time = $date.' '.$start_hour.':'.$start_min.':00';
	$end_time = $date.' '.$end_hour.':'.$end_min.':00';
	
	if($is_internal == 1){
		$applied_to_id = 0;
		$applied_to_type = 3;
		$billable = 0;
		$applied_to_id = 0;
		$cost = 0;
		$contribute_task_completion = 0;
		$company = "";
		$document_data[$document_title] = $internal_type;
	}else{
		$applied_to_type = 1;
		
		$sql = "SELECT task_name FROM tasks
			 WHERE task_id = ".$document_data[$task_field];
		$task_data = mysql_fetch_array(mysql_query($sql));
		$document_data[$document_title] = $task_data['task_name'];
		
		$sql = "SELECT p.project_company FROM projects AS p INNER JOIN tasks AS t ON
			 p.project_id = t.task_project WHERE t.task_id = ".$document_data[$task_field];
		
		$company_data = mysql_fetch_array(mysql_query($sql));
		$company = $company_data['project_company'];
	}
	
	if(!empty($task)){
		$sql = "SELECT task_name FROM tasks WHERE task_id = ".$task;
		$task_data = mysql_fetch_array(mysql_query($sql));
		
		$document_data[$document_title] = $task_data['task_name'];
		$applied_to_id = $task;
		$is_internal = 0;
		$applied_to_type="1";
		
		$sql = "SELECT p.project_company FROM projects AS p INNER JOIN tasks AS t ON
			 p.project_id = t.task_project WHERE t.task_id = ".$task;
		
		$company_data = mysql_fetch_array(mysql_query($sql));
		$company = $company_data['project_company'];
	}
	
	$sql = "SELECT user_cost_per_hour FROM users WHERE user_id = ".$AppUI->user_id;
	$user_data = mysql_fetch_array(mysql_query($sql));
	$cost = number_format($total_hours * $user_data['user_cost_per_hour'],2);
	
	$description = ereg_replace("\"","'",$description);
	
	$save_date = date('Y-m-d H:i:s');
	
	$sql = "INSERT INTO timexp (timexp_name, timexp_description, timexp_creator, timexp_date, timexp_type,
		timexp_value, timexp_cost, timexp_applied_to_type, timexp_applied_to_id, timexp_billable, 
		timexp_contribute_task_completion, timexp_start_time, timexp_end_time, timexp_save_date, timexp_company) VALUES (
	'".$document_data[$document_title]."',
	\"".@$description."\",
	$AppUI->user_id,
	'$date',
	1,
	$total_hours,
	$cost,
	$applied_to_type,
	$applied_to_id,
	$billable,
	0,
	'$start_time',
	'$end_time',
	'$save_date',
	'$company'
	)";
	
	if(!mysql_query($sql)){
		$msg='ERROR: '.mysql_error();
	}else{
		$msg= $AppUI->_('Data saved correctly');
	}
	
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("TextoDevuelto","innerHTML", $msg);
	return $objResponse;
}

$xajax->registerFunction("set_field_value");

function set_field_value($field, $value, $type){
	global $AppUI;
	//type=0:article 1:link
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("document_type","value", $type);
	
	switch ($type){
		case 0:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
			$documentType="Article";
			$document_title="title";
		break;
		
		case 1:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
			$documentType="link";
			$document_title="title";
		break;
			
		default:
			$table="files";
			$fieldName="file_id";
			$task_field="file_task";
			$project_field="file_project";
			$documentType="file";
			$document_title="file_description";
		break;
	}
	
	$sql = "SELECT * FROM ".$table." WHERE ".$fieldName." = ".$value;
	$document_data = mysql_fetch_array(mysql_query($sql));
	
	if(!empty($document_data[$project_field])){
		if(empty($document_data[$task_field])){
			$sql = "SELECT p.project_name FROM projects AS p 
				WHERE p.project_id = ".$document_data[$project_field];
		
			$project_data = mysql_fetch_array(mysql_query($sql));
			
			$value = "<b>".$project_data['project_name']." / ".$documentType.": ".$document_data[$document_title]."</b> - ".$AppUI->_("Please select the task");
		}else{
			$sql = "SELECT p.project_name, t.task_name FROM tasks AS t INNER JOIN projects AS p ON 
				p.project_id = t.task_project WHERE t.task_id = ".$document_data[$task_field];
		
			$task_data = mysql_fetch_array(mysql_query($sql));
			
			$value = "<b>".$task_data['project_name']." / ".$task_data['task_name']."</b> - ".$documentType.": ".$document_data[$document_title];
		}
	}else{
		$value = "<b>".$document_data[$document_title]."</b> - ".$AppUI->_("Please select the internal category");
	}
	
	$html = "<center>$value</center>";
	$objResponse->addAssign($field,"innerHTML", $html);
	
	return $objResponse;
}

$xajax->registerFunction("is_internal");

function is_internal($field, $value, $type){
	$objResponse = new myXajaxResponse();
	switch ($type){
		case 0:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
		break;
		
		case 1:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
		break;
			
		default:
			$table="files";
			$fieldName="file_id";
			$task_field="file_task";
			$project_field="file_project";
		break;
	}
	
	$sql = "SELECT $task_field, $project_field FROM $table WHERE $fieldName = $value";
	$document_data = mysql_fetch_array(mysql_query($sql));
	
	$display_billable = "none";
	$display_internal = "none";
	$display_tasks = "none";
	
	if(!empty($document_data[$project_field])){
		$html = 0;
		$display_billable = "";
		if(empty($document_data[$task_field])){
			$display_tasks = "";
			$htmlCombo = getProjectTasks($document_data[$project_field]);
			$objResponse->addAssign('project_tasks_combo',"innerHTML", $htmlCombo);
		}
	}else{
		$html = 1;
		$display_internal = "";
	}
	
	$objResponse->addAssign($field,"value", $html);
	$objResponse->addAssign('internal_types_combo',"style.display", $display_internal);
	$objResponse->addAssign('billable_combo',"style.display", $display_billable);
	$objResponse->addAssign('project_tasks_combo',"style.display", $display_tasks);
	return $objResponse;
}

function getProjectTasks($project_id){
    global $AppUI;
	$sql = "SELECT task_name, task_id FROM tasks WHERE task_project = $project_id";
	$result = mysql_query($sql);
	
	$html = "<select name='project_task_ajax' size='1' class='text' style='width:120px;'>";
	//$html .= "\n<option value='0' selected>".$AppUI->_('No Task')."</option>";
	
	while ($task = mysql_fetch_array($result)){
		$html .= "\n<option value='".$task['task_id']."'>".$task['task_name']."</option>";
	}
	$html .= "\n</select>";
    return $AppUI->_('Tasks').": ".$html;
}

$xajax->registerFunction("saveLogHistory");

function saveLogHistory($article_id, $type, $action, $comment)
{
	global $AppUI;

	$obj = new CArticle();
	$obj->article_id = $article_id;
	$obj->saveLog($type, $action, $comment);

	$objResponse = new myXajaxResponse();
	$objResponse->addScript("window.top.location = 'index.php?".$AppUI->state['SAVEDPLACE']."'");
	return $objResponse;
}

//Incluye la funcion para ver si ya existe en la BD un archivo con el nombre del que se enta intentando cargar
include_once($AppUI->cfg['root_dir']."/modules/files/file_exist.php");

$xajax->processRequests();

$xajax->printJavascript('./includes/xajax/');
?>
<script type="text/javascript">
  function addOption(selectId, val, txt, sel) {
    var objOption = new Option(txt, val,false,sel);
     document.getElementById(selectId).options.add(objOption);
   }
</script>