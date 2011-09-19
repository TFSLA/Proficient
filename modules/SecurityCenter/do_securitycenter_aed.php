<?php
require_once("./modules/SecurityCenter/funciones.php");
$redirect = $_POST['redirect'];


if (isset ($_POST['all_radio'] ) )
	if (!updateSecurity($_POST['all_radio'], 'all'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}
	
if ( isset($_POST['companies_r_txt']) OR isset($_POST['companies_rw_txt']) )
	if (!updateCompaniSecurity( $_POST['companies_r_txt'], $_POST['companies_rw_txt'] ))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}

if (isset ($_POST['reports_radio']) )
	if (!updateSecurity($_POST['reports_radio'],'reports'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	

if (isset ($_POST['timexp_radio']) )
	if (!updateSecurity($_POST['timexp_radio'],'timexp'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['todo_radio']) )
	if (!updateSecurity($_POST['todo_radio'],'todo'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['webtracking_radio']) )
	if (!updateSecurity($_POST['webtracking_radio'],'webtracking'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
/*	
if ( isset($_POST['calendar_r_txt']) OR isset($_POST['calendar_rw_txt']) )
	if (!updateCalendarSecurity( $_POST['calendar_r_txt'], $_POST['calendar_rw_txt']))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}*/	
		
if (isset ($_POST['projects_radio']) )
	if (!updateSecurity($_POST['projects_radio'],'projects'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}

if (isset ($_POST['calendar_radio'] ) )
	if (!updateSecurity($_POST['calendar_radio'], 'calendar'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	

if (isset ($_POST['contacts_radio'] ) )
	if (!updateSecurity($_POST['contacts_radio'], 'contacts'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['delegates_radio'] ) )
	if (!updateSecurity($_POST['delegates_radio'], 'delegates'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['forums_radio'] ) )
	if (!updateSecurity($_POST['forums_radio'], 'forums'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['articles_radio'] ) )
	if (!updateSecurity($_POST['articles_radio'], 'articles'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['reviews_radio'] ) )
	if (!updateSecurity($_POST['reviews_radio'], 'reviews'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	

if (isset ($_POST['wmail_radio'] ) )
	if (!updateSecurity($_POST['wmail_radio'], 'wmail'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['files_radio'] ) )
	if (!updateSecurity($_POST['files_radio'], 'files'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['pipeline_radio'] ) )
	if (!updateSecurity($_POST['pipeline_radio'], 'pipeline'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['hhrr_radio'] ) )
	if (!updateSecurity($_POST['hhrr_radio'], 'hhrr'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	

if (isset ($_POST['backup_radio'] ) )
	if (!updateSecurity($_POST['backup_radio'], 'backup'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	

if (isset ($_POST['emailalerts_radio'] ) )
	if (!updateSecurity($_POST['emailalerts_radio'], 'emailalerts'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	
if (isset ($_POST['admin_radio'] ) )
	if (!updateSecurity($_POST['admin_radio'], 'admin'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	

if (isset ($_POST['system_radio'] ) )
	if (!updateSecurity($_POST['system_radio'], 'system'))
	{
		$AppUI->setMsg( "Erro: " .db_error(), UI_MSG_ERROR );
		$AppUI->redirect($redirect);
	}	
	

//Si llego hasta aca es x que esta todo OK
$AppUI->setMsg( "msg_ok", UI_MSG_OK );
$AppUI->redirect("m=admin");

?>