<?php	
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar; 
IF ($_GET['autfilter']=='') $filtrar='on';
$params=array("timesheet_type"=>"$timexp_type");
//aplico los filtros
if ($project != -1)$params["timesheet_project"] = $project;

if ($status != -1) $params["timesheet_last_status"] = $status;
if ($filter =='on') $params["timesheet_last_status"] = "0, 1, 2, 3";

$pager_array = CTimesheet::getMyTimesheets($params,NULL,"myts");
$tslist 		= $pager_array["rows"];
$pager_links	= $pager_array["pager_links"];

$df = $AppUI->getPref('SHDATEFORMAT');

// Si no hay definido un tipo válido
if (!$timexp_type || !isset($timexp_types[$timexp_type])){
		$AppUI->setMsg( "Timesheet" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

// obtengo los proyectos con horas o gastos disponibles para crear timesheet
	$projects = CTimesheet::getMyUnassignedTimexpProjects($timexp_type);


 // En el vector de proyectos creo un proyecto "Internal" para las hs internas, si es que las hay

$sql_internal = "SELECT count(*) as cant, min(timexp_start_time) as start_time, min(timexp_date) as st ,max(timexp_end_time) as end_time, max(timexp_date) as et 
FROM timexp 
WHERE timexp_type ='$timexp_type' and 
timexp_applied_to_type = '3' and 
(timexp_timesheet is null or timexp_last_status in (2,4))
AND timexp_last_status IN (0,2,4) 
AND timexp_creator = '".$AppUI->user_id."'";


//echo "<pre>".$sql_internal."</pre>";
               
$data = db_exec( $sql_internal );
$internal = mysql_fetch_array($data);

if($internal[cant] >'0')
{
$id_internal = count($projects) + 1;
$project_internal["project_id"] = "0";
$project_internal["project_name"]= $AppUI->_("Internal");

if ($timexp_type == '1'){
	$project_internal["first_date"]= $internal[start_time];
	$project_internal["last_date"]= $internal[end_time];
}else{
	$project_internal["first_date"]= $internal[st];
	$project_internal["last_date"]= $internal[et];

}

array_push($projects, $project_internal);

}

?>
<script language="JavaScript"><!--
<?php

echo "var fecini$timexp_type = new Array();\n";
echo "var fecfin$timexp_type = new Array();\n";
echo "var today = '".date("Ymd")."';\n";

for($i=0; $i<count($projects); $i++){
	$pid = $projects[$i]["project_id"];
	$ini_date = new CDate($projects[$i]["first_date"]);
	$fin_date = new CDate($projects[$i]["last_date"]);

	echo "fecini{$timexp_type}['{$pid}_1'] = '".$ini_date->format($df)."';\n";
	echo "fecini{$timexp_type}['{$pid}_2'] = '".$ini_date->format(FMT_TIMESTAMP_DATE)."';\n";
	echo "fecfin{$timexp_type}['{$pid}_1'] = '".$fin_date->format($df)."';\n";
	echo "fecfin{$timexp_type}['{$pid}_2'] = '".$fin_date->format(FMT_TIMESTAMP_DATE)."';\n";
}
?>
function popTSCalendar<?php echo $timexp_type;?>( field ){
	calendarField = field;
	idate = eval( 'document.edit<?php echo $timexp_types[$timexp_type];?>Sheets.timesheet_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendar<?php echo $timexp_type;?>&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setTSCalendar<?php echo $timexp_type;?>( idate, fdate ) {
	if(idate>today){
		alert("<?php echo $AppUI->_("timexpInvalidDate");?>");
	}else{
		fld_date = eval( 'document.edit<?php echo $timexp_types[$timexp_type];?>Sheets.timesheet_' + calendarField );
		fld_fdate = eval( 'document.edit<?php echo $timexp_types[$timexp_type];?>Sheets.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
	}
}

function suggestDates<?php echo $timexp_type;?>() {
	var f = document.edit<?php echo $timexp_types[$timexp_type];?>Sheets;
	var fi = fecini<?php echo $timexp_type;?>;
	var ff = fecfin<?php echo $timexp_type;?>;
	var prj = f.timesheet_project.options[f.timesheet_project.selectedIndex].value;
	if (prj >= 0){
		f.timesheet_start_date.value = fi[prj+"_2"];
		f.start_date.value = fi[prj+"_1"];
		f.timesheet_end_date.value = ff[prj+"_2"];
		f.end_date.value = ff[prj+"_1"];	
	}else{
		f.timesheet_start_date.value = "";
		f.start_date.value = "";
		f.timesheet_end_date.value = "";
		f.end_date.value = "";		
	}
}

function showAvailableTimexp<?php echo $timexp_type;?>(){
	var f = document.edit<?php echo $timexp_types[$timexp_type];?>Sheets;
	var tetype = <?php echo $timexp_type;?>;
	if (f.timesheet_project.value!="-1"){
		window.open( 'index.php?m=timexp&a=vw_unassigned_timexps&dialog=1&suppressLogo=1&type=<?php echo $timexp_type;?>&project=' + f.timesheet_project.value, 'availabletimexps', 'top=250,left=250,width=650, height=320, scrollbars' );
	}else{
		alert("<?php echo $AppUI->_("timesheetsNoProject");?>");
	}
}

function submitIT<?php echo $timexp_type;?>(){
	var f = document.edit<?php echo $timexp_types[$timexp_type];?>Sheets;
	var fi = document.getElementById("cmd_start_date<?php echo $timexp_type;?>");
	var ff = document.getElementById("cmd_end_date<?php echo $timexp_type;?>");
    
	if (f.timesheet_project.value=="-1"){
		alert("<?php echo $AppUI->_('timesheetsNoProject');?>");
		f.timesheet_project.focus();
	}
	else if (f.timesheet_start_date.value==""){
		alert("<?php echo $AppUI->_('timesheetsEmptyStartDate');?>");
		fi.focus();
	}
	else if (f.timesheet_end_date.value==""){
		alert("<?php echo $AppUI->_('timesheetsEmptyEndDate');?>");
		ff.focus();
	}
	else if (f.timesheet_end_date.value < f.timesheet_start_date.value){
		alert("<?php echo $AppUI->_('timesheetsInvalidDates');?>");
	}
	else{
		f.submit();
	}

}
//--></script>
<?php $tableStyle = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"'; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" <?=$tableStyle?>>
<tr >
  <td valign="top">
	<table width="100%" border="0" cellpadding="2" cellspacing="1">
     <tr>
      <td colspan="5" height='20'><strong><?php echo $AppUI->_("My ".$name_sheets[$timexp_type]."s");?></strong></td>
      <form method='GET' action='index.php' name='filter'>
      	<input type='hidden' name='m' value='timexp'>
      	<input type='hidden' name='autfilter' value='off'>
      	<?php if ($filtrar=='on') $check='checked';?>
      	<td colspan="2" align='center'>
      		<input type='checkbox' name='filtrar' onclick='submit()' <?php echo $check; ?> >
      		<?php echo $AppUI->_('Hide Annulled'); ?> 		
      	</td>
      </form>
     </tr>
    </table>
   </td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
 <tr valign="top">
  <td valign="top">
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr class="tableHeaderGral">
	  <th width="70" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Status");?></th>
	  <th width="79" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Date");?></th>
    <th width="40%" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Project");?></th>
    <th width="79" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("From");?></th>
    <th width="79" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("To");?></th>
    <th colspan="2" nowrap="nowrap" align="center" align="center"><?php echo $AppUI->_("Total");?></th>
    </tr>
    <tr class="tableHeaderGral">
      <th width="111" nowrap="nowrap" align="center"><?php echo $AppUI->_("Billables");?></th>
      <th width="111" nowrap="nowrap" align="center"><?php echo $AppUI->_("No billables");?></th>
    </tr>
<form action="" method="POST" name="edit<?php echo $timexp_types[$timexp_type];?>Sheets" >
<input type="hidden" name="timesheet_type" value="<?php echo $timexp_type;?>" />
<input type="hidden" name="timesheet_user" value="<?php echo $AppUI->user_id;?>" />
<input type="hidden" name="dosql" value="do_timesheet_aed" />
<input type="hidden" name="timesheet_id" value="" />
<input type="hidden" name="timesheet_last_status" value="0" />    
<?php 
 
 if (count($tslist)==0){?>
    <tr>
      <td colspan="10"><?php echo $AppUI->_("No data available");?></td>
      </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>
<?php
}

for($i=0; $i<count($tslist);$i++){
	$row=$tslist[$i];
	$date = new CDate($row["timesheet_date"]);
	$startdate = new CDate($row["timesheet_start_date"]);
	$enddate = new CDate($row["timesheet_end_date"]);
	$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["timesheet_last_status"]].";\"";

	$estado = $row["timesheet_last_status"];
	
	$est_temp = $timexp_status[$estado];

	$estado_name = $AppUI->_($est_temp);
	if ($filtrar!='on' OR $bgcolor!='style="background-color: #FF5555;"'){
?>
    <tr <?php echo $bgcolor;?>>
	  <td align="center"><?php echo $estado_name; ?></td>
	  <td align="center"><?php echo $date->format($df);?></td>
      <td>&nbsp;&nbsp;<a href="index.php?m=timexp&a=viewsheet&timesheet_id=<?php echo $row["timesheet_id"];?>">
	  <?php if($row["project_name"]=="0"){ echo $AppUI->_("Internal"); }else{ echo $row["project_name"];}?>
	  </a></td>
      <td align="center"><?php echo $startdate->format($df);?></td>
      <td align="center"><?php echo $enddate->format($df);?></td>
      <td align='right'><?php echo number_format($row["totbil"], 2);?>&nbsp;&nbsp;</td>
      <td align='right'><?php echo number_format($row["totnobil"], 2);?>&nbsp;&nbsp;</td>
    </tr> 
<?php 
  }
}
$date = new CDate();

if (count($projects)){
?>	
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
    <tr>
			<td align='right' title>&nbsp;</td>
			<td align="center"><?php echo $date->format($df);?></td>
      <td><?php  

						$cbo_projects= array("-1"=>"");
						for($i=0; $i<count($projects); $i++){
							$pid = $projects[$i]["project_id"];
							$cbo_projects [$pid]=$projects[$i]["project_name"];
						}

						echo arraySelect($cbo_projects, "timesheet_project",'size="1" class="text" onchange="javascript: suggestDates'.$timexp_type.'();"', "-1" ) ;
				?>&nbsp;<a href="#" onClick="showAvailableTimexp<?php echo $timexp_type;?>()"><?php echo $AppUI->_("View available");?></a></td>
      <td align="center" nowrap="nowrap">
					<input type="hidden" name="timesheet_start_date" value="">
					<input type="text" name="start_date" value="" class="text" disabled="disabled" size="10">
					<a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('start_date')" id="cmd_start_date<?php echo $timexp_type;?>"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
			</td>
      <td align="center" nowrap="nowrap">
					<input type="hidden" name="timesheet_end_date" value="">
					<input type="text" name="end_date" value="" class="text" disabled="disabled" size="10">
					<a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('end_date')" id="cmd_end_date<?php echo $timexp_type;?>"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
			</td>
      <td colspan="2" align="center"><input type="button" class="buttonbig" value="<?php echo $AppUI->_('create '.strtolower($name_sheets[$timexp_type]));?>" onclick="submitIT<?php echo $timexp_type;?>();" /></td>
      </tr>
<?php } else { ?>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
    <tr>
			<td colspan="97" align='left' title><?php 
			echo $AppUI->_("You have no ".$timexp_types[$timexp_type]." records to create a ".$name_sheets[$timexp_type]);
			?></td>
    </tr>	

<?php };?>      
      
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	

  </table>
  </td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">
		<table width="400" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#000000">
		<tr>
<?php
$wid = (100 / (count($timexp_status)+0))."%";

			foreach($timexp_status as $st_id=>$status_name){ 
				?>
				<td width="<?php echo $wid;?>" align="center" bgcolor="<?php echo $timexp_status_color[$st_id];?>"><?php echo $AppUI->_($status_name);?></td>
				<?php 
			} 
			?>
		</tr>
		</table>

</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
</form>
</table>
<?php 
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='0'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>



<br/><br/><br/>