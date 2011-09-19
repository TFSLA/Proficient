<?php 

require_once( $AppUI->getModuleClass( 'timexp' ) );
global  $task_id, $bug_id, $obj, $percent,$timexp_id, $rnd_type, $timexp_types, $external, $timexp_applied_to_types, $billables,$hideTitleBlock,$dialog,$rnd_type,$external;
$external = 1;
$rnd_type="1";




$canEdit = true;
$accessLog = PERM_EDIT;

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
	
	// como fecha desde tomo la ultima fecha de carga de horas + 1 dia
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
var start_time   = new Array();
var end_time     = new Array();
var day_names 	  = new Array(<?php echo $day_names;?>)


function stripCharsInBag (s, bag)
{   var i;

     var d = s.split(bag).join(".");
  
     return d;
}

// Traigo el calendario //
function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.batchFrm.timexp_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

// El calendario devuelve el valor y lo imprimo //
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


/* Verifico que la cant de horas sea entera */
function Checkhour(field)
{   
	var frm = document.forms["batchFrm"];
    var c_horas = parseFloat(field.value);  // Cantidad de horas reales
    var p_horas = parseInt(field.value); // Parámetro de comparación 

	if (c_horas != p_horas)
	{
	alert("<?php echo $AppUI->_('timexpValue');?>");
	frm.timexp_value.focus();
	return;
	}

}

/* Verifico el formato de la hora de inicio */
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


<?php
echo $timexp_date->buildManualDateValidationJS();
?>

/* Funcion para la generación de los datos */
function generate_times(str)
{
    var frm = document.forms["batchFrm"];
	var rta = true;

	/* Valida la fecha de inicio */
	strMDVparam1 = frm.from_date;
    strMDVparam2 = frm.timexp_from_date_format;
    strMDVparam3 = frm.timexp_from_date;

    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpInvalidFromDate');?>");
        rta = false;
    }

    /* Valida la fecha de fin */
    strMDVparam1 = frm.to_date;
    strMDVparam2 = frm.timexp_to_date_format;
    strMDVparam3 = frm.timexp_to_date;

    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpInvalidToDate');?>");
        rta = false;
    }   
    
    anio1 = frm.timexp_from_date.value.substr(0,4);
	anio2 = frm.timexp_to_date.value.substr(0,4);
    
	if (anio1 != anio2)
	{
	alert("<?php echo $AppUI->_('timexpInvalidToDate2');?>");
    rta = false;
	}

    /* Me fijo que la fecha final sea mayor que la inicial */
	if (frm.timexp_from_date.value > frm.timexp_to_date.value){
	   alert("<?php echo $AppUI->_('timexpInvalidToDate3');?>");
       rta = false;
	}
    
	/* Valida que ingresen la cantidad de horas */
	var valor = parseFloat(frm.timexp_value.value);

	if (isNaN(valor) || valor <= 0){
		alert("<?php echo $AppUI->_('timexpValue');?>");
		rta = false;
		frm.timexp_value.focus();
	}
	
	/*  se fija que ingrese hora de inicio */
	if (trim(frm.timexp_start_time.value).length<1)
		{
		alert("<?php echo $AppUI->_('timexpInvalidstartime');?>");
		rta = false;
		frm.timexp_start_time.focus();
		}

    /* Se fija que si usa la cant por dia no exeda las 24 */
	if((frm.hours_type.value==0)&&(valor>24))
	{
	alert("<?php echo $AppUI->_('timexpValue2');?>");
	rta = false;
	frm.timexp_value.focus();
	}

	
    
	/* Verifica que la suma de la hora de inicio y la cantidad de horas no exeda el dia */
   if(frm.hours_type.value==0)
	{   
	 
	 a=frm.timexp_start_time.value.substr(0,2);
	 b=frm.timexp_start_time.value.substr(3,2); 
	   
	 var c = parseFloat(frm.timexp_value.value);
	 var d = parseInt(frm.timexp_value.value);

	 var h_inicio = parseFloat(a);

	temp = h_inicio + c;
    
			if ((temp >24)&&(rta==true))
			{
			alert("<?php echo $AppUI->_('timexpValue2');?>");
	        rta = false;
			}

			if ((temp ==24)&&(b>0)&&(rta==true))
			{
            alert("<?php echo $AppUI->_('timexpValue2');?>");
	        rta = false;
			}

			if ((temp ==24)&&(c >d)&&(rta==true))
			{
            alert("<?php echo $AppUI->_('timexpValue2');?>");
	        rta = false;
			}

	}
	else
	{
	var resta = "";
	var a = parseFloat(frm.timexp_start_time.value.substr(0,2)); // Hora de inicio
	var b = parseFloat(frm.timexp_value.value);  // Cantidad de horas
    
	// Preparo la fecha de inicio //

	anio1 = frm.timexp_from_date.value.substr(0,4);
	mes1  = parseFloat(frm.timexp_from_date.value.substr(4,2))-1;
    dia1  = frm.timexp_from_date.value.substr(6,2);
    
	// Preparo la fecha de fin //
	anio2 = frm.timexp_to_date.value.substr(0,4);
    mes2  = parseFloat(frm.timexp_to_date.value.substr(4,2))-1;
    dia2  = frm.timexp_to_date.value.substr(6,2);
    
	var oFini = new Date(anio1,mes1,dia1); 
    var oFfin = new Date(anio2,mes2,dia2); 
    
	var operacion = oFfin - oFini; 

	operacion = operacion/86400000; 

	resta = operacion;

	    if (resta==0)
		{
		 h_temp = a + b;
		}
		else
		{
		resta = operacion + 1;
	    temp = parseInt(b/(resta));
		h_temp = a + temp;
		}
        
		temp2 = b/(resta);

        if (h_temp>24)
		{
         alert("<?php echo $AppUI->_('timexpValue2');?>");
	     rta = false;
		}

		if ((h_temp==24)&&(temp2 >temp))
		{
         alert("<?php echo $AppUI->_('timexpValue2');?>");
	     rta = false;
		}

	}
    
	if(frm.timexp_from_date.value>today || trim(frm.timexp_from_date.value)==""){
		alert("<?php echo $AppUI->_('timexpInvalidFromDate2');?>");
		rta = false;
	}
	
	if(frm.timexp_to_date.value>today || trim(frm.timexp_to_date.value)==""){
		alert("<?php echo $AppUI->_('timexpInvalidFromDate2');?>");
		rta = false;
	}
	
	if(rta)
	{
		/* Si no cometieron errores preparo el vector */ 
	    if(str==1)
		{
		genera_vector();
		frm.generate.disabled=true;
		}
        else
		{
		addhour();
		}
	}
	
	build_table_times();
}

