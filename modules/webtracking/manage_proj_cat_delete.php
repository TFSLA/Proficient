<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_category		= gpc_get_string( 'category' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	# Confirm with the user
	if(!helper_ensure_confirmed( lang_get( 'category_delete_sure_msg' ),
							 lang_get( 'delete_category_button' ) )) return;

	category_remove( $f_project_id, $f_category );

	print_header_redirect( 'index.php?m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id );
?>
