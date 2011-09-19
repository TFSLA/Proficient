<?php
function do_log($delegator_id, $mod_id, $AppUI, $act){
	$sql="INSERT INTO delegations_log 
		(dl_delegate_id, dl_user_id, dl_module_id, dl_description) VALUES
		('$delegator_id', '".$AppUI->user_id."', '$mod_id', '$act')";
	db_exec($sql);
	//echo "<br>$sql<br>\n";
}
?>