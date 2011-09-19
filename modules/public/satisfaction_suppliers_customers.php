<?

	function getSatisfactionLevel($level_type, $project_id)
	{
		global $AppUI;
		
		$sql = "SELECT level_satisfaction, level_satisfaction_user, level_satisfaction_date ";
		$sql .= "FROM satisfaction_suppliers_customers ";
		$sql .= "WHERE project_id = ".$project_id." ";
		$sql .= "AND level_satisfaction_type = ".$level_type." ";
		$sql .= "ORDER BY level_satisfaction_date DESC LIMIT 0,1";
		
		$result = mysql_query($sql);
		$data = mysql_fetch_array($result);
		
		return $data;
	}
	
	function getSatisfactionLevelProjects($company_id)
	{
		global $AppUI;
		
		$sql = "SELECT DISTINCT projects.project_id, project_name, COUNT(*) / SUM(level_satisfaction_type) as typecr ";
		$sql .= "FROM satisfaction_suppliers_customers ";
		$sql .= "INNER JOIN projects ON projects.project_id = satisfaction_suppliers_customers.project_id ";
		$sql .= "LEFT OUTER JOIN users ON (users.user_id = entity_id) ";
		$sql .= "WHERE (level_satisfaction_type = 1 AND users.user_company = ".$company_id.") ";
		$sql .= "OR (entity_id = ".$company_id." AND level_satisfaction_type = 2) ";
		$sql .= "AND satisfaction_suppliers_customers.project_id <> 0 ";
		$sql .= "GROUP BY projects.project_id, project_name ";
		$sql .= "ORDER BY projects.project_name";
		
		return db_loadList($sql);
	}	
	
	function getSatisfactionLevels($company_id, $project_id, $level_type)
	{
		global $AppUI;
		
		if($level_type == 1)
		{
			$sql = "SELECT level_satisfaction, level_satisfaction_user, level_satisfaction_date ";
			$sql .= "FROM satisfaction_suppliers_customers ";
			$sql .= "LEFT OUTER JOIN users ON (users.user_id = entity_id) ";
			$sql .= "WHERE project_id = ".$project_id." ";
			$sql .= "AND level_satisfaction_type = 1 AND users.user_company = ".$company_id." ";
			$sql .= "ORDER BY level_satisfaction_date DESC";
		}
		else
		{
			$sql = "SELECT level_satisfaction, level_satisfaction_user, level_satisfaction_date ";
			$sql .= "FROM satisfaction_suppliers_customers ";
			$sql .= "WHERE project_id = ".$project_id." ";			
			$sql .= "AND entity_id = ".$company_id." AND level_satisfaction_type = 2 ";
			$sql .= "ORDER BY level_satisfaction_date DESC";
		}		
				
		
		return db_loadList($sql);
	}
	
	function getSatisfactionLevelsFiles($company_id)
	{
		global $AppUI;
		
		$sql = "SELECT satisfaction_suppliers_customers.satisfaction_supplier_customer_id, level_satisfaction, level_satisfaction_user, level_satisfaction_date, file_name, original_file_name ";
		$sql .= "FROM satisfaction_suppliers_customers ";
		$sql .= "INNER JOIN satisfaction_suppliers_customers_files ON ";
		$sql .= "satisfaction_suppliers_customers.satisfaction_supplier_customer_id = satisfaction_suppliers_customers_files.satisfaction_supplier_customer_id ";
		$sql .= "WHERE entity_id = ".$company_id." AND level_satisfaction_type = 2 ";
		$sql .= "ORDER BY level_satisfaction_date DESC";
		
		return db_loadList($sql);
	}	
	
	function getSatisfactionLevelCompany($company_id, $level_type)
	{
		global $AppUI;
		
		if($level_type == 1)
		{
			$sql = "SELECT SUM(level_satisfaction) / COUNT(*) as average ";
			$sql .= "FROM satisfaction_suppliers_customers ";
			$sql .= "LEFT OUTER JOIN users ON (users.user_id = entity_id) ";
			$sql .= "WHERE level_satisfaction_type = 1 AND users.user_company = ".$company_id." ";
			$sql .= "AND level_satisfaction > 0";
		}
		else
		{
			$sql = "SELECT SUM(level_satisfaction) / COUNT(*) as average ";
			$sql .= "FROM satisfaction_suppliers_customers ";
			$sql .= "WHERE entity_id = ".$company_id." AND level_satisfaction_type = 2 ";
			$sql .= "AND level_satisfaction > 0";
		}		
				
		
		return db_loadList($sql);
	}	
		
	function addSatisfaction($level_type, $level, $entity_id, $project_id)
	{
		global $AppUI;
		
		$sql = "INSERT satisfaction_suppliers_customers (level_satisfaction_type, level_satisfaction, level_satisfaction_user, entity_id, project_id) ";
		$sql .= "VALUES (".$level_type.", ".$level.", ".$AppUI->user_id.", ".$entity_id.", ".$project_id.")";
		
		db_exec( $sql );
		
		return (mysql_insert_id());		
	}
	
	function addSatisfactionFile($satisfactionId, $file_name, $original_file_name)
	{
		global $AppUI;
		
		$sql = "INSERT satisfaction_suppliers_customers_files (satisfaction_supplier_customer_id, file_name, original_file_name) ";
		$sql .= "VALUES (".$satisfactionId.", '".$file_name."', '".$original_file_name."')";
		
		db_exec( $sql );
	}	

?>