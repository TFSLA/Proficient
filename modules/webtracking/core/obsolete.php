<?php
	
	###########################################################################
	# Check that obsolete configs are not used.
	# THIS FILE ASSUMES THAT THE CONFIGURATION IS INCLUDED AS WELL AS THE
	# config_api.php.
	###########################################################################

	# Check for obsolete variables

	# ==== Changes after 0.17.5 ====

	config_obsolete( 'new_color', 'status_colors' );
	config_obsolete( 'feedback_color', 'status_colors' );
	config_obsolete( 'acknowledged_color', 'status_colors' );
	config_obsolete( 'confirmed_color', 'status_colors' );
	config_obsolete( 'assigned_color', 'status_colors' );
	config_obsolete( 'resolved_color', 'status_colors' );
	config_obsolete( 'closed_color', 'status_colors' );

	config_obsolete( 'primary_table_tags', '' );
	config_obsolete( 'background_color', '' );
	config_obsolete( 'required_color', '' );
	config_obsolete( 'table_border_color', '' );
	config_obsolete( 'category_title_color', '' );
	config_obsolete( 'primary_color1', '' );
	config_obsolete( 'primary_color2', '' );
	config_obsolete( 'form_title_color', '' );
	config_obsolete( 'spacer_color', '' );
	config_obsolete( 'menu_color', '' );
	config_obsolete( 'fonts', '' );
	config_obsolete( 'font_small', '' );
	config_obsolete( 'font_normal', '' );
	config_obsolete( 'font_large', '' );
	config_obsolete( 'font_color', '' );

	config_obsolete( 'notify_developers_on_new', 'notify_flags' );
	config_obsolete( 'notify_on_new_threshold', 'notify_flags' );
	config_obsolete( 'notify_admin_on_new', 'notify_flags' );
	config_obsolete( 'view_bug_inc', '' );
	config_obsolete( 'ldap_organisation', 'ldap_organization' );
	config_obsolete( 'ldapauth_type', '' );
	config_obsolete( 'summary_product_colon_category', 'summary_category_include_project' );

	config_obsolete( 'allow_href_tags', 'html_make_links' );
	config_obsolete( 'allow_html_tags', 'html_valid_tags' );
	config_obsolete( 'html_tags', 'html_valid_tags' );
	config_obsolete( 'show_user_email', 'show_user_email_threshold' );
	
	config_obsolete( 'manage_custom_fields', 'manage_custom_fields_threshold' );
	config_obsolete( 'allow_bug_delete_access_level', 'delete_bug_threshold' );
	config_obsolete( 'bug_move_access_level', 'move_bug_threshold' );
	
	config_obsolete( 'php', '' );
	config_obsolete( 'use_experimental_custom_fields', '' );
	config_obsolete( 'mail_send_crlf', '' );

	config_obsolete( 'bugnote_include_file', '' );
	config_obsolete( 'bugnote_view_include_file', '' );
	config_obsolete( 'bugnote_add_include_file', '' );
	config_obsolete( 'history_include_file', '' );
	config_obsolete( 'print_bugnote_include_file', '' );
	config_obsolete( 'view_all_include_file', '' );
	config_obsolete( 'bug_view_inc', '' );
	config_obsolete( 'bug_file_upload_inc', '' );

	config_obsolete( 'show_source', '' );

	config_obsolete( 'summary_pad', '' );
?>
