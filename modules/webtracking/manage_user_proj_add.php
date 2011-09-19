<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_user_id		= gpc_get_int( 'user_id' );
	$f_access_level	= gpc_get_int( 'access_level' );
	$f_project_id	= gpc_get_int_array( 'project_id', array() );
	$t_manage_user_threshold = config_get( 'manage_user_threshold' );

	foreach ( $f_project_id as $t_proj_id ) {
		if ( access_has_project_level( $t_manage_user_threshold, $t_proj_id ) ) {
			project_add_user( $t_proj_id, $f_user_id, $f_access_level );
		}
	}

	print_header_redirect( 'index.php?m=webtracking&a=manage_user_edit_page&user_id=' . $f_user_id );
?>
