<?php
	global $hidefilterform, $infoboxmode, $tableclass, $filterrelations;
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'icon_api.php' );
	require_once( './modules/timexp/report_to_items.php' );
?>
<?php
	$t_filter = current_user_get_bug_filter();

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];

	$t_checkboxes_exist = false;

	$t_icon_path = config_get( 'icon_path' );
	if(!isset($hidefilterform)) 
		$hidefilterform = false;
	if (!isset($infoboxmode))
		$infoboxmode = false;
?>

<?php 

if (!$hidefilterform){
/* ====================== FILTER FORM ========================= */ ?>
<br />
<script language="JavaScript"> 

	function close_div(div_name){
		document.getElementById(div_name).style.display='none';
	}
	
	function progress_msg(visibility_st){ 
	
	var f = document.editFrm;
	
	if(visibility_st == 'mostrar')
	{
        // Muestro el cartel de procesando	  
		document.getElementById('progress').style.display='';
		document.getElementById('add_hours').style.display='none';
		
	   setTimeout("progress_msg('error')", 60*1000); 
			
	}else{
		   
	  // Oculto el mensaje de error
	  document.getElementById('progress').style.display = "none"; 		  
		}	
	}

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.filterFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scrollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.filterFrm.' + calendarField );
	fld_fdate = eval( 'document.filterFrm.bug_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

}


</script>
<form method="post" action="index.php?m=webtracking&a=view_all_set&f=3" name="filterFrm">
<input type="hidden" name="type" value="1" />
<input type="hidden" name="sort" value="<?php echo $t_sort ?>" />
<input type="hidden" name="dir" value="<?php echo $t_dir ?>" />
<input type="hidden" name="page_number" value="<?php echo $f_page_number ?>" />
<input type="hidden" name="per_page" value="<?php echo $t_filter['per_page'] ?>" />
<table class="width100" cellspacing="0" border="0">
<?php /* Filter Form Header Row  */ ?>
<tr class="row-category2">
	<td class="small-caption"><?php echo lang_get( 'reporter' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'assigned_to' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'category' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'severity' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'status' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'show' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'changed' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'hide_status' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'search' ) ?></td>
</tr>
<?php /*  Filter Form Fields  */ ?>
<tr>
	<?php /*  Reporter  */ ?>
	<td>
		<select class="small" name="reporter_id">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<?php print_reporter_option_list( $t_filter['reporter_id'] ) ?>
		</select>
	</td>

	<?php /*  Handler  */ ?>
	<td>
		<select class="small" name="handler_id">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="none" <?php check_selected( $t_filter['handler_id'], 'none' ); ?>><?php echo lang_get( 'none' ) ?></option>
			<?php print_assign_to_option_list( $t_filter['handler_id'] ) ?>
		</select>
	</td>

	<?php /*  Category  */ ?>
	<td>
		<select class="small" name="show_category">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<?php /* This shows orphaned categories as well as selectable categories */ ?>
			<?php print_category_complete_option_list( $t_filter['show_category'] ) ?>
		</select>
	</td>

	<?php /*  Severity  */ ?>
	<td>
		<select class="small" name="show_severity">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<?php print_enum_string_option_list( 'severity', $t_filter['show_severity'] ) ?>
		</select>
	</td>

	<?php /*  Status  */ ?>
	<td>
		<select class="small" name="show_status">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<?php print_enum_string_option_list( 'status', $t_filter['show_status'] ) ?>
		</select>
	</td>

	<?php /*  Number of bugs per page  */ ?>
	<td>
		<input type="text" class="text" name="per_page" size="3" maxlength="7" value="<?php echo $t_filter['per_page'] ?>" />
	</td>

	<?php /*  Mostrar Bugs con CAmbios en las Ultimas N horas  */ ?>
	<td>
		<input type="text" class="text" name="show_n_hours" size="3" maxlength="7" value="<?php echo $t_filter['show_n_hours'] ?>" />
		<input type="hidden" class="text" name="highlight_changed" size="3" maxlength="7" value="<?php echo $t_filter['highlight_changed'] ?>" />
	</td>
	<?php /*  Hide closed bugs  */ ?>
	<td>
		<input type="checkbox" name="hide_resolved" <?php check_checked( $t_filter['hide_resolved'], 'on' ); ?> />&nbsp;<?php echo lang_get( 'filter_resolved' ); ?>
		<input type="checkbox" name="hide_closed" <?php check_checked( $t_filter['hide_closed'], 'on' ); ?> />&nbsp;<?php echo lang_get( 'filter_closed' ); ?>
	</td>
	<?php /*  Text search  */ ?>
	<td colspan="1">
	    <input type="text" class="text" size="16" name="search" value="<?php echo $t_filter['search']; ?>" />
	</td>
</tr>
</table>

<table>
<tr class="spacer" height="1">
	<td></td>
</tr>

<table>
<table class="width100" cellspacing="0" border="0">
<?php /*  Search and Date Header Row  */ ?>
<tr class="row-category2">
	<td class="small-caption"><?php echo lang_get( 'date_deadline' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'date_from' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'date_to' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'product_version' )?></td>
	<td class="small-caption">&nbsp;</td>
</tr>


<?php /*  Search and Date fields  */ ?>
<tr>
	<?php /*  date_deadline  */ ?>
	<td class="left">
	<?php
		echo arraySelect($filterrelations,"deadline_rel",'class="small"', $t_filter['deadline_rel'], true,'','80px' );	
		$df = $AppUI->getPref('SHDATEFORMAT');   
		
		$date= strlen($t_filter['date_deadline'])>0 && $t_filter['date_deadline']>0 ? new CDate($t_filter['date_deadline']) : NULL;

	?>
					<input type="hidden" name="date_deadline" value="<?php echo $date ? $date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="bug_date_deadline" value="<?php echo $date ? $date->format( $df ) : "" ;?>" size="10" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('date_deadline')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>		

	<!--
		<select class="small" name="start_day">
			<?php print_day_option_list( $t_filter['start_day'] ) ?>
		</select>
		<select class="small" name="start_month">
			<?php print_month_option_list( $t_filter['start_month'] ) ?>
		</select>
		<select class="small" name="start_year">
			<?php print_year_option_list( $t_filter['start_year'] ) ?>
		</select>
	-->
	</td>


	<?php /*  date_from  */ ?>
	<td class="left">
	<?php
		echo arraySelect($filterrelations,"date_from_rel",'class="small"', $t_filter['date_from_rel'], true ,'','80px');	
		$df = $AppUI->getPref('SHDATEFORMAT');

		$date= strlen($t_filter['date_from'])>0 && $t_filter['date_from']>0 ? new CDate($t_filter['date_from']) : NULL;

	?>
					<input type="hidden" name="date_from" value="<?php echo $date ? $date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="bug_date_from" value="<?php echo $date ? $date->format( $df ) : "" ;?>" size="10" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('date_from')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
	</td>


	<?php /*  date_to  */ ?>
	<td class="left">
	<?php
		echo arraySelect($filterrelations,"date_to_rel",'class="small"', $t_filter['date_to_rel'], true ,'','80px');	
		$df = $AppUI->getPref('SHDATEFORMAT');

		$date= strlen($t_filter['date_to'])>0 && $t_filter['date_to']>0 ? new CDate($t_filter['date_to']) : NULL;

	?>
					<input type="hidden" name="date_to" value="<?php echo $date ? $date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="bug_date_to" value="<?php echo $date ? $date->format( $df ) : "" ;?>" size="10" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('date_to')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
	</td>


	<?php /* Product Version */ 
	?>
	<td class="left">
	<select class="small" name="show_version">
	        <?
		        if($t_filter['show_version']=="any")
				{
	                                     $sel1= "selected";
				}

				if($t_filter['show_version']=="na")
				{
	                                      $sel2= "selected";
				}
		    ?>
			<option value="any" <?=$sel1;?> ><?php echo lang_get( 'any' ) ?></option>
			<option value="na" <?=$sel2;?> ><?php echo lang_get( 'none' ) ?></option>
			<?php print_version_option_list( $t_filter['show_version'] ) ?> 
		</select>		
	</td>
	
	<?php /*  End date  
	<td class="left" colspan="2">
	<!--
		<select class="small" name="end_day">
			<?php print_day_option_list( $t_filter['end_day'] ) ?>
		</select>
		<select class="small" name="end_month">
			<?php print_month_option_list( $t_filter['end_month'] ) ?>
		</select>
		<select class="small" name="end_year">
			<?php print_year_option_list( $t_filter['end_year'] ) ?>
		</select>
	-->
	</td>*/ ?>

	<?php /*  SUBMIT button  */ ?>
	<td class="right">
		<input type="submit" name="filter" class="button" value="<?php echo lang_get( 'filter_button' ) ?>" />
	</td>
</tr>
</table>
</form>
<?php /*  ====================== end of FILTER FORM ========================= */ 
}   
?>

