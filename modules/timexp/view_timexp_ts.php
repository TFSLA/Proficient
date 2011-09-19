<?php
global  $task_id, $bug_id, $obj, $percent, $timexp_id, $rnd_type, $timexp_status_color,$btfilter;

$AppUI->savePlace(); 

//to do completar obteniendo permisos
$canEdit = true;
$accessLog = PERM_EDIT;

$timexp_id = intval( dPgetParam( $_GET, 'timexp_id', 0 ) );



$timexp = new CTimExp_TS();
if (!$timexp->load($timexp_id, false)){
	$AppUI->setMsg( 'Timexp_TS' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

$timesheet = new CTimesheet();

if (!$timesheet->load($timexp->timexp_ts_timesheet, false)){
	$AppUI->setMsg( 'Timesheet' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

$spvMode = $timesheet->canSupervise();
$spvMode_directReport = $timesheet->canSupervise_directReport($timexp_id);


// si no es supervisor, lo puede ver?
if (!$spvMode && !$spvMode_directReport)
	if(!$timexp->canRead()){
		//echo "<!-- cant read -->";
		$AppUI->redirect( "m=public&a=access_denied" );
	}

//si el historial no fue cargado por el usuario y adem? el user no es SYSADMIN
$canEdit = false;
				

$rnd_type = $timexp->timexp_ts_type;



if ($rnd_type=="1"){
	$label_value = "Hours";
	$sufix = "time";
	
	$start_time = intval( $timexp->timexp_ts_start_date ) ? new CDate( $timexp->timexp_ts_start_date ) : "";
	$end_time = intval( $timexp->timexp_ts_end_date ) ? new CDate( $timexp->timexp_ts_end_date ) : "";
	
	//Time arrays for selects
	$start = intval( substr($AppUI->getConfig('cal_day_start'), 0, 2 ) );
	$end   = intval( substr($AppUI->getConfig('cal_day_end'), 0, 2 ) );
	$inc   = $AppUI->getConfig('cal_day_increment');
	if ($start === null ) $start = 8;
	if ($end   === null ) $end = 17;
	if ($inc   === null)  $inc = 15;
	$hours = array();
	$hours["NULL"]="";
	for ( $hour = $start; $hour < $end + 1; $hour++ ) {
		for ( $min = 0 ; $min < 60; $min += $inc ) {
			$current_key = sprintf("%02d:%02d",$hour,$min);
			if (stristr($AppUI->getPref('TIMEFORMAT'), "%p") ){
				$hours[$current_key] = sprintf("%02d:%02d", $hour % 12 ,$min);
				$hours[$current_key] .= " ".(floor($hour / 12) == 0 ? "am" : "pm");
			}else{
				$hours[$current_key] = sprintf("%02d:%02d", $hour ,$min);
			}
		}	
		
	}
	

}else{
	$label_value = "Cost";
	$sufix = "expense";
}

$titleaction = $AppUI->_("View ". $timexp_types[$rnd_type]. " Log");


//obtengo la lista de tareas disponible para cargar horas
$user =  $user_id ? $user_id : $AppUI->user_id;
$current_tasks = CTask::getTasksList('allunfinished', $user);

$tmpprj = new CProject();
$projects = $tmpprj->getAllowedRecords( $user );
unset($tmpprj);
for ($i=0; $i<count($current_tasks); $i++){
	
	$prm = CTask::getTaskAccesses($current_tasks[$i]["task_id"], $user);
	
	if 		($prm["log"]==PERM_EDIT && $rnd_type=="1")
		$app_tasks[$current_tasks[$i]["task_id"]] = $current_tasks[$i]; 
	elseif 	($prm["expense"]==PERM_EDIT && $rnd_type=="2")
		$app_tasks[$current_tasks[$i]["task_id"]] = $current_tasks[$i]; 

}


$start_times = substr( $timexp->timexp_ts_start_time,11,5 );
$end_times = substr( $timexp->timexp_ts_end_time,11,5 );

// Task Update Form
	$df = $AppUI->getPref( 'SHDATEFORMAT' );
	$timexp_date = new CDate( $timexp->timexp_ts_date );
	


// setup the title block
$titleBlock = new CTitleBlock( $titleaction, 'timexp.gif', $m, "$m.$a" );
$titleBlock->addCell();
$titleBlock->addCrumb("?m=timexp&a=viewsheet&timesheet_id=".$timexp->timexp_ts_timesheet, "view sheet");

//$titleBlock->addCrumb("?m=timexp&a=view&timexp_id=".$timexp->timexp_ts_timexp, "view original ".strtolower($timexp_types[$rnd_type]));
$titleBlock->show();

?>
<table cellspacing="1" cellpadding="0" border="0" width="100%" class="std">
<tr>
	<td width="50%" valign="top">

		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('Date');?></td>
			<td nowrap="nowrap" class="hilite" width="30%"><?php echo $timexp_date->format( $df );?></td>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_($label_value);?></td>
			<td class="hilite" width="30%"><?php echo $timexp->timexp_ts_value;?></td>		
		</tr>
<? if ($rnd_type=="1"){?>		
		<tr> 
			<td align="right" style="font-weight: bold;" nowrap="nowrap"><?php echo $AppUI->_('Start Time');?></td>
			<td  class="hilite"><? echo $start_times; ?></td>
			<td align="right" style="font-weight: bold;" nowrap="nowrap"><?php echo $AppUI->_('End Time');?></td>
			<td  class="hilite"><? echo $end_times; ?></td>
		</tr>
<? } ?>			
		<tr>
		
			<td align="right" style="font-weight: bold;" valign="top"><?php echo $AppUI->_('Applied to');?>	</td>
			<td class="hilite" colspan="3">[<?php echo $AppUI->_($timexp_applied_to_types[$timexp->timexp_ts_applied_to_type]);?>]<br>
		<?
		switch ($timexp->timexp_ts_applied_to_type){
		case "1":
				$objTask = new CTask();
				if (!$objTask->load($timexp->timexp_ts_applied_to_id, false)){
					$AppUI->setMsg( 'Task' );
					$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
					$AppUI->redirect();
				}		
				$objPrj = new CProject();
				if (!$objPrj->load($objTask->task_project, false)){
					$AppUI->setMsg( 'Project' );
					$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
					$AppUI->redirect();
				}		
				echo $AppUI->_("Project").": ";
				echo "<a href=\"index.php?m=projects&a=view&project_id=$objPrj->project_id\">";
				echo $objPrj->project_name."</a> <br>";
				echo $AppUI->_("Task").": ";
				echo "<a href=\"index.php?m=tasks&a=view&task_id=$objTask->task_id\">";
				echo $objTask->task_name."</a> </br>";
				if ($rnd_type=="1"){
					echo $AppUI->_('Contribute to task completion').": ".$AppUI->_($billables[$timexp->timexp_ts_contribute_task_completion]);
				}
				break;
				
		case "2":
				require_once( "./modules/webtracking/core.php" );
				$t_core_path = config_get( 'core_path' );
				require_once( $t_core_path.'compress_api.php' );
				require_once( $t_core_path.'filter_api.php' );
				require_once( $t_core_dir . 'current_user_api.php' );

				$t_bug = bug_prepare_display( bug_get( $timexp->timexp_ts_applied_to_id, true ) );
				$bid=bug_format_id( $timexp->timexp_ts_applied_to_id );
				$bsummary=$t_bug->summary;
				$pid=$t_bug->project_id;

				$objPrj = new CProject();
				if (!$objPrj->load($pid, false)){
					$AppUI->setMsg( 'Project' );
					$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
					$AppUI->redirect();
				}		
				echo $AppUI->_("Project").": ";
				echo "<a href=\"index.php?m=projects&a=view&project_id=$objPrj->project_id\">";
				echo $objPrj->project_name."</a> <br>";
				echo $AppUI->_("Bug").": ";
				echo "<a href=\"index.php?m=webtracking&a=bug_view_page&bug_id=$bid\">";
				echo $bid." - ". $bsummary."</a> <br>";
				break;		
		
		}
		?>
			</td>		
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?=$AppUI->_('Billable').": ";?>	</td>
			<td class="hilite" width="30%"><?=$AppUI->_($billables[$timexp->timexp_ts_billable]); ?></td>		
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('Status');?>:</td>
			<?php
			$bgcolor = "style=\"background-color: ".$timexp_status_color[$timexp->timexp_ts_last_status].";\"";
			?>
			<td class="hilite" <?php echo $bgcolor;?> width="30%"><?php echo $AppUI->_($timexp_status[$timexp->timexp_ts_last_status]);?></td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">

		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>	
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('User');?>:</td>
			<td class="hilite" colspan="3"><?php 
			$usr = new CUser();
			$usr->load ($timexp->timexp_ts_creator);
			$user_full_name = $usr->user_first_name. " " . $usr->user_last_name;
			unset($usr);
			echo $user_full_name;?></td>
		</tr>
		<tr>	
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('Name');?>:</td>
			<td class="hilite" colspan="3"><?php echo $timexp->timexp_ts_name;?></td>
		</tr>
		<tr>
			<td align="right" valign="top" style="font-weight: bold;"><?=$AppUI->_('Description');?>:</br></br></br></br></br></td>
			<td valign="top" class="hilite" colspan="3"><?php echo $timexp->timexp_ts_description;?></td>
		</tr>

		</table>
	</td>
	
</tr>
</table>

</br></br>