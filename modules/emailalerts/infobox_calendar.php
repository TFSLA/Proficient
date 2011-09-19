<?
require_once( "modules/calendar/calendar.class.php" );
require_once( "infobox_calendar2.php" );

$this_day = new CDate( $date );

$minical = new CMonthCalendar( $this_day );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->setLinkFunctions( 'clickDay' );

echo '<table class="plain" align="center" cellspacing="0" cellpadding="0" border="0" width="180"><tr>';
echo '<td align="center">'.$minical->show().'</td>';
echo '</tr></table>';
?>