<?
global $xa;

$canRead = !getDenyRead( $m  );
$canEdit = !getDenyEdit( $m  );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

if($xa=="contacts_popup"){
  include("modules/wmail/contacts_popup.php");
  die();
}

include_once("./modules/wmail/include/header.inc.php");
//include_once("./modules/wmail/include/optionfolders.inc.php");

include_once("./modules/wmail/include/encryption.inc");
include_once("./modules/wmail/include/version.inc");
include_once("./modules/wmail/include/langs.inc");
include_once("./modules/wmail/conf/conf.inc");
include_once("./modules/wmail/conf/login.inc");
include_once("./modules/wmail/include/javascript.inc");
//set content type header
if (empty($int_lang))
	$int_lang = ($AppUI->user_locale == "en" ?$AppUI->user_locale."g":$AppUI->user_locale)."/";
if (!empty($int_lang)){
	include_once("./modules/wmail/lang/".$int_lang."init.inc");
}else{
	include_once("./modules/wmail/lang/".$default_lang."init.inc");
}
header("Content-type: text/html; charset=".$lang_charset);

$authenticated = false;

$cookie_session = $AppUI->getState( 'wmail_session' ) ? $AppUI->getState( 'wmail_session' ) : NULL;
if (!isset($session) || (empty($session))){	
	if ($cookie_session !== NULL)
		$session = $cookie_session;
}

// session not started yet
if (!isset($session) || (empty($session))){	
    $fromlogin=true;
    //figure out lang
    if (strlen($int_lang)>2){
        //lang selected from menu (probably)
        $lang = $int_lang;
    }else{
        //default, or non-selection
        $lang = (isset($default_lang)?$default_lang:"eng/");
    }
    include_once("./modules/wmail/conf/defaults.inc");

    //validate host
    if (isset($host)){
        //validate host
        if (!empty($default_host)){
            if (is_array($default_host)){
                if (empty($default_host[$host])){
                    $host="";
                    $error .= $loginErrors[0]."<br>\n";
                }
            }else{
                if (strcasecmp($host, $default_host)!=0){
                    $host="";
                    $error .= $loginErrors[0]."<br>\n";
                }
            }
        }
    }
                    echo $error;
	//auto append
	if ((empty($error)) && (is_array($AUTO_APPEND)) && (!empty($AUTO_APPEND[$host]))){
		if (strpos($user, $AUTO_APPEND[$host])===false) $user .= $AUTO_APPEND[$host];
	}

	//attempt to initiate session
	$sql="SELECT * FROM users WHERE user_id = $AppUI->user_id;";
	$users = db_loadList( $sql );

	if($users[0]["user_webmail_autologin"]=="Yes"){
	  $user     = $users[0]["user_email_user"];
	  $password = $users[0]["user_email_password"];
	  $host     = $default_host;
	}
	if ((isset($user))&&(isset($password))&&(isset($host))){

		if ((!isset($port))||(empty($port))) $port = $default_port;
		include("./modules/wmail/include/icl.inc");
		$user_name = $user;
		
		//first, authenticate against server
		$iil_conn=iil_Connect($host, $user, $password, $AUTH_MODE);
		if ($iil_conn){
			//run custom authentication code
            include("./modules/wmail/conf/custom_auth.inc");
            
			//if successful, start session
            if (empty($error)){
				if ((!isset($port))||(empty($port))) $port = $default_port;
                include("./modules/wmail/include/write_sinc.inc");
                if ($new_user){
                    include("./modules/wmail/conf/new_user.inc");
					$new_user = 1;
                }else{
					$new_user = 0;
				}
				$authenticated = true;
			}
            
			iil_Close($iil_conn);
			if (isset($session))
				$AppUI->setState ('wmail_session', $session);
		}else{
			$error = $iil_error."<br>";
		}
		
		//make log entry
		$log_action = "log in";
		include("./modules/wmail/include/log.inc");
	}
}


