<?php

	//$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	$t_core_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'history_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'date_api.php' );
	###########################################################################
	# File API
	###########################################################################

	# --------------------
	# Gets the filename without the bug id prefix.
	function file_get_display_name( $p_filename ) {
		$t_array = explode ('-', $p_filename, 2);

		# Check if it's a project document filename (doc-000-filename)
		# or a bug attachment filename (000-filename)
		# This is important to handle filenames with '-'s properly
		if ( $t_array[0] == config_get( 'document_files_prefix' ) ) {
			$t_array = explode ('-', $p_filename, 3);
		}
		return $t_array[count( $t_array ) - 1];
	}
	# --------------------
	# Check the number of attachments a bug has (if any)
	function file_bug_attachment_count( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );
		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$query = "SELECT bug_id FROM $t_bug_file_table WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );

		return db_num_rows( $result );
	}
	# --------------------
	# Check if a specific bug has attachments
	function file_bug_has_attachments( $p_bug_id ) {
		return ( file_bug_attachment_count( $p_bug_id ) > 0 );
	}
	# --------------------
	# List the attachments belonging to the specified bug.  This is used from within
	# bug_view_page.php and bug_view_advanced_page.php
	function file_list_attachments ( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$query = "SELECT id, title, diskfile, filename, filesize, UNIX_TIMESTAMP(date_added) as date_added
				FROM $t_bug_file_table
				WHERE bug_id='$c_bug_id'
				ORDER BY date_added";
		$result = db_query( $query );

		$t_bug = bug_get( $c_bug_id, false );
		$t_can_delete = ( $t_bug->status < config_get( 'bug_resolved_status_threshold' ) ) &&
				access_has_project_level( config_get( 'handle_bug_threshold' ) );

		$num_files = db_num_rows( $result );
		$image_previewed = false;
		for ( $i = 0 ; $i < $num_files ; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$t_filesize = number_format( $v_filesize );
			$t_date_added = format_date( config_get( 'normal_date_format' ), ( $v_date_added ) );

			if ( $image_previewed ) {
				$image_previewed = false;
				echo '<br />';
			}

			$t_href = "<a href=\"index.php?m=webtracking&a=file_download&suppressHeaders=yes&includeajax=false&file_id=$v_id&amp;type=bug\" target=\"_blank\">";

			echo $t_href;
			print_file_icon ( file_get_display_name( $v_filename ) );
			echo '</a>&nbsp;' . $t_href . file_get_display_name( $v_filename ) . 
				"</a> ($t_filesize bytes) <span class=\"italic\">$t_date_added</span>";

			if ( $t_can_delete ) {
				echo " [<a class=\"small\" href=\"index.php?m=webtracking&a=bug_file_delete&file_id=$v_id\">" . lang_get('delete_link') . '</a>]';
			}
			
			if ( ( FTP == config_get( 'file_upload_method' ) ) && file_exists ( $v_diskfile ) ) {
				echo ' (' . lang_get( 'cached' ) . ')';
			}

			if ( ( $v_filesize <= config_get( 'preview_attachments_inline_max_size' ) ) &&
				( $v_filesize != 0 ) && 
				( in_array( strtolower( file_get_extension( $v_diskfile ) ), array( 'png', 'jpg', 'gif', 'bmp' ), true ) ) ) {
				echo "<br /><img src=\"index.php?m=webtracking&a=file_download&file_id=$v_id&amp;type=bug\" />";
				$image_previewed = true;
			}

			if ( $i != ($num_files - 1) ) {
				echo '<br />';
			}
		}
	}
	# --------------------
	# delete all files that are associated with the given bug
	function file_delete_attachments( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$t_method = config_get( 'file_upload_method' );

		# Delete files from disk
		$query = "SELECT diskfile, filename
			FROM $t_bug_file_table
			WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );

		$file_count = db_num_rows( $result );
		if ( 0 == $file_count ) {
			return true;
		}

		if ( ( DISK == $t_method ) || ( FTP == $t_method ) ) {
			# there may be more than one file
			$ftp = 0;
			if ( FTP == $t_method ) {
				$ftp = file_ftp_connect();
			}

			for ( $i = 0 ; $i < $file_count ; $i++ ) {
				$row = db_fetch_array( $result );

				file_delete_local ( $row['diskfile'] );

				if ( FTP == $t_method ) {
					file_ftp_delete ( $ftp, $row['filename'] );
				}
			}

			if ( FTP == $t_method ) {
				file_ftp_disconnect( $ftp );
			}
		}

		# Delete the corresponding db records
		$query = "DELETE
			FROM $t_bug_file_table
			WHERE bug_id='$c_bug_id'";
		$result = db_query($query);

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	function file_delete_project_files( $p_project_id ) {
		$t_project_file_table = config_get( 'mantis_project_file_table' );
		$t_method = config_get( 'file_upload_method' );

		# Delete the file physically (if stored via DISK or FTP)
		if ( ( DISK == $t_method ) || ( FTP == $t_method ) ) {
			# Delete files from disk
			$query = "SELECT diskfile, filename FROM $t_project_file_table"
				. " WHERE project_id=$p_project_id";
			$result = db_query( $query );

			$file_count = db_num_rows( $result );

			$ftp = 0;
			if ( FTP == $t_method ) {
				$ftp = file_ftp_connect();
			}

			for ( $i = 0 ; $i < $file_count ; $i++ ) {
				$row = db_fetch_array( $result );

				file_delete_local ( $row['diskfile'] );

				if ( FTP == $t_method ) {
					file_ftp_delete ( $ftp, $row['filename'] );
				}
			}

			if ( FTP == $t_method ) {
				file_ftp_disconnect( $ftp );
			}
		}

		# Delete the corresponding db records
		$query = "DELETE FROM $t_project_file_table WHERE project_id=$p_project_id";
		$result = db_query($query);
	}
	# --------------------
	# Delete all cached files that are older than configured number of days.
	function file_ftp_cache_cleanup() {
		
	}
	# --------------------
	# Connect to ftp server using configured server address, user name, and password.
	function file_ftp_connect() {
		$conn_id = ftp_connect( config_get( 'file_upload_ftp_server' ) ); 
		$login_result = ftp_login( $conn_id, config_get( 'file_upload_ftp_user' ), config_get( 'file_upload_ftp_pass' ) );

		if ( ( !$conn_id ) || ( !$login_result ) ) {
			trigger_error( ERROR_FTP_CONNECT_ERROR, ERROR );
		}

		return $conn_id;
	}
	# --------------------
	# Put a file to the ftp server.
	function file_ftp_put ( $p_conn_id, $p_remote_filename, $p_local_filename ) {
		set_time_limit(0);
		$upload = ftp_put( $p_conn_id, $p_remote_filename, $p_local_filename, FTP_BINARY);
	}
	# --------------------
	# Get a file from the ftp server.
	function file_ftp_get ( $p_conn_id, $p_local_filename, $p_remote_filename ) {
		set_time_limit(0);
		$download = ftp_get( $p_conn_id, $p_local_filename, $p_remote_filename, FTP_BINARY);
	}
	# --------------------
	# Delete a file from the ftp server
	function file_ftp_delete ( $p_conn_id, $p_filename ) {
		@ftp_delete( $p_conn_id, $p_filename );
	}
	# --------------------
	# Disconnect from the ftp server
	function file_ftp_disconnect( $p_conn_id ) {
		ftp_quit( $p_conn_id ); 
	}
	# --------------------
	# Delete a local file even if it is read-only.
	function file_delete_local( $p_filename ) {
		# in windows replace with system("del $t_diskfile");
		if ( file_exists( $p_filename ) ) {
			chmod( $p_filename, 0775 );
			unlink( $p_filename );
		}
	}
	# --------------------
	# Return the specified field value
	function file_get_field( $p_file_id, $p_field_name ) {
		$c_file_id		= db_prepare_int( $p_file_id );
		$c_field_name	= db_prepare_string( $p_field_name );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		# get info
		$query = "SELECT $c_field_name
				  FROM $t_bug_file_table
				  WHERE id='$c_file_id'
				  LIMIT 1";
		$result = db_query( $query );

		return db_result( $result );
	}
	# --------------------
	function file_delete( $p_file_id ) {
		$c_file_id = db_prepare_int( $p_file_id );
		
		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$t_upload_method = config_get( 'file_upload_method' );
		$t_filename = file_get_field( $p_file_id, 'filename' );
		$t_bug_id = file_get_field( $p_file_id, 'bug_id' );

		if ( ( DISK == $t_upload_method ) || ( FTP == $t_upload_method ) ) {
			$t_diskfile = file_get_field( $p_file_id, 'diskfile' );

			if ( FTP == $t_upload_method ) {
				$ftp = file_ftp_connect();
				file_ftp_delete ( $ftp, $t_filename );
				file_ftp_disconnect( $ftp );
			}

			if ( file_exists( $t_diskfile ) ) {
				file_delete_local ( $t_diskfile );
			}
		}

		$query = "DELETE FROM $t_bug_file_table
				WHERE id='$c_file_id'";
		db_query( $query );

		# log file deletion
		history_log_event_special( $t_bug_id, FILE_DELETED, file_get_display_name ( $t_filename ) );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# File type check
	function file_type_check( $p_file_name ) {
		$t_allowed_files = config_get( 'allowed_files' );
		$t_disallowed_files = config_get( 'disallowed_files' );;

		# grab extension
		$t_ext_array = explode( '.', $p_file_name );
		$last_position = count( $t_ext_array )-1;
		$t_extension = $t_ext_array[$last_position];

		# check against disallowed files
		$t_disallowed_arr =  explode_enum_string( $t_disallowed_files );
		foreach ( $t_disallowed_arr as $t_val ) {
		    if ( $t_val == $t_extension ) {
		    	return false;
		    }
		}

		# if the allowed list is note populated then the file must be allowed
		if ( is_blank( $t_allowed_files ) ) {
			return true;
		}
		# check against allowed files
		$t_allowed_arr = explode_enum_string( $t_allowed_files );
		foreach ( $t_allowed_arr as $t_val ) {
		    if ( $t_val == $t_extension ) {
				return true;
		    }
		}
		return false;
	}
	# --------------------
	function file_add( $p_bug_id, $p_tmp_file, $p_file_name, $p_file_type='' ) {
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_file_type	= db_prepare_string( $p_file_type );

		if ( !file_type_check( $p_file_name ) ) {
			trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
		} else if ( is_uploaded_file( $p_tmp_file ) ) {
			$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
			$t_bug_id = bug_format_id( $p_bug_id );

			# prepare variables for insertion
			$t_file_path = project_get_field( $t_project_id, 'file_path' );
			$c_file_path = db_prepare_string( $t_file_path );

			$t_new_file_name = $t_bug_id . '-' . $p_file_name;
			$c_new_file_name = db_prepare_string( $t_new_file_name );

			$t_file_size = filesize( $p_tmp_file );
			$c_file_size = db_prepare_int( $t_file_size );

			$t_method = config_get( 'file_upload_method' );
			$t_bug_file_table = config_get( 'mantis_bug_file_table' );

			switch ( $t_method ) {
				case FTP:
				case DISK:
					file_ensure_valid_upload_path( $t_file_path );

					if ( !file_exists( $t_file_path . $t_new_file_name ) ) {
						if ( FTP == $t_method ) {
							$conn_id = file_ftp_connect();
							file_ftp_put ( $conn_id, $t_new_file_name, $p_tmp_file );
							file_ftp_disconnect ( $conn_id );
						}

						umask( 0333 );  # make read only
						copy( $p_tmp_file, $t_file_path . $t_new_file_name );

						$c_content = '';
					} else {
						trigger_error( ERROR_FILE_DUPLICATE, ERROR );
					}
					break;
				case DATABASE:
					$c_content = db_prepare_string( fread ( fopen( $p_tmp_file, 'rb' ), $t_file_size ) );
					break;
				default:
					trigger_error( ERROR_GENERIC, ERROR );
			}

			$query = "INSERT INTO $t_bug_file_table
						(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
						VALUES
						(null, $c_bug_id, '', '', '$c_file_path$c_new_file_name', '$c_new_file_name', '$c_file_path', $c_file_size, '$c_file_type', NOW(), '$c_content')";
			db_query( $query );

			# updated the last_updated date
			$result = bug_update_date( $p_bug_id );

			# log new bug
			history_log_event_special( $p_bug_id, FILE_ADDED, $p_file_name );
		}

	}
	
	function mailfile_add( $p_bug_id, $p_tmp_file, $p_file_name, $p_file_type='' ) {
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_file_type	= db_prepare_string( $p_file_type );

		if ( !file_type_check( $p_file_name ) ) {
			trigger_error( ERROR_FILE_NOT_ALLOWED, ERROR );
		} else if ( is_uploaded_file( $p_tmp_file ) ) {
			$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
			$t_bug_id = bug_format_id( $p_bug_id );

			# prepare variables for insertion
			$t_file_path = project_get_field( $t_project_id, 'file_path' );
			$c_file_path = db_prepare_string( $t_file_path );

			$t_new_file_name = $t_bug_id . '-' . $p_file_name;
			$c_new_file_name = db_prepare_string( $t_new_file_name );

			$t_file_size = filesize( $p_tmp_file );
			$c_file_size = db_prepare_int( $t_file_size );

			$t_method = config_get( 'file_upload_method' );
			$t_bug_file_table = config_get( 'mantis_bug_file_table' );

			switch ( $t_method ) {
				case FTP:
				case DISK:
					file_ensure_valid_upload_path( $t_file_path );

					if ( !file_exists( $t_file_path . $t_new_file_name ) ) {
						if ( FTP == $t_method ) {
							$conn_id = file_ftp_connect();
							file_ftp_put ( $conn_id, $t_new_file_name, $p_tmp_file );
							file_ftp_disconnect ( $conn_id );
						}

						umask( 0333 );  # make read only
						copy( $p_tmp_file, $t_file_path . $t_new_file_name );

						$c_content = '';
					} else {
						trigger_error( ERROR_FILE_DUPLICATE, ERROR );
					}
					break;
				case DATABASE:
					$c_content = db_prepare_string( fread ( fopen( $p_tmp_file, 'rb' ), $t_file_size ) );
					break;
				default:
					trigger_error( ERROR_GENERIC, ERROR );
			}

			$query = "INSERT INTO $t_bug_file_table
						(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
						VALUES
						(null, $c_bug_id, '', '', '$c_file_path$c_new_file_name', '$c_new_file_name', '$c_file_path', $c_file_size, '$c_file_type', NOW(), '$c_content')";
			db_query( $query );

			# updated the last_updated date
			$result = bug_update_date( $p_bug_id );

			# log new bug
			mailhistory_log_event_special( $p_bug_id, FILE_ADDED, $p_file_name );
		}

	}	
	
	# --------------------
	# Return true if file uploading is enabled (in our config and PHP's),
	#  false otherwise
	function file_is_uploading_enabled() {
		if ( ini_get_bool( 'file_uploads' ) && 
			 ON == config_get( 'allow_file_upload' ) ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check if the user can upload files for this project
	#  return true if they can, false otherwise
	#  the project defaults to the current project and the user to the current user
	function file_allow_project_upload( $p_project_id = null, $p_user_id = null ) {
		if ( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}
		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$t_access = user_get_access_level( $p_user_id, $p_project_id );

		if ( ! file_is_uploading_enabled() ||
			 $t_access < config_get( 'upload_project_file_threshold' ) ) { 
			return false;
		}

		return true;
	}

	# --------------------
	# Check if the user can upload files for this bug
	#  return true if they can, false otherwise
	#  the user defaults to the current user
	#
	#  if the bug null (the default) we answer whether the user can
	#   upload a file to a new bug in the current project
	function file_allow_bug_upload( $p_bug_id = null, $p_user_id = null ) {
		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		# If uploads are disbled just return false
		if ( ! file_is_uploading_enabled() ) {
			return false;
		}
		
		if ( null === $p_bug_id ) {		# new bug
			$t_project_id = helper_get_current_project();

			# the user must be the reporter if they're reporting a new bug
			$t_reporter = true;
		} else {						# existing bug
			$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

			# check if the user is the reporter of the bug
			$t_reporter = bug_is_user_reporter( $p_bug_id, $p_user_id );
		}

		# *** If we ever wanted to have a per-project setting enabling file
		#     uploads, we'd want to check it here before exempting the reporter

		if ( $t_reporter && ON == config_get( 'allow_reporter_upload' ) ) {
			return true;
		}

		# Get the user's access level
		$t_access = user_get_access_level( $p_user_id, $t_project_id );

		# Check the access level against the config setting
		if ( $t_access >= config_get( 'upload_bug_file_threshold' ) ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# checks whether the specified upload path exists and is writable
	function file_ensure_valid_upload_path( $p_upload_path ) {
		if ( is_blank( $p_upload_path ) || !file_exists( $p_upload_path ) || !is_dir( $p_upload_path ) || !is_writable( $p_upload_path ) ) {
			trigger_error( ERROR_FILE_INVALID_UPLOAD_PATH, ERROR );
		}
	}

	# --------------------
	# Get extension given the filename or its full path.
	function file_get_extension( $p_filename ) {
		$ext = '';
		$dot_found = false;
		$i = strlen( $p_filename ) - 1;
		while ( $i >= 0 ) {
			if ( $p_filename[$i] == '.' ) {
				$dot_found = true;
				break;
			}

			if ( ( $p_filename[$i] == "/" ) || ( $p_filename[$i] == "\\" ) ) {
				return '';
			}

			$ext = $p_filename[$i] . $ext;

			$i--;
		}

		if ( $dot_found ) {
			return $ext;
		} else {
			return '';
		}
	}
?>
