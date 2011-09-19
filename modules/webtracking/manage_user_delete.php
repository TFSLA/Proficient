<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	
	$f_user_id	= gpc_get_int( 'user_id' );

	if(!helper_ensure_confirmed( lang_get( 'delete_account_sure_msg' ),
							 lang_get( 'delete_account_button' ) )) return;

	user_delete( $f_user_id );

    $t_redirect_url = 'index.php?m=webtracking&a=manage_user_page';

	html_page_top1();

	html_meta_redirect( $t_redirect_url );

	html_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
