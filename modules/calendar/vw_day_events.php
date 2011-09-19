<?php /* CALENDAR $Id: vw_day_events.php,v 1.2 2009-07-15 00:00:24 nnimis Exp $ */
global $this_day, $first_time, $last_time, $company_id, $project_id;
require_once( './modules/timexp/report_to_items.php' );

$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator( $delegator_id, $mod_id ) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg("Delegator");
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
}
// load the event types
$types = dPgetSysVal( 'EventType' );
$links = array();

// assemble the links for the events

$first_time->setTime();
$first_time->addDays(1);

$events = CEvent::getEventsForPeriod( $first_time, $last_time, $delegator_id, ($company_id == "0" || $company_id == null ? null : $company_id), ($project_id == "0" || $project_id == null ? null : $project_id));
$events2 = array();

foreach ($events as $row)
{
	$ev = new CEvent;
	$ev->load($row["event_id"]);
	$events2[$ev->event_id] = $ev;
}

$tf = $AppUI->getPref('TIMEFORMAT');

$dayStamp = $this_day->format( FMT_TIMESTAMP_DATE );

//Tengo que ir a buscar a la compañia los settings del dia
$cpy = new CCompany();
$cpy->load( $AppUI->user_company );
//$start = absTime($cpy->company_start_time);
//$end = absTime($cpy->company_end_time);
$inc = $AppUI->getConfig('cal_day_increment');
/*
Esto era lo viejo, del archivo de configuracion, habria que hacer que esto sea el default si la compañia no lo tiene configurado.

$start = $AppUI->getConfig('cal_day_start');
$end = $AppUI->getConfig('cal_day_end');
*/

//if ($start == null ) $start = 8;
//if ($end == null ) $end = 17;
//if ($inc == null) $inc = 15;

$this_day->setTime( $start, 0, 0 );

echo  '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';

if ($events && count($events) > 0 && $events !== NULL)
{
	foreach ($events as $row)
	{
		$ev = new CEvent();
		$ev->load($row["event_id"]);

		if($ev->event_type=="3"){
		$href = "?m=calendar&a=view&event_id=$ev->event_id&delegator_id=$delegator_id&dialog=$dialog&date=".$this_day->format( FMT_TIMESTAMP_DATE )."";
		$alt = $ev->event_description;

		echo "\n\t<tr><td class=\"event\" valign=\"top\"><table  border=\"0\" ><tr><td class=\"event\" width=\"12\" valign=\"top\">";
		echo "\n" . dPshowImage( dPfindImage( 'event'.$ev->event_type.'.png', 'calendar' ), 12, 12, '' );
		echo "</td><td class=\"event\" rowspan=\"$rows\">";


		if ($href && !getDenyEdit("timexp")){ 
			echo "<a href='javascript:report_hours(".$ev->event_id.");' >"
				. "<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'>"
				. "</a><a href=\"$href\" class=\"event\" title=\"$alt\">"; 
		}
		$color = $ev->event_owner == $delegator_id ? "" : "red";
		echo "<font color=\"$color\">$ev->event_title</font>";
		if ($href){ echo "</a>";}
		echo "</td></tr></table></td></tr>";
		}
	}
}

echo '</table>';

$html = '<table cellspacing="1" bgcolor="white" cellpadding="2" border="0" width="100%" class="tbl">';

$n = (24)*60/$inc;

for ($i=0; $i < $n; $i++) {
	$html .= "\n<tr>";

	$tm = $this_day->format( $tf );
	$html .= "\n\t<td width=\"1%\" align=\"left\" nowrap>".($this_day->getMinute() ? $tm : "<b>$tm</b>")."</td>";

	$timeStamp = $this_day->format( "%H:%M:%S" );

	foreach ($events2 as $ev)
	{
		if(!$ev->event_recurse_type)
		{
			$evStartTime = new CDate( $ev->event_start_date );
			$startTime = $evStartTime->format("%H:%M:%S");
		}
		else
		{
			$startTime = $ev->event_start_time;
		}

		if($startTime == $timeStamp && $ev->event_type != "3")
		{
			if ( !$ev->event_recurse_type )
			{
				//Evento puntual
				$et = new CDate( $ev->event_end_date );
				$rows = (($et->getHour()*60 + $et->getMinute()) - ($this_day->getHour()*60 + $this_day->getMinute()))/$inc;
			}
			else
			{
				$et = $ev->event_end_time;
				$rows = ((substr($et, 0, 2)*60 + substr($et, 3, 2)) - ($this_day->getHour()*60 + $this_day->getMinute()))/$inc;
			}

			$href = "?m=calendar&a=view&event_id=$ev->event_id&delegator_id=$delegator_id&dialog=$dialog&date=".$this_day->format( FMT_TIMESTAMP_DATE )."";
			$alt = $ev->event_description;

			$html .= "\n\t<td class=\"event\" rowspan=\"$rows\" valign=\"top\">";

			$html .= "\n<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr>";
			$html .= "\n<td>" . dPshowImage( dPfindImage( 'event'.$ev->event_type.'.png', 'calendar' ), 16, 16, '' );
			$html .= "</td>\n<td>&nbsp;<b>" . $AppUI->_($types[$ev->event_type]) . "</b></td></tr></table>";
			
			if(!getDenyEdit("timexp")) {
				$html .= $href ? "\n\t\t<a href='javascript:report_hours(".$ev->event_id.");' >"
					. "<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'>"
					. "</a><a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
			}
			$color = $ev->event_owner == $delegator_id ? "" : "red";
			$html .= "<font color=\"$color\">\n\t\t{$ev->event_title}</font>";
			$html .= $href ? "\n\t\t</a>" : '';
			$html .= "\n\t</td>";
		}
	}

	$html .= "\n</tr>";

	$this_day->addSeconds( 60*$inc );
}

$html .= '</table>';

echo $html;

function absTime( $t )
{
	//Esta funcion se fija si la hora tiene media y lo transforma todo a un double
	$s = ($t[0]*10 + $t[1]).".".( $t[3] == "3" ? "5" : "0" );
	return doubleval( $s );
}
?>
