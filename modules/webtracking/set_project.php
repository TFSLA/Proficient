<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_make_default	= gpc_get_bool( 'make_default' );
	$f_ref			= gpc_get_string( 'ref', '' );
	

	if ( ALL_PROJECTS != $f_project_id ) {
		project_ensure_exists( $f_project_id );
	}
 
	# Set default project
	if ( $f_make_default ) {
		current_user_set_default_project( $f_project_id );
	}

	$actualiza_task = false;
	
	if(helper_get_current_project() != $f_project_id){
	   $actualiza_task = true;
	   $f_task_id = 0;
	}else{
	   $f_task_id = gpc_get_int( 'task_id' );  // Aca me saca
	}
	
	helper_set_current_project( $f_project_id );
	
	if ($actualiza_task){
	    helper_set_current_task( 0 );
	}else{
		helper_set_current_task( $f_task_id );
	}

	#@@@ we really need to make this more general... it is never intuitive
	#  to redirect to the main page as far as I can see. Is there a reason
	#  we can't just redirect back to the referrer in all cases?  See
	#  issue #2686 about this... -jf

	# redirect to 'same page' when switching projects.
	# view_all_* pages, and summary
	# for proxies that clear out HTTP_REFERER
	if ( !is_blank( $f_ref ) ) {
		$t_redirect_url = "index.php?m=webtracking&a=".$f_ref;
	} else if ( !isset( $_SERVER['HTTP_REFERER'] ) || is_blank( $_SERVER['HTTP_REFERER'] ) ) {
		$t_redirect_url = 'index.php?m=webtracking&a=main_page';
	} else if ( eregi( 'view_all_bug_page.php', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'index.php?m=webtracking&a=view_all_set&type=0';
	} else if ( eregi( 'index.php?m=webtracking&a=summary_page', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url =  'index.php?m=webtracking&a=summary_page';
	} else if ( eregi( 'index.php?m=webtracking&a=proj_user_menu_page', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'index.php?m=webtracking&a=proj_user_menu_page';
	} else if ( eregi( 'index.php?m=webtracking&a=manage_user_page', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'index.php?m=webtracking&a=manage_user_page';
	} else if ( eregi( 'index.php?m=webtracking&a=bug_report_page', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'index.php?m=webtracking&a=bug_report_page';
	} else if ( eregi( 'index.php?m=webtracking&a=bug_report_advanced_page', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'index.php?m=webtracking&a=bug_report_advanced_page';
	} else if ( eregi( 'index.php?m=webtracking&a=print_all_bug_page', $_SERVER['HTTP_REFERER'] ) ){
		echo "okokok";
		$t_redirect_url = 'index.php?m=webtracking&a=print_all_bug_page';
	} else {
		$t_redirect_url = 'index.php?m=webtracking&a=main_page';
	}

?>
<?php html_page_top1() ?>
<?php
	html_meta_redirect( $t_redirect_url );
?>
<?php html_page_top1() ?>

<br />
<div align="center">
<?php
	//echo lang_get( 'operation_successful' ).'<br />';

	//print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
