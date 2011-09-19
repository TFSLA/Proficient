<?php	
global $timesheet_id, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $qty_units, $timexp_applied_to_types, $iconsYN;


$ts_type = intval( dPgetParam( $_GET, 'type', 0 ) );
$project = intval( dPgetParam( $_GET, 'project', 0 ) );

$prj = new CProject();

if($project != '0' )
{
	$prj = new CProject();
	if (!$prj->load($project, false)){
		$AppUI->setMsg( 'Project' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
	}
}
else{
   $prj->project_name =  $AppUI->_("Internal");
}


$df = $AppUI->getPref('SHDATEFORMAT');
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

//--></script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td>
	<table width="100%" border="0" cellpadding="1" cellspacing="1" class="">
    <tr class="tableHeaderGral">
	  		<th width="100%" colspan="97" nowrap="nowrap"><?php 
	  		echo $AppUI->_("Project").": ".$prj->project_name." - ".$AppUI->_("Available ".$timexp_types[$ts_type]."s");
	  		?></th>
	  </tr>	
    <tr class="tableHeaderGral">
	  <th width="79"  nowrap="nowrap"><?php echo $AppUI->_("Date");?></th>
      <th width="80%"  nowrap="nowrap"><?php echo $AppUI->_("Applied To");?></th>
     <!--  <th width="20"  align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Contribute task completion");?>"><?php echo $AppUI->_("CTC");?></th> -->
			<th width="20"  align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Billable");?>"><?php echo $AppUI->_("Billable");?></th>
	  <th width="70"  nowrap="nowrap" align="center"><?php echo $AppUI->_($qty_units[$ts_type]);?></th>
      </tr>


<?php 

if($project != '0')
{
$telist = CTimesheet::getUnassignedTimexp($ts_type, $project);
}
else
{
$sql = "
        SELECT * FROM timexp 
		WHERE timexp_creator = '".$AppUI->user_id."'
		AND	  timexp_type = '$ts_type'
		AND	  timexp_applied_to_type in (3)
		AND  (timexp_timesheet is NULL OR timexp_last_status in (2,4) )
		AND	  timexp_last_status in(0, 2, 4)
		ORDER BY timexp_date
		";
//echo $sql;
$telist = db_loadList($sql);
}

if (count($telist)==0){?>
    <tr>
      <td colspan="10"><?php echo $AppUI->_("No data available");?></td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>
<?php
}

$tb = 0;

for($i=0; $i<count($telist);$i++){
	$row=$telist[$i];

	$canEdit = false;
	$texp = new CTimExp();
	$texp->load($row["timexp_id"]);
	$msg="";
	$canEdit = $texp->canEdit($msg);
	$tedate = new CDate($row["timexp_date"]);
	$canDelete = false;

	switch ($row["timexp_applied_to_type"]){
	case "1":
		$perms=CTask::getTaskAccesses($row["task_id"]);
		if ($perms["read"]) {
			$app_to_desc = $AppUI->_($timexp_applied_to_types[$row["timexp_applied_to_type"]]);
		}else{
			$app_to_desc = "[".$AppUI->_($timexp_applied_to_types[$row["timexp_applied_to_type"]])."] ";
		}	
		$app_to_desc .="{$row['task_id']} - {$row['task_name']}";				
		break;
		

	case "2":
		$app_to_desc = "[".$AppUI->_($timexp_applied_to_types[$row["timexp_applied_to_type"]])."] ";		
		$app_to_desc .=  "{$row['id']} - {$row['summary']}";
		break;
		
		
	case "3":	
		$app_to_desc = "[".$AppUI->_($timexp_applied_to_types[$row["timexp_applied_to_type"]])."] - $row[timexp_name]";

		break;

	case "4":
		$app_to_desc = "[".$AppUI->_($timexp_applied_to_types[$row["timexp_applied_to_type"]])."] ";		
		$app_to_desc .=  "{$row['id_todo']} - {$row['description']}";
		break;
	}
   
   $start_times = substr( $row[timexp_start_time],11,5 );
   $end_times = substr( $row[timexp_end_time],11,5 );

?>
    <tr valign="top">
			<td align="center"><?php echo $tedate->format($df);?></td>
			<td align="left"><?php if ($row["timexp_applied_to_type"]==1){?>
			<a href="#" onclick="onoffSpan('tl<?php echo $row["timexp_id"];?>');" title="<?php echo $AppUI->_("Show Details");?>"><img id="exptl<?php echo $row["timexp_id"];?>" src="./images/icons/expand.gif" alt="<?php echo $AppUI->_("Show Details");?>" border="0"  /></a>
			<?php
			}
			 echo $app_to_desc;
			 if ($row["timexp_applied_to_type"]==1){?>
					<span id="tl<?php echo $row["timexp_id"];?>" style="display: none;">
						<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
						<tr><td width="50"></td>
							<td>
								<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">
								<tr>
									<th colspan="4" class="tableHeaderGral" align="center"><?php echo $AppUI->_("Details");?></th>
									<th class="tableHeaderGral"><a href="#" onclick="hideSpan('tl<?php echo $row["timexp_id"];?>');"><img src="./images/icons/trash_small2.gif" alt="<?php echo $AppUI->_("Hide Details");?>" border="0"  /></a></th>
								</tr>
								<tr>
									<th class="tableHeaderGral" align="right"><?php echo $AppUI->_("Name");?></th>
									<td  colspan="3"><?php echo $row["timexp_name"];?></td>
								</tr>
								<tr>
									<th class="tableHeaderGral" align="right"><?php echo $AppUI->_("Description");?></th>
									<td colspan="3"><?php echo $row["timexp_description"];?></td>
								</tr>
								<?php if ($row["timexp_type"]==1){?>
								<tr>
									<th width="25%" class="tableHeaderGral" align="right"><?php echo $AppUI->_("Start Time");?></th>
									<td width="25%" ><? echo $start_times; ?></td>
									<th width="25%" class="tableHeaderGral" align="right"><?php echo $AppUI->_("End Time");?></th>
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
      <!-- <td align="center"><?php echo $iconsYN[$row["timexp_contribute_task_completion"]];?></td> -->
      <td align='center'><?php echo $iconsYN[$row["timexp_billable"]];?></td>
		 <td align='center'>
			<?php 
		    $tb = $tb + $row["timexp_value"];
		    echo number_format($row["timexp_value"], 2);
	        ?>
		 </td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
<?php 

}

?>	
  <tr>
    <td colspan="3" align="right">
	  <b>TOTAL</b>&nbsp;&nbsp;&nbsp;&nbsp;
	</td>
	<td align="center">
	  <?
	  echo number_format($tb, 2);
	  ?>
	</td>
  </tr>
   <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>
  </table>
  </td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
</table>
