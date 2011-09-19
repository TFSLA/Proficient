<?php
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'AreaVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'AreaVwTab' ) !== NULL ? $AppUI->getState( 'AreaVwTab' ) : 0;

// pull data
$sql = "
SELECT hhrr_functional_area.*,company_name
FROM hhrr_functional_area, companies
WHERE id = $id
	AND area_company = company_id
";
if (!db_loadHash( $sql, $dept )) {
	$titleBlock = new CTitleBlock( 'Invalid Functional Area ID', 'users.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->show();
} else {
	$company_id = $dept['area_company'];

	// setup the title block
	$titleBlock = new CTitleBlock( $AppUI->_("View Functional Area"), 'users.gif', $m, "$m.$a" );
	
	if ($canEdit) {
		$titleBlock->addCell();
		$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('new functional area').'">', '',
			'<form action="?m=functionalArea&a=addedit&company_id='.$company_id.'&area_parent='.$id.'" method="post">', '</form>'
		);
	}
	$titleBlock->addCrumb( "?m=companies", "company list" );
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	if ($canEdit) {
		$titleBlock->addCrumb( "?m=functionalArea&a=addedit&id=$id", "edit this functional area" );

		if ($canDelete) {
			$titleBlock->addCrumbRight(
				'<a href="javascript:delIt()">'
					. '<img align="absmiddle" src="' . dPfindImage( 'trash_small.gif', $m ) . '" alt="" border="0" />&nbsp;'
					. $AppUI->_('delete functional area') . '</a>'
			);
		}
	}
	$titleBlock->show();
?>

<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('delArea');?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<form name="frmDelete" action="./index.php?m=functionalArea" method="post">
	<input type="hidden" name="dosql" value="do_area_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />
</form>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr valign="top">
	<td width="50%">
		<strong><?php echo $AppUI->_("Details");?></strong>
		
		<!--<strong>Details</strong>!-->
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" width="40" nowrap><?php echo $AppUI->_("Functional Area Company");?>:</td>
			<td bgcolor="#ffffff" width="200" ><?php echo $dept["company_name"];?></td>
		</tr>
		<tr>
			<td align="right"  width="40" nowrap><?php echo $AppUI->_("Functional Area Name");?>:</td>
			<td bgcolor="#ffffff" width="200"><?php echo $dept["area_name"];?></td>
		</tr>
		<tr>
			<td align="right"  width="40" nowrap><?php echo $AppUI->_("Functional Area Parent");?>:</td>
			<?
				$sql = "SELECT area_name FROM hhrr_functional_area WHERE id= ".$dept["area_parent"];
				$area_parent = db_loadResult($sql);
			?>
			<td bgcolor="#ffffff" width="200"><?php echo $area_parent;?></td>
			<td width="500"> &nbsp; </td>
		</tr>
		</table>
	</td>
</tr>
</table>

</table>
<?php
}
?>
