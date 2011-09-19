<?php
/*
 * Name:      History
 * Directory: history
 * Version:   0.1
 * Class:     user
 * UI Name:   History
 * UI Icon:
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'History';
$config['mod_version'] = '0.1';
$config['mod_directory'] = 'history';
$config['mod_setup_class'] = 'CSetupHistory';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'History';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for tracking changes';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupHistory {   

	function install() {
		$sql = "CREATE TABLE history ( " .
		  "history_id int(10) unsigned NOT NULL auto_increment," .
		  "history_user int(10) NOT NULL default '0'," .
		  "history_module int(10) NOT NULL default '0'," .
		  "history_project int(10) NOT NULL default '0'," .
		  "history_date datetime NOT NULL default '0000-00-00 00:00:00'," .
		  "history_description text," .
		  "PRIMARY KEY  (history_id)," .
		  "UNIQUE KEY history_id (history_id)" .
		  ") TYPE=MyISAM;";
		db_exec( $sql );
		return null;
	}
	
	function remove() {
		db_exec( "DROP TABLE history" );
		return null;
	}
	
	function upgrade() {
		return null;
	}
}

?>	
	
