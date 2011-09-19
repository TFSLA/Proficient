<?php

	###########################################################################
	# HTML API
	#
	# These functions control the display of each page
	#
	# This is the call order of these functions, should you need to figure out
	#  which to modify or which to leave out.
	#
	#   html_page_top1
	#     html_begin
	#     html_head_begin
	#     html_content_type
	#     html_title
	#     html_css
	#  (html_meta_redirect)
	#   html_page_top2
	#     html_page_top2a
	#       html_head_end
	#       html_body_begin
	#       html_header
	#       html_top_banner
	#     html_login_info
	#    (print_project_menu_bar)
	#     print_menu
	#
	#  ...Page content here...
	#
	#   html_page_bottom1
	#    (print_menu)
	#     html_page_bottom1a
	#       html_bottom_banner
	#  	 html_footer
	#  	 html_body_end
	#  	 html_end
	#
	###########################################################################

	//$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	$t_core_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'project_api.php' );
	require_once( $t_core_dir . 'helper_api.php' );
	require_once( $t_core_dir . 'date_api.php' );

	# --------------------
	# Print the part of the page that comes before meta redirect tags should
	#  be inserted
	function html_page_top1() {
		html_begin();
		html_head_begin();
		html_content_type();
		html_title();
		html_css();
		include( config_get( 'meta_include_file' ) );
	}

	# --------------------
	# Print the part of the page that comes after meta tags, but before the
	#  actual page content
	function html_page_top2() {
		html_page_top2a();

		if ( !db_is_connected() ) {
			return;
		}

		html_login_info();
		if( ON == config_get( 'show_project_menu_bar' ) ) {
			print_project_menu_bar();
			echo '<br />';
		}
		print_menu();
	}

	# --------------------
	# Print the part of the page that comes after meta tags and before the
	#  actual page content, but without login info or menus.  This is used
	#  directly during the login process and other times when the user may
	#  not be authenticated
	function html_page_top2a() {
		html_head_end();
		html_body_begin();
		html_header();
		html_top_banner();
	}

	# --------------------
	# Print the part of the page that comes below the page content
	# $p_file should always be the __FILE__ variable. This is passed to show source
	function html_page_bottom1( $p_file = null ) {
		if ( !db_is_connected() ) {
			return;
		}

		if ( config_get( 'show_footer_menu' ) ) {
			echo '<br />';
			print_menu();
		}

		html_page_bottom1a( $p_file );
	}

	# --------------------
	# Print the part of the page that comes below the page content but leave off
	#  the menu.  This is used during the login process and other times when the
	#  user may not be authenticated.
	function html_page_bottom1a( $p_file = null ) {
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_SERVER;
		}

		if ( null === $p_file ) {
			$p_file = basename( $_SERVER['PHP_SELF'] );
		}

		html_bottom_banner();
		//PSA_MOD:
		//html_footer( $p_file );
		html_body_end();
		html_end();
	}

	# --------------------
	# (1) Print the document type and the opening <html> tag
	function html_begin() {
/*		# @@@ NOTE make this a configurable global.
		#echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		#echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';

		echo '<html>';
*/	}

	# --------------------
	# (2) Begin the <head> section
	function html_head_begin() {
//	   echo '<head>';
	}

	# --------------------
	# (3) Print the content-type
	function html_content_type() {
//		echo '<meta http-equiv="Content-type" content="text/html;charset=' . lang_get( 'charset' ) . '" />';
	}

	# --------------------
	# (4) Print the window title
	function html_title() {
/*		$t_title = config_get( 'window_title' );

		if ( auth_is_user_authenticated() &&
			 db_is_connected() &&
			 ON == config_get( 'show_project_in_title' ) ) {
			if ( ! is_blank( $t_title ) ) {
				$t_title .= ' - ';
			}
			$t_title .= project_get_name( helper_get_current_project() );
		}

		echo '<title>' . string_display( $t_title ) . "</title>\n";
*/	}

	# --------------------
	# (5) Print the link to include the css file
	function html_css() {
/*		$t_css_url = config_get( 'css_include_file' );
		echo '<link rel="stylesheet" type="text/css" href="' . $t_css_url . '" />';
		echo '<script language="JavaScript" type="text/javascript">';
		echo '<!--';
		echo 'if(document.layers) {document.write("<style>td{padding:0px;}<\/style>")}';
		echo '//-->';
		echo '</script>';
*/
	}

	# --------------------
	# (6) Print an HTML meta tag to redirect to another page
	# This function is optional and may be called by pages that need a redirect.
	# $p_time is the number of seconds to wait before redirecting.
	# If we have handled any errors on this page and the 'stop_on_errors' config
	#  option is turned on, return false and don't redirect.
	function html_meta_redirect( $p_url, $p_time=null ) {
		if ( ON == config_get( 'stop_on_errors' ) && error_handled() ) {
			return false;
		}

		if ( null === $p_time ) {
			$p_time = config_get( 'wait_time' );
		}

		echo "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\" />";

		return true;
	}

	# --------------------
	# (7) End the <head> section
	function html_head_end() {
//		echo '</head>';
	}

	# --------------------
	# (8) Begin the <body> section
	function html_body_begin() {
//		echo '<body>';
	}

	# --------------------
	# (9) Print the title displayed at the top of the page
	function html_header() {
/*		$t_title = config_get( 'page_title' );

		if ( auth_is_user_authenticated() &&
			 db_is_connected() &&
			 ON == config_get( 'show_project_in_title' ) ) {
			if ( ! is_blank( $t_title ) ) {
				$t_title .= ' - ';
			}
			$t_title .= project_get_name( helper_get_current_project() );
		}

		echo '<div class="center"><span class="pagetitle">' . string_display( $t_title ) . '</span></div>';
*/	}

	# --------------------
	# (10) Print a user-defined banner at the top of the page if there is one.
	function html_top_banner() {
		$t_page = config_get( 'top_include_page' );

		if ( !is_blank( $t_page ) &&
			 file_exists( $t_page ) &&
			 !is_dir( $t_page ) ) {
			include( $t_page );
		}
	}

	# --------------------
	# (11) Print the user's account information
	# Also print the select box where users can switch projects
	function html_login_info() {
		$t_username = current_user_get_field( 'username' );
		$t_access_level = get_enum_element( 'access_levels', current_user_get_access_level() );
		$t_now = format_date( config_get( 'complete_date_format' ) );

	}

	# --------------------
	# (12) Print a user-defined banner at the bottom of the page if there is one.
	function html_bottom_banner() {
		$t_page = config_get( 'bottom_include_page' );

		if ( !is_blank( $t_page ) &&
			 file_exists( $t_page ) &&
			 !is_dir( $t_page ) ) {
			include( $t_page );
		}
	}

	# --------------------
	# (13) Print the page footer information
	function html_footer( $p_file ) {
		global $g_timer, $g_queries_array;

		# If a user is logged in, update their last visit time.
		# We do this at the end of the page so that:
		#  1) we can display the user's last visit time on a page before updating it
		#  2) we don't invalidate the user cache immediately after fetching it
		if ( auth_is_user_authenticated() ) {
			$t_user_id = auth_get_current_user_id();
			user_update_last_visit( $t_user_id );
		}

		echo '<br />';
		echo '<hr size="1" />';
		if ( ON == config_get( 'show_version' ) ) {
			echo '<span class="timer"><a href="http://mantisbt.sourceforge.net/">Mantis ' . config_get( 'mantis_version' ) . '</a></span>';
		}
		echo '<address>Copyright &copy; 2000 - 2003</address>';
		echo '<address><a href="mailto:' . config_get( 'webmaster_email' ) . '">' . config_get( 'webmaster_email' ) . '</a></address>';
		if ( ON == config_get( 'show_timer' ) ) {
			$g_timer->print_times();
		}
		if ( ON == config_get( 'show_queries_count' ) ) {
			$t_count = count( $g_queries_array );
			echo $t_count.' total queries executed.<br />';
			echo count( array_unique ( $g_queries_array ) ).' unique queries executed.<br />';
			if ( ON == config_get( 'show_queries_list' ) ) {
				echo '<table>';
				$t_shown_queries = array();
				for ( $i = 0; $i < $t_count; $i++ ) {
					if ( in_array( $g_queries_array[$i], $t_shown_queries ) ) {
						echo '<tr><td style="color: red">'.($i+1).'</td><td style="color: red">'.htmlspecialchars($g_queries_array[$i]).'</td></tr>';
					} else {
						array_push( $t_shown_queries, $g_queries_array[$i] );
						echo '<tr><td>'.($i+1).'</td><td>'.htmlspecialchars($g_queries_array[$i]) . '</td></tr>';
					}
				}
				echo '</table>';
			}
		}
	}

	# --------------------
	# (14) End the <body> section
	function html_body_end() {
//		echo '</body>';
	}

	# --------------------
	# (15) Print the closing <html> tag
	function html_end() {
//		echo '</html>';
	}


	###########################################################################
	# HTML Menu API
	###########################################################################

	# --------------------
	# Print the main menu
	function print_menu() {
		if ( auth_is_user_authenticated() ) {
			$t_protected = current_user_get_field( 'protected' );

global $AppUI;
$AppUI->savePlace();
// setup the title block

global $titleBlock;
global $m;
global $a;
$titleBlock = new CTitleBlock( 'WebTracking', 'bugicon.gif', $m, "webtracking.index" );
$titleBlock->addCell();



if ($canEdit && $project_id) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new bug').'">', '',
		'<form action="?m=webtracking&a=addedit" method="post">', '</form>'
	);
}




			//RDG echo '<table class="width100" cellspacing="0">';
			//RDG echo '<tr>';
			//RDG 	echo '<td class="menu">';
				//PSA_MOD:
				//echo '<a href="main_page.php">' . lang_get( 'main_link' ) . '</a> | ';
				//RDG echo '<a href="index.php?m=webtracking&a=view_all_bug_page">' . lang_get( 'view_bugs_link' ) . '</a> | ';
				$titleBlock->addCrumb("?m=webtracking&a=view_all_bug_page", lang_get( 'view_bugs_link' ));
				if ( access_has_project_level( REPORTER ) ) {
					//RDG echo string_get_bug_report_link() . ' | ';http://isis01/psa/index.php?m=webtracking&a=bug_report_page
					$titleBlock->addCrumb("?m=webtracking&a=bug_report_page", lang_get( 'report_bug_link' ));
				}

				if ( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
					//RDG echo '<a href="index.php?m=webtracking&a=summary_page">' . lang_get( 'summary_link' ) . '</a> | ';
					$titleBlock->addCrumb("?m=webtracking&a=summary_page", lang_get( 'summary_link' ));
				}

				//PSA_MOD:
				//echo '<a href="proj_doc_page.php">' . lang_get( 'docs_link' ) . '</a> | ';

				if ( access_has_project_level( config_get( 'manage_project_threshold' ) ) ) {
					if ( access_has_project_level( ADMINISTRATOR ) ) {
					  //RDG $t_link = 'index.php?m=webtracking&a=manage_user_page';
					  $t_link = '?m=webtracking&a=manage_user_page';
					} else {
					  //RDG $t_link = 'index.php?m=webtracking&a=manage_proj_page';
					  $t_link = '?m=webtracking&a=manage_proj_page';
					}
					//RDG echo "<a href=\"$t_link\">" . lang_get( 'manage_link' ) . '</a> | ';
					$titleBlock->addCrumb( $t_link, lang_get( 'manage_link' ));
				}

				//PSA_MOD:
				/*
				if ( access_has_project_level( config_get( 'manage_news_threshold' ) ) ) {
					# Admin can edit news for All Projects (site-wide)
					if ( ( ALL_PROJECTS != helper_get_current_project() ) || ( access_has_project_level( ADMINISTRATOR ) ) ) {
						echo '<a href="index.php?m=webtracking&a=news_menu_page">' . lang_get( 'edit_news_link' ) . '</a> | ';
					} else {
						echo '<a href="index.php?m=webtracking&a=login_select_proj_page">' . lang_get( 'edit_news_link' ) . '</a> | ';
					}
				}
				*/
				//PSA_MOD_END

				# only show accounts that are NOT protected
				if ( OFF == $t_protected ) {
					//RDG echo '<a href="index.php?m=webtracking&a=account_prefs_page">' . lang_get( 'account_link' ) . '</a>  ';
					$titleBlock->addCrumb('?m=webtracking&a=account_prefs_page', lang_get( 'account_link' ));
				}

				//PSA_MOD:
				//echo '<a href="index.php?m=webtracking&a=logout_page">' . lang_get( 'logout_link' ) . '</a>';
				//RDG echo '</td>';

			if($_GET['o']=="projects"){
			          $task_project = $_GET['project_id'];
			          $task_id =  0;
			          helper_set_current_project( $task_project );
	            	          helper_set_current_task(0);
			}elseif ($_GET['o']=="tasks"){
			          $task_project = $_GET['project_id'];
			          $task_id =  $_GET['task_id'];
			          helper_set_current_project( $task_project );
	            	          helper_set_current_task($task_id );
			}else{
			$task_project = helper_get_current_project();
			$task_id =  helper_get_current_task();
			}

			if($task_project >0){
			$titleBlock->addCrumb("?m=projects&a=view&project_id=$task_project", lang_get( 'view_poject' ));
			}


