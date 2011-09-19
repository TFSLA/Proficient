<?php
ob_start();

$dPconfig = array();
include_once( "../includes/config.php" );

require_once( "../classes/ui.class.php" );
require_once( "../includes/main_functions.php" );

session_name( 'psa'.$dPconfig['instanceprefix']);
$AppUI=session_get_cookie_params();
session_register( 'AppUI' ); 


require_once( "../includes/db_connect.php" );



if ($_GET['id']!=0){
	if ($_GET['mod']=='1'){
		$sql="SELECT fbin_data, fname, ftype FROM companies WHERE company_id=";
	}
	$sql=$sql.$_GET['id'];
	//echo $sql;
	$rc=db_exec($sql);
	$vec=db_fetch_array($rc);
	$fdata = $vec['fbin_data'];
	$ftype = $vec['ftype'];
	$fname = $vec['fname'];
	
	Header( "Content-type: $ftype");
	Header("Content-Disposition: attachment; filename=$fname");
			echo $fdata;
	
	}
?>