<?php /*  ====================== BUG LIST =========================  */ ?>
<?php
	$col_count = 11;
	if (!$infoboxmode){
	if ( STATUS_LEGEND_POSITION_TOP == config_get( 'status_legend_position' ) ) {
		html_status_legend();
	}
	}
	$t_show_attachments = config_get( 'show_attachment_indicator' );
	if ( ON == $t_show_attachments ) {
		$col_count++;
	}

?>

<table >
<tr class="spacer" height="1">
	<td></td>
</tr>

<?php if (!$infoboxmode){ ?>
<?php } ?>
<form method="post" action="index.php?m=webtracking&a=bug_actiongroup_page">
<table class="<?= $tableclass ? $tableclass : "width100";?>" cellspacing="1" border="0">
<?php /*  Navigation header row  */ ?>
<tr>
	<?php /*  Viewing range info  */ ?>
	<td class="form-title" colspan="<?php echo $col_count - 2; ?>" nowrap="nowrap">
		<?php echo lang_get( 'viewing_bugs_title' ) ?>
		<?php
			if ( sizeof( $rows ) > 0 ) {
				$v_start = $t_filter['per_page'] * ($f_page_number-1) +1;
				$v_end   = $v_start + sizeof( $rows ) -1;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			echo "($v_start - $v_end / $t_bug_count)";
		?>

		<?php /*  Print and Export links  */ ?>
		<span class="small">
			<?php
				print_bracket_link( 'index.php?m=webtracking&a=print_all_bug_page', lang_get( 'print_all_bug_page_link' ) );
				echo '&nbsp;';
				print_bracket_link( 'index.php?m=webtracking&suppressHeaders=yes&a=csv_export&includeajax=0', lang_get( 'csv_export' ) );
			?>
		</span>
		<?php /*  end Print and Export links  */ ?>
	</td>

	<?php /*  Page number links  */ ?>
	<td class="right" colspan="2">
		<span class="small">
			<?php print_page_links( 'index.php?m=webtracking&a=view_all_bug_page', 1, $t_page_count, $f_page_number ) ?>
		</span>
	</td>
</tr>
<?php /*  Bug list column header row  */ ?>
<tr class="row-category">
<?php if (!$infoboxmode){ ?>
	<td class="center">&nbsp;</td>
<?php } ?>
	<td class="center">&nbsp;</td>
	<td class="center">&nbsp;</td>

	<?php /*  Priority column  */ ?>
	<td class="center">
		<?php print_view_bug_sort_link( 'P', 'priority', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'priority' ) ?>
	</td>

	<?php /*  Bug ID column  */ ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'id' ), 'id', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'id' ) ?>
	</td>

	<?php /*  Bugnote count column  */ ?>
	<td class="center">
		#
	</td>

	<?php /*  Attachment indicator */
		if ( ON == $t_show_attachments ) {
		  echo '<td class="center">';
			echo '<img src="' . $t_icon_path . 'attachment.png' . '" alt="" />';
			echo '</td>';
		}
	?>

	<?php /*  Category column  */ ?>
	<td class="center">
	<?php if ($infoboxmode){ ?>
		<?php print_view_bug_sort_link( $AppUI->_("Project"), 'category', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'category' ) ?>	
	<?php } else { ?>
		<?php print_view_bug_sort_link( lang_get( 'category' ), 'category', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'category' ) ?>
	<?php } ?>
	</td>

	<?php /*  Severity column  */ ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'severity' ) ?>
	</td>

	<?php /*  Status column  */ ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'status' ), 'status', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'status' ) ?>
	</td>

	<?php /*  Last Updated column  */ ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'last_updated' ) ?>
	</td>

	<?php /*  Summary column  */ ?>
	<td class="center">
		<?php print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $t_sort, $t_dir ) ?>
		<?php print_sort_icon( $t_dir, $t_sort, 'summary' ) ?>
	</td>