function addhour(){
	var frm = document.forms["batchFrm"];
	var valor_temp = stripCharsInBag(trim(frm.timexp_value.value),',');
	var valor = parseFloat(valor_temp);

	var	old_dates  = new Array();
	var	old_dates_ts  = new Array();
	var	old_hours  = new Array();
	var old_start_time = new Array();
	var old_end_time = new Array();
    

	      for(var i = 0; i < dates.length; i++){
			old_dates[old_dates.length] = dates[i];
			old_dates_ts[old_dates_ts.length] = dates_ts[i];
			old_hours[old_hours.length] = hours[i];
			old_start_time[old_start_time.length] = start_time[i];
			old_end_time[old_end_time.length] = end_time[i];
	       }
          
			var	new_dates  = new Array();
			var	new_dates_ts  = new Array();
			var	new_hours  = new Array();
			var new_start_time = new Array();
			var new_end_time = new Array();
           
		   // Calculo los dias , la cantidad de horas por dia //
		   	var valor = parseFloat(valor_temp);

		    var datePat = /^(\d{4})(\d{2})(\d{2})$/;
			var strDateFormat = frm.timexp_from_date_format.value;
			var matchArray1 = frm.timexp_from_date.value.match(datePat);
			matchArray1[2] = parseInt(matchArray1[2])-1;
			var from = new Date(matchArray1[1], matchArray1[2], matchArray1[3], 0, 0, 0);
	
			var matchArray2 = frm.timexp_to_date.value.match(datePat);
			matchArray2[2] = parseInt(matchArray2[2])-1;
			var to = new Date(matchArray2[1], matchArray2[2], matchArray2[3], 0, 0, 0);		
			var one_day = 1000 * 60 * 60 * 24;
			var to_ms = to.getTime();
			var from_ms = from.getTime();
			var dif = ((to_ms + one_day) - from_ms)/one_day;
			var hour_day = Math.round((valor / dif)*100)/100;


			for (var i = 0; i<dif; i++){
				var curdate = strDateFormat;

				curdate = curdate.replace("%d", (from.getDate().toString().length==1?"0":"") + from.getDate().toString());

				from_mes = parseInt(from.getMonth())+1;

				curdate = curdate.replace("%m", (from_mes.toString().length==1?"0":"") + from_mes.toString());

				curdate = curdate.replace("%Y", from.getFullYear().toString());
				new_dates[i] = curdate;
		
				var anio = curdate.substr(6,4);
				var mes = curdate.substr(3,2);
                var dia = curdate.substr(0,2);
                new_dates_ts[i] = anio+mes+dia;
               
                new_start_time[i] = frm.timexp_start_time.value;


			// Se fija si quiere dividir las horas por los dias //
            if (frm.hours_type.value==0)
		        {
				// Si no divide las horas 
				hour_day = parseFloat(valor_temp);
				new_hours[i] = hour_day;
				temp_h = hour_day;
				}
				else
				{
			    new_hours[i] = hour_day;
				temp_h = hour_day;
				}

				a=frm.timexp_start_time.value.substr(0,2)

			    b=frm.timexp_start_time.value.substr(3,2) 
	   
			    var c = parseInt(temp_h);
       
			    var h_inicio = parseFloat(a);

			    temp = h_inicio + c;
                
				var min1 = parseFloat(new_hours[i]);
				var min2 = parseInt(new_hours[i]);
          
				var difm = min1 - min2;

                
				 if (difm > 0)
					{
					   b = parseInt(b);

					   if ((difm > 0)&&(difm < 0.26))
						{
						   min = 15;
						}

                       if ((difm > 0.25)&&(difm < 0.51))
						{
						   min = 30;
						}
						
					   if ((difm > 0.50)&&(difm < 0.76))
						{
						   min = 45;
						}

					   if ((difm > 0.75)&&(difm < 1))
						{
						   min = 60;
						}

						b = b + min;

						if (b > 60)
						{ 
							 b = 60 - b;

							 if (b<0)
							 {
							  b = b * (-1);
							 }

						     temp =  temp + 1;
						}

						if (b == 60)
						{
							b = 0;
						    temp =  temp + 1;
						}
					}

                    if ((b > 0)&& (b < 16))
					{
					  b = 15;
					}

					if ((b > 15)&& (b < 31))
					{
					  b = 30;
					}

					if ((b > 30)&& (b < 46))
					{
					  b = 45;
					}

					if ((b > 45)&& (b < 60))
					{
					  b = 0;
					  temp = temp + 1;
					}

				    if (b ==0)
					{
					  b = "00";
					}

				    if (temp < 10)
					{
					 temp = "0"+temp;
					}


			                
				new_end_time[i] = temp+":"+b;

				from.setTime(from.getTime() + one_day);

			}



            // Antes de preparar el vector final me fijo que no haya registros repetidos //

			var	temp_dates  = new Array();
			var	temp_dates_ts  = new Array();
			var	temp_hours  = new Array();
			var temp_start_time = new Array();
			var temp_end_time = new Array();
            
			var k = 0;

            for(var i = 0; i < new_dates.length; i++)
	         {      
				    var gd = true;
					var cant = 0;

					for(var j = 0; j < old_dates.length; j++)
					 {
						if ((old_dates[j]==new_dates[i])&&(old_start_time[j]==new_start_time[i]))
						 {
							gd = false;
						 }
                         
						 if (old_dates[j]==new_dates[i])
						 {
							cant = cant + old_hours[j];
						 }

					 }
                         

					 if (gd)
				     {
					   cant = cant + new_hours[i];

						  if (cant < 24)
						  {
						  temp_dates[k]  = new_dates[i];
						  temp_dates_ts[k]  = new_dates_ts[i];
						  temp_hours[k]  = new_hours[i];
						  temp_start_time[k] = new_start_time[i];
						  temp_end_time[k] = new_end_time[i];

						  k++;
						  }
						  else
						  {
						  alert("<?php echo $AppUI->_('noadd');?>"+new_dates[i]+"<?php echo $AppUI->_('noadd2');?>");
						  }
				     }
	         }
           
			var	final_dates  = new Array();
			var	final_dates_ts  = new Array();
			var	final_hours  = new Array();
			var final_start_time = new Array();
			var final_end_time = new Array();

			total = old_dates.length + temp_dates.length;
            
			
			ind = 0;

			for(var i = 0; i < total; i++){

				if(i < old_dates.length)
				{
				 final_dates[i] = old_dates[i];
				 final_dates_ts[i] = old_dates_ts[i];
				 final_hours[i] = old_hours[i];
				 final_start_time[i] = old_start_time[i];
				 final_end_time[i] = old_end_time[i];
				}
				else{
					  
				final_dates[i] = temp_dates[ind];
				final_dates_ts[i] = temp_dates_ts[ind];
				final_hours[i] = temp_hours[ind];
				final_start_time[i] = temp_start_time[ind];
				final_end_time[i] = temp_end_time[ind];

				ind ++;
							 
			    }
			}

	dates = final_dates;
	dates_ts = final_dates_ts;
	hours = final_hours;
	start_time = final_start_time;
	end_time = final_end_time;
	
	if(dates.length == 0){
		frm.save.disabled = true;
	}

	frm.timexp_dates.value = dates_ts.join(",");
	frm.timexp_hours.value = hours.join(",");
	
	build_table_times();

}

