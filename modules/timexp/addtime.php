<?php /* TASKS $Id: addtime.php,v 1.7 2009-06-26 17:43:24 pkerestezachi Exp $ */

global  $task_id, $bug_id, $obj, $percent,$timexp_id, $rnd_type, $timexp_types, $external, $timexp_applied_to_types, $billables,$hideTitleBlock,$dialog;


require_once( "./classes/projects.class.php" );
require_once( "./classes/bugs.class.php" );
require_once( "./classes/todo.class.php" );

$rnd_type="1";

$canEdit = true;
$accessLog = PERM_EDIT;

if (!$rnd_type || !isset($timexp_types[$rnd_type])){
		$AppUI->setMsg( "timexp" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

$timexp_id = intval( dPgetParam( $_GET, 'timexp_id', 0 ) );

// si es la ventana popup s?o se puede agregar, no editar
if ($is_popup){
	unset($timexp_id);
}

$project="-1";
$project_name = "";
$redirect_url = "";
$timexp = new CTimExp();
$new = true;

//Obtengo el tipo de aplicaci?
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


$df = "%d/%m/%Y";
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

if (!$external && !$hideTitleBlock){

	// setup the title block
	$titleBlock = new CTitleBlock( $titleaction." ".$titleobject, 'timexp.gif', $m, "timexp.index" );
    
	if ($dialog != "1"){
		$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
		$titleBlock->addCrumb( "?m=timexp&a=vw_myday&sel_date_".strtolower($sufix)."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "my daily view" );	
		if ($spvMode){
			
		$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");
		$titleBlock->addCrumb("?m=timexp&a=vw_sup_day&sel_date_".strtolower($sufix)."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "daily supervision");
		}
		if (!$new)
			$titleBlock->addCrumb( "?m=timexp&a=view&timexp_id=$timexp_id", "view ".strtolower($sufix) );
	}
	$titleBlock->show();
	
}

// Obtengo los proyectos en los cuales el usuario puede agregar tareas
$projects = CProject::projectPermissions();


$jsProject = "var prj = new Array();\n";
if (count($projects)){
	$jsProject .= " prj[".implode($projects, "] = '1'; \n prj[")."] = '1'; \n";
}

$CProjects = new CProjects();

$CProjects->loadCompanies(true);
//$CProjects->addItemAtBeginOfCompanies($CProjects->addItem(0, $AppUI->_('Not Specified') ));

$CProjects->loadProjects(null, true);
//$CProjects->addItemAtBeginOfProjects($CProjects->addItemProject("0","0", $AppUI->_('Not Specified') ));

$CProjects->loadTasks(null, null, true);
//$CProjects->addItemAtBeginOfTasks($CProjects->addItemTask("0","0",$AppUI->_('Not Specified') ));


$CBugs = new CBugs();

$CBugs->loadCompanies(true);
//$CBugs->addItemAtBeginOfCompanies($CBugs->addItem(0, $AppUI->_('Not Specified') ));

$CBugs->loadProjects(null, true);
//$CBugs->addItemAtBeginOfProjects($CBugs->addItemProject("0","0", $AppUI->_('Not Specified') ));

$CBugs->loadBugs();
//$CBugs->addItemAtBeginOfBugs($CBugs->addItemBug("0","0", $AppUI->_('Not Specified') ));


$CTodos = new CTodos();

$CTodos->loadCompanies(true);
//$CTodos->addItemAtBeginOfCompanies($CTodos->addItem(0, $AppUI->_('Not Specified') ));

$CTodos->loadProjects(null, true);
//$CTodos->addItemAtBeginOfProjects($CTodos->addItemProject("0","0", $AppUI->_('Not Specified') ));

$CTodos->loadTodos();
//$CTodos->addItemAtBeginOfTodos($CTodos->addItemTodo("0","0", $AppUI->_('Not Specified') ));


 // Traigo los items para el campo nothing de la bd //


$lenguage = $AppUI->user_prefs[LOCALE];

$descrip_not = "descrip_".$lenguage;
			   
$sql = "select $descrip_not from timexp_exp order by $descrip_not asc";
$query = mysql_query($sql);
$strJS_nothing = "var arNothings = new Array();\n";              
			   
 while ($nothing = mysql_fetch_array($query))
 {
 $nada =  html_entity_decode($nothing[0]);
 $strJS_nothing .= "arNothings[arNothings.length] = new Array('$nada', '$nada');\n";
 }

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

	
function ValidTime(h, m, s) {
    with (new Date(0, 0, 0, h, m, s)) {
        return ((getHours() == h) && (getMinutes() == m));
    }
}


function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.batchFrm.timexp_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

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
    var p_horas = parseInt(field.value); // Par?etro de comparaci? 

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

/* Funcion para la generaci? de los datos */
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
		frm.generate.style.display = 'none';
		frm.addbuton.style.display = '';
		
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

	var	old_dates  = new Array();
	var	old_dates_ts  = new Array();
	var	old_hours  = new Array();
	var old_start_time = new Array();
	var old_end_time = new Array();
	var old_applied_to_type = new Array();
	var old_project_id_task = new Array();
	var old_project_id_bug = new Array();
	var old_project_id_todo = new Array();
	var old_task_id = new Array();
	var old_bug_id = new Array();
	var old_id_todo = new Array();
	var old_descrip = new Array();
	var old_billable = new Array();
	var old_named = new Array();
	var old_appid = new Array();
	var old_company = new Array();

    
	      for(var i = 0; i < dates.length; i++){
			old_dates[old_dates.length] = dates[i];
			old_dates_ts[old_dates_ts.length] = dates_ts[i];
			old_hours[old_hours.length] = hours[i];
			old_start_time[old_start_time.length] = start_time[i];
			old_end_time[old_end_time.length] = end_time[i];
			old_applied_to_type[old_applied_to_type.length] = applied_to_type[i];
            old_project_id_task[old_project_id_task.length] = project_id_task[i];
		    old_project_id_bug[old_project_id_bug.length] = project_id_bug[i];
			old_project_id_todo[old_project_id_todo.length] = project_id_todo[i];
		    old_task_id[old_task_id.length] = task_id[i];
            old_bug_id[old_bug_id.length] = bug_id[i];
			old_id_todo[old_id_todo.length] = id_todo[i];
			old_descrip[old_descrip.length] = descrip[i];
		    old_billable[old_billable.length] = billable[i];
			old_named[old_named.length] = named[i];
			old_appid[old_appid.length] = appid[i];
            old_company[old_company.length] = company[i];
	       }
           
			var	new_dates  = new Array();
			var	new_dates_ts  = new Array();
			var	new_hours  = new Array();
			var new_start_time = new Array();
			var new_end_time = new Array();
			var new_applied_to_type = new Array();
			var new_project_id_task = new Array();
			var new_project_id_bug = new Array();
			var new_project_id_todo = new Array();
			var new_task_id = new Array();
			var new_bug_id = new Array();
			var new_id_todo = new Array();
			var new_descrip = new Array();
			var new_billable = new Array();
			var new_named = new Array();
	        var new_appid = new Array();
			var new_company = new Array();
           
		   // Calculo los dias , la cantidad de horas por dia //
		   	var valor = parseFloat(frm.timexp_value.value);

		    var datePat = /^(\d{4})(\d{2})(\d{2})$/;
			var strDateFormat = frm.timexp_from_date_format.value;
			var matchArray1 = frm.timexp_from_date.value.match(datePat);
			matchArray1[2] = matchArray1[2]-1;
			var from = new Date(matchArray1[1], matchArray1[2], matchArray1[3], 0, 0, 0);
	
			var matchArray2 = frm.timexp_to_date.value.match(datePat);
			matchArray2[2] = matchArray2[2]-1;
			var to = new Date(matchArray2[1], matchArray2[2], matchArray2[3], 0, 0, 0);		
			var one_day = 1000 * 60 * 60 * 24;
			var to_ms = to.getTime();
			var from_ms = from.getTime();
			var dif = ((to_ms + one_day) - from_ms)/one_day;
			var hour_day = Math.round((valor / dif)*100)/100;

			for (var i = 0; i<dif; i++){
				var curdate = strDateFormat;

				new_applied_to_type[i] = frm.timexp_applied_to_type.value;
                new_project_id_task[i] = parseInt(frm.project_id_task.value);
				new_project_id_bug[i] = parseInt(frm.project_id_bug.value);
				new_project_id_todo[i] = parseInt(frm.project_id_todo.value);

				new_task_id[i] = frm.task_id.value;
                new_bug_id[i] = frm.bug_id.value;
				new_id_todo[i] = frm.id_todo.value;
				new_descrip[i] = '';

				if(new_applied_to_type[i]==3)
				{
                new_named[i] = frm.timexp_name2.value;
				new_appid[i] = '0';
				new_company[i]='0';
				} 
			
				if (new_applied_to_type[i]==1)
				{
				new_named[i] = frm.task_id.value;
				new_appid[i] = frm.task_id.value;
				new_company[i]= parseInt(frm.idcompany.value);
				}

				if (new_applied_to_type[i]==2)
				{
				new_named[i] = frm.bug_id.value;
				new_appid[i] = frm.bug_id.value;
				new_company[i]= parseInt(frm.id_company_bug.value);
				}

				if (new_applied_to_type[i]==4)
				{
				new_named[i] = frm.id_todo.value;
				new_appid[i] = frm.id_todo.value;
				new_company[i]= parseInt(frm.idcompany_todo.value);
				}

				new_billable[i] = frm.timexp_billable_box.value;

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
				hour_day = parseFloat(frm.timexp_value.value);
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
			var temp_applied_to_type = new Array();
			var temp_project_id_task = new Array();
			var temp_project_id_bug = new Array();
			var temp_project_id_todo = new Array();
			var temp_task_id = new Array();
			var temp_id_todo = new Array();
			var temp_bug_id = new Array();
			var temp_descrip = new Array();
			var temp_billable = new Array();
			var temp_named = new Array();
			var temp_appid = new Array();
			var temp_company = new Array();

            
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
						  temp_applied_to_type[k] = new_applied_to_type[i];
						  temp_project_id_task[k] = new_project_id_task[i];
						  temp_project_id_bug[k] = new_project_id_bug[i];
						  temp_project_id_todo[k] = new_project_id_todo[i];
						  temp_task_id[k] = new_task_id[i];
						  temp_bug_id[k] = new_bug_id[i];
						  temp_id_todo[k] = new_id_todo[i];
						  temp_descrip[k] = new_descrip[i];
						  temp_billable[k] = new_billable[i];
						  temp_named[k] = new_named[i];
						  temp_appid[k] = new_appid[i];
						  temp_company[k] = new_company[i];
                         
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
			var final_applied_to_type = new Array();
			var final_project_id_task = new Array();
			var final_project_id_bug = new Array();
			var final_project_id_todo = new Array();
			var final_task_id = new Array();
			var final_bug_id = new Array();
			var final_id_todo = new Array();
			var final_descrip = new Array();
			var final_billable = new Array();
			var final_named = new Array();
			var final_appid = new Array();
			var final_company = new Array();

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
				 final_applied_to_type[i] = old_applied_to_type[i];
				 final_project_id_task[i] = old_project_id_task[i];
				 final_project_id_bug[i] = old_project_id_bug[i];
				 final_project_id_todo[i] = old_project_id_todo[i];
				 final_task_id[i] = old_task_id[i];
				 final_bug_id[i] = old_bug_id[i];
				 final_id_todo[i] = old_id_todo[i];
				 final_descrip[i] = old_descrip[i];
				 final_billable[i] = old_billable[i];
				 final_named[i] = old_named[i];
			     final_appid[i] = old_appid[i];
				 final_company[i] = old_company[i];

				}
				else{
					  
				final_dates[i] = temp_dates[ind];
				final_dates_ts[i] = temp_dates_ts[ind];
				final_hours[i] = temp_hours[ind];
				final_start_time[i] = temp_start_time[ind];
				final_end_time[i] = temp_end_time[ind];
				final_applied_to_type[i] = temp_applied_to_type[ind];
				final_project_id_task[i] = temp_project_id_task[ind];
				final_project_id_bug[i] = temp_project_id_bug[ind];
				final_project_id_todo[i] = temp_project_id_todo[ind];
				final_task_id[i] = temp_task_id[ind];
				final_bug_id[i] = temp_bug_id[ind];
				final_id_todo[i] = temp_id_todo[ind];
				final_descrip[i] = temp_descrip[ind];
				final_billable[i] = temp_billable[ind];
				final_named[i] = temp_named[ind];
			    final_appid[i] = temp_appid[ind];
                final_company[i] = temp_company[ind];


				ind ++;
							 
			    }
			}

	dates = final_dates;
	dates_ts = final_dates_ts;
	hours = final_hours;
	start_time = final_start_time;
	end_time = final_end_time;
	applied_to_type = final_applied_to_type;
	project_id_task = final_project_id_task;
	project_id_bug = final_project_id_bug;
	project_id_todo = final_project_id_todo;
	task_id = final_task_id;
	bug_id = final_bug_id;
	id_todo = final_id_todo;
	descrip = final_descrip;
	billable = final_billable;
	named = final_named;
	appid = final_appid;
	company = final_company;

	if(dates.length == 0){
		frm.save.disabled = true;
	}

	frm.timexp_dates.value = dates_ts.join(",");
	frm.timexp_hours.value = hours.join(",");
	
	
	build_table_times();

}

function genera_vector(){
           
		   var frm = document.forms["batchFrm"];
		   var valor = parseFloat(frm.timexp_value.value);
               
		// Calculo los dias , la cantidad de horas por dia //
			dates  = new Array();
			dates_ts  = new Array();
			hours  = new Array();
			start_time = new Array();
			end_time = new Array();
			applied_to_type = new Array();
			project_id_task = new Array();
			project_id_bug = new Array();
			project_id_todo = new Array();
			task_id = new Array();
			bug_id = new Array();
			id_todo = new Array();
			descrip = new Array();
			billable = new Array();
			named = new Array();
			appid = new Array();
			company = new Array();
		

		    var datePat = /^(\d{4})(\d{2})(\d{2})$/;
			var strDateFormat = frm.timexp_from_date_format.value;
			var matchArray1 = frm.timexp_from_date.value.match(datePat);
			matchArray1[2] = matchArray1[2]-1;
			var from = new Date(matchArray1[1], matchArray1[2], matchArray1[3], 0, 0, 0);
	        
			var matchArray2 = frm.timexp_to_date.value.match(datePat);
			matchArray2[2] = matchArray2[2]-1;
			var to = new Date(matchArray2[1], matchArray2[2], matchArray2[3], 0, 0, 0);		
			var one_day = 1000 * 60 * 60 * 24;
			var to_ms = to.getTime();
			var from_ms = from.getTime();
			var dif = ((to_ms + one_day) - from_ms)/one_day;
			var hour_day = Math.round((valor / dif)*100)/100;
            

			for (var i = dates.length; i<dif; i++){
                
                applied_to_type[i] = frm.timexp_applied_to_type.value;
                project_id_task[i] = parseInt(frm.project_id_task.value);
				project_id_bug[i] = frm.project_id_bug.value;
				project_id_todo[i] = frm.project_id_todo.value;
				task_id[i] = frm.task_id.value;
                bug_id[i] = frm.bug_id.value;
				id_todo[i] = frm.id_todo.value;
				billable[i] = frm.timexp_billable_box.value;
				descrip[i] = '';

				if(applied_to_type[i]==3)
				{
                named[i] = frm.timexp_name2.value;
				appid[i] = '0';
				company[i]='0';
				} 
			
				if (applied_to_type[i]==1)
				{
				named[i] = frm.task_id.value;
				appid[i] = frm.task_id.value;
                company[i] = frm.idcompany.value;
				}

				if (applied_to_type[i]==2)
				{
				named[i] = frm.bug_id.value;
				appid[i] = frm.bug_id.value;
				company[i] = frm.id_company_bug.value;
				}

				if (applied_to_type[i]==4)
				{
				named[i] = frm.id_todo.value;
				appid[i] = frm.id_todo.value;
				company[i] = frm.idcompany_todo.value;
				}
                
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
				hour_day = parseFloat(frm.timexp_value.value);
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

	var html_header ='<table cellspacing="1" cellpadding="2" border="0" width="99%" style="background-color:#ffffff;"><tr style="background-color: #000000; font-weight: bold; color: #FFFFFF;"><th><?php echo $AppUI->_("Times");?>:</th><th align="right"><?php echo $AppUI->_("Start Time");?></th><th align="left"><?php echo $AppUI->_("End Time");?></th><th align="left"><?php echo $AppUI->_($label_value);?></th><th align="right" ><?php echo $AppUI->_("Applied to");?></th><th align="right" ><?php echo $AppUI->_("Company");?></th><th align="right" ><?php echo $AppUI->_("Project");?></th><th align="right" ><?php echo $AppUI->_("Name");?></th><th align="right" ><?php echo $AppUI->_("Description");?></th><th align="right" ><?php echo $AppUI->_("Billable");?></th><th align="right"></th><th align="right"></th></tr>';
	var html_footer = '</table>';
        
	var no_data	='<tr style="background-color:#ffffff;"><td colspan="12"><?php echo $AppUI->_("No data available");?></td></tr>';
	var row		='<tr onclick="javascript:this.style.backgroundColor=\'#99CCFF\';" onfocus="javascript:this.style.backgroundColor=\'#99CCFF\';" onblur="javascript:this.style.backgroundColor=\'white\'; document.forms["batchFrm"].focus();" ><td class="hilite" width="80">[DATE]</td><td class="hilite" align="right" >[START_HOURS]</td><td class="hilite" align="right">[END_HOURS]</td><td class="hilite" align="right" >[HOURS]</td><td class="hilite" align="right" width="60px">[APPLIED]</td><td class="hilite" align="right" width="60px">[COMPANY]</td><td class="hilite" align="right" width="60px">[PROJECT]</td><td class="hilite" align="right" width="60px">[NAME]</td><td class="hilite" align="right" width="60px">[DESCRIP]</td><td class="hilite" align="right" width="60px">[BILABLE]</td><td class="hilite" width="20px">[DEL]</td><td class="hilite" width="20px">[DUPLICA]</td></tr>';
	var row_total	='<tr style="border-top: 1px solid black;"><td class="hilite">[DATE]</td><td class="hilite" align="right" >&nbsp;</td><td class="hilite" align="right" >&nbsp;</td><td class="hilite" align="right" >[HOURS]</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" align="right" width="60px">&nbsp;</td><td class="hilite" width="20px">[DEL]</td><td class="hilite" width="20px">&nbsp;</td></tr>';
	var content = '';
    

	if (dates.length == hours.length && dates.length > 0){

		for(var i = 0; i < dates.length; i++){

			if (applied_to_type[i]==1)
			{
				var sel1 ="selected";
				var sel2 ="";
				var sel3 ="";
				var sel4 ="";
				var company_t_field ='<? echo $CProjects->generateHTMLcboCompanies_tabla("'+ company[i] +''","text"," onchange=\"javascript:changeCompany_table(' + i +',this);\" "); ?>';

				var select = 'value="'+ company[i] +'"';
				var select2 = 'value="'+ company[i] +'" selected';
		      
                company_t_field = company_t_field.replace(select,select2);

			    var company_field = company_t_field;


			}

			if (applied_to_type[i]==2)
			{
				var sel2 ="selected";
				var sel1 ="";
				var sel3 ="";
				var sel4 ="";
				
				var company_b_field ='<? echo $CBugs->generateHTMLcboCompanies_tabla("'+ company[i] +''","text"," onchange=\"javascript:changeCompany_table(' + i +',this);\" "); ?>';

				var select = 'value="'+ company[i] +'"';
				var select2 = 'value="'+ company[i] +'" selected';
		      
                company_b_field = company_b_field.replace(select,select2);

			    var company_field = company_b_field;

			}
            
			if (applied_to_type[i]==3)
			{
				var sel3 ="selected";
				var sel2 ="";
				var sel1 ="";
				var sel4 ="";
				var company_field ='';
			}
            

            if (applied_to_type[i]==4)
			{
				var sel2 ="";
				var sel1 ="";
				var sel3 ="";
				var sel4 ="selected";

				var company_to_field ='<? echo $CTodos->generateHTMLcboCompanies_tabla("'+ company[i] +''","text"," onchange=\"javascript:changeCompany_table(' + i +',this);\" "); ?>';

				var select = 'value="'+ company[i] +'"';
				var select2 = 'value="'+ company[i] +'" selected';
		      
                company_to_field = company_to_field.replace(select,select2);

			    var company_field = company_to_field;

			}

			var date_field = '<input type="hidden" name="timexp_date[]" value="' + dates_ts[i] + '" />' + dates[i];
			var hour_field = '<input type="text" name="timexp_value[]" class="text" size="4" align="right" value="' + hours[i] + '"onkeypress="return numeralsOnly(event)" onblur="updateHour(' + i +',this);" style="text-align: right;"/>';// + hours[i];
			var start_hour_field = '<input type="text" name="start_time[]" class="text" size="4" align="right" value="' + start_time[i] +'"  onblur="updatestart(' + i +',this);" style="text-align: right;"/>'
			var ends_hour_field = '<input type="text" name="end_time[]" class="text" size="4" align="right" value="' + end_time[i] + '"  onblur="updatend(' + i +',this);" style="text-align: right;"/ enabled>';
			var del_field = '<a href="javascript: delhour(' + i + ');" ><?php echo dPshowImage( './images/icons/trash_small.gif', 16,16, 'delete' ) ?></a>';
			var dup_field = '<a href="javascript: duphour(' + i + ');" ><?php echo dPshowImage( './images/article_management.gif', 20, 20, 'duplicate' ) ?></a>';
			var applied_to_field = '<select name="applied_to_type[]" size="1" class="text" onchange="javascript:changeApplied_table(' + i +',this);" ><option value="1" '+ sel1 +'><?php echo $AppUI->_("Task");?></option><option value="2" '+ sel2 +'><?php echo $AppUI->_("Bug");?></option><option value="3" '+ sel3 +'><?php echo $AppUI->_("Internal");?></option><option value="4" '+ sel4 +'><?php echo $AppUI->_("To-do");?></option></select>';
			var descrip_field = '<input type="text" class="text" name="timexp_description[]" value="'+ descrip[i]+'" maxlength="255" size="15" onblur="updatedescript(' + i +',this);" />';
			
            
			if (applied_to_type[i]==1)
            {
			/* Projectos */
            var Temp = new Array();
			Temp = arProjects;

			var select1 = '<select name=\"project_id_task[]\" class=\"text\" onchange=\"javascript: changeProjectSel_table(' + i +',this);\" >';
			var select3 = '</select>';

						for(var r = 0; r < Temp.length; r++){
						   
						  if (Temp[r][0]==company[i])
							{    
								if (project_id_task[i]==Temp[r][1])
								{
								var sel = 'selected';
								}
								else
								{
								var sel ='';
								}

								select2 += '<option value="'+Temp[r][1]+'" '+ sel +'>'+Temp[r][2]+'</option>';
							}
						}
				

            var project_t_field = select1+select2+select3; 
			var project_field = project_t_field;

			
			/* Fin de Projectos-Tareas */

			/* Tareas */
			var Temp = new Array();
			Temp = arTasks;
            
			var select1_t = '<select name=\"named[]\" class=\"text\" onchange=\"javascript: changeTaskSel_table(' + i +',this);\" >';
			var select3_t = '</select>';
            
			for(var k = 0; k < Temp.length; k++){

			  if (Temp[k][0]==project_id_task[i])
				{   
				    if (named[i]==Temp[k][1])
					{
					var sel = 'selected';
					}
					else
					{
					var sel ='';
					}
                    select += '<option value="'+Temp[k][1]+'" '+ sel +'>'+Temp[k][2]+'</option>';
				}
			}

            var name_field = select1_t+select+select3_t; 

			
			    if (billable[i]==0)
				{
				 var sel1 = 'selected';
				 var sel2 = '';
				}
				else
				{
				var sel2 = 'selected';
				var sel1 ='';
				}
			
			var bilable_field = '<select name="timexp_billable_box[]" size="1" class="text" onchange=\"javascript: changebilableSel_table(' + i +',this);\"><option value="0" '+ sel1 +'><?php echo $AppUI->_("No");?></option><option value="1" '+ sel2 +'><?php echo $AppUI->_("Yes");?></option></select>';

			}
            
			if (applied_to_type[i]==2)
            {

			/* Projectos */
            var Temp = new Array();
			Temp = arProjects_bug;
            
			var elect1 = '<select name=\"project_id_bug[]\" class=\"text\" onchange=\"javascript: changeProjectSel_table(' + i +',this);\" >';
			var elect3 = '</select>';
            
			
					for(var z = 0; z < Temp.length; z++){

					  if (Temp[z][0]==company[i])
						{   
							if (project_id_bug[i]==Temp[z][1])
							{
							var sel = 'selected';
							}
							else
							{
							var sel ='';
							}

							select2 +='<option value="'+Temp[z][1]+'" '+ sel +'>'+Temp[z][2]+'</option>';

							
						}
					}
				
            var project_b_field = elect1+select2+elect3; 

			var project_field = project_b_field;
			
			/* Fin de Projectos-Bugs */

			/* Bugs */
            
			var Temp = new Array();
			Temp = arBugs;
            
            var select1 = '<select name=\"named[]\" class=\"text\" onchange=\"javascript: changeBugSel_table(' + i +',this);\">';
            var select2 = '';
			var select3 = '</select>';

			for(var h = 0; h < Temp.length; h++){

			  if (Temp[h][0]==project_id_bug[i])
				{
				   if (named[i]==Temp[h][1])
					{
					var sel = 'selected';
					}
					else
					{
					var sel ='';
					}

                 select2 += '<option value="'+Temp[h][1]+'" '+ sel +'>'+Temp[h][2]+'</option>';
				}
			}

            var name_field = select1+select2+select3; 
			
			if (billable[i]==0)
				{
				 var selb1 = 'selected';
				 var selb2 = '';
				}
				else
				{
				var selb2 = 'selected';
				var selb1 = '';
				}
			
			var bilable_field = '<select name="timexp_billable_box[]" size="1" class="text" onchange=\"javascript: changebilableSel_table(' + i +',this);\"><option value="0" '+ selb1 +'>No</option><option value="1" '+ selb2 +'><?php echo $AppUI->_("Yes");?></option></select>';

            
			}

			if (applied_to_type[i]==4)
            {

			/* Projectos */
            var Temp = new Array();
			Temp = arProjects_todo;
            
			var elect1 = '<select name=\"project_id_todo[]\" class=\"text\" onchange=\"javascript: changeProjectSel_table(' + i +',this);\" >';
			var elect3 = '</select>';
            
			
					for(var e = 0; e < Temp.length; e++){

					  if (Temp[e][0]==company[i])
						{   
							
							if (project_id_todo[i]==Temp[e][1])
							{
							var sel_to = 'selected';
							}
							else
							{
							var sel_to ='';
							}
							
							select2 +='<option value="'+Temp[e][1]+'" '+ sel_to +'>'+Temp[e][2]+'</option>';

							
						}
					}
				
            var project_to_field = elect1+select2+elect3; 

			var project_field = project_to_field;
			
			/* Fin de Projectos-Todos */

			/* Todos */
            
			var Temp = new Array();
			Temp = arTodos;
            
            var select1 = '<select name=\"named[]\" class=\"text\" onchange=\"javascript: changeTodoSel_table(' + i +',this);\">';
            var select2 = '';
			var select3 = '</select>';

			for(var f = 0; f < Temp.length; f++){
             
			  if (Temp[f][0]==project_id_todo[i])
				{   
				   
				   if (named[i]==Temp[f][1])
					{
					var sel_td = 'selected';
					}
					else
					{
					var sel_td ='';
					}

                 select2 += '<option value="'+Temp[f][1]+'" '+ sel_td +'>'+Temp[f][2]+'</option>';
				 
				}
			}

            var name_field = select1+select2+select3; 
			
			if (billable[i]==0)
				{
				 var selb1 = 'selected';
				 var selb2 = '';
				}
				else
				{
				var selb2 = 'selected';
				var selb1 = '';
				}
			
			var bilable_field = '<select name="timexp_billable_box[]" size="1" class="text" onchange=\"javascript: changebilableSel_table(' + i +',this);\"><option value="0" '+ selb1 +'>No</option><option value="1" '+ selb2 +'><?php echo $AppUI->_("Yes");?></option></select>';

            
			}

			if (applied_to_type[i]==3)
            {
			/* Nothing */
			var project_t_field = '&nbsp;';

			var project_field = project_t_field;
			/* Nothing */
            

			<?=$strJS_nothing;?>
				
			var selectN1 = '<select name=\"named[]\" class=\"text\" size="1" onchange=\"javascript: changeNothingSel_table(' + i +',this);\">';
            var selectN2 = '';
			var selectN3 = '</select>';
            
			for(var h = 0; h < arNothings.length; h++){
             				   
				   if (named[i]==arNothings[h][1])
					{
					var sel_not = 'selected';
					}
					else
					{
					var sel_not ='';
					}
                 
                 selectN2 += '<option value="'+arNothings[h][1]+'" '+ sel_not +'>'+arNothings[h][1]+'</option>';
			}

			var name_field = selectN1+selectN2+selectN3;


			var bilable_field = 'No';
			}

            
			content += row.replace('[DATE]', date_field).replace('[START_HOURS]', start_hour_field).replace('[END_HOURS]',ends_hour_field).replace('[HOURS]', hour_field).replace('[APPLIED]', applied_to_field).replace('[COMPANY]', company_field).replace('[PROJECT]', project_field).replace('[NAME]', name_field).replace('[DESCRIP]', descrip_field).replace('[BILABLE]', bilable_field).replace('[DEL]', del_field).replace('[DUPLICA]', dup_field); 
		}
		
		var hour_field = '<b><span id="timexp_total_hours" name="timexp_total_hours" style="text-align: right;">&nbsp;</span></b>';// + hours[i];		
		//total hours
		content += row_total.replace('[DATE]', '<b><?php echo $AppUI->_('Total Hours');?></b>').replace('[HOURS]', hour_field).replace('[DEL]', '&nbsp;').replace('[DUPLICA]', '&nbsp;');	
		frm.save.disabled = false;
		
		obj.innerHTML = html_header + content + html_footer;
		recalculateTotal();
	}else{
		content = no_data;
		obj.innerHTML = html_header + content + html_footer;
	}

    if(dates.length == 0){
		frm.generate.disabled = false;
	}

}


