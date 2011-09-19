<?php 
//$AppUI->savePlace();
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar; 

$titleBlock = new CTitleBlock( 'New Licence', 'timexp.gif', $m, "timexp.index" );

$titleBlock->show();

if($_REQUEST["saved"]==1){
 	 $AppUI->setMsg( 'added' , UI_MSG_OK, true );
}
   
if($_REQUEST["error"]==1){
 	$AppUI->setMsg( 'check_fields', UI_MSG_ERROR );
}
?>

<script language="JavaScript"><!--
<?php

echo "var fecini$timexp_type = new Array();\n";
echo "var fecfin$timexp_type = new Array();\n";
echo "var today = '".date("Ymd")."';\n";

?>

function popTSCalendar<?php echo $timexp_type;?>( field ){
	calendarField = field;
	idate = eval( 'document.AddLicences.timesheet_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendar<?php echo $timexp_type;?>&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setTSCalendar<?php echo $timexp_type;?>( idate, fdate ) {

		fld_date = eval( 'document.AddLicences.timesheet_' + calendarField );
		fld_fdate = eval( 'document.AddLicences.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;

}


function submitIT() {
	var f = document.AddLicences;
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
		document.forms["AddLicences"].submit();
	}
}
//-->
</script>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
 <form name="AddLicences" action="/index.php?m=timexp&a=do_licence_add&dialog=1&suppressLogo=1" method="POST" enctype="multipart/form-data">
  <tr bgcolor="White"><td height="2" colspan="15"></td></tr>
  <tr><td><br></td></tr>
  <tr>
   <td width=20>
   </td>
   <td width=20% colspan=3>
    <?php echo $AppUI->_("From");?>:
    <input type="hidden" name="timesheet_start_date" value="">
    <input type=text name="start_date" class="text" disabled="disabled" size="10" value="">
    <a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('start_date')" id="cmd_start_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <?php echo $AppUI->_("To");?>:
    <input type="hidden" name="timesheet_end_date" value="">
    <input type=text name="end_date" class="text" disabled="disabled" size="10" value="">
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
	  <option value="Licencia">Licencia</option>
	  <option value="Vacaciones">Vacaciones</option>
	  <option value="Examen">Examen</option>
	  <option value="Enfermedad">Enfermedad</option>
	</select>
   </td>
   <td width="10%">
    <?php echo $AppUI->_("Comments");?>:
	</td><td rowspan="2" colspan="3">
    <textarea name="comentarios" rows=4 cols=35></textarea>
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
     <input type="button" class="button" value="<?php echo $AppUI->_("Cancel"); ?>" onClick="javascript:window.close();">
   </td>
   <td align=right>
    <input type="button" class="button" value="<?php echo $AppUI->_("Save"); ?>" onclick="validate();">
   </td>
  </tr>
  <tr><td><br></td></tr>
  <tr><td><br></td></tr>
 </table>
 </table>
</form>
</body>
</html>