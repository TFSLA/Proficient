<?php
global $AppUI;

if (!isset($_GET['suppressHeaders'])){
    $AppUI->redirect( $_SERVER['QUERY_STRING']."&suppressHeaders=1" );
}

$bWinOS = false;
$strServerOS = strtolower($_SERVER["SERVER_SOFTWARE"]);
if(strpos($strServerOS, "win") !== false && strpos($strServerOS, "unix") === false && strpos($strServerOS, "linux") === false){
	$bWinOS = true;
}

$fecha=date("YmdHi");
$zipName = "filesrepository_backup_$fecha.zip";
$strPHPpath = " ";

if($bWinOS){
	$strDirToZip = "files";
	$strPath = "files\\temp\\$zipName";	
	$strZipCommand = "start /B lib\zip\zip.exe -9 -r $strPath $strDirToZip -x files\\temp\\*.*";//windows
}else{
	$strDirToZip = "files";
	$strPath = $strPHPpath;
	//$strZipCommand = "./lib/zip/zip -9 -r $strPath $strDirToZip -x files/temp/*";//linux	
	$strZipCommand = "/lib/tar/tar -cvzf ./files/filesrepository_backup_$fecha.zip ./files/temp/*"; //linux
}

exec($strZipCommand);



/* AGREGO LOS FILES DE HHRR SI ESTA DEFINIDO EL DIRECTORIO */
/*$strDirToZip="";
$strDirToZip = $AppUI->getConfig("hhrr_uploads_dir");
if($strDirToZip){
	if($bWinOS){
		$strPath = "files\\temp\\$zipName";	
		$strZipCommand = "start /B lib\zip\zip.exe -9 -r $strPath $strDirToZip -x files\\temp\\*.*";//windows
	}else{
		$strPath = $strPHPpath;
		$strZipCommand = "./lib/zip/zip -9 -r $strPath $strDirToZip -x files/temp/*";//linux	
	}
	
	exec($strZipCommand);
}*/
/* FIN BACKUP HHRR FILES */


if(file_exists($strPath)){
	$strFile = file_get_contents($strPath);
	//delete file
    @unlink($strPath);
	
    
    header("Content-type:application/zip");
	$header = "Content-disposition: attachment; filename=\"$zipName\"";
    header($header);
	header("Content-length: " . strlen($strFile));
	header("Content-transfer-encoding: binary");
	header("Pragma: no-cache");
	header("Expires: 0");
	print($strFile);

}
?>