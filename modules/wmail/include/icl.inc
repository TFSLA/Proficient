<?php
/********************************************************
	include/icl.inc
	
	(C)Ryo Chijiiwa <Ryo@IlohaMail.org> 2002 

	This file is part of IlohaMail, and released under GPL.
	See COPYING, or http://www.fsf.org/copyleft/gpl.html
	
	PURPOSE:
		IlohaMail Client Library.  Load imap.inc or pop3.inc

********************************************************/

if (!isset($port)) $port = 143;

$colon_pos = strpos($host, ":");
if ($colon_pos!==false){
	$port = substr($host, $colon_pos+1);
	$host = substr($host, 0, $colon_pos);
}else if (empty($port)) $port = 143;

$slash_pos = strpos($host, "/");
if ($slash_pos!==false){
	$protocol = strtoupper(substr($host, 0, $slash_pos));
	$host = substr($host, $slash_pos + 1);
}else{
	if ($port==110) $protocol = "POP3";
	else if ($port==143) $protocol = "IMAP";
}


if ($protocol=="IMAP"){
    $ICL_CAPABILITY["folders"] = true;
    $ICL_CAPABILITY["search"] = true;
    $ICL_CAPABILITY["radar"] = true;
	$ICL_CAPABILITY["calendar"] = true;
	$ICL_CAPABILITY["flags"] = true;
	include("./modules/wmail/include/imap.inc");
}else if ($protocol=="POP3"){
    $ICL_CAPABILITY["folders"] = false;
    $ICL_CAPABILITY["search"] = false;
    $ICL_CAPABILITY["radar"] = false;
	$ICL_CAPABILITY["calendar"] = true;
	$ICL_CAPABILITY["flags"] = false;
	include("./modules/wmail/include/pop3.inc");
}

?>