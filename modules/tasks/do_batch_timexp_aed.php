<?php /* TASKS $Id: do_batch_timexp_aed.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */


$del = dPgetParam( $_POST, 'del', 0 );
$url = dPgetParam( $_POST, 'next', "" ); 
//echo "<pre>";var_dump($_POST);echo "</pre>";
$obj = new CTimExp();
$data_post = $_POST;

$dates = explode(",",$_POST["timexp_dates"]);
$values = explode(",",$_POST["timexp_hours"]);


// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Times & Expenses' );
$msg_gral = "";
for($i=0; $i < count($dates); $i++){
	$data_post["timexp_date"] = $dates[$i];
	$data_post["timexp_value"] = $values[$i];

	if (!$obj->bind( $data_post )) {
		$msg_gral .= $AppUI->_($obj->getError()); 
	}

	//filtro la data en funcion del tipo de rendicion
	if ($obj->timexp_type != "1"){
		$obj->timexp_start_time = "NULL";
		$obj->timexp_end_time = "NULL";
	}

	//si no es aplicado a tarea no puede contribuir a su completitud
	if ($obj->timexp_applied_to_type!="1"){
		$obj->timexp_contribute_task_completion = "0";
	}

	//si se carga algo y no es asignado a nada siempre es no facturable
	if ($obj->timexp_applied_to_type=="3"){
		$obj->timexp_billable="0";
	}
	//if ($obj->timexp_date) {
		$date = new CDate( $obj->timexp_date );
		$obj->timexp_date = $date->format( FMT_DATETIME_MYSQL );
	//}
     
	 $stime = $_POST[start_time];
	 $etime = $_POST[end_time];

	 $obj->timexp_start_time = $date->format("%Y-%m-%d ").$stime[$i].":00";
	 $obj->timexp_end_time = $date->format("%Y-%m-%d ").$etime[$i].":00";		
	/*

	if ($obj->timexp_type == "1" ){
		if ($obj->timexp_start_time)
			$obj->timexp_start_time = $date->format("%Y-%m-%d ").$_POST[start_time].":00";
		else 
			$obj->timexp_start_time = "0000-00-00 00:00:00";
		if ($obj->timexp_end_time)	
			$obj->timexp_end_time = $date->format("%Y-%m-%d ").$_POST[end_time];		
		else 
			$obj->timexp_end_time = "0000-00-00 00:00:00";
	}
	*/
   
  
	if (($msg = $obj->store())) {
		$msg_gral .= $AppUI->_($msg); 
	} else {
		if (!$data['timexp_id']){
			$data=array();
			$data["timexp_id"]=$obj->timexp_id;
			$data["timexp_status_id"]="0";
			$data["timexp_status_datetime"] = $obj->timexp_date;
			$data["timexp_status_value"] = $obj->timexp_last_status;
			$data["timexp_status_user"] = $obj->timexp_creator;

			$status = new CTimExpStatus();
			if (!$status->bind( $data )) {
				$msg_gral .= $AppUI->_($status->getError()); 
			}
				
			if (($msg = $status->store())) {
				$msg_gral .= $AppUI->_($msg); 
			}
		}
			
	}
  
} 


if ($msg_gral == ""){
	$AppUI->setMsg( 'inserted', UI_MSG_OK, true );		
}else{
	$AppUI->setMsg( $msg_gral, UI_MSG_ERROR, true );		
}

$AppUI->redirect($url);


?>