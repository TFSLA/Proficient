<?
if (! class_exists("CMonthCalendar"))
	require_once( $AppUI->getModuleClass( 'calendar' ) );
if (! class_exists("CTask"))
	require_once( $AppUI->getModuleClass( 'tasks' ) );

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

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%" class="" background="images/common/back_1linea_04.gif">';

if (isset( $links[$dayStamp] )) {
	foreach ($links[$dayStamp] as $e) {
		$href = isset($e['href']) ? $e['href'] : null;
		$alt = isset($e['alt']) ? $e['alt'] : null;

		$s .= "<tr>";
        $s .= "<td align=\"left\"><img src=\"images/common/lado.gif\" width=\"1\" height=\"17\"></td>";
        $s .= "<td>";
		$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
		$s .= "{$e['text']}";
		$s .= $href ? '</a>' : '';
		$s .= '</td>';
        $s .= "<td align=\"right\"><img src=\"images/common/lado.gif\" width=\"1\" height=\"17\"></td>";
        $s .= '</tr>';
	}
}
echo $s;

echo '</table>';
$min_view = 1;
require( "infobox_mytasksoverdue2.php" );
	
?>
