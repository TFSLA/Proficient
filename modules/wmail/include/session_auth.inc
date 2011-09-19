<?php

/********************************************************
	include/session_auth.inc
	
	(C)Copyright 2000-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>

	This file is part of IlohaMail, and released under GPL.
	See COPYING, or http://www.fsf.org/copyleft/gpl.html
	
	PURPOSE:
		Shell for session_auth.backend.inc

********************************************************/
global $AppUI;

include("./modules/wmail/conf/conf.inc");


//sanitize session ID
$user = eregi_replace("[^0-9-]", "", $user);

//time out
if (!$STAY_LOGGED_IN){
	$session_parts = explode("-", $user);
	$in_time = $session_parts[0];
	$valid_time = time() - $MAX_SESSION_TIME;
	if ($in_time < $valid_time){
		echo $AppUI->_("Session timeout").".  ". $AppUI->_("Please log out").".";
		if (!$do_not_die) exit;
	}
}

//determine backend
$sa_backend = $backend;
include("./modules/wmail/conf/db_conf.php");
if ($backend!="FS"){
	if (empty($DB_SESSIONS_TABLE)) $sa_backend = "FS";
	else $sa_backend = "DB";
}

//get session ID
$session_cookie = false;
if (!empty($ILOHAMAIL_SESSION)){
	$user = $ILOHAMAIL_SESSION;
	$session_cookie = true;
}

//do it...

include("./modules/wmail/include/session_auth.".$sa_backend.".inc");

if($AppUI->user_locale=="en") $my_prefs["lang"]="eng/";
if($AppUI->user_locale=="es") $my_prefs["lang"]="es/";


//theme stuff
if (empty($my_prefs["theme"])) $my_prefs["theme"] = "default";
include("./modules/wmail/themes/".$my_prefs["theme"]."/override.inc");

//remove session ID if cookies are used
if ($session_cookie) $user = "";

header("Content-Type: text/html; charset=".$my_prefs["charset"]);

?>