<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	
	require_once( $AppUI->getModuleClass( 'timexp' ) );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );
	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );
	
	$f_bug_resolution = $t_bug->resolution > 10 ? $t_bug->resolution : FIXED;
	
	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );
	

?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<form method="post" name="feefbackForm" action="index.php?m=webtracking&a=bug_feedback">
<input type="hidden" name="hour_name" value="<?php echo $t_bug->summary ?>" />

<table class="width75" cellspacing="1" align="center">


<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'updated_bug_title' ) ?>
	</td>
</tr>


<!-- Bugnote -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" colspan="2">
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="center" colspan="2">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>


<!-- Submit Button -->
<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'updated_bug_button' ) ?>" />
	</td>
</tr>


</table>
</form>


<br />
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
			include( 'bug_view_inc.php' ); ?>
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
			include( 'bugnote_view_inc.php' )?>


<?php html_page_bottom1( __FILE__ ) ?>
