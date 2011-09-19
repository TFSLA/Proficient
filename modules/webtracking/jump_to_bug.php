<?php
	# Redirect to the appropriate viewing page for the bug
?>
<?php
	require_once( 'core.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# Determine which view page to redirect back to.
	$f_bug_id		= gpc_get_int( 'bug_id' );
	
	print_header_redirect_view( $f_bug_id );
?>
