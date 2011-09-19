<?php
	# Delete a file from a bug and then view the bug
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php
	$f_file_id = gpc_get_int( 'file_id' );

	$t_bug_id = file_get_field( $f_file_id, 'bug_id' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id );

	file_delete( $f_file_id );

	print_header_redirect_view( $t_bug_id );
?>
