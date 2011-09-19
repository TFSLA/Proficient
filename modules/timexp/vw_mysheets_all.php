<?php

global $timexp_type, $timexp_types;

$AppUI->savePlace(); 

foreach($timexp_types as $ta_id => $ta_dsc){
	$timexp_type = $ta_id;
	include("vw_mysheets.php");
}

include("vw_mysheets_licenses.php");
?>