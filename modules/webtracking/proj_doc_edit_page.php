<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'string_api.php' );
?>
<?php
	# @@@ Need to obtain the project_id from the file once we have an API for that	
	access_ensure_project_level( MANAGER );

	$f_file_id = gpc_get_int( 'file_id' );

	$c_file_id = (integer)$f_file_id;

	$query = "SELECT *
			FROM $g_mantis_project_file_table
			WHERE id='$c_file_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	$v_title		= string_attribute( $v_title );
	$v_description 	= string_textarea( $v_description );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=proj_doc_update">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="file_id" value="<?php echo $f_file_id ?>" />
		<?php echo lang_get( 'upload_file_title' ) ?>
	</td>
	<td class="right">
		<?php print_doc_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="20%">
		<span class="required">*</span><?php echo lang_get( 'title' ) ?>
	</td>
	<td width="80%">
		<input type="text" class="text" name="title" size="70" maxlength="250" value="<?php echo $v_title ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="7" wrap="virtual"><?php echo $v_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'filename' ) ?>
	</td>
	<td>
		<?php
			$t_href = '<a href="index.php?m=webtracking&a=file_download&file_id='.$v_id.'&amp;type=doc">';
			echo $t_href;
			print_file_icon( $v_filename );
			echo '</a>&nbsp;' . $t_href . file_get_display_name( $v_filename ) . '</a>';
		?>
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'file_update_button' ) ?>" />
	</td>
</tr>
</table>
</form>

<br />

		<form method="post" action="index.php?m=webtracking&a=proj_doc_delete">
		<input type="hidden" name="file_id" value="<?php echo $f_file_id ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'file_delete_button' ) ?>" />
		</form>

</div>

<?php html_page_bottom1( __FILE__ ) ?>
