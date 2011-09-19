<?php
	# This include file prints out the list of bugnotes attached to the bug
	# $f_bug_id must be set and be set to the bug id
?>
<?php
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path . 'date_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	# grab the user id currently logged in
	$t_user_id	= auth_get_current_user_id();
	$c_bug_id		= (integer)$f_bug_id;

	# get the bugnote data
	$query = "SELECT *,UNIX_TIMESTAMP(date_submitted) as date_submitted
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$c_bug_id'
			ORDER BY date_submitted $g_bugnote_order";
	$result = db_query($query);
	$num_notes = db_num_rows($result);
?>

<?php # Bugnotes BEGIN ?>
<br />
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == $num_notes ) {
?>
<tr>
	<td class="print" colspan="2">
		<?php echo lang_get( 'no_bugnotes_msg' ) ?>
	</td>
</tr>
<?php } else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
<?php
	for ( $i=0; $i < $num_notes; $i++ ) {
		# prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v3' );
		$v3_date_submitted = format_date( config_get( 'normal_date_format' ), ( $v3_date_submitted ) );

		# grab the bugnote text and id and prefix with v3_
		$query = "SELECT note, id
				FROM $g_mantis_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query( $query );
		$v3_note = db_result( $result2, 0, 0 );
		$v3_bugnote_text_id = db_result( $result2, 0, 1 );

		$v3_note = string_display_links( $v3_note );
?>
<tr>
	<td class="print-spacer" colspan="2">
		<hr size="1" />
	</td>
</tr>
<tr>
	<td class="nopad" valign="top" width="15%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="print">
				<?php print_user( $v3_reporter_id ) ?>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php echo $v3_date_submitted ?>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		</table>
	</td>
	<td class="nopad" valign="top" width="85%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="print">
				<?php echo $v3_note ?>
			</td>
		</tr>
		</table>
	</td>
</tr>
<?php
		} # end for loop
	} # end else
?>
</table>
<?php # Bugnotes END ?>