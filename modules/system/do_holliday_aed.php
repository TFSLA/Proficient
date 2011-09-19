<?php
$del 			= dpGetParam( $_POST, "del", 0 );
$holliday_id 	= dpGetParam( $_POST, "holliday_id", 0 );

if (!$canEdit && ($AppUI->user_type != 1 && $m == "system")) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$obj=new CHolliday;	
if (! $obj->bind( $_POST ) ) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();	
}

/*
Array con las columnas y los nombre de las variables de la clase para
verificar duplicidad de registros
*/
$arCols=array();
$arCols[0]['colname']="holliday_day";
$arCols[0]['coltype']="int";
$arCols[0]['colvalue']="holliday_day";
$arCols[1]['colname']="holliday_month";
$arCols[1]['coltype']="int";
$arCols[1]['colvalue']="holliday_month";
$arCols[2]['colname']="holliday_year";
$arCols[2]['coltype']="int";
$arCols[2]['colvalue']="holliday_year";
$arCols[3]['colname']="holliday_company";
$arCols[3]['coltype']="int";
$arCols[3]['colvalue']="holliday_company";
/*
Fin Array
*/
//$msg = $obj->SysDuplicateRegistry($arCols);
//$AppUI->setMsg($msg, UI_MSG_ERROR);

if ( $del )
{
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}	
	//echo "Borrado";
}
else
{
	$isNotNew = @$_POST['holliday_id'];	
    if(($msg = $obj->SysDuplicateRegistry($arCols))){
        $AppUI->setMsg($msg, UI_MSG_ERROR);
    }else{
        if ( $msg = $obj->store() )
    	{
    		$AppUI->setMsg( $msg, UI_MSG_ERROR );
    	}
    	else
    	{
    		$AppUI->setMsg( $isNotNew ? "updated" : "inserted", UI_MSG_ALERT, true );
    	}
    }
}

$AppUI->redirect();
?>
