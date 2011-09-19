<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	access_ensure_can_close_bug( $f_bug_id );

	bug_close( $f_bug_id, $f_bugnote_text );

	print_successful_redirect( 'index.php?m=webtracking&a=view_all_bug_page' );
?>
