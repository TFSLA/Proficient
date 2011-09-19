<?
global $AppUI, $btfilter;

$btfilter = array();
$btfilter['show_category'] 		= "any";
$btfilter['show_severity']	 	= "any";
$btfilter['show_status'] 		= "any";
$btfilter['per_page'] 			= "20";
$btfilter['highlight_changed'] 	= "6";
$btfilter['hide_closed'] 		= "on";
$btfilter['reporter_id']		= "any";
$btfilter['handler_id'] 		= "$AppUI->user_id";
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
		
include("./modules/webtracking/infobox_mywebtracking.php");


?>

