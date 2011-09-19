<?php
	# Reset prefs to defaults then redirect to account_prefs_page.php3
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# protected account check
	current_user_ensure_unprotected();

	# get user id
	$t_user_id = auth_get_current_user_id();

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);
	
	# create a default array, same size than $t_field_name
	for ($i=0 ; $i<$field_name_count ; $i++) { 
		$t_default_arr[$i] = 0 ;
	}
	$t_default = implode('',$t_default_arr) ;

	# reset to defaults
	$query = "UPDATE $g_mantis_user_print_pref_table
			SET print_pref='$t_default'
			WHERE user_id='$t_user_id'";

	$result = db_query( $query );

	$t_redirect_url = 'index.php?m=webtracking&a=print_all_bug_options_page';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
