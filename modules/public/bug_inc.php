<?
global $AppUI, $btfilter;
/*
$btfilter = array();
$btfilter['show_category'] 		= "any";
$btfilter['show_severity']	 	= "any";
$btfilter['show_status'] 		= "10";
$btfilter['per_page'] 			= "20";
$btfilter['highlight_changed'] 	= "6";
$btfilter['hide_closed'] 		= "on";
$btfilter['reporter_id']		= "any";
$btfilter['handler_id'] 		= "any";
$btfilter['sort'] 				= "last_updated";
$btfilter['dir']		 		= "DESC";
$btfilter['start_month']		= "";
$btfilter['start_day'] 			= "";
$btfilter['start_year'] 		= "";
$btfilter['end_month'] 			= "";
$btfilter['end_day']			= "";
$btfilter['end_year']			= "";
$btfilter['search']				= "";
$btfilter['hide_resolved'] 		= "";
*/
	require_once( "./modules/webtracking/core.php" );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_dir . 'current_user_api.php' );

	$f_page_number		= gpc_get_int( 'page_number', 1 );

	$t_per_page = null;
	$t_bug_count = null;
	$t_page_count = null;
	
	$temprows = filter_get_bug_rows_filter( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $btfilter );

/*	
/// borro los bugs que el usuario no pueda actualizar
	for($i=0; $i < sizeof( $btrows ); $i++) {
		extract( $btrows[$i], EXTR_PREFIX_ALL, 'v' );
		if (! access_has_bug_level( UPDATER, $v_id ) ){
			unset($btrows[$i]);
		}
	
	}
	*/
	$btrows = array();
	for($i=0; $i < sizeof( $temprows ); $i++) {
		extract( $temprows[$i], EXTR_PREFIX_ALL, 'v' );
		$t_bug = bug_prepare_display( bug_get( $v_id, true ) );
		$btrows[$v_id]["bug_id"]=bug_format_id( $v_id );
		$btrows[$v_id]["summary"]=$t_bug->summary;
		$btrows[$v_id]["project_id"]=$v_project_id;
		
	}
//echo "<pre>";var_dump($btrows);echo "</pre>";	

?>
