<?php require_once( 'core.php' ) ?>
<?php
	# @@@ Need to obtain the project_id from the file once we have an API for that	
	access_ensure_project_level( MANAGER );

	$f_file_id		= gpc_get_int( 'file_id' );
	$f_title		= gpc_get_string( 'title' );
	if ( is_blank( $f_title ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_description	= gpc_get_string( 'description' );

	$c_file_id		= db_prepare_int( $f_file_id );
	$c_title 		= db_prepare_string( $f_title );
	$c_description 	= db_prepare_string( $f_description );

	$query = "UPDATE $g_mantis_project_file_table
			SET title='$c_title', description='$c_description'
			WHERE id='$c_file_id'";
	$result = db_query( $query );

	$t_redirect_url = 'index.php?m=webtracking&a=proj_doc_page';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
