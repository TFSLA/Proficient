<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

            require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_category	= gpc_get_string( 'default_category_name' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_category ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

            $category = urldecode($f_category);

            $query = "UPDATE btpsa_project_category_table SET default_category = '0'  WHERE project_id='".$f_project_id."' ";
            $mysql = db_query( $query );

             $query = "UPDATE btpsa_project_category_table SET default_category = '1'  WHERE project_id='".$f_project_id."' AND category='".$category."' ";
            $mysql = db_query( $query );

	$t_redirect_url = 'm=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id;

	$AppUI->setMsg( lang_get( 'operation_successful' ), UI_MSG_OK);
	$AppUI->redirect($t_redirect_url);
?>
