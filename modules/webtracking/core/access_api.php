<?php
	
	//$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	$t_core_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'constant_inc.php' );	
	require_once( $t_core_dir . 'helper_api.php' );
	require_once( $t_core_dir . 'authentication_api.php' );
	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'project_api.php' );

	###########################################################################
	# Access Control API
	###########################################################################

	# Function to be called when a user is attempting to access a page that
	# he/she is not authorised to.  This outputs an access denied message then
	# re-directs to the mainpage.
	function access_denied() {
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_SERVER;
		}

		// Si viene por webservice no necesita estar logueado.
		if($_POST['code']=='14149989'){
			return;
		}
		
		if ( ! auth_is_user_authenticated() ) {
			$p_return_page = string_url( $_SERVER['REQUEST_URI'] );
			print_header_redirect( 'index.php?m=webtracking&a=login_page&return=' . $p_return_page );
		} else {
			global $AppUI;
			$AppUI->redirect("m=public&a=access_denied");
			
			/*
			print '<center>';
			print '<p>'.error_string(ERROR_ACCESS_DENIED).'</p>';
			print_bracket_link( 'index.php?m=webtracking&a=main_page', lang_get( 'proceed' ) );
			print '</center>';*/
		}
		exit;
	}


	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_access_matrix = array();
	$g_cache_access_matrix_project_ids = array();
	$g_cache_access_matrix_user_ids = array();

	function access_cache_matrix_project( $p_project_id ) {
		global $g_cache_access_matrix, $g_cache_access_matrix_project_ids;

		$c_project_id = db_prepare_int( $p_project_id );

		if ( ALL_PROJECTS == $c_project_id ) {
			return array();
		}

		if ( ! in_array( (int)$p_project_id, $g_cache_access_matrix_project_ids ) ) {
			$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

			$query = "SELECT user_id, access_level
					  FROM $t_project_user_list_table
					  WHERE project_id='$c_project_id'";
			$result = db_query( $query );

			$count = db_num_rows( $result );

			for ( $i=0 ; $i < $count ; $i++ ) {
				$row = db_fetch_array( $result );
				
				$g_cache_access_matrix[(int)$row['user_id']][(int)$p_project_id] = (int)$row['access_level'];
			}

			$g_cache_access_matrix_project_ids[] = (int)$p_project_id;
		}

		$t_results = array();

		foreach( $g_cache_access_matrix as $t_user ) {
			if ( isset( $t_user[(int)$p_project_id] ) ) {
				$t_results[(int)$p_project_id] = $t_user[(int)$p_project_id];
			}
		}

		return $t_results;
	}

	function access_cache_matrix_user( $p_user_id ) {
		global $g_cache_access_matrix, $g_cache_access_matrix_user_ids;

		$c_user_id = db_prepare_int( $p_user_id );

		if ( ! in_array( (int)$p_user_id, $g_cache_access_matrix_user_ids ) ) {
			$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );

			$query = "SELECT project_id, access_level
					  FROM $t_project_user_list_table
					  WHERE user_id='$c_user_id'";
			$result = db_query( $query );

			$count = db_num_rows( $result );

			# make sure we always have an array to return
			$g_cache_access_matrix[(int)$p_user_id] = array();

			for ( $i=0 ; $i < $count ; $i++ ) {
				$row = db_fetch_array( $result );
				$g_cache_access_matrix[(int)$p_user_id][(int)$row['project_id']] = (int)$row['access_level'];
			}

			$g_cache_access_matrix_user_ids[] = (int)$p_user_id;
		}

		return $g_cache_access_matrix[(int)$p_user_id];
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check the current user's access against the given value and return true
	#  if the user's access is equal to or higher, false otherwise.
	#
	# This function only checks the user's global access level, ignoring any
	#  overrides they might have at a project level
	function access_has_global_level( $p_access_level ) {
		# Short circuit the check in this case
		if ( NOBODY == $p_access_level ) {
			return false;
		}

		# Deal with not logged in silently in this case
		# @@@ we may be able to remove this and just error
		#     and once we default to anon login, we can remove it for sure
		if ( ! auth_is_user_authenticated() ) {
			return false;
		}

		$t_access_level = current_user_get_field( 'access_level' );

		if ( $t_access_level >= $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check if the user has the specified global access level
	#  and deny access to the page if not	
	function access_ensure_global_level( $p_access_level ) {
		if ( ! access_has_global_level( $p_access_level ) ) {
			access_denied();
		}		
	}

	# --------------------
	# Check the current user's access against the given value and return true
	#  if the user's access is equal to or higher, false otherwise.
	#
	# This function checks the project access level first (for the current project
	#  if none is specified) and if the user is not listed, it falls back on the
	#  user's global access level.
	function access_has_project_level( $p_access_level, $p_project_id=null ) {
		# Short circuit the check in this case
		if ( NOBODY == $p_access_level ) {
			return false;
		}

		# Deal with not logged in silently in this case
		# @@@ we may be able to remove this and just error
		#     and once we default to anon login, we can remove it for sure
		if ( ! auth_is_user_authenticated() ) {
			return false;
		}

		if ( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}

		if ( ALL_PROJECTS == $p_project_id ) {
			return access_has_global_level( $p_access_level );
		}

		$t_access_level = access_get_local_level( auth_get_current_user_id(),
													$p_project_id );

		# Try to use the project access level.
		# If the user is not listed in the project, then try to fall back
		#  to the global access level
		if ( false === $t_access_level ) {
			$t_project_view_state = project_get_field( $p_project_id, 'view_state' );

			# If the project is private and the user isn't listed, then they
			# must have the private_project_threshold access level to get in.
			if ( VS_PRIVATE == $t_project_view_state ) {
				return ( access_has_global_level( config_get( 'private_project_threshold' ) ) );
			} else {
				$t_access_level = current_user_get_field( 'access_level' );
			}
		}

		return ( $t_access_level >= $p_access_level );
	}
 	
	# --------------------
	# Check if the user has the specified access level for the given project
	#  and deny access to the page if not
	function access_ensure_project_level( $p_access_level, $p_project_id=null ) {
		if ( ! access_has_project_level(  $p_access_level, $p_project_id ) ) {
			access_denied();
		}
	}

	# --------------------
	# Check the current user's access against the given value and return true
	#  if the user's access is equal to or higher, false otherwise.
	#
	# This function looks up the bug's project and performs an access check
	#  against that project
	function access_has_bug_level( $p_access_level, $p_bug_id ) {
		# Deal with not logged in silently in this case
		# @@@ we may be able to remove this and just error
		#     and once we default to anon login, we can remove it for sure
		if ( ! auth_is_user_authenticated() ) {
			return false;
		}

		# If the bug is private and the user is not the reporter, then the
		#  the user must also have higher access than private_bug_threshold
		if ( VS_PRIVATE == bug_get_field( $p_bug_id, 'view_state' ) &&
			 ! bug_is_user_reporter( $p_bug_id, auth_get_current_user_id() ) ) {
			$p_access_level = max( $p_access_level, config_get( 'private_bug_threshold' ) );
		}
	
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		return access_has_project_level( $p_access_level, $t_project_id );
	}

	# --------------------
	# Check if the user has the specified access level for the given bug
	#  and deny access to the page if not
	function access_ensure_bug_level( $p_access_level, $p_bug_id ) {
		if ( ! access_has_bug_level( $p_access_level, $p_bug_id ) ) {
			access_denied();
		}
 	}

	# --------------------
	# Check the current user's access against the given value and return true
	#  if the user's access is equal to or higher, false otherwise.
	#
	# This function looks up the bugnote's bug and performs an access check
	#  against that bug
	function access_has_bugnote_level( $p_access_level, $p_bugnote_id ) {
		# If the bug is private and the user is not the reporter, then the
		#  the user must also have higher access than private_bug_threshold
		if ( VS_PRIVATE == bugnote_get_field( $p_bugnote_id, 'view_state' ) &&
			 ! bugnote_is_user_reporter( $p_bugnote_id, auth_get_current_user_id() ) ) {
			$p_access_level = max( $p_access_level, config_get( 'private_bugnote_threshold' ) );
		}
	
		$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );

		return access_has_bug_level( $p_access_level, $t_bug_id );
	}

	# --------------------
	# Check if the user has the specified access level for the given bugnote
	#  and deny access to the page if not
	function access_ensure_bugnote_level( $p_access_level, $p_bugnote_id ) {
		if ( ! access_has_bugnote_level( $p_access_level, $p_bugnote_id ) ) {
			access_denied();
		}
 	}

	# --------------------
	# Check if the current user can close the specified bug
	function access_can_close_bug ( $p_bug_id ) {
		# If allow_reporter_close is enabled, then reporters can always close
		#  their own bugs
		if ( ON == config_get( 'allow_reporter_close' ) &&
			bug_is_user_reporter( $p_bug_id, auth_get_current_user_id() ) ) {
			return true;
		}

		return access_has_bug_level( config_get( 'close_bug_threshold' ), $p_bug_id );
	}
	
	# --------------------
	# Make sure that the current user can close the specified bug
	# See access_can_close_bug() for details.
	function access_ensure_can_close_bug( $p_bug_id ) {
		if ( !access_can_close_bug( $p_bug_id ) ) {
			access_denied();
		}
	}

	# --------------------
	# Check if the current user can reopen the specified bug
	function access_can_reopen_bug ( $p_bug_id ) {
		# If allow_reporter_reopen is enabled, then reporters can always reopen
		#  their own bugs
		if ( ON == config_get( 'allow_reporter_reopen' ) &&
			bug_is_user_reporter( $p_bug_id, auth_get_current_user_id() ) ) {
			return true;
		}

		return access_has_bug_level( config_get( 'reopen_bug_threshold' ), $p_bug_id );
	}
	
	# --------------------
	# Make sure that the current user can reopen the specified bug
	# See access_can_reopen_bug() for details.
	function access_ensure_can_reopen_bug( $p_bug_id ) {
		if ( !access_can_reopen_bug( $p_bug_id ) ) {
			access_denied();
		}
	}


	#===================================
	# Data Access
	#===================================

	function access_get_local_level( $p_user_id, $p_project_id ) {
		$p_project_id = (int)$p_project_id; // 000001 is different from 1.

		$t_project_level = access_cache_matrix_user( $p_user_id );

		if ( isset( $t_project_level[$p_project_id] ) ) {
			return $t_project_level[$p_project_id];
		} else {
			return false;
		}
	}
?>
