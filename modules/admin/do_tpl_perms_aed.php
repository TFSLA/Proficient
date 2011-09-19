<?php /* ADMIN $Id: do_tpl_perms_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = new CTemplatePermission();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
$AppUI->setMsg( 'Template Permission' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
} else {
	$isNotNew = @$_POST['template_permission_id'];
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>