function genera_vector(){
           
		   var frm = document.forms["batchFrm"];
           var valor_temp = stripCharsInBag(trim(frm.timexp_value.value),',');
		   var valor = parseFloat(valor_temp);
          

		// Calculo los dias , la cantidad de horas por dia //
			dates  = new Array();
			dates_ts  = new Array();
			hours  = new Array();
			start_time = new Array();
			end_time = new Array();

		    var datePat = /^(\d{4})(\d{2})(\d{2})$/;
			var strDateFormat = frm.timexp_from_date_format.value;
			var matchArray1 = frm.timexp_from_date.value.match(datePat);
			matchArray1[2] = parseInt(matchArray1[2])-1;
			var from = new Date(matchArray1[1], matchArray1[2], matchArray1[3], 0, 0, 0);
	        
			var matchArray2 = frm.timexp_to_date.value.match(datePat);
			matchArray2[2] = parseInt(matchArray2[2])-1;
			var to = new Date(matchArray2[1], matchArray2[2], matchArray2[3], 0, 0, 0);		
			var one_day = 1000 * 60 * 60 * 24;
			var to_ms = to.getTime();
			var from_ms = from.getTime();
			var dif = ((to_ms + one_day) - from_ms)/one_day;
			var hour_day = Math.round((valor / dif)*100)/100;
            

			for (var i = dates.length; i<dif; i++){
				var curdate = strDateFormat;

				curdate = curdate.replace("%d", (from.getDate().toString().length==1?"0":"") + from.getDate().toString());

				from_mes = parseInt(from.getMonth())+1;

				curdate = curdate.replace("%m", (from_mes.toString().length==1?"0":"") + from_mes.toString());

				curdate = curdate.replace("%Y", from.getFullYear().toString());
				dates[i] = curdate;
				
                var anio = curdate.substr(6,4);
				var mes = curdate.substr(3,2);
                var dia = curdate.substr(0,2);
                dates_ts[i] = anio+mes+dia;
                
				
                start_time[i] = frm.timexp_start_time.value;

			// Se fija si quiere dividir las horas por los dias //
                if (frm.hours_type.value==0)
		        {
				// Si no divide las horas 
				hour_day = valor;
				hours[i] = hour_day;
				}
				else
				{
			    hours[i] = hour_day;
				}
                

				a=frm.timexp_start_time.value.substr(0,2)

			    b=frm.timexp_start_time.value.substr(3,2) 
	   
			    var c = parseInt(hours[i]);
       
			    var h_inicio = parseFloat(a);

			    temp = h_inicio + c;

				var min1 = parseFloat(hours[i]);
				var min2 = parseInt(hours[i]);
          
				var difm = min1 - min2;

                
				 if (difm > 0)
					{
					   b = parseInt(b);

					   if ((difm > 0)&&(difm < 0.26))
						{
						   min = 15;
						}

                       if ((difm > 0.25)&&(difm < 0.51))
						{
						   min = 30;
						}
						
					   if ((difm > 0.50)&&(difm < 0.76))
						{
						   min = 45;
						}

					   if ((difm > 0.75)&&(difm < 1))
						{
						   min = 60;
						}

						b = b + min;

						if (b > 60)
						{ 
							 b = 60 - b;

							 if (b<0)
							 {
							  b = b * (-1);
							 }

						     temp =  temp + 1;
						}

						if (b == 60)
						{
							b = 0;
						    temp =  temp + 1;
						}
					}

                    if ((b > 0)&& (b < 16))
					{
					  b = 15;
					}

					if ((b > 15)&& (b < 31))
					{
					  b = 30;
					}

					if ((b > 30)&& (b < 46))
					{
					  b = 45;
					}

					if ((b > 45)&& (b < 60))
					{
					  b = 0;
					  temp = temp + 1;
					}

				    if (b ==0)
					{
					  b = "00";
					}

				    if (temp < 10)
					{
					 temp = "0"+temp;
					}

			               
				end_time[i] = temp+":"+b;

				from.setTime(from.getTime() + one_day);

			}
            
			frm.timexp_dates.value = dates_ts.join(",");
			frm.timexp_hours.value = hours.join(",");

}


