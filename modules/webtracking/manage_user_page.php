<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'icon_api.php' );
	require_once( $t_core_path . 'date_api.php' );
?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_sort	= gpc_get_string( 'sort', 'username' );
	$f_dir	= gpc_get_string( 'dir', 'ASC' );
	$f_hide = gpc_get_bool( 'hide' );
	$f_save = gpc_get_bool( 'save' );
	$f_prefix = strtoupper( gpc_get_string( 'prefix', config_get( 'default_manage_user_prefix' ) ) );

	$t_cookie_name = config_get( 'manage_cookie' );
	$t_lock_image = '<img src="' . config_get( 'icon_path' ) . 'protected.gif" width="8" height="15" border="0" alt="' . lang_get( 'protected' ) . '" />';

	# set cookie values for hide, sort by, and dir
	if ( $f_save ) {
		$t_manage_string = $f_hide.':'.$f_sort.':'.$f_dir;
		gpc_set_cookie( $t_cookie_name, $t_manage_string, true );
	} else if ( !is_blank( gpc_get_cookie( $t_cookie_name, '' ) ) ) {
		$t_manage_arr = explode( ':', gpc_get_cookie( $t_cookie_name ) );
		$f_hide = $t_manage_arr[0];

		if ( isset( $t_manage_arr[1] ) ) {
			$f_sort = $t_manage_arr[1];
		} else {
			$f_sort = 'username';
		}

		if ( isset( $t_manage_arr[2] ) ) {
			$f_dir  = $t_manage_arr[2];
		} else {
			$f_dir = 'DESC';
		}
	}

	# Clean up the form variables
	$c_sort = addslashes($f_sort);

	if ($f_dir == 'ASC') {
		$c_dir = 'ASC';
	} else {
		$c_dir = 'DESC';
	}

	if ($f_hide == 0) { # a 0 will turn it off
		$c_hide = 0;
	} else {            # anything else (including 'on') will turn it on
		$c_hide = 1;
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'index.php?m=webtracking&a=manage_user_page' ) ?>

<?php # New Accounts Form BEGIN ?>
<?php
	$t_user_table = config_get( 'mantis_user_table' );

	$days_old = 7;
	$query = "SELECT user_id as id, user_username as username, user_email as email, user_password as password, date_created, last_visit, enabled, protected, access_level, login_count, cookie_string
		FROM $t_user_table
		WHERE TO_DAYS(NOW()) - TO_DAYS(date_created) <= '$days_old'
		AND user_type < 5
		ORDER BY date_created DESC";
	$result = db_query( $query );
	$new_user_count = db_num_rows( $result );
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'new_accounts_title' ) ?> (<?php echo lang_get( '1_week_title' ) ?>) [<?php echo $new_user_count ?>]
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td>
<?php
for ($i=0;$i<$new_user_count;$i++) {
	$row = db_fetch_array( $result );
	$t_username = $row['username'];

	echo $t_username.' : ';
}
?>
	</td>
</tr>
</table>
<?php /* New Accounts Form END */ ?>

<?php /* Never Logged In Form BEGIN */ ?>
<?php
	$query = "SELECT user_id as id, user_username as username, user_email as email, user_password as password, date_created, last_visit, enabled, protected, access_level, login_count, cookie_string
		FROM $t_user_table
		WHERE login_count=0 AND user_type < 5
		ORDER BY date_created";
	$result = db_query( $query );
	$user_count = db_num_rows( $result );
?>
<br />
<?/*?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'never_logged_in_title' ) ?> [<?php echo $user_count ?>] 
		<?php print_bracket_link( 'index.php?m=webtracking&a=manage_user_prune', lang_get( 'prune_accounts' ) ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td>
<?php
	for ($i=0;$i<$user_count;$i++) {
		$row = db_fetch_array( $result );
		$t_username = $row['username'];

		echo $t_username.' : ';
	}
?>
	</td>
</tr>
</table>
<?*/?>
<?php /* Never Logged In Form END */ ?>

