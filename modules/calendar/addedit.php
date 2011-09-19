<?php /* CALENDAR $Id: addedit.php,v 1.2 2009-06-23 14:45:57 pkerestezachi Exp $ */

require_once( "./classes/projects.class.php" );
require_once( "./includes/main_functions.php" );

$event_id = intval(dPgetParam( $_GET, "event_id", "" ));
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );
$mod_id = 4;

// load the record data
$obj = new CEvent();

if (!$obj->load( $event_id ) && $event_id ) 
{
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

 // check permissions
if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );	
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator( $delegator_id, $mod_id ) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg("Delegator");
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$usr->load( $delegator_id );
	$permiso = $usr->getDelegatePermission( $AppUI->user_id, $mod_id );
	
	if ( $event_id )
	{
		$canEdit = $canEdit || ( $permiso == "AUTHOR" && $obj->event_creator == $AppUI->user_id );
		$canEdit = $canEdit || ( $permiso == "EDITOR" );
		$canEdit = $canEdit || $AppUI->user_type == 1;
	}
	else
	{
		$canEdit = 1;
	}	
}

if (!$canEdit) 
{
	$AppUI->redirect( "m=public&a=access_denied" );
}	

if ( $obj->event_owner && $obj->event_owner != $AppUI->user_id && $AppUI->user_type != 1 )
	$AppUI->redirect( "m=public&a=access_denied" );

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );
$fechaElegida = new CDate( $date );

// load the event types
$types = dPgetSysVal( 'EventType' );

$type[0] = "General";
$type[1] = "Appointment";
$type[2] = "Meeting";
$type[3] = "All Day Event";
$type[4] = "Anniversary";
$type[5] = "Reminder";
$type[6] = "Call";

/*$types_array = 0|General
1|Appointment
2|Meeting
3|All Day Event
4|Anniversary
5|Reminder
6|Call*/

$start_date = $obj->event_start_date ? new CDate( $obj->event_start_date ) : $fechaElegida;

// setup the title block
$titleBlock = new CTitleBlock( ($event_id ? "Edit Event" : "Add Event") , 'calendar.gif', $m, "colaboration.index" );

if ( $start_date && $dialog != 1)
{
	$first_week_day = $start_date;
	while ( $first_week_day->getDayOfWeek() != 1 )
	{
		$first_week_day->addDays(-1);
	}
	
	$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&dialog=$dialog&a=month_view&date=".$start_date->format( FMT_TIMESTAMP_DATE ), "month view" );
	$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&date=".$first_week_day->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog", "week view" );

	$titleBlock->addCrumb( "?m=calendar&a=day_view&dialog=$dialog&delegator_id=$delegator_id&date=".$start_date->format( FMT_TIMESTAMP_DATE ), "day view" );
	
	if($event_id)
		$titleBlock->addCrumb( "?m=calendar&a=view&delegator_id=$delegator_id&dialog=$dialog&event_id=$event_id", "view this event" );
}

$titleBlock->show();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

require_once( $AppUI->getModuleClass( 'projects' ) );

$prj = new CProject();
$projects = $prj->getAllowedRecords( $AppUI->user_id, 'project_id, project_name', 'project_name' ); 

$cpy = new CCompany();
$companies = $cpy->getAllowedRecords( $AppUI->user_id, 'company_id, company_name', 'company_name' );



$tmpTimeNow = date("His");
/*funcion para encontrar la hora mas proxima*/
function getNextTime($arValues, $strTime){
    foreach($arValues as $tmpKey => $tmpValue){
        if(intval($strTime) < intval($tmpKey)){
            return $tmpKey;
        }
    }
    return "000000";
}

if ($event_id) {
	$start_date = intval( $obj->event_start_date ) ? new CDate( $obj->event_start_date ) : null;
	$end_date = intval( $obj->event_end_date ) ? new CDate( $obj->event_end_date ) : $start_date;
} else {
    $start_date = new CDate( $date );
	$start_date->setTime( 8,0,0 );
	$end_date = new CDate( $date );
	$end_date->setTime( 9,0,0 );
    $tmpEndTime = $end_date;//lo uso como temporal para agregar una hora a la actual y ponerla en el cbo
    //$tmpEndTime->addSeconds(3600);
}

$recurs =  array (
	0=>"Never",
	"d"=>"Daily",
	"w"=>"Weekly",
	"m"=>"Monthly",
	"y"=>"Yearly"
);

$week_days = array (
	"Sunday",
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday"
);

$months = array(
	1=>"January",
	2=>"February",
	3=>"March",
	4=>"April",
	5=>"May",
	6=>"June",
	7=>"July",
	8=>"August",
	9=>"September",
	10=>"October",
	11=>"November",
	12=>"December"	
);

$invitation_types = array(
	"PERSONAL"=>"Only me",
	"PROJECT"=>"All users on the project",
	"COMPANY"=>"All users on the company",
	"PRIVATE"=>"Only the people I invite"
);

$ordered = array (
	1=>"First",
	2=>"Second",
	3=>"Third",
	4=>"Fourth",
	5=>"Fifth"
	);

$remind = array (
	"900" => '15 mins',
	"1800" => '30 mins',
	"3600" => '1 hour',
	"7200" => '2 hours',
	"14400" => '4 hours',
	"28800" => '8 hours',
	"56600" => '16 hours',
	"86400" => '1 day',
	"172800" => '2 days'
);

