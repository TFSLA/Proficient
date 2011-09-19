<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	access_ensure_can_close_bug( $f_bug_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php # Close Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=bug_close">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'close_bug_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'close_bug_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Close Form END ?>

<br />
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
			include( 'bug_view_inc.php' ); ?>
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
			include( 'bugnote_view_inc.php' )?>


<?php html_page_bottom1( __FILE__ ) ?>
