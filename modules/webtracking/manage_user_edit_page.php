<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_user_id = gpc_get_int( 'user_id' );

	$t_user = user_get_row( $f_user_id );

	html_page_top1();
	html_page_top2();

	print_manage_menu();
?>

<br />


<!-- USER INFO -->
<div align="center">
<form method="post" action="index.php?m=webtracking&a=manage_user_update">
<table class="width75" cellspacing="1" border="0">
<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<?php echo lang_get( 'edit_user_title' ) ?>
	</td>
</tr>


<!-- Username -->
<tr <?php echo helper_alternate_class( 1 ) ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="70%">
		<?php echo $t_user['username'] ?>
	</td>
</tr>

<!-- Email -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php echo $t_user['email']  ?>
	</td>
</tr>

<!-- Access Level -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>    
		<select name="access_level">
			<?php print_enum_string_option_list( 'access_levels', $t_user['access_level'] ) ?>
		</select>
	</td>
</tr>

<!-- Enabled Checkbox -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="enabled" <?php check_checked( $t_user['enabled'], ON ); ?> />
	</td>
</tr>

<!-- Protected Checkbox -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'protected' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="protected" <?php check_checked( $t_user['protected'], ON ); ?> />
	</td>
</tr>

<!-- Submit Button -->
<tr>
	<td colspan="2" class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?/*?>

<br />

<!-- RESET AND DELETE -->
<div class="border-center">
<!-- Reset Button -->
	<form method="post" action="index.php?m=webtracking&a=manage_user_reset">
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'reset_password_button' ) ?>" />
	</form>

<!-- Delete Button -->
	<form method="post" action="index.php?m=webtracking&a=manage_user_delete">
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'delete_user_button' ) ?>" />
	</form>
</div>
<br />
<div align="center">
<?php
	if ( ON == config_get( 'send_reset_password' ) ) {
		echo lang_get( 'reset_password_msg' );
	} else {
		echo lang_get( 'reset_password_msg2' );
	}
?>
</div>

<?*/?>

<!-- PROJECT ACCESS (if permissions allow) -->
<?php if ( access_has_global_level( config_get( 'manage_user_threshold' ) ) ) {
?>
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=manage_user_proj_add">
<table class="width75" cellspacing="1">
<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
		<?php echo lang_get( 'add_user_title' ) ?>
	</td>
</tr>

<!-- Assigned Projects -->
<tr <?php echo helper_alternate_class( 1 ) ?> valign="top">
	<td class="category" width="30%">
		<?php echo lang_get( 'assigned_projects' ) ?>:
	</td>
	<td width="70%">
		<?php print_project_user_list( $t_user['id'] ) ?>
	</td>
</tr>

<!-- Unassigend Project Selection -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td class="category">
		<?php echo lang_get( 'unassigned_projects' ) ?>:
	</td>
	<td>
		<select name="project_id[]" multiple="multiple" size="5">
			<?php print_project_user_list_option_list2( $t_user['id'] ) ?>
		</select>
	</td>
</tr>

<!-- New Access Level -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<select name="access_level">
		<?php
			# No administrator choice
			print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) )
		?>
		</select>
	</td>
</tr>

<!-- Submit Buttom -->
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	} # End of PROJECT ACCESS conditional section
?>



<!-- ACCOUNT PREFERENCES -->
<?php
	include ( 'account_prefs_inc.php' );
	edit_account_prefs( $t_user['id'], false, false, 'index.php?m=webtracking&a=manage_user_page' );
?>

<?php html_page_bottom1( __FILE__ ) ?>
