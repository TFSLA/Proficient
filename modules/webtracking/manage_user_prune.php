<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	
	$t_user_table = config_get( 'mantis_user_table' );

	# Delete the users who have never logged in and are older than 1 week
	$days_old = 7;
	$days_old = (integer)$days_old;
	$query = "SELECT user_id as id
			FROM $t_user_table
			WHERE login_count=0 AND TO_DAYS(NOW()) - '$days_old' > TO_DAYS(date_created)";
	$result = db_query($query);

	$count = db_num_rows( $result );

	if ( $count > 0 ) {

		if(!helper_ensure_confirmed( lang_get( 'confirm_account_pruning' ),
								 lang_get( 'prune_accounts_button' ) )) return;
	}

	for ($i=0; $i < $count; $i++) {
		$row = db_fetch_array( $result );
		user_delete($row['id']);
	}

	$t_redirect_url = 'index.php?m=webtracking&a=manage_user_page';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
