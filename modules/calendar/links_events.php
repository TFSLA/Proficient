<?php /* CALENDAR $Id: links_events.php,v 1.2 2009-07-15 00:00:24 nnimis Exp $ */

/**
* Sub-function to collect events within a period
* @param Date the starting date of the period
* @param Date the ending date of the period
* @param array by-ref an array of links to append new items to
* @param int the length to truncate entries by
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
function getEventLinks( $startPeriod, $endPeriod, &$links, $strMaxLen, $user_id=0, $company_id=null, $project_id=null)
{
	global $AppUI;
	global $dialog;

	if ( !$user_id )
	{
		$user_id = $AppUI->user_id;
	}

	//echo "<p>Antes de pedir los eventos para el periodo</p>";
	$events = CEvent::getEventsForPeriod( $startPeriod, $endPeriod, $user_id, $company_id, $project_id );
	//echo "<p>Calculando links para ".$startPeriod->format( FMT_DATETIME_MYSQL )." - ".$endPeriod->format( FMT_DATETIME_MYSQL )."</p>";
	// assemble the links for the events
    //var_dump($events);
    /*
    foreach($events as $rr){
        echo $rr["event_id"]." ";
    }
    */

    if ($events && count($events) > 0 && $events !== NULL)
		foreach ($events as $row)
		{
			$ev = new CEvent();
			$ev->load($row["event_id"]);
			$color = $ev->event_owner == $user_id ? "black" : "red";

			if ( !$ev->event_recurse_type )
			{
				//Eventos puntuales
				$start = new CDate( $ev->event_start_date );
				$end = new CDate( $ev->event_end_date );

				$tmp_start_time = new CDate( $ev->event_start_date );
				$start_time = $tmp_start_time->hour.":".$tmp_start_time->minute;

				$tmp_end_time = new CDate( $ev->event_end_date );
				$end_time = $tmp_end_time->hour.":".$tmp_end_time->minute;

				$date = $start;

				for($i=0; $i <= $start->dateDiff($end); $i++)
				{
					// the link
					$url = '?m=calendar&a=view&event_id='.$row['event_id'].'&delegator_id='.$user_id.'&dialog='.$dialog.'&date='.$date->format( FMT_TIMESTAMP_DATE );
					$link['href'] = '';
					$link['alt'] = $ev->event_description;

					if($ev->event_type=="3"){
					$link['text'] = '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr bgcolor ="#0099FF">'
						. '<td valign="top">';
						if(!getDenyEdit("timexp")) {
							$link['text'] .= '<a href="javascript:report_hours('.$row['event_id'].');" >'
							. '<img src="/images/icons/calendar_report.png" alt="Cargar Horas" border=0 style="height:18px;">'
							.  '</a>';
						}
						$link['text'] .= '</td><td align="center"><a href="' . $url . '" title="'.$ev->event_description.'">'
						. '<span class="event"><font color="'.$color.'">'.$ev->event_title.'</font></span></a><br/>'
						. '</td></tr></table>';
					}
					else{
                    $link['text'] = '<table cellspacing="0" cellpadding="0" border="0" ><tr>'
						. '<td valign="top">';
						if(!getDenyEdit("timexp")) {
							$link['text'] .= '<a href="javascript:report_hours('.$row['event_id'].');" >'
							. '<img src="/images/icons/calendar_report.png" alt="Cargar Horas" border=0 style="height:18px;">'
							. '</a>';
						}
						$link['text'] .= '</td><td><a href=' . $url . '>' 
						. '</a></td>'
						. '<td><a href="' . $url . '" title="'.$ev->event_description.'">['.$start_time.'&nbsp;-&nbsp;'.$end_time.']</br><span class="event">'.$ev->event_title.''
						. '</span></a><br/><br/>'
						. '</td></tr></table>';
					}

					$links[$date->format( FMT_TIMESTAMP_DATE )][] = $link;
					$date = $date->getNextDay();
				}
			}
			else
			{
				//echo "<p>Links: Procesando el evento '$ev->event_title'</p>";
				$start = new CDate( $row["event_start_date"] );
	                                    $end_tmp = NULL;
	                                    $date = clone($start);
	                                    
	            if($row["event_end_date"] != NULL) $end_tmp = new CDate( $row["event_end_date"] );
				//Para los recursivos uso este campo para guardar la fecha de la ocurrencia
				$url = '?m=calendar&a=view&event_id='.$row['event_id'].'&delegator_id='.$user_id.'&dialog='.$dialog.'&date='.$date->format( FMT_TIMESTAMP_DATE );
				$link['href'] = '';
				$link['alt'] = $ev->event_description;

				if($ev->event_type=="3"){
					$link['text'] = '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr bgcolor ="#0099FF">'
						. '<td valign="top"><a href="javascript:report_hours('.$row['event_id'].');" >'
						. '<img src="/images/icons/calendar_report.png" alt="Cargar Horas" border=0 style="height:18px;">'
						. '</a></td>'
						. '<td align="center"><a href="' . $url . '" title="'.$ev->event_description.'">&nbsp;<span class="event">'.$ev->event_title.''
						.'</span></a><br/><br/>'
						. '</td></tr></table>';
					}
					else{
	                $link['text'] = '<table cellspacing="0" cellpadding="0" border="0"><tr>'
	                	. '<td valign="top"><a href="javascript:report_hours('.$row['event_id'].');" >'
						. '<img src="/images/icons/calendar_report.png" alt="Cargar Horas" border=0 style="height:18px;">'
						. '</a></td>'
						. '<td><a href=' . $url . '>'
						. '</a></td><br/>'
						. '<td><a href="' . $url . '" title="'.$ev->event_description.'">['.substr($ev->event_start_time, 0, 5).'&nbsp;-&nbsp;'.substr($ev->event_end_time, 0, 5).']</br><span class="event">'.$ev->event_title.'</span></a><br/><br/>'
						. '</td></tr></table>';
					}

				$links[$start->format( FMT_TIMESTAMP_DATE )][] = $link;
	            if(isset($end_tmp)){
	                   $links[$end_tmp->format( FMT_TIMESTAMP_DATE )][] = $link;
	            }
			}
		}
}
?>
