<?php

require_once( './modules/timexp/report_to_items.php' );
require_once( './modules/projects/ajax.php' );

global $xajax;

$xajax->printJavaScript('/includes/xajax/');

$types = dPgetSysVal( 'EventType' );

if (isset($_POST['fromdate']))
	$from = new CDate($_POST['fromdate']);
else
	$from = new CDate();
	
$from->hour = 0;
$from->minute = 0;
$from->second = 0;
	
if (isset($_POST['todate']))
	$to = new CDate($_POST['todate']);
else
{
	$to = new CDate();
	$to->addDays(90);
}

$project_id = $_GET["project_id"];
$task_id = $_GET["task_id"];

if($project_id > 0)
{
	$tasks_array = CTask::getTasksList("all" , 0, 0, $project_id, 0, "0","task");

	for($i=0;$i<count($tasks_array["rows"]);$i++)
	{
		$tasks .= $tasks_array["rows"][$i]["task_id"].",";
	}
	
	if($tasks)
		$tasks = substr($tasks, 0, (strlen($tasks)-1));	
}
else
{
	$tasks = $task_id;
}
$tableStyle = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"';

$strHtml = "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" $tableStyle >";
$strHtml .= "	<tr>";
$strHtml .= "			<form name=\"frm\" method=\"post\">";
$strHtml .= "		<td colspan=\"3\" nowrap=\"nowrap\">";
$strHtml .= 				$AppUI->_('From'). ": ";
$strHtml .= "				<input type=\"hidden\" name=\"fromdate\" value=\"".$from->format("%Y%m%d")."\">";
$strHtml .= "				<input type=\"text\" name=\"a_fromdate\" disabled=\"disabled\" class=\"text\" value=\"".$from->format("%d/%m/%Y")."\">";
$strHtml .= "				<a href=\"#\" onClick=\"popCalendar('fromdate')\"><img src=\"./images/calendar.gif\" width=\"24\" height=\"12\" alt=\"".$AppUI->_('Calendar')."\" border=\"0\" /></a>";
$strHtml .= "				&nbsp;&nbsp;&nbsp;";
$strHtml .= 				$AppUI->_('To'). ": ";
$strHtml .= "				<input type=\"hidden\" name=\"todate\" value=\"".$to->format("%Y%m%d")."\">";
$strHtml .= "				<input type=\"text\" name=\"a_todate\" disabled=\"disabled\" class=\"text\" value=\"".$to->format("%d/%m/%Y")."\">";
$strHtml .= "				<a href=\"#\" onClick=\"popCalendar('todate')\"><img src=\"./images/calendar.gif\" width=\"24\" height=\"12\" alt=\"".$AppUI->_('Calendar')."\" border=\"0\" /></a>";
$strHtml .= "				&nbsp;&nbsp;&nbsp;";
$strHtml .= "				<input type=\"submit\" class=\"button\" value=\"".$AppUI->_('filter')."\">";
$strHtml .= "		</td>";
$strHtml .= "			</form>";
$strHtml .= "		<td colspan=\"3\" nowrap=\"nowrap\" align=\"right\">";
$strHtml .= "			<input type=\"button\" class=\"button\" value=\"".$AppUI->_('new event')."\" onclick=\"javascript:window.open('?m=calendar&a=addedit&dialog=1&suppressLogo=1&delegator_id=$AppUI->user_id&project=$project_id', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no')\">";
$strHtml .= "		</td>";
$strHtml .= "	</tr>";

$strHtml .= "<tr class=\"tableHeaderGral\">";
$strHtml .= "<td nowrap=\"nowrap\"></td>";
$strHtml .= "<td nowrap=\"nowrap\">".$AppUI->_('Date')."</td>";
$strHtml .= "<td nowrap=\"nowrap\">".$AppUI->_('Time')."</td>";
$strHtml .= "<td nowrap=\"nowrap\">".$AppUI->_('Event')."</td>";
$strHtml .= "<td nowrap=\"nowrap\">".$AppUI->_('Task')."</td>";
$strHtml .= "<td nowrap=\"nowrap\">".$AppUI->_('Location')."</td>";
$strHtml .= "</tr>";

