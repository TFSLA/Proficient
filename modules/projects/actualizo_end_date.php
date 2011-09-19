<?php
#####################################################################
#  Script para la actualización de la fecha de fin de los proyectos #
#  La fecha de fin de un proyecto debe ser la fecha de fin de la    #
#  tarea que termine última.                                        #
#####################################################################


// Traigo todos los projectos //

$query = "select project_id from projects";
$sql = mysql_query($query)or die(mysql_error());

 while ($vec=mysql_fetch_array($sql))
  {
     $query2 = "select task_end_date from tasks where task_project='$vec[project_id]' order by task_end_date desc";

	 $sql2 = mysql_query($query2);
						 
	 $fecha_fin = mysql_fetch_array($sql2);
   
     // Si el proyecto no tiene tareas, la fecha de fin es igual a la de inicio
	 if ($fecha_fin[0]=="")
	 {
	 $query3 = "select project_start_date from projects where project_id='$vec[project_id]'";
	 $sql3 = mysql_query($query3);
						 
	 $fecha_comienzo = mysql_fetch_array($sql3);
	 
     // Actualizo la base de datos //

	 $query4 = "UPDATE projects SET project_end_date = '$fecha_comienzo[0]' WHERE project_id='$vec[project_id]'";

	 $sql4 = mysql_query($query4);

	 }
	 else
	 {

	 // Actualizo la base de datos //

	 $query5 = "UPDATE projects SET project_end_date = '$fecha_fin[0]' WHERE project_id='$vec[project_id]'";

	 $sql5 = mysql_query($query5);

	 }
  }

  echo "<br><center><b>Se actualizaron todos los registros</b></center>";

?>