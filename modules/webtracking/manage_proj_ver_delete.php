<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'version_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_version		= gpc_get_string( 'version' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if(!helper_ensure_confirmed( lang_get( 'version_delete_sure' ),
							 lang_get( 'delete_version_button' ) ))return;

	version_remove( $f_project_id, $f_version );

	print_header_redirect( 'index.php?m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id );
?>
