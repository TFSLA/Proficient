<?php
// check permissions
//if ($AppUI->user_type!=1) {
if (getDenyRead($m)) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

error_reporting( E_ALL & ~E_NOTICE);
$do_report 		    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
$p_user_id 			= dPgetParam($_POST, 'user', null);
$p_show_failed 			= dPgetParam($_POST, 'show_failed', 0);


$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : false;
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : false;
/*
if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}*/
if (intval( $log_end_date )) {
	$end_date->setTime( 23, 59, 59 );
}


//Esto habria que ponerlo en un inc como se hace con los projects
//Es para llenar el combo de los usuarios
$sql = "
SELECT DISTINCT(user_id), concat(user_last_name, ', ', user_first_name) fullname
FROM users
WHERE user_type <> 5 
order by fullname
";
$users = db_loadHashList( $sql );

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
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('User');?>:&nbsp;&nbsp;
<? echo arraySelect($users, "user", 'size="1" class="text"', $p_user_id ); ?>
	</td>
	<td align="left"  nowrap="nowrap">
		<input class="button" type="checkbox" name="show_failed" value="1" <?php echo ($p_show_failed ? "checked":"") ?> /><?php echo $AppUI->_('Show failed logins'); ?>
	</td>
	<td align="right" width="50%" nowrap="nowrap" rowspan="2">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
<tr>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('From');?>:
		<input type="hidden" name="log_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ):"";?>" />
		<input type="text" name="start_date" value="<?php echo $start_date?$start_date->format( $df ):"";?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('To');?>
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
</tr>

</table>
</form>

<?php
if($do_report){	
	
	$events = dPgetSysVal("UserEvents");
	
	$sql = "
		SELECT user_log_id
		, user_log_user
		, user_log_date
		, user_log_ip
	 	, user_log_last_use
		, user_log_logout
		, user_log_event
		,unix_timestamp(if(user_log_logout>0, user_log_logout,user_log_last_use) ) 
			- unix_timestamp(user_log_date ) usetime
		from user_logs 
		where user_log_user = '$p_user_id'
	";
	
	if ($start_date){
		$sql .= "and user_log_date >= '".$start_date->format(FMT_TIMESTAMP_DATE)." 000000'\n";
	}
	if ($end_date){
		$sql .= "and user_log_date <= '".$end_date->format(FMT_TIMESTAMP_DATE)." 235959'\n";
	}
	if ($p_show_failed){
		$sql .= "and user_log_event in (1,2,3)\n";
	}else{
		$sql .= "and user_log_event in (1,2)\n";
	}
	$sql .= "ORDER BY user_log_date";
	
	$log_list = db_loadList($sql);
	//echo "<pre> $sql<br>";var_dump($log_list);echo "</pre>";
?>

<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th colspan='10'><?php echo $AppUI->_('User').": ".$users[$p_user_id] ;?></th>
	</tr>
	<tr>
		<th width="100px" align="left"><?php echo $AppUI->_('Event'); ?></th>
		<th><?php echo $AppUI->_('Login'); ?></th>
		<th><?php echo $AppUI->_('IP'); ?></th>
		<th><?php echo $AppUI->_('Last use'); ?></th>
		<th><?php echo $AppUI->_('Logout'); ?></th>
		<th><?php echo $AppUI->_('Use time'); ?></th>
	</tr>
<?php 
	
	for($i=0; $i<count($log_list); $i++){ 		
		$date = new CDate($log_list[$i]["user_log_date"]);
		$last_use = new CDate($log_list[$i]["user_log_last_use"]);
		$logout = $log_list[$i]["user_log_logout"] > 0 ?  new CDate($log_list[$i]["user_log_logout"]) : NULL;
		
		
		$usetime= $log_list[$i]["usetime"];
	  $d_d = floor($usetime/(60*60*24));
	  $d_h = floor(($usetime-$d_d*60*60*24)  /(60*60));
	  $d_m = floor(($usetime-$d_d*60*60*24-$d_h*60*60)  /(60));
	  $d_s = floor(($usetime-$d_d*60*60*24-$d_h*60*60-$d_m*60));
		$duracion = ($d_d ? $d_d."d ":"")
								.($d_h ? $d_h."h ":"")
								.($d_m ? $d_m."m ":"")
								.($d_s ? $d_s."s ":"");
		
		$bgcolor = $log_list[$i]["user_log_event"]=="3" ? " style='background-color: #ffdddd;'":"";
		?>
	<tr>
		<td <?php echo $bgcolor?>><?php echo $AppUI->_($events[$log_list[$i]["user_log_event"]]) ?> </td>
		<td <?php echo $bgcolor?>><?php echo $date->format($df)." ".$date->format($tf);?></td>
		<td <?php echo $bgcolor?> align='right'><?php echo $log_list[$i]["user_log_ip"]; ?> </td>
		<td <?php echo $bgcolor?>><?php echo $last_use->format($df)." ".$last_use->format($tf);?></td>
		<td <?php echo $bgcolor?>><?php echo $log_list[$i]["user_log_logout"] > 0 ? $logout->format($df)." ".$logout->format($tf):"";?></td>
		<td <?php echo $bgcolor?>><?php echo 	  $duracion;?></td>
	</tr>


<?php 
		if ($log_list[$i]["user_log_event"]=="2"){
			echo "<tr><td colspan=4></td></tr>";
		}
	
	} ?>
	</table>
	
<?php	
}
?>
