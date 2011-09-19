<?php
	global $btfilter;
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_dir . 'current_user_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_page_number		= gpc_get_int( 'page_number', 1 );

	$t_per_page = null;
	$t_bug_count = null;
	$t_page_count = null;
	
	$rows = filter_get_bug_rows_filter( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $btfilter );

//	compress_enable();

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		//html_meta_redirect( 'index.php?m=webtracking&a=infobox_mywebtracking&page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
	}
	
	$hidefilterform=true;
	$infoboxmode=true;
	$tableclass="width100infobox";

	include( 'view_all_inc.php' );

	html_page_bottom1( __FILE__ );

	/*
		$t_filter['show_category'] 		= "any";
		$t_filter['show_severity']	 	= "any";
		$t_filter['show_status'] 		= "any";
		$t_filter['per_page'] 			= "20";
		$t_filter['highlight_changed'] 	= "6";
		$t_filter['hide_closed'] 		= "on";
		$t_filter['reporter_id']		= "any";
		$t_filter['handler_id'] 		= "$AppUI->user_id";
		$t_filter['sort'] 				= "last_updated";
		$t_filter['dir']		 		= "DESC";
		$t_filter['start_month']		= "";
		$t_filter['start_day'] 			= "";
		$t_filter['start_year'] 		= "";
		$t_filter['end_month'] 			= "";
		$t_filter['end_day']			= "";
		$t_filter['end_year']			= "";
		$t_filter['search']				= "";
		$t_filter['hide_resolved'] 		= "";
<form method="post" action="index.php?m=webtracking&a=infobox_mywebtracking&f=3">
<input type="hidden" name="type" value="1" />
<input type="hidden" name="sort" value="last_updated" />
<input type="hidden" name="dir" value="DESC" />
<input type="hidden" name="page_number" value="1" />
<input type="hidden" name="per_page" value="50" />
<input type="hidden" name="reporter_id" value="any" />
<input type="hidden" name="handler_id" value="24" />
<input type="hidden" name="show_category" value="any" />
<input type="hidden" name="show_severity" value="any" />
<input type="hidden" name="show_status" value="any" />
<input type="hidden" name="per_page" value="20" />
<input type="hidden" name="highlight_changed" value="6" />
<input type="hidden" name="hide_resolved" value="0" />
<input type="hidden" name="hide_closed" value="1" />
<input type="hidden" name="search" value="" />
<input type="submit" name="filter" value="Filtrar" />
</form>
	*/
	
	
	
	
	?>
