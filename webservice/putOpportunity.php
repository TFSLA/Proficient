<?
	include_once('common.inc.php');
	
	$code = $_POST['code'];
	
	$AppUI->user_id = $_POST['user_id'];
	$AppUI->loadPrefs( $AppUI->user_id );
	
	if($code == '14149989')
	{
		$errorCode = 0;
		$errorDescription;
				
		$dateOpportunity = new CDate();
		
		$_POST["closingdate"] = $dateOpportunity->format( FMT_DATETIME_MYSQL );
		$_POST["invoicedate"] = $dateOpportunity->format( FMT_DATETIME_MYSQL );
		
		$canEdit = 1;
		
		//require_once( $AppUI->getConfig( "root_dir")."/modules/pipeline/leads.class.php" );
		include_once('../modules/pipeline/do_lead_aed.php');
		
		if($errorCode > 0)
			echo("<error><code>".$errorCode."</code><description>".$errorDescription."</description></error>");
	}
	else
	{
		echo("<error><code>101</code><description>Invalid credentials.</description></error>");
	}
?>