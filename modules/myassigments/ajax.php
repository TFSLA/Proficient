<?
require_once('./includes/xajax/xajax.inc.php');
require_once("functions.php");


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

$xajax->registerFunction("save_data");

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type, $type, $task){
	global $AppUI;
	//type=t:todo ta:task b:bug
	
	switch ($type){
		case 't':
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
			$assignmentType="ToDo";
			$assignment_title="description";
			$applied_to_type="4";
		break;
		
		case 'ta':
			$table="tasks";
			$fieldName="task_id";
			$task_field="task_id";
			$project_field="task_project";
			$assignmentType="Task";
			$assignment_title="task_name";
			$applied_to_type="1";
			$contribute_task_completion="1";
		break;
		
		case 'b':
			$table="btpsa_bug_table";
			$fieldName="id";
			$task_field="task_id";
			$project_field="project_id";
			$assignmentType=$AppUI->_("Bug");
			$assignment_title="summary";
			$applied_to_type="2";
		break;
			
		default:
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
			$assignmentType="ToDo";
			$assignment_title="description";
			$applied_to_type="4";
		break;
	}
	
	$sql = "SELECT * FROM ".$table." WHERE ".$fieldName." = ".$applied_to_id;
	$assignment_data = mysql_fetch_array(mysql_query($sql));
	
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
		$assignment_data[$assignment_title]=$internal_type;
	}else{
		$sql = "SELECT project_company FROM projects WHERE project_id = ".$assignment_data[$project_field];
		
		$company_data = mysql_fetch_array(mysql_query($sql)) or die($sql);
		$company = $company_data['project_company'];
	}
	
	if(!empty($assignment_data[$task_field])){
		$contribute_task_completion="1";
	}else{
		$contribute_task_completion="0";
	}
	
	$sql = "SELECT user_cost_per_hour FROM users WHERE user_id = ".$AppUI->user_id;
	$user_data = mysql_fetch_array(mysql_query($sql));
	$cost = number_format($total_hours * $user_data['user_cost_per_hour'],2);
	
	$description = ereg_replace("\"","",$description);
	$description = ereg_replace('\'',"",$description);
	
	$assignment_data[$assignment_title] = eregi_replace("'","",$assignment_data[$assignment_title]);
	$assignment_data[$assignment_title] = eregi_replace("\"","",$assignment_data[$assignment_title]);
	
	$save_date = date('Y-m-d H:i:s');
	
	$sql = "INSERT INTO timexp (timexp_name, timexp_description, timexp_creator, timexp_date, timexp_type,
		timexp_value, timexp_cost, timexp_applied_to_type, timexp_applied_to_id, timexp_billable, 
		timexp_contribute_task_completion, timexp_start_time, timexp_end_time, timexp_save_date, timexp_company) VALUES (
	'".$assignment_data[$assignment_title]."',
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
	'$company'
	)";
	
	if(!mysql_query($sql)){
		$msg='ERROR: '.$sql;
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
	//type=t:todo ta:task b:bug
	
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("document_type","value", $type);
	
	switch ($type){
		case 't':
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
			$assignmentType="ToDo";
			$assignment_title="description";
		break;
		
		case 'ta':
			$table="tasks";
			$fieldName="task_id";
			$task_field="task_id";
			$project_field="task_project";
			$assignmentType=$AppUI->_("Task");
			$assignment_title="task_name";
		break;
		
		case 'b':
			$table="btpsa_bug_table";
			$fieldName="id";
			$task_field="task_id";
			$project_field="project_id";
			$assignmentType=$AppUI->_("Bug");
			$assignment_title="summary";
		break;
			
		default:
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
			$assignmentType="ToDo";
			$assignment_title="description";
		break;
	}
	
	$sql = "SELECT * FROM ".$table." WHERE ".$fieldName." = ".$value;
	$assignment_data = mysql_fetch_array(mysql_query($sql));
	
	if(!empty($assignment_data[$project_field])){
		if(empty($assignment_data[$task_field])){
			$sql = "SELECT p.project_name FROM projects AS p 
				WHERE p.project_id = ".$assignment_data[$project_field];
		
			$project_data = mysql_fetch_array(mysql_query($sql));
			
			$value = "<b>".$project_data['project_name']." </b> - ".$assignmentType.": ".$assignment_data[$assignment_title];
		}else{
			$sql = "SELECT p.project_name, t.task_name FROM tasks AS t INNER JOIN projects AS p ON 
				p.project_id = t.task_project WHERE t.task_id = ".$assignment_data[$task_field];
		
			$task_data = mysql_fetch_array(mysql_query($sql));
			$value = "<b>".$task_data['project_name']." / ".$task_data['task_name']."</b> - ";
			
			if($type=='ta'){	//Si es tarea muestro el responsable
				$sql = "SELECT user_username FROM users WHERE user_id = ".$assignment_data['task_owner'];
				$owner_data = mysql_fetch_array(mysql_query($sql));
				$value .= $AppUI->_('Responsable').": ".$owner_data['user_username'];
			}else{
				$value .= $assignmentType.": ".$assignment_data[$assignment_title];	
			}
		}
	}else{
		$value = "<b>".$assignment_data[$assignment_title]."</b> - ".$AppUI->_("Please select the internal category");
	}
	
	$html = "<center>$value</center>";
	$objResponse->addAssign($field,"innerHTML", $html);
		
	return $objResponse;
}

