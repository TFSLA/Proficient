<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php 

	$f_type					= gpc_get_int( 'type', -1 );
	$f_print				= gpc_get_bool( 'print' );

	$f_show_category		= gpc_get_string( 'show_category', '' );
	$f_show_severity		= gpc_get_string( 'show_severity', '' );
	$f_show_status			= gpc_get_string( 'show_status', '' );
	$f_per_page				= gpc_get_int( 'per_page', -1 );
	$f_highlight_changed	= gpc_get_string( 'highlight_changed', config_get( 'default_show_changed' ) );
	$f_hide_closed			= gpc_get_bool( 'hide_closed' );
	$f_hide_resolved			= gpc_get_bool( 'hide_resolved' );
	$f_reporter_id			= gpc_get_string( 'reporter_id', '' );
	$f_handler_id			= gpc_get_string( 'handler_id', '' );
	# sort direction
	$f_sort					= gpc_get_string( 'sort', 'last_updated' );
	$f_dir					= gpc_get_string( 'dir', 'DESC' );
	# date values
	$f_start_month			= gpc_get_string( 'start_month', date( 'm' ) );
	$f_end_month			= gpc_get_string( 'end_month', date( 'm' ) );
	$f_start_day			= gpc_get_string( 'start_day', 1 );
	$f_end_day				= gpc_get_string( 'end_day', date( 'd' ) );
	$f_start_year			= gpc_get_string( 'start_year', date( 'Y' ) );
	$f_end_year				= gpc_get_string( 'end_year', date( 'Y' ) );
	$f_search				= gpc_get_string( 'search', '' );
	
	$f_deadline_rel			= gpc_get_string( 'deadline_rel', '' );
	$f_date_deadline		= gpc_get_string( 'date_deadline', '' );

	$f_date_from_rel		=gpc_get_string( 'date_from_rel', '' );
	$f_date_from				=gpc_get_string( 'date_from', '' );
	$f_date_to_rel		=gpc_get_string( 'date_to_rel', '' );
	$f_date_to				=gpc_get_string( 'date_to', '' );

	$f_show_version			= gpc_get_string( 'show_version', '' );
	$f_show_n_hours			=	gpc_get_string ( 'show_n_hours', '' );

	if ( $f_hide_closed ) {
		$f_hide_closed = 'on';
	}

	if ( $f_hide_resolved ) {
		$f_hide_resolved = 'on';
	}

	if ( $f_type < 0 ) {
		print_header_redirect( 'index.php?m=webtracking&a=view_all_bug_page' );
	}

	if ( ON == config_get( 'hide_closed_default' ) ) {
		$t_hide_closed_default = 'on';
	} else {
		$t_hide_closed_default = '';
	}

	# show bugs per page
	if ( $f_per_page < 0 ) {
		$f_per_page = config_get( 'default_limit_view' );
	}


