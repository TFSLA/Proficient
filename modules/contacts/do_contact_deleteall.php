<?php /* CONTACTS $Id: do_contact_deleteall.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
global $AppUI;
$sql = "delete from contacts where contact_creator = '".$AppUI->user_id."'";



	if(!db_exec( $sql )){
		$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
		//$AppUI->redirect();
	}else{
		$AppUI->setMsg( "deleted succesfully", UI_MSG_OK );
		//$AppUI->redirect();
	}

?>