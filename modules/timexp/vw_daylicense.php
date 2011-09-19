<?php
global  $timexp_type, $timexp_types, $timexp_applied_to_types, $qty_units , $supervise_user;

$df = $AppUI->getPref('SHDATEFORMAT');
$sup_user = $_REQUEST["sup_user"];

include "{$AppUI->cfg['root_dir']}/modules/timexp/config.php";

 if (isset($_GET[from_hour])) {
   $from_h = $_GET[from_hour];
 } else {
   $from_h = "00";
 }

 if (isset($_GET[from_min])) {
   $from_m = $_GET[from_min];
 } else {
   $from_m = "00";
 }

 $from_hora = $from_h.":".$from_m.":00.000";

 if (isset($_GET[to_hour])) {
  $to_h = $_GET[to_hour];
 } else {
   $to_h = "23";
 }

 if (isset($_GET[to_min])) {
   $to_m = $_GET[to_min];
 } else {
   $to_m = "59";
 }

 $to_hora = $to_h.":".$to_m.":59.999";

if (!(function_exists("mkFromTo"))){
	function mkFromTo($from_date, $to_date, $from_hora, $to_hora, $AppUI){
		
		$from_hour = substr($from_hora, 0, 2);
		$from_min = substr($from_hora, 3, 2);
		$to_hour = substr($to_hora, 0, 2);
		$to_min = substr($to_hora, 3, 2);


		if ($from_date!='--'){
			$from_year=substr($from_date, 0, 4);
			$from_month=substr($from_date, 5, 2);
			$from_day=substr($from_date, 8, 2);
		}
		else {
			$from_year=date("Y");
			$from_month=date("m")-1;
			$from_day=date("d");
		}
		if ($to_date!='--'){
			$to_year=substr($to_date, 0, 4);
			$to_month=substr($to_date, 5, 2);
			$to_day=substr($to_date, 8, 2);
		}
		else {
			$to_year=date("Y");
			$to_month=date("m");
			$to_day=date("d");
		}
		?>
		<table>
			<TR>
				<TD><? echo $AppUI->_("From")?> :</TD>
				<TD>
					<select name='from_day' size="1" class="text">
						<?php mkOption (1, 31, $from_day); ?>
					</select>
					<select name='from_month' size="1" class="text">
						<?php mkOption (1, 12, $from_month ); ?>
					</select>
					<select name='from_year' size="1" class="text">
						<?php mkOption (2000, date("Y"), $from_year); ?>
					</select> 
				</TD>
				<TD width="30">
				</TD>
				<TD align="right"><? echo $AppUI->_("To")?> :</TD>
				<TD align="left">
					<select name='to_day' size="1" class="text">
						<?php mkOption (1, 31, $to_day); ?>
					</select>
					<select name='to_month' size="1" class="text">
						<?php mkOption (1, 12, $to_month ); ?>
					</select>
					<select name='to_year' size="1" class="text">
						<?php mkOption (2000, date("Y"), $to_year); ?>
					</select> 
					<!-- <INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();"> -->
				</TD>
				<TD width="10"></TD>
				<TD>
					<?php echo $AppUI->_('Filter'); 
					if(isset($_GET["search_filter"])){
						$filter=$_GET["search_filter"];
						$selected = "selected='selected'";
					}
					?>:
					<select name='search_filter' size="1" class="text">
						<option value="1" <?php if($filter==1) echo $selected; ?>>
							<?php echo $AppUI->_('Request Date'); ?>
						</option>
						<option value="2" <?php if($filter==2) echo $selected; ?>>
							<?php echo $AppUI->_('Creation Date'); ?>
						</option>
						<option value="3" <?php if($filter==3) echo $selected; ?>>
							<?php echo $AppUI->_('License Term'); ?>
						</option>
					</select>
				</TD>
			</TR>
			<TR>
				<TD><? echo $AppUI->_("Between")?> :</TD>
				<TD align="left">
					<select name='from_hour' size="1" class="text">
						<?php mkOption (00, 23, $from_hour ); ?>
					</select> : 
					<select name='from_min' size="1" class="text">
						<?php mkOption (00, 59, $from_min ); ?>
					</select> </TD>
					<td></td>
                   <TD align="left"><? echo $AppUI->_("and")?> :</TD> 
				   <TD align="left">
					 <select name='to_hour' size="1" class="text">
						<?php mkOption (00, 23, $to_hour ); ?>
					</select> : 
					<select name='to_min' size="1" class="text">
						<?php mkOption (00, 59, $to_min ); ?>
					</select>
					<!-- <INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();"> -->
				</TD>
			</TR>
		</table>
		<?php
	}
}

