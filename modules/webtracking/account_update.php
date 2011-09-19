<?php
	# This page updates a user's information
	# If an account is protected then changes are forbidden
	# The page gets redirected back to account_page.php
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'email_api.php' );
?>
<?php
	auth_ensure_user_authenticated();
	
	current_user_ensure_unprotected();
?>
<?php
	$f_email			= gpc_get_string( 'email', '' );
	$f_password			= gpc_get_string( 'password', '' );
	$f_password_confirm	= gpc_get_string( 'password_confirm', '' );

	$f_email = email_append_domain( $f_email );

	user_set_email( auth_get_current_user_id(), $f_email );

	$t_redirect = 'index.php?m=webtracking&a=account_page';

	$AppUI = &$_SESSION['AppUI'];
	$AppUI->setMsg(lang_get( 'operation_successful' ), UI_MSG_OK);
	$redir = substr($f_redirect, strpos($f_redirect,"?")+1);
	$AppUI->redirect($redir);
	/*
	html_page_top1();
	html_meta_redirect( $t_redirect );
	html_page_top2();

	echo '<br /><div align="center">';

	echo lang_get( 'operation_successful' );
	echo '<br /><ul>';
	echo '<li>' . lang_get( 'email_updated' ) . '</li>';

	# Update password if the two match and are not empty
	if ( !is_blank( $f_password ) ) {
		if ( $f_password != $f_password_confirm ) {
			trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
		} else {
			user_set_password( auth_get_current_user_id(), $f_password );

			echo '<li>' . lang_get( 'password_updated' ) . '</li>';
		}
	}

	echo '</ul>';

	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
	echo '</div>';
	html_page_bottom1( __FILE__ );*/
?>
