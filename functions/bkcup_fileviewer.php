<?php /* $Id: bkcup_fileviewer.php,v 1.1 2009-05-19 21:15:27 pkerestezachi Exp $ */
//file viewer
require "../includes/config.php";
require "../classes/ui.class.php";



session_name( 'psa'.$dPconfig['instanceprefix'] );
if (get_cfg_var( 'session.auto_start' ) > 0) {
	session_write_close();
}
session_start();
session_register( 'AppUI' ); 

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
	if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout']))
		$user_log_id = $AppUI->user_log_id;
		
    $_SESSION['AppUI'] = new CAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

// check if we are logged in
if ($AppUI->doLogin()) {
    $AppUI->setUserLocale();
	// load basic locale settings
	@include_once( "../locales/$AppUI->user_locale/locales.php" );
	@include_once( "../locales/core.php" );

	$redirect = @$_SERVER['QUERY_STRING'];
	if (strpos( $redirect, 'logout' ) !== false) {
		$redirect = '';
	}
	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}


require "{$AppUI->cfg['root_dir']}/includes/db_connect.php";
include "{$AppUI->cfg['root_dir']}/includes/main_functions.php";
include "{$AppUI->cfg['root_dir']}/includes/permissions.php";

$canRead = !getDenyRead( 'backup' );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

IF ($_GET['id']!=''){
	$sql="SELECT * FROM backup WHERE id=".$_GET['id'];
	$vec=db_fetch_array(db_exec($sql));
	$file=$dPconfig['BckupPath']."/".$vec['file_name'];

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Cache-Control: private, max-age=1, pre-check=10");
	// END extra headers to resolve IE caching bug

	header("MIME-Version: 1.0");
	//header( "Content-length: {$file['file_size']}" );
	header( "Content-type: {tar.gz}" );
        header( "Content-transfer-encoding: binary\n");
	header( "Content-disposition: attachment; filename={$vec['file_name']}" );
	readfile( "{$file}" );
} else {
	$AppUI->setMsg( "fileIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>
