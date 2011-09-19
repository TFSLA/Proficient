<?php	
global $timesheet_id, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $qty_units;

$AppUI->savePlace(); 

//$licence_id = intval( dPgetParam( $_GET, 'licence_id', 0 ) );
$licence_id=$_REQUEST['licence_id'];

$sql = "select timexp_supervisor, user_type 
								from users 
								where user_id = ".$AppUI->user_id;

$is_not_supervised = db_loadResult($sql)=="-2" ? true : false;
$supervised_users = CTimexpSupervisor::getSupervisedUsers($AppUI->user_id);
$result = mysql_query($sql);
$spvMode = mysql_fetch_array($result);
$spvMode = $spvMode['user_type'];      //Verifico que sea Admin
//$spvMode = $timesheet->canSupervise();
//$spvMode_directReport = $timesheet->canSupervise_directReport();

$sql = "select * from timexp_licences where licence_id =".$_REQUEST['licence_id'];
$result = mysql_query($sql);
$licence_data = mysql_fetch_array($result);

$titleaction = $AppUI->_("View Licence");

//$ts_type = $timesheet->timesheet_type;

// setup the title block
$titleBlock = new CTitleBlock( $titleaction, 'timexp.gif', $m, "$m.$a" );

$titleBlock->addCell();
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday", "my daily view" );
$titleBlock->addCrumb( "?m=timexp&a=mysheets&tab=2", "my licences" );
if($licence_data['licence_status']==0) {
$titleBlock->addCrumb( "?m=timexp&a=editlicence&id=".$licence_data['licence_id'], "edit licence" );
}

if ($spvMode == 1){
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");
	$titleBlock->addCrumb("?m=timexp&a=suptimesheets&tab=2", "licences supervision");	
}

//si se puede anular
if ($licence_data['licence_status']==0 && $licence_data['licence_creator']==$AppUI->user_id){
	$htm_send = '<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>
						<a href="javascript:annul()" title="'.$AppUI->_("Annul").'">
							<img src="./images/icons/stock_cancel-16.png" width="16" height="16" alt="'.$AppUI->_("Annul").'" border="0" /></a></td>
						<td>&nbsp;<a href="javascript:annul()" title="'.$AppUI->_("Annul").'">'.$AppUI->_("Annul").'</a></td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
						'.
	($is_not_supervised ? '
						<td>
						<a href="javascript:approve()" title="'.$AppUI->_("Approve").'">
							<img src="./images/icons/stock_ok-16.png" width="16" height="16" alt="'.$AppUI->_("Approve").'" border="0" /></a></td>
						<td>&nbsp;<a href="javascript:approve()" title="'.$AppUI->_("Approve").'">'.$AppUI->_("Approve").'</a></td>'
					: '
						<td>
						<a href="javascript:sendforapproval()" title="'.$AppUI->_("Send for approval").'">
							<img src="./images/icons/stock_ok-16.png" width="16" height="16" alt="'.$AppUI->_("Send for approval").'" border="0" /></a></td>
						<td>&nbsp;<a href="javascript:sendforapproval()" title="'.$AppUI->_("Send for approval").'">'.$AppUI->_("Send for approval").'</a></td>'
	)				.'
	
					</tr>
				</table>';
	$titleBlock->addCrumbRight($htm_send );		
	
}
$titleBlock->show();

//$data = $timesheet->getTimesheetData();

$sql = "select * from users where user_id = ".$AppUI->user_id;
$result = mysql_query($sql);
$usr_data = mysql_fetch_array($result);

$start_date = new CDate($licence_data["licence_from_date"]);
$end_date = new CDate($licence_data["licence_to_date"]);
$date = new CDate($licence_data["licence_save_date"]);
$bgcolor = "style=\"background-color: ".$timexp_status_color[$licence_data["licence_status"]].";\"";
$df = $AppUI->getPref('SHDATEFORMAT');

$estado = $timexp_status[$licence_data["licence_status"]];
$estado_name = $AppUI->_($estado);   //Estado de la licencia

$string_total_date = strtotime($licence_data["licence_to_date"])-strtotime($licence_data["licence_from_date"]);
$total_date = intval($string_total_date/86400);
$total_date++;   //Total de días

$sql = "SELECT company_name FROM companies WHERE company_id = '".$usr_data['user_company']."'";
$result = db_exec($sql);
$data_cia = mysql_fetch_array($result);
$company_name = $data_cia['company_name'];  //Empresa

?>
<script language="JavaScript"><!--
function showJustification(justificationId){
	    	window.open( '/index.php?m=timexp&a=do_justification_show&supressHeaders=yes&id='+justificationId+'&dialog=1&suppressLogo=1','calwin', 'scrollbars=false');
}

function hideSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp){
		document.getElementById("exp"+spanname).src = "images/icons/expand.gif";
		document.getElementById("exp"+spanname).title = "<?php echo $AppUI->_("Show Details");?>";
		sp.style.display="none";
	}
}
function showSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp){
		document.getElementById("exp"+spanname).src = "images/icons/collapse.gif";
		document.getElementById("exp"+spanname).title = "<?php echo $AppUI->_("Hide Details");?>";
		sp.style.display="";
	}
}

function deleteJustification(id){
	if (confirm("<? echo $AppUI->_('doDeleteAdvice');?>" )) {
		window.location="/index.php?m=timexp&a=do_justification_delete&id="+id;
	}
}

