<?php 
GLOBAL $AppUI, $project_id, $deny, $canRead, $canEdit;

require_once( $AppUI->getModuleClass( 'files' ) );
require_once( $AppUI->getModuleClass('projects') );

require( "{$AppUI->cfg['root_dir']}/modules/files/do_file_aed.php" );
?>