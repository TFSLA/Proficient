<?php 
//$AppUI->savePlace();
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar; 

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


function submitIT<?php echo $timexp_type;?>(){
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
}
//--></script>

<html>
 <head>
  <title>Proficient :: Licencias</title>
 </head>
<body>
<form name="AddLicences" >
 <table cellspacing="1" cellpadding="0" border="0" width="100%">
 <tr>
 <td height=4></td>
 </tr>
 <tr>
   <table cellspacing="1" cellpadding="2" border="0" width="100%" class="">
   <td colspan="10" class="tableForm_bg" height='20'>
    <strong><?php echo $AppUI->_("Licences"); ?></strong>
   </td>
  </tr>
  <tr>
   <td><br><br>
   </td>
  </tr> 
  <tr>
   <td width=20>
   </td>
   <td width=20% colspan=3>
    <font face="Verdana" size=2><?php echo $AppUI->_("From");?>:</font>
    <input type="hidden" name="timesheet_start_date" value="">
    <input type=text name="start_date" class="text" disabled="disabled" size="10" value="">
    <a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('start_date')" id="cmd_start_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <font face="Verdana" size=2><?php echo $AppUI->_("To");?>:</font>
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
   <td align=left size=10%>
    <font face="Verdana" size=2>Certificado:</font>
   </td>
   <td width=5>
   </td>
   <td align=left>
        <input tabindex="8" name="file" type="file" size="60">
   </td>
   <td colspan=2>
   </td>
  </tr>
  <tr>
   <td><br><br>
   </td>
  </tr>
  <tr>
   <td>
   </td>
   <td align=left>
    <font face="Verdana" size=2><?php echo $AppUI->_("Comments");?>:</font>
   </td>
   <td align=left colspan=2>
    <textarea name="Comentarios" rows=4 cols=35></textarea>
   </td>
  </tr>
  <tr>
   <td><br><br>
   </td>
  </tr>
  <tr>
   <td>
   </td>
   <td align=left size=10%>
    <font face="Verdana" size=2><?php echo $AppUI->_("Type");?></font>
   </td>
   <td width=5>
   </td>
   <td align=left>
    <select name="Licencia_Tipo">
	<option value=0>Licencia</option>
	<option value=1>Vacaciones</option>
	<option value=2>Exámen</option>
	<option value=3>Enfermedad</option>
   </td>
   <td>
   </td>
  </tr>
  <tr>
   <td><br><br><br>
   </td>
  </tr>
  <tr>
   <td></td>
   <td colspan="2">
     <input type="button" class="button" value="Cancelar" onClick="javascript:history.back(-1);">
   </td>
   <td align=right>
    <input type="button" class="button" value="Guardar">
   </td>
  </tr>
 </table>
 </table>
</form>
</body>
</html>