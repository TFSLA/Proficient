<?php
/*
 * Name:      Wiki
 * Directory: wiki
 * Version:   1
 * Class:     user
 * UI Name:   Wiki
 * UI Icon:   wiki.gif
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Wiki';
$config['mod_version'] = '1';
$config['mod_directory'] = 'wiki';
$config['mod_setup_class'] = 'CSetupWiki';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Wiki';
$config['mod_ui_icon'] = 'wiki.gif';
$config['mod_description'] = 'A module for media wiki';
$config['mod_ui_order'] = '100';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupWiki {   

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