if (!(function_exists("mkOption"))){
	function mkOption ($from, $to, $formsel) {
		while ($to >= $from){
			if ($from==$formsel) $sel='SELECTED';
			if ($from<10) $cero=0;
			echo "<option value='$cero$from' $sel>$cero$from</option>\n";
			$sel='';
			$cero='';
			$from++;
		}
	}
}

//$date = new CDate($sel_date);
$from_date=$_GET['from_year']."-".$_GET['from_month']."-".$_GET['from_day'];
$to_date=$_GET['to_year']."-".$_GET['to_month']."-".$_GET['to_day'];


?>
<?php $tableStyle = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"'; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<table width="650" border="0" cellpadding="0" cellspacing="0" <?=$tableStyle?>>
<tr>
<form action="" method="GET">
<input type='hidden' name='sup_user' value="<?php echo $sup_user; ?>">
<input type='hidden' name='m' value='<?php echo $_GET['m']; ?>'>
<input type='hidden' name='a' value='<?php echo $_GET['a']; ?>'>
  <td colspan="10"><strong>
  <?php
    mkFromTo($from_date, $to_date, $from_hora, $to_hora, $AppUI);
?>
<table>
	<tr>
		<td>
		  <? echo $AppUI->_("Type").": ";?>
		</td>
		<td width="12">
		</td>
		<td>
		  <? echo_combo_types(); ?>
		</td>
		<td width="20"></td>
		<td>
		  <? echo $AppUI->_("Containing").": "; ?>
		</td>
		<td>
		 <? $txt = "";
		 if (isset($_GET["txtsearch"])){
		 	$txt = $_GET["txtsearch"];
		 }
		 ?>
		 <input class="formularios" type="text" size="30" name="txtsearch" value="<? echo $txt; ?>">
		</td>
	</tr>
</table>
<table>
<tr>
	<td>
	 <? echo $AppUI->_("Status").": "; ?>
	</td>
	<td><?  
	echo "<select name='license_status' size='1' class='text' >";
	echo "<option value='-1' >".$AppUI->_("All Status")."</option>\n";
        
		foreach ($timexp_status as $key => $val) {
		if ($_GET["license_status"] == $key) $sel_s ='selected="selected"';
		else $sel_s ='';
		if ($_GET["license_status"] == '') $sel_s ='';

		echo "<option value='".$key."' $sel_s>".$AppUI->_($val)."</option>\n";
	    }

	echo "</select>";
	?>
	</td>
	<td width="365">
	</td>
	<td>
	<input type="submit" value="<?=$AppUI->_("Filter")?>" class="button">
	</td>
</tr>
</table>
<input type="hidden" name="search" value="1">
</strong>
</td>
</tr>
</form>
</table>
</td>
</tr>
<tr>
<td valign="top">
<form action="" method="POST">
<table width="650" border="0" cellpadding="2" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<th width="20%" nowrap="nowrap" align="center"><?=$AppUI->_("Type");?></th>
	<th width="80px" nowrap="nowrap" align="center"><?=$AppUI->_("Requested");?></th>
	<th width="80px" align="center" nowrap="nowrap"><?=$AppUI->_("From Date");?></th>
	<th width="80px" align="center" nowrap="nowrap"><?=$AppUI->_("To Date");?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Status");?></th>
	<?php
	if($_GET["a"] == "vw_sup_day") { ?>
		<th width="70" align="center" nowrap="nowrap"><?=$AppUI->_("User");?></th>	
	<?php } ?>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Certificate");?>s</th>
	<th width="20px" align="center" nowrap="nowrap"><?=$AppUI->_("Available");?></th>
</tr>

