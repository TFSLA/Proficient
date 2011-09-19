<?php 
//$AppUI->savePlace();
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar; 

$license_id = $_REQUEST['id'];

if(!verify_user_id($license_id)){
	$AppUI->setMsg( 'Access Denied', UI_MSG_ERROR );
	$AppUI->redirect();
	break;
}

$titleBlock = new CTitleBlock( 'Edit License', 'timexp.gif', $m, "timexp.index" );

$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday", "my daily view" );
$titleBlock->addCrumb( "?m=timexp&a=mysheets&tab=2", "my licenses" );

$titleBlock->show();

$sql = "select * from timexp_licenses where license_id = ".$_REQUEST['id'];
$result = mysql_query($sql);
$license_data = mysql_fetch_array($result);

$start_date = new CDate($license_data["license_from_date"]);
$end_date = new CDate($license_data["license_to_date"]);
$df = $AppUI->getPref('SHDATEFORMAT');
?>

<script language="JavaScript"><!--
<?php

echo "var fecini$timexp_type = new Array();\n";
echo "var fecfin$timexp_type = new Array();\n";
echo "var today = '".date("Ymd")."';\n";

?>
function showcertificate(certificateId){
	    	window.open( '/index.php?m=timexp&a=do_certificate_show&supressHeaders=yes&id='+certificateId+'&dialog=1&suppressLogo=1','calwin', 'scrollbars=false');
}

