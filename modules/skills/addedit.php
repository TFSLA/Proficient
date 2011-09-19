<?php /* DEPARTMENTS $Id: addedit.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
// Add / Edit Company
$id = isset($_GET['id']) ? $_GET['id'] : 0;


// pull data for this department

$sql = "SELECT id, name FROM skillcategories ORDER BY sort";
$skillcategories = array( '0' => '' ) + db_loadHashList( $sql );
echo db_error();

$sql = "
SELECT *
FROM skills
WHERE id = $id
";
if (!db_loadHash( $sql, $drow ) && $id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Skill ID', 'hhrr.gif', $m, 'ID_HELP_HHRR_EDIT' );
	$titleBlock->addCrumb( "?m=hhrr", "Human Resources" );
	$titleBlock->show();
} else {


// setup the title block
	$ttl = $id > 0 ? "Edit Skill" : "Add Skill";
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
	if (form.description.value.length < 1) {
		alert( "Please enter a valid Skill description" );
		form.description.focus();
	} else {
		form.submit();
	}
}
</script>
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=skills" method="post">
	<input type="hidden" name="dosql" value="do_skill_aed" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />


<tr>
	<td align="right"><?php echo $AppUI->_( 'Skill Description' );?>:</td>
	<td><input type="text" class="text" name="description" value="<?php echo @$drow["description"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Category' );?>:</td>
	<td>
<?php
	echo arraySelect( $skillcategories, 'idskillcategory', 'size="1" class="text"', $drow["idskillcategory"] );
?>                                                    
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Value Description (prompt for)' );?>:</td>
	<td><input type="text" class="text" name="valuedesc" value="<?php echo @$drow["valuedesc"];?>" maxlength="15" size="15"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Options for this skill' );?>:</td>
	<td><input type="text" class="text" name="valueoptions" value="<?php echo @$drow["valueoptions"];?>" maxlength="48" size="48"> (comma separated)</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Ask for last use of this skill' );?>:</td>
	<td><select name="hidelastuse">
              <option <?php if($drow["hidelastuse"]=="N") echo "selected";?> value="N">Yes</option>
              <option <?php if($drow["hidelastuse"]=="Y") echo "selected";?> value="Y">No</option>
            </select>           
        </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Ask for months of exp. on this skill' );?>:</td>
	<td><select name="hidemonthsofexp">
              <option <?php if($drow["hidemonthsofexp"]=="N") echo "selected";?> value="N">Yes</option>
              <option <?php if($drow["hidemonthsofexp"]=="Y") echo "selected";?> value="Y">No</option>
            </select>           
        </td>
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