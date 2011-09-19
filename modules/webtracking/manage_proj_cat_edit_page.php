<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_category		= gpc_get_string( 'category' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$t_row = category_get_row( $f_project_id, $f_category );
	$t_assigned_to = $t_row['user_id'];
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php
	print_manage_menu( 'index.php?m=webtracking&a=manage_proj_cat_edit_page' );
?>

<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=manage_proj_cat_update">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_project_category_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<input type="hidden" name="project_id" value="<?php echo string_attribute( $f_project_id ) ?>" />
		<input type="hidden" name="category" value="<?php echo string_attribute( $f_category ) ?>" />
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td>
		<input type="text" class="text" name="new_category" size="32" maxlength="64" value="<?php echo string_attribute( $f_category ) ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td>
		<select name="assigned_to">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_assigned_to, $f_project_id ) ?>
		</select>
	</td>
</tr>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_category_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="index.php?m=webtracking&a=manage_proj_cat_delete">
		<input type="hidden" name="project_id" value="<?php echo string_attribute( $f_project_id ) ?>" />
		<input type="hidden" name="category" value="<?php echo string_attribute( $f_category ) ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'delete_category_button' ) ?>" />
	</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
