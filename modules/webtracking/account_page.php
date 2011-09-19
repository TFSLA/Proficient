<?php

	# CALLERS
	#	This page is called from:
	#	- print_menu()
	#	- print_account_menu()
	#	- header redirects from account_*.php

	# EXPECTED BEHAVIOUR
	#	- Display the user's current settings
	#	- Allow the user to edit their settings
	#	- Allow the user to save their changes
	#	- Allow the user to delete their account if account deletion is enabled

	# CALLS
	#	This page calls the following pages:
	#	- account_update.php  (to save changes)
	#	- account_delete.php  (to delete the user's account)

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- The user's account must not be protected

	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
    $row = user_get_row( auth_get_current_user_id() );
	extract( $row, EXTR_PREFIX_ALL, 'u' );

	$t_ldap = ( LDAP == config_get( 'login_method' ) );

	# In case we're using LDAP to get the email address... this will pull out
	#  that version instead of the one in the DB
	$u_email = user_get_email( $u_id, $u_username );

	html_page_top1();
	html_page_top2();
?>

<!-- # Edit Account Form BEGIN -->
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=account_update">
<table class="width75" cellspacing="1">

	<!-- Headings -->
	<tr>
		<td class="form-title">
			<?php echo lang_get( 'edit_account_title' ) ?>
		</td>
		<td class="right">
			<?php print_account_menu( 'index.php?m=webtracking&a=account_page' ) ?>
		</td>
	</tr>

<?php if ( $t_ldap ) { ?> <!-- With LDAP -->

	<!-- Username -->
	<tr class="row-1">
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo $u_username ?>
		</td>
	</tr>

	<!-- Password -->
	<tr class="row-2">
		<td colspan="2">
			The password settings are controlled by your LDAP entry,<br />
			hence cannot be edited here.
		</td>
	</tr>

<?php } else { ?> <!-- Without LDAP -->

	<!-- Username -->
	<tr class="row-1">
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo $u_username ?>
		</td>
	</tr>

	<!-- Password -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'password' ) ?>
		</td>
		<td>
			<input type="password" size="32" maxlength="32" name="password" />
		</td>
	</tr>

	<!-- Password confirmation -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'confirm_password' ) ?>
		</td>
		<td>
			<input type="password" size="32" maxlength="32" name="password_confirm" />
		</td>
	</tr>

<?php } ?> <!-- End LDAP conditional -->

<?php if ( $t_ldap && ON == config_get( 'use_ldap_email' ) ) { ?> <!-- With LDAP Email-->

	<!-- Email -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
			<?php echo $u_email ?>
		</td>
	</tr>

<?php } else { ?> <!-- Without LDAP Email -->

	<!-- Email -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
			<?php print_email_input( 'email', $u_email ) ?>
		</td>
	</tr>

<?php } ?> <!-- End LDAP Email conditional -->

	<!-- Access level -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'access_level' ) ?>
		</td>
		<td>
			<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
		</td>
	</tr>

	<!-- Project access level -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'access_level_project' ) ?>
		</td>
		<td>
			<?php echo get_enum_element( 'access_levels', current_user_get_access_level() ) ?>
		</td>
	</tr>

	<!-- Assigned project list -->
	<tr class="row-2" valign="top">
		<td class="category">
			<?php echo lang_get( 'assigned_projects' ) ?>
		</td>
		<td>
			<?php print_project_user_list( auth_get_current_user_id() ) ?>
		</td>
	</tr>

	<!-- BUTTONS -->
	<tr>
		<td>&nbsp;</td>
		<!-- Update Button -->
		<td>
			<input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</td>
	</tr>
</table>
</form>
</div>

<br />
<?php # Delete Account Form BEGIN ?>
<?php
	# check if users can't delete their own accounts
	if ( ON == config_get( 'allow_account_delete' ) ) {
?>

<!-- Delete Button -->
<div class="border-center">
	<form method="post" action="index.php?m=webtracking&a=account_delete">
	<input type="submit" class="button" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
	</form>
</div>

<?php } ?>
<?php # Delete Account Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