function popTSCalendar<?php echo $timexp_type;?>( field ){
	calendarField = field;
	idate = eval( 'document.EditLicense.timesheet_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendar<?php echo $timexp_type;?>&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setTSCalendar<?php echo $timexp_type;?>( idate, fdate ) {
		fld_date = eval( 'document.EditLicense.timesheet_' + calendarField );
		fld_fdate = eval( 'document.EditLicense.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
}

function submitIT() {
	var f = document.EditLicence;
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

function cleanFields(){
	document.all.item("start_date").innerText="";
	document.all.item("end_date").innerText="";
	document.all.item("comentarios").innerText="";
}

var position=1;

function remove_line(target){
	if(position>1)	{
   		position--;
   		var el = document.getElementById("file" + position);
   		// Obtenemos el padre de dicho elemento
   		var padre = el.parentNode;
   		// Eliminamos el hijo (el) del elemento padre
   		padre.removeChild(el);

   		/*var el = document.getElementById("'br" + position + "'");
   		var padre = el.parentNode;
   		padre.removeChild(el);*/
	}
//document.getElementById(target).innerHTML += "<input tabindex='8' name='file" + i + "' type='file' size='70'>";
}

function new_line(target){
  /*var li = document.createElement("<div name='divs" + position + "'>");
  var ul = document.getElementById(target);
  ul.appendChild(li);
  var NewTarget='divs'+position.toString();*/
  
  //var ul = document.getElementById("div0");
  var li = document.createElement("<br id= br'"+position+"'>");
  var ul = document.getElementById(target);
  ul.appendChild(li);
  
  var li = document.createElement("<input name='file" + position + "' type='file' size='70'>");
  var ul = document.getElementById(target);
  ul.appendChild(li);
  
  position++;
}

function validate(){
	var controls = document.all;
	var Error=false;
	var fechaInicio = controls.item("timesheet_start_date").value;
	var fechaFin = controls.item("timesheet_end_date").value;
	
	//Verificar fecha fin > fehca inicio
	if(fechaInicio > fechaFin){		
		alert("<?php echo $AppUI->_('timexpInvalidToDate3');?>");
		Error=true;
	}
	
	if(!Error){
		document.forms["EditLicense"].submit();
	}
}
//-->
</script>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
 <form name="EditLicense" action="/index.php?m=timexp&a=do_license_update" method="POST" enctype="multipart/form-data">
  <tr bgcolor="White"><td height="2" colspan="15"></td></tr>
  <tr><td><br></td></tr>
  <tr>
   <td width=20>
   </td>
   <td width=20% colspan=3>
    <input type="hidden" name="license_id" value="<?php echo $license_data['license_id']; ?>">
   
    <?php echo $AppUI->_("From");?>:
    <input type="hidden" name="timesheet_start_date" value=<?php 	
	    $start_date_formatted = $license_data["license_from_date"];
		$start_date_formatted = ereg_replace("-","",$start_date_formatted);
		$start_date_formatted = substr($start_date_formatted,0,8);
		echo $start_date_formatted; ?>>
    <input type=text name="start_date" class="text" disabled="disabled" size="10" value="<?php echo $start_date->format($df); ?>">
    <?php if(is_not_sended($license_data['license_id'])){ ?>
    	<a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('start_date')" id="cmd_start_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    <?php } ?>
   	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

    
    <?php echo $AppUI->_("To");?>:
    <input type="hidden" name="timesheet_end_date" value=<?php
     	$end_date_formatted = $license_data["license_to_date"];
		$end_date_formatted = ereg_replace("-","",$end_date_formatted);
		$end_date_formatted = substr($end_date_formatted,0,8);
		echo $end_date_formatted; ?>>
    <input type=text name="end_date" class="text" disabled="disabled" size="10" value="<?php echo $end_date->format($df); ?>">
    <?php if(is_not_sended($license_data['license_id'])){ ?>
    	<a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('end_date')" id="cmd_end_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    <?php } ?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php if(is_not_sended($license_data['license_id'])){ ?>
	    <input type="button" class="button" value="<?php echo $AppUI->_("Reset");?>" onclick="cleanFields()">
    <?php } ?>
   </td>
   <td width=5>
   </td>
   <td>
   </td>
  </tr>
  <tr>
   <td><br><br>
   </td>
  </tr>
  <tr>
   <td>
   </td>
   <td align=left width="14%" valign="top">
    <?php echo $AppUI->_("Type");?>:
	&nbsp;
	<?php combo_types_editing($license_data); ?>
   </td>
   <td width="10%">
    <?php echo $AppUI->_("Comments");?>:&nbsp;
	</td><td rowspan="2" colspan="3">
    <textarea name="comentarios" rows=4 cols=35 <?php if(!is_not_sended($license_data['license_id'])){ echo "disabled='disabled'"; }?>><?php echo $license_data['license_description']; ?></textarea>
   </td>
   <td width="20%"></td>
  </tr>
  <tr>
   <td><br><br>
   </td>
  </tr>
  <tr>
   <td>
   </td>
   <td align=left colspan="3" height="40">
    <?php echo $AppUI->_("certificates");?>
    [ <a href="#" onclick="new_line('Adj'); return false;">
     <?php echo $AppUI->_("Add"); ?></a> / 
     <a href="#" onclick="remove_line('Adj'); return false;">
     <?php echo $AppUI->_("Remove"); ?></a> ]
    </td>
  </tr>
  <tr>
   <td colspan="15"><hr><br>
   </td>
  </tr>
  <tr>
   <td></td>
     <?php 
       	$sql = "select * from timexp_licenses_certificates where 
			certificate_related_license = ".$_REQUEST['id'];
       	$result = mysql_query($sql);
       	
       if(mysql_numrows($result)==0){
       		echo "<td>".$AppUI->_("No previous saved certificates available")."<br><br></td>";
       }
       else {
       	while ($certificate = mysql_fetch_array($result)) {
			echo "<tr><td></td><td colspan='5'>";
			echo "<a href = '#' onclick = 'showcertificate(".$certificate["certificate_id"].
					");'>".$certificate["certificate_name"]."</a>";
			echo "</td></tr>";
       		}
       		echo "<tr><td><br><br></td></tr>";
       }
     ?>
  </tr>
  <tr>
   <td></td>
   <td colspan="4" nowrap="nowrap" width="50%">
    <div id="Adj">
     	 <input name="file0" type="file" size="70"/>
    </div>
   </td>
   <td></td>
  </tr>
  <tr>
   <td colspan="15"><br><hr><br>
   </td>
  </tr>
  <tr>
   <td></td>
   <td colspan="2">
     <input type="button" class="button" value="<?php echo $AppUI->_("Back"); ?>" onClick="javascript:window.location=('/index.php?m=timexp&a=viewlicense&license_id=<?php echo $license_data['license_id']; ?>');">
   </td>
   <td align=right>
    <input type="button" class="button" value="<?php echo $AppUI->_("Update"); ?>" onclick="validate();">
   </td>
  </tr>
  <tr><td><br></td></tr>
  <tr><td><br></td></tr>
 </table>
 </table>
</form>
</body>
</html>

<?php
//------------------------------------------------------------------------------------------------

function is_not_sended($id_license){
	$sql="select license_status from timexp_licenses where license_id = ".$id_license;
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result);
	
	if($row['license_status']==0){
		return true;
	}else{
		return false;
	}
}

//------------------------------------------------------------------------------------------------

function combo_types_editing($license_data){
global $AppUI;
echo '<select name="licenseType" size="1"';
if(!is_not_sended($license_data['license_id'])){ echo " disabled='disabled'"; }
echo '>';

$sql = "select * from timexp_licenses_types order by license_type_description_".$AppUI->user_locale;
$result = mysql_query($sql);

while ($row = mysql_fetch_array($result)){
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];
	$type_id = $row['license_type_id'];
	
	$option = '<option value="'.$type_id.'"';
	if($type_id == $license_data['license_type']){ $option.="selected='selected'"; }
	$option .= '>'.$type_desc.'</option>';
	
	echo $option;
}

echo '</select>';
return null;
}

//------------------------------------------------------------------------------------------------

function verify_user_id( $id_licencia ){
global $AppUI;
		
$sql="select license_creator, license_status from timexp_licenses where license_id = ".$id_licencia;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$user_id=$row['license_creator'];
$license_status=$row['license_status'];

if($user_id == $AppUI->user_id &&($license_status==1 || $license_status==0)) { return true; }
else { return false; }
}

?>