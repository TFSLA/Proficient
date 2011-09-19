<?php
	# To delete a relationship we need to unsure that:
	# - User not anomymous
	# - Source bug exists and is not in read-only state (peer bug could not exist...)
	# - User that update the source bug and at least view the destination bug
	# - Relationship must exist
	# --------------------------------------------------------

	# MASC RELATIONSHIP

	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'relationship_api.php' );

	$f_rel_id = gpc_get_int( 'rel_id' );
	$f_bug_id = gpc_get_int( 'bug_id' );

	if ( current_user_is_anonymous()) {
		access_denied();
	}

	# user has access to update the bug...
	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

	# bug is not read-only...
	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	# retrieve the destination bug of the relationship
	$t_dest_bug_id = relationship_get_linked_bug_id( $f_rel_id, $f_bug_id );

	# user can access to the related bug at least as viewer, if it's exist...
	if ( bug_exists( $t_dest_bug_id )) {
		if ( !access_has_bug_level( VIEWER, $t_dest_bug_id ) ) {
			error_parameters( $t_dest_bug_id );
			trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
		}
	}

	if(!helper_ensure_confirmed( lang_get( 'delete_relationship_sure_msg' ), lang_get( 'delete_relationship_button' ) )) return;

	# delete relationship from the DB
	relationship_delete( $f_rel_id );

	# update bug last updated (just for the src bug)
	bug_update_date( $f_bug_id );

	# Add log line to the history of both bugs
	# Send email notification to the users addressed by both the bugs
	history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, '', $t_dest_bug_id );
	email_relationship_added( $f_bug_id );

	# Add log line to the history of both bugs
	# Send email notification to the users addressed by both the bugs
	if ( bug_exists( $t_dest_bug_id )) {
		history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, '', $f_bug_id );
		email_relationship_added( $t_dest_bug_id );
	}

	print_header_redirect_view( $f_bug_id );

	# MASC RELATIONSHIP
?>
