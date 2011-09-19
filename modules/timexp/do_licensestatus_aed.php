<?php
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;

$msg = NULL;
$description = $_REQUEST["license_comment"]; 

while(LIST($license_id,$next_status)=EACH($_POST["licensestatus_status"])) {
	
	if(!verify_supervisor_id($license_id)){
			$AppUI->setMsg( 'Access Denied', UI_MSG_ERROR );
  			$AppUI->redirect();
  			break;
	}
	
	//si cambia el estado
	if ($next_status=="2" || $next_status=="3"){
		$date = date("Y-m-d H:i:s");
		
		$sql="update timexp_licenses set license_status = 
		".$next_status.", license_supervisor_comment = '".@$description[$license_id]."',
		license_approval_date = '".$date."', license_modified_by = $AppUI->user_id
		where license_id = ".$license_id;
		
		$msg = mysql_query($sql);
		
		if(!msg){ break; }
		
		$sql="select * from timexp_licenses where license_id = ".$license_id;
		$result = mysql_query($sql);
		$license_data=mysql_fetch_array($result);
		$user_id=$license_data['license_creator'];
		
		if(!$user_id) {
			$AppUI->setMsg( 'error', UI_MSG_ERROR );
  			$AppUI->redirect();
  			break;
		}
		
		$sql="select user_email from users where user_id = ".$user_id;
		$result = mysql_query($sql);
		$user_data=mysql_fetch_array($result);
		
		if(!$user_data) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
  			$AppUI->redirect();
  			break;
		}

		if(!notify_user($user_id,$license_id,$next_status,$description[$license_id])){
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
  			$AppUI->redirect(); 
  			break;
		}
		
		$sql = "select * from timexp_licenses_types where license_type_id = ".$license_data['license_type'];
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
			
		$license_type_desc = $row['license_type_description_'.$AppUI->getConfig('host_locale')];
		
		if($next_status == 3){
			$sql="insert into calendar_exclusions (user_id, from_date, to_date, description)
			VALUES (".$user_id.", '".$license_data['license_from_date']."', '".
			$license_data['license_to_date']."', '".$license_type_desc."')";
			$result = mysql_query($sql);
		}
	}
}

if ($msg){
	$AppUI->setMsg( 'updated' , UI_MSG_OK, true );		
 	$AppUI->redirect(); 
}else{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
  	$AppUI->redirect();
}

//------------------------------------------------------------------------------------------------
  
function notify_user( $id_user , $id_licencia , $next_status , $supervisorComment ){	
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

if ($next_status=="3"){ 
	$status_name=$AppUI->_to($user_language,"Approved"); 
	$supervisionDateMsg="Approvation Date";
}
else { 
	$status_name=$AppUI->_to($user_language,"Disapproved"); 
	$supervisionDateMsg="Disaprovation Date";
}

$title = "[PSA] ".$AppUI->_to($user_language,"LicenseStatusChanged")." ".$AppUI->_to($user_language,"by");
$title .= " ".$AppUI->user_first_name." ".$AppUI->user_last_name;

$sql = "select * from timexp_licenses_types where license_type_id = ".$license_data['license_type'];
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$license_type = $row['license_type_description_'.$user_language];

$msgBody=@"\n".
$AppUI->_to($user_language,"LicenseStatusChangedMSG").$status_name."\n".
"\n".str_repeat("=",70)."\n".
$AppUI->getConfig('base_url')."/index.php?m=timexp&a=viewlicense&license_id=".$license_data['license_id'].
"\n".str_repeat("=",70)."\n".
$AppUI->_to($user_language,"License Details").
"\n".str_repeat("=",70)."\n".
$AppUI->_to($user_language,"Type").": ".$license_type."\n".
$AppUI->_to($user_language,"From Date").": ".$startdate->format($df)."\n".
$AppUI->_to($user_language,"To Date").": ".$enddate->format($df)."\n\n".
$AppUI->_to($user_language,"Comments").": ".$license_data['license_description']."\n\n\n".
str_repeat("=",70)."\n".
$AppUI->_to($user_language,$supervisionDateMsg).": ".$date->format($df)."\n".
$AppUI->_to($user_language,"Supervisor Comments").": ".$supervisorComment."\n".
str_repeat("=",70)."\n\n";

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