</tr>
<?php /*  Spacer row  */ ?>
<tr>
	<td class="spacer" colspan="<?php echo $col_count ?>">&nbsp;</td>
</tr>
<?php mark_time( 'begin loop' ); ?>
<?php /*  Loop over bug rows and create $v_* variables  */ ?>
<?php  
	for($i=0; $i < sizeof( $rows ); $i++) {
		# prefix bug data with v_

		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display_links( $v_summary );
		$t_last_updated = format_date( config_get( 'short_date_format' ), $v_last_updated );

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
		$status_class = get_status_class( $v_status);	
?>
<tr bgcolor="<?php echo $status_color ?>" onMouseover="ddrivetip('<?php 
echo lang_get( 'reporter' ).": ";
print_user($v_reporter_id);
?>')";
onMouseout="hideddrivetip()">
	<?php /*  Checkbox  */ ?>
<?php if (!$infoboxmode){ ?>
	<td>
	<?php if(!getDenyEdit("timexp")) { 
			if($v_status == 90 || $v_status == 80)	$hideCompleted = 1;
			else $hideCompleted = 0;
		?>
		<a href='javascript:report_hours(<?php echo $v_id; ?>, 6, <?=$hideCompleted?>);' >
			<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'>
		</a>
		<?php }
		else echo "&nbsp;"; ?>
	</td>
<?php
	if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $v_id ) ) {
		$t_checkboxes_exist = true;
?>
	<td >
		<input type="checkbox" name="bug_arr[]" value="<?php echo "$v_id" ?>"/>
	</td>
<?php
	} else {
		echo '<td>&nbsp;</td>';
}
?>
<?php } ?>	
	<?php /*  Pencil shortcut  */ ?>
	<td class="center">
	<?php
		if ( access_has_bug_level( UPDATER, $v_id ) ) {
			echo '<a href="' . string_get_bug_update_url( $v_id ) . '"><img border="0" src="' . $t_icon_path . 'update.gif' . '" alt="' . lang_get( 'update_bug_button' ) . '" /></a>';
		} else {
			echo '&nbsp;';
		}
	?>
	</td>

	<?php /*  Priority  */ ?>
	<td class="center">
		<?php
			if ( ON == config_get( 'show_priority_text' ) ) {
				print_formatted_priority_string( $v_status, $v_priority );
			} else {
				print_status_icon( $v_priority );
			}
		?>
	</td>

	<?php /*  Bug ID and details link  */ ?>
	<td class="center">
		<?php print_bug_link( $v_id ); ?>
	</td>

	<?php /*  Bugnote count  */ ?>
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

	<?php /*  Attachment indicator */
	  
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

	<?php /*  Category  */ ?>
	<td class="center">
		<?php
			# type project name if viewing 'all projects'
			if ( ON == config_get( 'show_bug_project_links' ) &&
				 helper_get_current_project() == ALL_PROJECTS ||
				 $infoboxmode) {
				echo '<small>[';
				print_view_bug_sort_link( $project_name, 'project_id', $t_sort, $t_dir );
				echo ']</small><br />';
			}
			
			echo string_display( $v_category );
		?>
	</td>
	<?php /*  Severity  */ ?>
	<td class="center">
		<?php print_formatted_severity_string( $v_status, $v_severity ) ?>
	</td>
	<?php /*  Status / Handler  */ ?>
	<td class="center">
		<?php
			if ($v_status == 80)
				echo '<u><a title="' . get_enum_element( 'resolution', $v_resolution ) . '">' . get_enum_element( 'resolution', $v_resolution ) . '</a></u>';
			else
				echo '<u><a title="' . get_enum_element( 'resolution', $v_resolution ) . '">' . get_enum_element( 'status', $v_status ) . '</a></u>';
			# print username instead of status
			if ( $v_handler_id > 0 && ON == config_get( 'show_assigned_names' ) ) {
				echo ' (';
				print_user( $v_handler_id );
				echo ')';
			}
		?>
	</td>
	<?php /*  Last Updated  */ ?>
	<td class="center" title="<? echo(format_date($g_complete_date_format, $v_last_updated ));?>">
		<?php
			if ( $v_last_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
				echo '<span class="bold">'.$t_last_updated.'</span>';
			} else {
				echo $t_last_updated;
			}
		?>
	</td>
	<?php /*  Summary  */ ?>
	<td class="left">
		<?php
			echo $v_summary;
			
			if ( VS_PRIVATE == $v_view_state ) {
				echo ' <img src="' . $t_icon_path . 'protected.gif" width="8" height="15" alt="' . lang_get( 'private' ) . '" />';
			}
		 ?>
	</td>
