<?php
  
  $sql = "update timexp_licences set licence_status = ".$_REQUEST['set_status']."
  			where licence_id = ".$_REQUEST['licence_id'];
  
  if(db_exec($sql)){   
	/*$msgBody = 
	@"\n\n".
	$AppUI->_("From").": ".$_REQUEST['start_date'].
	"\n".
	$AppUI->_("To").": ".$_REQUEST['end_date'].				TERMINAR ESTO
	"\n\n".
	$AppUI->_("Type").": ".$_REQUEST['licenceType'].
	"\n\n".
	$AppUI->_("Owner").": ".$_REQUEST['user_names'].
	"\n\n".
	$AppUI->_("Comments").": ".$_REQUEST['comentarios'].
	"\n\n".
	"Link: ".$AppUI->getConfig('base_url')."/index.php?m=timexp&a=viewlicence&licence_id=".$id_licencia;

	$sql="select user_email from users where user_id = ".$row['user_supervisor'];
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
    mail($row['user_email'], $AppUI->_("NewLicenceNotificationTitle"),$msgBody, 'From: '.$AppUI->getConfig('mailfrom'));*/
  	$AppUI->redirect("m=timexp&a=viewlicence&licence_id=".$_REQUEST['licence_id']."&submitted=1"); 
  }
  else 
  {
  	$AppUI->redirect("m=timexp&a=viewlicence&licence_id=".$_REQUEST['licence_id']."&error=1"); 
  }

?>