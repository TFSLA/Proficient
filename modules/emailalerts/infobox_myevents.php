<?
require_once( "modules/calendar/calendar.class.php" );

$this_day = new CDate();
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

$date = new CDate();
$first_time = new CDate( $date );
$first_time->setDay( $date->getDay()-1);
$last_time = new CDate( $date );
$last_time->setDay( $date->getDay());

// load the event types
$types = dPgetSysVal( 'EventType' );
$links = array();

// assemble the links for the events
$events = CEvent::getEventsForPeriod( $first_time, $last_time );
$events2 = array();

$html = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';

foreach ($events as $row) {

		$start   = new CDate( $row['event_start_date'] );
		$starttm = $start->format( "%H:%M" );
		$end     = new CDate( $row['event_end_date'] );
		$endtm   = $end->format( "%H:%M" );

		$href = "?m=calendar&a=view&event_id=".$row['event_id'];
		$alt = $row['event_description'];

		$html .= "\n\t<td class=\"event\"  valign=\"top\">";

		$html .= "\n<table cellspacing=\"0\"  cellpadding=\"0\" border=\"0\"><tr>";
		$html .= "\n<td>".$starttm."-".$endtm."</td>";

		$html .= "<td> - ";
		$html .= $href ? "\n\t\t<a href=\"$href\" style='{color: #555555;}' title=\"$alt\">" : '';
		$html .= "\n\t\t{$row['event_title']}";
		$html .= $href ? "\n\t\t</a>" : '';
		$html .= " &nbsp;</td>";

		$html .= "<td>(</td><td>" . dPshowImage( dPfindImage( 'event'.$row['event_type'].'.png', 'calendar' ), 20, 20, '' );
		$html .= "</td>\n<td>&nbsp;" . $types[$row['event_type']] . ")</td>";

		$html .= "</tr></table>";
		$html .= "\n\t</td>";
		$html .= "\n</tr>";

}
$html .= '</table>';
echo $html;
?>
