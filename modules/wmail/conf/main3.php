<?

include_once("./modules/wmail/include/header.inc.php");


// session not started yet
if (!isset($session) || (empty($session))){	
    //figure out lang
    if (strlen($int_lang)>2){
        //lang selected from menu (probably)
        $lang = $int_lang;
    }else{
        //default, or non-selection
        $lang = (isset($default_lang)?$default_lang:"eng/");
    }
    include_once("./modules/wmail/conf/defaults.inc");

                    echo $error;
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
	

	include_once("./modules/wmail/include/optionfolders.inc.php");
}


	?>
