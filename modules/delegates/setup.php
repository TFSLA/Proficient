<?php
/*
 * Name:      Delegates
 * Directory: delegates
 * Version:   1.0
 * Class:     core
 * UI Name:   Delegates
 * UI Icon:   delegates.gif
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Delegates';
$config['mod_version'] = '1.0';
$config['mod_directory'] = 'delegates';
$config['mod_setup_class'] = 'CSetupDelegates';
$config['mod_type'] = 'core';
$config['mod_ui_name'] = 'Delegates';
$config['mod_ui_icon'] = 'delegates.gif';
$config['mod_description'] = '';
$config['mod_active'] = "1";
$config['mod_ui_active'] = "1";

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupDelegates {   

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