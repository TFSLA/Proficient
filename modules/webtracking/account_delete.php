<?php

	# CALLERS
	#	This page is called from:
	#	- account_page.php

	# EXPECTED BEHAVIOUR
	#	- Delete the currently logged in user account
	#	- Logout the current user
	#	- Redirect to the page specified in the logout_redirect_page config option

	# CALLS
	#	This page conditionally redirects upon completion

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- allow_account_delete config option must be enabled

	require_once( 'core.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	if ( OFF == config_get( 'allow_account_delete' ) ) {
		print_header_redirect( 'index.php?m=webtracking&a=account_page' );
	}
?>
<?php
	if(!helper_ensure_confirmed( lang_get( 'confirm_delete_msg' ),
							 lang_get( 'delete_account_button' ) ))return;

	user_delete( auth_get_current_user_id() );

	auth_logout();

	$t_redirect = config_get( 'logout_redirect_page' );

	html_meta_redirect( $t_redirect );
	
	html_page_top1();

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
