<?php /* CALENDAR $Id: index.php,v 1.5 2011-02-24 15:57:48 pkerestezachi Exp $ */
$AppUI->savePlace();

require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( './modules/timexp/report_to_items.php' );
include ('./functions/delegates_func.php');

if (isset( $_REQUEST['hideTasksMap'] ))
	$AppUI->setState( 'CalIdxHideTask', intval( $_REQUEST['hideTasksMap'] ) );
	
$hideTasksMap = $AppUI->getState( 'CalIdxHideTask' ) !== NULL ? $AppUI->getState( 'CalIdxHideTask' ) : "1";
			
if (isset( $_REQUEST['hideMyTasks'] ))
	$AppUI->setState( 'CalIdxHideMyTask', intval( $_REQUEST['hideMyTasks'] ) );

$hideMyTasks = $AppUI->getState( 'CalIdxHideMyTask' ) !== NULL ? $AppUI->getState( 'CalIdxHideMyTask' ) : "1";
		
// retrieve any state parameters
if (isset( $_REQUEST['company_id'] ))
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );

$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : "0";
	
if (isset( $_REQUEST['canal_id'] ))
	$AppUI->setState( 'CalIdxCanal', intval( $_REQUEST['canal_id'] ) );

$canal_id = $AppUI->getState( 'CalIdxCanal' ) !== NULL ? $AppUI->getState( 'CalIdxCanal' ) : "0";

if($company_id > 0 && $canal_id == "0")
	$extraProjects = array('where' => 'AND project_company = '.$company_id);

if ($company_id > 0 && $canal_id > 0)
	$extraProjects = array('where' => 'AND project_company = '.$company_id.' AND project_canal = '.$canal_id);	

if ($company_id == "0" && $canal_id > 0)
	$extraProjects = array('where' => ' AND project_canal = '.$canal_id);

if (isset( $_REQUEST['project_id'] ))
	$AppUI->setState( 'CalIdxProject', intval( $_REQUEST['project_id'] ) );

$project_id = $AppUI->getState( 'CalIdxProject' ) !== NULL ? $AppUI->getState( 'CalIdxProject' ) : "0";

if (isset( $_REQUEST['task_user_id'] ))
	$AppUI->setState( 'CalIdxUser', intval( $_REQUEST['task_user_id'] ) );

$task_user_id = $AppUI->getState( 'CalIdxUser' ) !== NULL ? $AppUI->getState( 'CalIdxUser' ) : "0";

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
else
{
	$allow = "";
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
	if($company_id > 0 && $project_id > 0)
		$extraProjects['where'] = 'AND project_id  = '.$project_id;
		
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

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );

$mod_id = 4; //El codigo del modulo para chequear que sean delegados validos.
$canAdd = 0; //Para ver si puede agregar registros
$user_id=$AppUI->user_id;
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
	IF ($permiso == "AUTHOR" OR $AppUI->user_type == 1) $canAdd = 1;
	$user_id=$delegator_id;
	do_log($delegator_id, $mod_id, $AppUI, 1);
}
else
{
	if ( getDenyRead( $m ) )
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$canEdit = !getDenyEdit( $m );
	$canAdd = $canEdit;
}

$first_time = new CDate( $date );
while ( $first_time->getDayOfWeek() != 1 )
{
       $first_time->addDays(-1);
}

// establish the focus 'date'
$this_week = $first_time;
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();

