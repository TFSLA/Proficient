<?php /* TASKS $Id: do_timesheetstatus_aed.php,v 1.2 2009-07-14 15:29:01 nnimis Exp $ */
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;

$ts_id_list = array_keys($_POST["timesheetstatus_status"]);
$msg=NULL;


if (count($ts_id_list)){
	for($i=0;$i<count($ts_id_list);$i++){
		$timesheet_id=$ts_id_list[$i];
		$next_status = $_POST["timesheetstatus_status"][$timesheet_id];
		
		if(isset($_POST["timesheetstatus_description"][$timesheet_id])){
        	$description = $_POST["timesheetstatus_description"][$timesheet_id];
		}

		if(isset($_POST["description"])){
        	$description = $_POST["description"];
		}

		//si cambia el estado
		if ($next_status!="-1"){
			$date = new CDate();		
			
			$obj = new CTimesheet();
			$obj->load($timesheet_id);
			
			$err = $obj->changeStatus($next_status, $description);
			if ($err!=NULL){
				$msg = $msg == NULL ? $err : $msg."<br/>".$err;
			}
			
			if ($next_status=="2" || $next_status=="4" )
			{
			   // Si lo desaprueba, paso las hs a estado UNSET para evitar confuciones
			   $query_timexp="UPDATE timexp SET timexp_last_status= '0',timexp_timesheet= NULL WHERE timexp_timesheet ='".$timesheet_id."'";
			   $sql_timexp = db_exec($query_timexp);
			}
			
			if ($next_status=="3")
			{
			/* Despues de cambiar el estado, calculo el costo */
            
			// Con el user traigo el costo x hora
			$query = "select user_cost_per_hour from users where user_id='$_POST[timesheetstatus_user]'";
            $sql = mysql_query($query);

			$costo = mysql_fetch_array($sql);
            $valor_hora = $costo[0];
			
			// Traigo la cantidad de hs aprobadas
			$query2 = "select timexp_id, timexp_value from timexp where timexp_timesheet='$timesheet_id'";
            $sql2 = mysql_query($query2);

			    while($cant_h = mysql_fetch_array($sql2))
				{
				 $costo = $valor_hora * $cant_h[1];

				 $query3 = "UPDATE timexp SET timexp_cost_ap = '$costo' where timexp_id = '$cant_h[0]'";
				 $sql3 = mysql_query($query3)or die(mysql_error());
				}

            
			// Horas puestas a nothing
			$query3 = "select timesheet_start_date, timesheet_end_date from timesheets where timesheet_id='$timesheet_id'";
            $sql3 = mysql_query($query3);

			$fechas = mysql_fetch_array($sql3);
			$fecha_inicio = $fechas[0];
			$fecha_fin = $fechas[1];

			$query4 = "select timexp_id, timexp_value from timexp where timexp_start_time >= '$fecha_inicio' and timexp_end_time <= '$fecha_fin' and timexp_creator='$_POST[timesheetstatus_user]' and timexp_applied_to_type='3' ";
			$sql4 = mysql_query($query4);

			while($cant_hn= mysql_fetch_array($sql4))
				{
				 $costo = $valor_hora * $cant_hn[1];

				 $query3 = "UPDATE timexp SET timexp_cost_ap = '$costo' where timexp_id = '$cant_hn[0]'";
				 $sql3 = mysql_query($query3)or die(mysql_error());
				}
			}
			
		}
		
	}
	if ($msg===NULL){
		$AppUI->setMsg( 'updated' , UI_MSG_OK, true );		
	}else{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	//$AppUI->redirect();
}

?>