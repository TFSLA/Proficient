<?php

$path = "/files/timexp/licenses/";
$certificate_id = $_REQUEST['id'];

$sql = "select certificate_name, certificate_related_license from timexp_licenses_certificates where 
		certificate_id = ".$certificate_id;
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$license_id = $row['certificate_related_license'];

if(!verify_id($license_id)){
	$AppUI->setMsg( 'Access Denied', UI_MSG_ERROR );
	$AppUI->redirect();
	break;
}

echo '
<script language="JavaScript">
    	window.location= "'.$path.$row['certificate_name'].'";
</script>' ;

//------------------------------------------------------------------------------------------------
  
function verify_id( $id_licencia ){	
global $AppUI;
		
$sql="select license_creator from timexp_licenses where license_id = ".$id_licencia;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$user_id=$row['license_creator'];

$sql = "select user_supervisor from users where user_id = ".$user_id;	
$result = mysql_query($sql);
$sup_data = mysql_fetch_array($result);

$supervisor_id = $sup_data['user_supervisor'];

if($supervisor_id == $AppUI->user_id || $user_id == $AppUI->user_id) { return true; }
else { return false; }
}

?>