// valid session
$login_success = false;
if ((isset($session)) && ($session != "")){
	$user=$session;
	
	//set session cookie
	/*
	if ($SESSION_COOKIES){
		setcookie("ILOHAMAIL_SESSION", $user);
		$ILOHAMAIL_SESSION = $user;
    }
	*/
	
    //auth and load session data
	include("./modules/wmail/include/session_auth.inc");
	include("./modules/wmail/conf/defaults.inc");
	
	//authenticate
	if (!$authenticated){
		include_once("./modules/wmail/include/icl.inc");
		$conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
		if ($conn){
			iil_Close($conn);
		}else{
			echo "Authentication failed.";
			exit;
		}
	}

	//do prefs (posted from "Prefs" pane so that changes apply to all frames)
	if ($do_prefs){
		//check charset (change to default if unsupported)
		include_once("./modules/wmail/lang/".$lang."init.inc");
		if (!empty($charset)){ 
			if (!$supported_charsets[$charset]) $charset = $lang_charset;
		}else{
			$charset = $lang_charset;
		}

		//apply changes...
		if (isset($apply)) $update=true;
		if ((isset($update))||(isset($revert))){
			//check rootdir
			if ($rootdir=="-") $rootdir = $rootdir_other;
		
			//initialize $my_prefs
			$my_prefs=$init["my_prefs"];
			
			//if updating, over-write values
			if (isset($update)){
				reset($my_prefs);
 				while (list($key, $value) = each ($my_prefs)) {
					 $my_prefs[$key]=$$key;
					echo "<!-- $key ".$$key." -->\n";
				}
			}
		
			//save prefs to backend
			include("./modules/wmail/include/save_prefs.inc");
		
    	    //display prefs page again
        	$show_page="prefs";
			
			//show error if any
			if (!empty($error)){
				echo "ERROR: $error";
				exit;
			}	
		}
	}
    
	//do pref_colors (posted from "Prefs:Colors" pane so that changes apply to all frames)
	if ($do_pref_colors){
		//apply changes...
		if (isset($apply)) $update=true;
		if ((isset($update))||(isset($revert))){
			//check rootdir
			if ($font_family=="other") $font_family = $font_family_other;
		
			//initialize $my_prefs
			$my_colors=$init["my_colors"];
			
			//if updating, over-write values
			if (isset($update)){
				reset($my_colors);
 				while (list($key, $value) = each ($my_colors)) {
				 	$my_colors[$key]=$$key;
					echo "<!-- $key ".$$key." -->\n";
				}
			}
		
			//save prefs to backend
			include("./modules/wmail/include/save_colors.inc");
		
    	    //display prefs page again
        	$show_page="pref_colors";
			
			//show error...
			if (!empty($error)){
				echo "ERROR: $error";
				exit;
			}	
		}
	}

    //overwrite lang prefs if different
    if ((strlen($int_lang)>2) && (strcmp($int_lang, $my_prefs["lang"])!=0)){
        $my_prefs["lang"] = $int_lang;
        include("./modules/wmail/lang/".$lang."init.inc");
        if ($supported_charsets[$my_prefs["charset"]]!=1) $my_prefs["charset"] = $lang_charset;
        include("./modules/wmail/include/save_prefs.inc");
    }
    
    //figure out which page to load in main frame
	if (($new_user)||($show_page=="prefs")) $main_page = "prefs.php?user=".$session;
	else if ($show_page == "pref_colors") $main_page = "pref_colors.php?user=".$session;
	else {
		$main_page = "main.php";
		$folder="INBOX";
		$user=$session;
	}
	//else $main_page = "main.php?folder=INBOX&user=".$session;
	
	//show document head
	echo $document_head;
	
	//show frames
    if (($my_prefs["list_folders"]==1) && ($port!=110)){
		//...with folder list
		$login_success = true;
		?>
		<FRAMESET ROWS="30,*"  frameborder=no border=0 framespacing=0 MARGINWIDTH="0" MARGINHEIGHT="0">
			<FRAMESET COLS="30,*"  frameborder=no border=0 framespacing=0 MARGINWIDTH="0" MARGINHEIGHT="0">
				<FRAME SRC="radar.php?user=	<?php echo $session?>" NAME="radar" SCROLLING="NO" MARGINWIDTH="0" MARGINHEIGHT="0"  frameborder=no border=0 framespacing=0>
				<FRAME SRC="tool.php?user=	<?php echo $session?>" NAME="tool" SCROLLING="NO" MARGINWIDTH="0" MARGINHEIGHT="0"  frameborder=no border=0 framespacing=0>
			</FRAMESET>
			<FRAMESET COLS="	<?php echo $my_prefs["folderlistWidth"]?>,*" frameborder=no border=0 framespacing=0 MARGINWIDTH="0" MARGINHEIGHT="0">
				<FRAME SRC="folders.php?user=	<?php echo $session?>" NAME="list1"  MARGINWIDTH=5 MARGINHEIGHT=5 NORESIZE frameborder=no border=0 framespacing=0>
				<FRAME SRC="	<?php echo $main_page?>" NAME="list2" MARGINWIDTH=10 MARGINHEIGHT=10 FRAMEBORDER=no border=0 framespacing=0>
			</FRAMESET>
		</FRAMESET>
		<?php
	}else if (empty($error)){
		//...without folder list
		$login_success = true;
		$tab=0;
		//$folder="INBOX";

		include_once("./modules/wmail/include/optionfolders.inc.php");
	
		//include("$main_page");
	}
}

