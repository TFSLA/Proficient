<?php 
global $external;
require_once( $AppUI->getModuleClass( 'timexp' ) );
$external = 1;
include("{$AppUI->cfg['root_dir']}/modules/timexp/adeditexpense.php");

?>


