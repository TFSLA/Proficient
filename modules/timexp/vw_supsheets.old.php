<?php	
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $ts_status_transition, $qty_units;





$df = $AppUI->getPref('SHDATEFORMAT');

// Si no hay definido un tipo válido
if (!$timexp_type || !isset($timexp_types[$timexp_type])){
		$AppUI->setMsg( "Timesheet" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

// obtengo todos los timesheets que el user puede supervisar
$params=array(
"timesheet_type"=>"$timexp_type");
$ts_id_list = CTimesheet::getSupTimesheets($params);

$tslist=array();
$params=array(
"timesheet_id"=> implode($ts_id_list,","));
$tslist=CTimesheet::getListTimesheetsData($params);

//echo "<pre>";var_dump($ts_id_list); echo "</pre>";

if (true){

//echo "<pre>";var_dump($projects); echo "</pre>";
?>
<script language="JavaScript"><!--
<?php
/*
echo "var fecini$timexp_type = new Array();\n";
echo "var fecfin$timexp_type = new Array();\n";

for($i=0; $i<count($projects); $i++){
	$pid = $projects[$i]["project_id"];
	$ini_date = new CDate($projects[$i]["first_date"]);
	$fin_date = new CDate($projects[$i]["last_date"]);

	echo "fecini{$timexp_type}['{$pid}_1'] = '".$ini_date->format($df)."';\n";
	echo "fecini{$timexp_type}['{$pid}_2'] = '".$ini_date->format(FMT_TIMESTAMP_DATE)."';\n";
	echo "fecfin{$timexp_type}['{$pid}_1'] = '".$fin_date->format($df)."';\n";
	echo "fecfin{$timexp_type}['{$pid}_2'] = '".$fin_date->format(FMT_TIMESTAMP_DATE)."';\n";
}
*/
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
	if (prj > 0){
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

function submitIT<?php echo $timexp_type;?>(){
	var f = document.edit<?php echo $timexp_types[$timexp_type];?>Sheets;
	f.submit();

}


function hideSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp){
		sp.style.display="none";
	}
}
function showSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp){
		sp.style.display="";
	}
}

function switchdesc(rad, id){
	var sp = "tsdesc"+id;

	if (rad.value == "-1"){
		hideSpan(sp);
	}else{
		showSpan(sp);
		var txtdesc = document.getElementById("timesheetstatus_description[" + id + "]");
		if (txtdesc)
			txtdesc.focus();
	}
}

function listProperties(obj, objName) {
    var result = "";
    for (var i in obj) {
        result += objName + "." + i + "=" + obj[i] + "<br>";
    }
    var db = document.getElementById("debug");
    db.innerHTML = result;
}

//--></script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<form action="" method="POST" name="edit<?php echo $timexp_types[$timexp_type];?>Sheets" >
<input type="hidden" name="timesheetstatus_user" value="<?php echo $AppUI->user_id;?>" />
<input type="hidden" name="dosql" value="do_timesheetstatus_aed" />

<tr>
  <td>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
     <tr>
      <td colspan="12" class="tableForm_bg" ><strong><?php echo $AppUI->_($name_sheets[$timexp_type]."s Supervision");?></strong></td>
     </tr>	
    <tr class="tableHeaderGral">
			<th width="79" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("Date");?></th>
			<th width="20%" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("User");?></th>
      <th width="30%" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("Project");?></th>
      <th width="79" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("From");?></th>
      <th width="79" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("To");?></th>
      <th colspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Total ".$qty_units[$timexp_type]);?></th>
      <th colspan="4" nowrap="nowrap" align="center"><?php echo $AppUI->_("Change to");?></th>
      </tr>
    <tr class="tableHeaderGral">
      <th width="81" nowrap="nowrap" title="<?php echo $AppUI->_("Billables");?>"><?php echo $AppUI->_("B");?></th>
      <th width="81" nowrap="nowrap" title="<?php echo $AppUI->_("No billables");?>"><?php echo $AppUI->_("No B");?></th>
      <th width="20" align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Don't change");?>"><?php echo $AppUI->_("DC");?></th>
      <th width="20" align="center" nowrap="nowrap" title="<?php echo $AppUI->_("On course");?>"><?php echo $AppUI->_("O");?></th>
      <th width="20" align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Approved");?>"><?php echo $AppUI->_("A");?></th>
      <th width="20" align="center" nowrap="nowrap" title="<?php echo $AppUI->_("Disapproved");?>"><?php echo $AppUI->_("D");?></th>   
    </tr>
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
	$bgcolor1 = "style=\"background-color: ".$timexp_status_color[1].";\"";
	$bgcolor2 = "style=\"background-color: ".$timexp_status_color[3].";\"";
	$bgcolor3 = "style=\"background-color: ".$timexp_status_color[2].";\"";

