<?php /* CALENDAR $Id: month_view.php,v 1.4 2009-06-19 18:33:56 pkerestezachi Exp $ */
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );

include ('./functions/delegates_func.php');
require_once( './modules/timexp/report_to_items.php' );

if (isset( $_REQUEST['hideTasksMap'] ))
	$AppUI->setState( 'CalIdxHideTask', intval( $_REQUEST['hideTasksMap'] ) );
	
$hideTasksMap = $AppUI->getState( 'CalIdxHideTask' ) !== NULL ? $AppUI->getState( 'CalIdxHideTask' ) : "1";
			
if (isset( $_REQUEST['hideMyTasks'] ))
	$AppUI->setState( 'CalIdxHideMyTask', intval( $_REQUEST['hideMyTasks'] ) );

$hideMyTasks = $AppUI->getState( 'CalIdxHideMyTask' ) !== NULL ? $AppUI->getState( 'CalIdxHideMyTask' ) : "1";
	
if ( $delegator_id == $AppUI->user_id )
{
	if ( getDenyRead( $m ) )
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$canEdit = !getDenyEdit( $m );
	$canAdd = $canEdit;
}
else
{
	$mod_id = 4;
	//Hay que ver que sea delegado de quien dice ser
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
	$canAdd = $permiso == "AUTHOR" || $AppUI->user_type == 1;

	do_log($delegator_id, $mod_id, $AppUI, 1);
	//echo "<br>$sql<br>";
}

$AppUI->savePlace();
dPsetMicroTime();

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );

// set default adicional parameters
$extraCompanies=null;
$extraProjects=null;

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] ))
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );

$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : "0";

if (isset( $_REQUEST['canal_id'] ))
	$AppUI->setState( 'CalIdxCanal', intval( $_REQUEST['canal_id'] ) );

$canal_id = $AppUI->getState( 'CalIdxCanal' ) !== NULL ? $AppUI->getState( 'CalIdxCanal' ) : "0";

if($company_id !="" && $canal_id =="")
	$extraProjects = array('where' => 'AND project_company = '.$company_id);

if ($company_id !="" && $canal_id !="")

	$extraProjects = array('where' => 'AND project_company = '.$company_id.' AND project_canal = '.$canal_id);	

if ($company_id =="" && $canal_id !="")
	$extraProjects = array('where' => ' AND project_canal = '.$canal_id);
	
if (isset( $_REQUEST['project_id'] ))
	$AppUI->setState( 'CalIdxProject', intval( $_REQUEST['project_id'] ) );

$project_id = $AppUI->getState( 'CalIdxProject' ) !== NULL ? $AppUI->getState( 'CalIdxProject' ) : "0";

if (isset( $_REQUEST['task_user_id'] ))
	$AppUI->setState( 'CalIdxUser', intval( $_REQUEST['task_user_id'] ) );
	
$task_user_id = $AppUI->getState( 'CalIdxUser' ) !== NULL ? $AppUI->getState( 'CalIdxUser' ) : "0";	

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name');
natcasesort($companies);
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

// Traigo los canales
$obj_cia = new CCompany();
$allow = $obj_cia->getAllowedRecords( $AppUI->user_id, 'company_id');

if (count($allow) >0  )
{
  $allow = "AND company_id IN (" . implode( ',', $allow ) . ")";
}

if($company_id > 0)
{
  $sql_cia = "AND p.project_company = '$company_id' ";
}

$sql_canal = "SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE p.project_canal = c.company_id  ".$allow." ".$sql_cia." order by company_name ";

$canal = db_loadHashList( $sql_canal );
$canal = arrayMerge( array( '0'=>$AppUI->_('All') ), $canal );

// get the list of visible projects
$project = new CProject();

if($company_id != "0")
{
	$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name',null,$extraProjects );
	natcasesort($projects);
}
$projects = arrayMerge( array( '0'=>$AppUI->_('All') ), $projects );

if($hideTasksMap == "0")
{
	if($company_id != "0" && $project_id != "0")
		$extraProjects = arrayMerge( array('where' => 'AND project_id  = '.$project_id), $extraProjects );
		
	$projects_users = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name',null, $extraProjects);
	
	foreach ($projects_users as $k => $v )
	{
		$usersTemp = CProject::getUsersAssignedToTasks($k);
		$users = arrayMerge( $usersTemp, $users );
	}

	if(count($users) > 0)
	{
		foreach ($users as $u => $p )
		{
			$user = new CUser();
			$user->load($u);
			
			$usersAvailables[$u] = $user->user_last_name.", ".$user->user_first_name;
		}
	}
	
	$usersAvailables = arrayMerge( array( '0'=>$AppUI->_('All') ), $usersAvailables );
}

dPsetMicroTime();
require_once( $AppUI->getModuleClass( 'tasks' ) );

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// establish the focus 'date'
$this_week = new CDate( $date );
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();


