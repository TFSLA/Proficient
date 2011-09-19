<?php /* SYSKEYS $Id: do_syskey_aed.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
$AppUI->redirect( "m=public&a=access_denied" );
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = new CSysKey();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

/*
Array con las columnas y los nombre de las variables de la clase para
verificar duplicidad de registros
*/
$arCols=array();
$arCols[0]['colname']="syskey_name";
$arCols[0]['coltype']="string";
$arCols[0]['colvalue']="syskey_name";
/*
Fin Array
*/

$AppUI->setMsg( "System Lookup Keys", UI_MSG_ALERT );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
} else {
    if(($msg = $obj->SysDuplicateRegistry($arCols))){
        $AppUI->setMsg($msg, UI_MSG_ERROR);
    }else{
    	if (($msg = $obj->store())) {
    		$AppUI->setMsg( $msg, UI_MSG_ERROR );
    	} else {
    		$AppUI->setMsg( @$_POST['syskey_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );
    	}
    }
}
$AppUI->redirect( "m=system&u=syskeys&a=keys" );
?>
