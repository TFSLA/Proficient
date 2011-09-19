<?php
  
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;
	
$upload_dir = $AppUI->getConfig('root_dir').'/files/timexp/licences/'; //Dir para guardar files
$datetime = date("Y-m-d H:i:s");
$count = 0;
$format_acept = true;

foreach($_FILES as $import_file){
	if($import_file[size]>2000000){
		  $format_acept = false;
	}
}

if(empty($_REQUEST['timesheet_start_date']) || empty($_REQUEST['timesheet_end_date']) || empty($_REQUEST['licenceType'])) {
	$format_acept = false;
}

if($_REQUEST['timesheet_start_date'] < date("Y-m-d H:i:s") || $_REQUEST['timesheet_start_date'] > $_REQUEST['timesheet_end_date']) {
	$format_acept = false;
}

if($format_acept){
		if (!is_dir($upload_dir)){
			//mkdir($upload_dir,0755);  //Crear Path si no existe
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/',0755);
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/licences/',0755);
		}
		
	foreach($_FILES as $import_file){
		if(!(empty($import_file[name])||$import_file[size]==0)){
			
		    $sql = "select MAX(licence_id) as Licence_ID from timexp_licences";
		    $result = mysql_query($sql);
		    $row = mysql_fetch_array($result);
			
		    $id_licencia = $row['Licence_ID'] + 1;
			$extension = explode('.',$import_file[name]);  //la extensión del file va a ser el último elemento
		    
			$count++;
			$file_name = 'Justification_'.$id_licencia.'_'.$count.'.'.$extension[count($extension)-1];
		
			move_uploaded_file($import_file['tmp_name'], $upload_dir.$file_name); //Upload
			
		    $sql = "insert into timexp_licences_justifications (justification_name, 
		  		justification_create_date, justification_related_licence, justification_size)
		  		values ('".$file_name."', '".$datetime."', ".$id_licencia.", ".$import_file[size].")";
		    
		    if(!db_exec($sql)){   
		  	    $AppUI->redirect('m=timexp&a=new_licence&dialog=1&suppressLogo=1&error=1'); 
		    }
		}
	}
	$sql = "select user_supervisor from users where user_id = ".$AppUI->user_id;
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	$status = 0;
	
	if($row['user_supervisor']==0) { $status = 3; } 
	
	$sql = "insert into timexp_licences (licence_creator, licence_save_date, licence_type, 
				licence_description, licence_status, licence_from_date, licence_to_date,
				licence_has_attachments)
				values (".$AppUI->user_id.", '".$datetime."', '".$_REQUEST['licenceType']."', 
				'".@$_REQUEST['comentarios']."', ".$status.", '".$_REQUEST['timesheet_start_date']."', '
				".$_REQUEST['timesheet_end_date']."', ".$count.")";
	
	if(db_exec($sql)){
		$AppUI->redirect('m=timexp&a=new_licence&dialog=1&suppressLogo=1&saved=1');
	}
	else {
		  $AppUI->redirect('m=timexp&a=new_licence&dialog=1&suppressLogo=1&error=1'); 
	}
}
else {
	  $AppUI->redirect('m=timexp&a=new_licence&dialog=1&suppressLogo=1&error=1'); 
}
?>