$durations = array();
for ( $i = 30; $i < 1440; $i+= 30 )
{
	$hs = intval($i / 60);
	$ms = $i % 60;
	$durations[$i] = ($hs != 0 ? $hs." ".( $hs > 1 ? $AppUI->_("hours") : $AppUI->_("hour") ) : "");
	$durations[$i] .= ($ms != 0 ? " ".$ms." ".$AppUI->_("mins") : "");
}


// build array of times in 30 minute increments
$times = array();
$t = new CDate();
$t->setTime( 0,0,0 );
if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');
for ($m=0; $m < 60; $m++) {
	$times[$t->format( "%H%M%S" )] = $t->format( LOCALE_TIME_FORMAT );
	$t->addSeconds( 1800 );
}

$CProjects = new CProjects();

$CProjects->loadCompanies();
$CProjects->addItemAtBeginOfCompanies($CProjects->addItem(0, $AppUI->_('Not Specified') ));

$CProjects->loadProjects();
$CProjects->addItemAtBeginOfProjects($CProjects->addItemProject("0","0", $AppUI->_('Not Specified') ));

$CProjects->loadTasks();
$CProjects->addItemAtBeginOfTasks($CProjects->addItemTask("0","0",$AppUI->_('Not Specified') ));


if(isset($_GET['project']))   //Para agregar eventos desde la solapa de Proyectos
{
	$project = $_GET['project'];
	
	$objProject = new CProject();
	$objProject->load($_GET['project']);
	$company = $objProject->project_company;
}
elseif($obj->event_task > 0)
{
	require_once( "./modules/tasks/tasks.class.php" );

	$task = $obj->event_task;
	
	$objTask = new CTask();
	$objTask->load($task);
	
	$project = $objTask->task_project;
	$task = $objTask->task_id;
	
	$objProject = new CProject();
	$objProject->load($project);
	
	$company = $objProject->project_company;
}

if($obj->event_salepipeline > 0)
	$pipeline_id = $obj->event_salepipeline;
else
	if(isset($_GET['lead_id']))
		$pipeline_id = $_GET['lead_id'];
	
?>

<script language="javascript">

function setProject(obj, project_id)
{
	var objSel = eval(obj);
	
	for(i=0;i<objSel.options.length;i++)
	{
		if(objSel.options[i].value == project_id)
		{
			objSel.options[i].selected = true;
			changeTask();
			return;
		}
	}
}

function setTask(obj, task_id)
{
	var objSel = eval(obj);
	
	for(i=0;i<objSel.options.length;i++)
	{
		if(objSel.options[i].value == task_id)
		{
			objSel.options[i].selected = true;
			return;
		}
	}
}

function validaDaily( f )
{	
	if ( f.radio_daily[0].checked )
	{
		//Tiene que haber algo en el textbox
		if ( f.event_recur_every_x_days.value != "" )
		{
			iVal = parseInt(f.event_recur_every_x_days.value);
			if ( !isNaN( iVal ) )
			{
				if ( iVal > 0 )
				{
					return true;
				}
				else
				{
					alert ( '<?=$AppUI->_("Please enter a valid number")?>' );
				}
			}
			else
			{
				alert ( '<?=$AppUI->_("Please enter a valid number")?>' );				
			}
		}
		else
		{
			alert ( '<?=$AppUI->_("Please enter a number")?>' );			
		}
		f.event_recur_every_x_days.focus();
	}
	else
	{
		if ( !f.radio_daily[1].checked )
		{
			alert ( '<?=$AppUI->_("Please select a recursion pattern")?>' );
			return false;
		}
		//Repite todos los dias de semana
		return true;
	}
	return false;
}

function validaWeekly( f )
{
	if ( f.event_recur_every_x_weeks.value != "" )
	{		
		iVal = parseInt( f.event_recur_every_x_weeks.value );
		if ( !isNaN( iVal ) )
		{			
			if ( iVal > 0 )
			{				
				//verificar los checkboxes
				checkeado = false;
				for ( i = 0; i < 7 && !checkeado; i++ )
				{
					id = "event_recur_every_n_days_" + i;
					elem = eval( "document.editFrm."+ id);
					checkeado = elem.checked;
				}
				if ( checkeado )
				{
					return true;
				}
				else
				{
					alert('<?=$AppUI->_("Please check at least one day")?>');
				}	
			}
			else
			{
				alert('<?=$AppUI->_("Please enter a valid number")?>');
			}
		}
		else
		{
			alert('<?=$AppUI->_("Please enter a valid number")?>');
		}
	}
	else
	{
		alert('<?=$AppUI->_("Please enter a number")?>'); 
	}
	f.event_recur_every_x_weeks.focus();
	return false;
}

