<?php
/*
 * Name:      Backup
 * Directory: backup
 * Version:   1.0.1
 * Class:     user
 * UI Name:   Backup
 * UI Icon:   companies.gif
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Backup';
$config['mod_version'] = '1.0.1';
$config['mod_directory'] = 'backup';
$config['mod_setup_class'] = 'CSetupBackup';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Backup';
$config['mod_ui_icon'] = 'companies.gif';
$config['mod_description'] = 'A module for backing up the database';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupBackup {   

	function install() {
		return null;
	}
	
	function remove() {
		return null;
	}
	
	function upgrade() {
		return null;
	}
}

?>