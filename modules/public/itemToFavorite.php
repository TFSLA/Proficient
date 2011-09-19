<?
	include_once('./modules/public/itemToFavorite_functions.php');

	$item_id = $_GET['item_id'];
	$item_type = $_GET['item_type'];
	$del = $_GET['item_mode_del'];
	$refreshCache = false;
	
	$sql = "SELECT user_id FROM favorites_items WHERE user_id = ".$AppUI->user_id." AND item_id = ".$item_id." AND item_type = ".$item_type;
	$result = mysql_query($sql);
	$data = mysql_fetch_array($result);
	
	if($del == "1")
	{
		if($data)
		{
			$refreshCache = true;
			
			$sql = "DELETE FROM favorites_items WHERE user_id = ".$AppUI->user_id." AND item_id = ".$item_id." AND item_type = ".$item_type;
			db_exec($sql);
			$AppUI->setMsg($AppUI->_('Item removed from favorites'), UI_MSG_OK, true );
		}
		else
			$AppUI->setMsg($AppUI->_('The item that you try to remove, does not exist in your favorites list'), UI_MSG_ERROR, true );
	}
	else
	{
		if(!$data)
		{
			$refreshCache = true;
		
			$sql = "INSERT INTO favorites_items (user_id, item_id, item_type) VALUES (".$AppUI->user_id.", ".$item_id.", ".$item_type.")";
			db_exec($sql);
			$AppUI->setMsg($AppUI->_('Item add to favorites'), UI_MSG_OK, true );
		}
		else
			$AppUI->setMsg($AppUI->_('The item that you try to add, already exists in your favorites list'), UI_MSG_ERROR, true );
	}
	
	if ($refreshCache)
		refreshFavoriteData();
	
	$AppUI->redirect($AppUI->getPlace());	
?>