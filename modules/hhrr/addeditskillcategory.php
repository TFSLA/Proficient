<?php /* DEPARTMENTS $Id: addeditskillcategory.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// Add / Edit Company
$id = isset($_GET['id']) ? $_GET['id'] : 0;


// pull data for this department
$sql = "
SELECT *
FROM skillcategories
WHERE id = $id
";
if (!db_loadHash( $sql, $drow ) && $id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Skill Category ID', 'hhrr.gif', $m, 'hhrr.index' );
	$titleBlock->addCrumb( "?m=hhrr", "Human Resources" );
	$titleBlock->show();
} else {

// setup the title block
	$ttl = $id > 0 ? "Edit Skill Category" : "Add Skill Category";
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->addCrumb( "?m=hhrr&tab=3", strtolower($AppUI->_('Skill Category list')) );
	$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
	if (trim(form.name.value).length < 1) {
		alert( "<?php echo $AppUI->_('Please enter a valid Skill Category name')?>" );
		form.name.focus();
	} else {
		form.submit();
	}
}
</script>
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=hhrr" method="post">
	<input type="hidden" name="dosql" value="do_skillcategory_aed" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />


<tr>
	<td align="right"><?php echo $AppUI->_( 'Skill Category Name' );?>:</td>
	<td><input type="text" class="text" name="name" value="<?php echo @$drow["name"];?>" maxlength="48" size="48"></td>
</tr>

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
