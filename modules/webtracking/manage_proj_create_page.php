<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'create_project_threshold' ) );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'index.php?m=webtracking&a=manage_proj_create_page' ) ?>

<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=manage_proj_create">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'add_project_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'project_name' )?>
	</td>
	<td width="75%">
		<input type="text" class="text" name="name" size="64" maxlength="128" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td>
		<select name="status">
		<?php print_enum_string_option_list( 'project_status' ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state' ) ?>
		</select>
	</td>
</tr>
<?php
	if ( config_get( 'allow_file_upload' ) ) {
	?>
		<tr class="row-2">
			<td class="category">
				<?php echo lang_get( 'upload_file_path' ) ?>
			</td>
			<td>
				<input type="text" class="text" name="file_path" size="70" maxlength="250" />
			</td>
		</tr>
		<?php
	}
?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_project_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