// si hay contenidos anteriores los guardo en una var temporal
$prev_content="";
if (ob_get_length()){
	$prev_content = ob_get_contents();
	ob_clean();
}else{
	ob_start();
}
	       // Antes de obtener el proyecto actual verifico si el proyecto seleccionado corresponde a la incidencia seleccionada , si es que la hay
	          if ($_GET['bug_id']!="")
	         {
				$bug_id = gpc_get_int( 'bug_id' );
				$t_bug = bug_prepare_display( bug_get( $bug_id, true ) );

				$project_bug = $t_bug->project_id;

	            if($task_project != $project_bug)
	            {
	            	helper_set_current_project( $project_bug );
	            	helper_set_current_task(0);

	            	$task_project = $project_bug;
	            	$task_id = '0';
	            }
			}


	       if(($_GET['task_id'] != "" || $_POST['task_id']!= "") && $_GET['o']!="projects")
	       {
	       	  if ($_GET['task_id'] != "")
	       	  {
	       	  	$taskId = $_GET['task_id'];
	       	  }

	       	  if ($_POST['task_id'] != "")
	       	  {
	       	  	$taskId = $_POST['task_id'];
	       	  }

	          $query_project = "SELECT task_project FROM tasks WHERE task_id='".$taskId."' ";
	          $sql_project = db_loadColumn($query_project);
	          $prj = $sql_project[0];

	                  helper_set_current_project( $prj );
		      helper_set_current_task($taskId);

		      $task_project = $prj;
		      $task_id = $taskId;
                       }

	   echo '<table cellspacing="0" cellpadding="0" border="0" width="200">';
	   echo '<tr>';

                //el tag form antes del td para que no genere espaciado
                echo '<form method="post" name="form_set_project" action="index.php?m=webtracking&a=set_project">';

                    echo '<td class="menu" valign="middle" style="white-space: nowrap;">';

                        echo '&nbsp;&nbsp; ';
        				if ( ON == config_get( 'use_javascript' )) {
        					echo '<select name="project_id" id="project_id" class="small" onchange="document.forms.form_set_project.submit();" style="width: 250px">';
        				} else {
        					echo '<select name="project_id" id="project_id" class="small" style="width: 250px>';
        				}
        				print_project_option_list( $task_project );
        				echo '</select></td>';


        			echo '<td class="menu" valign="middle" style="white-space: nowrap;" >';

        				// Traigo las tareas del proyecto seleccionado
        				if ($task_project > 0){
        				$sql_tasks = "SELECT task_id, task_name FROM tasks WHERE task_project = '".$task_project."' ";
		                $tasks_list = db_loadHashList($sql_tasks);
        				}

		                echo '<select name="task_id" style="width:250px" class="small" onchange="document.forms.form_set_project.submit();">';

				        if(count($tasks_list)>0)
				        {
				        	echo "<option value=\"0\">".$AppUI->_("All tasks")."</option>";

					        foreach ($tasks_list as $id_task=>$task_name)
					        {
					        	if ($id_task == $task_id)
					        	{ $sel = "SELECTED"; }
					        	else
					        	{ $sel = ""; }

					        	echo "<option value=\"$id_task\" $sel>$task_name</option>";
					        }
				        }
				        else
				        {
				        	echo "<option value=\"0\">".$AppUI->_("All tasks")."</option>";
				        }


		                echo '</select>';

                    echo '</td><td class="menu" valign="middle" style="white-space: nowrap;">';
                        echo '<input type="submit" class="button"  value="' . lang_get( 'switch' ) . '" class="button" />';
                        echo '&nbsp;&nbsp;';
                    echo '</td>';
                echo '</form>';
                //el tag form antes del td para que no genere espaciado
                echo '<form method="post" action="index.php?m=webtracking&a=jump_to_bug">';
                    echo '<td class="menu" style="white-space: nowrap;">';

    					echo "&nbsp;&nbsp;<input type=\"text\" name=\"bug_id\" size=\"10\" class=\"small\" />&nbsp;";
                    echo '</td><td class="menu" valign="middle" style="white-space: nowrap;">';
                        echo '<input type="submit" class="button" value="' . lang_get( 'jump' ) . '" class="button" />&nbsp;';

                    echo '</td>';
                echo '</form>';
			echo '</tr>';
			echo '</table>';