function build_table_times(){
	var frm = document.forms["batchFrm"];
	
	var obj = document.getElementById("timesdiv");
	
	var html_header ='<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" >';
	var html_footer = '</table>';
    
	var no_data	='<tr><td class="hilite"><?php echo $AppUI->_('No data available');?></td></tr>';
	var row		='<tr><td class="hilite">[DATE]</td><td class="hilite" align="right" width="60px">[START_HOURS]</td><td class="hilite" align="right" width="60px">[END_HOURS]</td><td class="hilite" align="right" width="60px">[HOURS]</td><td class="hilite" width="20px">[DEL]</td><td class="hilite" width="20px">[DUPLICA]</td></tr>';
	var row_total	='<tr style="border-top: 1px solid black;"><td class="hilite">[DATE]</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">[HOURS]</td><td class="hilite" width="20px">[DEL]</td><td class="hilite" width="20px">&nbsp;</td></tr>';
	var content = '';
    
	if (dates.length == hours.length && dates.length > 0){

		for(var i = 0; i < dates.length; i++){
			var date_field = '<input type="hidden" name="timexp_date[]" value="' + dates_ts[i] + '" />' + dates[i];
			var hour_field = '<input type="text" name="timexp_value[]" class="text" size="10" align="right" value="' + hours[i] + '"onkeypress="return numeralsOnly(event)" onblur="updateHour(' + i +',this);" style="text-align: right;"/>';// + hours[i];
			var start_hour_field = '<input type="text" name="start_time[]" class="text" size="10" align="right" value="' + start_time[i] +'"  onblur="updatestart(' + i +',this);" style="text-align: right;"/>'
			var ends_hour_field = '<input type="text" name="end_time[]" class="text" size="10" align="right" value="' + end_time[i] + '"  onblur="updatend(' + i +',this);" style="text-align: right;"/ enabled>';
			var del_field = '<a href="javascript: delhour(' + i + ');" ><?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, 'delete' ) ?></a>';
			var dup_field = '<a href="javascript: duphour(' + i + ');" ><?php echo dPshowImage( './images/article_management.gif', 20, 20, 'duplicate' ) ?></a>';

			content += row.replace('[DATE]', date_field).replace('[START_HOURS]', start_hour_field).replace('[END_HOURS]',ends_hour_field).replace('[HOURS]', hour_field).replace('[DEL]', del_field).replace('[DUPLICA]', dup_field);
		}
		
		var hour_field = '<b><span id="timexp_total_hours" name="timexp_total_hours" style="text-align: right;">&nbsp;</span></b>';// + hours[i];		
		//total hours
		content += row_total.replace('[DATE]', '<b><?php echo $AppUI->_('Total Hours');?></b>').replace('[HOURS]', hour_field).replace('[DEL]', '&nbsp;').replace('[DUPLICA]', '&nbsp;');	
		frm.save.disabled = false;
		add = '<tr><td align="right" colspan="6"><input type="button" class="button" value="<?php echo $AppUI->_('add');?>" onclick="generate_times(0);"/></td></tr>';
		obj.innerHTML = html_header + content + add + html_footer;
		recalculateTotal();
	}else{
		content = no_data;
		obj.innerHTML = html_header + content + html_footer;
	}

    if(dates.length == 0){
		frm.generate.disabled = false;
	}

}


