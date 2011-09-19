<?php /* TASKS $Id: do_timesheet_aed.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;

$annul = dPgetParam( $_POST, 'annul', 0 );

//echo "<pre>";var_dump($_POST);echo "</pre>";
// hago que la fecha de fin del timesheet sea el ultimo momento del dia
$_POST["timesheet_end_date"] = $_POST["timesheet_end_date"]."235959";

$tsobj = new CTimesheet();

if (!$tsobj->bind( $_POST )) {
	$AppUI->setMsg( $tsobj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg(  $name_sheets[$tsobj->timesheet_type] );


if ($annul) {
	$tsobj->load($tsobj->timesheet_id);
	if (! $msg = $tsobj->annul($_POST["description"])){
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	$AppUI->setMsg( 'annulled', UI_MSG_OK, true );	
} else {


	$date = new CDate( $tsobj->timesheet_date );
	$tsobj->timesheet_date = $date->format( FMT_DATETIME_MYSQL );
	
	if (( $msg = $tsobj->existsUnassignedTimexp())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}else {
		if (($msg = $tsobj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$AppUI->redirect();
		} else {
			if (!$_POST['timesheet_id']){

				$data=array();
				$data["timesheetstatus_timesheet"]=$tsobj->timesheet_id;
				$data["timesheetstatus_id"]="";
				$data["timesheetstatus_date"] = $tsobj->timesheet_date;
				$data["timesheetstatus_status"] = "0";
				$data["timesheetstatus_user"] = $tsobj->timesheet_user;
				$data["timesheetstatus_description"] = "";

				$status = new CTimesheetStatus();
				if (!$status->bind( $data )) {
					$AppUI->setMsg( $status->getError(), UI_MSG_ERROR );
					$AppUI->redirect();
				}

				if (($msg = $status->store())) {
					$AppUI->setMsg( $msg, UI_MSG_ERROR );
					$AppUI->redirect();
				}

				set_time_limit(0); //cambio el tiempo de ejecucion para que pueda realizar los calculos
				if (($msg = $tsobj->assignTimexp())) {
					$AppUI->setMsg( $msg, UI_MSG_ERROR );
					$AppUI->redirect();
				}
				set_time_limit(30); //restablesco el tiempo de ejecucion en 30''
			}
			$AppUI->setMsg( @$_POST['timesheet_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );	
		}
	}
	$AppUI->redirect("m=timexp&a=viewsheet&timesheet_id=$tsobj->timesheet_id");
	
	//$AppUI->redirect();
}

?>