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
	access_ensure_project_level( config_get('report_bug_threshold' ) );

	$dialog = intval(dPgetParam( $_GET, "dialog", 0 ));
	$suppressLogo = intval(dPgetParam( $_GET, "suppressLogo", 0 ));
	$callback = dPgetParam( $_GET, "callback", "" );

	$f_build				= gpc_get_string( 'build', '' );
	$f_platform				= gpc_get_string( 'platform', '' );
	$f_os					= gpc_get_string( 'os', '' );
	$f_os_build				= gpc_get_string( 'os_build', '' );
	$f_product_version		= gpc_get_string( 'product_version', '' );
	$f_profile_id			= gpc_get_int( 'profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'handler_id', 0 );
	$f_view_state			= gpc_get_int( 'view_state', 0 );

	$f_category				= gpc_get_string( 'category', '' );
	$f_reproducibility		= gpc_get_int( 'reproducibility' );
	$f_severity				= gpc_get_int( 'severity' );
	$f_priority				= gpc_get_int( 'priority', NORMAL );
	$f_date_deadline			= gpc_get_string( 'date_deadline', 'NULL' );

	$f_summary				= gpc_get_string( 'summary' );
	$f_description			= gpc_get_string( 'description' );
	$f_steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', '' );
	$f_additional_info		= gpc_get_string( 'additional_info', '' );

	$f_file					= gpc_get_file( 'file', null );
	$f_report_stay			= gpc_get_bool( 'report_stay' );
	$f_project_id			= gpc_get_int( 'project_id' );
	$f_task_id			    = gpc_get_int( 'task_id', 0 );

	$t_reporter_id		= auth_get_current_user_id();
	$t_upload_method	= config_get( 'file_upload_method' );

	$canEdit_kb = !getDenyEdit( 'articles' );

	if($canEdit_kb){
	$f_kb_type = gpc_get_int( 'kb_type' );
	$f_kb_section = gpc_get_int( 'kb_section' );
	$f_kb_item = gpc_get_int( 'kb_item' );
	}

	$f_summary	    = str_replace("&#180;", "`", $f_summary);
	$f_description	    = str_replace("&#180;", "`", $f_description);
	$f_steps_to_reproduce	= str_replace("&#180;", "`", $f_steps_to_reproduce);
	$f_additional_info	= str_replace("&#180;", "`", $f_additional_info);
	$f_summary	    = str_replace("&#168;", "''", $f_summary);
	$f_description	    = str_replace("&#168;", "''", $f_description);
	$f_steps_to_reproduce	= str_replace("&#168;", "''", $f_steps_to_reproduce);
	$f_additional_info	= str_replace("&#168;", "''", $f_additional_info);

	$f_summary	    = str_replace("&acute;", "`", $f_summary);
	$f_description	    = str_replace("&acute;", "`", $f_description);
	$f_steps_to_reproduce	= str_replace("&acute;", "`", $f_steps_to_reproduce);
	$f_additional_info	= str_replace("&acute;", "`", $f_additional_info);
	$f_summary	    = str_replace("&uml;", "''", $f_summary);
	$f_description	    = str_replace("&uml;", "''", $f_description);
	$f_steps_to_reproduce	= str_replace("&uml;", "''", $f_steps_to_reproduce);
	$f_additional_info	= str_replace("&uml;", "''", $f_additional_info);

	# If a file was uploaded, and we need to store it on disk, let's make
	#  sure that the file path for this project exists
	if ( is_uploaded_file( $f_file['tmp_name'] ) &&
		  file_allow_bug_upload() &&
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

	$t_bug_id = bug_create( $f_project_id,
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
	if ( is_uploaded_file( $f_file['tmp_name'] ) &&
		  0 != $f_file['size'] &&
		  file_allow_bug_upload() ) {
		file_add( $t_bug_id, $f_file['tmp_name'], $f_file['name'], $f_file['type'] );
	}

	# Base de conocimientos
	if($canEdit_kb){
		if($f_kb_item >0){

		    $query_insert_kb = "INSERT INTO btpsa_bug_kb (project_id, bug_id,kb_type,kb_section,kb_item) VALUES ('".$f_project_id."','".$t_bug_id."','".$f_kb_type."', '".$f_kb_section."', '".$f_kb_item."') ";
	                db_query( $query_insert_kb );
		}
	}

	# Handle custom field submission
	$t_related_custom_field_ids = custom_field_get_linked_ids( $f_project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		# Do not set custom field value if user has no write access.
		if( !custom_field_has_write_access( $t_id, $t_bug_id ) ) {
			continue;
		}

		$t_def = custom_field_get_definition( $t_id );
		if( !custom_field_set_value( $t_id, $t_bug_id, gpc_get_string( "custom_field_$t_id", $t_def['default_value'] ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	email_new_bug( $t_bug_id );

	$AppUI = &$_SESSION['AppUI'];
	$AppUI->setMsg(lang_get( 'operation_successful' ), UI_MSG_OK);


	if($_GET['o']=="tasks" && $f_task_id !="")
	{
	  //  $t_redirect_url = "index.php?".$AppUI->state['SAVEDPLACE-1'];
	  $redir = 'm=tasks&a=view&task_id='.$f_task_id;
	}elseif ($_GET['o']=="projects" && $f_project_id !="" ){
	  $redir = 'm=projects&a=view&project_id='.$f_project_id;
            }else{
	  $f_redirect_url =  'index.php?m=webtracking&a=view_all_bug_page';
	  $redir = substr($f_redirect_url, strpos($f_redirect_url,"?")+1);
	}

                   // echo "Redir $redir <br>";
	html_page_top1();

	if ( ! $f_report_stay ) {
		$AppUI->redirect($redir);
		//html_meta_redirect( 'index.php?m=webtracking&a=view_all_bug_page' );
	}

	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';



if ($callback!="" && $dialog=="1"){
	echo "<b>".$AppUI->_("Selected Record")."</b><br />";
	echo "<b>".$AppUI->_("Bug").":</b> ".$f_summary."<br />";

	echo "<br />";
	echo $AppUI->_("If this window doesn't close itself, click"). " ";
	echo '<a href="javascript: setClose();">'.$AppUI->_("here")."</a> ";
	echo $AppUI->_("to return")."."; ?>
	<script language="javascript"><!--
		function setClose(){
			var key = "<?php echo $t_bug_id;?>";
			var val = "<?php echo $f_summary;?>";
			window.opener.<?php echo $callback;?>(key, val);
			window.close();
		}
		function loader(){
			window.setTimeout("setClose()", 2000);
		}
		loader();
	//--></script>
<?php
}else{
	if ( $f_report_stay ) {
?>
	<form method="post" name="editFrm" action="<?php echo string_get_bug_report_url() ?>">
		<input type="hidden" name="category" 		value="<?php echo $f_category ?>" />
		<input type="hidden" name="severity" 		value="<?php echo $f_severity ?>" />
		<input type="hidden" name="reproducibility" value="<?php echo $f_reproducibility ?>" />
		<input type="hidden" name="profile_id" 		value="<?php echo $f_profile_id ?>" />
		<input type="hidden" name="platform" 		value="<?php echo $f_platform ?>" />
		<input type="hidden" name="os" 				value="<?php echo $f_os ?>" />
		<input type="hidden" name="os_build" 		value="<?php echo $f_os_build ?>" />
		<input type="hidden" name="product_version" value="<?php echo $f_product_version ?>" />
		<input type="hidden" name="build" 			value="<?php echo $f_build ?>" />
		<input type="hidden" name="report_stay" 	value="1" />
		<input type="hidden" name="view_state"		value="<?php echo $f_view_state ?>" />
		<input type="hidden" name="o" value="<?=$_GET['o']?>">
		<!--<input type="submit" class="button" value="<?php echo lang_get( 'report_more_bugs' ) ?>" />-->
	</form>
	<script language="javascript"><!--
	   document.editFrm.submit();
	//--></script>
<?php
	} else {
		print_bracket_link( string_get_bug_view_url( $t_bug_id ), lang_get( 'view_submitted_bug_link' ) . " $t_bug_id" );
		print_bracket_link( 'index.php?m=webtracking&a=view_all_bug_page', lang_get( 'view_bugs_link' ) );
	}
}
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
