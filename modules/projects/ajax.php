<?php
//session_start();
require_once("./classes/projects.class.php");
require_once( $AppUI->getModuleClass( 'articles' ));
require_once("./includes/xajax/xajax.inc.php");

class myXajaxResponse extends xajaxResponse  {
  function addCreateOptions($sSelectId, $options,$selected) {
    $this->addScript("document.getElementById('".$sSelectId."').length=0");
    if (sizeof($options) >0) {
       foreach ($options as $k => $v) {
       	 $sel=($selected==$k)?"true":"false";
       	 
       	 $name = $v;
		 $name=ereg_replace('"','&quot;',$name);
		 $name=ereg_replace("'","%27",$name);
       	 
         $this->addScript("addOption('".$sSelectId."','".$k."','".$name."',".$sel.");");
       }
     }
  }
}

$xajax = new xajax();
$xajax->registerFunction("addnotes");
$xajax->registerFunction("clear");
$xajax->registerFunction("notes");
$xajax->registerFunction("edit");
$xajax->registerFunction("action_multiple");
$xajax->registerFunction("delnote");
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

/**
 * De acuerdo a la accion seleccionada arma el como de tareas o usuarios del proyecto ingresado
 *
 * @param integer $action = 1: trae usuarios, 2: trae tareas
 * @param integer $project_id = id del proyecto del que se trae los usuarios o las tareas
 * @param string  $field_name = nombre del campo a actualizar
 */

function action_multiple($action, $project_id,$field_name ,$selected = "")
{
	global $AppUI;
   
   $objResponse = new myXajaxResponse();
   
   // accion = 2  Trae tareas
   if($action == '2')
   {
   	 // Traigo las tareas
     $sql_tasks = "SELECT task_id, task_name FROM tasks WHERE task_project='".$project_id."' ";
     $list = db_loadHashList( $sql_tasks );
     
     $list = arrayMerge( array('0' =>$AppUI->_("None task")), $list );
   }
   
   // Accion = 1 Trae los usuarios del proyecto
   if($action == '1')
   {
   	 // Traigo los usuarios
   	 $sql_users = "SELECT
					u.user_id,
					CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
					FROM project_roles AS pr
					INNER JOIN users AS u
					ON (pr.user_id=u.user_id)
				   WHERE project_id='".$project_id."'
				   UNION
				   SELECT
					u.user_id,
					CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
					FROM project_owners AS po
					INNER JOIN users AS u
					ON (po.project_owner=u.user_id)	
				   WHERE project_id='".$project_id."'
					GROUP BY user_id";
   	 $list = db_loadHashList( $sql_users );
   }
   
    // Cambia prioridad
   if($action == '4')
   { 
   	 $list['1'] = $AppUI->_('High');
   	 $list['2'] = $AppUI->_('Normal');
   	 $list['3'] = $AppUI->_('Low');
   	 
   	 $selected = '2';
   }
   
   // Cambia estado
   if ($action =='5')
   {
   	  $list['1'] = $AppUI->_('Complete');
   	  $list['0'] = $AppUI->_('Incomplete');
   	  
   	  $selected = '1';
   }
   
   if (!$list){
		$list=array(0=>$AppUI->_("No data available"));
   }
   
   $objResponse->addCreateOptions($field_name , $list ,$selected);
   return $objResponse->getXML();
}

function addnotes($rows, $item, $text, $comment_id, $type){
	$item=checkpost($item);
	$rows=checkpost($rows);
	$text=checkpost($text);
	if ($comment_id==0) $sql="INSERT INTO know_base_note (user_id, know_base_note, know_base_type, know_base_item_id) VALUES (".$_SESSION['AppUI']->user_id.", \"$text\", \"$type\",\"$item\");";
	else $sql="UPDATE know_base_note SET know_base_note=\"$text\" WHERE know_base_note_id='$comment_id'";
	if (db_exec($sql)) {
		if ($comment_id==0){
			if ($type==1) $sql="UPDATE files SET file_comments=file_comments+1 WHERE file_id=$item";
			if ($type==2) $sql="UPDATE articles SET article_comments=article_comments+1 WHERE article_id=$item";
		}
		db_exec($sql);
		//echo "($rows, $item, $text, $comment_id, $type)";
		include ('./includes/kbnotes.php');
		$objResponse = new xajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $notes);
		return $objResponse;
	}
	else {
		$objResponse = new xajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $sql);
		return $objResponse;
	}
}