function updatend(ind,obj){
	var frm = document.forms["batchFrm"];
	var endtime = obj.value;
	var rta = true;

	var	old_dates  = new Array();
	var	old_dates_ts  = new Array();
	var	old_hours  = new Array();
	var old_start_time = new Array();
	var old_end_time = new Array();
    

		  for(var i = 0; i < dates.length; i++){

				if (ind==i){
				  old_dates[i] = dates[i];
				  old_dates_ts[i] = dates_ts[i];
				  old_hours[i] = hours[i];
				  old_end_time[i] = endtime;

				  a = endtime.substr(0,2);
				  b = endtime.substr(3,2);
				 
				  cant_horas = parseInt(old_hours[i]);
				 
				  c = parseFloat(a);
				 
				 
				  var resta = c - cant_horas;

				  d =parseInt(old_hours[i]);
                  e = parseFloat(old_hours[i]);

				   
                   
				   difm = e - d;

				   if (difm > 0)
					{
					   b = parseInt(b);

					   if ((difm > 0)&&(difm < 0.26))
						{
						   min = 15;
						}

                       if ((difm > 0.25)&&(difm < 0.51))
						{
						   min = 30;
						}
						
					   if ((difm > 0.50)&&(difm < 0.76))
						{
						   min = 45;
						}

					   if ((difm > 0.75)&&(difm < 1))
						{
						   min = 60;
						}

						b = b - min;


						if (b < 0)
						{  
							bt = b * (-1);
							b = 60 - bt;
						    resta = resta - 1;
						}
					}

                    if ((b > 0)&& (b < 16))
					{
					  b = 15;
					}

					if ((b > 15)&& (b < 31))
					{
					  b = 30;
					}

					if ((b > 30)&& (b < 46))
					{
					  b = 45;
					}

					if ((b > 45)&& (b < 60))
					{
					  b = 0;
					  resta = resta - 1;
					}


				    if (b ==0)
					{
					  b = "00";
					}
                  
					if (resta < 10)
					{
					 resta = "0"+resta;
					}

					if ((resta ==24)&&(b > 0)&&(rta==true))
					{
					 alert("<?php echo $AppUI->_('noduplica');?>");
					 rta = false;
					}

				var temp = resta+":"+b;
				  
				  old_start_time[i] = temp;

					 /* Hago las validaciones */

							if ((resta >24)&&(rta==true))
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							old_start_time[i] = start_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}else if ((resta ==24)&&(b>0)&&(rta==true))
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							old_start_time[i] = start_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}
							else
							{
							old_start_time[i] = temp;
							}

					/* Fin de las validaciones */
				} 
				else
			    {
				  old_dates[i] = dates[i];
				  old_dates_ts[i] = dates_ts[i];
				  old_hours[i] = hours[i];
				  old_start_time[i] = start_time[i];
				  old_end_time[i] = end_time[i];
			    }
	       }

	    
	dates = old_dates;
	dates_ts = old_dates_ts;
	hours = old_hours;
	start_time = old_start_time;
	end_time = old_end_time;
	

    if (rta)
	{
		if(dates.length == 0){
			frm.save.disabled = true;
		}

		frm.timexp_dates.value = dates_ts.join(",");
		frm.timexp_hours.value = hours.join(",");
		
		build_table_times();
	} 
  
}

