<?php /* CALENDAR $Id: inv_addedit.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$AppUI->saveplace();
$event_id = intval(dPgetParam( $_GET, "event_id", "" ));
$orderby = dPgetParam( $_GET, "orderby", "user_last_name" );
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );

// get the passed timestamp (today if none)
// load the record data
$obj = new CEvent();

if (!$obj->load( $event_id ) && $event_id) {
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// check permissions
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 4;

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
	$canEdit = $canEdit || ( $permiso == "AUTHOR" && $obj->event_creator == $AppUI->user_id );
	$canEdit = $canEdit || ( $permiso == "EDITOR" );
	$canEdit = $canEdit || $AppUI->user_type == 1;
}
if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

$start_date = $obj->event_start_date ? new CDate( $obj->event_start_date ) : null;

// setup the title block
$titulo = $AppUI->_("Edit invitations to")." ".$obj->event_title;
$titleBlock = new CTitleBlock( $titulo, 'calendar.gif', $m, "$m.$a" );

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
	$titleBlock->addCrumb( "?m=calendar&a=addedit&delegator_id=$delegator_id&dialog=$dialog&event_id=$event_id", "edit this event" );
}

$titleBlock->show();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

require_once( $AppUI->getModuleClass( 'projects' ) );
?>
<script language="javascript">
function delMe( x ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Invitation');?>" + "?" )) {
		document.frmDelete.invitation_id.value = x;
		document.frmDelete.submit();
	}
}

</script>
<form name="frmDelete" action="?m=calendar&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_event_inv_del" />
	<input type="hidden" name="event_id" value="<?php echo $event_id;?>" />
	<input type="hidden" name="invitation_id" value ="" />
</form>
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<td width="60" align="right">
		&nbsp; <?php echo $AppUI->_('sort by');?>:&nbsp;
	</td>
	<td width="150" class="tableHeaderText">
		<a href="?m=calendar&a=inv_addedit&orderby=user_last_name&event_id=<?=$event_id?>&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" ><?php echo $AppUI->_('User name');?></a>
	</td>
	<td class="tableHeaderText">
		<a href="?m=calendar&a=inv_addedit&orderby=invitation_mail&event_id=<?=$event_id?>&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" ><?php echo $AppUI->_('E-mail');?></a>
	</td>
	<td class="tableHeaderText">
		<a href="?m=calendar&a=inv_addedit&orderby=invitation_status&event_id=<?=$event_id?>&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" ><?php echo $AppUI->_('Status');?></a>
	</td>
	<td class="tableHeaderText">
		<a href="?m=calendar&a=inv_addedit&orderby=invitation_status&event_id=<?=$event_id?>&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" ><?php echo $AppUI->_('Sent');?></a>
	</td>	
</tr>
<?php 
//$debugsql = 1;
$invited = $obj->getInvitations($orderby);

//print_r( $invited );
foreach ($invited as $row) 
{

	if($row["invitation_sent"] == 0)
		$invitedPendings++;
?>
<tr>
	<td align="right" nowrap="nowrap" width=70 >
<?php if ($canEdit) { ?>
		<a href="javascript:delMe(<?php echo $row["invitation_id"];?>)" title="<?php echo $AppUI->_('delete');?>">
			<?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' ); ?>
		</a>			
<?php } ?>
	</td>
	<td>		
		<?php
        if( $row["user_id"] ){
            echo $row["user_last_name"].', '.$row["user_first_name"];
        }elseif($row["contact_id"] ){
            echo $row["contact_last_name"].', '.$row["contact_first_name"];
        }else{
            echo "-";
        }
        ?>
	</td>
	<td>
		<?php
            if($row["user_email"] != ""){
                echo $row["user_email"];
            }elseif($row["contact_email"]){
                echo $row["contact_email"];
            }elseif ( $row["invitation_mail"] != "" ){
                echo $row["invitation_mail"];
            }else{
                echo "-";
            }
        ?>
	</td>
	<td>		
		<?
		switch( $row["invitation_status"] )
		{
			case "ACCEPTED":
				$color = "green";
				break;
			case "REJECTED":
				$color = "red";
				break;
		}
		echo "<font color=\"$color\">".$AppUI->_($row["invitation_status"])."</font>";
		?>
	</td>
	<td>		
		<?
		if( $row["invitation_sent"] == "0")
			echo "<font color=\"red\">".$AppUI->_("No")."</font>";
		else
			echo "<font color=\"blue\">".$AppUI->_("Yes")."</font>";
		?>
	</td>	
</tr>
<tr class="tableRowLineCell"><td colspan="5"></td></tr>
<?php }?>
</table>
<form action="?m=calendar&a=add_inv&event_id=<?php echo $event_id;?>&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
<input type="hidden" name="hdnCheckListado" value="1">
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<tr>
	<td align="right" colspan="2">
		<? if ( count($invitedPendings) ){?>
			<input type="button" value="<?php echo $AppUI->_( 'send pending invitations' );?>" class="button" onclick="window.location.href='?m=calendar&a=do_inv_sent&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>&event_id=<?=$event_id?>';">
		<? } ?>	
		<input type="submit" value="<?php echo $AppUI->_( 'edit invitations' );?>" class="button">
	</td>
</tr>
</table>