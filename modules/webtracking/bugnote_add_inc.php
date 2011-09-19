<?php if ( ( $t_bug->status < config_get( 'bug_resolved_status_threshold' ) ) &&
		( access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<br />
<a name="addbugnote">
<form method="post" action="index.php?m=webtracking&a=bugnote_add">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'bugnote' ) ?>
	</td>
	<td width="75%">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<?php if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'private' ) ?>
	</td>
	<td>
		<input type="checkbox" name="private" />
	</td>
</tr>
<?php } ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_bugnote_button' ) ?>" />
	</td>
</tr>
</table>
</form>
<?php # Bugnote Add Form END ?>
<?php } ?>
