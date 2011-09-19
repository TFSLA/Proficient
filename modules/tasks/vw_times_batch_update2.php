<?php 

require_once( $AppUI->getModuleClass( 'timexp' ) );
global  $task_id, $bug_id, $obj, $percent,$timexp_id, $rnd_type, $timexp_types, $external, $timexp_applied_to_types, $billables,$hideTitleBlock,$dialog,$rnd_type,$external;
$external = 1;
$rnd_type="1";




$canEdit = true;
$accessLog = PERM_EDIT;

//var_dump($rnd_type);
//var_dump($timexp_types);
if (!$rnd_type || !isset($timexp_types[$rnd_type])){
		$AppUI->setMsg( "timexp" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

$timexp_id = intval( dPgetParam( $_GET, 'timexp_id', 0 ) );

// si es la ventana popup sólo se puede agregar, no editar
if ($is_popup){
	unset($timexp_id);
}

$project="-1";
$project_name = "";
$redirect_url = "";
$timexp = new CTimExp();
$new = true;
//Obtengo el tipo de aplicación
$rnd_app_to = "1";
if (isset($bug_id)) { $rnd_app_to = "2";}
if (isset($task_id)) { $rnd_app_to = "1";}

$timexp->timexp_applied_to_type =  $rnd_app_to;
//Pongo el id al cual se aplica la rendicion
$timexp->timexp_applied_to_id =  $task_id ? $task_id : ($bug_id ? $bug_id : "NULL");




//asigno el tipo de carga Hora o Gasto
$timexp->timexp_type = $rnd_type;

//valores por defecto
$timexp->timexp_billable = 1;
if ($rnd_app_to=="1")	$timexp->timexp_contribute_task_completion = "1";
if ($rnd_app_to=="3")	$timexp->timexp_billable = 0;


$titleaction = $AppUI->_("Add ");
if ($dialog==1){
	$redirect_url = $_SERVER['QUERY_STRING'];
}


if ($rnd_type=="1"){
	$label_value = "Hours";
	$start_time = intval( $timexp->timexp_start_time ) ? new CDate( $timexp->timexp_start_time ) : "";
	$end_time = intval( $timexp->timexp_end_time ) ? new CDate( $timexp->timexp_end_time ) : "";
	
	//Time arrays for selects
	$start = intval( substr($AppUI->getConfig('cal_day_start'), 0, 2 ) );
	$end   = intval( substr($AppUI->getConfig('cal_day_end'), 0, 2 ) );
	$inc   = $AppUI->getConfig('cal_day_increment');
	if ($start === null ) $start = 8;
	if ($end   === null ) $end = 17;
	if ($inc   === null)  $inc = 15;
	$hours = array();
	$hours["NULL"]="";
	for ( $hour = $start; $hour < $end + 1; $hour++ ) {
		for ( $min = 0 ; $min < 60; $min += $inc ) {
			$current_key = sprintf("%02d:%02d",$hour,$min);
			if (stristr($AppUI->getPref('TIMEFORMAT'), "%p") ){
				$hours[$current_key] = sprintf("%02d:%02d", $hour % 12 ,$min);
				$hours[$current_key] .= " ".(floor($hour / 12) == 0 ? "am" : "pm");
			}else{
				$hours[$current_key] = sprintf("%02d:%02d", $hour ,$min);
			}
		}	
		
	}

}else{
	$label_value = "Cost";
}

$sufix = $timexp_types[$rnd_type];
$titleobject = $AppUI->_($sufix);

if (!($canEdit && ($accessLog==PERM_EDIT))) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


$df = $AppUI->getPref( 'SHDATEFORMAT' );
$timexp_date = new CDate( $timexp->timexp_date );

$spvMode = $timexp->canSupervise();

$today = new CDate();

$date_from = new CDate();
if($task_id){
	$project_id = db_loadResult("
				select task_project from tasks where task_id='$task_id'");
	
	// cmo fecha desde tomo la ultima fecha de carga de horas + 1 dia
	$sql = "select max(timexp_date)
			from timexp te 
			
			where timexp_type = 1 
			and timexp_applied_to_type = 1 
			and timexp_applied_to_id = '$task_id'";
	$last_date = db_loadResult($sql);
	if ($last_date){
		$date_from = new CDate($last_date);
		$date_from->addDays(1);
	}else{
		//sino obtengo la fecha de inicio de la tarea
		$sql = "select task_start_date from tasks where task_id = '$task_id' ";
		$last_date = db_loadResult($sql);
		if ($last_date){	
			$date_from = new CDate($last_date);
		}
	}
}
if($date_from->format(FMT_TIMESTAMP) > $today->format(FMT_TIMESTAMP)){
	$date_from = new CDate();
}

$today = $today->format(FMT_TIMESTAMP_DATE);

$day_names = array("Sunday","Monday", "Tuesday","Wednesday","Thursday","Friday","Saturday");
for($i=0; $i < count($day_names); $i++){
	$day_names[$i] = $AppUI->_($day_names[$i]);
}
$day_names = '"'.implode('", "', $day_names).'"';


?>

<!-- TIMER RELATED SCRIPTS -->
<script language="JavaScript">
var today		  = "<?php echo $today;?>";
var dates		  = new Array();
var dates_ts	  = new Array();
var hours		  = new Array();
var start_hours   = new Array();
var end_hours     = new Array();
var day_names 	  = new Array(<?php echo $day_names;?>)

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.batchFrm.timexp_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	if(idate>today){
		alert("<?php echo $AppUI->_("timexpInvalidDate");?>");
	}else{
		fld_date = eval( 'document.batchFrm.timexp_' + calendarField );
		fld_fdate = eval( 'document.batchFrm.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
	}
}

function checkvalue(obj){
	obj.value = trim(obj.value);
	var valor = parseFloat(obj.value);
	if (obj.value.length > 0){
		if (isNaN(valor)){
			alert("<?php echo $AppUI->_('timexpValue');?>");
			rta = false;
			obj.focus();
		}else{
			obj.value = valor;
		}
	}
}

<?php
echo $timexp_date->buildManualDateValidationJS();
?>

function validateTimexp(){
	var frm = document.forms["batchFrm"];
	var rta = true;
	frm.timexp_billable.value = frm.timexp_billable_box.options[frm.timexp_billable_box.selectedIndex].value;
    strMDVparam1 = frm.from_date;
    strMDVparam2 = frm.timexp_from_date_format;
    strMDVparam3 = frm.timexp_from_date;

    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpInvalidFromDate');?>");
        rta = false;
    }

    strMDVparam1 = frm.to_date;
    strMDVparam2 = frm.timexp_to_date_format;
    strMDVparam3 = frm.timexp_to_date;

    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpInvalidToDate');?>");
        rta = false;
    }   
	if(frm.timexp_to_date.value<frm.timexp_from_date.value){
		alert("<?php echo $AppUI->_("timexpInvalidDates");?>");
		rta = false;
	}        
    if (frm.timexp_applied_to_type.value != "1"){
		frm.timexp_contribute_task_completion.value = "0";
	}
	if (frm.timexp_applied_to_type.value == "3"){
		frm.timexp_billable.value = "0";
	}
	var valor = parseFloat(frm.timexp_value.value);
	if (isNaN(valor) || valor <= 0){
		alert("<?php echo $AppUI->_('timexpValue');?>");
		rta = false;
		frm.timexp_value.focus();
	}
	var tename = trim(frm.timexp_name.value);
	if (tename.length == 0){
		alert("<?php echo $AppUI->_('timexpName');?>");
		rta = false;
		frm.timexp_name.focus();	
	}
	
	if (dates.length == 0){
		alert("<?php echo $AppUI->_('timexpNoDates');?>");
		rta = false;
	}	
	
	if (frm.timexp_applied_to_type.value != "3"){
		var appid = parseFloat(frm.timexp_applied_to_id.value);
		if(isNaN(appid) || appid <= 0){
			if (frm.timexp_applied_to_type.value==1){
				alert("<?php echo $AppUI->_("timexpAppTaskID");?>");
			}else{
				alert("<?php echo $AppUI->_("timexpAppBugID");?>");
			}
			
			rta = false;
		}
	}
	

	if(frm.timexp_from_date.value>today){
		alert("<?php echo $AppUI->_("timexpInvalidDate");?>");
		rta = false;
	}
	if(frm.timexp_to_date.value>today){
		alert("<?php echo $AppUI->_("timexpInvalidDate");?>");
		rta = false;
	}
	if(frm.timexp_to_date.value<frm.timexp_from_date.value){
		alert("<?php echo $AppUI->_("timexpInvalidDate");?>");
		rta = false;
	}
<?php
//validaciones para horas
if ($rnd_type==1){	
	
//validaciones para expenses
}elseif ($rnd_type==2){?>



<? } ?>



	return rta;
}

function CheckTime(str)
{
hora=str.value

if (hora=='') {return}
if (hora.length>5) {alert("<?php echo $AppUI->_('timexpInvalidstartime');?>");return}
if (hora.length!=5) {alert("<?php echo $AppUI->_('timexpInvalidstartime');?>");return}
a=hora.charAt(0) //<=2
b=hora.charAt(1) //<4
c=hora.charAt(2) //:
d=hora.charAt(3) //<=5
if ((a==2 && b>3) || (a>2)) {alert("<?php echo $AppUI->_('timexpInvalidstartime');?>");return}
if (d>5) {alert("<?php echo $AppUI->_('timexpInvalidstartime');?>");return}
if (c!=':') {alert("<?php echo $AppUI->_('timexpInvalidstartime');?>");return}

}

function generate_times(){
	var frm = document.forms["batchFrm"];
	var rta = true;
	
	strMDVparam1 = frm.from_date;
    strMDVparam2 = frm.timexp_from_date_format;
    strMDVparam3 = frm.timexp_from_date;

    // Valida la fecha de inicio //
    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpInvalidFromDate');?>");
        rta = false;
    }

    strMDVparam1 = frm.to_date;
    strMDVparam2 = frm.timexp_to_date_format;
    strMDVparam3 = frm.timexp_to_date;

    // Valida la fecha de fin //
    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpInvalidToDate');?>");
        rta = false;
    }   
    

    // Me fijo que la fecha final sea mayor que la inicial //
	if (frm.timexp_from_date.value > frm.timexp_to_date.value){
	   alert("<?php echo $AppUI->_('timexpInvalidToDate');?>");
       rta = false;
	}
    
	// Valida que ingresen la cantidad de horas //
	var valor = parseFloat(frm.timexp_value.value);
	if (isNaN(valor) || valor <= 0){
		alert("<?php echo $AppUI->_('timexpValue');?>");
		rta = false;
		frm.timexp_value.focus();
	}
	
	//  se fija que ingrese hora de inicio //
	if (trim(frm.timexp_start_time.value).length<1)
		{
		alert("<?php echo $AppUI->_('timexpInvalidstartime2');?>");
		rta = false;
		frm.timexp_start_time.focus();
		}


    // Se fija que si usa la cant por dia no exeda las 24 //
	if((frm.hours_type.value==1)&&(valor>24))
	{
	alert("<?php echo $AppUI->_('timexpInvalidstartime3');?>");
	rta = false;
	frm.timexp_value.focus();
	}

    
	// Verifica que la suma de la hora de inicio y la cantidad de horas no exeda el dia //
    if(frm.hours_type.value==1)
	{   
	 
	 a=frm.timexp_start_time.value.substr(0,2);
	 b=frm.timexp_start_time.value.substr(3,2); 
	   
	 var c = parseFloat(frm.timexp_value.value);
	 var h_inicio = parseFloat(a);

	temp = h_inicio + c;

			if (temp >24)
			{
			alert("<?php echo $AppUI->_('timexpValue');?>");
	        rta = false;
			}

			if ((temp ==24)&&(b>0))
			{
            alert("<?php echo $AppUI->_('timexpValue');?>");
	        rta = false;
			}
	}
	else
	{
	var resta = "";
	var a = parseInt(frm.timexp_start_time.value.substr(0,2)); // Hora de inicio
	var b = parseFloat(frm.timexp_value.value);  // Cantidad de horas

	resta = frm.timexp_to_date.value-frm.timexp_from_date.value;

    
	    if (resta==0)
		{
		 h_temp = a + b;
		}
		else
		{
	    temp = parseInt(b/(resta-1))+1;
		h_temp = a + temp;
		}
    
	

        if (h_temp>24)
		{
         alert("<?php echo $AppUI->_('timexpValue');?>");
	     rta = false;
		}

	}

   
	if(frm.timexp_from_date.value>today || trim(frm.timexp_from_date.value)==""){
		alert("<?php echo $AppUI->_('timexpInvalidFromDate');?>");
		rta = false;
	}
	
	if(frm.timexp_to_date.value>today || trim(frm.timexp_to_date.value)==""){
		alert("<?php echo $AppUI->_('timexpInvalidToDate');?>");
		rta = false;
	}
	
	

	if(rta){
        
        // Usa la misma cantidad de horas en cada dia// 
		var tipo_generador = 1;
		
		if (tipo_generador == 1){
			var fme = document.getElementById("calendar_getter");
			var calFrm = document.getCal;
			calFrm.from.value = frm.timexp_from_date.value;
			calFrm.to.value = frm.timexp_to_date.value;
			calFrm.submit();
		
		}else{
	       
			var datePat = /^(\d{4})(\d{2})(\d{2})$/;
			var strDateFormat = frm.timexp_from_date_format.value;
			var matchArray1 = frm.timexp_from_date.value.match(datePat);
			var from = new Date(matchArray1[1], matchArray1[2], matchArray1[3], 0, 0, 0);
	
			var matchArray2 = frm.timexp_to_date.value.match(datePat);
			var to = new Date(matchArray2[1], matchArray2[2], matchArray2[3], 0, 0, 0);		
			var one_day = 1000 * 60 * 60 * 24;
			var to_ms = to.getTime();
			var from_ms = from.getTime();
			var dif = ((to_ms + one_day) - from_ms)/one_day;
			var hour_day = Math.round((valor / dif)*100)/100;


			dates  = new Array();
			dates_ts  = new Array();
			hours  = new Array();
			for (var i=0; i<dif; i++){
				var curdate = strDateFormat;
				curdate = curdate.replace("%d", (from.getDate().toString().length==1?"0":"") + from.getDate().toString());
				curdate = curdate.replace("%m", (from.getMonth().toString().length==1?"0":"") + from.getMonth().toString());
				curdate = curdate.replace("%Y", from.getFullYear().toString());
				dates[i] = day_names[(from.getDay()+4)%7] +" " + curdate;
				dates_ts[i] = from.getFullYear().toString() + (from.getMonth().toString().length==1?"0":"") + from.getMonth().toString() + (from.getDate().toString().length==1?"0":"") + from.getDate().toString(); 
				hours[i] = hour_day;
				from.setTime(from.getTime() + one_day);
			}
			frm.timexp_dates.value = dates_ts.join(",");
			frm.timexp_hours.value = hours.join(",");
		}
	}else{
		
	}
	build_table_times();
}

