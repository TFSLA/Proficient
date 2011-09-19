<?php
	# Insert the bugnote into the database then redirect to the bug page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_private		= gpc_get_bool( 'private' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id );

	$f_bugnote_text = trim( $f_bugnote_text );
	$f_bugnote_text = str_replace("&#180;", "`", $f_bugnote_text);
	$f_bugnote_text = str_replace("&#168;", "''", $f_bugnote_text);
	$f_bugnote_text = str_replace("&acute;", "`", $f_bugnote_text);
	$f_bugnote_text = str_replace("&uml;", "''", $f_bugnote_text);

	# check for blank bugnote
	if ( !is_blank( $f_bugnote_text ) ) {
		bugnote_add( $f_bug_id, $f_bugnote_text, $f_private );
		email_bugnote_add( $f_bug_id );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
