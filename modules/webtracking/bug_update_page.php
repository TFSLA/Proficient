<?php
	# Show the simple update bug options
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'date_api.php' );
	
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	if ( ADVANCED_ONLY == config_get( 'show_update' ) ) {
		print_header_redirect ( 'index.php?m=webtracking&a=bug_update_advanced_page&bug_id=' . $f_bug_id );
	}

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

	$t_bug = bug_prepare_edit( bug_get( $f_bug_id, true ) );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<script language="JavaScript"> 

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scrollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.' + calendarField );
	fld_fdate = eval( 'document.editFrm.bug_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

}


</script>
<form method="post" action="index.php?m=webtracking&a=bug_update&o=<?=$_GET['o']?>" name="editFrm">
<table class="width100" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title" colspan="3">
		<input type="hidden" name="bug_id"			value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'updating_bug_simple_title' ) ?>
	</td>
	<td class="right" colspan="3">
<?php
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), lang_get( 'back_to_bug_link' ) );

	if ( BOTH == config_get( 'show_update' ) ) {
		print_bracket_link( 'index.php?m=webtracking&a=bug_update_advanced_page&bug_id=' . $f_bug_id, lang_get( 'update_advanced_link' ) );
	}
?>
	</td>
</tr>


<tr class="row-category">
	<td width="15%">
		<?php echo lang_get( 'id' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'severity' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'reproducibility' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'date_submitted' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'last_update' ) ?>
	</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>
	
	<!-- Bug ID -->
	<td>
		<?php echo bug_format_id( $f_bug_id ) ?>
	</td>

	<!-- Category -->
	<td>
		<select name="category">
			<?php print_category_option_list( $t_bug->category, $t_bug->project_id ) ?>
		</select>
	</td>

	<!-- Severity -->
	<td>
		<select name="severity">
			<?php print_enum_string_option_list( 'severity', $t_bug->severity ) ?>
		</select>
	</td>

	<!-- Reproducibility -->
	<td>
		<select name="reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $t_bug->reproducibility ) ?>
		</select>
	</td>

	<!-- Date Submitted -->
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ?>
	</td>

	<!-- Date Updated -->
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ?>
	</td>

</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>

<!-- Tarea -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" >
		<?php echo lang_get( 'link_to_task' ) ?>
	</td>
	<td  colspan="5">
	    <?php 
		   // Traigo las tareas del proyecto seleccionado
		   $sql_tasks = "SELECT task_id, task_name FROM tasks WHERE task_project = '".$t_bug->project_id."' ";
		   $tasks_list = db_loadHashList($sql_tasks);
		   
		?>
		<select tabindex="0" name="task_id" style="width:381px">
		  <?php
		        if(count($tasks_list)>0)
		        {
		        	echo "<option value=\"0\">".$AppUI->_("All tasks")."</option>";
		        	
			        foreach ($tasks_list as $id_task=>$task_name) 
			        {
			        	if ($id_task == $t_bug->task_id )
			        	{ $sel = 'selected="selected"';
			        	}else{
			        	  $sel = "";
			        	}
			        	echo "<option value=\"$id_task\" $sel >$task_name</option>";
			        }
		        }
		        else
		        {
		        	echo "<option value=\"0\">".$AppUI->_("No data available")."</option>";
		        }
		        
		  ?>	
		</select>
	</td>
	
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Reporter -->
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<select name="reporter_id">
			<?php print_reporter_option_list( $t_bug->reporter_id, $t_bug->project_id ) ?>
		</select>
	</td>

	<!-- View Status -->
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state', $t_bug->view_state) ?>
		</select>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Assigned To -->
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<select name="handler_id">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_bug->handler_id, $t_bug->project_id ) ?>
		</select>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Priority -->
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<select name="priority">
			<?php print_enum_string_option_list( 'priority', $t_bug->priority ) ?>
		</select>
	</td>

	<!-- Resolution -->
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Status -->
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<select name="status">
			<?php print_enum_string_option_list( 'status', $t_bug->status ) ?>
		</select>
	</td>

	<!-- Duplicate ID -->
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<?php echo $t_bug->duplicate_id ?>
	</td>

	
	<td colspan="2">&nbsp;</td>

</tr>


<!-- 	spacer -->
<tr <?php echo helper_alternate_class() ?>>


	<!-- ETA -->
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<input type="text" class="text" name="eta" size="20" maxlength="30" value="<?php echo $t_bug->eta ?>" />
	</td>
	
	<!--
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<select name="eta">
			<?php print_enum_string_option_list( 'eta', $t_bug->eta ) ?>
		</select>
	</td>
-->
		<!-- DEADLINE -->
	<td class="category">
		<?php echo lang_get( 'date_deadline' ) ?>
	</td>
	<td>
	<?php  
	$df = $AppUI->getPref('SHDATEFORMAT');

	$date= $t_bug->date_deadline > 0? new CDate(date('Y-m-d H:i:s T', $t_bug->date_deadline)) : NULL;


	?>
					<input type="hidden" name="date_deadline" value="<?php echo $date ? $date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="bug_date_deadline" value="<?php echo $date ? $date->format( $df ) : "" ;?>" size="10" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('date_deadline')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>&nbsp;
					<script> function cleardeadline(){ var f = document.editFrm; f.date_deadline.value="NULL"; f.bug_date_deadline.value="";  } </script>
					<a href="javascript:cleardeadline();"><?php echo $AppUI->_('clear');?></a>
	</td>	
	
	
	
	
	

						
			<!-- spacer -->
	<td colspan="2">&nbsp;</td>

</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<input type="text" class="text" name="summary" size="80" maxlength="128" value="<?php echo $t_bug->summary ?>" />
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="description" wrap="virtual"><?php echo $t_bug->description ?></textarea>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="additional_information" wrap="virtual"><?php echo $t_bug->additional_information ?></textarea>
	</td>
</tr>


<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( !$t_def['advanced'] && custom_field_has_write_access( $t_id, $f_bug_id ) ) {
			$t_custom_fields_found = true;
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td colspan="5">
		<?php
			print_custom_field_input( $t_def, $f_bug_id );
		?>
	</td>
</tr>
<?php
		} # !$t_def['advanced']
	} # foreach( $t_related_custom_field_ids as $t_id )
?>


<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>
<?php } # custom fields found ?>

<!-- Bugnote Text Box -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
	<td colspan="5">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>


<!-- Bugnote Private Checkbox (if permitted) -->
<?php if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'private' ) ?>
	</td>
	<td colspan="5">
		<input type="checkbox" name="private" />
	</td>
</tr>
<?php } ?>


<!-- Submit Button -->
<tr>
	<td class="center" colspan="6">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	</td>
</tr>


</table>
</form>

<?php //include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
			include( 'bugnote_view_inc.php' )?>


<?php html_page_bottom1( __FILE__ ) ?>
