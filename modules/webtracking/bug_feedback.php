<?php
	# This file sets the bug to the chosen resolved state and adds a
	#  bugnote giving a reason for the resolution
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );
;

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );


	bug_feedback( $f_bug_id, $f_bugnote_text );

	if ( $f_close_now ) {
		bug_set_field( $f_bug_id, 'status', CLOSED );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
