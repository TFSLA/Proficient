<?php
global $AppUI;
$bug_id = dPgetParam($_POST,"bug_id",0);
$hours = dPgetParam($_POST,"hours",0);
$billable = dPgetParam($_POST,"billable",0);
$name = dPgetParam($_POST,"hour_name",0);
$description = dPgetParam($_POST,"bugnote_text",0);

if (is_numeric($hours) && $hours > 0 && $bug_id > 0){
	$today = new CDate();
	
	$data = array();
	$data["timexp_id"] = "";
	$data["timexp_creator"] = $AppUI->user_id;
	$data["timexp_applied_to_type"] = 2;
	$data["timexp_applied_to_id"] = $bug_id;
	$data["timexp_type"] = 1;
	$data["timexp_date"] = $today->format( FMT_DATETIME_MYSQL );
	$data["timexp_start_time"] = "0000-00-00 00:00:00";
	$data["timexp_end_time"] = "0000-00-00 00:00:00";
	$data["timexp_contribute_task_completion"] = 0;
	$data["timexp_value"] = $hours;
	$data["timexp_billable"] = $billable;
	$data["timexp_name"] = $name;
	$data["timexp_description"] = $description;

	
	require_once( $AppUI->getModuleClass( 'timexp' ) );

	

	$obj = new CTimExp();
	
	if (!$obj->bind( $data )) {
		$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
		$AppUI->redirect();
	}
	
	// prepare (and translate) the module name ready for the suffix
	$AppUI->setMsg( 'Times & Expenses' );
	
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
	if ($obj->timexp_type == "1" ){
		$obj->timexp_start_time = $date->format("%Y-%m-%d ").$obj->timexp_start_time.":00";
		$obj->timexp_end_time = $date->format("%Y-%m-%d ").$obj->timexp_end_time.":00";		
	}

	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect($url);
	} else {

		$data=array();
		$data["timexp_id"]=$obj->timexp_id;
		$data["timexp_status_id"]="0";
		$data["timexp_status_datetime"] = $obj->timexp_date;
		$data["timexp_status_value"] = $obj->timexp_last_status;
		$data["timexp_status_user"] = $obj->timexp_creator;

		$status = new CTimExpStatus();
		if (!$status->bind( $data )) {
			$AppUI->setMsg( $status->getError(), UI_MSG_ERROR );
			$AppUI->redirect($url);
		}
			
		if (($msg = $status->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$AppUI->redirect($url);
		}
		$AppUI->setMsg( @$data['timexp_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );		
	}
	//$AppUI->redirect("m=timexp&a=view&timexp_id=$obj->timexp_id");
	//$AppUI->redirect($url);
	unset($obj);
	unset($data);


}

?>