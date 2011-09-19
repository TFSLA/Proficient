<?php
	# Assign bug to user then redirect to viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	$user_assign = auth_get_current_user_id();
	
	if($AppUI->user_id == $user_assign ){
		
	   # access level needed to be able to be listed in the assign to field. (REPORTER)
	   $handle_bug_threshold =  access_has_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );
	   
	   if ( !$handle_bug_threshold ) {
	   	  access_denied(); 
                 }

	}else{
	    # access level needed to update bugs (UPDATER)
	    access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	
	    # access level needed to be able to be listed in the assign to field. (REPORTER)
	    access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );
	}
           
	bug_assign( $f_bug_id, auth_get_current_user_id() );
            
	print_successful_redirect_to_bug( $f_bug_id );
?>