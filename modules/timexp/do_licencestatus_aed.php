<?php
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;

$msg = NULL;

while(LIST($licence_id,$next_status)=EACH($_POST["licencestatus_status"])) {

		$description = $_POST["licence_comment"][$licence_id];

		//si cambia el estado
		if ($next_status=="2" || $next_status=="3"){
			$date = date("Y-m-d H:i:s");

			$sql="update timexp_licences set licence_status = 
			".$next_status.", licence_supervisor_comment = '".$description."',
			licence_approval_date = '".$date."' where licence_id = ".$licence_id;
			
			$msg = mysql_query($sql);
		}
		if ($next_status=="6") {
			//MANDAR MAIL
			//mail($row['user_email'], $AppUI->_("NewLicenceNotificationTitle"),$msgBody, 'From: '.$AppUI->getConfig('mailfrom'));
		}
	}

if ($msg){
	$AppUI->setMsg( 'updated' , UI_MSG_OK, true );		
 	$AppUI->redirect(); 
}else{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
  	$AppUI->redirect();
}
?>