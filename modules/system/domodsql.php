<?php /* SYSTEM $Id: domodsql.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
##
## Activate or move a module entry
##
$cmd = dPgetParam( $_GET, 'cmd', '0' );
$mod_id = intval( dPgetParam( $_GET, 'mod_id', '0' ) );
$mod_directory = dPgetParam( $_GET, 'mod_directory', '0' );

$obj = new CModule();
if ($mod_id) {
	$obj->load( $mod_id );
} else {
	$obj->mod_directory = $mod_directory;
}

$ok = include_once( "{$AppUI->cfg['root_dir']}/modules/$obj->mod_directory/setup.php" );

if (!$ok) {
	if ($obj->mod_type != 'core') {
		$AppUI->setMsg( 'Module setup file could not be found', UI_MSG_ERROR );
		$AppUI->redirect();
	}
}
eval( "\$setup = new {$config['mod_setup_class']}();" );

switch ($cmd) {
	case 'moveup':
	case 'movedn':
		$obj->move( $cmd );
		$AppUI->setMsg( 'Module re-ordered', UI_MSG_OK );
		break;
	case 'toggle':
	// just toggle the active state of the table entry
		$obj->mod_active = 1 - $obj->mod_active;
		$obj->store();
		$AppUI->setMsg( 'Module state changed', UI_MSG_OK );
		break;
	case 'toggleMenu':
	// just toggle the active state of the table entry
		$obj->mod_ui_active = 1 - $obj->mod_ui_active;
		$obj->store();
		$AppUI->setMsg( 'Module menu state changed', UI_MSG_OK );
		break;
	case 'install':
	// do the module specific stuff
		$AppUI->setMsg( $setup->install() );
		$obj->bind( $config );
	// add to the installed modules table
		$obj->install();
		$AppUI->setMsg( 'Module installed', UI_MSG_OK );
		break;
	case 'remove':
	// do the module specific stuff
		$AppUI->setMsg( $setup->remove() );
	// remove from the installed modules table
		$obj->remove();
		$AppUI->setMsg( 'Module removed', UI_MSG_ALERT );
		break;
	case 'upgrade':
		$AppUI->setMsg( $setup->upgrade() );
		break;
	default:
		$AppUI->setMsg( 'Unknown Command', UI_MSG_ERROR );
		break;
}
$AppUI->redirect();
?>