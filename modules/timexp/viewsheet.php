<?php	
global $timesheet_id, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $qty_units;

$AppUI->savePlace(); 

$timesheet_id = intval( dPgetParam( $_GET, 'timesheet_id', 0 ) );

// retrieve any status parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TxpVwTSTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TxpVwTSTab' ) !== NULL ? $AppUI->getState( 'TxpVwTSTab' ) : 0;



$timesheet = new CTimesheet();
if (!$timesheet->load($timesheet_id, false)){
	$AppUI->setMsg( 'Timesheet' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}	

if($timesheet->timesheet_project == 0)
{
	$sql = "select user_supervisor 
				from users 
				where user_id = ".$timesheet->timesheet_user;
}
else
{
	$sql = "select timexp_supervisor 
				from users 
				where user_id = ".$timesheet->timesheet_user;
}

$is_not_supervised = (db_loadResult($sql) == -1 ? true : false);

$supervised_users = CTimexpSupervisor::getSupervisedUsers($AppUI->user_id);
$spvMode = $timesheet->canSupervise();
$spvMode_directReport = $timesheet->canSupervise_directReport();

	
$titleaction = $AppUI->_("View ".$name_sheets[$timesheet->timesheet_type]);

$ts_type = $timesheet->timesheet_type;



// setup the title block
$titleBlock = new CTitleBlock( $titleaction, 'timexp.gif', $m, "$m.$a" );

$titleBlock->addCell();
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday", "my daily view" );
if ($spvMode || $spvMode_directReport){
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");
	$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");	
}


//si se puede anular
if ($timesheet->timesheet_last_status==0 && $timesheet->timesheet_user==$AppUI->user_id){
	$htm_send = '<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>
						<a href="javascript:annul()" title="'.$AppUI->_("Annul").'">
							<img src="./images/icons/stock_cancel-16.png" width="16" height="16" alt="'.$AppUI->_("Annul").'" border="0" /></a></td>
						<td>&nbsp;<a href="javascript:annul()" title="'.$AppUI->_("Annul").'">'.$AppUI->_("Annul").'</a></td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
						'.
						
						'<td>
						<a href="javascript:sendforapproval()" title="'.$AppUI->_("Send for approval").'">
							<img src="./images/icons/stock_ok-16.png" width="16" height="16" alt="'.$AppUI->_("Send for approval").'" border="0" /></a></td>
						<td>&nbsp;<a href="javascript:sendforapproval()" title="'.$AppUI->_("Send for approval").'">'.$AppUI->_("Send for approval").'</a></td>'
					.'
					
					</tr>
				</table>';
	$titleBlock->addCrumbRight($htm_send );
	
}
$titleBlock->show();

$data = $timesheet->getTimesheetData();

$start_date = new CDate($data["timesheet_start_date"]);
$end_date = new CDate($data["timesheet_end_date"]);
$date = new CDate($data["timesheet_date"]);
$bgcolor = "style=\"background-color: ".$timexp_status_color[$data["timesheet_last_status"]].";\"";
$df = $AppUI->getPref('SHDATEFORMAT');

if (($timesheet->timesheet_last_status != 0) && ($timesheet->timesheet_last_status != 4) && ($timesheet->timesheet_sent_to != 0))
{
	$sent_to = "";
	if($timesheet->timesheet_sent_to > 0)
	{
		$sql = "SELECT user_first_name, user_last_name FROM users WHERE user_id = $timesheet->timesheet_sent_to";
		$user_data = mysql_fetch_array(mysql_query($sql));
		$sent_to = $user_data["user_first_name"]." ".$user_data["user_last_name"];
	}
	else 
	{
		$sent_to = $AppUI->_("Project Administrators");
	}
}

if (($timesheet->timesheet_last_status != 1) && ($timesheet->timesheet_last_status != 0) && ($timesheet->timesheet_modified_by != 0))
{
	$sql = "SELECT user_first_name, user_last_name FROM users WHERE user_id = $timesheet->timesheet_modified_by";
	$user_data = mysql_fetch_array(mysql_query($sql));
	$modified_by = $user_data["user_first_name"]." ".$user_data["user_last_name"];
}
?>
<script language="JavaScript"><!--
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
		document.getElementById('annulDiv').style.display='';
	}
}

function sendforapproval() {
	var desc="";
	if (confirm( "<?=	$AppUI->_('doSendAdvice');?>" )) {
		if (desc=window.prompt("<?=	$AppUI->_('doSendComments');?>")){
			document.frmSend.description.value = desc;			
		}	
		document.frmSend.submit();	
	}
}

function send(){
	desc = document.getElementById('annulDescription').value;
	document.frmAnul.description.value = desc;
	document.frmAnul.submit();
}
//-->
</script>

<div id="annulDiv" name="annulDiv" style='display:none;position:absolute;padding:0px;width:400px;height:120px;background-color: #E9E9E9; left: 40%; top: 40%; border:1px solid;'>
 <br>
   <center>
   	  <? echo $AppUI->_("doAnnulDescription").":"; ?>
   	  <br><br>
   	  <input type="text" name="annulDescription" id="annulDescription" class="text" style="width:300px;" />
   </center>
   <br>
   <table border="0" width="100%">
   <tr>
    <td width="15px;"></td>
   	<td align="left">
   	  <input type="button" class="button" value="<?=$AppUI->_("close")?>" onclick="javascript:document.getElementById('annulDiv').style.display='none';">
   	</td>
   	<td align="right">
   	  <input type="button" class="button" value="<?=$AppUI->_("submit")?>" onclick="send();">
   	</td>
   	<td width="15px;"></td>
   </tr>
   </table>
 <br>
