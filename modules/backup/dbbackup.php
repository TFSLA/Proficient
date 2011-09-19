<?
require_once DIR_BASE . 'tbl_dump/tbl_dump.php';

// database access
define('DATABASE_HOST',					'host');
define('DATABASE_NAME',					'dbName');
define('DATABASE_PASSWORD',				'password');
define('DATABASE_USER',					'user');

// set path to backup dir
define('DIR_SQLDUMPS',					'/local/path/to/your/backup/dir');	// without trailing slash

// in your backup dir create a folder for each table with the table name
// e.g. /table1, /table2

// function parameters
// saveDumpAsFile($table, $exportStructure = true, $nameExtension = '', $limit_from = 0, $limit_to = 0)

// example: export records 1001 to 2000 without table structure
// saveDumpAsFile('tableName', false, '', 1001, 2000);

// call backup function (exports all records including table structure)
saveDumpAsFile('tableName');
?>