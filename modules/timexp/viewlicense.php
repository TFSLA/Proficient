<?php
global $timesheet_id, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $qty_units;

$AppUI->savePlace(); 

$license_id=$_REQUEST['license_id'];

if(!verify_user_id($license_id)){
	$AppUI->setMsg( 'Access Denied', UI_MSG_ERROR );
	$AppUI->redirect();
	break;
}

if($_REQUEST['submitted']==1){
	$AppUI->setMsg( 'updated' , UI_MSG_OK, true );	
}

if($_REQUEST['error']==1){
	$AppUI->setMsg( 'Error', UI_MSG_ERROR );
}

$sql = "select user_supervisor
								from users 
								where user_id = ".$AppUI->user_id;

$is_not_supervised = db_loadResult($sql)== "-1" ? true : false;
$supervised_users = CTimexpSupervisor::getSupervisedUsers($AppUI->user_id);
$result = mysql_query($sql);
$spvMode = mysql_fetch_array($result);
$spvMode = $spvMode['user_type'];      //Verifico que sea Admin

$sql = "select * from timexp_licenses where license_id =".$license_id;
$result = mysql_query($sql);
$license_data = mysql_fetch_array($result);

$titleaction = $AppUI->_("View License");

// setup the title block
$titleBlock = new CTitleBlock( $titleaction, 'timexp.gif', $m, "$m.$a" );

$addtime = '<table height="1">
    <tr>
    	<td>
    	<form action="index.php?m=timexp&a=addtime" method="POST"><td>
    	<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new time').'"';
//$addtime .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=addtime&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );\"";
$addtime .= ' /></td></form>
    	<form action="index.php?m=timexp&a=addeditexpense" method="POST"><td>';
$addexpense = '<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new expense').'"';
$addexpense .= ' /></td></form><td>
		<form action="index.php?m=timexp&a=new_license" method="POST"><td>';

$addlicense .= '<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new license').'"';
//$addlicense .= " onclick=\"javascript:window.open('./index.php?m=timexp&a=new_license&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );\"";
$addlicense .= ' /></td></form></td></tr></table>';

// setup the title block
$titleBlock = new CTitleBlock( $titleaction." ".$titleobject, 'timexp.gif', $m, "$m.$a" );

$titleBlock->addCell();
$titleBlock->addCell(
		$addtime."&nbsp;".$addexpense."&nbsp;".$addlicense, '',
		'', ''
);

$titleBlock->addCell();
$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday", "my daily view" );
$titleBlock->addCrumb( "?m=timexp&a=mysheets&tab=2", "my licenses" );
if(($license_data['license_status']==0 || $license_data['license_status']==1 && $license_data['license_creator']==$AppUI->user_id)) {
$titleBlock->addCrumb( "?m=timexp&a=editlicense&id=".$license_data['license_id'], "edit license" );
}

if ($spvMode == 1){
	$titleBlock->addCrumb("?m=timexp&a=vw_sup_day", "daily supervision");
}

