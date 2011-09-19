<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'email_api.php' );
?>
<?php
	$f_username		= gpc_get_string( 'username' );
	$f_email		= gpc_get_string( 'email' );

	$f_username = trim( $f_username );
	$f_email	= trim( $f_email );
	
	# Check to see if signup is allowed
	if ( OFF == config_get( 'allow_signup' ) ) {
		print_header_redirect( 'index.php?m=webtracking&a=login_page' );
		exit;
	}

	# check for empty username
	if ( is_blank( $f_username ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}

	$f_email = email_append_domain( $f_email );

	# Check for a properly formatted email with valid MX record
	#  Don't allow blank emails when signing up though, no matter what.
	if ( is_blank( $f_email ) || !email_is_valid( $f_email ) ) {
		echo $f_email.' '.lang_get( 'invalid_email' ).'<br />';
		echo '<a href="index.php?m=webtracking&a=signup_page">'.lang_get( 'proceed' ).'</a>';
		exit;
	}

	# Passed our checks.  Insert into DB then send email.
	if ( !user_signup( $f_username, $f_email ) ) {
		echo lang_get( 'account_create_fail' ).'<br />';
		echo '<a href="index.php?m=webtracking&a=signup_page">'.lang_get( 'proceed' ).'</a>';
		exit;
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2a() ?>

<br />
<div align="center">
<?php
	echo "[$f_username - $f_email] ".lang_get( 'password_emailed_msg' ).'<br />'.lang_get( 'no_reponse_msg').'<br /><br />';

	print_bracket_link( 'index.php?m=webtracking&a=login_page', lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1a( __FILE__ ) ?>
