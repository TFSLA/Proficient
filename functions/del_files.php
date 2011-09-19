<?php

chdir("../");

include_once( "./includes/config.php" );
require_once( "./includes/db_mysql.php" );

db_connect( $dPconfig["dbhost"], $dPconfig["dbname"], $dPconfig["dbuser"], $dPconfig["dbpass"] );


$timestamp = 7; //Los archivos se guardan por una semana

// Traigo todos los archivos que hay que borrar
$sql_files="SELECT file_id, file_name FROM files WHERE file_delete_pending = 1 AND DATE_ADD(file_date_delete, INTERVAL $timestamp DAY) <= FROM_UNIXTIME(UNIX_TIMESTAMP())";
$rc_sql_files=mysql_query($sql_files);

while ($vec=mysql_fetch_array($rc_sql_files) )
{
echo "<br> Borrando por completo es archivo: " .$vec['file_name'] ."ID: " .$vec['file_id'] ."<br>";
	
	//Por cada archivo que hay que borrar traigo todas las versiones del mismo
	$sql_versions="SELECT id_files_ver, version_file_name FROM files_versions WHERE file_id = {$vec['file_id']}";
	$rc_sql_versions=mysql_query($sql_versions);
	
	while ($vec_versions=mysql_fetch_array($rc_sql_versions) )
	{
		// Removemos el archivo del FileSystem
		$archivo = "{$dPconfig['root_dir']}/files/{$vec_versions['version_file_name']}";
		unlink( $archivo );
		echo "Eliminando el archivo: " .$archivo ."<br>";
	}
	
	// Borro todos los registros de la tabla files_versions de ese archivo.
	$sql_del = "DELETE FROM files_versions WHERE file_id = {$vec['file_id']}";
	if (!db_exec( $sql_del ))
		return db_error();
	
	//Borro la entrada a la tabla FILES
	$sql_del = "DELETE FROM files WHERE file_id = {$vec['file_id']}";
	if (!db_exec( $sql_del ))
		return db_error();
	
}

echo "<hr>";

// Traigo todas las versiones, NO ARCHIVOS para borrar
$condicion = "delete_pending = 1 AND DATE_ADD(date_delete, INTERVAL $timestamp DAY) <= FROM_UNIXTIME(UNIX_TIMESTAMP())";
$sql_files = "SELECT id_files_ver, version_file_name FROM files_versions WHERE " .$condicion;
$rc_sql_files=mysql_query($sql_files);

while ($vec=mysql_fetch_array($rc_sql_files) )
{
echo "<br>Borrando ID-Version: " .$vec['id_files_ver'] ."<br>";
	
		// Removemos el archivo del FileSystem
		$archivo = "{$dPconfig['root_dir']}/files/{$vec['version_file_name']}";
		unlink( $archivo );
		echo "Eliminando la verion: " .$archivo ."<br>";
}
	
// Borro todos los registros de la tabla files_versions de las versiones eliminadas.
$sql_del = "DELETE FROM files_versions WHERE " .$condicion;
if (!db_exec( $sql_del ))
	return db_error();


//Por ultimo busco si quedo algun archivo sin version y quito el registro de la db
$sql = "SELECT files.file_id FROM files
LEFT JOIN files_versions ON files.file_id=files_versions.file_id
WHERE files_versions.file_id IS NULL";

$vec_files = db_loadColumn ($sql);

/*		
print_r ($vec_files);
echo "|" .$vec_files[0] ."|" .$vec_files[1];
*/

if ( count($vec_files) > 0 )
{
	$sql = "DELETE FROM files WHERE file_id IN ( " .implode( ',', $vec_files ). ")";
		if (!db_exec( $sql ))
			return db_error();
}






//require_once( "./includes/db_connect.php" );
function db_loadColumn( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	while ($row = db_fetch_row( $cur )) {
		$list[] = $row[0];
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

?>