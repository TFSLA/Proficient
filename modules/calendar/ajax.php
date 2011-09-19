<?
require_once('./includes/xajax/xajax.inc.php');
require_once('./modules/calendar/calendar.class.php');

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

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type){
	global $AppUI;
	$contribute_task_completion = 1;
	$applied_to_type = 1;
	
	$sql = "SELECT * FROM events WHERE event_id = $applied_to_id";
	$event_data = mysql_fetch_array(mysql_query($sql));
	
	$date = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
	
	$start_time = $date.' '.$start_hour.':'.$start_min.':00';
	$end_time = $date.' '.$end_hour.':'.$end_min.':00';
	
	if($is_internal == 1){
		$event_data['event_title'] = $internal_type;
		$applied_to_id = 0;
		$applied_to_type = 3;
		$billable = 0;
		$applied_to_id = 0;
		$cost = 0;
		$contribute_task_completion = 0;
		$company = "";
	}else{
		$sql = "SELECT p.project_id, t.task_id FROM tasks AS t INNER JOIN projects AS p ON 
			p.project_id = t.task_project WHERE t.task_id = ".$event_data['event_task'];
		$company_data = mysql_fetch_array(mysql_query($sql));
		$company = $company_data['project_id'];
		$applied_to_id = $company_data['task_id'];
		
		$sql = "SELECT user_cost_per_hour FROM users WHERE user_id = ".$AppUI->user_id;
		$user_data = mysql_fetch_array(mysql_query($sql));
		$cost = number_format($total_hours * $user_data['user_cost_per_hour'],2);
	}
	
	$description = ereg_replace("\"","'",$description);
	
	$save_date = date('Y-m-d H:i:s');
	
	$sql = "INSERT INTO timexp (timexp_name, timexp_description, timexp_creator, timexp_date, timexp_type,
		timexp_value, timexp_cost, timexp_applied_to_type, timexp_applied_to_id, timexp_billable, 
		timexp_contribute_task_completion, timexp_start_time, timexp_end_time, timexp_save_date, timexp_company) VALUES (
	'".$event_data['event_title']."',
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
		$msg='ERROR: '.mysql_error();
	}else{
		if($AppUI->user_locale=='en')
			$msg='Data saved correctly';
		else 
			$msg='Datos guardados correctamente';
	}
	
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("TextoDevuelto","innerHTML", $msg);
	return $objResponse;
}

$xajax->registerFunction("set_field_value");

function set_field_value($field, $value){
	global $AppUI;
	$objResponse = new myXajaxResponse();
	
	$sql = "SELECT * FROM events WHERE event_id = $value";
	$event_data = mysql_fetch_array(mysql_query($sql));
	
	if($event_data['event_recurse_type']!='w' && $event_data['event_recurse_type']!='m' && $event_data['event_recurse_type']!='y' && $event_data['event_recurse_type']!='d'){
		$event_start_hour = substr( strval($event_data['event_start_date']),11,2);
		$event_start_min = substr( strval($event_data['event_start_date']),14,2);
		$event_end_hour = substr( strval($event_data['event_end_date']),11,2);
		$event_end_min = substr( strval($event_data['event_end_date']),14,2);
	}else{
		$event_start_hour = substr( strval($event_data['event_start_time']),0,2);
		$event_start_min = substr( strval($event_data['event_start_time']),3,2);
		$event_end_hour = substr( strval($event_data['event_end_time']),0,2);
		$event_end_min = substr( strval($event_data['event_end_time']),3,2);
	}
	
	$objResponse->addAssign("from_hour","value", $event_start_hour);
	$objResponse->addAssign("from_min","value", $event_start_min);
	$objResponse->addAssign("to_hour","value", $event_end_hour);
	$objResponse->addAssign("to_min","value", $event_end_min);
	
	$objResponse->addAssign("comments","innerHTML", $event_data['event_title'].": ".$event_data['event_description']);
	
	if(!empty($event_data['event_task'])){
		$sql = "SELECT p.project_name, t.task_name FROM tasks AS t INNER JOIN projects AS p ON 
			p.project_id = t.task_project WHERE t.task_id = ".$event_data['event_task'];
		
		$task_data = mysql_fetch_array(mysql_query($sql));
		
		$value = "<b>".$task_data['project_name']." / ".$task_data['task_name']."</b> - ".$AppUI->_('Event').": ".$event_data['event_title'];
	}else if (!empty($event_data['event_salepipeline'])) {
	
		$sql = "SELECT accountname, projecttype FROM salespipeline WHERE id = ".$event_data['event_salepipeline'];
		
		$pipeline_data = mysql_fetch_array(mysql_query($sql));
		
		$value = "<b>".$pipeline_data['accountname']." / ".$pipeline_data['projecttype']."</b> - ".$AppUI->_('Event').": ".$event_data['event_title'];	
	
	}else{
		$value = "<b>".$event_data['event_title']."</b> - ".$AppUI->_("Please select the internal category");
	}
	$html = "<center>$value</center>";
	$objResponse->addAssign($field,"innerHTML", $html);
	return $objResponse;
}

$xajax->registerFunction("is_internal");

function is_internal($field, $value){
	$sql = "SELECT event_task, event_salepipeline FROM events WHERE event_id = $value";
	$event_data = mysql_fetch_array(mysql_query($sql));
	$objResponse = new myXajaxResponse();
	
	$display_billable = "none";
	$display_internal = "none";
	
	if(!empty($event_data['event_task'])){
		$html = 0;
		$display_billable = "";
	}else if(!empty($event_data['event_salepipeline'])){
		$html = 1;
		$display_internal = "";
		$objResponse->addAssign("internalTypes","value", 10);  //10: Desarrollo de negocios
	}else{
		$html = 1;
		$display_internal = "";
	}
	
	$objResponse->addAssign($field,"value", "$html");
	$objResponse->addAssign('internal_types_combo',"style.display", $display_internal);
	$objResponse->addAssign('billable_combo',"style.display", $display_billable);
	return $objResponse;
}

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