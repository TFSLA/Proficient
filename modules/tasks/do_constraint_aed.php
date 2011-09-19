<?
$task_id = dpGetParam( $_POST, "task_id", 0 );
$constraint_id = dpGetParam( $_POST, "constraint_id", 0 );
$del = dpGetParam( $_POST, "del", 0 );
print_r( $_POST );
$obj = new CTaskConstraint();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

if ( $del )
{
	if ( $msg = $obj->delete() )
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	else
	{
		$AppUI->setMsg( "Constraint deleted", UI_MSG_OK);
	}	
}
else
{	
	if ( $obj->constraint_type == "ASAP" || $obj->constraint_type == "ALAP" )
	{
		$obj->constraint_parameter = NULL;
	}
	if (($msg = $obj->store(true))) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( (@$_POST['constraint_id'] ? 'Constraint updated' : 'Constraint added'), UI_MSG_OK);		
	}
}
//exit();
$AppUI->redirect();
?>