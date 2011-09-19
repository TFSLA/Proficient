<?php
	# This file POSTs data to report_bug.php
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	$dialog = intval(dPgetParam( $_GET, "dialog", 0 ));
	$suppressLogo = intval(dPgetParam( $_GET, "suppressLogo", 0 ));
	$callback = dPgetParam( $_GET, "callback", "" );
	$p_project_id = dPgetParam( $_GET, "project_id", "" );


	$action_string = "";
	if (($callback!="" && $dialog=="1" && $p_project_id) || ($p_project_id !="" && $_GET['o']=="projects")){
		$action_string="&dialog=$dialog&suppressLogo=$suppressLogo&callback=$callback";

		if (!helper_set_current_project( $p_project_id ))
			$AppUI->redirect( "m=public&a=access_denied" );
	}


	# this page is invalid for the 'All Project' selection
	if ( ALL_PROJECTS == helper_get_current_project() ) {
	       if($_GET['project_id']==""){
         	print_header_redirect( 'index.php?m=webtracking&a=login_select_proj_page&ref=bug_report_page' );
               }
	}


	if ( ADVANCED_ONLY == config_get( 'show_report' ) ) {
		print_header_redirect ( 'index.php?m=webtracking&a=bug_report_advanced_page' );
	}

	access_ensure_project_level( config_get( 'report_bug_threshold' ) );

	$f_category				= gpc_get_string( 'category', '' );
	$f_reproducibility		= gpc_get_int( 'reproducibility', 0 );
	$f_severity				= gpc_get_int( 'severity', 0 );
	$f_priority				= gpc_get_int( 'priority', NORMAL );
	$f_summary				= gpc_get_string( 'summary', '' );
	$f_description			= gpc_get_string( 'description', '' );
	$f_additional_info		= gpc_get_string( 'additional_info', '' );
	$f_view_state			= gpc_get_int( 'view_state', VS_PUBLIC );

	$f_report_stay			= gpc_get_bool( 'report_stay' );

	$t_project_id			= helper_get_current_project();
            $t_task_id                      = helper_get_current_task();


?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<script language="JavaScript">

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.report_bug_form.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scrollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.report_bug_form.' + calendarField );
	fld_fdate = eval( 'document.report_bug_form.bug_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

}

function changeFormReport(pAction){
	var f;
	f = document.report_bug_form;
	f.action = pAction;
	f.submit();
}

</script>
<?
    if(isset($_POST['o'])){
    	$o = $_POST['o'];
    }else{
    	$o = $_GET['o'];
    }

?>
<form name="report_bug_form" method="post" <?php if ( file_allow_bug_upload() ) { echo 'enctype="multipart/form-data"'; } ?> action="index.php?m=webtracking&a=bug_report<?php echo $action_string;?>&o=<?=$o?>">
<table class="width75" cellspacing="1" align="center">


<!-- Title -->
<tr>
	<td class="form-title">
              <?
	     if($_GET['project_id']!=""){
	     	$t_project_id = $_GET['project_id'];
	     }
	 ?>
	  <input type="hidden" name="no_sense_for_iefix_only" value="<?php echo uniqid("") ?>" />
		<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
		<input type="hidden" name="handler_id" value="0" />
		<?php echo lang_get( 'enter_report_details_title' ) ?>
	</td>
	<td class="right">
		<?php
			if ( BOTH == config_get( 'show_report' ) ) {
				print_bracket_link( 'javascript:changeFormReport(\'index.php?m=webtracking&a=bug_report_advanced_page\')', lang_get( 'advanced_report_link' ) );
				//echo "<a href=\"javascript:submitIt('index.php?m=webtracking&a=bug_report_advanced_page')\">adv report</a>";
			}
		?>
	</td>
</tr>

<!-- Tarea -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'link_to_task' ) ?>
	</td>
	<td width="70%">
	    <?php
		   // Traigo las tareas del proyecto seleccionado
		   $sql_tasks = "SELECT task_id, task_name FROM tasks WHERE task_project = '".$t_project_id ."' ";
		   $tasks_list = db_loadHashList($sql_tasks);
		?>
		<select tabindex="0" name="task_id" style="width:381px">
		  <?php
		        if(count($tasks_list)>0)
		        {
                                 if ($_GET['task_id']!="")
		        	        {
		        	        	$task_sel = $_GET['task_id'];
		        	        }else{
		        	        	$task_sel = $t_task_id;
		        	        }

		        	echo "<option value=\"0\">".$AppUI->_("All tasks")."</option>";

			        foreach ($tasks_list as $id_task=>$task_name)
			        {
                                        if ($id_task == $task_sel  ){
			        		$sel_t = "SELECTED";
			        	}else{
			        		$sel_t ="";
			        	}

			        	echo "<option value=\"$id_task\" $sel_t>$task_name</option>";
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


<!-- Category -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'category' ) ?> <?php print_documentation_link( 'category' ) ?>
	</td>
	<td width="70%">
		<select tabindex="1" name="category">
			<?php print_category_option_list( $f_category, $t_project_id ) ?>
		</select>
	</td>
</tr>


<!-- Reproducibility -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'reproducibility' ) ?> <?php print_documentation_link( 'reproducibility' ) ?>
	</td>
	<td>
		<select tabindex="2" name="reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
		</select>
	</td>
