<?php
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'system' ) );


$cal_type = dPgetParam( $_POST, 'cal_type', 0 );
$id = dPgetParam( $_POST, 'id', 0 );
$project = dPgetParam( $_POST, 'project', 0 );
$from = dPgetParam( $_POST, 'from', "" ); 
$to = dPgetParam( $_POST, 'to', "" ); 
$df = dPgetParam( $_POST, 'dateformat', "" ); 


$wc = new CWorkCalendar( $cal_type, $id, $project);

$working_dates = $wc->getWorkingDates($from, $to);

//echo "<pre>";var_dump($working_dates);echo "</pre>";

$dates = array();
$dates_ts = array();
for($i = 0; $i < count($working_dates); $i++){
	$date = new CDate($working_dates[$i]["date"]);
	$dates[] = $date->format($df);
	$dates_ts[] = $date->format(FMT_TIMESTAMP_DATE);
	unset ($date);
}


?>
<script language="Javascript">

<?php
echo "window.parent.setDates('".
	implode(",", $dates)."' , '".
	implode(",", $dates_ts)."');";
?>

</script>