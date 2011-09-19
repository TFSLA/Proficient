<?php

	//$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	$t_core_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'category_api.php' );
	require_once( $t_core_dir . 'version_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'file_api.php' );

	###########################################################################
	# Project API
	###########################################################################

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_project = array();
	$g_cache_project_missing = array();
	$g_cache_project_all = false;

	# --------------------
	# Cache a project row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the project can't be found.  If the second parameter is
	#  false, return false if the project can't be found.
	function project_cache_row( $p_project_id, $p_trigger_errors=true ) {
		global $g_cache_project, $g_cache_project_missing;

		$c_project_id = db_prepare_int( $p_project_id );

		if ( isset ( $g_cache_project[(int)$p_project_id] ) ) {
			return $g_cache_project[(int)$p_project_id];
		} else if ( isset( $g_cache_project_missing[(int)$p_project_id] ) ) {
			return false;
		}

		$t_project_table = config_get( 'mantis_project_table' );

		$query = "SELECT project_id as id, project_name as name, project_status as status, enabled, view_state, access_min, file_path, project_description as description 
				  FROM $t_project_table 
				  WHERE project_id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			$g_cache_project_missing[(int)$p_project_id] = true;

			if ( $p_trigger_errors ) {
				trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_project[(int)$p_project_id] = $row;

		return $row;
	}

	# --------------------
	# Cache all project rows and return an array of them
	function project_cache_all() {
		global $g_cache_project, $g_cache_project_all;
		
		if ( ! $g_cache_project_all ) {
			$t_project_table = config_get( 'mantis_project_table' );

			$query = "SELECT project_id as id, project_name as name, project_status as status, enabled, view_state, access_min, file_path, project_description as description 
					  FROM $t_project_table";

			//echo "<br> $query <br>";
			$result = db_query( $query );

			$count = db_num_rows( $result );

			for ( $i = 0 ; $i < $count ; $i++ ) {
				$row = db_fetch_array( $result );

				$g_cache_project[(int)$row['id']] = $row;
			}

			$g_cache_project_all = true;
		}

		return $g_cache_project;
	}

	# --------------------
	# Clear the project cache (or just the given id if specified)
	function project_clear_cache( $p_project_id = null ) {
		global $g_cache_project, $g_cache_project_missing, $g_cache_project_all;
		
		if ( null === $p_project_id ) {
			$g_cache_project = array();
			$g_cache_project_missing = array();
			$g_cache_project_all = false;
		} else {
			unset( $g_cache_project[(int)$p_project_id] );
			unset( $g_cache_project_missing[(int)$p_project_id] );
			$g_cache_project_all = false;
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# check to see if project exists by id
	# return true if it does, false otherwise
	function project_exists( $p_project_id ) {
		# we're making use of the caching function here.  If we
		#  succeed in caching the project then it exists and is
		#  now cached for use by later function calls.  If we can't
		#  cache it we return false.
		if ( false == project_cache_row( $p_project_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function project_ensure_exists( $p_project_id ) {
		if ( ! project_exists( $p_project_id ) ) {
			trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# check to see if project exists by name
	function project_is_name_unique( $p_name ) {
		$c_name = db_prepare_string( $p_name );

		$t_project_table = config_get( 'mantis_project_table' );

		$query ="SELECT COUNT(*) 
				 FROM $t_project_table 
				 WHERE project_name='$c_name'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function project_ensure_name_unique( $p_name ) {
		if ( ! project_is_name_unique( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_NOT_UNIQUE, ERROR );
		}
	}

	# --------------------
	# check to see if the user/project combo already exists
	# returns true is duplicate is found, otherwise false
	function project_includes_user( $p_project_id, $p_user_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );

		$query = "SELECT COUNT(*)
				  FROM $t_project_user_list_table
				  WHERE project_id='$c_project_id' AND
						user_id='$c_user_id'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return false;
		} else {
			return true;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Create a new project
	function project_create( $p_name, $p_description, $p_status, $p_view_state=VS_PUBLIC, $p_file_path='', $p_enabled=true ) {
		# Make sure file path has trailing slash
		$p_file_path = terminate_directory_path( $p_file_path );

		$c_name 		= db_prepare_string( $p_name );
		$c_description 	= db_prepare_string( $p_description );
		$c_status		= db_prepare_int( $p_status );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_file_path	= db_prepare_string( $p_file_path );
		$c_enabled		= db_prepare_bool( $p_enabled );

		if ( is_blank( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
		}

		project_ensure_name_unique( $p_name );

		if ( !is_blank( $p_file_path ) ) {
			file_ensure_valid_upload_path( $p_file_path );
		}

		$t_project_table = config_get( 'mantis_project_table' );

		$query = "INSERT
				  INTO $t_project_table
					( project_id, project_name, project_status, enabled, view_state, file_path, project_description )
				  VALUES
					( null, '$c_name', '$c_status', '$c_enabled', '$c_view_state', '$c_file_path', '$c_description' )";

		db_query( $query );

		# return the id of the new project
		return db_insert_id();
	}

	# --------------------
	# Delete a project
	function project_delete( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_table = config_get( 'mantis_project_table' );

		# Delete the bugs
		bug_delete_all( $p_project_id );

		# Delete associations with custom field definitions.
		custom_field_unlink_all( $p_project_id );

		# Delete the project categories
		category_remove_all( $p_project_id );

		# Delete the project versions
		version_remove_all( $p_project_id );

		# Delete the project files
		project_delete_all_files( $p_project_id );

		# Delete the records assigning users to this project
		project_remove_all_users( $p_project_id );

		# Delete the project entry
		$query = "DELETE
				FROM $t_project_table
				WHERE project_id='$c_project_id'";
		
		db_query( $query );

		project_clear_cache( $p_project_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Update a project
	function project_update( $p_project_id, $p_name, $p_description, $p_status, $p_view_state, $p_file_path, $p_enabled ) {
		# Make sure file path has trailing slash
		$p_file_path = terminate_directory_path( $p_file_path );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_name 		= db_prepare_string( $p_name );
		$c_description 	= db_prepare_string( $p_description );
		$c_status		= db_prepare_int( $p_status );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_file_path	= db_prepare_string( $p_file_path );
		$c_enabled		= db_prepare_bool( $p_enabled );

/*		if ( is_blank( $p_name ) ) {
			trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
		}

		$t_old_name = project_get_field( $p_project_id, 'name' );

		if ( $p_name != $t_old_name ) {
			project_ensure_name_unique( $p_name );
		}

		if ( !is_blank( $p_file_path ) ) {
			file_ensure_valid_upload_path( $p_file_path );
		}
*/
		$t_project_table = config_get( 'mantis_project_table' );

/*		$query = "UPDATE $t_project_table
				  SET name='$c_name',
					status='$c_status',
					enabled='$c_enabled',
					view_state='$c_view_state',
					file_path='$c_file_path',
					description='$c_description'
				  WHERE id='$c_project_id'";
*/
		$query = "UPDATE $t_project_table
				  SET 	project_status='$c_status',
					enabled='$c_enabled',
					view_state='$c_view_state'
				  WHERE project_id='$c_project_id'";


		db_query( $query );

		project_clear_cache( $p_project_id );

		# db_query errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return the row describing the given project
	function project_get_row( $p_project_id ) {
		return project_cache_row( $p_project_id );
	}

	# --------------------
	# Return all rows describing all projects
	function project_get_all_rows() {
		return project_cache_all();
	}

	# --------------------
	# Return the specified field of the specified project
	function project_get_field( $p_project_id, $p_field_name ) {
		$row = project_get_row( $p_project_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# Return the name of the project
	# Handles ALL_PROJECTS by returning the internationalized string for All Projects
	function project_get_name( $p_project_id ) {
		if ( ALL_PROJECTS == $p_project_id ) {
			return lang_get( 'all_projects' );
		} else {
			return project_get_field( $p_project_id, 'name' );
		}
	}

	# --------------------
	# Return the user's local (overridden) access level on the project or false
	#  if the user is not listed on the project
	function project_get_local_user_access_level( $p_project_id, $p_user_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );

		if ( ALL_PROJECTS == $c_project_id ) {
			return false;
		}

		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$query = "SELECT access_level
				  FROM $t_project_user_list_table
				  WHERE user_id='$c_user_id' AND project_id='$c_project_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			return db_result( $result );
		} else {
			return false;
		}
	}

	# --------------------
	# return the descriptor holding all the info from the project user list
	# for the specified project
	function project_get_local_user_rows( $p_project_id ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$query = "SELECT *
				FROM $t_project_user_list_table
				WHERE project_id='$c_project_id'";

		$result = db_query( $query );

		$t_user_rows = array();
		$t_row_count = db_num_rows( $result );

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			array_push( $t_user_rows, db_fetch_array( $result ) );
		}

		return $t_user_rows;
	}

	# --------------------
	# Return an array of info about users who have access to the the given project
	# For each user we have 'id', 'username', and 'access_level' (overall access level)
	# If the second parameter is given, return only users with an access level
	#  higher than the given value.
	function project_get_all_user_rows( $p_project_id, $p_access_level=ANYBODY ) {
		$c_project_id	= db_prepare_int( $p_project_id );

		# Optimization when access_level is NOBODY
		if ( NOBODY == $p_access_level ) {
			return array();
		}

		$t_user_table = config_get( 'mantis_user_table' );
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$t_on = ON;

		$t_access_level = $p_access_level;

		if ( VS_PRIVATE == project_get_field( $p_project_id, 'view_state' ) ) {
			# @@@ we need to get this logic out somewhere else.
			#   I was getting access_min from the project but apparently we got
			#   rid of that in 0.17.2.  The user docs claim developers and higher
			#   get into private projects but the code seems to only allow administrators in
			$t_access_level = max( $t_access_level, config_get( 'private_project_threshold' ) );
		}

		$t_access_clause = '';

		if ( $t_access_level > 0 ) {
			$t_access_clause = ' AND access_level >= ' . $t_access_level;
		}

		$t_users = array();

		$query = "SELECT user_id as id, user_username as username, access_level
					FROM $t_user_table
					WHERE enabled = $t_on
					  $t_access_clause
					ORDER BY user_username";

		$result = db_query( $query );
		$t_row_count = db_num_rows( $result );
		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_users[$row['id']] = $row;
		}

		// Get the project overrides
		$query = "SELECT u.user_id as id, u.user_username as username, l.access_level
					FROM $t_project_user_list_table l, $t_user_table u
					WHERE l.user_id = u.user_id
					  AND u.enabled = $t_on
					  AND l.project_id = $c_project_id
					ORDER BY username";

		$result = db_query( $query );
		$t_row_count = db_num_rows( $result );
		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result );

			if ( $row['access_level'] >= $p_access_level ) {
				$t_users[$row['id']] = $row;
			} else {
				# If user's overridden level is lower than required, so remove
				#  them from the list if they were previously there
				unset( $t_users[$row['id']] );
			}
		}

		return multi_sort( array_values($t_users), 'username' );
	}


	#===================================
	# Data Modification
	#===================================

	# --------------------
	# add user with the specified access level to a project
	function project_add_user( $p_project_id, $p_user_id, $p_access_level ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_access_level	= db_prepare_int( $p_access_level );

		$query = "INSERT
				  INTO $t_project_user_list_table
				    ( project_id, user_id, access_level )
				  VALUES
				    ( '$c_project_id', '$c_user_id', '$c_access_level')";
		
		db_query( $query );

		# db_query errors on failure so:
		return true;
	}

	# --------------------
	# update entry
	# must make sure entry exists beforehand
	function project_update_user_access( $p_project_id, $p_user_id, $p_access_level ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_access_level	= db_prepare_int( $p_access_level );

		$query = "UPDATE $t_project_user_list_table
				  SET access_level='$c_access_level'
				  WHERE	project_id='$c_project_id' AND
						user_id='$c_user_id'";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}

	# --------------------
	# update or add the entry as appropriate
	#  This function involves one more db query than project_update_user_acces() 
	#  or project_add_user()
	function project_set_user_access( $p_project_id, $p_user_id, $p_access_level ) {
		if ( project_includes_user( $p_project_id, $p_user_id ) ) {
			return project_update_user_access( $p_project_id, $p_user_id, $p_access_level );
		} else {
			return project_add_user( $p_project_id, $p_user_id, $p_access_level );
		}
	}

	# --------------------
	# remove user from project
	function project_remove_user( $p_project_id, $p_user_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_user_id		= db_prepare_int( $p_user_id );

		$query = "DELETE FROM $t_project_user_list_table
				  WHERE project_id='$c_project_id' AND
						user_id='$c_user_id'";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}

	# --------------------
	# delete all users from the project user list for a given project
	# this is useful when deleting or closing a project
	function project_remove_all_users( $p_project_id ) {
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

		$c_project_id	= db_prepare_int( $p_project_id );

		$query = "DELETE FROM $t_project_user_list_table
				WHERE project_id='$c_project_id'";

		db_query( $query );

		# db_query errors on failure so:
		return true;
	}

	# --------------------
	# Copy all users and their permissions from the source project to the
	#  destination project
	function project_copy_users( $p_destination_id, $p_source_id ) {
		# Copy all users from current project over to another project
		$rows = project_get_local_user_rows( $p_source_id );

		for ( $i = 0 ; $i < sizeof( $rows ) ; $i++ ) {
			extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

			# if there is no duplicate then add a new entry
			# otherwise just update the access level for the existing entry
			if ( project_includes_user( $p_destination_id, $v_user_id ) ) {
				project_update_user_access( $p_destination_id, $v_user_id, $v_access_level );
			} else {
				project_add_user( $p_destination_id, $v_user_id, $v_access_level );
			}
		}
	}

	# --------------------
	# Delete all files associated with a project
	function project_delete_all_files( $p_project_id ) {
		file_delete_project_files( $p_project_id );
	}

	#===================================
	# Other
	#===================================

	# --------------------
	# Pads the project id with the appropriate number of zeros.
	function project_format_id( $p_project_id ) {
		$t_padding = config_get( 'display_project_padding' );
		return( str_pad( $p_project_id, $t_padding, '0', STR_PAD_LEFT ) );
	}
?>
