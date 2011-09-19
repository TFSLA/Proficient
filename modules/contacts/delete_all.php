<?php /* CONTACTS $Id: delete_all.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */


// setup the title block
$titleBlock = new CTitleBlock( 'Delete all my contacts', 'contacts.jpg', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=contacts&user_id=$user_id&dialog=$dialog", "contacts list" );
$titleBlock->show();
?>

<form name="deleteall" action="" method="post">
	<input type="hidden" name="dosql" value="do_contact_deleteall" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="contact_creator" value="<?=$AppUI->user_id;?>" />
<table border="0" cellpadding="4" cellspacing="0" width="250" class="std" align="center">
<tr>
	<th valign="top" align="center" class="hilite">
		¿ Está seguro que desea eliminar todos sus contactos ?
	</th>
</tr>
<tr>
	<td valign="top" align="center">
		<input type="button" name="btncanc" class="button" value="&nbsp;&nbsp;&nbsp;No&nbsp;&nbsp;&nbsp;" onclick="javascript: location.href = './index.php?m=contacts';"/>
		<input type="submit" name="btnok" class="button" value="&nbsp;&nbsp;&nbsp;Si&nbsp;&nbsp;&nbsp;" />
	</td>
</tr>
</table>
</form>