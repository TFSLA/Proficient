<?php
	# This page allows the user to edit his/her profile
	# Changes get POSTed to account_prof_update.php
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'profile_api.php' );
?>
<?php
	auth_ensure_user_authenticated();
	
	current_user_ensure_unprotected();
?>
<?php
	# protected account check
	current_user_ensure_unprotected();

	$f_profile_id	= gpc_get_int( 'profile_id' );
	$f_action		= gpc_get_string( 'action' );

	# If deleteing profile redirect to delete script
	if ( 'delete' == $f_action) {
		print_header_redirect( 'index.php?m=webtracking&a=account_prof_delete&profile_id=' . $f_profile_id );
	}
	# If Defaulting profile redirect to make default script
	else if ( 'default' == $f_action ) {
		print_header_redirect( 'index.php?m=webtracking&a=account_prof_make_default&profile_id=' . $f_profile_id );
	}

	$row = profile_get_row( auth_get_current_user_id(), $f_profile_id );

   	extract( $row, EXTR_PREFIX_ALL, 'v' );
?>

<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php # Edit Profile Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=account_prof_update">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="profile_id" value="<?php echo $v_id ?>" />
		<?php echo lang_get( 'edit_profile_title' ) ?>
	</td>
	<td class="right">
		<?php print_account_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'platform' ) ?>
	</td>
	<td width="75%">
		<input type="text" class="text" name="platform" size="32" maxlength="32" value="<?php echo string_attribute( $v_platform ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'operating_system' ) ?>
	</td>
	<td>
		<input type="text" class="text" name="os" size="32" maxlength="32" value="<?php echo string_attribute( $v_os ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" class="text" name="os_build" size="16" maxlength="16" value="<?php echo string_attribute( $v_os_build ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="8" wrap="virtual"><?php echo string_textarea( $v_description ) ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit Profile Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
