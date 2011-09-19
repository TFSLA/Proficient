<?
	include_once('common.inc.php');
	
	$code = $_POST['code'];
	
	$AppUI->user_id = $_POST['user_id'];
	$AppUI->loadPrefs( $AppUI->user_id );
	
	if($code == '14149989')
	{
		$maxfilesize = 52428800;
		
		$errorCode = 0;
		$errorDescription;
		
		require_once($AppUI->getModuleClass('files'));
		include_once('../modules/files/file_exist_uploadtool.php');
		include_once('../functions/files_func.php');		
		include_once('../modules/files/do_file_aed_uploadtool.php');
		
		if($errorCode > 0)
			echo("<error><code>".$errorCode."</code><description>".$errorDescription."</description></error>");
	}
	else
	{
		echo("<error><code>101</code><description>Invalid credentials.</description></error>");
	}
?>