<?php
	# This sample file contains the essential files that you MUST
	# configure to your specific settings.  You may override settings
	# from config_defaults_inc.php by assigning new values in this file

	# Rename this file to config_inc.php after configuration.

	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################

	# In general the value OFF means the feature is disabled and ON means the
	# feature is enabled.  Any other cases will have an explanation.

	# Look in http://mantisbt.sourceforge.net/manual or config_defaults_inc.php for more
	# detailed comments.

	# --- database variables ---------

	# set these values to match your setup
	$g_hostname      = $dPconfig['dbhost'];
	$g_port          = 3306;         # 3306 is default
	$g_db_username   = $dPconfig['dbuser'];
	$g_db_password   = $dPconfig['dbpass'];
	$g_database_name = $dPconfig['dbname'];

	# --- email variables -------------
	$g_administrator_email  = $dPconfig['adminmail'];
	$g_webmaster_email      = $dPconfig['webmastermail'];

	# the "From: " field in emails
	//$g_from_email           = $dPconfig['mailfrom'];
	$g_from_email           = "NO-REPLY@".$dPconfig['site_domain'];

	# the "To: " address all emails are sent.  This can be a mailing list or archive address.
	# Actual users are emailed via the bcc: fields
	$g_to_email             = $dPconfig['mailfrom'];

	# the return address for bounced mail
	$g_return_path_email    = $dPconfig['mailfrom'];

	# --- login method ----------------
	# CRYPT or PLAIN or MD5 or LDAP or BASIC_AUTH
	$g_login_method = MD5;

	# --- email vars ------------------
	# set to OFF to disable email check
	# These should be OFF for Windows installations
	$g_validate_email            = OFF;
	$g_check_mx_record           = OFF;

	# --- file upload settings --------
	# This is the master setting to disable *all* file uploading functionality
	#
	# The default value is ON but you must make sure file uploading is enabled
	#  in PHP as well.  You may need to add "file_uploads = TRUE" to your php.ini.
	$g_allow_file_upload	= ON;



// nuevo estado actualizado para reeflejar nueva info
$g_status_enum_string =
	'10:new,20:feedback,25:updated,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed';

# Status color additions
$g_status_colors['updated'] = '#EDCBFF';

$g_status_enum_workflow[NEW_]=
	'10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned';
$g_status_enum_workflow[FEEDBACK] =
	'10:new,20:feedback,25:updated,30:acknowledged,40:confirmed,50:assigned';
$g_status_enum_workflow[UPDATED] =
	'20:feedback,30:acknowledged,40:confi rmed,50:assigned';
$g_status_enum_workflow[ACKNOWLEDGED] =
	'20:feedback,30:acknowledged,40:confi rmed,50:assigned';
$g_status_enum_workflow[CONFIRMED] =
	'20:feedback,40:confirmed,50:assigned';
$g_status_enum_workflow[ASSIGNED] =
	'20:feedback,50:assigned,90:closed';
$g_status_enum_workflow[RESOLVED] =
	'50:assigned,80:resolved,90:closed';
$g_status_enum_workflow[CLOSED] =
	'50:assigned,90:closed';

	###############################
	# Mantis Bugnote Settings
	###############################

	# --- bugnote ordering ------------
	# change to ASC or DESC
	$g_bugnote_order		= 'DESC';

	# --- bug history ordering ----
	# change to ASC or DESC
	$g_history_order		= 'DESC';


	$g_notify_flags['new']	= array('bugnotes'	=> OFF,
									'monitor'	=> OFF,
									'threshold_min'	=> DEVELOPER,
									'threshold_max' => NOBODY);

	$g_notify_flags['reopened']['threshold_min'] = MANAGER;

	$g_notify_flags['closed']	= array(
									'threshold_min'	=> DEVELOPER,
									'threshold_max' => NOBODY);

	$g_bug_readonly_status_threshold = ON;
	$g_update_readonly_bug_threshold = ON;
?>
