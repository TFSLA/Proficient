<?php	
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $ts_status_transition, $qty_units, $project, $user, $status;

$df = $AppUI->getPref('SHDATEFORMAT');

$pager_array = CTimesheet::getMyTimesheets($params,NULL,"myts");
$tslist 		= $pager_array["rows"];
$pager_links	= $pager_array["pager_links"];

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

function submitIT(){
	var f = document.editLicenceSheets;
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

	if (rad.value == "-1" || rad.value == "6"){
		hideSpan(sp);
	}else{
		showSpan(sp);
		var txtdesc = document.getElementById("licence_description[" + id + "]");
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
<form action="/index.php?m=timexp&a=do_licencestatus_aed" method="POST" name="editLicenceSheets" >
<input type="hidden" name="timesheetstatus_user" value="<?php echo $AppUI->user_id;?>" />

<tr>
  <td>
	<table width="100%" border="0" cellpadding="0" cellspacing="1" class="">
	<col width="79px"><col><col><col width="79px"><col width="79px"><col width="80px"><col width="80px"><col width="80px">
    <tr class="tableHeaderGral" height="30">
	  <th width="70" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("Date");?></th>
      <th width="100" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("User");?></th>
      <th width="79" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("Type");?></th>
      <th width="70" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("From");?></th>
      <th width="70" rowspan="2" nowrap="nowrap"><?php echo $AppUI->_("To");?></th>
      <th width="150" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Details");?></th>
      <th width="40" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Justifications");?></th>
      <th width="70" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Total"); ?></th>
      <th width="120" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Change to");?></th>
      </tr>
      <tr>
<?php 
$query="select * from timexp_licences where licence_status = 1 order by licence_save_date";
$registros = mysql_query($query);

if (mysql_num_rows($registros)!=0){
	$bgcolor1 = "style=\"background-color: ".$timexp_status_color[1].";\"";
	$bgcolor2 = "style=\"background-color: ".$timexp_status_color[3].";\"";
	$bgcolor3 = "style=\"background-color: ".$timexp_status_color[2].";\"";

	$total = 0;
	$count = 0;
	
while($row=mysql_fetch_array($registros)){
	$canSupervise=false;
	
	$query = "select user_id, user_first_name, user_last_name from users where 
	user_supervisor = ".$AppUI->user_id." and user_id = ".$row["licence_creator"];
	$resultados=mysql_query($query);
	
    if($reg=mysql_fetch_array($resultados)) {
	$date = new CDate($row["licence_save_date"]);
	$startdate = new CDate($row["licence_from_date"]);
	$enddate = new CDate($row["licence_to_date"]);
	$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["licence_status"]].";\"";
	
	$next_status = $timexp_status;
    ?>
    
    <tr <?php echo $bgcolor;?>>
			<td align="center"><?php echo $date->format($df);?></td>
			<td><?php echo $reg["user_last_name"].", ".$reg["user_first_name"];?></td>
      <td><a href="index.php?m=timexp&a=viewlicence&licence_id=<?php echo $row["licence_id"];?>">
	  <?php echo $row["licence_type"]; ?></a></td>
      <td align="center"><?php echo $startdate->format($df);?></td>
      <td align="center"><?php echo $enddate->format($df);?></td>
      <td align="center"><?php echo substr($row["licence_description"],0,30)."...";?></td>
      <td align="center"><?php 
      	if($row["licence_has_attachments"]==0){
      		echo "<img border=0 src='/images/icons/trash20.gif' alt='No'>";
      	}else{
      		echo "<img border=0 src='/images/icons/stock_ok-16.png' alt='Ok'>";      		
      	}
      ?></td>
      <td align="center">
      <?php
	  $string_total_date = strtotime($row["licence_to_date"])-strtotime($row["licence_from_date"]);
	  $total_date = intval($string_total_date/86400);
	  $total_date++;
	  $total+=$total_date;
	  echo "$total_date";
      ?>
      </td>
      <td align='center' colspan="4"><?php  
      
	  unset($next_status[0]);
	  unset($next_status[1]);
	  unset($next_status[4]);
      $next_status = arrayMerge(array("-1"=>"---------------------"), $next_status);
      $next_status = arrayMerge(array("6"=>$AppUI->_("Request Justification")), $next_status);
      
      echo arraySelect($next_status, 'licencestatus_status['.$row["licence_id"].']', 'size="1" class="text" width="100" onchange="javascript: switchdesc(this.options[this.selectedIndex], '.$row["licence_id"].');"', "-1", true );

	  //echo "<input type='text' name='licencestatus_status[".$row['licence_id']."]'";
      ?>
      </td>
    </tr>
    <tr <?php echo $bgcolor;?>>
      <td colspan="97" align="right">
					<span id="tsdesc<?php echo $row["licence_id"];?>" name="tsdesc<?php echo $row["licence_id"];?>" style="display: none;">
						<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
						<tr><td width="50"></td>
							<td>
								<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="right" valign="top"><?php echo $AppUI->_("Comments");?>:</td>
									<td align="left" width="75%"><textarea name="<?php echo "licence_comment[".$row["licence_id"]."]";?>" class="text" cols="60" rows="2" ></textarea></td>
								</tr>
								</table>
							</td>
						</tr>
						</table>
					</span>
     		 	</td>
    		</tr>	     
			<?php 
   			}
  		}
 	}
}


if ($total>0 && mysql_num_rows($registros)!=0){
   ?>
   <tr class="tableRowLineCell">
      <td colspan="7" align="right" ><b>TOTAL</b> </td>
	  </td>
	  <td align="center"> 
      <?php // Total de dias
	   echo $total;
	  ?> 
	  </b>
      </td>
      <td></td>
   	</tr>	
    <tr>
      <td colspan="20" align="right"><input type="button" class="button" value="<?php echo $AppUI->_('update');?>" onclick="submitIT();" /></td>
    </tr>		
<?php } else {  ?>
	<tr>
      <td colspan="10"><?php echo $AppUI->_("No data available");?></td>
    </tr>
    <tr class="tableRowLineCell">
      <td colspan="97"></td>
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
<?php 
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>
<pre>
<span id="debug">
</span>
</pre>