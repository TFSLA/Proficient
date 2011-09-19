<?php

function getTwitterActiveUsers()
{
	global $AppUI;

	$sql = "SELECT DISTINCT u.user_id, CONCAT(u.user_last_name,', ', u.user_first_name) AS name";
	$sql .= " FROM twitter t";
	$sql .= " INNER JOIN users u ON u.user_id = t.twitter_user_id";
	$sql .= " LEFT JOIN user_preferences up ON up.pref_user = t.twitter_user_id AND up.pref_name = 'STPU_TWITTER'";
	$sql .= " WHERE t.twitter_status = 1";
	$sql .= " AND u.user_status = 0";
	$sql .= " AND (up.pref_value = '1' OR u.user_company = ".$AppUI->user_company.")";
	
	return(db_loadHashList($sql));
}

function getTwitterUserData($user_id)
{
	$sql = "SELECT * FROM twitter WHERE twitter_user_id = ".$user_id;
	
	return(db_loadList($sql));
}

?>
