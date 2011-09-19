<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	#centers the chart
	$center = 0.30;

	#position of the legend
	$poshorizontal = 0.10;
	$posvertical = 0.09;

	create_bug_enum_summary_pct( lang_get( 'severity_enum_string' ), 'severity');
	graph_bug_enum_summary_pct( lang_get( 'by_severity_pct' ) );
?>