<?php require_once( 'core.php' ) ?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_user_id		= gpc_get_int( 'user_id' );

	# We should check both since we are in the project section and an
	#  admin might raise the first threshold and not realize they need
	#  to raise the second
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

	project_remove_user( $f_project_id, $f_user_id );

	print_header_redirect( 'index.php?m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id );
?>
