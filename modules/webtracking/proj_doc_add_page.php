<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	if ( ! file_allow_project_upload() ) {
		access_denied();
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form method="post" enctype="multipart/form-data" action="index.php?m=webtracking&a=proj_doc_add">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'upload_file_title' ) ?>
	</td>
	<td class="right">
		<?php print_doc_menu( 'index.php?m=webtracking&a=proj_doc_add_page' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'title' ) ?>
	</td>
	<td width="75%">
		<input type="text" class="text" name="title" size="70" maxlength="250" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="7" wrap="virtual"></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'select_file' ) ?>
	</td>
	<td>
		<input name="file" type="file" size="70" />
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'upload_file_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
