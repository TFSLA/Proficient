<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'relationship_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_history		= gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

	if ( SIMPLE_ONLY == config_get( 'show_view' ) ) {
		print_header_redirect ( 'index.php?m=webtracking&a=bug_view_page&bug_id=' . $f_bug_id );
	}

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

//	compress_enable();

	html_page_top1();
	html_page_top2();
?>

<br />
<table class="width100" cellspacing="1">


<tr>

	<!-- Title -->
	<td class="form-title" colspan="4">
		<?php echo lang_get( 'viewing_bug_advanced_details_title' ) ?>

		<!-- Jump to Bugnotes -->
		<span class="small"><?php print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ) ) ?></span>

		<!-- Send Bug Reminder -->
	<?php
		if ( !current_user_is_anonymous() &&
			  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id ) ) {
	?>
		<span class="small">
			<?php print_bracket_link( 'index.php?m=webtracking&a=bug_reminder_page&bug_id='.$f_bug_id, lang_get( 'bug_reminder' ) ) ?>
		</span>
	<?php
		}
	?>

	</td>

	<!-- Links -->
	<td class="right" colspan="2">

		<!-- Simple View (if enabled) -->
	<?php if ( BOTH == config_get( 'show_view' ) ) { ?>
			<span class="small"><?php print_bracket_link( 'index.php?m=webtracking&a=bug_view_page&bug_id=' . $f_bug_id, lang_get( 'view_simple_link' ) ) ?></span>
	<?php } ?>

		<!-- History -->
		<span class="small"><?php print_bracket_link( 'index.php?m=webtracking&a=bug_view_page&bug_id=' . $f_bug_id . '&amp;history=1#history', lang_get( 'bug_history' ) ) ?></span>

		<!-- Print Bug -->
		<span class="small"><?php print_bracket_link( 'index.php?m=webtracking&a=print_bug_page&bug_id=' . $f_bug_id, lang_get( 'print' ) ) ?></span>

	</td>

</tr>


<!-- Labels -->
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
		<?php echo $t_bug->category ?>
	</td>

	<!-- Severity -->
	<td>
		<?php echo get_enum_element( 'severity', $t_bug->severity ) ?>
	</td>

	<!-- Reproducibility -->
	<td>
		<?php echo get_enum_element( 'reproducibility', $t_bug->reproducibility ) ?>
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
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
    <td class="category" >
		<?php echo lang_get( 'task' ) ?>
	</td>
	<td colspan="5">
	    <?
	    $sql_task = "SELECT task_name FROM tasks WHERE task_id='".$t_bug->task_id."' ";
	    $task_data = db_loadColumn($sql_task);

	    echo $task_data[0];
	    ?>
	</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Reporter -->
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<?php
		$pre = "";
		$post = "";
		if (!getDenyRead("admin", $t_bug->reporter_id )){
			$pre = '<a href="?m=admin&a=viewuser&user_id='.$t_bug->reporter_id.'">';
			$post = "</a>";
		}

		echo $pre;
		print_user_with_subject( $t_bug->reporter_id, $f_bug_id );
		echo $post;
		?>
	</td>

	<!-- View Status -->
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_bug->view_state ) ?>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

</tr>


<!-- Handler -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<?php print_user_with_subject( $t_bug->handler_id, $f_bug_id ) ?>
	</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Priority -->
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'priority', $t_bug->priority ) ?>
	</td>

	<!-- Resolution -->
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>

	<!-- Platform -->
	<td class="category">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td>
		<?php echo $t_bug->platform ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Status -->
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<?php echo get_enum_element( 'status', $t_bug->status ) ?>
	</td>

	<!-- Duplicate ID -->
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<?php print_duplicate_id( $t_bug->duplicate_id ) ?>
	</td>

	<!-- Operating System -->
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Projection -->
	<td class="category">
		<?php echo lang_get( 'projection' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'projection', $t_bug->projection ) ?>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

	<!-- OS Version -->
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os_build ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- ETA -->
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<!--<?php echo get_enum_element( 'eta', $t_bug->eta ) ?>-->
		<?php echo $t_bug->eta ?>
	</td>

	<!-- View Status -->
	<td class="category">
		<?php echo lang_get( 'date_deadline' ) ?>
	</td>
	<td>
		<?php
		if (strlen($t_bug->date_deadline)>0 && $t_bug->date_deadline>0)
			print_date( config_get( 'short_date_format' ), $t_bug->date_deadline );
		else
			echo "";
		?>
	</td>

	<!-- Product Version -->
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->version ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- spacer -->
	<td colspan="4">&nbsp;</td>

	<!-- Product Build -->
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<?php echo $t_bug->build?>
	</td>

</tr>


<!-- spacer -->
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->summary ?>
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->description ?>
	</td>
</tr>


<!-- Steps to Reproduce -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->steps_to_reproduce ?>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>


<!-- spacer -->
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		if ( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
			continue;
		} # has read access

		$t_custom_fields_found = true;
		$t_def = custom_field_get_definition( $t_id );
?>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get_defaulted( $t_def['name'] ) ?>
		</td>
		<td colspan="5">
		<?php
			$t_custom_field_value = custom_field_get_value( $t_id, $f_bug_id );
			if( CUSTOM_FIELD_TYPE_EMAIL == $t_def['type'] ) {
				echo "<a href=\"mailto:$t_custom_field_value\">$t_custom_field_value</a>";
			} else {
				echo $t_custom_field_value;
			}
		?>
		</td>
	</tr>
