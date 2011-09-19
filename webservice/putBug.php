<?
	include_once('common.inc.php');
	
	$code = $_POST['code'];

	$AppUI->user_id = $_POST['user_id'];
	$AppUI->loadPrefs( $AppUI->user_id );
	$AppUI->setUserLocale();
	
	$mails_followup = $_POST['emails_followup'];
	            
	if($code == '14149989')
	{
		$maxfilesize = 52428800;
		
		$errorCode = 0;
		$errorDescription;
		
		require_once('../modules/webtracking/core.php');

		$t_core_path = config_get( 'core_path' );
	
		require_once( $t_core_path.'string_api.php' );
		require_once( $t_core_path.'file_api.php' );
		require_once( $t_core_path.'bug_api.php' );
		require_once( $t_core_path.'custom_field_api.php' );
		
		
		$f_build				= gpc_get_string( 'build', '' );
		$f_platform				= gpc_get_string( 'platform', '' );
		$f_os					= gpc_get_string( 'os', '' );
		$f_os_build				= gpc_get_string( 'os_build', '' );
		$f_product_version		= gpc_get_string( 'product_version', '' );
		$f_profile_id			= gpc_get_int( 'profile_id', 0 );
		$f_handler_id			= gpc_get_int( 'handler_id', 0 );
		$f_view_state			= gpc_get_int( 'view_state', 0 );

		$f_project_id			= gpc_get_int( 'project_id' );
		
		//Get Category default
		$t_categories = category_get_all_rows( $f_project_id );		
		if ( count( $t_categories ) > 0 )
		{
			foreach ( $t_categories as $t_category )
				if($t_category['default_category'] == "1")
					$f_category = $t_category['category'];
		}
		
		if(strlen($f_category) == 0)
		$f_category				= gpc_get_string( 'category', '' );
		
		$f_reproducibility		= gpc_get_int( 'reproducibility' );
		$f_severity				= gpc_get_int( 'severity' );
		$f_priority				= gpc_get_int( 'priority', NORMAL );
		$f_date_deadline			= gpc_get_string( 'date_deadline', 'NULL' );

		$f_summary				= gpc_get_string( 'summary' );
		$f_description			= quoted_printable_decode(gpc_get_string( 'description' ));
		$f_steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', '' );
		$f_additional_info		= gpc_get_string( 'additional_info', '' );

		$f_task_id			    = gpc_get_int( 'task_id', 0 );

		$t_reporter_id		= $AppUI->user_id;
		
		$f_file			    = gpc_get_file( 'file', null );
		
		# If a file was uploaded, and we need to store it on disk, let's make
		#  sure that the file path for this project exists
		if ( is_uploaded_file( $f_file['tmp_name'] ) &&
			  ( DISK == $t_upload_method || FTP == $t_upload_method ) ) {
			$t_file_path = project_get_field( $f_project_id, 'file_path' );

			if ( !file_exists( $t_file_path ) ) {
				trigger_error( ERROR_NO_DIRECTORY, ERROR );
			}
		}


		# if a profile was selected then let's use that information
		if ( 0 != $f_profile_id ) {
			$row = user_get_profile_row( $t_reporter_id, $f_profile_id );

			if ( is_blank( $f_platform ) ) {
				$f_platform = $row['platform'];
			}
			if ( is_blank( $f_os ) ) {
				$f_os = $row['os'];
			}
			if ( is_blank( $f_os_build ) ) {
				$f_os_build = $row['os_build'];
			}
		}

		$t_bug_id = mailbug_create( $f_project_id,
						$t_reporter_id, $f_handler_id,
						$f_priority,
						$f_severity, $f_reproducibility,
						$f_category,
						$f_os, $f_os_build,
						$f_platform, $f_product_version,
						$f_build,
						$f_profile_id, $f_summary, $f_view_state,
						$f_description, $f_steps_to_reproduce, $f_additional_info, $f_date_deadline, $f_task_id );

                        
		# Handle the file upload
		if ( 0 != $f_file['size'] ) {
			mailfile_add( $t_bug_id, $f_file['tmp_name'], $f_file['name'], $f_file['type'] );
		}
		
		// Si ingreso la incidencia sin errores, proceso los mails de followup
		if($mails_followup !=""){ bug_email_followup($t_bug_id , $mails_followup); }		

		$GLOBALS['g_path'] = $AppUI->cfg['base_url']."/";
		email_new_bug( $t_bug_id );
		
		if($errorCode > 0){
			echo("<error><code>".$errorCode."</code><description>".$errorDescription."</description></error>");
		}else{			
			echo("<message>Operation Successfully</message>");
		}		
	}
	else
	{
		echo("<error><code>101</code><description>Invalid credentials.</description></error>");
	}
	
	
	function mailbug_create( $p_project_id,
				$p_reporter_id, $p_handler_id,
				$p_priority,
				$p_severity, $p_reproducibility,
				$p_category,
				$p_os, $p_os_build,
				$p_platform, $p_version,
				$p_build, 
				$p_profile_id, $p_summary, $p_view_state,
				$p_description, $p_steps_to_reproduce, $p_additional_info, $p_date_deadline, $p_task_id )
	{
					global $AppUI;
	

		$c_summary				= db_prepare_string( $p_summary );
		$c_description			= db_prepare_string( $p_description );
		$c_project_id			= db_prepare_int( $p_project_id );
		$c_reporter_id			= db_prepare_int( $p_reporter_id );
		$c_handler_id			= db_prepare_int( $p_handler_id );
		$c_priority				= db_prepare_int( $p_priority );
		$c_severity				= db_prepare_int( $p_severity );
		$c_reproducibility		= db_prepare_int( $p_reproducibility );
		$c_category				= db_prepare_string( $p_category );
		$c_os					= db_prepare_string( $p_os );
		$c_os_build				= db_prepare_string( $p_os_build );
		$c_platform				= db_prepare_string( $p_platform );
		$c_version				= db_prepare_string( $p_version );
		$c_build				= db_prepare_string( $p_build );
		$c_profile_id			= db_prepare_int( $p_profile_id );
		$c_view_state			= db_prepare_int( $p_view_state );
		$c_steps_to_reproduce	= db_prepare_string( $p_steps_to_reproduce );
		$c_additional_info		= db_prepare_string( $p_additional_info );
		$c_date_deadline		= db_prepare_string( $p_date_deadline );
		$c_date_deadline = $c_date_deadline == "" ? "NULL" : "'$c_date_deadline'";
		$c_task_id			= db_prepare_int( $p_task_id );

		# Summary and description cannot be blank
		if ( is_blank( $c_summary ) || is_blank( $c_description ) ) {
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_bug_text_table = config_get( 'mantis_bug_text_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_project_category_table = config_get( 'mantis_project_category_table' );

		# Insert text information
		$query = "INSERT
				  INTO $t_bug_text_table
				    ( id, description, steps_to_reproduce, additional_information )
				  VALUES
				    ( null, '$c_description', '$c_steps_to_reproduce',
				      '$c_additional_info' )";
		db_query( $query );

		# Get the id of the text information we just inserted
		# NOTE: this is guarranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_text_id = db_insert_id();

		# check to see if we want to assign this right off
		$t_status = NEW_;

		# if not assigned, check if it should auto-assigned.
		if ( 0 == $c_handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$query = "SELECT user_id
					  FROM $t_project_category_table
					  WHERE project_id='$c_project_id' AND category='$c_category'";

			$result = db_query( $query );

			if ( db_num_rows( $result ) > 0 ) {
				$c_handler_id = $p_handler_id = db_result( $result );
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if ( ( $c_handler_id != 0 ) && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
			$t_status = ASSIGNED;
		}

		# Insert the rest of the data
		$t_resolution = OPEN;

		$query = "INSERT
				  INTO $t_bug_table
				    ( id, project_id,
				      reporter_id, handler_id,
				      duplicate_id, priority,
				      severity, reproducibility,
				      status, resolution,
				      projection, category,
				      date_submitted, last_updated,
				      eta, date_deadline, bug_text_id,
				      os, os_build,
				      platform, version,
				      build,
				      profile_id, summary, view_state, task_id )
				  VALUES
				    ( null, '$c_project_id',
				      '$c_reporter_id', '$c_handler_id',
				      '0', '$c_priority',
				      '$c_severity', '$c_reproducibility',
				      '$t_status', '$t_resolution',
				      10, '$c_category',
				      NOW(), NOW(),
				      10, $c_date_deadline, '$t_text_id',
				      '$c_os', '$c_os_build',
				      '$c_platform', '$c_version',
				      '$c_build',
				      '$c_profile_id', '$c_summary', '$c_view_state', '$c_task_id' )";
		//echo "<pre>$query</pre>";
		db_query( $query );

		$t_bug_id = db_insert_id();

		# log new bug
		mailhistory_log_event_special( $t_bug_id, NEW_BUG );

		return $t_bug_id;
	}
	
	function mailhistory_log_event_special( $p_bug_id, $p_type, $p_optional='',  $p_optional2='' )
	{
		global $AppUI;
	
	
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_type			= db_prepare_int( $p_type );
		$c_optional		= db_prepare_string( $p_optional );
		$c_optional2	= db_prepare_string( $p_optional2 );
		$t_user_id		= $AppUI->user_id;

		$query = "INSERT INTO " . config_get( 'mantis_bug_history_table' ) . "
				( user_id, bug_id, date_modified, type, old_value, new_value )
				VALUES
				( '$t_user_id', '$c_bug_id', NOW(), '$c_type', '$c_optional', '$c_optional2' )";
		$result = db_query( $query );
	}	
	
	/**
	 * Ingresa los mail de no usuarios para el seguimiento de incidencias
	 *
	 * @param integer $t_bug_id = Id de la incidecia 
	 * @param  string  $mails_followup = String con los mails de los usuarios a ingresar (pablo Kerestezachi|pkerestezachi@tfsla.com;Claudia Tobares|ctobares@tfsla.com);
	 */
	function bug_email_followup($t_bug_id , $mails_followup){
	      global $AppUI;
		
	      // Meto los mails del string en un vector
	      $mails = explode(";", $mails_followup);
	      
	      for($i=0;$i<count($mails);$i++)
	      {
	      	$datos = explode("|", $mails[$i]);
	      	$name = $datos[0];
	      	$mail = $datos[1];
	      	
	      	if($name=="")
	      	{
	      	     $n = explode("@", $mail);	
	      	     $name = $n[0];
	      	}
	      	
	      	$name = trim($name);
	      	$mail = trim($mail);
	      	
	      	if($mail!=""){
	      	$query = "INSERT INTO btpsa_email_followup ( bug_id, email,name ) VALUES ( '$t_bug_id', '".$mail."','".$name."')";
		$result = db_query( $query );
	      	}
	      }
	      
	      return;
	}
?>