<?php
global $m, $company_id;
$canEdit = !getDenyEdit( $m, $company_id );
require_once( $AppUI->getModuleClass( 'system' ) );

include_once( "./modules/system/do_calendar_aed.php");

?>