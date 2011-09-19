<?php 
//$AppUI->savePlace();
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar; 

$titleBlock = new CTitleBlock( 'Edit Licence', 'timexp.gif', $m, "timexp.index" );

$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday", "my daily view" );
$titleBlock->addCrumb( "?m=timexp&a=mysheets&tab=2", "my licences" );

$titleBlock->show();

$sql = "select * from timexp_licences where licence_id = ".$_REQUEST['id'];
$result = mysql_query($sql);
$licence_data = mysql_fetch_array($result);

$start_date = new CDate($licence_data["licence_from_date"]);
$end_date = new CDate($licence_data["licence_to_date"]);
$df = $AppUI->getPref('SHDATEFORMAT');
?>

<script language="JavaScript"><!--
<?php

echo "var fecini$timexp_type = new Array();\n";
echo "var fecfin$timexp_type = new Array();\n";
echo "var today = '".date("Ymd")."';\n";

?>
function showJustification(justificationId){
	    	window.open( '/index.php?m=timexp&a=do_justification_show&supressHeaders=yes&id='+justificationId+'&dialog=1&suppressLogo=1','calwin', 'scrollbars=false');
}

function popTSCalendar<?php echo $timexp_type;?>( field ){
	calendarField = field;
	idate = eval( 'document.EditLicence.timesheet_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendar<?php echo $timexp_type;?>&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setTSCalendar<?php echo $timexp_type;?>( idate, fdate ) {
		fld_date = eval( 'document.EditLicence.timesheet_' + calendarField );
		fld_fdate = eval( 'document.EditLicence.' + calendarField );
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
	
	//Verificar fecha inicio >= hoy
	if(fechaInicio < today){
		alert("<?php echo $AppUI->_('timexpInvalidFromDate');?>");
		Error=true;
	}
	
	if(!Error){
		document.forms["EditLicence"].submit();
	}
}
//-->
</script>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
 <form name="EditLicence" action="/index.php?m=timexp&a=do_licence_update" method="POST" enctype="multipart/form-data">
  <tr bgcolor="White"><td height="2" colspan="15"></td></tr>
  <tr><td><br></td></tr>
  <tr>
   <td width=20>
   </td>
   <td width=20% colspan=3>
    <input type="hidden" name="licence_id" value="<?php echo $licence_data['licence_id']; ?>">
   
    <?php echo $AppUI->_("From");?>:
    <input type="hidden" name="timesheet_start_date" value=<?php 	
	    $start_date_formatted = $licence_data["licence_from_date"];
		$start_date_formatted = ereg_replace("-","",$start_date_formatted);
		$start_date_formatted = substr($start_date_formatted,0,8);
		echo $start_date_formatted; ?>>
    <input type=text name="start_date" class="text" disabled="disabled" size="10" value="<?php echo $start_date->format($df); ?>">
    <a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('start_date')" id="cmd_start_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <?php echo $AppUI->_("To");?>:
    <input type="hidden" name="timesheet_end_date" value=<?php
     	$end_date_formatted = $licence_data["licence_to_date"];
		$end_date_formatted = ereg_replace("-","",$end_date_formatted);
		$end_date_formatted = substr($end_date_formatted,0,8);
		echo $end_date_formatted; ?>>
    <input type=text name="end_date" class="text" disabled="disabled" size="10" value="<?php echo $end_date->format($df); ?>">
    <a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('end_date')" id="cmd_end_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" class="button" value="<?php echo $AppUI->_("Reset");?>" onclick="cleanFields()">
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
   <td align=left width="25%" valign="top">
    <?php echo $AppUI->_("Type");?>:
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <select name="licenceType" size="1">
	  <option <?php if($licence_data['licence_type']=='Licencia') {echo "selected='selected'";} ?> value="Licencia">Licencia</option>
	  <option <?php if($licence_data['licence_type']=='Vacaciones') {echo "selected='selected'";} ?> value="Vacaciones">Vacaciones</option>
	  <option <?php if($licence_data['licence_type']=='Examen') {echo "selected='selected'";} ?> value="Examen">Examen</option>
	  <option <?php if($licence_data['licence_type']=='Enfermedad') {echo "selected='selected'";} ?> value="Enfermedad">Enfermedad</option>
	</select>
   </td>
   <td width="10%">
    <?php echo $AppUI->_("Comments");?>:
	</td><td rowspan="2" colspan="3">
    <textarea name="comentarios" rows=4 cols=35><?php echo $licence_data['licence_description']; ?></textarea>
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
    <?php echo $AppUI->_("Justifications");?>
    [ <a href="#" onclick="new_line('Adj'); return false;">
     <img src="images/mas.png" border="0" width="8" height="8" 
     alt="<?php echo $AppUI->_("Add Justification"); ?>"></a> / 
     <a href="#" onclick="remove_line('Adj'); return false;">
     <img src="images/menos.png" border="0" width="8" height="8" 
     alt="<?php echo $AppUI->_("Remove Justification"); ?>"></a> ]
    </td>
  </tr>
  <tr>
   <td colspan="15"><hr><br>
   </td>
  </tr>
  <tr>
   <td></td>
     <?php 
       	$sql = "select * from timexp_licences_justifications where 
			justification_related_licence = ".$_REQUEST['id'];
       	$result = mysql_query($sql);
       	
       if(mysql_numrows($result)==0){
       		echo "<td>".$AppUI->_("No previous saved Justifications available")."<br><br></td>";
       }
       else {
       	while ($justification = mysql_fetch_array($result)) {
			echo "<tr><td></td><td colspan='5'>";
			echo "<a href = '#' onclick = 'showJustification(".$justification["justification_id"].
					");'>".$justification["justification_name"]."</a>";
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
     <input type="button" class="button" value="<?php echo $AppUI->_("Back"); ?>" onClick="javascript:history.back();">
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