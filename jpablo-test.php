<?php




$license_key="a010a2aabb1f83fbc2b75a2aa494e89b";                                                // Md5('nombre de la empresa')

include ('includes/license.php');

error_reporting( E_ALL & ~E_NOTICE);




is_file( "./includes/config.php" )

       or die( "Fatal Error.  You haven't created a config file yet." );




// required includes for start-up

$dPconfig = array();

require_once( "./includes/config.php" );

if(! is_file( "./includes/mktime_bug/mktime_difference.php" )){

        include_once("./includes/mktime_bug/mktime_update_difference.php");

}

require_once( "./classes/ui.class.php" );

require_once( "./includes/main_functions.php" );




// Check that the user has correctly set the root directory

is_file( "{$dPconfig['root_dir']}/includes/config.php" ) or die( "FATAL ERROR: Root directory in configuration file probably incorrect." );




$AppUI = new CAppUI();        

$_SESSION['AppUI'] = new CAppUI();

$AppUI =& $_SESSION['AppUI'];

$AppUI->setConfig( $dPconfig );

$AppUI->checkStyle();

// load the commonly used classes

require_once( $AppUI->getSystemClass( 'date' ) );

require_once( $AppUI->getSystemClass( 'dp' ) );

require_once( $AppUI->getSystemClass( 'userlogs' ) );

require_once( $AppUI->getSystemClass( 'pager' ) );

require_once( $AppUI->getSystemClass( 'libmail' ) );

// load the db handler

require_once( "./includes/db_connect.php" );

require_once( "./misc/debug.php" );

include("./includes/permissions.php");




    $AppUI->login("jpvillaverde","",false);

    //include("includes/loadperms.php");

    require_once("./includes/permissions.php");

    // Procesa solo si tiene permisos

    if(!getDenyRead("todo"))echo "Usuario con permisos";

    else echo "Usuario sin permisos"




?>

