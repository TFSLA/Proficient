<?php
	# Allows the user to select a project that is visible to him
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_ref = gpc_get_string( 'ref', '' );
?>
<?php html_page_top1() ?>
<?php html_page_top2a() ?>

<!-- Project Select Form BEGIN -->
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=set_project">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="ref" value="<?php echo $f_ref ?>" />
		<?php echo lang_get( 'login_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="40%">
		<?php echo lang_get( 'choose_project' ) ?>
	</td>
	<td width="60%">
		<select name="project_id">
		<?php print_project_option_list( ALL_PROJECTS ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'make_default' ) ?>
	</td>
	<td>
		<input type="checkbox" name="make_default" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'select_project_button') ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
