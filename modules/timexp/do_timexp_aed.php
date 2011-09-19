<?php /* TASKS $Id: do_timexp_aed.php,v 1.2 2009-06-25 18:05:16 nnimis Exp $ */

$del = dPgetParam( $_POST, 'del', 0 );
$url = dPgetParam( $_POST, 'next', "" ); 
$obj = new CTimExp();


if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Times & Expenses' );

// si el registro existe 
if ($_POST['timexp_id']){
	$texp = new CTimExp();
	$texp->load($_POST['timexp_id']);
	// y no lo puede editar
	if (!$texp->canEdit($msg) && $del == 0){
		$AppUI->setMsg( "You have no permission to uptade the record", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (!$texp->canDelete($msg) && $del == 1){
		$AppUI->setMsg( "You have no permission to delete the record", UI_MSG_ERROR );
		$AppUI->redirect();
	}	
	unset ($texp);
}

if ($del) {
	$obj->load($_POST['timexp_id']);
	$obj->timexp_value = 0;
	//$nxtscr = dPgetParam( $_POST, 'nxtscr', "-1" );
	$nxtscr = dPgetParam( $_POST, 'nxtscr', "m=timexp&a=mysheets" );
	
	if ($msg = $obj->store()){
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();	
		
	}else{
		if (($msg = $obj->delete())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$AppUI->redirect();
		
		} else {
			$AppUI->setMsg( "deleted", UI_MSG_ALERT );
			$AppUI->redirect($nxtscr);
		}	
	}
} else {
	//filtro la data en funcion del tipo de rendicion
	if ($obj->timexp_type != "1"){
		$obj->timexp_start_time = "NULL";
		$obj->timexp_end_time = "NULL";
	}
	
	if($obj->timexp_expense_category >= 0)
		$obj->timexp_description = "";

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
		if ($obj->timexp_start_time)
			$obj->timexp_start_time = $date->format("%Y-%m-%d ").$obj->timexp_start_time.":00";
		else 
			$obj->timexp_start_time = "0000-00-00 00:00:00";
		if ($obj->timexp_end_time == "00:00"){	
			$date->addDays(1);
			$obj->timexp_end_time = $date->format("%Y-%m-%d ").$obj->timexp_end_time.":00";	
		}else if ($obj->timexp_end_time)	
			$obj->timexp_end_time = $date->format("%Y-%m-%d ").$obj->timexp_end_time.":00";			else 
			$obj->timexp_end_time = "0000-00-00 00:00:00";
	}

    // Aplicado a tareas //
	if ($obj->timexp_applied_to_type=="1")
	{
		$obj->timexp_company = $_POST['idcompany'];
        $obj->timexp_contribute_task_completion = "0";
		$obj->timexp_applied_to_id = $_POST['task_id'];

		  $query = "select task_name from tasks where task_id=".$_POST['task_id'];
		  $sql = mysql_query($query);
		  $nombre1 = mysql_fetch_array($sql);
		  $nombre = $nombre1[task_name];
	}

	 // Aplicado a bugs //
	if ($obj->timexp_applied_to_type=="2")
	{
		$obj->timexp_company = $_POST['id_company_bug'];
        $obj->timexp_contribute_task_completion = "0";
		$obj->timexp_applied_to_id = $_POST['bug_id'];

		  $query = "select summary from btpsa_bug_table where id=".$_POST['bug_id'];
		  $sql = mysql_query($query);
		  $nombre1 = mysql_fetch_array($sql);
		  $nombre = $nombre1[summary];

	}

	 // Aplicado a tareas //
	if ($obj->timexp_applied_to_type=="4")
	{
		$obj->timexp_company = $_POST['idcompany'];
        $obj->timexp_contribute_task_completion = "0";
		$obj->timexp_applied_to_id = $_POST['id_todo'];

		  $query = "select description from project_todo where id_todo=".$_POST['id_todo'];
		  $sql = mysql_query($query);
		  $nombre1 = mysql_fetch_array($sql);
		  $nombre = $nombre1[description];
	}

	// Aplicado a nothing //
	if ($obj->timexp_applied_to_type=="3")
	{
		$obj->timexp_company = "0";
        $obj->timexp_contribute_task_completion = "0";
		$obj->timexp_applied_to_id =  "0";

		$nombre = $_POST[timexp_name2];

	}
    
	$obj->timexp_name = $nombre;
    
   
  
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect($url);
	} else {
		if (!$_POST['timexp_id']){
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
		}
		$AppUI->setMsg( @$_POST['timexp_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );		
	}
	$AppUI->redirect($url);
}

?>