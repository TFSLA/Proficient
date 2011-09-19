<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	#centers the chart
	$center = 0.26;

	#position of the legend
	$poshorizontal = 0.03;
	$posvertical = 0.09;

	create_bug_enum_summary_pct( lang_get( 'resolution_enum_string' ), 'resolution' );
	graph_bug_enum_summary_pct( lang_get( 'by_resolution_pct' ) );
?>