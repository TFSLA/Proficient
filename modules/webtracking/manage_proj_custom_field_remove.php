<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	$f_field_id		= gpc_get_int( 'field_id' );
	$f_project_id	= gpc_get_int( 'project_id' );

	# We should check both since we are in the project section and an
	#  admin might raise the first threshold and not realize they need
	#  to raise the second
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'custom_field_link_threshold' ), $f_project_id );

	custom_field_unlink( $f_field_id, $f_project_id );

	$t_redirect_url = 'index.php?m=webtracking&a=manage_proj_edit_page&project_id=' . $f_project_id;
?>
<?php html_page_top1() ?>
<?php
	html_meta_redirect( $t_redirect_url );
?>
<?php html_page_top2() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>
