<?php
$fecha=date("YmdHi");
$zipName = "filesrepository_backup_$fecha.zip";
$strPHPpath = " ";
$strDirToZip = "files";
$strPath = $strPHPpath;

$Zip_File="../../files/filesrepository_backup_$fecha.zip";
$Files_To_Zip="../../files/temp/";
$log="../../backup.log";
$tar="../../../../../lib/tar/tar -cvzf $Zip_File $Files_To_Zip >&1 >> $log";
echo $tar;
exec ('$tar');
















/*$fecha=date("YmdHi");
$file="../../files/filesrepository_backup_$fecha.bz2";
$bz = bzopen("$file", "w");
$backup1="../../files/temp";
bzwrite($bz, $backup1);
bzclose($bz);
echo "<html>";
echo "<a href='$file'>file</a>";
echo "</html>";*/
?>