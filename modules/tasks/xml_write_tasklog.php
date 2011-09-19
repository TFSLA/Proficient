<?php 
require_once($AppUI->getModuleClass("timexp"));

$obj = new CTimExp();
$data = array (
"timexp_id" 		=> "",
"timexp_type"		=> "1",
"timexp_name"		=> $task_log_name,
"timexp_description"=> $task_log_description,
"timexp_creator"	=> $AppUI->user_id,
"timexp_value"		=> $task_log_hours,
"timexp_date"		=> $task_log_date,
"timexp_start_time" => "0000-00-00 00:00:00",
"timexp_end_time" 	=> "0000-00-00 00:00:00",
);
$msg = NULL;
if (!$obj->bind( $data )) {
	$msg = "ERROR :: ".$AppUI->_($obj->getError());
}else{
	if (!($msg = $obj->store())) {
		$data=array();
		$data["timexp_id"]=$obj->timexp_id;
		$data["timexp_status_id"]="0";
		$data["timexp_status_datetime"] = $obj->timexp_date;
		$data["timexp_status_value"] = $obj->timexp_last_status;
		$data["timexp_status_user"] = $obj->timexp_creator;

		$status = new CTimExpStatus();
		if (!$status->bind( $data )) {
			$msg = "ERROR :: ".$AppUI->_($status->getError());
		}else{
			$msg = $status->store();
		}
	}
}

echo "<" . "?xml version=\"1.0\" encoding=\"iso-8859-1\" ?" . ">\n";
echo "<tasklogresult>\n";

if(! $msg ) echo "  OK\n";
 
else echo "  ERROR\n";

echo "</tasklogresult>\n";



/* OLD VERSION */
/*
//fmt: 'yyyy-mm-dd hh:mm:ss'

$sql="INSERT INTO `task_log` ( `task_log_id` , `task_log_task` , `task_log_name` , `task_log_description` , `task_log_creator` , `task_log_hours` , `task_log_date` , `task_log_costcode` ) VALUES ('', '$task_log_task', '$task_log_name', '$task_log_description', '{$AppUI->user_id}', 'task_log_hours', '$task_log_date', '$task_log_costcode');";
echo "<" . "?xml version=\"1.0\" encoding=\"iso-8859-1\" ?" . ">\n";
echo "<tasklogresult>\n";

if($res = mysql_query($sql) ) echo "  OK\n";
 
else echo "  ERROR\n";

echo "</tasklogresult>\n";

*/
?> 