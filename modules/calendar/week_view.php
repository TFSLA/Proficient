<?php /* CALENDAR $Id: week_view.php,v 1.2 2009-06-19 18:33:56 pkerestezachi Exp $ */
$AppUI->savePlace();

require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( './modules/timexp/report_to_items.php' );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );

$mod_id = 4; //El codigo del modulo para chequear que sean delegados validos.
$canAdd = 1; //Para ver si puede agregar registros

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

// establish the focus 'date'
$this_week = new CDate( $date );
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();

// prepare time period for 'events'
$first_time = new CDate( Date_calc::beginOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( Date_calc::endOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$last_time->setTime( 23, 59, 59 );

$prev_week = new CDate( Date_calc::beginOfPrevWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$next_week = new CDate( Date_calc::beginOfNextWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );

$links = array();

// assemble the links for the tasks
require_once( $AppUI->getConfig( 'root_dir' )."/modules/calendar/links_tasks.php" );
getTaskLinks( $first_time, $last_time, $links, 50, 0 );


// assemble the links for the events
require_once( $AppUI->getConfig( 'root_dir' )."/modules/calendar/links_events.php" );
getEventLinks( $first_time, $last_time, $links, 50, $delegator_id );

// setup the title block
$titleBlock = new CTitleBlock( 'Week View', 'calendar.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&a=month_view&delegator_id=$delegator_id&dialog=$dialog&date=".$this_week->format( FMT_TIMESTAMP_DATE ), "month view" );
if ( $canAdd )
{
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
		'<form action="?m=calendar&a=addedit&dialog='.$dialog.'&delegator_id='.$delegator_id.'" method="post">', '</form>'
	);
}
$titleBlock->show();
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

<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
<tr>
	<td>
		<a href="?m=calendar&a=week_view&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&date=<?=$prev_week->format( FMT_TIMESTAMP_DATE )?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
	</td>
	<th width="100%">
		<span style="font-size:12pt"><?php echo $AppUI->_( 'Week' ).' '.$this_week->format( "%U - %Y" ); ?></span>
	</th>
	<td>
		<a href="?m=calendar&a=week_view&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&date=<?=$next_week->format( FMT_TIMESTAMP_DATE) ?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></A>
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
	$s .= '<td><a href="'.$href.'"><?php echo $day1 ?>';

	$s .= $dayStamp == $today ? '<span style="color:red">' : '';
	$s .= $show_day->format( $format[$column] );
	$s .= $dayStamp == $today ? '</span>' : '';
	$s .= '</a></td></tr>';

	$s .= '<tr><td>';

	if (isset( $links[$dayStamp] )) {
		foreach ($links[$dayStamp] as $e) {
			$href = isset($e['href']) ? $e['href'] : null;
			$alt = isset($e['alt']) ? $e['alt'] : null;

			$s .= "<br />";
			$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
			$s .= "{$e['text']}";
			$s .= $href ? '</a>' : '';
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
<tr>
	<td colspan="2" bgcolor="#e9e9e9" align="right">		
		<form name="frm">
			<input type="hidden" name="m" value="calendar">	
			<input type="hidden" name="date" value="<?=$this_week->format( FMT_TIMESTAMP_DATE )?>">
			<input type="hidden" name="a" value="week_view">
			<input type="text" name="a_date" disabled="disabled" class="text" value="<?=$this_week->format($df)?>">
			<input type="hidden" name="delegator_id" value="<?=$delegator_id?>">
			<input type="hidden" name="dialog" value="<?=$dialog?>">
			<a href="#" onClick="popCalendar('date')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
			</a>			
			<input type="submit" class="button" value = "<?=strtolower($AppUI->_("Go to"))?>">
			&nbsp;&nbsp;
			<a href="./index.php?m=calendar&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>&a=week_view"><?php echo $AppUI->_('today');?></A>
		</form>		
	</td>
</tr>
</table>