</div>

<table cellspacing="1" cellpadding="0" border="0" width="100%" class="std">
 <form name="frmAnul" action="" method="post">
	<input type="hidden" name="dosql" value="do_timesheetstatus_aed" />
	<input type="hidden" name="description" value="" />
	<input type="hidden" name="timesheetstatus_status[<?php echo $timesheet_id;?>]" value="4" />
	<input type="hidden" name="timesheet_id" value="<?php echo $timesheet_id;?>" />
</form> 
 <form name="frmSend" action="" method="post">
	<input type="hidden" name="dosql" value="do_timesheetstatus_aed" />
	<input type="hidden" name="description" value="" />
	<input type="hidden" name="timesheetstatus_status[<?php echo $timesheet_id;?>]" value="<?php echo ($is_not_supervised ? 3 : 1)?>" />
	<input type="hidden" name="timesheet_id" value="<?php echo $timesheet_id;?>" />
</form> 
<tr>
	<td width="50%" valign="top">
	    <?
		$query_cia = "SELECT company_name, company_canal FROM companies WHERE company_id = '".$data['project_company']."'";
		$sql_cia = db_exec($query_cia);
		$data_cia = mysql_fetch_array($sql_cia);
		$company_name = $data_cia['company_name'];

		$query_canal = "SELECT company_name FROM companies WHERE company_id = '".$data_cia['company_canal']."'";
		$sql_canal = db_exec($query_canal);
		$data_canal = mysql_fetch_array($sql_canal);
		$company_canal = $data_canal['company_name'];

		?>

		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("User");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%"><?php echo $data["user_last_name"].", ".$data["user_first_name"];?></td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Company");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%"><?php echo $company_name;?></td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Channel");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%"><?php echo $company_canal;?></td>
		</tr>
		
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Project");?>:</td>
			<td class="hilite" colspan="3" width="80%">
			<?php 
			if($data[timesheet_project]!='0'){
			   echo $data["project_name"];
		    }else{
			   echo $AppUI->_("Internal");
			}
			?>
			</td>		
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Date");?>:</td>
			<td class="hilite" colspan="3" width="80%"><?php echo $date->format($df);?></td>	
		</tr>			
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("From");?>:</td>
			<td class="hilite" width="30%"><?php echo $start_date->format($df);?></td>	
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("To");?>:</td>
			<td class="hilite" width="30%"><?php echo $end_date->format($df);?></td>	
		</tr>
		<?php if (($timesheet->timesheet_last_status != 0) && ($timesheet->timesheet_last_status != 4) && ($timesheet->timesheet_sent_to != 0))
		{ ?>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Sent to");?>:</td>
			<td class="hilite" colspan="3" width="80%"><?=$sent_to?></td>
		</tr>
		<?php } ?>
		
		</table>
	</td>
	<td width="50%" valign="top">

		<table cellspacing="1" cellpadding="2" border="0" width="100%">
  	
		<tr>
			<td align="right" style="font-weight: bold;" nowrap><?php echo $AppUI->_("Billable");?>:</td>
			<td class="hilite"  ><?php echo number_format($data["totbil"],2);?></td>	
			<td colspan="2"  width="60%">&nbsp;</td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;"  nowrap><?php echo "No ".$AppUI->_("Billable");?>:</td>
			<td class="hilite"  ><?php echo number_format($data["totnobil"],2)?></td>
			<td colspan="2" width="60%">&nbsp;</td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" nowrap><?php echo $AppUI->_("Total ".$qty_units[$data["timesheet_type"]]);?>:</td>
			<td  class="hilite">
			 <?
			 $total = $data["totbil"] + $data["totnobil"];
			 echo number_format($total,2);
			 ?>
			</td>
			<td colspan="2"  width="60%">&nbsp;</td>
		</tr>
  	<tr>
			<td align="right" style="font-weight: bold;" width="20%" colspan="4">&nbsp;</td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Status");?>:</td>
			<td class="hilite" colspan="3" width="80%" <?php echo $bgcolor;?>><?php echo $AppUI->_($timexp_status[$data["timesheet_last_status"]]);?></td>	
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><b><?php echo $AppUI->_("Comments");?>:</b></td>
			<td class="hilite" colspan="3" width="80%" valign="top"><?php echo $data["timesheetstatus_description"];?></td>	
		</tr>
		<?php if (($timesheet->timesheet_last_status != 1) && ($timesheet->timesheet_last_status != 0) && ($timesheet->timesheet_modified_by != 0)) 
		{ ?>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Modified by");?>:</td>
			<td class="hilite" colspan="3" width="80%"><?=$modified_by?></td>
		</tr>
		<?php } ?>
		</table>
	</td>
	
</tr>
<tr bgcolor="#666666">
	<td height="1" colspan="3"></td>
</tr>
</table>

<?php


// tabbed information boxes
$tabBox = new CTabBox( "?m=$m&a=$a&timesheet_id=$timesheet_id", "{$AppUI->cfg['root_dir']}/modules/timexp/", $tab );

$tabBox->add( 'viewsheet_timexps', "Included ".$qty_units[$ts_type] );
if ($spvMode || $spvMode_directReport){
	$tabBox->add( 'viewsheet_status', 'Status Log' );
}
$tabBox->show();


 ?>