function validaMonthly(f)
{
	if ( f.event_recur_every_x_months.value != "" )
	{
		iVal = parseInt(f.event_recur_every_x_months.value);
		if ( !isNaN(iVal) )
		{
			if ( iVal > 0 )
			{
				if ( f.radio_monthly[0].checked )
				{
					//Ejecuta el XX de cada X meses
					if (f.m_event_recur_every_dd_day.value != "")
					{
						iVal = parseInt(f.m_event_recur_every_dd_day.value);
						if (!isNaN(iVal))
						{
							if (iVal > 0 && iVal <=31 )
							{
								if (iVal >= 29 && iVal <=31 ){
									return (confirm('<?php echo $AppUI->_("Some months have less than 29 days. In those months the event will take place the last day of the month.")?>'));
								}
								return true;
								
							}
							else
							{
								alert('<?=$AppUI->_("Please enter a valid number")?>');			
							}
						}
						else
						{
							alert('<?=$AppUI->_("Please enter a number")?>');
						}
					}
					else
					{
						alert('<?=$AppUI->_("Please enter a number")?>');
					}
					f.m_event_recur_every_dd_day.focus();
					return false;		
				}
				else
				{
					if ( !f.radio_monthly[1].checked )
					{
						alert( '<?=$AppUI->_("Please select a recursion pattern")?>' );
						return false;
					}
					return true;
				}				
			}
			else
			{
				alert('<?=$AppUI->_("Please enter a valid number")?>');								
			}
		}
		else
		{
			alert('<?=$AppUI->_("Please enter a number")?>');
		}						
	}
	else
	{
		alert('<?=$AppUI->_("Please enter a number")?>');
	}
	f.event_recur_every_x_months.focus();
	return false;
}

