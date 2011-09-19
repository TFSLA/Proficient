<?php	
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $ts_status_transition, $qty_units, $project, $user, $status;

$df = $AppUI->getPref('SHDATEFORMAT');

$pager_array = getSupLicenses($params,NULL,"lic");
$tslist 		= $pager_array["rows"];
$pager_links	= $pager_array["pager_links"];

?>
<script language="JavaScript"><!--
function requestCertificate(id){
	window.location="/index.php?m=timexp&a=do_certificate_request&id="+id;
}

function submitIT(){
	var f = document.editlicensesheets;
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
		var txtdesc = document.getElementById("license_description[" + id + "]");
		if (txtdesc)
			txtdesc.focus();
	}
}

function listProperties(obj, objName){
    var result = "";
    for (var i in obj) {
        result += objName + "." + i + "=" + obj[i] + "<br>";
    }
    var db = document.getElementById("debug");
    db.innerHTML = result;
}

//--></script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<form action="./index.php?m=timexp&a=do_licensestatus_aed" method="POST" name="editlicensesheets" >
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
      <th width="40" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("certificates");?></th>
      <th width="70" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Total"); ?></th>
      <th width="120" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Change to");?></th>
      </tr>
      <tr>
<?php 
if (count($tslist)!=0){
	$bgcolor1 = "style=\"background-color: ".$timexp_status_color[1].";\"";
	$bgcolor2 = "style=\"background-color: ".$timexp_status_color[3].";\"";
	$bgcolor3 = "style=\"background-color: ".$timexp_status_color[2].";\"";

	$total = 0;
	$count = 0;
	foreach ($tslist as $row) {
		$canSupervise=false;
		
		$query = "SELECT user_id, user_first_name, user_last_name FROM users 
			WHERE user_id = ".$row["license_creator"];
		$resultados=mysql_query($query);
		$reg=mysql_fetch_array($resultados);
		
		$date = new CDate($row["license_save_date"]);
		$startdate = new CDate($row["license_from_date"]);
		$enddate = new CDate($row["license_to_date"]);
		$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["license_status"]].";\"";
		
		$next_status = $timexp_status;
	    ?>
	    
	    <tr <?php echo $bgcolor;?>>
				<td align="center"><?php echo $date->format($df);?></td>
				<td><?php echo $reg["user_last_name"].", ".$reg["user_first_name"];?></td>
	      <td><a href="index.php?m=timexp&a=viewlicense&license_id=<?php echo $row["license_id"];?>">
		  <?php echo get_license_type_desc($row["license_type"]); ?></a></td>
	      <td align="center"><?php echo $startdate->format($df);?></td>
	      <td align="center"><?php echo $enddate->format($df);?></td>
	      <td align="center"><?php echo substr($row["license_description"],0,30)."...";?></td>
	      <td align="center"><?php
	      if($row["license_has_attachments"]==0){
			  echo "<a href='#' onclick='requestCertificate(".$row["license_id"].");'>";
			  echo $AppUI->_('Request');
			  echo "</a>";
			  
		  	  if(license_certificate_requested($row["license_id"])){
	  			echo "<br>(".$AppUI->_('requested').")";
		 		}
	      	}else{
			  echo "<a href='index.php?m=timexp&a=viewlicense&license_id=".$row["license_id"]."'>";
			  echo $AppUI->_('Show');
			  echo "</a>";
	      	}
	      ?></td>
	      <td align="center">
	      <?php
		  $string_total_date = strtotime($row["license_to_date"])-strtotime($row["license_from_date"]);
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
	      
	      echo arraySelect($next_status, 'licensestatus_status['.$row["license_id"].']', 'size="1" class="text" width="100" onchange="javascript: switchdesc(this.options[this.selectedIndex], '.$row["license_id"].');"', "-1", true );
	      ?>
	      </td>
	    </tr>
	    <tr <?php echo $bgcolor;?>>
	      <td colspan="97" align="right">
				<span id="tsdesc<?php echo $row["license_id"];?>" name="tsdesc<?php echo $row["license_id"];?>" style="display: none;">
					<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
					<tr><td width="50"></td>
						<td>
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td align="right" valign="top"><?php echo $AppUI->_("Comments");?>:</td>
								<td align="left" width="75%"><textarea name="<?php echo "license_comment[".$row["license_id"]."]";?>" class="text" cols="60" rows="2" ></textarea></td>
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

if ($total>0 && count($tslist)!=0){
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
<?php 

function getSupLicenses($params, $order=NULL, $paged_results = false){
	global $AppUI, $status, $user;
	
	$sql="SELECT * FROM timexp_licenses AS l 
		  INNER JOIN users u ON u.user_id = l.license_creator
		  WHERE 1 = 1 AND
		  u.user_supervisor = $AppUI->user_id";
	
	if(!empty($status) && $status != -1){
		$sql .= " AND license_status = $status";
	}else{
		if($status <> -1)
			$sql .= " AND license_status = 1";
		else 
			$sql .= " AND license_status <> 0";
	}
	
	if(!empty($user) && $user != -1){
		$sql .= " AND license_creator = $user";
	}
	
	$sql .= " ORDER BY license_send_date";
	//echo "<pre>$sql</pre>";
	
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

function get_license_type_desc($type_id){
	global $AppUI;
	
	$sql = "select * from timexp_licenses_types where license_type_id = ".$type_id;
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];

	return $type_desc;
}	

function license_certificate_requested($id_license) {
	$sql = "select license_certificate_requested from timexp_licenses where license_id = ".$id_license;
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	if($row['license_certificate_requested'] != 0) {
		return true;
	}else{
		return false;
	}
}

?>