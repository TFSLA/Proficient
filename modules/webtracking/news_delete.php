<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'news_api.php' );
?>
<?php
	$f_news_id = gpc_get_int( 'news_id' );

	$row = news_get_row( $f_news_id );

	access_ensure_project_level( config_get( 'manage_news_threshold' ), $row['project_id'] );

	if(!helper_ensure_confirmed( lang_get( 'delete_news_sure_msg' ),
							 lang_get( 'delete_news_item_button' ) )) return;
    news_delete( $f_news_id );

    $t_redirect_url = 'index.php?m=webtracking&a=news_menu_page';
	print_header_redirect( $t_redirect_url );
?>
