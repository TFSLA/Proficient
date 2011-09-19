<?php
/*
 * Name:      Dashboard
 * Directory: dashboard
 * Version:   1.0.1
 * Class:     user
 * UI Name:   Dashboard
 * UI Icon:   calendar.jpg
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Dashboard';
$config['mod_version'] = '1.0.1';
$config['mod_directory'] = 'dashboard';
$config['mod_setup_class'] = 'CSetupDashboard';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Dashboard';
$config['mod_ui_icon'] = 'calendar.jpg';
$config['mod_description'] = 'A module for system control & overview';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupDashboard {   

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