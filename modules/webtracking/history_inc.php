<?php
	# This include file prints out the bug history

	# $f_bug_id must already be defined
?>
<?php
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'history_api.php' );
?>

<a name="history" id="history" /><br />
<?php
	$t_history = history_get_events_array( $f_bug_id );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<?php echo lang_get( 'bug_history' ) ?>
	</td>
</tr>
<tr class="row-category">
	<td class="small-caption">
		<?php echo lang_get( 'date_modified' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'field' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'change' ) ?>
	</td>
</tr>
<?php
	foreach ( $t_history as $t_item ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">
		<?php echo $t_item['date'] ?>
	</td>
	<td class="small-caption">
		<?php print_user( $t_item['userid'] ) ?>
	</td>
	<td class="small-caption">
		<?php echo string_display( $t_item['note'] ) ?>
	</td>
	<td class="small-caption">
		<?php echo string_display( $t_item['change'] ) ?>
	</td>
</tr>
<?php
	} # end for loop
?>
</table>
