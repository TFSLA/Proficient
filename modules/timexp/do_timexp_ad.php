<?php /* TASKS $Id: do_timexp_ad.php,v 1.3 2009-07-13 16:12:36 nnimis Exp $ */

$del = dPgetParam( $_POST, 'del', 0 );
$url = dPgetParam( $_POST, 'next', "" ); 

echo "<pre>";var_dump($_POST);echo "</pre>";

$obj = new CTimExp();
$data_post = $_POST;


$dates = explode(",",$_POST["timexp_dates"]);
$values = explode(",",$_POST["timexp_hours"]);
$hora_inicio = explode(",",$_POST["hora_inicio"]);
$hora_final = explode(",",$_POST["hora_final"]);
$aplicado_a = explode(",",$_POST["tapplied_to_type"]);
$named = explode(",",$_POST["timexp_named"]);
$descrip = explode(",",$_POST["descripcion"]);
$tbillable = explode(",",$_POST["tbillable"]);
$company = explode(",",$_POST["company"]);


// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Times & Expenses' );
$msg_gral = "";
for($i=0; $i < count($dates); $i++){
	$data_post["timexp_date"] = $dates[$i];
	$data_post["timexp_value"] = $values[$i];
    
	if (!$obj->bind( $data_post )) {
		$msg_gral .= $AppUI->_($obj->getError()); 
	}
    
	$obj->timexp_applied_to_type = $aplicado_a[$i];

	/* Con el tipo y el id busco el nombre */

    $obj->timexp_billable = $tbillable[$i]; 

	if ($aplicado_a[$i]=="1")
	{
	  $obj->timexp_applied_to_id =  $named[$i];
	  $obj->timexp_contribute_task_completion = "1";

	  $query = "select task_name from tasks where task_id=".$named[$i];
	  $sql = mysql_query($query);
	  $nombre1 = mysql_fetch_array($sql);
	  $nombre = $nombre1[task_name];

	  $obj->timexp_company = $company[$i];

	}

	if ($aplicado_a[$i]=="2")
	{ 
	  $obj->timexp_applied_to_id =  $named[$i];
	  $obj->timexp_contribute_task_completion = "1";

	  $query = "select summary from btpsa_bug_table where id=".$named[$i];
	  $sql = mysql_query($query);
	  $nombre1 = mysql_fetch_array($sql);
	  $nombre = $nombre1[summary];

	  $obj->timexp_company = $company[$i];

	}
    
	if ($aplicado_a[$i]=="4")
	{ 
	  $obj->timexp_applied_to_id =  $named[$i];
	  $obj->timexp_contribute_task_completion = "1";

	  $query = "select description from project_todo where id_todo =".$named[$i];
	  $sql = mysql_query($query);
	  $nombre1 = mysql_fetch_array($sql);
	  $nombre = $nombre1[description];

	  $obj->timexp_company = $company[$i];

	}

	if ($aplicado_a[$i]=="3")
	{
	  $obj->timexp_applied_to_id = '0';

	  $nombre = $named[$i];
    
	  $obj->timexp_company = '0';
	  $obj->timexp_billable = '0'; 

	}
    echo "<br>Nombre: ".$nombre."<br>";

	$obj->timexp_name = $nombre;

	$obj->timexp_description = $descrip[$i];

	

	

	//filtro la data en funcion del tipo de rendicion
	if ($obj->timexp_type != "1"){
		$obj->timexp_start_time = "NULL";
		$obj->timexp_end_time = "NULL";
	}

	//si no es aplicado a tarea no puede contribuir a su completitud
	if ($obj->timexp_applied_to_type!="1"){
		$obj->timexp_contribute_task_completion = "0";
	}
    
	# Las hs internas tambien deben aprobarse
	//si se carga algo y no es asignado a nada siempre es no facturable
	/*if ($obj->timexp_applied_to_type=="3"){
		$obj->timexp_billable="0";
	}*/

	//if ($obj->timexp_date) {
		$date = new CDate( $obj->timexp_date );
		$obj->timexp_date = $date->format( FMT_DATETIME_MYSQL );
	//}
     
	 $stime = $hora_inicio[$i];
	 $etime = $hora_final[$i];

	 echo "etime: ".$etime."<br>";

	 if($etime=="24:00"){
		 $etime = "23:59:59";
	 }
	 else{
		 $etime = $etime.":00";
	 }
     
	 $obj->timexp_start_time = $date->format("%Y-%m-%d ").$stime.":00";
	 $obj->timexp_end_time = $date->format("%Y-%m-%d ").$etime;	

	 echo $obj->timexp_end_time;
     
	 // Una vez que acomode todo, hago las validaciones antes de ingresar algo en la bd //
      
	 $msg_error = array();

	 # Tipo 

	   if ($obj->timexp_applied_to_type=="")
	    {
		 $msg_error[type] = $AppUI->_('Applied to'); 
	    }

      # Name 
	    
	   if ($obj->timexp_name=="")
	    {
		 $msg_error[name] = $AppUI->_('Name');
	    }
      
	  # Value - No tiene que estar vacío y tiene que ser un número
        
	    $ti = settype($obj->timexp_value,double);
		$de = gettype($obj->timexp_value);

	   if (($obj->timexp_value=="") or (!(is_double($obj->timexp_value))))
	    {
		 $msg_error[value] = $AppUI->_($label_value); 
	    }
		
      # Fechas

		// Fecha de Inicio no puede ser superior a la fecha de fin
      /*  
		if ($obj->timexp_end_time < $obj->timexp_start_time)
		{
         $msg_error[fecha_inicio] = $AppUI->_('Start Time'); 
		}
        
		$ts = time();
		$fecha_actual = date("Ymd",$ts);

        //yyyy-mm-dd  hh:mm:ss

		$anio_fin = substr($obj->timexp_end_time,0,4); 
		$mes_fin = substr($obj->timexp_end_time,5,2); 
		$dia_fin = substr($obj->timexp_end_time,8,2);

		$fecha_de_fin = $anio_fin.$mes_fin.$dia_fin;

		$anio_inicio = substr($obj->timexp_start_time,0,4); 
		$mes_inicio = substr($obj->timexp_start_time,5,2); 
		$dia_inicio = substr($obj->timexp_start_time,8,2);

		$fecha_de_inicio = $anio_inicio.$mes_inicio.$dia_inicio;


		// No pueden ser superior al día actual

		if ($fecha_de_fin > $fecha_actual)
		{
         $msg_error[fecha_fin] = $AppUI->_('End Time'); 
		}

		if ($fecha_de_inicio > $fecha_actual)
		{
         $msg_error[fecha_inicio] = $AppUI->_('Start Time'); 
		}
       		
		# Horas 

		// Hora + value no puede superar las 24 hs

		$hora_inicio = substr($obj->timexp_start_time,11,2); 
        $min_inicio = substr($obj->timexp_start_time,14,2);

		$hora_fin = substr($obj->timexp_end_time,11,2); 
        $min_fin = substr($obj->timexp_end_time,14,2);
        
		
	    $temp = $obj->timexp_value + $hora_inicio; 

        if ($temp > 24)
		{
         $msg_error[fecha_inicio] = $AppUI->_('Start Time'); 
		}

		if (($temp == 24) and ($min_inicio > 0))
		{
         $msg_error[fecha_inicio] = $AppUI->_('Start Time'); 
		}

		$temp = $obj->timexp_value + $hora_fin; 

        if ($temp > 24)
		{
         $msg_error[fecha_fin] = $AppUI->_('End Time'); 
		}

		if (($temp == 24) and ($min_fin > 0))
		{
         $msg_error[fecha_fin] = $AppUI->_('End Time'); 
		}
         */
        $cant_errores = count($msg_error);

	 
	 /* -------------------------- Fin de las validaciones -------------------------------*/
	   
    if ($cant_errores == 0)
	{   
		echo "<br>Objeto<pre>";print_r($obj); echo "</pre>";
		
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

} 

	if ($cant_errores != 0)
	{   
		$msg_gral = "Stored failed. Error in field<br>";

		foreach($msg_error AS $tipo2 => $descr) 
				{
				$msg_gral .= "$msg_error[$tipo2]<br>";
				}
	}

if ($msg_gral == ""){
	$AppUI->setMsg( 'inserted', UI_MSG_OK, true );		
}else{

	$AppUI->setMsg( $msg_gral, UI_MSG_ERROR, true );		
}

//$AppUI->redirect('m=timexp&a=addtime&dialog=1&suppressLogo=1');
$AppUI->redirect('m=timexp&a=addtime');


?>