function updatestart(ind,obj){
	var frm = document.forms["batchFrm"];
	var starttime = obj.value;
	var rta = true;

	var	old_dates  = new Array();
	var	old_dates_ts  = new Array();
	var	old_hours  = new Array();
	var old_start_time = new Array();
	var old_end_time = new Array();
    

		  for(var i = 0; i < dates.length; i++){

				if (ind==i){
				  old_dates[i] = dates[i];
				  old_dates_ts[i] = dates_ts[i];
				  old_hours[i] = hours[i];
				  old_start_time[i] = starttime;

				  a = starttime.substr(0,2);
				  b = starttime.substr(3,2);
				 
				  cant_horas = parseInt(old_hours[i]);
				 
				  c = parseFloat(a);
				 
				 
				  var suma = c + cant_horas;

				  d =parseInt(old_hours[i]);
                  e = parseFloat(old_hours[i]);

                   
				   difm = e - d;

				   if (difm > 0)
					{
					   b = parseInt(b);

					   if ((difm > 0)&&(difm < 0.26))
						{
						   min = 15;
						}

                       if ((difm > 0.25)&&(difm < 0.51))
						{
						   min = 30;
						}
						
					   if ((difm > 0.50)&&(difm < 0.76))
						{
						   min = 45;
						}

					   if ((difm > 0.75)&&(difm < 1))
						{
						   min = 60;
						}

						b = b + min;

						if (b > 60)
						{ 
							 b = 60 - b;

							 if (b<0)
							 {
							  b = b * (-1);
							 }

						     suma =  suma + 1;
						}

						if (b == 60)
						{
							b = 0;
						    suma =  suma + 1;
						}
					}

                    if ((b > 0)&& (b < 16))
					{
					  b = 15;
					}

					if ((b > 15)&& (b < 31))
					{
					  b = 30;
					}

					if ((b > 30)&& (b < 46))
					{
					  b = 45;
					}

					if ((b > 45)&& (b < 60))
					{
					  b = 0;
					  suma = suma + 1;
					}

				    if (b ==0)
					{
					  b = "00";
					}

				    if (suma < 10)
					{
					 suma = "0"+suma;
					}

                   if ((suma ==24)&&(b > 0)&&(rta==true))
					{
					 alert("<?php echo $AppUI->_('noduplica');?>");
					 rta = false;
					}


				  var temp = suma+":"+b;
				  
				  old_end_time[i] = temp;

					 /* Hago las validaciones */

							if ((suma >24)&&(rta==true))
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							old_start_time[i] = start_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}else if ((suma ==24)&&(b>0)&&(rta==true))
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							old_start_time[i] = start_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}
							else
							{
							old_end_time[i] = temp;
							}

					/* Fin de las validaciones */
				} 
				else
			    {
				  old_dates[i] = dates[i];
				  old_dates_ts[i] = dates_ts[i];
				  old_hours[i] = hours[i];
				  old_start_time[i] = start_time[i];
				  old_end_time[i] = end_time[i];
			    }
	       }

	    
	dates = old_dates;
	dates_ts = old_dates_ts;
	hours = old_hours;
	start_time = old_start_time;
	end_time = old_end_time;
	

    if (rta)
	{
		if(dates.length == 0){
			frm.save.disabled = true;
		}

		frm.timexp_dates.value = dates_ts.join(",");
		frm.timexp_hours.value = hours.join(",");
		
		build_table_times();
	} 
  
}


function updateHour(ind,obj){
	var frm = document.forms["batchFrm"];
	var hour = parseFloat(obj.value);
	var rta = true;

	var	old_dates  = new Array();
	var	old_dates_ts  = new Array();
	var	old_hours  = new Array();
	var old_start_time = new Array();
	var old_end_time = new Array();

		  for(var i = 0; i < dates.length; i++){

				if (ind==i){
				  old_dates[i] = dates[i];
				  old_dates_ts[i] = dates_ts[i];
				  old_hours[i] = hour;
				  old_start_time[i] = start_time[i];
				  
				  var old_start = old_start_time[i];
				  
				  a = old_start.substr(0,2);
				  b = old_start.substr(3,2);
				 
				  cant_horas = parseInt(old_hours[i]);
				 
				  c = parseFloat(a);
				 
				 
				  var suma = c + cant_horas;

				  d =parseInt(old_hours[i]);
                  e = parseFloat(old_hours[i]);

				 
				   difm = e - d;

				   if (difm > 0)
					{
					   b = parseInt(b);

					   if ((difm > 0)&&(difm < 0.26))
						{
						   min = 15;
						}

                       if ((difm > 0.25)&&(difm < 0.51))
						{
						   min = 30;
						}
						
					   if ((difm > 0.50)&&(difm < 0.76))
						{
						   min = 45;
						}

					   if ((difm > 0.75)&&(difm < 1))
						{
						   min = 60;
						}

						b = b + min;

						if (b > 60)
						{ 
							 b = 60 - b;

							 if (b<0)
							 {
							  b = b * (-1);
							 }

						     suma =  suma + 1;
						}

						if (b == 60)
						{
							b = 0;
						    suma =  suma + 1;
						}
					}

                    if ((b > 0)&& (b < 16))
					{
					  b = 15;
					}

					if ((b > 15)&& (b < 31))
					{
					  b = 30;
					}

					if ((b > 30)&& (b < 46))
					{
					  b = 45;
					}

					if ((b > 45)&& (b < 60))
					{
					  b = 0;
					  suma = suma + 1;
					}

				    if (b ==0)
					{
					  b = "00";
					}
                   
				    if ((suma ==24)&&(b > 0)&&(rta==true))
					{
					 alert("<?php echo $AppUI->_('noduplica');?>");
					 rta = false;
					}
                   

				  var temp = suma+":"+b;
				  
				  old_end_time[i] = temp;

					 /* Hago las validaciones */

							if ((suma >24)&&(rta==true))
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}else if ((suma ==24)&&(b>0)&&(rta==true))
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}
							else
							{
							old_end_time[i] = temp;
							}

					/* Fin de las validaciones */


				}
				else
				  {
					old_dates[i] = dates[i];
					old_dates_ts[i] = dates_ts[i];
					old_hours[i] = hours[i];
					old_start_time[i] = start_time[i];
					old_end_time[i] = end_time[i];
				  }
	       }

		
   
	dates = old_dates;
	dates_ts = old_dates_ts;
	hours = old_hours;
	start_time = old_start_time;
	end_time = old_end_time;
	

    if (rta)
	{
		if(dates.length == 0){
			frm.save.disabled = true;
		}

		frm.timexp_dates.value = dates_ts.join(",");
		frm.timexp_hours.value = hours.join(",");
		
		build_table_times();
	}
}

