<?php

	function getUserIdByEmail( $email )
	{
		$sql = "SELECT user_id";
		$sql .= " FROM users WHERE user_email = '".$email."'";
		$sql .= " OR user_email_alternative1 = '".$email."'";
		$sql .= " OR user_email_alternative2 = '".$email."'";
		$sql .= " limit 0,1;";
		
		$resultUser = db_loadColumn($sql);
		
		return $resultUser[0];
	}

	function getProjectUsers( $project )
	{
		$sql = "SELECT u.user_id";
		$sql .= " FROM project_roles AS pr";
		$sql .= " INNER JOIN users AS u ON (pr.user_id = u.user_id)";
		$sql .= " WHERE project_id = ".$project;
		$sql .= " UNION";
		$sql .= " SELECT u.user_id";
		$sql .= " FROM project_owners AS po";
		$sql .= " INNER JOIN users AS u";
		$sql .= " ON (po.project_owner = u.user_id)";
		$sql .= " WHERE project_id = ".$project;
		$sql .= " GROUP BY user_id";
				
		$user_projects =  db_loadHashList($sql);
		
		return $user_projects;
	}

	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) {	
		$uid = intval( $uid );

		$allowed = array();

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
		$prjAllowed = db_loadHashList( $sql, $index );
		
		return $prjAllowed;
	}
?>