</tr>


<!-- Severity -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'severity' ) ?> <?php print_documentation_link( 'severity' ) ?>
	</td>
	<td>
		<select tabindex="3" name="severity">
			<?php print_enum_string_option_list( 'severity', $f_severity ) ?>
		</select>
	</td>
</tr>


<!-- Priority (if permissions allow) -->
<?php if ( access_has_project_level( config_get( 'update_bug_threshold' ) ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'priority' ) ?> <?php print_documentation_link( 'priority' ) ?>
	</td>
	<td>
		<select tabindex="4" name="priority">
			<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
		</select>
	</td>
</tr>
<?php } ?>

<!-- Deadline -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'date_deadline' ) ?> <?php print_documentation_link( 'date_deadline' ) ?>
	</td>
	<td>
	<?php
	$df = $AppUI->getPref('SHDATEFORMAT');

	$date=  NULL;


	?>
					<input type="hidden" name="date_deadline" value="<?php echo $date ? $date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="bug_date_deadline" value="<?php echo $date ? $date->format( $df ) : "" ;?>" size="10" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('date_deadline')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
	</td>
</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'summary' ) ?> <?php print_documentation_link( 'summary' ) ?>
	</td>
	<td>
		<input tabindex="5" type="text" class="text" name="summary" size="60" maxlength="128" value="<?php echo $f_summary ?>" />
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'description' ) ?> <?php print_documentation_link( 'description' ) ?>
	</td>
	<td>
		<textarea tabindex="6" name="description" cols="60" rows="5" wrap="virtual"><?php echo $f_description ?></textarea>
	</td>
</tr>


<!-- Additional information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?> <?php print_documentation_link( 'additional_information' ) ?>
	</td>
	<td>
		<textarea tabindex="7" name="additional_info" cols="60" rows="5" wrap="virtual"><?php echo $f_additional_info ?></textarea>
	</td>
</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( !$t_def['advanced'] && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
			$t_custom_fields_found = true;
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td>
		<?php print_custom_field_input( $t_def ) ?>
	</td>
</tr>
<?php
		} # if (!$t_def['advanced']) && has write access
	} # foreach( $t_related_custom_field_ids as $t_id )
?>


<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>
<?php } ?>


<!-- File Upload (if enabled) -->
<?php if ( file_allow_bug_upload() ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
	<td >
		<input type="hidden" name="max_file_size" value="<?php echo config_get( 'max_file_size' ) ?>" />
		<input tabindex="8" name="file" type="file" size="60" />
	</td>
</tr>
<?php } ?>

<?php

           $canEdit_kb = !getDenyEdit( 'articles' );

	if($canEdit_kb)
	{ ?>
            <tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get( 'knowledge_base' ) ?>
		</td>
		<td nowrap>
			<!--  Tipo -  Seccion - Titulo  -->
		<select name="kb_type" id="kb_type"  onchange="xajax_kb_type_section(document.report_bug_form.kb_type.value, document.report_bug_form.kb_section.value,'');" style="width: 160px;" >
		     <option value="-1"><? echo lang_get('choose_item'); ?></option>
		     <option value="0"  ><?php echo lang_get( 'articles' ) ?></option>
		     <option value="2"  ><?php echo lang_get( 'files' ) ?></option>
		     <option value="1"  ><?php echo lang_get( 'links' ) ?></option>
		</select>

		<select name="kb_section" id="kb_section" onchange="xajax_kb_type_section( document.report_bug_form.kb_type.value, document.report_bug_form.kb_section.value,'' );"; style="width: 160px; ">
		       <option value="-1">Top</option>
		       <?
		               $query = "SELECT articlesection_id, name FROM articlesections  order by name";
		               $sql =  db_query($query);

		               while($result = db_fetch_array( $sql ))
		               {
		                   echo "<option value=\"".$result['articlesection_id']."\" >".$result['name']."</option>";
		               }
		       ?>
		  </select>

		  <select name="kb_item" id="kb_item" style="width: 200px; "></select>

		</td>
            </tr>

            <script language="javascript">
	        xajax_kb_type_section( document.report_bug_form.kb_type.value, document.report_bug_form.kb_section.value,'' );
	</script>

<?	} ?>


<!-- View Status -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<input tabindex="9" type="radio" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php check_checked( $f_view_state, VS_PUBLIC ) ?> /> <?php echo lang_get( 'public' ) ?>
		<input tabindex="10" type="radio" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php check_checked( $f_view_state, VS_PRIVATE ) ?> /> <?php echo lang_get( 'private' ) ?>
	</td>
</tr>


<!-- Report Stay (report more bugs) -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'report_stay' ) ?> <?php print_documentation_link( 'report_stay' ) ?>
	</td>
	<td>
		<input tabindex="11" type="checkbox" name="report_stay" <?php check_checked( $f_report_stay ) ?> /> (<?php echo lang_get( 'check_report_more_bugs' ) ?>)
	</td>
</tr>


<!-- Submit Button -->
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input tabindex="12" type="submit" class="button" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
	</td>
</tr>


</table>
</form>


<!-- Autofocus JS -->
<script language="JavaScript">
<!--
	window.document.report_bug_form.category.focus();
//-->
</script>

<?php html_page_bottom1( __FILE__ ) ?>
