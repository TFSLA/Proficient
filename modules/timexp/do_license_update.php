<?php
  
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;
	
$upload_dir = $AppUI->getConfig('root_dir').'/files/timexp/licenses/'; //Dir para guardar files
$datetime = date("Y-m-d H:i:s");
$count = 0;
$format_acept = true;
$id_licencia = $_REQUEST['license_id'];

foreach($_FILES as $import_file){
	if($import_file[size]>2000000){
		  $format_acept = false;
	}
}

if(is_not_sended($id_licencia)){
	if(empty($_REQUEST['timesheet_start_date']) || empty($_REQUEST['timesheet_end_date']) || empty($_REQUEST['licenseType'])) {
		$format_acept = false;
	}
	
	if($_REQUEST['timesheet_start_date'] < date("Y-m-d H:i:s") || $_REQUEST['timesheet_start_date'] > $_REQUEST['timesheet_end_date']) {
		$format_acept = false;
	}
}

if($format_acept){
		if (!is_dir($upload_dir)){
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/',0755);
			mkdir($upload_dir,0755);  //Crear Path si no existe
		}
	    $sql = "select license_has_attachments from timexp_licenses where license_id = ".$id_licencia;
	    $result = mysql_query($sql);
	    $row = mysql_fetch_array($result);
	    $count = $row["license_has_attachments"];
		
	foreach($_FILES as $import_file){
		if(!(empty($import_file[name])||$import_file[size]==0)){
		    $count++;
		    
			$extension = explode('.',$import_file[name]);  //la extensión del file va a ser el último elemento
		    
			$file_name = 'certificate_'.$id_licencia.'_'.$count.'.'.$extension[count($extension)-1];
		
			move_uploaded_file($import_file['tmp_name'], $upload_dir.$file_name); //Upload
			
		    $sql = "insert into timexp_licenses_certificates (certificate_name, 
		  		certificate_create_date, certificate_related_license, certificate_size)
		  		values ('".@$file_name."', '".$datetime."', ".$id_licencia.", ".$import_file[size].")";
		    
		    if(!db_exec($sql)){
		  	    $AppUI->redirect('m=timexp&a=new_license&dialog=1&suppressLogo=1&error=1'); 
		    }
		}
	}
	
	if(is_not_sent($id_licencia)){
		$sql = "update timexp_licenses set license_save_date = '".date("Y-m-d H:i:s")."', 
				license_type = ".$_REQUEST['licenseType'].", 
				license_description = '".$_REQUEST['comentarios']."', 
				license_from_date = '".$_REQUEST['timesheet_start_date']."', 
				license_to_date = '".$_REQUEST['timesheet_end_date']."',
				license_has_attachments = ".$count." where license_id = ".$id_licencia;
	}else{
		$sql = "update timexp_licenses set license_save_date = '".date("Y-m-d H:i:s")."', 
				license_has_attachments = ".$count." where license_id = ".$id_licencia;
		if($count!=0){	notifySupervisor($id_licencia,$count); }
	}
	
	
	if(db_exec($sql)){
 		$AppUI->setMsg( 'updated' , UI_MSG_OK, true );	
  		$AppUI->redirect('m=timexp&a=editlicense&id='.$id_licencia);
 	 }
 	 else {
  		$AppUI->setMsg( "error", UI_MSG_ERROR );
  		$AppUI->redirect('m=timexp&a=editlicense&id='.$id_licencia);
  	}
}
else {
	$AppUI->setMsg( "error", UI_MSG_ERROR );
	$AppUI->redirect('m=timexp&a=editlicense&id='.$id_licencia);
}

//---------------------------------------------------------------------------------------------

function is_not_sent($id_license){
	$sql="select license_status from timexp_licenses where license_id = ".$id_license;
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result);
	
	if($row['license_status']==0){
		return true;
	}else{
		return false;
	}
}

//------------------------------------------------------------------------------------------------

function notifySupervisor( $id_licencia , $certificates ){	
	global $AppUI;
	
	$sql="select user_supervisor from users where user_id = ".$AppUI->user_id;
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	$supervisor_id=$row['user_supervisor'];
	
	$sql="select user_email from users where user_id = ".$supervisor_id;
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	$supervisor_email=$row['user_email'];
	
	$sql = "select * from timexp_licenses where license_id = ".$id_licencia;	
	$result = mysql_query($sql);
	$license_data = mysql_fetch_array($result);

	$startdate = new CDate($license_data["license_from_date"]);
	$enddate = new CDate($license_data["license_to_date"]);
	$date = new CDate(date("Y-m-d H:i:s"));
	
  	$usr = new CUser();
	$usr->load($supervisor_id);
	$prefs = CUser::getUserPrefs($usr->user_id);
	$user_language = isset($prefs["LOCALE"]) ? $prefs["LOCALE"] : $AppUI->getConfig("host_locale");
	$df = isset($prefs["SHDATEFORMAT"]) ? $prefs["SHDATEFORMAT"] : $AppUI->getPref('SHDATEFORMAT');
	
	$strEmailFrom = $AppUI->getConfig("mailfrom");
	
	$Ccompany = new CCompany();
	if($Ccompany->load($usr->user_company)){
		if(!empty($Ccompany->company_email)){
			$strEmailFrom = $Ccompany->company_email;
		}
	}
	
	$sql = "select * from timexp_licenses_types where license_type_id = ".$license_data['license_type'];
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	$license_type = $row['license_type_description_'.$user_language];
	
	$msgBody=@"\n".
	$AppUI->_to($user_language,"CertificateSubmittedMSG")."\n".
	"\n".str_repeat("=",70)."\n".
	$AppUI->getConfig('base_url')."/index.php?m=timexp&a=viewlicense&license_id=".$license_data['license_id'].
	"\n".str_repeat("=",70)."\n".
	$AppUI->_to($user_language,"License Details").
	"\n".str_repeat("=",70)."\n".
	$AppUI->_to($user_language,"Owner").": ".$AppUI->user_last_name.", ".$AppUI->user_first_name."\n".
	$AppUI->_to($user_language,"Type").": ".$license_type."\n".
	$AppUI->_to($user_language,"From Date").": ".$startdate->format($df)."\n".
	$AppUI->_to($user_language,"To Date").": ".$enddate->format($df)."\n".
	$AppUI->_to($user_language,"Comments").": ".$license_data['license_description']."\n\n".
	$AppUI->_to($user_language,"Submitted certificates").": ".$certificates."\n\n\n".
	str_repeat("=",70)."\n\n";
	
	$title = "[PSA] ".$AppUI->_to($user_language,"CertificateSubmittedTitle");
	
	mail($supervisor_email, $title,$msgBody, 'From: '.$strEmailFrom);
	
	return true;
}



?>