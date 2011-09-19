<?php
	# Removes all the cookies and then redirect to the page specified in
	#  the config option logout_redirect_page
?>
<?php require_once( 'core.php' ); ?>
<?php
	auth_logout();

	print_header_redirect( config_get( 'logout_redirect_page' ) );
?>