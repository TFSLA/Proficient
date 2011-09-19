<?php 
global $timexp_status;
$timexp_id_list = dPgetParam( $_POST, 'timexp_id_list', "" );
$week_status_value = intval(dPgetParam( $_POST, 'week_status_value', 0 ));

$timexp_list = explode(",", $timexp_id_list);

if (count($timexp_list) && $week_status_value > 0){
	$msg="";
	foreach($timexp_list as $timexp_id){
		if (isset($timexp_status[$week_status_value]) && !$msg){
			$data = $_POST;
			$data["timexp_id"]=$timexp_id;
			$data["timexp_status_value"]=$week_status_value;
			$data["timexp_status_id"]="";

			// instancio un objeto del reg seleccionado
			$objTimExp = new CTimExp();
			$objTimExp->load( $data["timexp_id"] );

			//verifico que el usuario pueda actualizar el registro
			if ($objTimExp->canSupervise()){
				$obj = new CTimExpStatus();
				// enlazo los datos con el objeto
				if (!$obj->bind( $data )) {
					$msg = $obj->getError();
				}else{
					$date = new CDate( );
					$obj->timexp_status_datetime = $date->format( FMT_DATETIME_MYSQL );
					// si no hay errores al guardar
					if (!($msg = $obj->store())) {
						//actualizo el ultimo estado
						$objTimExp->timexp_last_status = $data["timexp_status_value"];
						$msg = $objTimExp->store();
					}
				}
			}

			//vacio las variables
			unset($obj);
			unset($date);
			unset($objTimExp);
			unset($data);
		}

	}
	
	$AppUI->setMsg( 'Times & Expenses Status' ); 
	if ($msg) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}else{
		$AppUI->setMsg( 'updated', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>