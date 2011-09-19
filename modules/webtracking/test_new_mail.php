<?php
	# This page stores the reported bug
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php

	//email_new_bug( "34" );
	$list = email_build_bcc_list($_GET["id"], 'closed' );
	echo "<pre>";
	var_dump($list);
	echo "</pre>";

?>