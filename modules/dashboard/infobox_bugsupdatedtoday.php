<?
global $AppUI, $btfilter;

$btfilter = array();
$btfilter['show_category'] 		= "any";
$btfilter['show_severity']	 	= "any";
$btfilter['show_status'] 		= "any";
$btfilter['per_page'] 			= "20";
$btfilter['highlight_changed'] 	= "6";
$btfilter['hide_closed'] 		= "";
$btfilter['reporter_id']		= "any";
$btfilter['handler_id'] 		= "any";
$btfilter['sort'] 				= "last_updated";
$btfilter['dir']		 		= "DESC";
$btfilter['start_month']		= date("m");
$btfilter['start_day'] 			= date("d");
$btfilter['start_year'] 		= date("Y");
$btfilter['end_month'] 			= date("m");
$btfilter['end_day']			= date("d");
$btfilter['end_year']			= date("Y");
$btfilter['search']				= "";
$btfilter['hide_resolved'] 		= "";

include("./modules/webtracking/infobox_mywebtracking.php");


?>
