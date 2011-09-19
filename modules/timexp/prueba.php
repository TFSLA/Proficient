<?php

require_once( $AppUI->getSystemClass ('dp' ) );
require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'admin' ) );
require_once( $AppUI->getModuleClass( 'files' ) );
require_once( $AppUI->getModuleClass( 'forums' ) );
require_once( $AppUI->getModuleClass( 'system' ) );
require_once( $AppUI->getModuleClass( 'emailalerts' ) );

global $AppUI;


		$uid = "1";
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
		$allowed = array();

		$usr = new CUser();
		if (!$usr->load($uid, false)){
			$AppUI->setMsg( 'User' );
			$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
			$AppUI->redirect();
		
		}	
		//el SYSADMIN siempre puede ver todos los proyectos
		if ($usr->user_type == 1){
			$sql = "SELECT $fields, -1 permission_value"
				. "\nFROM projects "
				. ($orderby ? "\nORDER BY $orderby" : '');	
			return db_loadHashList( $sql, $index );	

		}

		//obtengo los proyectos en donde el usuario es responsable, administrador o usuario del proyecto
		$sql = "
		select project_id from projects where project_owner = $uid
		union
		select project_id from project_owners where project_owner = $uid
		union
		select project_id from project_roles where  role_id = 2 and user_id = $uid
		";
       //echo "<pre>$sql</pre>";		
		$allowed =  db_loadColumn($sql);		
		
		$sql = "select project_company from projects  where project_id IN (" . implode( ',', $allowed ) . ")";
		
		$companies = (count($allowed) > 0 ? db_loadColumn($sql) : array("-1"));		
/*
echo "<pre>";				
var_dump($companies);
echo "</pre>";		
*/
	
		// Si el usuario tiene permisos sobre el m?ulo proyectos entonces lista el proyecto
		$sql = "SELECT $fields"
			. "\nFROM projects, permissions pperm, permissions cperm";

		if (@$extra['from']) {
			$sql .= ',' . $extra['from'];
		}
		
		$sql .= "\nWHERE pperm.permission_user = $uid"
			. "\n	AND pperm.permission_value <> 0"
			. "\n	AND ("
			. "\n		(pperm.permission_grant_on = 'all')"
			. "\n		OR (pperm.permission_grant_on = 'projects' AND pperm.permission_item = -1)"
			. "\n		OR (pperm.permission_grant_on = 'projects' AND pperm.permission_item = project_id)"
			. "\n	)"
			. (count($allowed) > 0 ? "\n\tAND project_id IN (" . implode( ',', $allowed ) . ')' : '');

		$sql .= "\n AND cperm.permission_user = $uid"
					. "\n	AND cperm.permission_value <> 0"
					. "\n	AND ("
					. "\n		(cperm.permission_grant_on = 'all')"
					. "\n		OR (cperm.permission_grant_on = 'companies' AND cperm.permission_item = -1)"
					. "\n		OR (cperm.permission_grant_on = 'companies' AND cperm.permission_item = project_company)"
					. "\n	)"
					. (count($companies) > 0 ? "\n\tAND project_company IN (" . implode( ',', $companies ) . ')' : '');
								
		if (@$extra['where']) {
			$sql .= "\n\t" . $extra['where'];
		}

		$sql .= ($orderby ? "\nORDER BY $orderby" : '');
//echo "<pre>$sql</pre>";
		$prjAllowed = db_loadHashList( $sql, $index );
		
		
		echo $prjAllowed;

	?>