for($i=0; $i<count($tslist);$i++){
	$row=$tslist[$i];
	
	$tsobj = new CTimesheet();
	$tsobj->load($row["timesheet_id"]);
	$date = new CDate($row["timesheet_date"]);
	$startdate = new CDate($row["timesheet_start_date"]);
	$enddate = new CDate($row["timesheet_end_date"]);
	$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["timesheet_last_status"]].";\"";
	
	$next_status = explode(",",$ts_status_transition[$row["timesheet_last_status"]]);
	
?>
    <tr <?php echo $bgcolor;?>>
			<td align="center"><?php echo $date->format($df);?></td>
			<td nowrap="nowrap"><?php echo $row["user_last_name"].", ".$row["user_first_name"];?></td>
      <td nowrap="nowrap"><a href="index.php?m=timexp&a=viewsheet&timesheet_id=<?php echo $row["timesheet_id"];?>"><?php echo $row["project_name"];?></a></td>
      <td align="center"><?php echo $startdate->format($df);?></td>
      <td align="center"><?php echo $enddate->format($df);?></td>
      <td align='right'><?php echo number_format($row["totbil"], 2);?></td>
      <td align='right'><?php echo number_format($row["totnobil"], 2);?></td>
      <td align='center'><?php  
      

      
      			echo '<input type="radio" name="timesheetstatus_status['.$row["timesheet_id"].']" value="-1" onclick="switchdesc(this, '.$row["timesheet_id"].');" checked defaultChecked />';
      ?></td>      
      <td align='center' <?php echo $bgcolor1;?>><?php 
      		if ($tsobj->canChangeStatus("1")){
      			echo '<input type="radio" name="timesheetstatus_status['.$row["timesheet_id"].']" value="1"  onclick="switchdesc(this, '.$row["timesheet_id"].');" />';
      		}else{
      			echo "&nbsp;";
      		}
      ?></td>
      <td align='center' <?php echo $bgcolor2;?>><?php 
      		if ($tsobj->canChangeStatus("3")){
      			echo '<input type="radio" name="timesheetstatus_status['.$row["timesheet_id"].']" value="3" onclick="switchdesc(this, '.$row["timesheet_id"].');" />';
      		}else{
      			echo "&nbsp;";
      		}
      ?></td>
      <td align='center' <?php echo $bgcolor3;?>><?php 
      		if ($tsobj->canChangeStatus("2")){
      			echo '<input type="radio" name="timesheetstatus_status['.$row["timesheet_id"].']" value="2" onclick="switchdesc(this, '.$row["timesheet_id"].');" />';
      		}else{
      			echo "&nbsp;";
      		}
      ?></td>
      </tr>
    <tr <?php echo $bgcolor;?>>
      <td colspan="97" align="right">
					<span id="tsdesc<?php echo $row["timesheet_id"];?>" name="tsdesc<?php echo $row["timesheet_id"];?>" style="display: none;">
						<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
						<tr><td width="50"></td>
							<td>
								<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="right" valign="top"><?php echo $AppUI->_("Description");?>:</td>
									<td align="left" width="75%"><textarea name="<?php echo "timesheetstatus_description[".$row["timesheet_id"]."]";?>" class="text" cols="60" rows="2" ></textarea></td>
								</tr>
								</table>
							</td>
						</tr>
						</table>
					</span>      
      
      </td>
    </tr>	     
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
    </tr>	
<?php 

}
 
if (count($tslist)!=0){?>
    <tr>
      <td colspan="20" align="right"><input type="button" class="button" value="<?php echo $AppUI->_('update');?>" onclick="submitIT<?php echo $timexp_type;?>();" /></td>
    </tr>		
<?php } ?>
  </table>
  </td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">
		<table width="400" border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#000000">
		<tr>
<?php
$wid = (100 / (count($timexp_status)+0))."%";

			foreach($timexp_status as $st_id=>$status_name){ ?>
			<td width="<?php echo $wid;?>" align="center" bgcolor="<?php echo $timexp_status_color[$st_id];?>"><?php echo $AppUI->_($status_name);?></td>
<?php } ?>
		</tr>
		</table>

</tr>
<tr>
  <td width="100%" colspan="7" class="tabox">&nbsp;</td>
</tr>
</form>
</table>

<pre>
<span id="debug">
</span>
</pre>
<?php }?>


<br/><br/><br/>