function setDates(strdates, strdates_ts){
	var frm = document.forms["batchFrm"];
	var gen_type = frm.hours_type.selectedIndex + 1;
	dates = strdates.split(",");
	dates_ts = strdates_ts.split(",");
	
	
	var valor = parseFloat(frm.timexp_value.value);
	if (gen_type == 1) 
		var hour_day = Math.round((valor / dates.length)*100)/100;
	else 
		var hour_day = Math.round(valor*100)/100;

	hours = new Array();
	for (var i=0; i<dates.length; i++){
		hours[i] = hour_day;
	}
	frm.timexp_dates.value = dates_ts.join(",");
	frm.timexp_hours.value = hours.join(",");
	build_table_times();
}
function updateHour(i, obj){
	var frm = document.forms["batchFrm"];
	var hour = parseFloat(obj.value);

	if(!isNaN(hour)){
		hours[i] = hour;
		recalculateTotal();
	}else{
		alert('<?php echo $AppUI->_('timexpValue');?>');
		obj.value = hours[i];
	}	
}

function recalculateTotal(){
	var frm = document.forms["batchFrm"];
	var total_span = document.getElementById("timexp_total_hours");

	var total = 0;
	for (var i=0; i<hours.length; i++){
		total += hours[i];
	}

	//frm.timexp_value.value = total;
	frm.timexp_hours.value = hours.join(",");
	if(total_span)
		total_span.innerHTML = (Math.round(total * 100) / 100);
}

