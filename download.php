<?php
require "./includes/config.php";
require "./classes/ui.class.php";

session_name( 'psa'.$dPconfig['instanceprefix'] );

if (get_cfg_var( 'session.auto_start' ) > 0)
{
	session_write_close();
}

session_start();
session_register( 'AppUI' );

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout']))
{
	if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout']))
		$user_log_id = $AppUI->user_log_id;

    $_SESSION['AppUI'] = new CAppUI();
}

$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

// check if we are logged in
if ($AppUI->doLogin())
{
    $AppUI->setUserLocale();
	// load basic locale settings
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );

	$redirect = @$_SERVER['QUERY_STRING'];
	if (strpos( $redirect, 'logout' ) !== false)
	{
		$redirect = '';
	}

	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}

require "{$AppUI->cfg['root_dir']}/includes/db_connect.php";
include "{$AppUI->cfg['root_dir']}/includes/main_functions.php";
include "{$AppUI->cfg['root_dir']}/classes/dp.class.php";
include "{$AppUI->cfg['root_dir']}/includes/permissions.php";

$file_name = isset($_GET['file_name']) ? $_GET['file_name'] : '';
$real_path = isset($_GET['real_path']) ? $_GET['real_path'] : '';

$real_path = $AppUI->cfg['root_dir']."/".$real_path;

if (!file_exists( $real_path ))
{
	$AppUI->setMsg( "El archivo: {$file_name} ($real_path) NO EXISTE", UI_MSG_ERROR );
	$AppUI->redirect();
}

header("Pragma: ");
header("Cache-Control: ");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Cache-Control: private, max-age=1, pre-check=10");
// END extra headers to resolve IE caching bug


//Hay un bug con IE7 que reemplaza los espacios x _. Con esta linea lo solucionamos
if (browser() == "MSIE")
	 $file_name = str_replace(" ","%20",$file_name);

header("MIME-Version: 1.0");
//header( "Content-length: {$file['file_size']}" );
//header( "Content-type: {$file['file_type']}" );
header( "Content-transfer-encoding: binary\n");
header( "Content-Disposition: attachment; filename=\"{$file_name}\"");
readfile($real_path);

function browser()
{
	if ((ereg("Nav", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Gold", $_SERVER["HTTP_USER_AGENT"])) || (ereg("X11", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Mozilla", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Netscape", $_SERVER["HTTP_USER_AGENT"])) AND (!ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) AND (!ereg("Konqueror", $_SERVER["HTTP_USER_AGENT"])) AND (!ereg("Firefox", $_SERVER["HTTP_USER_AGENT"]))) $browser = "Netscape";
	elseif(ereg("Firefox", $_SERVER["HTTP_USER_AGENT"])) $browser = "FireFox";
	elseif(ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) $browser = "MSIE";
	elseif(ereg("Lynx", $_SERVER["HTTP_USER_AGENT"])) $browser = "Lynx";
	elseif(ereg("Opera", $_SERVER["HTTP_USER_AGENT"])) $browser = "Opera";
	elseif(ereg("WebTV", $_SERVER["HTTP_USER_AGENT"])) $browser = "WebTV";
	elseif(ereg("Konqueror", $_SERVER["HTTP_USER_AGENT"])) $browser = "Konqueror";
	elseif((eregi("bot", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Google", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Slurp", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Scooter", $_SERVER["HTTP_USER_AGENT"])) || (eregi("Spider", $_SERVER["HTTP_USER_AGENT"])) || (eregi("Infoseek", $_SERVER["HTTP_USER_AGENT"]))) $browser = "Bot";
	else $browser = "Other";

	return $browser;
}

?>
