<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( 'ajax.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_page_number		= gpc_get_int( 'page_number', 1 );
	# check to see if the cookie does not exist
	if ( !filter_is_cookie_valid() ) {
		print_header_redirect( 'index.php?m=webtracking&a=view_all_set&type=0' );
	}

	$t_per_page = null;
	$t_bug_count = null;
	$t_page_count = null;
    
 
	$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count );
    
	/*echo "<pre>";
       print_r($rows);
    echo "</pre>";*/
  
//	compress_enable();

	html_page_top1();
	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect( 'index.php?m=webtracking&a=view_all_bug_page&page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
	}

	html_page_top2();
//	echo "<br>date_from = ".$t_filter['date_from'];

	include( 'view_all_inc.php' );
	html_page_bottom1( __FILE__ );
?>