</tr>
<?php /*  end of Repeating bug row  */ ?>
<?php
	}
?>
<?php /*  ====================== end of BUG LIST =========================  */ ?>

<?php if (!$infoboxmode){ ?>
<?php /*  ====================== MASS BUG MANIPULATION =========================  */ ?>
	<tr>
		<td colspan="<?php echo $col_count ?>" align="right">
<?php
		if ( $t_checkboxes_exist ) {
?>
            <table border="0">
                <tr>
                    <td> 
                                   <select class="small" name="action">
				<?php print_all_bug_action_option_list() ?>
			</select>  
                    </td>
                    <td>
			<input type="submit" class="button" value="<?php echo 'OK';  ?>" />
                    </td>
                    <td width="2">
                    </td>
                    <?php /*  Page number links  */ ?>
					<td class="right" >
						<span class="small">
							<?php print_page_links( 'index.php?m=webtracking&a=view_all_bug_page', 1, $t_page_count, $f_page_number ) ?>
						</span>
					</td>
                </tr>    
            </table>

<?php
		} else { 
			 /*  Page number links  */ ?>
			<td class="right" colspan="2">
				<span class="small">
					<?php print_page_links( 'index.php?m=webtracking&a=view_all_bug_page', 1, $t_page_count, $f_page_number ) ?>
				</span>
			</td>
	    <?
		}
?>
		</td>
		
	</tr>
<?php /*  ====================== end of MASS BUG MANIPULATION =========================  */ ?>
<?php } ?>
</table>
</form>
<?php mark_time( 'end loop' ); ?>
<?php if (!$infoboxmode){ ?>
<?php
	if ( STATUS_LEGEND_POSITION_BOTTOM == config_get( 'status_legend_position' ) ) {
		html_status_legend();
	}
?>
<?php } ?>
