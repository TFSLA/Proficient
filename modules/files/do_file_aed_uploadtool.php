<?php /* FILES $Id: do_file_aed_uploadtool.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
//addfile sql

if($errorCode == 0)
{
	$file_id = file_exist($_FILES['file']['name'], $_POST['file_project'], $_POST['file_section'], $_POST['file_opportunity'], $_POST['is_private'] == '1');

	$obj = new CFile();

	if (!$obj->bind( $_POST ))
	{
		$errorCode = 102;
		$errorDescription = "Error binding GET variables";
	}
	
	if($errorCode == 0)
	{
		if($file_id)
		{
		
			$fileExist = new CFile();
			$fileExist->load( $file_id );
		
			if($fileExist->file_description != "")
				$obj->file_description = $fileExist->file_description;
			
			$version_description = 'Versionado por E-mail';
			$tipo_cambio = 'grande';
		}
	}
}

if($errorCode == 0)
{
	set_time_limit( 600 );
	ignore_user_abort( 1 );

	$upload = null;
	if (isset( $_FILES['file']) ){
		$upload = $_FILES['file'];

		if ( ( $upload['size'] < 1 ) && ( !$file_id ) )
		{
				$errorCode = 103;
				$errorDescription = "Upload file size is zero.  Process aborted";
		}
		else if ($upload['name'] != NULL)
		{
			$obj->file_name = $upload['name'];
			$obj->file_type = $upload['type'];
			$obj->file_size = $upload['size'];
			$obj->file_date = db_unix2dateTime( time() );
			$obj->_file_real_filename = uniqid( rand() ); //Creamos un nombre aleatorio y unico

			if ( !$obj->moveTemp( $upload ))
			{
				$errorCode = 104;
				$errorDescription = "File Upload error";
			}
		}
	}
}

if($errorCode == 0)
{
	$sql = "SELECT category_id FROM files_category WHERE INSTR('".$obj->file_description."', name_es) > 0 OR INSTR('".$obj->file_description."', name_en) > 0 LIMIT 0,1";

	$categories = db_loadColumn($sql);

	if(count($categories) == 1)
		$obj->file_category = $categories[0];
}

if($errorCode == 0)
{
	if (!$file_id)
		$obj->file_owner = $AppUI->user_id;
	
	if($file_id)
		$obj->file_id = $file_id;

	if (! ($msg = $obj->store_fede($file_id, $version_description, $tipo_cambio, $AppUI->user_id )) )
	{
		if(!$file_id && $obj->is_private!=1){
			$sql = "SELECT MAX(file_id) AS file_id FROM files";
			$id_data = mysql_fetch_array(mysql_query($sql));
			$fileId = $id_data['file_id'];

			$obj->project = $_POST['file_project'];
			$obj->file_id = $fileId;
			$obj->notifyNewKnowledge($_POST['notify_type']);
		}elseif($obj->is_private!=1){
			$obj->project = $_POST['file_project'];
			$obj->notifyNewKnowledge($_POST['notify_type'],true);  //notificación de Archivo actualizado
		}
	}
}

if($errorCode == 0)
	echo $obj->file_id;
?>