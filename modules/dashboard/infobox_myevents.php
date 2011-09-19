<?php
set_time_limit(10);

//echo "<pre>";
//echo "\n require calendar class";
if (! class_exists("CMonthCalendar"))
	require_once( $AppUI->getModuleClass( 'calendar' ) );

if (! class_exists("CProject"))
	require_once( $AppUI->getModuleClass( 'projects' ) );

	
if (! function_exists("multi_sort2")	){
	function multi_sort2(&$array, $sortby, $order='asc') {
		if (!count($array)>0) return true;
		$sortarray = $sortby."_srt";
		$$sortarray = array();
		foreach ($array as $key => $row) {
			$$sortarray = array_merge($$sortarray, array ($key => $row[$sortby]));
		}
		
		//var_dump($$sortarray);
		//echo "<br>";
		//var_dump($array);
		
		$const = $order == 'asc' ? SORT_ASC : SORT_DESC;
		
		// Sort by volume DESC, then by edition ASC.  
		// Plug in $arr at the end so it is sorted by the common key
		$s = array_multisort($$sortarray, $const, $array);
		
	   	return $s;
	}
}


if (! function_exists("array_csort")	){
	function array_csort()   //coded by Ichier2003
	{
	    $args = func_get_args();
	    $marray = array_shift($args);
		
		if ( empty( $marray )) return array();
		
		$i = 0;
	    $msortline = "return(array_multisort(";
		$sortarr = array();
	    foreach ($args as $arg) {
	        $i++;
	        if (is_string($arg)) {
	            foreach ($marray as $row) {
	                $sortarr[$i][] = $row[$arg];
	            }
	        } else {
	            $sortarr[$i] = $arg;
	        }
	        $msortline .= "\$sortarr[".$i."],";
	    }
	    $msortline .= "\$marray));";
	
	    eval($msortline);
	    return $marray;
	}
}
//echo "\n instanciate cdate";
$this_day = new CDate();
//echo "\n get calendar company or user company";
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;
//echo " = $company_id";

//echo "\n get today start date and end date";
$date = new CDate();
$first_time = new CDate( $date );
$first_time->setDay( $date->getDay());
$first_time->setTime( 0, 0, 0 );
$last_time = new CDate( $date );
$last_time->setDay( $date->getDay());
$last_time->setTime( 23, 59, 59 );
/*
$first_time = new CDate( $date );
$first_time->setDay( 1 );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( $date );
$last_time->setDay( $date->getDaysInMonth() );
*/


//echo "\n load event types";
// load the event types
$types = dPgetSysVal( 'EventType' );
$links = array();


//echo "\n get events for period";
// assemble the links for the events
//echo "<p>Calculando links para ".$first_time->format( FMT_DATETIME_MYSQL )." - ".$last_time->format( FMT_DATETIME_MYSQL )."</p>";
$events = CEvent::getEventsForPeriod($first_time, $last_time);
$events2 = array();


//echo "\n build output data";
$html = '<table cellspacing="0" cellpadding="2" border="0" width="100%" class=""><tr>';


for ($i=0; $i<count($events); $i++){
	$row=$events[$i];
	$start   = new CDate( $row['event_start_date'] );
	$starttm = $start->format( "%H:%M" );
	$end     = new CDate( $row['event_end_date'] );
	$endtm   = $end->format( "%H:%M" );
	$objEvent = new CEvent();
	if ($objEvent->load( $row['event_id'])){
	
		if ( !$objEvent->event_recurse_type )
		{
			//Es un evento puntual		
			//$start = new CDate( $objEvent->event_start_date );
			//$events2[$start->format( "%H%M%S" )] = $objEvent;		
		}
		else
		{
			//Es un evento con repeticion
			$st = $objEvent->event_start_time;
			$starttm = substr($st, 0, 2).":".substr($st, 3, 2) ;
			$et = $objEvent->event_end_time;
			$endtm = substr($et, 0, 2).":".substr($et, 3, 2) ;							
		}	
		$events[$i]["st"]=$starttm;
		$events[$i]["et"]=$endtm;
	}

}

//echo "\n sort outout data";
//var_dump($events);
//multi_sort2($events,"st", "desc");
//var_dump($events);
array_csort($events,"st", "et", "event_id", "event_start_date");

//echo "\n dump events \n";
//var_dump($events);
//echo "</pre>";

foreach ($events as $row) {

		$objEvent = new CEvent();
		if ($objEvent->load( $row['event_id'])){
		
			$starttm = $row["st"];
			$endtm = $row["et"];
			
			$href = "?m=calendar&a=view&event_id=".$objEvent->event_id;
			$alt = $objEvent->event_description;
	
			$html .= "\n\t<td class=\"event\"  valign=\"top\">";
	
			$html .= "\n<table cellspacing=\"0\"  cellpadding=\"0\" border=\"0\"><tr>";
			$html .= "\n<td>[".$starttm."-".$endtm."]</td>";
	
			$html .= "<td>&nbsp;- ";
			$html .= $href ? "\n\t\t<a href=\"$href\" style='{color: #555555;}' title=\"$alt\">" : '';
			$html .= "\n\t\t{$objEvent->event_title}";
			$html .= $href ? "\n\t\t</a>" : '';
			$html .= " &nbsp;</td>";
	
			$html .= "<td>(</td><td>" . dPshowImage( dPfindImage( 'event'.$objEvent->event_type.'.png', 'calendar' ), 20, 20, '' );
			$html .= "</td>\n<td>&nbsp;" . $AppUI->_($types[$objEvent->event_type] ). ")</td>";
	
			$html .= "</tr></table>";
			$html .= "\n\t</td>";
			$html .= "\n</tr>";
            $html .= "\n<tr class=\"tableRowLineCell\"><td ></td></tr>";
		}
}
$html .= '</table>';
echo $html;
?>
