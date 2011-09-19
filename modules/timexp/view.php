<?php
global  $task_id, $bug_id, $obj, $percent, $timexp_id, $rnd_type, $timexp_status_color,$btfilter, $name_sheets;

$AppUI->savePlace(); 

//to do completar obteniendo permisos
$canEdit = true;
$accessLog = PERM_EDIT;
$lang = $AppUI->user_locale;

$timexp_id = intval( dPgetParam( $_GET, 'timexp_id', 0 ) );


$timexp = new CTimExp();
if (!$timexp->load($timexp_id, false)){
	$AppUI->setMsg( 'Timexp' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$spvMode = $timexp->canSupervise();
//$spvMode_directReport = $timesheet->canSupervise_directReport($timexp_id);

// se puede leer??
//if (!$spvMode && !$spvMode_directReport)
if (!$timexp->canRead()){
	echo "<pre> cant read </pre>";
	//$AppUI->redirect( "m=public&a=access_denied" );
}

$sql = "SELECT name_$lang FROM timexp_expenses_categories WHERE category_id = $timexp->timexp_expense_category";
$Category = db_loadResult($sql);

//si el historial no fue cargado por el usuario y además el user no es SYSADMIN
$canEdit = $timexp->canEdit($msg);
$canDelete = $timexp->canDelete($msgDelete);
			
$titleaction = $AppUI->_("View ");		

$rnd_type = $timexp->timexp_type;

if ($rnd_type=="1"){
	$label_value = "Hours";
	$sufix = "time";
	
	$start_time = intval( $timexp->timexp_start_time ) ? new CDate( $timexp->timexp_start_time ) : "";
	$end_time = intval( $timexp->timexp_end_time ) ? new CDate( $timexp->timexp_end_time ) : "";
	
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

$titleobject = $AppUI->_($timexp_types[$rnd_type]);

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




// Task Update Form
	$df = $AppUI->getPref( 'SHDATEFORMAT' );
	$timexp_date = new CDate( $timexp->timexp_date );
/*
$addtime = '<input type="button" class="button" value="'.$AppUI->_('new time').'"';
$addtime .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=addedittime&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=750, height=520, scrollbars=false' );\"";
$addtime .= '" />';

$addexpense = '<input type="button" class="button" value="'.$AppUI->_('new expense').'"';
$addexpense .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=addeditexpense&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=750, height=350, scrollbars=false' );\"";
$addexpense .= '" />';
*/

$addtime = '<table height="1">
    <tr>
    	<td>
    	<form action="index.php?m=timexp&a=addtime" method="POST"><td>
    	<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new time').'"';
//$addtime .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=addtime&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );\"";
$addtime .= ' /></td></form>
    	<form action="index.php?m=timexp&a=addeditexpense" method="POST"><td>';
$addexpense = '<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new expense').'"';
$addexpense .= ' /></td></form><td>
		<form action="index.php?m=timexp&a=new_license" method="POST"><td>';

$addlicense .= '<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new license').'"';
//$addlicense .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=new_license&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );\"";
$addlicense .= ' /></td></form></td></tr></table>';

// setup the title block
$titleBlock = new CTitleBlock( $titleaction." ".$titleobject, 'timexp.gif', $m, "$m.$a" );

$titleBlock->addCell();
$titleBlock->addCell(
		$addtime."&nbsp;".$addexpense."&nbsp;".$addlicense, '',
		'', ''
);

//$titleBlock->addCrumb("?m=timexp&a=vw_myweek&week_date_".$sufix."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "my weekly view");
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday&sel_date_".$sufix."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "my daily view" );
if ($spvMode){
	//$titleBlock->addCrumb("?m=timexp&a=vw_sup_week&week_date_".$sufix."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "weekly supervision");
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day&sel_date_".$sufix."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "daily supervision");
	$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");	
}

if ($canEdit){
	if($rnd_type == 2)
		$titleBlock->addCrumb( "?m=timexp&a=addedit".$sufix."&timexp_id=$timexp_id", "edit ".strtolower($timexp_types[$rnd_type]) );	
	else
		$titleBlock->addCrumb( "?m=timexp&a=edi".$sufix."&timexp_id=$timexp_id", "edit ".strtolower($timexp_types[$rnd_type]) );	
}
if ($canDelete){
	$titleBlock->addCrumbDelete( 'delete '.$sufix, $canEdit, $msg );
}

$titleBlock->show();

?>
<script language="javascript">
function delIt() {
	if (confirm( "<?=	$AppUI->_('doDeleteAdvice');?>" )) {
		document.frmDelete.submit();
	}
}
</script>


