<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	$f_name	= gpc_get_string( 'name' );

	$f_name = trim( $f_name );
	if ( is_blank( $f_name ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if ( ! custom_field_is_name_unique( $f_name ) ) {
		trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
	}

	$t_field_id = custom_field_create( $f_name );

	if ( ON == config_get( 'custom_field_edit_after_create' ) ) {
		$t_redirect_url = "index.php?m=webtracking&a=manage_custom_field_edit_page&field_id=$t_field_id";
	} else {
		$t_redirect_url = 'index.php?m=webtracking&a=manage_custom_field_page';
	}
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
