<?php	
global $timesheet_id, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $qty_units, $timexp_applied_to_types, $iconsYN, $ts_status_transition;

$timesheet_id = intval( dPgetParam( $_GET, 'timesheet_id', 0 ) );

$timesheet = new CTimesheet();
if (!$timesheet->load($timesheet_id, false)){
	$AppUI->setMsg( 'Timesheet' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

$spvMode = $timesheet->canSupervise();

$ts_type = $timesheet->timesheet_type;

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref( 'TIMEFORMAT' );
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
	<form action="" method="POST" name="edit<?php echo $timexp_types[$timexp_type];?>Sheets" >
	<input type="hidden" name="timesheetstatus_user" value="<?php echo $AppUI->user_id;?>" />
	<input type="hidden" name="dosql" value="do_timesheetstatus_aed" />	
    <tr class="tableHeaderGral">
	  	<th width="79" align="center" nowrap="nowrap"><?php echo $AppUI->_("Date");?></th>
      <th width="200"  nowrap="nowrap"><?php echo $AppUI->_("User");?></th>
      <th width="20" align="left" nowrap="nowrap"><?php echo $AppUI->_("Status");?></th>
			<th width="80%"  nowrap="nowrap" ><?php echo $AppUI->_("Comments");?></th>
     </tr>


<?php 

$stlist = $timesheet->getListTimesheetStatus();
if (count($stlist)==0){?>
    <tr>
      <td colspan="10"><?php echo $AppUI->_("No data available");?></td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>
<?php
}

for($i=0; $i<count($stlist);$i++){
	$row=$stlist[$i];
	
	$tsdate = new CDate($row["timesheetstatus_date"]);
	$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["timesheetstatus_status"]].";\"";

?>
    <tr valign="top" <?php echo $bgcolor;?>>
			<td align="center" nowrap="nowrap"><?php echo $tsdate->format($df)."&nbsp;".$tsdate->format($tf);?>&nbsp;&nbsp;</td>
			<td align="left" nowrap="nowrap"><?php echo $row["user_name"];?></td>
			<td align="left" nowrap="nowrap"><?php echo $AppUI->_($timexp_status[$row["timesheetstatus_status"]]);?>&nbsp;&nbsp;</td>
			<td align="left"><?php echo $row["timesheetstatus_description"];?></td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
<?php 

}

	$tsdate = new CDate();
	$user_full_name = $AppUI->user_last_name.", ". $AppUI->user_first_name;
	if ($ts_status_transition[$timesheet->timesheet_last_status]!=""){
		$ns_list = explode(",",$ts_status_transition[$timesheet->timesheet_last_status]);
		//echo "<pre>";var_dump($sql); echo "</pre>";
		$next_status = array();
		for($i=0; $i<count($ns_list); $i++){
			if($timesheet->canChangeStatus($ns_list[$i])){
				$next_status[$ns_list[$i]] = $timexp_status[$ns_list[$i]];
			}
		}
		//echo "<pre>";var_dump($ns_list); echo "</pre>";
		if (count($next_status)){
?>	
    <tr valign="top">
			<td align="center" nowrap="nowrap"><?php echo $tsdate->format($df)."&nbsp;".$tsdate->format($tf);?>&nbsp;&nbsp;</td>
			<td align="left" nowrap="nowrap"><?php echo $user_full_name;?></td>
			<td align="left" nowrap="nowrap">
			<?php echo arraySelect($next_status, "timesheetstatus_status[".$timesheet->timesheet_id."]", 'size="1" class="text"', NULL, true );?></td>
			<td align="left" valign="top">
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
				<tr>
					<td>
						<textarea name="<?php echo "timesheetstatus_description[".$timesheet->timesheet_id."]";?>" class="text" cols="50" rows="2" ></textarea></td>
					<td>
						&nbsp;<input type="submit" class="button" value="<?php echo $AppUI->_('update');?>" />
					</td>
				</tr>
				</table>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
<?php }} ?>    
	</form>
  </table>
  </td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
</table>
