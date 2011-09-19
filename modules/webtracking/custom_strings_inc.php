<?php 
global $AppUI;


if($AppUI->user_locale=="es"){
	$s_status_enum_string = 
		'10:nuevo,20:se necesitan mas datos,25:mas datos enviados,30:aceptado,40:confirmado,50:asignado,80:resuelto,90:cerrado';
	$s_updated_bug_button = "enviar mas datos"; 
	$s_updated_bug_title = "Enviar mas datos a incidencia"; 
	$s_email_notification_title_for_status_bug_updated = 
		"La siguiente incidencia ha sido actualizada con MAS DATOS."; 	
	$s_email_updated_msg = "La siguiente incidencia ha sido actualizada con MAS DATOS."; 	
} 

if($AppUI->user_locale=="en"){
	
	$s_status_enum_string = 
		'10:new,20:feedback,25:feedback submitted,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'; 
	$s_updated_bug_button = "Submit feedback"; 
	$s_updated_bug_title = "Submitted feedback"; 
	$s_email_notification_title_for_status_bug_updated = "The feedback request of the following bug was SUBMITTED."; 
	$s_email_updated_msg = "The feedback request of the following bug was SUBMITTED."; 
} 

?>