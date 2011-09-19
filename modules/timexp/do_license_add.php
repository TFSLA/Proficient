<?php

global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;
	
$upload_dir = $AppUI->getConfig('root_dir').'/files/timexp/licenses/'; //Dir para guardar files
$datetime = date("Y-m-d H:i:s");
$count = 0;
$format_acept = true;

foreach($_FILES as $import_file){
	if($import_file[size]>2000000){
		  $format_acept = false;
	}
}

if(empty($_REQUEST['timesheet_start_date']) || empty($_REQUEST['timesheet_end_date']) || empty($_REQUEST['licenseType'])) {
	$format_acept = false;
}

if($_REQUEST['timesheet_start_date'] < date("Y-m-d H:i:s") || $_REQUEST['timesheet_start_date'] > $_REQUEST['timesheet_end_date']) {
	$format_acept = false;
}

if($format_acept){
		if (!is_dir($upload_dir)){
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/',0755);
			mkdir($upload_dir,0755);  //Crear Path si no existe
		}
		
	foreach($_FILES as $import_file){
		if(!(empty($import_file[name])||$import_file[size]==0)){
			
		    $sql = "select MAX(license_id) as License_ID from timexp_licenses";
		    $result = mysql_query($sql);
		    $row = mysql_fetch_array($result);
			
		    $id_licencia = $row['License_ID'] + 1;
			$extension = explode('.',$import_file[name]);  //la extensión del file va a ser el último elemento
		    
			$count++;
			$file_name = 'certificate_'.$id_licencia.'_'.$count.'.'.$extension[count($extension)-1];
		
			move_uploaded_file($import_file['tmp_name'], $upload_dir.$file_name); //Upload
			
		    $sql = "insert into timexp_licenses_certificates (certificate_name, 
		  		certificate_create_date, certificate_related_license, certificate_size)
		  		values ('".$file_name."', '".$datetime."', ".$id_licencia.", ".$import_file[size].")";
		    
		    if(!db_exec($sql)){   
		  	    $AppUI->redirect('m=timexp&a=new_license&error=1'); 
		    }
		}
	}
	
	$sql = "select user_supervisor from users where user_id = ".$AppUI->user_id;
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	$status = 0;
	
	$approval_date = null;
	if(($row['user_supervisor'] == 0) || ($row['user_supervisor']==-1) || (empty($row["user_supervisor"]))) { 
		$status = 3;
		$approval_date = date("Y-m-d H:i:s");
	}
	
	$sql = "insert into timexp_licenses (license_creator, license_save_date, license_type, 
				license_description, license_status, license_from_date, license_to_date,
				license_has_attachments, license_approval_date, license_send_date)
				values (".$AppUI->user_id.", '".$datetime."', ".$_REQUEST['licenseType'].", 
				'".@$_REQUEST['comentarios']."', ".$status.", '".$_REQUEST['timesheet_start_date']."', '
				".$_REQUEST['timesheet_end_date']."', ".$count.",'".$approval_date."','".$approval_date."')";
	
	if(db_exec($sql)){
		$AppUI->redirect('m=timexp&a=new_license&saved=1');
	}
	else {
		  $AppUI->redirect('m=timexp&a=new_license&error=1'); 
	}
}
else {
	  $AppUI->redirect('m=timexp&a=new_license&error=1'); 
}
?>