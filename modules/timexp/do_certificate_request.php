<?php
  
$license_id = $_REQUEST['id'];

if(!$license_id) {
	$AppUI->setMsg( 'error', UI_MSG_ERROR );
	$AppUI->redirect(); 
}

if(!verify_supervisor_id($license_id)){
	$AppUI->setMsg( 'Access Denied', UI_MSG_ERROR );
	$AppUI->redirect();
	break;
}


$sql = "select * from timexp_licenses where license_id = ".$license_id;
$result = mysql_query($sql);
$license_data = mysql_fetch_array($result);

if(!$license_data) {
	$AppUI->setMsg( 'error', UI_MSG_ERROR );
	$AppUI->redirect(); 
}

$user_id=$license_data["license_creator"];

if(!sendCertificateRequest($user_id,$license_id)){
	$AppUI->setMsg( "Access Denied", UI_MSG_ERROR );
	$AppUI->redirect(); 
}

$sql = "update timexp_licenses set license_certificate_requested = 1 where license_id = ".$license_id;
mysql_query($sql);

$AppUI->setMsg( 'requested' , UI_MSG_OK, true );	
$AppUI->redirect();

//------------------------------------------------------------------------------------------------

function sendCertificateRequest( $id_user , $id_licencia ){	
global $AppUI;
	
$sql="select user_email from users where user_id = ".$id_user;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$user_email=$row['user_email'];

$sql = "select * from timexp_licenses where license_id = ".$id_licencia;	
$result = mysql_query($sql);
$license_data = mysql_fetch_array($result);

$startdate = new CDate($license_data["license_from_date"]);
$enddate = new CDate($license_data["license_to_date"]);
$date = new CDate(date("Y-m-d H:i:s"));

$usr = new CUser();
$usr->load($id_user);
$prefs = CUser::getUserPrefs($usr->user_id);
$user_language = isset($prefs["LOCALE"]) ? $prefs["LOCALE"] : $AppUI->getConfig("host_locale");
$df = isset($prefs["SHDATEFORMAT"]) ? $prefs["SHDATEFORMAT"] : $AppUI->getPref('SHDATEFORMAT');
//$df = $AppUI->getPref('SHDATEFORMAT');

$strEmailFrom = $AppUI->getConfig("mailfrom");

$Ccompany = new CCompany();
if($Ccompany->load($usr->user_company)){
	if(!empty($Ccompany->company_email)){
		$strEmailFrom = $Ccompany->company_email;
	}
}
	
$sql = "select * from timexp_licenses_types where license_type_id = ".$license_data['license_type'];
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$license_type = $row['license_type_description_'.$user_language];

$msgBody=@"\n".
$AppUI->_to($user_language,"CertificateRequestMSG")."\n".
"\n".str_repeat("=",70)."\n".
$AppUI->getConfig('base_url')."/index.php?m=timexp&a=viewlicense&license_id=".$license_data['license_id'].
"\n".str_repeat("=",70)."\n".
$AppUI->_to($user_language,"License Details").
"\n".str_repeat("=",70)."\n".
$AppUI->_to($user_language,"Type").": ".$license_type."\n".
$AppUI->_to($user_language,"From Date").": ".$startdate->format($df)."\n".
$AppUI->_to($user_language,"To Date").": ".$enddate->format($df)."\n".
$AppUI->_to($user_language,"Comments").": ".$license_data['license_description']."\n\n\n".
str_repeat("=",70);
	
$title = "[PSA] ".$AppUI->_to($user_language,"RequestCertificateTitle");

mail($user_email, $title,$msgBody, 'From: '.$strEmailFrom);
	
return true;
}

//------------------------------------------------------------------------------------------------
  
function verify_supervisor_id( $id_licencia ){	
global $AppUI;
		
$sql="select license_creator from timexp_licenses where license_id = ".$id_licencia;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$user_id=$row['license_creator'];

$sql = "select user_supervisor from users where user_id = ".$user_id;	
$result = mysql_query($sql);
$sup_data = mysql_fetch_array($result);

$supervisor_id = $sup_data['user_supervisor'];

if($supervisor_id == $AppUI->user_id) { return true; }
else { return false; }
}

?>