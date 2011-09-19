<?php
	# Make the specified profile the default
	# Redirect to account_prof_menu_page.php
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	auth_ensure_user_authenticated();
	
	current_user_ensure_unprotected();
?>
<?php
	$f_profile_id	= gpc_get_int( 'profile_id' );

	current_user_set_pref( 'default_profile', $f_profile_id );

	print_header_redirect( 'index.php?m=webtracking&a=account_prof_menu_page' );
?>