function notes($rows, $item, $type){
	include ('./includes/kbnotes.php');
	$objResponse = new xajaxResponse();
	$objResponse->addAssign($rows,"innerHTML", $notes);
	return $objResponse;
}

function delnote($rows, $item, $comment_id, $type){
	$sql="DELETE FROM know_base_note WHERE know_base_note_id='$comment_id'";
	if (db_exec($sql)) {
		$sql="SELECT COUNT(know_base_note_id) AS citems FROM know_base_note WHERE know_base_type=$type AND know_base_item_id=$item";
		$vec=db_fetch_array(db_exec($sql));
		if ($type==1) $sql="UPDATE files SET file_comments=".$vec['citems']." WHERE file_id=$item";
		if ($type==2) $sql="UPDATE articles SET article_comments=".$vec['citems']." WHERE article_id=$item";
		db_exec($sql);
		include ('./includes/kbnotes.php');
		$objResponse = new xajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $notes);
		return $objResponse;
	}
	else {
		$objResponse = new xajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $sql);
		return $objResponse;
	}
}

function clear($rows){
	$clear='';
	$objResponse = new xajaxResponse();
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
						<a href='javascript: //' onclick=\"var text=document.forms['edit$rows']['text$rows'].value; xajax_addnotes($rows, $item, text, $comment_id, $type); document.forms['edit$rows']['text$rows'].value=''; open_rows[$rows][1]=0;openclose_edit(open_rows, $rows, $item, $type);\">[$add]</a>
						<a href='javascript: //' onclick=\"document.forms['edit$rows']['text$rows'].value=''\">[$clear]</a>\n
					</td>\n
				</tr>\n";
	$edit .= "</table></form>\n";
	$edit .= "</td></tr>";
	$edit .= "</table>\n";
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("new_".$rows,"innerHTML", $edit);
	return $objResponse;
}

