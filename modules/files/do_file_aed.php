<?php /* FILES $Id: do_file_aed.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
//addfile sql

$file_id = intval( dPgetParam( $_POST, 'file_id', 0 ) );
$version_description = $_POST['version_description'];
$tipo_cambio = $_POST['tipo_cambio'];
$del = intval( dPgetParam( $_POST, 'del', 0 ) );
$obj = new CFile();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect($AppUI->state['SAVEDPLACE']);
}

if($obj->is_protected=='on'){
	$obj->is_protected=1;
}else {
	$obj->is_protected=0;
}
if($obj->is_private=='on'){
	$obj->is_private=1;
}else {
	$obj->is_private=0;
}
// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'File' );
// delete the file
if ($del)
{
	$obj->load( $file_id );
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect($AppUI->state['SAVEDPLACE']);
	}
}

if ($recovery)
{
	$obj->load( $file_id );
	if (($msg = $obj->recovery())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "recovered", UI_MSG_ALERT, true );
		$AppUI->redirect($AppUI->state['SAVEDPLACE']);
	}

	return;
}

set_time_limit( 600 );
ignore_user_abort( 1 );

//echo "<pre>";print_r($_POST);echo "</pre>";die;
//echo "<pre>";print_r($obj);echo "</pre>";die;

$upload = null;
if (isset( $_FILES['formfile']) ){
	$upload = $_FILES['formfile'];

	if ( ( $upload['size'] < 1 ) && ( !$file_id ) ) //Si el chango esta actualizando los datos no hace falta que siempre suba un archivo nuevo
	{
			$AppUI->setMsg( 'Upload file size is zero.  Process aborted.', UI_MSG_ERROR );
			$AppUI->redirect();
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
			$AppUI->setMsg( 'fileUploadError', UI_MSG_ERROR );
			$AppUI->redirect($AppUI->state['SAVEDPLACE']);
		}
	}
}

if (!$file_id)
{
	$obj->file_owner = $AppUI->user_id;
}

if ( ($msg = $obj->store_fede($file_id, $version_description, $tipo_cambio, $AppUI->user_id )) )
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
else
{
	//$obj->saveLog(2,2);

	if(!$file_id && $obj->is_private!=1){
		$sql = "SELECT MAX(file_id) AS file_id FROM files";
		$id_data = mysql_fetch_array(mysql_query($sql));
		$fileId = $id_data['file_id'];
		
		$obj->project = $_POST['project'];
		$obj->file_id = $fileId;
		$obj->notifyNewKnowledge($_POST['notify_type']);
	}elseif($obj->is_private!=1){
	
		if($obj->_file_real_filename != NULL)
		{
			$obj->project = $_POST['project'];
			$obj->notifyNewKnowledge($_POST['notify_type'],true);  //notificación de Archivo actualizado
		}
	}
	$AppUI->setMsg( $file_id ? 'updated' : 'added', UI_MSG_OK, true );
}

$AppUI->redirect($AppUI->state['SAVEDPLACE']);
?>