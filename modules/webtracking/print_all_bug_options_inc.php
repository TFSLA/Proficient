<?php
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
# this function only gets the field names, by appending strings
function get_field_names()
{
	#currently 27 fields
	return $t_arr = array (		lang_get( 'id' ),
							    lang_get( 'category' ),
							    lang_get( 'severity' ),
							    lang_get( 'reproducibility' ),
								lang_get( 'date_submitted' ),
								lang_get( 'last_update' ),
								lang_get( 'reporter' ),
								lang_get( 'assigned_to' ),
								lang_get( 'priority' ),
								lang_get( 'status' ),
								lang_get( 'build' ),
								lang_get( 'projection' ),
								lang_get( 'eta' ),
								lang_get( 'platform' ),
								lang_get( 'os' ),
								lang_get( 'os_version' ),
								lang_get( 'product_version' ),
								lang_get( 'resolution' ),
								lang_get( 'duplicate_id' ),
								lang_get( 'summary' ),
								lang_get( 'description' ),
								lang_get( 'steps_to_reproduce' ),
								lang_get( 'additional' ).'_'.lang_get( 'information' ),
								lang_get( 'attached_files' ),
								lang_get( 'bugnote_title' ),
								lang_get( 'bugnote_date' ),
								lang_get( 'bugnote_description' )) ;
}


function edit_printing_prefs( $p_user_id = null, $p_error_if_protected = true, $p_redirect_url = '' )
{
	if ( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$c_user_id = db_prepare_int( $p_user_id );

	# protected account check
	if ( $p_error_if_protected ) {
		user_ensure_unprotected( $p_user_id );
	}

	$t_user_print_pref_table = config_get( 'mantis_user_print_pref_table' );

	if ( is_blank( $p_redirect_url ) ) {
		$p_redirect_url = 'index.php?m=webtracking&a=print_all_bug_page';
	}

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count( $t_field_name_arr );

	# Grab the data
    $query = "SELECT print_pref
    		FROM $t_user_print_pref_table
			WHERE user_id='$c_user_id'";
    $result = db_query( $query );

    ## OOPS, No entry in the database yet.  Lets make one
    if ( 0 == db_num_rows( $result ) ) {

		# create a default array, same size than $t_field_name
		for ($i=0 ; $i<$field_name_count ; $i++) {
			$t_default_arr[$i] = 1 ;
		}
		$t_default = implode( '', $t_default_arr ) ;

		# all fields are added by default
		$query = "INSERT
				INTO $t_user_print_pref_table
				(user_id, print_pref)
				VALUES
				('$c_user_id','$t_default')";

		$result = db_query( $query );

		# Rerun select query
	    $query = "SELECT print_pref
	    		FROM $t_user_print_pref_table
				WHERE user_id='$c_user_id'";
	    $result = db_query( $query );
    }

    # putting the query result into an array with the same size as $t_fields_arr
	$row = db_fetch_array( $result );
	$t_prefs = $row[0];
   
?>

<?php # Account Preferences Form BEGIN ?>
<?php $t_index_count=0; ?>
<br />
<div align="center">
<form method="post" action="index.php?m=webtracking&a=print_all_bug_options_update">
<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="redirect_url" value="<?php echo string_attribute( $p_redirect_url ) ?>" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'printing_preferences_title' ) ?>
	</td>
	<td class="right">
	</td>
</tr>


<?php # display the checkboxes
for ($i=0 ; $i <$field_name_count ; $i++) {

	$row_color = $i%2+1;
	PRINT "<tr class=\"row-($row_color)\">";
?>

	<td class="category">
		<?php echo $t_field_name_arr[$i] ?>
	</td>
	<td>
		<?php # @@@ REWORK Code should not span two lines except in extreme cases.  Build this into a variable then print it out.  ?>
		<?php //echo 'print_'.strtolower(str_replace(' ','_', $t_field_name_arr[$i])); ?>
		<input type="checkbox" name="<?php echo 'print_' . strtolower( str_replace( ' ', '_', $t_field_name_arr[$i] ) ); ?>"
		<?php if ( isset( $t_prefs[$i] ) && ( $t_prefs[$i]==1 ) ) echo 'checked="checked"' ?> />
	</td>
</tr>

<?php
}
?>
<tr>
	<td>&nbsp;</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_prefs_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="index.php?m=webtracking&a=print_all_bug_options_reset">
	<input type="submit" class="button" value="<?php echo lang_get( 'reset_prefs_button' ) ?>" />
	</form>
</div>

<?php } # end of edit_printing_prefs() ?>
