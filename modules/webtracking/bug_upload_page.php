<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=bug_resolve">
<table class="width75" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'resolve_bug_title' ) ?>
	</td>
</tr>


<!-- Resolution -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<select name="resolution">
			<?php print_enum_string_option_list( "resolution", FIXED ) ?>
		</select>
	</td>
</tr>


<!-- Duplicate ID -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<input type="text" class="text" name="duplicate_id" maxlength="7" />
	</td>
</tr>


<!-- Close Immediately (if enabled) -->
<?php if ( ON == config_get( 'allow_close_immediately' ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'close_immediately' ) ?>
	</td>
	<td>
		<input type="checkbox" name="close_now" />
	</td>
</tr>
<?php } ?>


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
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'resolve_bug_button' ) ?>" />
	</td>
</tr>


</table>
</form>
</div>

<br />
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
			include( 'bug_view_inc.php' ); ?>
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
			include( 'bugnote_view_inc.php' )?>

<?php html_page_bottom1( __FILE__ ) ?>
