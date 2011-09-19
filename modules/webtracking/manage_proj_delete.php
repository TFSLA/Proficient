<?php require_once( 'core.php' ) ?>
<?php
	$f_project_id = gpc_get_int( 'project_id' );
	
	access_ensure_project_level( config_get( 'delete_project_threshold' ), $f_project_id );

	if(!helper_ensure_confirmed( lang_get( 'project_delete_msg' ),
							 lang_get( 'project_delete_button' ) )) return;

	project_delete( $f_project_id );

	# Don't leave the current project set to a deleted project - 
	#  set it to All Projects
	if ( helper_get_current_project() == $f_project_id ) {
		helper_set_current_project( ALL_PROJECTS );
	}

    $t_redirect_url = 'index.php?m=webtracking&a=manage_proj_page';
	print_header_redirect( $t_redirect_url );
?>
