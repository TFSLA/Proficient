<?php
	# Deletes the bug and re-directs to view_all_bug_page.php
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'delete_bug_threshold' ), $f_bug_id );

	if(!helper_ensure_confirmed( lang_get( 'delete_bug_sure_msg' ), lang_get( 'delete_bug_button' ) ))return;

	if(bug_delete( $f_bug_id ))
	{
	     # Si se borra la incidencia, borro tambien sus relaciones
	     $query = "DELETE FROM btpsa_bug_kb WHERE bug_id='".$f_bug_id."' ";
	     db_query($query);
	}

	print_successful_redirect( 'index.php?m=webtracking&a=view_all_bug_page' );
?>