function validaYearly( f )
{
	var daysOfMonth = new Array( 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	
	if ( f.radio_yearly[1].checked )
	{
		return true;
	}
	else
	{
		if ( f.radio_yearly[0].checked )
		{
			if ( f.y_event_recur_every_dd_day.value!="" )
			{
				iVal = parseInt(f.y_event_recur_every_dd_day.value);
				if ( !isNaN(iVal) )
				{			
					if ( iVal >0 && iVal <= daysOfMonth[f.event_recur_every_mm_month.selectedIndex] )
					{
						return true;
					}
					else
					{
						alert('<?=$AppUI->_("Please enter a valid number")?>');
					}			
				}
				else
				{
					alert('<?=$AppUI->_("Please enter a number")?>');
				}
			}
			else
			{
				alert('<?=$AppUI->_("Please enter a number")?>');
			}
			f.y_event_recur_every_dd_day.focus();
			return false;
		}
		else
		{
			alert( '<?=$AppUI->_("Please select a recursion pattern")?>' );
			return false;
		}
	}	
}

function validaRecursivo( f )
{		
	if ( f.event_recurs_end[1].checked)
	{
		if ( f.event_no_occurrences.value != "" )
		{
			iVal = parseInt( f.event_no_occurrences.value );
			if ( !isNaN(iVal) )
			{
				if ( iVal > 0 )
				{
					return true;
				}
				else
				{
					alert('<?=$AppUI->_("Please enter a valid number")?>');					
				}				
			}
			else
			{
				alert('<?=$AppUI->_("Please enter a number")?>');
			}
		}
		else
		{
			alert('<?=$AppUI->_("Please enter a number")?>');
		}
		f.event_no_occurrences.focus();
		return false;
	}
	else
	{
		var st = '';
		var et = '';
		st = f.event_start_time.options[f.event_start_time.selectedIndex].value;
		et = f.event_end_time.options[f.event_end_time.selectedIndex].value;
		/*if ( st>et )
		{
			alert('<?=$AppUI->_("Start time must be prior to end time.")?>');				
			return false;
		}*/


		if ( f.event_recurs_end[2].checked )
		{	
			if ( f.event_end_occurrence.value == "0000-00-00" )
			{
				alert('<?=$AppUI->_("Please select an end date")?>');				
				return false;
			}
	
			var st = '';
			var et = '';
			st = f.event_start_date_r.value;
			et = f.event_end_occurrence.value;				
			if ( st>et )
			{
				alert('<?=$AppUI->_("Start date must be prior to end date.")?>');				
				return false;
			}				
		}
		return true;
	}	
}

function submitIt(){
	var form = document.editFrm;
	var doSubmit = true;

 	if (trim(form.event_title.value).length < 1) {
		alert('<?=$AppUI->_("Please enter a valid event title")?>');
		form.event_title.focus();
		doSubmit = false;
	}
	
	if (trim(form.event_start_date.value).length < 1){
		alert('<?=$AppUI->_("Please enter a start date")?>');
		form.event_start_date.focus();
		doSubmit = false;
	}

	if (trim(form.event_end_date.value).length < 1){
		alert('<?=$AppUI->_("Please enter an end date")?>');
		form.event_end_date.focus();
		doSubmit = false;
	} 
	var st = '';
	var et = '';
	st = form.event_start_date.value+" ";
	st += form.start_time.options[form.start_time.selectedIndex].value;

	et = form.event_end_date.value+" ";
	et += form.end_time.options[form.end_time.selectedIndex].value;

	var rt = form.event_recurse_type.options[form.event_recurse_type.selectedIndex].value;
	var re = form.event_recurs_end[2].checked;

	if ( st > et &&  rt == "0" ){
		alert('<?=$AppUI->_("Start date & time must be prior to end date & time")?>');
		doSubmit = false;
	} 
	
	if ( form.opportunities.value != "0" && form.task_id.value != "0" && doSubmit){
		alert('<?=$AppUI->_("You must enter a task or an opportunity")?>');
		doSubmit = false;		
	}
		
	if ( doSubmit )
	{
		switch ( form.event_recurse_type.selectedIndex )
		{
			case 0:
				
				break;
			case 1:
				doSubmit = validaDaily( form );
				break;
			case 2:
				doSubmit = validaWeekly( form );
				break;
			case 3:
				doSubmit = validaMonthly( form );
				break;
			case 4:
				doSubmit = validaYearly( form );
				break;
		}
		
		if ( form.event_recurse_type.selectedIndex != 2 && doSubmit )
		{
			doSubmit = validaRecursivo( form );
		}
	}	
	if ( doSubmit )
	{
        form.submit();
	}	
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;	
	idate = eval( 'document.editFrm.event_' + field + '.value' );	
	if ( idate == "0000-00-00" )
	{
		idate = "<?=$date?>";		
	}
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate +'&suppressLogo=1', 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.event_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

	if(calendarField=='start_date'){
		 fld_date = eval( 'document.editFrm.event_end_date');
	     fld_fdate = eval( 'document.editFrm.end_date');
	     fld_date.value = idate;
	     fld_fdate.value = fdate;
	}

    setEventEndDate();
}

var tablaActual="<?=$AppUI->_( $obj->event_recurse_type ? $recurs[$obj->event_recurse_type] : $recurs[0] )?>";

function mostrarTabla()
{
	var nuevoId = document.editFrm.event_recurse_type.options[document.editFrm.event_recurse_type.selectedIndex].text;
	 
	if (tablaActual != "")
	{
		document.getElementById(tablaActual).style.display = 'none';
	}	 	
	//Si el evento es recursivo oculto la parte de los eventos puntuales, si es puntual oculto la de los recursivos
	if ( nuevoId != "<?=$AppUI->_($recurs[0])?>" )
	{
		document.getElementById("<?=$AppUI->_($recurs[0])?>").style.display = "none";
		document.getElementById(nuevoId).style.display = 'block';
		tablaActual=nuevoId;
		document.getElementById("recursivos").style.display = 'block';
	}	
	else
	{
		document.getElementById("recursivos").style.display = 'none';
		document.getElementById("<?=$AppUI->_($recurs[0])?>").style.display = "block";
	}	
}

function getEventDuration()
{
	var f = document.editFrm;
	var s = f.event_start_time.selectedIndex;
	var e = f.event_end_time.selectedIndex;
    var resultado = 0;
    if(s < e){
        resultado = e - s;
    }
    if(s > e){
	    resultado = (s - (e+f.event_start_time.length))*-1;
    }
    return resultado;
}

function changeEventDuration()
{
	var f = document.editFrm;
	var d = getEventDuration();
	if (! f )
		alert("no se encuentra el form");
	if ( d > 0 )
	{
		f.event_duration.selectedIndex = d-1;
	}
    setEventEndDate();
}

function changeEventEnd()
{
   var f = document.editFrm;
    var intEvSTime;
    var intEvETime;
    var intEvETimeLength;
    
	if (f.event_end_time.value <= f.event_start_time.value){

		intEvETime = f.event_end_time.selectedIndex;
		intEvSTimeLength = f.event_start_time.length-1;

		intEvSTime = intEvETime - 2;

		chkIndex = false;

		if(intEvSTimeLength < intEvSTime){
			intEvSTime = intEvSTime - (intEvSTimeLength+1);
		}
		f.event_start_time.selectedIndex = intEvSTime;
	}
}

function changeEventStart(){
    var f = document.editFrm;
    var intEvSTime;
    var intEvETime;
    var intEvETimeLength;

    intEvSTime = f.event_start_time.selectedIndex;
    intEvETimeLength = f.event_end_time.length-1;

    intEvETime = intEvSTime + 2;
    chkIndex = false;

    if(intEvETimeLength < intEvETime){
        intEvETime = intEvETime - (intEvETimeLength+1);
    }
    f.event_end_time.selectedIndex = intEvETime;
}

function changeEventStart_nr(){
    var f = document.editFrm;
    var intEvSTime;
    var intEvETime;
    var intEvETimeLength;

    intEvSTime = f.start_time.selectedIndex;
    intEvETimeLength = f.end_time.length-1;

    intEvETime = intEvSTime + 2;
    chkIndex = false;
    
    if (f.start_time.selectedIndex >= parseInt(f.start_time.options.length - 2)){
	    intEvETime = f.start_time.options.length - 1;
	}
    else if(intEvETimeLength < intEvETime){
        intEvETime = intEvETime - (intEvETimeLength+1);
    }
    f.end_time.selectedIndex = intEvETime;
  }

function changeEventEnd_nr()
{
   var f = document.editFrm;
    var intEvSTime;
    var intEvETime;
    var intEvETimeLength;
    
    if (f.end_time.selectedIndex == 0)
    	f.start_time.selectedIndex = 0;
	else if (f.end_time.value <= f.start_time.value){

		intEvETime = f.end_time.selectedIndex;
		intEvSTimeLength = f.start_time.length-1;

		intEvSTime = intEvETime - 2;

		chkIndex = false;

		if(intEvSTimeLength < intEvSTime){
			intEvSTime = intEvSTime - (intEvSTimeLength+1);
		}
		f.start_time.selectedIndex = intEvSTime;
	}
}

function setEventEndDate(){
	
    var f = document.editFrm;
    var intEvSTime;
    var intEvETime;
    var intEvSDate;
    var intEvEDate;

    if(eval(f.event_end_date_r)){

        intEvSTime = f.event_start_time.selectedIndex;
        intEvETime = f.event_end_time.selectedIndex;

        intEvSDate = parseInt(f.event_start_date_r.value);
        intEvEDate = parseInt(f.event_end_date_r.value);

        if(intEvSTime > intEvETime){
            intEvSDate += 1;

        }/*else{
            intEvEDate = intEvSDate;
        }*/
         f.event_end_date_r.value=intEvSDate;
    }

}

function checkvalue(obj){
	obj.value = trim(obj.value);
	var valor = parseFloat(obj.value);
	if (obj.value.length > 0){
		if (isNaN(valor)){
			alert("<?php echo $AppUI->_('Please enter a number');?>");
			rta = false;
			obj.focus();
		}else{
			obj.value = valor;
		}
	}
}

<?
	$CProjects->setFrmName("editFrm");
	$CProjects->setCboCompanies("idcompany");
	$CProjects->setCboProjects("project_id_task");
	$CProjects->setJSSelectedProject("project_id_task");
	$CProjects->setCboTasks("task_id");
	$CProjects->setJSSelectedTask("task_id");
		
	echo $CProjects->generateJS();
	echo $CProjects->generateJSTask();
?>
/*
function cambia_tipo(all_day)
{
	var f = document.editFrm;
	
	if(all_day.value){
	     f.event_type.disabled = true;
	}else{
	     f.event_type.disabled =false;
	}
	
	
}*/


</script>
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<form name="editFrm" action="?m=calendar&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="event_id" value="<?php echo $event_id;?>" />
	<input type="hidden" name="delegator_id" value="<?=$delegator_id?>" />	
	<input type="hidden" name="event_owner" value="<?= $obj->event_owner ? $obj->event_owner : $delegator_id ?>" />
	<input type="hidden" name="event_creator" value="<?= $obj->event_creator ? $obj->event_creator : $AppUI->user_id?>" />
	<?php
	if(isset($_GET['project'])){
		echo '<input type="hidden" name="from_projects_tab" value="1" />';
	}
	?>
<tr>
	<td width="33%" align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Event Title' );?>:</td>
	<td width="20%">
		<input type="text" class="text" size="25" name="event_title" value="<?php echo @$obj->event_title;?>" maxlength="255">
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Type');?>:</td>
	<td>
<?php	
            if ($obj->event_type==3)
            {
                $disabled = "Disabled";
                $sel_type = "0";
            }else{
                $sel_type = $obj->event_type;
            }
            
	echo arraySelect( $type, 'event_type', 'size="1" class="text" ', $obj->event_type, true );
?>
	</td>
</tr>
<tr>
	<td valign="top" align="right"><?php echo $AppUI->_( 'Description' );?>:</td>
	<td align="left" colspan="3">
		<textarea class="textarea" name="event_description" rows="5" cols="45"><?php echo @$obj->event_description;?></textarea></td>
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Location' );?>:</td>	
	<td><input type="text" class="text" size="36" name="event_location" value="<?php echo @$obj->event_location;?>" maxlength="255"></td>
</tr>
<script language="javascript">
function mostrarCombo( cual )
{	 
	document.getElementById("projectsCombo").style.display = (cual.value == 'PROJECT' ? "block" : "none");
	document.getElementById("companiesCombo").style.display = (cual.value == 'COMPANY' ? "block" : "none");	
	if ( cual.value == 'PRIVATE' )
	{		
		document.editFrm.btnSubmit.value = "<?=$AppUI->_("next")?>";
	}
	else
	{
		document.editFrm.btnSubmit.value = "<?=$AppUI->_("submit")?>";
	}
}

</script>
<tr>
	<td align="right" nowrap="nowrap"><?=$AppUI->_("People invited")?>:</td>
	<td>
	    <? if($obj->event_invitation_type==""){
           $obj->event_invitation_type = "PERSONAL";
		   }
		?>

		<?=arraySelect( $invitation_types, "event_invitation_type", 'size="1" class="text" onChange="mostrarCombo(this)"', $obj->event_invitation_type, true, true, '230 px' ); ?>
		<span id="projectsCombo" style="display:<?=$obj->event_invitation_type == "PROJECT" ? "block":"none"?>"><?=arraySelect( $projects, "event_project", 'size="1" class="text"',$obj->event_project, true, true, '230 px' )?></span>
		<span id="companiesCombo" style="display:<?=$obj->event_invitation_type == "COMPANY" ? "block":"none"?>"><?=arraySelect( $companies, "event_company", 'size="1" class="text"',$obj->event_company, true, true, '230 px' )?></span>
	</td>
</tr>
<tr> 
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Recurs' );?>:</td>
	<td><?php echo arraySelect( $recurs, 'event_recurse_type', 'size="1" class="text" onChange="mostrarTabla()"', $obj->event_recurse_type, true,false ); ?></td>	
</tr>
<tr> 
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Company' );?>:</td>
	<td><?php echo $CProjects->generateHTMLcboCompanies($company, "text");?></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
	<td><?php echo $CProjects->generateHTMLcboProjects($project, "text"," onchange=\"javascript:changeTask();\" ");?></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task' );?>:</td>
	<td><?php echo $CProjects->generateHTMLcboTasks($task, "text"); ?></td>
</tr>
<?php echo $CProjects->generateJScallFunctions(); ?>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Opportunity' );?>:</td>
	
	<?php
	
	$pipeline = new CUser();
	$pipeline->load($AppUI->user_id);
	
	$pipelines = $pipeline->getSalesPipelines( 0, "status in ('Opportunity', 'On Hold', 'Negotiation', 'Decision')" );
	
	$arrPipeline[0] = $AppUI->_('Not Specified');
	
	for($i=0;$i<count($pipelines);$i++)
	{
		$arrPipeline[$pipelines[$i]["id"]] = $pipelines[$i]["accountname"]." - ".$pipelines[$i]["projecttype"];
	}
	?>
	
	<td><?php echo(arraySelect($arrPipeline, 'opportunities', "class=\"text\" style=\"width: 220px\" tabindex=\"10\"", $pipeline_id, true, false));?></td>
</tr>
</table>

<!-- Eventos puntuales -->
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" style="display:<?=(!$obj->event_recurse_type ? "block":"none")?>" id="<?=$AppUI->_("Never")?>">
<tr>
	<td align="right" width="206"><?php echo $AppUI->_( 'Start Date' );?>:</td>
	<td  width="206">
		<input type="hidden" name="event_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : '';?>">
		<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Time' );?>:</td>
	<td><?php echo arraySelect( $times, 'start_time', 'size="1" class="text" onChange="changeEventStart_nr();"', $start_date->format( "%H%M%S" ),'',false); ?></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'End Date' );?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="event_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>">
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Time' );?>:</td>
	<td><?php echo arraySelect( $times, 'end_time', 'size="1" class="text" onChange="changeEventEnd_nr();"', $end_date->format( "%H%M%S" ),'',false); ?></td>
