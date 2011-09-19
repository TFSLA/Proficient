<?php
	# This include file prints out the bug information
	# $f_bug_id MUST be specified before the file is included
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'date_api.php' );
?>
<?php
	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );
?>

<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="6">
		<?php echo lang_get( 'viewing_bug_simple_details_title' ) ?>
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

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>
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

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

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


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>


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
	                           if($orig=="resolve"){
	                                echo "&nbsp;&nbsp;[<a href=\"index.php?m=webtracking&a=bug_kb_delete&bug_id=$f_bug_id&id_kb=".$kb_rows['id']."&orig=resolve\">".lang_get( 'delete_item' )."</a>]";
	                           }else{
	                               echo "&nbsp;&nbsp;[<a href=\"index.php?m=webtracking&a=bug_kb_delete&bug_id=$f_bug_id&id_kb=".$kb_rows['id']."\">".lang_get( 'delete_item' )."</a>]";
	                           }
	                        }

	                        echo "<br>";
	                }
	           ?>
	</td>
</tr>


</table>
