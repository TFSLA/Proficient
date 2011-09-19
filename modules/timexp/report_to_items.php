<?php
global $xajax;

$test = new CDate();
$df = $AppUI->getPref('SHDATEFORMAT');

if (!(function_exists("mkFromTo"))){
	function mkFromTo($from_hora, $to_hora, $AppUI){

		if(date('H')==0) $from_hour = '23';
		else $from_hour = (string)(date("H") - 1);
		
		$from_min = (string)(date("i"));
		$to_hour = (string)(date("H"));
		$to_min = (string)(date("i"));

		?>
		<? echo $AppUI->_("From") ; ?>
		
			<select name='from_hour' size="1" class="text" onchange="calculate();">
				<?php mkOption (00, 23, $from_hour ); ?>
			</select>:
			<select name='from_min' size="1" class="text" onchange="calculate();">
				<?php mkOption (00, 59, $from_min ); ?>
			</select> 
			&nbsp;&nbsp;
			<? echo $AppUI->_("To") ; ?>
			<select name='to_hour' size="1" class="text" onchange="calculate();">
				<?php mkOption (00, 23, $to_hour ); ?>
			</select>:
			<select name='to_min' size="1" class="text" onchange="calculate();">
				<?php mkOption (00, 59, $to_min ); ?>
			</select>
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

if(!function_exists("echo_internal_types_combo")){
	function echo_internal_types_combo(){
		global $AppUI;
		
		$sel="";
		$loc = $AppUI->user_locale;
		$sql = "SELECT * FROM timexp_exp";
		$result = mysql_query($sql);
		
		echo '<select name="internalTypes" class="text">';
		
		while ($row = mysql_fetch_array($result)) {
			//if($row['id_expense']==2) $sel="selected";
			echo '<option value="'.$row['id_expense'].'" '.$sel.' >'.$row['descrip_'.$loc].'</option>';
   			$sel="";
		}
		
   		echo '</select>';
	}
}

?>

<input type="hidden" name="user_type" value="1">
  <?  /*------------------- Form para carga de horas --------- */ ?>
<div id="add_hours" name="add_hours" style='display:none;position:absolute;padding:0px;width:820px;background-color: #E9E9E9; left: 25%; top: 40%; border:1px solid;'>
<form name="addHours" action="POST">
	<input type="hidden" name="todo_id" id="todo_id" value="">
	<input type="hidden" name="is_internal" id="is_internal" value="">
	<input type="hidden" name="document_type" id="document_type" value="">
	<input type="hidden" name="task_id" id="task_id" value="">
	<input type="hidden" name="date_format" value="%d/%m/%Y">
     <table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
			<TR>
				<TD colspan="5" background="images/silver-back_header.jpg">
					<img src='images/silver-logo-small.jpg' alt="Proficient">
				</TD>
			</TR>
     		<TR>
				<TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
				<TD></TD>
				<TD></TD>
				<TD></TD>
				<TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
			</TR>
			<TR>
				<TD colspan="5">
					<hr>
					<br><div id="todo_name"></div><br>
					<hr>
	     		</TD>
			</TR>
			<TR>
				<TD colspan="5"><br></TD>
			</TR>
			<TR>
				<TD><br></TD>
				<TD width="40%" nowrap>
					<?php
						$date = new CDate();
						
					?>
					<input type="hidden" name="item_start_date" value="<?php echo date("Ymd"); ?>">
					<input type="hidden" name="item_start_date_format" value="<?php echo $df; ?>">
   					<input type=text name="start_date" class="text" size="10" value="<?php echo $date->format($df); ?>" onblur="isValidDate( 'start_date' );">
    				<a href="javascript:popTSCalendarReportToItems('start_date')" id="cmd_start_date">
    				<img src="images/calendar.gif" border="0" alt="<?php echo $AppUI->_('Calendar');?>"></a>
				</TD>
				<TD colspan="2" align="right">
					<?php mkFromTo($from_hora, $to_hora, $AppUI); ?>&nbsp;&nbsp;
					Total <input type="text" name="total_hours" value="1.00" disabled size="3" class="text">
					&nbsp;&nbsp;&nbsp;
				<span id='billable_combo' style="display:;">
				<? echo $AppUI->_("Billable")?>:
   					<select name="is_billable" class="text">
   						<option value="1"><?php echo $AppUI->_('Yes');?></option>
   						<option value="0"><?php echo $AppUI->_('No');?></option>
   					</select>
   				</span>
   				<span id='internal_types_combo' style="display:none;">
				<? echo $AppUI->_("Type")?>:
				<?php echo_internal_types_combo(); ?>
   				</span>
				</TD>
				<TD><br></TD>
			</TR>
			<TR>
				<TD><br></TD>
			</TR>
			<TR>
				<TD><br></TD>
				<TD><? echo $AppUI->_("Comments")?></TD>
				<TD colspan="2" align="right">
				<textarea style="width:600px" rows="3" name="comments" id="comments" class="text"></textarea> </TD>
				<TD><br></TD>
			</TR>
			<TR>
				<TD><br></TD>
			</TR>
			<TR>
				<TD><br></TD>
				<TD colspan="3" align="right">
					<span id='project_tasks_combo' style="display:none;">
						<select name="project_task_ajax" id="project_task_ajax" style="width:400px;">
							<option value="0">No Data</option>
						</select>
	   				</span>
	   				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php
						if($AppUI->user_locale=="en"){ 
							echo "Add more";
						}else{
							echo "Cargar más";
						}
					?>
					<input type="checkbox" name="keep_reporting" id="keep_reporting">
					<span id='complete_todo_check' style="display:none;">
						<br>
						<span id='itemNameAction'></span><input type="checkbox" name="complete" id="complete">
					</span>
				</TD>
			</TR>
			<TR>
				<TD><br></TD>
				<TD colspan="3" align="right">
					<br>
					<div id="TextoDevuelto"></div>
					<br>
				</TD>
			</TR>
			<tr>
			   <td></td>
			   <td colspan="2" align="left">
			     <input type="button" class="button" value="<?php echo $AppUI->_("Cancel"); ?>" onClick="close_div('add_hours');">
			   </td>
			   <td align="right">
			    <input type="button" class="button" value="<?php echo $AppUI->_("Save"); ?>" onclick="save_hours();">
			   </td>
			   <TD><br></TD>
			</tr>
		</table>
		</form>
  </div>

  <?  /*------------------- Mensaje de procesando --------- */ ?>
  <div id="progress" name="progress" style='display:none;position:absolute;padding:0px;width:350px;height:70px;background-color: #E9E9E9; left: 40%; top: 40%; border:1px solid;'>
     <br><center><b>Cargando, por favor espere un momento...</b></center>
     <br>
     <center><? echo dPshowImage( './images/loadinfo-4.net.gif', 24, 24, '' ); ?></center>
 </div>

 <?  /*------------------- Mensaje de Finalizado --------- */ ?>
  <div id="success" name="success" style='display:none;position:absolute;padding:0px;width:400px;height:100px;background-color: #E9E9E9; left: 40%; top: 40%; border:1px solid;'>
     <center>
     	<input type="button" class="button" value="Volver" onclick="return_to_form();">
     	&nbsp;&nbsp;
     	<input type="button" class="button" value="Cerrar" onclick="close_div('success');">
     </center>
 </div>
 
<script language="javascript">

function isValidDate(strField){
    var bMDVok = true;
    var strMDVparam1 = eval( 'document.addHours.' + strField );
    var strMDVparam2 = eval( 'document.addHours.item_' + strField +'_format');
    var strMDVparam3 = eval( 'document.addHours.item_' + strField );
    if (trim(strMDVparam1.value)!=""){
	    if(setManualDate(strMDVparam1, strMDVparam2, strMDVparam3) ==  false ){
	    	alert("Formato de fecha incorrecto.");
	        bMDVok = false;
	    }
    }
    return bMDVok;
}

function setManualDate(objFrmDate, objFrmFormatDate, objFrmfDate){
	var bOk = true;
	strDate = objFrmDate.value;
	strFormatDate = objFrmFormatDate.value;
	arDate = setDateToFormat(strDate,GetFormatDate(strFormatDate));
	
	if(arDate === false) bOk = false;
	if(validateDate(arDate) && bOk){
	    if(arDate[0].length < 2) arDate[0] = "0" + arDate[0];
	    if(arDate[1].length < 2) arDate[1] = "0" + arDate[1];
	    objFrmfDate.value = arDate[2]+arDate[1]+arDate[0];
	    bOk = true;
	}else{
	    bOk = false;
	}
	return bOk;
}

function popTSCalendarReportToItems( field ){
	calendarField = field;
	idate = eval( 'document.addHours.item_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendarReportToItems&date=' + idate, 'calwin', 'top=150, width=250, height=220, scollbars=false' );
}

function setTSCalendarReportToItems( idate, fdate ) {
		fld_date = eval( 'document.addHours.item_' + calendarField );
		fld_fdate = eval( 'document.addHours.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
}

function calculate() {
	var hours_total;
	f = document.addHours;
	
	hours_total = parseFloat(f.to_hour.value) + parseFloat(f.to_min.value / 60) - parseFloat(f.from_hour.value) - parseFloat(f.from_min.value / 60);
	
	f.total_hours.value = hours_total.toFixed(2);
}

function close_div(div_name){
	document.getElementById(div_name).style.display='none';
}

function return_to_form(){
	close_div('success');
	document.getElementById('add_hours').style.display='';
}

function save_hours(){
	progress_msg('mostrar');
	var f = document.addHours;
	
	var description = f.comments.value;
	var date = f.item_start_date.value;
	var applied_to_id = f.todo_id.value;
	var billable = f.is_billable.value;
	var start_hour = f.from_hour.value;
	var start_min = f.from_min.value;
	var end_hour = f.to_hour.value;
	var end_min = f.to_min.value;
	var total_hours = f.total_hours.value;
	var internal_type = f.internalTypes.options[f.internalTypes.value-1].text;
	var is_internal = f.is_internal.value;
	var document_type_value = f.document_type.value;
	var project_task = f.project_task_ajax.value;
	var error = 0;
	
	/* Valida la fecha*/
	strMDVparam1 = f.start_date;
    strMDVparam2 = f.date_format;
    strMDVparam3 = f.item_start_date;
   
    if(setManualDateReportItems(strMDVparam1, strMDVparam2, strMDVparam3) ==  false ){
        alert("<?php echo $AppUI->_('Invalid format Date'); ?>");
        error = 1;
    }
	
	if((end_hour == start_hour && end_min < start_min) || end_hour < start_hour){
		error = 1;
		alert('<?php echo $AppUI->_('From hour must be less or equal than To hour'); ?>');
	}
	
	if(error == 0){
		xajax_save_data(description, date, applied_to_id, billable, start_hour, start_min, end_hour, end_min, total_hours, is_internal, internal_type, document_type_value, project_task);
		
		progress_msg('-');
		close_div('add_hours');
		if(f.keep_reporting.checked==true){
			document.getElementById('add_hours').style.display='';
			setTimeout("cleanMsg('TextoDevuelto')", 2*1000);
		}else{
			document.getElementById('add_hours').style.display='none';
			if( f.complete.checked == true)
			{
				xajax_completeAssignment(applied_to_id,document_type_value,document.URL);
			}
		}
	}else{
		progress_msg('-');
		document.getElementById('add_hours').style.display='';
	}
}

function redirectUrl(url){
	window.location = url;
}

function cleanMsg(divItem){
	document.getElementById(divItem).innerHTML='';
}

function progress_msg(visibility_st){
	
	var f = document.editFrm;
	
	if(visibility_st == 'mostrar')
	{
        // Muestro el cartel de procesando
		document.getElementById('progress').style.display='';
		document.getElementById('add_hours').style.display='none';
		
		// Me aseguro que no quede el mensaje colgado en caso de error
	   setTimeout("progress_msg('error')", 60*1000);
		
	}else{ 
	  // Oculto el mensaje de error
	  document.getElementById('progress').style.display = "none";
	}
}

function getWindowPosY(){
    positionY = document.body.scrollTop;
    if (positionY < 0) {
        positionY = 0;
    }
    return positionY;
}

function report_hours(todo_id, type, hideComplete){
	var f = document.addHours;
	f.todo_id.value = todo_id;
	document.getElementById('TextoDevuelto').innerHTML="";
	document.getElementById('keep_reporting').checked=false;
	document.getElementById('complete').checked=false;
	document.getElementById('add_hours').style.top = getWindowPosY() + 150;
	document.getElementById('progress').style.top = getWindowPosY() + 150;
	document.getElementById('success').style.top = getWindowPosY() + 150;
	document.getElementById('add_hours').style.display='';
	xajax_set_field_value("todo_name",todo_id,type);
	
	if( hideComplete != 1 )
	{
		if( (type == 't') || (type == 4) || ('<?php echo $_GET["m"] ?>' == 'todo') )
		{
			document.getElementById("itemNameAction").innerHTML = '<?=($AppUI->user_locale=="en" ? "Complete ToDo" : "Completar ToDo")?>';
			document.getElementById("complete_todo_check").style.display = '';
		}
		else if ( (type == 'ta') || (type == 3) || ('<?php echo $_GET["m"] ?>' == 'tasks') )
		{
			document.getElementById("itemNameAction").innerHTML = '<?=($AppUI->user_locale=="en" ? "Complete Task" : "Completar Tarea")?>';
			document.getElementById("complete_todo_check").style.display = '';
		}
		else if ( (type == 'b') || (type == 6) || ('<?php echo $_GET["m"] ?>' == 'webtracking') )
		{
			document.getElementById("itemNameAction").innerHTML = '<?=($AppUI->user_locale=="en" ? "Resolve Bug" : "Resolver Incidencia")?>';
			document.getElementById("complete_todo_check").style.display = '';
		}
	}
	else
		document.getElementById("complete_todo_check").style.display = 'none';
		
	<?php if($_GET['m']=='calendar' || $_GET['m']=='articles' || $_GET['m']=='files' || $_GET['m']=='projects' || $_GET['m']=='myassigments' || $_GET['m']=='pipeline') {?>
		xajax_is_internal("is_internal",todo_id,type);<?php 
	} ?>
}

function setManualDateReportItems(objFrmDate, objFrmFormatDate, objFrmfDate){
    var bOk = true;
    strDate = objFrmDate.value;
    strFormatDate = objFrmFormatDate.value;

    arDate = setDateToFormat(strDate,GetFormatDate(strFormatDate));
    if(arDate === false) bOk = false;
    if(validateDate(arDate) && bOk){
        if(arDate[0].length < 2) arDate[0] = "0" + arDate[0];
        if(arDate[1].length < 2) arDate[1] = "0" + arDate[1];
        objFrmfDate.value = arDate[2]+arDate[1]+arDate[0];
        bOk = true;
    }else{
        bOk = false;
    }
    return bOk;
}

function setDateToFormat(strDate, strFormat){
    arDate = new Array();
	bstrDateOk = false;//si la fecha tiene 3 partes
    strSeparadores = new Array();
    strSeparadores[0]="/";
    strSeparadores[1]=".";

    for(i=0; i < strSeparadores.length; i++){
        if(strDate.indexOf(strSeparadores[i]) > -1){
            arStrDate = strDate.split(strSeparadores[i]);
            if(arStrDate.length > 2){ 
				bstrDateOk = true;
				continue;
            }
        }
    }

	if(!bstrDateOk) return bstrDateOk;//salgo si la fecha esta mal ingresada

    switch(strFormat){
        case "ddmmyyyy":
            arDate[0] = arStrDate[0];
            arDate[1] = arStrDate[1];
            arDate[2] = arStrDate[2];
            break;
        case "mmddyyyy":
            arDate[0] = arStrDate[1];
            arDate[1] = arStrDate[0];
            arDate[2] = arStrDate[2];
            break;
        case false:
            return false;
            break;
    }
    return arDate;
}

function GetFormatDate(strDateFormat){
    var strValue = false;
    strSeparadores = new Array();

    strSeparadores[0]="/";
    strSeparadores[1]=".";

    if(strDateFormat == "") return strValue;

    for(i=0; i < strSeparadores.length; i++){
        if(strDateFormat.indexOf(strSeparadores[i]) > -1){
            arStrDate = strDateFormat.split(strSeparadores[i]);
            if(arStrDate.length > 2) continue;
        }
    }

    if(arStrDate != false){
        if(arStrDate[0].indexOf("d") > -1 && arStrDate[1].indexOf("m") > -1){
            strValue = "ddmmyyyy";
        }else if(arStrDate[0].indexOf("m") > -1 && arStrDate[1].indexOf("d") > -1){
            strValue = "mmddyyyy";
        }
    }

    return strValue;
}

function validateDate(arDate){
	var rta = false;
    arMonth = new Array();
    arMonth[1]="31";
    arMonth[2]="28";
    arMonth[3]="31";
    arMonth[4]="30";
    arMonth[5]="31";
    arMonth[6]="30";
    arMonth[7]="31";
    arMonth[8]="31";
    arMonth[9]="30";
    arMonth[10]="31";
    arMonth[11]="30";
    arMonth[12]="31";

    if(arDate != false){
        intDay = arDate[0];
        intMonth = arDate[1];
        intYear = arDate[2];
    }else{
        return rta;
    }

    intDay = parseInt(parseFloat(intDay));
    intMonth = parseInt(parseFloat(intMonth));
    strYear = intYear;
    intYear = parseInt(parseFloat(intYear));
    rta = true;

    if(intDay < 1 || intDay > 31) rta = false;
    if(intMonth < 1 || intMonth > 12) rta = false;
    if(strYear.length != 4) rta = false;

    if(intYear % 4 == 0) arMonth[2] = "29";

    if(parseInt(arMonth[intMonth]) < intDay) rta=false;

    return rta;
}

function select_ajax(company, department, user_type)
{
   xajax_addSelect_Departments('user_department', company ,department, '', '', '<?=$AppUI->_('All')?>');	
}

</script>

<script type="text/javascript">
  function addOption(selectId, val, txt, sel) {
    var objOption = new Option(txt, val,false,sel);
     document.getElementById(selectId).options.add(objOption);
   }
</script>
