<?php
	# Word 2000 export page
	# The bugs displayed in print_all_bug_page.php are saved in a .doc file
	# The IE icon allows to see or directly print the same result
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_type_page	= gpc_get_string( 'type_page', 'word' );
	$f_search		= gpc_get_string( 'search', false ); # @@@ need a better default
	$f_offset		= gpc_get_int( 'offset', 0 );
	$f_export		= gpc_get_string( 'export' );
	$f_show_flag	= gpc_get_bool( 'show_flag' );

	# word or html export
	if ( $f_type_page != 'html' ) {
		$t_export_title = $g_page_title."_word";
		$t_export_title = ereg_replace( '[\/:*?"<>|]', '', $t_export_title );
		header( 'Content-Type: application/msword' );
		header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.doc"' );
	}

	$t_cookie_value = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );

	# check to see if the cookie does not exist
	if ( is_blank( $t_cookie_value ) ) {
		print_header_redirect( 'index.php?m=webtracking&a=view_all_set&type=0&amp;print=1' );
	}

	# check to see if new cookie is needed
	$t_setting_arr 			= explode( '#', $t_cookie_value );
	if ( $t_setting_arr[0] != $g_cookie_version ) {
		print_header_redirect( 'index.php?m=webtracking&a=view_all_set&type=0&amp;print=1' );
	}

	# Load preferences
	$f_show_category 		= $t_setting_arr[1];
	$f_show_severity	 	= $t_setting_arr[2];
	$f_show_status 			= $t_setting_arr[3];
	$f_per_page 			= $t_setting_arr[4];
	$f_highlight_changed 	= $t_setting_arr[5];
	$f_hide_closed 			= $t_setting_arr[6];
	$f_reporter_id 				= $t_setting_arr[7];
	$f_handler_id 			= $t_setting_arr[8];
	$f_sort 				= $t_setting_arr[9];
	$f_dir		 			= $t_setting_arr[10];
	$f_start_month			= $t_setting_arr[11];
	$f_start_day 			= $t_setting_arr[12];
	$f_start_year 			= $t_setting_arr[13];
	$f_end_month 			= $t_setting_arr[14];
	$f_end_day				= $t_setting_arr[15];
	$f_end_year				= $t_setting_arr[16];
	$f_hide_resolved 			= $t_setting_arr[18];

	# Clean input
	$c_offset 				= (integer)$f_offset;
	$c_user_id				= (integer)$f_reporter_id;
	$c_assign_id			= (integer)$f_handler_id;
	$c_per_page				= (integer)$f_per_page;
	$c_show_category		= addslashes( $f_show_category );
	$c_show_severity		= addslashes( $f_show_severity );
	$c_show_status			= addslashes( $f_show_status );
	$c_search				= addslashes( $f_search );
	$c_sort					= addslashes( $f_sort );

	if ( 'DESC' == $f_dir ) {
		$c_dir = 'DESC';
	} else {
		$c_dir = 'ASC';
	}

	# Limit reporters to only see their reported bugs
	if ( ( ON == $g_limit_reporters ) &&
		( !access_has_project_level( UPDATER ) ) ) {
		$c_user_id = auth_get_current_user_id();
	}

	# Build our query string based on our viewing criteria

	$query = 'SELECT DISTINCT *, UNIX_TIMESTAMP(last_updated) as last_updated, UNIX_TIMESTAMP(date_submitted) as date_submitted
			 FROM $g_mantis_bug_table';

	$t_project_id = helper_get_current_project();

	# project selection
	if ( ALL_PROJECTS == $t_project_id ) { # ALL projects
		$t_access_level = current_user_get_field( 'access_level' );
		$t_user_id = auth_get_current_user_id();

		$t_pub = VS_PUBLIC;
		$t_prv = VS_PRIVATE;
		$query2 = "SELECT DISTINCT( p.project_id )
			FROM $g_mantis_project_table p, $g_mantis_project_user_list_table u
			WHERE (p.enabled=1 AND
				p.view_state='$t_pub') OR
				(p.enabled=1 AND
				p.view_state='$t_prv' AND
				u.user_id='$t_user_id'  AND
				u.project_id=p.project_id)
			ORDER BY p.project_name";
		$result2 = db_query( $query2 );
		$project_count = db_num_rows( $result2 );

		if ( 0 == $project_count ) {
			$t_where_clause = ' WHERE 1=0';
		} else {
			$t_where_clause = ' WHERE (';
			for ( $i=0;$i<$project_count;$i++ ) {
				$row = db_fetch_array( $result2 );
				extract( $row, EXTR_PREFIX_ALL, 'v' );

				$t_where_clause .= "(project_id='$v_id')";
				if ( $i < $project_count - 1 ) {
					$t_where_clause .= ' OR ';
				}
			} # end for
			$t_where_clause .= ')';
		}
	} else {
		$t_where_clause = " WHERE project_id='$t_project_id'";
	}
	# end project selection

	if ( $f_reporter_id != 'any' ) {
		$t_where_clause .= " AND reporter_id='$c_user_id'";
	}

	if ( 'none' == $f_handler_id ) {
		$t_where_clause .= ' AND handler_id=0';
	} else if ( $f_handler_id != 'any' ) {
		$t_where_clause .= " AND handler_id='$c_assign_id'";
	}

	$t_clo_val = CLOSED;
	if ( ( 'on' == $f_hide_closed  )&&( 'closed' != $f_show_status ) ) {
		$t_where_clause = $t_where_clause." AND status<>'$t_clo_val'";
	}

	$t_resolved_val = RESOLVED;
	if ( ( 'on' == $f_hide_resolved  )&&( 'resolved' != $f_show_status ) ) {
		$t_where_clause = $t_where_clause." AND status<>'$t_resolved_val'";
	}

	if ( $f_show_category != 'any' ) {
		$t_where_clause = $t_where_clause." AND category='$c_show_category'";
	}
	if ( $f_show_severity != 'any' ) {
		$t_where_clause = $t_where_clause." AND severity='$c_show_severity'";
	}
	if ( $f_show_status != 'any' ) {
		$t_where_clause = $t_where_clause." AND status='$c_show_status'";
	}

	# Simple Text Search - Thnaks to Alan Knowles
	if ( $f_search ) {
		$t_columns_clause = " $g_mantis_bug_table.*";

		$t_where_clause .= " AND ((summary LIKE '%$c_search%')
							OR (description LIKE '%$c_search%')
							OR (steps_to_reproduce LIKE '%$c_search%')
							OR (additional_information LIKE '%$c_search%')
							OR ($g_mantis_bug_table.id LIKE '%$c_search%')
							OR ($g_mantis_bugnote_text_table.note LIKE '%$c_search%'))
							AND $g_mantis_bug_text_table.id = $g_mantis_bug_table.bug_text_id";

		$t_from_clause = " FROM $g_mantis_bug_table, $g_mantis_bug_text_table
							LEFT JOIN $g_mantis_bugnote_table      ON $g_mantis_bugnote_table.bug_id  = $g_mantis_bug_table.id
							LEFT JOIN $g_mantis_bugnote_text_table ON $g_mantis_bugnote_text_table.id = $g_mantis_bugnote_table.bugnote_text_id ";
	} else {
		$t_columns_clause = ' *';
		$t_from_clause = " FROM $g_mantis_bug_table";
	}

	if ( is_blank( $c_sort ) ) {
		$c_sort='last_updated';
	}
	$query  = 'SELECT DISTINCT '.$t_columns_clause.', UNIX_TIMESTAMP(last_updated) as last_updated, UNIX_TIMESTAMP(date_submitted) as date_submitted';
	$query .= $t_from_clause;
	$query .= $t_where_clause;

	$query = $query." ORDER BY '$c_sort' $c_dir";
	if ( $f_sort != 'priority' ) {
		$query = $query.', priority DESC';
	}

	$query = $query." LIMIT $c_offset, $c_per_page";

	# perform query
	$result = db_query( $query );
	$row_count = db_num_rows( $result );

