<?php
//include('../../includes/config.php');
global $AppUI;


// database access
define('DATABASE_HOST',					$dPconfig['dbhost']);
define('DATABASE_NAME',					$dPconfig['dbname']);
define('DATABASE_PASSWORD',				$dPconfig['dbpass']);
define('DATABASE_USER',					$dPconfig['dbuser']);

require_once($AppUI->getLibraryClass('tbl_dump/tbl_dump'));
//require_once ("./tbl_dump/tbl_dump.php");

// in your backup dir create a folder for each table with the table name
// e.g. /table1, /table2

// function parameters
// saveDumpAsFile($table, $exportStructure = true, $nameExtension = '', $limit_from = 0, $limit_to = 0)

// example: export records 1001 to 2000 without table structure
// saveDumpAsFile('tableName', false, '', 1001, 2000);

// call backup function (exports all records including table structure)
//print_r( $_POST );
saveDumpAsFile( $_POST["export_what"] == 1 || $_POST["export_what"] == 2, $_POST["export_what"] == 1 || $_POST["export_what"] == 3, $_POST["compress"] == 1, $_POST["droptable"] == "on" );
?>