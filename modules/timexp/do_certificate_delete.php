<?php
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;


$sql="select license_has_attachments from timexp_licenses where
	license_id = ".$license_id;
$result=mysql_query($sql);
$row=mysql_fetch_array($result);

$certificate_id=$_REQUEST["id"];

$sql="select certificate_name, certificate_related_license from timexp_licenses_certificates where
		certificate_id = ".$certificate_id;
$result=mysql_query($sql);
$row=mysql_fetch_array($result);

$license_id=$row['certificate_related_license'];

if(!verify_user_id($license_id)){
	$AppUI->setMsg( "Access Denied", UI_MSG_ERROR );
	$AppUI->redirect(); 
}

$msg = NULL;
$upload_dir = $AppUI->getConfig('root_dir').'/files/timexp/licenses/';

if(unlink($upload_dir.$row["certificate_name"])){
	$sql="delete from timexp_licenses_certificates where
			certificate_id = ".$certificate_id;
			
	$msg = mysql_query($sql);
	
	$certificates_count=$row['license_has_attachments'];
	$certificates_count--;

	$sql="update timexp_licenses set license_has_attachments = ".$certificates_count." where
	license_id = ".$license_id;
	mysql_query($sql);

	if ($msg){
		$AppUI->setMsg( 'deleted' , UI_MSG_OK, true );		
	 	$AppUI->redirect(); 
	}else{
		$AppUI->setMsg( "error", UI_MSG_ERROR );
	  	$AppUI->redirect();
	}
}else{
	$AppUI->setMsg( "error", UI_MSG_ERROR );
  	$AppUI->redirect();
}

//------------------------------------------------------------------------------------------------
 
function verify_user_id( $id_licencia ){
global $AppUI;
		
$sql="select license_creator from timexp_licenses where license_id = ".$id_licencia;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$user_id=$row['license_creator'];

if($user_id == $AppUI->user_id) { return true; }
else { return false; }
}

?>