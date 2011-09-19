<?
global $canEdit, $contact_id, $row, $delegator_id, $delegador, $xajax, $AppUI, $type, $types;

require_once("./modules/calendar/calendar.class.php");
/*$Ccontact = new CContact();
$Ccontact->load($contact_id);
$events = $Ccontact->getRelatedEvents();
*/
$types = dPgetSysVal( 'EventType' );

$from = new CDate();

$from->hour = 0;
$from->minute = 0;
$from->second = 0;
$from->year = 1900;

$to = new CDate();
$to->addDays(600);

$events = CEvent::getEventsForPeriodByContact($from,$to,$contact_id);

?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
	<tr class="tableHeaderGral">
		<th>&nbsp;</th>
		<th valign="top" width="20%"><?php echo $AppUI->_( 'Date' ); ?></th>
		<th valign="top" width="20%"><?php echo $AppUI->_( 'Hour' ); ?></th>
		<th valign="top"><?php echo $AppUI->_( 'Event' ); ?></th>
		<th valign="top"><?php echo $AppUI->_( 'Task' ); ?></th>
		<th valign="top"><?php echo $AppUI->_( 'Location' ); ?></th>
	</tr>
<?php
if(count($events)>0){
	require_once("./modules/calendar/calendar.class.php");
	foreach($events AS $event){
		
		$objEvent = new CEvent();
		$objEvent->load($event["event_id"]);
	
		$start = new CDate( $objEvent->event_start_date );
		$start_time = $start->hour.":".$start->minute;
		
		$end = new CDate( $objEvent->event_end_date );
		$end_time = $end->hour.":".$end->minute;
		
		if(!$objEvent->event_recurse_type)
		{
			if($objEvent->event_type == "3")
			{
				$timeEvent = $AppUI->_($types[$objEvent->event_type]);
				$dateEvent = $start->format("%d/%m/%Y");
			}
			else
			{
				$timeEvent = $start_time."&nbsp;-&nbsp;".$end_time;
				$dateEvent = $start->format("%d/%m/%Y")."&nbsp;-&nbsp;".$end->format("%d/%m/%Y");
			}
		}
		else
		{
			$dateEvent = $start->format("%d/%m/%Y");
	
			if($objEvent->event_type == "3")
			{
				$timeEvent = $AppUI->_($types[$objEvent->event_type]);
			}
			else
			{
				if($event["event_start_date"])
				{
					$dateTemp = new CDate($event["event_start_date"]);
					$dateEvent = $dateTemp->format("%d/%m/%Y");
				}
	
				$timeEvent = substr($objEvent->event_start_time, 0, 5)."&nbsp;-&nbsp;".substr($objEvent->event_end_time, 0, 5);
			}
		}
		
		$sql = "SELECT task_name FROM tasks WHERE task_id = ".$objEvent->event_task;
		$data = mysql_fetch_array(mysql_query($sql));
		$task = $data["task_name"];
		if(empty($task)) $task = "-";
		
		$s = '';
		$s .= '<tr>';
		$s .= '<td valign="top">'.'</td>';
		$s .= '<td valign="top" align="center">'.$dateEvent.'</td>';
		$s .= '<td valign="top" align="center">'.$timeEvent.'</td>';
		$s .= '<td><strong>';
		$s .= '<a href="index.php?m=calendar&a=view&event_id='.$id.'&delegator_id='.$AppUI->user_id.'">'.$objEvent->event_title.'</a>';
		$s .= '</strong></td>';
		$s .= '<td valign="top">'.$task.'</td>';
		$s .= '<td valign="top">'.$objEvent->event_location.'</td>';
		$s .= '</tr>';
		
		echo $s;
	}
}else{
	echo "<tr><td>".$AppUI->_("No data available")."</td></tr>";
}
?>
</table>