<?php
/*
echo "<pre>";
print_r ($_POST);
//print_r ($_GET);
echo "</pre>";
*/
define("NOTE_INTERVIEW", 1);
define("NOTE_INTERNAL", 2);

$accion = isset($_POST['accion']) ? $_POST['accion'] : "";

//Si la accion es borrar la nota la borramos
if ($accion == "del_hhrr_note")
{
	$sql = "DELETE FROM hhrr_notes WHERE hhrr_note_id = ". $_POST['hhrr_note_id'];
	if ( db_exec($sql) )
		$AppUI->setMsg( $AppUI->_( 'deleted' ), UI_MSG_OK);	
	else
		$AppUI->setMsg( $AppUI->_( 'errorDelet' ), UI_MSG_ERROR);
}
elseif($accion == "new_hhrr_note") //Nueva nota
{
	$hhrr_user_id=$_POST['user_id'];
	$hhrr_note_owner=$AppUI->user_id;
	$noteType=$_POST['noteType'];
	if ( $noteType == NOTE_INTERVIEW )
		$hhrr_note=$_POST['interviewcomments'];
	else
		$hhrr_note=$_POST['comments'];
	
	$sql = "INSERT INTO hhrr_notes (hhrr_user_id, hhrr_note, hhrr_note_type, hhrr_note_date, hhrr_note_owner) 
				VALUES ($hhrr_user_id, '$hhrr_note', $noteType, NOW(), $hhrr_note_owner);";
	
	if ( db_exec($sql) )
		$AppUI->setMsg( $AppUI->_( 'inserted' ), UI_MSG_OK);	
	else
		$AppUI->setMsg( $AppUI->_( 'updateError' ). db_error(), UI_MSG_ERROR);
}
elseif($accion == "edit_hhrr_note") //Edicion nota
{
	$hhrr_note_id=$_POST['hhrr_note_id'];
	if ( $noteType == NOTE_INTERVIEW )
		$hhrr_note=$_POST['interviewcomments'];
	else
		$hhrr_note=$_POST['comments'];
	
	$sql = "UPDATE hhrr_notes SET hhrr_note='$hhrr_note' WHERE hhrr_note_id = $hhrr_note_id;";
	
	if ( db_exec($sql) )
		$AppUI->setMsg( $AppUI->_( 'updated' ), UI_MSG_OK);	
	else
		$AppUI->setMsg( $AppUI->_( 'updateError' ).db_error(), UI_MSG_ERROR);
}

$AppUI->redirect();
?>
