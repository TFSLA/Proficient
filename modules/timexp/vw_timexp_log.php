<?php	
global $timexp_id, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $qty_units, $timexp_applied_to_types, $iconsYN;

$timexp_id = intval( dPgetParam( $_GET, 'timexp_id', 0 ) );

$timexp = new CTimExp();
if (!$timexp->load($timexp_id, false)){
	$AppUI->setMsg( 'Timesheet' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

$spvMode = $timexp->canSupervise();

if (!$spvMode){
	$AppUI->redirect( "m=public&a=access_denied" );
}

$ts_type = $timexp->timexp_type;


$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');
?>
<script language="JavaScript"><!--
function hideSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp){
		document.getElementById("exp"+spanname).src = "images/icons/expand.gif";
		document.getElementById("exp"+spanname).title = "<?php echo $AppUI->_("Show Details");?>";
		sp.style.display="none";
	}
}
function showSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp){
		document.getElementById("exp"+spanname).src = "images/icons/collapse.gif";
		document.getElementById("exp"+spanname).title = "<?php echo $AppUI->_("Hide Details");?>";
		sp.style.display="";
	}
}

function onoffSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp.style.display=="none"){
		showSpan(spanname);
	}else{
		hideSpan(spanname)
	}
}

function annul() {
	var desc="";
	if (confirm( "<?=	$AppUI->_('doAnnulAdvice');?>" )) {
		if (desc=window.prompt("<?=	$AppUI->_('doAnnulDescription');?>")){
			document.frmAnnul.description.value = desc;
			document.frmAnnul.submit();
		}
	}
}
//--></script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
    <tr class="tableHeaderGral">
	  <th width="160"  nowrap="nowrap"><?php echo $AppUI->_("Sheet Date");?></th>
	  <th width="79"  nowrap="nowrap"><?php echo $AppUI->_("Date");?></th>
      <th width="80%"  nowrap="nowrap"><?php echo $AppUI->_("Applied To");?></th>
      <th width="20"  align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Contribute task completion");?>"><?php echo $AppUI->_("CTC");?></th>
			<th width="20"  align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Billable");?>"><?php echo $AppUI->_("B");?></th>
	  <th width="70"  nowrap="nowrap" align="center"><?php echo $AppUI->_($qty_units[$timexp->timexp_type]);?></th>
      </tr>


<?php 

$telist = $timexp->getTimexpLog();
if (count($telist)==0){?>
    <tr>
      <td colspan="10"><?php echo $AppUI->_("No data available");?></td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>
<?php
}

for($i=0; $i<count($telist);$i++){
	$row=$telist[$i];

	$canEdit = false;
	$texp = new CTimExp();
	$texp->load($row["timexp_ts_id"]);
	$msg="";
	$canEdit = $texp->canEdit($msg);
	$tedate = new CDate($row["timexp_ts_date"]);
	$tsdate = new CDate($row["timesheet_date"]);
	$canDelete = false;

	$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["timesheet_last_status"]].";\"";
	
	switch ($row["timexp_ts_applied_to_type"]){
	case "1":
		$perms=CTask::getTaskAccesses($row["task_id"]);
		if ($perms["read"]) {
			$app_to_desc ="[<a href=\"?m=tasks&a=view&task_id={$row['task_id']}\" title=\"".$AppUI->_('view task')."\">";
			$app_to_desc .= $AppUI->_($timexp_applied_to_types[$row["timexp_ts_applied_to_type"]]);
			$app_to_desc .= "</a>] ";
		}else{
			$app_to_desc = "[".$AppUI->_($timexp_applied_to_types[$row["timexp_ts_applied_to_type"]])."] ";
		}	
		$app_to_desc .="<a href=\"?m=timexp&a=view_timexp_ts&timexp_id={$row['timexp_ts_id']}\" title=\"".$AppUI->_("view ".strtolower($timexp_types[$row["timexp_ts_type"]])." log")."\">";	
		$app_to_desc .="{$row['task_id']} - {$row['task_name']}</a>";				
		break;
		

	case "2":
		$app_to_desc = "[".$AppUI->_($timexp_applied_to_types[$row["timexp_ts_applied_to_type"]])."] ";	
		$app_to_desc .="<a href=\"?m=timexp&a=view_timexp_ts&timexp_id={$row['timexp_ts_id']}\" title=\"".$AppUI->_("View ".$timexp_types[$row["timexp_type"]])."\">";	
		$app_to_desc .=  "{$row['id']} - {$row['summary']}</a>";
		break;
		
		
	case "3":
		$app_to_desc ="<a href=\"?m=timexp&a=view_timexp_ts&timexp_id={$row['timexp_ts_id']}\" title=\"".$AppUI->_("View ".$timexp_types[$row["timexp_type"]])."\">";	
		$app_to_desc .= "[".$AppUI->_($timexp_applied_to_types[$row["timexp_ts_applied_to_type"]])."] ";
		$app_to_desc .=  "</a>";
		break;
	}
      
	$start_times = substr( $row[task_start_date],11,5 );
    $end_times = substr( $row[task_end_date],11,5 );

 
