<?php
global $timexp_id, $timexp_status, $timexp_status_color, $te_status_transition;

$timexp = new CTimExp();
if (!$timexp->load($timexp_id, false)){
	$AppUI->setMsg( 'Timexp' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

$canSupervise = $timexp->canSupervise();

if (!$canSupervise) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

$status_log = $timexp->getStatusLog();
$status_list = $te_status_transition[$timexp->timexp_last_status];
//echo "<pre>";var_dump($status_list);echo "</pre>";
if ( $status_list != NULL){
	$status_list_tmp = explode (",", $status_list);
	$status_list = array();
	for($i=0; $i < count ($status_list_tmp); $i++){
		$status_list[$status_list_tmp[$i]] = $timexp_status[$status_list_tmp[$i]];
	}
}

?>
<TABLE width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td width="400" valign="top">

	<table width="400" border=0 cellpadding="2" cellspacing="1" class="tbl">
	<tr>
	  <th width="50px" nowrap="nowrap"><b><?php echo $AppUI->_("Date");?></b></th>
	  <th width="50px" nowrap="nowrap"><b><?php echo $AppUI->_("Time");?></b></th>
	  <th width="200px" nowrap="nowrap"><b><?php echo $AppUI->_("User");?></b></th>
	  <th width="100px" nowrap="nowrap"><b><?php echo $AppUI->_("Status");?></b></th>
	</tr>
<?php

if (!count($status_log)){
	?>
	<tr>
	  <td colspan="10"><?php echo $AppUI->_("timexpNoStatusThere is no status for this record.");?></td>
	</tr>
	<?php
}else{
	for($i = 0; $i < count($status_log); $i++){
		$row = $status_log[$i];
		$status_date = new CDate($row["timexp_status_datetime"]);
		$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["timexp_status_value"]].";\"";
	?>
	<tr>
	  <td <?php echo $bgcolor;?> nowrap="nowrap"><?php echo $status_date->format($df);?></td>
	  <td <?php echo $bgcolor;?> nowrap="nowrap"><?php echo $status_date->format($tf);?></td>
	  <td <?php echo $bgcolor;?> nowrap="nowrap"><?php echo $row["user_full_name"];?></td>
	  <td <?php echo $bgcolor;?> nowrap="nowrap"><?php echo $AppUI->_($timexp_status[$row["timexp_status_value"]]);?></td>
	</tr>
	<?php
	}
}
?>
	</table>
	</td>
	<td width="350" valign="top">
<?php if ( $status_list != NULL){ ?>
		<table width="250" border=0 cellpadding="2" cellspacing="1">
		<form name="editFrm" action="" method="POST">
		<input type="hidden" name="timexp_status_id" value="" />
		<input type="hidden" name="timexp_id" value="<?php echo $timexp_id;?>" />
		<input type="hidden" name="timexp_status_user" value="<?php echo $AppUI->user_id;?>" />
		<input type="hidden" name="dosql" value="do_timexp_status_a" />
		<tr>
			<td width="50px"><b><?php echo $AppUI->_("Supervisor");?>:</b></td>
			<td><?php echo $AppUI->user_first_name." ". $AppUI->user_last_name;?></td>
		</tr>
		<tr>
			<td><b><?php echo $AppUI->_("Status");?>:</b></td>
			<td><?php echo arraySelect($status_list, "timexp_status_value", 'size="1" class="text"', NULL, true );?></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" value="<?php echo $AppUI->_('update');?>" class="button" name="sqlaction2">
			</td>
		</tr>
		</table>
<?php }else{ echo "&nbsp;"; } ?>
	</td>
</tr>
</table>
<?php
//echo "<pre>";var_dump($status_list);echo "</pre>";
?>