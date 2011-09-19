<?php
	# Remove the bugnote and bugnote text and redirect back to
	# the viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );

	# Check if the current user is allowed to delete the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_reporter_id ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		access_ensure_bugnote_level( config_get( 'delete_bugnote_threshold' ), $f_bugnote_id );
	}

	if(!helper_ensure_confirmed( lang_get( 'delete_bugnote_sure_msg' ),
							 lang_get( 'delete_bugnote_button' ) ))return;

	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

	bugnote_delete( $f_bugnote_id );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