$filters = ob_get_contents();
ob_clean();
// si hay contenidos anteriores los vuelvo a presentar
if ($prev_content){
	echo $prev_content;
}else{
	ob_end_clean();
}


$titleBlock->addCell($filters);
$titleBlock->show();



		}
	}

	# --------------------
	# Print the menu bar with a list of projects to which the user has access
	function print_project_menu_bar() {
		$t_project_ids = current_user_get_accessible_projects();

		echo '<table class="width100" cellspacing="0" >';
		echo '<tr>';
			echo '<td class="menu">';
			echo '<a href="index.php?m=webtracking&a=set_project&project_id=' . ALL_PROJECTS . '">' . lang_get( 'all_projects' ) . '</a>';
			$t_project_count = count( $t_project_ids );
			for ( $i=0 ; $i < $t_project_count ; $i++ ) {
				$t_id = $t_project_ids[$i];
				echo " | <a href=\"index.php?m=webtracking&a=set_project&project_id=$t_id\">" . string_display( project_get_field( $t_id, 'name' ) ) . '</a>';
			}
			echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	# --------------------
	# Print the menu for the graph summary section
	function print_menu_graph() {
		if ( config_get( 'use_jpgraph' ) ) {
			$t_icon_path = config_get( 'icon_path' );

			echo '<br />';
			echo '<a href="index.php?m=webtracking&a=summary_page"><img src="' . $t_icon_path.'synthese.gif" border="0" align="center" />' . lang_get( 'synthesis_link' ) . '</a> | ';
			echo '<a href="index.php?m=webtracking&a=summary_graph_imp_status"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'status_link' ) . '</a> | ';
			echo '<a href="index.php?m=webtracking&a=summary_graph_imp_priority"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'priority_link' ) . '</a> | ';
			echo '<a href="index.php?m=webtracking&a=summary_graph_imp_severity"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'severity_link' ) . '</a> | ';
			echo '<a href="index.php?m=webtracking&a=summary_graph_imp_category"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'category_link' ) . '</a> | ';
			echo '<a href="index.php?m=webtracking&a=summary_graph_imp_resolution"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'resolution_link' ) . '</a>';
		}
	}

	# --------------------
	# Print the menu for the manage section
	# $p_page specifies the current page name so it's link can be disabled
	function print_manage_menu( $p_page='' ) {
		if ( !access_has_project_level( ADMINISTRATOR ) ) {
			return;
		}

		$t_manage_user_page 		= 'index.php?m=webtracking&a=manage_user_page';
		$t_manage_project_menu_page = 'index.php?m=webtracking&a=manage_proj_page';
		$t_manage_custom_field_page = 'index.php?m=webtracking&a=manage_custom_field_page';
		$t_documentation_page 		= 'index.php?m=webtracking&a=documentation_page';

		switch ( $p_page ) {
			case $t_manage_user_page				: $t_manage_user_page 				= ''; break;
			case $t_manage_project_menu_page: $t_manage_project_menu_page 	= ''; break;
			//PSA_MOD:
			//case $t_manage_custom_field_page: $t_manage_custom_field_page 	= ''; break;
			//case $t_documentation_page		: $t_documentation_page 		= ''; break;
			//PSA_MOD_END
		}

		echo '<br /><div align="center">';
			print_bracket_link( $t_manage_user_page, lang_get( 'manage_users_link' ) );
			print_bracket_link( $t_manage_project_menu_page, lang_get( 'manage_projects_link' ) );
			//PSA_MOD:
			//print_bracket_link( $t_manage_custom_field_page, lang_get( 'manage_custom_field_link' ) );
			//print_bracket_link( $t_documentation_page, lang_get( 'documentation_link' ) );
			//PSA_MOD_END
		echo '</div>';
	}

	# --------------------
	# Print the menu for the account section
	# $p_page specifies the current page name so it's link can be disabled
	function print_account_menu( $p_page='' ) {
		$t_account_page 				= 'index.php?m=webtracking&a=account_page';
		$t_account_prefs_page 			= 'index.php?m=webtracking&a=account_prefs_page';
		$t_account_profile_menu_page 	= 'index.php?m=webtracking&a=account_prof_menu_page';

		switch ( $p_page ) {
			case $t_account_page				: $t_account_page 				= ''; break;
			case $t_account_prefs_page			: $t_account_prefs_page 		= ''; break;
			case $t_account_profile_menu_page	: $t_account_profile_menu_page 	= ''; break;
		}

		//print_bracket_link( $t_account_page, lang_get( 'account_link' ) );
		print_bracket_link( $t_account_prefs_page, lang_get( 'change_preferences_link' ) );
		if ( access_has_project_level( config_get( 'add_profile_threshold' ) ) ) {
			print_bracket_link( $t_account_profile_menu_page, lang_get( 'manage_profiles_link' ) );
		}
	}

	# --------------------
	# Print the menu for the docs section
	# $p_page specifies the current page name so it's link can be disabled
	function print_doc_menu( $p_page='' ) {
		$t_documentation_html 	= config_get( 'manual_url' );
		$t_proj_doc_page 		= 'index.php?m=webtracking&a=proj_doc_page';
		$t_proj_doc_add_page 	= 'index.php?m=webtracking&a=proj_doc_add_page';

		switch ( $p_page ) {
			case $t_documentation_html	: $t_documentation_html	= ''; break;
			case $t_proj_doc_page		: $t_proj_doc_page		= ''; break;
			case $t_proj_doc_add_page	: $t_proj_doc_add_page	= ''; break;
		}

		print_bracket_link( $t_documentation_html, lang_get( 'user_documentation' ) );
		print_bracket_link( $t_proj_doc_page, lang_get( 'project_documentation' ) );
		if ( file_allow_project_upload() ) {
			print_bracket_link( $t_proj_doc_add_page, lang_get( 'add_file' ) );
		}
	}

	# --------------------
	# Print the menu for the management docs section
	# $p_page specifies the current page name so it's link can be disabled
	function print_manage_doc_menu( $p_page='' ) {
		$t_path = config_get( 'path' ).'doc/';
		$t_documentation_page = 'index.php?m=webtracking&a=documentation_page';

		switch ( $p_page ) {
			case $t_documentation_page: $t_documentation_page = ''; break;
		}

		echo '<br /><div align="center">';
			print_bracket_link( $t_documentation_page, lang_get( 'system_info_link' ) );
			print_bracket_link( $t_path.'ChangeLog', 'ChangeLog' );
			print_bracket_link( $t_path.'README', 'README' );
			print_bracket_link( $t_path.'INSTALL', 'INSTALL' );
			print_bracket_link( $t_path.'UPGRADING', 'UPGRADING' );
			print_bracket_link( $t_path.'CUSTOMIZATION', 'CUSTOMIZATION' );
		echo '</div>';
	}

	# --------------------
	# Print the menu for the summary section
	# $p_page specifies the current page name so it's link can be disabled
	function print_summary_menu( $p_page='' ) {
		echo '<div align="center">';
		print_bracket_link( 'index.php?m=webtracking&a=print_all_bug_page', lang_get( 'print_all_bug_page_link' ) );

		if ( config_get( 'use_jpgraph' ) != 0 ) {
			$t_summary_page 		= 'index.php?m=webtracking&a=summary_page';
			$t_summary_jpgraph_page = 'index.php?m=webtracking&a=summary_jpgraph_page';

			switch ( $p_page ) {
				case $t_summary_page		: $t_summary_page			= ''; break;
				case $t_summary_jpgraph_page: $t_summary_jpgraph_page	= ''; break;
			}

			print_bracket_link( $t_summary_page, lang_get( 'summary_link' ) );
			print_bracket_link( $t_summary_jpgraph_page, lang_get( 'summary_jpgraph_link' ) );
		}
		echo '</div>';
	}


	#=========================
	# Candidates for moving to print_api
	#=========================

	# --------------------
	# Print the color legend for the status colors
	function html_status_legend() {
		echo '<br />';
		echo '<table class="width100" cellspacing="1">';
		echo '<tr>';
		$t_arr  = explode_enum_string( config_get( 'status_enum_string' ) );
		$enum_count = count( $t_arr );
		$width = (integer) (100 / $enum_count);
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$t_val = get_enum_element( 'status', $t_s[0] );

			$t_color = get_status_color( $t_s[0] );
			echo "<td class=\"small-caption\" width=\"$width%\" bgcolor=\"$t_color\">$t_val</td>";
		}
		echo '</tr>';
		echo '</table>';
	}

	# --------------------
	# Print an html button inside a form
	function html_button ( $p_action, $p_button_text, $p_fields = null ) {
		//$p_action = urlencode( $p_action );
		$p_button_text = string_attribute( $p_button_text );
		if ( null === $p_fields ) {
			$p_fields = array();
		}

		echo "<form method=\"post\" action=\"$p_action\">\n";

		foreach ( $p_fields as $key => $val ) {
			$key = string_attribute( $key );
			$val = string_attribute( $val );

			echo "	<input type=\"hidden\"  name=\"$key\" value=\"$val\" />\n";
		}

		echo "	<input type=\"submit\" class=\"button\" value=\"$p_button_text\" />\n";
		echo "</form>\n";
	}

	# --------------------
	# Print a button to update the given bug
	function html_button_bug_update( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			html_button( string_get_bug_update_page(),
						 lang_get( 'update_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to assign the given bug
	function html_button_bug_assign( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id ) ) {
			$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );

			if ( $t_handler_id != auth_get_current_user_id() ) {
				html_button( 'index.php?m=webtracking&a=bug_assign',
							 lang_get( 'bug_assign_button' ),
							 array( 'bug_id' => $p_bug_id ) );
			}
		}
	}

	# --------------------
	# Print a button to resolve the given bug
	function html_button_bug_resolve( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id ) && access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'index.php?m=webtracking&a=bug_resolve_page',
						 lang_get( 'resolve_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}
	# --------------------
	# Print a button to resolve the given bug
	function html_button_bug_feedback( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'index.php?m=webtracking&a=bug_feedback_page',
						 lang_get( 'updated_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to move the given bug to a different project
	# MASC RELATIONSHIP
	function html_button_bug_create_child( $p_bug_id ) {
		if ( ON == config_get( 'enable_relationship' ) ) {
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
				html_button( 'bug_create_child.php',
							 lang_get( 'create_child_bug_button' ),
							 array( 'bug_id' => $p_bug_id ) );
			}
		}
	}
	# MASC RELATIONSHIP

	# --------------------
	# Print a button to reopen the given bug
	function html_button_bug_reopen( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'reopen_bug_threshold' ), $p_bug_id )
			 || ( bug_get_field( $p_bug_id, 'reporter_id' ) == auth_get_current_user_id()
				  && ON == config_get( 'allow_reporter_reopen' ) ) ) {
			html_button( 'index.php?m=webtracking&a=bug_reopen_page',
						 lang_get( 'reopen_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to close the given bug
	function html_button_bug_close( $p_bug_id ) {
		$t_status = bug_get_field( $p_bug_id, 'status' );

		if ( access_can_close_bug ( $p_bug_id ) && ( $t_status < CLOSED ) ) {
			html_button( 'index.php?m=webtracking&a=bug_close_page',
						 lang_get( 'close_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to monitor the given bug
	function html_button_bug_monitor( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'index.php?m=webtracking&a=bug_monitor',
						 lang_get( 'monitor_bug_button' ),
						 array( 'bug_id' => $p_bug_id, 'action' => 'add' ) );
		}
	}

	# --------------------
	# Print a button to unmonitor the given bug
	#  no reason to ever disallow someone from unmonitoring a bug
	function html_button_bug_unmonitor( $p_bug_id ) {
		html_button( 'index.php?m=webtracking&a=bug_monitor',
					 lang_get( 'unmonitor_bug_button' ),
					 array( 'bug_id' => $p_bug_id, 'action' => 'delete' ) );
	}

	# --------------------
	# Print a button to delete the given bug
	function html_button_bug_delete( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'delete_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'index.php?m=webtracking&a=bug_delete',
						 lang_get( 'delete_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	#---------------------
	# Print a select and a button for printing with a the loco company
	function html_button_print($p_bug_id){
	?>
	<SCRIPT LANGUAGE="JavaScript">
	//<!-- Begin
	function popUp(URL) {
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=1, location=0, statusbar=0, menubar=0, resizable=0, width=980, height=800, left=250, top=102');");
	}
	// End -->
	</script>
		<form action="javascript:popUp('./modules/webtracking/print_index.php?p_bug_id=<?php echo $p_bug_id; ?>')" method="GET">
		<input type='submit'  class='button' value='<?php echo lang_get( 'print_bug_button' ); ?>'>
	</form>
	<?php
	}

	# --------------------
	# Print all buttons for view bug pages
	function html_buttons_view_bug_page( $p_bug_id ) {
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_status = bug_get_field( $p_bug_id, 'status' );

		echo '<table><tr><td>';



		if ( $t_status < $t_resolved ) {
			# UPDATE button
			html_button_bug_update( $p_bug_id );

			echo '</td><td>';
			if ( $t_status == FEEDBACK ){
				# FEEDBACK button
				html_button_bug_feedback( $p_bug_id );

				echo '</td><td>';
			}
			# ASSIGN button
			html_button_bug_assign( $p_bug_id );

			echo '</td><td>';

			# RESOLVE button
			html_button_bug_resolve( $p_bug_id );
		} else {
			# REOPEN button
			html_button_bug_reopen( $p_bug_id );
			if( $t_status == $t_resolved ) {
				echo '</td><td>';
				# RESOLVE button
				html_button_bug_resolve( $p_bug_id );
			}
		}
		echo '</td>';

		# CLOSE button
		echo '<td>';
		html_button_bug_close( $p_bug_id );
		echo '</td>';

		# MONITOR/UNMONITOR button
		echo '<td>';
		if ( user_is_monitoring_bug( auth_get_current_user_id(), $p_bug_id ) ) {
			html_button_bug_unmonitor( $p_bug_id );
		} else {
			html_button_bug_monitor( $p_bug_id );
		}
		echo '</td>';
		# DELETE button
		echo '<td>';
		html_button_bug_delete( $p_bug_id );
		echo '</td>';

		echo '<td>';
		html_button_print($p_bug_id);
		echo '</td>';

		# PUBLISH button

	            $canEdit = !getDenyEdit( 'articles' );
	            if($canEdit)
	            {
			echo "<td>
				<form action=\"?m=articles&a=addeditarticle&type=0\" method=\"POST\">
				<input type='hidden' name='bug_id' value='$p_bug_id'>
				<input type='submit'  class='button' value='".lang_get( 'publish_kb' )."'>
				</form>
				</td>";
			echo '</tr></table>';
	            }
	}
?>
