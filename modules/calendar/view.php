<?php /* CALENDAR $Id: view.php,v 1.2 2009-05-21 16:30:47 ctobares Exp $ */
$event_id = intval( dPgetParam( $_GET, "event_id", 0 ) );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 4;
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CEvent();
$canDelete = $obj->canDelete( $msg, $event_id );

// load the record data
if (!$obj->load( $event_id )) {
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
else 
{
	$AppUI->savePlace();
}

if ( $delegator_id != $AppUI->user_id )
{
	//Es calendario ajeno
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
	$canEdit = ( $permiso == "AUTHOR" && $obj->event_creator == $AppUI->user_id && $obj->event_owner == $delegator_id );
	$canEdit = $canEdit || ( $permiso == "EDITOR" && $obj->event_owner == $delegator_id );
	$canEdit = $canEdit || $AppUI->user_type == 1;
}
else
{
	if ( $obj->event_owner != $AppUI->user_id && $AppUI->user_type != 1 )
	{	
		if(($obj->event_project > 0 && !(in_array($AppUI->user_id, array_keys(CProject::getOwners($obj->event_project))) 
			|| in_array($AppUI->user_id, array_keys(CProject::getUsers($obj->event_project)))
				|| in_array($obj->event_project, array_keys(CUser::getOwnedProjects($AppUI->user_id))) )))
		{
			$AppUI->redirect( "m=public&a=access_denied" );
		}
				
		if($obj->event_company > 0 && $obj->event_company != $AppUI->user_company)
		{
		$AppUI->redirect( "m=public&a=access_denied" );
		}
		
		if($obj->event_project <= 0 && $obj->event_company <= 0)
		{
			$AppUI->redirect( "m=public&a=access_denied" );
		}		
	}

	//Es el propio calendario
	// check permissions for this record
	$canEdit = !getDenyEdit( $m, $event_id );	
}

// load the event types
$types = dPgetSysVal( 'EventType' );

// load the event recurs types
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

$ordered = array (
	1=>"First",
	2=>"Second",
	3=>"Third",
	4=>"Fourth",
	5=>"Fifth"
	);
	
//echo "Mis permisos son de '$permiso'";

/*if ($obj->event_owner != $AppUI->user_id) {
	$canEdit = false;
}*/

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$start_date = $obj->event_start_date ? new CDate( $obj->event_start_date ) : null;
$end_date = $obj->event_end_date ? new CDate( $obj->event_end_date ) : null;

if($start_date!=null){
$date = $start_date->format( "%Y%m%d" );
}


// setup the title block
$titleBlock = new CTitleBlock( 'View Event', 'calendar.gif', $m, "$m.$a" );

if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
		'<form action="?m=calendar&a=addedit&delegator_id='.$delegator_id.'&dialog='.$dialog.'" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&dialog=$dialog&a=month_view&date=".$date, "month view" );
if ( $start_date )
{
	/*$first_week_day = $start_date;
	while ( $first_week_day->getDayOfWeek() != 1 )
	{
		$first_week_day->addDays(-1);
	}*/
	$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&date=".$date."&dialog=$dialog", "week view" );
}
if ($canEdit) {
	$titleBlock->addCrumb( "?m=calendar&a=day_view&dialog=$dialog&delegator_id=$delegator_id&date=".$date, "day view" );
	
	if ( $obj->event_owner == $AppUI->user_id || $AppUI->user_type == 1 )
	{	
	$titleBlock->addCrumb( "?m=calendar&a=addedit&delegator_id=$delegator_id&dialog=$dialog&event_id=$event_id", "edit this event" );
	$titleBlock->addCrumbDelete( 'delete event', $canDelete, $msg );
}
}

$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('eventDelete');?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=calendar&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="event_id" value="<?php echo $event_id;?>" />
	<input type="hidden" name="delegator_id" value="<?=$delegator_id?>" />
</form>

<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Event Title');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->event_title;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type');?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($types[$obj->event_type]);?></td>
		</tr>	
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Location');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->event_location;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('People invited');?>:</td>
<?
switch ( $obj->event_invitation_type )
{
	case "PERSONAL":
		?>
			<td class="hilite" width="100%"><?php echo $AppUI->_("Only me");?></td>
		<?
		break;
	case "PROJECT":
		?>
			<td class="hilite" width="100%">
				<?php echo $AppUI->_("All users on the project");?>
				<? 
				require_once( $AppUI->getModuleClass( 'projects' ) );
				$p = new CProject();
				$p->load( $obj->event_project );
				echo $p->project_name;
				?>				
			</td>
		<?
		break;
	case "COMPANY":
		?>
			<td class="hilite" width="100%">
				<?php echo $AppUI->_("All users on the company");?>
				<? 
				require_once( $AppUI->getModuleClass( 'companies' ) );
				$c = new CCompany();
				$c->load( $obj->event_company );
				echo $c->company_name;
				?>				
			</td>			
		<?
		break;
	case "PRIVATE":
		$invitations = $obj->getInvitations("invitation_id");
		$b = 0;
		foreach( $invitations as $inv )
		{
			switch ($inv["invitation_status"])
			{
				case "ACCEPTED":
					$color = "green";
					break;
				case "REJECTED":
					$color = "red";
					break;
				default:
					$color = "";
					break;
			}
			if ( $b )
			{
				?>
				<tr>
				<td align="right" nowrap="nowrap">&nbsp;</td>
				<?
			}
			?>			
			<td class="hilite" width="100%"><font color="<?=$color?>">
                <?//=$inv["user_last_name"] != "" ? $inv["user_last_name"].", ".$inv["user_first_name"]  : $inv["invitation_mail"]?>
                <?php
                    if( $inv["user_id"] != "" ){
                        echo $inv["user_last_name"].', '.$inv["user_first_name"];
                    }elseif($inv["contact_id"] != ""){
                        echo $inv["contact_last_name"].', '.$inv["contact_first_name"];
                    }elseif($inv["invitation_mail"] != ""){
                        echo $inv["invitation_mail"];
                    }
                    
                    if($color != "")
                    	echo("&nbsp;".$AppUI->_("(".$inv["invitation_status"].")"));
                    else
                    	echo("</font>");
                ?>
                </font>
            </td>
			</tr>
			<?
			$b = 1;
		}
		break;
}	
?>			
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Recurs');?>:</td>
			<td class="hilite"><?php echo $AppUI->_($recurs[$obj->event_recurse_type]);?></td>
		</tr>
