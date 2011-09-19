<?php

	# CALLERS
	#	This page is called from:
	#	- account_prefs_inc.php

	# EXPECTED BEHAVIOUR
	#	- Reset the user's preferences to default values
	#	- Redirect to account_prefs_page.php or another page, if given

	# CALLS
	#	This page conditionally redirects upon completion

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- User must not be protected

	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'user_pref_api.php' );

	#============ Parameters ============
	$f_redirect_url	= gpc_get_string( 'redirect_url', 'index.php?m=webtracking&a=account_prefs_page' );

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
	user_pref_set_default( auth_get_current_user_id() );

	print_header_redirect( $f_redirect_url );
?>
