<?php
global $m, $company_id;
$canEdit = !getDenyEdit( $m, $company_id );
require_once( $AppUI->getModuleClass( 'system' ) );
var_dump($canEdit);
include_once( "./modules/system/do_holliday_copy.php");

?>