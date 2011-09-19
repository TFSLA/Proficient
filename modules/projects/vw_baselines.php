<?php /* PROJECTS $Id: vw_baselines.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->saveplace();
$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );

// check permissions for this record
$canEdit = !getDenyEdit( $m, $project_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the record data
$row = new CProject();

if (!$row->load( $project_id, false ) && $project_id > 0) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($project_id > 0)
{
	$canEdit = $row->canEdit();
}

if (!$canEdit) 
{
	$AppUI->redirect( "m=public&a=access_denied" );
}
// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

// setup the title block
$titleBlock = new CTitleBlock( "Baselines", 'projects.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view project" );
$titleBlock->show();
?>
<script language="javascript">
function delMe( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Baseline');?> " + y + "?" )) {
		document.frmDelete.baseline_id.value = x;
		document.frmDelete.submit();
	}
}
</script>
<form name="frmDelete" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_baseline_aed" />
	<input type="hidden" name="baseline_id" value="0" />
	<input type="hidden" name="del" value="1" />
</form>

<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
	<tr class="tableHeaderGral">
		<td>&nbsp;</td>
		<th><?=$AppUI->_("Name")?></th>
		<th><?=$AppUI->_("Date")?></th>
	</tr>
<?
$baselines = $row->getBaselines();
//print_r($baselines);
$baseline = new CBaseline();
foreach( $baselines as $bid )
{
	$baseline->load( $bid["id"] );
	$fecha = new CDate($baseline->date);
	?>
	<tr>
		<td align="right" nowrap="nowrap" width="20">
			<table align=center width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<a href="javascript:delMe(<?php echo $baseline->id;?>, '<?php echo $baseline->name;?>')" title="<?php echo $AppUI->_('delete');?>">
							<?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' ); ?>
						</a>
					</td>
				</tr>
			</table>				
		</td>
		<td>
			<a href="?m=projects&a=addedit_baselines&baseline_id=<?=$baseline->id?>&project_id=<?=$project_id?>"><?=$baseline->name;?></a>
		</td>
		<td>
			<a href="?m=projects&a=addedit_baselines&baseline_id=<?=$baseline->id?>&project_id=<?=$project_id?>"><?=$fecha->format( $df );?></a>
		</td>
	</tr>
    <tr class="tableRowLineCell">
        <td colspan="3">
        </td>
    </tr>
	<?
}
?>
</table>
<table cellpadding="2" cellspacing="1" border="0" width="100%" class="std">
	<tr>
		<td align="left"><input type="button" value="<?=$AppUI->_("back")?>" class="button" onClick="location.href='?m=projects&a=addedit&project_id=<?=$project_id?>'"></td>
		<td align="right"><input type="button" value="<?=$AppUI->_("add")?>" class="button" onClick="location.href='?m=projects&a=addedit_baselines&project_id=<?=$project_id?>'"></td>
	</tr>
</table>

