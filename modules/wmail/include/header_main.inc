<?php

/////////////////////////////////////////////////////////
//	
//	include/header_main.inc
//
//	(C)Copyright 2001-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	PURPOSE:
		Provide header and initialization functionality common to many pages.
		Including:
			1.  Display HTML header	
					(including BODY tag with colors set according to user prefs)
			2.  Read user preferences into $my_prefs and $my_colors arrays
	PRE-CONDITIONS:
		$user - Session ID
	COMMENTS:
		Reading preferences into array is actually done in "include/session_auth.inc"

********************************************************/
global $GLOBALS;
$infobox = $GLOBALS["infobox"];
include_once("./modules/wmail/include/nocache.inc");
include_once("./modules/wmail/conf/conf.inc");
include_once("./modules/wmail/conf/db_conf.php");
//disable time limit
if(!ini_get("safe_mode")){
	set_time_limit($MAX_EXEC_TIME);
}

//get current page
$current_page = $_SERVER["PHP_SELF"];
$pos = strrpos($current_page, "/");
if ($pos !== false){
	$current_page = substr($current_page, $pos+1);
}

//setup code for onLoad and onUnload
$onUnLoad = "";
if (strpos($current_page, "compose2.php")!==false) {
	$onUnLoad = " onUnload=\"close_popup();\"";
}
$onLoad = "";
if (strpos($current_page, "contacts_popup.php")!==false) {
	$onLoad = " onLoad=\"acknowledge_popup();\"";
	$onUnLoad = " onUnLoad=\"alert_close();\"";
}

//continue only if valid session ID
if (isset($user)){
	$sid=$user;
	include("./modules/wmail/include/session_auth.inc");
	include("./modules/wmail/lang/".$my_prefs["charset"].".inc");
	include("./modules/wmail/include/ryosimap.inc");


if (!$infobox){
/*	?>
<HTML>
<HEAD>
*/?>
<!--
IlohaMail by Ryo Chijiiwa
http://ilohamail.org
//-->
	<?php
	$linkc=$my_colors["main_link"];
	//$bgc=$my_colors["main_bg"];
	$bgc=$my_colors["main_darkbg"];
	$textc=$my_colors["main_text"];
	$hilitec=$my_colors["main_hilite"];
	$font_size = $my_colors["font_size"];
	
	//$css_url = '<link rel="stylesheet" type="text/css" href="./style/default/main.css" media="all" /><style type="text/css" media="all">@import "./style/default/main.css";</style>';

	/*
	?>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo $my_prefs["charset"]?>">
		<?php */
		 echo $css_url?>
		<?php
		/** include CSS **/
		//include("./modules/wmail/include/css.inc");
		
		/** include JavaScript **/
		include("./modules/wmail/include/javascript.inc");
/*		?>
	</HEAD>
	<?php */
}
// 	echo '<BODY TEXT="'.$textc.'" BGCOLOR="'.$bgc.'" LINK="'.$linkc.'" ALINK="'.$linkc.'" VLINK="'.$linkc.'"'.$onLoad.$onUnLoad.'>';
}else{
//	echo "<BODY>";
	echo "User unspecified:".$user;
	//echo "<script> parent.location=\"login.php\"; </script>";
//	echo "</HTML></BODY>";
	exit;
}
flush();
?>
