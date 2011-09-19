<?php
/********************************************************
	conf/defaults.inc
		
	PRE-CONDITION:
		$lang = Language to use (e.g. "eng/" or "jp/")
	PURPOSE:
		Provide central location for setting default
        values for user options.
********************************************************/
global $message_priorities;
if ((!isset($lang))||(strlen($lang)<3)) $lang="eng/";
include("./modules/wmail/lang/".$lang."init.inc");
if (!isset($lang_charset)) $lang_charset="ISO-8859-1";
if (!isset($rootdir)) $rootdir="";

if (isset($host)){
	$defaults_path="./modules/wmail/conf/defaults.".$host.".inc";
	if (file_exists(realpath($defaults_path))){
		include($defaults_path);
		$included=true;
	}
}

if (!$included){
	include("./modules/wmail/conf/defaults.generic.inc");
}
/*$message_priorities = array(
	"1" => "Low",
	"3" => "Normal",
	"5" => "High"
);*/

$message_priorities = array(
	"1" => $AppUI->_("Low"),
	"3" => $AppUI->_("Normal"),
	"5" => $AppUI->_("High")
);
?>