<?php /*  Script para actualizar el presupuesto de hhrr de las tareas ya creadas */

global $AppUI;


/*--------- Formula -------------*/

/*
Costo hhrr = ( trabajo * unidades )/100 * costo_hhrr 
*/

/*-------------------------------*/

$query = "SELECT task_id, task_work FROM tasks WHERE task_work != 0";
$sql = mysql_query($query)or die(mysql_error());

while($vec=mysql_fetch_array($sql))
{  
	$work = $vec[task_work];

	// Por cada tarea traigo los usuarios y las unidades
    
	$query_u ="SELECT user_units, user_cost_per_hour FROM user_tasks WHERE task_id = '$vec[task_id]'";
	$sql_u = mysql_query($query_u)or die(mysql_error());
    
	$presupuesto = 0;
	
	while($vec_u = mysql_fetch_array($sql_u))
	{
	   $unidades = $vec_u[user_units];
	   $costo_hhrr = $vec_u[user_cost_per_hour];
      
       $presup_hhrr = ($work * $unidades)/100 * $vec_u[user_cost_per_hour];
	   
	   $presupuesto = $presup_hhrr + $presupuesto;
	}
    
    echo "&nbsp;&nbsp;Tarea: ".$vec[task_id]." Presupuesto hhrr total: ".$presupuesto."<br>";
	
	$query_insert = "UPDATE tasks SET task_target_budget_hhrr= '$presupuesto' WHERE task_id='$vec[task_id]' ";
	$sql_insert = mysql_query($query_insert)or die(mysql_error());
}

echo "<b>&nbsp;&nbsp;Actualización terminada</b>";