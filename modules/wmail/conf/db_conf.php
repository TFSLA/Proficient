<?php
/********************************************************

    conf/db_conf.php
    
	PURPOSE:
		Provide central location for configuring DB-related variables.
		This file replaces (or will replace) the mysqlrc.inc file.

********************************************************/

// DB connection/login info

$DB_HOST=$dPconfig['dbhost'];
$DB_USER=$dPconfig['dbuser'];
$DB_PASSWORD=$dPconfig['dbpass'];

// database name
// ***REQUIRED***
$DB_NAME=$dPconfig['dbname'];

// database brand
// ***REQUIRED***
$DB_TYPE="MySQL";

// Users table name
// ***REQUIRED***
$DB_USERS_TABLE = "webmail_users";

// Sessions table name
// ***REQUIRED***
$DB_SESSIONS_TABLE = "webmail_sessions";

// Contacts table name
//$DB_CONTACTS_TABLE = "webmail_contacts";
$DB_CONTACTS_TABLE = "contacts";

// Prefs table name
$DB_PREFS_TABLE = "webmail_prefs";

// Colors table name
$DB_COLORS_TABLE = "webmail_colors";

// Identities table name
$DB_IDENTITIES_TABLE = "webmail_identities";

// Calendars table name
$DB_CALENDAR_TABLE = "webmail_calendar";

// Bookmarks table name
$DB_BOOKMARKS_TABLE = "webmail_bookmarks";

// Bookmarks table name
//		Optional: Comment out to use file based backend
$DB_CACHE_TABLE = "webmail_cache";

// Log table name
//		Optional: Comment out to use file based backend
//$DB_LOG_TABLE = "webmail_user_log";

// Use persistent connections
//		Optional: Set to 'true' to enable
$DB_PERSISTENT = false;

?>