</tr>
<!--<tr>
   <td align="right" colspan="3">
   <?/* if($obj->event_type == "3"){ ?>
   		<input type="checkbox" name="all_day" checked  onchange="cambia_tipo(this)">
   	<?} else {?>
   		<input type="checkbox" name="all_day" onchange="cambia_tipo(this)">
   	<? } ?>
   </td>
   <td align="left" >
   <?php echo $AppUI->_('All Day Event'); */?>
   </td>
</tr>-->
<tr>
  <td colspan="4">
   <BR>&nbsp;
  </td>
</tr>
</table>

<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
</table>

<!-- Evento daily -->
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" style="display:<?=($obj->event_recurse_type=="d" ? "block" : "none") ?>" id="<?=$AppUI->_("Daily")?>">
<tr>
	<td align="right" nowrap="nowrap">
		<input type="radio" name="radio_daily" value="0" <?=($obj->event_recur_every_x_days > 0 || !$obj->event_recur_every_x_days ? "checked" : "") ?>>
	</td>
	<td>		
		<p><?php echo $AppUI->_( "Every" );?> <input type="text" name="event_recur_every_x_days" value="<?=$obj->event_recur_every_x_days > 0 ? $obj->event_recur_every_x_days : "" ?>" class="text" size="7" onClick="radio_daily[0].checked=true" onClick="checkvalue(this);" maxlength="5" > <?=$AppUI->_( "Day(s)" );?></p>
	</td>	
