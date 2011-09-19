<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	html_page_top1();
	html_page_top2();

	print_manage_menu( 'index.php?m=webtracking&a=manage_user_create_page' );
?>
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=manage_user_create">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'create_new_account_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td width="75%">
		<input type="text" class="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email' ) ?>
	</td>
	<td>
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'password' ) ?>
	</td>
	<td>
		<input type="password" name="password" size="32" maxlength="32" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'verify_password' ) ?>
	</td>
	<td>
		<input type="password" name="password_verify" size="32" maxlength="32" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>
	</td>
	<td>
		<select name="access_level">
			<?php print_enum_string_option_list( 'access_levels', config_get( 'default_new_account_access_level' ) ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>
	</td>
	<td>
		<input type="checkbox" name="enabled" checked="checked" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'protected' ) ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="protected" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'create_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
