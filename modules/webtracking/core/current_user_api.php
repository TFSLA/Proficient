<?php

	//$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	$t_core_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'filter_api.php' );

	###########################################################################
	# Current User API
	#
	# Wrappers around the User API that pass in the logged-in user for you
	###########################################################################

	# --------------------
	# Return the access level of the current user in the current project
	function current_user_get_access_level() {
		return user_get_access_level( auth_get_current_user_id(),
										helper_get_current_project() );
	}
	# --------------------
	# Return the number of open assigned bugs to the current user in
	#  the current project
	function current_user_get_assigned_open_bug_count() {
		return user_get_assigned_open_bug_count( auth_get_current_user_id(),
													helper_get_current_project() );
	}
	# --------------------
	# Return the number of open reported bugs by the current user in
	#  the current project
	function current_user_get_reported_open_bug_count() {
		return user_get_reported_open_bug_count( auth_get_current_user_id(),
													helper_get_current_project() );
	}
	# --------------------
	# Return the specified field of the currently logged in user
	function current_user_get_field( $p_field_name ) {
		return user_get_field( auth_get_current_user_id(),
								$p_field_name );
	}
	# --------------------
	# Return the specified field of the currently logged in user
	function current_user_get_pref( $p_pref_name ) {
		return user_pref_get_pref( auth_get_current_user_id(), $p_pref_name );
	}
	# --------------------
	# Return the specified field of the currently logged in user
	function current_user_set_pref( $p_pref_name, $p_pref_value ) {
		return user_pref_set_pref( auth_get_current_user_id(), $p_pref_name, $p_pref_value );
	}
	# --------------------
	# Return the specified field of the currently logged in user
	function current_user_set_default_project( $p_project_id ) {
		return user_set_default_project( auth_get_current_user_id(), $p_project_id );
	}
	# --------------------
	# Return the an array of projects to which the currently logged in user
	#  has access
	function current_user_get_accessible_projects() {
		return user_get_accessible_projects( auth_get_current_user_id() );
	}
	# --------------------
	# Return true if the currently logged in user is has a role of administrator
	#  or higher, false otherwise
	function current_user_is_administrator() {
		return user_is_administrator( auth_get_current_user_id() );
	}
	# --------------------
	# Return true if the currently logged in user protected, false otherwise
	function current_user_is_protected() {
		return user_is_protected( auth_get_current_user_id() );
	}
	# --------------------
	# Return true if the currently user is the anonymous user
	function current_user_is_anonymous() {
		return current_user_get_field( 'username' ) == config_get( 'anonymous_account' );
	}
	# --------------------
	# Trigger an ERROR if the current user account is protected
	function current_user_ensure_unprotected() {
		user_ensure_unprotected( auth_get_current_user_id() );
	}
	# --------------------
	# return the bug filter parameters for the current user
	#  this could be modified to call a user_api function to get the
	#  filter out of a db or whatever
	function current_user_get_bug_filter() {
		# check to see if new cookie is needed
		if ( !filter_is_cookie_valid() ) {
			return false;
		}

		$t_view_all_cookie = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );

		$t_setting_arr = explode( '#', $t_view_all_cookie );
	
		$t_filter = array();
		# Assign each value to a named key
		$t_filter['show_category'] 			= $t_setting_arr[1];
		$t_filter['show_severity']	 		= $t_setting_arr[2];
		$t_filter['show_status'] 				= $t_setting_arr[3];
		$t_filter['per_page'] 					= $t_setting_arr[4];
		$t_filter['highlight_changed'] 	= $t_setting_arr[5];
		$t_filter['hide_closed'] 				= $t_setting_arr[6];
		$t_filter['reporter_id']				= $t_setting_arr[7];
		$t_filter['handler_id'] 				= $t_setting_arr[8];
		$t_filter['sort'] 							= $t_setting_arr[9];
		$t_filter['dir']					 			= $t_setting_arr[10];
		$t_filter['start_month']				= $t_setting_arr[11];
		$t_filter['start_day'] 					= $t_setting_arr[12];
		$t_filter['start_year'] 				= $t_setting_arr[13];
		$t_filter['end_month'] 					= $t_setting_arr[14];
		$t_filter['end_day']						= $t_setting_arr[15];
		$t_filter['end_year']						= $t_setting_arr[16];
		$t_filter['search']							= $t_setting_arr[17];
		$t_filter['hide_resolved'] 			= $t_setting_arr[18];
		$t_filter['deadline_rel'] 			= $t_setting_arr[19];
		$t_filter['date_deadline'] 			= $t_setting_arr[20];
		$t_filter['date_from_rel'] 			= $t_setting_arr[21];
		$t_filter['date_from'] 					= $t_setting_arr[22];
		$t_filter['date_to_rel'] 				= $t_setting_arr[23];
		$t_filter['date_to'] 						= $t_setting_arr[24];
		$t_filter['show_version'] 			= $t_setting_arr[25];
		$t_filter['show_n_hours']				= $t_setting_arr[26];

		
		return $t_filter;
	}
?>
