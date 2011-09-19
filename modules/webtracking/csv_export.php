<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_path . 'date_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# check to see if the cookie does not exist
	if ( !filter_is_cookie_valid() ) {
		print_header_redirect( 'index.php?m=webtracking&a=view_all_set&type=0' );
	}

	# Send headers to browser to activate mime loading
	header( 'Content-Type: application/octet-stream; name=' . config_get( 'page_title' ) . '.csv' );
	header('charset= utf-8');
	//header( 'Content-Transfer-Encoding: base64' );
	header( 'Content-Disposition: attachment; filename=' . config_get( 'page_title' ) . '.csv' );

	$t_page_number = 1;
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;

	$rows = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );

	echo lang_get('email_project') . ',' . config_get('page_title') . "\r\n\r\n";
	echo lang_get( 'priority' ) . ',' .
			lang_get( 'id' ) . ',' .
			lang_get( 'severity' ) . ',' .
			lang_get( 'status' ) . ',' .
			lang_get( 'version' ) . ',' .
			lang_get( 'assigned_to' ) . ',' .
			lang_get( 'reporter' ) . ',' .
			lang_get( 'updated' ) . ',' .
			lang_get( 'summary' ) . "\r\n";

	for ( $i=0 ; $i < sizeof($rows) ; $i++ ) {
		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$t_last_updated		= format_date( config_get( 'short_date_format' ), $v_last_updated );
		$t_priority			= get_enum_element( 'priority', $v_priority );
		$t_severity			= get_enum_element( 'severity', $v_severity );
		$t_status			= get_enum_element( 'status', $v_status );
		$t_hander_name		= user_get_name( $v_handler_id );
		$t_reporter_name	= user_get_name( $v_reporter_id );
		//$v_summary			= string_display_links( $v_summary );

		//$v_summary			= utf8_encode($v_summary);

		$data = "$t_priority,$v_id,$t_severity,$t_status,$v_version,$t_hander_name,$t_reporter_name,$t_last_updated,\"$v_summary\"\r\n";
		print $data;
	}

	exit;

?>
