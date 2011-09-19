<?php /* DEPARTMENTS $Id: do_skillcategory_aed.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$skillcat = new CSkillCategory();

echo $_POST[0];
if (($msg = $skillcat->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Skill Category' );
if ($del) {
	if (($msg = $skillcat->delete())) {
		$AppUI->setMsg( $AppUI->_($msg), UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	
	}
} else {
	if (($msg = $skillcat->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}
//$AppUI->redirect();
?>