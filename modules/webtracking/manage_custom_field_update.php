<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	$f_field_id						= gpc_get_int( 'field_id' );
	$f_return						= gpc_get_string( 'return', 'manage_custom_field_page.php' );
	$t_values['name']				= gpc_get_string( 'name' );
	$t_values['type']				= gpc_get_int( 'type' );
	$t_values['possible_values']	= gpc_get_string( 'possible_values' );
	$t_values['default_value']		= gpc_get_string( 'default_value' );
	$t_values['valid_regexp']		= gpc_get_string( 'valid_regexp' );
	$t_values['access_level_r']		= gpc_get_int( 'access_level_r' );
	$t_values['access_level_rw']	= gpc_get_int( 'access_level_rw' );
	$t_values['length_min']			= gpc_get_int( 'length_min' );
	$t_values['length_max']			= gpc_get_int( 'length_max' );
	$t_values['advanced']			= gpc_get_bool( 'advanced' );

	custom_field_update( $f_field_id, $t_values );

	html_page_top1();

	html_meta_redirect( $f_return );

	html_page_top2();
?>

<br />

<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $f_return, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
