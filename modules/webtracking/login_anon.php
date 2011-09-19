<?php
 /* login_anon.php logs a user in anonymously without having to enter a username
 * or password.
 *
 * Depends on two global configuration variables:
 * allow_anonymous_login - bool which must be true to allow anonymous login.
 * anonymous_account - name of account to login with.
 *
 * TODO:
 * Check how manage account is impacted.
 * Might be extended to allow redirects for bug links etc.
 */
	require_once( 'core.php' );

	print_header_redirect( 'index.php?m=webtracking&a=login&username=' . config_get( 'anonymous_account' ) . '&amp;perm_login=false' );
?>