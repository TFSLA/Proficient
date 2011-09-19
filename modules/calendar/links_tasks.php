<?php /* CALENDAR $Id: links_tasks.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */

/**
* Sub-function to collect tasks within a period
*
* @param Date the starting date of the period
* @param Date the ending date of the period
* @param array by-ref an array of links to append new items to
* @param int the length to truncate entries by
* @param int the company id to filter by
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/


function getTaskLinks( $startPeriod, $endPeriod, &$links, $strMaxLen, $company_id=0, $project_id=null, $myassignments=false, $mapTasks=false, $assignUser=null)
{
	GLOBAL $AppUI;

	include("./modules/tasks/possible_tasks.inc.php");

	if (@$project_id)
		$tasks = CTask::getTasksForPeriod( $startPeriod, $endPeriod, 0, $project_id, '',$tasklist );
	else
		$tasks = CTask::getTasksForPeriod( $startPeriod, $endPeriod, $company_id ,'','', $tasklist);
		
	$durnTypes = dPgetSysVal( 'TaskDurationType' );

	$link = array();
	$sid = 3600*24;
	// assemble the links for the tasks
	
	foreach ($tasks as $row)
	{
		$resources = null;
		$users = CTask::getAssignedUsers($row["task_id"]);
		
		//FILTRO PARA SABER SI LA TAREA TIENE RECURSO.
		if($mapTasks)
		{
			$unsettask = true;
			
			if(count($users))
			{
				foreach ($users as $user_id => $user_value)
				{
					if($assignUser == null)
					{
						$unsettask = false;
						break;
					}
					else if ($assignUser == $user_value["user_id"] || ($myassignments && $AppUI->user_id == $user_value["user_id"]))
					{
						$unsettask = false;
						break;
					}
				}
			}
			
			if($unsettask)
				continue;			
		}
		
		//FILTRO PARA SABER SI MUESTRO MIS ASIGNACIONES O NO.
		if ($myassignments && !$mapTasks)
		{
			$unsettask = true;
			
			if(count($users))
			{
				foreach ($users as $user_id => $user_value)
				{
					if ($AppUI->user_id == $user_value["user_id"])
					{
						$unsettask = false;
						break;
					}
				}
			}
			
			if($unsettask)
				continue;
		}
		
		// the link
		$link['href'] = "?m=tasks&a=view&task_id=".$row['task_id'];
		$link['alt'] = "Proyecto: ".$row['project_name'];

		// the link text
		if (strlen( $row['task_name'] ) > $strMaxLen)
		{
			$row['task_name'] = substr( $row['task_name'], 0, $strMaxLen ).'...';
		}
		
		// determine which day(s) to display the task
		$start = new CDate( $row['task_start_date'] );
		$end = $row['task_end_date'] ? new CDate( $row['task_end_date'] ) : null;
		$durn = $row['task_duration'];
		$durnType = $row['task_duration_type'];
		
		if (count($users)>0)
		{
			if($mapTasks)
				$resources = " [ ";
				
			$link['alt'] .= "\nUsuarios Asignados: ";
			foreach ($users as $user_id => $user_value)
			{
				if($mapTasks)
					$resources .= $user_value["user_last_name"].", ".$user_value["user_first_name"]."; ";
					
				$link['alt'] .= "\n".$user_value["user_last_name"].", ".$user_value["user_first_name"]." (".trim($user_value["user_units"])."%)";
			}
			
			if($mapTasks)
			{
				$resources = substr($resources, 0, (strlen($resources)-2));
				$resources .= " ]";
			}
		}
		
		$link['text'] = '<span style="background-color:#'.$row['color'].'; color='.bestColor($row['color']).';">'.$row['task_name'].($resources ? $resources : "").'</span><br>';

		// convert duration to days
		if ($durnType < 24.0 )
		{
			if ($durn > $AppUI->cfg['daily_working_hours'])
			{
				$durn /= $AppUI->cfg['daily_working_hours'];
			}
			else
			{
				$durn = 0.0;
			}
		}
		else
		{
			$durn *= ($durnType / 24.0);
		}

		// fill in between start and finish based on duration
		if ($durn > 1)
		{
			$temp = $start;
			
			while ($start->format( FMT_TIMESTAMP_DATE ) <= $temp->format( FMT_TIMESTAMP_DATE ) && $end->format( FMT_TIMESTAMP_DATE ) >= $temp->format( FMT_TIMESTAMP_DATE ))
			{
				if($start->format( FMT_TIMESTAMP_DATE ) <= $temp->format( FMT_TIMESTAMP_DATE ) 
					&& $end->format( FMT_TIMESTAMP_DATE ) >= $temp->format( FMT_TIMESTAMP_DATE )
						&& $temp->isWorkingDay())
				{
					$tempLink = $link;
					$tempLink['alt'] = $start->format( "%d/%m/%Y %H:%M" )." - ".$end->format( "%d/%m/%Y %H:%M" )."\n".$link['alt'];
					$links[$temp->format( FMT_TIMESTAMP_DATE )][] = $tempLink;
				}

				if($endPeriod->format( FMT_TIMESTAMP_DATE ) >= $temp->format( FMT_TIMESTAMP_DATE ))
					$temp->addSeconds( $sid );
				else
					break;
			}
		}
		else
		{
			$tempLink = $link;
			$tempLink['alt'] = $start->format( "%d/%m/%Y %H:%M" )." - ".$end->format( "%d/%m/%Y %H:%M" )."\n".$link['alt'];
			$links[$start->format( FMT_TIMESTAMP_DATE )][] = $tempLink;
		}
	}
}

?>
