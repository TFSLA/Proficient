<?php
	# CALLERS
	#	This page is submitted to by the following pages:
	#	- bugnote_inc.php

	# EXPECTED BEHAVIOUR
	#	Allow the user to modify the text of a bugnote, then submit to
	#	bugnote_update.php with the new text

	# RESTRICTIONS & PERMISSIONS
	#	- none beyond API restrictions
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );

	# Check if the current user is allowed to edit the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_reporter_id ) || 
	 	( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}

	# Check if the bug has been resolved
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	if ( bug_get_field( $t_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' ) ) {
		# @@@ The error should be more generic.
		trigger_error( ERROR_BUG_RESOLVED_ACTION_DENIED, ERROR );
	}

	$t_bugnote_text = string_textarea( bugnote_get_text( $f_bugnote_id ) );

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $t_bug_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=bugnote_update">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="bugnote_id" value="<?php echo $f_bugnote_id ?>" />
		<?php echo lang_get( 'edit_bugnote_title' ) ?>
	</td>
	<td class="right">
		<?php print_bracket_link( $t_redirect_url, lang_get( 'go_back' ) ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea cols="80" rows="10" name="bugnote_text" wrap="virtual"><?php echo $t_bugnote_text ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