/*   array contents
     --------------
	 0: version
	 1: $f_show_category
	 2: $f_show_severity
	 3: $f_show_status
	 4: $f_per_page
	 5: $f_highlight_changed
	 6: $f_hide_closed
	 7: $f_reporter_id
	 8: $f_handler_id
	 9: $f_sort
	10: $f_dir
	11: $f_start_month
	12: $f_start_day
	13: $f_start_year
	14: $f_end_month
	15: $f_end_day
	16: $f_end_year
	17: $f_search
	18: $f_hide_resolved
*/
	# Set new filter values.  These are stored in a cookie
	$t_view_all_cookie = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
	$t_setting_arr	= explode( '#', $t_view_all_cookie );

    print_r($t_setting_arr);

	if ( isset($t_setting_arr[5]) ) {
		check_varset( $f_highlight_changed, $t_setting_arr[5] );
	} else {
		check_varset( $f_highlight_changed, config_get( 'default_show_changed' ) );
	}

	$t_cookie_version = config_get( 'cookie_version' );
	$t_default_show_changed = config_get( 'default_show_changed' );
    
	

	switch ( $f_type ) {
		# New cookie
		case '0':
				$t_settings_string = "$t_cookie_version".
									"#any#any".
									"#any#$f_per_page".
									"#$t_default_show_changed#$t_hide_closed_default".
									"#any#any".
									"#last_updated#DESC".
									"#$f_start_month#$f_start_day".
									"#$f_start_year#$f_end_month".
									"#$f_end_day#$f_end_year".
									"#$f_search#$f_hide_resolved".
									"#$f_deadline_rel#$f_date_deadline".
									"#$f_date_from_rel#$f_date_from".
									"#$f_date_to_rel#$f_date_to".
									"#any".
									"#$f_show_n_hours";
				break;
		# Update filters
		case '1':
				$t_settings_string = "$t_cookie_version".
									"#$f_show_category#$f_show_severity".
									"#$f_show_status#$f_per_page".
									"#$f_highlight_changed#$f_hide_closed".
									"#$f_reporter_id#$f_handler_id".
									"#$f_sort#$f_dir".
									"#$f_start_month#$f_start_day".
									"#$f_start_year#$f_end_month".
									"#$f_end_day#$f_end_year".
									"#$f_search#$f_hide_resolved".
									"#$f_deadline_rel#$f_date_deadline".
									"#$f_date_from_rel#$f_date_from".
									"#$f_date_to_rel#$f_date_to".
									"#$f_show_version".
									"#$f_show_n_hours";
				break;
		# Set the sort order and direction
		case '2':
		        
				$t_settings_string = "$t_setting_arr[0]".
									"#$t_setting_arr[1]#$t_setting_arr[2]".
									"#$t_setting_arr[3]#$t_setting_arr[4]".
									"#$t_setting_arr[5]#$t_setting_arr[6]".
									"#$t_setting_arr[7]#$t_setting_arr[8]".
									"#$f_sort#$f_dir".
									"#$t_setting_arr[11]#$t_setting_arr[12]".
									"#$t_setting_arr[13]#$t_setting_arr[14]".
									"#$t_setting_arr[15]#$t_setting_arr[16]".
									"#$t_setting_arr[17]#$t_setting_arr[18]".
									"#$f_deadline_rel[19]#$f_date_deadline[20]".
									"#$f_date_from_rel[21]#$f_date_from[22]".
									"#$f_date_to_rel[23]#$f_date_to[24]".
									"#$t_setting_arr[25]".
									"#$t_setting_arr[26]";
				break;

		# does nothing. catch all case
		default: 
				$t_settings_string = "$t_setting_arr[0]".
									"#$t_setting_arr[1]#$t_setting_arr[2]".
									"#$t_setting_arr[3]#$t_setting_arr[4]".
									"#$t_setting_arr[5]#$t_setting_arr[6]".
									"#$t_setting_arr[7]#$t_setting_arr[8]".
									"#$t_setting_arr[9]#$t_setting_arr[10]".
									"#$t_setting_arr[11]#$t_setting_arr[12]".
									"#$t_setting_arr[13]#$t_setting_arr[14]".
									"#$t_setting_arr[15]#$t_setting_arr[16]".
									"#$t_setting_arr[17]#$t_setting_arr[18]".
									"#$f_deadline_rel[19]#$f_date_deadline[20]".
									"#$f_date_from_rel[21]#$f_date_from[22]".
									"#$f_date_to_rel[23]#$f_to_from[24]".
									"#$f_show_version[25]".
									"#$t_setting_arr[26]";
	}
    
	# set cookie values
	setcookie( config_get( 'view_all_cookie' ), $t_settings_string, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );

	# redirect to print_all or view_all page
	if ( $f_print ) {
		$t_redirect_url = 'index.php?m=webtracking&a=print_all_bug_page';
	} else {
	$date_from=$_POST['date_from'];
	$date_from_rel=$_POST['date_from_rel'];
		$t_redirect_url = 'index.php?m=webtracking&a=view_all_bug_page&dfr='.$date_from_rel.'&df='.$date_from;
	}
    
	$t_view_all_cookie = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
	$t_setting_arr	= explode( '#', $t_view_all_cookie );
    

	print_header_redirect( $t_redirect_url );
?>
