<?php require_once( 'core.php' ) ?>
<?php
	$f_project_id 	= gpc_get_int( 'project_id' );
	//$f_name 		= gpc_get_string( 'name' );
	//$f_description 	= gpc_get_string( 'description' );
	$f_status 		= gpc_get_int( 'status' );
	$f_view_state 	= gpc_get_int( 'view_state' );
	//$f_file_path 	= gpc_get_string( 'file_path', '' );
	$f_enabled	 	= gpc_get_bool( 'enabled' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	project_update( $f_project_id, $f_name, $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled );

	print_header_redirect( 'index.php?m=webtracking&a=manage_proj_page' );
?>
