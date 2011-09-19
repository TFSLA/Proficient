<?php
global $AppUI, $timexp_type, $timexp_types, $timexp_status, $timexp_status_color, $name_sheets;

$msg = NULL;
$upload_dir = $AppUI->getConfig('root_dir').'/files/timexp/licences/';

$sql="select justification_name from timexp_licences_justifications where
		justification_id = ".$_REQUEST["id"];
$result=mysql_query($sql);
$row=mysql_fetch_array($result);

if(unlink($upload_dir.$row["justification_name"])){
	$sql="delete from timexp_licences_justifications where
			justification_id = ".$_REQUEST["id"];
			
	$msg = mysql_query($sql);
	
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
?>