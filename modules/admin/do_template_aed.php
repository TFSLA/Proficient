<?php /* ADMIN $Id: do_template_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : 0;

$obj = new CTemplate();
//$securitytemplate_id=$id;
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Template' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
} else {
	$isNotNew = @$_REQUEST['securitytemplate_id'];
	if (!$isNotNew) {
		$obj->user_owner = $AppUI->id;
	}
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>