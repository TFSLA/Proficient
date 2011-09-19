<?

$sql = "/*** Propietario de Tarea pueden hacer todo (-1)***/
		select 	distinct  ta.task_id, item_id, -1 permission_value from task_permission_items ti, tasks ta where (ta.task_owner = 10) and item_id in (1, 2, 3, 4, 5) and	(ta.task_project = 0 or 0 = 0) and (ta.task_access = 0 or 0 = 0) and (ti.item_id = 0 or 0 = 0) and (ta.task_id = 0 or 0 = 0) order by task_id,  item_id";
echo "<pre>$sql</pre>";

$prmTaskOwner=db_loadList($sql);
echo "<pre>";
    print_r($prmTaskOwner);
echo "</pre>";

?>