<?php
	# Check to see if cookies are working
?>
<?php require_once( 'core.php' ) ?>
<?php
	//$f_return = gpc_get_string( 'return', 'main_page.php' );

	$f_return = 'index.php?m=webtracking&a=view_all_bug_page';
	if ( auth_is_user_authenticated() ) {
		$t_redirect_url = $f_return;
	} else {
		$t_redirect_url = 'index.php?m=webtracking&a=login_page&cookie_error=1';
	}

	print_header_redirect( $t_redirect_url );
?>