function delhour(ind){
	var frm = document.forms["batchFrm"];
	var	tmp_dates  = new Array();
	var	tmp_dates_ts  = new Array();
	var	tmp_hours  = new Array();

	for(var i = 0; i < dates.length; i++){
		if(ind!=i){
			tmp_dates[tmp_dates.length] = dates[i];
			tmp_dates_ts[tmp_dates_ts.length] = dates_ts[i];
			tmp_hours[tmp_hours.length] = hours[i];
		}
	}
	dates = tmp_dates;
	dates_ts = tmp_dates_ts;
	hours = tmp_hours;
	
	if(dates.length == 0){
		frm.save.disabled = true;
	}

	frm.timexp_dates.value = dates_ts.join(",");
	frm.timexp_hours.value = hours.join(",");
	
	//build_table_times();

	var frm = document.forms["batchFrm"];
	
	var obj = document.getElementById("timesdiv");
	var start_time = new Array();
	var end_time = new Array();
	
	var html_header ='<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" >';
	var html_footer = '</table>';
    
	var no_data	='<tr><td class="hilite"><?php echo $AppUI->_('No data available');?></td></tr>';
	var row		='<tr><td class="hilite">[DATE]</td><td class="hilite" align="right" width="60px">[START_HOURS]</td><td class="hilite" align="right" width="60px">[END_HOURS]</td><td class="hilite" align="right" width="60px">[HOURS]</td><td class="hilite" width="20px">[DEL]</td></tr>';
	var row_total	='<tr style="border-top: 1px solid black;"><td class="hilite">[DATE]</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">[HOURS]</td><td class="hilite" width="20px">[DEL]</td></tr>';
	var content = '';
    
	if (dates.length == hours.length && dates.length > 0){

		for(var i = 0; i < dates.length; i++){
            
            // Si no quiere usar el calendar calculo la fecha de fin //
			a=frm.timexp_start_time.value.substr(0,2)

			b=frm.timexp_start_time.value.substr(3,2) 
	   
			var c = parseInt(hours[i]);
           // var d = ceil(c.value);
			var h_inicio = parseFloat(a);

			temp = h_inicio + c;

			if (temp >24)
			{
			temp = 24;
			}
			
			var timexp_end_time = temp+":"+b;
            start_time[i] = frm.timexp_start_time.value;
			end_time[i] = timexp_end_time;

			var date_field = '<input type="hidden" name="timexp_date[]" value="' + dates_ts[i] + '" />' + dates[i];
			var hour_field = '<input type="text" name="timexp_value[]" class="text" size="10" align="right" value="' + hours[i] + '"onkeypress="return numeralsOnly(event)" onblur="updateHour(' + i + ', this);" style="text-align: right;"/>';// + hours[i];
			var start_hour_field = '<input type="text" name="start_time" class="text" size="10" align="right" value="' + start_time[i] +'"  style="text-align: right;"/>'
			var ends_hour_field = '<input type="text" name="end_time" class="text" size="10" align="right" value="' + end_time[i] + '"   style="text-align: right;"/ enabled>';
			var del_field = '<a href="javascript: delhour(' + i + ');" ><?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, 'delete' ) ?></a>';
			content += row.replace('[DATE]', date_field).replace('[START_HOURS]', start_hour_field).replace('[END_HOURS]',ends_hour_field).replace('[HOURS]', hour_field).replace('[DEL]', del_field);

		}
		
		var hour_field = '<b><span id="timexp_total_hours" name="timexp_total_hours" style="text-align: right;">&nbsp;</span></b>';// + hours[i];		
		//total hours
		content += row_total.replace('[DATE]', '<b><?php echo $AppUI->_('Total Hours');?></b>').replace('[HOURS]', hour_field).replace('[DEL]', '&nbsp;');	
		frm.save.disabled = false;
		obj.innerHTML = html_header + content + html_footer;
		recalculateTotal();
	}else{
		content = no_data;
		obj.innerHTML = html_header + content + html_footer;
	}
	


}

