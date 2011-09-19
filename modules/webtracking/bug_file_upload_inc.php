<?php
	# This include file prints out the bug file upload form
	# It POSTs to bug_file_add.php
?>
<?php
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php
	# check if we can allow the upload... bail out if we can't
	if ( ! file_allow_bug_upload( $f_bug_id ) ) {
		return false;
	}

	$t_max_file_size = (int)config_get( 'max_file_size' );
?>
<br />
<div align="center">
<form method="post" enctype="multipart/form-data" action="index.php?m=webtracking&a=bug_file_add">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="15%">
		<?php echo lang_get( 'select_file' ) ?><br />
		<?php echo '(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)'?>
	</td>
	<td width="85%">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<input name="file" class="small" type="file" size="60" />
		<input type="submit" class="button" value="<?php echo lang_get( 'upload_file_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
