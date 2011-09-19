<?php
ob_start();

$dPconfig = array();
include_once( "./includes/config.php" );

if(! is_file( "./includes/mktime_bug/mktime_difference.php" )){
        include_once("./includes/mktime_bug/mktime_update_difference.php");
}

require_once( "./classes/ui.class.php" );
require_once( "./includes/main_functions.php" );

$cookie_name = 'psa';
$dPconfig['version'] = "1.0";

session_name( 'psa'.$dPconfig['instanceprefix'] );

if (get_cfg_var( 'session.auto_start' ) > 0) {
	session_write_close();
}
session_start();
session_register( 'AppUI' ); 

// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

// Check that the user has correctly set the root directory
is_file( "{$dPconfig['root_dir']}/includes/config.php" ) or die( "FATAL ERROR: Root directory in configuration file probably incorrect." );
  
// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
	if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout']))
		$user_log_id = $AppUI->user_log_id;
	$AppUI = new CAppUI();	
    $_SESSION['AppUI'] = new CAppUI();
}

$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();

$iniplace = $AppUI->getPlace();
$inistate = $AppUI->state;

// load the commonly used classes
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getSystemClass( 'userlogs' ) );
require_once( $AppUI->getSystemClass( 'pager' ) );
require_once( $AppUI->getSystemClass( 'libmail' ) );

// load the db handler
require_once( "./includes/db_connect.php" );
require_once( "./misc/debug.php" );


if ($AppUI->user_id < 0)
{   
	if ($_POST['username'] == ""){
	$user = base64_decode($HTTP_COOKIE_VARS[$cookie_name]);
	$cookie = explode(":", $user); 
    
	$username = $cookie[0];
	$password = $cookie[1];
	
	}else{
		$username = $_POST['username'];
		$password = $_POST['password'];
		$savecookie = dPgetParam( $_REQUEST, 'savecookie', '' );
	}
	
	$ok = $AppUI->login( $username, $password );
	
	
	if (!$ok) {
			@include_once( "./locales/core.php" );
			$AppUI->setMsg( 'Login Failed' );
									if($rawlogin=="true"){
										echo "failed";die();
									}
	
			$user = db_loadResult("select user_id from users where user_username = '$username'");
			loguserevent(3, $user);                
		}		//PARA WEBTRACKING
		else{
			
			if ($savecookie){
			$data_user = base64_encode("$username:$password");
			setcookie($cookie_name,$data_user,time()+86400*365);
			}
			
			loguserevent(1);  
			$debug_err_msg = "";
			require_once( './modules/webtracking/core.php' );
			$f_username		= $username;
			$f_password		= $password;
			$f_perm_login           = gpc_get_bool( 'perm_login' );
			
			$bt_login = auth_attempt_login( $f_username, $f_password, $f_perm_login );
		
			if (!$bt_login){
				$fp_debug = @fopen("./files/temp/webtracking.log","a+");
				@fwrite($fp_debug, date("Y-m-d H:i:s")."\t");
				@fwrite($fp_debug, sprintf("%-20s", $f_username)."\t");
				@fwrite($fp_debug, sprintf("%-6s",($bt_login?"true":"false"))."\t");
				@fwrite($fp_debug, $debug_err_msg."\n");
				@fclose($fp_debug);
			}
							if($rawlogin=="true"){
								include_once("modules/".$m."/".$a.".php");
								die();
							}
							
		}
	
}

// load module based locale settings
@include_once( "./locales/$AppUI->user_locale/locales.php" );
@include_once( "./locales/core.php" );

@include_once( "./functions/" . $m . "_func.php" );

// load default preferences if not logged in
if ($AppUI->doLogin()) {
    $AppUI->loadPrefs( 0 );
}

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

// check is the user needs a new password
if (dPgetParam( $_POST, 'lostpass', 0 )) {
	
	$AppUI->setUserLocale();
	@include_once "./locales/$AppUI->user_locale/locales.php";
	@include_once "./locales/core.php";
	setlocale( LC_TIME, $AppUI->user_lang );
	if (dPgetParam( $_REQUEST, 'sendpass', 0 )) {
		require  "./includes/sendpass.php";
		sendNewPass();
	} else {
		require  "./style/$uistyle/lostpass.php";
	}
	exit();
}

if ($AppUI->user_id > 0)
{
if (is_file("./modules/$m/" . "ajax.php")) include_once( "./modules/$m/" . "ajax.php" );
@include_once( "./locales/core.php" );
@include_once( "./modules/$m/" . ($u ? "$u/" : "") . "$u.class.php" );
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

if ($_GET['inc']) $inc=$_GET['inc'];
elseif ($_POST['inc']) $inc=$_POST['inc'];
include ($inc);
}else{
	$AppUI->setUserLocale();
	require "./style/$uistyle/login.php";
	
}



?>