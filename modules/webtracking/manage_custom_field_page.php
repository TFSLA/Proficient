<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php 
	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	html_page_top1();
	html_page_top2();

	print_manage_menu( 'index.php?m=webtracking&a=manage_custom_field_page' );
?>
	<br />

<!-- List of custom field -->
<table class="width100" cellspacing="1">
	<tr>
		<td class="form-title" colspan="6">
				<?php echo lang_get( 'custom_fields_setup' ) ?>
		</td>
	</tr>
	<tr>
		<td class="category" width="12%">
			<?php echo lang_get( 'custom_field_name' ) ?>
		</td>
		<td class="category" width="12%">
			<?php echo lang_get( 'custom_field_project_count' ) ?>
		</td>
		<td class="category" width="12%">
			<?php echo lang_get( 'custom_field_type' ) ?>
		</td>
		<td class="category" width="40%">
			<?php echo lang_get( 'custom_field_possible_values' ) ?>
		</td>
		<td class="category" width="12%">
			<?php echo lang_get( 'custom_field_default_value' ) ?>
		</td>
		<td class="category" width="12%">
			<?php echo lang_get( 'custom_field_advanced' ) ?>
		</td>
	</tr>
	<?php 
		$t_custom_fields = custom_field_get_ids();
		foreach( $t_custom_fields as $t_field_id )
		{
			$t_desc = custom_field_get_definition( $t_field_id );
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<a href="index.php?m=webtracking&a=manage_custom_field_edit_page&field_id=<?php echo $t_field_id ?>"><?php echo string_display( $t_desc['name'] ) ?></a>
			</td>
			<td>
				<?php echo count( custom_field_get_project_ids( $t_field_id ) ) ?>
			</td>
			<td>
				<?php echo get_enum_element( 'custom_field_type', $t_desc['type'] ) ?>
			</td>
			<td>
				<?php echo string_display( $t_desc['possible_values'] ) ?>
			</td>
			<td>
				<?php echo string_display( $t_desc['default_value'] ) ?>
			</td>
			<td align="center">
				<?php echo trans_bool( $t_desc['advanced'] ) ?>
			</td>
		</tr>
	<?php
		} # Create Form END 
	?>
</table>

<br />

<form method="post" action="index.php?m=webtracking&a=manage_custom_field_create">
		<input type="text" class="text" name="name" size="32" maxlength="64" />
		<input type="submit" class="button" value="<?php echo lang_get( 'add_custom_field_button' ) ?>" />
</form>

<br />

<?php html_page_bottom1( __FILE__ ) ?>