?>

<?php # Word Export ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<?php html_page_top1() ?>
<?php html_head_end() ?>
<?php html_body_begin() ?>

<?php
	//$t_bug_arr_sort[$row_count]=-1;
	$f_bug_arr = explode_enum_string( $f_export );

	# $t_bug_arr_sort contains 1 if the field as been selected, 0 if not
	for( $i=0; $i < $row_count; $i++ ) {
		if ( isset( $f_bug_arr[$i] ) ) {
			$index = $f_bug_arr[$i];
			$t_bug_arr_sort[$index]=1;
		}
	}

	for( $j=0; $j < $row_count; $j++ ) {

		# prefix bug data with v_
		$row = db_fetch_array( $result );

		extract( $row, EXTR_PREFIX_ALL, 'v' );
		$t_last_updated = date( $g_short_date_format, $v_last_updated );

		# grab the bugnote count
		$bugnote_count = bug_get_bugnote_count( $v_id );

		# grab the project name
		$project_name = project_get_field( $v_project_id, 'name' );

		# bug text infos
		$query3 = "SELECT *
			FROM $g_mantis_bug_text_table
			WHERE id='$v_bug_text_id'";
		$result3 = db_query( $query3 );
		$row = db_fetch_array( $result3 );
		extract( $row, EXTR_PREFIX_ALL, 'v2' );

		$v_os 						= string_display( $v_os );
		$v_os_build					= string_display( $v_os_build );
		$v_platform					= string_display( $v_platform );
		$v_version 					= string_display( $v_version );
		$v_summary 					= string_display_links( $v_summary );
		$v2_description 			= string_display_links( $v2_description );
		$v2_steps_to_reproduce 		= string_display_links( $v2_steps_to_reproduce );
		$v2_additional_information 	= string_display_links( $v2_additional_information );

		# display the available and selected bugs
		if ( isset( $t_bug_arr_sort[$j] ) || ( $f_show_flag==0 )) {
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'viewing_bug_advanced_details_title' ) ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" />
	</td>
</tr>
<tr class="print-category">
	<td class="print" width="16%">
		<?php echo lang_get( 'id' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'category' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'severity' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'reproducibility' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'date_submitted' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'last_update' ) ?>:
	</td>
</tr>
<tr class="print">
	<td class="print">
		<?php echo $v_id ?>
	</td>
	<td class="print">
		<?php echo $v_category ?>
	</td>
	<td class="print">
		<?php echo get_enum_element( 'severity', $v_severity ) ?>
	</td>
	<td class="print">
		<?php echo get_enum_element( 'reproducibility', $v_reproducibility ) ?>
	</td>
	<td class="print">
		<?php print_date( config_get( 'normal_date_format' ), $v_date_submitted ) ?>
	</td>
	<td class="print">
		<?php print_date( config_get( 'normal_date_format' ), $v_last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'reporter' ) ?>:
	</td>
	<td class="print">
		<?php print_user_with_subject( $v_reporter_id, $v_id ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'platform' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_platform ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'assigned_to' ) ?>:
	</td>
	<td class="print">
		<?php print_user_with_subject( $v_handler_id, $v_id ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'os' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_os ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'priority' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'priority', $v_priority ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'os_version' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_os_build ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'status' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'status', $v_status ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'product_version' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_version ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'product_build' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_build?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'resolution' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'resolution', $v_resolution ) ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'projection' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'projection', $v_projection ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'duplicate_id' ) ?>:
	</td>
	<td class="print">
		<?php print_duplicate_id( $v_duplicate_id ) ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'eta' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'eta', $v_eta ) ?>
	</td>
	<td class="print" colspan="4">&nbsp;</td>