// prepare time period for 'events'
/*$first_time = new CDate( Date_calc::beginOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );*/
//$first_time =  $first_week_day;
$last_time = new CDate( Date_calc::endOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$last_time->setTime( 23, 59, 59 );

$last_time->addDays(1);

$prev_week = new CDate( Date_calc::beginOfPrevWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$next_week = new CDate( Date_calc::beginOfNextWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );


$links = array();

//No muestro las tareas porque tiene el flag de hide Task = 1
if($hideMyTasks != "1" || $hideTasksMap != "1")
{
	// assemble the links for the tasks
	require_once( "modules/calendar/links_tasks.php" );
	getTaskLinks( $first_time, $last_time, $links, 50, $company_id, $project_id, ($hideMyTasks != "1" ? true : false), ($hideTasksMap != "1" ? true : false), ($task_user_id > "0" ? $task_user_id : null));
}

// assemble the links for the events
require_once( "modules/calendar/links_events.php" );
require_once( "modules/calendar/links_exceptions.php" );
getEventLinks( $first_time, $last_time, $links, 50, $delegator_id, ($company_id == "0" || $company_id == null ? null : $company_id), ($project_id == "0" || $project_id == null ? null : $project_id));
getExceptionLinks($first_time, $last_time, $links, 50);

if($hideTasksMap != "1")
{
	if ($task_user_id == "0")
	{
		$users_owners = CProject::getUsersMyOwnerProjects('', 0, 0, 0, '', false, $AppUI->user_company);
		
		foreach ( $users_owners as $user_key => $user_name )
			getExceptionLinks($first_time, $last_time, $links, 50, $user_key, false );
	}
	else
		getExceptionLinks($first_time, $last_time, $links, 50, $task_user_id, false );
}

if($date != ""){
   $fecha = $date;	
}else{
   $fecha = $this_week->format( FMT_TIMESTAMP_DATE );	
}
// setup the title block
$titleBlock = new CTitleBlock( 'Week View', 'calendar.gif', $m, "colaboration.index" );
$titleBlock->addCrumb( "?m=calendar&a=month_view&delegator_id=$delegator_id&dialog=$dialog&date=".$fecha, "month view" );
$titleBlock->addCrumb("?m=calendar&a=day_view&dialog=$dialog&delegator_id=$delegator_id&date=".$fecha, "day view" );
if($canAdd==1){
$titleBlock->addCrumb("?m=calendar&a=addedit&dialog=$dialog&delegator_id=$delegator_id&date=".$fecha, "new event" );
}

$urlHideTask = "?m=calendar&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog&hideMyTasks=".($hideMyTasks == '1' ? '0' : '1')."&hideTasksMap=".$hideTasksMap;
$urlHideTaskMap = "?m=calendar&delegator_id=$delegator_id&date=".$this_week->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog&hideMyTasks=".$hideMyTasks."&hideTasksMap=".($hideTasksMap == '1' ? '0' : '1')."&t=t";
$titleBlock->addCrumb($urlHideTask, ($hideMyTasks == '1' ? $AppUI->_('show my assigned tasks') : $AppUI->_('hide my assigned tasks')));
$titleBlock->addCrumb($urlHideTaskMap, ($hideTasksMap == '1' ? $AppUI->_('show allocation map') : $AppUI->_('hide allocation map')));

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

if ( $canAdd )
{
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new event').'">', '',
		'<form action="?m=calendar&a=addedit&dialog='.$dialog.'&delegator_id='.$delegator_id.'&date='.$this_week->format( FMT_TIMESTAMP_DATE ).'" method="post">', '</form>'
	);
}

$titleBlock->show();

//print_r($this_week);
$mes = $this_week->format( "%B" );

?>

<style type="text/css">
TD.weekDay  {
	height:120px;
	vertical-align: top;
	padding: 1px 4px 1px 4px;
	border-bottom: 1px solid #ccc;
	border-right: 1px solid  #ccc;
	text-align: left;
}
</style>

<table width="100%" border="0">
	<tr valign='top'>
		<td width="55%">
			<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle" >
			<tr>
				<td>
					<a href="?m=calendar&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&date=<?=$prev_week->format( FMT_TIMESTAMP_DATE )?>"><img src="images/prev.gif" width="17" height="17" alt="pre" border="0"></A>
				</td>
				<th width="100%">
					<?php
					$weekMonth=$this_week->format( "%B" );
					eval ("\$weekMonth=\$AppUI->_('$weekMonth');");
					?>
					<span style="font-size:10pt"><?php echo $AppUI->_( 'Week' ).' '.$this_week->format( "%U" ).' - '.$weekMonth.' '.$this_week->format( "%Y" ); ?></span>					
				</th>
				<td nowrap>
					<a href="?m=calendar&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&date=<?=$next_week->format( FMT_TIMESTAMP_DATE) ?>"><img src="images/next.gif" width="17" height="17" alt="next" border="0"></a>
				</td>
			</tr>
			</table>
			<table border="0" cellspacing="1" cellpadding="2" width="100%" style="margin-width:4px;background-color:white">
			<?php
			$column = 0;
			$format = array( "<strong>%d</strong> %A", "%A <strong>%d</strong>" );
			$show_day = $this_week;

			$today = new CDate();
			$today = $today->format( FMT_TIMESTAMP_DATE );
			if ($_GET['date']!='') $today=$_GET['date'];
			//echo "<br><br>$today";

			for ($i=0; $i < 7; $i++) {
				$dayStamp = $show_day->format( FMT_TIMESTAMP_DATE );

				$day  = $show_day->getDay();
				$href = "?m=calendar&a=day_view&date=$dayStamp&delegator_id=$delegator_id&dialog=$dialog";

				$s = '';
				if ($column == 0) {
					$s .= '<tr>';
				}
				$s .= '<td class="weekDay" style="width:50%;">';

				$s .= '<table style="width:100%;border-spacing:0;">';
				$s .= '<tr>';
				$s .= '<td><a href="'.$href.'"><B>'.$day1;

				$s .= $dayStamp == $today ? '<span style="color:red">' : '';
				$showday=$show_day->format( $format[0] );
				$showdayNro=substr($showday, 0, 19);
				$showdayDay=substr($showday, 19);
				eval ("\$showdayDay=\$AppUI->_('$showdayDay');");
				$s .= $showdayNro.' '.$showdayDay;
				$s .= $dayStamp == $today ? '</b></span>' : '';
				$s .= '</a><br/><br/></td></tr>';

				$s .= '<tr><td>';

				if (isset( $links[$dayStamp] )) {
					foreach ($links[$dayStamp] as $e) {
						$href = isset($e['href']) ? $e['href'] : null;
						$alt = isset($e['alt']) ? $e['alt'] : null;
						$text = str_replace('</br>','&nbsp;',$e['text']);

						$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
						$s .= $text;
						$s .= $href ? '</a><br/>' : '';
					}
				}

				$s .= '</td></tr></table>';

				$s .= '</td>';
				if ($column == 1) {
					$s .= '</tr>';
				}
				$column = 1 - $column;

			// select next day
				$show_day->addSeconds( 24*3600 );
				echo $s;
			}

			//Para el go to
			$df = $AppUI->getPref('SHDATEFORMAT');
			?>
			</table>
		</td>
		<td valign='top'>
			<?php
				include('modules/calendar/user_todo.php');
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2" bgcolor="#e9e9e9" align="center">
			<form name="frm">
				<input type="hidden" name="m" value="calendar">
				<input type="hidden" name="date" value="<?=$this_week->format( FMT_TIMESTAMP_DATE )?>">
				<input type="text" name="a_date" disabled="disabled" class="text" value="<?=$this_week->format($df)?>">
				<input type="hidden" name="delegator_id" value="<?=$delegator_id?>">
				<input type="hidden" name="dialog" value="<?=$dialog?>">
				<a href="#" onClick="popCalendar('date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
				<input type="submit" class="button" value = "<?=strtolower($AppUI->_("Go to"))?>">
				&nbsp;&nbsp;
				<a href="./index.php?m=calendar&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>"><?php echo $AppUI->_('today');?></A>
			</form>
		</td>
	</tr>
</table>
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
</script>