<table cellspacing="1" cellpadding="0" border="0" width="100%" class="std">
<form name="frmDelete" action="" method="post">
	<input type="hidden" name="dosql" value="do_timexp_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="timexp_id" value="<?php echo $timexp_id;?>" />
</form>
<tr>
	<td width="50%" valign="top">

		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('Date');?></td>
			<td nowrap="nowrap" class="hilite" width="30%"><?php echo $timexp_date->format( $df );?></td>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_($label_value);?></td>
			<td class="hilite" width="30%"><?php echo $timexp->timexp_value;?></td>		
		</tr>
<? if ($rnd_type=="1"){?>		
		<tr>
			<td align="right" style="font-weight: bold;" nowrap="nowrap"><?php echo $AppUI->_('Start Time');?></td>
			<td  class="hilite"><?=($start_time ? $start_time->getHour().":". $start_time->getMinute() : "");?></td>
			<td align="right" style="font-weight: bold;" nowrap="nowrap"><?php echo $AppUI->_('End Time');?></td>
			<td  class="hilite"><?=($end_time ? $end_time->getHour().":". $end_time->getMinute() : "");?></td>
		</tr>
<? } ?>			
		<tr>
		
			<td align="right" style="font-weight: bold;" valign="top"><?php echo $AppUI->_('Applied to');?>	</td>
			<td class="hilite" colspan="3">[<?php echo $AppUI->_($timexp_applied_to_types[$timexp->timexp_applied_to_type]);?>]<br>
		<?
		switch ($timexp->timexp_applied_to_type){
		case "1":
				$objTask = new CTask();
				if (!$objTask->load($timexp->timexp_applied_to_id, false)){
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
					echo $AppUI->_('Contribute to task completion').": ".$AppUI->_($billables[$timexp->timexp_contribute_task_completion]);
				}
				break;
				
		case "2":
				require_once( "./modules/webtracking/core.php" );
				$t_core_path = config_get( 'core_path' );
				require_once( $t_core_path.'compress_api.php' );
				require_once( $t_core_path.'filter_api.php' );
				require_once( $t_core_dir . 'current_user_api.php' );

				$t_bug = bug_prepare_display( bug_get( $timexp->timexp_applied_to_id, true ) );
				$bid=bug_format_id( $timexp->timexp_applied_to_id );
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
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('Status');?>:</td>
			<?php
			$bgcolor = "style=\"background-color: ".$timexp_status_color[$timexp->timexp_last_status].";\"";
			?>
			<td class="hilite" <?php echo $bgcolor;?> width="30%"><?php echo $AppUI->_($timexp_status[$timexp->timexp_last_status]);?></td>
			<td align="right" style="font-weight: bold;" width="20%"><?=$AppUI->_('Billable').": ";?>	</td>
			<td class="hilite" width="30%"><?=$AppUI->_($billables[$timexp->timexp_billable]); ?></td>					
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;"><?=$AppUI->_("Available").": ";?>	</td>
			<td class="hilite"><?php 
				echo $AppUI->_($timexp->isAvailable() ? "Yes" : "No"); ?></td>		
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;"><?=$AppUI->_("Category").": ";?>	</td>
			<td class="hilite"><?php echo $Category ?></td>		
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">

		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>	
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('User');?>:</td>
			<td class="hilite" colspan="3"><?php 
			$usr = new CUser();
			$usr->load ($timexp->timexp_creator);
			$user_full_name = $usr->user_first_name. " " . $usr->user_last_name;
			unset($usr);
			echo $user_full_name;?></td>
		</tr>
		<tr>	
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_('Name');?>:</td>
			<td class="hilite" colspan="3"><?php echo $timexp->timexp_name;?></td>
		</tr>
		<tr>
			<td align="right" valign="top" style="font-weight: bold;"><?=$AppUI->_('Description');?>:</br></br></br></br></br></td>
			<td valign="top" class="hilite" colspan="3"><?php echo $timexp->timexp_description;?></td>
		</tr>

		</table>
	</td>
	
</tr>
</table>

<?php 


// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpVwTab' ) !== NULL ? $AppUI->getState( 'TxpVwTab' ) : 0;



if ($spvMode){
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=$m&a=$a&timexp_id=$timexp_id", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );
	//$tabBox->add( 'vw_log_status', 'Log Status' );
	$tabBox->add( 'vw_timexp_log', 'Log' );
	$tabBox->show();
}


?>
</br></br>