<?php
	# Updates printing prefs then redirect to print_all_bug_page_page.php
?>
<?php require_once( 'core.php' ) ?>
<?php require( 'print_all_bug_options_inc.php' ) ?>

<?php auth_ensure_user_authenticated() ?>
<?php
	$f_user_id		= gpc_get_int( 'user_id' );
	$f_redirect_url	= gpc_get_string( 'redirect_url' );

	# the check for the protected state is already done in the form, there is
	# no need to duplicate it here.

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);

	# check the checkboxes
	for ($i=0 ; $i <$field_name_count ; $i++) {
		$t_name='print_'.strtolower(str_replace(' ','_',$t_field_name_arr[$i]));
		if ( !isset( $$t_name ) || ( 1 == ($$t_name) ) ) {
			$t_prefs_arr[$i] = 0;
		}
		else {
			$t_prefs_arr[$i] = 1;
		}
	}

	# get user id
	$t_user_id = $f_user_id;

	$c_export = implode('',$t_prefs_arr);

	# update preferences
	$query = "UPDATE $g_mantis_user_print_pref_table
			SET print_pref='$c_export'
			WHERE user_id='$t_user_id'";

	$result = db_query( $query );

	html_page_top1();
	html_meta_redirect( $f_redirect_url );
	html_page_top2();
	PRINT '<br /><div align="center">';

	if ( $result ) {
		print lang_get( 'operation_successful' );
	} else {
		print error_string( ERROR_GENERIC );
	}

	PRINT '<br />';
	print_bracket_link( $f_redirect_url, lang_get( 'proceed' ) );
	PRINT '<br /></div>';
	html_page_bottom1( __FILE__ );
?>