function changeApplied_table(ind,obj){
	var frm = document.forms["batchFrm"];
	var new_Applied = obj.value;
    
	for(var i = 0; i < dates.length; i++){
        
		if (ind==i){
		   applied_to_type[i] = new_Applied;

		   if (new_Applied=="3")
			{
		     named[i] = 'Reunión Interna';
			 company[i] = '0';
			}
			else
			{
             named[i] ='0';
			}


			project_id_todo[i]='0';
			project_id_bug[i]='0';
			project_id_task[i]='0';
			task_id[i]='0';
			bug_id[i] = '0';
			id_todo[i]='0'; 

			 if (new_Applied=="1")
			 {
                Temp = arProjects;
				var cia_temp = 0;

				  for(var r = 0; r < Temp.length; r++){
					  if (Temp[r][0]== company[i] )
					  {  
					   var proj_t = Temp[r][1];
					   r = Temp.length;
					   var cia_temp = 1
					  }
				 }
                 
				 Temp = arTasks;

				 for(var r = 0; r < Temp.length; r++){

				  if (Temp[r][0]== proj_t )
				  {  
				  var task_temp = Temp[r][1];
				  r = Temp.length;
				  }
				 }
                 

				 if (!cia_temp){
				 company[i] = '0';
				 }
				 else{
				 project_id_task[i] = proj_t;
				 task_id[i]= task_temp;
				 named[i]= task_temp;
				 }

			 }

			 if (new_Applied=="2")
			 {
                Temp = arProjects_bug;
				var cia_temp = 0;

				  for(var r = 0; r < Temp.length; r++){
					  if (Temp[r][0]== company[i] )
					  {  
					  var proj_b = Temp[r][1];
					  r = Temp.length;
					  var cia_temp = 1
					  }
				 }
                 
                 Temp = arBugs;

				 for(var r = 0; r < Temp.length; r++){

				  if (Temp[r][0]== proj_b )
				  {  
				  var bug_temp = Temp[r][1];
				  r = Temp.length;
				  }
				 }

				 if (!cia_temp){
				 company[i] = '0';
				 }
				 else{
				 project_id_bug[i] = proj_b;
				 bug_id[i] = bug_temp;
				 named[i] = bug_temp;
				 }
			 }

			 if (new_Applied=="4")
			 {
                Temp = arProjects_todo;
				var cia_temp = 0;

				  for(var r = 0; r < Temp.length; r++){
					  if (Temp[r][0]== company[i] )
					  {  
					  var proj_to = Temp[r][1];
		              r = Temp.length;
					  var cia_temp = 1
					  }
				 }

				 Temp = arTodos;

				 for(var r = 0; r < Temp.length; r++){

					  if (Temp[r][0]== proj_to )
					  {  
					  var todo_temp = Temp[r][1];
					  r = Temp.length;
					  }
				 }
                 
				 if (!cia_temp){
				 company[i] = '0';
				 }
				 else{
					 project_id_todo[i] = proj_to;
					 id_todo[i] = todo_temp;
					 named[i] = todo_temp;
				 }
			 }
             
		}
	}
 	frm.save.disabled = true;
    build_table_times();
}