function recalculateTotal(){
	var frm = document.forms["batchFrm"];
	var total_span = document.getElementById("timexp_total_hours");

    
	var total = 0;
	for (var i=0; i<hours.length; i++){
		total += hours[i];
	}

	frm.timexp_hours.value = hours.join(",");
	if(total_span)
		total_span.innerHTML = (Math.round(total * 100) / 100);
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

function duphour(ind){

   var frm = document.forms["batchFrm"];
   var rta = true; 

	var	old_dates  = new Array();
	var	old_dates_ts  = new Array();
	var	old_hours  = new Array();
	var old_start_time = new Array();
	var old_end_time = new Array();
          
		  

	      for(var i = 0; i < dates.length; i++){

			if (ind==i){
			  var new_dates = dates[i];
			  var new_dates_ts = dates_ts[i];
			  var new_hours = hours[i];
			  var new_start_time = end_time[i];
              
			  a = new_start_time.substr(0,2);
			  b = new_start_time.substr(3,2);
			  cant_horas = parseInt(new_hours);
              c = parseFloat(a);
              
			  var suma = c + cant_horas;

			 if ((suma > 24)&&(rta==true))
				{
				 alert("<?php echo $AppUI->_('noduplica');?>");
				 rta = false;
				}
			   
			 
			    var min1 = parseFloat(new_hours);
				var min2 = parseInt(new_hours);
          
				var difm = min1 - min2;
           
                

				if (difm > 0 )
				{
				  b = parseInt(b);

				  if ((difm > 0)&&(difm < 0.26 ))
					{
					 b = b + 15;
					}

				   if ((difm > 0.25)&&(difm < 0.51 ))
					{
					 b = b + 30;
					}
                    
				   if ((difm > 0.5) &&(difm < 0.76 ))
					{
					 b =  b + 45;
					}
                   
                   if ((difm > 0.75) &&(difm < 1 ))
					{
					 b = "00";
					 suma = suma +1;
					}
				   
				} 

			   if (b > 59)
				{
                 b = 60 - b;

				 if (b < 0)
					{
					 b = b * (-1);
					}

				 suma = suma +1;
				}

				if (b==0)
				{
				 b = "00";
				}

			   if (suma <10)
				{
				 suma = "0"+suma;
				}
              
			  if ((suma ==24)&&(b > 0)&&(rta==true))
				{
				 alert("<?php echo $AppUI->_('noduplica');?>");
				 rta = false;
				}
              
			   if ((suma > 24)&&(b > 0)&&(rta==true))
				{
				 alert("<?php echo $AppUI->_('noduplica');?>");
				 rta = false;
				}


			  var new_end_time = suma+":"+b;

			}
			old_dates[old_dates.length] = dates[i];
			old_dates_ts[old_dates_ts.length] = dates_ts[i];
			old_hours[old_hours.length] = hours[i];
			old_start_time[old_start_time.length] = start_time[i];
			old_end_time[old_end_time.length] = end_time[i];
	       }
           
		   // voy a ver cuantas horas ya cargo para ese día //
           var canth = 0;

           for(var i = 0; i < dates.length; i++){
			  
			   if (dates[i]==new_dates)
				{
				canth = canth + hours[i];
				}
		   }
           
		   canth = canth + cant_horas;

		   if ((canth > 24 )&&(rta==true))
	        {
			  alert("<?php echo $AppUI->_('noadd');?>"+new_dates+"<?php echo $AppUI->_('noadd2');?>");
			  rta = false;
	        }

			var	final_dates  = new Array();
			var	final_dates_ts  = new Array();
			var	final_hours  = new Array();
			var final_start_time = new Array();
			var final_end_time = new Array();

			total = old_dates.length + 1;
            
			for(var i = 0; i < total; i++){

				if(i < old_dates.length)
				{
				 final_dates[i] = old_dates[i];
				 final_dates_ts[i] = old_dates_ts[i];
				 final_hours[i] = old_hours[i];
				 final_start_time[i] = old_start_time[i];
				 final_end_time[i] = old_end_time[i];
				}
				else{
				 final_dates[i] = new_dates;
				 final_dates_ts[i] = new_dates_ts;
				 final_hours[i] = new_hours;
				 final_start_time[i] = new_start_time;
				 final_end_time[i] = new_end_time;
			    }
			}

  // Antes de grabar voy a ver si ya no fue duplicada esa misma franja //
	
     var cont = 0;

	 for(var i = 0; i < total; i++){
	      if ((final_start_time[i]==new_start_time)&&(final_dates[i]==new_dates))
		   { 
			cont = cont + 1;
		   }
	 }  
	
     
	if ((cont > 1)&&(rta==true))
	{ 
	  alert("<?php echo $AppUI->_('noduplica2');?>");
	  rta = false;
	}

	if (rta)
	{   
		dates = final_dates;
		dates_ts = final_dates_ts;
		hours = final_hours;
		start_time = final_start_time;
		end_time = final_end_time;

		if(dates.length == 0){
			frm.save.disabled = true;
		}

		frm.timexp_dates.value = dates_ts.join(",");
		frm.timexp_hours.value = hours.join(",");
		
		build_table_times();
	}
}


function delhour(ind){
	var frm = document.forms["batchFrm"];
	var	tmp_dates  = new Array();
	var	tmp_dates_ts  = new Array();
	var	tmp_hours  = new Array();
	var tmp_start_time = new Array();
	var tmp_end_time = new Array();

	for(var i = 0; i < dates.length; i++){
		if(ind!=i){
			tmp_dates[tmp_dates.length] = dates[i];
			tmp_dates_ts[tmp_dates_ts.length] = dates_ts[i];
			tmp_hours[tmp_hours.length] = hours[i];
			tmp_start_time[tmp_start_time.length] = start_time[i];
			tmp_end_time[tmp_end_time.length] = end_time[i];
		}
	}

	dates = tmp_dates;
	dates_ts = tmp_dates_ts;
	hours = tmp_hours;
	start_time = tmp_start_time;
	end_time = tmp_end_time;
	
	if(dates.length == 0){
		frm.save.disabled = true;
	}

	frm.timexp_dates.value = dates_ts.join(",");
	frm.timexp_hours.value = hours.join(",");
	
	build_table_times();

}

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
    
	if (rta){
		frm.submit();
		}
	
}