function getProjectTasks($project_id){
    global $AppUI;
	$sql = "SELECT task_name, task_id FROM tasks WHERE task_project = $project_id";
	$result = mysql_query($sql);
	
	$html = "<select name='project_task_ajax' size='1' class='text' style='width:120px;'>";
	$html .= "\n<option value='0' selected>".$AppUI->_('None')."</option>";
	
	while ($task = mysql_fetch_array($result)){
		$html .= "\n<option value='".$task['task_id']."'>".$task['task_name']."</option>";
	}
	$html .= "\n</select>";
    return $AppUI->_('Tasks').": ".$html;
}

$xajax->registerFunction("is_internal");

function is_internal($field, $value, $type){
	//type=t:todo ta:task b:bug
	switch ($type){
		case 't':
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
		break;
		
		case 'ta':
			$table="tasks";
			$fieldName="task_id";
			$task_field="task_id";
			$project_field="task_project";
		break;
		
		case 'b':
			$table="btpsa_bug_table";
			$fieldName="id";
			$task_field="task_id";
			$project_field="project_id";
		break;
			
		default:
			$table="project_todo";
			$fieldName="id_todo";
			$task_field="task_id";
			$project_field="project_id";
		break;
	}
	
	$objResponse = new myXajaxResponse();
	
	$sql = "SELECT $task_field, $project_field FROM $table WHERE $fieldName = $value";
	$assignment_data = mysql_fetch_array(mysql_query($sql));
	
	$display_billable = "none";
	$display_internal = "none";
	$display_tasks = "none";
	
	if(!empty($assignment_data[$project_field])){
		$html = 0;
		$display_billable = "";
	}else{
		$html = 1;
		$display_internal = "";
	}
	
	$objResponse->addAssign($field,"value", "$html");
	$objResponse->addAssign('internal_types_combo',"style.display", $display_internal);
	$objResponse->addAssign('billable_combo',"style.display", $display_billable);
	$objResponse->addAssign('project_tasks_combo',"style.display", $display_tasks);
	return $objResponse;
}

$xajax->registerFunction("addUsersMyOwnerProjects");

function addUsersMyOwnerProjects($selectId, $p_project_id, $company_id, $canal_id, $selected="", $allUsers = TRUE)
{
	global $AppUI;
    $objResponse = new myXajaxResponse();
     
    $list = CProject::getUsersMyOwnerProjects($selectId, $p_project_id, $company_id, $canal_id, $selected, ($allUsers == 'TRUE' ? true : false));
  
    $objResponse->addCreateOptions($selectId, $list,$selected);
    return $objResponse->getXML();
}

$xajax->registerFunction("changeMyAssigmentsActive");

function changeMyAssigmentsActive($user_id, $assigment_id, $assigment_type, $status)
{
	global $AppUI;
	
	$objResponse = new myXajaxResponse();
	
	$AppUI->setState( 'assigment_active_id', $assigment_id);
	$AppUI->setState( 'assigment_active_type', $assigment_type);
	
	$newDate = new CDate();
	
	$AppUI->setState( 'assigment_active_date', $newDate->format("%d/%m/%y %H:%M:%S"));
	$AppUI->setState( 'assigment_active_save', true);

	$sql = "DELETE FROM myassigments_active WHERE user_id = ".$user_id;
	$ret = db_exec( $sql );
	
	if($status == '1')
	{
		$sql = "INSERT INTO myassigments_active (user_id, myassigment_id, myassigment_type, myassigment_date) VALUES ( ".$AppUI->user_id.", ".$assigment_id.", '".$assigment_type."', NOW())";
		$ret = db_exec( $sql );
	}
	
	$AppUI->setState('myassigment_dateactive', null);
	
	$AppUI->setMsg(($status == '1' ? $AppUI->_('Assigment activated') : $AppUI->_('Assigment deactivated')), UI_MSG_OK);
	$message = str_replace("'",'', $AppUI->getMsg());
	$message = str_replace('"','\"', $message);	
	
	$objResponse->addScript("showGenericMessage('".$message."');");
	
	return $objResponse->getXML();
}

$xajax->processRequests();

$xajax->printJavascript('./includes/xajax/');
?>
<script type="text/javascript">
  function addOption(selectId, val, txt, sel) {
    var objOption = new Option(txt, val,false,sel);
     document.getElementById(selectId).options.add(objOption);
   }
</script>