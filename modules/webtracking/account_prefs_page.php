<?php

	# CALLERS
	#	This page is called from:
	#	- print_account_menu()
	#	- header redirects from account_*.php

	# EXPECTED BEHAVIOUR
	#	- Display the user's current preferences
	#	- Allow the user to edit the preferences
	#	- Provide the option of saving changes or resetting to default values

	# CALLS
	#	This page calls the following pages:
	#	- acount_prefs_update.php  (to save changes)
	#	- account_prefs_reset.php  (to reset preferences to default values)

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- The user's account must not be protected

	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
	include( 'account_prefs_inc.php' );

	html_page_top1();
	html_page_top2();

	edit_account_prefs();

	html_page_bottom1( __FILE__ );
?>
