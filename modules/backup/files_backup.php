<?php
  include_once('pclzip.lib.php');
	$max_execution_time = ini_get("max_execution_time");
	set_time_limit(0);
	$fecha=date("YmdHi");
	$dirs = array(
		 './files'
		);
	$filename = "./files/temp/files-$fecha.zip";
  $archive = new PclZip($filename);
  $v_list = $archive->create($dirs); 
	set_time_limit($max_execution_time);
  if ($v_list == 0) {
    die("Error : ".$archive->errorInfo(true));
  }
	$msg = "Archivo creado con éxito <br>";
	$msg .= "<a href=\"$filename\" >Descargar </a>";
	//echo $msg;
	
	$zip_content = file_get_contents($filename);
    $file = "files_backup_$fecha.zip";
	$mime_type = 'application/x-zip';
	header('Content-Disposition: inline; filename="' . $file . '"');
	header('Content-Type: ' . $mime_type);
	echo $zip_content;	
	
?> 
 
