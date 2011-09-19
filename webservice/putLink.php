<?
	include_once('common.inc.php');
	
	$username = $_POST['username'];
	$password = $_POST['password'];	
	
	if($AppUI->login($username,$password, true, false))
	{
		$errorCode = 0;
		$errorDescription;
		
		require_once($AppUI->getModuleClass('articles'));
		include_once('../modules/articles/do_link_aed_uploadtool.php');
		
		if($errorCode > 0)
			echo("<error><code>".$errorCode."</code><description>".$errorDescription."</description></error>");
		else
			echo("<message>Operation Successfully</message>");		
	}
	else
	{
		echo("<error><code>101</code><description>Invalid credentials.</description></error>");
	}
?>