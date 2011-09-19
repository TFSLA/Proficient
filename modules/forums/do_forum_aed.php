<?php /* FORUMS $Id: do_forum_aed.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = new CForum();

if (($msg = $obj->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Forum' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( "m=forums" );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['forum_id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>