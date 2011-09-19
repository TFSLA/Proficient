<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	
	$titleBlock = new CTitleBlock( $AppUI->_('Access').' '.$AppUI->_('Denied'), 'bugicon.gif', $m, "$m.$a" );
	$titleBlock->show();
	
	echo $AppUI->_("You have not enough permissions to perform this action.");

	html_page_bottom1( __FILE__ );


/*
die();
	# Login page POSTs results to login.php
	# Check to see if the user is already logged in

?>
<?php
	require_once( 'core.php' );
	html_page_top1();
	html_page_top2a();
?>

<br />
<div align="center">
<?php
	$f_error		= gpc_get_bool( 'error' );
	$f_cookie_error	= gpc_get_bool( 'cookie_error' );
	$f_return		= gpc_get_string( 'return', '' );

	# Only echo error message if error variable is set
	if ( $f_error ) {
		echo lang_get( 'login_error' ) . '<br />';
	}
	if ( $f_cookie_error ) {
		echo lang_get( 'login_cookies_disabled' ) . '<br />';
	}

	# Display short greeting message
	echo lang_get( 'login_page_info' );
?>
</div>

<!-- Login Form BEGIN -->
<br />
<div align="center">
<form name="login_form" method="post" action="index.php?m=webtracking&a=login">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<?php
			if ( !is_blank( $f_return ) ) {
			?>
				<input type="hidden" name="return" value="<?php echo $f_return ?>" />
				<?php
			}
			echo lang_get( 'login_title' ) ?>
	</td>
	<td class="right">
	<?php
		if ( ON == config_get( 'allow_anonymous_login' ) ) {
			print_bracket_link( 'index.php?m=webtracking&a=login_anon', lang_get( 'login_anonymously' ) );
		}
	?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td width="75%">
		<input type="text" class="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'password' ) ?>
	</td>
	<td>
		<input type="password" name="password" size="16" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'save_login' ) ?>
	</td>
	<td>
		<input type="checkbox" name="perm_login" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'login_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	print_signup_link();

	#
	# Do some checks to warn administrators of possible security holes.
	# Since this is considered part of the admin-checks, the strings are not translated.
	#

	# Warning, if plain passwords are selected
	if ( config_get( 'login_method' ) === PLAIN ) {
		echo '<div class="warning" align="center">';
		echo '<p><font color="red"><strong>WARNING:</strong> Plain password authentication is used, this will expose your passwords to administrators.</font></p>';
		echo '</div>';
	}

	# Generate a warning if administrator/root is valid.
	if ( user_get_id_by_name( 'administrator' ) !== false ) {
		if ( auth_does_password_match( user_get_id_by_name( 'administrator' ), 'root' ) ) {
			echo '<div class="warning" align="center">';
			echo '<p><font color="red"><strong>WARNING:</strong> You should disable the default "administrator" account or change its password.</font></p>';
			echo '</div>';
		}
	}

	# Check if the admin directory is available and is readable.
	
	$t_admin_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
	if ( is_dir( $t_admin_dir ) && is_readable( $t_admin_dir ) ) {
			echo '<div class="warning" align="center">';
			echo '<p><font color="red"><strong>WARNING:</strong> Admin directory should be removed.</font></p>';
			echo '</div>';
	}
?>

<script type="text/javascript" language="JavaScript">
<!--
	window.document.login_form.username.focus();
//-->
</script>

<?php html_page_bottom1a( __FILE__ ) */?>
