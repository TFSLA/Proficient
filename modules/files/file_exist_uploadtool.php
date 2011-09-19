<?php

/**
 * Esta funcion se fija en la BD si ya existe un archivo con ese nombre y el mismo proyecto.
 * Si existe le propone al usuario ir a la pagina de edicion de ese archivo para desde ahi generar la nueva version.
 */
function file_exist($file_name, $file_project, $file_section, $file_opportunity, $file_is_private)
{
	global $AppUI;

	$file_name=preg_replace( '/^.+[\\\\\\/]/', '', $file_name );
	
	$file_name = mb_convert_encoding($file_name, "ISO-8859-1", "UTF-8");

	$sql = "SELECT file_id FROM files";
	$sql .= " WHERE file_name = '".$file_name."'";
	$sql .= " AND file_project = ".$file_project;
	$sql .= " AND file_section = ".$file_section;
	$sql .= " AND file_opportunity = ".$file_opportunity;
	$sql .= " AND ((is_private = 1 AND file_owner = ".$AppUI->user_id.")";
	
	if(!$file_is_private)
		$sql .= " OR is_private = 0)";
	else
		$sql .= ")";
	
	$sql .= " AND file_delete_pending = 0";
	$sql .= " LIMIT 1;";
	
	$file_exist=db_loadResult($sql);

	if ($file_exist)
		return $file_exist;
	else
		return 0;
}
?>
