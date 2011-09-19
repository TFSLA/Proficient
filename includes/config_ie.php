<?php /* $Id: config_ie.php,v 1.1 2009-05-19 21:15:30 pkerestezachi Exp $ */


/*
	* * * INSTALLATION INSTRUCTIONS * * *

	YOU MUST customise "config-dist.php" to your local system:

	1) COPY config-dist.php to "config.php" [if it doesn't exist]

	2) EDIT "config.php" to include your database connection and other local settings.
*/

// DATABASE ACCESS INFORMATION [DEFAULT example]
// Modify these values to suit your local settings

$dPconfig['dbtype'] = "mysql";      // ONLY MySQL is supported at present
$dPconfig['dbhost'] = "127.0.0.1";
$dPconfig['dbname'] = "psa_beta";
$dPconfig['dbuser'] = "_test_psa";
$dPconfig['dbpass'] = "tvl-288";


// set this value to true to use persistent database connections
$dPconfig['dbpersist'] = false;

// check for legacy password
// ONLY REQUIRED FOR UPGRADES prior to and including version 1.0 alpha 2
$dPconfig['check_legacy_password'] = false;

/*
 Localisation of the host for this dotproject,
 that is, what language will the login screen be in.
*/
$dPconfig['host_locale'] = "en";
$dPconfig['host_locale_list'] = array("en", "es"); 
$dPconfig['currency_symbol'] = "$";

// default user interface style
$dPconfig['host_style'] = "default";

// local settings
$dPconfig['root_dir'] = "/var/www/htdocs/beta.psa.tfsla.com";  // No trailing slash
$dPconfig['ApacheChoroot'] = "/";
$dPconfig['BckupPath'] = "/var/tfsback";		// Achache relative path
$dPconfig['company_name'] = "Technology for Solutions";
$dPconfig['page_title'] = "Proficient";
$dPconfig['base_url'] = "http://beta.psa.tfsla.com";
$dPconfig['site_domain'] = "beta.psa.tfsla.com";
$dPconfig['smtphost'] = "127.0.0.1";
$dPconfig['mailfrom'] = "info@tfsla.com";
$dPconfig['instanceprefix'] = "betatfslacom"; 
$dPconfig['backupfreq'] = '1'; //Backup frequency in days
$dPconfig['backuphist'] = '10';	//Backup Keep last ...
$dPconfig['mysqldump'] = "/usr/local/bin/mysqldump";

$dPconfig['upload_imag'] = "/files/articles";		
$dPconfig['base_url_imag'] = "./files/articles";

// HHRR Settings
// BackEnd 
$dPconfig['company_hhrr_mail'] = "info@tfsla.com";
$dPconfig['company_contact_info'] = "Tel 4341-4530"; //html formatted
// FrontEnd 
$dPconfig['hhrr_uploads_dir'] = "./files/hhrr";         // Uploads path
$dPconfig['hhrr_cv_extensions'] = array("doc", "pdf", "rtf", "txt", "wri", "swf", "zip", "rar", "bz2", "tgz", "gz", "tar", "z", "arj");         // File types allowed for cv uploads
$dPconfig['hhrr_pic_extensions'] = array("jpg", "jpeg", "gif", "png"); // pic types allowed for Photo uploads



// settings for use in webtracking
$dPconfig['adminmail'] = "info@tfsla.com";
$dPconfig['webmastermail'] = "info@tfsla.com";


// enable if you want to be able to see other users's tasks
$dPconfig['show_all_tasks'] = false;

// enable if you want to support gantt charts
$dPconfig['enable_gantt_charts'] = true;

/** Sets the locale for the jpGraph library.  Leave blank if you experience problems */
$dPconfig['jpLocale'] = '';

// enable if you want to log changes using the history module
$dPconfig['log_changes'] = false;

// enable if you want to check task's start and end dates
// disable if you want to be able to leave start or end dates empty
$dPconfig['check_tasks_dates'] = true;

// warn when a translation is not found (for developers and tranlators)
$dPconfig['locale_warn'] = false;

// the string appended to untranslated string or unfound keys
$dPconfig['locale_alert'] = '^';

// the number of 'working' hours in a day
$dPconfig['daily_working_hours'] = 8.0;

// set debug = true to help analyse errors
$dPconfig['debug'] = false;

// set to true if you need to be able to relink tickets to
// an arbitrary parent.  Useful for email-generated tickets,
// but the interface is a bit clunky.
$dPconfig['link_tickets_kludge'] = false;

// Calendar settings.
// Day view start end and increment
$dPconfig['cal_day_start'] = 0;		// Start hour, in 24 hour format
$dPconfig['cal_day_end'] = 23;		// End hour in 24 hour format
$dPconfig['cal_day_increment'] = 15;	// Increment, in minutes


// Timesheets Settings
$dPconfig['timexp_notify_admin'] = false;		// Send or not an email to the SYSADMIN 
												// when a timesheet changes its status
$dPconfig['timexp_notify_creator'] = true;		// Send or not an email to the creator 
			  									// of a timesheet when it changes its status

$dPconfig['timexp_notify_supervisors'] = true;		// Send or not an email to the supervisors 
													// of a timesheet when it changes its status

							
$dPconfig['timexp_notify_test'] = false;					// only for development test
$dPconfig['timexp_notify_file'] = "./files/temp/timexp_notify.txt";			// Location to output the notify 

// Projects Settings

	// 25 = Reporter (see Access levels on: ./webtracking/core/constant_inc.php)
$dPconfig['projects_users_default_webtracking_permission'] = "25";			
$dPconfig['projects_admins_default_webtracking_permission'] = "90"; 

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";


// difference in seconds if the server has the mktime problem
//if(! is_file( "./includes/mktime_bug/mktime_difference.php" )){
//	include_once("./includes/mktime_bug/mktime_update_difference.php");
//}
//include_once("./includes/mktime_bug/mktime_difference.php");


if (!$dPconfig['mktime_difference'])
	$dPconfig['mktime_difference'] = 0;


$dPconfig['records_per_page'] = 20;	
$dPconfig['webtracking_log_mail_send'] = false;	

// alerts params
$dPconfig["debugalerts"] = true;
$dPconfig["n_days_milestone_ending"] = "5";
$dPconfig["n_days_constraint_date"] = "5";
$dPconfig["n_days_target_end_date"] = "5" ;
$dPconfig["x_perc_target_budget_exceeded"] = ".9";
$dPconfig["x_days_age_data_hhrr"] = "365";
$dPconfig["x_days_resend_age_data_hhrr"] = "180";


// modificar el archvo includes/config.php y agregar 
$dPconfig["mail_to_folder_enabled"] = true;
$dPconfig["mail_disable_send"] = false;
$dPconfig["mail_to_folder_path"] = "files/temp";

// Cantidad de dias a mostrar en MIS ASIGNACIONES por defecto
$dPconfig['days_myassignments'] = 30;


?>