<?php
	} # foreach
?>

<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>
<?php } # custom fields found ?>


<!-- Attachments -->
<?php
	$t_show_attachments = ( $t_bug->reporter_id == auth_get_current_user_id() ) || access_has_bug_level( config_get( 'view_attachments_threshold' ), $f_bug_id );

	if ( $t_show_attachments ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<a name="attachments" id="attachments" />
		<?php echo lang_get( 'attached_files' ) ?>
	</td>
	<td colspan="5">
		<?php file_list_attachments ( $f_bug_id ); ?>
	</td>
</tr>
<?php
	}
?>


<SCRIPT LANGUAGE="JavaScript">
//<!-- Begin
function popUp_kb(URL) {
var day = new Date();
var id = day.getTime();

eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=1, location=0, statusbar=0, menubar=0, resizable=0, width=850, height=660, left=10, top=10');");
}
// End -->
</script>

<!-- Base de conocimientos -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<a name="knowledgebase" id="knowledgebase" />
		<?php echo lang_get( 'knowledge_base' ) ?>
	</td>
	<td colspan="5">
	           <?
	                $query_kb = "SELECT id, kb_type, kb_section, kb_item , title as articulo, files.file_name as file, files.file_type
				  FROM btpsa_bug_kb
				  LEFT JOIN articles ON btpsa_bug_kb .kb_item=articles.article_id
				  LEFT JOIN files ON btpsa_bug_kb .kb_item= files.file_id
				  WHERE  btpsa_bug_kb.bug_id='".$f_bug_id ."'  ";

	                $result = db_query($query_kb);
                            $canEdit = !getDenyEdit( 'articles' );

	                while($kb_rows = db_fetch_array($result) )
	                {
	                	if($kb_rows['kb_type']=='0'){
	                	        echo "<a href=\"javascript:popUp_kb('index_inc.php?inc=./modules/articles/viewarticle.php&m=articles&id=$kb_rows[kb_item]')\">".$kb_rows['articulo']."</a>";
	                	}else if($kb_rows['kb_type']=='1'){
	                	       echo "<a href=\"javascript:popUp_kb('index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id=$kb_rows[kb_item]')\">".$kb_rows['articulo']."</a>";
	                	}else{
	                	       echo "<a href=\"javascript:popUp_kb('index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=$kb_rows[kb_item]')\">".$kb_rows['file']."</a>";
	                	}

	                	if($canEdit)
	                        {
	                           echo "&nbsp;&nbsp;[<a href=\"index.php?m=webtracking&a=bug_kb_delete&bug_id=$f_bug_id&id_kb=".$kb_rows['id']."\">".lang_get( 'delete_item' )."</a>]";
	                        }

	                        echo "<br>";
	                }
	           ?>
	</td>
</tr>

<!-- Bug Relationships -->
<!--<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'bug_relationships' ) ?>
	</td>
	<td colspan="5">
		<?php
			$result = relationship_fetch_all_src( $f_bug_id );
			$relationship_count = db_num_rows( $result );
			for ( $i = 0 ; $i < $relationship_count ; $i++ ) {
				$row = db_fetch_array( $result );

				$t_bug_link = string_get_bug_view_link( $row['destination_bug_id'] );
				switch ( $row['relationship_type'] ) {
					case BUG_DUPLICATE:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'duplicate_of' ) );
						break;
					case BUG_RELATED:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'related_to' ) );
						break;
					case BUG_DEPENDANT:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'dependant_on' ) );
						break;
					default:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'duplicate_of' ) );
				}

				echo $t_description . '<br />';
			}
		?>
		<?php
			$result = relationship_fetch_all_dest( $f_bug_id );
			$relationship_count = db_num_rows( $result );
			for ( $i = 0 ; $i < $relationship_count ; $i++ ) {
				$row = db_fetch_array( $result );

				$t_bug_link = string_get_bug_view_link( $row['source_bug_id'] );
				switch ( $row['relationship_type'] ) {
					case BUG_DUPLICATE:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'has_duplicate' ) );
						break;
					case BUG_RELATED:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'related_to' ) );
						break;
					case BUG_DEPENDANT:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'blocks' ) );
						break;
					default:
						$t_description = str_replace( '%id', $t_bug_link, lang_get( 'has_duplicate' ) );
				}

				echo $t_description . '<br />';
			}
		?>
	</td>
</tr>-->


<!-- Buttons -->
<tr align="center">
	<td align="center" colspan="6">
<?php
	html_buttons_view_bug_page( $f_bug_id );
?>
	</td>
</tr>
</table>

<?php
	//$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
	$t_mantis_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR;

	# File upload box
	if ( $t_bug->status < config_get( 'bug_resolved_status_threshold' ) ) {
		include( $t_mantis_dir . 'bug_file_upload_inc.php' );
	}

	# Incluyo Box de base de conocimientos
	include($t_mantis_dir.'bug_kb_view.inc.php');

	# Bug Relationships
	# MASC RELATIONSHIP
	if ( ON == config_get( 'enable_relationship' ) ) {
		relationship_view_box ( $f_bug_id );
	}

	# User list monitoring the bug
	include( $t_mantis_dir . 'bug_monitor_list_view_inc.php' );

	# Bugnotes
	include( $t_mantis_dir . 'bugnote_add_inc.php' );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );

	# History
	if ( $f_history ) {
		include( $t_mantis_dir . 'history_inc.php' );
	}

	html_page_bottom1( __FILE__ );
?>