function delall(){
	var frm = document.forms["batchFrm"];
	var	tmp_dates  = new Array();
	var	tmp_dates_ts  = new Array();
	var	tmp_hours  = new Array();
	var tmp_start_time = new Array();
	var tmp_end_time = new Array();

	dates = tmp_dates;
	dates_ts = tmp_dates_ts;
	hours = tmp_hours;
	start_time = tmp_start_time;
	end_time = tmp_end_time;
	
	frm.save.disabled = true;
	
	build_table_times();
}

</script>
<!-- END OF TIMER RELATED SCRIPTS -->


<table cellspacing="2" cellpadding="2" border="0" width="100%" class="tableForm_bg">
<form name="batchFrm" action="" method="post" >
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
<tr>

	<td colspan="4">
		<?php
		$hour_types = array('Use the hours on each working day','Divide the hours equally on each working day');
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
		<input type="text" class="text" name="timexp_value" value="<?php echo $timexp->timexp_value;?>" maxlength="8" size="6"/>
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


<!-- Para que ingrese la hora de inicio, la hora de fin la calculo -->
<tr>
	<td   align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('Start Time');?>
	</td>
	<td>
    <input type='hidden' name="fecha_inicio" value="1">    
	<? if ($timexp->timexp_start_time =="")
	   {
		$timexp->timexp_start_time = "09:00";
	   }
	?>
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
  <td colspan="4" align="right">
       <input type="button" class="button" name="clear" value="<?php echo $AppUI->_('Clear');?>" onclick="delall();">
       <input type="button" class="button" name="generate" value="<?php echo $AppUI->_('generate');?>" onclick="generate_times('1');"/>
  </td>
<tr>
<tr>
	<td colspan="4">
		<table cellspacing="0" cellpadding="2" border="0" width="100%" class="" >
		<tr class="tableHeaderGral">
			<th width="25%"><?php echo $AppUI->_("Times");?>:</th>
			<th align="right">
			<?php echo $AppUI->_('Start Time');?>
			</th>
			<th align="left">&nbsp;
			<?php echo $AppUI->_('End Time');?>
			</th>
			<th align="left">
			<?php echo $AppUI->_($label_value);?>
			</th>
			<th align="right" width="15%">
			&nbsp;
			</th>
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
		<input type="button" name="save" disabled="true" class="button" value="<?php echo $AppUI->_('update');?>" onclick="validateTimexp();"/>
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