?>
    <tr valign="top" <?php echo $bgcolor?>>
    	<td align="center" nowrap="nowrap"><a href="?m=timexp&a=viewsheet&timesheet_id=<?php echo $row["timexp_ts_timesheet"];?>" title="<?php echo $AppUI->_("view sheet")."\">".$tsdate->format($df)." ".$tsdate->format($tf)."</a>";?></td>
			<td align="center"><?php echo $tedate->format($df);?></td>
			<td align="left"><?php if ($row["timexp_ts_applied_to_type"]==1){?>
			<a href="#" onclick="onoffSpan('tl<?php echo $row["timexp_ts_id"];?>');" title="<?php echo $AppUI->_("Show Details");?>"><img id="exptl<?php echo $row["timexp_ts_id"];?>" src="./images/icons/expand.gif" alt="<?php echo $AppUI->_("Show Details");?>" border="0"  /></a>
			<?php
			}
			 echo $app_to_desc;
			 if ($row["timexp_ts_applied_to_type"]==1){?>
					<span id="tl<?php echo $row["timexp_ts_id"];?>" style="display: none;">
						<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
						<tr><td width="50"></td>
							<td>
								<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">
								<tr>
									<th colspan="4" class="tableHeaderGral" align="center"><?php echo $AppUI->_("Details");?></th>
									<th class="tableHeaderGral"><a href="#" onclick="hideSpan('tl<?php echo $row["timexp_ts_id"];?>');"><img src="./images/icons/trash_small2.gif" alt="<?php echo $AppUI->_("Hide Details");?>" border="0"  /></a></th>
								</tr>
								<tr>
									<th class="tableHeaderGral" align="right"><?php echo $AppUI->_("Name");?></th>
									<td  colspan="3"><?php echo $row["timexp_ts_name"];?></td>
								</tr>
								<tr>
									<th class="tableHeaderGral" align="right"><?php echo $AppUI->_("Description");?></th>
									<td colspan="3"><?php echo $row["timexp_ts_description"];?></td>
								</tr>
								<?php if ($row["timexp_ts_type"]==1){ ?>
								<tr>
									<th width="25%" class="tableHeaderGral" align="right"><?php echo $AppUI->_("Start Time");?></th>
									<td width="25%" ><? echo $start_times; ?></td>
									<th width="25%" class="tableHeaderGral" align="right"><?php echo $AppUI->_("Finish Time");?></th>
									<td width="25%"><? echo $end_times; ?></td>
								</tr>
								<?php } ?>
								</table>
							</td>
						</tr>
						</table>
					</span>
			<?php } ?>
			</td>
      <td align="center"><?php echo $iconsYN[$row["timexp_ts_contribute_task_completion"]];?></td>
      <td align='center'><?php echo $iconsYN[$row["timexp_ts_billable"]];?></td>
			<td align='center'><?php echo number_format($row["timexp_ts_value"], 2);?></td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
<?php 

}

?>	


  </table>
  </td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
</table>
