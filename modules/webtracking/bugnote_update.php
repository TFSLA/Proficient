<?php
	# Update bugnote data then redirect to the appropriate viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path . 'date_api.php' );
?>
<?php
	$f_bugnote_id	= gpc_get_int( 'bugnote_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );
            $f_bugnote_text = str_replace("&#180;", "`", $f_bugnote_text);
            $f_bugnote_text = str_replace("&#168;", "''", $f_bugnote_text);
            $f_bugnote_text = str_replace("&acute;", "`", $f_bugnote_text);
            $f_bugnote_text = str_replace("&uml;", "''", $f_bugnote_text);
	
	# Check if the current user is allowed to edit the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_reporter_id ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) )) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}
	
	# Check if the bug has been resolved
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	if ( bug_get_field( $t_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' ) ) {
		trigger_error( ERROR_BUG_RESOLVED_ACTION_DENIED, ERROR );
	}
	
	$f_bugnote_text = trim( $f_bugnote_text ) . "\n\n";
	
	$dateBugNote = new CDate();
	
	$f_bugnote_text	.= lang_get( 'edited_on' ) . $dateBugNote->format($AppUI->getPref('SHDATEFORMAT')." ".$AppUI->getPref('TIMEFORMAT'));

	bugnote_set_text( $f_bugnote_id, $f_bugnote_text );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
