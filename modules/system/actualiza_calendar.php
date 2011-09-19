<?php /*  Script para asignar calendario a los usuarios que no tengan asignado uno, por defecto se toma el de sytema 15-12-2006 */
global $AppUI;

$query = "SELECT user_id FROM users WHERE user_type <> '5'";

$sql = mysql_query($query);

$registros_t = mysql_num_rows($sql);
$registros = 0;

while($vec=mysql_fetch_array($sql))
{
	$id_user = $vec[user_id];

	$query_cal = "SELECT count(distinct(calendar_id)) FROM calendar WHERE calendar_user = '$id_user' and calendar_status='1' ";
    $sql_cal = mysql_query($query_cal);
    
	$result = mysql_fetch_array($sql_cal);
	$cant = $result[0];

	if($cant != "1")
	{     
		  $registros = $registros + 1;

		  // Si tiene mas de un calendario activo, los inactivo a todos y le asigno uno nuevo
		  if($cant > "1")
		  { 
		   $query_inac = "UPDATE calendar SET calendar_status = '0' WHERE calendar_user = '$id_user' ";
		   db_exec( $query_inac );
		  }

	       $sql_ins = "INSERT INTO calendar (calendar_name, calendar_company, calendar_project, calendar_user, calendar_propagate, calendar_status) VALUES ('Default', 0, 0, $id_user ,  1, 1)";
		   db_exec( $sql_ins );
		   $id_c = mysql_insert_id();
			
		   $query2 = "SELECT calendar_id FROM calendar WHERE calendar_company ='0' AND calendar_project='0' AND calendar_user='0' AND calendar_status='1' ";
           $sql2 = mysql_query($query2);
           $data = mysql_fetch_array($sql2);

		   $default_calendar = $data[calendar_id];

		   $query3 = "SELECT * FROM calendar_days WHERE calendar_id = '".$default_calendar."' ";
		   $sql3 = mysql_query($query3);

			while($vec2 = mysql_fetch_array($sql3))
			{ 
			          
              if($vec2[calendar_day_from_time1]!="")
			  {
			  $day_from_time1 = "'".$vec2[calendar_day_from_time1]."'";
			  $day_to_time1 = "'".$vec2[calendar_day_to_time1]."'";
			  
			  }
			  else{
			  $day_from_time1 = "NULL";
			  $day_to_time1 = "NULL";
			  }

			  if($vec2[calendar_day_from_time2]!="")
			  {
			  $day_from_time2 = "'".$vec2[calendar_day_from_time2]."'";
			  $day_to_time2 ="'". $vec2[calendar_day_to_time2]."'";
			  }
			  else{
			  $day_from_time2 = "NULL";
			  $day_to_time2 = "NULL";
			  }

			  if($vec2[calendar_day_from_time3]!="")
			  {
			  $day_from_time3 = "'".$vec2[calendar_day_from_time3]."'";
			  $day_to_time3 = "'".$vec2[calendar_day_to_time3]."'";
			  }
			  else{
			  $day_from_time3 = "NULL";
			  $day_to_time3 = "NULL";
			  }

			  if($vec2[calendar_day_from_time4]!="")
			  {
			  $day_from_time4 = "'".$vec2[calendar_day_from_time4]."'";
			  $day_to_time4 = "'".$vec2[calendar_day_to_time4]."'";
			  }
			  else{
			  $day_from_time4 = "NULL";
			  $day_to_time4 = "NULL";
			  }

			  if($vec2[calendar_day_from_time5]!="")
			  {
			  $day_from_time5 = "'".$vec2[calendar_day_from_time5]."'";
			  $day_to_time5 = "'".$vec2[calendar_day_to_time5]."'";
			  }
			  else{
			  $day_from_time5 = "NULL";
			  $day_to_time5 = "NULL";
			  }

			  $query4 = "INSERT INTO calendar_days 
			  (calendar_day_id, calendar_id, calendar_day_day, calendar_day_working, calendar_day_from_time1, calendar_day_to_time1, calendar_day_from_time2, calendar_day_to_time2, calendar_day_from_time3, calendar_day_to_time3, calendar_day_from_time4, calendar_day_to_time4, calendar_day_from_time5, calendar_day_to_time5, calendar_day_hours) 
			  VALUES (NULL, '$id_c', '".$vec2[calendar_day_day]."', '".$vec2[calendar_day_working]."', $day_from_time1, $day_to_time1, $day_from_time2, $day_to_time2, $day_from_time3, $day_to_time3, $day_from_time4, $day_to_time4, $day_from_time5, $day_to_time5, '".$vec2[calendar_day_hours]."')";
              
			  $sql4 = mysql_query($query4);
			}
	}
    
}


echo "&nbsp;&nbsp;<br><b>Se actualizaron ".$registros." registros de un total de ".$registros_t." usuarios <b>";
?>

