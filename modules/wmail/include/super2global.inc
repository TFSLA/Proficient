<?php
/********************************************************
	include/super2global.inc
	
	(C)Ryo Chijiiwa <Ryo@IlohaMail.org> 2002 

	This file is part of IlohaMail, and released under GPL.
	See COPYING, or http://www.fsf.org/copyleft/gpl.html
	
	PURPOSE:
		Convert super globals (in PHP > 4.1) to globals.

********************************************************/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

if (isset($_GET)){
	while ( list($var, $val) = each($_GET) ) $$var = $val;
}

if (isset($_POST)){
	while ( list($var, $val) = each($_POST) ) $$var = $val;
}

if (isset($_FILES)){
	while ( list($n, $val) = each($_FILES) ){
		$$n = $_FILES[$n]['tmp_name'];
		$var = $n."_name";
		$$var = $_FILES[$n]['name'];
		$var = $n."_size";
		$$var = $_FILES[$n]['size'];
		$var = $n."_type";
		$$var = $_FILES[$n]['type'];
	}
}

if (!empty($_COOKIE["ILOHAMAIL_SESSION"])){
	$ILOHAMAIL_SESSION = $_COOKIE["ILOHAMAIL_SESSION"];
	$user = $ILOHAMAIL_SESSION;
	$session = $user;
}

include_once("./modules/wmail/include/langs.inc");

//sanitize int_lang...just in case
if (isset($int_lang)){
	if (empty($languages[$int_lang])) $int_lang = "";
}

// validate $lang, set to default
// auth_session should set it to $my_prefs["lang"]
if (empty($languages[$lang])){
	if ((!empty($int_lang)) && ($languages[$int_lang])) $lang = $int_lang;
	else $lang = "eng/";
}
?>