//si se puede anular
if ($license_data['license_status']==0 && $license_data['license_creator']==$AppUI->user_id){
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

$sql = "select * from users where user_id = ".$license_data["license_creator"];
$result = mysql_query($sql);
$usr_data = mysql_fetch_array($result);

$start_date = new CDate($license_data["license_from_date"]);
$end_date = new CDate($license_data["license_to_date"]);
$bgcolor = "style=\"background-color: ".$timexp_status_color[$license_data["license_status"]].";\"";
$df = $AppUI->getPref('SHDATEFORMAT');

if(empty($license_data['license_send_date']) || $license_data['license_send_date']=='0000-00-00 00:00:00') { $date = "-"; }
else { $date = new CDate($license_data["license_send_date"]); }

$estado = $timexp_status[$license_data["license_status"]];
$estado_name = $AppUI->_($estado);   //Estado de la licencia

$string_total_date = strtotime($license_data["license_to_date"])-strtotime($license_data["license_from_date"]);
$total_date = intval($string_total_date/86400);
$total_date++;   //Total de días

$sql = "SELECT company_name FROM companies WHERE company_id = '".$usr_data['user_company']."'";
$result = db_exec($sql);
$data_cia = mysql_fetch_array($result);
$company_name = $data_cia['company_name'];  //Empresa

if (($license_data["license_status"] != 0) && ($license_data["license_status"] != 4) && ($license_data["license_sent_to"] > 0))
{
	$sql = "SELECT user_first_name, user_last_name FROM users WHERE user_id = ".$license_data["license_sent_to"];
	$user_data = mysql_fetch_array(mysql_query($sql));
	$sent_to = $user_data["user_first_name"]." ".$user_data["user_last_name"];
}

if (($license_data["license_status"] != 1) && ($license_data["license_status"] != 0) && ($license_data["license_modified_by"] > 0))
{
	$sql = "SELECT user_first_name, user_last_name FROM users WHERE user_id = ".$license_data["license_modified_by"];
	$user_data = mysql_fetch_array(mysql_query($sql));
	$modified_by = $user_data["user_first_name"]." ".$user_data["user_last_name"];
}
?>
<script language="JavaScript"><!--
function showcertificate(certificateId){
	    	window.open( '/index.php?m=timexp&a=do_certificate_show&supressHeaders=yes&id='+certificateId+'&dialog=1&suppressLogo=1','calwin', 'scrollbars=false');
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

function deletecertificate(id){
	if (confirm("<? echo $AppUI->_('doDeleteAdvice');?>" )) {
		window.location="/index.php?m=timexp&a=do_certificate_delete&id="+id;
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

function approve() {
	var desc="";
	if (confirm( "<?=	$AppUI->_('doApproveAdvice');?>" )) {
		document.frmSend.submit();	
	}
}

function sendforapproval() {
	var desc="";
	if (confirm( "<?=	$AppUI->_('doSendAdvice');?>" )) {
		document.frmSend.submit();	
	}
}

//--></script>
<table cellspacing="1" cellpadding="0" border="0" width="100%" class="std">
 <form name="frmAnul" action="./index.php?m=timexp&a=do_license_submit" method="post">
 	<input type="hidden" name="set_status" value="4" />
	<input type="hidden" name="license_id" value="<?php echo $license_id;?>" />
</form>
 <form name="frmSend" action="./index.php?m=timexp&a=do_license_submit" method="post">
	<input type="hidden" name="set_status" value= <?php echo ($is_not_supervised ? '3' : '1'); ?> />
	<input type="hidden" name="license_id" value="<?php echo $license_id;?>" />
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
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Requested");?>:</td>
			<td nowrap="nowrap" colspan="3" class="hilite" width="80%">
			<?php if($date != '-') { echo $date->format($df); }
				else { echo $date; } ?>
			</td>
		</tr>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><b><?php echo $AppUI->_("Comments");?>:</b></td>
			<td class="hilite" colspan="3" width="80%" valign="top"><?php 
			if(strlen($license_data['license_description']) > 300){
				echo substr($license_data['license_description'],0,300)."...";
			} else {
				echo $license_data['license_description'];
			}?>
			</td>
		</tr>
		<?php if (($license_data["license_status"] != 0) && ($license_data["license_status"] != 4) && ($license_data["license_sent_to"] > 0))
		{ ?>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Sent to");?>:</td>
			<td class="hilite" colspan="3" width="80%"><?=$sent_to?></td>
		</tr>
		<?php } ?>
		<?php if (($license_data["license_status"] != 1) && ($license_data["license_status"] != 0) && ($license_data["license_modified_by"] > 0)) 
		{ ?>
		<tr>
			<td align="right" style="font-weight: bold;" width="20%"><?php echo $AppUI->_("Modified by");?>:</td>
			<td class="hilite" colspan="3" width="80%"><?=$modified_by?></td>
		</tr>
		<?php } ?>
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
			<td class="hilite" colspan="3" width="80%" valign="top"><?php echo get_license_type($license_data['license_type']);?></td>	
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
    if($license_data['license_status']==2 || $license_data['license_status']==3){
    	$approval_date=new CDate($license_data["license_approval_date"]);
    	
    	echo "<table><tr><td>
    	".$AppUI->_("Supervisor Comments").": ".$license_data['license_supervisor_comment']."
    	</td></tr><tr><td>";

    	if($license_data['license_status']==3){
	    	echo $AppUI->_("Approbation Date");
    	}else{
    		echo $AppUI->_("Rejection Date");
    	}
    	
    	echo ": ".$approval_date->format($df).
	    	"</td></tr></table><hr>";
    }
?>
<?php	
	$sql = "select * from timexp_licenses_certificates where 
	certificate_related_license = ".$_REQUEST['license_id'];
	
	$result = mysql_query($sql);
	
  	if(mysql_num_rows($result)==0){
  		echo "<table><tr><td>".$AppUI->_("No certificates available")."</td></tr></table>";
  	} else { ?>
  	
	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="">
      <tr height="20" bgcolor="DDDDDD">
       	  <th width="50%" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Certificate");?></th>
       	  <th rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Saved");?></th>
       	  <th width="70" rowspan="2" nowrap="nowrap" align="center"><?php echo $AppUI->_("Size")." [kb]";?></th>
       	  <th width="20" rowspan="2"></th>
	  </tr>
	  <tr>
  	<?php
	while ($row = mysql_fetch_array($result)) {
		$date = new CDate($row["certificate_create_date"]);
		echo "<tr><td>";
		echo "<a href = '#' onclick = 'showcertificate(".$row["certificate_id"].
				");'>".$row["certificate_name"]."</a>";
		echo "</td><td align='center'>".$date->format($df).
		     "</td><td align='center'>".number_format(($row['certificate_size']/1000),2).
		     "</td>";
		if($license_data["license_status"]==0){
				echo "<td align='center'> <a href='#' onclick='deletecertificate(".$row["certificate_id"].");'>
			     	<img src='/images/icons/trash_bueno.png' alt='".$AppUI->_("Delete")."' 
			     	border='0'></a></td></tr>";
			}else{
				echo "<td></td></tr>";
			}
		}
  	}
	echo "</table>";

function verify_user_id( $id_licencia ){	
global $AppUI;
		
$sql="select license_creator from timexp_licenses where license_id = ".$id_licencia;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$user_id=$row['license_creator'];

$sql = "select user_supervisor from users where user_id = ".$user_id;	
$result = mysql_query($sql);
$sup_data = mysql_fetch_array($result);

$supervisor_id = $sup_data['user_supervisor'];

if($supervisor_id == $AppUI->user_id || $user_id == $AppUI->user_id) { return true; }
else { return false; }
}

function get_license_type($type_id){
	global $AppUI;
	
	$sql = "select * from timexp_licenses_types where license_type_id = ".$type_id;
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];

	return $type_desc;
}	
?>