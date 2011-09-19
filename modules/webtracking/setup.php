<?php
/*
 * Name:      Web Tracking
 * Directory: webtracking
 * Version:   1
 * Class:     user
 * UI Name:   Web Tracking
 * UI Icon:   companies.gif
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'WebTracking';
$config['mod_version'] = '1';
$config['mod_directory'] = 'webtracking';
$config['mod_setup_class'] = 'CSetupWebtracking';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Webtracking';
$config['mod_ui_icon'] = 'companies.gif';
$config['mod_description'] = 'A module for software tracking';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupWebtracking {   

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