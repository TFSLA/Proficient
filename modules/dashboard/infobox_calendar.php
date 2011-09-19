<?
if (! class_exists("CMonthCalendar"))
	require_once( $AppUI->getModuleClass( 'calendar' ) );
	
require_once( "infobox_calendar2.php" );

$date = date('Ymd');
$this_day = new CDate( $date );
$minical = new CMonthCalendar( $this_day );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->setLinkFunctions( 'clickDay' );

echo '<table class="" align="center" cellspacing="0" cellpadding="0" border="0" width="180"><tr>';
echo '<td align="center">'.$minical->show().'</td>';
echo '</tr></table>';
?>
