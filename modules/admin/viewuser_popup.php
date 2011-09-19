<?php 
//Esta es una modificacion del archivo viewuser.php con los TABS solamente para ser llamada desde el Security Center como PopUp
$AppUI->savePlace();
$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserVwTab' ) !== NULL ? $AppUI->getState( 'UserVwTab' ) : 0;
	
		$tabBox = new CTabBox( "?m=admin&a=viewuser_popup&user_id=$user_id&dialog=1&suppressLogo=1", "{$AppUI->cfg['root_dir']}/modules/admin/", $tab );
		$tabBox->add( 'vw_usr_proj', 'Project Security' );
		$tabBox->add( 'vw_task_perms', 'Advanced User Security' );	
		$tabBox->show();

?>