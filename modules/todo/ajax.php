<?
require_once('./includes/xajax/xajax.inc.php');


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

$xajax->registerFunction("action_multiple");

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
   
   // Asocia a Tareas
   if($action == '2')
   {
   	 $vec_proj = explode(",",$project_id);
   	 
   	  foreach ($vec_proj as $proj_id)
	  {
	   	 if ($proj_id != "")
	   	 {
	   	 	$task_project = $proj_id;	
	   	 }
	  }
   	 
   	 // Traigo las tareas
     $sql_tasks = "SELECT task_id, task_name FROM tasks WHERE task_project='".$task_project."' ";
     
     $list = db_loadHashList( $sql_tasks );
     
     $list = arrayMerge( array('0' =>$AppUI->_("None task")), $list );
   }
   
   // Asigna usuarios
   if($action == '1')
   {
   	 if($project_id !="")
   	 {
	   	 $vec_proj = explode(",",$project_id);
	   	 $cant_proj = 0;
	   	 
	   	 foreach ($vec_proj as $proj_id)
	   	 {
	   	 	if ($proj_id != "")
	   	 	{
	   	 		$query_proj = $query_proj."OR project_id = '$proj_id' ";
	   	 		$cant_proj = $cant_proj + 1;
	   	 	}
	   	 }
	   	 
	   	 $query_proj = substr($query_proj,2);
	   	 
	   	 // Traigo los usuarios
	   	 $sql_users = "SELECT
						u.user_id, 
						CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name, 
						project_id
						FROM project_roles AS pr
						INNER JOIN users AS u
						ON (pr.user_id=u.user_id)
					   WHERE $query_proj
					   UNION
					   SELECT
						u.user_id, 
						CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name,
						project_id
						FROM project_owners AS po
						INNER JOIN users AS u
						ON (po.project_owner=u.user_id)	
					   WHERE $query_proj
						GROUP BY user_id";
	   	 
	   	 $resp = db_exec( $sql_users );
	   	 
	   	 while ($vec_users = mysql_fetch_array($resp))
	   	 {
	   	 	$vec_u[$vec_users[user_id]][] = $vec_users[project_id];
	   	 	$user_data[$vec_users[user_id]] = $vec_users[user_name];
	   	 }
	   	 //echo "<pre>"; print_r($vec_u); echo "</pre>";
	   	 if(count($vec_u)>0)
	   	 {
	   	 	foreach ($vec_u as $user=>$projs)
	   	 	{  
	   	 		if($cant_proj == count($projs))
	   	 		{
	   	 			$list[$user] = $user_data[$user];
	   	 		}
	   	 	}
	   	 }
	   	 
   	 }
   }
   
   // Cambia prioridad
   if($action == '4')
   { 
   	 $list['1'] = $AppUI->_('High');
   	 $list['2'] = $AppUI->_('Normal');
   	 $list['3'] = $AppUI->_('Low');
   	 
   	 $selected = '2';
   }
   
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

$xajax->registerFunction("save_data");

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type, $task){
	global $AppUI;
	$sql = "SELECT * FROM project_todo WHERE id_todo = $applied_to_id";
	$todo_data = mysql_fetch_array(mysql_query($sql));
	
	$date = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
	
	$start_time = $date.' '.$start_hour.':'.$start_min.':00';
	$end_time = $date.' '.$end_hour.':'.$end_min.':00';
	
	$sql = "SELECT project_company FROM projects WHERE project_id = ".$todo_data['project_id'];
	$company_data = mysql_fetch_array(mysql_query($sql));
	$company = $company_data['project_company'];
	
	$sql = "SELECT user_cost_per_hour, timexp_supervisor FROM users WHERE user_id = ".$AppUI->user_id;
	$user_data = mysql_fetch_array(mysql_query($sql));
	$cost = number_format($total_hours * $user_data['user_cost_per_hour'],2);
	
	$description = ereg_replace("\"","'",$description);
	
	$save_date = date('Y-m-d H:i:s');
	
	$status = 0;
	if($user_data["timexp_supervisor"] == -1) $status = 3;
	
	$sql = "INSERT INTO timexp (timexp_name, timexp_description, timexp_creator, timexp_date, timexp_type,
		timexp_value, timexp_cost, timexp_applied_to_type, timexp_applied_to_id, timexp_billable, 
		timexp_contribute_task_completion, timexp_start_time, timexp_end_time, timexp_save_date, timexp_company,
		timexp_last_status) VALUES (
	'".$todo_data['description']."',
	\"".@$description."\",
	$AppUI->user_id,
	'$date',
	1,
	$total_hours,
	$cost,
	4,
	$applied_to_id,
	$billable,
	0,
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

function set_field_value($field, $value){
	global $AppUI;
	$objResponse = new myXajaxResponse();
	
	$sql = "SELECT * FROM project_todo WHERE id_todo = $value";
	$todo_data = mysql_fetch_array(mysql_query($sql));
	
	$sql = "SELECT p.project_name, c.company_name FROM projects AS p INNER JOIN companies AS c ON 
		c.company_id = p.project_company WHERE p.project_id = ".$todo_data['project_id'];
	
	$project_data = mysql_fetch_array(mysql_query($sql));
	
	if(!empty($todo_data['project_id']) && empty($todo_data['task_id'])){
		$sql = "SELECT p.project_name FROM projects AS p 
			WHERE p.project_id = ".$todo_data['project_id'];
	
		$project_data = mysql_fetch_array(mysql_query($sql));
		
		$value = "<b>".$project_data['project_name']."</b> - ToDo: ".$todo_data["description"];
	}else{
		$sql = "SELECT p.project_name, t.task_name FROM tasks AS t INNER JOIN projects AS p ON 
			p.project_id = t.task_project WHERE t.task_id = ".$todo_data["task_id"];
	
		$task_data = mysql_fetch_array(mysql_query($sql));
		$objResponse->addAssign('project_tasks_combo',"style.display", "none");
		$value = "<b>".$task_data['project_name']." / ".$task_data['task_name']."</b> - ToDo: ".$todo_data['description'];
	}
		
	$html = "<center>$value</center>";
	$objResponse->addAssign($field,"innerHTML", $html);
	return $objResponse;
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