<?php
  
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;
	
$upload_dir = $AppUI->getConfig('root_dir').'/files/timexp/licences/'; //Dir para guardar files
$datetime = date("Y-m-d H:i:s");
$count = 0;
$format_acept = true;
$id_licencia = $_REQUEST['licence_id'];

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
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/',0755);
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/licences/',0755);
			//mkdir($upload_dir,0755);  //Crear Path si no existe
		}
	    $sql = "select licence_has_attachments from timexp_licences where licence_id = ".$id_licencia;
	    $result = mysql_query($sql);
	    $row = mysql_fetch_array($result);
	    $count = $row["licence_has_attachments"];
		
	foreach($_FILES as $import_file){
		if(!(empty($import_file[name])||$import_file[size]==0)){
		    $count++;
		    
			$extension = explode('.',$import_file[name]);  //la extensión del file va a ser el último elemento
		    
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
	
    $sql = "update timexp_licences set licence_save_date = '".date("Y-m-d H:i:s")."', 
			licence_type = '".$_REQUEST['licenceType']."', 
			licence_description = '".$_REQUEST['comentarios']."', 
			licence_from_date = '".$_REQUEST['timesheet_start_date']."', 
			licence_to_date = '".$_REQUEST['timesheet_end_date']."',
			licence_has_attachments = ".$count." where licence_id = ".$id_licencia;
	
	if(db_exec($sql)){
 		$AppUI->setMsg( 'updated' , UI_MSG_OK, true );	
  		$AppUI->redirect('m=timexp&a=editlicence&id='.$id_licencia);
 	 }
 	 else {
  		$AppUI->setMsg( "error", UI_MSG_ERROR );
  		$AppUI->redirect('m=timexp&a=editlicence&id='.$id_licencia);
  	}
}
else {
	$AppUI->setMsg( "error", UI_MSG_ERROR );
	$AppUI->redirect('m=timexp&a=editlicence&id='.$id_licencia);
}
?>