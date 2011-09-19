<?php

$xajax->registerFunction("file_exist");

/**
 * Esta funcion se fija en la BD si ya existe un archivo con ese nombre y el mismo proyecto.
 * Si existe le propone al usuario ir a la pagina de edicion de ese archivo para desde ahi generar la nueva version.
 */
function file_exist($file_name, $file_project, $file_is_private)
{
	global $AppUI;
	$objResponse = new xajaxResponse();

	$file_name=preg_replace( '/^.+[\\\\\\/]/', '', $file_name );

	$file_name = mb_convert_encoding($file_name, "ISO-8859-1", "UTF-8");

	$sql = "SELECT file_id FROM files";
	$sql .= " WHERE file_name = '".$file_name."'";
	$sql .= " AND file_project = ".$file_project;
	$sql .= " AND ((is_private = 1 AND file_owner = ".$AppUI->user_id.")";
	
	if($file_is_private == 'false')
		$sql .= " OR is_private = 0)";
	else
		$sql .= ")";
	
	$sql .= " AND file_delete_pending = 0";
	$sql .= " LIMIT 1;";

	$file_exist=db_loadResult($sql);

	if ($file_exist)
	{
		/**
		 * addConfirmCommands (integer $iCmdNumber, string $sMessage)
		 * $iCmdNumber (integer): the number of commands to skip if the user presses Cancel in the browsers's confirm dialog
		 * $sMessage (string): the message to show in the browser's confirm dialog
		 */
		$objResponse->addConfirmCommands(1,utf8_encode($AppUI->_( 'fileNewExist' )));
		$objResponse->addRedirect("index.php?m=files&a=addedit&file_id=$file_exist");
	}
	else
	{
		$objResponse->addScript("document.uploadFrm.submit();");
	}

	return $objResponse;
}
?>
