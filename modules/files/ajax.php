<?php
//session_start();
require_once("./includes/xajax/xajax.inc.php");
require_once("./classes/projects.class.php");

class myXajaxResponse extends xajaxResponse  {
  function addCreateOptions($sSelectId, $options,$selected) {
    $this->addScript("document.getElementById('".$sSelectId."').length=0");
    if (sizeof($options) > 0) {
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

	require_once( $AppUI->getModuleClass('articles') );

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

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type, $document_type){
	global $AppUI;
	
	$sql = "SELECT * FROM files WHERE file_id = ".$applied_to_id;
	$file_data = mysql_fetch_array(mysql_query($sql));	
	
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
		$file_data["file_name"] = $internal_type;
	}else{
		$applied_to_type = 1;
		
		$sql = "SELECT task_name FROM tasks
			 WHERE task_id = ".$file_data["file_task"];
		$task_data = mysql_fetch_array(mysql_query($sql));
		$file_data["file_name"] = $task_data['task_name'];
		
		$sql = "SELECT p.project_company FROM projects AS p INNER JOIN tasks AS t ON
			 p.project_id = t.task_project WHERE t.task_id = ".$file_data["file_task"];
		
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
	'".$file_data["file_name"]."',
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
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("document_type","value", $type);
	
	$sql = "SELECT * FROM files WHERE file_id = ".$value;
	$file_data = mysql_fetch_array(mysql_query($sql));
	
	if(!empty($file_data["file_task"])){
		$sql = "SELECT p.project_name, t.task_name FROM tasks AS t INNER JOIN projects AS p ON 
			p.project_id = t.task_project WHERE t.task_id = ".$file_data["file_task"];
	
		$task_data = mysql_fetch_array(mysql_query($sql));
		
		$value = $task_data['project_name']." / ".$task_data['task_name']." - ".$AppUI->_("File").": ".$file_data["file_name"];
	}else{
		$value = $file_data["file_name"]." - ".$AppUI->_("Please select the internal category");
	}
	
	$html = "<center><b>$value</b></center>";
	$objResponse->addAssign($field,"innerHTML", $html);
	
	return $objResponse;
}

$xajax->registerFunction("is_internal");

function is_internal($field, $value){
	$sql = "SELECT file_task FROM files WHERE file_id = $value";
	$file_data = mysql_fetch_array(mysql_query($sql));
	
	$display_billable = "none";
	$display_internal = "none";
	
	if(!empty($file_data["file_task"])){
		$html = 0;
		$display_billable = "";
	}else{
		$html = 1;
		$display_internal = "";
	}
	
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign($field,"value", "$html");
	$objResponse->addAssign('internal_types_combo',"style.display", $display_internal);
	$objResponse->addAssign('billable_combo',"style.display", $display_billable);
	return $objResponse;
}

$xajax->registerFunction("saveLogHistory");

function saveLogHistory($file_id, $type, $action, $comment)
{
	global $AppUI;

	$obj = new CFile();
	$obj->file_id = $file_id;
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