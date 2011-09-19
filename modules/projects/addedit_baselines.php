<?php /* PROJECTS $Id: addedit_baselines.php,v 1.3 2009-05-22 17:06:26 ctobares Exp $ */
//$debugsql = 1;
$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );
$baseline_id = intval( dPgetParam( $_GET, "baseline_id", 0 ) );

// check permissions for this record
$canEdit = !getDenyEdit( $m, $project_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the record data
$project = new CProject();
if (!$project->load( $project_id, false ) && $project_id > 0) 
{
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($project_id > 0)
{
	$canEdit = $project->canEdit();
}

if (!$canEdit) 
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

$baseline = new CBaseLine();

if ( $baseline_id && !$baseline->load( $baseline_id ) )
{
	$AppUI->setMsg( 'Baseline' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
// format dates
$df = $AppUI->getPref('SHDATEFORMAT');
$durnTypes = dPgetSysVal("TaskDurationType");

// setup the title block
$titleBlock = new CTitleBlock( "Edit project baselines", 'projects.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view project" );
$titleBlock->addCrumb( "?m=projects&a=vw_baselines&project_id=$project_id", "baselines" );

$titleBlock->show();
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $AppUI->cfg['base_url'];?>/lib/calendar/calendar-dp.css" title="blue" />
<!-- import the calendar script -->
<script type="text/javascript" src="<?php echo $AppUI->cfg['base_url'];?>/lib/calendar/calendar.js"></script>
<!-- import the language module -->
<script type="text/javascript" src="<?php echo $AppUI->cfg['base_url'];?>/lib/calendar/lang/calendar-<?php echo $AppUI->user_locale; ?>.js"></script>

<script language="javascript">

var calendarField = '';
var calWin = null;

function popCalendar( field )
{
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=280, height=250, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.' + calendarField );
	fld_fdate = eval( 'document.editFrm.baseline_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function submitIt() 
{
	var f = document.editFrm;
	doSubmit = true;
	
	if ( f.name == "" )
	{
		doSubmit = false;
		alert( "<?=$AppUI->_("Please enter a baseline name")?>" );
	}	
	
	if ( doSubmit )
	{
		f.submit();
	}
}

function toggle( id )
{
	var elem = document.getElementById( "expenses_" + id );
	var sign = document.getElementById( "sign_" + id );
	
	if ( elem.style.display == "none" )
	{
		elem.style.display = "block";
		sign.innerHTML = "<?=$AppUI->_("Hide expenses")?>"
	}
	else
	{
		elem.style.display = "none";
		sign.innerHTML = "<?=$AppUI->_("View expenses")?>";		
	}
}
</script>
<form name="editFrm" action="" method="post">
	<input type="hidden" name="dosql" value="do_baseline_aed" />
	<input type="hidden" name="project_id" value="<?= $project_id ? $project_id : $baseline->project_id ?>" />
	<input type="hidden" name="baseline_id" value="<?php echo $baseline_id;?>" />
	
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<col width="200"><col width="80%">
	<tr>
		<td align="right" nowrap="nowrap" width="200"  style="font-weight: bold;"><?php echo $AppUI->_('Baseline name');?>:</td>
		<td><input type="text" name="name" value="<?php echo $baseline->name;?>" size="10" maxlength="255" class="text"/></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap" width="200"  style="font-weight: bold;"><?php echo $AppUI->_('Baseline date');?>:</td>
		<? $fecha = new CDate( $baseline->date ); ?>
		<td>
			<input type="hidden" name="date" value="<?php echo $fecha->format( FMT_DATETIME_MYSQL );?>"/>
			<?php echo $fecha->format( $df );?>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap" width="200"  style="font-weight: bold;"><?php echo $AppUI->_('Actual Finish Date');?>:</td>
		<td>
		<?
		$actual_end_date = $baseline->project_actual_end_date ? new CDate( $baseline->project_actual_end_date ): new CDate( $project->project_actual_end_date );
		?>	
			<input type="hidden" name="project_actual_end_date" value="<?php echo $actual_end_date->format( FMT_DATETIME_MYSQL );?>" />
			<?php echo $actual_end_date ? $actual_end_date->format( $df ) : '';?>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap" width="200"  style="font-weight: bold;"><?php echo $AppUI->_('Actual Budget');?> <?php echo $dPconfig['currency_symbol'] ?>:</td>
		<? $actual_budget = $baseline->project_actual_budget ? $baseline->project_actual_budget : $project->project_actual_budget; ?>
		<td>
			<input type="hidden" name="actual_budget" value="<?=$actual_budget?>"/>
			<?=$actual_budget?>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap" width="200"  style="font-weight: bold;"><?php echo $AppUI->_('Status');?>:</td>
		<td>
			<? $status = intval( $baseline->project_status ? $baseline->project_status : $project->project_status ) ?>
			<input type="hidden" name="status" value="<?=$status?>"/>
			<?=$AppUI->_($pstatus[$status])?>
		</td>		
	</tr>
	<tr>
		<td align="right" nowrap="nowrap" width="200"  style="font-weight: bold;"><?php echo $AppUI->_('Progress');?>:</td>	
		<td>
			<? $percent_complete = $baseline->project_percent_complete ? $baseline->project_percent_complete : $project->project_percent_complete ?>
			<?php echo intval($percent_complete);?> %	
		</td>
	</tr>
	<tr>
		<th colspan="2" align="center">
			<?=$AppUI->_("Task detail")?>
		</th>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" border="0" cellpadding="2" cellspacing="0" class="" bgcolor="White">
			<col >
			<col width="80px">
			<col width="80px">
			<col width="100px">
			<col width="100px">
			<col width="80px">
			<col width="120px">
				<tr class="tableHeaderGral">
					<td align="center">
						<?=$AppUI->_("Name")?>						
					</td>
					<td align="center">
						<?=$AppUI->_("Work")?>
					</td>					
					<td align="center">
						<?=$AppUI->_("Hours worked")?>
					</td>
					<td align="center">
						<?=$AppUI->_("Start date")?>
					</td><?/*
					<td>
						<?=$AppUI->_("Duration")?>
					</td>*/ ?>
					<td align="center">
						<?=$AppUI->_("End date")?>
					</td>
					<td align="center">
						<?=$AppUI->_("Percent complete")?>
					</td>
					<td nowrap="nowrap">
						&nbsp;
					</td>					
				</tr>			
	<?
	/*if ( $baseline_id )
	{
		//Es un baseline viejo		
		$bltasks = $baseline->getTasks();		
		$task = new CBaselineTask();
		$colId = "id";
		$expenseColId = "timexp_id";
		$expense = new CTimexp();		

	}
	else
	{
		//Es nuevo baseline, hay que mostrar las tareas del proyecto
		$bltasks = $project->getTasks();
		$task = new CTask();
		$colId = "task_id";
		$expenseColId = "timexp_id";
		$expense = new CTimexp();
	}*/
        
      $bltasks = $project->getTasks();
      $task = new CTask();
      $colId = "task_id";
      $expenseColId = "timexp_id";
      $expense = new CTimexp();      
   
	foreach( $bltasks as $task_row )
	{
		$task->load( $task_row[$colId] );
		$start= new CDate( $task->task_start_date );
		$end = new CDate( $task->task_end_date );
		$expensesIds = $task->getExpenses();
		?>			
				<tr>
					<td>
						<?=$task->task_name?>
					</td>
					<td align="right">
					            <?
					                if($task->task_work!=0){
						       $task->task_work = number_format($task->task_work, 3, '.', '');
						       $separado_por_puntos = explode(".", $task->task_work);
						       
						       if (count($separado_por_puntos)>1)
						       {
						       	$decimal1 = substr($separado_por_puntos[1], 0,1);
					       	            $decimal2 = substr($separado_por_puntos[1], 1,1);
					       	            $decimal3 = substr($separado_por_puntos[1], 2,1);
					       	
						       	if($separado_por_puntos[1]=="000"){
						       	    $task->task_work =$separado_por_puntos[0];
						       	}elseif ($decimal2=="0" && $decimal3=="0"){
						       	    $task->task_work = $separado_por_puntos[0].".".$decimal1;
						       	}elseif ($decimal2!="0" && $decimal3=="0"){
						       	    $task->task_work = $separado_por_puntos[0].".".$decimal1.$decimal2;
						       	}
						       }
						    }
					           ?>
						<?=$task->task_work." ".
							substr($AppUI->_($durnTypes[1]),0,1)?>
					</td>
					<td align="right">
					             <?
					                if($task->task_hours_worked!=0){
						       $task->task_hours_worked = number_format($task->task_hours_worked, 3, '.', '');
						       $separado_por_puntos = explode(".", $task->task_hours_worked);
						       
						       if (count($separado_por_puntos)>1)
						       {
						       	if($separado_por_puntos[1]=="000"){
						       	$task->task_hours_worked = $separado_por_puntos[0];
						       	}
						       }
						    }
					            ?>
						<?=$task->task_hours_worked." ".
							substr($AppUI->_($durnTypes[1]),0,1)?>
					</td>
					<td align="center">
						<?=$start->format( $df )?>
					</td><?/*
					<td>
						<?=$task->task_duration?>
					</td>*/?>
					<td align="center">
						<?=$end->format( $df )?>
					</td>
					<td align="right">
						<?=$task->task_manual_percent_complete?>%
					</td>
					<td nowrap="nowrap" align="right">
						<?
						if ( count( $expensesIds )>0  && $expensesIds!== false )
						{
							?>
						<a href="javascript: //" onClick="toggle( '<?=$task_row[$colId]?>' )"><span id="sign_<?=$task_row[$colId]?>"><?=$AppUI->_("View expenses")?></span></a>
							<?
						}
						?>							
					</td>
				</tr>
				<tr>
					<td colspan="7">
						<table class="std" width="100%" id="expenses_<?=$task_row[$colId]?>" style="display:none; " >
							<tr class="tableHeaderGral">
								<td>
									<?=$AppUI->_("Description")?>
								</td>
								<td>
									<?=$AppUI->_("Cost")?>
								</td>								
							</tr>
							<?		
							if 	( count( $expensesIds ) >0  && $expensesIds!== false){	
	
								foreach ( $expensesIds as $expenseId )
								{
									$expense->load( $expenseId[$expenseColId] );
									?>
								<tr>
									<td>
										<?=$expense->timexp_description?>									
									</td>
									<td>
										<?=$expense->timexp_value?>
									</td>
								</tr>
									<?
								}
							}else{?>
							
							<?
							}
							?>							
						</table>
					</td>
				</tr>
                <tr class="tableRowLineCell">
                    <td colspan="7"></td>
                </tr>
				
		<?
	}
	?>			
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:location.href = '?<?php echo $AppUI->getPlace();?>';" />
		</td>
		<td align="right">
			<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt();" />
			<input type="hidden" name="hassign" />
		</td>
	</tr>
</table>
</form>
