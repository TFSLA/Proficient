<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php
	$f_news_id		= gpc_get_int( 'news_id' );
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_headline		= gpc_get_string( 'headline' );
	$f_announcement	= gpc_get_string( 'announcement', '' );
	$f_body			= gpc_get_string( 'body', '' );

	$row = news_get_row( $f_news_id );

	# Check both the old project and the new project
	access_ensure_project_level( config_get( 'manage_news_threshold' ), $row['project_id'] );
	access_ensure_project_level( config_get( 'manage_news_threshold' ), $f_project_id );

	news_update( $f_news_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );
	$f_headline 	= string_display( $f_headline );
	$f_body 		= string_display_links( $f_body );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
	<?php echo lang_get( 'operation_successful' ) ?><br />
<?php
	print_bracket_link( 'index.php?m=webtracking&a=news_edit_page&news_id='.$f_news_id.'&amp;action=edit', lang_get( 'edit_link' ) );
	print_bracket_link( 'index.php?m=webtracking&a=news_menu_page', lang_get( 'proceed' ) );
?>
<br /><br />
<table class="width75" cellspacing="1">
<tr>
	<td class="news-heading">
		<span class="bold"><?php echo $f_headline ?></span>
	</td>
</tr>
<tr>
	<td class="news-body">
		<?php echo $f_body ?>
	</td>
</tr>
</table>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
