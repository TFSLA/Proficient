<?php
	# The specified profile is deleted and the user is redirected to
	# account_prof_menu_page.php3
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
	$f_profile_id	= gpc_get_int( 'profile_id' );

	profile_delete( auth_get_current_user_id(), $f_profile_id );

	print_header_redirect( 'index.php?m=webtracking&a=account_prof_menu_page' );
?>
