<?

if($_GET['m'] == 'twitter' || !$xajax)
{	
	require_once("./includes/xajax/xajax.inc.php");
	
	class myXajaxResponse extends xajaxResponse {}
	
	$xajax = new xajax("index.php?m=twitter");

	include_once("modules/public/ajax.php");

	$xajax->processRequests();

	$xajax->printJavascript('includes/xajax/');
}

?>