</tr>
<tr>
	<td align="right" nowrap="nowrap">
		<input type="radio" name="radio_daily" value = "1" <?=( $obj->event_recur_every_x_days == "-1" ? "checked" : "" )?>>
	</td>
	<td>
		<?php echo $AppUI->_( "Every weekday" );?>
	</td>
</tr>
</table>
<!-- Fin evento daily -->

<!-- Evento weekly -->
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" style="display:<?= ($obj->event_recurse_type=="w" ? "block" : "none") ?>" id="<?=$AppUI->_("Weekly")?>">
<tr>
	<td align="right" nowrap="nowrap">
		<?= $AppUI->_("Every")?>
	</td>
	<td>		
		<p><input type="text" name="event_recur_every_x_weeks" value="<?=$obj->event_recur_every_x_weeks?>" class="text" size="7"> <?=$AppUI->_( "Week(s) on:" );?></p>
	</td>	
</tr>
<tr>
	<?	
	for ( $i = 0; $i < 4; $i++ )
	{
		?>
		<td>
			<input type="checkbox" name="event_recur_every_n_days_<?=$i?>" id="event_recur_every_n_days_<?=$i?>" <?= ($obj->event_recur_every_n_days[$i] == "1" || (!$obj->event_recur_every_n_days && $fechaElegida->getDayOfWeek() == $i ) ? "checked" : "" )?> value="1">
			<?=$AppUI->_($week_days[$i])?>
		<?
	}
	?>
</tr>
<tr>
	<?
	for ( $i = 4; $i < 7; $i++ )
	{
		?>
		<td>
			<input type="checkbox" name="event_recur_every_n_days_<?=$i?>" id="event_recur_every_n_days_<?=$i?>" value="1" <?= ($obj->event_recur_every_n_days[$i] == "1" || (!$obj->event_recur_every_n_days && $fechaElegida->getDayOfWeek() == $i ) ? "checked" : "" )?>>
			<?=$AppUI->_($week_days[$i])?>
		<?
	}
	?>
</tr>
</table>
<!-- Fin evento weekly -->

