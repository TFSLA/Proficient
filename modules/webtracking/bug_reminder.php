<?php
	# This page allows an authorized user to send a reminder by email to another user
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'email_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_to			= gpc_get_int_array( 'to' );
	$f_body			= gpc_get_string( 'body' );

	access_ensure_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );

	# Automically add recipients to monitor list if they are above the monitor
	# threshold, option is enabled, and not reporter or handler.
	foreach ( $f_to as $t_recipient )
	{
		if ( ON == config_get( 'reminder_recipents_monitor_bug' ) &&
			access_has_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id ) &&
			!bug_is_user_handler( $f_bug_id, $t_recipient ) && 
			!bug_is_user_reporter( $f_bug_id, $t_recipient ) ) {
			bug_monitor( $f_bug_id, $t_recipient );
		}
	}

	$result = email_bug_reminder( $f_to, $f_bug_id, $f_body );

	# Add reminder as bugnote if store reminders option is ON.
	if ( ON == config_get( 'store_reminders' ) ) {
		$t_body = lang_get( 'reminder_sent_to' ) . ' ' .
					( implode( ', ', $result ) );
		if ( !is_blank( $f_body ) ) {
			$t_body .= "\n\n" . $f_body;
		}

		bugnote_add( $f_bug_id, $t_body );
	}
	$f_redirect_url =  string_get_bug_view_url( $f_bug_id );
	$AppUI = &$_SESSION['AppUI'];
	$AppUI->setMsg(lang_get( 'operation_successful' ), UI_MSG_OK);
	$redir = substr($f_redirect_url, strpos($f_redirect_url,"?")+1);
	$AppUI->redirect($redir);

	html_page_top1();
	html_meta_redirect( string_get_bug_view_url( $f_bug_id ) );
	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), lang_get( 'proceed' ) );
?>
</div>
<?php html_page_bottom1( __FILE__ ) ?>
