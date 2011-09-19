<?php 
//$AppUI->savePlace();
global $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets, $project, $status, $filtrar; 

$titleBlock = new CTitleBlock( 'New License', 'timexp.gif', $m, "timexp.index" );

$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
$titleBlock->addCrumb( "?m=timexp&a=vw_myday", "my daily view" );

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
	idate = eval( 'document.Addlicenses.timesheet_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendar<?php echo $timexp_type;?>&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setTSCalendar<?php echo $timexp_type;?>( idate, fdate ) {

		fld_date = eval( 'document.Addlicenses.timesheet_' + calendarField );
		fld_fdate = eval( 'document.Addlicenses.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;

}


function submitIT() {
	var f = document.Addlicenses;
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
	var Error=false;
	var fechaInicio = document.getElementById("timesheet_start_date").value;
	var fechaFin = document.getElementById("timesheet_end_date").value;
	
	//Verificar fecha fin > fehca inicio
	if(fechaInicio > fechaFin){		
		alert("<?php echo $AppUI->_('timexpInvalidToDate3');?>");
		Error=true;
	}
	
	if(!Error){
		document.forms["Addlicenses"].submit();
	}
}
//-->
</script>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
 <form name="Addlicenses" action="./index.php?m=timexp&a=do_license_add&dialog=1&suppressLogo=1" method="POST" enctype="multipart/form-data">
  <tr bgcolor="White"><td height="2" colspan="15"></td></tr>
  <tr><td><br></td></tr>
  <tr>
   <td width=20>
   </td>
   <td width=20% colspan=3>
    <?php echo $AppUI->_("From");?>:
    <input type="hidden" name="timesheet_start_date" id="timesheet_start_date" value="">
    <input type=text name="start_date" class="text" disabled="disabled" size="10" value="">
    <a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('start_date')" id="cmd_start_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <?php echo $AppUI->_("To");?>:
    <input type="hidden" name="timesheet_end_date" id="timesheet_end_date" value="">
    <input type=text name="end_date" class="text" disabled="disabled" size="10" value="">
    <a href="#" onClick="popTSCalendar<?php echo $timexp_type;?>('end_date')" id="cmd_end_date<?php echo $timexp_type;?>"><img src=images/calendar.gif border=0 alt="<?php echo $AppUI->_('Calendar');?>"></a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" class="button" value="<?php echo $AppUI->_("reset");?>" onclick="cleanFields()">
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
	<?php combo_types() ?>
   </td>
   <td width="10%" align="right">
    <?php echo $AppUI->_("Comments");?>:&nbsp;
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
    <?php echo $AppUI->_("Certificate")."s";?>
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
   <?php if(isset($_GET["saved"]))
   			$nav = "javascript:window.location='./index.php?m=timexp';";
   		 else 
   		 	$nav = "javascript:history.back();"; ?>
     <input type="button" class="button" value="<?php echo $AppUI->_("cancel"); ?>" onClick="<?=$nav?>">
   </td>
   <td align=right>
    <input type="button" class="button" value="<?php echo $AppUI->_("save"); ?>" onClick="validate();">
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

function combo_types(){
global $AppUI;
echo '<select name="licenseType" size="1">';

$sql = "select * from timexp_licenses_types order by license_type_description_".$AppUI->user_locale;
$result = mysql_query($sql);

while ($row = mysql_fetch_array($result)){
	$type_desc = $row['license_type_description_'.$AppUI->user_locale];
	$type_id = $row['license_type_id'];
	
	echo '<option value='.$type_id.'>'.$type_desc.'</option>';
}

echo '</select>';
return null;
}

?>