function build_table_times(){
	var frm = document.forms["batchFrm"];
	
	var obj = document.getElementById("timesdiv");
	var start_time = new Array();
	var end_time = new Array();
	
	var html_header ='<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" >';
	var html_footer = '</table>';
    
	var no_data	='<tr><td class="hilite"><?php echo $AppUI->_('No data available');?></td></tr>';
	var row		='<tr><td class="hilite">[DATE]</td><td class="hilite" align="right" width="60px">[START_HOURS]</td><td class="hilite" align="right" width="60px">[END_HOURS]</td><td class="hilite" align="right" width="60px">[HOURS]</td><td class="hilite" width="20px">[DEL]</td></tr>';
	var row_total	='<tr style="border-top: 1px solid black;"><td class="hilite">[DATE]</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">[HOURS]</td><td class="hilite" width="20px">[DEL]</td></tr>';
	var content = '';
    
	if (dates.length == hours.length && dates.length > 0){

		for(var i = 0; i < dates.length; i++){
            
            // Si no quiere usar el calendar calculo la fecha de fin //
			a=frm.timexp_start_time.value.substr(0,2)

			b=frm.timexp_start_time.value.substr(3,2) 
	   
			var c = parseInt(hours[i]);
           // var d = ceil(c.value);
			var h_inicio = parseFloat(a);

			temp = h_inicio + c;

			if (temp >24)
			{
			temp = 24;
			}
			
			var timexp_end_time = temp+":"+b;
            start_time[i] = frm.timexp_start_time.value;
			end_time[i] = timexp_end_time;
            

			var date_field = '<input type="hidden" name="timexp_date[]" value="' + dates_ts[i] + '" />' + dates[i];
			var hour_field = '<input type="text" name="timexp_value[]" class="text" size="10" align="right" value="' + hours[i] + '"onkeypress="return numeralsOnly(event)" onblur="updateHour(' + i + ', this);" style="text-align: right;"/>';// + hours[i];
			var start_hour_field = '<input type="text" name="start_time" class="text" size="10" align="right" value="' + start_time[i] +'"  style="text-align: right;"/>'
			var ends_hour_field = '<input type="text" name="end_time" class="text" size="10" align="right" value="' + end_time[i] + '"   style="text-align: right;"/ enabled>';
			var del_field = '<a href="javascript: delhour(' + i + ');" ><?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, 'delete' ) ?></a>';
			content += row.replace('[DATE]', date_field).replace('[START_HOURS]', start_hour_field).replace('[END_HOURS]',ends_hour_field).replace('[HOURS]', hour_field).replace('[DEL]', del_field);
            
			if (frm.duplicate.checked)
			{
			j = i+1;

			dates[j] = dates[i];
            dates_ts[j] = dates_ts[i];
            hours[j] = hours[i];
            start_time[j] = start_time[i];
			end_time[j] = end_time[i];
 
			var date_field = '<input type="hidden" name="timexp_date[]" value="' + dates_ts[j] + '" />' + dates[j];
			var hour_field = '<input type="text" name="timexp_value[]" class="text" size="10" align="right" value="' + hours[j] + '"onkeypress="return numeralsOnly(event)" onblur="updateHour(' + j + ', this);" style="text-align: right;"/>';
			var start_hour_field = '<input type="text" name="start_time" class="text" size="10" align="right" value="' + start_time[j] +'"  style="text-align: right;"/>'
			var ends_hour_field = '<input type="text" name="end_time" class="text" size="10" align="right" value="' + end_time[j]  + '"   style="text-align: right;"/ enabled>';
			var del_field = '<a href="javascript: delhour(' + j + ');" ><?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, 'delete' ) ?></a>';
			content += row.replace('[DATE]', date_field).replace('[START_HOURS]', start_hour_field).replace('[END_HOURS]',ends_hour_field).replace('[HOURS]', hour_field).replace('[DEL]', del_field);

            i = j;
		    }

		}
		
		var hour_field = '<b><span id="timexp_total_hours" name="timexp_total_hours" style="text-align: right;">&nbsp;</span></b>';// + hours[i];		
		//total hours
		content += row_total.replace('[DATE]', '<b><?php echo $AppUI->_('Total Hours');?></b>').replace('[HOURS]', hour_field).replace('[DEL]', '&nbsp;');	
		frm.save.disabled = false;
		obj.innerHTML = html_header + content + html_footer;
		recalculateTotal();
	}else{
		content = no_data;
		obj.innerHTML = html_header + content + html_footer;
	}
	
	
	
}
   
