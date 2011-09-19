<?php

$do_report 		    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
$log_all_projects 	= dPgetParam($_POST["log_all_projects"], 0);
$log_all		    = dPgetParam($_POST["log_all"], 0);

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date   = intval( $log_end_date )   ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );
?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}
</script>

<form name="editFrm" action="index.php?m=projects&a=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">


<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period');?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all_projects" <?php if ($log_all_projects) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All Projects' );?>
	</td>
	
	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>

</table>
</form>

<?php
if($do_report){
	
	// Let's figure out which users we have
	$sql = "SELECT  u.user_id,
	 				u.user_username, 
					u.user_first_name, 
					u.user_last_name
	        FROM users AS u";
	
	$user_list = db_loadHashList($sql, "user_id");
	
	$sql = "SELECT t.*
			FROM tasks AS t
			WHERE (task_start_date
			   BETWEEN \"".$start_date->format( FMT_DATETIME_MYSQL )."\" 
	                AND \"".$end_date->format( FMT_DATETIME_MYSQL )."\" 
	           OR task_end_date	BETWEEN \"".$start_date->format( FMT_DATETIME_MYSQL )."\" 
	                AND \"".$end_date->format( FMT_DATETIME_MYSQL )."\")
	        AND !isnull(task_end_date) AND task_end_date != '0000-00-00 00:00:00'
	        AND !isnull(task_start_date) AND task_start_date != '0000-00-00 00:00:00'
	        AND task_dynamic   ='0'
	        AND task_milestone = '0'
	        AND task_duration  > 0";

	if(!$log_all_projects){
		$sql .= " AND t.task_project='$project_id'\n";
	}

	$task_list_hash = db_loadHashList($sql, "task_id");
	$task_list      = array();
	foreach($task_list_hash as $task_id => $task_data){
		$task = new CTask();
		$task->bind($task_data);
		$task_list[] = $task;
	}
	
	$user_usage            = array();
	$task_dates            = array();
	
	$actual_date = $start_date;
	$days_header = ""; // we will save days title here
	
	if ( count($task_list) == 0 ) {
		echo "<p>" . $AppUI->_( 'No data available' ) ."</p>";
	}else {
		foreach($task_list as $task) {
			$task_start_date  = new CDate($task->task_start_date);
			$task_end_date    = new CDate($task->task_end_date);
			
			$day_difference   = $task_end_date->dateDiff($task_start_date);
			$actual_date      = $task_start_date;
	
			$users                 = $task->getAssignedUsers();
			$task_duration_per_day = $task->getTaskDurationPerDay();
			
			for($i = 0; $i<=$day_difference; $i++){
				if(!$actual_date->before($start_date) && !$actual_date->after($end_date)
				   && $actual_date->isWorkingDay()){
	
					foreach($users as $user_id => $user_data){
						if(!isset($user_usage[$user_id][$actual_date->format("%Y%m%d")])){
							$user_usage[$user_id][$actual_date->format("%Y%m%d")] = 0;
						}
						$user_usage[$user_id][$actual_date->format("%Y%m%d")] += $task_duration_per_day;
						if($user_usage[$user_id][$actual_date->format("%Y%m%d")] < 0.005){
							//We want to show at least 0.01 even when the assigned time is very small so we know
							//that at that time the user has a running task
							$user_usage[$user_id][$actual_date->format("%Y%m%d")] += 0.006;
						}
					}
				}
				$actual_date->addDays(1);
			}
		}
	
		$days_difference = $end_date->dateDiff($start_date);
		$actual_date     = $start_date;
		$working_days_count = 0;
		$allocated_hours_sum = 0;
		
		$table_header = "<tr><th>".$AppUI->_("User")."</th>";
		for($i=0; $i<=$days_difference; $i++){
			$table_header .= "<th>".utf8_encode(Date_Calc::getWeekdayAbbrname($actual_date->day, $actual_date->month, $actual_date->year, 3))."</th>";
			if($actual_date->isWorkingDay()){
				$working_days_count++;
			}
			$actual_date->addDays(1);
		}
		$table_header .= "<th nowrap='nowrap'>".$AppUI->_("Allocated")."</th></tr>";
		
		$table_rows = "";
		
		foreach($user_list as $user_id => $user_data){
			if(isset($user_usage[$user_id])) {
				$table_rows .= "<tr><td nowrap='nowrap'>(".$user_data["user_username"].") ".$user_data["user_first_name"]." ".$user_data["user_last_name"]."</td>";
				$actual_date = $start_date;
				for($i=0; $i<=$days_difference; $i++){
					$table_rows .= "<td>";
					if(isset($user_usage[$user_id][$actual_date->format("%Y%m%d")])){
						$hours       = number_format($user_usage[$user_id][$actual_date->format("%Y%m%d")],2);
						$table_rows .= $hours;
						$percentage_used = round($hours/$AppUI->getConfig("daily_working_hours")*100);
						$bar_color       = "blue";
						if($percentage_used > 100){
							$bar_color = "red";
							$percentage_used = 100;
						}
						$table_rows .= "<div style='height:2px;width:$percentage_used%; background-color:$bar_color'>&nbsp;</div>";
					} else {
						$table_rows .= "&nbsp;";
					} 
					$table_rows .= "</td>";
					$actual_date->addDays(1);
				}
				
				$array_sum = array_sum($user_usage[$user_id]);
				$average_user_usage = number_format( ($array_sum/($working_days_count*$AppUI->getConfig("daily_working_hours")))*100, 2);
				$allocated_hours_sum += $array_sum;
				
				$bar_color = "blue";
				if($average_user_usage > 100){
					$bar_color = "red";
					$average_user_usage = 100;
				}
				$table_rows .= "<td ><div align='right'>". $average_user_usage ;
				$table_rows .= "%</div>";
				$table_rows .= "<div align='left' style='height:2px;width:$average_user_usage%; background-color:$bar_color'>&nbsp;</div></td>";
				$table_rows .= "</tr>";
				
			}
		}
		?>
			<center><table class="std">
			<?php echo $table_header . $table_rows; ?>
			</table>
			<table width="100%"><tr><td align="center">
		<?php
			$total_hours_capacity = $working_days_count*$AppUI->getConfig("daily_working_hours")*count($user_usage);
	
			echo $AppUI->_("<h4>Total capacity for shown users</h4>");
			echo $AppUI->_("Allocated hours").": ".number_format($allocated_hours_sum,2)."<br />";
			echo $AppUI->_("Total capacity").": ".number_format($total_hours_capacity,2)."<br />";
			echo $AppUI->_("Percentage used").": ".number_format($allocated_hours_sum/$total_hours_capacity,2)*100 ."%<br />";
	?>
			</td>
			<td align="center">
		<?php
			$total_hours_capacity = $working_days_count*$AppUI->getConfig("daily_working_hours")*count($user_list);
	
			echo $AppUI->_("<h4>Total capacity for all users</h4>");
			echo $AppUI->_("Allocated hours").": ".number_format($allocated_hours_sum,2)."<br />";
			echo $AppUI->_("Total capacity").": ".number_format($total_hours_capacity,2)."<br />";
			echo $AppUI->_("Percentage used").": ".number_format($allocated_hours_sum/$total_hours_capacity,2)*100 ."%<br />";
	}			
}
			?>		
			</td></tr>
			</table>
			</center>