function onoffSpan(spanname){
	var sp = document.getElementById(spanname);
	if(sp.style.display=="none"){
		showSpan(spanname);
	}else{
		hideSpan(spanname)
	}
}

function annul() {
	var desc= "";
    var descrip = "";
	if (confirm("<? echo $AppUI->_('doAnnulAdvice');?>" )) {
		document.frmAnul.submit();
	}
}

<?php if ($is_not_supervised){ ?>
function approve() {
	var desc="";
	if (confirm( "<?=	$AppUI->_('doApproveAdvice');?>" )) {
		document.frmSend.submit();	
	}
}
<?php }else{ ?>
function sendforapproval() {
	var desc="";
	if (confirm( "<?=	$AppUI->_('doSendAdvice');?>" )) {
		document.frmSend.submit();	
	}
}
<?php } ?>
//--></script>
<table cellspacing="1" cellpadding="0" border="0" width="100%" class="std">
 <form name="frmAnul" action="/index.php?m=timexp&a=do_licence_submit" method="post">
 	<input type="hidden" name="set_status" value="4" />
	<input type="hidden" name="licence_id" value="<?php echo $licence_id;?>" />
</form>
 <form name="frmSend" action="/index.php?m=timexp&a=do_licence_submit" method="post">
	<input type="hidden" name="set_status" value= <?php echo ($is_not_supervised ? '3' : '1'); ?> />
	<input type="hidden" name="licence_id" value="<?php echo $licence_id;?>" />
</form> 
<tr>
	<td width="50%" valign="top">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("User");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%"><?php echo $usr_data["user_last_name"].", ".$usr_data["user_first_name"];?></td>
			<input type="hidden" name="user_names" value="<?php echo $usr_data["user_last_name"].", ".$usr_data["user_first_name"];?>">
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Company");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%"><?php echo $company_name;?></td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Receibed");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%"><?php echo $date->format($df);?></td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><b><?php echo $AppUI->_("Comments");?>:</b></td>
			<td class="hilite" colspan="3" width="80%" valign="top"><?php 
			if(strlen($licence_data['licence_description']) > 300){
				echo substr($licence_data['licence_description'],0,300)."...";
			} else {
				echo $licence_data['licence_description'];
			}?>
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" style="font-weight: bold;" nowrap><?php echo $AppUI->_("Total "."");?>:</td>
			<td  class="hilite">
			 <?
			 echo number_format($total_date,2);
			 ?>
			</td>
			<td colspan="2"  width="60%">&nbsp;</td>
		</tr>
  	<tr>
			<td align="right" style="font-weight: bold;" width="20%" colspan="4">&nbsp;</td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Status");?>:</td>
			<td class="hilite" colspan="3" width="80%" <?php echo $bgcolor;?>><?php echo $estado_name;?></td>	
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><b><?php echo $AppUI->_("Type");?>:</b></td>
			<td class="hilite" colspan="3" width="80%" valign="top"><?php echo $licence_data['licence_type'];?></td>	
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("From");?>:</td>
			<td class="hilite" width="30%"><?php echo $start_date->format($df);?></td>	
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("To");?>:</td>
			<td class="hilite" width="30%"><?php echo $end_date->format($df);?></td>	
		</tr>

		</table>
	</td>
	
</tr>
<tr bgcolor="#666666">
	<td height="1" colspan="3"></td>
</tr>
</table>
<?php
    if($licence_data['licence_status']==2 || $licence_data['licence_status']==3){
    	$approval_date=new CDate($licence_data["licence_approval_date"]);
    	
    	echo "<table><tr><td>
    	".$AppUI->_("Supervisor Comments").": ".$licence_data['licence_supervisor_comment']."
    	</td></tr><tr><td>";

    	if($licence_data['licence_status']==3){
	    	echo $AppUI->_("Approbation Date");
    	}else{
    		echo $AppUI->_("Rejection Date");
    	}
    	
    	echo ": ".$approval_date->format($df).
	    	"</td></tr></table><hr>";
    }
?>
<?php	
	$sql = "select * from timexp_licences_justifications where 
	justification_related_licence = ".$_REQUEST['licence_id'];
	
	$result = mysql_query($sql);
	
  	if(mysql_num_rows($result)==0){
  		echo "<table><tr><td>".$AppUI->_("No justifications available")."</td></tr></table>";
  	} else { ?>
  	
	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="">
      <tr height="20" bgcolor="DDDDDD">
       	  <th width="50%" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Justification");?></th>
       	  <th rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Saved");?></th>
       	  <th width="70" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Size")." [kb]";?></th>
       	  <th width="20" rowspan="2"></th>
	  </tr>
	  <tr>
  	<?php
	while ($row = mysql_fetch_array($result)) {
		$date = new CDate($row["justification_create_date"]);
		echo "<tr><td>";
		echo "<a href = '#' onclick = 'showJustification(".$row["justification_id"].
				");'>".$row["justification_name"]."</a>";
		echo "</td><td align='center'>".$date->format($df).
		     "</td><td align='center'>".number_format(($row['justification_size']/1000),2).
		     "</td>";
		if($licence_data["licence_status"]==0){
				echo "<td align='center'> <a href='#' onclick='deleteJustification(".$row["justification_id"].");'>
			     	<img src='/images/icons/trash_bueno.png' alt='".$AppUI->_("Delete")."' 
			     	border='0'></a></td></tr>";
			}else{
				echo "<td></td></tr>";
			}
		}
  	}
	echo "</table>";
?>