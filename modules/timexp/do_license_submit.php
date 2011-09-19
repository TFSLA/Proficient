<?php

  $license_id = $_REQUEST['license_id'];
  
   if(!verify_user_id($license_id)){
		$AppUI->setMsg( 'Access Denied', UI_MSG_ERROR );
		$AppUI->redirect();
   }
  
   $sql = "select user_supervisor 
				from users 
				where user_id = ".$AppUI->user_id;
		
		$user_supervisor = db_loadResult($sql);
   
  $sql = "update timexp_licenses set license_status = ".$_REQUEST['set_status'].
  			", license_send_date = '".date("Y-m-d H:i:s")."', license_sent_to = $user_supervisor
  			where license_id = ".$license_id;

  if(db_exec($sql)){
  		$sql = "select license_creator from timexp_licenses where license_id = ".$license_id;	
  		$result = mysql_query($sql);
  		$row = mysql_fetch_array($result);
  		
 		if(!$row) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
  			$AppUI->redirect(); 
  			break;
		}

		$sql="select user_supervisor from users where user_id = ".$AppUI->user_id;
		$result = mysql_query($sql);
		$row=mysql_fetch_array($result);
		$user_supervisor=$row['user_supervisor'];

		if(!$user_supervisor) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
  			$AppUI->redirect(); 
  			break;
		}
		
		if(!notify_supervisor($user_supervisor, $license_id)){
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
  			$AppUI->redirect(); 
  			break;
		}
		
	  	$AppUI->redirect("m=timexp&a=viewlicense&license_id=".$_REQUEST['license_id']."&submitted=1");
  }
  else
  {
  	$AppUI->redirect("m=timexp&a=viewlicense&license_id=".$_REQUEST['license_id']."&error=1");
  }
  

//------------------------------------------------------------------------------------------------
  
function notify_supervisor( $id_supervisor , $id_licencia ){	
global $AppUI;
	
$sql="select user_email from users where user_id = ".$id_supervisor;
$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$supervisor_email=$row['user_email'];

$sql = "select * from timexp_licenses where license_id = ".$id_licencia;	
$result = mysql_query($sql);
$row = mysql_fetch_array($result);

$startdate = new CDate($row["license_from_date"]);
$enddate = new CDate($row["license_to_date"]);  		
	
$usr = new CUser();
$usr->load($id_supervisor);
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

$title = "[PSA] ".$AppUI->_to($user_language,"NewlicenseNotificationTitle");

$sql = "select * from timexp_licenses_types where license_type_id = ".$row['license_type'];
$result = mysql_query($sql);
$reg = mysql_fetch_array($result);
$license_type = $reg['license_type_description_'.$user_language];

$msgBody = 
@"\n".
$AppUI->_to($user_language,"NewlicenseNotificationTitleMSG").
"\n".str_repeat("=",70)."\n".
$AppUI->getConfig('base_url')."/index.php?m=timexp&a=viewlicense&license_id=".$id_licencia.
"\n".str_repeat("=",70)."\n".
$AppUI->_to($user_language,"License Details").
"\n".str_repeat("=",70)."\n".
$AppUI->_to($user_language,"From Date").": ".$startdate->format($df).
"\n".
$AppUI->_to($user_language,"To Date").": ".$enddate->format($df).
"\n".
$AppUI->_to($user_language,"Type").": ".$license_type.
"\n".
$AppUI->_to($user_language,"Owner").": ".$AppUI->user_last_name.", ".$AppUI->user_first_name.
"\n".
$AppUI->_to($user_language,"Comments").": ".$row['license_description']."\n\n\n".
"\n".str_repeat("=",70)."\n";



mail($supervisor_email, $title,$msgBody, 'From: '.$strEmailFrom);

return true;
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