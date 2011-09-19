<?php /* $Id: import_export.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
//$AppUI->savePlace();
include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6; 
//$dialog = dPgetParam( $_GET, "dialog", $user_id != $AppUI->user_id );

$canAdd = $canEdit;

if ( $delegator_id != $AppUI->user_id )
{	
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canAdd = $permisos == "AUTHOR" || $permisos == "EDITOR" || $AppUI->user_type == 1;
	do_log($delegator_id, $mod_id, $AppUI, 2);
	
}

if ( !$canAdd )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

// setup the title block
$titleBlock = new CTitleBlock( 'Contacts import and export', 'contacts.gif', $m, "colaboration.index" );
$titleBlock->addCrumb( "?m=contacts&delegator_id=$delegator_id&dialog=$dialog", "contacts list" );
$titleBlock->show();

$export_formats = array( 
"HTML"=>"HTML",
"CSV"=>"CSV",
"XLS"=>"XLS",
"Outlook"=>"Outlook",
);

$import_formats = array(
"Outlook"=>"Outlook",
"Outlook Express"=>"Outlook Express",
"vCard"=>"vCard",
//"Palm Desktop"=>"Palm Desktop",
//"Netscape"=>"Netscape",
"Yahoo"=>"Yahoo",
);
?>
<script language="javascript">
function doImport()
{
	var form = document.importFrm;
	
	if ( !checkFile( form.file.value ) )
	{
		alert( "<?php echo $AppUI->_("That is not a valid filename")?>" );
		form.file.focus();
		return false;
	}
	form.submit();
}

function checkFile( f )
{
	return f != "";
}

function exportTo( fmt )
{
	var form = document.exportFrm;
	
	form.format.value = fmt;
	form.submit();
}
</script>
<?php 
$titleBlock->showSection2("Import");
?>
<table width="100%" border="0" cellpadding="1" cellspacing="1"  class="contacts">	
	<tr>
		<td>
		<?php /*
			<form action="?m=contacts&delegator_id=<?php echo $delegator_id?>&dialog=<?php echo $dialog?>" method="post" name="importFrm" enctype="multipart/form-data">*/ ?>
		<form action="" method="post" name="importFrm" enctype="multipart/form-data">
			<input type="hidden" name="max_file_size" value="109605000" />
			<input type="hidden" name="dosql" value="do_contacts_import" />			
			<table>
				<tr>
					<td>
						<table>
							<tr>
								<td>1.</td>
								<td><?php echo $AppUI->_("Select a program from which to import your contacts")?></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><?php echo arraySelect( $import_formats, "format", 'class="text"', '', true,'','280px' );?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table>
							<tr>
								<td>2.</td>
								<td><?php echo $AppUI->_("Access that program and export them")?></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><?php echo $AppUI->_("Consult the helps on the right for more information");?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table>
							<tr>
								<td>3.</td>
								<td><?php echo $AppUI->_("Specify the file to import")?></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><input type="file" name="file" class="text"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table><? /*
							<tr>
								<td>4.</td>
								<td><?php echo $AppUI->_("Select your preference")?></td>
							</tr>	*/ ?>
							<tr>
								<td>4.</td>
								<td><?php 
								$duplicates = array("1"=>"Update", "2"=>"Skip");
								echo arraySelect( $duplicates, "duplicates", 'class="text"', '', true );?> <?php echo $AppUI->_("Duplicated contacts")?></td>
							</tr>
						</table>
					</td>
				</tr>				
				<tr>
					<td>
						<table>
							<tr>
								<td>5.</td>
								<td><?php echo $AppUI->_("Click 'import now' in order to import the file")?></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><input type="button" value="<?php echo $AppUI->_("import now")?>" class="button" onclick="doImport()"></td>
							</tr>
						</table>
					</td>
				</tr>	
			</table>
			</form>
		</td>
		<td>
			<p><?php echo $AppUI->_("Help importing from")?>:</p>
			<ul>
				<li><a href="?m=contacts&a=help_import_outlook&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('Outlook')?></a></li>
				<li><a href="?m=contacts&a=help_import_outlookexpress&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('Outlook Express')?></a></li>
				<li><a href="?m=contacts&a=help_import_palmdesktop&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('Palm Desktop')?></a></li>
<? /*				<li><a href="?m=contacts&a=help_import_netscape&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('Netscape')?></a></li> */ ?>
				<li><a href="?m=contacts&a=help_import_yahoo&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('Yahoo')?></a></li>				
			</ul>
			<?php /*
			<strong><?php echo $AppUI->_("Warning")?>:</strong><?php echo $AppUI->_("Importing data may result in duplication of other contacts allready in your address book")?>. <!--Quiz?valga la pena borrar todos los contactos de tu libreta.-->
			*/ ?>
		</td>
	</tr>
</table>

<?php 
$titleBlock->showSection2("Export");
?>
<table width="100%" border="0" cellpadding="1" cellspacing="1"  class="contacts">
	<tr>
		<td>
			<form name="exportFrm" action="?m=contacts&delegator_id=<?php echo $delegator_id?>&dialog=<?php echo $dialog?>" method="post">
			<input type="hidden" name="dosql" value="do_contacts_export" />
			<input type="hidden" name="format" value="" />
			<table>
				<tr>
					<td colspan="3"><?php echo $AppUI->_("Select the program which you want to export to and click on the corresponding button")?></td>							
				</tr>
				<?php
				foreach ( $export_formats as $k=>$v )
				{
					?>
				<tr>
					<td align="right">
						<?php echo $AppUI->_($v)?>						
					</td>
					<td align="left">
						<input type="button" class="button" value="<?php echo $AppUI->_("export now")?>" onclick="exportTo( '<?php echo $k?>' );">
					</td>					
				</tr>
					<?php
				}
				?>
			</table>
			</form>
		</td>
		<!--<td>
			<p><?php echo $AppUI->_("Help exporting to")?>:</p>
			<ul>
				<li><a href="?m=contacts&a=help_export_html&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('HTML')?></a></li>
				<li><a href="?m=contacts&a=help_export_csv&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('CSV')?></a></li>
				<li><a href="?m=contacts&a=help_export_xls&dialog=<?php echo $dialog;?>&delegator_id=<?php echo $delegator_id;?>"><?php echo $AppUI->_('XLS')?></a></li>
			</ul>
		</td>-->
	</tr>
</table>