<?php
// check permissions

if (getDenyRead( $m )) {
    $AppUI->redirect( "m=public&a=access_denied" );
}
error_reporting( E_ALL & ~E_NOTICE);
$do_report 		    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
$p_user_type 			= dPgetParam($_POST, 'user_type', "0");


$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : false;
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : false;

if (intval( $log_end_date )) {
	$end_date->setTime( 23, 59, 59 );
}


$users = $utypes;
$users["0"]="All";
unset($users[5]);

?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&suppressLogo=1&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="left" nowrap="nowrap" colspan="8"><?php echo $AppUI->_('User type');?>:&nbsp;&nbsp;
<? echo arraySelect($users, "user_type", 'size="1" class="text"', $p_user_type , true); ?>
	</td>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('From');?>:
		<input type="hidden" name="log_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ):"";?>" />
		<input type="text" name="start_date" value="<?php echo $start_date?$start_date->format( $df ):"";?>" class="text" disabled="disabled" size="10" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>&nbsp;
					<script> function clearstart_date(){ var f = document.editFrm; f.log_start_date.value="NULL"; f.start_date.value="";  } </script>
					<a href="javascript:clearstart_date();"><?php echo $AppUI->_('clear');?></a>&nbsp;&nbsp;-&nbsp;&nbsp;
	</td>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('To');?>
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" size="10" />
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>&nbsp;
					<script> function clearend_date(){ var f = document.editFrm; f.log_end_date.value="NULL"; f.end_date.value="";  } </script>
					<a href="javascript:clearend_date();"><?php echo $AppUI->_('clear');?></a>
	</td>
	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>

</table>
</form>

<?php
if($do_report){	

	$user_list = db_loadHashList($sql, "user_id");
	
	$events = dPgetSysVal("UserEvents");
	
	$sql = "
		SELECT user_id
		, concat(user_last_name, ', ', user_first_name) user_fullname
		, user_username
		, sum(  user_log_event=1)qlogs
		, sum(  user_log_event=3 )qflogs
		from user_logs inner join users u on user_log_user = user_id
	";
	if ( $p_user_type != "0" )
	{
		$sql .= " and u.user_type = '$p_user_type'";
	}	
	
	if ($start_date){
		$sql .= "and user_log_date >= '".$start_date->format(FMT_TIMESTAMP_DATE)." 000000'\n";
	}
	if ($end_date){
		$sql .= "and user_log_date <= '".$end_date->format(FMT_TIMESTAMP_DATE)." 235959'\n";
	}

	$sql .= "
					Group By user_id, user_fullname
					ORDER BY user_username";
	
	$log_list = db_loadList($sql);
/*	echo "<pre>$sql<br>";
	var_dump($log_list);
	echo "</pre>";*/
?>

<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th width="120px" align="left"><?php echo ucfirst($AppUI->_('Username')); ?></th>
		<th width="250px" align="left"><?php echo $AppUI->_('Name'); ?></th>
		<th><?php echo $AppUI->_('Login')."s"; ?></th>
		<th><?php echo $AppUI->_('Failed logins'); ?></th>
	
	</tr>
<?php 
	
	for($i=0; $i<count($log_list); $i++){ 		
		$date = new Date($log_list[$i]["user_log_date"]);
		?>
	<tr>
		<td><?php echo $log_list[$i]["user_username"]; ?> </td>
		<td><?php echo $log_list[$i]["user_fullname"]; ?> </td>
		<td><?php echo $log_list[$i]["qlogs"];?></td>
		<td><?php echo $log_list[$i]["qflogs"];?></td>
	</tr>


<?php 

	} ?>
	</table>
	
<?php	
}
?>
