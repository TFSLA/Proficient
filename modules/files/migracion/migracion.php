<?php
//Agrupar y borrar carpeta
error_reporting(E_ERROR | E_PARSE);

chdir("../../");
include ('./includes/config.php');
require_once( "./includes/db_mysql.php" );
db_connect( $dPconfig["dbhost"], $dPconfig["dbname"], $dPconfig["dbuser"], $dPconfig["dbpass"] );
$sql="SELECT * FROM files";


require_once( "./includes/config.php" );

$rc=mysql_query($sql);

while ($vec=mysql_fetch_array($rc) )
{
	$origen = $dPconfig['root_dir'] ."/files/" .$vec['file_project'] ."/" .$vec['file_real_filename'];
	$destino = $dPconfig['root_dir'] ."/files/" .$vec['file_real_filename'];
	
	if ( rename($origen,$destino) )
		echo "Copiando de: " .$origen ."&nbsp &nbsp &nbsp a: " .$destino ."<br>"; 
		
	$carpeta = $dPconfig['root_dir'] ."/files/" .$vec['file_project'];
	if ( rmdir($carpeta) )
		echo "Borrando carpeta: " .$carpeta ;
}

/*
$sql = "use `psa_beta`; DROP TABLE IF EXISTS files; DROP TABLE IF EXISTS files_versions;";
$rc=mysql_query($sql);
if (!$rc) 
{
	die('Erro en la consulta: ' . mysql_error());
}
else
	echo "Script SQL OK";
*/

?>