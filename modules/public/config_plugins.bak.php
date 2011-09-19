<?
	$user_id = $_GET['user_id'];
	$pluginEnabled = $_GET['pluginenabled'];
	$pluginName = $_GET['pluginname'];

   	$sqlExists = "SELECT plugin_user_id FROM user_plugins WHERE plugin_user_id = ".$user_id." AND plugin_name = '".$pluginName."';";

	$recordExists = db_loadColumn($sqlExists);

	if($recordExists[0] > 0)
	{
		$sql = "UPDATE user_plugins SET plugin_enabled = ".$pluginEnabled." WHERE plugin_user_id = ".$user_id." AND plugin_name = '".$pluginName."';";
	}
	else
	{
		$sql = "INSERT INTO user_plugins (plugin_user_id, plugin_name, plugin_enabled) VALUES  (".$user_id.", '".$pluginName."', ".$pluginEnabled.");";
	}

	db_exec($sql);
?>