function changeCompany_table(ind,obj){  
    var frm = document.forms["batchFrm"];
	var new_company = obj.value;
    var Temp = new Array();

	/* Busco en el vector de projectos el primero para traer las tareas de ese project */
	Temp = arProjects;

	 for(var r = 0; r < Temp.length; r++){

	  if (Temp[r][0]== new_company )
	  {  
      var proj_t = Temp[r][1];
	  r = Temp.length;
	  }
	 }

	 Temp = arTasks;

	 for(var r = 0; r < Temp.length; r++){

	  if (Temp[r][0]== proj_t )
	  {  
      var task_temp = Temp[r][1];
	  r = Temp.length;
	  }
	 }
    
	 /* Busco en el vector de projectos el primero para traer los bugs de ese project */
	Temp = arProjects_bug;

	 for(var r = 0; r < Temp.length; r++){

	  if (Temp[r][0]== new_company )
	  {  
      var proj_b = Temp[r][1];
	  r = Temp.length;
	  }
	 }

	 Temp = arBugs;

	 for(var r = 0; r < Temp.length; r++){

	  if (Temp[r][0]== proj_b )
	  {  
      var bug_temp = Temp[r][1];
	  r = Temp.length;
	  }
	 }

	  /* Busco en el vector de projectos el primero para traer los todos de ese project */
	  Temp = arProjects_todo;

	 for(var r = 0; r < Temp.length; r++){

		  if (Temp[r][0]== new_company )
		  {  
		  var proj_to = Temp[r][1];
		  r = Temp.length;
		  }
	 }

	 Temp = arTodos;

	 for(var r = 0; r < Temp.length; r++){

		  if (Temp[r][0]== proj_to )
		  {  
		  var todo_temp = Temp[r][1];
		  r = Temp.length;
		  }
	 }
      
	 for(var i = 0; i < dates.length; i++){
        
		if (ind==i){
		   company[i] = new_company;
		   
		    if (applied_to_type[i]==1)
			 {
		     project_id_task[i] = proj_t;
			 project_id_bug[i] = '0';
			 project_id_todo[i] = '0';
			 task_id[i]= task_temp;
			 named[i]= task_temp;
			 }
            
			if (applied_to_type[i]==2)
			 {
		     project_id_bug[i] = proj_b;
             project_id_task[i] = '0';
			 project_id_todo[i] = '0';
			 bug_id[i] = bug_temp;
			 named[i] = bug_temp;
			 }

			if (applied_to_type[i]==3)
			 {
		     project_id_bug[i] = '0';
			 project_id_task[i] = '0';
			 bug_id[i] = '0';
			 task_id[i]= '0';
			 named[i] = 'Reunión Interna';
			 }

			 if (applied_to_type[i]==4)
			 {
		     project_id_todo[i] = proj_to;
             project_id_task[i] = '0';
			 project_id_bug[i] = '0';
			 id_todo[i] = todo_temp;
			 named[i] = todo_temp;
			 }
		}
	}
    
	frm.save.disabled = true;
    build_table_times();
}


