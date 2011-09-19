<?php /* TASKS $Id: addexp.php,v 1.2 2009-06-26 17:43:24 pkerestezachi Exp $ */

global  $task_id, $bug_id, $obj, $percent,$timexp_id, $rnd_type, $timexp_types, $external, $timexp_applied_to_types, $billables,$hideTitleBlock,$dialog;

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

// si es la ventana popup s?o se puede agregar, no editar
if ($is_popup){
	unset($timexp_id);
}

$project="-1";
$project_name = "";
$redirect_url = "";
$timexp = new CTimExp();
if ($timexp_id ) {
	$new = false;
	$timexp->load( $timexp_id );
	//si el historial no fue cargado por el usuario y adem? el user no es SYSADMIN
	$canEdit =$timexp->canEdit($msg);
	if (!$canEdit){
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	
	// Cuando la rendici? no tenga estado pendiente no se puede modificar
	/*if ($timexp->timexp_last_status != "0"){
		$AppUI->setMsg( $timexp_types[$rnd_type] );
		$AppUI->setMsg( "is not pending", UI_MSG_ERROR, true );
		$AppUI->redirect();	
	}*/
		

	if ($timexp->timexp_type != $rnd_type){
		$AppUI->setMsg( $timexp_types[$rnd_type] );
		$AppUI->setMsg( "Invalid ", UI_MSG_ERROR, true );
		$AppUI->redirect();	
	}
			
	if ($timexp->timexp_applied_to_type == "1" && $timexp->timexp_applied_to_id!= 0){
		$sql = "select task_id, task_name from tasks where task_id = ".$timexp->timexp_applied_to_id;
		$rdo = db_loadHashList($sql);
		if (!isset($rdo[$timexp->timexp_applied_to_id])){
			$apptodsc="[".$AppUI->_("Not existing task")."]";
			$apptoid=$timexp->timexp_applied_to_id;
		}else{
			$apptodsc=$rdo[$timexp->timexp_applied_to_id];
			$apptoid=$timexp->timexp_applied_to_id;		
		}
		
		$project = db_loadResult("select task_project from tasks where task_id = $apptoid");

	}else if($timexp->timexp_applied_to_type == "2" && $timexp->timexp_applied_to_id!= 0){
		$sql = "select id, summary from btpsa_bug_table where id = ".$timexp->timexp_applied_to_id;
		$rdo = db_loadHashList($sql);
		if (!isset($rdo[$timexp->timexp_applied_to_id])){
			$apptodsc="[".$AppUI->_("Not existing bug")."]";
			$apptoid=$timexp->timexp_applied_to_id;
		}else{
			$apptodsc=$rdo[$timexp->timexp_applied_to_id];
			$apptoid=$timexp->timexp_applied_to_id;		
		}
		$project = db_loadResult("select project_id from btpsa_bug_table where id = $apptoid");
	}
	
	if ($project!="-1")
		$project_name = db_loadResult("select project_name from projects where project_id = $project");
	else
		$project_name = "";

	$titleaction = $AppUI->_("Edit ");		
	
} else {
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



// Obtengo los proyectos en los cuales el usuario puede agregar tareas
$projects = CProject::projectPermissions();
//echo "<pre>";var_dump($projects);echo "</pre>";

$jsProject = "var prj = new Array();\n";
if (count($projects)){
	$jsProject .= " prj[".implode($projects, "] = '1'; \n prj[")."] = '1'; \n";
}
//echo "<pre>";var_dump($jsProject);echo "</pre>";

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$timexp_date = new CDate( $timexp->timexp_date );

$spvMode = $timexp->canSupervise();

$today = new CDate();
$today = $today->format(FMT_TIMESTAMP_DATE);

if (!$external && !$hideTitleBlock){

	// setup the title block
	$titleBlock = new CTitleBlock( $titleaction." ".$titleobject, 'timexp.gif', $m, "$m.$a" );
	if ($dialog != "1"){
		//$titleBlock->addCrumb("?m=timexp&a=vw_myweek&week_date_".strtolower($sufix)."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "my weekly view");
		$titleBlock->addCrumb("?m=timexp&a=mysheets", "my sheets");
		$titleBlock->addCrumb( "?m=timexp&a=vw_myday&sel_date_".strtolower($sufix)."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "my daily view" );
		if ($spvMode){
			//$titleBlock->addCrumb("?m=timexp&a=vw_sup_week&week_date_".strtolower($sufix)."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "weekly supervision");
		$titleBlock->addCrumb("?m=timexp&a=suptimesheets", "sheets supervision");
		$titleBlock->addCrumb("?m=timexp&a=vw_sup_day&sel_date_".strtolower($sufix)."=".$timexp_date->format(FMT_TIMESTAMP_DATE), "daily supervision");
		}
		if (!$new)
			$titleBlock->addCrumb( "?m=timexp&a=view&timexp_id=$timexp_id", "view ".strtolower($sufix) );
	}
	$titleBlock->show();
}


	
?>

<!-- TIMER RELATED SCRIPTS -->
<script language="JavaScript">
	// please keep these lines on when you copy the source
	// made by: Nicolas - http://www.javascript-page.com
	// adapted by: Juan Carlos Gonzalez jcgonz@users.sourceforge.net
	var today		  = "<?php echo $today;?>";
	var timerID       = 0;
	var tStart        = null;
    var total_minutes = 0;
	<?php echo $jsProject;
	?>

			
	function UpdateTimer() {
	   if(timerID) {
	      clearTimeout(timerID);
	      clockID  = 0;
	   }
	
 /*       // One minute has passed
    if (total_minutes!=0){
       total_minutes = total_minutes+1;
     }
	*/   
	   document.getElementById("timerStatus").innerHTML = "( "+total_minutes+" <?php echo $AppUI->_('minutes elapsed'); ?> )";

	   // Lets round hours to two decimals
	   
	   var total_hours   = Math.round( (total_minutes / 60) * 100) / 100;
	   document.editFrm.timexp_value.value = total_hours;

	   // One minute has passed
	   total_minutes = total_minutes+1;
      var curtime = new Date();
      document.editFrm.timexp_end_time.value = curtime.getHours()+":"+curtime.getMinutes();
      	   
	   timerID = setTimeout("UpdateTimer()", 60000);
	   
	}
	
	function timerStart() {
		var frm = document.forms["editFrm"];
		
		if(!timerID){ // this means that it needs to be started
			frm.timerStartStopButton.value = "<?php echo $AppUI->_('Stop');?>";
            UpdateTimer();
      var curtime = new Date();
      frm.timexp_start_time.value = curtime.getHours()+":"+curtime.getMinutes();
      frm.timexp_end_time.value = frm.timexp_start_time.value;
      frm.timexp_start_time.disabled=true;
      frm.timexp_end_time.disabled=true;
           
		} else { // timer must be stoped
			frm.timerStartStopButton.value = "<?php echo $AppUI->_('Start');?>";
			document.getElementById("timerStatus").innerHTML = "";
			
			timerStop();
			
      frm.timexp_start_time.disabled=false;
      frm.timexp_end_time.disabled=false;			
		}
	}
	
	function timerStop() {
	   if(timerID) {
	      clearTimeout(timerID);
	      timerID  = 0;
        total_minutes = "0.00";//total_minutes-1;
	   }
	}
	
	function timerReset() {
		var frm = document.forms["editFrm"];
		frm.timexp_value.value = "0.00";
   	total_minutes = 0;
   	if(timerID){
	    var curtime = new Date();
	    frm.timexp_start_time.value = curtime.getHours()+":"+curtime.getMinutes();
	    frm.timexp_end_time.value = frm.timexp_start_time.value;
	    frm.timexp_start_time.disabled=true;
	    frm.timexp_end_time.disabled=true;   	
   	}
	}
	
	function changeApplied(sel, reset){
		var opts = new Array();
		opts[1] = "task";
		opts[2] = "incidence";
		opts[3] = "none";
		var opt = opts[parseInt(sel.options[sel.selectedIndex].value)];
		if (reset)
			sel.form.timexp_applied_to_id.value = "0";
		document.getElementById("taskdiv").style.display = 'none';
		document.getElementById("incidencediv").style.display = 'none';
		document.getElementById("billablediv").style.display = 'none';
		document.getElementById("nobillablediv").style.display = '';
		document.getElementById("projectdiv").style.display = 'none';
		if (opt!='none'){
			document.getElementById(opt + "div").style.display = '';
			document.getElementById("billablediv").style.display = '';
			document.getElementById("nobillablediv").style.display = 'none';
			document.getElementById("projectdiv").style.display = '';
                        x = document.getElementById("namediv");
                        x.innerHTML="";
                        x.innerHTML="<input type='text' class='text' name='timexp_name' value='<?php echo $timexp->timexp_name;?>' maxlength='255' size='30'/>";
		}
                else{
                        x = document.getElementById("namediv");
                        x.innerHTML="";
                        x.innerHTML="<select class='text' name='timexp_name'>
																			<option value='Reuni&oacute;n terna'>Reuni&oacute;n Interna</option>
																			<option value='Reuni&oacute;n con Clientes'>Reuni&oacute;n con Clientes</option>
																			<option value='Viaje'>Viaje</option>
																			<option value='Generaci&oacute;n de propuesta'>Generaci&oacute;n de propuesta</option>
																			<option value='Soporte equipos'>Soporte equipos </option>
																			<option value='Entrenamiento'>Entrenamiento</option>
																			<option value='Generaci&oacute;n de documentaci&oacute;n interna'>Generaci&oacute;n de documentaci&oacute;n interna</option>
																			<option value='Otros'>Otros</option>
																		</select>";
                }
	}	
	
	
function ValidTime(h, m, s) {
    with (new Date(0, 0, 0, h, m, s)) {
        return ((getHours() == h) && (getMinutes() == m));
    }
}



function CheckTime(obj) {
    var T ;
    var Q = trim(obj.value).replace(".",":");
    if (Q.indexOf(":") == -1 && Q.length ==4){
    	Q = Q.substr(0,2) + ":" + Q.substr(2,2);
    }
    if (Q.length == 0)
    	return true;
    if ((T = /^(\d\d):(\d\d)$/.exec(Q)) == null) {
        alert("<?php echo $AppUI->_("timexpTime");?>");
        obj.focus();
        return false;
    }
    if (!ValidTime(T[1], T[2], 0)) {
        alert("<?php echo $AppUI->_("timexpTime");?>");
        obj.focus();
        return false;
    }
    obj.value = Q;
    return true;
}

function getDateObject(obj , is_endtime){
	// Checks if time is in HH:MM:SS AM/PM format.
	// The seconds and AM/PM are optional.

	<?php	
	$tf = $AppUI->getPref('TIMEFORMAT');
	$ampm_time =  strpos("%p", $tf) ? 'true' : 'false';
	//echo "\n var ampm_time = ".$ampm_time.";"
	echo "\n var ampm_time = false;"
	?>	

	var timeStr = trim(obj.value).replace(".",":");

	if (timeStr.length ==0){
		return null;
	}else if (timeStr.length == 4){
		timeStr = timeStr.substr(0,2)+":"+timeStr.substr(2,2);
	}else if (timeStr.length == 6){
		timeStr = timeStr.substr(0,2)+":"+timeStr.substr(2,2)+":"+timeStr.substr(4,2);
	}
	
	var timePat = /^(\d{1,2}):(\d{1,2})(:(\d{1,2}))?(\s?(AM|am|PM|pm))?$/;
	var timePat = /^(\d{1,2}):(\d{1,2})$/;

	var matchArray = timeStr.match(timePat);
	if (matchArray == null) {
		showError("<?php echo $AppUI->_("timexpTime");?>");
		return false;
	}
	hour = matchArray[1];
	minute = matchArray[2];
	second = matchArray[4];
	ampm = matchArray[6];

	if (second=="") { second = null; }
	if (ampm=="") { ampm = null }

	if (hour < 0  || hour > 23) {
		showError("<?php echo $AppUI->_("timexpTime");?>");
		return false;
	}
	if (hour <= 12 && ampm == null && ampm_time) {
		showError("You must specify AM or PM.");
		return false;
	}
	if  (hour > 12 && ampm != null) {
		showError("<?php echo $AppUI->_("timexpTime");?>");
		return false;
	}
	if (minute < 0 || minute > 59) {
		showError("<?php echo $AppUI->_("timexpTime");?>");
		return false;
	}
	if (second != null && (second < 0 || second > 59)) {
		showError("<?php echo $AppUI->_("timexpTime");?>");
		return false;
	}
	
	var hour2 = hour;
	if (ampm == "PM" || ampm == "pm" ){
		hour2 += 12;
	}
	var second2=second;
	if (second ==null){
		second2=0;
	}
	if (is_endtime && hour2==0 && minute==0)
		var objtime = new Date(0, 0, 1, hour2, minute, second2);
	else		
		var objtime = new Date(0, 0, 0, hour2, minute, second2);
	
	var str = "";
	str="0000"+hour.toString();
	obj.value = str.substring(str.length - 2, str.length)+":";
	str="0000"+minute.toString();
	obj.value += str.substring(str.length - 2, str.length);
	if (second != null){
		str="0000"+second.toString();
		obj.value += ":"+str.substring(str.length -2, str.length);
	}
	if (ampm != null){
		obj.value += " "+ampm;
	}

	return objtime;    
	
}
function listProperties(obj, objName) {
    var result = "";
    for (var i in obj) {
        result += objName + "." + i + "=" + obj[i] + "\n";
    }
    alert(result);
}

function CompareTimes(initime, fintime, hours) {
    var T  ;
   
    if (initime===false || fintime ===false){
    	return false;
    }
		
    if (initime!==null && fintime!==null){
	    if (!(initime<fintime)) {
	        alert("<?php echo $AppUI->_("timexpInvalidTimes");?>");
	        return false;
	    }
	    var dif = Math.round(100 * (fintime-initime)/3600000) / 100;
	    //var dif = (fintime-initime)/3600000;
	    if (dif != hours  && hours!="") {
	        alert("<?php echo $AppUI->_("timexpInvalidTimes&Duration");?>");
	        return false;
	    }
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
		// si no est?cargado el fin
		if (e.value=="" && s.value!="" && h.value!=""){
			var fintime = new Date();
			fintime.setTime(initime.getTime() + h.value * 3600000);
				
			str="0000"+fintime.getHours().toString();
			e.value = str.substring(str.length - 2, str.length)+":";
			str="0000"+fintime.getMinutes().toString();
			e.value += str.substring(str.length - 2, str.length);
			
		} else 
		// si no est?cargado el inicio
		if (e.value!="" && s.value=="" && h.value!=""){
			var initime = new Date();
			initime.setTime(fintime.getTime() - h.value * 3600000);
			str="0000"+initime.getHours().toString();
			s.value = str.substring(str.length - 2, str.length)+":";
			str="0000"+initime.getMinutes().toString();
			s.value += str.substring(str.length - 2, str.length);			
		} else 
		// si no est? cargadas las horas
		if (e.value!="" && s.value!="" && h.value==""){
			h.value = Math.round(100 * (fintime-initime)/3600000) / 100;
		} 
		 
		// si es modificado el inicio o el final recalculo la duraci?
		if ((s == mod || e == mod) && (e.value!="" && s.value!="")){
			h.value = Math.round(100 * (fintime-initime)/3600000) / 100;
		}

	}
		

}


function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.timexp_' + field + '.value' );
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
		fld_date = eval( 'document.editFrm.timexp_' + calendarField );
		fld_fdate = eval( 'document.editFrm.' + calendarField );
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
	var frm = document.forms["editFrm"];
	var rta = true;
	frm.timexp_billable.value = frm.timexp_billable_box.options[frm.timexp_billable_box.selectedIndex].value;
    strMDVparam1 = frm.date;
    strMDVparam2 = frm.timexp_date_format;
    strMDVparam3 = frm.timexp_date;

    if(<?php echo $timexp_date->buildFunctionMDVJS(); ?>){
        alert("<?php echo $AppUI->_('timexpDateError');?>");
        rta = false;
    }

    if (frm.timexp_applied_to_type.value != "1"){
		frm.timexp_contribute_task_completion.value = "0";
	}
	if (frm.timexp_applied_to_type.value == "3"){
		frm.timexp_billable.value = "0";
	}
	var valor = parseFloat(frm.timexp_value.value);
	if (isNaN(valor)){
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
	

	if(frm.timexp_date.value>today){
		alert("<?php echo $AppUI->_("timexpInvalidDate");?>");
		rta = false;
	}

	if(timerID){
		alert("<?php echo $AppUI->_("timexpTimerActive");?>");
		rta = false;
	}
		
<?php
//validaciones para horas
if ($rnd_type==1){	
/*
	if (!CheckTime(frm.timexp_start_time)){
		rta = false;
	}
	if (!CheckTime(frm.timexp_end_time)){
		rta = false;
	}	
	if (!CompareTimes(frm.timexp_start_time, frm.timexp_end_time, valor)){
		rta = false;
	}		*/ ?>

  var initime = getDateObject(frm.timexp_start_time);
  var fintime = getDateObject(frm.timexp_end_time, true);	


	if (!CompareTimes(initime,fintime,valor) || initime == false || fintime==false){
		rta = false;
	}			
	
<?php
//validaciones para expenses
}elseif ($rnd_type==2){?>



<? } ?>



	return rta;
}



function popProject() {
	window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setProject&table=projects', 'selector', 'left=50,top=50,height=300,width=400,resizable')
}


function setProject( key, val ) {
	var f = document.editFrm;
	f.task_item.value="-1";
	f.task_item_name.value="";
	f.bug_item.value="-1";
	f.bug_item_name.value="";
	if (key > 0) {
		f.project_item.value = key;
		f.project_item_name.value = val;
		f.task_pop.disabled=false;
		f.bug_pop.disabled=false;
	} else {
		f.project_item.value = '-1';
		f.project_item_name.value = '';
		f.task_pop.disabled=true;
		f.bug_pop.disabled=true;
		
	}

}

function popBug() {
	var f = document.editFrm;
	if (f.project_item.value != '-1'){
		window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setBug&table=bugs&bug_project=' + f.project_item.value, 'selector', 'left=50,top=50,height=300,width=400,resizable')
	}else{
		alert("<?php echo $AppUI->_('timexpProject');?>");	
	}
}

function setBug( key, val ) {
	var f = document.editFrm;
	if (key > 0) {
		f.bug_item.value = key;
		f.bug_item_name.value = val;
		f.timexp_applied_to_id.value = key;
	}
}

function popNewBug() {
	var f = document.editFrm;
	if (f.project_item.value != '-1'){
		window.open('./index.php?m=webtracking&a=bug_report_page&suppressLogo=1&dialog=1&callback=setNewBug&project_id=' + f.project_item.value, 'selector', 'left=50,top=50,height=300,width=400,resizable,scrollbars')
	}else{
		alert("<?php echo $AppUI->_('timexpProject');?>");	
	}

}

function setNewBug( key, val ) {
	var f = document.editFrm;
	if (key > 0) {
		f.bug_item.value = key;
		f.bug_item_name.value = val;
		f.timexp_applied_to_id.value = key;
	}
}

function popTask() {
	var f = document.editFrm;
	if (f.project_item.value != '-1'){
		window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setTask&table=tasks&task_project=' + f.project_item.value + '&task_perm=<?php echo $sufix;?>', 'selector', 'left=50,top=50,height=300,width=400,resizable')
	}else{
		alert("<?php echo $AppUI->_('timexpProject');?>");	
	}
}

function setTask( key, val ) {
	var f = document.editFrm;
	if (key > 0) {
		f.task_item.value = key;
		f.task_item_name.value = val;
		f.timexp_applied_to_id.value = key;
	}
}


function popNewTask() {
	var f = document.editFrm;
	if (f.project_item.value != '-1'){
		window.open('./index.php?m=tasks&a=addedit&suppressLogo=1&dialog=1&callback=setNewTask&task_project=' + f.project_item.value, 'selector', 'left=50,top=50,height=300,width=400,resizable,scrollbars')
	}else{
		alert("<?php echo $AppUI->_('timexpProject');?>");	
	}

}

function setNewTask( key, val ) {
	var f = document.editFrm;
	if (val != '') {
		f.task_item.value = key;
		f.task_item_name.value = val;
		f.timexp_applied_to_id.value = key;
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
</script>
<!-- END OF TIMER RELATED SCRIPTS -->


<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
<form name="editFrm" action="" method="post" onsubmit="return validateTimexp();">
	<input type="hidden" name="uniqueid" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="dosql" value="do_timexp_aed" />
	<input type="hidden" name="timexp_id" value="<?php echo $timexp->timexp_id;?>" />
	<input type="hidden" name="timexp_creator" value="<?php echo $timexp->timexp_creator ? $timexp->timexp_creator : $AppUI->user_id;?>" />
<?php if ($external){ ?>
	<input type="hidden" name="timexp_applied_to_type" value="<?php echo $timexp->timexp_applied_to_type;?>" />
<?php } ?>
	<input type="hidden" name="timexp_applied_to_id" value="<?php echo $timexp->timexp_applied_to_id;?>" />	
	<input type="hidden" name="timexp_billable" value="<?php echo $timexp->timexp_billable;?>" />
	<input type="hidden" name="timexp_type" value="<?php echo $timexp->timexp_type;?>" />
	<input type="hidden" name="next" value="<?php echo $redirect_url;?>" />
<tr>
	<td align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('Date');?>
	</td>
	<td nowrap="nowrap" width="300">
		<input type="hidden" name="timexp_date" value="<?php echo $timexp_date->format( FMT_TIMESTAMP_DATE );?>">
        <input type="hidden" name="timexp_date_format" value="<?php echo $df; ?>">
        <input type="text" name="date" value="<?php echo $timexp_date->format( $df );?>" class="text"  size="12">
		<a href="#" onClick="popCalendar('date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align="right" style="font-weight: bold;"><?php echo $AppUI->_('Name');?>:</td>
	<td>
	<input type='text' class='text' name='timexp_name' value='<?php echo $timexp->timexp_name;?>' maxlength='255' size='30'/>
	</td>
</tr>
<tr>
	<td align="right" style="font-weight: bold;"><?php echo $AppUI->_($label_value);?></td>
	<td>
	<input type="text" class="text" name="timexp_value" value="<?php echo $timexp->timexp_value;?>" maxlength="8" size="6"  />
<? if ($rnd_type=="1"){?>
<input type='button' class="button" value='<?php echo $AppUI->_('Start');?>' onclick='javascript:timerStart()' name='timerStartStopButton' />
		<input type='button' class="button" value='<?php echo $AppUI->_('Reset'); ?>' onclick="javascript:timerReset()" name='timerResetButton' />
		<span id='timerStatus'></span>
<? } ?>
	</td>
	<td rowspan="5" align="right" valign="top" style="font-weight: bold;"><?php echo $AppUI->_('Description');?>:</td>
	<td rowspan="5" valign="top">
		<textarea name="timexp_description" class="textarea" cols="40" rows="8"><?php echo $timexp->timexp_description;?></textarea>
		<div id="errorDiv" name="errorDiv"></div>
	</td>
</tr>
<? if ($rnd_type=="1"){?>
<tr>
	<td   align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('Start Time');?>
	</td>
	<td>
<input type='text' class="text" value='<?=($start_time ? $start_time->getHour().":". $start_time->getMinute() : "");?>' name='timexp_start_time'  maxlength="12" size="10"  onblur="synchourstimes(this)"/>
<?php
	//echo arraySelect($hours, "start_time",'size="1" class="text"', $start_time ? $start_time->getHour().":". $start_time->getMinute() : "NULL" ) ;
				?>
	</td>
</tr>

<tr>
	<td align="right" style="font-weight: bold;">
		<?php echo $AppUI->_('End Time');?>
	</td>
	<td><input type='text' class="text" value='<?=($end_time ? $end_time->getHour().":". $end_time->getMinute() : "");?>'  name='timexp_end_time'  maxlength="12" size="10"  onblur="synchourstimes(this)" />		
<?php
	//echo arraySelect($hours, "end_time",'size="1" class="text"', $end_time ? $end_time->getHour().":". $end_time->getMinute() : "NULL" ) ;
				?>
	</td>
</tr>
<? } 
	
?>
<tr>
	<td align="right" style="font-weight: bold;" valign="top">
		<?php echo $AppUI->_('Applied to');?>
	</td>
	<td height="100px" valign="top">
<?php
		if (!$external){
			echo arraySelect( $timexp_applied_to_types, 'timexp_applied_to_type', 'size="1" class="text"  onchange="javascript: changeApplied(this, true);"', $timexp->timexp_applied_to_type, true );
		}else{
			echo $AppUI->_($timexp_applied_to_types[$timexp->timexp_applied_to_type]);
		}
?>
<?php /* 
********************************************************************************+
Selecci? del proyecto
********************************************************************************+
*/ ?>
<div id="projectdiv" style="display: true;">
<?php if (!$external){ ?>
<table width="100%">
<col width="70px"><col  width="150px"><col>
<input type="hidden" name="project_item" value="<?php echo $project ;?>" />
<tr><td ><?php echo $AppUI->_("Project").":";?></td>
	<td ><input type="text" class="text" name="project_item_name" value="<?php 
		echo $project_name;
		?>" size="25" disabled /></td>
    <td><input type="button" class="button" value="..." onclick="popProject()" /></td>
</tr>
</table>
<?php } ?>
</div>

<?php /* 
********************************************************************************+
Selecci? de tarea
********************************************************************************+
*/ ?>
<div id="taskdiv" style="display: true;">
<table width="100%">
<col width="70px"><col  width="150px"><col>
<?php if (!$external){ ?>
        <input type="hidden" name="task_item" value="<?php 
		echo ($timexp->timexp_applied_to_type == 1 && $apptoid ?  $apptoid : "");
		?>" />
<tr><td ><?php echo $AppUI->_("Task").":";?></td>
    <td ><input type="text" class="text" name="task_item_name" value="<?php 
		echo ($timexp->timexp_applied_to_type == 1 && $apptoid ? $apptodsc :"");
		?>" size="25" disabled /></td>
   <td><input type="button" class="button" name="task_pop" value="..." onclick="popTask()" <?php
		echo ($project=="-1")? "disabled" : "";?> />
	</td>
</tr>


<?php } ?>
<? if ($rnd_type=="1"){?>
<tr><td colspan="2">
	<?=$AppUI->_('Contribute to task completion').": ";?></td><td>
	<?=arraySelect( $billables, 'timexp_contribute_task_completion', 'size="1" class="text"', $timexp->timexp_contribute_task_completion,true); ?>
	</td></tr>
<? } ?>
</table>
</div>	



<?php /*
********************************************************************************+
Selecci? de incidencia
********************************************************************************+
*/ ?>
<div id="incidencediv" style="display: none;">
<table width="100%">
<col width="70px"><col  width="150px"><col>
<input type="hidden" name="bug_item" value="<?php 
echo ($timexp->timexp_applied_to_type == 2 && $apptoid ?  $apptoid : "");
?>" />
<tr><td ><?php echo $AppUI->_("Bug").":";?></td>
	<td><input type="text" class="text" name="bug_item_name" value="<?php
		echo ($timexp->timexp_applied_to_type == 2 && $apptoid ? $apptodsc :"");
		?>" size="25" disabled /></td>
    <td><input type="button" class="button" name="bug_pop" value="..." onclick="popBug()" <?php
		echo ($project=="-1")? "disabled" : "";?> />
	</td>
</tr>
</table>		
</div>
	</td>
</tr>
<tr>
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
</table>
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tableForm_bg" >
<tr>
	<td align="right">
		<input type="submit" class="button" value="<?php echo $AppUI->_('update');?>" />
	</td>
</tr>
</form>
</table>
<?php 
if ($rnd_type=="1"){?>
<iframe id="cookie_saver" name="cookie_saver" height="1" width="1" scrolling="No" style="border: 0px; " src="./index.php?m=public&a=cookie_saver&suppressLogo=1&dialog=1&autorefresh=1"></iframe>
<?php } 
if (!$external){ ?>
<script>
changeApplied(document.editFrm.timexp_applied_to_type, false);
</script>
<?php } ?>
