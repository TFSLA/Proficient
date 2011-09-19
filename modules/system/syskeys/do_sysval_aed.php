<?php /* SYSKEYS $Id: do_sysval_aed.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
$AppUI->redirect( "m=public&a=access_denied" );
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = new CSysVal();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

/*
Array con las columnas y los nombre de las variables de la clase para
verificar duplicidad de registros
*/
$arCols=array();
$arCols[0]['colname']="sysval_key_id";
$arCols[0]['coltype']="int";
$arCols[0]['colvalue']="sysval_key_id";
$arCols[1]['colname']="sysval_title";
$arCols[1]['coltype']="string";
$arCols[1]['colvalue']="sysval_title";
/*
Fin Array
*/

$AppUI->setMsg( "System Lookup Values", UI_MSG_ALERT );

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
    		$AppUI->setMsg( @$_POST['sysval_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );
    	}
    }
}

$AppUI->redirect( "m=system&u=syskeys" );
?>
