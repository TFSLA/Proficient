<?php
/////////////////////////////////////////////////////////
//	
//	include/write_sinc.inc
//
//	(C)Copyright 2000-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	PURPOSE:
		Shell for write_sinc.backend.inc.
		Gets config from ./modules/wmail/conf/conf.inc

********************************************************/        

include("./modules/wmail/conf/conf.inc");

$user_name = strtolower($user_name);
$host = strtolower($host);

if ($backend!="FS") $backend = "DB";
include("./modules/wmail/include/write_sinc.".$backend.".inc");

/**** Backend agnostic code ***/
if (empty($error)){
	//prep uploads dir
	$uploadDir = $UPLOAD_DIR;
	if (empty($uploadDir)) $uploadDir = "./modules/wmail/uploads/";
	if (!is_dir(realpath($uploadDir))) $error .= "Invalid uploads directory<br>\n";
	else{
		$uploadDir = $uploadDir.ereg_replace("[\\/]", "", $user_name.".".$host);
		if (!is_dir(realpath($uploadDir))) mkdir($uploadDir, 0700);
	}
	
	//prep cache dir
	if (empty($CACHE_DIR)) $CACHE_DIR = "./modules/wmail/cache/";
	if (is_dir(realpath($CACHE_DIR))){
		$cacheDir = $CACHE_DIR.ereg_replace("[\\/]", "", $user_name.".".$host);
		if (!is_dir(realpath($cacheDir))) mkdir($cacheDir, 0700);
	}
	
	if (!file_exists(realpath($uploadDir))) $error .= "Invalid uploads directory<br>\n";
}

?>