function changeTaskSel_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_name = obj.value;
	var Temp = new Array();
	var  Bu = new Array(); 
	var k = 0;

	Temp = arTasks;


	  for(var i = 0; i < dates.length; i++){
         
		if (ind==i){

		    if (applied_to_type[i]==1)
			 {
		     task_id[i] = new_name;
			 bug_id[i]  = '0';
			 }
            
			if (applied_to_type[i]==2)
			 {
		     task_id[i] = '0';
			 bug_id[i]  = new_name;
			 }

			if (applied_to_type[i]==3)
			 {
		     task_id[i] = '0';
			 bug_id[i]  = '0';
			 }

		   task_id[i] = new_name;
		   named[i] = new_name;
		}
	}

    //alert('changeTaskSel_table:'+task_id);

	frm.save.disabled = true;
    build_table_times();
}

function updatedescript(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_desc = obj.value;

    for(var i = 0; i < dates.length; i++){
        
		/*if (ind==i){
		   new_desc = new_desc.replace(",",";");
		   new_desc = new_desc.replace("\"","'");
		   descrip[i] = new_desc;
		   
		}*/
		
		
		if (ind==i){
			new_desc = replaceChars(new_desc, ',', ';');
			new_desc = replaceChars(new_desc, '"', "'");
			descrip[i] = new_desc;
		}
		
	}
    
	frm.save.disabled = true;
    build_table_times();
}