function numeralsOnly(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : 
        ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
        //alert("Enter numerals only in this field.");
        return false;
    }
    return true;
}


function synchourstimes(mod){
	cleanError();
	var frm = document.forms["editFrm"];
	var h = frm.timexp_value;
	var s = frm.timexp_start_time;
	var e = frm.timexp_end_time;

  var initime = getDateObject(s);
  var fintime = getDateObject(e, true);	
  
	var rta = true;	
	checkvalue(h);

	// si se borra el registro modificado no hago cambios
	if (mod.value==""){
		rta=false;
	}

	if (!CompareTimes(initime,fintime,h.value) || initime == false || fintime==false){
		rta = false;
	}		
		
	if (rta){
		var str = "";
		// si no está cargado el fin
		if (e.value=="" && s.value!="" && h.value!=""){
			var fintime = new Date();
			fintime.setTime(initime.getTime() + h.value * 3600000);
				
			str="0000"+fintime.getHours().toString();
			e.value = str.substring(str.length - 2, str.length)+":";
			str="0000"+fintime.getMinutes().toString();
			e.value += str.substring(str.length - 2, str.length);
			
		} else 
		// si no está cargado el inicio
		if (e.value!="" && s.value=="" && h.value!=""){
			var initime = new Date();
			initime.setTime(fintime.getTime() - h.value * 3600000);
			str="0000"+initime.getHours().toString();
			s.value = str.substring(str.length - 2, str.length)+":";
			str="0000"+initime.getMinutes().toString();
			s.value += str.substring(str.length - 2, str.length);			
		} else 
		// si no están cargadas las horas
		if (e.value!="" && s.value!="" && h.value==""){
			h.value = Math.round(100 * (fintime-initime)/3600000) / 100;
		} 
		 
		// si es modificado el inicio o el final recalculo la duración
		if ((s == mod || e == mod) && (e.value!="" && s.value!="")){
			h.value = Math.round(100 * (fintime-initime)/3600000) / 100;
		}

	}
		

}



