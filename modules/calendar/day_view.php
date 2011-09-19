<?php /* CALENDAR $Id: day_view.php,v 1.4 2009-06-19 18:33:56 pkerestezachi Exp $ */
require_once( $AppUI->getModuleClass( 'tasks' ) );
include ('./functions/delegates_func.php');

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

$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );

$mod_id = 4; //El codigo del modulo para chequear que sean delegados validos.
$canAdd = 1; //En principio puede agregar registros
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
	$canAdd = $permiso == "AUTHOR" || $AppUI->user_type == 1;
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

$AppUI->savePlace();

require_once( $AppUI->getModuleClass( 'tasks' ) );

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CalVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CalVwTab' ) !== NULL ? $AppUI->getState( 'CalVwTab' ) : 0;
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

// establish the focus 'date'
$this_day = new CDate( $date );
$dd = $this_day->getDay();
$mm = $this_day->getMonth();
$yy = $this_day->getYear();

// prepare time period for 'events'
$first_time = clone($this_day);
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );

$last_time = clone($this_day);
$last_time->setTime( 23, 59, 59 );

$prev_day = new CDate( Date_calc::prevDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );
$next_day = new CDate( Date_calc::nextDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );
 
$first_week_day = clone($this_day);
while ( $first_week_day->getDayOfWeek() != 1 )
{
	$first_week_day->addDays(-1);
}
// setup the title block
$titleBlock = new CTitleBlock( 'Day View', 'calendar.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&a=month_view&date=".$this_day->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog", "month view" );
$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&date=".$this_day->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog", "week view" );

if($canAdd==1){
$titleBlock->addCrumb( "?m=calendar&a=addedit&dialog=$dialog&delegator_id=$delegator_id&date=".$this_day->format( FMT_TIMESTAMP_DATE ), "new event" );
}


$titleBlock->addCell( '&nbsp;&nbsp;'.$AppUI->_('Company').':'.arraySelect( $companies, 'company_id', 'onChange="document.pickFilter.submit()" class="text" style="width:160px;" ', $company_id,'',false, '' ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickFilter">', '');
$titleBlock->addCell();

$titleBlock->addCell( '&nbsp;&nbsp;'.$AppUI->_('Canal').':'.arraySelect( $canal, 'canal_id', 'onChange="document.pickFilter.submit()" class="text" style="width:160px;" ', $canal_id,'',false, '' ), '',
	'', '');

$titleBlock->addCell( '&nbsp;&nbsp;' . $AppUI->_('Project').':'.arraySelect( $projects, 'project_id', 'onChange="document.pickFilter.submit()" size="1" class="text" style="width:160px;', $project_id,'',false,'' ), '','', '</form>');
$titleBlock->addCell();

if ( $canAdd )
{
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new event').'">', '',
		'<form action="?m=calendar&delegator_id='.$delegator_id.'&a=addedit&date='.$this_day->format( FMT_TIMESTAMP_DATE ).'&dialog='.$dialog.'" method="post">', '</form>'
	);
}

$titleBlock->show();
?>
<script language="javascript">
function clickDay( idate, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+idate+'&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>';
}


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

<style type="text/css">
table.tbl td.event {
	background-color: #f0f0f0;
}
</style>

<table width="100%" cellspacing="0" cellpadding="4">
<tr>
	<td valign="top">
		<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
		<tr>
			<td>
				<a href="?m=calendar&a=day_view&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&date=<?=$prev_day->format( FMT_TIMESTAMP_DATE )?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></a>
			</td>
			<th width="100%">
				<?php 
				
				$dia_stmp = $this_day->format( "%A");
				$dia_ntmp = $this_day->format( "%d" );
				$mes_tmp = $this_day->format( "%B" ); 
				$year = $this_day->format( "%Y" ); 
				
				if ($AppUI->user_prefs[LOCALE]=="es"){
				echo $AppUI->_($dia_stmp).", ".$dia_ntmp." ".$AppUI->_('of')." ".$AppUI->_($mes_tmp)." ".$AppUI->_('of')." ".$year;
				}
				else
				{
				echo $AppUI->_($dia_stmp).", ".$dia_ntmp." ".$AppUI->_($mes_tmp)." ".$year;
				}
				?>
			</th>
			<td>
				<a href="?m=calendar&a=day_view&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&date=<?=$next_day->format( FMT_TIMESTAMP_DATE )?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></a>
			</td>
		</tr>
		</table>

<?php
// tabbed information boxes
$tabBox = new CTabBox( "?m=calendar&a=day_view&delegator_id=$delegator_id&date=".$this_day->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog",
	"{$AppUI->cfg['root_dir']}/modules/calendar/", $tab );
$tabBox->add( 'vw_day_events', 'Events' );
$tabBox->add( 'vw_day_tasks', 'Tasks' );
$tabBox->show();
?>
	</td>
	<td valign="top" width="175">
<?php
$minical = new CMonthCalendar( $this_day );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions( 'clickDay' );
$minical->user_id = $delegator_id;
$minical->clickMonth = false;

$minical->setDate( $minical->prev_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$minical->setDate( $minical->next_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$minical->setDate( $minical->next_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$df = $AppUI->getPref('SHDATEFORMAT');
?>
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td colspan="2" align="center">
					<form name="frm">
						<input type="hidden" name="m" value="calendar">	
						<input type="hidden" name="date" value="<?=$this_day->format( FMT_TIMESTAMP_DATE )?>">
						<input type="hidden" name="a" value="day_view">
						<input type="hidden" name="delegator_id" value="<?=$delegator_id?>">
						<input type="hidden" name="dialog" value="<?=$dialog?>">
						<input type="text" name="a_date" disabled="disabled" class="text" value = "<?=$this_day->format($df)?>">
						<a href="#" onClick="popCalendar('date')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
						</a>			
						<input type="submit" class="button" value = "<?=$AppUI->_("Go to")?>">			
					</form>		
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