<?php /* Manage Form BEGIN */ ?>
<?php
	$t_prefix_array = array( 'ALL' );

	for ( $i = 'A'; $i != 'AA'; $i++ ) {
		$t_prefix_array[] = $i;
	}

	for ( $i = 0; $i <= 9; $i++ ) {
		$t_prefix_array[] = "$i";
	}

	$t_index_links = '<br /><center><table class="width75"><tr>';
	foreach ( $t_prefix_array as $t_prefix ) {
		if ( $t_prefix === 'ALL' ) {
			$t_caption = lang_get( 'show_all_users' );
		} else {
			$t_caption = $t_prefix;
		}

		if ( $t_prefix == $f_prefix ) {
			$t_link = "<strong>$t_caption</string>";
		} else {
			$t_link = '<a href="index.php?m=webtracking&a=manage_user_page&prefix=' . $t_prefix .'">' . $t_caption . '</a>';
		}
		$t_index_links .= '<td>' . $t_link . '</td>';
	}
	$t_index_links .= '</tr></table></center>';

	echo $t_index_links;

	if ( $f_prefix === 'ALL' ) {
		$t_where = '(1 = 1)';
	} else {
		$t_where = "(user_username like '$f_prefix%')";
	}

	# Get the user data in $c_sort order
	if ( 0 == $c_hide ) {
		$query = "SELECT user_id as id, user_username as username, user_email as email, user_password as password, last_visit, enabled, protected, access_level, login_count, cookie_string,  UNIX_TIMESTAMP(date_created) as date_created,
				UNIX_TIMESTAMP(last_visit) as last_visit
				FROM $t_user_table
				WHERE $t_where AND user_type < 5
				ORDER BY '$c_sort' $c_dir";
	} else {
		$query = "SELECT user_id as id, user_username as username, user_email as email, user_password as password, last_visit, enabled, protected, access_level, login_count, cookie_string,  UNIX_TIMESTAMP(date_created) as date_created,
				UNIX_TIMESTAMP(last_visit) as last_visit
				FROM $t_user_table
				WHERE (TO_DAYS(NOW()) - TO_DAYS(last_visit) < '$days_old') AND $t_where AND user_type < 5
				ORDER BY '$c_sort' $c_dir";
	}

    $result = db_query($query);
	$user_count = db_num_rows( $result );
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="5">
		<?php echo lang_get( 'manage_accounts_title' ) ?> [<?php echo $user_count ?>]
		<?php //print_bracket_link( 'index.php?m=webtracking&a=manage_user_create_page', lang_get( 'create_new_account_link' ) ) ?>
		<?php print_bracket_link( 'index.php?m=admin&a=addedituser', lang_get( 'create_new_account_link' ) ) ?>

	</td>
	<td class="center" colspan="2">
		<form method="post" action="index.php?m=webtracking&a=manage_user_page">
		<input type="hidden" name="sort" value="<?php echo $c_sort ?>" />
		<input type="hidden" name="dir" value="<?php echo $c_dir ?>" />
		<input type="hidden" name="save" value="1" />
		<input type="checkbox" name="hide" value="1" <?php check_checked( $c_hide, 1 ); ?> /> <?php echo lang_get( 'hide_inactive' ) ?>
		<input type="submit" class="button" value="<?php echo lang_get( 'filter_button' ) ?>" />
		</form>
	</td>
</tr>
<tr class="row-category">
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', lang_get( 'username' ), 'username', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'username' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', lang_get( 'email' ), 'email', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'email' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', lang_get( 'access_level' ), 'access_level', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'access_level' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', lang_get( 'enabled' ), 'enabled', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'enabled' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', $t_lock_image, 'protected', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'protected' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', lang_get( 'date_created' ), 'date_created', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'date_created' ) ?>
	</td>
<?/*?>
	<td>
		<?php print_manage_user_sort_link(  'index.php?m=webtracking&a=manage_user_page', lang_get( 'last_visit' ), 'last_visit', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'last_visit' ) ?>
	</td>
<?*/?>
</tr>
<?php
	for ($i=0;$i<$user_count;$i++) {
		# prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'u' );

		$u_date_created  = format_date( config_get( 'normal_date_format' ), $u_date_created );
		$u_last_visit    = format_date( config_get( 'normal_date_format' ), $u_last_visit );
?>
<tr <?php echo helper_alternate_class( $i ) ?>>
	<td>
		<a href="index.php?m=webtracking&a=manage_user_edit_page&user_id=<?php echo $u_id ?>"><?php echo $u_username ?></a>
	</td>
	<td><?php print_email_link( $u_email, $u_email ) ?></td>
	<td><?php echo get_enum_element( 'access_levels', $u_access_level ) ?></td>
	<td><?php echo trans_bool( $u_enabled ) ?></td>
	<td class="center">
          <?php
		if ( $u_protected ) {
			echo " $t_lock_image";
		} else {
			echo '&nbsp;';
		}
          ?>
        </td>
	<td><?php echo $u_date_created ?></td>
<?/*?>	<td><?php echo $u_last_visit ?></td><?*/?>

</tr>
<?php
	}  # end for
?>
</table>
<?php # Manage Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
