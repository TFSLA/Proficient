<?php

/////////////////////////////////////////////////////////
//	
//	source/login.php
//
//	(C)Copyright 2000-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//		This file is part of IlohaMail.
//		IlohaMail is free software released under the GPL 
//		license.  See enclosed file COPYING for details,
//		or see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	AUTHOR: Ryo Chijiiwa <ryo@ilohamail.org>
	FILE: source/login.php
	PURPOSE:
		Contrary to what the name suggests, this file is loaded at logoff time.
		Includes include/session_close.inc to clean up session data.
	PRE-CONDITIONS:
		$user - Sessiono ID;
	POST-CONDITIONS:
		Should clean all session specific information from records, including cached password.
	COMMENTS:
		For alternate data back-ends, modify include/session_close.inc instead of this file.
********************************************************/
include_once("./modules/wmail/include/super2global.inc");
include("./modules/wmail/conf/conf.inc");

//clobber cookie
if ($_COOKIE["ILOHAMAIL_SESSION"]) setcookie("ILOHAMAIL_SESSION", "");
if ($_COOKIE["IMAIL_SESS_KEY_".$user]) setcookie ("IMAIL_SESS_KEY_".$user, "", time()-3600, "/", $_SERVER[SERVER_NAME]);

if ($logout==1){
		$do_not_die = true;
		$AppUI->setState ('wmail_session', NULL);
		include_once("./modules/wmail/include/session_auth.inc");
		include_once("./modules/wmail/include/icl.inc");
?>
<HTML>
<BODY>
<center><p><br><br><font size=+1><b>Log Out...</b></font></center>
<?php
		//clean up cache on POP3
		iil_ClearCache($loginID, $host);

		//delete any undeleted attachments
		$uploadDir = $UPLOAD_DIR.ereg_replace("[\\/]", "", $loginID.".".$host);
		if (is_dir(realpath($uploadDir))){
			if ($handle = opendir($uploadDir)) {
				while (false !== ($file = readdir($handle))) { 
					if ($file != "." && $file != "..") { 
						$file_path = $uploadDir."/".$file;
						echo $file_path."<br>\n";
						//unlink($file_path);
					} 
				}
				closedir($handle); 
			}
		}	
		
		//delete cache files
		$cacheDir = $CACHE_DIR.ereg_replace("[\\/]", "", $loginID.".".$host);
		if (is_dir(realpath($cacheDir))){
			if ($handle = opendir($cacheDir)) {
				while (false !== ($file = readdir($handle))) { 
					if ($file != "." && $file != "..") { 
						$file_path = $cacheDir."/".$file;
						echo $file_path."<br>\n";
						//unlink($file_path);
					} 
				}
				closedir($handle); 
			}
		}
		
		
		//delete FS session files
		if (is_dir(realpath($SESSION_DIR))){
			if ($handle = opendir($SESSION_DIR)){
				while (false !== ($file = readdir($handle))) {
					$timestamp = time();
					$dash_pos = strpos($file, "-");
					if ($dash_pos!==false) $timestamp = substr($file, 0, $dash_pos);
					if ((is_numeric($timestamp)) 
						&& ((time()-$timestamp) > $MAX_SESSION_TIME)){
						$file_path = $SESSION_DIR."/".$file;
						echo $file_path;
						//unlink($file_path);
					}
				}
				closedir($handle); 
			}
		}
		
		//log entry
		$log_action = "log out";
		$user_name = $loginID;
		include_once("./modules/wmail/include/log.inc");
		
		//close session
		include_once("./modules/wmail/include/session_close.inc");
        
        include("./modules/wmail/conf/login.inc");
        if (empty($logout_url)) $logout_url = "?m=wmail";
		?>
		<script>
			parent.location="<?php echo $logout_url?>";
		</script>
		<?php
}

?>
</BODY></HTML>
