<?php
	/**
	 * \brief Devuelve el la ultima version del archivo que se pase como parametro SIN contemplar que el archivo este borrado o no
	 * \author Fede Ravizzini
	 * \date 26/12/06
	 * \version 1.0
	 * \return FALSE en caso de error.
	 */	
	function get_file_last_version_without_del($file_id)
	{
		$sql = "SELECT version FROM files_versions WHERE file_id = " .$file_id ."  ORDER BY version DESC LIMIT 1";
		$resultado = mysql_query( $sql );
		if ( $resultado == FALSE)
			return FALSE;
		
		$row = mysql_fetch_array($resultado);
		return  $row[0];		
	}
	
	/**
	 * \brief Devuelve el la ultima version del archivo que se pase como parametro y que la version no este borrada
	 * \author Fede Ravizzini
	 * \date 26/12/06
	 * \version 1.0
	 * \return FALSE en caso de error.
	 */	
	function get_file_last_version_with_del($file_id)
	{
		$sql = "SELECT version FROM files_versions WHERE file_id = " .$file_id ." AND delete_pending = 0 ORDER BY version DESC LIMIT 1";
		$resultado = mysql_query( $sql );
		if ( $resultado == FALSE)
			return FALSE;
		
		$row = mysql_fetch_array($resultado);
		return  $row[0];		
	}
	

	/**
	 * \brief Actualiza la DB marcando como pendiente para borrar el archivo que se pase con la fecha de hoy
	 * \author Fede Ravizzini
	 * \date 26/12/06
	 * \version 1.0
	 * \return NULL en caso de error.
	 */		
	function del_file_version($id_files_ver)
	{
	
		$sql = "UPDATE files_versions SET delete_pending = 1, date_delete = FROM_UNIXTIME(UNIX_TIMESTAMP()) WHERE id_files_ver = $id_files_ver";
		if (!db_exec( $sql ))
		{
			return db_error();
		}
		
		return NULL;
	}
	
	function del_file() 
	{
		global $AppUI;
		global $file_id;
		
		$sql = "UPDATE files SET file_delete_pending = 1, file_date_delete = FROM_UNIXTIME(UNIX_TIMESTAMP()) WHERE file_id = $file_id";
		if (!db_exec( $sql ))
		{
			return db_error();
		}
		
		return NULL;
	}
	
	
	
	/**
	 * \brief Actualiza la DB marcando pasando el campo delete_pending a 0 para que no sea eliminado por el cron.
	 * \author Fede Ravizzini
	 * \date 03/01/07
	 * \version 1.0
	 * \return NULL en caso de error.
	 */		
	function recover_file_version($id_files_ver)
	{
	
		$sql = "UPDATE files_versions SET delete_pending = 0, date_delete = '0000-00-00 00:00:00' WHERE id_files_ver = $id_files_ver";
		if (!db_exec( $sql ))
		{
			return db_error();
		}
		
		return NULL;
	}
	

/**
* \brief Corta un string segun la longitud pasada y muestra los ultimos caracteres
* \return El nuevo string.
*/

if (!function_exists('cutString')){
	function cutString($pString, $pLengthToShow=35, $pShowLastChars=4)
	{
		$valReturn = $pString;
		$intPoints = 3;
		$strChar = ".";
	
		if(strlen($pString) > $pLengthToShow){
			$strTmp = substr($pString, 0, ($pLengthToShow - $intPoints - $pShowLastChars));
			for($i=0; $i < $intPoints; $i++){
			$strTmp .= $strChar;
			}
			if($pShowLastChars > 0){
			$strTmp .= substr($pString, - $pShowLastChars);
			}
			$valReturn = $strTmp;
		}
	
		return $valReturn;
	}
}

	function ultima_ver()
	{
		global $file_id;
		
		$sql = "SELECT COUNT(*) AS cuantos FROM files_versions WHERE file_id = " .$file_id ." AND delete_pending = 0;";
		$resultado = mysql_query( $sql );
		if ( $resultado == FALSE)
			return FALSE;
		
		$row = mysql_fetch_array($resultado);

		
		return ( $row[0] == 1? "TRUE" : "FALSE" );
		//return  "cant: " .$row[0];
	}
	
	
	/* Devuelve el tama�o en una forma humanamente legible*/
	function size_hum_read($size)
	{
	$i=0;
	$iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
	while (($size/1024)>1)
	{
	$size=$size/1024;
	$i++;
	}
	return substr(intval($size),0,strpos($size,'.')+4).$iec[$i];
	}
	// Usage : size_hum_read(filesize($file));

	
?>