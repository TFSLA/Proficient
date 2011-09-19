<?php
	# Add file to a bug and then view the bug
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php
	$f_bug_id	= gpc_get_int( 'bug_id' );
	$f_file		= gpc_get_file( 'file' );
	
	if ( ! file_allow_bug_upload( $f_bug_id ) ) {
		access_denied();
	}

	access_ensure_bug_level( config_get( 'upload_bug_file_threshold' ), $f_bug_id );

	if ( !is_uploaded_file( $f_file['tmp_name'] ) || 0 == $f_file['size'] ) {
		trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
	}

	$t_upload_method	= config_get( 'file_upload_method' );

	file_add( $f_bug_id, $f_file['tmp_name'], $f_file['name'], $f_file['type'] );

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_bug_id );

	$AppUI = &$_SESSION['AppUI'];
	$AppUI->setMsg(lang_get( 'operation_successful' ), UI_MSG_OK);
	$redir = substr($f_redirect_url, strpos($f_redirect_url,"?")+1);
	$AppUI->redirect($redir);
/*
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

<?php html_page_bottom1( __FILE__ ) 
*/
?>