function replaceChars(campo, entra, sale) {
			out = entra; // reemplazar la letra a
			add = sale; // por la letra z
			temp = "" + campo;
			
			while (temp.indexOf(out)>-1) {
			pos= temp.indexOf(out);
			
			temp = "" + (temp.substring(0, pos) + add + 
			temp.substring((pos + out.length), temp.length));
			}
			
		   
		    return temp;
}

function changeProjectSel_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_proj = obj.value;
        

		for(var i = 0; i < dates.length; i++){
		if (ind==i){

		    if (applied_to_type[i]==1)
			 {
		     project_id_task[i] = new_proj;
			 project_id_todo[i]  = '0';
			 project_id_bug[i]  = '0';

				 Temp = arTasks;

				 for(var r = 0; r < Temp.length; r++){

					  if (Temp[r][0]== new_proj )
					  {  
					  var task_temp = Temp[r][1];
					  r = Temp.length;
					  }
				 }
				
				 task_id[i]= task_temp;
			     named[i]= task_temp;

			 }
            
			if (applied_to_type[i]==2)
			 {
		     project_id_task[i] = '0';
			 project_id_todo[i]  = '0';
			 project_id_bug[i]  = new_proj;

			     Temp = arBugs;

				 for(var r = 0; r < Temp.length; r++){
					  if (Temp[r][0]== new_proj )
					  {  
					  var bug_temp = Temp[r][1];
					  r = Temp.length;
					  }
				 }

				 bug_id[i] = bug_temp;
			     named[i] = bug_temp;


			 }

			if (applied_to_type[i]==4)
			 {
		     project_id_task[i] = '0';
			 project_id_bug[i]  = '0';
             project_id_todo[i]  = new_proj;

				 Temp = arTodos;

				 for(var r = 0; r < Temp.length; r++){
					  if (Temp[r][0]== new_proj )
					  {  
					  var todo_temp = Temp[r][1];
					  r = Temp.length;
					  }
				   }

				   id_todo[i] = todo_temp;
			       named[i] = todo_temp;

			 }

		}
	}
    
	frm.save.disabled = true;
    build_table_times();
}


function changebilableSel_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_bilable = obj.value;
        

		for(var i = 0; i < dates.length; i++){
		if (ind==i){
		   billable[i] = new_bilable;
		}
	}

	frm.save.disabled = true;
    build_table_times();
}


function changeBug_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_proj = obj.value;
	var Temp = new Array();
	Temp = arBugs;
	var  Bu = new Array(); 

	var k = 0;

	for(var i = 0; i < dates.length; i++){
        
		if (ind==i){
		   project_id_bug[i] = new_proj;

			   for(var h = 0; h < Temp.length; h++){

				  if (Temp[h][0] == new_proj)
					{  
					   named[i]==Temp[h][2];
					   Bu[k] = h;

					   k = k+1;
					  
					}
				}

				l = Bu[0];
    
	            named[i]= Temp[l][1];
		}

	}
    


	frm.save.disabled = true;
    build_table_times();
}


function changeBugSel_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_bug = obj.value;

		for(var i = 0; i < dates.length; i++){
        
		if (ind==i){
		   named[i] = new_bug;
		}
	}

	frm.save.disabled = true;
    build_table_times();
}


function changeTodoSel_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_todo = obj.value;

		for(var i = 0; i < dates.length; i++){
        
		if (ind==i){
		   named[i] = new_todo;
		}
	}

	frm.save.disabled = true;
    build_table_times();
}


