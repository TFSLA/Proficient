<?php require_once( 'core.php' ) ?>
<?php
	if ( auth_is_user_authenticated() ) {
		print_header_redirect( 'index.php?m=webtracking&a=view_all_bug_page' );
	} else {
		print_header_redirect( 'index.php?m=webtracking&a=login_page' );
	}
?>