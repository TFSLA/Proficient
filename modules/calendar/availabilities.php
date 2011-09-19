<?
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
$users = dpGetParam( $_GET, "users", "" );
$date = dpGetParam( $_GET, "date", "" );

$this_day = new CDate( $date );

$usuarios = $users = explode(";", $users);

$start = $AppUI->getConfig('cal_day_start');
$end = $AppUI->getConfig('cal_day_end');
$inc = $AppUI->getConfig('cal_day_increment');

if ($start == null ) $start = 8;
if ($end == null ) $end = 17;
if ($inc == null) $inc = 30;

$tf = $AppUI->getPref('TIMEFORMAT');

require_once( $AppUI->getModuleClass( "admin" ) );
require_once( $AppUI->getModuleClass( "projects" ) );

?>
<table cellspacing="1" cellpadding="2" width="100%" border="1">
	<tr>
		<th><?=$AppUI->_("User")?></th>
<?
$this_day->setTime( $start, 0, 0 );
for ($i=0, $n=($end-$start)*60/$inc; $i < $n; $i++) 
{
	$tm = $this_day->format( $tf );
	?>
		<th width="1%" align="right" nowrap="nowrap"><?= $this_day->getMinute() ? $tm : "<b>$tm</b>"?></th>
	<?
	$this_day->addSeconds( 60*$inc );
}
?>
	</tr>
<?
//echo $this_day->format( "%H%M%S" );
foreach ( $usuarios as $u )
{
	$usr = new CUser();
	$usr->load( $u );
	$start_d = new CDate( $date );
	$start_d->setTime( 0, 0, 0 );	
	$end_d = new CDate( $date );
	$end_d->setTime( 23, 59, 59 );
	
	$events = CEvent::getEventsForPeriod( $start_d, $end_d, $u );	
	$events2 = array();
	foreach ($events as $row) 
	{	
		$ev = new CEvent;
		$ev->load($row["event_id"]);	
		if ( !$ev->event_recurse_type )
		{
			$start_d = new CDate( $ev->event_start_date );
			$events2[$start_d->format( "%H%M%S" )] = $ev;		
		}
		else
		{
			$start_s = str_replace( ":", "", $ev->event_start_time);
			$events2[$start_s] = $ev;						
		}		
	}
	//print_r($events2);	
	?>		
	<tr>
		<td width="1%" align="right" nowrap="nowrap"><?=$usr->user_last_name.", ".$usr->user_first_name?></td>
	<?	
	$this_day->setTime( $start, 0, 0 );	
	for ($i=0, $n=($end-$start)*60/$inc; $i < $n; $i++) 
	{
		$timeStamp = $this_day->format( "%H%M%S" );
		$cols = 1;
		if( $events2[$timeStamp] ) 
		{			
			$ev = $events2[$timeStamp];

			if ( !$ev->event_recurse_type )
			{
				//Evento puntual			
				$et = new CDate( $ev->event_end_date );
				$cols = (($et->getHour()*60 + $et->getMinute()) - ($this_day->getHour()*60 + $this_day->getMinute()))/$inc;
			}
			else
			{
				$et = $ev->event_end_time;
				$cols = ((substr($et, 0, 2)*60 + substr($et, 3, 2)) - ($this_day->getHour()*60 + $this_day->getMinute()))/$inc;  
			}	
			?>
		<td width="1%" align="right" colspan="<?=$cols?>" nowrap="nowrap" bgcolor="lightblue">
			&nbsp;
		</td>		
			<?			
		}
		else
		{
			?>
		<td width="1%" align="right" nowrap="nowrap">&nbsp;</td>
			<?			
		}	
		$this_day->addSeconds( 60*$inc*$cols );		
		$i += $cols - 1;
	}	
	?>		
	</tr>
	<?
}
?>	
</table>