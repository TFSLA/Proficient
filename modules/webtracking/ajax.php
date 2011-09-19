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

$xajax->registerFunction("save_data");

function save_data($description, $date, $applied_to_id, $billable, $start_hour, $start_min, $end_hour, $end_min, $total_hours, $is_internal, $internal_type, $tasks){
	global $AppUI;
	$sql = "SELECT * FROM btpsa_bug_table WHERE id = $applied_to_id";
	$incident_data = mysql_fetch_array(mysql_query($sql));

	$date = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);

	$start_time = $date.' '.$start_hour.':'.$start_min.':00';
	$end_time = $date.' '.$end_hour.':'.$end_min.':00';

	$sql = "SELECT project_company FROM projects WHERE project_id = ".$incident_data['project_id'];
	$company_data = mysql_fetch_array(mysql_query($sql));
	$company = $company_data['project_company'];

	$sql = "SELECT user_cost_per_hour FROM users WHERE user_id = ".$AppUI->user_id;
	$user_data = mysql_fetch_array(mysql_query($sql));
	$cost = number_format($total_hours * $user_data['user_cost_per_hour'],2);

	$description = ereg_replace("\"","'",$description);

	$save_date = date('Y-m-d H:i:s');

	$sql = "INSERT INTO timexp (timexp_name, timexp_description, timexp_creator, timexp_date, timexp_type,
		timexp_value, timexp_cost, timexp_applied_to_type, timexp_applied_to_id, timexp_billable,
		timexp_contribute_task_completion, timexp_start_time, timexp_end_time, timexp_save_date, timexp_company) VALUES (
	'".$incident_data['summary']."',
	\"".@$description."\",
	$AppUI->user_id,
	'$date',
	1,
	$total_hours,
	$cost,
	2,
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
	$objResponse = new myXajaxResponse();

	$sql = "SELECT project_id, summary FROM btpsa_bug_table WHERE id = $value";
	$incident_data = mysql_fetch_array(mysql_query($sql));

	$sql = "SELECT p.project_name, c.company_name FROM projects AS p INNER JOIN companies AS c ON
		c.company_id = p.project_company WHERE p.project_id = ".$incident_data['project_id'];

	$project_data = mysql_fetch_array(mysql_query($sql));

	if(!empty($incident_data["project_id"]) && empty($incident_data["task_id"])){
		$value = "<b>".$project_data['project_name']."</b> - ID: ".$incident_data['summary'];
	}else{
		$sql = "SELECT task_name FROM tasks WHERE task_id = ".$incident_data["task_id"];
		$task_data = mysql_fetch_array(mysql_query($sql));

		$value = "<b>".$project_data['project_name']." / ".$task_data['task_name']."</b> - ID: ".$incident_data['summary'];
	}

	$html = "<center>$value</center>";
	$objResponse->addAssign($field,"innerHTML", $html);
	return $objResponse;
}

$xajax->registerFunction("kb_type_section");

function kb_type_section($kb_type, $kb_section, $selected){
    global $AppUI;

   $objResponse = new myXajaxResponse();

   if($kb_section !='0')
   {
   	$file_section = "AND file_section ='".$kb_section."' ";
   	$art_section = "AND articlesection_id='".$kb_section."' ";
   }

   # Traigo los items de acuerdo al tipo (  -1 ->todos , 0->articulos, 1 -> links, 2-> archivos)
   switch ($kb_type)
   {
   	case '0':
   		$query_type = "SELECT article_id, title FROM articles WHERE 1=1 $art_section AND type='0' order by title";
   	break;
   	case '1':
   		$query_type = "SELECT article_id, title FROM articles WHERE 1=1 $art_section AND type='1' order by title";
   	break;
   	case '2':
   		$query_type = "SELECT file_id as id, file_name as name FROM files  WHERE 1=1 $file_section order by file_name";
   	break;
   }

   //echo "query_type: $query_type<br>";
   if($query_type !=""){  $list = db_loadHashList( $query_type ); }

   if(count($list)==0 || $list=="")
   {
       $list[0] = "Ninguno";
   }

   $objResponse->addCreateOptions('kb_item', $list,$selected);
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