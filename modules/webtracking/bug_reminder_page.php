<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php # Send reminder Form BEGIN ?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="index.php?m=webtracking&a=bug_reminder">
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'bug_reminder' ) ?>
	</td>
</tr>
<tr>
	<td class="category">
		<?php echo lang_get( 'to' ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'reminder' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<select name="to[]" multiple="multiple" size="10">
			<?php echo print_project_user_option_list() ?>
		</select>
	</td>
	<td class="center">
		<textarea name="body" cols="65" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'bug_send_button' ) ?>">
	</td>
</tr>
</form>
</table>
</div>
<?php # Send reminder Form END ?>
</form>
</table>

<br />
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
			include( 'bug_view_inc.php' ); ?>
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
			include( 'bugnote_view_inc.php' )?>


<?php html_page_bottom1( __FILE__ ) ?>