</script>
<!-- END OF TIMER RELATED SCRIPTS -->
<? /*
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th >
		<?php echo $AppUI->_('Batch new times');?>
	</th>
</tr>
</table> */ ?>
<table cellspacing="2" cellpadding="2" border="0" width="100%" class="tableForm_bg">
<form name="batchFrm" action="" method="post" onsubmit="return validateTimexp();">
	<input type="hidden" name="uniqueid" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="dosql" value="do_batch_timexp_aed" />
	<input type="hidden" name="timexp_id" value="<?php echo $timexp->timexp_id;?>" />
	<input type="hidden" name="timexp_creator" value="<?php echo $timexp->timexp_creator ? $timexp->timexp_creator : $AppUI->user_id;?>" />
<?php if ($external){ ?>
	<input type="hidden" name="timexp_applied_to_type" value="<?php echo $timexp->timexp_applied_to_type;?>" />
<?php } ?>
	<input type="hidden" name="timexp_applied_to_id" value="<?php echo $timexp->timexp_applied_to_id;?>" />	
	<input type="hidden" name="timexp_billable" value="<?php echo $timexp->timexp_billable;?>" />
	<input type="hidden" name="timexp_type" value="<?php echo $timexp->timexp_type;?>" />
	<input type="hidden" name="next" value="<?php echo $redirect_url;?>" />
	<input type="hidden" name="timexp_dates" value="" />
	<input type="hidden" name="timexp_hours" value="" />
	<input type="hidden" name="timexp_start_time" value="<?php echo $timexp->timexp_type;?>" />" />
