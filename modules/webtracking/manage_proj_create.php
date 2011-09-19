<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'create_project_threshold' ) );

	$f_name 		= gpc_get_string( 'name' );
	$f_description 	= gpc_get_string( 'description' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_status		= gpc_get_int( 'status' );
	$f_file_path	= gpc_get_string( 'file_path', '' );

	project_create( $f_name, $f_description, $f_status, $f_view_state, $f_file_path );

	$t_redirect_url = 'index.php?m=webtracking&a=manage_proj_page';

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
