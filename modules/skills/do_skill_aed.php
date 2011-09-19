<?php /* DEPARTMENTS $Id: do_skill_aed.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$skill = new CSkill();
echo $_POST[0];
if (($msg = $skill->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Skill' );
if ($del) {
	if (($msg = $skill->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
} else {
	if (($msg = $skill->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}
$AppUI->redirect();
?>