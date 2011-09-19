<?php
	# This file reopens a bug
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	access_ensure_can_reopen_bug( $f_bug_id );

	bug_reopen( $f_bug_id, $f_bugnote_text );

	print_successful_redirect_to_bug( $f_bug_id );
?>
