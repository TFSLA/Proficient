<?
require_once( "modules/calendar/calendar.class.php" );
require_once( "modules/tasks/tasks.class.php" );

$this_day = new CDate();
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

$date = new CDate();
$first_time = new CDate( $date );
$first_time->setDay( $date->getDay()-1);
$last_time = new CDate( $date );
$last_time->setDay( $date->getDay());

$links = array();
// assemble the links for the tasks
require_once( "modules/calendar/links_tasks.php" );

getTaskLinks( $first_time, $last_time, $links, 100, $company_id );


$s = '';
$dayStamp = $this_day->format( FMT_TIMESTAMP_DATE );

echo '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';

if (isset( $links[$dayStamp] )) {
	foreach ($links[$dayStamp] as $e) {
		$href = isset($e['href']) ? $e['href'] : null;
		$alt = isset($e['alt']) ? $e['alt'] : null;

		$s .= "<tr><td>";
		$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
		$s .= "{$e['text']}";
		$s .= $href ? '</a>' : '';
		$s .= '</td></tr>';
	}
}
echo $s;

echo '</table>';
$min_view = 1;
require( "infobox_mytasksoverdue2.php" );
	
?>