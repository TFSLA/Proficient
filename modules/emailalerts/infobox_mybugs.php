<center><b><font color=red><?=$AppUI->_( 'Infobox Under Construction' )?></font></b></center>
<?/*?>
<table class="width100" cellspacing="1">
<?php # -- Bug list column header row -- ?>
<tr class="row-category">
	<td class="center">&nbsp;</td>

	<td class="center">&nbsp;</td>

	<?php # -- Priority column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( 'P', 'priority', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'priority' ) ?>
	</td>

	<?php # -- Bug ID column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'id' ), 'id', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'id' ) ?>
	</td>

	<?php # -- Bugnote count column -- ?>
	<td class="center">
		#
	</td>

	<?php # -- Attachment indicator --
		if ( ON == $t_show_attachments ) {
		  echo '<td class="center">';
			echo '<img src="' . $t_icon_path . 'attachment.png' . '" alt="" />';
			echo '</td>';
		}
	?>

	<?php # -- Category column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'category' ), 'category', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'category' ) ?>
	</td>

	<?php # -- Severity column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'severity' ) ?>
	</td>

	<?php # -- Status column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'status' ), 'status', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'status' ) ?>
	</td>

	<?php # -- Last Updated column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'last_updated' ) ?>
	</td>

	<?php # -- Summary column -- ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'summary' ) ?>
	</td>
</tr>
<?php # -- Spacer row -- ?>
<tr>
	<td class="spacer" colspan="<?php echo $col_count ?>">&nbsp;</td>
</tr>
<?php mark_time( 'begin loop' ); ?>
<?php # -- Loop over bug rows and create $v_* variables -- ?>
<?php
	for($i=0; $i < sizeof( $rows ); $i++) {
		# prefix bug data with v_

		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display_links( $v_summary );
		$t_last_updated = date( config_get( 'short_date_format' ), $v_last_updated );

		# choose color based on status
		$status_color = get_status_color( $v_status );

		# grab the bugnote count
		$bugnote_count = bug_get_bugnote_count( $v_id );

		# Check for attachments
		$t_attachment_count = 0;
		if ( ON == $t_show_attachments 
			&& ( $v_reporter_id == auth_get_current_user_id() 
				|| access_has_bug_level( config_get( 'view_attachments_threshold' ), $v_id ) ) ) {
		   $t_attachment_count = file_bug_attachment_count( $v_id );
		}

		# grab the project name
		$project_name = project_get_field( $v_project_id, 'name' );

		if ( $bugnote_count > 0 ) {
			$v_bugnote_updated = bug_get_newest_bugnote_timestamp( $v_id );
		}
?>
<tr bgcolor="<?php echo $status_color ?>">
	<?php # -- Checkbox -- ?>
<?php
	if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $v_id ) ) {
		$t_checkboxes_exist = true;
?>
	<td>
		<input type="checkbox" name="bug_arr[]" value="<?php echo "$v_id" ?>" />
	</td>
<?php
	} else {
		echo '<td>&nbsp;</td>';
}
?>
	
	<?php # -- Pencil shortcut -- ?>
	<td class="center">
	<?php
		if ( access_has_bug_level( UPDATER, $v_id ) ) {
			echo '<a href="' . string_get_bug_update_url( $v_id ) . '"><img border="0" src="' . $t_icon_path . 'update.png' . '" alt="' . lang_get( 'update_bug_button' ) . '" /></a>';
		} else {
			echo '&nbsp;';
		}
	?>
	</td>

	<?php # -- Priority -- ?>
	<td class="center">
		<?php
			if ( ON == config_get( 'show_priority_text' ) ) {
				print_formatted_priority_string( $v_status, $v_priority );
			} else {
				print_status_icon( $v_priority );
			}
		?>
	</td>

	<?php # -- Bug ID and details link -- ?>
	<td class="center">
		<?php print_bug_link( $v_id ) ?>
	</td>

	<?php # -- Bugnote count -- ?>
	<td class="center">
		<?php
			if ( $bugnote_count > 0 ) {
				$t_bugnote_link = '<a href="' . string_get_bug_view_url( $v_id ) . '&amp;nbn=' . $bugnote_count . '#bugnotes">' . $bugnote_count . '</a>'; 
				if ( $v_bugnote_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
					echo '<span class="bold">';
					echo $t_bugnote_link;
					echo '</span>';
				} else {
					echo $t_bugnote_link;
				}
			} else {
				echo '&nbsp;';
			}
		?>
	</td>

	<?php # -- Attachment indicator --
	  
		if ( ON == $t_show_attachments ) {
		  echo '<td class="center">';
			if ( 0 < $t_attachment_count ) {
				echo '<a href="' . string_get_bug_view_url( $v_id ) . '#attachments">';
				echo '<img border="0" src="' . $t_icon_path . 'attachment.png' . '"';
				echo ' alt="' . lang_get( 'attachment_alt' ) . '"';
				echo ' title="' . $t_attachment_count . ' ' . lang_get( 'attachments' ) . '"';
				echo ' />';
				echo '</a>';
			} else {
			  echo '&nbsp;';
			}
			echo '</td>';
		}
	?>

	<?php # -- Category -- ?>
	<td class="center">
		<?php
			# type project name if viewing 'all projects'
			if ( ON == config_get( 'show_bug_project_links' ) &&
				 helper_get_current_project() == ALL_PROJECTS ) {
				echo '<small>[';
				print_view_bug_sort_link( $project_name, 'project_id', $t_sort, $t_dir );
				echo ']</small><br />';
			}

			echo string_display( $v_category );
		?>
	</td>
	<?php # -- Severity -- ?>
	<td class="center">
		<?php print_formatted_severity_string( $v_status, $v_severity ) ?>
	</td>
	<?php # -- Status / Handler -- ?>
	<td class="center">
		<?php
			echo '<u><a title="' . get_enum_element( 'resolution', $v_resolution ) . '">' . get_enum_element( 'status', $v_status ) . '</a></u>';
			# print username instead of status
			if ( $v_handler_id > 0 && ON == config_get( 'show_assigned_names' ) ) {
				echo ' (';
				print_user( $v_handler_id );
				echo ')';
			}
		?>
	</td>
	<?php # -- Last Updated -- ?>
	<td class="center">
		<?php
			if ( $v_last_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
				echo '<span class="bold">'.$t_last_updated.'</span>';
			} else {
				echo $t_last_updated;
			}
		?>
	</td>
	<?php # -- Summary -- ?>
	<td class="left">
		<?php
			echo $v_summary;
			if ( VS_PRIVATE == $v_view_state ) {
				echo ' <img src="' . $t_icon_path . 'protected.gif" width="8" height="15" alt="' . lang_get( 'private' ) . '" />';
			}
		 ?>
	</td>
</tr>
<?php # -- end of Repeating bug row -- ?>
<?php
	}
?>
<?php # -- ====================== end of BUG LIST ========================= -- ?>

<?*/?>