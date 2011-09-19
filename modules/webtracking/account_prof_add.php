<?php
	# This file adds a new profile and redirects to account_proj_menu_page.php
?>
<?php 
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'profile_api.php' );
?>
<?php
	auth_ensure_user_authenticated();
	
	current_user_ensure_unprotected();
?>
<?php
	access_ensure_project_level( config_get( 'add_profile_threshold' ) );

	$f_platform		= gpc_get_string( 'platform' );
	$f_os			= gpc_get_string( 'os' );
	$f_os_build		= gpc_get_string( 'os_build' );
	$f_description	= gpc_get_string( 'description' );

	profile_create( auth_get_current_user_id(), $f_platform, $f_os, $f_os_build, $f_description );

	print_header_redirect( 'index.php?m=webtracking&a=account_prof_menu_page' );
?>
