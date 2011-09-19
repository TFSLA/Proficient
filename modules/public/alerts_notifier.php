<?php
ob_start();

error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING );

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
error_reporting( E_ALL & ~E_NOTICE );

// required includes for start-up

chdir("../../");
$dPconfig = array();
require_once( "./includes/config.php" );
require_once( "./classes/ui.class.php" );
require_once( "./includes/main_functions.php" );
require_once( "includes/db_mysql.php" );
$AppUI = new CAppUI();
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );

require_once( "includes/db_connect.php" );
require_once( "includes/permissions.php" );
//include_once( "./locales/$AppUI->user_locale/locales.php" );
//include_once( "locales/core.php" );

require_once( $AppUI->getModuleClass( 'emailalerts' )  );


//$rta = Notifier::MilestonesEnding();
//$rta = Notifier::TaskConstraintDate();
//$rta = Notifier::ProjectTargetEndDate();
$rta = Notifier::HhrrBackendUsersWithOldData();
var_dump($rta);
ob_end_flush();
?>