if($tasks)
	$events = CEvent::getEventsForPeriodByTasks( $from, $to, $tasks);

if($events)
{
	foreach($events as $event)
	{
		$dateEvent;
		$timeEvent;

		$objEvent = new CEvent();
		$objEvent->load($event["event_id"]);

		$start = new CDate( $objEvent->event_start_date );
		$start_time = $start->hour.":".$start->minute;

		$end = new CDate( $objEvent->event_end_date );
		$end_time = $end->hour.":".$end->minute;

		if($start->format("%Y%m%d") >= $from->format("%Y%m%d") && $start->format("%Y%m%d") <= $to->format("%Y%m%d"))
		{
			$task = getTaskName($objEvent->event_task);
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

			$strHtml .= "<tr>";
			
			if(($objEvent->event_owner == $AppUI->user_id) || ($AppUI->user_type == 1))
			{
				$strHtml .= "<td width=\"7%\">";
				if(!getDenyEdit("timexp")) {
					$strHtml .= "<a href=\"javascript:report_hours('".$objEvent->event_id."','5');\" >";
					$strHtml .= "<img src=\"./images/icons/calendar_report.png\" border=\"0\" style=\"height:18px;\">";
				}
				$strHtml .= "</a>";
				$strHtml .= "&nbsp;";
				$strHtml .= "<a href=\"/index.php?m=calendar&a=addedit&event_id=".$objEvent->event_id."\">";
				$strHtml .= dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );
				$strHtml .= "</a>";
				$strHtml .= "&nbsp;";				
				$strHtml .= "<a href=\"javascript:delIt_event(".$objEvent->event_id.")\">";
				$strHtml .= dPshowImage( './images/icons/trash_small.gif', 16, 16, '' );
				$strHtml .= "</a>";
				$strHtml .= "</td>";
			}
			else
			$strHtml .= "<td  width=\"7%\">&nbsp;</td>";
			
			
			$strHtml .= "<td align=\"left\" width=\"20%\" nowrap>".$dateEvent."</a></td>";
			$strHtml .= "<td align=\"left\" width=\"20%\" nowrap>".$timeEvent."</a></td>";
			$strHtml .= "<td align=\"left\"><b>".$objEvent->event_title."</b></td>";
			$strHtml .= "<td align=\"left\">".$task."</td>";
			$strHtml .= "<td align=\"left\">".$objEvent->event_location."</td>";
			$strHtml .= "</tr>";			
		}		
	}
}

$strHtml .= "</table>";

echo($strHtml);

?>

<form name="frmDeleteEvent" action="./index.php?m=calendar" method="post">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<? echo($project_id) ?>" />
	<input type="hidden" name="task_id" value="<? echo($task_id) ?>" />
	<input type="hidden" name="fromproject" value="<? if($project_id > 0) { echo("1"); } else { echo("0"); } ?>" />
	<input type="hidden" name="fromtask" value="<? if($task_id > 0) { echo("1"); } else { echo("0"); } ?>" />
	<input type="hidden" name="event_id" value="" />
</form>

<script language="javascript">
	function popCalendar( field )
	{
		calendarField = field;
		idate = eval( 'document.frm.' + field + '.value' );
		window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate +'&suppressLogo=1', 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
	}
	
	function setCalendar( idate, fdate ) {
		fld_date = eval( 'document.frm.' + calendarField );
		fld_fdate = eval( 'document.frm.a_' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
	}	
	
	function delIt_event(pEventId) {
		if (confirm( "¿Borrar el evento?" )) {
			document.frmDeleteEvent.event_id.value = pEventId;
			document.frmDeleteEvent.submit();
		}
	}
</script>

<?php

function getTaskName($task_id)
{
	$sql = "SELECT task_name FROM tasks WHERE task_id = $task_id";
	$register = mysql_fetch_array(mysql_query($sql)) or die(mysql_error());
	
	return $register['task_name'];
}

?>
