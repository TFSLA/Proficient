<?php /* TASKS $Id: do_updateexpensetask.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

$del = dPgetParam( $_POST, 'del', 0 );

$obj = new CTaskExpense();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($obj->task_expense_date) {
	$date = new CDate( $obj->task_expense_date );
	$obj->task_expense_date = $date->format( FMT_DATETIME_MYSQL );
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Task Expense' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT );
	}
	$AppUI->redirect();
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( @$_POST['task_expense_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}

$task = new CTask();

if (!$task->load( $obj->task_expense_task)) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID $obj->task_expense_task ", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

//$perm=$obj->getPermissionTask($AppUI->user_id);
$task->check();

if ($task->task_manual_percent_complete >= 100) {
	$task->task_end_date = $obj->task_expense_date;
}

// FIXME: buscar a ver que joraca hacer la funcion dPgetParam
// Si modificaron el porcentaje de avance manual, lo guardo en la db
if ( dPgetParam( $_POST, 'task_manual_percent_complete', null )) 
//if ( $_POST['task_manual_percent_complete'] )
	$task->task_manual_percent_complete_insert($_POST['task_manual_percent_complete']);

//$task->task_manual_percent_complete = dPgetParam( $_POST, 'task_percent_complete', null );

if (($msg = $task->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
}

$AppUI->redirect();
?>