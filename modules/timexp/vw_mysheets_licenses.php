<?php	
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar, $AppUI; 

IF ($_GET['autfilter']=='') $filtrar='on';
$params["timesheet_last_status"] = "0, 1, 2, 3";

$pager_array = getMyLicenses($params,NULL,"lic");
$tslist 		= $pager_array["rows"];
$pager_links	= $pager_array["pager_links"];

$df = $AppUI->getPref('SHDATEFORMAT');

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
<table width="100%" border="0" cellpadding="0" cellspacing="0" >
<tr>
  <td>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" <?=$tableStyle?>>
     <tr>
      <td height="30px" colspan="5" class="tableForm_bg" height='20'>&nbsp;&nbsp;&nbsp;<strong><?php echo $AppUI->_("My licenses");?></strong></td>
      <form method='GET' action='index.php' name='filter'	>
      	<input type='hidden' name='m' value='timexp'>
      	<input type='hidden' name='autfilter' value='off'>
      	<?php if ($filtrar=='on') $check='checked';?>
      	<td class="tableForm_bg" align='center' colspan="2">
      		<input type='checkbox' name='filtrar' onclick='submit()' <?php echo $check; ?> >
      		
      		<?php echo $AppUI->_('Hide Annulled'); ?> 		
      	</td>
      </form>
     </tr>
    </table>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr height="30" class="tableHeaderGral">
	  <th width="70" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Status");?></th>
	  <th width="79" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Date");?></th>
    <th width="15%" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Type");?></th>
    <th width="30%" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Details");?></th>
    <th width="79" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("From");?></th>
    <th width="79" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("To");?></th>
    <th rowspan="2" align="center" nowrap="nowrap" align="center"><?php echo $AppUI->_("Total");?></th>
    </tr>
<form action="" method="POST" name="edit<?php echo $timexp_types[$timexp_type];?>Sheets" >
<input type="hidden" name="timesheet_type" value="<?php echo $timexp_type;?>" />
<input type="hidden" name="timesheet_user" value="<?php echo $AppUI->user_id;?>" />
<input type="hidden" name="timesheet_id" value="" />
<input type="hidden" name="timesheet_last_status" value="0" />    
    <tr><tr>
      <td colspan="10">
      <?php
      	if(count($tslist)==0){ echo $AppUI->_("No data available"); }
      	else {
      		foreach ($tslist as $reg){
				$date = new CDate($reg["license_save_date"]);
				$startdate = new CDate($reg["license_from_date"]);
				$enddate = new CDate($reg["license_to_date"]);
				$bgcolor = "style=\"background-color: ".$timexp_status_color[$reg["license_status"]].";\"";

				$estado = $reg["license_status"];
	
				$est_temp = $timexp_status[$estado];

				$estado_name = $AppUI->_($est_temp);
				if ($filtrar!='on' OR $bgcolor!='style="background-color: #FF5555;"'){
				?>
    	<tr <?php echo $bgcolor; ?> >
	  	<td align="center"><?php echo $estado_name; ?></td>
	  	<td align="center"><?php echo $date->format($df);?></td>
      	<td>&nbsp;&nbsp; <a href="index.php?m=timexp&a=viewlicense&license_id=<?php echo $reg["license_id"];?>"> 	  	
      	<?php show_license_type($reg["license_type"]);?>
	  	</a></td>
	  	<td align="center"><?php echo substr($reg["license_description"],0,50)."..."; ?></td>
      	<td align="center"><?php echo $startdate->format($df);?></td>
      	<td align="center"><?php echo $enddate->format($df);?></td>
      	<td align="center"><?php 
      	$string_total_date = strtotime($reg["license_to_date"])-strtotime($reg["license_from_date"]);
		$total_date = intval($string_total_date/86400);
		$total_date++;
      	echo $total_date;?>&nbsp;&nbsp;</td>
    	</tr> 
		<?php 
  			}
      	 }
      }
      ?>
      </td>
    </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
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

<?php 

//------------------------------------------------------------------------------------------------

function getMyLicenses($params, $order=NULL, $paged_results = false){
	global $AppUI, $status;
	$user = $AppUI->user_id;

	$sql="select * from timexp_licenses";
	$sql.="\n\t where license_creator = $user ";
	if($status != -1)
		$sql.="\n\t and license_status = $status";
	
	$sql.="\n\t order by license_status, license_send_date, license_save_date";
	
	if(isset($order)){
		$sql .= "\n\t order by $order";
	}
		
	if ($paged_results){
		$dp = new DataPager($sql, $paged_results);
		$dp->showPageLinks = true;
		$rows = $dp->getResults();
		$pager_links = $dp->RenderNav();		
		return array ( "rows" => $rows,
					   "pager_links"=>$pager_links);	
	}else{
		return db_loadList($sql);
	}
}

//------------------------------------------------------------------------------------------------

function show_license_type($type_id){
	global $AppUI;
	
	$sql = "select * from timexp_licenses_types where license_type_id = ".$type_id;
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];

	echo $type_desc;
	return null;
}

?>