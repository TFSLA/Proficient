<?php require_once( 'core.php' ) ?>
<?php
	# Check for invalid access to signup page
	if ( OFF == config_get( 'allow_signup' ) ) {
		print_header_redirect( 'index.php?m=webtracking&a=login_page' );
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2a() ?>

<br />
<div align="center">
<?php echo lang_get( 'signup_info' ) ?>
</div>

<?php # Signup form BEGIN ?>
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=signup">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'signup_title' ) ?>
	</td>
	<td class="right">
		<?php print_bracket_link( 'index.php?m=webtracking&a=login_page', lang_get( 'go_back' ) ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="70%">
		<input type="text" class="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'signup_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Signup form END ?>

<?php html_page_bottom1a( __FILE__ ) ?>
