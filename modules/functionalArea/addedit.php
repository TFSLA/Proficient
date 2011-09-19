<?php
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;
// pull data for this area
$sql = "
SELECT hhrr_functional_area.*, company_name
FROM hhrr_functional_area
LEFT JOIN companies ON company_id = $company_id
WHERE id = $id
";
if (!db_loadHash( $sql, $drow )&&$id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Area ID', 'users.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	if ($company_id) {
		$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	}
	$titleBlock->show();
} else {
	##echo $sql.db_error();##
	$company_id = $id ? $drow['area_company'] : $company_id;

	// check if valid company
	$sql = "SELECT company_name FROM companies WHERE company_id = $company_id";
	$company_name = db_loadResult( $sql );
	if (!$id && $company_name === null) {
		$AppUI->setMsg( 'badCompany', UI_MSG_ERROR );
		$AppUI->redirect();
	}

	// collect all the departments in the company
	//$areas = array( 0 => '' );
	if ($company_id) {
		$sql = "SELECT id,area_name,area_parent FROM hhrr_functional_area WHERE area_company=$company_id AND area_parent = 0 AND id != $id";
		//$areas = arrayMerge( array( '0'=>array( 0, $AppUI->_( '- Select Unit -' ), -1 ) ), db_loadHashList( $sql, 'id' ));
		$areas = arrayMerge( array( '0'=> $AppUI->_( '- Select Unit -' )), db_loadHashList( $sql ));
		//$areas = db_loadHashList( $sql, 'id' );
/*		echo "<pre>";
		print_r($areas);
		echo "</pre>";*/
	##echo $sql.db_error();##
	}

// setup the title block
	$ttl = $id > 0 ? "Edit Functional Area" : "Add Functional Area";
	$titleBlock = new CTitleBlock( $ttl, 'users.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
	if (trim(form.area_name.value).length < 2) {
		alert( '<?php echo $AppUI->_( 'deptValidName' );?>' );
		form.area_name.focus();
	} else {
		form.submit();
	}
}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=functionalArea" method="post">
	<input type="hidden" name="dosql" value="do_area_aed" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />
	<input type="hidden" name="area_company" value="<?php echo $company_id;?>" />

<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Functional Area Company' );?>:</td>
	<td><strong><?php echo $company_name;?></strong></td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Functional Area Name' );?>:</td>
	<td>
		<input type="text" class="text" name="area_name" value="<?php echo @$drow["area_name"];?>" size="50" maxlength="255" />
		<span class="smallNorm">(<?php echo $AppUI->_( 'required' );?>)</span>
	</td>
</tr>
<tr>


<?php
if (count( $areas )) {
?>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Functional Area Parent' );?>:</td>
	<td>
<?php
	//echo arraySelectTree( $areas, 'area_parent', 'class=text size=1', @$drow["area_parent"] );
	echo arraySelect( $areas, 'area_parent', 'class=text size=1', @$drow["area_parent"] );
?>
	</td>
</tr>
<?php } else {
	echo '<input type="hidden" name="area_parent" value="0">';
}
?>

<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
<?php } ?>