// setup the title block
$titleBlock = new CTitleBlock( 'Monthly Calendar', 'calendar.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog", "week view" );
$titleBlock->addCrumb( "?m=calendar&a=day_view&dialog=$dialog&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE ), "day view" );
if($canAdd==1){
$titleBlock->addCrumb( "?m=calendar&a=addedit&dialog=$dialog&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE ), "new event" );
}

$titleBlock->addCell( '&nbsp;&nbsp;'.$AppUI->_('Company').':'.arraySelect( $companies, 'company_id', 'onChange="document.pickFilter.submit()" class="text" style="width:160px;" ', $company_id,'',false, '' ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickFilter">', '');
$titleBlock->addCell();

$titleBlock->addCell( '&nbsp;&nbsp;'.$AppUI->_('Canal').':'.arraySelect( $canal, 'canal_id', 'onChange="document.pickFilter.submit()" class="text" style="width:160px;" ', $canal_id,'',false, '' ), '',
	'', '');

$titleBlock->addCell( '&nbsp;&nbsp;' . $AppUI->_('Project').':'.arraySelect( $projects, 'project_id', 'onChange="document.pickFilter.submit()" size="1" class="text" style="width:160px;', $project_id,'',false,'' ), '','', ($hideTasksMap == "0" ? '' : '</form>'));
$titleBlock->addCell();

if ($hideTasksMap == "0")
	$titleBlock->addCell( '&nbsp;&nbsp;' . $AppUI->_('User').':'.arraySelect($usersAvailables, 'task_user_id', 'onChange="document.pickFilter.submit()" size="1" class="text" style="width:160px;', $task_user_id,'',false,'' ), '','', '</form>');
	$titleBlock->addCell();

$urlHideTask = "?m=calendar&a=month_view&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog&hideMyTasks=".($hideMyTasks == '1' ? '0' : '1')."&hideTasksMap=".$hideTasksMap;
$urlHideTaskMap = "?m=calendar&a=month_view&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog&hideMyTasks=".$hideMyTasks."&hideTasksMap=".($hideTasksMap == '1' ? '0' : '1')."&t=t";
$titleBlock->addCrumb($urlHideTask, ($hideMyTasks == '1' ? $AppUI->_('show my assigned tasks') : $AppUI->_('hide my assigned tasks')));
$titleBlock->addCrumb($urlHideTaskMap, ($hideTasksMap == '1' ? $AppUI->_('show allocation map') : $AppUI->_('hide allocation map')));

if ( $canAdd )
{
	$titleBlock->addCell(
		'&nbsp;<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new event').'">', '',
		'<form action="?m=calendar&a=addedit&dialog='.$dialog.'&delegator_id='.$delegator_id.'&date='.$this_week->format( FMT_TIMESTAMP_DATE ).'" method="post">', '</form>'
	);
}
$titleBlock->show();
//Preferencia del usuario
$df = $AppUI->getPref('SHDATEFORMAT');
?>
<script language="javascript">

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.frm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate +'&suppressLogo=1', 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.frm.' + calendarField );
	fld_fdate = eval( 'document.frm.a_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+uts+'&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>';
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=calendar&date='+uts+'&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>';
}

 </script>

<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td>
<?php
// establish the focus 'date'
$date = new CDate( $date );

// prepare time period for 'events'
$first_time = new CDate( $date );
$first_time->setDay( 1 );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( $date );
$last_time->setDay( $date->getDaysInMonth() );
$last_time->setTime( 23, 59, 59 );

$links = array();


//No muestro las tareas porque tiene el flag de hide Task = 1
if($hideMyTasks != "1" || $hideTasksMap != "1")
{
	// assemble the links for the tasks
	require_once( "modules/calendar/links_tasks.php" );
	getTaskLinks( $first_time, $last_time, $links, 20, $company_id, $project_id, ($hideMyTasks != "1" ? true : false), ($hideTasksMap != "1" ? true : false), ($task_user_id > "0" ? $task_user_id : null));
}

// assemble the links for the events
require_once( "modules/calendar/links_events.php" );
require_once( "modules/calendar/links_exceptions.php" );
getEventLinks( $first_time, $last_time, $links, 20, $delegator_id, ($company_id == "0" || $company_id == null ? null : $company_id), ($project_id == "0" || $project_id == null ? null : $project_id));
getExceptionLinks($first_time, $last_time, $links, 20);

if($hideTasksMap != "1")
{
	if ($task_user_id == "0")
	{
		$users_owners = CProject::getUsersMyOwnerProjects('', 0, 0, 0, '', false, $AppUI->user_company);
		
		foreach ( $users_owners as $user_key => $user_name )
			getExceptionLinks($first_time, $last_time, $links, 20, $user_key, false );
	}
	else
		getExceptionLinks($first_time, $last_time, $links, 20, $task_user_id, false );
}

// create the main calendar
$cal = new CMonthCalendar( $date  );
$cal->setStyles( 'motitle', 'mocal' );
$cal->setLinkFunctions( 'clickDay', 'clickWeek' );
$cal->setEvents( $links );
$cal->delegator_id = $delegator_id;


// Ac  pega el calendario del mes
echo $cal->show_big();

// create the mini previous and next month calendars under
$minical = new CMonthCalendar( $cal->prev_month );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions( 'clickDay' );
$minical->delegator_id= $delegator_id;
echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td valign="top" align="center" width="200">'.$minical->show().'</td>';
?>
<td valign="center" align="center" width="100%">
	<form name="frm">
		<input type="hidden" name="m" value="calendar" />
		<input type="hidden" name="delegator_id" value="<?=$delegator_id?>" />
		<input type="hidden" name="dialog" value="<?=$dialog?>" />
		<input type="hidden" name="date" value="<?=$date->format( FMT_TIMESTAMP_DATE )?>" />
		<input type="text" name="a_date" disabled="disabled" class="text" value = "<?=$date->format($df)?>" />
		<a href="#" onClick="popCalendar('date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
		<input type="submit" class="button" value = "<?=strtolower($AppUI->_("Go to"))?>">
	</form>
</td>
<?
$minical->setDate( $cal->next_month );

echo '<td valign="top" align="center" width="200">'.$minical->show().'</td>';
echo '</tr></table>';
?>
</td></tr></table>
