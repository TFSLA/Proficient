<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'version_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_version		= gpc_get_string( 'version' );
	$f_date_order	= gpc_get_string( 'date_order' );
	$f_new_version	= gpc_get_string( 'new_version' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_new_version ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_version		= trim( $f_version );
	$f_new_version	= trim( $f_new_version );

	version_update( $f_project_id, $f_version, $f_new_version, $f_date_order );

	$t_redirect_url = 'index.php?m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id;

	$AppUI->setMsg( lang_get( 'operation_successful' ), UI_MSG_OK);
	$AppUI->redirect('m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id);
?>
<?php
	html_page_top1();

	html_meta_redirect( $t_redirect_url );

	html_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
