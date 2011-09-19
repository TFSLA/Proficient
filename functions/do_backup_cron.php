<?php
$dPconfig = array();

$debug='0';
include ('../includes/config.php');
require_once( "../includes/db_mysql.php" );
/*
ACTION								STATUS
Manual Backup					0						STANDBY
Manual_Backup					1						DO BACKUP
Manual_Backup					2						DOING BACKUP
Automatick Backup			0						STANDBY
Automatick Backup			2						DOING BACKUP

ID 1 MANUAL
ID 2 AUTO

*/
db_connect( $dPconfig["dbhost"], $dPconfig["dbname"], $dPconfig["dbuser"], $dPconfig["dbpass"] );

$sql1="SELECT * FROM backup_data WHERE id='1'";
$sql2="SELECT * FROM backup_data WHERE id='2'";
$vec1=db_fetch_array(db_exec($sql1));
$vec2=db_fetch_array(db_exec($sql2));

$dbdate=substr(str_replace('-','',$vec2['date']), 0, 8);
$do_back_update=$dbdate+$dPconfig['backupfreq'];
$acdate=date("Ymd");
//echo "<br>Vec1".$vec1['status'];
//echo "<br>Vec2".$vec2['status'];

IF (($vec1['status']=='1' AND $vec2['status']==0) OR ($vec2['status']=='0' AND $do_back_update<=$acdate)){
	//Bloqueo el uso del backup
	IF ($vec1['status']=='1'){
		$sql3="UPDATE backup_data SET status='2' WHERE id='1'";
		$file_exec='M';
	}
	ELSE {
		$sql3="UPDATE backup_data SET status='2' WHERE id='2'";
		$file_exec='A';
	}
	db_exec($sql3);
	
	$fecha=date("YmdHi");
	// Base de datos
	$database="-B ".$dPconfig['dbname'];
	$user="-u ".$dPconfig['dbuser'];
	$host="-h ".$dPconfig['dbhost'];
	$pwd="--password=".$dPconfig['dbpass'];
	$bckup=$dPconfig['ApacheChoroot'].$dPconfig['BckupPath'];
	$file="$bckup/DB-backup-".$dPconfig['dbname']."-$fecha.sql";
	$fileDB="DB-backup-".$dPconfig['dbname']."-$fecha.tar.gz";
	$fileFR="FR-backup-".$dPconfig['dbname']."-$fecha.tar.gz";
	$fileDBtgz="$bckup/$fileDB";
	$fileFRtgz="$bckup/$fileFR";
	$filespath=$dPconfig['ApacheChoroot']."/".$dPconfig['root_dir']."/files";
	
	
	//Tar
	$exec['1']=$dPconfig['mysqldump']." $database --opt $host $user $pwd > $file && tar -cvzf $fileDBtgz $file && rm $file";   // MysqlDump de la base de datos
	$exec['2']="tar -cvzf $fileFRtgz $filespath";
	
	$sql4="INSERT INTO backup ( file_name, file_date, file_content, file_exec) VALUES ('$fileDB', now(), 'DB', '$file_exec')";
	db_exec( $sql4 );
	$sql5="INSERT INTO backup ( file_name, file_date, file_content, file_exec) VALUES ('$fileFR', now(), 'FR', '$file_exec')";
	db_exec( $sql5 );
	

	IF ($debug==0){
		exec($exec['1']);
		exec($exec['2']);
	}
	
	// BORRO HISTORIAL VIEJO
	$sql6="SELECT id FROM backup ORDER BY  id DESC LIMIT 1";				//Busco la cantidad de Registros
	$vec=db_fetch_array(db_exec($sql6));
	$borrarid=$vec['id']-($dPconfig['backuphist']*2);								//Veo a partir del cual hay que borrar
	$sql7="SELECT id, file_name FROM backup WHERE id<=$borrarid";		//Genero vector de files a borrar
	$rc=db_exec($sql7);

	while ($vec=db_fetch_array($rc)){
		$fileRM=$bckup."/".$vec['file_name'];									//Armo el path con el nombre del archivo
		$exec['5']="rm $fileRM";														//Borro el Archivo
		exec($exec['5']);
		$sql8="DELETE FROM backup WHERE id=".$vec['id'];		//Borro el registro de la base
		db_exec($sql8);																			//Borro el registro de la base
	}

	// Desbloqueo el uso del backup
	IF ($vec1['status']=='1') $sql9="UPDATE backup_data SET status='0', date=now() WHERE id='1'";
	ELSE	$sql9="UPDATE backup_data SET status='0', date=now() WHERE id='2'";
	db_exec($sql9);
}

// SOLO PARA DEBUG
IF ($debug==1){
		echo "<br>".$exec['1'];
		echo "<br>".$exec['2'];
		echo "<br>".$sql1;
		echo "<br>".$sql2;
		echo "<br>".$sql3;
		echo "<br>".$sql4;
		echo "<br>".$sql5;
		echo "<br>".$sql6;
		echo "<br>".$sql7;
		echo "<br>".$sql8;
		echo "<br>".$sql9;
	}
?>