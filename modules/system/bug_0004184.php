<? /*###################################################################################
   # Este script corrige la incidencia:    
   # http://proficient.tfsla.com/index.php?m=webtracking&a=bug_view_page&bug_id=0004184
   # [Tareas] Error en recalculo de fecha de fin
   #####################################################################################*/

// Traigo todos id de los calendarios que tiene registros con cantidad de hs en 0 para dias laborales
$query = "SELECT distinct(calendar_id) FROM calendar_days WHERE calendar_day_working ='1' AND calendar_day_hours = '0'";
$sql = db_exec($query);


// Recorro el vec con los id y voy cargandolos en el objeto calendar
while($vec=mysql_fetch_array($sql))
{	
	$obj = new CCalendar();
	$obj->load($vec['calendar_id']);
	$obj->loadCalendarDays();
	
	/*echo "<pre>";
	   print_r($obj);
	echo "</pre>";  */
	
	// Recorro los dias de la semana
	for ($i=1;$i<=7;$i++){
		
		// Por cada dia recorro los turnos y hago la sumatoria de dias laborables
		for($j=1;$j<=5;$j++){
			
			$cal_to_time = "calendar_day_to_time".$j;
			$cal_from_time = "calendar_day_from_time".$j;
			
			$hs_to = $obj->_calendar_days[$i]->$cal_to_time;
			$hs_from = $obj->_calendar_days[$i]->$cal_from_time;

			$ts_to = (substr($hs_to,11,2) * 3600 )+  (substr($hs_to,14,2) * 60);
            $ts_from = (substr($hs_from,11,2) * 3600 )+  (substr($hs_from,14,2) * 60);
          
			$hs[$obj->_calendar_days[$i]->calendar_day_id] += ($ts_to - $ts_from) / 3600;
		}
		
		// Por cafa dia guardo la cantidad de hs laborables
		$query_update =  "UPDATE calendar_days SET calendar_day_hours = '".$hs[$obj->_calendar_days[$i]->calendar_day_id]."' WHERE calendar_day_id = '".$obj->_calendar_days[$i]->calendar_day_id."' ";
		
		$sql_update = db_exec($query_update);
		
	}
}


?>
 
Este script corrige la incidencia:    
<br>
<a href="http://proficient.tfsla.com/index.php?m=webtracking&a=bug_view_page&bug_id=0004184" target="_blank">[Tareas] Error en recalculo de fecha de fin <a>
