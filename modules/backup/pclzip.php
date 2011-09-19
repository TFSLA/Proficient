<?php
  include_once('class.pclzip.lib.php');
	$max_execution_time = ini_get("max_execution_time");
	set_time_limit(0);
	$fecha=date("YmdHi");
	$dirs = array(
		'./classes'
	, './functions'
	, './images'
	, './includes'
	, './lib'
	, './locales'
	, './misc'
	, './modules'
	, './style'
//	, './*'
	);
	$filename = "./files/temp/psa-$fecha.zip";
  $archive = new PclZip($filename);
  $v_list = $archive->create($dirs); 
	set_time_limit($max_execution_time);
  if ($v_list == 0) {
    die("Error : ".$archive->errorInfo(true));
  }
	$msg = "Archivo creado con éxito <br>";
	$msg .= "<a href=\"$filename\" >Descargar </a>";
	echo $msg;
?> 
 
