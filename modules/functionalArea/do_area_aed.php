<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;
echo "<pre>";
print_r($_POST);
echo "</pre>";

$dept = new CArea();
if (($msg = $dept->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Functional Area' );
if ($del) {
	if (($msg = $dept->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
} else {
	if (($msg = $dept->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}
$AppUI->redirect();
?>