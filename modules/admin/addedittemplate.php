<?php /* DEPARTMENTS $Id: addedittemplate.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$id = $securitytemplate_id;

$sql = "
SELECT *
FROM securitytemplates 
WHERE securitytemplate_id = $id
";
if ($id > 0 && !db_loadHash( $sql, $drow ) ) {
	$titleBlock = new CTitleBlock( 'Invalid Security Template  ID', 'user_management.gif', $m, 'ID_HELP_SECURITYTEMPLATE_EDIT' );
	$titleBlock->addCrumb( "?m=admin", "Security Templates" );
	$titleBlock->show();
} else {


// setup the title block
	$ttl = $id > 0 ? "Edit Template" : "Add Template";
	$titleBlock = new CTitleBlock( $ttl, 'user_management.gif', $m, 'ID_HELP_SECURITY_TEMPLATE_EDIT' );
	if(!getDenyRead('users')){
		$titleBlock->addCrumb( "?m=admin&tab=0", "users list" );
		$titleBlock->addCrumb( "?m=admin", "template list" );
	}
	if ($id>0){
		$titleBlock->addCrumb( "?m=admin&a=viewtemplate&securitytemplate_id=$id", "view this template");
		if($canEdit)
			$titleBlock->addCrumb( "?m=admin&a=addedittemplate", "add template");		
	}
			
	$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
	if (trim(form.securitytemplate_name.value).length < 1) {
		alert( "<?=$AppUI->_('Please enter the Template Name')?>" );
		form.securitytemplate_name .focus();
	} else {
		form.submit();
	}
}
</script>
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=admin" method="post" enctype="multipart/form-data">
	<input type="hidden" name="dosql" value="do_template_aed" />
	<input type="hidden" name="securitytemplate_id" value="<?php echo $securitytemplate_id;?>" />


<tr>
	<td align="right"><?php echo $AppUI->_( 'Template Name' );?>:</td>
	<td><input type="text" class="text" name="securitytemplate_name" value="<?php echo @$drow["securitytemplate_name"];?>" maxlength="20" size="20></td>
	<td valign="top" align="center">

        </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Template Description' );?>:</td>
	<td><textarea rows=4 cols=70 name="securitytemplate_description"><?php echo @$drow["securitytemplate_description"];?></textarea></td>
	<td valign="top" align="center">

        </td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td colspan="2" align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
<?php } ?>