$xajax->registerFunction("save_data");

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type, $document_type, $task){
	global $AppUI;
	
	$contribute_task_completion = 1;
	
	switch ($document_type){
		case 0:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
			$documentType="article";
			$document_title="title";
			$applied_to_type=1;
		break;
		
		case 1:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
			$documentType="link";
			$document_title="title";
			$applied_to_type=1;
		break;
			
		case 3:
			$table="tasks";
			$fieldName="task_id";
			$task_field="task_id";
			$project_field="task_project";
			$documentType="Task";
			$document_title="task_name";
			$applied_to_type=1;
		break;
		
		case 4:
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
			$documentType="ToDo";
			$document_title="description";
			$applied_to_type=4;
		break;
		
		case 5:
			$table="events";
			$fieldName="event_id";
			$task_field="event_task";
			$project_field="event_project";
			$documentType="Event";
			$document_title="event_title";
			$applied_to_type=1;
		break;
		
		case 6:
			$table="btpsa_bug_table";
			$fieldName="id";
			$task_field="task_id";
			$project_field="project_id";
			$documentType="Bug";
			$document_title="summary";
			$applied_to_type=2;
		break;
		
		default:
			$table="files";
			$fieldName="file_id";
			$task_field="file_task";
			$project_field="file_project";
			$documentType="file";
			$document_title="file_name";
			$applied_to_type=1;
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
		$document_data[$document_title]=$internal_type;
	}else{
		if(!empty($document_data[$task_field]) && $document_type!=6){
			$sql = "SELECT task_name FROM tasks WHERE task_id = ".$document_data[$task_field];
			$task_data = mysql_fetch_array(mysql_query($sql));
			$document_data[$document_title]=$task_data['task_name'];
			
			if($document_type!=3 && $document_type!=4){ //Reportar las horas a la tarea
				$applied_to_id = $document_data[$task_field];
			}
		}
		
		$sql = "SELECT project_company FROM projects WHERE project_id = ".$document_data[$project_field];
		
		$company_data = mysql_fetch_array(mysql_query($sql));
		$company = $company_data['project_company'];
	}
	
	if(!empty($task) && ($_GET['tab']==2 || $_GET['tab']==3) && $_GET['m']=="projects"){
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
	
	$sql = "SELECT user_cost_per_hour, timexp_supervisor FROM users WHERE user_id = ".$AppUI->user_id;
	$user_data = mysql_fetch_array(mysql_query($sql));
	$cost = number_format($total_hours * $user_data['user_cost_per_hour'],2);
	
	$description = ereg_replace("\"","'",$description);
	$description = ereg_replace("\'","",$description);
	
	$save_date = date('Y-m-d H:i:s');
	
	$cost = ereg_replace(",","",$cost);
	
	$status = 0;
	if($user_data["timexp_supervisor"] == -1) $status = 3;
	
	$sql = "INSERT INTO timexp (timexp_name, timexp_description, timexp_creator, timexp_date, timexp_type,
		timexp_value, timexp_cost, timexp_applied_to_type, timexp_applied_to_id, timexp_billable, 
		timexp_contribute_task_completion, timexp_start_time, timexp_end_time, timexp_save_date, timexp_company,
		timexp_last_status ) VALUES (
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
	$contribute_task_completion,
	'$start_time',
	'$end_time',
	'$save_date',
	'$company',
	$status
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
	//type=0:article 1:link 3:task 4:ToDo 6:bug
	
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("document_type","value", $type);
	
	switch ($type){
		case 0:
			$table="articles";
			$fieldName="article_id";
			$task_field="task";
			$project_field="project";
			$documentType="article";
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
		
		case 3:
			$table="tasks";
			$fieldName="task_id";
			$task_field="task_id";
			$project_field="project_id";
			$documentType="Task";
			$document_title="task_name";
		break;
		
		case 4:
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$documentType="ToDo";
			$document_title="description";
		break;
		
		case 5:
			$table="events";
			$fieldName="event_id";
			$task_field="event_task";
			$documentType="Event";
			$document_title="event_title";
		break;
		
		case 6:
			$table="btpsa_bug_table";
			$fieldName="id";
			$task_field="task_task";
			$documentType="Bug";
			$document_title="summary";
		break;
			
		default:
			$table="files";
			$fieldName="file_id";
			$task_field="file_task";
			$project_field="file_project";
			$documentType="file";
			$document_title="file_name";
		break;
	}
	
	$sql = "SELECT * FROM ".$table." WHERE ".$fieldName." = ".$value;
	$document_data = mysql_fetch_array(mysql_query($sql));
	
	if(!empty($document_data[$task_field])){
		$sql = "SELECT p.project_name, t.task_name FROM tasks AS t INNER JOIN projects AS p ON 
			p.project_id = t.task_project WHERE t.task_id = ".$document_data[$task_field];
		
		$task_data = mysql_fetch_array(mysql_query($sql));
		
		$value = "<b>".$task_data['project_name']." / ".$task_data['task_name']."</b> - ".$AppUI->_($documentType).": ".$document_data[$document_title];
	}else{
		if($_GET['tab']!=3 && $_GET['m']=="projects" && $type != 4 && empty($document_data[$project_field])){
			$value = "<b>".$document_data[$document_title]."</b> - ".$AppUI->_("Please select the internal category");
		}else{
			if($type != 4 && $type != 6){
				$sql = "SELECT project_name FROM projects WHERE project_id = ".$document_data[$project_field];
				$project_data = mysql_fetch_array(mysql_query($sql));
				$value = "<b>".$project_data['project_name']." / ".$document_data[$document_title]."</b> - ".$AppUI->_("Please select the task");
			}else{
				if($type == 6){
					$value = "<b>".$AppUI->_("Bug").": ".$document_data[$document_title]."</b>";
				}else {
					$value = "<b>ToDo: ".$document_data[$document_title]."</b>";
				}
			}
		}
	}
	
	$html = "<center>$value</center>";
	$objResponse->addAssign($field,"innerHTML", $html);
	
	return $objResponse;
}

//type=0:article 1:link 3:task 4:ToDo 5:event 6:bug
$xajax->registerFunction("is_internal");

function is_internal($field, $value, $type){
	if($type!=3 && $type!=4 && $type!=5 && $type!=6){ //Tipos NO internos seguro
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
		
		if(!empty($document_data[$project_field])){
			$html = 0;
			$display_billable = "";
		}else{
			$html = 1;
			$display_internal = "";
		}
		
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($field,"value", "$html");

		if($_GET['tab']!=3 && $_GET['m']=="projects"){
			$objResponse->addAssign('internal_types_combo',"style.display", $display_internal);
		}else{
			$objResponse->addAssign('internal_types_combo',"style.display", "none");
		}
		
		//echo "html: ".$html." campo proyecto: ".$document_data[$project_field]." campo Task: ".$document_data[$task_field];
		
		if(!empty($document_data[$project_field]) && $document_data[$task_field]==0 && $html=1){
			$htmlCombo = getProjectTasks($document_data[$project_field]);
			$objResponse->addAssign('project_tasks_combo',"style.display", "");
			$objResponse->addAssign('project_tasks_combo',"innerHTML", $htmlCombo);
			//die("<pre>".print_r($htmlCombo)."</pre>");
		}else{
			$objResponse->addAssign('project_tasks_combo',"style.display", "none");
			$objResponse->addAssign('project_tasks_combo',"value", "0");
		}
	
		$objResponse->addAssign('billable_combo',"style.display", $display_billable);
	}else{
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($field,"value", "0");
		$objResponse->addAssign('billable_combo',"style.display", "");
	}
	return $objResponse;
}

$xajax->registerFunction("saveLogHistory");

function saveLogHistory($item_id, $type, $action, $comment)
{
	global $AppUI;

	if($type == 2)
	{
		$obj = new CFile();
		$obj->file_id = $item_id;
		$obj->saveLog($type, $action, $comment);
	}
	else
	{
		include_once( $AppUI->getModuleClass( 'articles' ) );
		$obj = new CArticle();
		$obj->article_id = $item_id;
		$obj->saveLog($type, $action, $comment);
	}

	$objResponse = new myXajaxResponse();
	$objResponse->addScript("window.top.location = 'index.php?".$AppUI->state['SAVEDPLACE']."'");
	return $objResponse;
}

function getProjectTasks($project_id){
    global $AppUI;
	$sql = "SELECT task_name, task_id FROM tasks WHERE task_project = $project_id";
	$result = mysql_query($sql);
	
	$html = "<select name='project_task_ajax' size='1' class='text' style='width:120px;'>";
	//$html .= "\n<option value='0' selected>".$AppUI->_('None')."</option>";
	
	while ($task = mysql_fetch_array($result)){
		$html .= "\n<option value='".$task['task_id']."'>".$task['task_name']."</option>";
	}
	$html .= "\n</select>";
    return $AppUI->_('Tasks').": ".$html;
}
//Incluye la funcion para ver si ya existe en la BD un archivo con el nombre del que se enta intentando cargar
include_once($AppUI->cfg['root_dir']."/modules/files/file_exist.php");

// Incluyo Funciones Empresa/Canal/Proyecto
include("./modules/public/ajax.php");

$xajax->processRequests();

$xajax->printJavascript('./includes/xajax/');
?>
<script language="JavaScript">
function close_div(div_name){
	document.getElementById(div_name).style.display='none';
}
	
function progress_msg(visibility_st){

var f = document.editFrm;

if(visibility_st == 'mostrar'){
	// Muestro el cartel de procesando	  
	document.getElementById('progress').style.display='';
	document.getElementById('add_hours').style.display='none';

    setTimeout("progress_msg('error')", 60*1000); 
		
	}else{
	   
  	// Oculto el mensaje de error
  	document.getElementById('progress').style.display = "none"; 		  
	}
}
</script>

<script type="text/javascript">
  function addOption(selectId, val, txt, sel) {
    var objOption = new Option(txt, val,false,sel);
     document.getElementById(selectId).options.add(objOption);
   }
</script>