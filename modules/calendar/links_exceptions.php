<?php /* CALENDAR $Id: links_exceptions.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */

function getExceptionLinks($startPeriod, $endPeriod, &$links, $strMaxLen, $user_id=0, $showHollidays = true )
{
	global $AppUI;
	global $dialog;

	if ( !$user_id )
		$user_id = $AppUI->user_id;
	else
	{
		$userArr = null;
		$userArr[$user_id] = $user_id;
		$user_name = CUser::getUsersFullName($userArr);
		
		$user_name = $user_name[0]['fullname'];
	}
		
	$exceptions = CCalendar::getUserCalendarExclusions($startPeriod, $endPeriod, $user_id);
	
    if ($exceptions && count($exceptions) > 0 && $exceptions !== NULL)
    {
		foreach ($exceptions as $exception)
		{
			$start = new CDate( $exception['from_date'] );
			$end = new CDate( $exception['to_date'] );
			
			$date = $start;

			for($i=0; $i <= $start->dateDiff($end); $i++)
			{
				$url = "index.php?m=admin&a=calendars&user_id=".$user_id."&tab=1&dialog=".$dialog;
				$link['href'] = '';
				$link['alt'] = '';

				$link['text'] = '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr bgcolor ="red">'
					. '<td align="center"><a href="' . $url . '" title="'.$exception['description'].'">'
					. '<span class="event"><font color="white">'.$AppUI->_($exception['description']).($user_name ? ' [ '.$user_name.' ]' : '').'</font></span></a><br/>'
					. '</td></tr></table>';

				$links[$date->format( FMT_TIMESTAMP_DATE )][] = $link;
				$date = $date->getNextDay();
			}
		}
	}
	
	
	if($showHollidays)
	{
		//Feriados
		$hollidays = CHolliday::getHollidaysForPeriod($startPeriod, $endPeriod);

		if ($hollidays && count($hollidays) > 0 && $hollidays !== NULL)
		{
			foreach ($hollidays as $holliday)
			{			
				$link['href'] = '';
				$link['alt'] = '';

				$link['text'] = '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr bgcolor ="red">'
					. '<td align="center"><span class="event"><font color="white">'.$holliday['holliday_name'].'</font></span><br/>'
					. '</td></tr></table>';

				$links[$holliday['holliday_date']][] = $link;
			}
		}
	}
}
?>
