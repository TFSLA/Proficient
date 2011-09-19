<?php
	# Update bug data then redirect to the appropriate viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
           
	# extract current extended information
	$t_bug_data = bug_get( $f_bug_id, true );
	
	echo "1 <pre>";  print_r($t_bug_data); echo "</pre>";

	$t_bug_data->reporter_id		= gpc_get_int( 'reporter_id', $t_bug_data->reporter_id );
	$t_bug_data->handler_id			= gpc_get_int( 'handler_id', $t_bug_data->handler_id );
	$t_bug_data->duplicate_id		= gpc_get_int( 'duplicate_id', $t_bug_data->duplicate_id );
	$t_bug_data->priority			= gpc_get_int( 'priority', $t_bug_data->priority );
	$t_bug_data->severity			= gpc_get_int( 'severity', $t_bug_data->severity );
	$t_bug_data->reproducibility	= gpc_get_int( 'reproducibility', $t_bug_data->reproducibility );
	$t_bug_data->status				= gpc_get_int( 'status', $t_bug_data->status );
	$t_bug_data->resolution			= gpc_get_int( 'resolution', $t_bug_data->resolution );
	$t_bug_data->projection			= gpc_get_int( 'projection', $t_bug_data->projection );
	$t_bug_data->category			= gpc_get_string( 'category', $t_bug_data->category );
	$t_bug_data->eta				= gpc_get_int( 'eta', $t_bug_data->eta );
	$t_bug_data->os					= gpc_get_string( 'os', $t_bug_data->os );
	$t_bug_data->os_build			= gpc_get_string( 'os_build', $t_bug_data->os_build );
	$t_bug_data->platform			= gpc_get_string( 'platform', $t_bug_data->platform );
	$t_bug_data->version			= gpc_get_string( 'version', $t_bug_data->version );
	$t_bug_data->build				= gpc_get_string( 'build', $t_bug_data->build );
	$t_bug_data->view_state			= gpc_get_int( 'view_state', $t_bug_data->view_state );
	$t_bug_data->summary			= gpc_get_string( 'summary', $t_bug_data->summary );
	$t_bug_data->date_deadline			= gpc_get_string( 'date_deadline', $t_bug_data->date_deadline );

	$t_bug_data->description		= gpc_get_string( 'description', $t_bug_data->description );
	$t_bug_data->steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', $t_bug_data->steps_to_reproduce );
	$t_bug_data->additional_information	= gpc_get_string( 'additional_information', $t_bug_data->additional_information );
            $t_bug_data->task_id                           = $_POST['task_id'];
	
	$f_private						= gpc_get_bool( 'private' );
	$f_bugnote_text					= gpc_get_string( 'bugnote_text', '' );
	
	$t_bug_data->summary			= str_replace("&#180;", "`", $t_bug_data->summary);
	$t_bug_data->description                                 = str_replace("&#180;", "`", $t_bug_data->description);
	$t_bug_data->steps_to_reproduce	            = str_replace("&#180;", "`", $t_bug_data->steps_to_reproduce);
	$t_bug_data->additional_information               = str_replace("&#180;", "`", $t_bug_data->additional_information);
	
	$t_bug_data->summary			= str_replace("&#168;", "''", $t_bug_data->summary);
	$t_bug_data->description                                 = str_replace("&#168;", "''", $t_bug_data->description);
	$t_bug_data->steps_to_reproduce	            = str_replace("&#168;", "''", $t_bug_data->steps_to_reproduce);
	$t_bug_data->additional_information               = str_replace("&#168;", "''", $t_bug_data->additional_information);
	
	$t_bug_data->summary			= str_replace("&acute;", "`", $t_bug_data->summary);
	$t_bug_data->description                                 = str_replace("&acute;", "`", $t_bug_data->description);
	$t_bug_data->steps_to_reproduce	            = str_replace("&acute;", "`", $t_bug_data->steps_to_reproduce);
	$t_bug_data->additional_information               = str_replace("&acute;", "`", $t_bug_data->additional_information);
	
	$t_bug_data->summary			= str_replace("&uml;", "''", $t_bug_data->summary);
	$t_bug_data->description                                 = str_replace("&uml;", "''", $t_bug_data->description);
	$t_bug_data->steps_to_reproduce	            = str_replace("&uml;", "''", $t_bug_data->steps_to_reproduce);
	$t_bug_data->additional_information               = str_replace("&uml;", "''", $t_bug_data->additional_information);
	
	echo "2 <pre>";  print_r($t_bug_data); echo "</pre>";
	
	# Handle auto-assigning
	if ( ( NEW_ == $t_bug_data->status )
	  && ( 0 != $t_bug_data->handler_id )
	  && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
		$t_bug_data->status = ASSIGNED;
	}

	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		# Do not set custom field value if user has no write access.
		if( !custom_field_has_write_access( $t_id, $f_bug_id ) ) {
			continue;
		}

		$t_def = custom_field_get_definition( $t_id );
		if ( !custom_field_set_value( $t_id, $f_bug_id, gpc_get_string( "custom_field_$t_id", $t_def['default_value'] ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	# Add a bugnote if there is one
	$f_bugnote_text = trim( $f_bugnote_text );
	if ( !is_blank( $f_bugnote_text ) ) {
		$f_bugnote_text = str_replace("&#180;", "`", $f_bugnote_text);
		$f_bugnote_text = str_replace("&#168;", "''", $f_bugnote_text);
		
		$f_bugnote_text = str_replace("&acute;", "`", $f_bugnote_text);
		$f_bugnote_text = str_replace("&uml;", "''", $f_bugnote_text);
		
		bugnote_add( $f_bug_id, $f_bugnote_text, $f_private );
	}

	# Update the bug entry
	bug_update( $f_bug_id, $t_bug_data, true );

	$foreign = false;
	
	if($_GET['o']=="tasks" || $_GET['o']=="projects")$foreign = true;
	
	print_successful_redirect_to_bug( $f_bug_id, $foreign );
?>
