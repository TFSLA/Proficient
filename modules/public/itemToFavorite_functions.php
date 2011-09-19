<?
	function refreshFavoriteData()
	{
	
		/*
			1 = PROJECTS
			2 = OPORTUNIDADES
			3 = SECCIONES DE LA KB
			4 = FOROS
			5 = SUPERVISION DE TIEMPO Y GASTOS
			6 = MIS REGISTROS
			7 = KB ARTICLES
			8 = KB FILES
			9 = CONTACTS
		   10 = HUMAN RESOURCES
		*/
	
		global $AppUI;
		
		//Cache Favorites
		$sql = "SELECT * FROM favorites_items WHERE user_id = ".$AppUI->user_id." ORDER BY item_type";
		$result = mysql_query($sql);
		$countFavorites = 0;
		
		$favorites = null;

		while ($rowFavorite = mysql_fetch_array($result))
		{
			switch($rowFavorite['item_type'])
			{
				case "1";
					$sql = "SELECT project_name FROM projects WHERE project_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];
					$item_link = "index.php?m=projects&a=view&project_id=".$rowFavorite['item_id'];
					break;
				case "2";
					$sql = "SELECT accountname FROM salespipeline WHERE id = ".$rowFavorite['item_id'];					
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index.php?m=pipeline&a=view&lead_id=".$rowFavorite['item_id']."&delegator_id=".$AppUI->user_id;
					break;
				case "3";
					$sql = "SELECT name FROM articlesections WHERE articlesection_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index.php?m=articles&articlesection_id=".$rowFavorite['item_id'];
					break;
				case "4";
					$sql = "SELECT forum_name FROM forums WHERE forum_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index.php?m=forums&a=viewer&forum_id=".$rowFavorite['item_id'];
					break;
				case "5";
					$item_label = $AppUI->_('Supervision T&E');
					$item_link = "index.php?m=timexp&a=suptimesheets";
					break;
				case "6";
					$item_label = $AppUI->_('My Registries');
					$item_link = "index.php?m=timexp&a=vw_myday";
					break;
				case "7";
					$sql = "SELECT title FROM articles WHERE article_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index_inc.php?inc=./modules/articles/viewarticle.php&m=articles&id=".$rowFavorite['item_id'];
					break;
				case "8";
					$sql = "SELECT file_description FROM files WHERE file_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=".$rowFavorite['item_id'];
					break;
				case "9";
					$sql = "SELECT CONCAT(contact_last_name, ', ', contact_first_name) as name FROM contacts WHERE contact_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index.php?m=contacts&a=viewcontact&tab=0&contact_id=".$rowFavorite['item_id'];
					break;					
				case "10";
					$sql = "SELECT CONCAT(user_last_name, ', ', user_first_name) as name FROM users WHERE user_id = ".$rowFavorite['item_id'];
					$favoriteLabel = db_loadColumn($sql);
					$item_label = $favoriteLabel[0];					
					$item_link = "index.php?m=hhrr&a=viewhhrr&id=".$rowFavorite['item_id'];
					break;					
			}

			$item_label = strtoupper(substr($item_label, 0, 1)).substr($item_label, 1);
			$favorites[$countFavorites] = array('item_id' => $rowFavorite['item_id'], 'item_type' => $rowFavorite['item_type'], 'item_label' => $item_label, 'item_link' => $item_link);

			$favoriteLabelConcat = "";
			$countFavorites++;
		}
		
		$AppUI->setState('ItemsFavorites', $favorites);
	}
	
	function HasItemInFavorites($item_id, $item_type)
	{
		global $AppUI;
	
		$favorites = $AppUI->getState('ItemsFavorites');

		for ($i=0;$i<count($favorites);$i++)
		{
			if($favorites[$i]['item_id'] == $item_id && $favorites[$i]['item_type'] == $item_type)
			{
				return 1;
				break;
			}
		}
		
		return 0;
	}
	
	function getFavoriteItemTypeLabel($item_type)
	{
		$arrItemType = array(	1 => 'Projects',
								2 => 'Opportunities',
								3 => 'KB Sections',
								4 => 'Forums',
								5 => 'Time & Expenses',
								6 => 'My Registries',
								7 => 'Articles',
								8 => 'Files',
								9 => 'Contacts',
								10 => 'HHRR'
							);
		
		return $arrItemType[$item_type];
	}
?>