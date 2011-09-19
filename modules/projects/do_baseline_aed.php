<?php /* PROJECTS $Id: do_baseline_aed.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$obj = new CBaseline();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
// convert dates to SQL format first

$del = dPgetParam( $_POST, 'del', 0 );

$obj->id = $_POST["baseline_id"];
// prepare (and translate) the module name ready for the suffix
if ($del) 
{
	if (!$obj->canDelete( $msg )) 
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) 
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	else 
	{
		$AppUI->setMsg( "Baseline deleted", UI_MSG_ALERT);		
		$AppUI->redirect();
	}
}
else
{
	$debugql=1;	
	if (($msg = $obj->store())) 
	{		
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	else
	{		
		$isNotNew = @$_POST['baseline_id'];
		$AppUI->setMsg( "Baseline" . ($isNotNew ? ' updated' : ' inserted'), UI_MSG_OK);
	}
	/*if (isset($hassign)) 
	{
		$obj->updateOwners( $hassign );
	}
	
	if (isset($husersassign)) {
		$obj->updateUsers( $husersassign );
	}*/	
	$AppUI->redirect();
}
?>
