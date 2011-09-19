<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_user_id		= gpc_get_int( 'user_id' );

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

	$result = project_remove_user( $f_project_id, $f_user_id );

	print_header_redirect( 'index.php?m=webtracking&a=manage_user_edit_page&user_id=' .$f_user_id );
?>