<? 
switch ( $obj->event_recurse_type )
{
	case "":
	?>		
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Starts');?>:</td>
			<td class="hilite"><?php echo $start_date ? $start_date->format( "$df $tf" ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Ends');?>:</td>
			<td class="hilite"><?php echo $end_date ? $end_date->format( "$df $tf" ) : '-';?></td>
		</tr>
	<?
		break;
	case "d":
	?>
		<tr>
			<td align="right" nowrap="nowrap">&nbsp;</td>
			<?
			if ( $obj->event_recur_every_x_days > 0 )
			{
			?>
			<td class="hilite"><?php echo $AppUI->_( "Every" )." ".$obj->event_recur_every_x_days." ".$AppUI->_( "Day(s)" )?></td>
			<?
			}
			else
			{
			?>
			<td class="hilite"><?php echo $AppUI->_( "Every weekday" );?></td>
			<?
			}
			?>	
		</tr>
	<?
		break;
		case "w":
	?>
		<tr>
			<td align="right" nowrap="nowrap"><?= $AppUI->_("Every")?></td>
			<td class="hilite"><?=$obj->event_recur_every_x_weeks?> <?=$AppUI->_( "Week(s) on:" );?> </td>
		</tr>
		<tr>
			<td nowrap="nowrap">&nbsp;</td>
		<?
		$days = "";
		$band = 0;
		for ( $i = 0; $i < 7; $i++ )
		{
			if ( $obj->event_recur_every_n_days[$i] == "1" )
			{
				$days .= (!$band ? "" : ", ").$AppUI->_($week_days[$i]);
				$band = 1;				
			}			
		}
		?>
			<td class="hilite"><?=$days?></td>
		</tr>
		<?
		break;
	case "m":
	?>
		<tr>
			<td align="right" nowrap="nowrap">&nbsp;</td>
			<td class="hilite">
			<?
			if ( $obj->event_recur_every_dd_day > 0 )
			{
			?>					
				<?php echo $AppUI->_( "Day" ); echo " ".$obj->event_recur_every_dd_day." ";?>
			<?
			}
			else
			{
			?>
				<?php echo $AppUI->_( "The" )." ".$AppUI->_($ordered[$obj->event_recur_every_nd_day])." ".$AppUI->_($week_days[$obj->event_recur_every_n_day])?>
			<?		
			}
			?>
			<?=$AppUI->_( "of every" );?> <?=$obj->event_recur_every_x_months?> <?=$AppUI->_("month(s)")?>
		</td>	
	</tr>
	<?
		break;
	case "y":
	?>
	<tr>
		<td align="right" nowraping="nowraping"><?=$AppUI->_("Every")?></td>
		<td class="hilite"><?=$AppUI->_($months[$obj->event_recur_every_mm_month])?></td>
	</tr>	
	<tr>
		<td align="right" nowrap="nowrap">&nbsp;</td>
		<td class="hilite">
		<?
		if ($obj->event_recur_every_dd_day > 0)
		{
		?>				
			<?=$AppUI->_("Day")?> <?=$obj->event_recur_every_dd_day?>
		<?
		}
		else
		{
		?>
			<?=$AppUI->_("The")?> <?=$AppUI->_($ordered[$obj->event_recur_every_nd_day])." ".$AppUI->_($week_days[$obj->event_recur_every_n_day]) ?>
		<?
		}
		?>
		</td>
	</tr>	
	<?
		break;
}
if ( $obj->event_recurse_type )
{
	$ev_st_time = new CDate( "0000-00-00 ".$obj->event_start_time);
	$st_time = $ev_st_time->format("%H%M%S");
	
	$ev_end_time = new CDate( "0000-00-00 ".$obj->event_end_time);
	$end_time = $ev_end_time->format("%H%M%S");

	$duration = intval(substr($end_time, 0, 2))*60 + intval(substr($end_time, 2, 2));		
	$duration -= intval(substr($st_time, 0, 2))*60 + intval(substr($st_time, 2, 2));
	$df = $AppUI->getPref('SHDATEFORMAT');
	$ev_st = new CDate($obj->event_start_date);	
	
	$durations = array();
	for ( $i = 30; $i < 1410; $i+= 30 )
	{
		$hs = intval($i / 60);
		$ms = $i % 60;
		$durations[$i] = ($hs != 0 ? $hs." ".( $hs > 1 ? $AppUI->_("hours") : $AppUI->_("hour") ) : "");
		$durations[$i] .= ($ms != 0 ? " ".$ms." ".$AppUI->_("mins") : "");
	}		
?>
	<tr>
		<td align="right" nowrap="nowrap"><?=$AppUI->_("Start")?></td>
		<td class="hilite"><?=$obj->event_start_time?></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><?=$AppUI->_("End")?></td>
		<td class="hilite"><?=$obj->event_end_time ?></td>
	</tr>
	<tr>	
		<td align="right" nowrap="nowrap"><?=$AppUI->_("Duration")?></td>
		<td class="hilite"><?=$durations[$duration]?></td>	
	</tr>
	<tr>
		<td align="right" nowrap="nowrap"><?=$AppUI->_("Start")?></td>
		<td class="hilite"><?=$ev_st->format($df)?>
	</tr>
	<tr>
		<?
		if ( $obj->event_no_occurrences == "-1" && $obj->event_end_occurrence != "0000-00-00" )
		{
		?>
		<td align="right" nowrap="nowrap"><?=$AppUI->_("End by")?></td>
		<? $end_date = new CDate( $obj->event_end_occurrence ); ?>
		<td class="hilite"><?php echo $end_date->format($df) ?></td>
		<?
		}
		else
		{
			if ( $obj->event_no_occurrences > 0 )
			{
		?>
		<td align="right" nowrap="nowrap"><?=$AppUI->_("End after")?></td>
		<td class="hilite"><?php echo $obj->event_no_occurrences." ".$AppUI->_("occurrences")?></td>
		<?		
			}
			else
			{
			?>
		<td align="right" nowrap="nowrap">&nbsp;</td>
		<td class="hilite"><?=$AppUI->_("No end date")?></td>
			<?		
			}
		}
		?>
	</tr>
<?
}
?>
		</table>
	</td>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Description');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $obj->event_description);?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
