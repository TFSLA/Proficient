<?php 

$obj = new CTimExpStatus();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}


$date = new CDate( );
$obj->timexp_status_datetime = $date->format( FMT_DATETIME_MYSQL );

$AppUI->setMsg( 'Times & Expenses Status' ); 
if (($msg = $obj->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
} else {
	
	$objTimExp = new CTimExp();
	$objTimExp->load( $_POST["timexp_id"] );
	$objTimExp->timexp_last_status = $_POST["timexp_status_value"];
	if (($msg = $objTimExp->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}else{
		$AppUI->setMsg( 'inserted', UI_MSG_OK, true );
	}
}

$AppUI->redirect();
?>