<tr>
<?/*
	<td align="right" style="font-weight: bold; vertical-align: top;">
		&nbsp;<?php //echo $AppUI->_('Type');?>
	</td> */?>
	<td colspan="4">
		<?php
		$hour_types = array('Divide the hours equally on each working day',	
							'Use the hours on each working day');
		echo arraySelect( $hour_types, 'hours_type', 'size="1" class="text"  onchange=""', '', true );
?>
	</td>
	<td align="right" style="font-weight: bold;"><?php echo $AppUI->_('Name');?>:</td>
	<td>
		<input type="text" class="text" name="timexp_name" value="<?php echo $timexp->timexp_name;?>" maxlength="255" size="30"/>
	</td>	
</tr>	
<tr>
	<td align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('From');?>
	</td>
	<td nowrap="nowrap" width="120">
		<input type="hidden" name="timexp_from_date" value="<?php echo $date_from->format( FMT_TIMESTAMP_DATE );?>">
        <input type="hidden" name="timexp_from_date_format" value="<?php echo $df; ?>">
        <input type="text" name="from_date" value="<?php echo $date_from->format( $df );?>" class="text"  size="12">
		<a href="#" onClick="popCalendar('from_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('To');?>
	</td>
	<td nowrap="nowrap" width="120">
		<input type="hidden" name="timexp_to_date" value="<?php echo $timexp_date->format( FMT_TIMESTAMP_DATE );?>">
        <input type="hidden" name="timexp_to_date_format" value="<?php echo $df; ?>">
        <input type="text" name="to_date" value="<?php echo $timexp_date->format( $df );?>" class="text"  size="12">
		<a href="#" onClick="popCalendar('to_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>	
	<td rowspan="5" align="right" valign="top" style="font-weight: bold;"><?php echo $AppUI->_('Description');?>:</td>
	<td rowspan="5" valign="top">
		<textarea name="timexp_description" class="textarea" cols="40" rows="8"><?php echo $timexp->timexp_description;?></textarea>
		<div id="errorDiv" name="errorDiv"></div>
	</td>
</tr>
<tr>
	<td align="right" style="font-weight: bold;"><?php echo $AppUI->_($label_value);?></td>
	<td>
		<input type="text" class="text" name="timexp_value" value="<?php echo $timexp->timexp_value;?>" maxlength="8" size="6" />
	</td>
	<td align="right" style="font-weight: bold;"><?php echo $AppUI->_('Billable').": ";?>	</td>
	
	<td>
	<div id="billablediv" style="display: true;">
	<?php echo arraySelect( $billables, 'timexp_billable_box', 'size="1" class="text"', $timexp->timexp_billable, true); ?>
	</div>
	<div id="nobillablediv" style="display: none;">
	No
	</div>
	</td>	

</tr>

<!-- Pregunto si tomo como parametro el calendar -->
<!-- <tr>
   <td align="right" style="font-weight: bold;"><?php echo $AppUI->_('Calendar');?></td>
   <td>
   <input type="checkbox" name="wantscaledar" value="1" <?if($wantscaledar==1){echo "checked";}?> >
   </td>
</tr> -->

<!-- Pregunto si quiere duplicar cada linea -->
<tr>
   <td align="right" style="font-weight: bold;">Duplica</td>
   <td>
   <input type="checkbox" name="duplicate" value="1" <?if($duplicate==1){echo "checked";}?> >
   </td>
</tr>

<!-- Para que ingrese la hora de inicio, la hora de fin la calculo -->
<tr>
	<td   align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('Start Time');?>
	</td>
	<td>
    <input type='hidden' name="fecha_inicio" value="1">    
	<input type='text' class="text" value='<? echo $timexp->timexp_start_time; ?>' name='timexp_start_time'  onblur="CheckTime(this)"  maxlength="12" size="10"/>
	</td>
</tr>
	
