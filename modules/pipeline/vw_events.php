<?
GLOBAL $AppUI, $lead, $lead_id, $canEdit, $delegator_id, $dialog;
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
<?
$events = new CEvent();
$arrEvents = $events->getEventsByPipeline($lead_id);

if ($canEdit) 
{
?>
	<tr>
		<td align="right" valign="middle" style="height:30px;" colspan="6">
			<input type="button" class=button value="<?=$AppUI->_( 'new event' )?>" onClick="javascript:window.location='./index.php?m=calendar&a=addedit&lead_id=<?=$lead->id?>&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>';">
		</td>
	</tr>
<?
}

if ( count( $arrEvents ) )
{
	?>
	
	<tr class="tableHeaderGral">
		<? 
		if ( $canEdit ) 
		{
		?> 
			<th>&nbsp;</th>
		<?
		}
		?>
		<th valign="top" align="left"><?=$AppUI->_( 'Date' )?></th>
		<th valign="top" align="left"><?=$AppUI->_( 'Kind of event' )?></th>
		<th valign="top" align="left"><?=$AppUI->_( 'Title' )?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
	</tr>
	<?

	$types = dPgetSysVal( 'EventType' );

	foreach( $arrEvents as $e )
	{			
		?>
	<tr class="">
		<td colspan="4"></td>
	</tr>
	<tr>
		<?
		if ( $canEdit )
		{
		?>
		<td>
			<?php  if(!getDenyEdit("timexp")) { ?>
				<a href='javascript:report_hours(<?=$e->event_id?>, -2);' >
				<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
			<?php } ?>
			<a href="./index.php?m=calendar&a=view&event_id=<?=$e->event_id?>&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>">
			<img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_('Edit Event')?>" border="0" width="20" height="20"></a>
			<a href="javascript:delEvent('<?=$e->event_id?>')"><img src="images/icons/trash_small.gif"  border="0" alt="<?=$AppUI->_('delete')?>"></a>
		</td>
		<?
		}
		?>
		<td>
		<?
			$d = new CDate( $contact->date );
			echo($d->format( $df ));
		?>
		</td>
		<td><?=$AppUI->_($types[$e->event_type])?></td>
		<td><?=$e->event_title?></td>
	</tr>
	<tr class="tableRowLineCell">
		<td colspan="4"></td>
	</tr>
		<?
	}
} 
else 
{
	?>		
	<tr>
		<td colspan="4"><?=$AppUI->_('No data available')?></td>
	</tr>
	<?
}
?>
	<tr>
		<td nowrap="nowrap" colspan="4" rowspan="99" align="right" valign="top" style="background-color:#ffffff"></td>
	</tr>
</table>