<?php
if($_REQUEST['search']==1){
	$from_date = $_REQUEST["from_year"]."-".$_REQUEST["from_month"]."-".$_REQUEST["from_day"]." ".$_REQUEST["from_hour"].":".$_REQUEST["from_min"].":00";
	$to_date = $_REQUEST["to_year"]."-".$_REQUEST["to_month"]."-".$_REQUEST["to_day"]." ".$_REQUEST["to_hour"].":".$_REQUEST["to_min"].":00";
	
	$sql = "SELECT * FROM timexp_licenses";
	if($_REQUEST['search_filter']==1){
		$sql .= " WHERE license_send_date >= '".$from_date."'";
		$sql .= " AND license_send_date <= '".$to_date."'";		
	} 
	if($_REQUEST['search_filter']==2){
		$sql .= " WHERE license_save_date >= '".$from_date."'";
		$sql .= " AND license_save_date <= '".$to_date."'";
	}
	if($_REQUEST['search_filter']==3){
		$sql .= " WHERE license_from_date >= '".$from_date."'";
		$sql .= " AND license_to_date <= '".$to_date."'";
	}
	if(isset($_REQUEST["sup_user"]) && !empty($_REQUEST["sup_user"])) {
		$sql .= " AND license_creator = ".$_REQUEST["sup_user"];
	} else {
		$sql .= " AND license_creator = ".$AppUI->user_id;
	}
	
	if($_REQUEST['license_status'] != -1){
		$sql.=" AND license_status = ".$_GET['license_status'];
	}
	
	if($_REQUEST['licenseType'] != -1){
		$sql.=" AND license_type = ".$_GET['licenseType'];
	}
	
	if(!empty($_REQUEST['txtsearch'])){
		$sql.=" AND license_description LIKE '%".@$_GET['txtsearch']."%'";
	}
	
	$sql .= " ORDER BY license_save_date";
	$result = mysql_query($sql);
	
	//die($sql);
	
	$df = $AppUI->getPref('SHDATEFORMAT');
	
	if(mysql_num_rows($result)){
		while ($row=mysql_fetch_array($result)) {
			if(($row["license_send_date"] <> '0000-00-00 00:00:00') && (!empty($row["license_send_date"]))){
				$request_date = new CDate($row["license_send_date"]);
			} else $request_date = '-';
			$start_date = new CDate($row["license_from_date"]);
			$end_date = new CDate($row["license_to_date"]);
			$estado = $timexp_status[$row["license_status"]];
			$estado_name = $AppUI->_($estado);   //Estado de la licencia
			$available=$AppUI->_('No');
			
			if($row["license_status"]==0 || $row["license_status"]==1){
				$available=$AppUI->_('Yes');
			}
			
			$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["license_status"]].";\"";
			$Type = get_license_type_ToShow($row['license_type']);
			$license_id = $row["license_id"];
			
			echo "<tr $bgcolor>";
			echo "<td align='left'><a href='/index.php?m=timexp&a=viewlicense&license_id=".$license_id."'>".$Type."</a>";
			echo "</td>";
			echo "<td align='center'>";
				if($request_date!='-') { echo $request_date->format($df); }
				else { echo $request_date; }
			echo "</td>";
			echo "<td align='center'>".$start_date->format($df);
			echo "</td>";
			echo "<td align='center'>".$end_date->format($df);
			echo "</td>";
			echo "<td>".$estado_name;
			echo "</td>";
			if($_REQUEST["a"] == "vw_sup_day"){
				$sql = "SELECT user_username FROM users WHERE user_id = ".$row["license_creator"];
				$resulta = mysql_query($sql);
				$user_data = mysql_fetch_array($resulta);
				echo "<td>".$user_data["user_username"];
				echo "</td>";
			}
			echo "<td align='center'>".$row['license_has_attachments'];
			echo "</td>";
			echo "<td align='center'>".$available;
			echo "</td>";
			echo "</tr>";
		}
	}else{
		echo "<tr><td colspan=5>".$AppUI->_("No data available")."</td></tr>";
	}
}
?>
	</td>	
</tr>
</table>
</form>
</td>
</tr>
</table>
<?php

//==========================================================================================

function echo_combo_types(){
global $AppUI;
echo '<select name="licenseType" size="1" class="text">';

$sql = "select * from timexp_licenses_types order by license_type_description_".$AppUI->user_locale;
$result = mysql_query($sql);

if(isset($_GET["licenseType"])){
	$type=$_GET["licenseType"];
}

echo '<option value=-1>'.$AppUI->_("All Types").'</option>';

while ($row = mysql_fetch_array($result)){
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];
	$type_id = $row['license_type_id'];
	$selected="";
	if($type_id==$type) { $selected=" selected='selected'"; }
	echo '<option value='.$type_id.$selected.'>'.$type_desc.'</option>';
}

echo '</select>';
return null;
}

//==========================================================================================

function get_license_type_ToShow($type_id){
	global $AppUI;
	
	$sql = "select * from timexp_licenses_types where license_type_id = ".$type_id;
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];

	return $type_desc;
}

?>