<?php
	# This page allows the user to set the fields of the bugs he wants to print
	# Update is POSTed to acount_prefs_update.php3
	# Reset is POSTed to acount_prefs_reset.php3
?><?php require_once( 'core.php' ) ?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php auth_ensure_user_authenticated() ?>       
<?php
	html_page_top1();
	html_page_top2();
	edit_printing_prefs();
	html_page_bottom1( __FILE__ );
?>
