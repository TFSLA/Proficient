<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$height = 100;

	enum_bug_group( lang_get( 'severity_enum_string' ), 'severity' );
	graph_group( lang_get( 'by_severity_mix' ) );
?>