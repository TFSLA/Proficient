<?php /* CONTACTS $Id: help_import_outlookexpress.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
//$dialog = dPgetParam( $_GET, "dialog", $user_ud != $AppUI->user_id );

// load the record data
$msg = '';
$row = new CContact();
$canDelete = $row->canDelete( $msg, $contact_id );

if ( !$row->load( $contact_id ) && $contact_id > 0)  
{
	$AppUI->setMsg( 'Contact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} 

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{		
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	if ( $contact_id )
	{
		$canEdit = $permisos == "AUTHOR" && $row->contact_creator == $AppUI->user_id && $row->contact_owner == $delegator_id;
		$canEdit = $canEdit || ($permisos == "EDITOR" && $row->contact_owner == $delegator_id);
		$canEdit = $canEdit || $AppUI->user_type == 1;
	}
	else
	{
		$canEdit = 1;
	}
}
else
{
	if (!$canRead) 
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}	

	// check permissions for this record
	$canEdit = !getDenyEdit( $m, $contact_id );
}
$titleBlock = new CTitleBlock( "Import contacts from Outlook Express", 'contacts.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=contacts&dialog=$dialog&delegator_id=$delegator_id&a=import_export", "import / export" );
$titleBlock->show();
?>
<table>
	<tr>
		<td colspan="2">
			<?php echo $AppUI->_("In order to transfer contact information from Microsoft Outlook Express to PSA you have to perform the following operations").":";?>
		</td>		
	</tr>
	<tr>
		<td>1.</td>
		<td><?php echo $AppUI->_("Open Microsoft Outlook Express")?>.</td>
	</tr>
	<tr>
		<td>2.</td>
		<td><?php echo $AppUI->_('Select "Export" from the "File" menu')?>.</td>
	</tr>
	<tr>
		<td>3.</td>
		<td><?php echo $AppUI->_('On the "Export" menu, select "Address book"')?>.</td>
	</tr>
	<tr>
		<td>4.</td>
		<td><?php echo $AppUI->_('Select the file type "Text file (Comma separated values)"')?>.</td>
	</tr>
	<tr>
		<td>5.</td>
		<td><?php echo $AppUI->_('Click on the "Export" button')?>.</td>
	</tr>
	<tr>
		<td>6.</td>
		<td><?php echo $AppUI->_('Write the name and path of the file, with extension .CSV')?>.</td>
	</tr>
	<tr>
		<td>7.</td>
		<td><?php echo $AppUI->_('Click on the "Next" button')?>.</td>
	</tr>
	<tr>
		<td>8.</td>
		<td><?php echo $AppUI->_('Select the fields you wish to export. It\'s recommended to activate all the checkboxes')?>.</td>
	</tr>
	<tr>
		<td>9.</td>
		<td><?php echo $AppUI->_('Click "Finish" in order to export the data to the .CSV file on the selected location')?>.</td>
	</tr>	
	<tr>
		<td colspan="2">
		<?php echo $AppUI->_("The .CSV file generated during the process described above can be used in PSA")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php echo $AppUI->_("Next, you have to import your .CSV file to your PSA contacts")?>:
		</td>
	</tr>
	<tr>
		<td>1.</td>
		<td><?php echo $AppUI->_('Open your PSA contacts')?>.</td>
	</tr>
	<tr>
		<td>2.</td>
		<td><?php echo $AppUI->_('Click on the "import / export" link at the bottom right')?>.</td>
	</tr>
	<tr>
		<td>3.</td>
		<td><?php echo $AppUI->_('On the import section, select "Microsoft Outlook Express (.CSV file)"')?>.</td>
	</tr>
	<tr>
		<td>4.</td>
		<td><?php echo $AppUI->_('Write the path and file name or press the browse button in order to look for it')?>.</td>
	</tr>
	<tr>
		<td>5.</td>
		<td><?php echo $AppUI->_('Select wich action should be take with duplicated contacts')?>.</td>
	</tr>	
	<tr>
		<td>6.</td>
		<td><?php echo $AppUI->_('Press the "import now" button')?>.</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php echo $AppUI->_("Bear in mind that PSA can only import data that Outlook Express includes into the .CSV file")?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="right">
			<input type="button" class="button" value="<?php echo $AppUI->_("back")?>" onclick="history.go(-1);"/>			
		</td>
	</tr>	
</table> 