//couldn't log in, show login form
if (!$login_success){
	//check for cookie...
	if ($_COOKIE["ILOHAMAIL_SESSION"]){
		$user = "";
		setcookie("ILOHAMAIL_SESSION", "");
	}

	//put together lang options
	$langOptions="<option value=\"--\">--";
	while (list($key, $val)=each($languages)) 
		$langOptions.="<option value=\"$key\" ".(strcmp($key,$int_lang)==0?"SELECTED":"").">$val\n";

	//colors...
	$bgcolor = $default_colors["folder_bg"];
	$textColorOut = $default_colors["folder_link"];
	$bgcolorIn = $default_colors["tool_bg"];
	$textColorIn = $default_colors["tool_link"];
	
	//load lang file
	if (!empty($int_lang)){
		include("./modules/wmail/lang/".$int_lang."login.inc");
	}else{
		include("./modules/wmail/lang/".$default_lang."login.inc");
	}
	
	//set a test cookie
	if ($USE_COOKIES){
		setcookie ("IMAIL_TEST_COOKIE", "test", time()+$MAX_SESSION_TIME, "/", $_SERVER[SERVER_NAME]);
	}
	
	//print document head
	echo $document_head;
	
	echo "\n<!-- \nSESS_KEY: $IMAIL_SESS_KEY $MAX_SESSION_TIME ".$_SERVER[SERVER_NAME]."\nOLD: $OLD_SESS_KEY\n //-->\n";
	?>
	<!--<BODY BGCOLOR="<?php echo $bgcolor?>" TEXT="<?php echo $textColorOut?>" LINK="<?php echo $textColorOut?>" ALINK="<?php echo $textColorOut?>" VLINK="<?php echo $textColorOut?>" onLoad="document.forms[0].user.focus();">-->
	
	
<?

	$titleBlock = new CTitleBlock( "Welcome to webmail", 'webmail.gif', $m, "colaboration.index" );
	
	$titleBlock->show();

?>	
	<p><BR><BR>
	<center>
	<form name="loginform" method="POST" action="index.php?m=wmail">
	<input type="hidden" name="logout" value=0>
	<table border="0" cellspacing="2" cellpadding="0" >
	<tr><td align="center" colspan=2>
	<?php
		include("./modules/wmail/conf/login_title.inc");
        if (!empty($error)) echo "<font color=\"#FFAAAA\">".$error."</font><br>";
	?>
	</td>
	</tr>
	<?
	  $sql="SELECT * FROM users WHERE user_id = $AppUI->user_id;";
	  $users = db_loadList( $sql );
	  $user = $users[0]["user_email_user"];
	?>
	<tr><td align=right><?php echo $loginStrings[0] ?>:</td><td><input type="text" name="user" class="text" value="<?php echo $user; ?>" size=25></td></tr>
	<tr><td align=right><?php echo $loginStrings[1] ?>: </td><td><input type="password" name="password" class="text" value="" size=25 AUTOCOMPLETE="off"></td></tr>
	<?php
		$HTTP_HOST = strtolower($_SERVER["HTTP_HOST"]);
		if (is_array($VDOMAIN_DETECT) && empty($host)){
			$host = $VDOMAIN_DETECT[$HTTP_HOST];
		}
		//empty default host
			//show text box
		//default host is array
			//show list (select $host)
		//default host is string
			//show host
			//don't show host
		if (empty($default_host)){
			echo "<tr><td align=right>".$loginStrings[2].": </td><td><input type=text class=\"text\" name=\"host\" value=\"$host\">&nbsp;&nbsp;</td></tr>";
		}else if (is_array($default_host)){
			echo  "<tr><td align=right>".$loginStrings[2].":</td><td><select class=\"text\" name=\"host\">\n";
			reset($default_host);
			while ( list($server, $name) = each($default_host) ){
				echo "<option value=\"$server\" ".($server==$host?"SELECTED":"").">$name\n";
			}
			echo "</select></td></tr>";			
		}else{
			echo "<input type=hidden name=\"host\" value=\"$default_host\">";
			if (!$hide_host){
				echo  "<tr><td align=right>".$loginStrings[2].": </td><td><b>$host</b>&nbsp;&nbsp;";
				echo "</td></tr>";
			}
		}
			
		//initialize default rootdir and port 
		if ((!isset($rootdir))||(empty($rootdir))) $rootdir = $default_rootdir;
		if ((!isset($port))||(empty($port))) $port = $default_port;
		
		//show (or hide) protocol selection
		if (!$hide_protocol){
			echo "<tr>";
			echo "<td align=\"right\">".$loginStrings[3].": </td>\n<td>";
            echo "<select class=\"text\" name=\"port\">\n";
            echo "<option value=\"143\" ".($port==143?"SELECTED":"").">IMAP\n";
            echo "<option value=\"110\" ".($port==110?"SELECTED":"").">POP3\n";
            echo "</select>\n";
			//echo "<td><input type=\"text\" name=\"port\" value=\"$port\" size=\"4\"></td>";
			echo "</td></tr>\n";
		}else{
			echo "<input type=\"hidden\" name=\"port\" value=\"$default_port\">\n";
		}
		
		//show (or hide) root dir box
		if (!$hide_rootdir){
			echo "<tr>";
			echo "<td align=\"right\">".$loginStrings[4].":</td>";
			echo "<td><input type=\"text\" class=\"text\" name=\"rootdir\" value=\"$rootdir\" size=\"12\"></td>";
			echo "</tr>\n";
		}else{
			echo "<input type=\"hidden\" name=\"rootdir\" value=\"$default_rootdir\">\n";
		}
		
		if (!$hide_lang){
			echo "<tr><td align=right>".$loginStrings[5].": </td><td>\n";
   		 	echo "<select class=\"text\" name=\"int_lang\">\n";
			echo $langOptions;
			echo "</select></td></tr>\n";
		}
	?>
	<tr><td>&nbsp;</td><td align="center"><input type="submit" class="button" value="<?php echo strtolower($loginStrings[6]) ?>">&nbsp;&nbsp;<p> </td></tr>
	</table>
	</form>
	<?php
		include("./modules/wmail/conf/login_blurb.inc");
	?>
	</center>
	<!--</body>-->
	<?php
}
	?>