<!-- Evento monthly -->
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" style="display:<?= ($obj->event_recurse_type=="m" ? "block" : "none") ?>" id="<?=$AppUI->_("Monthly")?>">
<tr>
	<td align="right" width="5" nowrap="nowrap" >
		<input type="radio" name="radio_monthly" value="dd" <?=($obj->event_recur_every_dd_day > 0 || !$obj->event_recur_every_dd_day ? "checked" : "")?>>
	</td>
	<td width="1%" nowrap="nowrap">		
		<?php echo $AppUI->_( "Day" );?> <input onClick="radio_monthly[0].checked=true" type="text" name="m_event_recur_every_dd_day" value="<?=$obj->event_recur_every_dd_day > 0 ? $obj->event_recur_every_dd_day : "" ?>" class="text" size="7">
	</td>	
	<td rowspan="2" width="20">
	</td>	
	<td rowspan="2">
	 	<?=$AppUI->_( "of every" );?> <input type="text" name="event_recur_every_x_months" value="<?=$obj->event_recur_every_x_months?>" class="text" size="7"> <?=$AppUI->_("month(s)")?>
	</td>	
</tr>
		
<tr>
	<td align="right" width="5" nowrap="nowrap">
		<input type="radio" name="radio_monthly" value = "nd" <?=($obj->event_recur_every_dd_day == "-1" ? "checked" : "")?>>
	</td>
	<td width="1%" nowrap="nowrap">
		<?php echo $AppUI->_( "The" );?> 
		<select name="m_event_recur_every_nd_day" class="text" onClick="radio_monthly[1].checked=true">
			<?
			for ($i=1; $i < count($ordered); $i++ )
			{
				?>
				<option value="<?=$i?>" <?=($obj->event_recur_every_nd_day==$i ? "selected" : "" )?>><?=$AppUI->_($ordered[$i])?></option>
				<?
			}
			?>
		</select>
		<select name="m_event_recur_every_n_day" class="text" onClick="radio_monthly[1].checked=true">
			<?
			for ($i=0; $i < count($week_days); $i++ )
			{
				?>
				<option value="<?=$i?>" <?=($obj->event_recur_every_n_day == $i || ($obj->event_recur_every_nd_day < 1 && $fechaElegida->getDayOfWeek() == $i ) ? "selected" : "")?>><?=$AppUI->_($week_days[$i])?></option>
				<?
			}
			?>
		</select>
	</td>	
</tr>

</table>
<!-- Fin evento monthly -->

<!-- Evento yearly -->
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" style="display:<?= ($obj->event_recurse_type=="y" ? "block" : "none") ?>" id="<?=$AppUI->_("Yearly")?>">
<tr>
	<td align="right" nowraping="nowraping">
		<?=$AppUI->_("Every")?>
	</td>
	<td>	
		<select name="event_recur_every_mm_month" class="text" onClick="radio_yearly[0].checked=true">
		<?
		for ($i = 1; $i < count($months) + 1; $i++ )
		{
			?>
			<option value="<?=$i?>" <?=$obj->event_recur_every_mm_month == $i || ( $obj->event_recur_every_mm_month <= 0 && $fechaElegida->getMonth() == $i) ? "selected" : ""?> ><?=$AppUI->_($months[$i])?></option>
			<?
		}
		?>
		</select>
	</td>
<tr>
	<td align="right" nowrap="nowrap">
		<input type="radio" name="radio_yearly" <?=$obj->event_recur_every_dd_day > 0 || !$obj->event_recur_every_dd_day ? "checked" : ""?> value="dd">		
	</td>
	<td>
		<p><?=$AppUI->_("Day")?>
		<input type="text" name="y_event_recur_every_dd_day" value="<?=$obj->event_recur_every_dd_day > 0 ? $obj->event_recur_every_dd_day : "" ?>" size="7" class="text" onClick="radio_yearly[0].checked=true">
		</p>
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap">
		<input type="radio" name="radio_yearly" <?=$obj->event_recur_every_dd_day == "-1" ? "checked" : ""?> value="nd">
	</td>
	<td>
		<p>
		<?=$AppUI->_("The")?> 
		<select name="y_event_recur_every_nd_day" class="text" onClick="radio_yearly[1].checked=true">
			<?
			for ( $i=1; $i < count($ordered) + 1; $i++ )
			{
				?>
				<option value="<?=$i?>" <?=$i==$obj->event_recur_every_nd_day ? "selected" : "" ?>><?=$AppUI->_($ordered[$i])?></option>
				<?
			}
			?>
		</select> 
		<select name="y_event_recur_every_n_day" class="text" onClick="radio_yearly[1].checked=true">
			<?
			for ( $i=0; $i < count($week_days); $i++ )
			{
				?>
				<option value="<?=$i?>" <?= $i==$obj->event_recur_every_n_day || (!$obj->event_recur_every_n_day && $fechaElegida->getDayOfWeek() == $i) ? "selected" : "" ?>><?=$AppUI->_($week_days[$i])?></option>
				<?
			}
			?>
		</select>		
		</p>
	</td>
</tr>
</table>
<!-- Fin evento yearly -->