function changeNothingSel_table(ind,obj){
    var frm = document.forms["batchFrm"];
	var new_nothing = obj.value;

		for(var i = 0; i < dates.length; i++){
        
		if (ind==i){
		   named[i] = new_nothing;
		}
	}

	frm.save.disabled = true;
    build_table_times();
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

							if (resta >24)
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							old_start_time[i] = start_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}else if ((resta ==24)&&(b>0))
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

							if (suma >24)
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							old_start_time[i] = start_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}else if ((suma ==24)&&(b>0))
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

							if (suma >24)
							{
							old_hours[i] = hours[i];
							old_end_time[i] = end_time[i];
							alert("<?php echo $AppUI->_('timexpValue2');?>");
							rta = false;
							}else if ((suma ==24)&&(b>0))
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
	var old_applied_to_type = new Array();
	var old_project_id_task = new Array();
	var old_project_id_bug = new Array();
	var old_project_id_todo = new Array();
	var old_task_id = new Array();
	var old_bug_id = new Array();
	var old_id_todo = new Array();
	var old_descrip = new Array();
	var old_billable = new Array();
	var old_named = new Array();
	var old_appid = new Array();
	var old_company = new Array();
		  

	      for(var i = 0; i < dates.length; i++){

			if (ind==i){
			  var new_dates = dates[i];
			  var new_dates_ts = dates_ts[i];
			  var new_hours = hours[i];
			  var new_start_time = end_time[i];
			  var new_applied_to_type = applied_to_type[i];
			  var new_project_id_task = project_id_task[i];
			  var new_project_id_bug = project_id_bug[i];
			  var new_project_id_todo = project_id_todo[i];
			  var new_task_id = task_id[i];
			  var new_bug_id = bug_id[i];
			  var new_id_todo = id_todo[i];
			  var new_descrip = descrip[i];
			  var new_billable = billable[i];
			  var new_named = named[i];
	          var new_appid = appid[i];
			  var new_company = company[i];
              
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
			old_applied_to_type[old_applied_to_type.length] = applied_to_type[i];
            old_project_id_task[old_project_id_task.length] = project_id_task[i];
		    old_project_id_bug[ old_project_id_bug.length] = project_id_bug[i];
			old_project_id_todo[ old_project_id_todo.length] = project_id_todo[i];
		    old_task_id[old_task_id.length] = task_id[i];
            old_bug_id[old_bug_id.length] = bug_id[i];
			old_id_todo[old_id_todo.length] = id_todo[i];
			old_descrip[old_descrip.length] = descrip[i];
		    old_billable[old_billable.length] = billable[i];
			old_named[old_named.length] = named[i];
			old_appid[old_appid.length] = appid[i];
			old_company[old_company.length] = company[i];
	       }
           
		   // voy a ver cuantas horas ya cargo para ese d? //
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
			var final_applied_to_type = new Array();
			var final_project_id_task = new Array();
			var final_project_id_bug = new Array();
			var final_project_id_todo = new Array();
			var final_task_id = new Array();
			var final_bug_id = new Array();
			var final_id_todo = new Array();
			var final_descrip = new Array();
			var final_billable = new Array();
			var final_named = new Array();
			var final_appid = new Array();
			var final_company = new Array();


			total = old_dates.length + 1;
            
			for(var i = 0; i < total; i++){

				if(i < old_dates.length)
				{
				 final_dates[i] = old_dates[i];
				 final_dates_ts[i] = old_dates_ts[i];
				 final_hours[i] = old_hours[i];
				 final_start_time[i] = old_start_time[i];
				 final_end_time[i] = old_end_time[i];
				 final_applied_to_type[i] = old_applied_to_type[i];
				 final_project_id_task[i] = old_project_id_task[i];
				 final_project_id_bug[i] = old_project_id_bug[i];
				 final_project_id_todo[i] = old_project_id_todo[i];
				 final_task_id[i] = old_task_id[i];
				 final_bug_id[i] = old_bug_id[i];
				 final_id_todo[i] = old_id_todo[i];
				 final_descrip[i] = old_descrip[i];
				 final_billable[i] = old_billable[i];
				 final_named[i] = old_named[i];
			     final_appid[i] = old_appid[i];
				 final_company[i] = old_company[i];
				}
				else{
				 final_dates[i] = new_dates;
				 final_dates_ts[i] = new_dates_ts;
				 final_hours[i] = new_hours;
				 final_start_time[i] = new_start_time;
				 final_end_time[i] = new_end_time;
				 final_applied_to_type[i] = new_applied_to_type;
				 final_project_id_task[i] = new_project_id_task;
				 final_project_id_bug[i] = new_project_id_bug;
				 final_project_id_todo[i] = new_project_id_todo;
				 final_task_id[i] = new_task_id;
				 final_bug_id[i] = new_bug_id;
				 final_id_todo[i] = new_id_todo;
				 final_descrip[i] = new_descrip;
				 final_billable[i] = new_billable;
				 final_named[i] = new_named;
			     final_appid[i] = new_appid;
				 final_company[i] = new_company;
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
		applied_to_type = final_applied_to_type;
		project_id_task = final_project_id_task;
		project_id_bug = final_project_id_bug;
		project_id_todo = final_project_id_todo;
		task_id = final_task_id;
		bug_id = final_bug_id;
		id_todo = final_id_todo;
		descrip = final_descrip;
		billable = final_billable;
		named = final_named;
	    appid = final_appid;
		company = final_company;

		if(dates.length == 0){
			frm.save.disabled = true;
		}

		frm.timexp_dates.value = dates_ts.join(",");
		frm.timexp_hours.value = hours.join(",");
		//alert('Tasks: '+task_id);
		//alert('Bugs: '+bug_id);
		//alert('Todos: '+id_todo);
		
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
	var tmp_applied_to_type = new Array();
	var tmp_project_id_task = new Array();
	var tmp_project_id_bug = new Array();
	var tmp_project_id_todo = new Array();
	var tmp_task_id = new Array();
	var tmp_bug_id = new Array();
	var tmp_id_todo = new Array();
	var tmp_descrip = new Array();
	var tmp_billable = new Array();
	var tmp_named = new Array();
	var tmp_appid = new Array();
	var tmp_company = new Array();


	for(var i = 0; i < dates.length; i++){
		if(ind!=i){
			tmp_dates[tmp_dates.length] = dates[i];
			tmp_dates_ts[tmp_dates_ts.length] = dates_ts[i];
			tmp_hours[tmp_hours.length] = hours[i];
			tmp_start_time[tmp_start_time.length] = start_time[i];
			tmp_end_time[tmp_end_time.length] = end_time[i];
			tmp_applied_to_type[tmp_applied_to_type.length] = applied_to_type[i];
            tmp_project_id_task[tmp_project_id_task.length] = project_id_task[i];
		    tmp_project_id_bug[tmp_project_id_bug.length] = project_id_bug[i];
			tmp_project_id_todo[tmp_project_id_todo.length] = project_id_todo[i];
		    tmp_task_id[tmp_task_id.length] = task_id[i];
            tmp_bug_id[tmp_bug_id.length] = bug_id[i];
			tmp_id_todo[tmp_bug_id.length] = id_todo[i];
			tmp_descrip[tmp_descrip.length] = descrip[i];
		    tmp_billable[tmp_billable.length] = billable[i];
			tmp_named[tmp_named.length] = named[i];
			tmp_appid[tmp_appid.length] = appid[i];
			tmp_company[tmp_company.length] = company[i];
		}
	}

	dates = tmp_dates;
	dates_ts = tmp_dates_ts;
	hours = tmp_hours;
	start_time = tmp_start_time;
	end_time = tmp_end_time;
	applied_to_type = tmp_applied_to_type;
    project_id_task = tmp_project_id_task;
	project_id_bug = tmp_project_id_bug;
	project_id_todo = tmp_project_id_todo;
	task_id = tmp_task_id;
    bug_id = tmp_bug_id;
	id_todo = tmp_id_todo;
	descrip = tmp_descrip;
	billable = tmp_billable;
	named = tmp_named;
	appid = tmp_appid;
	company = tmp_company;
	
	if(dates.length == 0){
		frm.save.disabled = true;
		frm.generate.style.display = '';
		frm.addbuton.style.display = 'none';
	}

	frm.timexp_dates.value = dates_ts.join(",");
	frm.timexp_hours.value = hours.join(",");
	
	
	build_table_times();

}

function delall(){
	var frm = document.forms["batchFrm"];
	var	tmp_dates  = new Array();
	var	tmp_dates_ts  = new Array();
	var	tmp_hours  = new Array();
	var tmp_start_time = new Array();
	var tmp_end_time = new Array();
	var tmp_applied_to_type = new Array();
	var tmp_project_id_task = new Array();
	var tmp_project_id_bug = new Array();
	var tmp_project_id_todo = new Array();
	var tmp_task_id = new Array();
	var tmp_bug_id = new Array();
	var tmp_id_todo = new Array();
	var tmp_descrip = new Array();
	var tmp_billable = new Array();
	var tmp_named = new Array();
	var tmp_appid = new Array();
	var tmp_company = new Array();

	dates = tmp_dates;
	dates_ts = tmp_dates_ts;
	hours = tmp_hours;
	start_time = tmp_start_time;
	end_time = tmp_end_time;
	applied_to_type = tmp_applied_to_type;
    project_id_task = tmp_project_id_task;
	project_id_bug = tmp_project_id_bug;
	project_id_todo = tmp_project_id_todo;
	task_id = tmp_task_id;
    bug_id = tmp_bug_id;
	id_todo = tmp_id_todo;
	descrip = tmp_descrip;
	billable = tmp_billable;
	named = tmp_named;
	appid = tmp_appid;
	company = tmp_company;

	frm.generate.style.display = '';
	frm.addbuton.style.display = 'none';
	
	frm.save.disabled = true;
	
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
    
	
	var valor = parseFloat(frm.timexp_value.value);
	if (isNaN(valor) || valor <= 0){
		alert("<?php echo $AppUI->_('timexpValue');?>");
		rta = false;
		frm.timexp_value.focus();
	}
	
	if (dates.length == 0){
		alert("<?php echo $AppUI->_('timexpNoDates');?>");
		rta = false;
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

	var completos = 0;
	// Reviso el vector para asegurarme que todos los registros tengan cargado el nombre //
	  for(var i = 0; i < dates.length; i++){
          
		  if ((applied_to_type[i]==1)||(applied_to_type[i]==2))
		  {
			if (named[i]==0)
			  {
			   completos = 1;
			  }
		  }
	  }

	 if(completos ==1){
		alert("<?php echo $AppUI->_("timexpName");?>");
		rta = false;
	}
	
    
	if (rta){
		frm.timexp_dates.value = dates_ts.join(",");
		frm.timexp_hours.value = hours.join(",");
		frm.hora_inicio.value = start_time.join(",");
		frm.hora_final.value = end_time.join(",");
		frm.tapplied_to_type.value = applied_to_type.join(",");
		frm.timexp_named.value = named.join(",");
		frm.descripcion.value = descrip.join(",");
		frm.tbillable.value = billable.join(",");
		frm.company.value = company.join(",");
		frm.submit();
		}
}


function showError(msg){
	var panel = document.getElementById("errorDiv");
	if (panel){
		panel.innerHTML = '<font color="red">ERROR:</font>&nbsp;' + msg + '<br>';
	}
}

function cleanError(){
	var panel = document.getElementById("errorDiv");
	if (panel){
		panel.innerHTML = '';
	}
}


function changeApplied(sel, reset){
		var opts = new Array();
		opts[1] = "task";
		opts[2] = "incidence";
		opts[3] = "none";
        opts[4] = "todo";
        
		var opt = opts[parseInt(sel.options[sel.selectedIndex].value)];

		if (reset)
		sel.form.timexp_applied_to_id.value = "0";
		document.getElementById("taskdiv").style.display = 'none';
		document.getElementById("tododiv").style.display = 'none';
		document.getElementById("incidencediv").style.display = 'none';
		document.getElementById("billablediv").style.display = 'none';
		document.getElementById("nobillablediv").style.display = '';
		document.getElementById("projectdiv").style.display = 'none';


		if (opt!='none'){
			document.getElementById(opt + "div").style.display = '';
			document.getElementById("billablediv").style.display = '';
			document.getElementById("nobillablediv").style.display = 'none';
			document.getElementById("nothingdiv").style.display = 'none';
            
			if (opt=='task')
            {
			document.getElementById("projectdiv").style.display = '';
			document.getElementById("projectdiv2").style.display = 'none';
			document.getElementById("projectdiv3").style.display = 'none';
			document.getElementById("nothingdiv").style.display = 'none';
			}

			if (opt=='incidence')
            {
			document.getElementById("projectdiv").style.display = 'none';
			document.getElementById("projectdiv2").style.display = '';
			document.getElementById("projectdiv3").style.display = 'none';
			document.getElementById("nothingdiv").style.display = 'none';
			}

			if (opt=='todo')
            {
			document.getElementById("projectdiv").style.display = 'none';
			document.getElementById("projectdiv2").style.display = 'none';
			document.getElementById("projectdiv3").style.display = '';
			document.getElementById("nothingdiv").style.display = 'none';
			}

			document.getElementById("nothingdiv").style.display = 'none';
                     
		}
                else{
				 document.getElementById("projectdiv").style.display = 'none';
			     document.getElementById("projectdiv2").style.display = 'none';
				 document.getElementById("projectdiv3").style.display = 'none';
				 document.getElementById("nothingdiv").style.display = '';
					 
                }

         
	}	


<?php 
		$CProjects->setFrmName("batchFrm");
		$CProjects->setCboCompanies("idcompany");
		$CProjects->setCboProjects("project_id_task");
		$CProjects->setJSSelectedProject("project_id_task");
		$CProjects->setCboTasks("task_id");
		$CProjects->setJSSelectedTask("task_id");
		
		echo $CProjects->generateJS();
		echo $CProjects->generateJSTask();

		$CBugs->setFrmName("batchFrm");
		$CBugs->setCboCompanies("idcompany");
		$CBugs->setCboProjects("project_id_bug");
		$CBugs->setJSSelectedProject("project_id_bug");
		$CBugs->setCboBugs("bug_id");
		$CBugs->setJSSelectedBug("bug_id");
		
		echo $CBugs->generateJS();
		echo $CBugs->generateJSBug();

		$CTodos->setFrmName("batchFrm");
		$CTodos->setCboCompanies("idcompany");
		$CTodos->setCboProjects("project_id_todo");
		$CTodos->setJSSelectedProject("project_id_todo");
		$CTodos->setCboTodos("id_todo");
		$CTodos->setJSSelectedTodo("id_todo");
		
		echo $CTodos->generateJS();
		echo $CTodos->generateJSTodo();
        
?>

</script>
<!-- END OF TIMER RELATED SCRIPTS -->

<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
<form name="batchFrm" action="" method="post" >
	<input type="hidden" name="uniqueid" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="dosql" value="do_timexp_ad" />
	<input type="hidden" name="timexp_id" value="<?php echo $timexp->timexp_id;?>" />
	<input type="hidden" name="timexp_creator" value="<?php echo $timexp->timexp_creator ? $timexp->timexp_creator : $AppUI->user_id;?>" />
<?php if ($external){ ?>
	<input type="hidden" name="timexp_applied_to_type" value="<?php echo $timexp->timexp_applied_to_type;?>" />
<?php } ?>
	<input type="hidden" name="timexp_applied_to_id" value="<?php echo $timexp->timexp_applied_to_id;?>" />	
	<input type="hidden" name="timexp_billable" value="<?php echo $timexp->timexp_billable;?>" />
	<input type="hidden" name="tbillable" value="" />
	<input type="hidden" name="timexp_type" value="<?php echo $timexp->timexp_type;?>" />
	<input type="hidden" name="next" value="<?php echo $redirect_url;?>" />
    <input type="hidden" name="timexp_dates" value="" />
	<input type="hidden" name="timexp_hours" value="" />
	<input type="hidden" name="tapplied_to_type" value="" />
	<input type="hidden" name="hora_inicio" value="" />
	<input type="hidden" name="hora_final" value="" />
	<input type="hidden" name="timexp_named" value="" />
	<input type="hidden" name="descripcion" value="" />
	<input type="hidden" name="company" value="" />
<tr>

	<td colspan="4">
		<?php
		$hour_types = array('Use the hours on each working day','Divide the hours equally on each working day');
		echo arraySelect( $hour_types, 'hours_type', 'size="1" class="text" tabindex="1" onchange=""', '', true,'','350px' );
        ?>
	</td>
	<td> 
	</td>	
</tr>	


<tr>
	<td align="left" style="font-weight: bold;">
		&nbsp;<?php echo $AppUI->_('From');?>
	</td>
	<td nowrap="nowrap" width="120">
		<input type="hidden" name="timexp_from_date" value="<?php echo $date_from->format( FMT_TIMESTAMP_DATE );?>">
        <input type="hidden" name="timexp_from_date_format" value="<?php echo $df; ?>">
        <input type="text" name="from_date" value="<?php echo $date_from->format( $df );?>" class="text"  size="12" tabindex="2">
		<a href="#" onClick="popCalendar('from_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('To');?>
	</td>
	<td nowrap="nowrap" width="120">
		<input type="hidden" name="timexp_to_date" value="<?php echo $timexp_date->format( FMT_TIMESTAMP_DATE );?>">
        <input type="hidden" name="timexp_to_date_format" value="<?php echo $df; ?>">
        <input type="text" name="to_date" value="<?php echo $timexp_date->format( $df );?>" class="text"  size="12" tabindex="3">
		<a href="#" onClick="popCalendar('to_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>	
	<td rowspan="5" align="right" valign="top" style="font-weight: bold;"><!-- <?php echo $AppUI->_('Description');?>: --><?php echo $AppUI->_('Applied to');?>
	</td>
	<td rowspan="5" valign="top">

	<?php
		if (!$external){
			echo arraySelect( $timexp_applied_to_types, 'timexp_applied_to_type', 'size="1" tabindex="7" class="text"  onchange="javascript: changeApplied(this, true);"', $timexp->timexp_applied_to_type, true,'','90px' );
		}else{
			echo $AppUI->_($timexp_applied_to_types[$timexp->timexp_applied_to_type]);
		}
    ?>
	<?php /* 

			********************************************************************************+
			Selecci? del proyecto
			********************************************************************************+
			*/ ?>

			<!-- Para la seleccion de proyectos/tareas  -->
			<div id="projectdiv" style="display: true;"><br>
            

			<table width="70%" border="0" cellspacing="0" cellpadding="0">
			<col width="60px"><col ><col>

			<tr> 
			  <td>
					<?php echo $AppUI->_("Company").":";?>
			  </td>
			  <td align="left">
				&nbsp;<?php 
				// Con el proyect traigo la company_id 

				$sql = "select project_company from projects where project_id='$project_id' ";
                $query = mysql_query($sql);

				$comp = mysql_fetch_array($query);

				$company = $comp[0];

				echo $CProjects->generateHTMLcboCompanies($company, "text");?>
			  </td>
			</tr>
			</table>
			<br>

			<table width="70%" border="0" cellspacing="0" cellpadding="0">
			<col width="60px"><col><col>

			<tr> 
			  <td align="right">
				<?php if (!$external){ ?>
					<?php echo $AppUI->_("Project").":";?>
					<input type="hidden" name="project_item" value="<?php 
					echo $project ;
					?>" />
			  </td>
			  <td align="left">
				
				&nbsp;<?php echo $CProjects->generateHTMLcboProjects($project_id, "text"," onchange=\"javascript:changeTask();\" ");?>
			  </td>
			<?php } ?>
			</tr>
			   <?php echo $CProjects->generateJScallFunctions(); ?>
			</table>

			</div>

			<!-- Para la seleccion de proyectos/bugs  -->

			<div id="projectdiv2" style="display: true;"><br>

			<table width="70%" border="0" cellspacing="0" cellpadding="0">
			<col width="60px"><col ><col>

			<tr> 
			  <td>
					<?php echo $AppUI->_("Company").":";?>
			  </td>
			  <td align="left">
				&nbsp;<?php echo $CBugs->generateHTMLcboCompanies($company, "text");?>
			  </td>
			</tr>
			</table>
			<br>

			 <table width="70%" border="0" cellspacing="0" cellpadding="0">
			<col width="60px"><col><col>

			<tr> 
			  <td align="right">
				<?php if (!$external){ ?>
					<?php echo $AppUI->_("Project").":";?>
					<input type="hidden" name="project_item" value="<?php 
					echo $project ;
					?>" />
			  </td>
			  <td align="left">
				&nbsp;
				<?php echo $CBugs->generateHTMLcboProjects($project, "text");?>
			  </td>
			<?php } ?>
			</tr>
				<?php echo $CBugs->generateJScallFunctions(); ?>
			</table> 

			</div> 

			<!-- Para la seleccion de proyectos/todos  -->

			<div id="projectdiv3" style="display: true;"><br>

			<table width="70%" border="0" cellspacing="0" cellpadding="0">
			<col width="60px"><col ><col>

			<tr> 
			  <td>
					<?php echo $AppUI->_("Company").":";?>
			  </td>
			  <td align="left">
				&nbsp;<?php echo $CTodos->generateHTMLcboCompanies($company, "text");?>
			  </td>
			</tr>
			</table>
			<br>

			 <table width="70%" border="0" cellspacing="0" cellpadding="0">
			<col width="60px"><col><col>

			<tr> 
			  <td align="right">
				<?php if (!$external){ ?>
					<?php echo $AppUI->_("Project").":";?>
					<input type="hidden" name="project_item" value="<?php 
					echo $project ;
					?>" />
			  </td>
			  <td align="left">
				&nbsp;
				<?php echo $CTodos->generateHTMLcboProjects($project, "text");?>
			  </td>
			<?php } ?>
			</tr>
				<?php echo $CTodos->generateJScallFunctions(); ?>
			</table> 

			</div> 

        <?php /* 
		********************************************************************************+
		Selecci? de tarea
		********************************************************************************+
		*/ ?>
		<div id="taskdiv" style="display: true;"><br>
		<table width="70%" border="0" cellspacing="0" cellpadding="0">
		<col width="60px"><col ><col>
		<?php if (!$external){ ?>
				<input type="hidden" name="task_item" value="<?php 
				echo ($timexp->timexp_applied_to_type == 1 && $apptoid ?  $apptoid : "");
				?>" />
		<tr><td align="right"><?php echo $AppUI->_("Task").":";?></td>
			<td align="left">&nbsp;
			<?php echo $CProjects->generateHTMLcboTasks($task_id, "text"); ?>
			</td>
		     <?php echo $CProjects->generateJScallFunctions_task(); ?>
		</tr>

		<?php } ?>
		</table>
		</div>	


		<?php /* 
		********************************************************************************+
		Selecci? de Nothing
		********************************************************************************+
		*/ ?>
		<div id="nothingdiv" style="display: none;"><br>
		<table width="70%" border="0" cellspacing="0" cellpadding="0">
		<col width="60px"><col  width="165px"><col>

		<tr><td ><?php echo $AppUI->_('Name');?>: </td>
			<td>
			<select class='text' name='timexp_name2' tabindex="8">
			
			<?
			   // Traigo los items para el campo nothing de la bd //


			   $lenguage = $AppUI->user_prefs[LOCALE];

			   $descrip_not = "descrip_".$lenguage;
			   
			   $sql = "select $descrip_not from timexp_exp order by $descrip_not asc";

			   $query = mysql_query($sql);
               
			   
			   while ($nothing = mysql_fetch_array($query))
			   {
				echo "<option value='$nothing[0]'>$nothing[0]</option>";
			   }
			   
			?>

			</select>
			
			</td>
		 
		</tr>

		</table>
		</div>	


		<?php /* 
		********************************************************************************+
		Selecci? de To-do's
		********************************************************************************+
		*/ ?>
		<div id="tododiv" style="display: true;"><br>
		<table width="70%" border="0" cellspacing="0" cellpadding="0">
		<col width="60px"><col ><col>
		<?php if (!$external){ ?>
				<input type="hidden" name="task_item" value="<?php 
				echo ($timexp->timexp_applied_to_type == 1 && $apptoid ?  $apptoid : "");
				?>" />
		<tr><td align="right"><?php echo $AppUI->_("To-do").":";?></td>
			<td align="left">&nbsp;
			<?php echo $CTodos->generateHTMLcboTodos($id_todo, "text"); ?>
			</td>
		     <?php echo $CTodos->generateJScallFunctions_todo(); ?>
		</tr>

		<?php } ?>
		</table>
		</div>	
		   

		<?php /*
		********************************************************************************+
		Selecci? de incidencia
		********************************************************************************+
		*/ ?>
		<div id="incidencediv" style="display: none;">
			   <br>
			   <table width="70%" border="0" cellspacing="0" cellpadding="0">
				<col width="60px"><col><col>

				<tr> 
				  <td align="right">
					<?php echo $AppUI->_("Bug").":";?>
				  </td>
				  <td align="left">
					 <input type="hidden" name="bug_item" value="<?php 
						echo ($timexp->timexp_applied_to_type == 2 && $apptoid ?  $apptoid : "");
						?>" />
					
					&nbsp;<?php echo $CBugs->generateHTMLcboBugs($bug, "text"); ?>
				  </td>
				</tr>
				<?php echo $CBugs->generateJScallFunctions_bug(); ?>

				</table>

		</div>
		
	</td>
</tr>

<tr>
	<td align="left" style="font-weight: bold;">&nbsp;<?php echo $AppUI->_($label_value);?></td>
	<td>
		<input type="text" class="text" name="timexp_value" value="<?php echo $timexp->timexp_value;?>" maxlength="8" size="6" tabindex="4"/>
	</td>
	<td align="right" style="font-weight: bold;"><?php echo $AppUI->_('Billable').": ";?>	</td>
	
	<td>
	<div id="billablediv" style="display: true;">
	<?php echo arraySelect( $billables, 'timexp_billable_box', 'size="1" tabindex="5" class="text"', $timexp->timexp_billable, true,'','60px'); ?>
	</div>
	<div id="nobillablediv" style="display: none;">
	No
	</div>
	</td>	

</tr>


<!-- Para que ingrese la hora de inicio, la hora de fin la calculo -->
<tr>
	<td   align="left" style="font-weight: bold;">
		&nbsp;<?php echo $AppUI->_('Start Time');?>
	</td>
	<td>
    <input type='hidden' name="fecha_inicio" value="1">    
	<? if ($timexp->timexp_start_time =="")
	   {
		$timexp->timexp_start_time = "09:00";
	   }
	?>
	<input type='text' class="text" value='<? echo $timexp->timexp_start_time; ?>' name='timexp_start_time'  onblur="CheckTime(this)"  maxlength="12" tabindex="6" size="10"/>
	</td>
</tr>
	
<tr>
	<td align="right" style="font-weight: bold;" valign="top">
		<!-- <?php echo $AppUI->_('Applied to');?> -->
	</td>
 </tr>
<tr>
  <td colspan="4" align="right">
       <BR><BR><BR>
       <input type="button" class="button" name="clear" value="<?php echo $AppUI->_('clear');?>" onclick="delall();">
       <input type="button" class="button" name="generate" value="<?php echo $AppUI->_('generate');?>" onclick="generate_times('1');" style="display: true;" tabindex="11"/>
	   <input type="button" class="button" name="addbuton" value="<?php echo $AppUI->_('add');?>" onclick="generate_times(0);" style="display: none;"/>
  </td>
<tr>
<tr>
	<td colspan="6">
	<div id="timesdiv">
		<table cellspacing="0" cellpadding="2" border="0" width="98%" class="" >
		<tr class="tableHeaderGral">
			<th ><?php echo $AppUI->_("Times");?>:</th>
			<th align="right">
			<?php echo $AppUI->_('Start Time');?>
			</th>
			<th align="left">&nbsp;
			<?php echo $AppUI->_('End Time');?>
			</th>
			<th align="left">
			<?php echo $AppUI->_($label_value);?>
			</th>
			<th align="right" >
			<?php echo $AppUI->_('Applied to');?>
            </th>
			<th align="right" >
			<?php echo $AppUI->_('Company');?>
			</th>
			<th align="right" >
			<?php echo $AppUI->_("Project");?>
			</th>
            <th align="right" >
			<?php echo $AppUI->_('Name');?>
			</th>
			<th align="right" >
			<?php echo $AppUI->_('Description');?>
			</th>
			<th align="right" >
			<?php echo $AppUI->_('Billable');?>
			</th>
			
			<th align="right" width="15%">
			&nbsp;
			</th>
		</tr>
		</table>
		
		<table cellspacing="1" cellpadding="2" border="0" width="98%" class="std" > 
		<tr class="std"><td class="hilite"><?php echo $AppUI->_('No data available');?></td>		
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
changeApplied(document.batchFrm.timexp_applied_to_type, false);
//--></script>
<?php } ?>
