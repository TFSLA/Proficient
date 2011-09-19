<?php 
ob_start();

//error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING );

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
error_reporting( E_ALL & ~E_NOTICE);
//error_reporting( E_ALL);

$ini =  explode(" ",microtime());
$ini = $ini[1] + $ini[0];


is_file( "./includes/config.php" )
	or die( "Fatal Error.  You haven't created a config file yet." );

// required includes for start-up
$dPconfig = array();
require_once( "./includes/config.php" );
require_once( "./classes/ui.class.php" );
require_once( "./includes/main_functions.php" );

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = dPgetParam( $_GET, 'suppressHeaders', false );

// manage the session variable(s)


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

// Do not change version for support reasons
$dPconfig['version'] = "1.0";

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
	if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout']))
		$user_log_id = $AppUI->user_log_id;
		
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


// load the db handler
require_once( "./includes/db_connect.php" );
require_once( "./misc/debug.php" );


//record logout
if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout'])){
	loguserlogout($user_log_id);
}
	
// load default preferences if not logged in
if ($AppUI->doLogin()) {
    $AppUI->loadPrefs( 0 );
}

// check if the user is trying to log in
if (isset($_POST['login']) || isset($_GET['login'])) {
	$username = dPgetParam( $_REQUEST, 'username', '' );
	$password = dPgetParam( $_REQUEST, 'password', '' );
	$redirect = dPgetParam( $_REQUEST, 'redirect', '' );
	$ok = $AppUI->login( $username, $password );
	if (!$ok) {
		@include_once( "./locales/core.php" );
		$AppUI->setMsg( 'Login Failed' );
                if($rawlogin=="true"){
                  echo "failed";die();
                }

    $user = db_loadResult("select user_id from users where user_username = '$username'");
    loguserevent(3, $user);                
	}
        //PARA WEBTRACKING
	else{
		loguserevent(1);  
		
		require_once( 'modules/webtracking/core.php' );
		$f_username		= $username;
		$f_password		= $password;
		$f_perm_login           = gpc_get_bool( 'perm_login' );

		auth_attempt_login( $f_username, $f_password, $f_perm_login );
	 
             if($rawlogin=="true"){
               include_once("modules/".$m."/".$a.".php");
               die();
             }
             
	}
	//PARA WEBTRACKING, FIN
	$AppUI->redirect( "$redirect" );
}

// supported since PHP 4.2
// writeDebug( var_export( $AppUI, true ), 'AppUI', __FILE__, __LINE__ );

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

// clear out main url parameters
$m = '';
$a = '';
$u = '';


// check if we are logged in
if ($AppUI->doLogin()) {
    $AppUI->setUserLocale();
	// load basic locale settings
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );

	$redirect = @$_SERVER['QUERY_STRING'];
	if (strpos( $redirect, 'logout' ) !== false) {
		$redirect = '';
	}
	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}

// bring in the rest of the support and localisation files
require_once( "./includes/permissions.php" );

// set the module and action from the url
/*
$m = dPgetParam( $_GET, 'm', getReadableModule() );
$u = dPgetParam( $_GET, 'u', '' );
$a = dPgetParam( $_GET, 'a', 'index' );
*/

$u = dPgetParam( $_GET, 'u', '' );
$m = dPgetParam( $_GET, 'm', 'nomrecord' );
if($m=="nomrecord"){
  $hpstring = $AppUI->getPref( 'HOMEPAGE' )? $AppUI->getPref( 'HOMEPAGE' ) : "m=dashboard&a=viewdb";
	$vars = split ('&', $hpstring);
	$i = 0;
	while ($i < count ($vars)) {
		$b = split ('=', $vars[$i]);
		$$b[0] = $b[1];
		$i++;
	}
//	if(!IsSet($a))

}
else
  $a = dPgetParam( $_GET, 'a', 'index' );




@include_once( "./functions/" . $m . "_func.php" );

// TODO: canRead/Edit assignements should be moved into each file

// check overall module permissions
// these can be further modified by the included action files
$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );
$canAuthor = $canEdit;
$canDelete = $canEdit;
 
// load module based locale settings
@include_once( "./locales/$AppUI->user_locale/locales.php" );
@include_once( "./locales/core.php" );


if ( !$suppressHeaders ) {
	// output the character set header
	if (isset( $locale_char_set )) {
		header("Content-type: text/html;charset=$locale_char_set");
	}
}

/*
 * 
 * TODO: Permissions should be handled by each file.
 * Denying access from index.php still doesn't asure
 * someone won't access directly skipping this security check.
 * 
// bounce the user if they don't have at least read access
if (!(
	  // however, some modules are accessible by anyone
	  $m == 'public' ||
	  ($m == 'admin' && $a == 'viewuser')
	  )) {
	if (!$canRead) {
		$AppUI->redirect( "m=public&a=access_denied" );
	}
}
*/

// include the module class file
if (is_file($AppUI->getModuleClass( $m ) ))
	include_once( $AppUI->getModuleClass( $m ) );
@include_once( "./modules/$m/" . ($u ? "$u/" : "") . "$u.class.php" );

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset( $_REQUEST["dosql"]) ) {
    //require("./dosql/" . $_REQUEST["dosql"] . ".php");
    require ("./modules/$m/" . $_REQUEST["dosql"] . ".php");
}

// start output proper
include "./style/$uistyle/overrides.php";
ob_start();
if(!$suppressHeaders) {
	require "./style/$uistyle/header.php";
}
require ("./modules/$m/" . ($u ? "$u/" : "") . ($a ? "$a": "index").".php");

/*
if ($m=="timexp" && !in_array($a, array("addedittime", "addeditexpense", "vw_unassigned_timexps") ) ){
	require ("./modules/$m/popup.php");
}
*/

if(!$suppressHeaders) {
	require "./style/$uistyle/footer.php";
}

// si se han guardado los permisos de pm en la sesión los borro
if (isset($AppUI->pmPermissions)){
	unset($AppUI->pmPermissions);
}

$fin = explode(" ",microtime());
$fin =  $fin[1] + $fin[0];
$tiempo = $fin - $ini;



//registro el ultimo acceso de el usuario a la sessión
loguseruse();


/*
echo "<pre>";
echo "<br>Generada en ".strval(round($tiempo * 10000) / 10000)." segundos";
echo "<br>Iniplace: $iniplace"."<br>State: ";
var_dump($inistate);

echo "<br>Finplace: ".$AppUI->getPlace()."<br>State: ";
var_dump($AppUI->state);
echo "</pre>";
*/
//session_register( 'AppUI' ); 




ob_end_flush();
?>
