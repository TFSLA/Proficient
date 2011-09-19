<?php /* TASKS $Id: editime.php,v 1.7 2009-06-26 17:43:24 pkerestezachi Exp $ */

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

if ($timexp_id ) {
	$new = false;
	$timexp->load( $timexp_id );
	//si el historial no fue cargado por el usuario y adem? el user no es SYSADMIN
	$canEdit = $timexp->canEdit($msg);
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

if (!$external && !$hideTitleBlock){

	// setup the title block
	$titleBlock = new CTitleBlock( $titleaction." ".$titleobject, 'timexp.gif', $m, "$m.$a" );
    
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
//$CProjects->addItemAtBeginOfCompanies($CProjects->addItem(0, "Not Specified"));

$CProjects->loadProjects(null, true);
//$CProjects->addItemAtBeginOfProjects($CProjects->addItemProject("0","0", "Not Specified"));

$CProjects->loadTasks(null, null, true);
//$CProjects->addItemAtBeginOfTasks($CProjects->addItemTask("0","0","Not Specified"));


$CBugs = new CBugs();

$CBugs->loadCompanies(true);
//$CBugs->addItemAtBeginOfCompanies($CBugs->addItem(0, "Not Specified"));

$CBugs->loadProjects(null, true);
//$CBugs->addItemAtBeginOfProjects($CBugs->addItemProject("0","0", "Not Specified"));

$CBugs->loadBugs();
//$CBugs->addItemAtBeginOfBugs($CBugs->addItemBug("0","0","Not Specified"));


$CTodos = new CTodos();

$CTodos->loadCompanies(true);
//$CTodos->addItemAtBeginOfCompanies($CTodos->addItem(0, "Not Specified"));

$CTodos->loadProjects(null, true);
//$CTodos->addItemAtBeginOfProjects($CTodos->addItemProject("0","0", "Not Specified"));

$CTodos->loadTodos();
//$CTodos->addItemAtBeginOfTodos($CTodos->addItemTodo("0","0","Not Specified"));

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



/* Funcion para la generaci? de los datos */
function generate_times(str)
{
    var frm = document.forms["batchFrm"];
	var rta = true;

       
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
	   validateTimexp()
	}
	
}


function validateTimexp(){
	var frm = document.forms["batchFrm"];
	var rta = true;

	frm.timexp_billable.value = frm.timexp_billable_box.options[frm.timexp_billable_box.selectedIndex].value;
    strMDVparam1 = frm.from_date;
    strMDVparam2 = frm.timexp_from_date_format;
    strMDVparam3 = frm.timexp_from_date;


    strMDVparam1 = frm.to_date;
    strMDVparam2 = frm.timexp_to_date_format;
    strMDVparam3 = frm.timexp_to_date;

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
	
	if (frm.timexp_start_time.value == ""){
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
          
		  if ((applied_to_type[i]==1)||(applied_to_type[i]==2)||(applied_to_type[i]==4))
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
	<input type="hidden" name="dosql" value="do_timexp_edit" />
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
	<input type="hidden" name="hora_inicio" value="" />
	<input type="hidden" name="hora_final" value="" />
	<input type="hidden" name="timexp_named" value="" />
	<input type="hidden" name="descripcion" value="" />
	<input type="hidden" name="company" value="" />
	<input type="hidden" name="hours_type" value="0" />
	
<tr>

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
        <input type="text" name="from_date" value="<?php echo $timexp_date->format( $df );?>" class="text"  size="12" tabindex="2">
		<a href="#" onClick="popCalendar('from_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
		<input type="hidden" name="timexp_to_date" value="<?php echo $date_from->format( FMT_TIMESTAMP_DATE );?>">
        <input type="hidden" name="timexp_to_date_format" value="<?php echo $df; ?>">
	</td>
	
	<td rowspan="5" align="right" valign="top" style="font-weight: bold;"><!-- <?php echo $AppUI->_('Description');?>: --><?php echo $AppUI->_('Applied to');?>
	</td>
	<td rowspan="5" valign="top">

	<?php
		if (!$external){
			echo arraySelect( $timexp_applied_to_types, 'timexp_applied_to_type', 'size="1" tabindex="7" class="text"  onchange="javascript: changeApplied(this, true);"', $timexp->timexp_applied_to_type, true );
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
               	 echo $CProjects->generateHTMLcboCompanies($timexp->timexp_company, "text");?>
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
				&nbsp;<?php 
				
				if ($timexp->timexp_applied_to_type="1"){
				$sql = "select task_project from tasks where task_id='$timexp->timexp_applied_to_id' ";
                $query = mysql_query($sql);

				$comp = mysql_fetch_array($query);

				$project_id = $comp[0];
				}

 				echo $CProjects->generateHTMLcboProjects($project_id, "text"," onchange=\"javascript:changeTask();\" ");?>
			  </td>
			<?php } ?>
			</tr>
			   <?php echo $CProjects->generateJScallFunctions($project_id); ?>
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
				&nbsp;<?php echo $CBugs->generateHTMLcboCompanies($timexp->timexp_company, "text");?>
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
				<?php 

				if ($timexp->timexp_applied_to_type="2"){
				$sql = "select project_id from btpsa_bug_table where id='$timexp->timexp_applied_to_id' ";
                $query = mysql_query($sql);

				$comp = mysql_fetch_array($query);

				$project_id = $comp[0];
				}
				 echo $CBugs->generateHTMLcboProjects($project_id, "text");?>
			  </td>
			<?php } ?>
			</tr>
				<?php echo $CBugs->generateJScallFunctions($project_id); ?>
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
				&nbsp;<?php echo $CTodos->generateHTMLcboCompanies($timexp->timexp_company, "text");?>
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
				<?php 

				if ($timexp->timexp_applied_to_type="4"){
				$sql = "select project_id from project_todo where id_todo='$timexp->timexp_applied_to_id' ";
                $query = mysql_query($sql);

				$comp = mysql_fetch_array($query);

				$project_id = $comp[0];
				}
				
				echo $CTodos->generateHTMLcboProjects($project_id, "text");?>
			  </td>
			<?php } ?>
			</tr>
				<?php echo $CTodos->generateJScallFunctions($project_id); ?>
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
			<?php echo $CProjects->generateHTMLcboTasks($timexp->timexp_applied_to_id, "text"); ?>
			</td>
		     <?php echo $CProjects->generateJScallFunctions_task($timexp->timexp_applied_to_id); ?>
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
			   	$str = ereg_replace("&aacute;","á",$nothing[0]);
			   	$str = ereg_replace("&eacute;","é",$str);
			   	$str = ereg_replace("&iacute;","í",$str);
			   	$str = ereg_replace("&oacute;","ó",$str);
			   	$str = ereg_replace("&uacute;","ú",$str);
			   	
			   	if($timexp->timexp_name == $str)
			   		$sel = "selected";
			   	else 
			   		$sel = "";
			   		
				echo "<option value='$nothing[0]' $sel>$nothing[0]</option>";
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
		*/ 
		?>
		<div id="tododiv" style="display: true;"><br>
		<table width="70%" border="0" cellspacing="0" cellpadding="0">
		<col width="60px"><col ><col>
		<?php if (!$external){ ?>
				<input type="hidden" name="task_item" value="<?php 
				echo ($timexp->timexp_applied_to_type == 1 && $apptoid ?  $apptoid : "");
				?>" />
		<tr><td align="right"><?php echo $AppUI->_("To-do").":";?></td>
			<td align="left">&nbsp;
			<?php echo $CTodos->generateHTMLcboTodos($timexp->timexp_applied_to_id, "text"); ?>
			</td>
		     <?php echo $CTodos->generateJScallFunctions_todo($timexp->timexp_applied_to_id); ?>
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
					
					&nbsp;<?php echo $CBugs->generateHTMLcboBugs($timexp->timexp_applied_to_id, "text"); ?>
				  </td>
				</tr>
				<?php echo $CBugs->generateJScallFunctions_bug($timexp->timexp_applied_to_id); ?>

				</table>

		</div>
		
	</td>
</tr>

<tr>
	<td align="left" style="font-weight: bold;">&nbsp;<?php echo $AppUI->_($label_value);?></td>
	<td>
		<input type="text" class="text" name="timexp_value" value="<?php echo $timexp->timexp_value;?>" maxlength="8" size="6" tabindex="4"/>
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
	<input type='text' class="text" value='<?=($start_time ? $start_time->getHour().":". $start_time->getMinute() : "");?>' name='timexp_start_time'  onblur="CheckTime(this)"  maxlength="12" tabindex="6" size="10"/>
	</td>
</tr>
	
<tr>
	<td align="left" style="font-weight: bold;"><?php echo $AppUI->_('Billable').": ";?>	</td>
	
	<td>
	<div id="billablediv" style="display: true;">
	<?php echo arraySelect( $billables, 'timexp_billable_box', 'size="1" tabindex="5" class="text"', $timexp->timexp_billable, true); ?>
	</div>
	<div id="nobillablediv" style="display: none;">
	No
	</div>
 </tr>
<tr>
 <td rowspan="5" align="left" valign="top" style="font-weight: bold;"><?php echo $AppUI->_('Description');?>:</td>
	<td rowspan="5" valign="top">
		<textarea name="timexp_description" class="textarea" cols="40" rows="8"><?php echo $timexp->timexp_description;?></textarea>
		<div id="errorDiv" name="errorDiv"></div>
	</td>
</tr>

</table>
<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg" >
<tr>
	<td align="right">
		<input type="button" name="save"  class="button" value="<?php echo $AppUI->_('update');?>" onclick="generate_times();"/>
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
