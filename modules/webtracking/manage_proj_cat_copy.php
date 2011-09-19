<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id		= gpc_get_int( 'project_id' );
	$f_other_project_id	= gpc_get_int( 'other_project_id' );
	$f_copy_from		= gpc_get_bool( 'copy_from' );
	$f_copy_to			= gpc_get_bool( 'copy_to' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( $f_copy_from ) {
	  $t_src_project_id = $f_other_project_id;
	  $t_dst_project_id = $f_project_id;
	} else if ( $f_copy_to ) {
	  $t_src_project_id = $f_project_id;
	  $t_dst_project_id = $f_other_project_id;
	} else {
		trigger_error( ERROR_CATEGORY_NO_ACTION, ERROR );
	}

	$rows = category_get_all_rows( $t_src_project_id );

	foreach ( $rows as $row ) {
		$t_category = $row['category'];

		if ( category_is_unique( $t_dst_project_id, $t_category ) ) {
			category_add( $t_dst_project_id, $t_category );
		}
	}

	print_header_redirect( 'index.php?m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id );
?>
