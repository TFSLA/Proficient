<?php
	###########################################################################
	# User Preferences API
	###########################################################################

	#===================================
	# Preference Structure Definition
	#===================================
	class UserPreferences {
		var $default_profile;
		var $default_project;
		var $advanced_report;
		var $advanced_view;
		var $advanced_update;
		var $refresh_delay;
		var $redirect_delay;
		var $email_on_new;
		var $email_on_assigned;
		var $email_on_feedback;
		var $email_on_resolved;
		var $email_on_closed;
		var $email_on_reopened;
		var $email_on_bugnote;
		var $email_on_status;
		var $email_on_priority;
		var $language;

		function UserPreferences() {
			$this->default_profile			= 0;
			$this->default_project			= ALL_PROJECTS;
			$this->advanced_report			= config_get( 'default_advanced_report');
			$this->advanced_view			= config_get( 'default_advanced_view');
			$this->advanced_update			= config_get( 'default_advanced_update');
			$this->refresh_delay			= config_get( 'default_refresh_delay');
			$this->redirect_delay			= config_get( 'default_redirect_delay');
			$this->email_on_new				= config_get( 'default_email_on_new');
			$this->email_on_assigned		= config_get( 'default_email_on_assigned');
			$this->email_on_feedback		= config_get( 'default_email_on_feedback');
			$this->email_on_resolved		= config_get( 'default_email_on_resolved');
			$this->email_on_closed			= config_get( 'default_email_on_closed');
			$this->email_on_reopened		= config_get( 'default_email_on_reopened');
			$this->email_on_bugnote			= config_get( 'default_email_on_bugnote');
			$this->email_on_status			= config_get( 'default_email_on_status');
			$this->email_on_priority		= config_get( 'default_email_on_priority');
			$this->language					= config_get( 'default_language');
		}
	}

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_user_pref = array();

	# --------------------
	# Cache a user preferences row if necessary and return the cached copy
	#  If the third parameter is true (default), trigger an error
	#  if the preferences can't be found.  If the second parameter is
	#  false, return false if the preferences can't be found.
	function user_pref_cache_row( $p_user_id, $p_project_id = ALL_PROJECTS, $p_trigger_errors = true) {
		global $g_cache_user_pref;

		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		if ( isset ( $g_cache_user_pref[$c_user_id][$c_project_id] ) ) {
			return $g_cache_user_pref[$c_user_id][$c_project_id];
		}

		$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id' AND project_id='$c_project_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		if ( !isset( $g_cache_user_pref[$c_user_id] ) ) {
			$g_cache_user_pref[$c_user_id] = array();
		}

		$g_cache_user_pref[$c_user_id][$c_project_id] = $row;

		return $row;
	}

	# --------------------
	# Clear the user preferences cache (or just the given id if specified)
	function user_pref_clear_cache( $p_user_id = null, $p_project_id = null ) {
		global $g_cache_user_pref;

		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		if ( null === $p_user_id ) {
			$g_cache_user_pref = array();
		} else if ( null === $p_project_id ) {
			unset( $g_cache_user_pref[$c_user_id] );
		} else {
			unset( $g_cache_user_pref[$c_user_id][$c_project_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# return true if the user has prefs assigned for the given project,
	#  false otherwise
	#
	# Trying to get the row shouldn't be any slower in the DB than getting COUNT(*)
	#  and the transfer time is negligable.  So we try to cache the row - it could save
	#  us another query later.
	function user_pref_exists( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		if ( false === user_pref_cache_row( $p_user_id, $p_project_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# perform an insert of a preference object into the DB
	#
	# Also see the higher level user_pref_set() and user_pref_set_default()
	function user_pref_insert( $p_user_id, $p_project_id, $p_prefs ) {
		$c_user_id 		= db_prepare_int( $p_user_id );
		$c_project_id 	= db_prepare_int( $p_project_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table 	= config_get( 'mantis_user_pref_table' );

		$t_vars = get_object_vars( $p_prefs );
		$t_values = array();

		foreach ( $t_vars as $var => $val ) {
			array_push( $t_values, '\'' . db_prepare_string( $p_prefs->$var ) . '\'' );
		}

		$t_vars_string = implode( ', ', array_keys( $t_vars ) );
		$t_values_string = implode( ', ', $t_values );

	    $query = "INSERT
				  INTO $t_user_pref_table
				    (id, user_id, project_id, 
					  $t_vars_string)
				  VALUES
				    (null, '$c_user_id', '$c_project_id', 
					  $t_values_string)";
		db_query($query);

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# perform an update of a preference object into the DB
	#
	# Also see the higher level user_pref_set() and user_pref_set_default()
	function user_pref_update( $p_user_id, $p_project_id, $p_prefs ) {
		$c_user_id 		= db_prepare_int( $p_user_id );
		$c_project_id 	= db_prepare_int( $p_project_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table 	= config_get( 'mantis_user_pref_table' );

		$t_vars = get_object_vars( $p_prefs );
		
		$t_pairs = array();

		foreach ( $t_vars as $var => $val ) {
			array_push( $t_pairs, "$var = '" . db_prepare_string( $p_prefs->$var ) . '\'' );
		}

		$t_pairs_string = implode( ', ', $t_pairs );

	    $query = "UPDATE $t_user_pref_table
				  SET $t_pairs_string
				  WHERE user_id = '$c_user_id' AND project_id = '$c_project_id'";
		db_query($query);

		user_pref_clear_cache( $p_user_id, $p_project_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# delete a preferencess row
	# returns true if the prefs were successfully deleted
	function user_pref_delete( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_project_id	= db_prepare_int( $p_project_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "DELETE
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id'
				    AND project_id='$c_project_id'";
		db_query( $query );

		user_pref_clear_cache( $p_user_id, $p_project_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# delete all preferences for a user in all projects
	# returns true if the prefs were successfully deleted
	#
	# It is far more efficient to delete them all in one query than to
	#  call user_pref_delete() for each one and the code is short so that's
	#  what we do
	function user_pref_delete_all( $p_user_id ) {
		$c_user_id		= db_prepare_int( $p_user_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_pref_table = config_get( 'mantis_user_pref_table' );

		$query = "DELETE
				  FROM $t_user_pref_table
				  WHERE user_id='$c_user_id'";
		db_query( $query );

		user_pref_clear_cache( $p_user_id );

		# db_query() errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# return the user's preferences
	function user_pref_get_row( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		return user_pref_cache_row( $p_user_id, $p_project_id );
	}

	# --------------------
	# return the user's preferences in a UserPreferences object
	function user_pref_get( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_prefs = new UserPreferences;

		$row = user_pref_cache_row( $p_user_id, $p_project_id, false );

		# If the user has no preferences for the given project
		if ( false === $row ) {
			if ( ALL_PROJECTS != $p_project_id ) {
				# Try to get the prefs for ALL_PROJECTS (the defaults)
				$row = user_pref_cache_row( $p_user_id, ALL_PROJECTS, false );
			}

			# If $row is still false (the user doesn't have default preferences)
			if ( false === $row ) {
				# We use an empty array
				$row = array(); 
			}
		}

		$t_row_keys = array_keys( $row );

		$t_vars = get_object_vars( $t_prefs );
	
		# Check each variable in the class
		foreach ( $t_vars as $var => $val ) {
			# If we got a field from the DB with the same name
			if ( in_array( $var, $t_row_keys, true ) ) {
				# Store that value in the object
				$t_prefs->$var = $row[$var];
			}
		}

		return $t_prefs;
	}

	# --------------------
	# Return the specified preference field for the user id
	# If the preference can't be found try to return a defined default
	# If that fails, trigger a WARNING and return ''
	function user_pref_get_pref( $p_user_id, $p_pref_name, $p_project_id = ALL_PROJECTS ) {
		$t_prefs = user_pref_get( $p_user_id, $p_project_id );

		$t_vars = get_object_vars( $t_prefs );
		
		if ( in_array( $p_pref_name, array_keys( $t_vars ), true ) ) {
			return $t_prefs->$p_pref_name;
		} else {
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Set a user preference
	#
	# By getting the prefs for the project first we deal fairly well with defaults.
	#  If there are currently no prefs for that project, the ALL_PROJECTS prefs will 
	#  be returned so we end up storing a new set of prefs for the given project
	#  based on the prefs for ALL_PROJECTS.  If there isn't even an entry for
	#  ALL_PROJECTS, we'd get returned a default UserPreferences object to modify.
	function user_pref_set_pref( $p_user_id, $p_pref_name, $p_pref_value, $p_project_id = ALL_PROJECTS ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_pref_name	= db_prepare_string( $p_pref_name );
		$c_pref_value	= db_prepare_string( $p_pref_value );
		$c_project_id	= db_prepare_int( $p_project_id );

		$t_prefs = user_pref_get( $p_user_id, $p_project_id );

		$t_prefs->$p_pref_name = $p_pref_value;

		user_pref_set( $p_user_id, $t_prefs, $p_project_id );

		return true;
	}

	# --------------------
	# set the user's preferences for the project from the given preferences object
	#  Do the work by calling user_pref_update() or user_pref_insert() as appropriate
	function user_pref_set( $p_user_id, $p_prefs, $p_project_id = ALL_PROJECTS ) {
		if ( user_pref_exists( $p_user_id, $p_project_id ) ) {
			return user_pref_update( $p_user_id, $p_project_id, $p_prefs );
		} else {
			return user_pref_insert( $p_user_id, $p_project_id, $p_prefs );
		}
	}

	# --------------------
	# create a set of default preferences for the project
	function user_pref_set_default( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		# get a default preferences object
		$t_prefs = new UserPreferences();

		return user_pref_set( $p_user_id, $t_prefs, $p_project_id );
	}

?>
