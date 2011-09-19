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
	$f_resolution	= gpc_get_int( 'resolution', FIXED );
	$f_duplicate_id	= gpc_get_int( 'duplicate_id', null );
	$f_close_now	= gpc_get_bool( 'close_now' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );

	# make sure the bug is not being marked as a duplicate of itself
	if ( $f_duplicate_id === $f_bug_id ) {
		trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
	}

	$resolve = bug_resolve( $f_bug_id, $f_resolution, $f_bugnote_text, $f_duplicate_id );
            
	// Si la incidencia se resolvio, manda los mails a los no usuario (followup mails)
	if($resolve){ 
                   require_once( 'bug_followup.php' ); 
	       send_followup_mails( $f_bug_id, $f_bugnote_text );
	}
	
	if ( $f_close_now ) {
		bug_set_field( $f_bug_id, 'status', CLOSED );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