</tr>
<?php
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
?>
<tr class="print">
	<td class="print-category">
		<?php echo $t_def['name'] ?>:
	</td>
	<td class="print" colspan="5">
		<?php
			echo custom_field_get_value( $t_id, $v_id );
		?>
	</td>
</tr>
<?php
}       // foreach
?>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'summary' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v_summary ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'description' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_description ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_steps_to_reproduce ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'additional_information' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_additional_information ?>
	</td>
</tr>
<?php
	# account profile description
	if ( $v_profile_id > 0 ) {
		$query4 = "SELECT description
				FROM $g_mantis_user_profile_table
				WHERE id='$v_profile_id'";
		$result4 = db_query( $query4 );
		$t_profile_description = '';
		if ( db_num_rows( $result4 ) > 0 ) {
			$t_profile_description = db_result( $result4, 0 );
		}
		$t_profile_description = string_display( $t_profile_description );

?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'system_profile' ) ?>
	</td>
	<td class="print" colspan="5">
		<?php echo $t_profile_description ?>
	</td>
</tr>
<?php
	} # profile description
?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'attached_files' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php
			$query5 = "SELECT *
					FROM $g_mantis_bug_file_table
					WHERE bug_id='$v_id'";
			$result5 = db_query( $query5 );
			$num_files = db_num_rows( $result5 );
			for ( $i=0;$i<$num_files;$i++ ) {
				$row = db_fetch_array( $result5 );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );
				$v2_filesize = round( $v2_filesize / 1024 );
				$v2_date_added = format_date( config_get( 'normal_date_format' ), ( $v2_date_added ) );

				switch ( $g_file_upload_method ) {
					case DISK:	PRINT "$v2_filename ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
							break;
					case DATABASE:	PRINT "$v2_filename ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
							break;
				}

				if ( $i != ( $num_files - 1 ) ) {
					PRINT '<br />';
				}
			}
		?>
	</td>
</tr>
<?php
	# get the bugnote data
	$query6 = "SELECT *,UNIX_TIMESTAMP(date_submitted) as date_submitted
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$v_id'
			ORDER BY date_submitted $g_bugnote_order";
	$result6 = db_query( $query6 );
	$num_notes = db_num_rows( $result6 );
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
	<?php }
		else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
			<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
	<?php
		for ( $k=0; $k < $num_notes; $k++ ) {
			# prefix all bugnote data with v3_
			$row = db_fetch_array( $result6 );
			extract( $row, EXTR_PREFIX_ALL, 'v3' );
			$v3_date_submitted = format_date( config_get( 'normal_date_format' ), ( $v3_date_submitted ) );

			# grab the bugnote text and id and prefix with v3_
			$query6 = "SELECT note, id
					FROM $g_mantis_bugnote_text_table
					WHERE id='$v3_bugnote_text_id'";
			$result7 = db_query( $query6 );
			$v3_note = db_result( $result7, 0, 0 );
			$v3_bugnote_text_id = db_result( $result7, 0, 1 );

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
		} # end for
	} # end else
?>

</table>
<?php # Bugnotes END ?>
</table>


<?php
echo '<br /><br />';
		} # end isset
}  # end main loop
?>