<!-- Tabla para todos lo eventos recursivos -->
<?
	$ev_st_time = new CDate( "0000-00-00 ".$obj->event_start_time);
	$st_time = $ev_st_time->format("%H%M%S");
    
	$ev_end_time = new CDate( "0000-00-00 ".$obj->event_end_time);
	
    if(!$event_id){ 
		$ev_end_time->addSeconds(3600);//agrego una hora
		}

	$end_time = $ev_end_time->format("%H%M%S");
     
	$duration = intval(substr($end_time, 0, 2))*60 + intval(substr($end_time, 2, 2));
	$duration -= intval(substr($st_time, 0, 2))*60 + intval(substr($st_time, 2, 2));
    	
	
	if ($obj->event_start_time){
	 $tmp = $st_time;
	}
	else{
	 $ev_st_time = new CDate( "0000-00-00 08:00:00");
	 $st_time = $ev_st_time->format("%H%M%S");

	 $tmp = $st_time;
	}
        
	if ($obj->event_end_time){
	 $tmp_e = $end_time;
	}
	else{
	 $ev_et_time = new CDate( "0000-00-00 09:00:00");
	 $et_time = $ev_et_time->format("%H%M%S");

	 $tmp_e = $et_time;
	}

 ?>
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std" style="display:<?= $obj->event_recurse_type ? "block" : "none" ?>" id="recursivos">
<tr>
	<td align="right" nowrap="nowrap">
		<?=$AppUI->_("Start")?><?php echo arraySelect( $times, "event_start_time", 'size="1" class="text" onChange="changeEventStart();"', $tmp ,'',false ); ?>
	</td>
	<td>
		<?=$AppUI->_("End")?><?php echo arraySelect( $times, "event_end_time", 'size="1" class="text" id="event_end_time" onChange="changeEventEnd();" ', $tmp_e ,'',false); ?>
                        
		 <? /*if($obj->event_type == "3"){ ?>
	   		<input type="checkbox" name="all_day_r" checked  onchange="cambia_tipo(this)">
	   	 <?} else {?>
	   		<input type="checkbox" name="all_day_r" onchange="cambia_tipo(this)">
	   	 <? } ?>
		  
		   <?php echo $AppUI->_('All Day Event'); */?>

	</td>
	<td>
        
		<!-- <?=$AppUI->_("Duration")?><?php echo arraySelect( $durations, "event_duration", 'size="1" class="text", onChange="changeEventEnd();"', $duration,'',false );?> -->
        <?php
        //echo "<script language=\"javascript\">changeEventEnd();</script>";
        ?>
    </td>
</tr>
<tr>
	<td>
		<input type = "hidden" name = "event_start_date_r" value = "<?=$start_date->format( FMT_TIMESTAMP_DATE )?>">
        <input type = "hidden" name = "event_end_date_r" value = "<?=$end_date->format( FMT_TIMESTAMP_DATE )?>">
		<?=$AppUI->_("Start")?> <input type="text" name="start_date_r" value="<?php echo $start_date ? $start_date->format( $df ) : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('start_date_r');">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td>
		<input type = "radio" name = "event_recurs_end" value = "n" <?=( ($obj->event_no_occurrences == "-1" && $obj->event_end_occurrence == "0000-00-00") || !$obj->event_no_occurrences ? "checked":"")?>>
		<?=$AppUI->_("No end date")?>
	</td>
	<td>
		&nbsp;
	</td>	
</tr>
</tr>	
	<td>
		&nbsp;
	</td>
	<td>		
		<input type = "radio" name = "event_recurs_end" value = "x" <?=($obj->event_no_occurrences > 0 ? "checked":"")?>>
		<?=$AppUI->_("End after")?>
	</td> 
	<td>		
		<input size="7" class="text" type="text" name="event_no_occurrences" onClick="event_recurs_end[1].checked=true" value="<?=$obj->event_no_occurrences > 0 ? $obj->event_no_occurrences : "" ?>"> <?=$AppUI->_("occurrences")?>
	</td>	
</tr>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type = "radio" name = "event_recurs_end" value = "d" <?=($obj->event_end_occurrence && $obj->event_end_occurrence != "0000-00-00" ? "checked":"")?>>
		<?=$AppUI->_("End by")?>	
	</td>
	<td>
		<input type = "hidden" name = "event_end_occurrence" value = "<?= $obj->event_end_occurrence != null ? $obj->event_end_occurrence : "" ?>">
		<? $end_oc = (intval($obj->event_end_occurrence) ? new CDate($obj->event_end_occurrence) : null ) ?>
		<input class="text" type="text" name="end_occurrence" onClick="event_recurs_end[2].checked=true" disabled="disabled" value="<?=($end_oc ? $end_oc->format( $df ) : "" )?>">
		<a href="#" onClick="popCalendar('end_occurrence');event_recurs_end[2].checked=true;">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>	
</tr>
</table>
<!-- Fin eventos recursivos -->
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<tr>
	<td colspan="2">
		<?php
			if(isset($_GET['project'])){
		?>
			<input type="button" value="<?php echo $AppUI->_( 'close' );?>" class="button" onclick="javascript:window.close();">
		<?php }else{
		?>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onclick="javascript:history.back();">
		<?php } ?>
	</td>
	<td align="right" colspan="2">
		<input type="button" value="<?php echo $AppUI->_( 'submit' ) ?>" class="button" onClick="submitIt()" name="btnSubmit">
	</td>
</tr>
</table>
</form>
<script language="javascript">
	setProject('document.editFrm.project_id_task', '<? echo($project) ?>');
	setTask('document.editFrm.task_id', '<? echo($task) ?>');
	
	
</script>