<tr>
	<td align="right" style="font-weight: bold;" valign="top">
		<?php echo $AppUI->_('Applied to');?>
	</td>
	<td valign="top" colspan="3">
<?php
		if (!$external){
			echo arraySelect( $timexp_applied_to_types, 'timexp_applied_to_type', 'size="1" class="text"  onchange="javascript: changeApplied(this, true);"', $timexp->timexp_applied_to_type, true );
		}else{
			echo $AppUI->_($timexp_applied_to_types[$timexp->timexp_applied_to_type]);
		}
?>



<?php /* 
********************************************************************************+
Selección del proyecto
********************************************************************************+
*/ ?>
<div id="projectdiv" style="display: true;">
<?php if (!$external){ ?>
		<?php echo $AppUI->_("Project").":";?>
        <input type="hidden" name="project_item" value="<?php 
		echo $project ;
		?>" />
        <input type="text" class="text" name="project_item_name" value="<?php 
		echo $project_name;
		?>" size="25" disabled />
        <input type="button" class="button" value="..." onclick="popProject()" />
<?php } ?>
</div>

<?php /* 
********************************************************************************+
Selección de tarea
********************************************************************************+
*/ ?>
<div id="taskdiv" style="display: true;">
<? if ($rnd_type=="1"){?>
	<?=$AppUI->_('Contribute to task completion').": ";?>
	<?=arraySelect( $billables, 'timexp_contribute_task_completion', 'size="1" class="text"', $timexp->timexp_contribute_task_completion,true); ?>
<? } ?>
</div>	



<?php /*
********************************************************************************+
Selección de incidencia
********************************************************************************+
*/ ?>
<div id="incidencediv" style="display: none;"><?php echo $AppUI->_("Bug").":";?>
        <input type="hidden" name="bug_item" value="<?php 
		echo ($timexp->timexp_applied_to_type == 2 && $apptoid ?  $apptoid : "");
		?>" />
        <input type="text" class="text" name="bug_item_name" value="<?php
		echo ($timexp->timexp_applied_to_type == 2 && $apptoid ? $apptodsc :"");
		?>" size="25" disabled />
        <input type="button" class="button" name="bug_pop" value="..." onclick="popBug()" <?php
		echo ($project=="-1")? "disabled" : "";?> />
		<input type="button" class="button" name="new_bug" value="<?php echo $AppUI->_("new bug");?>" onclick="popNewBug()" disabled />
</div>
	</td>
</tr>
<tr>
	<td colspan="4">
		<table cellspacing="0" cellpadding="2" border="0" width="100%" class="" >
		<tr class="tableHeaderGral">
			<th ><?php echo $AppUI->_("Times");?>:</th>
			<th align="right"><input type="button" class="button" value="<?php echo $AppUI->_('generate');?>" onclick="generate_times();"/></th>
		</tr>
		</table>
		<div id="timesdiv">
		<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" >
		<tr><td class="hilite"><?php echo $AppUI->_('No data available');?></td>		
		</tr>
		</table>
		</div>
	</td>
</tr>
</table>
<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg" >
<tr>
	<td align="right">
		<input type="submit" name="save" disabled="true" class="button" value="<?php echo $AppUI->_('update');?>" />
	</td>
</tr>
</form>
</table>
<form name="getCal" action="index.php?m=public&a=get_work_calendar&suppressHeaders=1&dialog=1" method="post" target="calendar_getter">
	<input type="hidden" name="cal_type" value="3" />
	<input type="hidden" name="id" value="<?php echo $AppUI->user_id;?>" />
	<input type="hidden" name="project" value="<?php echo $project;?>" />
	<input type="hidden" name="from" value="" />
	<input type="hidden" name="to" value="" />
	<input type="hidden" name="dateformat" value="<?php echo $df;?>" />
</form>
<? if (@$_GET["debuginteraction"] == "123"){ ?> 
	<iframe id="calendar_getter" name="calendar_getter" width="600" height="200" frameborder="0" scrolling="Auto" style="border: 1px solid; " src="about:blank"></iframe>
<?php }else{ ?>
	<iframe id="calendar_getter" name="calendar_getter" width="0" height="0" frameborder="0" scrolling="Auto" style="border: 0px solid; " src="about:blank"></iframe>
<?php } ?>
<?php 
if ($rnd_type=="1"){?>
<iframe id="cookie_saver" name="cookie_saver" height="1" width="1" scrolling="No" style="border: 0px; " src="./index.php?m=public&a=cookie_saver&suppressLogo=1&dialog=1&autorefresh=1"></iframe>
<?php } 
if (!$external){ ?>
<script><!--
changeApplied(document.editFrm.timexp_applied_to_type, false);
//--></script>
<?php } ?>