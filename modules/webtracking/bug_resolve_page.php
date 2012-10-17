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
<script language="JavaScript"><!--
function validateResolve(){
	var frm = document.forms["resolveForm"];
	var rta = true;

	if ( trim( frm.hours.value ) != "" ) {
		var valor = parseFloat(frm.hours.value);
		if (isNaN(valor)){
			alert("<?php echo $AppUI->_('timexpValue');?>");
			rta = false;
			frm.hours.focus();
		}else if( valor < 0 ){
			alert("<?php echo $AppUI->_('timexpValue');?>");
			rta = false;
			frm.hours.focus();
		}
	}

	return rta;
}
//-->
</script>
<form method="post" name="resolveForm" action="index.php?m=webtracking&a=bug_resolve" onsubmit="return validateResolve();">
<input type="hidden" name="dosql" value="do_timexp_aed" />
<input type="hidden" name="hour_name" value="<?php echo $t_bug->summary ?>" />

<table class="width75" cellspacing="1" align="center">


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
			<?php print_enum_string_option_list( "resolution", $f_bug_resolution ) ?>
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


<!-- Reported Hours -->
<?php /*
<tr>
	<td class="form-title" colspan="2">
		<?php echo $AppUI->_("Report worked hours") ?>
	</td>
</tr> */ ?>
<!-- <tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo $AppUI->_("Worked hours") ?>
	</td>
	<td>
		<input type="text" class="text" name="hours" maxlength="7" value="0" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo $AppUI->_("Billable") ?>
	</td>
	<td>
		<?php echo arraySelect( $billables, 'billable', 'size="1" class="text"', 1, true); ?>
	</td>
</tr>			 -->




<!-- Submit Button -->
<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'resolve_bug_button' ) ?>" />
	</td>
</tr>


</table>
</form>


<br />
<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
                                    $orig = 'resolve';
			include( 'bug_view_inc.php' ); ?>

<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
			include( 'bugnote_view_inc.php' )?>

<? # Incluyo Box de base de conocimientos
	//include($t_mantis_dir.'bug_kb_view.inc.php'); ?>

<?php html_page_bottom1( __FILE__ ) ?>
