<?php
/* Script para borrar registros de timexp_ts cuyas tareas o todos fueron borradas */

# Recorro los  timesheets y traigo los projectos

$query_ts = "SELECT timesheet_id, timesheet_project FROM timesheets";
$sql_timesheet =  db_exec($query_ts);

while($vec= mysql_fetch_array($sql_timesheet))
{
         
          # Por cada projecto traigo las tareas, 
          $sql_tsk = "SELECT task_id FROM tasks WHERE task_project = '".$vec['timesheet_project']."' ";
          $tsks =  db_loadColumn($sql_tsk);
          
          echo "<pre>"; print_r($tsks); echo "</pre>";

          # Borro los registros de timexp_ts asociados a tareas  
          if(count($tsks)>0){
	          $del_tsk = "DELETE FROM timexp_ts WHERE timexp_ts_timesheet = '".$vec['timesheet_id']."' AND timexp_ts_applied_to_type = '1' AND timexp_ts_applied_to_id NOT IN  ('" . implode( "','", $tsks ) . "')";
	          echo "$del_tsk <br>";     
	          $sql_del_tsk = db_exec($del_tsk);
          }else{
          	        $del_tsk = "DELETE FROM timexp_ts WHERE timexp_ts_timesheet = '".$vec['timesheet_id']."' AND timexp_ts_applied_to_type = '1' ";
	         echo "$del_tsk <br>";     
	         $sql_del_tsk = db_exec($del_tsk);
          }
          
          # Por cada projecto traigo los todos, 
          $sql_todo = "SELECT id_todo FROM project_todo WHERE project_id ='".$vec['timesheet_project']."' ";
          $todo =  db_loadColumn($sql_todo);
          
          echo "<pre>"; print_r($todo); echo "</pre>";
          
           # Borro los registros de timexp_ts asociados a todos con 
           if(count($todo)>0){
	          $del_todo = "DELETE FROM timexp_ts WHERE timexp_ts_timesheet = '".$vec['timesheet_id']."' AND timexp_ts_applied_to_type = '4' AND timexp_ts_applied_to_id NOT IN  ('" . implode( "','", $todo ) . "')";
	          echo "$del_todo <br>";     
	          $sql_del_todo = db_exec($del_todo);
           }else{
           	        $del_todo = "DELETE FROM timexp_ts WHERE timexp_ts_timesheet = '".$vec['timesheet_id']."' AND timexp_ts_applied_to_type = '4' ";
	        echo "$del_todo <br>";     
	        $sql_del_todo = db_exec($del_todo);
           }
        
}

// Recorro timesheets , reviso si tiene registros creados en timexp
$query_ts = "select  timesheet_id
		       , sum(if(te.timexp_ts_billable=1, te.timexp_ts_value,0)) totbil
		       , sum(if(te.timexp_ts_billable=0, te.timexp_ts_value,0)) totnobil
	         from timesheets ts 
	         left outer join timexp_ts te on ts.timesheet_id = te.timexp_ts_timesheet left join users u on u.user_id = ts.timesheet_user 
	         left join projects p on p.project_id = ts.timesheet_project 
	         where 1=1
	                 group by timesheet_id
	                 order by timesheet_date desc";
$sql_timesheet =  db_exec($query_ts);

while($vec= mysql_fetch_array($sql_timesheet))
{
	if ($vec['totbil']==0 && $vec['totnobil']==0)
	{
		$del_ts = "DELETE FROM timesheets  WHERE timesheet_id ='".$vec['timesheet_id']."' ";
		db_exec($del_ts);
	}
}

?>
