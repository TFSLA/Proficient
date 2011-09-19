<?
GLOBAL $AppUI, $lead, $canEdit, $lead_id, $delegator_id, $dialog;
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
	<?
	if ($canEdit)
	{
		?>
			<tr>
				<td align="right" valign="middle" style="height:30px;" colspan="6">
					<input type="button" class=button value="<?=$AppUI->_( 'newContact' )?>" onClick="javascript:window.location='./index.php?m=contacts&a=addedit&lead_id=<?=$lead_id?>&delegator_id=<?=$delegator_id?>&hideTabs=1&dialog=<?=$dialog?>';">
				</td>
			</tr>
		<?
	}

	$contacts = $lead->getContactsByPipeline();
	if (count( $contacts ) )
	{
		?>
        <tr class="tableHeaderGral">
		<th>&nbsp;</th>
		<th valign="top" align="left"><?=$AppUI->_( 'Last Name' )?></th>
		<th valign="top" align="left"><?=$AppUI->_( 'First Name' )?></th>
		<th valign="top" align="left"><?=$AppUI->_( 'Company' )?></th>
		<th valign="top" align="left"><?=$AppUI->_( 'Email' )?></th>
		<th valign="top" align="left"><?=$AppUI->_( 'Phone' )?></th>
		</tr>
		<?
		require_once("./modules/contacts/contacts.class.php");
		
		while( LIST($id,$desc) = EACH ($contacts) )
		{
			$contact = new CContact();
			$contact->load( $id );
			$contactData = $AppUI->_("Last Name").": ".$contact->contact_last_name."<br>";
	        $contactData .= $AppUI->_("First Name").": ".$contact->contact_first_name."<br>";
	        $contactData .= $AppUI->_("Company").": ".$contact->contact_company."<br>";
	        $contactData .= "Email: ".$contact->contact_email."<br>";
	        $contactData .= $AppUI->_("Phone").": ".$contact->contact_phone."<br>";
	        $contactData .= $AppUI->_("Mobile Phone").": ".$contact->contact_mobile."<br>";
	        
			$eventsJS = " onmouseover=\"tooltipLink('<pre>$contactData</pre>', 'Contacto');\" onmouseout=\"tooltipClose();\" ";
			?>
        <tr class="">
            <td colspan="4"></td>
        </tr>
        <tr>
			<td>
				&nbsp;
			</td>
			<td>
				<a href="index.php?m=contacts&a=viewcontact&tab=0&contact_id=<?=$id?>" <?=$eventsJS?> >
				<?php echo $contact->contact_last_name; ?>
				</a>
			</td>
			<td valign="top">
				<a href="index.php?m=contacts&a=viewcontact&tab=0&contact_id=<?=$id?>" <?=$eventsJS?> >
				<?php echo $contact->contact_first_name; ?>
				</a>
			</td>
			<td valign="top">
				<a href="index.php?m=contacts&a=viewcontact&tab=0&contact_id=<?=$id?>" <?=$eventsJS?> >
				<?php echo $contact->contact_company; ?>
				</a>
			</td>
			<td valign="top">
				<?php echo $contact->contact_email; ?>
			</td>
			<td valign="top" align="left">
				<?php echo $contact->contact_business_phone; ?>
			</td>
		</tr>
        <tr class="tableRowLineCell">
            <td colspan="6"></td>
        </tr>
		<?
		}
	}
	else
	{
		?>
		<tr>
			<td><?=$AppUI->_('No data available')?></td>
		</tr>
		<?
	}
	?>
	<tr>
		<td nowrap="nowrap" colspan="6" rowspan="99" align="right" valign="top" style="background-color:#ffffff"></td>
	</tr>
</table>