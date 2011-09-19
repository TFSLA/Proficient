<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	create_bug_enum_summary( lang_get( 'priority_enum_string' ), 'priority');
	graph_bug_enum_summary( lang_get( 'by_priority' ) );
?>