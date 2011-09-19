<?php

if (getDenyEdit("hhrr")){
	$AppUI->redirect( "m=public&a=access_denied" );
}

$upload_dir = $AppUI->getConfig('hhrr_uploads_dir');

$_FILES["doc_file"]["name"] = ereg_replace(" ","_",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("á","a",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("é","e",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("í","i",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("ó","o",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("ú","u",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("'","-",$_FILES["doc_file"]["name"]);
$_FILES["doc_file"]["name"] = ereg_replace("\"","-",$_FILES["doc_file"]["name"]);

$import_file = $_FILES["doc_file"];
$id = $_POST["user_id"];
$datetime = date("Y-m-d H:i:s");
$from_date = $_POST["log_from_date_doc"];
$to_date = $_POST["log_to_date_doc"];
$comments = $_POST["doc_comments"];
$userdir="$upload_dir/$id";
$doc_id = $_POST["doc_id"];
$action = $_POST["action"];

if($action == "del"){
	$sql = "SELECT doc_file FROM hhrr_performance_documents WHERE doc_id = $doc_id";
	$file_name = mysql_fetch_array(mysql_query($sql));
	
	if(unlink($userdir."/".$file_name["doc_file"])){
		$sql = "DELETE FROM hhrr_performance_documents WHERE doc_id = $doc_id";
		mysql_query($sql) or die(mysql_error());
		
		$AppUI->setMsg( $AppUI->_("Document deleted"), UI_MSG_ALERT, true );
		$AppUI->redirect();
	}else{
		$AppUI->setMsg( "ERROR: ".$userdir."/".$file_name["doc_file"]." file don't exists", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

if((empty($import_file["name"]) || $import_file["size"]==0) && $action == "add"){
	$AppUI->setMsg( $AppUI->_("Invalid File or empty size"), UI_MSG_ERROR );
	$AppUI->redirect();
}

if(is_file($userdir."/".$import_file["name"])){
	$AppUI->setMsg( $AppUI->_("File already exists!"), UI_MSG_ERROR );
	$AppUI->redirect();
}

//die("<pre>".print_r($import_file)."</pre>");

if (!is_dir($userdir)){
	mkdir($userdir,0755);
}

if($action == "add"){
	$file_name = $import_file["name"];
	if(is_null(move_uploaded_file($import_file['tmp_name'], $userdir."/".$file_name))) {
		$AppUI->setMsg( $AppUI->_("Unable to upload file"), UI_MSG_ERROR );
		$AppUI->redirect();
	}
	
	$sql = "INSERT INTO hhrr_performance_documents
			( user_id, doc_file, from_date, to_date, comments, saved_date, user_creator) 
			VALUES ( 
			$id, 
			'".$file_name."', 
			'".$from_date."', 
			'".$to_date."', 
			'".$comments."', 
			'".$datetime."', 
			$AppUI->user_id )";
	
	if(!db_exec($sql)){
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		$AppUI->redirect();
	}
}elseif ($action=="edit"){
	if(!empty($import_file["name"]) && $import_file["size"] > 0){
		$new_file_name = $import_file["name"];
		if(is_null(move_uploaded_file($import_file['tmp_name'], $userdir."/".$new_file_name))) {
			$AppUI->setMsg( $AppUI->_("Unable to upload file"), UI_MSG_ERROR );
			$AppUI->redirect();
		}
		
		$sql = "SELECT doc_file FROM hhrr_performance_documents WHERE doc_id = $doc_id";
		$file_name = mysql_fetch_array(mysql_query($sql));
		
		if(!unlink($userdir."/".$file_name["doc_file"])){
			$AppUI->setMsg( "ERROR: ".$userdir."/".$file_name["doc_file"]." file don't exists", UI_MSG_ERROR );
			$AppUI->redirect();
		}
		$sql_ext = "doc_file = '$new_file_name' , ";
	}
	$sql = "UPDATE hhrr_performance_documents SET
				$sql_ext
				from_date = '$from_date' , 
				to_date = '$to_date' , 
				comments = '$comments' 
				WHERE doc_id = $doc_id
				";
	
	if(!db_exec($sql)){
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

$AppUI->setMsg( $AppUI->_("Document added"), UI_MSG_OK, true );
$AppUI->redirect();

?>